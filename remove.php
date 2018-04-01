<?php

/**
 * @package SimplePortal ElkArte
 *
 * @author SimplePortal Team
 * @copyright 2015-2017 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.0 RC1
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
);

foreach ($defaults as $index => $value)
{
	$updates[$index] = $value;
}

updateSettings($updates);

if (ELK === 'SSI')
{
	echo 'Settings changes were carried out successfully.';
}
