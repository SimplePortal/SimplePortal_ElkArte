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
 * SimplePortal Menus Administration controller class.
 * This class handles the adding/editing of menus
 */
class ManagePortalMenus_Controller extends Action_Controller
{
	/**
	 * The starting point for the controller, called before all others
	 */
	public function action_index()
	{
		global $context, $txt;

		// Admin or at least manage menu permissions
		if (!allowedTo('sp_admin'))
		{
			isAllowedTo('sp_manage_menus');
		}

		// Going to need these
		require_once(SUBSDIR . '/PortalAdmin.subs.php');
		loadTemplate('PortalAdminMenus');

		$subActions = array(
			'listmainitem' => array($this, 'action_sportal_admin_menus_main_item_list'),
			'addmainitem' => array($this, 'action_sportal_admin_menus_main_item_edit'),
			'editmainitem' => array($this, 'action_sportal_admin_menus_main_item_edit'),
			'deletemainitem' => array($this, 'action_sportal_admin_menus_main_item_delete'),

			'listcustommenu' => array($this, 'action_sportal_admin_menus_custom_menu_list'),
			'addcustommenu' => array($this, 'action_sportal_admin_menus_custom_menu_edit'),
			'editcustommenu' => array($this, 'action_sportal_admin_menus_custom_menu_edit'),
			'deletecustommenu' => array($this, 'action_sportal_admin_menus_custom_menu_delete'),

			'listcustomitem' => array($this, 'action_sportal_admin_menus_custom_item_list'),
			'addcustomitem' => array($this, 'action_sportal_admin_menus_custom_item_edit'),
			'editcustomitem' => array($this, 'action_sportal_admin_menus_custom_item_edit'),
			'deletecustomitem' => array($this, 'action_sportal_admin_menus_custom_item_delete'),
		);

		// Start up the controller, provide a hook since we can
		$action = new Action('portal_menus');

		// Set up the tabs
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['sp_admin_menus_title'],
			'help' => 'sp_MenusArea',
			'description' => $txt['sp_admin_menus_desc'],
			'tabs' => array(
			//	'listmainitem' => array(),
			//	'addmainitem' => array(),
				'listcustommenu' => array(),
				'addcustommenu' => array(),
			),
		);

		// Default to list the categories
		$subAction = $action->initialize($subActions, 'listcustommenu');
		$context['sub_action'] = $subAction;

		// Extra tab in the right cases
		if ($context['sub_action'] === 'listcustomitem' && !empty($_REQUEST['menu_id']))
		{
			$context[$context['admin_menu_name']]['tab_data']['tabs']['addcustomitem'] = array(
				'add_params' => ';menu_id=' . $_REQUEST['menu_id'],
			);
		}

