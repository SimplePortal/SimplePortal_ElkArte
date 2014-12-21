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
 * SimplePortal Shoutbox Administation controller class.
 * This class handles the administration of the shoutbox
 */
class ManagePortalShoutbox_Controller extends Action_Controller
{
	/**
	 * Main dispatcher.
	 * This function checks permissions and passes control through.
	 */
	public function action_index()
	{
		global $context, $txt;

		// You must have admin or shoutbox privilages to be here
		if (!allowedTo('sp_admin'))
			isAllowedTo('sp_manage_shoutbox');

		// Some help will be needed
		require_once(SUBSDIR . '/PortalAdmin.subs.php');
		require_once(SUBSDIR . '/Portal.subs.php');
		loadTemplate('PortalAdminShoutbox');

		$subActions = array(
			'list' => array($this, 'action_list'),
			'add' => array($this, 'action_edit'),
			'edit' => array($this, 'action_edit'),
			'prune' => array($this, 'action_prune'),
			'delete' => array($this, 'action_delete'),
			'status' => array($this, 'action_status'),
			'blockredirect' => array($this, 'action_block_redirect'),
		);

		// Start up the controller, provide a hook since we can
		$action = new Action('portal_shoutbox');

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['sp_admin_shoutbox_title'],
			'help' => 'sp_ShoutboxArea',
			'description' => $txt['sp_admin_shoutbox_desc'],
			'tabs' => array(
				'list' => array(
				),
				'add' => array(
				),
			),
		);

		// Default the action to list if none or no valid option is given
		$subAction = $action->initialize($subActions, 'list');
		$context['sub_action'] = $subAction;

