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
 * Integration hook integrate_setup_allow
 * Called from Theme.php setupMenuContext(), used to modify menu privileges
 */
function sp_integrate_setup_allow()
{
	global $context;

	$context['allow_admin'] = $context['allow_admin'] && allowedTo(array('admin_forum', 'manage_boards', 'sp_admin', 'sp_manage_settings', 'sp_manage_blocks', 'sp_manage_articles', 'sp_manage_pages', 'sp_manage_shoutbox'));
}

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
	{
		return;
	}

	$actions['forum'] = array('BoardIndex.controller.php', 'BoardIndex_Controller', 'action_boardindex');
	$actions['portal'] = array('PortalMain.controller.php', 'PortalMain_Controller', 'action_index');
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

	require_once(SUBSDIR . '/Portal.subs.php');
	loadLanguage('SPortalAdmin');
	loadLanguage('SPortal');

	$temp = $admin_areas;
	$admin_areas = array();

	foreach ($temp as $area => $data)
	{
		$admin_areas[$area] = $data;

		// Add in our admin menu option after layout aka forum
		if ($area === 'layout')
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
					/* Shhh its a secret for now, not done yet ;)
					'portalmenus' => array(
						'label' => $txt['sp_admin_menus_title'],
						'file' => 'PortalAdminMenus.controller.php',
						'controller' => 'ManagePortalMenus_Controller',
						'function' => 'action_index',
						'icon' => 'menus.png',
						'permission' => array('sp_admin', 'sp_manage_menus'),
						'subsections' => array(
							'listmainitem' => array($txt['sp_admin_menus_main_item_list']),
							'addmainitem' => array($txt['sp_admin_menus_main_item_add']),
							'listcustommenu' => array($txt['sp_admin_menus_custom_menu_list']),
							'addcustommenu' => array($txt['sp_admin_menus_custom_menu_add']),
							'addcustomitem' => array($txt['sp_admin_menus_custom_item_add'], 'enabled' => !empty($_REQUEST['sa']) && $_REQUEST['sa'] === 'listcustomitem'),
						),
					),
					*/
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
							'liststyle' => array($txt['sp_admin_style_profiles_list']),
							'listvisibility' => array($txt['sp_admin_visibility_profiles_list']),
						),
					),
				),
			);
		}
	}
}

/**
 * Permissions hook, integrate_load_permissions, called from ManagePermissions.php
 * used to add new permissions
 *
 * @param mixed[] $permissionGroups
 * @param mixed[] $permissionList
 * @param mixed[] $leftPermissionGroups
 * @param mixed[] $hiddenPermissions
 * @param mixed[] $relabelPermissions
 */
function sp_integrate_load_permissions(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions)
{
	$permissionList['membergroup'] = array_merge($permissionList['membergroup'], array(
		'sp_admin' => array(false, 'sp', 'sp'),
		'sp_manage_settings' => array(false, 'sp', 'sp'),
		'sp_manage_blocks' => array(false, 'sp', 'sp'),
		'sp_manage_articles' => array(false, 'sp', 'sp'),
		'sp_manage_pages' => array(false, 'sp', 'sp'),
		'sp_manage_shoutbox' => array(false, 'sp', 'sp'),
		'sp_manage_menus' => array(false, 'sp', 'sp'),
		'sp_manage_profiles' => array(false, 'sp', 'sp'),
	));

	$permissionGroups['membergroup'][] = 'sp';

	$leftPermissionGroups[] = 'sp';
}

/**
 * Whos online hook, integrate_whos_online, called from who.subs
 * translates custom actions to allow us to show what area a user is in
 *
 * @param string $actions
 *
 * @return array
 */
function sp_integrate_whos_online($actions)
{
	global $scripturl, $modSettings, $txt;

	$data = null;

	require_once(SUBSDIR . '/Portal.subs.php');
	loadLanguage('SPortal');

	// This may miss if its needed for the first action ... need to improve the hook or find another location
	if ($modSettings['sp_portal_mode'] == 1)
	{
		$txt['who_index'] = sprintf($txt['sp_who_index'], $scripturl);
		$txt['whoall_forum'] = sprintf($txt['sp_who_forum'], $scripturl);
	}
	elseif ($modSettings['sp_portal_mode'] == 3)
	{
		$txt['whoall_portal'] = sprintf($txt['sp_who_index'], $scripturl);
	}

	// If its a portal action, lets check it out.
	if (isset($actions['page']))
	{
		$data = sp_whos_online_page($actions['page']);
	}
	elseif (isset($actions['article']))
	{
		$data = sp_whos_online_article($actions['article']);
	}

	return $data;
}

