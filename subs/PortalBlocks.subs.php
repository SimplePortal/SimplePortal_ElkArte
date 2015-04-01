<?php

/**
 * @package SimplePortal
 *
 * @author SimplePortal Team
 * @copyright 2014 SimplePortal Team
 * @license BSD 3-clause
 *
 * @version 2.4
 */

if (!defined('ELK'))
	die('No access...');

/**
 * Shortcut
 *
 * @param string $name
 * @param mixed[] $parameters
 * @param int $id
 * @param boolean $return_parameters
 * @deprecated since 2.4
 */
function sp_call_block($name, $parameters, $id, $return)
{
	static $instances = array();
	static $db = null;

	if ($db === null)
		$db = database();

	if (!isset($instances[$name]))
	{
		require_once(SUBSDIR . '/spblocks/' . str_replace('_', '', $name) . '.block.php');
		$class = $name . '_Block';
		$instances[$name] = new $class($db);
	}

	$instance = $instances[$name];

	if ($return)
		return $instance->parameters();

	$instance->setup($parameters, $id);
	$instance->render();
}

/**
 * User info block, shows avatar, group, icons, posts, karma, etc
 *
 * @param mixed[] $parameters not used in this block
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 * @deprecated since 2.4
 */
function sp_userInfo($parameters, $id, $return_parameters = false)
{
	sp_call_block('UserInfo', $parameters, $id, $return_parameters);
}

/**
 * Latest member block, shows name and join date for X latest members
 *
 * @param mixed[] $parameters
 *		'limit' => number of members to show
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 * @deprecated since 2.4
 */
function sp_latestMember($parameters, $id, $return_parameters = false)
{
	sp_call_block('LatestMember', $parameters, $id, $return_parameters);
}

/**
 * Who's online block, shows count of users online names
 *
 * @param mixed[] $parameters
 *		'online_today' => shows all users that were online today (requires user online today addon)
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 * @deprecated since 2.4
 */
function sp_whosOnline($parameters, $id, $return_parameters = false)
{
	sp_call_block('WhosOnline', $parameters, $id, $return_parameters);
}

/**
 * Board Stats block, shows count of users online names
 *
 * @param mixed[] $parameters
 *		'averages' => Will calculate the daily average (posts, topics, registrations, etc)
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 * @deprecated since 2.4
 */
function sp_boardStats($parameters, $id, $return_parameters = false)
{
	sp_call_block('BoardStats', $parameters, $id, $return_parameters);
}

/**
 * Top Posters block, shows the top posters on the site, with avatar and name
 *
 * @param mixed[] $parameters
 *		'limit' => number of top posters to show
 *		'type' => period to determine the top poster, 0 all time, 1 today, 2 week, 3 month
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 * @deprecated since 2.4
 */
function sp_topPoster($parameters, $id, $return_parameters = false)
{
	sp_call_block('TopPoster', $parameters, $id, $return_parameters);
}

/**
 * Top stats block, shows the top x members who has achieved top position of various stats
 * Designed to be flexible so adding additional member stats is easy
 *
 * @param mixed[] $parameters
 *		'limit' => number of top posters to show
 *		'type' => top stat to show
 *		'sort_asc' => direction to show the list
 * 		'last_active_limit'
 * 		'enable_label' => use the label
 * 		'list_label' => title for the list
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 * @deprecated since 2.4
 */
function sp_topStatsMember($parameters, $id, $return_parameters = false)
{
	sp_call_block('TopStatsMember', $parameters, $id, $return_parameters);
}

/**
 * Recent Post or Topic block, shows the most recent posts or topics on the forum
 *
 * @param mixed[] $parameters
 *		'boards' => list of boards to get posts from,
 *		'limit' => number of topics/posts to show
 *		'type' => recent 0 posts or 1 topics
 * 		'display' => compact or full view of the post/topic
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 * @deprecated since 2.4
 */
function sp_recent($parameters, $id, $return_parameters = false)
{
	sp_call_block('Recent', $parameters, $id, $return_parameters);
}

