<?php

/**
 * @package SimplePortal ElkArte
 *
 * @author SimplePortal Team
 * @copyright 2015-2023 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.1
 */

use BBC\PreparseCode;
use ElkArte\Errors\ErrorContext;

/**
 * SimplePortal Page Administration controller class.
 *
 * - This class handles the adding/editing/listing of pages
 */
class ManagePortalPages_Controller extends Action_Controller
{
	/** @var array */
	protected $blocks = array();

	/**
	 * Main dispatcher.
	 * This function checks permissions and passes control through.
	 */
	public function action_index()
	{
		global $context, $txt;

		// Admin or pages permissions required
		if (!allowedTo('sp_admin'))
		{
			isAllowedTo('sp_manage_pages');
		}

		// Can't do much without our little buddys
		require_once(SUBSDIR . '/PortalAdmin.subs.php');
		require_once(SUBSDIR . '/Portal.subs.php');
		loadTemplate('PortalAdminPages');

		// The actions we know
		$subActions = array(
			'list' => array($this, 'action_list'),
			'add' => array($this, 'action_edit'),
			'edit' => array($this, 'action_edit'),
			'status' => array($this, 'action_status'),
			'delete' => array($this, 'action_delete'),
		);

		// Start up the controller, provide a hook since we can
		$action = new Action('portal_page');

		// Default to the list action
		$subAction = $action->initialize($subActions, 'list');
		$context['sub_action'] = $subAction;

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['sp_admin_pages_title'],
			'help' => 'sp_PagesArea',
			'description' => $txt['sp_admin_pages_desc'],
			'tabs' => array(
				'list' => array(),
				'add' => array(),
			),
		);

