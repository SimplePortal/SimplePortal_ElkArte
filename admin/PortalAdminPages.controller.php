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
class ManagePortalPages_Controller extends Action_Controller
{
	/**
	 * Main dispatcher.
	 * This function checks permissions and passes control through.
	 */
	public function action_index()
	{
		global $context, $txt;

		// Admin or pages permissions required
		if (!allowedTo('sp_admin'))
			isAllowedTo('sp_manage_pages');

		// Can't do much without our little buddys
		require_once(SUBSDIR . '/PortalAdmin.subs.php');
		require_once(SUBSDIR . '/Portal.subs.php');
		loadTemplate('PortalAdminPages');

		$subActions = array(
			'list' => array($this, 'action_sportal_admin_page_list'),
			'add' => array($this, 'action_sportal_admin_page_edit'),
			'edit' => array($this, 'action_sportal_admin_page_edit'),
			'status' => array($this, 'action_sportal_admin_page_status'),
			'delete' => array($this, 'action_sportal_admin_page_delete'),
		);

		// Default to the list action
		$subAction = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'list';
		$context['sub_action'] = $subAction;

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['sp_admin_pages_title'],
			'help' => 'sp_PagesArea',
			'description' => $txt['sp_admin_pages_desc'],
			'tabs' => array(
				'list' => array(
				),
				'add' => array(
				),
			),
		);

