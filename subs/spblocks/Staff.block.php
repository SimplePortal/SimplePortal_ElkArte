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
 * Staff Block, show the list of forum staff members
 *
 * @param mixed[] $parameters
 *        'lmod' => set to include local moderators as well
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
class Staff_Block extends SP_Abstract_Block
{
	protected $color_ids = array();

	/**
	 * Constructor, used to define block parameters
	 *
	 * @param Database|null $db
	 */
	public function __construct($db = null)
	{
		$this->block_parameters = array(
			'lmod' => 'check',
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
		global $scripturl, $color_profile;

		require_once(SUBSDIR . '/Members.subs.php');

		// Including local board moderators
		if (empty($parameters['lmod']))
		{
			$request = $this->_db->query('', '
				SELECT
					id_member
				FROM {db_prefix}moderators',
				array()
			);
			$local_mods = array();
			while ($row = $this->_db->fetch_assoc($request))
				$local_mods[$row['id_member']] = $row['id_member'];
			$this->_db->free_result($request);

			if (count($local_mods) > 10)
			{
				$local_mods = array();
			}
		}
		else
		{
			$local_mods = array();
		}

		// Admins and global moderator list
		$global_mods = membersAllowedTo('moderate_board', 0);
		$admins = membersAllowedTo('admin_forum');

		// You only get one listing, highest authority
		$all_staff = array_merge($local_mods, $global_mods, $admins);
		$all_staff = array_unique($all_staff);

		$request = $this->_db->query('', '
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
		$this->data['staff_list'] = array();
		while ($row = $this->_db->fetch_assoc($request))
		{
			$this->color_ids[$row['id_member']] = $row['id_member'];

			if (in_array($row['id_member'], $admins))
			{
				$row['type'] = 1;
			}
			elseif (in_array($row['id_member'], $global_mods))
			{
				$row['type'] = 2;
			}
			else
			{
				$row['type'] = 3;
			}

			$this->data['staff_list'][$row['type'] . '-' . $row['id_member']] = array(
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
		$this->_db->free_result($request);

		ksort($this->data['staff_list']);
		$this->data['staff_count'] = count($this->data['staff_list']);
		$this->data['icons'] = array(1 => 'admin', 'gmod', 'lmod');

		$this->_colorids();
	}

	/**
	 * Provide the color profile id's
	 */
	private function _colorids()
	{
		global $color_profile;

		if (sp_loadColors($this->color_ids) !== false)
		{
			foreach ($this->data['staff_list'] as $k => $p)
			{
				if (!empty($color_profile[$p['id']]['link']))
				{
					$this->data['staff_list'][$k]['link'] = $color_profile[$p['id']]['link'];
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
function template_sp_staff($data)
{
	global $scripturl;

	echo '
		<table class="sp_fullwidth">';

	$count = 0;
	foreach ($data['staff_list'] as $staff)
	{
		echo '
			<tr>
				<td class="sp_staff centertext">', !empty($staff['avatar']['href']) ? '
					<a href="' . $scripturl . '?action=profile;u=' . $staff['id'] . '">
						<img src="' . $staff['avatar']['href'] . '" alt="' . $staff['name'] . '" style="max-width:40px" />
					</a>' : '', '
				</td>
				<td ', sp_embed_class($data['icons'][$staff['type']], '', 'sp_staff_info' . $data['staff_count'] != ++$count ? ' sp_staff_divider' : ''), '>',
					$staff['link'], '<br />', $staff['group'], '
				</td>
			</tr>';
	}

	echo '
		</table>';
}