/**
 * Top Topics Block, shows top topics by number of view or number of posts
 *
 * @param mixed[] $parameters
 *		'limit' => number of posts to show
 *		'type' => 0 replies or 1 views
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 * @deprecated since 2.4
 */
function sp_topTopics($parameters, $id, $return_parameters = false)
{
	sp_call_block('TopTopics', $parameters, $id, $return_parameters);
}

/**
 * Top Boards Block, shows top boards by number of posts
 *
 * @param mixed[] $parameters
 *		'limit' => number of boards to show
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 * @deprecated since 2.4
 */
function sp_topBoards($parameters, $id, $return_parameters = false)
{
	sp_call_block('TopBoards', $parameters, $id, $return_parameters);
}

/**
 * Poll Block, Shows a specific poll, the most recent or a random poll
 * If user can vote, provides for voting selection
 *
 * @param mixed[] $parameters
 *		'topic' => topic id of the poll
 *		'type' => 1 the most recently posted poll, 2 displays a random poll, null for specific topic
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 * @deprecated since 2.4
 */
function sp_showPoll($parameters, $id, $return_parameters = false)
{
	sp_call_block('ShowPoll', $parameters, $id, $return_parameters);
}

/**
 * Board Block, Displays a list of posts from selected board(s)
 *
 * @param mixed[] $parameters
 *		'board' => Board(s) to select posts from
 *		'limit' => max number of posts to show
 *		'start' => id of post to start from
 *		'length' => preview length of the post
 *		'avatar' => show the poster avatar
 *		'per_page' => number of posts per page to show
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 * @deprecated since 2.4
 */
function sp_boardNews($parameters, $id, $return_parameters = false)
{
	sp_call_block('BoardNews', $parameters, $id, $return_parameters);
}

/**
 * Quick Search Block, Displays a quick search box
 *
 * @param mixed[] $parameters not used in this block
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 * @deprecated since 2.4
 */
function sp_quickSearch($parameters, $id, $return_parameters = false)
{
	sp_call_block('QuickSearch', $parameters, $id, $return_parameters);
}

/**
 * News Block, Displays the forum news
 *
 * @param mixed[] $parameters not used in this block
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 * @deprecated since 2.4
 */
function sp_news($parameters, $id, $return_parameters = false)
{
	sp_call_block('News', $parameters, $id, $return_parameters);
}

/**
 * Image Attachment Block, Displays a list of recent post image attachments
 *
 * @param mixed[] $parameters
 *		'limit' => Board(s) to select posts from
 *		'direction' => 0 Horizontal or 1 Vertical display
 *		'disablePoster' => don't show the poster of the attachment
 *		'disableDownloads' => don't show a download link
 *		'disableLink' => don't show a link to the post
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 * @deprecated since 2.4
 */
function sp_attachmentImage($parameters, $id, $return_parameters = false)
{
	sp_call_block('AttachmentImage', $parameters, $id, $return_parameters);
}

/**
 * Attachment Block, Displays a list of recent attachments (by name)
 *
 * @param mixed[] $parameters
 *		'limit' => Board(s) to select posts from
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 * @deprecated since 2.4
 */
function sp_attachmentRecent($parameters, $id, $return_parameters = false)
{
	sp_call_block('AttachmentRecent', $parameters, $id, $return_parameters);
}

/**
 * Calendar Block, Displays a full calendar block
 *
 * @param mixed[] $parameters
 *		'events' => show events
 *		'birthdays' => show birthdays
 *		'holidays' => show holidays
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 * @deprecated since 2.4
 */
function sp_calendar($parameters, $id, $return_parameters = false)
{
	sp_call_block('Calendar', $parameters, $id, $return_parameters);
}

/**
 * Calendar Info Block, Displays basic calendar ... birthdays, events and holidays.
 *
 * @param mixed[] $parameters
 *		'events' => show events
 *		'future' => how many months out to look for items
 *		'birthdays' => show birthdays
 *		'holidays' => show holidays
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 * @deprecated since 2.4
 */
