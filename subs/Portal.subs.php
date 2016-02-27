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
 * Initializes the portal, outputs all the blocks as needed
 *
 * @param boolean $standalone
 */
function sportal_init($standalone = false)
{
	global $context, $scripturl, $modSettings, $settings, $maintenance, $sportal_version;

	$sportal_version = '1.0.0 Beta 2';

	if ((isset($_REQUEST['action']) && $_REQUEST['action'] === 'dlattach'))
	{
		return;
	}

	if ((isset($_REQUEST['xml']) || isset($_REQUEST['api'])) && ((isset($_REQUEST['action']) && $_REQUEST['action'] !== 'shoutbox')))
	{
		return;
	}

	// Not running standalone then we need to load in some template information
	if (!$standalone)
	{
		// Load the portal css and the default css if its not yet loaded.
		loadCSSFile('portal.css');

		// rtl css as well?
		if (!empty($context['right_to_left']))
		{
			loadCSSFile('portal_rtl.css');
		}

		if (!empty($_REQUEST['action']) && in_array($_REQUEST['action'], array('admin')))
		{
			loadLanguage('SPortalAdmin');
		}

		if (!isset($settings['sp_images_url']))
		{
			if (file_exists($settings['theme_dir'] . '/images/sp'))
			{
				$settings['sp_images_url'] = $settings['theme_url'] . '/images/sp';
			}
			else
			{
				$settings['sp_images_url'] = $settings['default_theme_url'] . '/images/sp';
			}
		}
	}

	// Portal not enabled, or mobile, or debug, or maintenance, or .... then bow out now
	if (!empty($modSettings['sp_disableMobile']) && empty($_GET['page']) && empty($_GET['article']) || !empty($settings['disable_sp']) || empty($modSettings['sp_portal_mode']) || ((!empty($modSettings['sp_maintenance']) || !empty($maintenance)) && !allowedTo('admin_forum')) || isset($_GET['debug']) || (empty($modSettings['allow_guestAccess']) && $context['user']['is_guest']))
	{
		$context['disable_sp'] = true;

		if ($standalone)
		{
			$get_string = '';
			foreach ($_GET as $get_var => $get_value)
			{
				$get_string .= $get_var . (!empty($get_value) ? '=' . $get_value : '') . ';';
			}

			redirectexit(substr($get_string, 0, -1));
		}

		return;
	}

	// Not standalone means we need to load some portal specific information in to context
	if (!$standalone)
	{
		require_once(SUBSDIR . '/spblocks/SPAbstractBlock.class.php');

		// Not running via ssi then we need to get SSI for its functions
		if (ELK !== 'SSI')
		{
			require_once(BOARDDIR . '/SSI.php');
		}

		// Portal specific templates and language
		loadTemplate('Portal');
		loadLanguage('SPortal');

		if (!empty($modSettings['sp_maintenance']) && !allowedTo('sp_admin'))
		{
			$modSettings['sp_portal_mode'] = 0;
		}

		if (empty($modSettings['sp_standalone_url']))
		{
			$modSettings['sp_standalone_url'] = '';
		}

		if ($modSettings['sp_portal_mode'] == 3)
		{
			$context += array(
				'portal_url' => $modSettings['sp_standalone_url'],
				'page_title' => $context['forum_name'],
			);
		}
		else
		{
			$context += array(
				'portal_url' => $scripturl,
			);
		}

		if ($modSettings['sp_portal_mode'] == 1)
		{
			$context['linktree'][0] = array(
				'url' => $scripturl . '?action=forum',
				'name' => $context['forum_name'],
			);
		}

		if (!empty($context['linktree']) && $modSettings['sp_portal_mode'] == 1)
		{
			foreach ($context['linktree'] as $key => $tree)
			{
				if (strpos($tree['url'], '#c') !== false && strpos($tree['url'], 'action=forum#c') === false)
				{
					$context['linktree'][$key]['url'] = str_replace('#c', '?action=forum#c', $tree['url']);
				}
			}
		}
	}
	else
	{
		$_GET['action'] = 'portal';
	}

	// Load the headers if necessary.
	sportal_init_headers();

	// Load permissions
	sportal_load_permissions();

	// Play with our blocks
	$context['standalone'] = $standalone;
	sportal_load_blocks();

	// Determine if we will show blocks on the portal page
	$context['SPortal']['on_portal'] = sportal_process_visibility('portal');

	// Add the portal template
	if (!Template_Layers::getInstance()->hasLayers(true) && !in_array('portal', Template_Layers::getInstance()->getLayers()))
	{
		Template_Layers::getInstance()->add('portal');
	}
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
	{
		return $initialized;
	}

	// Generate a safe scripturl
	$safe_scripturl = $scripturl;
	$current_request = empty($_SERVER['HTTP_HOST']) ? $_SERVER['SERVER_NAME'] : $_SERVER['HTTP_HOST'];

	if (strpos($scripturl, 'www.') !== false && strpos($current_request, 'www.') === false)
	{
		$safe_scripturl = str_replace('://www.', '://', $scripturl);
	}
	elseif (strpos($scripturl, 'www.') === false && strpos($current_request, 'www.') !== false)
	{
		$safe_scripturl = str_replace('://', '://www.', $scripturl);
	}

	// The shoutbox may fail to function in certain cases without using a safe scripturl
	addJavascriptVar(array('sp_script_url' => '\'' . $safe_scripturl . '\''));

	// Load up some javascript!
	loadJavascriptFile('portal.js?sp110');

	// We use drag and sort blocks for the front page
	$javascript = '';
	if ($modSettings['sp_portal_mode'] == 1)
	{
		$modSettings['jquery_include_ui'] = true;

		// Javascript to allow D&D ordering of the front page blocks, not for guests
		if (empty($_REQUEST['action']) && !($user_info['is_guest'] || $user_info['id'] == 0))
		{
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
		}
	}

	if ($modSettings['sp_resize_images'])
	{
		$javascript .= '
		createEventListener(window);
		window.addEventListener("load", sp_image_resize, false);';
	}

	// Let the template know we have some inline JS to display
	if (!empty($javascript))
	{
		addInlineJavascript($javascript, true);
	}

	// Mark it as done so we don't do it again
	$initialized = true;

	return $initialized;
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
		{
			$result = false;
		}
		elseif (!empty($profile['groups_allowed']) && count(array_intersect($user_info['groups'], $profile['groups_allowed'])) > 0)
		{
			$result = true;
		}

		if ($result)
		{
			$allowed[] = $profile['id'];
		}
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
		{
			resetMemberLayout();
		}
		else
		{
			foreach ($layout as $id => $column)
			{
				if (empty($column) || empty($id) || !$context['SPortal']['sides'][$id]['active'])
				{
					continue;
				}

				// For each custom arranged block
				foreach ($column as $item)
				{
					if (empty($blocks[$item]))
					{
						continue;
					}

					// Style information for the block, based on its style profile
					$blocks[$item]['style'] = sportal_select_style($blocks[$item]['styles']);

					// For each moved block, instantiate it and run setup
					$blocks[$item]['instance'] = sp_instantiate_block($blocks[$item]['type']);
					$blocks[$item]['instance']->setup($blocks[$item]['parameters'], $block['id']);
					$context['SPortal']['blocks'][$id][] = $blocks[$item];

					// Don't do this again
					unset($blocks[$item]);
				}

				$context['SPortal']['blocks']['custom_arrange'] = true;
			}
		}
	}

	if (!isset($context['SPortal']['blocks']))
	{
		$context['SPortal']['blocks'] = array();
	}

	// For each active block, determine it style and get and instance of it for use
	foreach ($blocks as $block)
	{
		if (!$context['SPortal']['sides'][$block['column']]['active'] || empty($block['type']))
		{
			continue;
		}

		$block['style'] = sportal_select_style($block['styles']);

		$block['instance'] = sp_instantiate_block($block['type']);
		$block['instance']->setup($block['parameters'], $block['id']);

		$context['SPortal']['sides'][$block['column']]['last'] = $block['id'];
		$context['SPortal']['blocks'][$block['column']][] = $block;
	}

	foreach ($context['SPortal']['sides'] as $side)
	{
		if (empty($context['SPortal']['blocks'][$side['id']]))
		{
			$context['SPortal']['sides'][$side['id']]['active'] = false;
		}

		$context['SPortal']['sides'][$side['id']]['collapsed'] = $context['user']['is_guest']
			? !empty($_COOKIE['sp_' . $side['name']])
			: !empty($options['sp_' . $side['name']]);
	}
}

