<?php

/**
 * @package SimplePortal
 *
 * @author SimplePortal Team
 * @copyright 2013 SimplePortal Team
 * @license BSD 3-clause
 *
 * @version 2.4
 */

if (!defined('ELK'))
	die('No access...');

/**
 * integration hook integrate_actions
 * Called from dispatcher.class, used to add in custom actions
 *
 * @param array $actions
 */
function sp_integrate_actions(&$actions)
{
	global $context;

	if (!empty($context['disable_sp']))
		return;

	$actions['forum'] = array('BoardIndex.php', 'BoardIndex');
	$actions['portal'] = array('PortalMain.php', 'sportal_main');
}

/**
 * Admin hook, integrate_admin_areas, called from Menu.subs
 * adds the admin menu
 *
 * @param array $admin_areas
 */
function sp_integrate_admin_areas(&$admin_areas)
{
	global $txt;

	loadLanguage('SPortalAdmin');
	loadLanguage('SPortal');

	$temp = $admin_areas;
	$admin_areas = array();

	foreach ($temp as $area => $data)
	{
		$admin_areas[$area] = $data;

		if ($area == 'layout')
		{
			$admin_areas['portal'] = array(
				'title' => $txt['sp-adminCatTitle'],
				'permission' => array('sp_admin', 'sp_manage_settings', 'sp_manage_blocks', 'sp_manage_articles', 'sp_manage_pages', 'sp_manage_shoutbox'),
				'areas' => array(
					'portalconfig' => array(
						'label' => $txt['sp-adminConfiguration'],
						'file' => 'PortalAdminMain.controller.php',
						'controller' => 'ManagePortalConfig_Controller',
						'function' => 'action_index',
						'icon' => 'configuration.png',
						'permission' => array('sp_admin', 'sp_manage_settings'),
						'subsections' => array(
							'information' => array($txt['sp-info_title']),
							'generalsettings' => array($txt['sp-adminGeneralSettingsName']),
							'blocksettings' => array($txt['sp-adminBlockSettingsName']),
							'articlesettings' => array($txt['sp-adminArticleSettingsName']),
						),
					),
					'portalblocks' => array(
						'label' => $txt['sp-blocksBlocks'],
						'file' => 'PortalAdminBlocks.controller.php',
						'controller' => 'ManagePortalBlocks_Controller',
						'function' => 'action_index',
						'icon' => 'blocks.png',
						'permission' => array('sp_admin', 'sp_manage_blocks'),
						'subsections' => array(
							'list' => array($txt['sp-adminBlockListName']),
							'add' => array($txt['sp-adminBlockAddName']),
							'header' => array($txt['sp-positionHeader']),
							'left' => array($txt['sp-positionLeft']),
							'top' => array($txt['sp-positionTop']),
							'bottom' => array($txt['sp-positionBottom']),
							'right' => array($txt['sp-positionRight']),
							'footer' => array($txt['sp-positionFooter']),
						),
					),
					'portalarticles' => array(
						'label' => $txt['sp_admin_articles_title'],
						'file' => 'PortalAdminArticles.controller.php',
						'controller' => 'ManagePortalArticles_Controller',
						'function' => 'action_index',
						'icon' => 'articles.png',
						'permission' => array('sp_admin', 'sp_manage_articles'),
						'subsections' => array(
							'list' => array($txt['sp_admin_articles_list']),
							'add' => array($txt['sp_admin_articles_add']),
						),
					),
					'portalcategories' => array(
						'label' => $txt['sp_admin_categories_title'],
						'file' => 'PortalAdminCategories.controller.php',
						'controller' => 'ManagePortalCategories_Controller',
						'function' => 'action_index',
						'icon' => 'categories.png',
						'permission' => array('sp_admin', 'sp_manage_articles'),
						'subsections' => array(
							'list' => array($txt['sp_admin_categories_list']),
							'add' => array($txt['sp_admin_categories_add']),
						),
					),
					'portalpages' => array(
						'label' => $txt['sp_admin_pages_title'],
						'file' => 'PortalAdminPages.controller.php',
						'controller' => 'ManagePortalPages_Controller',
						'function' => 'action_index',
						'icon' => 'pages.png',
						'permission' => array('sp_admin', 'sp_manage_pages'),
						'subsections' => array(
							'list' => array($txt['sp_admin_pages_list']),
							'add' => array($txt['sp_admin_pages_add']),
						),
					),
					'portalshoutbox' => array(
						'label' => $txt['sp_admin_shoutbox_title'],
						'file' => 'PortalAdminShoutbox.controller.php',
						'controller' => 'ManagePortalShoutbox_Controller',
						'function' => 'action_index',
						'icon' => 'shoutbox.png',
						'permission' => array('sp_admin', 'sp_manage_shoutbox'),
						'subsections' => array(
							'list' => array($txt['sp_admin_shoutbox_list']),
							'add' => array($txt['sp_admin_shoutbox_add']),
						),
					),
				),
			);
		}
	}
}

