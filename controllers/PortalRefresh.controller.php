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
 * Refresh controller.
 * This class handles requests for block updates
 */
class PortalRefresh_Controller extends Action_Controller
{
	protected $_request = true;
	protected $_block_id;

	/**
	 * This method is executed before any action handler.
	 * Loads common things for all methods
	 */
	public function pre_dispatch()
	{
		loadLanguage('SPortal');
		require_once(SUBSDIR . '/Portal.subs.php');
		require_once(SUBSDIR . '/spblocks/SPAbstractBlock.class.php');

		// Not running via ssi then we need to get SSI for many block functions
		if (ELK !== 'SSI')
		{
			require_once(BOARDDIR . '/SSI.php');
		}

		sportal_load_permissions();
	}

	/**
	 * Default method
	 */
	public function action_index()
	{
		// Where do you want to go today? Well this should never be called
		obExit(false);
	}

	/**
	 * Refresh the who's online block
	 */
	public function action_whos_api()
	{
		$this->_check_access();
		$this->_process();
	}

	/**
	 * Refresh the recent block
	 */
	public function action_recent_api()
	{
		$this->_check_access();
		$this->_process();
	}

	/**
	 * Refresh the boardstats block
	 */
	public function action_boardstats_api()
	{
		$this->_check_access();
		$this->_process();
	}

	/**
	 * Standard block processing, instance, setup, render, return
	 */
	private function _process()
	{
		// Details about the block to refresh, check that its enabled, and permissions
		$block_details = getBlockInfo(null, $this->_block_id, true, false, true);
		$block_details = array_shift($block_details);

		// Load the block and render it
		$block = sp_instantiate_block($block_details['type'], $this->_block_id);
		$block->setup($block_details['parameters'], $this->_block_id);
		$block->render();

		// Render outputs, jquery .load is expecting just that.
		obExit(false);
	}

	/**
	 * Basic checks to ensure this is a valid request, for a valid source
	 */
	private function _check_access()
	{
		global $context, $modSettings, $user_info, $settings, $maintenance;

		// Not for guests etc
		if ($user_info['is_guest'] || $user_info['id'] == 0 || $user_info['possibly_robot'])
			$this->_request = false;

		// And not if the site or portal is disabled / maintenance / etc
		if (!empty($modSettings['sp_disableMobile']) || !empty($settings['disable_sp']) || empty($modSettings['sp_portal_mode']) || ((!empty($modSettings['sp_maintenance']) || !empty($maintenance)) || (empty($modSettings['allow_guestAccess']) && $context['user']['is_guest'])))
			$this->_request = false;

		// Refreshing what, nothing ?
		if (empty($_POST['block']))
			$this->_request = false;

 		// Not this time then
		if (!$this->_request)
			obExit(false);

		// You should have a valid session with the request as well
		checkSession();

		// Then lets see if they can refresh this block
		$this->_block_id = (int) $_POST['block'];
	}
}