/**
 * A shortcut that takes care of instantiating the block and returning the instance
 *
 * @param string $name The name of the block (without "_Block" at the end)
 */
function sp_instantiate_block($name)
{
	static $instances = array(), $db = null;

	if ($db === null)
	{
		$db = database();
	}

	if (!isset($instances[$name]))
	{
		require_once(SUBSDIR . '/spblocks/' . str_replace('_', '', $name) . '.block.php');

		$class = $name . '_Block';
		$instances[$name] = new $class($db);
	}

	$instance = $instances[$name];

	return $instance;
}

/**
 * If a member has arranged blocks in some bizarre fashion, this will reset the layout to
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

	// Clear the user theme cache to reflect the changes now
	cache_put_data('theme_settings-' . $settings['theme_id'] . ':' . $user_info['id'], null, 60);
}

/**
 * This function, returns all of the information about particular blocks.
 *
 * @param int|null $column_id
 * @param int|null $block_id
 * @param boolean|null $state
 * @param boolean|null $show
 * @param boolean|null $permission
 *
 * @return array of block values
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
	{
		$query[] = sprintf($context['SPortal']['permissions']['query'], 'spb.permissions');
	}

	if (!empty($state))
	{
		$query[] = 'spb.state = {int:state}';
		$parameters['state'] = 1;
	}

	$request = $db->query('', '
		SELECT
			spb.id_block, spb.label, spb.type, spb.col, spb.row, spb.permissions, spb.state,
			spb.force_view, spb.visibility, spb.styles, spp.variable, spp.value
		FROM {db_prefix}sp_blocks AS spb
			LEFT JOIN {db_prefix}sp_parameters AS spp ON (spp.id_block = spb.id_block)' . (!empty($query) ? '
		WHERE ' . implode(' AND ', $query) : '') . '
		ORDER BY spb.col, spb.row', $parameters
	);
	$return = array();
	while ($row = $db->fetch_assoc($request))
	{
		if (!empty($show) && !sportal_check_visibility($row['visibility']))
		{
			continue;
		}

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
				'styles' => $row['styles'],
				'visibility' => $row['visibility'],
				'state' => empty($row['state']) ? 0 : 1,
				'force_view' => $row['force_view'],
				'collapsed' => $context['user']['is_guest'] ? !empty($_COOKIE['sp_block_' . $row['id_block']]) : !empty($options['sp_block_' . $row['id_block']]),
				'parameters' => array(),
			);
		}

		if (!empty($row['variable']))
		{
			$return[$row['id_block']]['parameters'][$row['variable']] = $row['value'];
		}
	}
	$db->free_result($request);

	return $return;
}

/**
 * Function to get a block's display/show information.
 *
 * @param string[]|string|null $query
 *
 * @return array
 */
