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
 * Admin info block
 *
 * @param mixed[] $parameters not used in this block
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
class Admin_Info_Block extends SP_Abstract_Block
{
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
		global $user_info, $modSettings;

		$this->data['admin'] = array();
		$this->data['moderation'] = array();

		// Only admins/moderators, no matter what the block permissions say
		if ($user_info['is_admin'] || allowedTo('moderate_forum'))
		{
			// Function to get the stats
			require_once(SUBSDIR . '/Moderation.subs.php');
			require_once(SUBSDIR . '/Error.subs.php');
			require_once(SUBSDIR . '/Members.subs.php');

			// Moderation totals
			$totals = loadModeratorMenuCounts();

			// The admin may want to know about errors and users waiting activation
			if ($user_info['is_admin'])
			{
				$this->data['admin']['errors'] = numErrors(null);

				$activation_numbers = countInactiveMembers();
				$this->data['awaiting_activation'] = 0;
				foreach ($activation_numbers as $activation_type => $total_members)
				{
					if (in_array($activation_type, array(0, 2)))
					{
						$this->data['admin']['awaiting_activation'] += $total_members;
					}
				}
			}

			// Do they have board approval?
			$approve_boards = !empty($user_info['mod_cache']['ap']) ? $user_info['mod_cache']['ap'] : boardsAllowedTo('approve_posts');

			// Any posts / topics / attachments awaiting approval
			if ($modSettings['postmod_active'] && !empty($approve_boards))
			{
				$this->data['moderation']['topics'] = $totals['topics'];
				$this->data['moderation']['posts'] = $totals['posts'];
				$this->data['moderation']['attachments'] = $totals['attachments'];
			}

			// Open moderation reports
			if (!empty($user_info['mod_cache']) && $user_info['mod_cache']['bq'] != '0=1')
			{
				$this->data['moderation']['reports'] = $totals['reports'];
			}

			// Failed emails
			if (!empty($modSettings['maillist_enabled']) && allowedTo('approve_emails'))
			{
				$this->data['moderation']['emailmod'] = $totals['emailmod'];
			}

			// Pending group requests
			if (!empty($user_info['mod_cache']) && $user_info['mod_cache']['gq'] != '0=1')
			{
				$this->data['moderation']['groupreq'] = $totals['groupreq'];
			}

			// Members waiting approval (3,4,5 codes)
			if (allowedTo('moderate_forum') && ((!empty($modSettings['registration_method']) && $modSettings['registration_method'] == 2) || !empty($modSettings['approveAccountDeletion'])))
			{
				$this->data['moderation']['memberreq'] = $totals['memberreq'];
			}
		}

		$this->setTemplate('template_sp_admininfo');
	}
}

/**
 * Main template for this block
 *
 * @param mixed[] $data
 */
function template_sp_admininfo($data)
{
	global $txt;

	loadLanguage('ModerationCenter');

	echo '
		<div>
			<ul class="sp_list">';

	foreach ($data['admin'] as $area => $count)
	{
		echo '
				<li ', sp_embed_class('dot'), '>
					', $txt['sp_admincount_' . $area], ': ', $count, '
				</li>';
	}

	echo
			'</ul>
			<strong>', $txt['sp_admincount_approval'], '</strong>
			<ul class="sp_list">';

	foreach ($data['moderation'] as $area => $count)
	{
		echo '
				<li ', sp_embed_class('dot'), '>
					', $txt['sp_admincount_' . $area], ': ', $count, '
				</li>';
	}

	echo '
			</ul>
		</div>';
}