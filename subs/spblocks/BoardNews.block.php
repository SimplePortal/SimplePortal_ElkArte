<?php

/**
 * @package SimplePortal
 *
 * @author SimplePortal Team
 * @copyright 2015 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.0 Beta 2
 */

if (!defined('ELK'))
{
	die('No access...');
}

/**
 * Board Block, Displays a list of posts from selected board(s)
 *
 * @param mixed[] $parameters
 *        'board' => Board(s) to select posts from
 *        'limit' => max number of posts to show
 *        'start' => id of post to start from
 *        'length' => preview length of the post
 *        'avatar' => show the poster avatar
 *        'per_page' => number of posts per page to show
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
class Board_News_Block extends SP_Abstract_Block
{
	protected $colorids = array();
	protected $icon_sources = array();

	/**
	 * Constructor, used to define block parameters
	 *
	 * @param Database|null $db
	 */
	public function __construct($db = null)
	{
		$this->block_parameters = array(
			'board' => 'boards',
			'limit' => 'int',
			'start' => 'int',
			'length' => 'int',
			'avatar' => 'check',
			'per_page' => 'int',
		);

		parent::__construct($db);
	}

	/**
	 * Initializes a block for use.
	 *
	 * - Called from portal.subs as part of the sportal_load_blocks process
	 *
	 * @param mixed[] $parameters
	 * @param int $id
	 */
	public function setup($parameters, $id)
	{
		global $scripturl, $txt, $settings, $modSettings, $context;

		// Break out / sanitize all the parameters
		$board = !empty($parameters['board']) ? explode('|', $parameters['board']) : null;
		$limit = !empty($parameters['limit']) ? (int) $parameters['limit'] : 5;
		$start = !empty($parameters['start']) ? (int) $parameters['start'] : 0;
		$length = isset($parameters['length']) ? (int) $parameters['length'] : 250;
		$avatars = !empty($parameters['avatar']);
		$per_page = !empty($parameters['per_page']) ? (int) $parameters['per_page'] : 0;

		$limit = max(0, $limit);
		$start = max(0, $start);

		// Language
		loadLanguage('Stats');

		// Common message icons
		$this->_stable_icons();

		// Load the topics they can see
		$request = $this->_db->query('', '
			SELECT
				t.id_first_msg
			FROM {db_prefix}topics AS t
				INNER JOIN {db_prefix}boards AS b ON (b.id_board = t.id_board)
				INNER JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
			WHERE {query_see_board}
				AND ' . (empty($board) ? 't.id_first_msg >= {int:min_msg_id}' : 't.id_board IN ({array_int:current_board})') . ($modSettings['postmod_active'] ? '
				AND t.approved = {int:is_approved}' : '') . '
				AND (t.locked != {int:locked} OR m.icon != {string:icon})
			ORDER BY t.id_first_msg DESC
			LIMIT {int:limit}',
			array(
				'current_board' => $board,
				'min_msg_id' => $modSettings['maxMsgID'] - 45 * min($limit, 5),
				'is_approved' => 1,
				'locked' => 1,
				'icon' => 'moved',
				'limit' => $limit,
			)
		);
		$posts = array();
		while ($row = $this->_db->fetch_assoc($request))
			$posts[] = $row['id_first_msg'];
		$this->_db->free_result($request);

		// No posts, then set the error template an return
		if (empty($posts))
		{
			$this->setTemplate('template_sp_boardNews_error');
			$this->data['error_msg'] = $txt['error_sp_no_posts_found'];

			return;
		}
		// Posts and page limits?
		elseif (!empty($per_page))
		{
			$limit = count($posts);
			$start = !empty($_REQUEST['news' . $id]) ? (int) $_REQUEST['news' . $id] : 0;

			$clean_url = str_replace('%', '%%', preg_replace('~news' . $id . '=[^;]+;?~', '', $_SERVER['REQUEST_URL']));
			$current_url = $clean_url . (strpos($clean_url, '?') !== false ? (in_array(substr($clean_url, -1), array(';', '?')) ? '' : ';') : '?');
		}

		// Load the actual post details for the topics
		$request = $this->_db->query('', '
			SELECT
				m.icon, m.subject, m.body, IFNULL(mem.real_name, m.poster_name) AS poster_name, m.poster_time,
				t.num_replies, t.id_topic, m.id_member, m.smileys_enabled, m.id_msg, t.locked, mem.avatar, mem.email_address,
				a.id_attach, a.attachment_type, a.filename, t.num_views
			FROM {db_prefix}topics AS t
				INNER JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = m.id_member)
				LEFT JOIN {db_prefix}attachments AS a ON (a.id_member = mem.id_member)
			WHERE t.id_first_msg IN ({array_int:post_list})
			ORDER BY t.id_first_msg DESC
			LIMIT ' . (!empty($per_page) ? '{int:start}, ' : '') . '{int:limit}',
			array(
				'post_list' => $posts,
				'start' => $start,
				'limit' => !empty($per_page) ? $per_page : $limit,
			)
		);
		$this->data['news'] = array();
		while ($row = $this->_db->fetch_assoc($request))
		{
			// Using the cutoff tag?
			$limited = false;
			if (($cutoff = Util::strpos($row['body'], '[cutoff]')) !== false)
			{
				require_once(SUBSDIR . '/Post.subs.php');

				$row['body'] = Util::substr($row['body'], 0, $cutoff);
				preparsecode($row['body']);
				$limited = true;
			}

			// Good time to do this is ... now
			censorText($row['subject']);
			censorText($row['body']);
			$row['body'] = parse_bbc($row['body'], $row['smileys_enabled'], $row['id_msg']);

			// Shorten the text if needed, link the ellipsis if the body has been shortened.
			if ($limited || !empty($length))
			{
				$ellip = '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.0" title="' . $row['subject'] . '">&hellip;</a>';
				$row['body'] = Util::shorten_html($row['body'], $length, $ellip, false);
			}

			if (empty($modSettings['messageIconChecks_disable']) && !isset($this->icon_sources[$row['icon']]))
			{
				$this->icon_sources[$row['icon']] = file_exists($settings['theme_dir'] . '/images/post/' . $row['icon'] . '.png') ? 'images_url' : 'default_images_url';
			}

			if ($modSettings['sp_resize_images'])
			{
				$row['body'] = str_ireplace('class="bbc_img', 'class="bbc_img sp_article', $row['body']);
			}

			if (!empty($row['id_member']))
			{
				$this->colorids[$row['id_member']] = $row['id_member'];
			}

			// Build an array of message information for output
			$this->data['news'][] = array(
				'id' => $row['id_topic'],
				'message_id' => $row['id_msg'],
				'icon' => '<img src="' . $settings[$this->icon_sources[$row['icon']]] . '/post/' . $row['icon'] . '.png" class="icon" alt="' . $row['icon'] . '" />',
				'subject' => $row['subject'],
				'time' => standardTime($row['poster_time']),
				'views' => $row['num_views'],
				'body' => $row['body'],
				'href' => $scripturl . '?topic=' . $row['id_topic'] . '.0',
				'link' => '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.0">' . $txt['sp_read_more'] . '</a>',
				'replies' => $row['num_replies'],
				'comment_href' => !empty($row['locked']) ? '' : $scripturl . '?action=post;topic=' . $row['id_topic'] . '.' . $row['num_replies'] . ';num_replies=' . $row['num_replies'],
				'comment_link' => !empty($row['locked']) ? '' : '| <a href="' . $scripturl . '?action=post;topic=' . $row['id_topic'] . '.' . $row['num_replies'] . ';num_replies=' . $row['num_replies'] . '">' . $txt['ssi_write_comment'] . '</a>',
				'new_comment' => !empty($row['locked']) ? '' : '| <a href="' . $scripturl . '?action=post;topic=' . $row['id_topic'] . '.' . $row['num_replies'] . '">' . $txt['ssi_write_comment'] . '</a>',
				'poster' => array(
					'id' => $row['id_member'],
					'name' => $row['poster_name'],
					'href' => !empty($row['id_member']) ? $scripturl . '?action=profile;u=' . $row['id_member'] : '',
					'link' => !empty($row['id_member']) ? '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['poster_name'] . '</a>' : $row['poster_name']
				),
				'locked' => !empty($row['locked']),
				'is_last' => false,
				'avatar' => $avatars ? determineAvatar($row) : array(),
			);
		}
		$this->_db->free_result($request);

		// Nothing found, say so and exit
		if (empty($this->data['news']))
		{
			$this->setTemplate('template_sp_boardNews_error');
			$this->data['error_msg'] = $txt['error_sp_no_posts_found'];

			return;
		}

		$this->data['news'][count($this->data['news']) - 1]['is_last'] = true;

		// If we want color id's then lets add them in
		$this->_color_ids();

		// And posts may have videos to show
		$this->data['embed_videos'] = !empty($modSettings['enableVideoEmbeding']);

		if (!empty($per_page))
		{
			$context['sp_boardNews_page_index'] = constructPageIndex($current_url . 'news' . $id . '=%1$d', $start, $limit, $per_page, true);
		}
	}

	/**
	 * Provide the color profile id's
	 */
	private function _color_ids()
	{
		global $color_profile;

		if (sp_loadColors($this->colorids) !== false)
		{
			foreach ($this->data['news'] as $k => $p)
			{
				if (!empty($color_profile[$p['poster']['id']]['link']))
				{
					$this->data['news'][$k]['poster']['link'] = $color_profile[$p['poster']['id']]['link'];
				}
			}
		}
	}

	/**
	 * Standard message icons
	 */
	private function _stable_icons()
	{
		$stable_icons = array('xx', 'thumbup', 'thumbdown', 'exclamation', 'question', 'lamp', 'smiley', 'angry', 'cheesy', 'grin', 'sad', 'wink', 'moved', 'recycled', 'wireless');

		foreach ($stable_icons as $icon)
		{
			$this->icon_sources[$icon] = 'images_url';
		}
	}
}

