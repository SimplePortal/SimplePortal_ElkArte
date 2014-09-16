<?php

/**
 * @package SimplePortal
 *
 * @author SimplePortal Team
 * @copyright 2014 SimplePortal Team
 * @license BSD 3-clause
 *
 * @version 2.4
 */

if (!defined('ELK'))
	die('No access...');

/**
 * Initializes the portal, outputs all the blocks as needed
 *
 * @param boolean $standalone
 */
function sportal_init($standalone = false)
{
	global $context, $scripturl, $modSettings, $settings, $maintenance, $sportal_version;

	$sportal_version = '2.4';

	if ((isset($_REQUEST['action']) && $_REQUEST['action'] == 'dlattach'))
		return;

	if ((isset($_REQUEST['xml']) || isset($_REQUEST['api'])) && ((isset($_REQUEST['action']) && $_REQUEST['action'] !== 'shoutbox')))
		return;

	// Not running standalone then we need to load in some template information
	if (!$standalone)
	{
		// Load the portal css and the default css if its not yet loaded.
		loadTemplate(false, 'portal');

		// rtl css as well?
		if (!empty($context['right_to_left']))
			loadCSSFile('portal_rtl.css');

		if (!empty($_REQUEST['action']) && in_array($_REQUEST['action'], array('admin', 'helpadmin')))
			loadLanguage('SPortalAdmin', sp_languageSelect('SPortalAdmin'));

		if (!isset($settings['sp_images_url']))
		{
			if (file_exists($settings['theme_dir'] . '/images/sp'))
				$settings['sp_images_url'] = $settings['theme_url'] . '/images/sp';
			else
				$settings['sp_images_url'] = $settings['default_theme_url'] . '/images/sp';
		}
	}

	// Portal not enabled then bow out now
	if ($context['browser_body_id'] == 'mobile' || !empty($settings['disable_sp']) || empty($modSettings['sp_portal_mode']) || ((!empty($modSettings['sp_maintenance']) || !empty($maintenance)) && !allowedTo('admin_forum')) || isset($_GET['debug']) || (empty($modSettings['allow_guestAccess']) && $context['user']['is_guest']))
	{
		$context['disable_sp'] = true;

		if ($standalone)
		{
			$get_string = '';
			foreach ($_GET as $get_var => $get_value)
				$get_string .= $get_var . (!empty($get_value) ? '=' . $get_value : '') . ';';
			redirectexit(substr($get_string, 0, -1));
		}

		return;
	}

	// Not standalone means we need to load some portal specific information in to context
	if (!$standalone)
	{
		require_once(SUBSDIR . '/PortalBlocks.subs.php');

		// Not running via ssi then we need to get SSI for its functions
		if (ELK !== 'SSI')
			require_once(BOARDDIR . '/SSI.php');

		// Portal specific templates and language
		loadTemplate('Portal');
		loadLanguage('SPortal', sp_languageSelect('SPortal'));

		if (!empty($modSettings['sp_maintenance']) && !allowedTo('sp_admin'))
			$modSettings['sp_portal_mode'] = 0;

		if (empty($modSettings['sp_standalone_url']))
			$modSettings['sp_standalone_url'] = '';

		if ($modSettings['sp_portal_mode'] == 3)
			$context += array(
				'portal_url' => $modSettings['sp_standalone_url'],
				'page_title' => $context['forum_name'],
			);
		else
			$context += array(
				'portal_url' => $scripturl,
			);

		if ($modSettings['sp_portal_mode'] == 1)
			$context['linktree'][0] = array(
				'url' => $scripturl . '?action=forum',
				'name' => $context['forum_name'],
			);

		if (!empty($context['linktree']) && $modSettings['sp_portal_mode'] == 1)
		{
			foreach ($context['linktree'] as $key => $tree)
				if (strpos($tree['url'], '#c') !== false && strpos($tree['url'], 'action=forum#c') === false)
					$context['linktree'][$key]['url'] = str_replace('#c', '?action=forum#c', $tree['url']);
		}
		else
			$_GET['action'] = 'portal';
	}

	// Load the headers if necessary.
	sportal_init_headers();

	// Load permissions
	sportal_load_permissions();

	// Play with our blocks
	$context['standalone'] = $standalone;
	sportal_load_blocks();

	$context['SPortal']['on_portal'] = getShowInfo(0, 'portal', '');

	if (!Template_Layers::getInstance()->hasLayers(true) && !in_array('portal', Template_Layers::getInstance()->getLayers()))
		Template_Layers::getInstance()->add('portal');
}

/**
 * Deals with the initialization of SimplePortal headers.
 */
function sportal_init_headers()
{
	global $modSettings, $txt, $user_info, $scripturl;
	static $initialized;

	// If already loaded just return
	if (!empty($initialized))
		return;

	// Generate a safe scripturl
	$safe_scripturl = $scripturl;
	$current_request = empty($_SERVER['HTTP_HOST']) ? $_SERVER['SERVER_NAME'] : $_SERVER['HTTP_HOST'];

	if (strpos($scripturl, 'www.') !== false && strpos($current_request, 'www.') === false)
		$safe_scripturl = str_replace('://www.', '://', $scripturl);
	elseif (strpos($scripturl, 'www.') === false && strpos($current_request, 'www.') !== false)
		$safe_scripturl = str_replace('://', '://www.', $scripturl);

	// The shoutbox may fail to function in certain cases without using a safe scripturl
	addJavascriptVar(array('sp_script_url' => '\'' . $safe_scripturl . '\''));

	// Load up some javascript!
	loadJavascriptFile('portal.js?sp24');

	// We use sortable for the front page
	$modSettings['jquery_include_ui'] = true;
	$javascript = '';

	// Javascipt to allow D&D ordering of the front page blocks, not for guests
	if (empty($_REQUEST['action']) && !($user_info['is_guest'] || $user_info['id'] == 0))
		$javascript .= '
			// Set up our sortable call
			$().elkSortable({
				sa: "userblockorder",
				error: "' . $txt['portal_order_error'] . '",
				title: "' . $txt['portal_order_title'] . '",
				handle: ".sp_drag_header",
				tag: ".sp_column",
				opacity: 0.9,
				connect: ".sp_column",
				containment: "#main_content_section",
				tolerance: "pointer",
				href: "/",
				placeholder: "ui-state-highlight",
				axis: "",
			});';

	if ($modSettings['sp_resize_images'])
	{
		$javascript .= '
		createEventListener(window);
		window.addEventListener("load", sp_image_resize, false);';
	}

	// Let the template know we have some inline JS to display
	addInlineJavascript($javascript, true);

	$initialized = true;
}

/**
 * Loads the permissions for the profiles
 */
function sportal_load_permissions()
{
	global $context, $user_info;

	$profiles = sportal_get_profiles(null, 1);
	$allowed = array();

	foreach ($profiles as $profile)
	{
		$result = false;
		if (!empty($profile['groups_denied']) && count(array_intersect($user_info['groups'], $profile['groups_denied'])) > 0)
			$result = false;
		elseif (!empty($profile['groups_allowed']) && count(array_intersect($user_info['groups'], $profile['groups_allowed'])) > 0)
			$result = true;

		if ($result)
			$allowed[] = $profile['id'];
	}

	$context['SPortal']['permissions'] = array(
		'profiles' => $allowed,
		'query' => empty($allowed) ? '0=1' : 'FIND_IN_SET(%s, \'' . implode(',', $allowed) . '\')',
	);
}

/**
 * Loads all the defined portal blocks in to context
 */