function sportal_process_visibility($query)
{
	global $context, $modSettings;

	// Fetch any page, category or article as needed
	if (!empty($_GET['page']) && (empty($context['current_action']) || $context['current_action'] === 'portal'))
	{
		$page_info = sportal_get_pages($_GET['page'], true, true);
	}

	if (!empty($_GET['category']) && (empty($context['current_action']) || $context['current_action'] === 'portal'))
	{
		$category_info = sportal_get_categories($_GET['category'], true, true);
	}

	if (!empty($_GET['article']) && (empty($context['current_action']) || $context['current_action'] === 'portal'))
	{
		$article_info = sportal_get_articles($_GET['article'], true, true);
	}

	// Some variables for easy checking.
	$action = !empty($context['current_action']) ? $context['current_action'] : '';
	$sub_action = !empty($context['current_subaction']) ? $context['current_subaction'] : '';
	$board = !empty($context['current_board']) ? 'b' . $context['current_board'] : '';
	$topic = !empty($context['current_topic']) ? 't' . $context['current_topic'] : '';
	$page = !empty($page_info['id']) ? 'p' . $page_info['id'] : '';
	$category = !empty($category_info['id']) ? 'c' . $category_info['id'] : '';
	$article = !empty($article_info['id']) ? 'a' . $article_info['id'] : '';
	$portal = (empty($action) && empty($sub_action) && empty($board) && empty($topic) && empty($page) && empty($category) && empty($article) && ELK !== 'SSI' && $modSettings['sp_portal_mode'] == 1) || $action === 'portal' || !empty($context['standalone']) ? true : false;
	$forum = (empty($action) && empty($sub_action) && empty($board) && empty($topic) && empty($page) && empty($category) && empty($article) && ELK !== 'SSI' && $modSettings['sp_portal_mode'] != 1) || $action === 'forum';

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

	// Set some action exceptions so they use a common root name
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
			{
				continue;
			}

			if (!isset($portal_actions[$key]))
			{
				$portal = false;
			}
			elseif (is_array($portal_actions[$key]) && !in_array($value, $portal_actions[$key]))
			{
				$portal = false;
			}
		}
	}

	// Set the action to a common one, eg reminder => login
	foreach ($exceptions as $key => $exception)
	{
		if (in_array($action, $exception))
		{
			$action = $key;
		}
	}

	// Complex display options first...
	if (($boundary = strpos($query, '$php')) !== false)
	{
		$code = substr($query, $boundary + 4);

		$variables = array(
			'{$action}' => "'$action'",
			'{$sa}' => "'$sub_action'",
			'{$board}' => "'$board'",
			'{$topic}' => "'$topic'",
			'{$page}' => "'$page'",
			'{$category}' => "'$category'",
			'{$article}' => "'$article'",
			'{$portal}' => $portal,
			'{$forum}' => $forum,
		);

		return eval(str_replace(array_keys($variables), array_values($variables), un_htmlspecialchars($code)) . ';');
	}

	if (!empty($query))
	{
		$query = explode(',', $query);
	}
	else
	{
		return false;
	}

	// Take care of custom actions.
	$special = array();
	$exclude = array();
	foreach ($query as $value)
	{
		if (!isset($value[0]))
		{
			continue;
		}

		$name = '';
		$item = '';

		// Is this a weird action?
		if ($value[0] === '~')
		{
			if (strpos($value, '|') !== false)
			{
				list ($name, $item) = explode('|', substr($value, 1));
			}
			else
			{
				$name = substr($value, 1);
			}

			if (empty($item))
			{
				$special[$name] = true;
			}
			else
			{
				$special[$name][] = $item;
			}
		}
		// Might be excluding something!
		elseif ($value[0] === '-')
		{
			// We still may have weird things...
			if ($value[1] === '~')
			{
				if (strpos($value, '|') !== false)
				{
					list ($name, $item) = explode('|', substr($value, 2));
				}
				else
				{
					$name = substr($value, 2);
				}

				if (empty($item))
				{
					$exclude['special'][$name] = true;
				}
				else
				{
					$exclude['special'][$name][] = $item;
				}
			}
			else
			{
				$exclude['regular'][] = substr($value, 1);
			}
		}
	}

	// We don't want to show it on this action/page/board?
	if (!empty($exclude['regular']) && count(array_intersect(array($action, $page, $board, $category, $article), $exclude['regular'])) > 0)
	{
		return false;
	}

	// Maybe we don't want to show it in somewhere special.
	if (!empty($exclude['special']))
	{
		foreach ($exclude['special'] as $key => $value)
		{
			if (isset($_GET[$key]))
			{
				if (is_array($value) && !in_array($_GET[$key], $value))
				{
					continue;
				}
				else
				{
					return false;
				}
			}
		}
	}

	// Did we disable all blocks for this action?
	if (!empty($modSettings['sp_' . $action . 'IntegrationHide']))
	{
		return false;
	}
	// If we will display show the block.
	elseif (in_array('all', $query))
	{
		return true;
	}
	// If we are on portal, show portal blocks; if we are on forum, show forum blocks.
	elseif (($portal && in_array('portal', $query)) || ($forum && in_array('forum', $query)))
	{
		return true;
	}
	elseif (!empty($action) && $action !== 'portal' && (in_array('allaction', $query) || in_array($action, $query)))
	{
		return true;
	}
	elseif (!empty($board) && (in_array('allboard', $query) || in_array($board, $query)))
	{
		return true;
	}
	elseif (!empty($page) && (in_array('allpage', $query) || in_array($page, $query)))
	{
		return true;
	}
	elseif (!empty($category) && (in_array('allcategory', $query) || in_array($category, $query)))
	{
		return true;
	}
	elseif (!empty($article) && (in_array('allarticle', $query) || in_array($article, $query)))
	{
		return true;
	}

	// For addons using weird urls...
	foreach ($special as $key => $value)
	{
		if (isset($_GET[$key]))
		{
			if (is_array($value) && !in_array($_GET[$key], $value))
			{
				continue;
			}
			else
			{
				return true;
			}
		}
	}

	// Ummm, no block!
	return false;
}

