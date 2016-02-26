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
 * Top stats block, shows the top x members who has achieved top position of various stats
 * Designed to be flexible so adding additional member stats is easy
 *
 * @param mixed[] $parameters
 *        'limit' => number of top posters to show
 *        'type' => top stat to show
 *        'sort_asc' => direction to show the list
 *        'last_active_limit'
 *        'enable_label' => use the label
 *        'list_label' => title for the list
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
class Top_Stats_Member_Block extends SP_Abstract_Block
{
	protected $sp_topStatsSystem = array();
	protected $color_ids = array();

	/**
	 * Constructor, used to define block parameters
	 *
	 * @param Database|null $db
	 */
	public function __construct($db = null)
	{
		global $txt, $modSettings;

		$this->block_parameters = array(
			'type' => array(
				'0' => $txt['sp_topStatsMember_total_time_logged_in'],
				'1' => $txt['sp_topStatsMember_Posts'],
				'2' => $txt['sp_topStatsMember_Karma_Good'],
				'3' => $txt['sp_topStatsMember_Karma_Bad'],
				'4' => $txt['sp_topStatsMember_Karma_Total'],
				'5' => $txt['sp_topStatsMember_Likes_Received'],
				'6' => $txt['sp_topStatsMember_Likes_Given'],
				'7' => $txt['sp_topStatsMember_Likes_Total'],
			),
			'limit' => 'int',
			'sort_asc' => 'check',
			'last_active_limit' => 'int',
			'enable_label' => 'check',
			'list_label' => 'text',
		);

		/*
		* The system setup array, the order depends on the $txt array of the select name
		*
		* 'mod_id' - Only used as information
		* 'field' - The members field that should be loaded.  Please don't forget to add mem. before the field names
		* 'order' - What is the field name i need to be sort after
		* 'where' - Here you can add additional where statements
		* 'output_text' - What should be displayed after the avatar and nickname
		*	 - For example if your field is karmaGood 'output_text' => $txt['karma'] . '%karmaGood%';
		* 'output_function' - With this you can add to the $row of the query some information.
		* 'reverse' - On true it change the reverse cause, if not set it will be false :)
		* 'enabled' - true = mod exists or is possible to use :D
		* 'error_msg' => $txt['my_error_msg']; You can insert here what kind of error message should appear if the modification not exists =D
		*/
		$this->sp_topStatsSystem = array(
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
				'name' => 'Likes Received/Given',
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
			'7' => array(
				'name' => 'Likes Totals',
				'field' => 'mem.likes_received, mem.likes_given',
				'order' => 'mem.likes_received',
				'output_text' => $txt['sp_topStatsMember_Likes_Received'] . ':&nbsp;%likes_received% / ' . $txt['sp_topStatsMember_Likes_Given'] . ':&nbsp;%likes_given%',
				'enabled' => !empty($modSettings['likes_enabled']),
				'error_msg' => $txt['sp_likes_is_disabled'],
			),
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
		global $context, $scripturl, $modSettings;

		// Standard Variables
		$type = !empty($parameters['type']) ? $parameters['type'] : 0;
		$limit = !empty($parameters['limit']) ? (int) $parameters['limit'] : 5;
		$sort_asc = !empty($parameters['sort_asc']);

		// Time is in days, but we need seconds
		$last_active_limit = !empty($parameters['last_active_limit']) ? $parameters['last_active_limit'] * 86400 : 0;
		$this->data['enable_label'] = !empty($parameters['enable_label']);
		$this->data['list_label'] = !empty($parameters['list_label']) ? $parameters['list_label'] : '';

		// Setup current block type
		$current_system = $this->sp_topStatsSystem[$type];

		// Possible to output?
		if (empty($current_system['enabled']))
		{
			if (!empty($current_system['error_msg']))
			{
				$this->setTemplate('template_sp_topStatsMember_error');
				$this->data['error_msg'] = $current_system['error_msg'];
			}

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
			{
				$where[] = 'mem.id_member IN (' . $data[4] . ')';
			}
			else
			{
				unset($modSettings[$chache_id]);
			}
		}

		// Last active remove
		if (!empty($last_active_limit))
		{
			$timeLimit = time() - $last_active_limit;
			$where[] = "last_login > $timeLimit";
		}

		if (!empty($current_system['where']))
		{
			$where[] = $current_system['where'];
		}

		if (!empty($where))
		{
			$where = 'WHERE (' . implode(')
				AND (', $where) . ')';
		}
		else
		{
			$where = "";
		}

		// Finally make the query with the parameters we built
		$request = $this->_db->query('', '
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
				'limit' => isset($context['common_stats']['total_members']) && $context['common_stats']['total_members'] > 100 ? ($limit + 5) : $limit,
				'field' => $current_system['field'],
				'where' => $where,
				'order' => $current_system['order'],
				'sort' => ($sort_asc ? 'ASC' : 'DESC'),
			)
		);
		$this->data['members'] = array();
		$count = 1;
		$chache_member_ids = array();
		while ($row = $this->_db->fetch_assoc($request))
		{
			// Collect some to cache data
			$chache_member_ids[$row['id_member']] = $row['id_member'];
			if ($count++ > $limit)
			{
				continue;
			}

			$this->color_ids[$row['id_member']] = $row['id_member'];

			// Setup the row
			$output = '';

			// Prepare some data of the row?
			if (!empty($current_system['output_function']))
			{
				$current_system['output_function']($row);
			}

			if (!empty($current_system['output_text']))
			{
				$output = $current_system['output_text'];
				foreach ($row as $item => $replacewith)
				{
					$output = str_replace('%' . $item . '%', $replacewith, $output);
				}
			}

			$this->data['members'][] = array(
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
		$this->_db->free_result($request);

		// Update the cache, at least around 100 members are needed for a good working version
		if (empty($modSettings['sp_disableChache']) && isset($context['common_stats']['total_members']) && $context['common_stats']['total_members'] > 0 && !empty($chache_member_ids) && count($chache_member_ids) > $limit && empty($modSettings[$chache_id]))
		{
			$toCache = array($type, $limit, ($sort_asc ? 1 : 0), time(), implode(',', $chache_member_ids));
			updateSettings(array($chache_id => implode(';', $toCache)));
		}
		// One time error, if this happens the cache needs an update
		elseif (!empty($modSettings[$chache_id]))
		{
			updateSettings(array($chache_id => '0;0;0;1000;0'));
		}

		// Color the id's
		$this->_colorids();

		// Set the template to use
		$this->setTemplate('template_sp_topStatsMember');
	}

	/**
	 * Provide the color profile id's
	 */
	private function _colorids()
	{
		global $color_profile;

		if (sp_loadColors($this->color_ids) !== false)
		{
			foreach ($this->data['members'] as $k => $p)
			{
				if (!empty($color_profile[$p['id']]['link']))
				{
					$this->data['members'][$k]['link'] = $color_profile[$p['id']]['link'];
				}
			}
		}
	}
}

/**
 * Error template for this block
 *
 * @param mixed[] $data
 */
function template_sp_topStatsMember_error($data)
{
	echo $data['error_msg'];
}

/**
 * Main template for this block
 *
 * @param mixed[] $data
 */
function template_sp_topStatsMember($data)
{
	global $scripturl, $txt;

	// No one found, let them know
	if (empty($data['members']))
	{
		echo '
			', $txt['error_sp_no_members_found'];

		return;
	}

	// Finally, output the block
	echo '
			<table class="sp_fullwidth">';

	if ($data['enable_label'])
	{
		echo '
				<tr>
					<td class="sp_top_poster centertext" colspan="2">
						<strong>', $data['list_label'], '</strong>
					</td>
				</tr>';
	}

	foreach ($data['members'] as $member)
	{
		echo '
				<tr>
					<td class="sp_top_poster centertext">', !empty($member['avatar']['href']) ? '
						<a href="' . $scripturl . '?action=profile;u=' . $member['id'] . '">
							<img src="' . $member['avatar']['href'] . '" alt="' . $member['name'] . '" style="max-width:40px" />
						</a>' : '', '
					</td>
					<td>
						', $member['link'], '<br /><span class="smalltext">', $member['output'], '</span>
					</td>
				</tr>';
	}

	echo '
			</table>';
}