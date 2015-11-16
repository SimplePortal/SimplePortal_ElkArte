<?php

/**
 * @package SimplePortal ElkArte
 *
 * @author SimplePortal Team
 * @copyright 2015 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.1.0 Beta 1
 */

if (!defined('ELK'))
	die('No access...');

/**
 * SimplePortal Profiles Administration controller class.
 * This class handles the adding/editing/listing of profiles for permissions and styles
 */
class ManagePortalProfile_Controller extends Action_Controller
{
	/**
	 * Main dispatcher.
	 * This function checks permissions and passes control through.
	 */
	public function action_index()
	{
		global $context, $txt;

		// Helpers
		require_once(SUBSDIR . '/PortalAdmin.subs.php');
		loadTemplate('PortalAdminProfiles');

		// What can we do
		$subActions = array(
			'listpermission' =>  array($this, 'action_list', 'permission' => 'sp_manage_profiles'),
			'addpermission' =>  array($this, 'action_edit', 'permission' => 'sp_manage_profiles'),
			'editpermission' =>  array($this, 'action_edit', 'permission' => 'sp_manage_profiles'),
			'deletepermission' =>  array($this, 'action_delete', 'permission' => 'sp_manage_profiles'),
			'liststyle' => array($this, 'sportal_admin_style_profiles_list', 'permission' => 'sp_manage_profiles'),
			'addstyle' => array($this, 'sportal_admin_style_profiles_edit', 'permission' => 'sp_manage_profiles'),
			'editstyle' => array($this, 'sportal_admin_style_profiles_edit', 'permission' => 'sp_manage_profiles'),
			'deletestyle' => array($this, 'sportal_admin_style_profiles_delete', 'permission' => 'sp_manage_profiles'),
			);

		// Start up the controller, provide a hook since we can
		$action = new Action('portal_profile');

		// Leave some breadcrumbs so we know our way back
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['sp_admin_profiles_title'],
			'help' => 'sp_ProfilesArea',
			'description' => $txt['sp_admin_profiles_desc'],
			'tabs' => array(
				'listpermission' => array(),
				'addpermission' => array(),
				'liststyle' => array(),
				'addstyle' => array(),
			),
		);

		// Default to the listpermission action
		$subAction = $action->initialize($subActions, 'listpermission');
		$context['sub_action'] = $subAction;