/**
 * Determines if the block should be shown for this area, action, etc
 *
 * @param int $visibility_id
 *
 * @return bool if to show the block or not
 */
function sportal_check_visibility($visibility_id)
{
	global $context;

	static $visibilities;

	// Load the visibility profiles so we can put them to use on the blocks
	if (!isset($visibilities))
	{
		$visibilities = sportal_get_profiles(null, 3);
	}

	// See if we can show this block, here, now, for this ...
	if ($visibility_id == '0' && !isset($visibilities[$visibility_id]))
	{
		// No id, assume its off
		return false;
	}
	elseif (isset($visibilities[$visibility_id]))
	{
		// Can we show it here, should we?
		if ($context['browser_body_id'] === 'mobile' && empty($visibilities[$visibility_id]['mobile_view']))
		{
			return false;
		}
		else
		{
			return sportal_process_visibility($visibilities[$visibility_id]['final']);
		}
	}
	else
	{
		// No block for you
		return false;
	}
}

/**
 * This is a simple function that loads calendar data for the portal as infrequently as possible
 *
 * @param string $type type of data to load, events, birthdays, etc
 * @param string $low_date don't load data before this date
 * @param string|boolean $high_date don't load data after this date, false for no limit
 *
 * @return array
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
	{
		return $loaded[$type]($low_date, ($high_date === false ? $low_date : $high_date));
	}
	else
	{
		return array();
	}
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
	{
		return false;
	}

	// Can't just look for no users. :P
	if (empty($users))
	{
		return false;
	}

	// MemberColorLink compatible, cache more data, handle also some special member color link colors
	if (!empty($modSettings['MemberColorLinkInstalled']))
	{
		$colorData = load_onlineColors($users);

		// This happen only on not existing Members... but given ids...
		if (empty($colorData))
		{
			return false;
		}

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
		{
			unset($users[$k]);
		}
		else
		{
			$users[$k] = $u;
		}
	}

	$loaded_ids = array();

	// Is this a totally new variable?
	if (empty($color_profile))
	{
		$color_profile = array();
	}
	// Otherwise, we will need to do some reformating of the old data.
	else
	{
		foreach ($users as $k => $u)
		{
			if (isset($color_profile[$u]))
			{
				$loaded_ids[] = $u;
				unset($users[$k]);
			}
		}
	}

	// Make sure that we have some users.
	if (empty($users))
	{
		return empty($loaded_ids) ? false : $loaded_ids;
	}

	// Correct array pointer for the user
	reset($users);

	// Load the data.
	$request = $db->query('', '
		SELECT
			mem.id_member, mem.member_name, mem.real_name, mem.id_group,
			mg.online_color AS member_group_color,
			pg.online_color AS post_group_color
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
		$color_profile[$row['id_member']]['link'] = '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '"' . (!empty($onlineColor)
			? ' style="color: ' . $onlineColor . ';"' : '') . '>' . $row['real_name'] . '</a>';
		$color_profile[$row['id_member']]['colored_name'] = (!empty($onlineColor)
			? '<span style="color: ' . $onlineColor . ';">' : '') . $row['real_name'] . (!empty($onlineColor)
			? '</span>' : '');
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
 *
 * @return string
 */