function sportal_load_blocks()
{
	global $context, $modSettings, $options;

	$context['SPortal']['sides'] = array(
		5 => array(
			'id' => '5',
			'name' => 'header',
			'active' => true,
		),
		1 => array(
			'id' => '1',
			'name' => 'left',
			'active' => !empty($modSettings['showleft']),
		),
		2 => array(
			'id' => '2',
			'name' => 'top',
			'active' => true,
		),
		3 => array(
			'id' => '3',
			'name' => 'bottom',
			'active' => true,
		),
		4 => array(
			'id' => '4',
			'name' => 'right',
			'active' => !empty($modSettings['showright']),
		),
		6 => array(
			'id' => '6',
			'name' => 'footer',
			'active' => true,
		),
	);

	// Get the blocks in the system
	$blocks = getBlockInfo(null, null, true, true, true);

	// If the member has arranged the blocks, display them like that
	if (!empty($options['sp_block_layout']))
	{
		$layout = @unserialize($options['sp_block_layout']);

		// If some bad arrangement data found its way in
		if ($layout === false)
			resetMemberLayout();
		else
		{
			foreach ($layout as $id => $column)
			{
				if (empty($column) || empty($id) || !$context['SPortal']['sides'][$id]['active'])
					continue;

				foreach ($column as $item)
				{
					if (empty($blocks[$item]))
						continue;

					$blocks[$item]['style'] = sportal_parse_style('explode', $blocks[$item]['style'], true);
					$context['SPortal']['blocks'][$id][] = $blocks[$item];
					unset($blocks[$item]);
				}

				$context['SPortal']['blocks']['custom_arrange'] = true;
			}
		}
	}

	if (!isset($context['SPortal']['blocks']))
		$context['SPortal']['blocks'] = array();

	foreach ($blocks as $block)
	{
		if (!$context['SPortal']['sides'][$block['column']]['active'])
			continue;

		$block['style'] = sportal_parse_style('explode', $block['style'], true);

		$context['SPortal']['sides'][$block['column']]['last'] = $block['id'];
		$context['SPortal']['blocks'][$block['column']][] = $block;
	}

	foreach($context['SPortal']['sides'] as $side)
	{
		if (empty($context['SPortal']['blocks'][$side['id']]))
			$context['SPortal']['sides'][$side['id']]['active'] = false;

		$context['SPortal']['sides'][$side['id']]['collapsed'] = $context['user']['is_guest'] ? !empty($_COOKIE['sp_' . $side['name']]) : !empty($options['sp_' . $side['name']]);
	}
}

/**
 * If a member has arranged blocks in some bizare fashion, this will reset the layout to
 * the default one
 */
function resetMemberLayout()
{
	global $settings, $user_info;

	$db = database();

	$db->query('', '
		DELETE FROM {db_prefix}themes
		WHERE id_theme = {int:current_theme}
			AND variable = {string:theme_variable}
			AND id_member = {int:id_member}',
		array(
			'current_theme' => $settings['theme_id'],
			'theme_variable' => 'sp_block_layout',
			'id_member' => $user_info['id'],
		)
	);
}

/**
 * This function, returns all of the information about particular blocks.
 *
 * @param int|null $column_id
 * @param int|null $block_id
 * @param boolean|null $state
 * @param boolean|null $show
 * @param boolean|null $permission
 */
function getBlockInfo($column_id = null, $block_id = null, $state = null, $show = null, $permission = null)
{
	global $context, $options, $txt;

	$db = database();

	$query = array();
	$parameters = array();
	if (!empty($column_id))
	{
		$query[] = 'spb.col = {int:col}';
		$parameters['col'] = !empty($column_id) ? $column_id : 0;
	}

	if (!empty($block_id))
	{
		$query[] = 'spb.id_block = {int:id_block}';
		$parameters['id_block'] = !empty($block_id) ? $block_id : 0;
	}

	if (!empty($permission))
		$query[] = sprintf($context['SPortal']['permissions']['query'], 'spb.permissions');

	if (!empty($state))
	{
		$query[] = 'spb.state = {int:state}';
		$parameters['state'] = 1;
	}

	$request = $db->query('', '
		SELECT
			spb.id_block, spb.label, spb.type, spb.col, spb.row, spb.permissions, spb.state,
			spb.force_view, spb.display, spb.display_custom, spb.style, spp.variable, spp.value
		FROM {db_prefix}sp_blocks AS spb
			LEFT JOIN {db_prefix}sp_parameters AS spp ON (spp.id_block = spb.id_block)' . (!empty($query) ? '
		WHERE ' . implode(' AND ', $query) : '') . '
		ORDER BY spb.col, spb.row', $parameters
	);
	$return = array();
	while ($row = $db->fetch_assoc($request))
	{
		if (!empty($show) && !getShowInfo($row['id_block'], $row['display'], $row['display_custom']))
			continue;

		if (!isset($return[$row['id_block']]))
		{
			$return[$row['id_block']] = array(
				'id' => $row['id_block'],
				'label' => $row['label'],
				'type' => $row['type'],
				'type_text' => !empty($txt['sp_function_' . $row['type'] . '_label']) ? $txt['sp_function_' . $row['type'] . '_label'] : $txt['sp_function_unknown_label'],
				'column' => $row['col'],
				'row' => $row['row'],
				'permissions' => $row['permissions'],
				'state' => empty($row['state']) ? 0 : 1,
				'force_view' => $row['force_view'],
				'display' => $row['display'],
				'display_custom' => $row['display_custom'],
				'style' => $row['style'],
				'collapsed' => $context['user']['is_guest'] ? !empty($_COOKIE['sp_block_' . $row['id_block']]) : !empty($options['sp_block_' . $row['id_block']]),
				'parameters' => array(),
			);
		}

		if (!empty($row['variable']))
			$return[$row['id_block']]['parameters'][$row['variable']] = $row['value'];
	}
	$db->free_result($request);

	return $return;
}

/**
 * Function to get a block's display/show information.
 *
 * @param int|null $block_id
 * @param string|null $display
 * @param string|null $custom
 */
function getShowInfo($block_id = null, $display = null, $custom = null)
{
	global $context, $modSettings;
	static $variables;

	$db = database();

	// Do we have the display info?
	if ($display === null || $custom === null)
	{
		// Make sure that its an integer.
		$block_id = (int) $block_id;

		// We need an ID.
		if (empty($block_id))
			return false;

		// Get the info.
		$result = $db->query('', '
			SELECT display, display_custom
			FROM {db_prefix}sp_blocks
			WHERE id_block = {int:id_block}
			LIMIT 1',
			array(
				'id_block' => $block_id,
			)
		);
		list ($display, $custom) = $db->fetch_row($result);
		$db->free_result($result);
	}

	if (!empty($_GET['page']) && (empty($context['current_action']) || $context['current_action'] == 'portal'))
	{
		if (empty($context['SPortal']['permissions']))
			sportal_load_permissions();
		$page_info = sportal_get_pages($_GET['page'], true, true);
	}

	// Some variables for ease.
	$action = !empty($context['current_action']) ? $context['current_action'] : '';
	$sub_action = !empty($context['current_subaction']) ? $context['current_subaction'] : '';
	$board = !empty($context['current_board']) ? 'b' . $context['current_board'] : '';
	$topic = !empty($context['current_topic']) ? 't' . $context['current_topic'] : '';
	$page = !empty($page_info['id']) ? 'p' . $page_info['id'] : '';
	$portal = (empty($action) && empty($sub_action) && empty($board) && empty($topic) && ELK !== 'SSI' && $modSettings['sp_portal_mode'] == 1) || $action == 'portal' || !empty($context['standalone']) ? true : false;

	// Will hopefully get larger in the future.
	$portal_actions = array(
		'articles' => true,
		'start' => true,
		'theme' => true,
		'PHPSESSID' => true,
		'wwwRedirect' => true,
		'www' => true,
		'variant' => true,
		'language' => true,
		'action' => array('portal'),
	);

	// Set some action exceptions.
	$exceptions = array(
		'post' => array('announce', 'editpoll', 'emailuser', 'post2', 'sendtopic'),
		'register' => array('activate', 'coppa'),
		'forum' => array('collapse'),
		'admin' => array('credits', 'theme', 'viewquery', 'viewsmfile'),
		'moderate' => array('groups'),
		'login' => array('reminder'),
		'profile' => array('trackip', 'viewprofile'),
	);

	// Still, we might not be in portal!
	if (!empty($_GET) && empty($context['standalone']))
	{
		foreach ($_GET as $key => $value)
		{
			if (preg_match('~^news\d+$~', $key))
				continue;

			if (!isset($portal_actions[$key]))
				$portal = false;
			elseif (is_array($portal_actions[$key]) && !in_array($value, $portal_actions[$key]))
				$portal = false;
		}
	}

	// Set the action to more known one.
	foreach ($exceptions as $key => $exception)
		if (in_array($action, $exception))
			$action = $key;

	// Take care of custom actions.
	$special = array();
	$exclude = array();
	if (!empty($custom))
	{
		// Complex display options first...
		if (substr($custom, 0, 4) === '$php')
		{
			if (!isset($variables))
			{
				$variables = array(
					'{$action}' => "'$action'",
					'{$sa}' => "'$sub_action'",
					'{$board}' => "'$board'",
					'{$topic}' => "'$topic'",
					'{$page}' => "'$page'",
					'{$portal}' => $portal,
				);
			}

			return @eval(str_replace(array_keys($variables), array_values($variables), un_htmlspecialchars(substr($custom, 4))) . ';');
		}

		$custom = explode(',', $custom);

		// This is special...
		foreach ($custom as $key => $value)
		{
			$name = '';
			$item = '';

			// Is this a weird action?
			if ($value[0] == '~')
			{
				@list($name, $item) = explode('|', substr($value, 1));

				if (empty($item))
					$special[$name] = true;
				else
					$special[$name][] = $item;
			}

			// Might be excluding something!
			elseif ($value[0] == '-')
			{
				// We still may have weird things...
				if ($value[1] == '~')
				{
					@list($name, $item) = explode('|', substr($value, 2));

					if (empty($item))
						$exclude['special'][$name] = true;
					else
						$exclude['special'][$name][] = $item;
				}
				else
					$exclude['regular'][] = substr($value, 1);
			}
		}

		// Add what we have to main variable.
		if (!empty($display))
			$display = $display . ',' . implode(',', $custom);
		else
			$display = $custom;
	}

	// We don't want to show it on this action/page/board?
	if (!empty($exclude['regular']) && count(array_intersect(array($action, $page, $board), $exclude['regular'])) > 0)
		return false;

	// Maybe we don't want to show it in somewhere special.
	if (!empty($exclude['special']))
	{
		foreach ($exclude['special'] as $key => $value)
		{
			if (isset($_GET[$key]))
			{
				if (is_array($value) && !in_array($_GET[$key], $value))
					continue;
				else
					return false;
			}
		}
	}

	// If no display info and/or integration disabled and we are on portal; show it!
	if ((empty($display) || empty($modSettings['sp_enableIntegration'])) && $portal)
		return true;
	// No display info and/or integration disabled and no portal; no need...
	elseif (empty($display) || empty($modSettings['sp_enableIntegration']))
		return false;
	// Get ready for real action if you haven't yet.
	elseif (!is_array($display))
		$display = explode(',', $display);

	// Did we disable all blocks for this action?
	if (!empty($modSettings['sp_' . $action . 'IntegrationHide']))
		return false;
	// If we will display show the block.
	elseif (in_array('all', $display))
		return true;
	// If we are on portal, show portal blocks; if we are on forum, show forum blocks.
	elseif (($portal && (in_array('portal', $display) || in_array('sportal', $display))) || (!$portal && in_array('sforum', $display)))
		return true;
	elseif (!empty($board) && (in_array('allboard', $display) || in_array($board, $display)))
		return true;
	elseif (!empty($action) && $action != 'portal' && (in_array('allaction', $display) || in_array($action, $display)))
		return true;
	elseif (!empty($page) && (in_array('allpages', $display) || in_array($page, $display)))
		return true;
	elseif (empty($action) && empty($board) && empty($_GET['page']) && !$portal && ($modSettings['sp_portal_mode'] == 2 || $modSettings['sp_portal_mode'] == 3) && in_array('forum', $display))
		return true;

	// For mods using weird urls...
	foreach ($special as $key => $value)
	{
		if (isset($_GET[$key]))
		{
			if (is_array($value) && !in_array($_GET[$key], $value))
				continue;
			else
				return true;
		}
	}

	// Ummm, no block!
	return false;
}