/**
 * Page online hook, helper function to determine the page a user is viewing
 *
 * @param string $page_id
 *
 * @return string
 */
function sp_whos_online_page($page_id)
{
	global $scripturl, $txt, $context;

	$db = database();

	$data = $txt['who_hidden'];
	$numeric_ids = '';
	$string_ids = '';
	$page_where = '';

	if (is_numeric($page_id))
	{
		$numeric_ids = (int) $page_id;
	}
	else
	{
		$string_ids = $page_id;
	}

	if (!empty($numeric_ids))
	{
		$page_where = 'id_page IN ({int:numeric_ids})';
	}

	if (!empty($string_ids))
	{
		$page_where = 'namespace IN ({string:string_ids})';
	}

	$query = sprintf($context['SPortal']['permissions']['query'], 'permissions');

	$result = $db->query('', '
		SELECT
			id_page, namespace, title, permissions
		FROM {db_prefix}sp_pages
		WHERE ' . $page_where . ' AND ' . $query . '
		LIMIT {int:limit}',
		array(
			'numeric_ids' => $numeric_ids,
			'string_ids' => $string_ids,
			'limit' => 1,
		)
	);
	$page_data = '';
	while ($row = $db->fetch_assoc($result))
	{
		$page_data = array(
			'id' => $row['id_page'],
			'namespace' => $row['namespace'],
			'title' => $row['title'],
		);
	}
	$db->free_result($result);

	if (!empty($page_data))
	{
		if (isset($page_data['id']))
		{
			$data = sprintf($txt['sp_who_page'], $page_data['id'], censorText($page_data['title']), $scripturl);
		}

		if (isset($page_data['namespace']))
		{
			$data = sprintf($txt['sp_who_page'], $page_data['namespace'], censorText($page_data['title']), $scripturl);
		}
	}

	return $data;
}

/**
 * Article online hook, helper function to determine the page a user is viewing
 *
 * @param string $article_id
 *
 * @return string
 */
function sp_whos_online_article($article_id)
{
	global $scripturl, $txt, $context;

	$db = database();

	$data = $txt['who_hidden'];
	$numeric_ids = '';
	$string_ids = '';
	$article_where = '';

	if (is_numeric($article_id))
	{
		$numeric_ids = (int) $article_id;
	}
	else
	{
		$string_ids = $article_id;
	}

	if (!empty($numeric_ids))
	{
		$article_where = 'id_article IN ({int:numeric_ids})';
	}

	if (!empty($string_ids))
	{
		$article_where = 'namespace IN ({string:string_ids})';
	}

	$query = sprintf($context['SPortal']['permissions']['query'], 'permissions');

	$result = $db->query('', '
		SELECT
			id_article, namespace, title, permissions
		FROM {db_prefix}sp_articles
		WHERE ' . $article_where . ' AND ' . $query . '
		LIMIT {int:limit}',
		array(
			'numeric_ids' => $numeric_ids,
			'string_ids' => $string_ids,
			'limit' => 1,
		)
	);
	$article_data = '';
	while ($row = $db->fetch_assoc($result))
	{
		$article_data = array(
			'id' => $row['id_article'],
			'namespace' => $row['namespace'],
			'title' => $row['title'],
		);
	}
	$db->free_result($result);

	if (!empty($article_data))
	{
		if (isset($article_data['id']))
		{
			$data = sprintf($txt['sp_who_article'], $article_data['id'], censorText($article_data['title']), $scripturl);
		}

		if (isset($article_data['namespace']))
		{
			$data = sprintf($txt['sp_who_article'], $article_data['namespace'], censorText($article_data['title']), $scripturl);
		}
	}

	return $data;
}

/**
 * Theme hook, integrate_init_theme, called from load.php
 * Used to initialize main portal functions as soon as the theme is started
 */