function sp_embed_image($name, $alt = '', $width = null, $height = null, $title = true, $id = null)
{
	global $modSettings, $settings, $txt;
	static $default_alt, $randomizer;

	loadLanguage('SPortal');

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
			'add' => $txt['sp_add'],
			'items' => $txt['sp_items'],
			'given' => $txt['sp_likes_given'],
			'received' => $txt['sp_likes_received'],
		);
	}

	if (!isset($randomizer) || $randomizer > 7)
	{
		$randomizer = 0;
	}

	$randomizer++;

	// Use a default alt text if available and none was supplied
	if (empty($alt) && isset($default_alt[$name]))
	{
		$alt = $default_alt[$name];
	}

	// You want a title, use the alt text if we can
	if ($title === true)
	{
		$title = !empty($alt) ? $alt : '';
	}

	if (empty($alt))
	{
		$alt = $name;
	}

	// dots using random colors
	if (in_array($name, array('dot', 'star')) && empty($modSettings['sp_disable_random_bullets']))
	{
		$name .= $randomizer;
	}

	// Build the image tag
	$image = '<img src="' . $settings['sp_images_url'] . '/' . $name . '.png" alt="' . $alt . '"' . (!empty($title)
		? ' title="' . $title . '"' : '') . (!empty($width) ? ' width="' . $width . '"' : '') . (!empty($height)
		? ' height="' . $height . '"' : '') . (!empty($id) ? ' id="' . $id . '"' : '') . ' />';

	return $image;
}

