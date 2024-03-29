<?php

/**
 * @package SimplePortal ElkArte
 *
 * @author SimplePortal Team
 * @copyright 2015-2023 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.2
 */

if (file_exists(__DIR__ . '/SSI.php') && !defined('ELK'))
{
	require_once(__DIR__ . '/SSI.php');
}
elseif (!defined('ELK'))
{
	exit('<b>Error:</b> Cannot install - please verify you put this in the same place as ELK\'s index.php.');
}

global $modSettings, $package_cache;

$defaults = array(
	'sp_portal_mode' => 1,
	'sp_disableForumRedirect' => 1,
	'showleft' => 1,
	'showright' => 1,
	'leftwidth' => '225px',
	'rightwidth' => '225px',
	'sp_adminIntegrationHide' => 1,
	'sp_resize_images' => 1,
	'sp_articles_index_per_page' => 5,
	'sp_articles_index_total' => 20,
	'sp_articles_per_page' => 10,
	'sp_articles_comments_per_page' => 20,
	'sp_articles_attachment_dir' => BOARDDIR . '/sp_attach',
);

$updates = array(
	'sp_version' => '1.0.3',
	'front_page' => 'PortalMain_Controller',
	'admin_features' => $modSettings['admin_features'] . ',pt'
);

foreach ($defaults as $index => $value)
{
	if (!isset($modSettings[$index]))
	{
		$updates[$index] = $value;
	}
}

updateSettings($updates);

// Enable the core feature by default on install
Hooks::instance()->enableIntegration('Portal_Integrate');

$standalone_file = BOARDDIR . '/PortalStandalone.php';

if (isset($package_cache[$standalone_file]))
{
	$package_cache[$standalone_file] = str_replace('full/path/to/forum', BOARDDIR, $package_cache[$standalone_file]);
}
elseif (file_exists($standalone_file))
{
	$current_data = file_get_contents($standalone_file);
	if (strpos($current_data, 'full/path/to/forum') !== false)
	{
		$fp = fopen($standalone_file, 'wb+');
		fwrite($fp, str_replace('full/path/to/forum', BOARDDIR, $current_data));
		fclose($fp);
	}
}

if (ELK === 'SSI')
{
	echo 'Settings changes were carried out successfully.';
}