		// Go!
		$action = new Action();
		$action->initialize($subActions, 'list');
		$action->dispatch($subAction);
	}

	/**
	 * Show page listing of all portal pages in the system
	 */
	public function action_sportal_admin_page_list()
	{
		global $context, $scripturl, $txt, $modSettings;

		// build the listoption array to display the categories
		$listOptions = array(
			'id' => 'portal_pages',
			'title' => $txt['sp_admin_articles_list'],
			'items_per_page' => $modSettings['defaultMaxMessages'],
			'no_items_label' => $txt['error_sp_no_pages'],
			'base_href' => $scripturl . '?action=admin;area=portalpages;sa=list;',
			'default_sort_col' => 'title',
			'get_items' => array(
				'function' => array($this, 'list_spLoadPages'),
			),
			'get_count' => array(
				'function' => array($this, 'list_spCountPages'),
			),
			'columns' => array(
				'title' => array(
					'header' => array(
						'value' => $txt['sp_admin_pages_col_title'],
					),
					'data' => array(
						'db' => 'title',
					),
					'sort' => array(
						'default' => 'title',
						'reverse' => 'title DESC',
					),
				),
				'namespace' => array(
					'header' => array(
						'value' => $txt['sp_admin_pages_col_namespace'],
					),
					'data' => array(
						'db' => 'page_id',
					),
					'sort' => array(
						'default' => 'namespace',
						'reverse' => 'namespace DESC',
					),
				),
				'type' => array(
					'header' => array(
						'value' => $txt['sp_admin_pages_col_type'],
					),
					'data' => array(
						'db' => 'type',
					),
					'sort' => array(
						'default' => 'type',
						'reverse' => 'type DESC',
					),
				),
				'views' => array(
					'header' => array(
						'value' => $txt['sp_admin_pages_col_views'],
					),
					'data' => array(
						'db' => 'views',
					),
					'sort' => array(
						'default' => 'views',
						'reverse' => 'views DESC',
					),
				),
				'status' => array(
					'header' => array(
						'value' => $txt['sp_admin_pages_col_status'],
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
						'value' => $txt['sp_admin_pages_col_actions'],
						'class' => 'centertext',
					),
					'data' => array(
						'sprintf' => array(
							'format' => '<a href="?action=admin;area=portalpages;sa=edit;page_id=%1$s;' . $context['session_var'] . '=' . $context['session_id'] . '" accesskey="e">' . sp_embed_image('modify') . '</a>&nbsp;
								<a href="?action=admin;area=portalpages;sa=delete;page_id=%1$s;' . $context['session_var'] . '=' . $context['session_id'] . '" onclick="return confirm(' . JavaScriptEscape($txt['sp_admin_pages_delete_confirm']) . ') && submitThisOnce(this);" accesskey="d">' . sp_embed_image('delete') . '</a>',
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
				'href' => $scripturl . '?action=admin;area=portalpages;sa=remove',
				'include_sort' => true,
				'include_start' => true,
				'hidden_fields' => array(
					$context['session_var'] => $context['session_id'],
				),
			),
			'additional_rows' => array(
				array(
					'position' => 'below_table_data',
					'value' => '<input type="submit" name="remove_pages" value="' . $txt['sp_admin_pages_remove'] . '" class="right_submit" />',
				),
			),
		);

		// Set the context values
		$context['page_title'] = $txt['sp_admin_pages_title'];
		$context['sub_template'] = 'show_list';
		$context['default_list'] = 'portal_pages';

		// Create the list.
		require_once(SUBSDIR . '/List.class.php');
		createList($listOptions);
	}

	/**
	 * Callback for createList(),
	 * Returns the number of articles in the system
	 *
	 * @param int $messageID
	 */
	public function list_spCountPages()
	{
	   return sp_count_pages();
	}

	/**
	 * Callback for createList()
	 * Returns an array of articles
	 *
	 * @param int $start
	 * @param int $items_per_page
	 * @param string $sort
	 */
	public function list_spLoadPages($start, $items_per_page, $sort)
	{
		return sp_load_pages($start, $items_per_page, $sort);
	}

	/**
	 * Interface for adding/editing a page
	 */
	public function action_sportal_admin_page_edit()
	{
		global $txt, $context, $modSettings, $options;

		$db = database();

		require_once(SUBSDIR . '/Editor.subs.php');
		require_once(SUBSDIR . '/Post.subs.php');

		$context['SPortal']['is_new'] = empty($_REQUEST['page_id']);

		if (!empty($_REQUEST['content_mode']) && $_POST['type'] == 'bbc')
		{
			require_once(SUBSDIR . 'Html2BBC.class.php');
			$bbc_converter = new Convert_BBC($_REQUEST['content']);
			$_REQUEST['content'] = $bbc_converter->get_bbc();

			$_REQUEST['content'] = un_htmlspecialchars($_REQUEST['content']);
			$_POST['content'] = $_REQUEST['content'];
		}

		$context['sides'] = array(
			5 => $txt['sp-positionHeader'],
			1 => $txt['sp-positionLeft'],
			2 => $txt['sp-positionTop'],
			3 => $txt['sp-positionBottom'],
			4 => $txt['sp-positionRight'],
			6 => $txt['sp-positionFooter'],
		);

		$blocks = getBlockInfo();
		$context['page_blocks'] = array();

		foreach ($blocks as $block)
		{
			$shown = false;
			$tests = array('all', 'allpages', 'sforum');
			if (!$context['SPortal']['is_new'])
				$tests[] = 'p' . ((int) $_REQUEST['page_id']);

			foreach (array('display', 'display_custom') as $field)
			{
				if (substr($block[$field], 0, 4) === '$php')
					continue 2;

				$block[$field] = explode(',', $block[$field]);

				if (!$context['SPortal']['is_new'] && in_array('-p' . ((int) $_REQUEST['page_id']), $block[$field]))
					continue;

				foreach ($tests as $test)
				{
					if (in_array($test, $block[$field]))
					{
						$shown = true;
						break;
					}
				}
			}

			$context['page_blocks'][$block['column']][] = array(
				'id' => $block['id'],
				'label' => $block['label'],
				'shown' => $shown,
			);
		}

		if (!empty($_POST['submit']))
		{
			checkSession();

			if (!isset($_POST['title']) || Util::htmltrim(Util::htmlspecialchars($_POST['title'], ENT_QUOTES)) === '')
				fatal_lang_error('sp_error_page_name_empty', false);

			if (!isset($_POST['namespace']) || Util::htmltrim(Util::htmlspecialchars($_POST['namespace'], ENT_QUOTES)) === '')
				fatal_lang_error('sp_error_page_namespace_empty', false);

			$result = $db->query('', '
				SELECT id_page
				FROM {db_prefix}sp_pages
				WHERE namespace = {string:namespace}
					AND id_page != {int:current}
				LIMIT {int:limit}',
				array(
					'limit' => 1,
					'namespace' => Util::htmlspecialchars($_POST['namespace'], ENT_QUOTES),
					'current' => (int) $_POST['page_id'],
				)
			);
			list ($has_duplicate) = $db->fetch_row($result);
			$db->free_result($result);

			if (!empty($has_duplicate))
				fatal_lang_error('sp_error_page_namespace_duplicate', false);

			if (preg_match('~[^A-Za-z0-9_]+~', $_POST['namespace']) != 0)
				fatal_lang_error('sp_error_page_namespace_invalid_chars', false);

			if (preg_replace('~[0-9]+~', '', $_POST['namespace']) === '')
				fatal_lang_error('sp_error_page_namespace_numeric', false);

			if ($_POST['type'] == 'php' && !empty($_POST['content']) && empty($modSettings['sp_disable_php_validation']))
			{
				require_once(SUBSDIR . '/DataValidator.class.php');

				$validator = new Data_Validator();
				$validator->validation_rules(array('content' => 'php_syntax'));
				$validator->validate(array('content' => $_POST['content']));
				$error = $validator->validation_errors();

				if ($error)
					fatal_lang_error($error, false);
			}

			if (!empty($_POST['blocks']) && is_array($_POST['blocks']))
			{
				foreach ($_POST['blocks'] as $id => $block)
					$_POST['blocks'][$id] = (int) $block;
			}
			else
				$_POST['blocks'] = array();

			$fields = array(
				'namespace' => 'string',
				'title' => 'string',
				'body' => 'string',
				'type' => 'string',
				'permissions' => 'int',
				'style' => 'string',
				'status' => 'int',
			);

			$page_info = array(
				'id' => (int) $_POST['page_id'],
				'namespace' => Util::htmlspecialchars($_POST['namespace'], ENT_QUOTES),
				'title' => Util::htmlspecialchars($_POST['title'], ENT_QUOTES),
				'body' => Util::htmlspecialchars($_POST['content'], ENT_QUOTES),
				'type' => in_array($_POST['type'], array('bbc', 'html', 'php')) ? $_POST['type'] : 'bbc',
				'permissions' => (int) $_POST['permissions'],
				'style' => sportal_parse_style('implode'),
				'status' => !empty($_POST['status']) ? 1 : 0,
			);

			if ($page_info['type'] == 'bbc')
				preparsecode($page_info['body']);

			if ($context['SPortal']['is_new'])
			{
				unset($page_info['id']);

				$db->insert('', '
					{db_prefix}sp_pages',
					$fields,
					$page_info,
					array('id_page')
				);
				$page_info['id'] = $db->insert_id('{db_prefix}sp_pages', 'id_page');
			}
			else
			{
				$update_fields = array();
				foreach ($fields as $name => $type)
					$update_fields[] = $name . ' = {' . $type . ':' . $name . '}';

				$db->query('', '
					UPDATE {db_prefix}sp_pages
					SET ' . implode(', ', $update_fields) . '
					WHERE id_page = {int:id}', $page_info
				);
			}

			$to_show = array();
			$not_to_show = array();
			$changes = array();

			foreach ($context['page_blocks'] as $page_blocks)
			{
				foreach ($page_blocks as $block)
				{
					if ($block['shown'] && !in_array($block['id'], $_POST['blocks']))
						$not_to_show[] = $block['id'];
					elseif (!$block['shown'] && in_array($block['id'], $_POST['blocks']))
						$to_show[] = $block['id'];
				}
			}

			foreach ($to_show as $id)
			{
				if ((empty($blocks[$id]['display']) && empty($blocks[$id]['display_custom'])) || $blocks[$id]['display'] == 'sportal')
				{
					$changes[$id] = array(
						'display' => 'portal,p' . $page_info['id'],
						'display_custom' => '',
					);
				}
				elseif (in_array($blocks[$id]['display'], array('allaction', 'allboard')))
				{
					$changes[$id] = array(
						'display' => '',
						'display_custom' => $blocks[$id]['display'] . ',p' . $page_info['id'],
					);
				}
				elseif (in_array('-p' . $page_info['id'], explode(',', $blocks[$id]['display_custom'])))
				{
					$changes[$id] = array(
						'display' => $blocks[$id]['display'],
						'display_custom' => implode(',', array_diff(explode(',', $blocks[$id]['display_custom']), array('-p' . $page_info['id']))),
					);
				}
				elseif (empty($blocks[$id]['display_custom']))
				{
					$changes[$id] = array(
						'display' => implode(',', array_merge(explode(',', $blocks[$id]['display']), array('p' . $page_info['id']))),
						'display_custom' => '',
					);
				}
				else
				{
					$changes[$id] = array(
						'display' => $blocks[$id]['display'],
						'display_custom' => implode(',', array_merge(explode(',', $blocks[$id]['display_custom']), array('p' . $page_info['id']))),
					);
				}
			}

			foreach ($not_to_show as $id)
			{
				if (count(array_intersect(array($blocks[$id]['display'], $blocks[$id]['display_custom']), array('sforum', 'allpages', 'all'))) > 0)
				{
					$changes[$id] = array(
						'display' => '',
						'display_custom' => $blocks[$id]['display'] . $blocks[$id]['display_custom'] . ',-p' . $page_info['id'],
					);
				}
				elseif (empty($blocks[$id]['display_custom']))
				{
					$changes[$id] = array(
						'display' => implode(',', array_diff(explode(',', $blocks[$id]['display']), array('p' . $page_info['id']))),
						'display_custom' => '',
					);
				}
				else
				{
					$changes[$id] = array(
						'display' => implode(',', array_diff(explode(',', $blocks[$id]['display']), array('p' . $page_info['id']))),
						'display_custom' => implode(',', array_diff(explode(',', $blocks[$id]['display_custom']), array('p' . $page_info['id']))),
					);
				}
			}

			foreach ($changes as $id => $data)
			{
				$db->query('', '
					UPDATE {db_prefix}sp_blocks
					SET
						display = {string:display},
						display_custom = {string:display_custom}
					WHERE id_block = {int:id}',
					array(
						'id' => $id,
						'display' => $data['display'],
						'display_custom' => $data['display_custom'],
					)
				);
			}

			redirectexit('action=admin;area=portalpages');
		}

		if (!empty($_POST['preview']))
		{
			$context['SPortal']['page'] = array(
				'id' => $_POST['page_id'],
				'page_id' => $_POST['namespace'],
				'title' => Util::htmlspecialchars($_POST['title'], ENT_QUOTES),
				'body' => Util::htmlspecialchars($_POST['content'], ENT_QUOTES),
				'type' => $_POST['type'],
				'permissions' => $_POST['permissions'],
				'style' => sportal_parse_style('implode'),
				'status' => !empty($_POST['status']),
			);

			if ($context['SPortal']['page']['type'] == 'bbc')
				preparsecode($context['SPortal']['page']['body']);

			loadTemplate('PortalPages');
			$context['SPortal']['preview'] = true;
		}
		elseif ($context['SPortal']['is_new'])
		{
			$context['SPortal']['page'] = array(
				'id' => 0,
				'page_id' => 'page' . mt_rand(1, 5000),
				'title' => $txt['sp_pages_default_title'],
				'body' => '',
				'type' => 'bbc',
				'permissions' => 3,
				'style' => '',
				'status' => 1,
			);
		}
		else
		{
			$_REQUEST['page_id'] = (int) $_REQUEST['page_id'];
			$context['SPortal']['page'] = sportal_get_pages($_REQUEST['page_id']);
		}

		if ($context['SPortal']['page']['type'] == 'bbc')
			$context['SPortal']['page']['body'] = str_replace(array('"', '<', '>', '&nbsp;'), array('&quot;', '&lt;', '&gt;', ' '), un_preparsecode($context['SPortal']['page']['body']));

		if ($context['SPortal']['page']['type'] != 'bbc')
		{
			$temp_editor = !empty($options['wysiwyg_default']);
			$options['wysiwyg_default'] = false;
		}

		$editorOptions = array(
			'id' => 'content',
			'value' => $context['SPortal']['page']['body'],
			'width' => '95%',
			'height' => '200px',
			'preview_type' => 0,
		);
		create_control_richedit($editorOptions);
		$context['post_box_name'] = $editorOptions['id'];

		if (isset($temp_editor))
			$options['wysiwyg_default'] = $temp_editor;

		$context['SPortal']['page']['permission_profiles'] = sportal_get_profiles(null, 1, 'name');
		$context['SPortal']['page']['style'] = sportal_parse_style('explode', $context['SPortal']['page']['style'], !empty($context['SPortal']['preview']));

		if (empty($context['SPortal']['page']['permission_profiles']))
			fatal_lang_error('error_sp_no_permission_profiles', false);

		$context['page_title'] = $context['SPortal']['is_new'] ? $txt['sp_admin_pages_add'] : $txt['sp_admin_pages_edit'];
		$context['sub_template'] = 'pages_edit';
	}

	/**
	 * Update the page status / active on/off
	 */
	public function action_sportal_admin_page_status()
	{
		$db = database();

		checkSession('get');

		$page_id = !empty($_REQUEST['page_id']) ? (int) $_REQUEST['page_id'] : 0;

		$db->query('', '
			UPDATE {db_prefix}sp_pages
			SET status = CASE WHEN status = {int:is_active} THEN 0 ELSE 1 END
			WHERE id_page = {int:id}',
			array(
				'is_active' => 1,
				'id' => $page_id,
			)
		);

		redirectexit('action=admin;area=portalpages');
	}

	/**
	 * Delete a page from the system
	 */
	public function action_sportal_admin_page_delete()
	{
		$page_ids = array();

		// Get the page id's to remove
		if (!empty($_POST['remove_pages']) && !empty($_POST['remove']) && is_array($_POST['remove']))
		{
			checkSession();

			foreach ($_POST['remove'] as $index => $page_id)
				$page_ids[(int) $index] = (int) $page_id;
		}
		elseif (!empty($_REQUEST['page_id']))
		{
			checkSession('get');
			$page_ids[] = (int) $_REQUEST['page_id'];
		}

		// If we have some to remove ....
		if (!empty($page_ids))
			sp_delete_pages($page_ids);

		redirectexit('action=admin;area=portalpages');
	}
}