/**
 * Permissions hook, integrate_load_permissions, called from ManagePermissions.php
 * used to add new permisssions
 *
 * @param array $permissionGroups
 * @param array $permissionList
 * @param array $leftPermissionGroups
 * @param array $hiddenPermissions
 * @param array $relabelPermissions
 */
function sp_integrate_load_permissions(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions)
{
	$permissionList['membergroup']['sp_admin'] = array(false, 'sp', 'sp');
	$permissionList['membergroup']['sp_manage_settings'] = array(false, 'sp', 'sp');
	$permissionList['membergroup']['sp_manage_blocks'] = array(false, 'sp', 'sp');
	$permissionList['membergroup']['sp_manage_articles'] = array(false, 'sp', 'sp');
	$permissionList['membergroup']['sp_manage_pages'] = array(false, 'sp', 'sp');
	$permissionList['membergroup']['sp_manage_shoutbox'] = array(false, 'sp', 'sp');

	$permissionGroups['membergroup'][] = 'sp';

	$leftPermissionGroups[] = 'sp';
}

/**
 * Whos online hook, integrate_whos_online, called from who.subs
 * translates custom actions to allow show what area a user is in
 *
 * @param type $actions
 */
function sp_integrate_whos_online($actions)
{
	global $scripturl, $modSettings, $txt;

	$db = database();
	$data = null;
	loadLanguage('SPortal');

	// This may miss if its needed for the first action ... need to improve the hook or find another location
	if ($modSettings['sp_portal_mode'] == 1)
	{
		$txt['who_index'] = sprintf($txt['sp_who_index'], $scripturl);
		$txt['whoall_forum'] = sprintf($txt['sp_who_forum'], $scripturl);
	}

	// If its a portal action, lets check it out.
	if (isset($actions['page']))
	{
		$data = $txt['who_hidden'];
		$page_id = $actions['page'];

		// Due to the way the who action hook works, this is done one by one instead of batch
		// Arrays are left in place for the future, meaning a new hook is needed
		$numeric_ids = array();
		$string_ids = array();
		$page_where = array();

		if (is_numeric($page_id))
			$numeric_ids[] = (int) $page_id;
		else
			$string_ids[] = $page_id;

		if (!empty($numeric_ids))
			$page_where[] = 'id_page IN ({array_int:numeric_ids})';

		if (!empty($string_ids))
			$page_where[] = 'namespace IN ({array_string:string_ids})';

		$result = $db->query('', '
			SELECT id_page, namespace, title, permission_set, groups_allowed, groups_denied
			FROM {db_prefix}sp_pages
			WHERE ' . implode(' OR ', $page_where) . '
			LIMIT {int:limit}',
			array(
				'numeric_ids' => $numeric_ids,
				'string_ids' => $string_ids,
				'limit' => count($page_ids),
			)
		);
		$page_data = array();
		while ($row = $db->fetch_assoc($result))
		{
			if (!sp_allowed_to('page', $row['id_page'], $row['permission_set'], $row['groups_allowed'], $row['groups_denied']))
				continue;

			$page_data[] = array(
				'id' => $row['id_page'],
				'namespace' => $row['namespace'],
				'title' => $row['title'],
			);
		}
		$db->free_result($result);

		if (!empty($page_data))
		{
			foreach ($page_data as $page)
			{
				if (isset($page_ids[$page['id']]))
					foreach ($page_ids[$page['id']] as $k => $session_text)
						$data = sprintf($session_text, $page['id'], censorText($page['title']), $scripturl);

				if (isset($page_ids[$page['namespace']]))
					foreach ($page_ids[$page['namespace']] as $k => $session_text)
						$data = sprintf($session_text, $page['namespace'], censorText($page['title']), $scripturl);
			}
		}
	}

	return $data;
}

/**
 * Frontpage hook, integrate_frontpage, called from the dispatcher,
 * used to replace the default action with a new one
 *
 * @param array $default_action
 */