/**
 * This is a simple function that returns nothing if the language file exist and english if it does not exist
 * This will help to make it possible to load each time the english language!
 *
 * @param string $template_name
 */
function sp_languageSelect($template_name)
{
	global $user_info, $language, $settings;
	static $already_loaded = array();

	if (isset($already_loaded[$template_name]))
		return $already_loaded[$template_name];

	$lang = isset($user_info['language']) ? $user_info['language'] : $language;

	// Make sure we have $settings - if not we're in trouble and need to find it!
	if (empty($settings['default_theme_dir']))
		loadEssentialThemeData();

	// For each file open it up and write it out!
	$allTemplatesExists = array();
	foreach (explode('+', $template_name) as $template)
	{
		// Obviously, the current theme is most important to check.
		$attempts = array(
			array($settings['theme_dir'], $template, $lang, $settings['theme_url']),
			array($settings['theme_dir'], $template, $language, $settings['theme_url']),
		);

		// Do we have a base theme to worry about?
		if (isset($settings['base_theme_dir']))
		{
			$attempts[] = array($settings['base_theme_dir'], $template, $lang, $settings['base_theme_url']);
			$attempts[] = array($settings['base_theme_dir'], $template, $language, $settings['base_theme_url']);
		}

		// Fallback on the default theme if necessary.
		$attempts[] = array($settings['default_theme_dir'], $template, $lang, $settings['default_theme_url']);
		$attempts[] = array($settings['default_theme_dir'], $template, $language, $settings['default_theme_url']);

		// Try to find the language file.
		$allTemplatesExists[$template] = false;
		$already_loaded[$template] = 'english';
		foreach ($attempts as $k => $file)
		{
			if (file_exists($file[0] . '/languages/' . $file[2] . '/' . $file[1] . '.' . $file[2] . '.php'))
			{
				$already_loaded[$template] = '';
				$allTemplatesExists[$template] = true;
				break;
			}
		}
	}

	// So all need to be true that it work ;)
	foreach ($allTemplatesExists as $exist)
		if (!$exist)
		{
			$already_loaded[$template_name] = 'english';
			return 'english';
		}

	// Everthing is fine, let's go back :D
	$already_loaded[$template_name] = '';

	return '';
}

/**
 * Loads a set of calendar data, holdays, birthdays, events
 *
 * @param string $type type of data to load, events, birthdays, etc
 * @param string $low_date don't load data before this date
 * @param string|false $high_date dont load data after this date, false for no limit
 */
function sp_loadCalendarData($type, $low_date, $high_date = false)
{
	static $loaded;

	if (!isset($loaded))
	{
		require_once(SUBSDIR . '/Calendar.subs.php');

		$loaded = array(
			'getEvents' => 'getEventRange',
			'getBirthdays' => 'getBirthdayRange',
			'getHolidays' => 'getHolidayRange',
		);
	}

	if (!empty($loaded[$type]))
		return $loaded[$type]($low_date, ($high_date === false ? $low_date : $high_date));
	else
		return array();
}

/**
 * This is a small script to load colors for SPortal.
 *
 * @param int[] $users
 */
function sp_loadColors($users = array())
{
	global $color_profile, $scripturl, $modSettings;

	$db = database();

	// This is for later, if you like to disable colors ;)
	if (!empty($modSettings['sp_disableColor']))
		return false;

	// Can't just look for no users. :P
	if (empty($users))
		return false;

	// MemberColorLink compatible, cache more data, handle also some special member color link colors
	if (!empty($modSettings['MemberColorLinkInstalled']))
	{
		$colorData = load_onlineColors($users);

		// This happen only on not existing Members... but given ids...
		if (empty($colorData))
			return false;

		$loaded_ids = array_keys($colorData);

		foreach ($loaded_ids as $id)
		{
			if (!empty($id) && !isset($color_profile[$id]['link']))
			{
				$color_profile[$id]['link'] = $colorData[$id]['colored_link'];
				$color_profile[$id]['colored_name'] = $colorData[$id]['colored_name'];
			}
		}
		return empty($loaded_ids) ? false : $loaded_ids;
	}

	// Make sure it's an array.
	$users = !is_array($users) ? array($users) : array_unique($users);

	// Check up the array :)
	foreach ($users as $k => $u)
	{
		$u = (int) $u;

		if (empty($u))
			unset($users[$k]);
		else
			$users[$k] = $u;
	}

	$loaded_ids = array();
	// Is this a totally new variable?
	if (empty($color_profile))
		$color_profile = array();
	// Otherwise, we will need to do some reformating of the old data.
	else
	{
		foreach ($users as $k => $u)
			if (isset($color_profile[$u]))
			{
				$loaded_ids[] = $u;
				unset($users[$k]);
			}
	}

	// Make sure that we have some users.
	if (empty($users))
		return empty($loaded_ids) ? false : $loaded_ids;

	// Correct array pointer for the user
	reset($users);

	// Load the data.
	$request = $db->query('', '
		SELECT
			mem.id_member, mem.member_name, mem.real_name, mem.id_group,
			mg.online_color AS member_group_color, pg.online_color AS post_group_color
		FROM {db_prefix}members AS mem
			LEFT JOIN {db_prefix}membergroups AS pg ON (pg.id_group = mem.id_post_group)
			LEFT JOIN {db_prefix}membergroups AS mg ON (mg.id_group = mem.id_group)
		WHERE mem.id_member ' . ((count($users) == 1) ? '= {int:current}' : 'IN ({array_int:users})'),
		array(
			'users' => $users,
			'current' => (int) current($users),
		)
	);

	// Go through each of the users.
	while ($row = $db->fetch_assoc($request))
	{
		$loaded_ids[] = $row['id_member'];
		$color_profile[$row['id_member']] = $row;
		$onlineColor = !empty($row['member_group_color']) ? $row['member_group_color'] : $row['post_group_color'];
		$color_profile[$row['id_member']]['color'] = $onlineColor;
		$color_profile[$row['id_member']]['link'] = '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '"' . (!empty($onlineColor) ? ' style="color: ' . $onlineColor . ';"' : '') . '>' . $row['real_name'] . '</a>';
		$color_profile[$row['id_member']]['colored_name'] = (!empty($onlineColor) ? '<span style="color: ' . $onlineColor . ';">' : '' ) . $row['real_name'] . (!empty($onlineColor) ? '</span>' : '');
	}
	$db->free_result($request);

	// Return the necessary data.
	return empty($loaded_ids) ? false : $loaded_ids;
}