/**
 * Builds an sprite class tag for use in templates
 *
 * @param string $name
 * @param string $title
 * @param string $extraclass
 * @param string $spriteclass
 *
 * @return string
 */
function sp_embed_class($name, $title = '', $extraclass = '', $spriteclass = 'dot')
{
	global $modSettings, $txt;
	static $default_title, $randomizer;

	loadLanguage('SPortal');

	// Some default title text settings for our standard sprites
	if (!isset($default_title))
	{
		$default_title = array(
			'dot' => '',
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
			'add' => $txt['sp_add'],
			'items' => $txt['sp_items'],
			'given' => $txt['sp_likes_given'],
			'received' => $txt['sp_likes_received'],
		);
	}

	// Use a default title text if available and none was supplied
	if (empty($title) && isset($default_title[$name]))
	{
		$title = $default_title[$name];
	}
	else
	{
		$title = $name;
	}

	// dots / start using colors
	if (in_array($name, array('dot', 'star')) && empty($modSettings['sp_disable_random_bullets']))
	{
		// Loop through the dot colors
		if (!isset($randomizer) || $randomizer > 7)
		{
			$randomizer = 0;
		}

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
 *
 * @return array()
 */
function sportal_parse_style($action, $setting = '', $process = false)
{
	static $process_cache;

	$style = array();

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
		{
			if (isset($_POST[$parameter]))
			{
				$style .= $parameter . '~' . Util::htmlspecialchars(Util::htmltrim($_POST[$parameter]), ENT_QUOTES) . '|';
			}
			else
			{
				$style .= $parameter . '~|';
			}
		}

		if (!empty($style))
		{
			$style = substr($style, 0, -1);
		}
	}
	elseif ($action === 'explode')
	{
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
				{
					$style['title']['class'] .= ' ' . $style['title_custom_class'];
				}

				$style['title']['style'] = $style['title_custom_style'];
			}

			if (empty($style['no_body']))
			{
				$style['body']['class'] = $style['body_default_class'];
			}
			else
			{
				$style['body']['class'] = '';
			}

			if (!empty($style['body_custom_class']))
			{
				$style['body']['class'] .= ' ' . $style['body_custom_class'];
			}

			$style['body']['style'] = $style['body_custom_style'];

			$process_cache[$setting] = $style;
		}
		elseif ($process)
		{
			$style = $process_cache[$setting];
		}
	}

	return $style;
}

/**
 * Loads a category by id, or category's by namespace
 *
 * @param int|string|null $category_id
 * @param boolean $active
 * @param boolean $allowed
 * @param string $sort
 *
 * @return array()
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
	{
		$query[] = sprintf($context['SPortal']['permissions']['query'], 'permissions');
	}

	// Check if the category is even active
	if (!empty($active))
	{
		$query[] = 'status = {int:status}';
		$parameters['status'] = 1;
	}

	// Lets see what we can find
	$request = $db->query('', '
		SELECT
			id_category, namespace, name, description, permissions, articles, status
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
 * Increase the view counter for articles or pages
 *
 * @param string $name
 * @param int $id
 *
 * @return bool|null
 */
