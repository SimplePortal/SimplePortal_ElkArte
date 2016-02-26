<?php

/**
 * @package SimplePortal ElkArte
 *
 * @author SimplePortal Team
 * @copyright 2015 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.0 Beta 1
 */

if (!defined('ELK'))
{
	die('No access...');
}

/**
 * Load a shoutboxs parameters by ID
 *
 * @param int|null $shoutbox_id
 * @param boolean $active
 * @param boolean $allowed
 *
 * @return array
 */
function sportal_get_shoutbox($shoutbox_id = null, $active = false, $allowed = false)
{
	global $context;

	$db = database();

	$query = array();
	$parameters = array();

	if ($shoutbox_id !== null)
	{
		$query[] = 'id_shoutbox = {int:shoutbox_id}';
		$parameters['shoutbox_id'] = $shoutbox_id;
	}

	if (!empty($allowed))
	{
		$query[] = sprintf($context['SPortal']['permissions']['query'], 'permissions');
	}

	if (!empty($active))
	{
		$query[] = 'status = {int:status}';
		$parameters['status'] = 1;
	}

	$request = $db->query('', '
		SELECT
			id_shoutbox, name, permissions, moderator_groups, warning, allowed_bbc, height,
			num_show, num_max, refresh, reverse, caching, status, num_shouts, last_update
		FROM {db_prefix}sp_shoutboxes' . (!empty($query) ? '
		WHERE ' . implode(' AND ', $query) : '') . '
		ORDER BY name',
		$parameters
	);
	$return = array();
	while ($row = $db->fetch_assoc($request))
	{
		$return[$row['id_shoutbox']] = array(
			'id' => $row['id_shoutbox'],
			'name' => $row['name'],
			'permissions' => $row['permissions'],
			'moderator_groups' => $row['moderator_groups'] !== '' ? explode(',', $row['moderator_groups']) : array(),
			'warning' => $row['warning'],
			'allowed_bbc' => explode(',', $row['allowed_bbc']),
			'height' => $row['height'],
			'num_show' => $row['num_show'],
			'num_max' => $row['num_max'],
			'refresh' => $row['refresh'],
			'reverse' => $row['reverse'],
			'caching' => $row['caching'],
			'status' => $row['status'],
			'num_shouts' => $row['num_shouts'],
			'last_update' => $row['last_update'],
		);
	}
	$db->free_result($request);

	return !empty($shoutbox_id) ? current($return) : $return;
}

/**
 * Loads all the shouts for a given shoutbox
 *
 * @param int $shoutbox id of the shoutbox to get data from
 * @param mixed[] $parameters
 *
 * @return array
 */