		// Shout it on out
		$action->dispatch($subAction);
	}

	/**
	 * List all the shouts in the system
	 *
	 * Shout, shout, let it all out, these are the things I can do without
	 * Come on, I'm talking to you, come on
	 */
	public function action_list()
	{
		global $context, $scripturl, $txt, $modSettings;

		// Build the listoption array to display the categories
		$listOptions = array(
			'id' => 'portal_shout',
			'title' => $txt['sp_admin_shoutbox_list'],
			'items_per_page' => $modSettings['defaultMaxMessages'],
			'no_items_label' => $txt['error_sp_no_pages'],
			'base_href' => $scripturl . '?action=admin;area=portalshoutbox;sa=list;',
			'default_sort_col' => 'name',
			'get_items' => array(
				'function' => array($this, 'list_spLoadShoutbox'),
			),
			'get_count' => array(
				'function' => array($this, 'list_spCountShoutbox'),
			),
			'columns' => array(
				'name' => array(
					'header' => array(
						'value' => $txt['sp_admin_shoutbox_col_name'],
					),
					'data' => array(
						'db' => 'name',
					),
					'sort' => array(
						'default' => 'name',
						'reverse' => 'name DESC',
					),
				),
				'shouts' => array(
					'header' => array(
						'value' => $txt['sp_admin_shoutbox_col_shouts'],
						'class' => 'centertext',
					),
					'data' => array(
						'db' => 'shouts',
						'class' => 'centertext',
					),
					'sort' => array(
						'default' => 'shouts',
						'reverse' => 'shouts DESC',
					),
				),
				'caching' => array(
					'header' => array(
						'value' => $txt['sp_admin_shoutbox_col_caching'],
						'class' => 'centertext',
					),
					'data' => array(
						'db' => 'caching',
						'class' => 'centertext',
					),
					'sort' => array(
						'default' => 'caching',
						'reverse' => 'caching DESC',
					),
				),
				'status' => array(
					'header' => array(
						'value' => $txt['sp_admin_shoutbox_col_status'],
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
						'value' => $txt['sp_admin_shoutbox_col_actions'],
						'class' => 'centertext',
					),
					'data' => array(
						'sprintf' => array(
							'format' => '<a href="?action=admin;area=portalshoutbox;sa=edit;shoutbox_id=%1$s;' . $context['session_var'] . '=' . $context['session_id'] . '" accesskey="m">' . sp_embed_image('modify') . '</a>&nbsp;
								<a href="?action=admin;area=portalshoutbox;sa=prune;shoutbox_id=%1$s;' . $context['session_var'] . '=' . $context['session_id'] . '" accesskey="p">' . sp_embed_image('bin') . '</a>&nbsp;
								<a href="?action=admin;area=portalshoutbox;sa=delete;shoutbox_id=%1$s;' . $context['session_var'] . '=' . $context['session_id'] . '" onclick="return confirm(' . JavaScriptEscape($txt['sp_admin_shoutbox_delete_confirm']) . ') && submitThisOnce(this);" accesskey="d">' . sp_embed_image('delete') . '</a>',
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
						'function' => create_function('$row', '
							return \'<input type="checkbox" name="remove[]" value="\' . $row[\'id\'] . \'" class="input_check" />\';
						'),
						'class' => 'centertext',
					),
				),
			),
			'form' => array(
				'href' => $scripturl . '?action=admin;area=portalshoutbox;sa=remove',
				'include_sort' => true,
				'include_start' => true,
				'hidden_fields' => array(
					$context['session_var'] => $context['session_id'],
				),
			),
			'additional_rows' => array(
				array(
					'position' => 'below_table_data',
					'value' => '<input type="submit" name="remove_shoutbox" value="' . $txt['sp_admin_shoutbox_remove'] . '" class="right_submit" />',
				),
			),
		);

		// Set the context values
		$context['page_title'] = $txt['sp_admin_shoutbox_title'];
		$context['sub_template'] = 'show_list';
		$context['default_list'] = 'portal_shout';

		// Create the list.
		require_once(SUBSDIR . '/GenericList.class.php');
		createList($listOptions);
	}

	/**
	 * Callback for createList(),
	 * Returns the number of articles in the system
	 */
	public function list_spCountShoutbox()
	{
	   return sp_count_shoutbox();
	}

	/**
	 * Callback for createList()
	 * Returns an array of articles
	 *
	 * @param int $start
	 * @param int $items_per_page
	 * @param string $sort
	 */
	public function list_spLoadShoutbox($start, $items_per_page, $sort)
	{
		return sp_load_shoutbox($start, $items_per_page, $sort);
	}

	/**
	 * Edit an existing shoutbox or add a new one
	 */
	public function action_edit()
	{
		global $txt, $context, $modSettings, $editortxt;

		$db = database();

		$context['SPortal']['is_new'] = empty($_REQUEST['shoutbox_id']);

		if (!empty($_POST['submit']))
		{
			checkSession();

			if (!isset($_POST['name']) || Util::htmltrim(Util::htmlspecialchars($_POST['name'], ENT_QUOTES)) === '')
				fatal_lang_error('sp_error_shoutbox_name_empty', false);

			$result = $db->query('', '
				SELECT id_shoutbox
				FROM {db_prefix}sp_shoutboxes
				WHERE name = {string:name}
					AND id_shoutbox != {int:current}
				LIMIT 1',
				array(
					'limit' => 1,
					'name' => Util::htmlspecialchars($_POST['name'], ENT_QUOTES),
					'current' => (int) $_POST['shoutbox_id'],
				)
			);
			list ($has_duplicate) = $db->fetch_row($result);
			$db->free_result($result);

			if (!empty($has_duplicate))
				fatal_lang_error('sp_error_shoutbox_name_duplicate', false);

			if (isset($_POST['moderator_groups']) && is_array($_POST['moderator_groups']) && count($_POST['moderator_groups']) > 0)
			{
				foreach ($_POST['moderator_groups'] as $id => $group)
					$_POST['moderator_groups'][$id] = (int) $group;

				$_POST['moderator_groups'] = implode(',', $_POST['moderator_groups']);
			}
			else
				$_POST['moderator_groups'] = '';

			if (!empty($_POST['allowed_bbc']) && is_array($_POST['allowed_bbc']))
			{
				foreach ($_POST['allowed_bbc'] as $id => $tag)
					$_POST['allowed_bbc'][$id] = Util::htmlspecialchars($tag, ENT_QUOTES);

				$_POST['allowed_bbc'] = implode(',', $_POST['allowed_bbc']);
			}
			else
				$_POST['allowed_bbc'] = '';

			$fields = array(
				'name' => 'string',
				'permissions' => 'int',
				'moderator_groups' => 'string',
				'warning' => 'string',
				'allowed_bbc' => 'string',
				'height' => 'int',
				'num_show' => 'int',
				'num_max' => 'int',
				'reverse' => 'int',
				'caching' => 'int',
				'refresh' => 'int',
				'status' => 'int',
			);

			$shoutbox_info = array(
				'id' => (int) $_POST['shoutbox_id'],
				'name' => Util::htmlspecialchars($_POST['name'], ENT_QUOTES),
				'permissions' => (int) $_POST['permissions'],				'moderator_groups' => $_POST['moderator_groups'],
				'warning' => Util::htmlspecialchars($_POST['warning'], ENT_QUOTES),
				'allowed_bbc' => $_POST['allowed_bbc'],
				'height' => (int) $_POST['height'],
				'num_show' => (int) $_POST['num_show'],
				'num_max' => (int) $_POST['num_max'],
				'reverse' => !empty($_POST['reverse']) ? 1 : 0,
				'caching' => !empty($_POST['caching']) ? 1 : 0,
				'refresh' => (int) $_POST['refresh'],
				'status' => !empty($_POST['status']) ? 1 : 0,
			);

			if ($context['SPortal']['is_new'])
			{
				unset($shoutbox_info['id']);

				$db->insert('', '
					{db_prefix}sp_shoutboxes',
					$fields,
					$shoutbox_info,
					array('id_shoutbox')
				);
				$shoutbox_info['id'] = $db->insert_id('{db_prefix}sp_shoutboxes', 'id_shoutbox');
			}
			else
			{
				$update_fields = array();
				foreach ($fields as $name => $type)
					$update_fields[] = $name . ' = {' . $type . ':' . $name . '}';

				$db->query('', '
					UPDATE {db_prefix}sp_shoutboxes
					SET ' . implode(', ', $update_fields) . '
					WHERE id_shoutbox = {int:id}', $shoutbox_info
				);
			}

			sportal_update_shoutbox($shoutbox_info['id']);

			if ($context['SPortal']['is_new'] && (allowedTo(array('sp_admin', 'sp_manage_blocks'))))
				redirectexit('action=admin;area=portalshoutbox;sa=blockredirect;shoutbox=' . $shoutbox_info['id']);
			else
				redirectexit('action=admin;area=portalshoutbox');
		}

		if ($context['SPortal']['is_new'])
		{
			$context['SPortal']['shoutbox'] = array(
				'id' => 0,
				'name' => $txt['sp_shoutbox_default_name'],
				'permissions' => 3,
				'moderator_groups' => array(),
				'warning' => '',
				'allowed_bbc' => array('b', 'i', 'u', 's', 'url', 'code', 'quote', 'me'),
				'height' => 200,
				'num_show' => 20,
				'num_max' => 1000,
				'reverse' => 0,
				'caching' => 1,
				'refresh' => 0,
				'status' => 1,
			);
		}
		else
		{
			$_REQUEST['shoutbox_id'] = (int) $_REQUEST['shoutbox_id'];
			$context['SPortal']['shoutbox'] = sportal_get_shoutbox($_REQUEST['shoutbox_id']);
		}

		loadLanguage('Editor');

		$context['SPortal']['shoutbox']['permission_profiles'] = sportal_get_profiles(null, 1, 'name');
		sp_loadMemberGroups($context['SPortal']['shoutbox']['moderator_groups'], 'moderator', 'moderator_groups');

		if (empty($context['SPortal']['shoutbox']['permission_profiles']))
			fatal_lang_error('error_sp_no_permission_profiles', false);

		$context['allowed_bbc'] = array(
			'b' => $editortxt['Bold'],
			'i' => $editortxt['Italic'],
			'u' => $editortxt['Underline'],
			's' => $editortxt['Strikethrough'],
			'pre' => $editortxt['Preformatted Text'],
			'img' => $editortxt['Insert an image'],
			'url' => $editortxt['Insert a link'],
			'email' => $editortxt['Insert an email'],
			'sup' => $editortxt['Superscript'],
			'sub' => $editortxt['Subscript'],
			'tt' => $editortxt['Teletype'],
			'code' => $editortxt['Code'],
			'quote' => $editortxt['Insert a Quote'],
			'size' => $editortxt['Font Size'],
			'font' => $editortxt['Font Name'],
			'color' => $editortxt['Font Color'],
			'me' => 'me',
		);

		$disabled_tags = array();
		if (!empty($modSettings['disabledBBC']))
			$disabled_tags = explode(',', $modSettings['disabledBBC']);
		if (empty($modSettings['enableEmbeddedFlash']))
			$disabled_tags[] = 'flash';

		foreach ($disabled_tags as $tag)
		{
			if ($tag == 'list')
				$context['disabled_tags']['orderlist'] = true;

			$context['disabled_tags'][trim($tag)] = true;
		}

		$context['page_title'] = $context['SPortal']['is_new'] ? $txt['sp_admin_shoutbox_add'] : $txt['sp_admin_shoutbox_edit'];
		$context['sub_template'] = 'shoutbox_edit';
	}

	/**
	 * Who does not like prunes .. or maybe cut down the shout list
	 */
	public function action_prune()
	{
		global $context, $txt;

		$db = database();

		$shoutbox_id = empty($_REQUEST['shoutbox_id']) ? 0 : (int) $_REQUEST['shoutbox_id'];
		$context['shoutbox'] = sportal_get_shoutbox($shoutbox_id);

		if (empty($context['shoutbox']))
			fatal_lang_error('error_sp_shoutbox_not_exist', false);

		if (!empty($_POST['submit']))
		{
			checkSession();

			if (!empty($_POST['type']))
			{
				$where = array('id_shoutbox = {int:shoutbox_id}');
				$parameters = array('shoutbox_id' => $shoutbox_id);

				if ($_POST['type'] == 'days' && !empty($_POST['days']))
				{
					$where[] = 'log_time < {int:time_limit}';
					$parameters['time_limit'] = time() - $_POST['days'] * 86400;
				}
				elseif ($_POST['type'] == 'member' && !empty($_POST['member']))
				{
					$request = $db->query('', '
						SELECT id_member
						FROM {db_prefix}members
						WHERE member_name = {string:member}
							OR real_name = {string:member}
						LIMIT {int:limit}',
						array(
							'member' => strtr(trim(Util::htmlspecialchars($_POST['member'], ENT_QUOTES)), array('\'' => '&#039;')),
							'limit' => 1,
						)
					);
					list ($member_id) = $db->fetch_row($request);
					$db->free_result($request);

					if (!empty($member_id))
					{
						$where[] = 'id_member = {int:member_id}';
						$parameters['member_id'] = $member_id;
					}
				}

				if ($_POST['type'] == 'all' || count($where) > 1)
				{
					$db->query('', '
						DELETE FROM {db_prefix}sp_shouts
						WHERE ' . implode(' AND ', $where),
						$parameters
					);

					if ($_POST['type'] != 'all')
					{
						$request = $db->query('', '
							SELECT COUNT(*)
							FROM {db_prefix}sp_shouts
							WHERE id_shoutbox = {int:shoutbox_id}
							LIMIT {int:limit}',
							array(
								'shoutbox_id' => $shoutbox_id,
								'limit' => 1,
							)
						);
						list ($total_shouts) = $db->fetch_row($request);
						$db->free_result($request);
					}
					else
						$total_shouts = 0;

					$db->query('', '
						UPDATE {db_prefix}sp_shoutboxes
						SET num_shouts = {int:total_shouts}
						WHERE id_shoutbox = {int:shoutbox_id}',
						array(
							'shoutbox_id' => $shoutbox_id,
							'total_shouts' => $total_shouts,
						)
					);

					sportal_update_shoutbox($shoutbox_id);
				}
			}

			redirectexit('action=admin;area=portalshoutbox');
		}

		$context['page_title'] = $txt['sp_admin_shoutbox_prune'];
		$context['sub_template'] = 'shoutbox_prune';
	}

	/**
	 * Remove a shout from the system
	 */
	public function action_delete()
	{
		$shoutbox_ids = array();

		// Get the page id's to remove
		if (!empty($_POST['remove_shoutbox']) && !empty($_POST['remove']) && is_array($_POST['remove']))
		{
			checkSession();

			foreach ($_POST['remove'] as $index => $page_id)
				$shoutbox_ids[(int) $index] = (int) $page_id;
		}
		elseif (!empty($_REQUEST['shoutbox_id']))
		{
			checkSession('get');
			$shoutbox_ids[] = (int) $_REQUEST['shoutbox_id'];
		}

		// If we have some to remove ....
		if (!empty($shoutbox_ids))
			sp_delete_shoutbox($shoutbox_ids);

		redirectexit('action=admin;area=portalshoutbox');
	}

	/**
	 * Updates the system status of the shoutout, toggle on/off
	 */
	public function action_status()
	{
		$db = database();

		checkSession('get');

		$shoutbox_id = !empty($_REQUEST['shoutbox_id']) ? (int) $_REQUEST['shoutbox_id'] : 0;

		$db->query('', '
			UPDATE {db_prefix}sp_shoutboxes
			SET status = CASE WHEN status = {int:is_active} THEN 0 ELSE 1 END
			WHERE id_shoutbox = {int:id}',
			array(
				'is_active' => 1,
				'id' => $shoutbox_id,
			)
		);

		redirectexit('action=admin;area=portalshoutbox');
	}

	/**
	 * Used to gain access to the add a shoutbox block
	 */
	public function action_block_redirect()
	{
		global $context, $scripturl, $txt;

		if (!allowedTo('sp_admin'))
			isAllowedTo('sp_manage_blocks');

		$context['page_title'] = $txt['sp_admin_shoutbox_add'];
		$context['redirect_message'] = sprintf($txt['sp_admin_shoutbox_block_redirect_message'], $scripturl . '?action=admin;area=portalblocks;sa=add;selected_type=sp_shoutbox;parameters[]=shoutbox;shoutbox=' . $_GET['shoutbox'], $scripturl . '?action=admin;area=portalshoutbox');
		$context['sub_template'] = 'shoutbox_block_redirect';
	}
}