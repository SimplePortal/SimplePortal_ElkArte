<?php

/**
 * @package SimplePortal ElkArte
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
 * Shoutbox controller.
 * This class handles requests for Shoutbox Functionality
 */
class Shoutbox_Controller extends Action_Controller
{
	/**
	 * Need the template for the shoutbox
	 */
	public function pre_dispatch()
	{
		loadTemplate('PortalShoutbox');
		require_once(SUBSDIR . '/PortalShoutbox.subs.php');
	}

	/**
	 * Default method
	 */
	public function action_index()
	{
		// We really only have one choice :P
		$this->action_sportal_shoutbox();
	}

	/**
	 * The Shoutbox ... allows for the adding, editing, deleting and viewing of shouts
	 */
	public function action_sportal_shoutbox()
	{
		global $context, $scripturl, $user_info;

		// ID of the shoutbox we are working on and timestamp
		$shoutbox_id = !empty($_REQUEST['shoutbox_id']) ? (int) $_REQUEST['shoutbox_id'] : 0;
		$request_time = !empty($_REQUEST['time']) ? (int) $_REQUEST['time'] : 0;

		// We need to know which shoutbox this is for/from
		$context['SPortal']['shoutbox'] = sportal_get_shoutbox($shoutbox_id, true, true);

		// Shouting but no one is there to here you
		if (empty($context['SPortal']['shoutbox']))
		{
			if (isset($_REQUEST['xml']))
			{
				obExit(false, false);
			}
			else
			{
				fatal_lang_error('error_sp_shoutbox_not_exist', false);
			}
		}

		// Any warning title for the shoutbox, like Not For Support ;P
		$context['SPortal']['shoutbox']['warning'] = parse_bbc($context['SPortal']['shoutbox']['warning']);

		$can_moderate = allowedTo('sp_admin') || allowedTo('sp_manage_shoutbox');
		if (!$can_moderate && !empty($context['SPortal']['shoutbox']['moderator_groups']))
		{
			$can_moderate = count(array_intersect($user_info['groups'], $context['SPortal']['shoutbox']['moderator_groups'])) > 0;
		}

		// Adding a shout
		if (!empty($_REQUEST['shout']))
		{
			// Pretty basic
			is_not_guest();
			checkSession('request');

			// If you are not flooding the system, add the shout to the box
			if (!($flood = sp_prevent_flood('spsbp', false)))
			{
				require_once(SUBSDIR . '/Post.subs.php');

				$_REQUEST['shout'] = Util::htmlspecialchars(trim($_REQUEST['shout']));
				preparsecode($_REQUEST['shout']);

				if (!empty($_REQUEST['shout']))
				{
					sportal_create_shout($context['SPortal']['shoutbox'], $_REQUEST['shout']);
				}
			}
			else
			{
				$context['SPortal']['shoutbox']['warning'] = $flood;
			}
		}

		// Removing a shout, regret saying that do you :P
		if (!empty($_REQUEST['delete']))
		{
			checkSession('request');

			if (!$can_moderate)
			{
				fatal_lang_error('error_sp_cannot_shoutbox_moderate', false);
			}

			$delete = (int) $_REQUEST['delete'];

			if (!empty($delete))
			{
				sportal_delete_shout($shoutbox_id, $delete);
			}
		}

		// Responding to an ajax request
		if (isset($_REQUEST['xml']))
		{
			$shout_parameters = array(
				'limit' => $context['SPortal']['shoutbox']['num_show'],
				'bbc' => $context['SPortal']['shoutbox']['allowed_bbc'],
				'reverse' => $context['SPortal']['shoutbox']['reverse'],
				'cache' => $context['SPortal']['shoutbox']['caching'],
				'can_moderate' => $can_moderate,
			);

			// Get all the shouts for this box
			$context['SPortal']['shouts'] = sportal_get_shouts($shoutbox_id, $shout_parameters);

			// Return a clean xml response
			Template_Layers::getInstance()->removeAll();
			$context['sub_template'] = 'shoutbox_xml';
			$context['SPortal']['updated'] = empty($context['SPortal']['shoutbox']['last_update']) || $context['SPortal']['shoutbox']['last_update'] > $request_time;

			return;
		}

		// Show all the shouts in this box
		$total_shouts = sportal_get_shoutbox_count($shoutbox_id);

		$context['per_page'] = $context['SPortal']['shoutbox']['num_show'];
		$context['start'] = !empty($_REQUEST['start']) ? (int) $_REQUEST['start'] : 0;
		$context['page_index'] = constructPageIndex($scripturl . '?action=shoutbox;shoutbox_id=' . $shoutbox_id, $context['start'], $total_shouts, $context['per_page']);

		$shout_parameters = array(
			'start' => $context['start'],
			'limit' => $context['per_page'],
			'bbc' => $context['SPortal']['shoutbox']['allowed_bbc'],
			'cache' => $context['SPortal']['shoutbox']['caching'],
			'can_moderate' => $can_moderate,
		);

		$context['SPortal']['shouts_history'] = sportal_get_shouts($shoutbox_id, $shout_parameters);
		$context['SPortal']['shoutbox_id'] = $shoutbox_id;
		$context['sub_template'] = 'shoutbox_all';
		$context['page_title'] = $context['SPortal']['shoutbox']['name'];
	}
}