/**
 * Builds an image tag for use in templates
 *
 * @param string $name
 * @param string $alt
 * @param int|null $width
 * @param int|null $height
 * @param string|boolean $title
 * @param int|null $id
 */
function sp_embed_image($name, $alt = '', $width = null, $height = null, $title = true, $id = null)
{
	global $modSettings, $settings, $txt;
	static $default_alt, $randomizer;

	loadLanguage('SPortal', sp_languageSelect('SPortal'));

	// Some default alt text settings for our standard images
	if (!isset($default_alt))
	{
		$default_alt = array(
			'dot' => $txt['sp-dot'],
			'stars' => $txt['sp-star'],
			'arrow' => $txt['sp-arrow'],
			'modify' => $txt['modify'],
			'delete' => $txt['delete'],
			'delete_small' => $txt['delete'],
			'history' => $txt['sp_shoutbox_history'],
			'refresh' => $txt['sp_shoutbox_refresh'],
			'smiley' => $txt['sp_shoutbox_smiley'],
			'style' => $txt['sp_shoutbox_style'],
			'bin' => $txt['sp_shoutbox_prune'],
			'move' => $txt['sp_move'],
		);
	}

	if (!isset($randomizer) || $randomizer > 7)
		$randomizer = 0;
	$randomizer++;

	// Use a default alt text if available and none was supplied
	if (empty($alt) && isset($default_alt[$name]))
		$alt = $default_alt[$name];

	// You want a title, use the alt text if we can
	if ($title === true)
		$title = !empty($alt) ? $alt : '';

	if (empty($alt))
		$alt = $name;

	// dots using random colors
	if (in_array($name, array('dot', 'star')) && empty($modSettings['sp_disable_random_bullets']))
		$name .= $randomizer;

	// Build the image tag
	$image = '<img src="' . $settings['sp_images_url'] . '/' . $name . '.png" alt="' . $alt . '"' . (!empty($title) ? ' title="' . $title . '"' : '') . (!empty($width) ? ' width="' . $width . '"' : '') . (!empty($height) ? ' height="' . $height . '"' : '') . (!empty($id) ? ' id="' . $id . '"' : '') . ' />';

	return $image;
}

/**
 * Builds an sprite class tag for use in templates
 *
 * @param string $name
 * @param string $title
 * @param string| $extaclass
 * @param string $spriteclass
 */
function sp_embed_class($name, $title = '', $extraclass = '', $spriteclass = 'dot')
{
	global $modSettings, $txt;
	static $default_title, $randomizer;

	loadLanguage('SPortal', sp_languageSelect('SPortal'));

	// Some default title text settings for our standard sprites
	if (!isset($default_title))
	{
		$default_title = array(
			'dot' => $txt['sp-dot'],
			'stars' => $txt['sp-star'],
			'arrow' => $txt['sp-arrow'],
			'modify' => $txt['modify'],
			'delete' => $txt['delete'],
			'delete_small' => $txt['delete'],
			'history' => $txt['sp_shoutbox_history'],
			'refresh' => $txt['sp_shoutbox_refresh'],
			'smiley' => $txt['sp_shoutbox_smiley'],
			'style' => $txt['sp_shoutbox_style'],
			'bin' => $txt['sp_shoutbox_prune'],
			'move' => $txt['sp_move'],
		);
	}

	// Use a default title text if available and none was supplied
	if (empty($title) && isset($default_title[$name]))
		$title = $default_title[$name];
	else
		$title = $name;

	// dots / start using colors
	if (in_array($name, array('dot', 'star')) && empty($modSettings['sp_disable_random_bullets']))
	{
		// Loop through the dot colors
		if (!isset($randomizer) || $randomizer > 7)
			$randomizer = 0;
		$randomizer++;
		$name = $name . $randomizer;
	}

	// Build the attributes
	return 'class="' . $spriteclass . ' ' . $name . (!empty($extraclass) ? ' ' . $extraclass : '') . '" title="' . $title . '"';
}

/**
 * Implodes or Expands a set of style attributes
 *
 * @param string $action implode or explode
 * @param string $setting string of style options joined on name~value|name~value
 * @param boolean $process
 */
function sportal_parse_style($action, $setting = '', $process = false)
{
	static $process_cache;

	if ($action === 'implode')
	{
		$style = '';
		$style_parameters = array(
			'title_default_class',
			'title_custom_class',
			'title_custom_style',
			'body_default_class',
			'body_custom_class',
			'body_custom_style',
			'no_title',
			'no_body',
		);

		foreach ($style_parameters as $parameter)
			if (isset($_POST[$parameter]))
				$style .= $parameter . '~' . Util::htmlspecialchars(Util::htmltrim($_POST[$parameter]), ENT_QUOTES) . '|';
			else
				$style .= $parameter . '~|';

		if (!empty($style))
			$style = substr($style, 0, -1);
	}
	elseif ($action === 'explode')
	{
		$style = array();

		// Supplying a style set or using our defaults
		if (!empty($setting))
		{
			$temp = explode('|', $setting);
			foreach ($temp as $item)
			{
				list ($key, $value) = explode('~', $item);
				$style[$key] = $value;
			}
		}
		else
		{
			$style = array(
				'title_default_class' => 'category_header',
				'title_custom_class' => '',
				'title_custom_style' => '',
				'body_default_class' => 'portalbg',
				'body_custom_class' => '',
				'body_custom_style' => '',
				'no_title' => false,
				'no_body' => false,
			);
		}

		// Set the style values for use in the templates
		if ($process && !isset($process_cache[$setting]))
		{
			if (empty($style['no_title']))
			{
				$style['title']['class'] = $style['title_default_class'];

				if (!empty($style['title_custom_class']))
					$style['title']['class'] .= ' ' . $style['title_custom_class'];

				$style['title']['style'] = $style['title_custom_style'];
			}

			if (empty($style['no_body']))
				$style['body']['class'] = $style['body_default_class'];
			else
				$style['body']['class'] = '';

			if (!empty($style['body_custom_class']))
				$style['body']['class'] .= ' ' . $style['body_custom_class'];

			$style['body']['style'] = $style['body_custom_style'];

			$process_cache[$setting] = $style;
		}
		elseif ($process)
			$style = $process_cache[$setting];
	}

	return $style;
}

/**
 * Returns the number of views and comments for a given article
 *
 * @param int $id the id of the article
 */
function sportal_get_article_views_comments($id)
{
	$db = database();

	if (empty($id))
		return array(0, 0);

	// Make the request
	$request = $db->query('', '
		SELECT
			views, comments
		FROM {db_prefix}sp_articles
		WHERE id_article = {int:article_id}
		LIMIT {int:limit}',
		array(
			'article_id' => $id,
			'limit' => 1,
		)
	);
	$result = array(0, 0);
	while($row = $db->fetch_assoc($request))
	{
		$result[0] = $row['views'];
		$result[1] = $row['comments'];
	}
	$db->free_result($request);

	return $result;
}

/**
 * Loads an article by id, or articles by namespace
 *
 * @param int|string|null $article_id id of an article or string for the articles in a namespace
 * @param boolean $active true to only return items that are active
 * @param boolean $allowed true to check permission access to the item
 * @param string $sort string passed to the order by parameter
 * @param int|null $category_id id of the category
 * @param int|null $limit limit the number of results
 * @param int|null $start start number for pages
 */
