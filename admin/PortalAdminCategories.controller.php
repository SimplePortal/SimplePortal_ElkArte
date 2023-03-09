<?php

/**
 * @package SimplePortal ElkArte
 *
 * @author SimplePortal Team
 * @copyright 2015-2023 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.0
 */

use ElkArte\Errors\ErrorContext;

/**
 * SimplePortal Category Administration controller class.
 *
 * - This class handles the adding/editing/listing of categories
 */
class ManagePortalCategories_Controller extends Action_Controller
{
	/** @var bool If we are adding a new category*/
	protected $_is_new;

	/** @var ErrorContext */
	protected $category_errors;

	/**
	 * Main dispatcher.
	 * This function checks permissions and passes control through.
	 */
	public function action_index()
	{
		global $context, $txt;

		// You need to be an admin or have manage permissions to change category settings
		if (!allowedTo('sp_admin'))
		{
			isAllowedTo('sp_manage_categories');
		}

		// We'll need the utility functions from here.
		require_once(SUBSDIR . '/PortalAdmin.subs.php');
		require_once(SUBSDIR . '/Portal.subs.php');

		$subActions = array(
			'list' => array($this, 'action_list'),
			'add' => array($this, 'action_edit'),
			'edit' => array($this, 'action_edit'),
			'status' => array($this, 'action_status'),
			'delete' => array($this, 'action_delete'),
		);

		// Start up the controller, provide a hook since we can
		$action = new Action('portal_categories');

		// Set up the tabs
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['sp_admin_categories_title'],
			'help' => 'sp_CategoriesArea',
			'description' => $txt['sp_admin_categories_desc'],
			'tabs' => array(
				'list' => array(),
				'add' => array(),
			),
		);

		// Default to list the categories
		$subAction = $action->initialize($subActions, 'list');
		$context['sub_action'] = $subAction;

