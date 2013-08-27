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
 * Admin hook, integrate_admin_areas, called from Admin.php
 * adds the admin menu
 *
 * @param array $admin_areas
 */
function sp_integrate_admin_areas(&$admin_areas)
{
	global $txt;

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
						'file' => 'ADMINDIR/PortalAdminMain.php',
						'function' => 'sportal_admin_config_main',
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
						'file' => 'ADMINDIR/PortalAdminBlocks.php',
						'function' => 'sportal_admin_blocks_main',
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
						'file' => 'ADMINDIR/PortalAdminArticles.php',
						'function' => 'sportal_admin_articles_main',
						'icon' => 'articles.png',
						'permission' => array('sp_admin', 'sp_manage_articles'),
						'subsections' => array(
							'list' => array($txt['sp_admin_articles_list']),
							'add' => array($txt['sp_admin_articles_add']),
						),
					),
					'portalcategories' => array(
						'label' => $txt['sp_admin_categories_title'],
						'file' => 'ADMINDIR/PortalAdminCategories.php',
						'function' => 'sportal_admin_categories_main',
						'icon' => 'categories.png',
						'permission' => array('sp_admin', 'sp_manage_articles'),
						'subsections' => array(
							'list' => array($txt['sp_admin_categories_list']),
							'add' => array($txt['sp_admin_categories_add']),
						),
					),
					'portalpages' => array(
						'label' => $txt['sp_admin_pages_title'],
						'file' => 'ADMINDIR/PortalAdminPages.php',
						'function' => 'sportal_admin_pages_main',
						'icon' => 'pages.png',
						'permission' => array('sp_admin', 'sp_manage_pages'),
						'subsections' => array(
							'list' => array($txt['sp_admin_pages_list']),
							'add' => array($txt['sp_admin_pages_add']),
						),
					),
					'portalshoutbox' => array(
						'label' => $txt['sp_admin_shoutbox_title'],
						'file' => 'ADMINDIR/PortalAdminShoutbox.php',
						'function' => 'sportal_admin_shoutbox_main',
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

function sp_integrate_whos_online($actions)
{
	global $scripturl, $modSettings, $txt;

	$db = database();
	$data = null;

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