function sp_integrate_frontpage(&$default_action)
{
	global $modSettings, $context;

	// Portal is active
	if (empty($context['disable_sp']))
	{
		$file = null;
		$function = null;

		// Any actions we need to handle with the portal, set up the action here.
		if (empty($_GET['page']) && empty($_GET['article']) && empty($_GET['category']) && $modSettings['sp_portal_mode'] == 1)
		{
			$file = SOURCEDIR . '/PortalMain.php';
			$function = 'sportal_main';
		}
		elseif (!empty($_GET['page']))
		{
			$file = SOURCEDIR . '/PortalPages.php';
			$function = 'sportal_page';
		}
		elseif (!empty($_GET['article']))
		{
			$file = $sourcedir . '/PortalArticles.php';
			$function = 'sportal_article';
		}
		elseif (!empty($_GET['category']))
		{
			$file = $sourcedir . '/PortalCategories.php';
			$function = 'sportal_category';
		}

		// Something portal-ish, then set the new action
		if (isset($file, $function))
		{
			$default_action = array(
				'file' => $file,
				'function' => $function
			);
		}
	}

	return;
}

/**
 * Help hook, integrate_quickhelp, called from help.controller.php
 * Used to add in additional help lanaguages for use in the admin quickhelp
 */
function sp_integrate_quickhelp()
{
	require_once (SUBSDIR . '/Portal.subs.php');

	// Load the Simple Portal Help file.
	loadLanguage('SPortalHelp', sp_languageSelect('SPortalHelp'));
}

/**
 * Integration hook integrate_buffer, called from Subs.php
 * Used to modify the output buffer before its sent
 *
 * @param string $tourniquet
 */
function sp_integrate_buffer($tourniquet)
{
	global $sportal_version, $context, $modSettings;

	$fix = str_replace('{version}', $sportal_version, '<a href="http://www.simpleportal.net/" target="_blank" class="new_win">SimplePortal {version} &copy; 2008-2013, SimplePortal</a>');

	if ((ELK == 'SSI' && empty($context['standalone'])) || empty($context['template_layers']) || WIRELESS || empty($modSettings['sp_portal_mode']) || strpos($tourniquet, $fix) !== false)
		return $tourniquet;

	// Append our cp notice at the end of the line
	$finds = array(
		'ElkArte &copy; 2013</a>',
	);
	$replaces = array(
		'ElkArte &copy; 2013</a>' . $fix,
	);

	$tourniquet = str_replace($finds, $replaces, $tourniquet);

	// Can't find it for some reason so we add it at the end
	if (strpos($tourniquet, $fix) === false)
	{
		$fix = '<div style="text-align: center; width: 100%; font-size: x-small; margin-bottom: 5px;">' . $fix . '</div></body></html>';
		$tourniquet = preg_replace('~</body>\s*</html>~', $fix, $tourniquet);
	}

	return $tourniquet;
}

/**
 * Menu Button hook, integrate_menu_buttons, called from subs.php
 * used to add top menu buttons
 *
 * @param array $buttons
 */
function sp_integrate_menu_buttons(&$buttons)
{
	global $txt, $scripturl, $modSettings;

	loadLanguage('SPortal');

	// Where do we want to place our button
	$insert_after = 'home';
	$counter = 0;

	// find the location in the buttons array
	foreach ($buttons as $area => $dummy)
	{
		if (++$counter && $area == $insert_after)
			break;
	}

	// Define the new menu item(s)
	sp_array_insert($buttons, 'home', array(
		'forum' => array(
			'title' => empty($txt['sp-forum']) ? 'Forum' : $txt['sp-forum'],
			'href' => $scripturl . ($modSettings['sp_portal_mode'] == 1 && empty($context['disable_sp']) ? '?action=forum' : ''),
			'show' => in_array($modSettings['sp_portal_mode'], array(1, 3)) && empty($context['disable_sp']),
			'sub_buttons' => array(
			),
		),
	), 'after');
}

/**
 * Helper function to insert a menu
 *
 * @param array $input the array we will insert to
 * @param string $key the key in the array
 * @param array $insert the data to add before or after the above key
 * @param string $where adding before or after
 * @param bool $strict
 */
function sp_array_insert(&$input, $key, $insert, $where = 'before', $strict = false)
{
	$position = array_search($key, array_keys($input), $strict);

	// If the key is not found, just insert it at the end
	if ($position === false)
	{
		$input = array_merge($input, $insert);
		return;
	}

	if ($where === 'after')
		$position += 1;

	// Insert as first
	if ($position === 0)
		$input = array_merge($insert, $input);
	else
		$input = array_merge(array_slice($input, 0, $position), $insert, array_slice($input, $position));
}