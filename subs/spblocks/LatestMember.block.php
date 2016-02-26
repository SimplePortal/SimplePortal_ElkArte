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
 * Latest member block, shows name and join date for X latest members
 *
 * @param mixed[] $parameters
 *        'limit' => number of members to show
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
class Latest_Member_Block extends SP_Abstract_Block
{
	protected $colorids = array();

	/**
	 * Constructor, used to define block parameters
	 *
	 * @param Database|null $db
	 */
	public function __construct($db = null)
	{
		$this->block_parameters = array(
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
		global $scripturl, $color_profile;

		// Load in the latest members
		require_once(SUBSDIR . '/Members.subs.php');
		$limit = !empty($parameters['limit']) ? (int) $parameters['limit'] : 5;
		$rows = recentMembers($limit);

		// Get them ready the template
		$this->data['members'] = array();

		foreach ($rows as $row)
		{
			if (!empty($row['id_member']))
			{
				$this->colorids[$row['id_member']] = $row['id_member'];
			}

			$this->data['members'][] = array(
				'id' => $row['id_member'],
				'name' => $row['real_name'],
				'href' => $scripturl . '?action=profile;u=' . $row['id_member'],
				'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>',
				'date' => standardTime($row['date_registered'], '%d %b'),
			);
		}

		// Using member profile colors
		$this->_colorids();

		$this->setTemplate('template_sp_latestMember');
	}

	/**
	 * Provide the color profile id's
	 */
	private function _colorids()
	{
		global $color_profile;

		if (sp_loadColors($this->colorids) !== false)
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
 * Main template for this block
 *
 * @param mixed[] $data
 */
function template_sp_latestMember($data)
{
	global $txt;

	// No recent members, suppose it could happen
	if (empty($data['members']))
	{
		echo '
			', $txt['error_sp_no_members_found'];

		return;
	}

	echo '
			<ul class="sp_list">';

	foreach ($data['members'] as $member)
	{
		echo '
				<li ', sp_embed_class('dot'), '>', $member['link'], ' - <span class="smalltext">', $member['date'], '</span></li>';
	}

	echo '
			</ul>';
}