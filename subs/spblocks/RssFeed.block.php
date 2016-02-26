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
 * RSS Block, Displays rss feed in a block.
 *
 * @param mixed[] $parameters
 *        'url' => url of the feed
 *        'show_title' => Show the feed title
 *        'show_content' => Show the content of the feed
 *        'show_date' => Show the date of the feed item
 *        'strip_preserve' => preserve tags
 *        'count' => number of items to show
 *        'limit' => number of characters of content to show
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
class Rss_Feed_Block extends SP_Abstract_Block
{
	/**
	 * Constructor, used to define block parameters
	 *
	 * @param Database|null $db
	 */
	public function __construct($db = null)
	{
		$this->block_parameters = array(
			'url' => 'text',
			'show_title' => 'check',
			'show_content' => 'check',
			'show_date' => 'check',
			'strip_preserve' => 'text',
			'count' => 'int',
			'limit' => 'int',
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
		global $txt;

		$feed = !empty($parameters['url']) ? un_htmlspecialchars($parameters['url']) : '';
		$this->data['show_title'] = !empty($parameters['show_title']);
		$this->data['show_content'] = !empty($parameters['show_content']);
		$this->data['show_date'] = !empty($parameters['show_date']);
		$strip_preserve = !empty($parameters['strip_preserve']) ? $parameters['strip_preserve'] : 'br';
		$strip_preserve = preg_match_all('~[A-Za-z0-9]+~', $strip_preserve, $match) ? $match[0] : array();
		$count = !empty($parameters['count']) ? (int) $parameters['count'] : 5;
		$limit = !empty($parameters['limit']) ? (int) $parameters['limit'] : 0;

		// Need a feed name to load it
		if (empty($feed))
		{
			$this->data['error_msg'] = $txt['error_sp_invalid_feed'];
			$this->setTemplate('template_sp_rssFeed_error');

			return;
		}

		$rss = array();

		require_once(SUBSDIR . '/Package.subs.php');
		$data = fetch_web_data($feed);
		$data_save = $data;

		// Convert it to UTF8 if we can and its not already
		preg_match('~encoding="([^"]*)"~', $data, $charset);
		if (!empty($charset[1]) && $charset != 'UTF-8')
		{
			// Use iconv if its available
			if (function_exists('iconv'))
			{
				$data = @iconv($charset[1], 'UTF-8//TRANSLIT//IGNORE', $data);
			}

			// No iconv or a false response from it
			if (!function_exists('iconv') || ($data == false))
			{
				// PHP (some 5.4 versions) mishandles //TRANSLIT//IGNORE and returns false: see https://bugs.php.net/bug.php?id=61484
				if ($data == false)
				{
					$data = $data_save;
				}

				if (function_exists('mb_convert_encoding'))
				{
					// Replace unknown characters with a space
					@ini_set('mbstring.substitute_character', '32');
					$data = @mb_convert_encoding($data, 'UTF-8', $charset[1]);
				}
				elseif (function_exists('recode_string'))
				{
					$data = @recode_string($charset[1] . '..' . 'UTF-8', $data);
				}
			}
		}

		$data = str_replace(array("\n", "\r", "\t"), '', $data);
		$data = preg_replace('~<\!\[CDATA\[(.+?)\]\]>~eu', '\'#cdata_escape_encode#\' . Util::htmlspecialchars(\'$1\')', $data);

		// Find all the feed items
		preg_match_all('~<item>(.+?)</item>~', $data, $items);
		foreach ($items[1] as $item_id => $item)
		{
			if ($item_id === $count)
			{
				break;
			}

			preg_match_all('~<([A-Za-z]+)>(.+?)</\\1>~', $item, $match);

			foreach ($match[0] as $tag_id => $dummy)
			{
				if (Util::strpos($match[2][$tag_id], '#cdata_escape_encode#') === 0)
				{
					$match[2][$tag_id] = stripslashes(un_htmlspecialchars(Util::substr($match[2][$tag_id], 21)));
				}

				$rss[$item_id][strtolower($match[1][$tag_id])] = un_htmlspecialchars($match[2][$tag_id]);
			}
		}

		// Nothing, say its invalid
		if (empty($rss))
		{
			$this->data['error_msg'] = $txt['error_sp_invalid_feed'];
			$this->setTemplate('template_sp_rssFeed_error');

			return;
		}

		// Add all the items to an array
		$this->data['items'] = array();
		foreach ($rss as $item)
		{
			$item['title'] = isset($item['title']) ? strip_tags($item['title']) : '';
			$item['description'] = isset($item['description']) ? strip_tags($item['description'], empty($strip_preserve) ? '' : '<' . implode('><', $strip_preserve) . '>') : '';

			$this->data['items'][] = array(
				'title' => $item['title'],
				'href' => $item['link'],
				'link' => $item['title'] === '' ? '' : ($item['link'] === '' ? $item['title'] : '<a href="' . $item['link'] . '" target="_blank" class="new_win">' . $item['title'] . '</a>'),
				'content' => $limit > 0 ? Util::shorten_text($item['description'], $limit, true) : $item['description'],
				'date' => !empty($item['pubdate']) ? standardTime(strtotime($item['pubdate']), '%d %B') : '',
			);
		}

		// No items in the feed
		if (empty($this->data['items']))
		{
			$this->data['error_msg'] = $txt['error_sp_invalid_feed'];
			$this->setTemplate('template_sp_rssFeed_error');

			return;
		}
		else
		{
			$this->data['items'][count($this->data['items']) - 1]['is_last'] = true;
		}

		$this->setTemplate('template_sp_rssFeed');
	}
}

/**
 * Error template for this block
 *
 * @param mixed[] $data
 */
function template_sp_rssFeed_error($data)
{
	echo '
		', $data['error_msg'];
}

/**
 * Main template for this block
 *
 * @param mixed[] $data
 */
function template_sp_rssFeed($data)
{
	if ($this->data['show_content'])
	{
		echo '
		<div class="sp_rss_flow">
			<ul class="sp_list">';

		foreach ($data['items'] as $item)
		{
			if ($data['show_title'] && !empty($item['link']))
			{
				echo '
				<li ', sp_embed_class('post', '', 'sp_list_top'), '>
					<strong>', $item['link'], '</strong>', ($this->data['show_date'] && !empty($item['date']) ? ' - ' . $item['date'] : ''), '
				</li>';
			}
			echo '
				<li', empty($item['is_last']) ? ' class="sp_list_divider"' : '', '>', $item['content'], '</li>';
		}

		echo '
			</ul>
		</div>';
	}
	else
	{
		echo '
		<ul class="sp_list">';

		foreach ($data['items'] as $item)
		{
			echo '
			<li ', sp_embed_class('dot_feed'), '> ', $item['link'], ($this->data['show_date'] && !empty($item['date']) ? ' - ' . $item['date'] : ''), '</li>';
		}

		echo '
		</ul>';
	}
}