		// Call the right function for this sub-action.
		$action->dispatch($subAction);
	}

	/**
	 * Show a listing of categories in the system
	 */
	public function action_list()
	{
		global $context, $scripturl, $txt, $modSettings;

		// Build the listoption array to display the categories
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
				'function' => array($this, 'list_spCountCategories'),
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
						'db' => 'category_id',
					),
					'sort' => array(
						'default' => 'category_id',
						'reverse' => 'category_id DESC',
					),
				),
				'articles' => array(
					'header' => array(
						'value' => $txt['sp_admin_categories_col_articles'],
						'class' => 'centertext',
					),
					'data' => array(
						'db' => 'articles',
						'class' => 'centertext',
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
						'class' => 'centertext',
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
							'format' => '
								<a href="?action=admin;area=portalcategories;sa=edit;category_id=%1$s;' . $context['session_var'] . '=' . $context['session_id'] . '" accesskey="e">' . sp_embed_image('edit') . '</a>&nbsp;
								<a href="?action=admin;area=portalcategories;sa=delete;category_id=%1$s;' . $context['session_var'] . '=' . $context['session_id'] . '" onclick="return confirm(' . JavaScriptEscape($txt['sp_admin_categories_delete_confirm']) . ') && submitThisOnce(this);" accesskey="d">' . sp_embed_image('trash') . '</a>',
							'params' => array(
								'id' => true,
							),
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
						'function' => function ($row)
						{
							return '<input type="checkbox" name="remove[]" value="' . $row['id'] . '" class="input_check" />';
						},
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
					'class' => 'submitbutton',
					'position' => 'below_table_data',
					'value' => '<a class="linkbutton" href="?action=admin;area=portalcategories;sa=add;' . $context['session_var'] . '=' . $context['session_id'] . '" accesskey="a">' . $txt['sp_admin_categories_add'] . '</a>
						<input type="submit" name="remove_categories" value="' . $txt['sp_admin_categories_remove'] . '" />',
				),
			),
		);

		// Set the context values
		$context['page_title'] = $txt['sp_admin_categories_title'];
		$context['sub_template'] = 'show_list';
		$context['default_list'] = 'portal_categories';

		// Create the list.
		require_once(SUBSDIR . '/GenericList.class.php');
		createList($listOptions);
	}

	/**
	 * Callback for createList(),
	 * Returns the number of categories in the system
	 */
	public function list_spCountCategories()
	{
		return sp_count_categories();
	}

	/**
	 * Callback for createList()
	 * Returns an array of categories
	 *
	 * @param int $start
	 * @param int $items_per_page
	 * @param string $sort
	 *
	 * @return array
	 */
	public function list_spLoadCategories($start, $items_per_page, $sort)
	{
		return sp_load_categories($start, $items_per_page, $sort);
	}

	/**
	 * Edit or add a category
	 */
	public function action_edit()
	{
		global $context, $txt;

		loadTemplate('PortalAdminCategories');
		$this->category_errors = ErrorContext::context('category', 0);
		$this->_is_new = empty($_REQUEST['category_id']);

		// Saving the category form
		if (!empty($_POST['submit']))
		{
			checkSession();

			// Clean and Review the post data for compliance
			require_once(SUBSDIR . '/DataValidator.class.php');
			$validator = new Data_Validator();
			$validator->sanitation_rules(array(
				'name' => 'Util::htmltrim|Util::htmlspecialchars',
				'namespace' => 'trim|Util::htmlspecialchars',
				'current' => 'intval',
				'description' => 'trim|Util::htmlspecialchars',
				'permissions' => 'intval',
				'category_id' => 'intval'
			));
			$validator->validation_rules(array(
				'name' => 'required',
				'namespace' => 'alpha_numeric|required',
				'description' => 'required'
			));
			$validator->text_replacements(array(
				'name' => $txt['sp_admin_categories_col_name'],
				'namespace' => $txt['sp_admin_categories_col_namespace'],
				'description' => $txt['sp_admin_categories_col_description']
			));

			// If you messed this up, tell them why
			if (!$validator->validate($_POST))
			{
				foreach ($validator->validation_errors() as $id => $error)
				{
					$this->category_errors->addError($error);
				}
			}

			if (sp_check_duplicate_category($validator->current, $validator->namespace))
			{
				$this->category_errors->addError('sp_error_category_namespace_duplicate');
			}

			if ($validator->namespace !== '' && preg_replace('~[0-9]+~', '', $_POST['namespace']) === '')
			{
				$this->category_errors->addError('sp_error_category_namespace_numeric');
			}

			$category_info = array(
				'id' => $validator->category_id,
				'category_id' => $validator->namespace,
				'namespace' => $validator->namespace,
				'name' => $validator->name,
				'description' => $validator->description,
				'permissions' => $validator->permissions,
				'status' => !empty($_POST['status']) ? 1 : 0,
			);

			// None shall pass ... with errors
			if ($this->category_errors->hasErrors())
			{
				// Return what we have to the form, show them the issues
				$context['category'] = $category_info;
				$context['category_errors'] = array(
					'errors' => $this->category_errors->prepareErrors(),
					'type' => 'minor',
					'title' => $txt['sp_form_errors_detected'],
				);
				unset($_POST['submit']);
			}
			else
			{
				// Clear to save
				$category_info['id'] = sp_update_category($category_info, $this->_is_new);
				redirectexit('action=admin;area=portalcategories');
			}
		}
		// Creating a new category, lets set up some defaults for the form
		elseif ($this->_is_new)
		{
			$context['category'] = array(
				'id' => 0,
				'category_id' => 'category' . random_int(1, 5000),
				'name' => $txt['sp_categories_default_name'],
				'description' => '',
				'permissions' => 3,
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

		$context['is_new'] = $this->_is_new;
		$context['category']['permission_profiles'] = sportal_get_profiles(null, 1, 'name');
		$context['category']['groups'] = sp_load_membergroups();
		$context['page_title'] = $this->_is_new ? $txt['sp_admin_categories_add'] : $txt['sp_admin_categories_edit'];
		$context['sub_template'] = 'categories_edit';
	}

	/**
	 * Switch the active status (on/off) of a category
	 */
	public function action_status()
	{
		global $context;

		checkSession(isset($_REQUEST['xml']) ? '' : 'get');

		$category_id = !empty($_REQUEST['category_id']) ? (int) $_REQUEST['category_id'] : 0;
		$state = sp_changeState('category', $category_id);

		// Doing this the ajax way?
		if (isset($_REQUEST['xml']))
		{
			$context['item_id'] = $category_id;
			$context['status'] = !empty($state) ? 'active' : 'deactive';

			// Clear out any template layers, add the xml response
			loadTemplate('PortalAdmin');
			$template_layers = Template_Layers::instance();
			$template_layers->removeAll();
			$context['sub_template'] = 'change_status';

			obExit();
		}

		redirectexit('action=admin;area=portalcategories');
	}

	/**
	 * Delete a category or group of categories by id
	 */
	public function action_delete()
	{
		$category_ids = array();

		// Receive the cat ids to remove
		if (!empty($_POST['remove_categories']) && !empty($_POST['remove']) && is_array($_POST['remove']))
		{
			checkSession();

			foreach ($_POST['remove'] as $index => $category_id)
			{
				$category_ids[(int) $index] = (int) $category_id;
			}
		}
		elseif (!empty($_REQUEST['category_id']))
		{
			checkSession('get');
			$category_ids[] = (int) $_REQUEST['category_id'];
		}

		// If we have some to remove
		if (!empty($category_ids))
		{
			sp_delete_categories($category_ids);
		}

		redirectexit('action=admin;area=portalcategories');
	}
}
