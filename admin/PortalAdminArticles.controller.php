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
 * SimplePortal Article Administration controller class.
 * This class handles the adding/editing/listing of articles
 */
class ManagePortalArticles_Controller extends Action_Controller
{
	/**
	 * Main dispatcher.
	 * This function checks permissions and passes control through.
	 */
	public function action_index()
	{
		global $context, $txt;

		// You need to be an admin or have manage article permissions
		if (!allowedTo('sp_admin'))
			isAllowedTo('sp_manage_articles');

		// We'll need the utility functions from here.
		require_once(SUBSDIR . '/PortalAdmin.subs.php');
		require_once(SUBSDIR . '/Portal.subs.php');
		loadTemplate('PortalAdminArticles');

		$subActions = array(
			'list' => array($this, 'action_sportal_admin_article_list'),
			'add' => array($this, 'action_sportal_admin_article_edit'),
			'edit' => array($this, 'action_sportal_admin_article_edit'),
			'status' => array($this, 'action_sportal_admin_article_status'),
			'delete' => array($this, 'action_sportal_admin_article_delete'),
		);

		// By default we want to list the articles
		$subAction = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'list';
		$context['sub_action'] = $subAction;

		// Set up the tab data
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['sp_admin_articles_title'],
			'help' => 'sp_ArticlesArea',
			'description' => $txt['sp_admin_articles_desc'],
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
	 * Show a listing of articles in the system
	 */
	public function action_sportal_admin_article_list()
	{
		global $context, $scripturl, $txt, $modSettings;

		// build the listoption array to display the categories
		$listOptions = array(
			'id' => 'portal_articles',
			'title' => $txt['sp_admin_articles_list'],
			'items_per_page' => $modSettings['defaultMaxMessages'],
			'no_items_label' => $txt['error_sp_no_articles'],
			'base_href' => $scripturl . '?action=admin;area=portalarticles;sa=list;',
			'default_sort_col' => 'title',
			'get_items' => array(
				'function' => array($this, 'list_spLoadArticles'),
			),
			'get_count' => array(
				'function' => array($this, 'list_spCountArticles'),
			),
			'columns' => array(
				'title' => array(
					'header' => array(
						'value' => $txt['sp_admin_articles_col_title'],
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
						'value' => $txt['sp_admin_articles_col_namespace'],
					),
					'data' => array(
						'db' => 'article_id',
					),
					'sort' => array(
						'default' => 'article_namespace',
						'reverse' => 'article_namespace DESC',
					),
				),
				'category' => array(
					'header' => array(
						'value' => $txt['sp_admin_articles_col_category'],
					),
					'data' => array(
						'db' => 'category_name',
					),
					'sort' => array(
						'default' => 'name',
						'reverse' => 'name DESC',
					),
				),
				'author' => array(
					'header' => array(
						'value' => $txt['sp_admin_articles_col_author'],
					),
					'data' => array(
						'db' => 'author_name',
					),
					'sort' => array(
						'default' => 'author_name',
						'reverse' => 'author_name DESC',
					),
				),
				'type' => array(
					'header' => array(
						'value' => $txt['sp_admin_articles_col_type'],
					),
					'data' => array(
						'db' => 'type',
					),
					'sort' => array(
						'default' => 'type',
						'reverse' => 'type DESC',
					),
				),
				'date' => array(
					'header' => array(
						'value' => $txt['sp_admin_articles_col_date'],
					),
					'data' => array(
						'db' => 'date',
					),
					'sort' => array(
						'default' => 'date',
						'reverse' => 'date DESC',
					),
				),
				'status' => array(
					'header' => array(
						'value' => $txt['sp_admin_articles_col_status'],
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
				'href' => $scripturl . '?action=admin;area=portalarticles;sa=remove',
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
		$context['page_title'] = $txt['sp_admin_articles_title'];
		$context['sub_template'] = 'show_list';
		$context['default_list'] = 'portal_articles';

		// Create the list.
		require_once(SUBSDIR . '/List.subs.php');
		createList($listOptions);
	}

	/**
	 * Callback for createList(),
	 * Returns the number of articles in the system
	 *
	 * @param int $messageID
	 */
	public function list_spCountArticles()
	{
	   return sp_count_articles();
	}

	/**
	 * Callback for createList()
	 * Returns an array of articles
	 *
	 * @param int $start
	 * @param int $items_per_page
	 * @param string $sort
	 */
	public function list_spLoadArticles($start, $items_per_page, $sort)
	{
		return sp_load_articles($start, $items_per_page, $sort);
	}

	/**
	 * Edits an existing or adds a new article to the system
	 * Handles the previewing of an article
	 */
	public function action_sportal_admin_article_edit()
	{
		global $context, $modSettings, $user_info, $options, $txt;

		$db = database();

		require_once(SUBSDIR . '/Editor.subs.php');
		require_once(SUBSDIR . '/Post.subs.php');

		$context['is_new'] = empty($_REQUEST['article_id']);

		// @todo we don't have html_to_bbc any longer
		if (!empty($_REQUEST['content_mode']) && $_POST['type'] == 'bbc')
		{
			$_REQUEST['content'] = html_to_bbc($_REQUEST['content']);
			$_REQUEST['content'] = un_htmlspecialchars($_REQUEST['content']);
			$_POST['content'] = $_REQUEST['content'];
		}

		if (!empty($_POST['submit']))
		{
			checkSession();

			if (!$context['is_new'])
			{
				$_REQUEST['article_id'] = (int) $_REQUEST['article_id'];
				$context['article'] = sportal_get_articles($_REQUEST['article_id']);
			}

			if (!isset($_POST['title']) || Util::htmltrim(Util::htmlspecialchars($_POST['title'], ENT_QUOTES)) === '')
				fatal_lang_error('sp_error_article_name_empty', false);

			if (!isset($_POST['namespace']) || Util::htmltrim(Util::htmlspecialchars($_POST['namespace'], ENT_QUOTES)) === '')
				fatal_lang_error('sp_error_article_namespace_empty', false);

			$result = $db->query('', '
				SELECT id_article
				FROM {db_prefix}sp_articles
				WHERE namespace = {string:namespace}
					AND id_article != {int:current}
				LIMIT 1',
				array(
					'limit' => 1,
					'namespace' => Util::htmlspecialchars($_POST['namespace'], ENT_QUOTES),
					'current' => (int) $_POST['article_id'],
				)
			);
			list ($has_duplicate) = $db->fetch_row($result);
			$db->free_result($result);

			if (!empty($has_duplicate))
				fatal_lang_error('sp_error_article_namespace_duplicate', false);

			if (preg_match('~[^A-Za-z0-9_]+~', $_POST['namespace']) != 0)
				fatal_lang_error('sp_error_article_namespace_invalid_chars', false);

			if (preg_replace('~[0-9]+~', '', $_POST['namespace']) === '')
				fatal_lang_error('sp_error_article_namespace_numeric', false);

			if ($_POST['type'] == 'php' && !empty($_POST['content']) && empty($modSettings['sp_disable_php_validation']))
			{
				$error = sp_validate_php($_POST['content']);

				if ($error)
					fatal_lang_error('error_sp_php_' . $error, false);
			}

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
				'id_category' => 'int',
				'namespace' => 'string',
				'title' => 'string',
				'body' => 'string',
				'type' => 'string',
				'permission_set' => 'int',
				'groups_allowed' => 'string',
				'groups_denied' => 'string',
				'status' => 'int',
			);

			$article_info = array(
				'id' => (int) $_POST['article_id'],
				'id_category' => (int) $_POST['category_id'],
				'namespace' => Util::htmlspecialchars($_POST['namespace'], ENT_QUOTES),
				'title' => Util::htmlspecialchars($_POST['title'], ENT_QUOTES),
				'body' => Util::htmlspecialchars($_POST['content'], ENT_QUOTES),
				'type' => $_POST['type'],
				'permission_set' => $permission_set,
				'groups_allowed' => $groups_allowed,
				'groups_denied' => $groups_denied,
				'status' => !empty($_POST['status']) ? 1 : 0,
			);

			if ($article_info['type'] == 'bbc')
				preparsecode($article_info['body']);

			if ($context['is_new'])
			{
				unset($article_info['id']);

				$fields = array_merge($fields, array(
					'id_member' => 'int',
					'member_name' => 'string',
					'date' => 'int',
				));

				$article_info = array_merge($article_info, array(
					'id_member' => $user_info['id'],
					'member_name' => $user_info['name'],
					'date' => time(),
				));

				$db->insert('', '
					{db_prefix}sp_articles',
					$fields,
					$article_info,
					array('id_article')
				);
				$article_info['id'] = $db->insert_id('{db_prefix}sp_articles', 'id_article');
			}
			else
			{
				$update_fields = array();
				foreach ($fields as $name => $type)
					$update_fields[] = $name . ' = {' . $type . ':' . $name . '}';

				$db->query('', '
					UPDATE {db_prefix}sp_articles
					SET ' . implode(', ', $update_fields) . '
					WHERE id_article = {int:id}',
					array (
						'id' => $article_info['id'],
					)
				);
			}

			if ($context['is_new'] || $article_info['id_category'] != $context['article']['category']['id'])
			{
				$db->query('', '
					UPDATE {db_prefix}sp_categories
					SET articles = articles + 1
					WHERE id_category = {int:id}',
					array(
						'id' => $article_info['id_category'],
					)
				);

				if (!$context['is_new'])
				{
					$db->query('', '
						UPDATE {db_prefix}sp_categories
						SET articles = articles - 1
						WHERE id_category = {int:id}',
						array(
							'id' => $context['article']['category']['id'],
						)
					);
				}
			}

			redirectexit('action=admin;area=portalarticles');
		}

		if (!empty($_POST['preview']))
		{
			$permission_set = 0;
			$groups_allowed = $groups_denied = array();

			if (!empty($_POST['permission_set']))
				$permission_set = (int) $_POST['permission_set'];
			elseif (!empty($_POST['membergroups']) && is_array($_POST['membergroups']))
			{
				foreach ($_POST['membergroups'] as $id => $value)
				{
					if ($value == 1)
						$groups_allowed[] = (int) $id;
					elseif ($value == -1)
						$groups_denied[] = (int) $id;
				}
			}

			if (!$context['is_new'])
			{
				$_REQUEST['article_id'] = (int) $_REQUEST['article_id'];
				$current = sportal_get_articles($_REQUEST['article_id']);
				$author = $current['author'];
				$date = timeformat($current['date']);
			}
			else
			{
				$author = array('link' => '<a href="' . $scripturl .'?action=profile;u=' . $user_info['id'] . '">' . $user_info['name'] . '</a>');
				$date = timeformat(time());
			}

			$context['article'] = array(
				'id' => $_POST['article_id'],
				'article_id' => $_POST['namespace'],
				'category' => sportal_get_categories((int) $_POST['category_id']),
				'author' => $author,
				'title' => Util::htmlspecialchars($_POST['title'], ENT_QUOTES),
				'body' => Util::htmlspecialchars($_POST['content'], ENT_QUOTES),
				'type' => $_POST['type'],
				'permission_set' => $permission_set,
				'groups_allowed' => $groups_allowed,
				'groups_denied' => $groups_denied,
				'date' => $date,
				'status' => !empty($_POST['status']),
			);

			if ($context['article']['type'] == 'bbc')
				preparsecode($context['article']['body']);

			loadTemplate('PortalArticles');
			$context['preview'] = true;
		}
		elseif ($context['is_new'])
		{
			$context['article'] = array(
				'id' => 0,
				'article_id' => 'article' . mt_rand(1, 5000),
				'category' => array('id' => 0),
				'title' => $txt['sp_articles_default_title'],
				'body' => '',
				'type' => 'bbc',
				'permission_set' => 3,
				'groups_allowed' => array(),
				'groups_denied' => array(),
				'status' => 1,
			);
		}
		else
		{
			$_REQUEST['article_id'] = (int) $_REQUEST['article_id'];
			$context['article'] = sportal_get_articles($_REQUEST['article_id']);
		}

		if ($context['article']['type'] == 'bbc')
			$context['article']['body'] = str_replace(array('"', '<', '>', '&nbsp;'), array('&quot;', '&lt;', '&gt;', ' '), un_preparsecode($context['article']['body']));

		if ($context['article']['type'] != 'bbc')
		{
			$temp_editor = !empty($options['wysiwyg_default']);
			$options['wysiwyg_default'] = false;
		}

		$editor_options = array(
			'id' => 'content',
			'value' => $context['article']['body'],
			'width' => '95%',
			'height' => '200px',
			'preview_type' => 0,
		);
		create_control_richedit($editor_options);
		$context['post_box_name'] = $editor_options['id'];

		if (isset($temp_editor))
			$options['wysiwyg_default'] = $temp_editor;

		$context['article']['groups'] = sp_load_membergroups();
		$context['article']['categories'] = sportal_get_categories();

		if (empty($context['article']['categories']))
			fatal_lang_error('error_sp_no_category', false);

		$context['page_title'] = $context['is_new'] ? $txt['sp_admin_articles_add'] : $txt['sp_admin_articles_edit'];
		$context['sub_template'] = 'articles_edit';
	}

	/**
	 * Update an articles status
	 */
	public function action_sportal_admin_article_status()
	{
		$db = database();

		checkSession('get');

		$article_id = !empty($_REQUEST['article_id']) ? (int) $_REQUEST['article_id'] : 0;

		$db->query('', '
			UPDATE {db_prefix}sp_articles
			SET status = CASE WHEN status = {int:is_active} THEN 0 ELSE 1 END
			WHERE id_article = {int:id}',
			array(
				'is_active' => 1,
				'id' => $article_id,
			)
		);

		redirectexit('action=admin;area=portalarticles');
	}

	/**
	 * Remove an article from the system
	 */
	public function action_sportal_admin_article_delete()
	{
		$article_ids = array();

		// Get the article id's to remove
		if (!empty($_POST['remove_articles']) && !empty($_POST['remove']) && is_array($_POST['remove']))
		{
			checkSession();

			foreach ($_POST['remove'] as $index => $article_id)
				$article_ids[(int) $index] = (int) $article_id;
		}
		elseif (!empty($_REQUEST['article_id']))
		{
			checkSession('get');
			$article_ids[] = (int) $_REQUEST['article_id'];
		}

		// If we have some to remove ....
		if (!empty($article_ids))
		{
			// Update the counts as we are about to remove some articles
			foreach ($article_ids as $index => $article_id)
			{
				$article_info = sportal_get_articles($article_id);
				sp_category_update_total($article_info['category']['id']);
			}
			
			sp_delete_articles($article_ids);
		}

		redirectexit('action=admin;area=portalarticles');
	}
}