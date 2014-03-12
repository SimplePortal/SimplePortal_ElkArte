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
 * @param mixed[] $actions
 */
function sp_integrate_actions(&$actions)
{
	global $context;

	if (!empty($context['disable_sp']))
		return;

	$actions['forum'] = array('BoardIndex.controller.php', 'BoardIndex_Controller', 'action_boardindex');
	$actions['portal'] = array('PortalMain.controller.php', 'Sportal_Controller', 'action_sportal_main');
	$actions['shoutbox'] = array('PortalShoutbox.controller.php', 'Shoutbox_Controller', 'action_sportal_shoutbox');
}

/**
 * Admin hook, integrate_admin_areas, called from Menu.subs
 * adds the admin menu
 *
 * @param mixed[] $admin_areas
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

		// Add in our admin menu option after layout aka forum
		if ($area == 'layout')
		{
			$admin_areas['portal'] = array(
				'title' => $txt['sp-adminCatTitle'],
				'permission' => array('sp_admin', 'sp_manage_settings', 'sp_manage_blocks', 'sp_manage_articles', 'sp_manage_pages', 'sp_manage_shoutbox', 'sp_manage_profiles'),
				'areas' => array(
					'portalconfig' => array(
						'label' => $txt['sp-adminConfiguration'],
						'file' => 'PortalAdminMain.controller.php',
						'controller' => 'ManagePortalConfig_Controller',
						'function' => 'action_index',
						'icon' => 'transparent.png',
						'class' => 'admin_img_corefeatures',
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
						'icon' => 'transparent.png',
						'class' => 'admin_img_news',
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
						'icon' => 'transparent.png',
						'class' => 'admin_img_posts',
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
						'icon' => 'transparent.png',
						'class' => 'admin_img_smiley',
						'permission' => array('sp_admin', 'sp_manage_shoutbox'),
						'subsections' => array(
							'list' => array($txt['sp_admin_shoutbox_list']),
							'add' => array($txt['sp_admin_shoutbox_add']),
						),
					),
					'portalprofiles' => array(
						'label' => $txt['sp_admin_profiles_title'],
						'file' => 'PortalAdminProfiles.controller.php',
						'controller' => 'ManagePortalProfile_Controller',
						'function' => 'action_index',
						'icon' => 'transparent.png',
						'class' => 'admin_img_permissions',
						'permission' => array('sp_admin', 'sp_manage_profiles'),
						'subsections' => array(
							'listpermission' => array($txt['sp_admin_permission_profiles_list']),
							'addpermission' => array($txt['sp_admin_permission_profiles_add']),
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
 * @param mixed[] $permissionGroups
 * @param mixed[] $permissionList
 * @param mixed[] $leftPermissionGroups
 * @param mixed[] $hiddenPermissions
 * @param mixed[] $relabelPermissions
 */
function sp_integrate_load_permissions(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions)
{
	$permissionList['membergroup']['sp_admin'] = array(false, 'sp', 'sp');
	$permissionList['membergroup']['sp_manage_settings'] = array(false, 'sp', 'sp');
	$permissionList['membergroup']['sp_manage_blocks'] = array(false, 'sp', 'sp');
	$permissionList['membergroup']['sp_manage_articles'] = array(false, 'sp', 'sp');
	$permissionList['membergroup']['sp_manage_pages'] = array(false, 'sp', 'sp');
	$permissionList['membergroup']['sp_manage_shoutbox'] = array(false, 'sp', 'sp');
	$permissionList['membergroup']['sp_manage_profiles'] = array(false, 'sp', 'sp');

	$permissionGroups['membergroup'][] = 'sp';

	$leftPermissionGroups[] = 'sp';
}

