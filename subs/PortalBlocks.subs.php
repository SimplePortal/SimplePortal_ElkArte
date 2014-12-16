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
 */
function sp_userInfo($parameters, $id, $return_parameters = false)
{
	sp_call_block('User_Info', $parameters, $id, $return_parameters);
}

/**
 * Latest member block, shows name and join date for X latest members
 *
 * @param mixed[] $parameters
 *		'limit' => number of members to show
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
function sp_latestMember($parameters, $id, $return_parameters = false)
{
	sp_call_block('Latest_Member', $parameters, $id, $return_parameters);
}

/**
 * Who's online block, shows count of users online names
 *
 * @param mixed[] $parameters
 *		'online_today' => shows all users that were online today (requires user online today addon)
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
function sp_whosOnline($parameters, $id, $return_parameters = false)
{
	sp_call_block('Whos_Online', $parameters, $id, $return_parameters);
}

/**
 * Board Stats block, shows count of users online names
 *
 * @param mixed[] $parameters
 *		'averages' => Will calculate the daily average (posts, topics, registrations, etc)
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
function sp_boardStats($parameters, $id, $return_parameters = false)
{
	sp_call_block('Board_Stats', $parameters, $id, $return_parameters);
}

/**
 * Top Posters block, shows the top posters on the site, with avatar and name
 *
 * @param mixed[] $parameters
 *		'limit' => number of top posters to show
 *		'type' => period to determine the top poster, 0 all time, 1 today, 2 week, 3 month
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
function sp_topPoster($parameters, $id, $return_parameters = false)
{
	sp_call_block('Top_Poster', $parameters, $id, $return_parameters);
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
 */
