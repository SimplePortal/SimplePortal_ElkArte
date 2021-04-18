<?php

/**
 * @package SimplePortal ElkArte
 *
 * @author SimplePortal Team
 * @copyright 2015-2017 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.0 RC1
 */


/**
 * Class Portal_Integrate
 */
class Portal_Integrate
{
	/**
	 * Register SimplePortal hooks to the system
	 *
	 * This function is only called after the SimplePortal core feature is enabled.  Enabling the
	 * core feature sets SPortal_Integrate as an enabled integration (via enableIntegration())
	 *
	 * The Hooks class makes static calls to ::register and ::settingsRegister for each class that
	 * was saved with enableIntegration() (stored in $modSettings['autoload_integrate'])
	 *
	 * @return array
	 */
	public static function register()
	{
		return array(
			array('integrate_init_theme', 'ManageSPortalModule_Controller::sp_integrate_init_theme'),
			array('integrate_current_action', 'ManageSPortalModule_Controller::sp_integrate_current_action'),
			array('integrate_action_boardindex_after', 'ManageSPortalModule_Controller::sp_integrate_boardindex'),
			array('integrate_actions', 'ManageSPortalModule_Controller::sp_integrate_actions'),
			array('integrate_whos_online', 'ManageSPortalModule_Controller::sp_integrate_whos_online'),
			array('integrate_action_frontpage', 'ManageSPortalModule_Controller::sp_integrate_frontpage'),
			array('integrate_quickhelp', 'ManageSPortalModule_Controller::sp_integrate_quickhelp'),
			array('integrate_buffer', 'ManageSPortalModule_Controller::sp_integrate_buffer'),
			array('integrate_menu_buttons', 'ManageSPortalModule_Controller::sp_integrate_menu_buttons'),
			array('integrate_redirect', 'ManageSPortalModule_Controller::sp_integrate_redirect'),
			array('integrate_sa_xmlhttp', 'ManageSPortalModule_Controller::sp_integrate_xmlhttp'),
			array('integrate_pre_bbc_parser', 'ManageSPortalModule_Controller::sp_integrate_pre_parsebbc'),
			array('integrate_setup_allow', 'ManageSPortalModule_Controller::sp_integrate_setup_allow'),
			array('integrate_additional_bbc', 'ManageSPortalModule_Controller::sp_integrate_additional_bbc'),
		);
	}

	/**
	 * Register ACP config hooks for setting values
	 *
	 * @return array
	 */
	public static function settingsRegister()
	{
		// $hook, $function, $file
		return array(
			array('integrate_admin_areas', 'ManageSPortalModule_Controller::sp_integrate_admin_areas'),
			array('integrate_load_permissions', 'ManageSPortalModule_Controller::sp_integrate_load_permissions'),
			array('integrate_load_illegal_guest_permissions', 'ManageSPortalModule_Controller::sp_integrate_load_illegal_guest_permissions'),
		);
	}
}