function sp_calendarInformation($parameters, $id, $return_parameters = false)
{
	sp_call_block('CalendarInformation', $parameters, $id, $return_parameters);
}

/**
 * RSS Block, Displays rss feed in a block.
 *
 * @param mixed[] $parameters
 *		'url' => url of the feed
 *		'show_title' => Show the feed title
 *		'show_content' => Show the content of the feed
 *		'show_date' => Show the date of the feed item
 *		'strip_preserve' => preserve tags
 * 		'count' => number of items to show
 * 		'limit' => number of characters of content to show
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 * @deprecated since 2.4
 */
function sp_rssFeed($parameters, $id, $return_parameters = false)
{
	sp_call_block('RssFeed', $parameters, $id, $return_parameters);
}

/**
 * Theme Selection Block, Displays themes available for user selection
 *
 * @param mixed[] $parameters not used in this block
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 * @deprecated since 2.4
 */
function sp_theme_select($parameters, $id, $return_parameters = false)
{
	sp_call_block('ThemeSelect', $parameters, $id, $return_parameters);
}

/**
 * Staff Block, show the list of forum staff members
 *
 * @param mixed[] $parameters
 *		'lmod' => set to include local moderators as well
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 * @deprecated since 2.4
 */
function sp_staff($parameters, $id, $return_parameters = false)
{
	sp_call_block('Staff', $parameters, $id, $return_parameters);
}

/**
 * Article Block, show the list of articles in the system
 *
 * @param mixed[] $parameters
 *		'category' => list of categories to choose article from
 *		'limit' => number of articles to show
 *		'type' => 0 latest 1 random
 *		'length' => length for the body text preview
 *		'avatar' => whether to show the author avatar or not
 *
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 * @deprecated since 2.4
 */
function sp_articles($parameters, $id, $return_parameters = false)
{
	sp_call_block('Articles', $parameters, $id, $return_parameters);
}

/**
 * Shoutbox Block, show the shoutbox thoughts box
 *
 * @param mixed[] $parameters
 *		'shoutbox' => list of shoutboxes to choose from
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 * @deprecated since 2.4
 */
function sp_shoutbox($parameters, $id, $return_parameters = false)
{
	sp_call_block('Shoutbox', $parameters, $id, $return_parameters);
}

/**
 * Gallery Block, show a gallery box with gallery items
 *
 * @param mixed[] $parameters
 *		'limit' => number of gallery items to show
 *		'type' =>
 *		'direction' => 0 horizontal or 1 vertical display in the block
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 * @deprecated since 2.4
 */
function sp_gallery($parameters, $id, $return_parameters = false)
{
	sp_call_block('Gallery', $parameters, $id, $return_parameters);
}

/**
 * Menu Block, creates a sidebar menu block based on the system main menu
 * @todo needs updating so it knows right vs left block for the flyout
 *
 * @param mixed[] $parameters -  not used in this block
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 * @deprecated since 2.4
 */
function sp_menu($parameters, $id, $return_parameters = false)
{
	sp_call_block('Menu', $parameters, $id, $return_parameters);
}

/**
 * Generic BBC Block, creates a BBC formatted block with parse_bbc
 *
 * @param mixed[] $parameters -  not used in this block
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 * @deprecated since 2.4
 */
function sp_bbc($parameters, $id, $return_parameters = false)
{
	sp_call_block('Bbc', $parameters, $id, $return_parameters);
}

/**
 * Generic HTML Block, creates a formatted block with HTML
 *
 * @param mixed[] $parameters -  not used in this block
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 * @deprecated since 2.4
 */
function sp_html($parameters, $id, $return_parameters = false)
{
	sp_call_block('Html', $parameters, $id, $return_parameters);
}

/**
 * Generic PHP Block, creates a PHP block to do whatever you can imagine :D
 *
 * @param mixed[] $parameters
 *		'textarea' =>
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 * @deprecated since 2.4
 */
function sp_php($parameters, $id, $return_parameters = false)
{
	sp_call_block('Php', $parameters, $id, $return_parameters);
}