function sp_integrate_init_theme()
{
	// Need to run init to determine if we are even active
	require_once(SUBSDIR . '/Portal.subs.php');
	sportal_init();
}

/**
 * Help hook, integrate_quickhelp, called from help.controller.php
 * Used to add in additional help languages for use in the admin quickhelp
 */
function sp_integrate_quickhelp()
{
	require_once(SUBSDIR . '/Portal.subs.php');

	// Load the Simple Portal Help file.
	loadLanguage('SPortalHelp');
	loadLanguage('SPortalAdmin');
}

/**
 * Integration hook integrate_buffer, called from ob_exit via call_integration_buffer
 * Used to modify the output buffer before its sent, here we add in our copyright
 *
 * @param string $tourniquet
 *
 * @return string
 */
function sp_integrate_buffer($tourniquet)
{
	global $sportal_version, $context, $modSettings;

	$fix = str_replace('{version}', $sportal_version, '<a href="https://simpleportal.net/" target="_blank" class="new_win">SimplePortal {version} &copy; 2008-' . strftime('%Y') . '</a>');

	if ((ELK === 'SSI' && empty($context['standalone'])) || !Template_Layers::instance()->hasLayers() || empty($modSettings['sp_portal_mode']) || strpos($tourniquet, $fix) !== false)
	{
		return $tourniquet;
	}

	// Don't display copyright for things like SSI.
	if (!defined('FORUM_VERSION'))
	{
		return $tourniquet;
	}

	// Append our cp notice at the end of the line
	$finds = array(
		sprintf('powered by %1$s</a> | ', FORUM_VERSION),
	);
	$replaces = array(
		sprintf('powered by %1$s</a> | ', FORUM_VERSION) . $fix . ' | ',
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
	global $txt, $scripturl, $modSettings, $context;

	require_once(SUBSDIR . '/Portal.subs.php');
	loadLanguage('SPortal');

	// Set the right portalurl based on what integration mode the portal is using
	if ($modSettings['sp_portal_mode'] == 1 && empty($context['disable_sp']))
	{
		$sportal_url = $scripturl . '?action=forum';
	}
	elseif ($modSettings['sp_portal_mode'] == 3 && empty($context['disable_sp']))
	{
		$buttons['home']['href'] = $modSettings['sp_standalone_url'];
		$sportal_url = $modSettings['sp_standalone_url'];
	}
	else
	{
		return;
	}

	// Define the new menu item(s), show it for modes 1 and 3 only
	$buttons = elk_array_insert($buttons, 'home', array(
		'forum' => array(
			'title' => empty($txt['sp-forum']) ? 'Forum' : $txt['sp-forum'],
			'data-icon' => '&#xf0c0;',
			'href' => $sportal_url,
			'show' => in_array($modSettings['sp_portal_mode'], array(1, 3)) && empty($context['disable_sp']),
			'sub_buttons' => array(),
		),
	), 'after');
}

/**
 * Redirection hook, integrate_redirect, called from subs.php redirectexit()
 *
 * @param string $setLocation
 *
 * @uses redirectexit_callback in subs.php
 */
function sp_integrate_redirect(&$setLocation)
{
	global $modSettings, $context, $scripturl;

	// Set the default redirect location as the forum or the portal.
	if ($scripturl == $setLocation && ($modSettings['sp_portal_mode'] == 1 || $modSettings['sp_portal_mode'] == 3))
	{
		// Redirect the user to the forum.
		if (!empty($modSettings['sp_disableForumRedirect']))
		{
			$setLocation = '?action=forum';
		}
		// Redirect the user to the SSI.php standalone portal.
		elseif ($modSettings['sp_portal_mode'] == 3)
		{
			$setLocation = $context['portal_url'];
		}
	}
	// If we are using Search engine friendly URLs then lets do the same for page links
	elseif (!empty($modSettings['queryless_urls']) && (empty($context['server']['is_cgi']) || ini_get('cgi.fix_pathinfo') == 1 || @get_cfg_var('cgi.fix_pathinfo') == 1) && (!empty($context['server']['is_apache']) || !empty($context['server']['is_lighttpd']) || !empty($context['server']['is_litespeed'])))
	{
		if (defined('SID') && SID != '')
		{
			$setLocation = preg_replace_callback('~^' . preg_quote($scripturl, '/') . '\?(?:' . SID . '(?:;|&|&amp;))((?:page)=[^#]+?)(#[^"]*?)?$~', 'redirectexit_callback', $setLocation);
		}
		else
		{
			$setLocation = preg_replace_callback('~^' . preg_quote($scripturl, '/') . '\?((?:page)=[^#"]+?)(#[^"]*?)?$~', 'redirectexit_callback', $setLocation);
		}
	}
}

/**
 * A single check for the sake of remove yet another code edit. :P
 * integrate_action_boardindex_after
 */
function sp_integrate_boardindex()
{
	global $context, $modSettings, $scripturl;

	if (!empty($_GET) && $_GET !== array('action' => 'forum'))
	{
		$context['robot_no_index'] = true;
	}

	// Set the board index canonical URL correctly when portal mode is set to front page
	if (!empty($modSettings['sp_portal_mode']) && $modSettings['sp_portal_mode'] == 1 && empty($context['disable_sp']))
	{
		$context['canonical_url'] = $scripturl . '?action=forum';
	}
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
	if ($current_action === 'home')
	{
		$current_action = $modSettings['sp_portal_mode'] == 3 && empty($context['standalone']) && empty($context['disable_sp'])
			? 'forum' : 'home';
	}

	if (empty($context['disable_sp']) && ((isset($_GET['board']) || isset($_GET['topic']) || in_array($context['current_action'], array('unread', 'unreadreplies', 'collapse', 'recent', 'stats', 'who'))) && in_array($modSettings['sp_portal_mode'], array(1, 3))))
	{
		$current_action = 'forum';
	}
}

/**
 * Add in to the xml array our sortable action
 * integrate_sa_xmlhttp
 *
 * @param mixed[] $subActions
 */
function sp_integrate_xmlhttp(&$subActions)
{
	$subActions['blockorder'] = array('controller' => 'ManagePortalBlocks_Controller', 'file' => 'PortalAdminBlocks.controller.php', 'function' => 'action_blockorder', 'permission' => 'admin_forum');
	$subActions['userblockorder'] = array('controller' => 'PortalMain_Controller', 'dir' => CONTROLLERDIR, 'file' => 'PortalMain.controller.php', 'function' => 'action_userblockorder');
}

/**
 * Add items to the not stat action array to prevent logging in some cases
 *
 * @param string $no_stat_actions
 */
function sp_integrate_pre_log_stats(&$no_stat_actions)
{
	// Don't track who actions for the shoutbox
	if (isset($_REQUEST['action']) && ($_REQUEST['action'] === 'shoutbox' && isset($_GET['xml'])))
	{
		$no_stat_actions[] = 'shoutbox';
	}

	// Don't track stats of portal xml actions.
	if (isset($_REQUEST['action']) && ($_REQUEST['action'] === 'portal' && isset($_GET['xml'])))
	{
		$no_stat_actions[] = 'portal';
	}
}

/**
 * Add permissions that guest should never be able to have
 * integrate_load_illegal_guest_permissions called from Permission.subs.php
 */
function sp_integrate_load_illegal_guest_permissions()
{
	global $context;

	// Guests shouldn't be able to have any portal specific permissions.
	$context['non_guest_permissions'] = array_merge($context['non_guest_permissions'], array(
		'sp_admin',
		'sp_manage_settings',
		'sp_manage_blocks',
		'sp_manage_articles',
		'sp_manage_pages',
		'sp_manage_shoutbox',
		'sp_manage_profiles',
		'sp_manage_menus',
	));
}

/**
 * Subs hook, integrate_pre_parsebbc
 *
 * - Allow addons access before entering the main parse_bbc loop
 * - Prevents parseBBC from working on these tags at all
 *
 * @param string $message
 * @param mixed[] $smileys
 * @param string $cache_id
 * @param string[]|null $parse_tags
 */
function sp_integrate_pre_parsebbc(&$message, &$smileys, &$cache_id, &$parse_tags)
{
	if (strpos($message, '[cutoff]') !== false)
	{
		$message = str_replace('[cutoff]', '', $message);
	}
}