		// Call the right function for this sub-action, if you have permission
		$action->dispatch($subAction);
	}

	/**
	 * Show page listing of all permission groups in the system
	 */
	public function action_list()
	{
		global $context, $scripturl, $txt, $modSettings;

		// Removing some permission profiles via checkbox?
		if (!empty($_POST['remove_profiles']) && !empty($_POST['remove']) && is_array($_POST['remove']))
		{
			checkSession();

			$remove = array();
			foreach ($_POST['remove'] as $index => $profile_id)
				$remove[(int) $index] = (int) $profile_id;

			sp_delete_profiles($remove);
		}

		// Build the listoption array to display the permission profiles
		$listOptions = array(
			'id' => 'portal_permisssions',
			'title' => $txt['sp_admin_permission_profiles_list'],
			'items_per_page' => $modSettings['defaultMaxMessages'],
			'no_items_label' => $txt['error_sp_no_profiles'],
			'base_href' => $scripturl . '?action=admin;area=portalprofiles;sa=listpermission;',
			'default_sort_col' => 'name',
			'get_items' => array(
				'function' => array($this, 'list_spLoadProfiles'),
			),
			'get_count' => array(
				'function' => array($this, 'list_spCountProfiles'),
			),
			'columns' => array(
				'name' => array(
					'header' => array(
						'value' => $txt['sp_admin_profiles_col_name'],
					),
					'data' => array(
						'db' => 'label',
					),
					'sort' => array(
						'default' => 'name',
						'reverse' => 'name DESC',
					),
				),
				'articles' => array(
					'header' => array(
						'value' => $txt['sp_admin_profiles_col_articles'],
						'class' => 'centertext',
					),
					'data' => array(
						'function' => create_function('$row', '
							return empty($row[\'articles\']) ? \'0\' : $row[\'articles\'];
						'),
						'class' => 'centertext',
					),
				),
				'blocks' => array(
					'header' => array(
						'value' => $txt['sp_admin_profiles_col_blocks'],
						'class' => 'centertext',
					),
					'data' => array(
						'function' => create_function('$row', '
							return empty($row[\'blocks\']) ? \'0\' : $row[\'blocks\'];
						'),
						'class' => 'centertext',
					),
				),
				'categories' => array(
					'header' => array(
						'value' => $txt['sp_admin_profiles_col_categories'],
						'class' => 'centertext',
					),
					'data' => array(
						'function' => create_function('$row', '
							return empty($row[\'categories\']) ? \'0\' : $row[\'categories\'];
						'),
						'class' => 'centertext',
					),
				),
				'pages' => array(
					'header' => array(
						'value' => $txt['sp_admin_profiles_col_pages'],
						'class' => 'centertext',
					),
					'data' => array(
						'function' => create_function('$row', '
							return empty($row[\'pages\']) ? \'0\' : $row[\'pages\'];
						'),
						'class' => 'centertext',
					),
				),
				'shoutboxes' => array(
					'header' => array(
						'value' => $txt['sp_admin_profiles_col_shoutboxes'],
						'class' => 'centertext',
					),
					'data' => array(
						'function' => create_function('$row', '
							return empty($row[\'shoutboxes\']) ? \'0\' : $row[\'shoutboxes\'];
						'),
						'class' => 'centertext',
					),
				),
				'action' => array(
					'header' => array(
						'value' => $txt['sp_admin_articles_col_actions'],
						'class' => 'centertext',
					),
					'data' => array(
						'sprintf' => array(
							'format' => '<a href="?action=admin;area=portalprofiles;sa=editpermission;profile_id=%1$s;' . $context['session_var'] . '=' . $context['session_id'] . '" accesskey="e">' . sp_embed_image('modify') . '</a>&nbsp;
								<a href="?action=admin;area=portalprofiles;sa=deletepermission;profile_id=%1$s;' . $context['session_var'] . '=' . $context['session_id'] . '" onclick="return confirm(' . JavaScriptEscape($txt['sp_admin_articles_delete_confirm']) . ') && submitThisOnce(this);" accesskey="d">' . sp_embed_image('delete') . '</a>',
							'params' => array(
								'id' => true,
							),
						),
						'class' => 'centertext',
						'style' => "width: 40px",
					),
				),
				'check' => array(
					'header' => array(
						'value' => '<input type="checkbox" onclick="invertAll(this, this.form);" class="input_check" />',
						'class' => 'centertext',
					),
					'data' => array(
						'function' => create_function('$row', '
							return \'<input type="checkbox" name="remove[]" value="\' . $row[\'id\'] . \'" class="input_check" />\';
						'),
						'class' => 'centertext',
					),
				),
			),
			'form' => array(
				'href' => $scripturl . '?action=admin;area=portalprofiles;sa=listpermission',
				'include_sort' => true,
				'include_start' => true,
				'hidden_fields' => array(
					$context['session_var'] => $context['session_id'],
				),
			),
			'additional_rows' => array(
				array(
					'position' => 'below_table_data',
					'value' => '<input type="submit" name="remove_profiles" value="' . $txt['sp_admin_profiles_remove'] . '" class="right_submit" />',
				),
			),
		);

		// Set the context values
		$context['page_title'] = $txt['sp_admin_permission_profiles_list'];
		$context['sub_template'] = 'show_list';
		$context['default_list'] = 'portal_permisssions';

		// Create the list.
		require_once(SUBSDIR . '/GenericList.class.php');
		createList($listOptions);
	}

	/**
	 * Callback for createList(),
	 * Returns the number of profiles of type
	 *
	 * @param int $type
	 */
	public function list_spCountProfiles($type = 1)
	{
		return sp_count_profiles($type);
	}

	/**
	 * Callback for createList()
	 * Returns an array of profiles of type
	 *
	 * @param int $start
	 * @param int $items_per_page
	 * @param string $sort
	 * @param int $type
	 */
	public function list_spLoadProfiles($start, $items_per_page, $sort, $type = 1)
	{
		return sp_load_profiles($start, $items_per_page, $sort, $type);
	}

	/**
	 * Add or edit a portal wide permissions profile
	 */
	public function action_edit()
	{
		global $context, $txt;

		// New or an edit?
		$context['is_new'] = empty($_REQUEST['profile_id']);

		// Saving the form
		if (!empty($_POST['submit']))
		{
			// Security first
			checkSession();

			// Always clean the name
			if (!isset($_POST['name']) || Util::htmltrim(Util::htmlspecialchars($_POST['name'], ENT_QUOTES)) === '')
				fatal_lang_error('sp_error_profile_name_empty', false);

			$groups_allowed = $groups_denied = '';

			// If specific member groups were picked, build the allow/deny arrays
			if (!empty($_POST['membergroups']) && is_array($_POST['membergroups']))
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

			// Add the data to place in the fields
			$profile_info = array(
				'id' => (int) $_POST['profile_id'],
				'type' => 1,
				'name' => Util::htmlspecialchars($_POST['name'], ENT_QUOTES),
				'value' => implode('|', array($groups_allowed, $groups_denied)),
			);

			// New we simply insert, or and edit will update
			$profile_info['id'] = sp_add_permission_profile($profile_info, $context['is_new']);

			redirectexit('action=admin;area=portalprofiles;sa=listpermission');
		}

		// Not saving, then its time to show the permission form
		if ($context['is_new'])
		{
			$context['profile'] = array(
				'id' => 0,
				'name' => $txt['sp_profiles_default_name'],
				'label' => $txt['sp_profiles_default_name'],
				'groups_allowed' => array(),
				'groups_denied' => array(),
			);
		}
		else
		{
			$_REQUEST['profile_id'] = (int) $_REQUEST['profile_id'];
			$context['profile'] = sportal_get_profiles($_REQUEST['profile_id']);
		}

		// Sub template time
		$context['profile']['groups'] = sp_load_membergroups();
		$context['page_title'] = $context['is_new'] ? $txt['sp_admin_profiles_add'] : $txt['sp_admin_profiles_edit'];
		$context['sub_template'] = 'permission_profiles_edit';
	}

	/**
	 * Remove a single permission profile from the system
	 */
	public function action_delete()
	{
		checkSession('get');

		$profile_id = !empty($_REQUEST['profile_id']) ? (int) $_REQUEST['profile_id'] : 0;

		sp_delete_permission_profile($profile_id);

		redirectexit('action=admin;area=portalprofiles;sa=listpermission');
	}

	/**
	 * Show page listing of all style groups in the system
	 */
	function sportal_admin_style_profiles_list()
	{
		global $context, $scripturl, $txt, $modSettings;

		// Removing some styles via the checkbox?
		if (!empty($_POST['remove_profiles']) && !empty($_POST['remove']) && is_array($_POST['remove']))
		{
			checkSession();

			$remove = array();
			foreach ($_POST['remove'] as $index => $profile_id)
				$remove[(int) $index] = (int) $profile_id;

			sp_delete_profiles($remove);
		}

		// Build the listoption array to display the style profiles
		$listOptions = array(
			'id' => 'portal_styles',
			'title' => $txt['sp_admin_style_profiles_list'],
			'items_per_page' => $modSettings['defaultMaxMessages'],
			'no_items_label' => $txt['error_sp_no_style_profiles'],
			'base_href' => $scripturl . '?action=admin;area=portalprofiles;sa=liststyle;',
			'default_sort_col' => 'name',
			'get_items' => array(
				'function' => array($this, 'list_spLoadProfiles'),
				'params' => array(
					2,
				),
			),
			'get_count' => array(
				'function' => array($this, 'list_spCountProfiles'),
				'params' => array(
					2,
				),
			),
			'columns' => array(
				'name' => array(
					'header' => array(
						'value' => $txt['sp_admin_profiles_col_name'],
					),
					'data' => array(
						'db' => 'label',
					),
					'sort' => array(
						'default' => 'name',
						'reverse' => 'name DESC',
					),
				),
				'articles' => array(
					'header' => array(
						'value' => $txt['sp_admin_profiles_col_articles'],
						'class' => 'centertext',
					),
					'data' => array(
						'function' => create_function('$row', '
							return empty($row[\'articles\']) ? \'0\' : $row[\'articles\'];
						'),
						'class' => 'centertext',
					),
				),
				'blocks' => array(
					'header' => array(
						'value' => $txt['sp_admin_profiles_col_blocks'],
						'class' => 'centertext',
					),
					'data' => array(
						'function' => create_function('$row', '
							return empty($row[\'blocks\']) ? \'0\' : $row[\'blocks\'];
						'),
						'class' => 'centertext',
					),
				),
				'pages' => array(
					'header' => array(
						'value' => $txt['sp_admin_profiles_col_pages'],
						'class' => 'centertext',
					),
					'data' => array(
						'function' => create_function('$row', '
							return empty($row[\'pages\']) ? \'0\' : $row[\'pages\'];
						'),
						'class' => 'centertext',
					),
				),
				'action' => array(
					'header' => array(
						'value' => $txt['sp_admin_articles_col_actions'],
						'class' => 'centertext',
					),
					'data' => array(
						'sprintf' => array(
							'format' => '<a href="?action=admin;area=portalprofiles;sa=editstyle;profile_id=%1$s;' . $context['session_var'] . '=' . $context['session_id'] . '" accesskey="e">' . sp_embed_image('modify') . '</a>&nbsp;
								<a href="?action=admin;area=portalprofiles;sa=deletestyle;profile_id=%1$s;' . $context['session_var'] . '=' . $context['session_id'] . '" onclick="return confirm(' . JavaScriptEscape($txt['sp_admin_articles_delete_confirm']) . ') && submitThisOnce(this);" accesskey="d">' . sp_embed_image('delete') . '</a>',
							'params' => array(
								'id' => true,
							),
						),
						'class' => 'centertext',
						'style' => "width: 40px",
					),
				),
				'check' => array(
					'header' => array(
						'value' => '<input type="checkbox" onclick="invertAll(this, this.form);" class="input_check" />',
						'class' => 'centertext',
					),
					'data' => array(
						'function' => create_function('$row', '
							return \'<input type="checkbox" name="remove[]" value="\' . $row[\'id\'] . \'" class="input_check" />\';
						'),
						'class' => 'centertext',
					),
				),
			),
			'form' => array(
				'href' => $scripturl . '?action=admin;area=portalprofiles;sa=liststyle',
				'include_sort' => true,
				'include_start' => true,
				'hidden_fields' => array(
					$context['session_var'] => $context['session_id'],
				),
			),
			'additional_rows' => array(
				array(
					'position' => 'below_table_data',
					'value' => '<input type="submit" name="remove_profiles" value="' . $txt['sp_admin_profiles_remove'] . '" class="right_submit" />',
				),
			),
		);

		// Set the context values
		$context['page_title'] = $txt['sp_admin_style_profiles_list'];
		$context['sub_template'] = 'show_list';
		$context['default_list'] = 'portal_styles';

		// Create the list.
		require_once(SUBSDIR . '/GenericList.class.php');
		createList($listOptions);
	}

	/**
	 * Add or edit a portal wide style profile
	 */
	function sportal_admin_style_profiles_edit()
	{
		global $context, $txt;

		// New or an edit to an existing style
		$context['is_new'] = empty($_GET['profile_id']);

		// Saving the style form
		if (!empty($_POST['submit']))
		{
			// Security first
			checkSession();

			// Always clean the profile name
			if (!isset($_POST['name']) || Util::htmltrim(Util::htmlspecialchars($_POST['name'], ENT_QUOTES)) === '')
				fatal_lang_error('sp_error_profile_name_empty', false);

			// Add the data to place in the fields
			$profile_info = array(
				'id' => (int) $_POST['profile_id'],
				'type' => 2,
				'name' => Util::htmlspecialchars($_POST['name'], ENT_QUOTES),
				'value' => sportal_parse_style('implode'),
			);

			// New we simply insert, or if editing update
			$profile_info['id'] = sp_add_permission_profile($profile_info, empty($_POST['profile_id']));

			// Tada
			redirectexit('action=admin;area=portalprofiles;sa=liststyle');
		}

		// Not saving, then its time to show the style form
		if ($context['is_new'])
		{
			$context['profile'] = array(
				'id' => 0,
				'name' => $txt['sp_profiles_default_name'],
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
		// Now a new style so fetch an existing one to display
		else
		{
			$profile_id = (int) $_GET['profile_id'];
			$context['profile'] = sportal_get_profiles($profile_id);
		}

		// We may not have much style, but we have class
		$context['profile']['classes'] = array(
			'title' => array('category_header', 'secondary_header', 'custom'),
			'body' => array('portalbg', 'portalbg2', 'information', 'roundframe', 'custom'),
		);

		$context['page_title'] = $context['is_new'] ? $txt['sp_admin_profiles_add'] : $txt['sp_admin_profiles_edit'];
		$context['sub_template'] = 'style_profiles_edit';
	}

	/**
	 * Remove a style profile from the system
	 */
	function sportal_admin_style_profiles_delete()
	{
		checkSession('get');

		$profile_id = !empty($_REQUEST['profile_id']) ? (int) $_REQUEST['profile_id'] : 0;

		sp_delete_permission_profile($profile_id);

		redirectexit('action=admin;area=portalprofiles;sa=liststyle');
	}
}