function sportal_get_shouts($shoutbox, $parameters)
{
	global $scripturl, $context, $user_info, $modSettings, $txt;

	$db = database();

	// Set defaults or used what was passed
	$shoutbox = !empty($shoutbox) ? (int) $shoutbox : 0;
	$start = !empty($parameters['start']) ? (int) $parameters['start'] : 0;
	$limit = !empty($parameters['limit']) ? (int) $parameters['limit'] : 20;
	$bbc = !empty($parameters['bbc']) ? $parameters['bbc'] : array();
	$reverse = !empty($parameters['reverse']);
	$cache = !empty($parameters['cache']);
	$can_delete = !empty($parameters['can_moderate']);

	// Cached, use it first
	if (!empty($start) || !$cache || ($shouts = cache_get_data('shoutbox_shouts-' . $shoutbox, 240)) === null)
	{
		$request = $db->query('', '
			SELECT
				sh.id_shout, sh.body, sh.log_time,
				IFNULL(mem.id_member, 0) AS id_member,
				IFNULL(mem.real_name, sh.member_name) AS member_name,
				mg.online_color AS member_group_color,
				pg.online_color AS post_group_color
			FROM {db_prefix}sp_shouts AS sh
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = sh.id_member)
				LEFT JOIN {db_prefix}membergroups AS pg ON (pg.id_group = mem.id_post_group)
				LEFT JOIN {db_prefix}membergroups AS mg ON (mg.id_group = mem.id_group)
			WHERE sh.id_shoutbox = {int:id_shoutbox}
			ORDER BY sh.id_shout DESC
			LIMIT {int:start}, {int:limit}',
			array(
				'id_shoutbox' => $shoutbox,
				'start' => $start,
				'limit' => $limit,
			)
		);
		$shouts = array();
		while ($row = $db->fetch_assoc($request))
		{
			// Disable the aeva mod for the shoutbox.
			$context['aeva_disable'] = true;
			$online_color = !empty($row['member_group_color']) ? $row['member_group_color'] : $row['post_group_color'];
			$shouts[$row['id_shout']] = array(
				'id' => $row['id_shout'],
				'author' => array(
					'id' => $row['id_member'],
					'name' => $row['member_name'],
					'link' => $row['id_member']
						? ('<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '" title="' . $txt['on'] . ' ' . strip_tags(standardTime($row['log_time'])) . '"' . (!empty($online_color)
								? ' style="color: ' . $online_color . ';"' : '') . '>' . $row['member_name'] . '</a>')
						: $row['member_name'],
					'color' => $online_color,
				),
				'time' => $row['log_time'],
				'text' => parse_bbc($row['body'], true, '', $bbc),
			);
		}
		$db->free_result($request);

		if (empty($start) && $cache)
		{
			cache_put_data('shoutbox_shouts-' . $shoutbox, $shouts, 240);
		}
	}

	foreach ($shouts as $shout)
	{
		// Private shouts @username: only get shown to the shouter and shoutee, and the admin
		if (preg_match('~^@(.+?): ~u', $shout['text'], $target) && Util::strtolower($target[1]) !== Util::strtolower($user_info['name']) && $shout['author']['id'] != $user_info['id'] && !$user_info['is_admin'])
		{
			unset($shouts[$shout['id']]);
			continue;
		}

		$shouts[$shout['id']] += array(
			'is_me' => preg_match('~^<div\sclass="meaction">\* ' . preg_quote($shout['author']['name'], '~') . '.+</div>$~', $shout['text']) != 0,
			'delete_link' => $can_delete
				? '<a class="dot dotdelete" href="' . $scripturl . '?action=shoutbox;shoutbox_id=' . $shoutbox . ';delete=' . $shout['id'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '"></a> ' : '',
			'delete_link_js' => $can_delete
				? '<a class="dot dotdelete" href="' . $scripturl . '?action=shoutbox;shoutbox_id=' . $shoutbox . ';delete=' . $shout['id'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '" onclick="sp_delete_shout(' . $shoutbox . ', ' . $shout['id'] . ', \'' . $context['session_var'] . '\', \'' . $context['session_id'] . '\'); return false;"></a> ' : '',
		);

		// Prepare for display in the box
		$shouts[$shout['id']]['time'] = standardTime($shouts[$shout['id']]['time']);
		$shouts[$shout['id']]['text'] = preg_replace('~(</?)div([^<]*>)~', '$1span$2', $shouts[$shout['id']]['text']);
		$shouts[$shout['id']]['text'] = preg_replace('~<a([^>]+>)([^<]+)</a>~', '<a$1' . $txt['sp_link'] . '</a>', $shouts[$shout['id']]['text']);
		$shouts[$shout['id']]['text'] = censorText($shouts[$shout['id']]['text']);

		// Ignored user, hide the shout with option to show it
		if (!empty($modSettings['enable_buddylist']) && in_array($shout['author']['id'], $context['user']['ignoreusers']))
		{
			$shouts[$shout['id']]['text'] = '<a href="#toggle" id="ignored_shout_link_' . $shout['id'] . '" onclick="sp_show_ignored_shout(' . $shout['id'] . '); return false;">[' . $txt['sp_shoutbox_show_ignored'] . ']</a><span id="ignored_shout_' . $shout['id'] . '" style="display: none;">' . $shouts[$shout['id']]['text'] . '</span>';
		}
	}

	if ($reverse)
	{
		$shouts = array_reverse($shouts);
	}

	return $shouts;
}

/**
 * Return the number of shouts in a given shoutbox
 *
 * @param int $shoutbox_id ID of the shoutbox
 *
 * @return int
 */
function sportal_get_shoutbox_count($shoutbox_id)
{
	$db = database();

	$request = $db->query('', '
		SELECT COUNT(*)
		FROM {db_prefix}sp_shouts
		WHERE id_shoutbox = {int:current}',
		array(
			'current' => $shoutbox_id,
		)
	);
	list ($total_shouts) = $db->fetch_row($request);
	$db->free_result($request);

	return $total_shouts;
}

/**
 * Adds a new shout to a given box
 *
 * - Prevents guest from adding a shout
 * - Checks the shout total and archives if over the display limit for the box
 *
 * @param int $shoutbox
 * @param string $shout
 *
 * @return bool
 */
function sportal_create_shout($shoutbox, $shout)
{
	global $user_info;

	$db = database();

	// If a guest shouts in the woods, and no one is there to hear them
	if ($user_info['is_guest'])
	{
		return false;
	}

	// What, its not like we can shout to nothing
	if (empty($shoutbox))
	{
		return false;
	}

	if (trim(strip_tags(parse_bbc($shout, false), '<img>')) === '')
	{
		return false;
	}

	// Add the shout
	$db->insert('', '
		{db_prefix}sp_shouts',
		array('id_shoutbox' => 'int', 'id_member' => 'int', 'member_name' => 'string', 'log_time' => 'int', 'body' => 'string',),
		array($shoutbox['id'], $user_info['id'], $user_info['name'], time(), $shout,),
		array('id_shout')
	);

	// To many shouts in the box, then its archive maintenance time
	$shoutbox['num_shouts']++;
	if ($shoutbox['num_shouts'] > $shoutbox['num_max'])
	{
		$request = $db->query('', '
			SELECT id_shout
			FROM {db_prefix}sp_shouts
			WHERE id_shoutbox = {int:shoutbox}
			ORDER BY log_time
			LIMIT {int:limit}',
			array(
				'shoutbox' => $shoutbox['id'],
				'limit' => $shoutbox['num_shouts'] - $shoutbox['num_max'],
			)
		);
		$old_shouts = array();
		while ($row = $db->fetch_assoc($request))
			$old_shouts[] = $row['id_shout'];
		$db->free_result($request);

		sportal_delete_shout($shoutbox['id'], $old_shouts, true);
	}
	else
	{
		sportal_update_shoutbox($shoutbox['id'], true);
	}

	return true;
}

/**
 * Removes a shout from the shoutbox by id
 *
 * @param int $shoutbox_id
 * @param int[]|int $shouts
 * @param bool $prune
 *
 * @return null
 */
function sportal_delete_shout($shoutbox_id, $shouts, $prune = false)
{
	$db = database();

	if (!is_array($shouts))
	{
		$shouts = array($shouts);
	}

	// Remove it
	$db->query('', '
		DELETE FROM {db_prefix}sp_shouts
		WHERE id_shout IN ({array_int:shouts})',
		array(
			'shouts' => $shouts,
		)
	);

	// Update the view
	sportal_update_shoutbox($shoutbox_id, $prune ? count($shouts) - 1 : count($shouts));
}

/**
 * Updates the number of shouts for a given shoutbox
 *
 * Can supply a specific number to update the count to
 * Use true to increment by 1
 *
 * @param int $shoutbox_id
 * @param int|bool $num_shouts if true increase the count by 1
 *
 * @return null
 */
function sportal_update_shoutbox($shoutbox_id, $num_shouts = 0)
{
	$db = database();

	$db->query('', '
		UPDATE {db_prefix}sp_shoutboxes
		SET last_update = {int:time}' . ($num_shouts === 0
			? ''
			: ',
			num_shouts = {raw:shouts}') . '
		WHERE id_shoutbox = {int:shoutbox}',
		array(
			'shoutbox' => $shoutbox_id,
			'time' => time(),
			'shouts' => $num_shouts === true ? 'num_shouts + 1' : 'num_shouts - ' . $num_shouts,
		)
	);

	cache_put_data('shoutbox_shouts-' . $shoutbox_id, null, 240);
}