		// Go!
		$action->dispatch($subAction);
	}

	/**
	 * Show page listing of all portal pages in the system
	 */
	public function action_list()
	{
		global $context, $scripturl, $txt, $modSettings;

		// Build the listoption array to display the categories
		$listOptions = array(
			'id' => 'portal_pages',
			'title' => $txt['sp_admin_pages_list'],
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
						'sprintf' => array(
							'format' => '<a href="?page=%1$s">%2$s</a>',
							'params' => array(
								'page_id' => true,
								'title' => true
							),
						),
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
						'db' => 'page_id'
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
						'class' => 'centertext',
					),
					'data' => array(
						'db' => 'views',
						'class' => 'centertext',
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
							'format' => '<a href="?action=admin;area=portalpages;sa=edit;page_id=%1$s;' . $context['session_var'] . '=' . $context['session_id'] . '" accesskey="e">' . sp_embed_image('edit') . '</a>&nbsp;
								<a href="?action=admin;area=portalpages;sa=delete;page_id=%1$s;' . $context['session_var'] . '=' . $context['session_id'] . '" onclick="return confirm(' . JavaScriptEscape($txt['sp_admin_pages_delete_confirm']) . ') && submitThisOnce(this);" accesskey="d">' . sp_embed_image('trash') . '</a>',
							'params' => array(
								'id' => true,
								'page_id' => true
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
				'href' => $scripturl . '?action=admin;area=portalpages;sa=remove',
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
					'value' => '<a class="linkbutton" href="?action=admin;area=portalpages;sa=add;' . $context['session_var'] . '=' . $context['session_id'] . '" accesskey="a">' . $txt['sp_admin_pages_add'] . '</a>
						<input type="submit" name="remove_pages" value="' . $txt['sp_admin_pages_remove'] . '" />',
				),
			),
		);

		// Set the context values
		$context['page_title'] = $txt['sp_admin_pages_title'];
		$context['sub_template'] = 'show_list';
		$context['default_list'] = 'portal_pages';

		// Create the list.
		require_once(SUBSDIR . '/GenericList.class.php');
		createList($listOptions);
	}

	/**
	 * Callback for createList(),
	 * Returns the number of articles in the system
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
	 *
	 * @return array
	 */
	public function list_spLoadPages($start, $items_per_page, $sort)
	{
		return sp_load_pages($start, $items_per_page, $sort);
	}

	/**
	 * Interface for adding/editing a page
	 */
	public function action_edit()
	{
		global $txt, $context;

		$context['SPortal']['is_new'] = empty($_REQUEST['page_id']);
		$context['SPortal']['preview'] = false;

		$pages_errors = ErrorContext::context('pages', 0);

		// Some help will be needed
		require_once(SUBSDIR . '/Editor.subs.php');
		require_once(SUBSDIR . '/Post.subs.php');

		// Saving the work?
		if (!empty($_POST['submit']) && !$pages_errors->hasErrors())
		{
			checkSession();
			$this->_sportal_admin_page_edit_save();
		}

		// Doing a quick look before you save or you messed up?
		if (!empty($_POST['preview']) || $pages_errors->hasErrors())
		{
			$context['SPortal']['page'] = array(
				'id' => $_POST['page_id'],
				'page_id' => $_POST['namespace'],
				'title' => Util::htmlspecialchars($_POST['title'], ENT_QUOTES),
				'body' => Util::htmlspecialchars($_POST['content'], ENT_QUOTES),
				'type' => $_POST['type'],
				'permissions' => $_POST['permissions'],
				'styles' => (int) $_POST['styles'],
				'status' => !empty($_POST['status']),
			);

			// Fix up bbc errors before we go to the preview
			if ($context['SPortal']['page']['type'] === 'bbc')
			{
				PreparseCode::instance()->preparsecode($context['SPortal']['page']['body'], false);
			}

			loadTemplate('PortalPages');

			// Showing errors or a preview?
			if ($pages_errors->hasErrors())
			{
				$context['pages_errors'] = array(
					'errors' => $pages_errors->prepareErrors(),
					'type' => $pages_errors->getErrorType() == 0 ? 'minor' : 'serious',
					'title' => $txt['sp_form_errors_detected'],
				);
			}
			else
			{
				$context['SPortal']['preview'] = true;

				// The editor will steal focus so we have to delay
				addInlineJavascript('setTimeout(() => $("html, body").animate({scrollTop: $("#preview_section").offset().top}, 250), 750);', true);
			}
		}
		// New page, set up with a random page ID
		elseif ($context['SPortal']['is_new'])
		{
			$context['SPortal']['page'] = array(
				'id' => 0,
				'page_id' => 'page' . random_int(1, 5000),
				'title' => $txt['sp_pages_default_title'],
				'body' => '',
				'type' => 'bbc',
				'permissions' => 3,
				'styles' => 4,
				'status' => 1,
			);
		}
		// Used page :P
		else
		{
			$_REQUEST['page_id'] = (int) $_REQUEST['page_id'];
			$context['SPortal']['page'] = sportal_get_pages($_REQUEST['page_id']);
		}

		if ($context['SPortal']['page']['type'] === 'bbc')
		{
			$context['SPortal']['page']['body'] = PreparseCode::instance()->un_preparsecode($context['SPortal']['page']['body']);
			$context['SPortal']['page']['body'] = str_replace(array('"', '<', '>', '&nbsp;'), array('&quot;', '&lt;', '&gt;', ' '), $context['SPortal']['page']['body']);
		}

		// Set up the editor, values, initial state, etc
		$this->prepareEditor();

		// Set the globals, spplugin will set the editor box as needed (editor or textbox, etc)
		addConversionJS($context['SPortal']['page']['type']);

		// Permissions
		$context['SPortal']['page']['permission_profiles'] = sportal_get_profiles(null, 1, 'name');
		if (empty($context['SPortal']['page']['permission_profiles']))
		{
			throw new Elk_Exception('error_sp_no_permission_profiles', false);
		}

		// Styles
		$context['SPortal']['page']['style_profiles'] = sportal_get_profiles(null, 2, 'name');
		if (empty($context['SPortal']['page']['style_profiles']))
		{
			throw new Elk_Exception('error_sp_no_style_profiles', false);
		}

		// And for the template
		$context['SPortal']['page']['style'] = sportal_select_style($context['SPortal']['page']['styles']);
		$context['SPortal']['page']['body'] = sportal_parse_content($context['SPortal']['page']['body'], $context['SPortal']['page']['type'], 'return');
		$context['page_title'] = $context['SPortal']['is_new'] ? $txt['sp_admin_pages_add'] : $txt['sp_admin_pages_edit'];
		$context['sub_template'] = 'pages_edit';
	}

	/**
	 * Sets up editor options as needed for SP, temporarily ignores any user options
	 * so we can enable it in the proper mode bbc/php/html
	 */
	private function prepareEditor()
	{
		global $context, $options;

		if ($context['SPortal']['page']['type'] !== 'bbc')
		{
			// No wizzy mode if they don't need it
			$temp_editor = !empty($options['wysiwyg_default']);
			$options['wysiwyg_default'] = false;
		}

		$editorOptions = array(
			'id' => 'content',
			'value' => $context['SPortal']['page']['body'],
			'width' => '100%',
			'height' => '275px',
			'preview_type' => 1,
		);
		$editorOptions['plugin_addons'] = array();
		$editorOptions['plugin_addons'][] = 'spplugin';
		create_control_richedit($editorOptions);

		$context['post_box_name'] = $editorOptions['id'];
		$context['post_box_class'] = $context['SPortal']['page']['type'] !== 'bbc' ? 'sceditor-container' : 'sp-sceditor-container';

		if (isset($temp_editor))
		{
			$options['wysiwyg_default'] = $temp_editor;
		}
	}

	/**
	 * Does the actual saving of the page data
	 *
	 * - Validates the data is safe to save
	 * - Updates existing pages or creates new ones
	 */
	private function _sportal_admin_page_edit_save()
	{
		global $txt, $context, $modSettings;

		// No errors, yet.
		$pages_errors = ErrorContext::context('pages', 0);

		// Use our standard validation functions in a few spots
		require_once(SUBSDIR . '/DataValidator.class.php');
		$validator = new Data_Validator();

		// Clean and Review the post data for compliance
		$validator->sanitation_rules(array(
			'title' => 'trim|Util::htmlspecialchars',
			'namespace' => 'trim|Util::htmlspecialchars',
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
			'title' => $txt['sp_error_page_name_empty'],
			'namespace' => $txt['sp_error_page_namespace_empty'],
			'content' => $txt['sp_admin_pages_col_body'],
		));

		// If you messed this up, back you go
		if (!$validator->validate($_POST))
		{
			foreach ($validator->validation_errors() as $id => $error)
			{
				$pages_errors->addError($error);
			}

			$this->action_edit();
		}

		// Can't have the same name in the same space twice
		$has_duplicate = sp_check_duplicate_pages($_POST['namespace'], $_POST['page_id']);
		if (!empty($has_duplicate))
		{
			$pages_errors->addError('sp_error_page_namespace_duplicate');
		}

		// Can't have a simple numeric namespace
		if (preg_replace('~\d+~', '', $_POST['namespace']) === '')
		{
			$pages_errors->addError('sp_error_page_namespace_numeric');
		}

		if ($_POST['type'] === 'php' && !allowedTo('admin_forum'))
		{
			throw new Elk_Exception('cannot_admin_forum', false);
		}

		// Running some php code, then we need to validate its legit code
		if ($_POST['type'] === 'php' && !empty($_POST['content']) && empty($modSettings['sp_disable_php_validation']))
		{
			$validator_php = new Data_Validator();
			$validator_php->validation_rules(array('content' => 'php_syntax'));

			// Bad PHP code
			if (!$validator_php->validate(array('content' => $_POST['content'])))
			{
				$pages_errors->addError($validator_php->validation_errors());
			}
		}

		// None shall pass ... with errors
		if ($pages_errors->hasErrors())
		{
			$this->action_edit();
		}

		// The data for the fields
		$page_info = array(
			'id' => (int) $_POST['page_id'],
			'namespace' => Util::htmlspecialchars($_POST['namespace'], ENT_QUOTES),
			'title' => Util::htmlspecialchars($_POST['title'], ENT_QUOTES),
			'body' => Util::htmlspecialchars($_POST['content'], ENT_QUOTES),
			'type' => in_array($_POST['type'], array('bbc', 'html', 'php', 'markdown')) ? $_POST['type'] : 'bbc',
			'permissions' => (int) $_POST['permissions'],
			'styles' => (int) $_POST['styles'],
			'status' => !empty($_POST['status']) ? 1 : 0,
		);

		if ($page_info['type'] === 'bbc')
		{
			PreparseCode::instance()->preparsecode($page_info['body'], false);
		}

		// Save away
		sp_save_page($page_info, $context['SPortal']['is_new']);

		redirectexit('action=admin;area=portalpages');

		return true;
	}

	/**
	 * Update the page status / active on/off
	 */
	public function action_status()
	{
		global $context;

		checkSession(isset($_REQUEST['xml']) ? '' : 'get');

		$page_id = !empty($_REQUEST['page_id']) ? (int) $_REQUEST['page_id'] : 0;
		$state = sp_changeState('page', $page_id);

		// Doing this the ajax way?
		if (isset($_REQUEST['xml']))
		{
			$context['item_id'] = $page_id;
			$context['status'] = !empty($state) ? 'active' : 'deactive';

			// Clear out any template layers, add the xml response
			loadTemplate('PortalAdmin');
			$template_layers = Template_Layers::instance();
			$template_layers->removeAll();
			$context['sub_template'] = 'change_status';

			obExit();
		}

		redirectexit('action=admin;area=portalpages');
	}

	/**
	 * Delete a page from the system
	 */
	public function action_delete()
	{
		$page_ids = array();

		// Get the page id's to remove
		if (!empty($_POST['remove_pages']) && !empty($_POST['remove']) && is_array($_POST['remove']))
		{
			checkSession();

			foreach ($_POST['remove'] as $index => $page_id)
			{
				$page_ids[(int) $index] = (int) $page_id;
			}
		}
		elseif (!empty($_REQUEST['page_id']))
		{
			checkSession('get');
			$page_ids[] = (int) $_REQUEST['page_id'];
		}

		// If we have some to remove ....
		if (!empty($page_ids))
		{
			sp_delete_pages($page_ids);
		}

		redirectexit('action=admin;area=portalpages');
	}
}
