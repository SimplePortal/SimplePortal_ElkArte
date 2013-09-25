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
 * SimplePortal Page Administation controller class.
 * This class handles the adding/editing/listing of pages
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

		// You are allowed here yes?
		if (!allowedTo('sp_admin'))
			isAllowedTo('sp_manage_profiles');

		// Helpers
		require_once(SUBSDIR . '/PortalAdmin.subs.php');
		loadTemplate('PortalAdminProfiles');

		// What can we do
		$subActions = array(
			'listpermission' => 'sportal_admin_permission_profiles_list',
			'addpermission' => 'sportal_admin_permission_profiles_edit',
			'editpermission' => 'sportal_admin_permission_profiles_edit',
			'deletepermission' => 'sportal_admin_permission_profiles_delete',
		);

		$subAction = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'listpermission';
		$context['sub_action'] = $subAction;

		// Lead some breadcrumbs so we knwo our way back
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['sp_admin_profiles_title'],
			'help' => 'sp_ProfilesArea',
			'description' => $txt['sp_admin_profiles_desc'],
			'tabs' => array(
				'listpermission' => array(
				),
				'addpermission' => array(
				),
			),
		);

		// Call the right function for this sub-action.
		$action = new Action();
		$action->initialize($subActions, 'listpermission');
		$action->dispatch($subAction);
	}

	/**
	 * Show page listing of all premission groups in the system
	 */
	function sportal_admin_permission_profiles_list()
	{
		global $context, $scripturl, $txt;

		// Removing some permission profiles?
		if (!empty($_POST['remove_profiles']) && !empty($_POST['remove']) && is_array($_POST['remove']))
		{
			checkSession();

			$remove = array();
			foreach ($_POST['remove'] as $index => $profile_id)
				$remove[(int) $index] = (int) $profile_id;

			sp_delete_profiles($remove);
		}

		// Build the listoption array to display the permission profles
		$listOptions = array(
			'id' => 'portal_permisssions',
			'title' => $txt['sp_admin_permission_profiles_list'],
			'items_per_page' => $modSettings['defaultMaxMessages'],
			'no_items_label' => $txt['error_sp_no_articles'],
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
						'db' => 'name',
					),
					'sort' => array(
						'default' => 'name',
						'reverse' => 'name DESC',
					),
				),
				'articles' => array(
					'header' => array(
						'value' => $txt['sp_admin_profiles_col_articles'],
					),
					'data' => array(
						'db' => 'article_id',
					),
				),
				'blocks' => array(
					'header' => array(
						'value' => $txt['sp_admin_profiles_col_blocks'],
					),
					'data' => array(
						'db' => 'article_id',
					),
				),
				'categories' => array(
					'header' => array(
						'value' => $txt['sp_admin_profiles_col_categories'],
					),
					'data' => array(
						'db' => 'article_id',
					),
				),
				'pages' => array(
					'header' => array(
						'value' => $txt['sp_admin_profiles_col_pages'],
					),
					'data' => array(
						'db' => 'article_id',
					),
				),
				'shoutboxes' => array(
					'header' => array(
						'value' => $txt['sp_admin_profiles_col_shoutboxes'],
					),
					'data' => array(
						'db' => 'article_id',
					),
				),
				'action' => array(
					'header' => array(
						'value' => $txt['sp_admin_articles_col_actions'],
						'class' => 'centertext',
					),
					'data' => array(
						'sprintf' => array(
							'format' => '<a href="?action=admin;area=portalarticles;sa=edit;article_id=%1$s;' . $context['session_var'] . '=' . $context['session_id'] . '" accesskey="e">' . sp_embed_image('modify') . '</a>&nbsp;
								<a href="?action=admin;area=portalarticles;sa=delete;article_id=%1$s;' . $context['session_var'] . '=' . $context['session_id'] . '" onclick="return confirm(' . JavaScriptEscape($txt['sp_admin_articles_delete_confirm']) . ') && submitThisOnce(this);" accesskey="d">' . sp_embed_image('delete') . '</a>',
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
					'value' => '<input type="submit" name="remove_articles" value="' . $txt['sp_admin_articles_remove'] . '" class="right_submit" />',
				),
			),
		);


		// Set the context values
		$context['page_title'] = $txt['sp_admin_permission_profiles_list'];
		$context['sub_template'] = 'show_list';
		$context['default_list'] = 'portal_permisssions';

		// Create the list.
		require_once(SUBSDIR . '/List.subs.php');
		createList($listOptions);
	}

	/**
	 * Callback for createList(),
	 * Returns the number of permission profiles
	 *
	 * @param int $messageID
	 */
	public function list_spCountProfiles()
	{
	   return sp_count_profiles();
	}

	/**
	 * Callback for createList()
	 * Returns an array of profile permissions
	 *
	 * @param int $start
	 * @param int $items_per_page
	 * @param string $sort
	 */
	public function list_spLoadProfiles($start, $items_per_page, $sort)
	{
		return sp_load_profiles($start, $items_per_page, $sort);
	}

	function sportal_admin_permission_profiles_edit()
	{
		global $context, $txt;

		$db = database();

		$context['is_new'] = empty($_REQUEST['profile_id']);

		if (!empty($_POST['submit']))
		{
			checkSession();

			if (!isset($_POST['name']) || Util::htmltrim(Util::htmlspecialchars($_POST['name'], ENT_QUOTES)) === '')
				fatal_lang_error('sp_error_profile_name_empty', false);

			$groups_allowed = $groups_denied = '';

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

			$fields = array(
				'type' => 'int',
				'name' => 'string',
				'value' => 'string',
			);

			$profile_info = array(
				'id' => (int) $_POST['profile_id'],
				'type' => 1,
				'name' => Util::htmlspecialchars($_POST['name'], ENT_QUOTES),
				'value' => implode('|', array($groups_allowed, $groups_denied)),
			);

			if ($context['is_new'])
			{
				unset($profile_info['id']);

				$db->insert('',
					'{db_prefix}sp_profiles',
					$fields,
					$profile_info,
					array('id_profile')
				);
				$profile_info['id'] = $db->insert_id('{db_prefix}sp_profiles', 'id_profile');
			}
			else
			{
				$update_fields = array();
				foreach ($fields as $name => $type)
					$update_fields[] = $name . ' = {' . $type . ':' . $name . '}';

				$db->query('','
					UPDATE {db_prefix}sp_profiles
					SET ' . implode(', ', $update_fields) . '
					WHERE id_profile = {int:id}',
					$profile_info
				);
			}

			redirectexit('action=admin;area=portalprofiles');
		}

		if ($context['is_new'])
		{
			$context['profile'] = array(
				'id' => 0,
				'name' => $txt['sp_profiles_default_name'],
				'groups_allowed' => array(),
				'groups_denied' => array(),
			);
		}
		else
		{
			$_REQUEST['profile_id'] = (int) $_REQUEST['profile_id'];
			$context['profile'] = sportal_get_profiles($_REQUEST['profile_id']);
		}

		$context['profile']['groups'] = sp_load_membergroups();

		$context['page_title'] = $context['is_new'] ? $txt['sp_admin_profiles_add'] : $txt['sp_admin_profiles_edit'];
		$context['sub_template'] = 'permission_profiles_edit';
	}

	function sportal_admin_permission_delete()
	{
		$db = database();

		checkSession('get');

		$profile_id = !empty($_REQUEST['profile_id']) ? (int) $_REQUEST['profile_id'] : 0;

		$db->query('','
			DELETE FROM {db_prefix}sp_profiles
			WHERE id_profile = {int:id}',
			array(
				'id' => $profile_id,
			)
		);

		redirectexit('action=admin;area=portalprofiles');
	}
}