function sportal_increase_viewcount($name, $id)
{
	$db = database();

	if ($name === 'page')
	{
		$query = array(
			'table' => 'sp_pages',
			'query_id' => 'id_page',
			'id' => $id
		);
	}
	elseif ($name === 'article')
	{
		$query = array(
			'table' => 'sp_articles',
			'query_id' => 'id_article',
			'id' => $id
		);
	}
	else
	{
		return false;
	}

	$db->query('', '
		UPDATE {db_prefix}{raw:table}
		SET views = views + 1
		WHERE {raw:query_id} = {int:id}',
		array(
			'table' => $query['table'],
			'query_id' => $query['query_id'],
			'id' => $id,
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
 *
 * @return array
 */
function sportal_get_pages($page_id = null, $active = false, $allowed = false, $sort = 'title')
{
	global $context, $scripturl;
	static $cache;

	$db = database();

	// If we already have the information, just return it
	$cache_name = implode(':', array($page_id, $active, $allowed));
	if (isset($cache[$cache_name]))
	{
		$return = $cache[$cache_name];
	}
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
		{
			$query[] = sprintf($context['SPortal']['permissions']['query'], 'permissions');
		}

		// Only active pages?
		if (!empty($active))
		{
			$query[] = 'status = {int:status}';
			$parameters['status'] = 1;
		}

		// Make the page request
		$request = $db->query('', '
			SELECT
				id_page, namespace, title, body, type, permissions, views, styles, status
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
				'styles' => $row['styles'],
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
 * @param string $body the string of text to treat as $type
 * @param string $type one of html, bbc, php
 * @param string $output_method if echo will echo the results, otherwise returns the string
 *
 * @return string|bool
 */
function sportal_parse_content($body, $type, $output_method = 'echo')
{
	if (($type === 'bbc' || $type === 'html') && strpos($body, '[cutoff]') !== false)
	{
		$body = str_replace('[cutoff]', '', $body);
	}

	switch ($type)
	{
		case 'bbc':
			if ($output_method === 'echo')
			{
				echo parse_bbc($body);
			}
			else
			{
				return parse_bbc($body);
			}
			break;
		case 'html':
			if ($output_method === 'echo')
			{
				echo un_htmlspecialchars($body);
			}
			else
			{
				return un_htmlspecialchars($body);
			}
			break;
		case 'php':
			$body = trim(un_htmlspecialchars($body));
			$body = trim($body, '<?php');
			$body = trim($body, '?>');

			ob_start();
			eval($body);
			$result = ob_get_contents();
			ob_end_clean();

			if ($output_method === 'echo')
			{
				echo $result;
			}
			else
			{
				return $result;
			}
			break;
	}

	return false;
}

/**
 * Return a specific menu, or all menus if none is provided
 *
 * @param int|null $menu_id
 * @param string $sort
 *
 * @return array|mixed
 */
function sportal_get_custom_menus($menu_id = null, $sort = 'id_menu')
{
	$db = database();

	$query = array();
	$parameters = array('sort' => $sort);

	if (isset($menu_id))
	{
		$query[] = 'id_menu = {int:menu_id}';
		$parameters['menu_id'] = (int) $menu_id;
	}

	$request = $db->query('', '
		SELECT
			id_menu, name
		FROM {db_prefix}sp_custom_menus' . (!empty($query) ? '
			WHERE ' . implode(' AND ', $query) : '') . '
		ORDER BY {raw:sort}',
		$parameters
	);
	$return = array();
	while ($row = $db->fetch_assoc($request))
	{
		$return[$row['id_menu']] = array(
			'id' => $row['id_menu'],
			'name' => $row['name'],
		);
	}
	$db->free_result($request);

	return !empty($menu_id) ? current($return) : $return;
}

/**
 * Return menu items for a given id, or all if none is passed
 *
 * @param int|null $item_id
 * @param string $sort
 *
 * @return array|mixed
 */
function sportal_get_menu_items($item_id = null, $sort = 'id_item')
{
	$db = database();

	$query = array();
	$parameters = array('sort' => $sort);

	if (isset($item_id))
	{
		$query[] = 'id_item = {int:item_id}';
		$parameters['item_id'] = (int) $item_id;
	}

	$request = $db->query('', '
		SELECT
			id_item, id_menu, namespace, title, href, target
		FROM {db_prefix}sp_menu_items' . (!empty($query) ? '
			WHERE ' . implode(' AND ', $query) : '') . '
		ORDER BY {raw:sort}',
		$parameters
	);
	$return = array();
	while ($row = $db->fetch_assoc($request))
	{
		$return[$row['id_item']] = array(
			'id' => $row['id_item'],
			'id_menu' => $row['id_menu'],
			'namespace' => $row['namespace'],
			'title' => $row['title'],
			'url' => $row['href'],
			'target' => $row['target'],
		);
	}
	$db->free_result($request);

	return !empty($item_id) ? current($return) : $return;
}

/**
 * Returns the details system profiles
 *
 * What is does:
 * - If no profile id is supplied, all profiles are returned
 * - If type = 1 (generally the case), the profile group permissions are returned
 *
 * @param int|null $profile_id
 * @param int|null $type
 * @param string|null $sort
 *
 * @return array
 */
function sportal_get_profiles($profile_id = null, $type = null, $sort = null)
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
		WHERE ' . implode(' AND ', $query) : '') . (!empty($sort) ? '
		ORDER BY {raw:sort}' : ''),
		$parameters
	);
	$return = array();
	while ($row = $db->fetch_assoc($request))
	{
		$return[$row['id_profile']] = array(
			'id' => $row['id_profile'],
			'name' => $row['name'],
			'label' => isset($txt['sp_admin_profiles' . substr($row['name'], 1)])
				? $txt['sp_admin_profiles' . substr($row['name'], 1)]
				: $row['name'],
			'type' => $row['type'],
			'value' => $row['value'],
		);

		// Get the permissions
		if ($row['type'] == 1)
		{
			list ($groups_allowed, $groups_denied) = explode('|', $row['value']);

			$return[$row['id_profile']] = array_merge($return[$row['id_profile']], array(
				'groups_allowed' => $groups_allowed !== '' ? explode(',', $groups_allowed) : array(),
				'groups_denied' => $groups_denied !== '' ? explode(',', $groups_denied) : array(),
			));
		}
		// Styles
		elseif ($row['type'] == 2)
		{
			$return[$row['id_profile']] = array_merge($return[$row['id_profile']], sportal_parse_style('explode', $row['value'], true));
		}
		// Visibility
		elseif ($row['type'] == 3)
		{
			list ($selections, $query, $mobile_view) = array_pad(explode('|', $row['value']), 3, '');

			$return[$row['id_profile']] = array_merge($return[$row['id_profile']], array(
				'selections' => explode(',', $selections),
				'query' => $query,
				'mobile_view' => $mobile_view,
				'final' => implode(',', array($selections, $query, $mobile_view)),
			));
		}
	}

	$db->free_result($request);

	return !empty($profile_id) ? current($return) : $return;
}

/**
 * Fetch a style based on its id
 *
 * @param int $style_id
 *
 * @return string
 */
function sportal_select_style($style_id)
{
	static $styles;

	if (!isset($styles))
	{
		$styles = sportal_get_profiles(null, 2);
	}

	if (isset($styles[$style_id]))
	{
		return $styles[$style_id];
	}
	else
	{
		return sportal_parse_style('explode', null, true);
	}
}

/**
 * Limits the number of actions a user can do in a given time
 *
 * - Currently only used by the shoutboxes and article comments
 *
 * @param string $type
 * @param boolean $fatal
 *
 * @return boolean
 */
function sp_prevent_flood($type, $fatal = true)
{
	global $modSettings, $user_info, $txt;

	$db = database();

	$limits = array(
		'spsbp' => 5,
	);

	if (!allowedTo('admin_forum'))
	{
		$time_limit = isset($limits[$type]) ? $limits[$type] : $modSettings['spamWaitTime'];
	}
	else
	{
		$time_limit = 2;
	}

	// Remove old blocks
	$db->query('', '
		DELETE FROM {db_prefix}log_floodcontrol
		WHERE log_time < {int:log_time}
			AND log_type = {string:log_type}',
		array(
			'log_time' => time() - $time_limit,
			'log_type' => $type,
		)
	);

	// Update existing ones that were still inside the time limit
	$db->insert('replace', '
		{db_prefix}log_floodcontrol',
		array('ip' => 'string-16', 'log_time' => 'int', 'log_type' => 'string'),
		array($user_info['ip'], time(), $type),
		array('ip', 'log_type')
	);

	// To many entries, need to slow them down
	if ($db->affected_rows() != 1)
	{
		if ($fatal)
		{
			fatal_lang_error('error_sp_flood_' . $type, false, array($time_limit));
		}
		else
		{
			return isset($txt['error_sp_flood_' . $type]) ? sprintf($txt['error_sp_flood_' . $type], $time_limit)
				: true;
		}
	}

	return false;
}