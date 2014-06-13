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
 * User info block, shows avatar, group, icons, posts, karma, etc
 *
 * @param mixed[] $parameters not used in this block
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
function sp_userInfo($parameters, $id, $return_parameters = false)
{
	global $context, $txt, $scripturl, $memberContext, $modSettings, $user_info, $color_profile, $settings;

	$block_parameters = array();

	if ($return_parameters)
		return $block_parameters;

	echo '
								<div class="sp_center sp_fullwidth">';

	// Show the guests a login area
	if ($context['user']['is_guest'])
	{
		echo '
									<script src="' . $settings['default_theme_url'] . '/scripts/sha256.js"></script>
									<form action="', $scripturl, '?action=login2;quicklogin" method="post" accept-charset="UTF-8"', empty($context['disable_login_hashing']) ? ' onsubmit="hashLoginPassword(this, \'' . $context['session_id'] . '\');"' : '', ' >
									<table>
											<tr>
												<td class="sp_right">
													<label for="sp_user">', $txt['username'], ':</label>&nbsp;
												</td>
												<td>
													<input type="text" id="sp_user" name="user" size="9" value="', !empty($user_info['username']) ? $user_info['username'] : '', '" />
												</td>
											</tr>
											<tr>
												<td class="sp_right">
													<label for="sp_passwrd">', $txt['password'], ':</label>&nbsp;
												</td>
												<td>
													<input type="password" name="passwrd" id="sp_passwrd" size="9" />
												</td>
											</tr>
											<tr>
												<td>
													<select name="cookielength">
														<option value="60">', $txt['one_hour'], '</option>
														<option value="1440">', $txt['one_day'], '</option>
														<option value="10080">', $txt['one_week'], '</option>
														<option value="43200">', $txt['one_month'], '</option>
														<option value="-1" selected="selected">', $txt['forever'], '</option>
													</select>
												</td>
												<td>
													<input type="submit" value="', $txt['login'], '" class="button_submit" />
												</td>
											</tr>
										</table>
										<input type="hidden" name="hash_passwrd" value="" />
										<input type="hidden" name="old_hash_passwrd" value="" />
										<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
										<input type="hidden" name="', $context['login_token_var'], '" value="', $context['login_token'], '" />
									</form>', sprintf($txt[$context['can_register'] ? 'welcome_guest_register' : 'welcome_guest'], $txt['guest_title'], $scripturl . '?action=login');
	}
	// A logged in member then
	else
	{
		// load up the members details
		loadMemberData($user_info['id']);
		loadMemberContext($user_info['id'], true);

		$member_info = $memberContext[$user_info['id']];

		if (sp_loadColors($member_info['id']) !== false)
			$member_info['colored_name'] = $color_profile[$member_info['id']]['colored_name'];

		$member_info['karma']['total'] = $member_info['karma']['good'] - $member_info['karma']['bad'];

		echo '
									',  $txt['hello_member'], ' <strong>', !empty($member_info['colored_name']) ? $member_info['colored_name'] : $member_info['name'], '</strong>
									<br /><br />';

		if (!empty($member_info['avatar']['image']))
			echo '
									<a href="', $scripturl, '?action=profile;u=', $member_info['id'], '">', $member_info['avatar']['image'], '</a>
									<br /><br />';

		if (!empty($member_info['group']))
			echo '
									', $member_info['group'], '<br />';
		else
			echo '
									', $member_info['post_group'], '<br />';

		echo '
									', $member_info['group_icons'], '
									<br />
									<br />
									<ul class="sp_list">
										<li ', sp_embed_class('dot'), '><strong>', $txt['posts'], ':</strong> ', $member_info['posts'], '</li>';

		if (!empty($modSettings['karmaMode']))
		{
			echo '
										<li ', sp_embed_class('dot'), '><strong>', $modSettings['karmaLabel'], '</strong> ';

			if ($modSettings['karmaMode'] == 1)
				echo $member_info['karma']['total'];
			elseif ($modSettings['karmaMode'] == 2)
				echo '+', $member_info['karma']['good'], '/-', $member_info['karma']['bad'];

			echo '</li>';
		}

		if (allowedTo('pm_read'))
		{
			echo '
										<li ', sp_embed_class('dot'), '>
											<strong>', $txt['sp-usertmessage'], ':</strong> <a href="', $scripturl, '?action=pm">', $context['user']['messages'], '</a>
										</li>
										<li ', sp_embed_class('dot'), '>
											<strong>', $txt['sp-usernmessage'], ':</strong> ', $context['user']['unread_messages'], '
										</li>';
		}

		echo '
										<li ', sp_embed_class('dot'), '>
											<a href="', $scripturl, '?action=unread">', $txt['unread_topics_visit'], '</a>
										</li>
										<li ', sp_embed_class('dot'), '>
											<a href="', $scripturl, '?action=unreadreplies">', $txt['unread_replies'], '</a>
										</li>
									</ul>
									<br />
									<a class="dot arrow" href="', $scripturl, '?action=profile">', $txt['profile'], '</a>
									<a class="dot arrow" href="', $scripturl, '?action=logout;sesc=', $context['session_id'], '">', $txt['logout'], '</a>';
	}

	echo '
								</div>';
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
	global $scripturl, $txt, $color_profile;

	$block_parameters = array(
		'limit' => 'int',
	);

	if ($return_parameters)
		return $block_parameters;

	// Load in the latest members
	require_once(SUBSDIR . '/Members.subs.php');
	$limit = !empty($parameters['limit']) ? (int) $parameters['limit'] : 5;
	$rows = recentMembers($limit);

	// Get them ready for color ids and the template
	$members = array();
	$colorids = array();
	foreach ($rows as $row)
	{
		if (!empty($row['id_member']))
			$colorids[$row['id_member']] = $row['id_member'];

		$members[] = array(
			'id' => $row['id_member'],
			'name' => $row['real_name'],
			'href' => $scripturl . '?action=profile;u=' . $row['id_member'],
			'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>',
			'date' => standardTime($row['date_registered'], '%d %b'),
		);
	}

	// No recent members, supose it could happen
	if (empty($members))
	{
		echo '
								', $txt['error_sp_no_members_found'];

		return;
	}

	// Using member profile colors
	if (!empty($colorids) && sp_loadColors($colorids) !== false)
	{
		foreach ($members as $k => $p)
		{
			if (!empty($color_profile[$p['id']]['link']))
				$members[$k]['link'] = $color_profile[$p['id']]['link'];
		}
	}

	echo '
								<ul class="sp_list">';

	foreach ($members as $member)
		echo '
									<li ', sp_embed_class('dot'), '>', $member['link'], ' - ', $member['date'], '</li>';

	echo '
								</ul>';
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
	global $scripturl, $modSettings, $txt, $context;

	$block_parameters = array(
		'online_today' => 'check'
	);

	if ($return_parameters)
		return $block_parameters;

	// Interface with the online today addon?
	$online_today = !empty($parameters['online_today']);

	$stats = ssi_whosOnline('array');

	echo '
								<ul class="sp_list">
									<li ', sp_embed_class('dot'), '> ', $txt['guests'], ': ', $stats['num_guests'], '</li>';

	// Spiders
	if (!empty($modSettings['show_spider_online']) && ($modSettings['show_spider_online'] < 3 || allowedTo('admin_forum')))
		echo '
									<li ', sp_embed_class('dot'), '> ', $txt['spiders'], ': ', $stats['num_spiders'], '</li>';

	echo '
									<li ', sp_embed_class('dot'), '> ', $txt['hidden'], ': ', $stats['num_users_hidden'], '</li>
									<li ', sp_embed_class('dot'), '> ', $txt['users'], ': ', $stats['num_users_online'], '</li>';

	// Show the users online, if any
	if (!empty($stats['users_online']))
	{
		echo '
									<li ', sp_embed_class('dot'), '> ', allowedTo('who_view') && !empty($modSettings['who_enabled']) ? '<a href="' . $scripturl . '?action=who">' : '', $txt['online_users'], allowedTo('who_view') && !empty($modSettings['who_enabled']) ? '</a>' : '', ':</li>
								</ul>
								<div class="sp_online_flow">
									<ul class="sp_list">';

		foreach ($stats['users_online'] as $user)
			echo '
										<li ', sp_embed_class($user['name'] == 'H' ? 'tux' : 'user', '', 'sp_list_indent'), '>', $user['hidden'] ? '<em>' . $user['link'] . '</em>' : $user['link'], '</li>';

		echo '
									</ul>
								</div>';
	}
	else
	{
		echo '
								</ul>
								<br />
								<div class="sp_fullwidth sp_center">', $txt['error_sp_no_online'], '</div>';
	}

	// Does the online today addon exist
	if ($online_today && !empty($modSettings['onlinetoday']) && file_exists(SUBSDIR . '/OnlineToday.class.php'))
	{
		require_once(SUBSDIR . '/OnlineToday.class.php');
		$context['info_center_callbacks'] = array();
		OnlineToday::get();

		if (empty($context['num_onlinetoday']))
			return;

		echo '
								<ul class="sp_list">
									<li ', sp_embed_class('dot'), '> ', $txt['sp-online_today'], ': ', $context['num_onlinetoday'], '</li>
								</ul>
								<div class="sp_online_flow">
									<ul class="sp_list">';

		foreach ($context['onlinetoday'] as $user)
			echo '
										<li ', sp_embed_class('user', '', 'sp_list_indent'), '>', $user, '</li>';

		echo '
									</ul>
								</div>';
	}
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
	global $scripturl, $modSettings, $txt;

	$block_parameters = array(
		'averages' => 'check',
	);

	if ($return_parameters)
		return $block_parameters;

	$averages = !empty($parameters['averages']) ? 1 : 0;

	loadLanguage('Stats');

	// Basic totals are easy
	$totals = ssi_boardStats('array');

	// Get the averages from the activity log, its the most recent snapshot
	if ($averages)
	{
		require_once(SUBSDIR . '/Stats.subs.php');
		$averages = getAverages();

		// The number of days the forum has been up...
		$total_days_up = ceil((time() - strtotime($averages['date'])) / (60 * 60 * 24));

		$totals += array(
			'average_members' => comma_format(round($averages['registers'] / $total_days_up, 2)),
			'average_posts' => comma_format(round($averages['posts'] / $total_days_up, 2)),
			'average_topics' => comma_format(round($averages['topics'] / $total_days_up, 2)),
			'average_online' => comma_format(round($averages['most_on'] / $total_days_up, 2)),
		);
	}

	echo '
								<ul class="sp_list">
									<li ', sp_embed_class('portalstats'), '>', $txt['total_members'], ': <a href="', $scripturl . '?action=mlist">', comma_format($totals['members']), '</a></li>
									<li ', sp_embed_class('portalstats'), '>', $txt['total_posts'], ': ', comma_format($totals['posts']), '</li>
									<li ', sp_embed_class('portalstats'), '>', $txt['total_topics'], ': ', comma_format($totals['topics']), '</li>
									<li ', sp_embed_class('portalstats'), '>', $txt['total_cats'], ': ', comma_format($totals['categories']), '</li>
									<li ', sp_embed_class('portalstats'), '>', $txt['total_boards'], ': ', comma_format($totals['boards']), '</li>
									<li ', sp_embed_class('portalstats'), '>', $txt['most_online'], ': ', comma_format($modSettings['mostOnline']), '</li>
								</ul>';

	// And the averages if required
	if ($averages)
	{
		echo '
								<hr />
								<ul class="sp_list">
									<li ', sp_embed_class('portalaverages'), '>', $txt['sp-average_posts'], ': ', comma_format($totals['average_posts']), '</li>
									<li ', sp_embed_class('portalaverages'), '>', $txt['sp-average_topics'], ': ', comma_format($totals['average_topics']), '</li>
									<li ', sp_embed_class('portalaverages'), '>', $txt['sp-average_members'], ': ', comma_format($totals['average_members']), '</li>
									<li ', sp_embed_class('portalaverages'), '>', $txt['sp-average_online'], ': ', comma_format($totals['average_online']), '</li>
								</ul>';
	}
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
	global $scripturl, $modSettings, $txt, $color_profile;

	$db = database();

	$block_parameters = array(
		'limit' => 'int',
		'type' => 'select',
	);

	if ($return_parameters)
		return $block_parameters;

	$limit = !empty($parameters['limit']) ? (int) $parameters['limit'] : 5;
	$type = !empty($parameters['type']) ? (int) $parameters['type'] : 0;

	// If not top poster of all time we need to set a start time
	if (!empty($type))
	{
		// Today
		if ($type == 1)
		{
			list($year, $month, $day) = explode('-', date('Y-m-d'));
			$start_time = mktime(0, 0, 0, $month, $day, $year);
		}
		// This week
		elseif ($type == 2)
			$start_time = mktime(0, 0, 0, date("n"), date("j"), date("Y")) - (date("N") * 3600 * 24);
		// This month
		elseif ($type == 3)
		{
			$months = array(1 => 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
			$start_time = mktime(0, 0, 0, date("n"), date("j"), date("Y")) - (3600 * 24 * $months[(int) date("m", time())]);
		}

		$start_time = forum_time(false, $start_time);

		$request = $db->query('', '
			SELECT
				mem.id_member, mem.real_name, COUNT(*) as posts, mem.email_address,
				mem.avatar, a.id_attach, a.attachment_type, a.filename
			FROM {db_prefix}messages AS m
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = m.id_member)
				LEFT JOIN {db_prefix}attachments AS a ON (a.id_member = mem.id_member)
			WHERE m.poster_time > {int:start_time}
				AND m.id_member != 0
			GROUP BY mem.id_member
			ORDER BY posts DESC
			LIMIT {int:limit}',
			array(
				'start_time' => $start_time,
				'limit' => $limit,
			)
		);
	}
	// Or from the start of time
	else
	{
		$request = $db->query('', '
			SELECT
				m.id_member, m.real_name, m.posts, m.avatar, m.email_address,
				a.id_attach, a.attachment_type, a.filename
			FROM {db_prefix}members AS m
				LEFT JOIN {db_prefix}attachments AS a ON (a.id_member = m.id_member)
			ORDER BY posts DESC
			LIMIT {int:limit}',
			array(
				'limit' => $limit,
			)
		);
	}
	$members = array();
	$colorids = array();
	// Load the member data
	while ($row = $db->fetch_assoc($request))
	{
		if (!empty($row['id_member']))
			$colorids[$row['id_member']] = $row['id_member'];

		if ($modSettings['avatar_action_too_large'] == 'option_html_resize' || $modSettings['avatar_action_too_large'] == 'option_js_resize')
		{
			$avatar_width = !empty($modSettings['avatar_max_width_external']) ? ' width="' . $modSettings['avatar_max_width_external'] . '"' : '';
			$avatar_height = !empty($modSettings['avatar_max_height_external']) ? ' height="' . $modSettings['avatar_max_height_external'] . '"' : '';
		}
		else
		{
			$avatar_width = '';
			$avatar_height = '';
		}

		$members[] = array(
			'id' => $row['id_member'],
			'name' => $row['real_name'],
			'href' => $scripturl . '?action=profile;u=' . $row['id_member'],
			'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>',
			'posts' => comma_format($row['posts']),
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

	// No results, say so
	if (empty($members))
	{
		echo '
								', $txt['error_sp_no_members_found'];
		return;
	}

	// Profile colors?
	if (!empty($colorids) && sp_loadColors($colorids) !== false)
	{
		foreach ($members as $k => $p)
		{
			if (!empty($color_profile[$p['id']]['link']))
				$members[$k]['link'] = $color_profile[$p['id']]['link'];
		}
	}

	// And output the block
	echo '
								<table class="sp_fullwidth">';

	foreach ($members as $member)
		echo '
									<tr>
										<td class="sp_top_poster sp_center">', !empty($member['avatar']['href']) ? '
											<a href="' . $scripturl . '?action=profile;u=' . $member['id'] . '"><img src="' . $member['avatar']['href'] . '" alt="' . $member['name'] . '" width="40" /></a>' : '', '
										</td>
										<td>
											', $member['link'], '<br />
											', $member['posts'], ' ', $txt['posts'], '
										</td>
									</tr>';

	echo '
								</table>';
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
	global $context, $txt, $scripturl, $user_info, $user_info, $modSettings, $color_profile;
	static $sp_topStatsSystem;

	$db = database();

	$block_parameters = array(
		'type' => array(
			'0' => $txt['sp_topStatsMember_total_time_logged_in'],
			'1' => $txt['sp_topStatsMember_Posts'],
			'2' => $txt['sp_topStatsMember_Karma_Good'],
			'3' => $txt['sp_topStatsMember_Karma_Bad'],
			'4' => $txt['sp_topStatsMember_Karma_Total'],
			'5' => $txt['sp_topStatsMember_Likes_Received'],
			'6' => $txt['sp_topStatsMember_Likes_Given'],
		),
		'limit' => 'int',
		'sort_asc' => 'check',
		'last_active_limit' => 'int',
		'enable_label' => 'check',
		'list_label' => 'text',
	);

	if ($return_parameters)
		return $block_parameters;

	if (empty($sp_topStatsSystem))
	{
		/*
		 * The system setup array, the order depends on the $txt array of the select name
		 *
		 * 'mod_id' - Only used as information
		 * 'field' - The members field that should be loaded.  Please don't forget to add mem. before the field names
		 * 'order' - What is the field name i need to be sort after
		 * 'where' - Here you can add additional where statements
		 * 'output_text' - What should be displayed after the avatar and nickname
		 *				For example if your field is karmaGood 'output_text' => $txt['karma'] . '%karmaGood%';
		 * 'output_function' - With this you can add to the $row of the query some information.
		 * 'reverse' - On true it change the reverse cause, if not set it will be false :)
		 * 'enabled' - true = mod exists or is possible to use :D
		 * 'error_msg' => $txt['my_error_msg']; You can insert here what kind of error message should appear if the modification not exists =D
		 */
		$sp_topStatsSystem = array(
			'0' => array(
				'name' => 'Total time logged in',
				'field' => 'mem.total_time_logged_in',
				'order' => 'mem.total_time_logged_in',
				'output_function' => create_function('&$row', '
					global $txt;

					// Figure out the days, hours and minutes.
					$timeDays = floor($row["total_time_logged_in"] / 86400);
					$timeHours = floor(($row["total_time_logged_in"] % 86400) / 3600);

					// Figure out which things to show... (days, hours, minutes, etc.)
					$timelogged = "";
					if ($timeDays > 0)
						$timelogged .= $timeDays . $txt["totalTimeLogged5"];

					if ($timeHours > 0)
						$timelogged .= $timeHours . $txt["totalTimeLogged6"];

					$timelogged .= floor(($row["total_time_logged_in"] % 3600) / 60) . $txt["totalTimeLogged7"];
					$row["timelogged"] = $timelogged;
				'),
				'output_text' => ' %timelogged%',
				'reverse_sort_asc' => false,
				'enabled' => true,
			),
			'1' => array(
				'name' => 'Posts',
				'field' => 'mem.posts',
				'order' => 'mem.posts',
				'output_text' => ' %posts% ' . $txt['posts'],
				'enabled' => true,
			),
			'2' => array(
				'name' => 'Karma Good',
				'field' => 'mem.karma_good, mem.karma_bad',
				'order' => 'mem.karma_good',
				'output_function' => create_function('&$row', '
					$row["karma_total"] = $row["karma_good"] - $row["karma_bad"];
				'),
				'output_text' => $modSettings['karmaLabel'] . ($modSettings['karmaMode'] == 1 ? ' %karma_total%' : ' +%karma_good%\-%karma_bad%'),
				'enabled' => !empty($modSettings['karmaMode']),
				'error_msg' => $txt['sp_karma_is_disabled'],
			),
			'3' => array(
				'name' => 'Karma Bad',
				'field' => 'mem.karma_good, mem.karma_bad',
				'order' => 'mem.karma_bad',
				'output_function' => create_function('&$row', '
					$row["karma_total"] = $row["karma_good"] - $row["karma_bad"];
				'),
				'output_text' => $modSettings['karmaLabel'] . ($modSettings['karmaMode'] == 1 ? ' %karma_total%' : ' +%karma_good%\-%karma_bad%'),
				'enabled' => !empty($modSettings['karmaMode']),
				'error_msg' => $txt['sp_karma_is_disabled'],
			),
			'4' => array(
				'name' => 'Karma Total',
				'field' => 'mem.karma_good, mem.karma_bad',
				'order' => 'FLOOR(1000000+karma_good-karma_bad)',
				'output_function' => create_function('&$row', '
					$row["karma_total"] = $row["karma_good"] - $row["karma_bad"];
				'),
				'output_text' => $modSettings['karmaLabel'] . ($modSettings['karmaMode'] == 1 ? ' %karma_total%' : ' +%karma_good%\-%karma_bad%'),
				'enabled' => !empty($modSettings['karmaMode']),
				'error_msg' => $txt['sp_karma_is_disabled'],
			),
			'5' => array(
				'name' => 'Likes Received',
				'field' => 'mem.likes_received',
				'order' => 'mem.likes_received',
				'output_text' => '%likes_received% ' . $txt['sp_topStatsMember_Likes_Received'],
				'enabled' => !empty($modSettings['likes_enabled']),
				'error_msg' => $txt['sp_likes_is_disabled'],
			),
			'6' => array(
				'name' => 'Likes Given',
				'field' => 'mem.likes_given',
				'order' => 'mem.likes_given',
				'output_text' => '%likes_given% ' . $txt['sp_topStatsMember_Likes_Given'],
				'enabled' => !empty($modSettings['likes_enabled']),
				'error_msg' => $txt['sp_likes_is_disabled'],
			),
		);
	}

	// Standard Variables
	$type = !empty($parameters['type']) ? $parameters['type'] : 0;
	$limit = !empty($parameters['limit']) ? (int) $parameters['limit'] : 5;
	$sort_asc = !empty($parameters['sort_asc']);

	// Time is in days, but we need seconds
	$last_active_limit = !empty($parameters['last_active_limit']) ? $parameters['last_active_limit'] * 86400 : 0;
	$enable_label = !empty($parameters['enable_label']);
	$list_label = !empty($parameters['list_label']) ? $parameters['list_label'] : '';

	// Setup current block type
	$current_system = $sp_topStatsSystem[$type];

	// Possible to output?
	if (empty($current_system['enabled']))
	{
		echo (!empty($current_system['error_msg']) ? $current_system['error_msg'] : '');
		return;
	}

	// Sort in reverse?
	$sort_asc = !empty($current_system['reverse']) ? !$sort_asc : $sort_asc;

	// Build the where statement
	$where = array();

	// If this is already cached, use it
	$chache_id = 'sp_chache_' . $id . '_topStatsMember';
	if (empty($modSettings['sp_disableChache']) && !empty($modSettings[$chache_id]))
	{
		$data = explode(';', $modSettings[$chache_id]);

		if ($data[0] == $type && $data[1] == $limit && !empty($data[2]) == $sort_asc && $data[3] > time() - 300) // 5 Minute cache
			$where[] = 'mem.id_member IN (' . $data[4] . ')';
		else
			unset($modSettings[$chache_id]);
	}

	// Last active remove
	if (!empty($last_active_limit))
	{
		$timeLimit = time() - $last_active_limit;
		$where[] = "lastLogin > $timeLimit";
	}

	if (!empty($current_system['where']))
		$where[] = $current_system['where'];

	if (!empty($where))
		$where = 'WHERE (' . implode(')
			AND (', $where) . ')';
	else
		$where = "";

	// Member avatars
	if ($modSettings['avatar_action_too_large'] == 'option_html_resize' || $modSettings['avatar_action_too_large'] == 'option_js_resize')
	{
		$avatar_width = !empty($modSettings['avatar_max_width_external']) ? ' width="' . $modSettings['avatar_max_width_external'] . '"' : '';
		$avatar_height = !empty($modSettings['avatar_max_height_external']) ? ' height="' . $modSettings['avatar_max_height_external'] . '"' : '';
	}
	else
	{
		$avatar_width = '';
		$avatar_height = '';
	}

	// Finaly make the query with the parameters we built
	$request = $db->query('', '
		SELECT
			mem.id_member, mem.real_name, mem.avatar, mem.email_address,
			a.id_attach, a.attachment_type, a.filename,
			{raw:field}
		FROM {db_prefix}members as mem
			LEFT JOIN {db_prefix}attachments AS a ON (a.id_member = mem.id_member)
		{raw:where}
		ORDER BY {raw:order} {raw:sort}
		LIMIT {int:limit}',
		array(
			// Prevent delete of user if the cache was available
			'limit' => $context['common_stats']['total_members'] > 100 ? ($limit + 5) : $limit,
			'field' => $current_system['field'],
			'where' => $where,
			'order' => $current_system['order'],
			'sort' => ($sort_asc ? 'ASC' : 'DESC'),
		)
	);
	$members = array();
	$colorids = array();
	$count = 1;
	$chache_member_ids = array();
	while ($row = $db->fetch_assoc($request))
	{
		// Collect some to cache data
		$chache_member_ids[$row['id_member']] = $row['id_member'];
		if ($count++ > $limit)
			continue;

		$colorids[$row['id_member']] = $row['id_member'];

		// Setup the row
		$output = '';

		// Prepare some data of the row?
		if (!empty($current_system['output_function']))
			$current_system['output_function']($row);

		if (!empty($current_system['output_text']))
		{
			$output = $current_system['output_text'];
			foreach ($row as $item => $replacewith)
				$output = str_replace('%' . $item . '%', $replacewith, $output);
		}

		$members[] = array(
			'id' => $row['id_member'],
			'name' => $row['real_name'],
			'href' => $scripturl . '?action=profile;u=' . $row['id_member'],
			'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>',
			'avatar' => determineAvatar(array(
				'avatar' => $row['avatar'],
				'filename' => $row['filename'],
				'id_attach' => $row['id_attach'],
				'email_address' => $row['email_address'],
				'attachment_type' => $row['attachment_type'],
			)),
			'output' => $output,
			'complete_row' => $row,
		);
	}
	$db->free_result($request);

	// No one found, let them know
	if (empty($members))
	{
		echo '
								', $txt['error_sp_no_members_found'];
		return;
	}

	// Update the cache, at least around 100 members are needed for a good working version
	if (empty($modSettings['sp_disableChache']) && $context['common_stats']['total_members'] > 0 && !empty($chache_member_ids) && count($chache_member_ids) > $limit && empty($modSettings[$chache_id]))
	{
		$toCache = array($type, $limit, ($sort_asc ? 1 : 0), time(), implode(',', $chache_member_ids));
		updateSettings(array($chache_id => implode(';', $toCache)));
	}
	// One time error, if this happens the cache needs an update
	elseif (!empty($modSettings[$chache_id]))
		updateSettings(array($chache_id => '0;0;0;1000;0'));

	if (!empty($colorids) && sp_loadColors($colorids) !== false)
	{
		foreach ($members as $k => $p)
		{
			if (!empty($color_profile[$p['id']]['link']))
				$members[$k]['link'] = $color_profile[$p['id']]['link'];
		}
	}

	// Finally, output the block
	echo '
								<table class="sp_fullwidth">';

	if ($enable_label)
		echo '
									<tr>
										<td class="sp_top_poster sp_center" colspan="2">
											<strong>', $list_label, '</strong>
										</td>
									</tr>';

	foreach ($members as $member)
	{
		echo '
									<tr>
										<td class="sp_top_poster sp_center">', !empty($member['avatar']['href']) ? '
											<a href="' . $scripturl . '?action=profile;u=' . $member['id'] . '">
												<img src="' . $member['avatar']['href'] . '" alt="' . $member['name'] . '" width="40" />
											</a>' : '', '
										</td>
										<td>
											', $member['link'], '<br />', $member['output'], '
										</td>
									</tr>';
	}

	echo '
								</table>';
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
	global $txt, $scripturl, $color_profile;

	$block_parameters = array(
		'boards' => 'boards',
		'limit' => 'int',
		'type' => 'select',
		'display' => 'select',
	);

	if ($return_parameters)
		return $block_parameters;

	$boards = !empty($parameters['boards']) ? explode('|', $parameters['boards']) : null;
	$limit = !empty($parameters['limit']) ? (int) $parameters['limit'] : 5;
	$type = 'ssi_recent' . (empty($parameters['type']) ? 'Posts' : 'Topics');
	$display = empty($parameters['display']) ? 'compact' : 'full';

	// Pass the values to the ssi_ function
	$items = $type($limit, null, $boards, 'array');

	if (empty($items))
	{
		echo '
								', $txt['error_sp_no_posts_found'];
		return;
	}
	else
		$items[count($items) - 1]['is_last'] = true;

	$colorids = array();
	foreach ($items as $item)
		$colorids[] = $item['poster']['id'];

	if (!empty($colorids) && sp_loadColors($colorids) !== false)
	{
		foreach ($items as $k => $p)
		{
			if (!empty($color_profile[$p['poster']['id']]['link']))
				$items[$k]['poster']['link'] = $color_profile[$p['poster']['id']]['link'];
		}
	}

	// Show the data in either a compact or full format
	if ($display == 'compact')
	{
		foreach ($items as $key => $item)
			echo '
								<a href="', $item['href'], '">', $item['subject'], '</a>
								<span class="smalltext">', $txt['by'], ' ', $item['poster']['link'], $item['new'] ? '' : ' <a href="' . $scripturl . '?topic=' . $item['topic'] . '.msg' . $item['new_from'] . ';topicseen#new" rel="nofollow"><span class="new_posts">' . $txt['new'] . '</span></a>',
								'<br />[', $item['time'], ']</span>
								<br />', empty($item['is_last']) ? '<hr />' : '';
	}
	elseif ($display == 'full')
	{
		echo '
								<table class="sp_fullwidth">';

		foreach ($items as $item)
			echo '
									<tr>
										<td ', sp_embed_class(empty($parameters['type']) ? 'post' : 'topic', '', 'sp_recent_icon sp_center' ), '</td>
										<td class="sp_recent_subject">
											<a href="', $item['href'], '">', $item['subject'], '</a>
											', $item['new'] ? '' : '<a href="' . $scripturl . '?topic=' . $item['topic'] . '.msg' . $item['new_from'] . ';topicseen#new"><span class="new_posts">' . $txt['new'] . '</span></a>', '<br />[', $item['board']['link'], ']
										</td>
										<td class="sp_recent_info sp_right">
											', $item['poster']['link'], '<br />', $item['time'], '
										</td>
									</tr>';

		echo '
								</table>';
	}
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
	global $txt, $user_info, $user_info, $topics;

	$block_parameters = array(
		'type' => 'select',
		'limit' => 'int',
	);

	if ($return_parameters)
		return $block_parameters;

	$type = !empty($parameters['type']) ? $parameters['type'] : 0;
	$limit = !empty($parameters['limit']) ? $parameters['limit'] : 5;

	// Use the ssi function to get the data
	$topics = ssi_topTopics($type ? 'views' : 'replies', $limit, 'array');

	if (empty($topics))
	{
		echo '
								', $txt['error_sp_no_topics_found'];
		return;
	}
	else
	{
		end($topics);
		$topics[key($topics)]['is_last'] = true;
	}

	echo '
								<ul class="sp_list">';

	foreach ($topics as $topic)
		echo '
									<li ', sp_embed_class('topic', '', 'sp_list_top'), '>', $topic['link'], '</li>
									<li class="sp_list_indent', empty($topic['is_last']) ? ' sp_list_bottom' : '', ' smalltext">', $txt['replies'], ': ', $topic['num_replies'], ' | ', $txt['views'], ': ', $topic['num_views'], '</li>';

	echo '
								</ul>';
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
	global $txt, $user_info, $user_info, $boards;

	$block_parameters = array(
		'limit' => 'int',
	);

	if ($return_parameters)
		return $block_parameters;

	$limit = !empty($parameters['limit']) ? $parameters['limit'] : 5;

	$boards = ssi_topBoards($limit, 'array');

	if (empty($boards))
	{
		echo '
								', $txt['error_sp_no_boards_found'];
		return;
	}
	else
	{
		end($boards);
		$boards[key($boards)]['is_last'] = true;
	}

	echo '
								<ul class="sp_list">';

	foreach ($boards as $board)
		echo '
									<li ', sp_embed_class('board', '', 'sp_list_top'), '>', $board['link'], '</li>
									<li class="sp_list_indent', empty($board['is_last']) ? ' sp_list_bottom' : '', ' smalltext">', $txt['topics'], ': ', $board['num_topics'], ' | ', $txt['posts'], ': ', $board['num_posts'], '</li>';

	echo '
								</ul>';
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
	global $context, $scripturl, $modSettings, $boardurl, $txt;

	$db = database();

	$block_parameters = array(
		'topic' => 'int',
		'type' => 'select',
	);

	if ($return_parameters)
		return $block_parameters;

	$topic = !empty($parameters['topic']) ? $parameters['topic'] : null;
	$type = !empty($parameters['type']) ? (int) $parameters['type'] : 0;
	$boardsAllowed = boardsAllowedTo('poll_view');

	if (empty($boardsAllowed))
	{
		loadLanguage('Errors');

		echo '
								', $txt['cannot_poll_view'];
		return;
	}

	if (!empty($type))
	{
		$request = $db->query('', '
			SELECT t.id_topic
			FROM {db_prefix}polls AS p
				INNER JOIN {db_prefix}topics AS t ON (t.id_poll = p.id_poll' . ($modSettings['postmod_active'] ? ' AND t.approved = {int:is_approved}' : '') . ')
				INNER JOIN {db_prefix}boards AS b ON (b.id_board = t.id_board)
			WHERE {query_wanna_see_board}
				AND p.voting_locked = {int:not_locked}' . (!in_array(0, $boardsAllowed) ? '
				AND b.id_board IN ({array_int:boards_allowed_list})' : '') . (!empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0 ? '
				AND b.id_board != {int:recycle_enable}' : '') . '
			ORDER BY {raw:type}
			LIMIT 1',
			array(
				'boards_allowed_list' => $boardsAllowed,
				'not_locked' => 0,
				'is_approved' => 1,
				'recycle_enable' => $modSettings['recycle_board'],
				'type' => $type == 1 ? 'p.id_poll DESC' : 'RAND()',
			)
		);
		list ($topic) = $db->fetch_row($request);
		$db->free_result($request);
	}

	if (empty($topic) || $topic < 0)
	{
		loadLanguage('Errors');

		echo '
								', $txt['topic_doesnt_exist'];
		return;
	}

	$poll = ssi_showPoll($topic, 'array');

	if ($poll['allow_vote'])
	{
		echo '
								<form action="', $boardurl, '/SSI.php?ssi_function=pollVote" method="post" accept-charset="UTF-8">
									<ul class="sp_list">
										<li><strong>', $poll['question'], '</strong></li>
										<li>', $poll['allowed_warning'], '</li>';

		foreach ($poll['options'] as $option)
			echo '
										<li><label for="', $option['id'], '">', $option['vote_button'], ' ', $option['option'], '</label></li>';

		echo '
										<li class="sp_center"><input type="submit" value="', $txt['poll_vote'], '" class="button_submit" /></li>
										<li class="sp_center"><a href="', $scripturl, '?topic=', $poll['topic'], '.0">', $txt['sp-pollViewTopic'], '</a></li>
									</ul>
									<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
									<input type="hidden" name="poll" value="', $poll['id'], '" />
								</form>';
	}
	elseif ($poll['allow_view_results'])
	{
		echo '
								<ul class="sp_list">
									<li><strong>', $poll['question'], '</strong></li>';

		foreach ($poll['options'] as $option)
			echo '
									<li ', sp_embed_class('dot'), '> ', $option['option'], '</li>
									<li class="sp_list_indent"><strong>', $option['votes'], '</strong> (', $option['percent'], '%)</li>';

		echo '
									<li><strong>', $txt['poll_total_voters'], ': ', $poll['total_votes'], '</strong></li>
									<li class="sp_center"><a href="', $scripturl, '?topic=', $poll['topic'], '.0">', $txt['sp-pollViewTopic'], '</a></li>
								</ul>';
	}
	else
		echo '
								', $txt['poll_cannot_see'];
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
	global $scripturl, $txt, $settings, $modSettings, $color_profile;

	$db = database();

	$block_parameters = array(
		'board' => 'boards',
		'limit' => 'int',
		'start' => 'int',
		'length' => 'int',
		'avatar' => 'check',
		'per_page' => 'int',
	);

	if ($return_parameters)
		return $block_parameters;

	// Break out / sanitize all the parameters
	$board = !empty($parameters['board']) ? explode('|', $parameters['board']) : null;
	$limit = !empty($parameters['limit']) ? (int) $parameters['limit'] : 5;
	$start = !empty($parameters['start']) ? (int) $parameters['start'] : 0;
	$length = isset($parameters['length']) ? (int) $parameters['length'] : 250;
	$avatars = !empty($parameters['avatar']);
	$per_page = !empty($paramers['per_page']) ? (int) $parameters['per_page'] : 0;

	$limit = max(0, $limit);
	$start = max(0, $start);

	loadLanguage('Stats');

	$stable_icons = array('xx', 'thumbup', 'thumbdown', 'exclamation', 'question', 'lamp', 'smiley', 'angry', 'cheesy', 'grin', 'sad', 'wink', 'moved', 'recycled', 'wireless');
	$icon_sources = array();
	foreach ($stable_icons as $icon)
		$icon_sources[$icon] = 'images_url';

	$request = $db->query('', '
		SELECT t.id_first_msg
		FROM {db_prefix}topics AS t
			INNER JOIN {db_prefix}boards AS b ON (b.id_board = t.id_board)
			INNER JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
		WHERE ' . (empty($board) ? '{query_see_board}
			AND t.id_first_msg >= {int:min_msg_id}' : 't.id_board IN ({array_int:current_board})') . ($modSettings['postmod_active'] ? '
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
	while ($row = $db->fetch_assoc($request))
		$posts[] = $row['id_first_msg'];
	$db->free_result($request);

	if (empty($posts))
	{
		echo '
				', $txt['error_sp_no_posts_found'];
		return;
	}
	elseif (!empty($per_page))
	{
		$limit = count($posts);
		$start = !empty($_REQUEST['news' . $id]) ? (int) $_REQUEST['news' . $id] : 0;

		// $clean_url = preg_replace('~news' . $id . '=[^;]+~', '', $_SERVER['REQUEST_URL']);
		// http://simpleportal.net/index.php?topic=11022.msg66021#msg66021
		$clean_url = preg_replace('~news' . $id . '=\d+;?~', '', $_SERVER['REQUEST_URL']);
		$current_url = $clean_url . (strpos($clean_url, '?') !== false ? (in_array(substr($clean_url, -1), array(';', '?')) ? '' : ';') : '?');
	}

	$request = $db->query('', '
		SELECT
			m.icon, m.subject, m.body, IFNULL(mem.real_name, m.poster_name) AS poster_name, m.poster_time, m.email_address,
			t.num_replies, t.id_topic, m.id_member, m.smileys_enabled, m.id_msg, t.locked, mem.avatar,
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
	$return = array();
	$colorids = array();
	while ($row = $db->fetch_assoc($request))
	{
		// Shorten the text if needed and run it through the parser
		if (!empty($length))
			$row['body'] = shorten_text($row['body'], $length, true);

		censorText($row['subject']);
		censorText($row['body']);

		$row['body'] = parse_bbc($row['body'], $row['smileys_enabled'], $row['id_msg']);

		// Link the ellipsis if the body has been shortened.
		$row['body'] = preg_replace('~(\.{3})$(?<!\.{4})~', '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.0" title="' . $row['subject'] . '">&hellip;</a>', $row['body']);

		if ($modSettings['avatar_action_too_large'] == 'option_html_resize' || $modSettings['avatar_action_too_large'] == 'option_js_resize')
		{
			$avatar_width = !empty($modSettings['avatar_max_width_external']) ? ' width="' . $modSettings['avatar_max_width_external'] . '"' : '';
			$avatar_height = !empty($modSettings['avatar_max_height_external']) ? ' height="' . $modSettings['avatar_max_height_external'] . '"' : '';
		}
		else
		{
			$avatar_width = '';
			$avatar_height = '';
		}

		if (empty($modSettings['messageIconChecks_disable']) && !isset($icon_sources[$row['icon']]))
			$icon_sources[$row['icon']] = file_exists($settings['theme_dir'] . '/images/post/' . $row['icon'] . '.png') ? 'images_url' : 'default_images_url';

		if ($modSettings['sp_resize_images'])
			$row['body'] = str_ireplace('class="bbc_img', 'class="bbc_img sp_article', $row['body']);

		if (!empty($row['id_member']))
			$colorids[$row['id_member']] = $row['id_member'];

		// Build an array of message information for output
		$return[] = array(
			'id' => $row['id_topic'],
			'message_id' => $row['id_msg'],
			'icon' => '<img src="' . $settings[$icon_sources[$row['icon']]] . '/post/' . $row['icon'] . '.png" class="icon" alt="' . $row['icon'] . '" />',
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

	// Nothing found, say so and exit
	if (empty($return))
	{
		echo '
				', $txt['error_sp_no_posts_found'];
		return;
	}

	$return[count($return) - 1]['is_last'] = true;

	// If we want color id's then lets add them in
	if (!empty($colorids) && sp_loadColors($colorids) !== false)
	{
		foreach ($return as $k => $p)
		{
			if (!empty($color_profile[$p['poster']['id']]['link']))
				$return[$k]['poster']['link'] = $color_profile[$p['poster']['id']]['link'];
		}
	}

	// Output all the details we have found
	foreach ($return as $news)
	{
		echo '
				<h3 class="category_header">
					<span class="sp_float_left sp_article_icon">', $news['icon'], '</span><a href="', $news['href'], '" >', $news['subject'], '</a>
				</h3>
				<div class="windowbg sp_article_content">
					<div class="sp_content_padding">';

		if ($avatars && $news['avatar']['name'] !== null && !empty($news['avatar']['href']))
			echo '
						<a href="', $scripturl, '?action=profile;u=', $news['poster']['id'], '"><img src="', $news['avatar']['href'], '" alt="', $news['poster']['name'], '" width="30" class="sp_float_right" /></a>
						<div class="middletext">', $news['time'], ' ', $txt['by'], ' ', $news['poster']['link'], '<br />', $txt['sp-articlesViews'], ': ', $news['views'], ' | ', $txt['sp-articlesComments'], ': ', $news['replies'], '</div>';
		else
			echo '
						<div class="middletext">', $news['time'], ' ', $txt['by'], ' ', $news['poster']['link'], ' | ', $txt['sp-articlesViews'], ': ', $news['views'], ' | ', $txt['sp-articlesComments'], ': ', $news['replies'], '</div>';

		echo '
						<div class="post"><hr />', $news['body'], '</div>
						<div class="sp_right">', $news['link'], ' ', $news['new_comment'], '</div>
					</div>
				</div>';
	}

	// Pagenation is a good thing
	if (!empty($per_page))
	{
		global $context;

		$context['page_index']  = constructPageIndex($current_url . 'news' . $id . '=%1$d', $start, $limit, $per_page, false);

		echo '
				<div class="sp_page_index">',
					template_pagesection(), '
				</div>';
	}
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
									<div class="sp_center">
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
								<div class="sp_center sp_fullwidth">', $context['random_news_line'], '</div>';
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
	global $txt, $color_profile;

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
	foreach ($items as $item)
	{
		echo !$direction ? '
									<tr>' : '';

		echo '
										<td>
											<div class="sp_image smalltext">', ($showLink ? '
												<a href="' . $item['file']['href'] . '">' . str_replace(array('_', '-'), ' ', $item['file']['filename']) . '</a><br />' : ''), '
												', $item['file']['image']['link'], '<br />', ($showDownloads ? '
												' . $txt['downloads'] . ': ' . $item['file']['downloads'] . '<br />' : ''), ($showPoster ? '
												' . $txt['posted_by'] . ': ' . $item['member']['link'] : ''), '
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

	echo '
								<table class="sp_acalendar smalltext">
									<tr>
										<td class="sp_center" colspan="7">
											', !empty($modSettings['cal_enabled']) ? '<a href="' . $scripturl . '?action=calendar;year=' . $calendar_data['current_year'] . ';month=' . $calendar_data['current_month'] . '">' . $txt['months_titles'][$calendar_data['current_month']] . ' ' . $calendar_data['current_year'] . '</a>' : $txt['months_titles'][$calendar_data['current_month']] . ' ' . $calendar_data['current_year'], '
										</td>
									</tr><tr>';

	foreach ($calendar_data['week_days'] as $day)
		echo '
										<td class="sp_center">', $txt['days_short'][$day], '</td>';

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
								<div class="sp_center smalltext" id="sp_calendar_', $day['day'], '">', $txt['error_sp_no_items_day'], '</div>';

				continue;
			}

			echo '
								<ul class="sp_list smalltext" id="sp_calendar_', $day['day'], '" ', !$day['is_today'] ? ' style="display: none;"' : '', '>';

			if (!empty($day['holidays']))
			{
				echo '
									<li class="sp_center"><strong>- ', $txt['sp_calendar_holidays'], ' -</strong></li>';

				foreach ($day['holidays'] as $key => $holiday)
					echo '
									<li ', sp_embed_class('holiday', '', 'sp_list_indent'), '>', $holiday, '</li>';
			}

			if (!empty($day['birthdays']))
			{
				echo '
									<li class="sp_center"><strong>- ', $txt['sp_calendar_birthdays'], ' -</strong></li>';

				foreach ($day['birthdays'] as $member)
					echo '
									<li ', sp_embed_class('birthday', '', 'sp_list_indent'), '>
										<a href="', $scripturl, '?action=profile;u=', $member['id'], '">', $member['name'], isset($member['age']) ? ' (' . $member['age'] . ')' : '', '</a>
									</li>';
			}

			if (!empty($day['events']))
			{
				echo '
									<li class="sp_center"><strong>- ', $txt['sp_calendar_events'], ' -</strong></li>';

				foreach ($day['events'] as $event)
					echo '
									<li ', sp_embed_class('event', '', 'sp_list_indent'), '>', $event['link'], '</li>';
			}

			echo '
								</ul>';
		}
	}

	echo '
								<div class="sp_center smalltext" id="sp_calendar_0" style="display: none;">', $txt['error_sp_no_items_day'], '</div>';

	addInlineJavascript('
		var current_day = "sp_calendar_' . $curPage['day'] . '";
		function sp_collapseCalendar(id)
		{
			new_day = "sp_calendar_" + id;
			if (new_day == current_day)
				return false;
			document.getElementById(current_day).style.display = "none";
			document.getElementById(new_day).style.display = "";
			current_day = new_day;
		}');
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

	if ($show_event)
	{
		if (!empty($event_future))
			$event_future_date = date("Y-m-d", ($now + $event_future * 86400));
		else
			$event_future_date = $today_date;

		$events = sp_loadCalendarData('getEvents', $today_date, $event_future_date);

		ksort($events);

		$displayed = array();
		foreach ($events as $day => $day_events)
			foreach ($day_events as $event_key => $event)
				if (in_array($event['id'], $displayed))
					unset($events[$day][$event_key]);
				else
					$displayed[] = $event['id'];

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

	if ($show_birthday)
	{
		$calendar_array['todayBirthdays'] = current(sp_loadCalendarData('getBirthdays', $today_date));
		$show_titles = !empty($show_event) || !empty($show_holiday);
	}

	if ($show_holiday)
	{
		$calendar_array['todayHolidays'] = current(sp_loadCalendarData('getHolidays', $today_date));
		$show_titles = !empty($show_event) || !empty($show_birthday);
	}

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
				list($year, $month, $day) = explode('-', $startdate);
				$currentDay = $day . ' ' . $txt['months_short'][(int) $month];

				foreach ($events as $event)
					echo '
									<li ', sp_embed_class('event'), '>', $event['link'], ' - ', $currentDay, '</li>';
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
	$limit = !empty($parameters['limit']) ? (int) $parameters['limit'] : 150;

	if (empty($feed))
	{
		echo '
								', $txt['error_sp_invalid_feed'];
		return;
	}

	$rss = array();

	require_once(SUBSDIR . '/Package.subs.php');
	$data = fetch_web_data($feed);

	if (function_exists('mb_convert_encoding'))
	{
		preg_match('~encoding="([^"]*)"~', $data, $charset);

		if (!empty($charset[1]) && $charset != 'UTF-8')
			$data = mb_convert_encoding($data, 'UTF-8', $charset[1]);
	}
	elseif (function_exists('iconv'))
	{
		preg_match('~encoding="([^"]*)"~', $data, $charset);

		if (!empty($charset[1]) && $charset != 'UTF-8')
			$data = iconv($charset[1], 'UTF-8', $data);
	}

	$data = str_replace(array("\n", "\r", "\t"), '', $data);
	$data = preg_replace('~<\!\[CDATA\[(.+?)\]\]>~eu', '\'#cdata_escape_encode#\' . Util::\'htmlspecialchars\'(\'$1\')', $data);

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

	if (empty($rss))
	{
		echo '
								', $txt['error_sp_invalid_feed'];
		return;
	}

	$items = array();
	foreach ($rss as $item)
	{
		$item['title'] = isset($item['title']) ? strip_tags($item['title']) : '';
		$item['description'] = isset($item['description']) ? strip_tags($item['description'], empty($strip_preserve) ? '' : '<' . implode('><', $strip_preserve) . '>') : '';

		$items[] = array(
			'title' => $item['title'],
			'href' => $item['link'],
			'link' => $item['title'] == '' ? '' : ($item['link'] == '' ? $item['title'] : '<a href="' . $item['link'] . '" target="_blank" class="new_win">' . $item['title'] . '</a>'),
			'content' => $limit > 0 && Util::strlen($item['description']) > $limit ? Util::substr($item['description'], 0, $limit) . '&hellip;' : $item['description'],
			'date' => !empty($item['pubdate']) ? standardTime(strtotime($item['pubdate']), '%d %B') : '',
		);
	}

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

	$db = database();

	$block_parameters = array();

	if ($return_parameters)
		return $block_parameters;

	loadLanguage('Profile');
	loadLanguage('ManageThemes');

	if (!empty($_SESSION['id_theme']) && (!empty($modSettings['theme_allow']) || allowedTo('admin_forum')))
		$current_theme = (int) $_SESSION['id_theme'];
	else
		$current_theme = $user_info['theme'];

	$current_theme = empty($current_theme) ? -1 : $current_theme;
	$available_themes = array();
	if (!empty($modSettings['knownThemes']))
	{
		$request = $db->query('', '
			SELECT id_theme, variable, value
			FROM {db_prefix}themes
			WHERE variable IN ({string:name}, {string:theme_url}, {string:theme_dir}, {string:images_url})
				AND id_theme IN ({array_string:known_themes})
				AND id_theme != {int:default_theme}',
			array(
				'default_theme' => 0,
				'name' => 'name',
				'theme_url' => 'theme_url',
				'theme_dir' => 'theme_dir',
				'images_url' => 'images_url',
				'known_themes' => explode(',', $modSettings['knownThemes']),
			)
		);
		while ($row = $db->fetch_assoc($request))
		{
			if (!isset($available_themes[$row['id_theme']]))
				$available_themes[$row['id_theme']] = array(
					'id' => $row['id_theme'],
					'selected' => $current_theme == $row['id_theme'],
				);
			$available_themes[$row['id_theme']][$row['variable']] = $row['value'];
		}
		$db->free_result($request);
	}

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
								<form method="post" action="" accept-charset="UTF-8">
									<div class="sp_center">
										<select name="theme" onchange="sp_theme_select(this)">';

	foreach ($available_themes as $theme)
		echo '
											<option value="', $theme['id'], '"', $theme['selected'] ? ' selected="selected"' : '', '>', $theme['name'], '</option>';

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
		var sp_ts_thumbs = new Array();';

	foreach ($available_themes as $id => $theme_data)
		$javascript .= '
			sp_ts_thumbs[' . $id - '] = "' . $theme_data['thumbnail_href'] . '";';

	$javascript .= '
			function sp_theme_select(obj)
			{
				var id = obj.options[obj.selectedIndex].value;
				document.getElementById("sp_ts_thumb").src = sp_ts_thumbs[id];
			}';

	addInlineJavascript($javascript);
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

	if (empty($parameters['lmod']))
	{
		$request = $db->query('', '
			SELECT id_member
			FROM {db_prefix}moderators AS mods',
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

		if ($modSettings['avatar_action_too_large'] == 'option_html_resize' || $modSettings['avatar_action_too_large'] == 'option_js_resize')
		{
			$avatar_width = !empty($modSettings['avatar_max_width_external']) ? ' width="' . $modSettings['avatar_max_width_external'] . '"' : '';
			$avatar_height = !empty($modSettings['avatar_max_height_external']) ? ' height="' . $modSettings['avatar_max_height_external'] . '"' : '';
		}
		else
		{
			$avatar_width = '';
			$avatar_height = '';
		}

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
										<td class="sp_staff sp_center">', !empty($staff['avatar']['href']) ? '
											<a href="' . $scripturl . '?action=profile;u=' . $staff['id'] . '"><img src="' . $staff['avatar']['href'] . '" alt="' . $staff['name'] . '" width="40" /></a>' : '', '
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
 *
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
function sp_articles($parameters, $id, $return_parameters = false)
{
	global $modSettings, $scripturl, $txt, $color_profile, $context;

	$db = database();

	$block_parameters = array(
		'category' => array(0 => $txt['sp_all']),
		'limit' => 'int',
		'type' => 'select',
		'length' => 'int',
	);

	if ($return_parameters)
	{
		require_once(SUBSDIR . '/PortalAdmin.subs.php');

		$categories = sp_load_categories();
		foreach ($categories as $category)
			$block_parameters['category'][$category['id']] = $category['name'];

		return $block_parameters;
	}

	// Maybe useful ... maybe not !
	if ($modSettings['avatar_action_too_large'] == 'option_html_resize' || $modSettings['avatar_action_too_large'] == 'option_js_resize')
	{
		$avatar_width = !empty($modSettings['avatar_max_width_external']) ? ' width="' . $modSettings['avatar_max_width_external'] . '"' : '';
		$avatar_height = !empty($modSettings['avatar_max_height_external']) ? ' height="' . $modSettings['avatar_max_height_external'] . '"' : '';
	}
	else
	{
		$avatar_width = '';
		$avatar_height = '';
	}

	// Set up for the query
	$category = empty($parameters['category']) ? 0 : (int) $parameters['category'];
	$limit = empty($parameters['limit']) ? 5 : (int) $parameters['limit'];
	$type = empty($parameters['type']) ? 0 : 1;
	$length = isset($parameters['length']) ? (int) $parameters['length'] : 250;

	// Permissions
	$query[] = sprintf($context['SPortal']['permissions']['query'], 'spa.permissions');
	$query[] = sprintf($context['SPortal']['permissions']['query'], 'spc.permissions');

	$request = $db->query('', '
		SELECT
			spc.name, spc.namespace AS category_namespace,
			mem.avatar, mem.email_address, IFNULL(spa.id_member, 0) AS id_author, IFNULL(mem.real_name, spa.member_name) AS author_name,
			at.id_attach, at.attachment_type, at.filename,
			spa.body, spa.title, spa.member_name, spa.namespace AS article_namespace, spa.id_member,
			spa.type, spa.date, spa.status, spa.id_article, spa.id_category
		FROM {db_prefix}sp_articles AS spa
			INNER JOIN {db_prefix}sp_categories AS spc ON (spc.id_category = spa.id_category)
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = spa.id_member)
			LEFT JOIN {db_prefix}attachments AS at ON (at.id_member = mem.id_member)
		WHERE
			spa.status = {int:approved}' . (!empty($category) ? '
			AND spa.id_category = {int:category}' : '') . '
			AND ' . implode(' AND ', $query) . '
		ORDER BY {raw:type}
		LIMIT {int:limit}',
		array(
			'approved' => 1,
			'category' => $category,
			'type' => $type ? 'RAND()' : 'spa.date DESC',
			'limit' => $limit,
		)
	);
	$articles = array();
	$colorids = array();
	while ($row = $db->fetch_assoc($request))
	{
		if (!empty($row['id_member']))
			$colorids[$row['id_member']] = $row['id_member'];

		$articles[] = array(
			'id' => $row['id_article'],
			'name' => $row['title'],
			'body' => $row['body'],
			'preview' => shorten_text($row['body'], $length, true),
			'href' => $scripturl . '?article=' . $row['article_namespace'],
			'link' => '<a href="' . $scripturl . '?article=' . $row['article_namespace'] . '">' . $row['title'] . '</a>',
			'category' => array(
				'id' => $row['id_category'],
				'name' => $row['name'],
				'href' => $scripturl . '?category=' . $row['category_namespace'],
				'link' => '<a href="' . $scripturl . '?category=' . $row['category_namespace'] . '">' . $row['name'] . '</a>',
			),
			'poster' => array(
				'id' => $row['id_author'],
				'name' => $row['author_name'],
				'href' => $scripturl . '?action=profile;u=' . $row['id_author'],
				'link' => $row['id_author'] ? ('<a href="' . $scripturl . '?action=profile;u=' . $row['id_author'] . '">' . $row['author_name'] . '</a>') : $row['author_name'],
			),
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
			if (!empty($color_profile[$p['poster']['id']]['link']))
				$articles[$k]['poster']['link'] = $color_profile[$p['poster']['id']]['link'];
		}
	}

	// No image
	if (!empty($image))
	{
		echo '
								<ul class="sp_list">';

		foreach ($articles as $article)
			echo '
									<li ', sp_embed_class('topic'), '>', $article['link'], '</li>';

		echo '
								</ul>';
	}
	// Or the avatar view
	else
	{
		echo '
								<table class="sp_fullwidth">';

		foreach ($articles as $article)
		{
			echo '
									<tr>
										<td class="sp_articles sp_center">';

			if (!empty($article['avatar']['href']))
				echo '
											<a href="', $scripturl, '?action=profile;u=', $article['poster']['id'], '">
												<img src="', $article['avatar']['href'], '" alt="', $article['poster']['name'], '" width="40" />
											</a>';

			echo '
										</td>
										<td>
											<span class="sp_articles_title">', $article['poster']['link'], '</span><br />
											', $article['link'], '
										</td>
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
	global $scripturl, $txt, $scripturl;
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

	// right now we only know about one gallery
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
		require_once(SOURCEDIR . '/Aeva-Subs.php');

		$items = aeva_getMediaItems(0, $limit, $type ? 'RAND()' : 'm.id_media DESC');
	}


	if (empty($items))
	{
		echo '
								', $txt['error_sp_no_pictures_found'];
		return;
	}

	echo '
								<table class="sp_auto_align">', $direction ? '
									<tr>' : '';

	foreach ($items as $item)
	{
		echo!$direction ? '
									<tr>' : '', '
										<td>
											<div class="sp_image smalltext">';

		if ($mod == 'aeva_media')
		{
			echo '
												<a href="', $scripturl, 'sa=item;in=', $item['id'], '">', $item['title'], '</a><br />
												<a href="', $scripturl, 'sa=item;in=', $item['id'], '"><img src="', $scripturl, 'sa=media;in=', $item['id'], ';thumb" alt="" /></a><br />
												', $txt['aeva_views'], ': ', $item['views'], '<br />
												', $txt['aeva_posted_by'], ': <a href="', $scripturl, '?action=profile;u=', $item['poster_id'], '">', $item['poster_name'], '</a><br />
												', $txt['aeva_in_album'], ': <a href="', $scripturl, 'sa=album;in=', $item['id_album'], '">', $item['album_name'], '</a>', $item['is_new'] ?
					'<br /><span class="new_posts">' . $txt['new'] . '</span>' : '';
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
 * @todo needs updating .. superfish it?
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
								<ul class="sp_list" id="sp_menu">';

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