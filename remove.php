<?php

/**
 * @package SimplePortal ElkArte
 *
 * @author SimplePortal Team
 * @copyright 2015-2021 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.0
 */

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('ELK'))
{
	require_once(dirname(__FILE__) . '/SSI.php');
}
elseif (!defined('ELK'))
{
	exit('<b>Error:</b> Cannot uninstall - please verify you put this in the same place as ELK\'s index.php.');
}

global $modSettings, $package_cache;

$defaults = array(
	'sp_portal_mode' => 0,
	'front_page' => '',
	'sp_disable' => 1,
);

foreach ($defaults as $index => $value)
{
	$updates[$index] = $value;
}

if (!empty($modSettings['admin_features']))
{
	$updates['admin_features'] = str_replace(',pt', '', $modSettings['admin_features']);
}
updateSettings($updates);
Hooks::instance()->disableIntegration('Portal_Integrate');

// And let ElkArte know we have been mucking about so the cache is reset
updateSettings(array('settings_updated' => time()));

if (ELK === 'SSI')
{
	echo 'Settings changes were carried out successfully.';
}
