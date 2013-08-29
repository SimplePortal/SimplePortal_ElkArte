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
 * SimplePortal Category Administation controller class.
 * This class handles the adding/editing/listing of categories
 */
class ManagePortalCategories_Controller extends Action_Controller
{
	/**
	 * Main dispatcher.
	 * This function checks permissions and passes control through.
	 */
	public function action_index()
	{
		global $context, $txt;

		// You need to be an admin or have manage permissions to change category settings
		if (!allowedTo('sp_admin'))
			isAllowedTo('sp_manage_categories');

		// We'll need the utility functions from here.
		require_once(SUBSDIR . '/PortalAdmin.subs.php');
		require_once(SUBSDIR . '/Portal.subs.php');
		loadTemplate('PortalAdminCategories');

		$subActions = array(
			'list' => array($this, 'action_list_categories'),
			'add' => array($this, 'action_sportal_admin_category_edit'),
			'edit' => array($this, 'action_sportal_admin_category_edit'),
			'status' => array($this, 'action_sportal_admin_category_status'),
			'delete' => array($this, 'action_sportal_admin_category_delete'),
		);

		// Default to list the categories
		$subAction = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'list';
		$context['sub_action'] = $subAction;

		// Set up the tabs
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['sp_admin_categories_title'],
			'help' => 'sp_CategoriesArea',
			'description' => $txt['sp_admin_categories_desc'],
			'tabs' => array(
				'list' => array(
				),
				'add' => array(
				),
			),
		);