function sportal_get_articles($article_id = null, $active = false, $allowed = false, $sort = 'spa.title', $category_id = null, $limit = null, $start = null)
{
	global $scripturl, $context, $modSettings, $color_profile;

	$db = database();

	$query = array();
	$parameters = array('sort' => $sort);

	// Load up an article or namspace
	if (!empty($article_id) && is_int($article_id))
	{
		$query[] = 'spa.id_article = {int:article_id}';
		$parameters['article_id'] = (int) $article_id;
	}
	elseif (!empty($article_id))
	{
		$query[] = 'spa.namespace = {string:namespace}';
		$parameters['namespace'] = $article_id;
	}

	// Want articles only from a specific category
	if (!empty($category_id))
	{
		$query[] = 'spa.id_category = {int:category_id}';
		$parameters['category_id'] = (int) $category_id;
	}

	// Checking access?
	if (!empty($allowed))
	{
		$query[] = sprintf($context['SPortal']['permissions']['query'], 'spa.permissions');
		$query[] = sprintf($context['SPortal']['permissions']['query'], 'spc.permissions');
	}

	// Its an active article and category?
	if (!empty($active))
	{
		$query[] = 'spa.status = {int:article_status}';
		$parameters['article_status'] = 1;
		$query[] = 'spc.status = {int:category_status}';
		$parameters['category_status'] = 1;
	}

	// Limits?
	if (!empty($limit))
	{
		$parameters['limit'] = $limit;
		$parameters['start'] = !empty($start) ? $start : 0;
	}

	// Make the request
	$request = $db->query('', '
		SELECT
			spa.id_article, spa.id_category, spa.namespace AS article_namespace, spa.title, spa.body,
			spa.type, spa.date, spa.status, spa.permissions AS article_permissions, spa.views, spa.comments,
			spc.permissions AS category_permissions, spc.name, spc.namespace AS category_namespace,
			m.avatar, IFNULL(m.id_member, 0) AS id_author, IFNULL(m.real_name, spa.member_name) AS author_name, m.email_address,
			a.id_attach, a.attachment_type, a.filename
		FROM {db_prefix}sp_articles AS spa
			INNER JOIN {db_prefix}sp_categories AS spc ON (spc.id_category = spa.id_category)
			LEFT JOIN {db_prefix}members AS m ON (m.id_member = spa.id_member)
			LEFT JOIN {db_prefix}attachments AS a ON (a.id_member = m.id_member)' . (!empty($query) ? '
		WHERE ' . implode(' AND ', $query) : '') . '
		ORDER BY {raw:sort}' . (!empty($limit) ? '
		LIMIT {int:start}, {int:limit}' : ''), $parameters
	);
	$return = array();
	$member_ids = array();
	while ($row = $db->fetch_assoc($request))
	{
		if (!empty($row['id_author']))
			$member_ids[$row['id_author']] = $row['id_author'];

		// Set up avatar to the right size, per forum settings
		if ($modSettings['avatar_action_too_large'] === 'option_html_resize' || $modSettings['avatar_action_too_large'] === 'option_js_resize')
		{
			$avatar_width = !empty($modSettings['avatar_max_width_external']) ? ' width="' . $modSettings['avatar_max_width_external'] . '"' : '';
			$avatar_height = !empty($modSettings['avatar_max_height_external']) ? ' height="' . $modSettings['avatar_max_height_external'] . '"' : '';
		}
		else
		{
			$avatar_width = '';
			$avatar_height = '';
		}

		$return[$row['id_article']] = array(
			'id' => $row['id_article'],
			'category' => array(
				'id' => $row['id_category'],
				'category_id' => $row['category_namespace'],
				'name' => $row['name'],
				'href' => $scripturl . '?category=' . $row['category_namespace'],
				'link' => '<a href="' . $scripturl . '?category=' . $row['category_namespace'] . '">' . $row['name'] . '</a>',
				'permissions' => $row['category_permissions'],
			),
			'author' => array(
				'id' => $row['id_author'],
				'name' => $row['author_name'],
				'href' => $scripturl . '?action=profile;u=' . $row['id_author'],
				'link' => $row['id_author'] ? ('<a href="' . $scripturl . '?action=profile;u=' . $row['id_author'] . '">' . $row['author_name'] . '</a>') : $row['author_name'],
				'avatar' => determineAvatar(array(
					'avatar' => $row['avatar'],
					'filename' => $row['filename'],
					'id_attach' => $row['id_attach'],
					'email_address' => $row['email_address'],
					'attachment_type' => $row['attachment_type'],
				)),
			),
			'article_id' => $row['article_namespace'],
			'title' => $row['title'],
			'href' => $scripturl . '?article=' . $row['article_namespace'],
			'link' => '<a href="' . $scripturl . '?article=' . $row['article_namespace'] . '">' . $row['title'] . '</a>',
			'body' => $row['body'],
			'type' => $row['type'],
			'date' => $row['date'],
			'permissions' => $row['article_permissions'],
			'views' => $row['views'],
			'comments' => $row['comments'],
			'status' => $row['status'],
		);
	}
	$db->free_result($request);

	// Use color profiles?
	if (!empty($member_ids) && sp_loadColors($member_ids) !== false)
	{
		foreach ($return as $key => $value)
		{
			if (!empty($color_profile[$value['author']['id']]['link']))
				$return[$key]['author']['link'] = $color_profile[$value['author']['id']]['link'];
		}
	}

	// Return one or all
	return !empty($article_id) ? current($return) : $return;
}

/**
 * Returns the count of articles in a given category
 *
 * @param ing $catid category identifier
 */
function sportal_get_articles_in_cat_count($catid)
{
	global $context;

	$db = database();

	$request = $db->query('', '
		SELECT COUNT(*)
		FROM {db_prefix}sp_articles
		WHERE status = {int:article_status}
			AND {raw:article_permissions}
			AND id_category = {int:current_category}
		LIMIT {int:limit}',
		array(
			'article_status' => 1,
			'article_permissions' => sprintf($context['SPortal']['permissions']['query'], 'permissions'),
			'current_category' => $catid,
			'limit' => 1,
		)
	);
	list ($total_articles) = $db->fetch_row($request);
	$db->free_result($request);

	return $total_articles;
}

/**
 * Returns the number of articles in the system that a user can see
 */
function sportal_get_articles_count()
{
	global $context;

	$db = database();

	$request = $db->query('', '
		SELECT COUNT(*)
		FROM {db_prefix}sp_articles AS spa
			INNER JOIN {db_prefix}sp_categories AS spc ON (spc.id_category = spa.id_category)
		WHERE spa.status = {int:article_status}
			AND spc.status = {int:category_status}
			AND {raw:article_permissions}
			AND {raw:category_permissions}
		LIMIT {int:limit}',
		array(
			'article_status' => 1,
			'category_status' => 1,
			'article_permissions' => sprintf($context['SPortal']['permissions']['query'], 'spa.permissions'),
			'category_permissions' => sprintf($context['SPortal']['permissions']['query'], 'spc.permissions'),
			'limit' => 1,
		)
	);
	list ($total_articles) = $db->fetch_row($request);
	$db->free_result($request);

	return $total_articles;
}

/**
 * Fetches the number of comments a given article has received
 *
 * @param int $id article id
 */
function sportal_get_article_comment_count($id)
{
	$db = database();

	$request = $db->query('', '
		SELECT COUNT(*)
		FROM {db_prefix}sp_comments
		WHERE id_article = {int:current_article}
		LIMIT {int:limit}',
		array(
			'current_article' => $id,
			'limit' => 1,
		)
	);
	list ($total_comments) = $db->fetch_row($request);
	$db->free_result($request);

	return $total_comments;
}

/**
 * Loads a category by id, or categorys by namespace
 *
 * @param int|string|null $category_id
 * @param boolean $active
 * @param boolean $allowed
 * @param string $sort
 */
