<?php

/**
 * @package SimplePortal
 *
 * @author SimplePortal Team
 * @copyright 2015-2023 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.0
 */


/**
 * Recent Post or Topic block, shows the most recent posts or topics on the forum
 *
 * @param array $parameters
 *        'boards' => list of boards to get posts from,
 *        'limit' => number of topics/posts to show
 *        'type' => recent 0 posts or 1 topics
 *        'display' => compact or full view of the post/topic
 * @param int $id - not used in this block
 * @param bool $return_parameters if true returns the configuration options for the block
 */
class Recent_Block extends SP_Abstract_Block
{
	/**
	 * Constructor, used to define block parameters
	 *
	 * @param Database|null $db
	 */
	public function __construct($db = null)
	{
		$this->block_parameters = array(
			'boards' => 'boards',
			'limit' => 'int',
			'type' => 'select',
			'display' => 'select',
			'refresh_value' => 'int'
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
		global $user_info;

		// Clean the parameters for this block
		$boards = !empty($parameters['boards']) ? explode('|', $parameters['boards']) : null;
		$limit = !empty($parameters['limit']) ? (int) $parameters['limit'] : 5;
		$type = 'ssi_recent' . (empty($parameters['type']) ? 'Posts' : 'Topics');
		$this->data['display'] = empty($parameters['display']) ? 'compact' : 'full';

		// Pass the values to the ssi_ function
		$this->data['items'] = $type($limit, null, $boards, 'array');
		$this->data['class_type'] = empty($parameters['type']) ? 'post' : 'topic';

		// Something recent
		if (!empty($this->data['items']))
		{
			end($this->data['items']);
			$num = key($this->data['items']);
			$this->data['items'][$num]['is_last'] = true;
			reset($this->data['items']);
		}

		// Color id's
		$this->_colorids();

		// Enabling auto refresh?
		if (!empty($parameters['refresh_value']) && !$user_info['is_guest'])
		{
			$this->refresh = array('sa' => 'recent', 'class' => '.sp_recent', 'id' => $id, 'refresh_value' => $parameters['refresh_value']);
			$this->auto_refresh();
		}

		// Off you go
		$this->setTemplate('template_sp_recent');
	}

	/**
	 * Provide the color profile id's
	 */
	private function _colorids()
	{
		global $color_profile;

		$color_ids = array();
		foreach ($this->data['items'] as $item)
		{
			if (!empty($item['poster']) && isset($item['poster']['id']))
				$color_ids[] = $item['poster']['id'];
		}

		if (!empty($color_ids) && sp_loadColors($color_ids) !== false)
		{
			foreach ($this->data['items'] as $k => $p)
			{
				if (!empty($color_profile[$p['poster']['id']]['link']))
				{
					$this->data['items'][$k]['poster']['link'] = $color_profile[$p['poster']['id']]['link'];
				}
			}
		}
	}
}

/**
 * Main template for this block
 *
 * @param array $data
 */
function template_sp_recent($data)
{
	global $txt, $scripturl;

	if (empty($data['items']))
	{
		echo '
		', $txt['error_sp_no_posts_found'];

		return;
	}

	// Show the data in either a compact or full format
	if ($data['display'] === 'compact')
	{
		foreach ($data['items'] as $item)
		{
			echo '
			', empty($item['is_new']) ? '' : ' <a href="' . $scripturl . '?topic=' . $item['topic'] . '.msg' . $item['new_from'] . ';topicseen#new" rel="nofollow"><span class="new_posts">' . $txt['new'] . '</span></a>&nbsp;', '
			<a href="', $item['href'], '">', $item['subject'], '</a>
			<span class="smalltext">', $txt['by'], ' ', $item['poster']['link'],
				'<br />[', $item['time'], '] ', $txt['in'], ' <em>', $item['board']['link'], '</em>
			</span>
			<br />', empty($item['is_last']) ? '<hr />' : '';
		}
	}
	elseif ($data['display'] === 'full')
	{
		echo '
			<table class="sp_fullwidth">';

		$embed_class = sp_embed_class($data['class_type'], '', 'sp_recent_icon centertext');
		foreach ($data['items'] as $item)
		{
			if (!empty($item['is_last']) || empty($item['html_time']))
				continue;

			echo '
				<tr>
					<td ', $embed_class, '></td>
					<td class="sp_recent_subject">',
						empty($item['is_new']) ? '' : '<a href="' . $scripturl . '?topic=' . $item['topic'] . '.msg' . $item['new_from'] . ';topicseen#new"><span class="new_posts">' . $txt['new'] . '</span></a>&nbsp;', '
						<a href="', $item['href'], '">', $item['subject'], '</a>
						<br />[', $item['board']['link'], ']
					</td>
					<td class="sp_recent_info righttext">
						', $item['poster']['link'], '<br />', $item['html_time'], '
					</td>
				</tr>
				<tr>
					<td colspan="3"><hr></td>
				</tr>';
		}

		echo '
			</table>';
	}
}