/**
 * Whos online hook, integrate_whos_online, called from who.subs
 * translates custom actions to allow show what area a user is in
 *
 * @param string $actions
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
			SELECT
				id_page, namespace, title, permissions
			FROM {db_prefix}sp_pages
			WHERE ' . implode(' OR ', $page_where) . '
			LIMIT {int:limit}',
			array(
				'numeric_ids' => $numeric_ids,
				'string_ids' => $string_ids,
				'limit' => 1,
			)
		);
		$page_data = array();
		while ($row = $db->fetch_assoc($result))
		{
			if (!sp_allowed_to('page', $row['id_page'], $row['permissions']))
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
 * Frontpage hook, integrate_frontpage, called from the site dispatcher,
 * used to replace the default action with a new one
 *
 * @param string $default_action
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
			// View the portal
			$file = CONTROLLERDIR . '/PortalMain.controller.php';
			$controller = 'Sportal_Controller';
			$function = 'action_sportal_main';
		}
		elseif (!empty($_GET['page']))
		{
			// View a specific page
			$file = CONTROLLERDIR . '/PortalPages.controller.php';
			$controller = 'Pages_Controller';
			$function = 'action_sportal_page';
		}
		elseif (!empty($_GET['article']))
		{
			// View a specific  article
			$file = CONTROLLERDIR . '/PortalArticles.controller.php';
			$controller = 'Article_Controller';
			$function = 'action_sportal_article';
		}
		elseif (!empty($_GET['category']))
		{
			// View a specific category
			$file = CONTROLLERDIR . '/PortalCategories.controller.php';
			$controller = 'Categories_Controller';
			$function = 'action_sportal_category';
		}

		// Something portal-ish, then set the new action
		if (isset($file, $function))
		{
			$default_action = array(
				'file' => $file,
				'controller' => isset($controller) ? $controller : null,
				'function' => $function
			);
		}
	}

	return;
}

/**
 * Help hook, integrate_quickhelp, called from help.controller.php
 * Used to add in additional help languages for use in the admin quickhelp
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

	if ((ELK == 'SSI' && empty($context['standalone'])) || !Template_Layers::getInstance()->hasLayers() || empty($modSettings['sp_portal_mode']) || strpos($tourniquet, $fix) !== false)
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
 * @param mixed[] $buttons
 */
function sp_integrate_menu_buttons(&$buttons)
{
	global $txt, $scripturl, $modSettings;

	loadLanguage('SPortal');

	// Define the new menu item(s)
	$buttons = elk_array_insert($buttons, 'home', array(
		'forum' => array(
			'title' => empty($txt['sp-forum']) ? 'Forum' : $txt['sp-forum'],
			'href' => $scripturl . ($modSettings['sp_portal_mode'] == 1 && empty($context['disable_sp']) ? '?action=forum' : ''),
			'show' => in_array($modSettings['sp_portal_mode'], array(1, 3)) && empty($context['disable_sp']),
			'sub_buttons' => array(),
		),
	), 'after');
}

/**
 * Redirection hook, integrate_redirect, called from subs.php redirectexit()
 *
 * @param string $setLocation
 * @param boolean $refresh
 * @uses redirectexit_callback in subs.php
 */
function sp_integrate_redirect(&$setLocation, &$refresh)
{
	global $modSettings, $context, $scripturl;

	// Set the default redirect location as the forum or the portal.
	if ($scripturl == $setLocation && ($modSettings['sp_portal_mode'] == 1 || $modSettings['sp_portal_mode'] == 3))
	{
		// Redirect the user to the forum.
		if (!empty($modSettings['sp_disableForumRedirect']))
			$setLocation = '?action=forum';
		// Redirect the user to the SSI.php standalone portal.
		elseif ($modSettings['sp_portal_mode'] == 3)
			$setLocation = $context['portal_url'];
	}
	// If we are using Search engine friendly URLs then lets do the same for page links
	elseif (!empty($modSettings['queryless_urls']) && (empty($context['server']['is_cgi']) || ini_get('cgi.fix_pathinfo') == 1 || @get_cfg_var('cgi.fix_pathinfo') == 1) && (!empty($context['server']['is_apache']) || !empty($context['server']['is_lighttpd']) || !empty($context['server']['is_litespeed'])))
	{
		if (defined('SID') && SID != '')
			$setLocation = preg_replace_callback('~^' . preg_quote($scripturl, '/') . '\?(?:' . SID . '(?:;|&|&amp;))((?:page)=[^#]+?)(#[^"]*?)?$~', 'redirectexit_callback', $setLocation);
		else
			$setLocation = preg_replace_callback('~^' . preg_quote($scripturl, '/') . '\?((?:page)=[^#"]+?)(#[^"]*?)?$~', 'redirectexit_callback', $setLocation);
	}
}

/**
 * A single check for the sake of remove yet another code edit. :P
 */
function sp_integrate_boardindex()
{
	global $context;

	if (!empty($_GET) && $_GET !== array('action' => 'forum'))
		$context['robot_no_index'] = true;
}

/**
 * Dealing with the current action?
 *
 * @param string $current_action
 */
function sp_integrate_current_action(&$current_action)
{
	global $modSettings, $context;

	// If it is home, it may be something else
	if ($current_action == 'home')
		$current_action = $modSettings['sp_portal_mode'] == 3 && empty($context['standalone']) && empty($context['disable_sp']) ? 'forum' : 'home';

	if(empty($context['disable_sp']) && ((isset($_GET['board']) || isset($_GET['topic']) || in_array($context['current_action'], array('unread', 'unreadreplies', 'recent', 'stats', 'who'))) && in_array($modSettings['sp_portal_mode'], array(1, 3))))
		$current_action = 'forum';
}