function sportal_get_categories($category_id = null, $active = false, $allowed = false, $sort = 'name')
{
	global $scripturl, $context;

	$db = database();

	$query = array();
	$parameters = array('sort' => $sort);

	// Asking for a specific category or the namespace
	if (!empty($category_id) && is_int($category_id))
	{
		$query[] = 'id_category = {int:category_id}';
		$parameters['category_id'] = (int) $category_id;
	}
	elseif (!empty($category_id))
	{
		$query[] = 'namespace = {string:namespace}';
		$parameters['namespace'] = $category_id;
	}

	// Check permissions to access this category
	if (!empty($allowed))
		$query[] = sprintf($context['SPortal']['permissions']['query'], 'permissions');

	// Check if the category is even active
	if (!empty($active))
	{
		$query[] = 'status = {int:status}';
		$parameters['status'] = 1;
	}

	// Lets see what we can find
	$request = $db->query('', '
		SELECT
			id_category, namespace, name, description,
			permissions, articles, status
		FROM {db_prefix}sp_categories' . (!empty($query) ? '
		WHERE ' . implode(' AND ', $query) : '') . '
		ORDER BY {raw:sort}', $parameters
	);
	$return = array();
	while ($row = $db->fetch_assoc($request))
	{
		$return[$row['id_category']] = array(
			'id' => $row['id_category'],
			'category_id' => $row['namespace'],
			'name' => $row['name'],
			'href' => $scripturl . '?category=' . $row['namespace'],
			'link' => '<a href="' . $scripturl . '?category=' . $row['namespace'] . '">' . $row['name'] . '</a>',
			'description' => $row['description'],
			'permissions' => $row['permissions'],
			'articles' => $row['articles'],
			'status' => $row['status'],
		);
	}
	$db->free_result($request);

	// Return the id or the whole batch
	return !empty($category_id) ? current($return) : $return;
}

/**
 * Loads all the comments that an article has generated
 *
 * @param int|null $article_id
 * @param int|null $limit limit the number of results
 * @param int|null $start start number for pages
 */