		// Call the right function for this sub-action.
		$action->dispatch($subAction);
	}

	/**
	 * List the custom menus in the system
	 */
	public function action_sportal_admin_menus_custom_menu_list()
	{
		global $context, $scripturl, $txt, $modSettings;

		// Want to remove some menus
		if (!empty($_POST['remove_menus']) && !empty($_POST['remove']) && is_array($_POST['remove']))
		{
			checkSession();

			$remove_ids = array();
			foreach ($_POST['remove'] as $index => $menu_id)
			{
				$remove_ids[(int) $index] = (int) $menu_id;
			}

			sp_remove_menu($remove_ids);
		}

		// Build the list option array to display the menus
		$listOptions = array(
			'id' => 'portal_menus',
			'title' => $txt['sp_admin_menus_custom_menu_list'],
			'items_per_page' => $modSettings['defaultMaxMessages'],
			'no_items_label' => $txt['sp_error_no_custom_menus'],
			'base_href' => $scripturl . '?action=admin;area=portalmenus;sa=listcustommenu;',
			'default_sort_col' => 'name',
			'get_items' => array(
				'function' => array($this, 'list_spLoadMenus'),
			),
			'get_count' => array(
				'function' => array($this, 'list_spCountMenus'),
			),
			'columns' => array(
				'name' => array(
					'header' => array(
						'value' => $txt['sp_admin_menus_col_name'],
					),
					'data' => array(
						'db' => 'name',
					),
					'sort' => array(
						'default' => 'cm.name ASC',
						'reverse' => 'cm.name DESC',
					),
				),
				'items' => array(
					'header' => array(
						'value' => $txt['sp_admin_menus_col_items'],
					),
					'data' => array(
						'db' => 'items',
					),
					'sort' => array(
						'default' => 'items',
						'reverse' => 'items DESC',
					),
				),
				'action' => array(
					'header' => array(
						'value' => $txt['sp_admin_menus_col_actions'],
						'class' => ' grid8 centertext',
					),
					'data' => array(
						'sprintf' => array(
							'format' => '
								<a href="' . $scripturl . '?action=admin;area=portalmenus;sa=addcustomitem;menu_id=%1$s;' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image('add') . '</a>
								<a href="' . $scripturl . '?action=admin;area=portalmenus;sa=listcustomitem;menu_id=%1$s;' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image('items') . '</a>
						 		<a href="' . $scripturl . '?action=admin;area=portalmenus;sa=editcustommenu;menu_id=%1$s;' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image('modify') . '</a>
								<a href="' . $scripturl . '?action=admin;area=portalmenus;sa=deletecustommenu;menu_id=%1$s;' . $context['session_var'] . '=' . $context['session_id'] . '" onclick="return confirm(\'' . $txt['sp_admin_menus_menu_delete_confirm'] . '\');">' . sp_embed_image('delete') . '</a>',
							'params' => array(
								'id' => true,
							)
						),
						'class' => 'centertext nowrap',
					),
				),
			),
			'form' => array(
				'href' => $scripturl . '?action=admin;area=portalmenus;sa=listcustommenu',
				'include_sort' => true,
				'include_start' => true,
				'hidden_fields' => array(
					$context['session_var'] => $context['session_id'],
				),
			),
		);

		// Set the context values
		$context['page_title'] = $txt['sp_admin_menus_custom_menu_list'];
		$context['sub_template'] = 'show_list';
		$context['default_list'] = 'portal_menus';

		// Create the list.
		require_once(SUBSDIR . '/GenericList.class.php');
		createList($listOptions);
	}

	/**
	 * Returns the number of menus in the system
	 * Callback for createList()
	 */
	public function list_spCountMenus()
	{
		return sp_menu_count();
	}

	/**
	 * Returns an array of menus, passthru really
	 * Callback for createList()
	 *
	 * @param int $start
	 * @param int $items_per_page
	 * @param string $sort
	 *
	 * @return array
	 */
	public function list_spLoadMenus($start, $items_per_page, $sort)
	{
		return sp_custom_menu_items($start, $items_per_page, $sort);
	}

	/**
	 * Create or edit a menu
	 */
	public function action_sportal_admin_menus_custom_menu_edit()
	{
		global $context, $txt;

		// New menu or existing menu
		$is_new = empty($_REQUEST['menu_id']);

		// Saving the edit/add
		if (!empty($_POST['submit']))
		{
			checkSession();

			if (!isset($_POST['name']) || Util::htmltrim(Util::htmlspecialchars($_POST['name'], ENT_QUOTES)) === '')
			{
				fatal_lang_error('sp_error_menu_name_empty', false);
			}

			$menu_info = array(
				'id' => (int) $_POST['menu_id'],
				'name' => Util::htmlspecialchars($_POST['name'], ENT_QUOTES),
			);

			$menu_info['id'] = sp_add_menu($menu_info, $is_new);

			redirectexit('action=admin;area=portalmenus;sa=listcustommenu');
		}

		// Not saving so set up for the template display
		if ($is_new)
		{
			$context['menu'] = array(
				'id' => 0,
				'name' => $txt['sp_menus_default_custom_menu_name'],
			);
		}
		else
		{
			$menu_id = (int) $_REQUEST['menu_id'];
			$context['menu'] = sportal_get_custom_menus($menu_id);
		}

		// Final template bits
		$context['page_title'] = $is_new ? $txt['sp_admin_menus_custom_menu_add'] : $txt['sp_admin_menus_custom_menu_edit'];
		$context['sub_template'] = 'menus_custom_menu_edit';
	}

	/**
	 * Delete a custom menu and its items
	 */
	public function action_sportal_admin_menus_custom_menu_delete()
	{
		checkSession('get');

		$menu_id = !empty($_REQUEST['menu_id']) ? (int) $_REQUEST['menu_id'] : 0;

		sp_remove_menu_items($menu_id);
		sp_remove_menu($menu_id);

		redirectexit('action=admin;area=portalmenus;sa=listcustommenu');
	}

	/**
	 * List the items contained in a custom menu
	 */
	public function action_sportal_admin_menus_custom_item_list()
	{
		global $context, $scripturl, $txt, $modSettings;

		// Want to remove some items from a menu?
		if (!empty($_POST['remove_items']) && !empty($_POST['remove']) && is_array($_POST['remove']))
		{
			checkSession();

			$remove = array();
			foreach ($_POST['remove'] as $index => $item_id)
			{
				$remove[(int) $index] = (int) $item_id;
			}

			sp_remove_menu_items($remove);
		}

		$menu_id = !empty($_REQUEST['menu_id']) ? (int) $_REQUEST['menu_id'] : 0;
		$context['menu'] = sportal_get_custom_menus($menu_id);

		if (empty($context['menu']))
		{
			fatal_lang_error('error_sp_menu_not_found', false);
		}

		// Build the list option array to display the custom items in this custom menu
		$listOptions = array(
			'id' => 'portal_items',
			'title' => $txt['sp_admin_menus_custom_item_list'],
			'items_per_page' => $modSettings['defaultMaxMessages'],
			'no_items_label' => $txt['sp_error_no_custom_menus'],
			'base_href' => $scripturl . '?action=admin;area=portalmenus;sa=listcustomitem;',
			'default_sort_col' => 'title',
			'get_items' => array(
				'function' => array($this, 'list_sp_menu_item'),
				'params' => array(
					$menu_id,
				),
			),
			'get_count' => array(
				'function' => array($this, 'list_sp_menu_item_count'),
				'params' => array(
					$menu_id,
				),
			),
			'columns' => array(
				'title' => array(
					'header' => array(
						'value' => $txt['sp_admin_menus_col_title'],
					),
					'data' => array(
						'db' => 'title',
					),
					'sort' => array(
						'default' => 'title ASC',
						'reverse' => 'title DESC',
					),
				),
				'namespace' => array(
					'header' => array(
						'value' => $txt['sp_admin_menus_col_namespace'],
					),
					'data' => array(
						'db' => 'namespace',
					),
					'sort' => array(
						'default' => 'namespace ASC',
						'reverse' => 'namespace DESC',
					),
				),
				'target' => array(
					'header' => array(
						'value' => $txt['sp_admin_menus_col_target'],
					),
					'data' => array(
						'db' => 'target',
					),
					'sort' => array(
						'default' => 'target',
						'reverse' => 'target DESC',
					),
				),
				'action' => array(
					'header' => array(
						'value' => $txt['sp_admin_menus_col_actions'],
						'class' => ' grid8 centertext',
					),
					'data' => array(
						'sprintf' => array(
							'format' => '
								<a href="' . $scripturl . '?action=admin;area=portalmenus;sa=editcustomitem;menu_id=%1$s;item_id=%2$s;' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image('modify') . '</a>
								<a href="' . $scripturl . '?action=admin;area=portalmenus;sa=deletecustomitem;menu_id=%1$s;item_id=%2$s;' . $context['session_var'] . '=' . $context['session_id'] . '" onclick="return confirm(\'' . $txt['sp_admin_menus_item_delete_confirm'] . '\');">' . sp_embed_image('delete') . '</a>',
							'params' => array(
								'menu' => true,
								'id' => true,
							)
						),
						'class' => 'centertext nowrap',
					),
				),
				'check' => array(
					'header' => array(
						'value' => '<input type="checkbox" onclick="invertAll(this, this.form);" class="input_check" />',
						'class' => 'centertext',
					),
					'data' => array(
						'function' => function($row) {return '<input type="checkbox" name="remove[]" value="' . $row['id'] . '" class="input_check" />';},
						'class' => 'centertext',
					),
				),
			),
			'form' => array(
				'href' => $scripturl . '?action=admin;area=portalmenus;sa=listcustomitem;menu_id=' . $menu_id,
				'include_sort' => true,
				'include_start' => true,
				'hidden_fields' => array(
					$context['session_var'] => $context['session_id'],
				),
			),
			'additional_rows' => array(
				array(
					'position' => 'below_table_data',
					'value' => '
						<input type="submit" name="remove_items" value="' . $txt['sp_admin_items_remove'] . '" class="right_submit" />',
				),
			),
		);

		// Set the context values
		$context['page_title'] = $txt['sp_admin_menus_custom_item_list'];
		$context['sub_template'] = 'show_list';
		$context['default_list'] = 'portal_items';

		// Create the list.
		require_once(SUBSDIR . '/GenericList.class.php');
		createList($listOptions);
	}

	/**
	 * Returns the number of menus in the system
	 * Callback for createList()
	 *
	 * @param int $menu_id
	 *
	 * @return int
	 */
	public function list_sp_menu_item_count($menu_id)
	{
		return sp_menu_item_count($menu_id);
	}

	/**
	 * Returns an array of menus, passthru really
	 * Callback for createList()
	 *
	 * @param int $start
	 * @param int $items_per_page
	 * @param string $sort
	 * @param int $menu_id
	 *
	 * @return array
	 */
	public function list_sp_menu_item($start, $items_per_page, $sort, $menu_id)
	{
		return sp_menu_items($start, $items_per_page, $sort, $menu_id);
	}

	/**
	 * Add or edit menu items
	 */
	public function action_sportal_admin_menus_custom_item_edit()
	{
		global $context, $txt;

		$menu_id = !empty($_REQUEST['menu_id']) ? (int) $_REQUEST['menu_id'] : 0;
		$context['menu'] = sportal_get_custom_menus($menu_id);

		// No menu, no further
		if (empty($context['menu']))
		{
			fatal_lang_error('error_sp_menu_not_found', false);
		}

		// Need to know if we are adding or editing
		$is_new = empty($_REQUEST['item_id']);

		// Saving the form.
		if (!empty($_POST['submit']))
		{
			checkSession();

			// Use our standard validation functions in a few spots
			require_once(SUBSDIR . '/DataValidator.class.php');
			$validator = new Data_Validator();

			// Clean and Review the post data for compliance
			$validator->sanitation_rules(array(
				'title' => 'Util::htmltrim|Util::htmlspecialchars',
				'namespace' => 'Util::htmltrim|Util::htmlspecialchars',
				'item_id' => 'intval',
				'url' => 'Util::htmlspecialchars',
				'target' => 'intval',
			));
			$validator->validation_rules(array(
				'title' => 'required',
				'namespace' => 'alpha_numeric|required',
				'item_id' => 'required',
			));
			$validator->text_replacements(array(
				'title' => $txt['sp_error_item_title_empty'],
				'namespace' => $txt['sp_error_item_namespace_empty'],
			));

			// If you messed this up, back you go
			if (!$validator->validate($_POST))
			{
				// @todo, should set  Error_Context::context and display in tempalte instead
				foreach ($validator->validation_errors() as $id => $error)
					fatal_lang_error($error, false);
			}

			// Can't have the same name in the same menu twice
			$has_duplicate = sp_menu_check_duplicate_items($validator->item_id, $validator->namespace);
			if (!empty($has_duplicate))
			{
				fatal_lang_error('sp_error_item_namespace_duplicate', false);
			}

			// Can't have a simple numeric namespace
			if (preg_replace('~[0-9]+~', '', $validator->namespace) === '')
			{
				fatal_lang_error('sp_error_item_namespace_numeric', false);
			}

			$item_info = array(
				'id' => $validator->item_id,
				'id_menu' => $context['menu']['id'],
				'namespace' => $validator->namespace,
				'title' => $validator->title,
				'href' => $validator->url,
				'target' => $validator->target,
			);

			// Adjust the url for the link type
			$link_type = !empty($_POST['link_type']) ? $_POST['link_type'] : '';
			$link_item = !empty($_POST['link_item']) ? $_POST['link_item'] : '';
			$link_item_id = 0;
			if ($link_type !== 'custom')
			{
				if (preg_match('~^\d+|[A-Za-z0-9_\-]+$~', $link_item, $match))
				{
					$link_item_id = $match[0];
				}
				else
				{
					fatal_lang_error('sp_error_item_link_item_invalid', false);
				}

				switch ($link_type)
				{
					case 'action':
					case 'page':
					case 'category':
					case 'article':
						$item_info['href'] = '$scripturl?' . $link_type . '=' . $link_item_id;
						break;
					case 'board':
						$item_info['href'] = '$scripturl?' . $link_type . '=' . $link_item_id . '.0';
						break;
				}
			}

			// Add or update the item
			sp_add_menu_item($item_info, $is_new);

			redirectexit('action=admin;area=portalmenus;sa=listcustomitem;menu_id=' . $context['menu']['id']);
		}

		// Prepare the items for the template
		if ($is_new)
		{
			$context['item'] = array(
				'id' => 0,
				'namespace' => 'item' . mt_rand(1, 5000),
				'title' => $txt['sp_menus_default_menu_item_name'],
				'url' => '',
				'target' => 0,
			);
		}
		// Not new so fetch what we know about the item
		else
		{
			$_REQUEST['item_id'] = (int) $_REQUEST['item_id'];
			$context['item'] = sportal_get_menu_items($_REQUEST['item_id']);
		}

		// Menu items
		$context['items']['action'] = array(
			'portal' => $txt['sp-portal'],
			'forum' => $txt['sp-forum'],
			'recent' => $txt['recent_posts'],
			'unread' => $txt['unread_topics_visit'],
			'unreadreplies' => $txt['unread_replies'],
			'profile' => $txt['profile'],
			'pm' => $txt['pm_short'],
			'calendar' => $txt['calendar'],
			'admin' => $txt['admin'],
			'login' => $txt['login'],
			'register' => $txt['register'],
			'post' => $txt['post'],
			'stats' => $txt['forum_stats'],
			'search' => $txt['search'],
			'mlist' => $txt['members_list'],
			'moderate' => $txt['moderate'],
			'help' => $txt['help'],
			'who' => $txt['who_title'],
		);

		$context['items'] = array_merge($context['items'], sp_block_template_helpers());
		$context['page_title'] = $is_new ? $txt['sp_admin_menus_custom_item_add'] : $txt['sp_admin_menus_custom_item_edit'];
		$context['sub_template'] = 'menus_custom_item_edit';
	}

	/**
	 * Remove an item from a menu
	 */
	public function action_sportal_admin_menus_custom_item_delete()
	{
		checkSession('get');

		$menu_id = !empty($_REQUEST['menu_id']) ? (int) $_REQUEST['menu_id'] : 0;
		$item_id = !empty($_REQUEST['item_id']) ? (int) $_REQUEST['item_id'] : 0;

		sp_remove_menu_items($item_id);

		redirectexit('action=admin;area=portalmenus;sa=listcustomitem;menu_id=' . $menu_id);
	}
}