/**
 * Error template for this block
 *
 * @param mixed[] $data
 */
function template_sp_boardNews_error($data)
{
	echo $data['error_msg'];
}

/**
 * Main template for this block
 *
 * @param mixed[] $data
 */
function template_sp_boardNews($data)
{
	global $context, $scripturl, $txt;

	// Auto video embedding enabled?
	if ($data['embed_videos'])
	{
		addInlineJavascript('
		$(document).ready(function() {
			$().linkifyvideo(oEmbedtext);
		});', true);
	}

	// Output all the details we have found
	foreach ($data['news'] as $news)
	{
		echo '
				<h3 class="category_header">
					<span class="floatleft sp_article_icon">', $news['icon'], '</span><a href="', $news['href'], '" >', $news['subject'], '</a>
				</h3>
				<div id="msg_', $news['message_id'], '" class="sp_article_content">
					<div class="sp_content_padding">';

		// @todo replace the <img> with $news['avatar']['img'] and some css for the max-width
		if (!empty($news['avatar']['href']))
		{
			echo '
						<a href="', $scripturl, '?action=profile;u=', $news['poster']['id'], '">
							<img src="', $news['avatar']['href'], '" alt="', $news['poster']['name'], '" style="max-width:40px" class="floatright" />
						</a>
						<div class="middletext">', $news['time'], ' ', $txt['by'], ' ', $news['poster']['link'], '<br />', $txt['sp-articlesViews'], ': ', $news['views'], ' | ', $txt['sp-articlesComments'], ': ', $news['replies'], '</div>';
		}
		else
		{
			echo '
						<div class="middletext">', $news['time'], ' ', $txt['by'], ' ', $news['poster']['link'], ' | ', $txt['sp-articlesViews'], ': ', $news['views'], ' | ', $txt['sp-articlesComments'], ': ', $news['replies'], '</div>';
		}

		echo '
						<div class="post"><hr />', $news['body'], '</div>
						<div class="righttext">', $news['link'], ' ', $news['new_comment'], '</div>
					</div>
				</div>';
	}

	// Pagenation is a good thing
	if (!empty($context['sp_boardNews_page_index']))
	{
		echo '
				<div class="sp_page_index">',
					template_pagesection(false, '', array('page_index' => 'sp_boardNews_page_index')), '
				</div>';
	}
}