function sportal_get_comments($article_id = null, $limit = null, $start = null)
{
	global $scripturl, $user_info, $modSettings;

	$db = database();

	$request = $db->query('', '
		SELECT
			spc.id_comment, IFNULL(spc.id_member, 0) AS id_author,
			IFNULL(m.real_name, spc.member_name) AS author_name,
			spc.body, spc.log_time,
			m.avatar,
			a.id_attach, a.attachment_type, a.filename
		FROM {db_prefix}sp_comments AS spc
			LEFT JOIN {db_prefix}members AS m ON (m.id_member = spc.id_member)
			LEFT JOIN {db_prefix}attachments AS a ON (a.id_member = m.id_member)
		WHERE spc.id_article = {int:article_id}
		ORDER BY spc.id_comment' . (!empty($limit) ? '
		LIMIT {int:start}, {int:limit}' : ''),
		array(
			'article_id' => (int) $article_id,
			'limit' => (int) $limit,
			'start' => (int) $start,
		)
	);
	$return = array();
	$member_ids = array();
	while ($row = $db->fetch_assoc($request))
	{
		if (!empty($row['id_author']))
			$member_ids[$row['id_author']] = $row['id_author'];

		if ($modSettings['avatar_action_too_large'] == 'option_html_resize' || $modSettings['avatar_action_too_large'] == 'option_js_resize')
		{
			$avatar_width = !empty($modSettings['avatar_max_width_external']) ? ' width="' . $modSettings['avatar_max_width_external'] . '"' : '';
			$avatar_height = !empty($modSettings['avatar_max_height_external']) ? ' height="' . $modSettings['avatar_max_height_external'] . '"' : '';
		}
		else
		{
			$avatar_width = '';
			$avatar_height = '';
		}

		$return[$row['id_comment']] = array(
			'id' => $row['id_comment'],
			'body' => parse_bbc($row['body']),
			'time' => standardTime($row['log_time']),
			'author' => array(
				'id' => $row['id_author'],
				'name' => $row['author_name'],
				'href' => $scripturl . '?action=profile;u=' . $row['id_author'],
				'link' => $row['id_author'] ? ('<a href="' . $scripturl . '?action=profile;u=' . $row['id_author'] . '">' . $row['author_name'] . '</a>') : $row['author_name'],
				'avatar' => array(
					'name' => $row['avatar'],
					'image' => $row['avatar'] == '' ? ($row['id_attach'] > 0 ? '<img src="' . (empty($row['attachment_type']) ? $scripturl . '?action=dlattach;attach=' . $row['id_attach'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $row['filename']) . '" alt="" class="avatar" border="0" />' : '') : (stristr($row['avatar'], 'http://') ? '<img src="' . $row['avatar'] . '"' . $avatar_width . $avatar_height . ' alt="" class="avatar" border="0" />' : '<img src="' . $modSettings['avatar_url'] . '/' . htmlspecialchars($row['avatar']) . '" alt="" class="avatar" border="0" />'),
					'href' => $row['avatar'] == '' ? ($row['id_attach'] > 0 ? (empty($row['attachment_type']) ? $scripturl . '?action=dlattach;attach=' . $row['id_attach'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $row['filename']) : '') : (stristr($row['avatar'], 'http://') ? $row['avatar'] : $modSettings['avatar_url'] . '/' . $row['avatar']),
					'url' => $row['avatar'] == '' ? '' : (stristr($row['avatar'], 'http://') ? $row['avatar'] : $modSettings['avatar_url'] . '/' . $row['avatar'])
				),
			),
			'can_moderate' => allowedTo('sp_admin') || allowedTo('sp_manage_articles') || (!$user_info['is_guest'] && $user_info['id'] == $row['id_author']),
		);
	}
	$db->free_result($request);

	// Colorization
	if (!empty($member_ids) && sp_loadColors($member_ids) !== false)
	{
		foreach ($return as $key => $value)
		{
			if (!empty($color_profile[$value['author']['id']]['link']))
				$return[$key]['author']['link'] = $color_profile[$value['author']['id']]['link'];
		}
	}

	return $return;
}

/**
 * Creates a new comment response to a published article
 *
 * @param int $article_id id of the article commented on
 * @param string $body text of the comment
 */
function sportal_create_comment($article_id, $body)
{
	global $user_info;

	$db = database();

	$db->insert('',
		'{db_prefix}sp_comments',
		array(
			'id_article' => 'int',
			'id_member' => 'int',
			'member_name' => 'string',
			'log_time' => 'int',
			'body' => 'string',
		),
		array(
			$article_id,
			$user_info['id'],
			$user_info['name'],
			time(),
			$body,
		),
		array('id_comment')
	);

	// Increase the comment count
	sportal_recount_comments($article_id);
}

/**
 * Edits an article comment that has already been made
 *
 * @param int $comment_id comment id to edit
 * @param string $body replacement text
 */
function sportal_modify_comment($comment_id, $body)
{
	$db = database();

	$db->query('', '
		UPDATE {db_prefix}sp_comments
		SET body = {text:body}
		WHERE id_comment = {int:comment_id}',
		array(
			'comment_id' => $comment_id,
			'body' => $body,
		)
	);
}

/**
 * Removes an comment from an article
 *
 * @param int $article_id article id, needed for the counter
 * @param int $comment_id comment it
 */
function sportal_delete_comment($article_id, $comment_id)
{
	$db = database();

	// Poof ... gone
	$db->query('', '
		DELETE FROM {db_prefix}sp_comments
		WHERE id_comment = {int:comment_id}',
		array(
			'comment_id' => $comment_id,
		)
	);

	// One less comment
	sportal_recount_comments($article_id);
}

/**
 * Recounts all comments made on an article and updates the DB with the new value
 *
 * @param int $article_id
 */
function sportal_recount_comments($article_id)
{
	$db = database();

	// How many comments were made for this article
	$request = $db->query('', '
		SELECT COUNT(*)
		FROM {db_prefix}sp_comments
		WHERE id_article = {int:article_id}
		LIMIT {int:limit}',
		array(
			'article_id' => $article_id,
			'limit' => 1,
		)
	);
	list ($comments) = $db->fetch_row($request);
	$db->free_result($request);

	// Update the article comment count
	$db->query('', '
		UPDATE {db_prefix}sp_articles
		SET comments = {int:comments}
		WHERE id_article = {int:article_id}',
		array(
			'article_id' => $article_id,
			'comments' => $comments,
		)
	);
}

/**
 * Load a page by ID
 *
 * @param int|null $page_id
 * @param boolean $active
 * @param boolean $allowed
 * @param string $sort
 */
function sportal_get_pages($page_id = null, $active = false, $allowed = false, $sort = 'title')
{
	global $context, $scripturl;
	static $cache;

	$db = database();

	// If we already have the information, just return it
	$cache_name = implode(':', array($page_id, $active, $allowed));
	if (isset($cache[$cache_name]))
		$return = $cache[$cache_name];
	else
	{
		$query = array();
		$parameters = array('sort' => $sort);

		// Page id or Page Namespace
		if (!empty($page_id) && is_int($page_id))
		{
			$query[] = 'id_page = {int:page_id}';
			$parameters['page_id'] = $page_id;
		}
		elseif (!empty($page_id))
		{
			$query[] = 'namespace = {string:namespace}';
			$parameters['namespace'] = Util::htmlspecialchars((string) $page_id, ENT_QUOTES);
		}

		// Use permissions?
		if (!empty($allowed))
			$query[] = sprintf($context['SPortal']['permissions']['query'], 'permissions');

		// Only active pages?
		if (!empty($active))
		{
			$query[] = 'status = {int:status}';
			$parameters['status'] = 1;
		}

		// Make the page request
		$request = $db->query('', '
			SELECT
				id_page, namespace, title, body, type, permissions, views, style, status
			FROM {db_prefix}sp_pages' . (!empty($query) ? '
			WHERE ' . implode(' AND ', $query) : '') . '
			ORDER BY {raw:sort}', $parameters
		);
		$return = array();
		while ($row = $db->fetch_assoc($request))
		{
			$return[$row['id_page']] = array(
				'id' => $row['id_page'],
				'page_id' => $row['namespace'],
				'title' => $row['title'],
				'href' => $scripturl . '?page=' . $row['namespace'],
				'link' => '<a href="' . $scripturl . '?page=' . $row['namespace'] . '">' . $row['title'] . '</a>',
				'body' => $row['body'],
				'type' => $row['type'],
				'permissions' => $row['permissions'],
				'views' => $row['views'],
				'style' => $row['style'],
				'status' => $row['status'],
			);
		}
		$db->free_result($request);

		// Save this so we don't have to do it again
		$cache[$cache_name] = $return;
	}

	return !empty($page_id) ? current($return) : $return;
}

/**
 * Prepare body text to be of type, html, bbc, php, etc
 *
 * @param string $body
 * @param string $type
 */
function sportal_parse_content($body, $type)
{
	if ($type == 'bbc')
		echo parse_bbc($body);
	elseif ($type == 'html')
		echo un_htmlspecialchars($body);
	elseif ($type == 'php')
	{
		$body = trim(un_htmlspecialchars($body));
		$body = trim($body, '<?php');
		$body = trim($body, '?>');
		eval($body);
	}
}

/**
 * Returns the details system profiles
 *
 * What is does:
 * - If no profile id is supplied, all profiles are returned
 * - If type = 1 (generally the case), the profile group permsissions are returned
 *
 * @param int|null $profile_id
 * @param int $type
 * @param string $sort
 */
function sportal_get_profiles($profile_id = null, $type = null, $sort = 'id_profile')
{
	global $txt;

	$db = database();

	$query = array();
	$parameters = array('sort' => $sort);

	if (isset($profile_id))
	{
		$query[] = 'id_profile = {int:profile_id}';
		$parameters['profile_id'] = (int) $profile_id;
	}

	if (isset($type))
	{
		$query[] = 'type = {int:type}';
		$parameters['type'] = (int) $type;
	}

	$request = $db->query('', '
		SELECT
			id_profile, type, name, value
		FROM {db_prefix}sp_profiles' . (!empty($query) ? '
		WHERE ' . implode(' AND ', $query) : '') . '
		ORDER BY {raw:sort}',
		$parameters
	);
	$return = array();
	while ($row = $db->fetch_assoc($request))
	{
		$return[$row['id_profile']] = array(
			'id' => $row['id_profile'],
			'name' => $row['name'],
			'label' => isset($txt['sp_admin_profiles' . substr($row['name'], 1)]) ? $txt['sp_admin_profiles' . substr($row['name'], 1)] : $row['name'],
			'type' => $row['type'],
			'value' => $row['value'],
		);

		// Get the permisssions
		if ($row['type'] == 1)
		{
			list ($groups_allowed, $groups_denied) = explode('|', $row['value']);

			$return[$row['id_profile']] = array_merge($return[$row['id_profile']], array(
				'groups_allowed' => $groups_allowed !== '' ? explode(',', $groups_allowed) : array(),
				'groups_denied' => $groups_denied !== '' ? explode(',', $groups_denied) : array(),
			));
		}
	}

	$db->free_result($request);

	return !empty($profile_id) ? current($return) : $return;
}

/**
 * Load a shoutboxs parameters by ID
 *
 * @param int|null $shoutbox_id
 * @param boolean $active
 * @param boolean $allowed
 */
function sportal_get_shoutbox($shoutbox_id = null, $active = false, $allowed = false)
{
	global $context;

	$db = database();

	$query = array();
	$parameters = array();

	if ($shoutbox_id !== null)
	{
		$query[] = 'id_shoutbox = {int:shoutbox_id}';
		$parameters['shoutbox_id'] = $shoutbox_id;
	}
	if (!empty($allowed))
		$query[] = sprintf($context['SPortal']['permissions']['query'], 'permissions');
	if (!empty($active))
	{
		$query[] = 'status = {int:status}';
		$parameters['status'] = 1;
	}

	$request = $db->query('', '
		SELECT
			id_shoutbox, name, permissions, moderator_groups, warning,
			allowed_bbc, height, num_show, num_max, refresh, reverse,
			caching, status, num_shouts, last_update
		FROM {db_prefix}sp_shoutboxes' . (!empty($query) ? '
		WHERE ' . implode(' AND ', $query) : '') . '
		ORDER BY name',
		$parameters
	);
	$return = array();
	while ($row = $db->fetch_assoc($request))
	{
		$return[$row['id_shoutbox']] = array(
			'id' => $row['id_shoutbox'],
			'name' => $row['name'],
			'permissions' => $row['permissions'],
			'moderator_groups' => $row['moderator_groups'] !== '' ? explode(',', $row['moderator_groups']) : array(),
			'warning' => $row['warning'],
			'allowed_bbc' => explode(',', $row['allowed_bbc']),
			'height' => $row['height'],
			'num_show' => $row['num_show'],
			'num_max' => $row['num_max'],
			'refresh' => $row['refresh'],
			'reverse' => $row['reverse'],
			'caching' => $row['caching'],
			'status' => $row['status'],
			'num_shouts' => $row['num_shouts'],
			'last_update' => $row['last_update'],
		);
	}
	$db->free_result($request);

	return !empty($shoutbox_id) ? current($return) : $return;
}

function sportal_get_shouts($shoutbox, $parameters)
{
	global $scripturl, $context, $user_info, $modSettings, $options, $txt;

	$db = database();

	$shoutbox = !empty($shoutbox) ? (int) $shoutbox : 0;
	$start = !empty($parameters['start']) ? (int) $parameters['start'] : 0;
	$limit = !empty($parameters['limit']) ? (int) $parameters['limit'] : 20;
	$bbc = !empty($parameters['bbc']) ? $parameters['bbc'] : array();
	$reverse = !empty($parameters['reverse']);
	$cache = !empty($parameters['cache']);
	$can_delete = !empty($parameters['can_moderate']);

	if (!empty($start) || !$cache || ($shouts = cache_get_data('shoutbox_shouts-' . $shoutbox, 240)) === null)
	{
		$request = $db->query('', '
			SELECT
				sh.id_shout, sh.body, IFNULL(mem.id_member, 0) AS id_member,
				IFNULL(mem.real_name, sh.member_name) AS member_name, sh.log_time,
				mg.online_color AS member_group_color, pg.online_color AS post_group_color
			FROM {db_prefix}sp_shouts AS sh
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = sh.id_member)
				LEFT JOIN {db_prefix}membergroups AS pg ON (pg.id_group = mem.id_post_group)
				LEFT JOIN {db_prefix}membergroups AS mg ON (mg.id_group = mem.id_group)
			WHERE sh.id_shoutbox = {int:id_shoutbox}
			ORDER BY sh.id_shout DESC
			LIMIT {int:start}, {int:limit}',
			array(
				'id_shoutbox' => $shoutbox,
				'start' => $start,
				'limit' => $limit,
			)
		);
		$shouts = array();
		while ($row = $db->fetch_assoc($request))
		{
			// Disable the aeva mod for the shoutbox.
			$context['aeva_disable'] = true;
			$online_color = !empty($row['member_group_color']) ? $row['member_group_color'] : $row['post_group_color'];
			$shouts[$row['id_shout']] = array(
				'id' => $row['id_shout'],
				'author' => array(
					'id' => $row['id_member'],
					'name' => $row['member_name'],
					'link' => $row['id_member'] ? ('<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '" title="' . $txt['on'] . ' ' . strip_tags(standardTime($row['log_time'])) . '"' . (!empty($online_color) ? ' style="color: ' . $online_color . ';"' : '') . '>' . $row['member_name'] . '</a>') : $row['member_name'],
					'color' => $online_color,
				),
				'time' => $row['log_time'],
				'text' => parse_bbc($row['body'], true, '', $bbc),
			);
		}
		$db->free_result($request);

		if (empty($start) && $cache)
			cache_put_data('shoutbox_shouts-' . $shoutbox, $shouts, 240);
	}

	foreach ($shouts as $shout)
	{
		if (preg_match('~^@(.+?): ~u', $shout['text'], $target) && Util::strtolower($target[1]) !== Util::strtolower($user_info['name']) && $shout['author']['id'] != $user_info['id'] && !$user_info['is_admin'])
		{
			unset($shouts[$shout['id']]);
			continue;
		}

		$shouts[$shout['id']] += array(
			'is_me' => preg_match('~^<div\sclass="meaction">\* ' . preg_quote($shout['author']['name'], '~') . '.+</div>$~', $shout['text']) != 0,
			'delete_link' => $can_delete ? '<a class="dot dotdelete" href="' . $scripturl . '?action=shoutbox;shoutbox_id=' . $shoutbox . ';delete=' . $shout['id'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '"></a> ' : '',
			'delete_link_js' => $can_delete ? '<a class="dot dotdelete" href="' . $scripturl . '?action=shoutbox;shoutbox_id=' . $shoutbox . ';delete=' . $shout['id'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '" onclick="sp_delete_shout(' . $shoutbox . ', ' . $shout['id'] . ', \'' . $context['session_var'] . '\', \'' . $context['session_id'] . '\'); return false;"></a> ' : '',
		);

		$shouts[$shout['id']]['text'] = str_replace(':jade:', '<img src="http://www.simpleportal.net/sp/cheerleader.png" alt="Jade!" />', $shouts[$shout['id']]['text']);
		$shouts[$shout['id']]['time'] = standardTime($shouts[$shout['id']]['time']);
		$shouts[$shout['id']]['text'] = preg_replace('~(</?)div([^<]*>)~', '$1span$2', $shouts[$shout['id']]['text']);
		$shouts[$shout['id']]['text'] = preg_replace('~<a([^>]+>)([^<]+)</a>~', '<a$1' . $txt['sp_link'] . '</a>', $shouts[$shout['id']]['text']);
		$shouts[$shout['id']]['text'] = censorText($shouts[$shout['id']]['text']);

		if (!empty($modSettings['enable_buddylist']) && !empty($options['posts_apply_ignore_list']) && in_array($shout['author']['id'], $context['user']['ignoreusers']))
			$shouts[$shout['id']]['text'] = '<a href="#toggle" id="ignored_shout_link_' . $shout['id'] . '" onclick="sp_show_ignored_shout(' . $shout['id'] . '); return false;">[' . $txt['sp_shoutbox_show_ignored'] . ']</a><span id="ignored_shout_' . $shout['id'] . '" style="display: none;">' . $shouts[$shout['id']]['text'] . '</span>';
	}

	if ($reverse)
		$shouts = array_reverse($shouts);

	return $shouts;
}

/**
 * Return the number of shouts in a given shoutbox
 *
 * @param int $shoutbox_id ID of the shoutbox
 */
function sportal_get_shoutbox_count($shoutbox_id)
{
	$db = database();
	$total_shouts = 0;

	$request = $db->query('', '
		SELECT COUNT(*)
		FROM {db_prefix}sp_shouts
		WHERE id_shoutbox = {int:current}',
		array(
			'current' => $shoutbox_id,
		)
	);
	list ($total_shouts) = $db->fetch_row($request);
	$db->free_result($request);

	return $total_shouts;
}

/**
 * Adds a new shout to a given box
 *
 * - Prevents guest from adding a shout
 * - Checks the shout total and archives if over the display limit for the box
 *
 * @param int $shoutbox
 * @param string $shout
 */
function sportal_create_shout($shoutbox, $shout)
{
	global $user_info;

	$db = database();

	// If a guest shouts in the woods, and no one is there to here them
	if ($user_info['is_guest'])
		return false;

	// What, it not like we can shout to nothing
	if (empty($shoutbox))
		return false;

	if (trim(strip_tags(parse_bbc($shout, false), '<img>')) === '')
		return false;

	// Add the shout
	$db->insert('', '
		{db_prefix}sp_shouts',
		array('id_shoutbox' => 'int', 'id_member' => 'int', 'member_name' => 'string', 'log_time' => 'int', 'body' => 'string',),
		array($shoutbox['id'], $user_info['id'], $user_info['name'], time(), $shout, ),
		array('id_shout')
	);

	// To many shouts in the box, then its archive maintance time
	$shoutbox['num_shouts']++;
	if ($shoutbox['num_shouts'] > $shoutbox['num_max'])
	{
		$request = $db->query('', '
			SELECT id_shout
			FROM {db_prefix}sp_shouts
			WHERE id_shoutbox = {int:shoutbox}
			ORDER BY log_time
			LIMIT {int:limit}',
			array(
				'shoutbox' => $shoutbox['id'],
				'limit' => $shoutbox['num_shouts'] - $shoutbox['num_max'],
			)
		);
		$old_shouts = array();
		while ($row = $db->fetch_assoc($request))
			$old_shouts[] = $row['id_shout'];
		$db->free_result($request);

		sportal_delete_shout($shoutbox['id'], $old_shouts, true);
	}
	else
		sportal_update_shoutbox($shoutbox['id'], true);
}

/**
 * Removes a shout from the shoutbox by id
 *
 * @param int $shoutbox_id
 * @param int[]|int $shouts
 * @param boolean $prune
 */
function sportal_delete_shout($shoutbox_id, $shouts, $prune = false)
{
	$db = database();

	if (!is_array($shouts))
		$shouts = array($shouts);

	// Remove it
	$db->query('', '
		DELETE FROM {db_prefix}sp_shouts
		WHERE id_shout IN ({array_int:shouts})',
		array(
			'shouts' => $shouts,
		)
	);

	// Update the view
	sportal_update_shoutbox($shoutbox_id, $prune ? count($shouts) - 1 : count($shouts));
}

/**
 * Updates the number of shouts for a given shoutbox
 *
 * Can supply a specific number to update the count to
 * Use true to increment by 1
 *
 * @param int $shoutbox_id
 * @param int $num_shouts
 */
function sportal_update_shoutbox($shoutbox_id, $num_shouts = 0)
{
	$db = database();

	$db->query('', '
		UPDATE {db_prefix}sp_shoutboxes
		SET last_update = {int:time}' . ($num_shouts === 0 ? '' : ',
			num_shouts = {raw:shouts}') . '
		WHERE id_shoutbox = {int:shoutbox}',
		array(
			'shoutbox' => $shoutbox_id,
			'time' => time(),
			'shouts' => $num_shouts === true ? 'num_shouts + 1' : 'num_shouts - ' . $num_shouts,
		)
	);

	cache_put_data('shoutbox_shouts-' . $shoutbox_id, null, 240);
}

function sp_prevent_flood($type, $fatal = true)
{
	global $modSettings, $user_info, $txt;

	$db = database();

	$limits = array(
		'spsbp' => 5,
	);

	if (!allowedTo('admin_forum'))
		$time_limit = isset($limits[$type]) ? $limits[$type] : $modSettings['spamWaitTime'];
	else
		$time_limit = 2;

	$db->query('', '
		DELETE FROM {db_prefix}log_floodcontrol
		WHERE log_time < {int:log_time}
			AND log_type = {string:log_type}',
		array(
			'log_time' => time() - $time_limit,
			'log_type' => $type,
		)
	);

	$db->insert('replace', '
		{db_prefix}log_floodcontrol',
		array('ip' => 'string-16', 'log_time' => 'int', 'log_type' => 'string'),
		array($user_info['ip'], time(), $type),
		array('ip', 'log_type')
	);

	if ($db->affected_rows() != 1)
	{
		if ($fatal)
			fatal_lang_error('error_sp_flood_' . $type, false, array($time_limit));
		else
			return isset($txt['error_sp_flood_' . $type]) ? sprintf($txt['error_sp_flood_' . $type], $time_limit) : true;
	}

	return false;
}