function sp_topStatsMember($parameters, $id, $return_parameters = false)
{
	sp_call_block('Top_Stats_Member', $parameters, $id, $return_parameters);
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
 */
function sp_topTopics($parameters, $id, $return_parameters = false)
{
	sp_call_block('Top_Topics', $parameters, $id, $return_parameters);
}

/**
 * Top Boards Block, shows top boards by number of posts
 *
 * @param mixed[] $parameters
 *		'limit' => number of boards to show
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
function sp_topBoards($parameters, $id, $return_parameters = false)
{
	sp_call_block('Top_Boards', $parameters, $id, $return_parameters);
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
 */
function sp_showPoll($parameters, $id, $return_parameters = false)
{
	sp_call_block('Show_Poll', $parameters, $id, $return_parameters);
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
 */
function sp_boardNews($parameters, $id, $return_parameters = false)
{
	sp_call_block('Board_News', $parameters, $id, $return_parameters);
}

/**
 * Quick Search Block, Displays a quick search box
 *
 * @param mixed[] $parameters not used in this block
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
function sp_quickSearch($parameters, $id, $return_parameters = false)
{
	global $scripturl, $txt;

	$block_parameters = array();

	if ($return_parameters)
		return $block_parameters;

	echo '
								<form action="', $scripturl, '?action=search2" method="post" accept-charset="UTF-8">
									<div class="centertext">
										<input type="text" name="search" value="" class="sp_search" /><br />
										<input type="submit" name="submit" value="', $txt['search'], '" class="button_submit" />
										<input type="hidden" name="advanced" value="0" />
									</div>
								</form>';
}

/**
 * News Block, Displays the forum news
 *
 * @param mixed[] $parameters not used in this block
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
function sp_news($parameters, $id, $return_parameters = false)
{
	global $context;

	$block_parameters = array();

	if ($return_parameters)
		return $block_parameters;

	echo '
								<div class="centertext">', $context['random_news_line'], '</div>';
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
 */
function sp_attachmentImage($parameters, $id, $return_parameters = false)
{
	global $txt, $color_profile, $scripturl;

	$block_parameters = array(
		'limit' => 'int',
		'direction' => 'select',
		'disablePoster' => 'check',
		'disableDownloads' => 'check',
		'disableLink' => 'check',
	);

	if ($return_parameters)
		return $block_parameters;

	$limit = empty($parameters['limit']) ? 5 : (int) $parameters['limit'];
	$direction = empty($parameters['direction']) ? 0 : 1;
	$type = array('jpg', 'png', 'gif', 'bmp');
	$showPoster = empty($parameters['disablePoster']);
	$showDownloads = empty($parameters['disableDownloads']);
	$showLink = empty($parameters['disableLink']);

	// Let ssi get the attachments
	$items = ssi_recentAttachments($limit, $type, 'array');

	// No attachments, at least none that they can see
	if (empty($items))
	{
		echo '
								', $txt['error_sp_no_attachments_found'];
		return;
	}

	$colorids = array();
	foreach ($items as $item)
		$colorids[] = $item['member']['id'];

	if (!empty($colorids) && sp_loadColors($colorids) !== false)
	{
		foreach ($items as $k => $p)
		{
			if (!empty($color_profile[$p['member']['id']]['link']))
				$items[$k]['member']['link'] = $color_profile[$p['member']['id']]['link'];
		}
	}

	// Build the output for display
	echo '
								<table class="sp_auto_align">', $direction ? '
									<tr>' : '';

	// For each image that was returned from ssi
	foreach ($items as $id => $item)
	{
		if (empty($item['file']['image']))
			continue;

		echo !$direction ? '
									<tr>' : '';

		echo '
										<td>
											<div class="sp_image smalltext">',
												($showLink ? '<a href="' . $item['file']['href'] . '">' . str_replace(array('_', '-'), ' ', $item['file']['filename']) . '</a><br />' : '') . '
												<a id="link_' . $id . '" href="' . $scripturl . '?action=dlattach;topic=' . $item['topic']['id'] . '.0;attach=' . $id . ';image">
												<img id="thumb_' . $id . '" src="' . $scripturl . '?action=dlattach;topic=' . $item['topic']['id'] . '.0;attach=' . $item['file']['image']['id'] . ';image" alt="' . $item['file']['filename'] . '" /></a>
												<br />' .
												($showLink ? '<div class="sp_image_topic">' . $item['topic']['link'] . '</div>' : '') .
												($showDownloads ? $txt['downloads'] . ': ' . $item['file']['downloads'] . '<br />' : ''),
												($showPoster ? $txt['posted_by'] . ': ' . $item['member']['link'] : ''), '
											</div>
										</td>';

		echo !$direction ? '
									</tr>' : '';
	}

	echo $direction ? '
									</tr>' : '';

	echo '
								</table>';
}

/**
 * Attachment Block, Displays a list of recent attachments (by name)
 *
 * @param mixed[] $parameters
 *		'limit' => Board(s) to select posts from
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
function sp_attachmentRecent($parameters, $id, $return_parameters = false)
{
	global $txt;

	$block_parameters = array(
		'limit' => 'int',
	);

	if ($return_parameters)
		return $block_parameters;

	$limit = empty($parameters['limit']) ? 5 : (int) $parameters['limit'];

	$items = ssi_recentAttachments($limit, array(), 'array');

	// No items they can see
	if (empty($items))
	{
		echo '
								', $txt['error_sp_no_attachments_found'];
		return;
	}

	echo '
								<ul class="sp_list">';

	foreach ($items as $item)
		echo '
									<li ', sp_embed_class('attach'), '>
										<a href="', $item['file']['href'], '">', $item['file']['filename'], '</a>
									</li>
									<li class="smalltext">', $txt['downloads'], ': ', $item['file']['downloads'], '</li>
									<li class="smalltext">', $txt['filesize'], ': ', $item['file']['filesize'], '</li>';

	echo '
								</ul>';
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
 */
function sp_calendar($parameters, $id, $return_parameters = false)
{
	global $modSettings, $options, $scripturl, $txt;

	$block_parameters = array(
		'events' => 'check',
		'birthdays' => 'check',
		'holidays' => 'check',
	);

	if ($return_parameters)
		return $block_parameters;

	require_once(SUBSDIR . '/Calendar.subs.php');
	$today = getTodayInfo();

	$curPage = array(
		'day' => $today['day'],
		'month' => $today['month'],
		'year' => $today['year']
	);

	$calendarOptions = array(
		'start_day' => !empty($options['calendar_start_day']) ? $options['calendar_start_day'] : 0,
		'show_week_num' => false,
		'show_events' => !empty($parameters['events']),
		'show_birthdays' => !empty($parameters['birthdays']),
		'show_holidays' => !empty($parameters['holidays']),
	);
	$calendar_data = getCalendarGrid($curPage['month'], $curPage['year'], $calendarOptions);

	// Output the calendar block
	echo '
								<table class="sp_acalendar smalltext">
									<tr>
										<td class="centertext" colspan="7">
											', !empty($modSettings['cal_enabled']) ? '<a href="' . $scripturl . '?action=calendar;year=' . $calendar_data['current_year'] . ';month=' . $calendar_data['current_month'] . '">' . $txt['months_titles'][$calendar_data['current_month']] . ' ' . $calendar_data['current_year'] . '</a>' : $txt['months_titles'][$calendar_data['current_month']] . ' ' . $calendar_data['current_year'], '
										</td>
									</tr><tr>';

	foreach ($calendar_data['week_days'] as $day)
		echo '
										<td class="centertext">', $txt['days_short'][$day], '</td>';

	echo '
									</tr>';

	foreach ($calendar_data['weeks'] as $week_key => $week)
	{
		echo '<tr>';

		foreach ($week['days'] as $day_key => $day)
		{
			echo '
										<td class="sp_acalendar_day">';

			if (empty($day['day']))
				unset($calendar_data['weeks'][$week_key]['days'][$day_key]);
			else
			{
				if (!empty($day['holidays']) || !empty($day['birthdays']) || !empty($day['events']))
					echo '<a href="#day" onclick="return sp_collapseCalendar(\'', $day['day'], '\');"><strong>', $day['is_today'] ? '[' : '', $day['day'], $day['is_today'] ? ']' : '', '</strong></a>';
				else
					echo '<a href="#day" onclick="return sp_collapseCalendar(\'0\');">', $day['is_today'] ? '[' : '', $day['day'], $day['is_today'] ? ']' : '', '</a>';
			}

			echo '</td>';
		}

		echo '
									</tr>';
	}

	echo '
								</table>
								<hr class="sp_acalendar_divider" />';

	foreach ($calendar_data['weeks'] as $week)
	{
		foreach ($week['days'] as $day)
		{
			if (empty($day['holidays']) && empty($day['birthdays']) && empty($day['events']) && !$day['is_today'])
				continue;
			elseif (empty($day['holidays']) && empty($day['birthdays']) && empty($day['events']))
			{
				echo '
								<div class="centertext smalltext" id="sp_calendar_', $day['day'], '">', $txt['error_sp_no_items_day'], '</div>';

				continue;
			}

			echo '
								<ul class="sp_list smalltext" id="sp_calendar_', $day['day'], '" ', !$day['is_today'] ? ' style="display: none;"' : '', '>';

			if (!empty($day['holidays']))
			{
				echo '
									<li class="centertext"><strong>- ', $txt['sp_calendar_holidays'], ' -</strong></li>';

				foreach ($day['holidays'] as $key => $holiday)
					echo '
									<li ', sp_embed_class('holiday', '', 'sp_list_indent'), '>', $holiday, '</li>';
			}

			if (!empty($day['birthdays']))
			{
				echo '
									<li class="centertext"><strong>- ', $txt['sp_calendar_birthdays'], ' -</strong></li>';

				foreach ($day['birthdays'] as $member)
					echo '
									<li ', sp_embed_class('birthday', '', 'sp_list_indent'), '>
										<a href="', $scripturl, '?action=profile;u=', $member['id'], '">', $member['name'], isset($member['age']) ? ' (' . $member['age'] . ')' : '', '</a>
									</li>';
			}

			if (!empty($day['events']))
			{
				echo '
									<li class="centertext"><strong>- ', $txt['sp_calendar_events'], ' -</strong></li>';

				foreach ($day['events'] as $event)
					echo '
									<li ', sp_embed_class('event', '', 'sp_list_indent'), '>', $event['link'], '</li>';
			}

			echo '
								</ul>';
		}
	}

	echo '
								<div class="centertext smalltext" id="sp_calendar_0" style="display: none;">', $txt['error_sp_no_items_day'], '</div>';

	addInlineJavascript('var current_day = "sp_calendar_' . $curPage['day'] . '";', true);
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
 */
function sp_calendarInformation($parameters, $id, $return_parameters = false)
{
	global $scripturl, $txt;

	$block_parameters = array(
		'events' => 'check',
		'future' => 'int',
		'birthdays' => 'check',
		'holidays' => 'check',
	);

	if ($return_parameters)
		return $block_parameters;

	$show_event = !empty($parameters['events']);
	$event_future = !empty($parameters['future']) ? (int) $parameters['future'] : 0;
	$event_future = abs($event_future);
	$show_birthday = !empty($parameters['birthdays']);
	$show_holiday = !empty($parameters['holidays']);
	$show_titles = false;

	// If they are not showing anything, not much to do !
	if (!$show_event && !$show_birthday && !$show_holiday)
	{
		echo '
								', $txt['sp_calendar_noEventsFound'];
		return;
	}

	$now = forum_time();
	$today_date = date("Y-m-d", $now);
	$calendar_array = array(
		'todayEvents' => array(),
		'futureEvents' => array(),
		'todayBirthdays' => array(),
		'todayHolidays' => array()
	);

	// Load calendar events
	if ($show_event)
	{
		// Just todays events or looking forward a few days?
		if (!empty($event_future))
			$event_future_date = date("Y-m-d", ($now + $event_future * 86400));
		else
			$event_future_date = $today_date;

		// Load them in
		$events = sp_loadCalendarData('getEvents', $today_date, $event_future_date);
		ksort($events);

		$displayed = array();
		foreach ($events as $day => $day_events)
		{
			foreach ($day_events as $event_key => $event)
			{
				if (in_array($event['id'], $displayed))
					unset($events[$day][$event_key]);
				else
					$displayed[] = $event['id'];
			}
		}

		if (!empty($events[$today_date]))
		{
			$calendar_array['todayEvents'] = $events[$today_date];
			unset($events[$today_date]);
		}

		if (!empty($events))
		{
			ksort($events);
			$calendar_array['futureEvents'] = $events;
		}
	}

	// Load in todays birthdays
	if ($show_birthday)
	{
		$calendar_array['todayBirthdays'] = current(sp_loadCalendarData('getBirthdays', $today_date));
		$show_titles = !empty($show_event) || !empty($show_holiday);
	}

	// Load in any holidays
	if ($show_holiday)
	{
		$calendar_array['todayHolidays'] = current(sp_loadCalendarData('getHolidays', $today_date));
		$show_titles = !empty($show_event) || !empty($show_birthday);
	}

	// Done collecting information, show what we found
	if (empty($calendar_array['todayEvents']) && empty($calendar_array['futureEvents']) && empty($calendar_array['todayBirthdays']) && empty($calendar_array['todayHolidays']))
	{
		echo '
								', $txt['sp_calendar_noEventsFound'];
		return;
	}
	else
	{
		echo '
								<ul class="sp_list">';

		if (!empty($calendar_array['todayHolidays']))
		{
			if ($show_titles)
				echo '
									<li><strong>', $txt['sp_calendar_holidays'], '</strong></li>';

			foreach ($calendar_array['todayHolidays'] as $key => $holiday)
				echo '
									<li ', sp_embed_class('holiday'), '>', $holiday, '</li>';
		}

		if (!empty($calendar_array['todayBirthdays']))
		{
			if ($show_titles)
				echo '
									<li><strong>', $txt['sp_calendar_birthdays'], '</strong></li>';

			foreach ($calendar_array['todayBirthdays'] as $member)
				echo '
									<li ', sp_embed_class('birthday'), '><a href="', $scripturl, '?action=profile;u=', $member['id'], '">', $member['name'], isset($member['age']) ? ' (' . $member['age'] . ')' : '', '</a></li>';
		}

		if (!empty($calendar_array['todayEvents']))
		{
			if ($show_titles)
				echo '
									<li><strong>', $txt['sp_calendar_events'], '</strong></li>';

			foreach ($calendar_array['todayEvents'] as $event)
				echo '
									<li ', sp_embed_class('event'), '>', $event['link'], !$show_titles ? ' - ' . standardTime(forum_time(), '%d %b') : '', '</li>';
		}

		if (!empty($calendar_array['futureEvents']))
		{
			if ($show_titles)
				echo '
									<li><strong>', $txt['sp_calendar_upcomingEvents'], '</strong></li>';

			foreach ($calendar_array['futureEvents'] as $startdate => $events)
			{
				foreach ($events as $event)
					echo '
									<li ', sp_embed_class('event'), '>', $event['link'], ' - ', timeformat(strtotime($startdate), '%d %b'), '</li>';
			}
		}

		echo '
								</ul>';
	}
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
 */
function sp_rssFeed($parameters, $id, $return_parameters = false)
{
	global $txt;

	$block_parameters = array(
		'url' => 'text',
		'show_title' => 'check',
		'show_content' => 'check',
		'show_date' => 'check',
		'strip_preserve' => 'text',
		'count' => 'int',
		'limit' => 'int',
	);

	if ($return_parameters)
		return $block_parameters;

	$feed = !empty($parameters['url']) ? un_htmlspecialchars($parameters['url']) : '';
	$show_title = !empty($parameters['show_title']);
	$show_content = !empty($parameters['show_content']);
	$show_date = !empty($parameters['show_date']);
	$strip_preserve = !empty($parameters['strip_preserve']) ? $parameters['strip_preserve'] : 'br';
	$strip_preserve = preg_match_all('~[A-Za-z0-9]+~', $strip_preserve, $match) ? $match[0] : array();
	$count = !empty($parameters['count']) ? (int) $parameters['count'] : 5;
	$limit = !empty($parameters['limit']) ? (int) $parameters['limit'] : 0;

	// Need a feed name to load it
	if (empty($feed))
	{
		echo '
								', $txt['error_sp_invalid_feed'];
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
			$data = @iconv($charset[1], 'UTF-8' . '//TRANSLIT//IGNORE', $data);

		// No iconv or a false response from it
		if (!function_exists('iconv') || ($data == false))
		{
			// PHP (some 5.4 versions) mishandles //TRANSLIT//IGNORE and returns false: see https://bugs.php.net/bug.php?id=61484
			if ($data == false)
				$data = $data_save;

			if (function_exists('mb_convert_encoding'))
			{
				// Replace unknown characters with a space
				@ini_set('mbstring.substitute_character', '32');
				$data = @mb_convert_encoding($data, 'UTF-8', $charset[1]);
			}
			elseif (function_exists('recode_string'))
				$data = @recode_string($charset[1] . '..' . 'UTF-8', $data);
		}
	}

	$data = str_replace(array("\n", "\r", "\t"), '', $data);
	$data = preg_replace('~<\!\[CDATA\[(.+?)\]\]>~eu', '\'#cdata_escape_encode#\' . Util::htmlspecialchars(\'$1\')', $data);

	// Find all the feed items
	preg_match_all('~<item>(.+?)</item>~', $data, $items);
	foreach ($items[1] as $item_id => $item)
	{
		if ($item_id === $count)
			break;

		preg_match_all('~<([A-Za-z]+)>(.+?)</\\1>~', $item, $match);

		foreach ($match[0] as $tag_id => $dummy)
		{
			if (Util::strpos($match[2][$tag_id], '#cdata_escape_encode#') === 0)
				$match[2][$tag_id] = stripslashes(un_htmlspecialchars(Util::substr($match[2][$tag_id], 21)));

			$rss[$item_id][strtolower($match[1][$tag_id])] = un_htmlspecialchars($match[2][$tag_id]);
		}
	}

	// Nothing, say its invalid
	if (empty($rss))
	{
		echo '
								', $txt['error_sp_invalid_feed'];
		return;
	}

	// Add all the items to an array
	$items = array();
	foreach ($rss as $item)
	{
		$item['title'] = isset($item['title']) ? strip_tags($item['title']) : '';
		$item['description'] = isset($item['description']) ? strip_tags($item['description'], empty($strip_preserve) ? '' : '<' . implode('><', $strip_preserve) . '>') : '';

		$items[] = array(
			'title' => $item['title'],
			'href' => $item['link'],
			'link' => $item['title'] == '' ? '' : ($item['link'] == '' ? $item['title'] : '<a href="' . $item['link'] . '" target="_blank" class="new_win">' . $item['title'] . '</a>'),
			'content' => $limit > 0 ? Util::shorten_text($item['description'], $limit, true) : $item['description'],
			'date' => !empty($item['pubdate']) ? standardTime(strtotime($item['pubdate']), '%d %B') : '',
		);
	}

	// No items in the feed
	if (empty($items))
	{
		echo '
								', $txt['error_sp_invalid_feed'];
		return;
	}
	else
		$items[count($items) - 1]['is_last'] = true;

	if ($show_content)
	{
		echo '
								<div class="sp_rss_flow">
									<ul class="sp_list">';

		foreach ($items as $item)
		{
			if ($show_title && !empty($item['link']))
				echo '
										<li ', sp_embed_class('post', '', 'sp_list_top'), '><strong>', $item['link'], '</strong>', ($show_date && !empty($item['date']) ? ' - ' . $item['date'] : ''), '</li>';
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

		foreach ($items as $item)
			echo '
									<li ', sp_embed_class('dot_feed'), '> ', $item['link'], ($show_date && !empty($item['date']) ? ' - ' . $item['date'] : ''), '</li>';

		echo '
								</ul>';
	}
}

/**
 * Theme Selection Block, Displays themes available for user selection
 *
 * @param mixed[] $parameters not used in this block
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
function sp_theme_select($parameters, $id, $return_parameters = false)
{
	global $modSettings, $user_info, $settings, $language, $txt;

	$block_parameters = array();

	if ($return_parameters)
		return $block_parameters;

	loadLanguage('Profile');
	loadLanguage('ManageThemes');
	require_once(SUBSDIR . '/Themes.subs.php');

	if (!empty($_SESSION['id_theme']) && (!empty($modSettings['theme_allow']) || allowedTo('admin_forum')))
		$current_theme = (int) $_SESSION['id_theme'];
	else
		$current_theme = $user_info['theme'];

	// Load in all the themes in the system
	$current_theme = empty($current_theme) ? -1 : $current_theme;
	$available_themes = installedThemes();

	// Set the guest theme
	if (!isset($available_themes[$modSettings['theme_guests']]))
	{
		$available_themes[0] = array(
			'num_users' => 0
		);
		$guest_theme = 0;
	}
	else
		$guest_theme = $modSettings['theme_guests'];

	$current_images_url = $settings['images_url'];

	foreach ($available_themes as $id_theme => $theme_data)
	{
		if ($id_theme == 0)
			continue;

		$settings['images_url'] = &$theme_data['images_url'];

		// Set the description in thier language if available
		if (file_exists($theme_data['theme_dir'] . '/languages/Settings.' . $user_info['language'] . '.php'))
			include($theme_data['theme_dir'] . '/languages/Settings.' . $user_info['language'] . '.php');
		elseif (file_exists($theme_data['theme_dir'] . '/languages/Settings.' . $language . '.php'))
			include($theme_data['theme_dir'] . '/languages/Settings.' . $language . '.php');
		else
		{
			$txt['theme_thumbnail_href'] = $theme_data['images_url'] . '/thumbnail.png';
			$txt['theme_description'] = '';
		}

		$available_themes[$id_theme]['thumbnail_href'] = $txt['theme_thumbnail_href'];
		$available_themes[$id_theme]['description'] = $txt['theme_description'];

		// Set the name, keep it short so it does not break our list
		$available_themes[$id_theme]['name'] = preg_replace('~\stheme$~i', '', $theme_data['name']);
		if (Util::strlen($available_themes[$id_theme]['name']) > 18)
			$available_themes[$id_theme]['name'] = Util::substr($available_themes[$id_theme]['name'], 0, 18) . '&hellip;';
	}

	$settings['images_url'] = $current_images_url;

	if ($guest_theme != 0)
		$available_themes[-1] = $available_themes[$guest_theme];

	$available_themes[-1]['id'] = -1;
	$available_themes[-1]['name'] = $txt['theme_forum_default'];
	$available_themes[-1]['selected'] = $current_theme == 0;
	$available_themes[-1]['description'] = $txt['theme_global_description'];

	ksort($available_themes);

	// Validate the selected theme id.
	if (!array_key_exists($current_theme, $available_themes))
	{
		$current_theme = -1;
		$available_themes[-1]['selected'] = true;
	}

	if (!empty($_POST['sp_ts_submit']) && !empty($_POST['sp_ts_permanent']) && !empty($_POST['theme']) && isset($available_themes[$_POST['theme']]) && (!empty($modSettings['theme_allow']) || allowedTo('admin_forum')))
		updateMemberData($user_info['id'], array('id_theme' => $_POST['theme'] == -1 ? 0 : (int) $_POST['theme']));

	echo '
								<form method="post" action="?" accept-charset="UTF-8">
									<div class="centertext">
										<select name="theme" onchange="sp_theme_select(this)">';

	foreach ($available_themes as $theme)
		echo '
											<option value="', $theme['id'], '"', $theme['id'] == $current_theme ? ' selected="selected"' : '', '>', $theme['name'], '</option>';

	echo '
										</select>
										<br /><br />
										<img src="', $available_themes[$current_theme]['thumbnail_href'], '" alt="', $available_themes[$current_theme]['name'], '" id="sp_ts_thumb" />
										<br /><br />
										<input type="checkbox" class="input_check" name="sp_ts_permanent" value="1" /> ', $txt['sp-theme_permanent'], '
										<br />
										<input type="submit" name="sp_ts_submit" value="', $txt['sp-theme_change'], '" class="button_submit" />
									</div>
								</form>';

	$javascript = '
		var sp_ts_thumbs = [];';

	foreach ($available_themes as $id => $theme_data)
		$javascript .= '
		sp_ts_thumbs[' . $id . '] = "' . $theme_data['thumbnail_href'] . '";';

	addInlineJavascript($javascript, true);
}

/**
 * Staff Block, show the list of forum staff members
 *
 * @param mixed[] $parameters
 *		'lmod' => set to include local moderators as well
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
function sp_staff($parameters, $id, $return_parameters = false)
{
	global $scripturl, $modSettings, $color_profile;

	$db = database();

	$block_parameters = array(
		'lmod' => 'check',
	);

	if ($return_parameters)
		return $block_parameters;

	require_once(SUBSDIR . '/Members.subs.php');

	// Including local board moderators
	if (empty($parameters['lmod']))
	{
		$request = $db->query('', '
			SELECT id_member
			FROM {db_prefix}moderators',
			array(
			)
		);
		$local_mods = array();
		while ($row = $db->fetch_assoc($request))
			$local_mods[$row['id_member']] = $row['id_member'];
		$db->free_result($request);

		if (count($local_mods) > 10)
			$local_mods = array();
	}
	else
		$local_mods = array();

	$global_mods = membersAllowedTo('moderate_board', 0);
	$admins = membersAllowedTo('admin_forum');

	$all_staff = array_merge($local_mods, $global_mods, $admins);
	$all_staff = array_unique($all_staff);

	$request = $db->query('', '
		SELECT
			m.id_member, m.real_name, m.avatar, m.email_address,
			mg.group_name,
			a.id_attach, a.attachment_type, a.filename
		FROM {db_prefix}members AS m
			LEFT JOIN {db_prefix}attachments AS a ON (a.id_member = m.id_member)
			LEFT JOIN {db_prefix}membergroups AS mg ON (mg.id_group = CASE WHEN m.id_group = {int:reg_group_id} THEN m.id_post_group ELSE m.id_group END)
		WHERE m.id_member IN ({array_int:staff_list})',
		array(
			'staff_list' => $all_staff,
			'reg_group_id' => 0,
		)
	);
	$staff_list = array();
	$colorids = array();
	while ($row = $db->fetch_assoc($request))
	{
		$colorids[$row['id_member']] = $row['id_member'];

		if (in_array($row['id_member'], $admins))
			$row['type'] = 1;
		elseif (in_array($row['id_member'], $global_mods))
			$row['type'] = 2;
		else
			$row['type'] = 3;

		$staff_list[$row['type'] . '-' . $row['id_member']] = array(
			'id' => $row['id_member'],
			'name' => $row['real_name'],
			'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>',
			'group' => $row['group_name'],
			'type' => $row['type'],
			'avatar' => determineAvatar(array(
				'avatar' => $row['avatar'],
				'filename' => $row['filename'],
				'id_attach' => $row['id_attach'],
				'email_address' => $row['email_address'],
				'attachment_type' => $row['attachment_type'],
			)),
		);
	}
	$db->free_result($request);

	ksort($staff_list);
	$staff_count = count($staff_list);
	$count = 0;
	$icons = array(1 => 'admin', 'gmod', 'lmod');

	if (!empty($colorids) && sp_loadColors($colorids) !== false)
	{
		foreach ($staff_list as $k => $p)
		{
			if (!empty($color_profile[$p['id']]['link']))
				$staff_list[$k]['link'] = $color_profile[$p['id']]['link'];
		}
	}

	echo '
								<table class="sp_fullwidth">';

	foreach ($staff_list as $staff)
		echo '
									<tr>
										<td class="sp_staff centertext">', !empty($staff['avatar']['href']) ? '
											<a href="' . $scripturl . '?action=profile;u=' . $staff['id'] . '">
												<img src="' . $staff['avatar']['href'] . '" alt="' . $staff['name'] . '" style="max-width:40px" />
											</a>' : '', '
										</td>
										<td ', sp_embed_class($icons[$staff['type']], '', 'sp_staff_info'. $staff_count != ++$count ? ' sp_staff_divider' : ''), '>',
											$staff['link'], '<br />', $staff['group'], '
										</td>
									</tr>';

	echo '
								</table>';
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
 */
function sp_articles($parameters, $id, $return_parameters = false)
{
	global $scripturl, $txt, $color_profile;

	$block_parameters = array(
		'category' => array(0 => $txt['sp_all']),
		'limit' => 'int',
		'type' => 'select',
		'length' => 'int',
		'avatar' => 'check',
	);

	if ($return_parameters)
	{
		require_once(SUBSDIR . '/PortalAdmin.subs.php');

		$categories = sp_load_categories();
		foreach ($categories as $category)
			$block_parameters['category'][$category['id']] = $category['name'];

		return $block_parameters;
	}

	require_once(SUBSDIR . '/Post.subs.php');

	// Set up for the query
	$category = empty($parameters['category']) ? 0 : (int) $parameters['category'];
	$limit = empty($parameters['limit']) ? 5 : (int) $parameters['limit'];
	$type = empty($parameters['type']) ? 0 : 1;
	$length = isset($parameters['length']) ? (int) $parameters['length'] : 250;
	$avatar = empty($parameters['avatar']) ? 0 : (int) $parameters['avatar'];

	$articles = sportal_get_articles(null, true, true, $type ? 'RAND()' : 'spa.date DESC', $category, $limit);

	$colorids = array();
	foreach ($articles as $article)
	{
		if (!empty($article['author']['id']))
			$colorids[$article['author']['id']] = $article['author']['id'];
	}

	// No articles in the system or none they can see
	if (empty($articles))
	{
		echo '
								', $txt['error_sp_no_articles_found'];
		return;
	}

	// Doing the color thing
	if (!empty($colorids) && sp_loadColors($colorids) !== false)
	{
		foreach ($articles as $k => $p)
		{
			if (!empty($color_profile[$p['author']['id']]['link']))
				$articles[$k]['author']['link'] = $color_profile[$p['author']['id']]['link'];
		}
	}

	// Not showing avatars, just use a compact link view
	if (empty($avatar))
	{
		echo '
			<ul class="sp_list">';

		foreach ($articles as $article)
			echo '
				<li>', sp_embed_image('topic'), ' ', $article['link'], '</li>';

		echo '
			</ul>';
	}
	// Or the full monty!
	else
	{
		echo '
							<table class="sp_fullwidth">';

		foreach ($articles as $article)
		{
			// Shorten it for the preview
			$article['body'] = sportal_parse_content($article['body'], $article['type'], 'return');
			$article['body'] = Util::shorten_html($article['body'], $length);

			echo '
								<tr class="sp_articles_row">
									<td class="sp_articles centertext">';

			// If we have an avatar to show, show it
			if ($avatar && !empty($article['author']['avatar']['href']))
				echo '
										<a href="', $scripturl, '?action=profile;u=', $article['author']['id'], '">
											<img src="', $article['author']['avatar']['href'], '" alt="', $article['author']['name'], '" style="max-width:40px" />
										</a>';

			echo '
									</td>
									<td>
										<span class="sp_articles_title">', $article['author']['link'], '</span><br />
										', $article['link'], '
									</td>
									<td>',
										$article['body'],
									'</td>
								</tr>
								<tr>
									<td colspan="3" class="sp_articles_row"></td>
								</tr>';
		}

		echo '
							</table>';
	}
}

/**
 * Shoutbox Block, show the shoutbox thoughts box
 *
 * @param mixed[] $parameters
 *		'shoutbox' => list of shoutboxes to choose from
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
function sp_shoutbox($parameters, $id, $return_parameters = false)
{
	global $context, $modSettings, $user_info, $settings, $txt, $scripturl, $editortxt;

	$db = database();

	$block_parameters = array(
		'shoutbox' => array(),
	);

	// return the paramters for use in setting up the blocks
	if ($return_parameters)
	{
		$shoutboxes = sportal_get_shoutbox();
		$in_use = array();

		$request = $db->query('', '
			SELECT id_block, value
			FROM {db_prefix}sp_parameters
			WHERE variable = {string:name}',
			array(
				'name' => 'shoutbox',
			)
		);
		while ($row = $db->fetch_assoc($request))
			if (empty($_REQUEST['block_id']) || $_REQUEST['block_id'] != $row['id_block'])
				$in_use[] = $row['value'];
		$db->free_result($request);

		// Load up all the shoutboxes that are NOT being used
		foreach ($shoutboxes as $shoutbox)
			if (!in_array($shoutbox['id'], $in_use))
				$block_parameters['shoutbox'][$shoutbox['id']] = $shoutbox['name'];

		if (empty($block_parameters['shoutbox']))
			fatal_error(allowedTo(array('sp_admin', 'sp_manage_shoutbox')) ? $txt['error_sp_no_shoutbox'] . '<br />' . sprintf($txt['error_sp_no_shoutbox_sp_moderator'], $scripturl . '?action=admin;area=portalshoutbox;sa=add') : $txt['error_sp_no_shoutbox_normaluser'], false);

		return $block_parameters;
	}

	loadTemplate('PortalShoutbox');
	loadLanguage('Editor');
	loadLanguage('Post');

	$shoutbox = sportal_get_shoutbox($parameters['shoutbox'], true, true);

	// To add a shoutbox you must have one or more defined for use
	if (empty($shoutbox))
	{
		echo '
								', $txt['error_sp_shoutbox_not_exist'];
		return;
	}

	// Going to add to the shoutbox \
	if (!empty($_POST['new_shout']) && !empty($_POST['submit_shout']) && !empty($_POST['shoutbox_id']) && $_POST['shoutbox_id'] == $shoutbox['id'])
	{
		// Make sure things are in order
		checkSession();
		is_not_guest();

		if (!($flood = sp_prevent_flood('spsbp', false)))
		{
			require_once(SUBSDIR . '/Post.subs.php');

			$_POST['new_shout'] = Util::htmlspecialchars(trim($_POST['new_shout']));
			preparsecode($_POST['new_shout']);

			if (!empty($_POST['new_shout']))
				sportal_create_shout($shoutbox, $_POST['new_shout']);
		}
		else
			$shoutbox['warning'] = $flood;
	}

	// Can they moderate the shoutbox (delete shouts?)
	$can_moderate = allowedTo('sp_admin') || allowedTo('sp_manage_shoutbox');
	if (!$can_moderate && !empty($shoutbox['moderator_groups']))
		$can_moderate = count(array_intersect($user_info['groups'], $shoutbox['moderator_groups'])) > 0;

	$shout_parameters = array(
		'limit' => $shoutbox['num_show'],
		'bbc' => $shoutbox['allowed_bbc'],
		'reverse' => $shoutbox['reverse'],
		'cache' => $shoutbox['caching'],
		'can_moderate' => $can_moderate,
	);
	$shoutbox['shouts'] = sportal_get_shouts($shoutbox['id'], $shout_parameters);

	$shoutbox['warning'] = parse_bbc($shoutbox['warning']);
	$context['can_shout'] = $context['user']['is_logged'];

	if ($context['can_shout'])
	{
		// Set up the smiley tags for the shoutbox
		$settings['smileys_url'] = $modSettings['smileys_url'] . '/' . $user_info['smiley_set'];
		$shoutbox['smileys'] = array('normal' => array(), 'popup' => array());
		if (empty($modSettings['smiley_enable']))
			$shoutbox['smileys']['normal'] = array(
				array('code' => ':)', 'filename' => 'smiley.gif', 'description' => $txt['icon_smiley']),
				array('code' => ';)', 'filename' => 'wink.gif', 'description' => $txt['icon_wink']),
				array('code' => ':D', 'filename' => 'cheesy.gif', 'description' => $txt['icon_cheesy']),
				array('code' => ';D', 'filename' => 'grin.gif', 'description' => $txt['icon_grin']),
				array('code' => '>:(', 'filename' => 'angry.gif', 'description' => $txt['icon_angry']),
				array('code' => ':(', 'filename' => 'sad.gif', 'description' => $txt['icon_sad']),
				array('code' => ':o', 'filename' => 'shocked.gif', 'description' => $txt['icon_shocked']),
				array('code' => '8)', 'filename' => 'cool.gif', 'description' => $txt['icon_cool']),
				array('code' => '???', 'filename' => 'huh.gif', 'description' => $txt['icon_huh']),
				array('code' => '::)', 'filename' => 'rolleyes.gif', 'description' => $txt['icon_rolleyes']),
				array('code' => ':P', 'filename' => 'tongue.gif', 'description' => $txt['icon_tongue']),
				array('code' => ':-[', 'filename' => 'embarrassed.gif', 'description' => $txt['icon_embarrassed']),
				array('code' => ':-X', 'filename' => 'lipsrsealed.gif', 'description' => $txt['icon_lips']),
				array('code' => ':-\\', 'filename' => 'undecided.gif', 'description' => $txt['icon_undecided']),
				array('code' => ':-*', 'filename' => 'kiss.gif', 'description' => $txt['icon_kiss']),
				array('code' => ':\'(', 'filename' => 'cry.gif', 'description' => $txt['icon_cry'])
			);
		else
		{
			if (($temp = cache_get_data('shoutbox_smileys', 3600)) == null)
			{
				$request = $db->query('', '
					SELECT code, filename, description, smiley_row, hidden
					FROM {db_prefix}smileys
					WHERE hidden IN ({array_int:hidden})
					ORDER BY smiley_row, smiley_order',
					array(
						'hidden' => array(0, 2),
					)
				);
				while ($row = $db->fetch_assoc($request))
				{
					$row['filename'] = htmlspecialchars($row['filename']);
					$row['description'] = htmlspecialchars($row['description']);
					$row['code'] = htmlspecialchars($row['code']);
					$shoutbox['smileys'][empty($row['hidden']) ? 'normal' : 'popup'][] = $row;
				}
				$db->free_result($request);

				cache_put_data('shoutbox_smileys', $shoutbox['smileys'], 3600);
			}
			else
				$shoutbox['smileys'] = $temp;
		}

		foreach (array_keys($shoutbox['smileys']) as $location)
		{
			$n = count($shoutbox['smileys'][$location]);
			for ($i = 0; $i < $n; $i++)
			{
				$shoutbox['smileys'][$location][$i]['code'] = addslashes($shoutbox['smileys'][$location][$i]['code']);
				$shoutbox['smileys'][$location][$i]['js_description'] = addslashes($shoutbox['smileys'][$location][$i]['description']);
			}

			if (!empty($shoutbox['smileys'][$location]))
				$shoutbox['smileys'][$location][$n - 1]['last'] = true;
		}

		// Basic shoutbox bbc we allow
		$shoutbox['bbc'] = array(
			'bold' => array('code' => 'b', 'before' => '[b]', 'after' => '[/b]', 'description' => $editortxt['Bold']),
			'italicize' => array('code' => 'i', 'before' => '[i]', 'after' => '[/i]', 'description' => $editortxt['Italic']),
			'underline' => array('code' => 'u', 'before' => '[u]', 'after' => '[/u]', 'description' => $editortxt['Underline']),
			'strike' => array('code' => 's', 'before' => '[s]', 'after' => '[/s]', 'description' => $editortxt['Strikethrough']),
			'pre' => array('code' => 'pre', 'before' => '[pre]', 'after' => '[/pre]', 'description' => $editortxt['Preformatted Text']),
			'img' => array('code' => 'img', 'before' => '[img]', 'after' => '[/img]', 'description' => $editortxt['Insert an image']),
			'url' => array('code' => 'url', 'before' => '[url]', 'after' => '[/url]', 'description' => $editortxt['Insert a link']),
			'email' => array('code' => 'email', 'before' => '[email]', 'after' => '[/email]', 'description' => $editortxt['Insert an email']),
			'sup' => array('code' => 'sup', 'before' => '[sup]', 'after' => '[/sup]', 'description' => $editortxt['Superscript']),
			'sub' => array('code' => 'sub', 'before' => '[sub]', 'after' => '[/sub]', 'description' => $editortxt['Subscript']),
			'tele' => array('code' => 'tt', 'before' => '[tt]', 'after' => '[/tt]', 'description' => $editortxt['Teletype']),
			'code' => array('code' => 'code', 'before' => '[code]', 'after' => '[/code]', 'description' => $editortxt['Code']),
			'quote' => array('code' => 'quote', 'before' => '[quote]', 'after' => '[/quote]', 'description' => $editortxt['Insert a Quote']),
		);
	}

	template_shoutbox_embed($shoutbox);
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
 */
function sp_gallery($parameters, $id, $return_parameters = false)
{
	global $scripturl, $txt, $scripturl, $modSettings;
	static $mod;

	$block_parameters = array(
		'limit' => 'int',
		'type' => 'select',
		'direction' => 'select',
	);

	if ($return_parameters)
		return $block_parameters;

	$limit = empty($parameters['limit']) ? 1 : (int) $parameters['limit'];
	$type = empty($parameters['type']) ? 0 : 1;
	$direction = empty($parameters['direction']) ? 0 : 1;

	// right now we only know about one gallery, but more may be added, maybe even ones we can
	// tell folks about :P
	if (!isset($mod))
	{
		if (file_exists(SOURCEDIR . '/Aeva-Media.php'))
			$mod = 'aeva_media';
		else
			$mod = '';
	}

	if (empty($mod))
	{
		echo '
								', $txt['error_sp_no_gallery_found'];
		return;
	}
	elseif ($mod == 'aeva_media')
	{
		require_once(SUBSDIR . '/Aeva-Subs.php');

		$items = aeva_getMediaItems(0, $limit, $type ? 'RAND()' : 'm.id_media DESC');
	}

	// No items in the gallery?
	if (empty($items))
	{
		echo '
								', $txt['error_sp_no_pictures_found'];
		return;
	}

	// We have gallery items to show!
	echo '
								<table class="sp_auto_align">', $direction ? '
									<tr>' : '';

	foreach ($items as $item)
	{
		echo !$direction ? '
									<tr>' : '', '
										<td>
											<div class="sp_image smalltext">';

		if ($mod == 'aeva_media')
		{
			echo '
												<a href="', $scripturl, '?action=media;sa=item;in=', $item['id'], '">', $item['title'], '</a><br />' . (!empty($modSettings['fancybox_enabled']) ? '
												<a href="' . $scripturl . '?action=media;sa=media;in=' . $item['id'] . '" rel="gallery" class="fancybox">' : '
												<a href="' . $scripturl . '?action=media;sa=item;in=' . $item['id'] . '">') . '
												<img src="', $scripturl, '?action=media;sa=media;in=', $item['id'], ';thumb" alt="" /></a><br />
												', $txt['aeva_views'], ': ', $item['views'], '<br />
												', $txt['aeva_posted_by'], ': <a href="', $scripturl, '?action=profile;u=', $item['poster_id'], '">', $item['poster_name'], '</a><br />
												', $txt['aeva_in_album'], ': <a href="', $scripturl, '?action=media;sa=album;in=', $item['id_album'], '">', $item['album_name'], '</a>';
		}

		echo '
											</div>
										</td>', !$direction ? '
									</tr>' : '';
	}

	echo $direction ? '
									</tr>' : '', '
								</table>';
}

/**
 * Menu Block, creates a sidebar menu block based on the system main menu
 * @todo needs updating so it knows right vs left block for the flyout
 *
 * @param mixed[] $parameters -  not used in this block
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
function sp_menu($parameters, $id, $return_parameters = false)
{
	global $context;

	$block_parameters = array();

	if ($return_parameters)
		return $block_parameters;

	if (empty($context['menu_buttons']))
		setupMenuContext();

	echo '
								<ul id="sp_menu" class="sp_list">';

	foreach ($context['menu_buttons'] as $act => $button)
	{
		echo '
									<li ', sp_embed_class('dot'), '>
										<a title="', strip_tags($button['title']), '" href="', $button['href'], '">', ($button['active_button'] ? '<strong>' : ''), $button['title'], ($button['active_button'] ? '</strong>' : ''), '</a>';

		if (!empty($button['sub_buttons']))
		{
			echo '
										<ul class="sp_list">';

			foreach ($button['sub_buttons'] as $sub_button)
				echo '
											<li ', sp_embed_class('dot', '', 'sp_list_indent'), '>
												<a title="', $sub_button['title'], '" href="', $sub_button['href'], '">', $sub_button['title'], '</a></li>';

			echo '
										</ul>';
		}

		echo '</li>';
	}

	echo '
								</ul>';

	// Superfish the menu
	$javascript = "
	$(document).ready(function() {
		if (use_click_menu)
			$('#sp_menu').superclick({speed: 150, animation: {opacity:'show', height:'toggle'}, speedOut: 0, activeClass: 'sfhover'});
		else
			$('#sp_menu').superfish({delay : 300, speed: 175, hoverClass: 'sfhover'});
	});";

	addInlineJavascript($javascript, true);
}

/**
 * Generic BBC Block, creates a BBC formatted block with parse_bbc
 *
 * @param mixed[] $parameters -  not used in this block
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
function sp_bbc($parameters, $id, $return_parameters = false)
{
	$block_parameters = array(
		'content' => 'bbc',
	);

	if ($return_parameters)
		return $block_parameters;

	$content = !empty($parameters['content']) ? $parameters['content'] : '';

	echo '
								', parse_bbc($content);
}

/**
 * Generic HTML Block, creates a formatted block with HTML
 *
 * @param mixed[] $parameters -  not used in this block
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
function sp_html($parameters, $id, $return_parameters = false)
{
	$block_parameters = array(
		'content' => 'textarea',
	);

	if ($return_parameters)
		return $block_parameters;

	$content = !empty($parameters['content']) ? $parameters['content'] : '';

	echo '
								', un_htmlspecialchars($content);
}

/**
 * Generic PHP Block, creates a PHP block to do whatever you can imagine :D
 *
 * @param mixed[] $parameters
 *		'textarea' =>
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
function sp_php($parameters, $id, $return_parameters = false)
{
	$block_parameters = array(
		'content' => 'textarea',
	);

	if ($return_parameters)
		return $block_parameters;

	$content = !empty($parameters['content']) ? $parameters['content'] : '';

	$content = trim(un_htmlspecialchars($content));

	// Strip leading / trailing php wrapper
	if (substr($content, 0, 5) == '<?php')
		$content = substr($content, 5);
	if (substr($content, -2) == '?>')
		$content = substr($content, 0, -2);

	// Can be scary :0
	eval($content);
}
