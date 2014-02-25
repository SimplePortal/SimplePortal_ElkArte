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
 * SimplePortal Article Administation controller class.
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

		// This are all the actions that we know
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
		$context['sub_action'] = $subAction;
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

		// Call the right function for this sub-action
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

		// Build the listoption array to display the categories
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
						'function' => create_function('$row', '
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
		require_once(SUBSDIR . '/List.class.php');
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
		global $context, $user_info, $options, $txt, $scripturl;

		$context['is_new'] = empty($_REQUEST['article_id']);

		$article_errors = Error_Context::context('article', 0);

		// Going to use editor an post functions
		require_once(SUBSDIR . '/Post.subs.php');
		require_once(SUBSDIR . '/Editor.subs.php');

		// Convert this to BBC?
		if (!empty($_REQUEST['content_mode']) && $_POST['type'] == 'bbc')
		{
			$convert = $_REQUEST['content'];
			require_once(SUBSDIR . '/Html2BBC.class.php');
			$bbc_converter = new Convert_BBC($convert);
			$convert = $bbc_converter->get_bbc();
			$convert = un_htmlspecialchars($convert);
			$_POST['content'] = $convert;
		}

		// Saving the work?
		if (!empty($_POST['submit']) && !$article_errors->hasErrors())
		{
			checkSession();
			$this->_sportal_admin_article_edit_save();
		}

		// Just taking a look before you save?
		if (!empty($_POST['preview']) || $article_errors->hasErrors())
		{
			if (!$context['is_new'])
			{
				$_REQUEST['article_id'] = (int) $_REQUEST['article_id'];
				$current = sportal_get_articles($_REQUEST['article_id']);
				$author = $current['author'];
				$date = standardTime($current['date']);
			}
			else
			{
				$author = array('link' => '<a href="' . $scripturl .'?action=profile;u=' . $user_info['id'] . '">' . $user_info['name'] . '</a>');
				$date = standardTime(time());
			}

			$context['article'] = array(
				'id' => $_POST['article_id'],
				'article_id' => $_POST['namespace'],
				'category' => sportal_get_categories((int) $_POST['category_id']),
				'author' => $author,
				'title' => Util::htmlspecialchars($_POST['title'], ENT_QUOTES),
				'body' => Util::htmlspecialchars($_POST['content'], ENT_QUOTES),
				'type' => $_POST['type'],
				'permissions' => $_POST['permissions'],
				'date' => $date,
				'status' => !empty($_POST['status']),
			);

			if ($context['article']['type'] === 'bbc')
				preparsecode($context['article']['body']);

			loadTemplate('PortalArticles');

			// Showing errors or a preview?
			if ($article_errors->hasErrors())
				$context['article_errors'] = array(
					'errors' => $article_errors->prepareErrors(),
					'type' => $article_errors->getErrorType() == 0 ? 'minor' : 'serious',
					'title' => $txt['ban_errors_detected'],
				);
			else
				$context['preview'] = true;
		}
		// Something new?
		elseif ($context['is_new'])
		{
			$context['article'] = array(
				'id' => 0,
				'article_id' => 'article' . mt_rand(1, 5000),
				'category' => array('id' => 0),
				'title' => $txt['sp_articles_default_title'],
				'body' => '',
				'type' => 'bbc',
				'permissions' => 3,
				'status' => 1,
			);
		}
		// Something used
		else
		{
			$_REQUEST['article_id'] = (int) $_REQUEST['article_id'];
			$context['article'] = sportal_get_articles($_REQUEST['article_id']);
		}

		if ($context['article']['type'] === 'bbc')
			$context['article']['body'] = str_replace(array('"', '<', '>', '&nbsp;'), array('&quot;', '&lt;', '&gt;', ' '), un_preparsecode($context['article']['body']));

		// Override user prefs for wizzy mode if they don't need it
		if ($context['article']['type'] !== 'bbc')
		{
			$temp_editor = !empty($options['wysiwyg_default']);
			$options['wysiwyg_default'] = false;
		}

		// Fire up the editor
		$editor_options = array(
			'id' => 'content',
			'value' => $context['article']['body'],
			'width' => '95%',
			'height' => '200px',
			'preview_type' => 0,
		);
		create_control_richedit($editor_options);
		$context['post_box_name'] = $editor_options['id'];

		// Restore thier settings
		if (isset($temp_editor))
			$options['wysiwyg_default'] = $temp_editor;

		// Final bits for the template, categorys and permission settings
		$context['article']['permission_profiles'] = sportal_get_profiles(null, 1, 'name');
		$context['article']['categories'] = sportal_get_categories();

		// Articles need permissions and categorys defined
		if (empty($context['article']['permission_profiles']))
			fatal_lang_error('error_sp_no_permission_profiles', false);
		if (empty($context['article']['categories']))
			fatal_lang_error('error_sp_no_category', false);

		$context['page_title'] = $context['is_new'] ? $txt['sp_admin_articles_add'] : $txt['sp_admin_articles_edit'];
		$context['sub_template'] = 'articles_edit';
	}

	/**
	 * Does the actual saving of the article data
	 *
	 * - validates the data is safe to save
	 * - updates existing articles or creates new ones
	 */
	private function _sportal_admin_article_edit_save()
	{
		global $context, $txt;

		// No errors, yet.
		$article_errors = Error_Context::context('article', 0);

		// Use our standard validation functions in a few spots
		require_once(SUBSDIR . '/DataValidator.class.php');
		$validator = new Data_Validator();

		// If its not new, lets load the current data
		$is_new = empty($_REQUEST['article_id']);
		if (!$is_new)
		{
			$_REQUEST['article_id'] = (int) $_REQUEST['article_id'];
			$context['article'] = sportal_get_articles($_REQUEST['article_id']);
		}

		// Clean and Review the post data for compliance
		$validator->sanitation_rules(array(
			'title' => 'trim|Util::htmlspecialchars',
			'namespace' => 'trim|Util::htmlspecialchars',
			'article_id' => 'intval',
			'category_id' => 'intval',
			'permissions' => 'intval',
			'type' => 'trim',
			'content' => 'trim'
		));
		$validator->validation_rules(array(
			'title' => 'required',
			'namespace' => 'alpha_numeric|required',
			'type' => 'required',
			'content' => 'required'
		));
		$validator->text_replacements(array(
			'title' => $txt['sp_admin_articles_col_title'],
			'namespace' => $txt['sp_admin_articles_col_namespace'],
			'content' => $txt['sp_admin_articles_col_body']
		));

		// If you messed this up, back you go
		if (!$validator->validate($_POST))
		{
			foreach ($validator->validation_errors() as $id => $error)
				$article_errors->addError($error);

			return $this->action_sportal_admin_article_edit();
		}

		// Lets make sure this namespace (article id) is unique
		$has_duplicate = sp_duplicate_articles($validator->article_id, $validator->namespace);
		if (!empty($has_duplicate))
			$article_errors->addError('sp_error_article_namespace_duplicate');

		// And we can't have just a numeric namespace (article id)
		if (preg_replace('~[0-9]+~', '', $validator->namespace) === '')
			$article_errors->addError('sp_error_article_namespace_numeric');

		// Posting some PHP code, and allowed? Then we need to validate it will run
		if ($_POST['type'] === 'php' && !empty($_POST['content']) && empty($modSettings['sp_disable_php_validation']))
		{
			$validator_php = new Data_Validator();
			$validator_php->validation_rules(array('content' => 'php_syntax'));

			// Bad PHP code
			if (!$validator_php->validate(array('content' => $_POST['content'])))
				$article_errors->addError($validator_php->validation_errors());
		}

		// None shall pass ... with errors
		if ($article_errors->hasErrors())
			return $this->action_sportal_admin_article_edit();

		// No errors then, prepare the data for saving
		$article_info = array(
			'id' => $validator->article_id,
			'id_category' => $validator->category_id,
			'namespace' => $validator->namespace,
			'title' => $validator->title,
			'body' => Util::htmlspecialchars($_POST['content'], ENT_QUOTES),
			'type' => in_array($validator->type, array('bbc', 'html', 'php')) ? $_POST['type'] : 'bbc',
			'permissions' => $validator->permissions,
			'status' => !empty($_POST['status']) ? 1 : 0,
		);

		if ($article_info['type'] === 'bbc')
			preparsecode($article_info['body']);

		// Save away
		sp_save_article($article_info, $is_new);

		redirectexit('action=admin;area=portalarticles');
	}

	/**
	 * Toggle an articles status
	 */
	public function action_sportal_admin_article_status()
	{
		checkSession('get');

		$article_id = !empty($_REQUEST['article_id']) ? (int) $_REQUEST['article_id'] : 0;
		sp_changeState('article', $article_id);

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