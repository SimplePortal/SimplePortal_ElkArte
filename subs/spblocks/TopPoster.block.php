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
 * Top Posters block, shows the top posters on the site, with avatar and name
 *
 * @param mixed[] $parameters
 *        'limit' => number of top posters to show
 *        'type' => period to determine the top poster, 0 all time, 1 today, 2 week, 3 month
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
class Top_Poster_Block extends SP_Abstract_Block
{
	/**
	 * @var array
	 */
	protected $color_ids = array();

	/**
	 * Constructor, used to define block parameters
	 *
	 * @param Database|null $db
	 */
	public function __construct($db = null)
	{
		$this->block_parameters = array(
			'limit' => 'int',
			'type' => 'select',
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
		global $txt, $scripturl;

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
			{
				$start_time = mktime(0, 0, 0, date("n"), date("j"), date("Y")) - (date("N") * 3600 * 24);
			}
			// This month
			elseif ($type == 3)
			{
				$months = array(1 => 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
				$start_time = mktime(0, 0, 0, date("n"), date("j"), date("Y")) - (3600 * 24 * $months[(int) date("m", time())]);
			}

			$start_time = forum_time(false, $start_time);

			$request = $this->_db->query('', '
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
			$request = $this->_db->query('', '
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
		$this->data['members'] = array();
		while ($row = $this->_db->fetch_assoc($request))
		{
			if (!empty($row['id_member']))
			{
				$this->color_ids[$row['id_member']] = $row['id_member'];
			}

			// Load the member data
			$this->data['members'][] = array(
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
		$this->_db->free_result($request);

		// Profile colors?
		$this->_color_ids();

		if (empty($this->data['members']))
		{
			$this->data['error_msg'] = $txt['error_sp_no_members_found'];
			$this->setTemplate('template_sp_topPoster_error');
		}
		else
		{
			$this->setTemplate('template_sp_topPoster');
		}
	}

	/**
	 * Provide the color profile id's
	 */
	private function _color_ids()
	{
		global $color_profile;

		if (sp_loadColors($this->color_ids) !== false)
		{

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
 * Main template for this block
 *
 * @param mixed[] $data
 */
function template_sp_topPoster_error($data)
{
	echo $data['error_msg'];
}

/**
 * Main template for this block
 *
 * @param mixed[] $data
 */
function template_sp_topPoster($data)
{
	global $scripturl, $txt;

	// And output the block
	echo '
		<table class="sp_fullwidth">';

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
					', $member['link'], '<br />
					<span class="smalltext">', $member['posts'], ' ', $txt['posts'], '</span>
				</td>
			</tr>';
	}

	echo '
		</table>';
}