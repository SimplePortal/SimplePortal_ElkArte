<?php

/**
 * @package SimplePortal
 *
 * @author SimplePortal Team
 * @copyright 2015-2023 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.0
 */

use BBC\ParserWrapper;

/**
 * Board Block, Displays a list of posts from selected board(s)
 *
 * @param array $parameters
 * 			'board' => Board(s) to select posts from
 * 			'limit' => max number of posts to show
 * 			'start' => id of post to start from
 *			'length' => preview length of the post
 *			'avatar' => show the poster avatar
 *			'per_page' => number of posts per page to show
 *			'attachment' => Show the first attachment as "blog" image
 * @param int $id - not used in this block
 * @param bool $return_parameters if true returns the configuration options for the block
 */
class Board_News_Block extends SP_Abstract_Block
{
	/** @var array */
	protected $attachments = array();

	/** @var array */
	protected $color_ids = array();

	/** @var array */
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
			'attachment' => 'check',
			'per_page' => 'int',
		);

		parent::__construct($db);
	}

	/**
	 * Initializes a block for use.
	 *
	 * - Called from portal.subs as part of the sportal_load_blocks process
	 *
	 * @param array $parameters
	 * @param int $id
	 */
	public function setup($parameters, $id)
	{
		global $scripturl, $txt, $settings, $context;

		// Break out / sanitize all the parameters
		$board = !empty($parameters['board']) ? explode('|', $parameters['board']) : null;
		$limit = !empty($parameters['limit']) ? (int) $parameters['limit'] : 5;
		$start = !empty($parameters['start']) ? (int) $parameters['start'] : 0;
		$length = isset($parameters['length']) ? (int) $parameters['length'] : 250;
		$avatars = !empty($parameters['avatar']);
		$attachments = !empty($parameters['attachment']);
		$per_page = !empty($parameters['per_page']) ? (int) $parameters['per_page'] : 0;

		$limit = max(0, $limit);
		$start = max(0, $start);

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
				AND ' . (empty($board) ? 't.id_first_msg >= {int:min_msg_id}' : 't.id_board IN ({array_int:current_board})') . ($this->_modSettings['postmod_active'] ? '
				AND t.approved = {int:is_approved}' : '') . '
				AND (t.locked != {int:locked} OR m.icon != {string:icon})
			ORDER BY t.id_first_msg DESC
			LIMIT {int:limit}',
			array(
				'current_board' => $board,
				'min_msg_id' => $this->_modSettings['maxMsgID'] - 45 * min($limit, 5),
				'is_approved' => 1,
				'locked' => 1,
				'icon' => 'moved',
				'limit' => $limit,
			)
		);
		$posts = array();
		while ($row = $this->_db->fetch_assoc($request))
		{
			$posts[] = $row['id_first_msg'];
		}
		$this->_db->free_result($request);

		// No posts, basic error message it is
		$current_url = '';
		if (empty($posts))
		{
			$this->setTemplate('template_sp_boardNews_error');
			$this->data['error_msg'] = $txt['error_sp_no_posts_found'];

			return;
		}

		// Posts and page limits?
		if (!empty($per_page))
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

		// Get the first attachment for each post for this group
		if (!empty($attachments))
		{
			$this->loadAttachments($posts);
		}

		$this->data['news'] = array();

		while ($row = $this->_db->fetch_assoc($request))
		{
			// Good time to do this is ... now
			censor($row['subject']);
			censor($row['body']);

			$attach = !empty($attachments) ? $this->getMessageAttach($row['id_msg'], $row['id_topic'], $row['body']) : '';
			$limited = sportal_parse_cutoff_content($row['body'], 'bbc', $length);

			// Link the ellipsis if the body has been shortened.
			if ($limited)
			{
				$row['body'] .= '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.0" title="' . $row['subject'] . '">&hellip;</a>';
			}

			if (empty($this->_modSettings['messageIconChecks_disable']) && !isset($this->icon_sources[$row['icon']]))
			{
				$this->icon_sources[$row['icon']] = file_exists($settings['theme_dir'] . '/images/post/' . $row['icon'] . '.png') ? 'images_url' : 'default_images_url';
			}

			if ($this->_modSettings['sp_resize_images'])
			{
				$row['body'] = str_ireplace('class="bbc_img', 'class="bbc_img sp_article', $row['body']);
			}

			if (!empty($row['id_member']))
			{
				$this->color_ids[$row['id_member']] = $row['id_member'];
			}

			// If ILA is enable and what we rendered has ILA tags, assume they don't need any further attachment help
			if (!empty($this->_modSettings['attachment_inline_enabled'])
				&& strpos($row['body'], '<img src="' . $scripturl . '?action=dlattach;attach=') !== false)
			{
				$attach = array();
			}

			// Build an array of message information for output
			$this->data['news'][] = array(
				'id' => $row['id_topic'],
				'message_id' => $row['id_msg'],
				'icon' => '<img src="' . $settings[$this->icon_sources[$row['icon']]] . '/post/' . $row['icon'] . '.png" class="icon" alt="' . $row['icon'] . '" />',
				'icon_name' => $row['icon'],
				'subject' => $row['subject'],
				'time' => standardTime($row['poster_time']),
				'views' => $row['num_views'],
				'body' => $row['body'],
				'href' => $scripturl . '?topic=' . $row['id_topic'] . '.0',
				'link' => '<a class="linkbutton" href="' . $scripturl . '?topic=' . $row['id_topic'] . '.0">' . $txt['sp_read_more'] . '</a>',
				'replies' => $row['num_replies'],
				'comment_href' => !empty($row['locked']) ? '' : $scripturl . '?action=post;topic=' . $row['id_topic'] . '.' . $row['num_replies'] . ';num_replies=' . $row['num_replies'],
				'comment_link' => !empty($row['locked']) ? '' : '<a class="linkbutton" href="' . $scripturl . '?action=post;topic=' . $row['id_topic'] . '.' . $row['num_replies'] . ';num_replies=' . $row['num_replies'] . '">' . $txt['reply'] . '</a>',
				'new_comment' => !empty($row['locked']) ? '' : '<a class="linkbutton" href="' . $scripturl . '?action=post;topic=' . $row['id_topic'] . '.' . $row['num_replies'] . '">' . $txt['reply'] . '</a>',
				'poster' => array(
					'id' => $row['id_member'],
					'name' => $row['poster_name'],
					'href' => !empty($row['id_member']) ? $scripturl . '?action=profile;u=' . $row['id_member'] : '',
					'link' => !empty($row['id_member']) ? '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['poster_name'] . '</a>' : $row['poster_name']
				),
				'locked' => !empty($row['locked']),
				'is_last' => false,
				'avatar' => $avatars ? determineAvatar($row) : array(),
				'attachment' => $attach,
			);
		}
		$this->_db->free_result($request);

		// Nothing found, set the message and return
		if (empty($this->data['news']))
		{
			$this->setTemplate('template_sp_boardNews_error');
			$this->data['error_msg'] = $txt['error_sp_no_posts_found'];

			return;
		}

		$this->data['news'][count($this->data['news']) - 1]['is_last'] = true;
		$this->setTemplate('template_sp_boardNews');

		// If we want color id's then lets add them in
		$this->_color_ids();

		// Account for embedded videos
		$this->data['embed_videos'] = !empty($this->_modSettings['enableVideoEmbeding']);

		if (!empty($per_page))
		{
			$context['sp_boardNews_page_index'] = constructPageIndex($current_url . 'news' . $id . '=%1$d', $start, $limit, $per_page, true);
		}
	}

	/**
	 * Load the first available attachments in a message (if any) for a group of messages
	 *
	 * @param array $posts
	 */
	protected function loadAttachments($posts)
	{
		global $attachments;

		require_once(SUBSDIR . '/Attachments.subs.php');

		// We will show attachments in the block, regardless, so save and restore
		$attachmentShowImages = $this->_modSettings['attachmentShowImages'];
		$this->_modSettings['attachmentShowImages'] = 1;
		$this->attachments = getAttachments($posts);
		$this->_modSettings['attachmentShowImages'] = $attachmentShowImages;

		if (!isset($attachments))
		{
			$attachments = array();
		}

		// For each message, use the first attachment in that message and no more
		foreach ($this->attachments as $id_msg => $attachs)
		{
			if (!isset($attachments[$id_msg]))
			{
				foreach ($attachs as $key => $val)
				{
					$attachments[$id_msg][$key] = $val;
					break;
				}
			}
		}
	}

	/**
	 * Load the attachment details into context
	 *
	 * - If the message was found to have attachments (via loadAttachments) then
	 * it will load that attachment data into context.
	 * - If the message did not have attachments, it is then searched for the first
	 * bbc IMG tag, and that image is used.
	 *
	 * @param int $id_msg
	 * @param int $id_topic
	 * @param string $body
	 *
	 * @return array|string
	 */
	protected function getMessageAttach($id_msg, $id_topic, &$body)
	{
		global $topic;

		if (!empty($this->attachments[$id_msg]))
		{
			// A little razzle dazzle since loadAttachment is dependant on this
			// poor behavior
			$o_topic = $topic ?? null;
			$topic = $id_topic;
			$this_attachs = loadAttachmentContext($id_msg);
			$topic = $o_topic;

			// Just one, and it must be an image
			$attachment = array_shift($this_attachs);
			if (!$attachment['is_image'])
			{
				return array();
			}

			return array(
				'id' => $attachment['id'],
				'href' => $attachment['href'] . ';image',
				'name' => $attachment['name'],
			);
		}

		// No attachments, perhaps an IMG tag then?
		$pos = strpos($body, '[img');
		if ($pos === false)
		{
			return '';
		}

		$img_tag = substr($body, $pos, strpos($body, '[/img]', $pos) + 6);
		$parser = ParserWrapper::instance();
		$img_html = $parser->parseMessage($img_tag, true);
		$body = str_replace($img_tag, '<div class="sp_attachment_thumb">' . $img_html . '</div>', $body);

		return array();
	}

	/**
	 * Provide the color profile id's
	 */
	private function _color_ids()
	{
		global $color_profile;

		if (sp_loadColors($this->color_ids) !== false)
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
 * @param array $data
 */
function template_sp_boardNews_error($data)
{
	echo $data['error_msg'];
}

/**
 * Main template for this block
 *
 * @param array $data
 */
function template_sp_boardNews($data)
{
	global $context, $scripturl, $txt, $modSettings;

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
		$attachment = $news['attachment'];

		echo '
			<h3 class="category_header">' . (empty($modSettings['messageIcons_enable'])
				? '<span class="icon i-' . $news['icon_name'] . '"></span>'
				: '<span class="floatleft sp_article_icon">' . $news['icon'] . '</span>') . '
				<a href="', $news['href'], '" >', $news['subject'], '</a>
			</h3>
			<div id="msg_', $news['message_id'], '" class="sp_article_content">
				<div class="sp_content_padding">';

		if (!empty($news['avatar']['href']))
		{
			echo '
					<a href="', $scripturl, '?action=profile;u=', $news['poster']['id'], '">
						<img src="', $news['avatar']['href'], '" alt="', $news['poster']['name'], '" style="max-width:40px" class="floatright" />
					</a>
					<div>', $news['time'], ' ', $txt['by'], ' ', $news['poster']['link'], '<br />', $txt['sp-articlesViews'], ': ', $news['views'], ' | ', $txt['sp-articlesComments'], ': ', $news['replies'], '</div>';
		}
		else
		{
			echo '
					<div>', $news['time'], ' ', $txt['by'], ' ', $news['poster']['link'], ' | ', $txt['sp-articlesViews'], ': ', $news['views'], ' | ', $txt['sp-articlesComments'], ': ', $news['replies'], '</div>';
		}

		echo '
					<div class="post">
						<hr />';

		if (!empty($attachment))
		{
			echo '
						<div class="sp_attachment_thumb">';

			// If you want Fancybox to tag this, remove nfb_ from the id
			echo '
							<a href="', $news['href'], '" id="nfb_link_', $attachment['id'], '"><img src="', $attachment['href'], '" alt="" title="', $attachment['name'], '" id="thumb_', $attachment['id'], '" /></a>';

			echo '
						</div>';
		}

		echo $news['body'], '
					</div>
				<div class="submitbutton">', $news['link'], ' ', $news['new_comment'], '</div>
			</div>
		</div>';
	}

	// Pagination is a good thing
	if (!empty($context['sp_boardNews_page_index']))
	{
		echo '
		<div class="sp_page_index">',
			template_pagesection(false, '', array('page_index' => 'sp_boardNews_page_index')), '
		</div>';
	}
}