		// Call the right function for this sub-action.
		$action = new Action();
		$action->initialize($subActions, 'list');
		$action->dispatch($subAction);
	}

	/**
	 * Show a listing of categories in the system
	 */
	public function action_list_categories()
	{
		global $context, $scripturl, $txt, $modSettings;

		// build the listoption array to display the categories
		$listOptions = array(
			'id' => 'portal_categories',
			'title' => $txt['sp_admin_categories_list'],
			'items_per_page' => $modSettings['defaultMaxMessages'],
			'no_items_label' => $txt['error_sp_no_categories'],
			'base_href' => $scripturl . '?action=admin;area=portalcategories;sa=list;',
			'default_sort_col' => 'name',
			'get_items' => array(
				'function' => array($this, 'list_spLoadCategories'),
			),
			'get_count' => array(
				'function' => array($this, 'list_spCategoryCount'),
			),
			'columns' => array(
				'name' => array(
					'header' => array(
						'value' => $txt['sp_admin_categories_col_name'],
					),
					'data' => array(
						'db' => 'name',
					),
					'sort' => array(
						'default' => 'name',
						'reverse' => 'name DESC',
					),
				),
				'namespace' => array(
					'header' => array(
						'value' => $txt['sp_admin_categories_col_namespace'],
					),
					'data' => array(
						'db' => 'namespace',
					),
					'sort' => array(
						'default' => 'namespace',
						'reverse' => 'namespace DESC',
					),
				),
				'articles' => array(
					'header' => array(
						'value' => $txt['sp_admin_categories_col_articles'],
					),
					'data' => array(
						'db' => 'articles',
					),
					'sort' => array(
						'default' => 'articles',
						'reverse' => 'articles DESC',
					),
				),
				'status' => array(
					'header' => array(
						'value' => $txt['sp_admin_categories_col_status'],
						'class' => 'centertext',
					),
					'data' => array(
						'db' => 'status_image',
						'class' => "centertext",
					),
					'sort' => array(
						'default' => 'status',
						'reverse' => 'status DESC',
					),
				),
				'action' => array(
					'header' => array(
						'value' => $txt['sp_admin_categories_col_actions'],
						'class' => 'centertext',
					),
					'data' => array(
						'sprintf' => array(
							'format' => '<a href="?action=admin;area=portalcategories;sa=edit;category_id=%1$s;' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image('modify') . '</a>&nbsp;
								<a href="?action=admin;area=portalcategories;sa=delete;category_id=%1$s;' . $context['session_var'] . '=' . $context['session_id'] . '" onclick="return confirm(' . JavaScriptEscape($txt['sp_admin_categories_delete_confirm']) . ') && submitThisOnce(this);" accesskey="d">' . sp_embed_image('delete') . '</a>',
							'params' => array(
								'id' => true,
							),
						),
						'class' => "centertext",
						'style' => "width: 40px",
					),
				),
				'check' => array(
					'header' => array(
						'value' => '<input type="checkbox" onclick="invertAll(this, this.form);" class="input_check" />',
						'class' => 'centertext',
					),
					'data' => array(
						'function' => create_function('$rowData', '
							return \'<input type="checkbox" name="remove[]" value="\' . $row[\'id\'] . \'" class="input_check" />\';
						'),
						'class' => 'centertext',
					),
				),
			),
			'form' => array(
				'href' => $scripturl . '?action=admin;area=portalcategories;sa=remove',
				'include_sort' => true,
				'include_start' => true,
				'hidden_fields' => array(
					$context['session_var'] => $context['session_id'],
				),
			),
			'additional_rows' => array(
				array(
					'position' => 'below_table_data',
					'value' => '<input type="submit" name="addfilter" value="' . $txt['sp_admin_categories_remove'] . '" class="right_submit" />',
				),
			),
		);

		// Set the context values
		$context['page_title'] = $txt['sp_admin_categories_title'];
		$context['sub_template'] = 'show_list';
		$context['default_list'] = 'portal_categories';

		// Create the list.
		require_once(SUBSDIR . '/List.subs.php');
		createList($listOptions);
	}

	/**
	 * Callback for createList(),
	 * Returns the number of categories in the system
	 *
	 * @param int $messageID
	 */
	public function list_spCategoryCount()
	{
	   return sp_category_count();
	}

	/**
	 * Callback for createList()
	 * Returns an array of categories
	 *
	 * @param int $start
	 * @param int $items_per_page
	 * @param string $sort
	 */
	public function list_spLoadCategories($start, $items_per_page, $sort)
	{
		return sp_load_category($start, $items_per_page, $sort);
	}

	/**
	 * Edit or add a category
	 */
	public function action_sportal_admin_category_edit()
	{
		global $context, $txt;

		$db = database();

		$context['is_new'] = empty($_REQUEST['category_id']);

		if (!empty($_POST['submit']))
		{
			checkSession();

			if (!isset($_POST['name']) || Util::htmltrim(Util::htmlspecialchars($_POST['name'], ENT_QUOTES)) === '')
				fatal_lang_error('sp_error_category_name_empty', false);

			if (!isset($_POST['namespace']) || Util::htmltrim(Util::htmlspecialchars($_POST['namespace'], ENT_QUOTES)) === '')
				fatal_lang_error('sp_error_category_namespace_empty', false);

			$result = $db->query('', '
				SELECT id_category
				FROM {db_prefix}sp_categories
				WHERE namespace = {string:namespace}
					AND id_category != {int:current}
				LIMIT 1',
				array(
					'limit' => 1,
					'namespace' => Util::htmlspecialchars($_POST['namespace'], ENT_QUOTES),
					'current' => (int) $_POST['category_id'],
				)
			);
			list ($has_duplicate) = $db->fetch_row($result);
			$db->free_result($result);

			if (!empty($has_duplicate))
				fatal_lang_error('sp_error_category_namespace_duplicate', false);

			if (preg_match('~[^A-Za-z0-9_]+~', $_POST['namespace']) != 0)
				fatal_lang_error('sp_error_category_namespace_invalid_chars', false);

			if (preg_replace('~[0-9]+~', '', $_POST['namespace']) === '')
				fatal_lang_error('sp_error_category_namespace_numeric', false);

			$permission_set = 0;
			$groups_allowed = $groups_denied = '';

			if (!empty($_POST['permission_set']))
				$permission_set = (int) $_POST['permission_set'];
			elseif (!empty($_POST['membergroups']) && is_array($_POST['membergroups']))
			{
				$groups_allowed = $groups_denied = array();

				foreach ($_POST['membergroups'] as $id => $value)
				{
					if ($value == 1)
						$groups_allowed[] = (int) $id;
					elseif ($value == -1)
						$groups_denied[] = (int) $id;
				}

				$groups_allowed = implode(',', $groups_allowed);
				$groups_denied = implode(',', $groups_denied);
			}

			$fields = array(
				'namespace' => 'string',
				'name' => 'string',
				'description' => 'string',
				'permission_set' => 'int',
				'groups_allowed' => 'string',
				'groups_denied' => 'string',
				'status' => 'int',
			);

			$category_info = array(
				'id' => (int) $_POST['category_id'],
				'namespace' => Util::htmlspecialchars($_POST['namespace'], ENT_QUOTES),
				'name' => Util::htmlspecialchars($_POST['name'], ENT_QUOTES),
				'description' => Util::htmlspecialchars($_POST['description'], ENT_QUOTES),
				'permission_set' => $permission_set,
				'groups_allowed' => $groups_allowed,
				'groups_denied' => $groups_denied,
				'status' => !empty($_POST['status']) ? 1 : 0,
			);

			if ($context['is_new'])
			{
				unset($category_info['id']);

				$db->insert('', '
					{db_prefix}sp_categories',
					$fields,
					$category_info,
					array('id_category')
				);
				$category_info['id'] = $db->insert_id('{db_prefix}sp_categories', 'id_category');
			}
			else
			{
				$update_fields = array();
				foreach ($fields as $name => $type)
					$update_fields[] = $name . ' = {' . $type . ':' . $name . '}';

				$db->query('', '
					UPDATE {db_prefix}sp_categories
					SET ' . implode(', ', $update_fields) . '
					WHERE id_category = {int:id}', $category_info
				);
			}

			redirectexit('action=admin;area=portalcategories');
		}

		if ($context['is_new'])
		{
			$context['category'] = array(
				'id' => 0,
				'category_id' => 'category' . mt_rand(1, 5000),
				'name' => $txt['sp_categories_default_name'],
				'description' => '',
				'permission_set' => 3,
				'groups_allowed' => array(),
				'groups_denied' => array(),
				'status' => 1,
			);
		}
		else
		{
			$_REQUEST['category_id'] = (int) $_REQUEST['category_id'];
			$context['category'] = sportal_get_categories($_REQUEST['category_id']);
		}

		$context['category']['groups'] = sp_load_membergroups();
		$context['page_title'] = $context['is_new'] ? $txt['sp_admin_categories_add'] : $txt['sp_admin_categories_edit'];
		$context['sub_template'] = 'categories_edit';
	}

	/**
	 * Switch the active status of a category
	 */
	public function action_sportal_admin_category_status()
	{
		checkSession('get');

		$category_id = !empty($_REQUEST['category_id']) ? (int) $_REQUEST['category_id'] : 0;
		sp_category_update($category_id);

		redirectexit('action=admin;area=portalcategories');
	}

	/**
	 * Delete a category or group of categories by id
	 */
	public function action_sportal_admin_category_delete()
	{
		$category_ids = array();

		// Retreive the cat ids to remove
		if (!empty($_POST['remove_categories']) && !empty($_POST['remove']) && is_array($_POST['remove']))
		{
			checkSession();

			foreach ($_POST['remove'] as $index => $category_id)
				$category_ids[] = (int) $category_id;
		}
		elseif (!empty($_REQUEST['category_id']))
		{
			checkSession('get');
			$category_ids[] = (int) $_REQUEST['category_id'];
		}

		// If we have some to remove
		if (!empty($category_ids))
			sp_delete_categories($category_ids);

		redirectexit('action=admin;area=portalcategories');
	}
}