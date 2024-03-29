<?php

/**
 * @package SimplePortal ElkArte
 *
 * @author SimplePortal Team
 * @copyright 2015-2023 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.2
 */

use BBC\PreparseCode;
use ElkArte\Errors\AttachmentErrorContext;
use ElkArte\Errors\ErrorContext;

/**
 * SimplePortal Article Administration controller class.
 * This class handles the adding/editing/listing of articles
 */
class ManagePortalArticles_Controller extends Action_Controller
{
	/** @var bool|int hold the article id if existing */
	protected $_is_aid;

	/** @var array */
	protected $_attachments;

	/** @var ErrorContext */
	protected $article_errors;

	/** @var AttachmentErrorContext */
	protected $attach_errors;

	/**
	 * This method is executed before any action handler.
	 * Loads common things for all methods
	 */
	public function pre_dispatch()
	{
		// We'll need the utility functions from here.
		require_once(SUBSDIR . '/PortalAdmin.subs.php');
		require_once(SUBSDIR . '/Portal.subs.php');
		require_once(SUBSDIR . '/PortalArticle.subs.php');
	}

	/**
	 * Main article dispatcher.
	 *
	 * This function checks permissions and passes control through.
	 */
	public function action_index()
	{
		global $context, $txt;

		// You need to be an admin or have manage article permissions
		if (!allowedTo('sp_admin'))
		{
			isAllowedTo('sp_manage_articles');
		}

		loadTemplate('PortalAdminArticles');

		// These are all the articles actions that we know
		$subActions = array(
			'list' => array($this, 'action_list'),
			'add' => array($this, 'action_edit'),
			'edit' => array($this, 'action_edit'),
			'status' => array($this, 'action_status'),
			'delete' => array($this, 'action_delete'),
		);

		// Start up the controller, provide a hook since we can
		$action = new Action('portal_articles');

		// Set up the tab data
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['sp_admin_articles_title'],
			'help' => 'sp_ArticlesArea',
			'description' => $txt['sp_admin_articles_desc'],
			'tabs' => array(
				'list' => array(),
				'add' => array(),
			),
		);

		// By default, we want to list articles
		$subAction = $action->initialize($subActions, 'list');
		$context['sub_action'] = $subAction;

		// Call the right function for this sub-action
		$action->dispatch($subAction);
	}

	/**
	 * Show a listing of articles in the system
	 */
	public function action_list()
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
						'sprintf' => array(
							'format' => '<a href="?article=%1$s">%2$s</a>',
							'params' => array(
								'article_id' => true,
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
						'sprintf' => array(
							'format' => '<a href="?category=%1$s">%2$s</a>',
							'params' => array(
								'category_id' => true,
								'category_name' => true
							),
						),
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
							'format' => '
								<a href="?action=admin;area=portalarticles;sa=edit;article_id=%1$s;' . $context['session_var'] . '=' . $context['session_id'] . '" accesskey="e">' . sp_embed_image('edit') . '</a>&nbsp;
								<a href="?action=admin;area=portalarticles;sa=delete;article_id=%1$s;' . $context['session_var'] . '=' . $context['session_id'] . '" onclick="return confirm(' . JavaScriptEscape($txt['sp_admin_articles_delete_confirm']) . ') && submitThisOnce(this);" accesskey="d">' . sp_embed_image('trash') . '</a>',
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
				'href' => $scripturl . '?action=admin;area=portalarticles;sa=delete',
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
					'value' => '<a class="linkbutton" href="?action=admin;area=portalarticles;sa=add;' . $context['session_var'] . '=' . $context['session_id'] . '" accesskey="a">' . $txt['sp_admin_articles_add'] . '</a>
						<input type="submit" name="remove_articles" value="' . $txt['sp_admin_articles_remove'] . '" />',
				),
			),
		);

		// Set the context values
		$context['page_title'] = $txt['sp_admin_articles_title'];
		$context['sub_template'] = 'show_list';
		$context['default_list'] = 'portal_articles';

		// Create the list.
		require_once(SUBSDIR . '/GenericList.class.php');
		createList($listOptions);
	}

	/**
	 * Callback for createList(),
	 * Returns the number of articles in the system
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
	 *
	 * @return array
	 */
	public function list_spLoadArticles($start, $items_per_page, $sort)
	{
		return sp_load_articles($start, $items_per_page, $sort);
	}

	/**
	 * Edits an existing OR adds a new article to the system
	 *
	 * - Handles the previewing of an article
	 * - Handles article attachments
	 */
	public function action_edit()
	{
		global $context, $txt;

		// Load dependency's, prepare error checking
		$this->editInit();

		// Want to save the work?
		if (!empty($_POST['submit']) && !$this->article_errors->hasErrors() && !$this->attach_errors->hasErrors())
		{
			// If the session has timed out, let the user re-submit their form.
			if (checkSession('post', '', false) !== '')
			{
				$this->article_errors->addError('session_timeout');

				// Disable the preview and the save
				unset($_POST['preview'], $_POST['submit']);

				return $this->action_edit();
			}

			// Check for errors and if none Save it
			$this->_sportal_admin_article_edit_save();
		}

		// Prepare the form fields, preview, errors, etc
		$this->prepareArticleForm();

		// On to the editor
		if ($context['article']['type'] === 'bbc')
		{
			$context['article']['body'] = PreparseCode::instance()->un_preparsecode($context['article']['body']);
			$context['article']['body'] = str_replace(array('"', '<', '>', '&nbsp;'), array('&quot;', '&lt;', '&gt;', ' '), $context['article']['body']);
		}

		$this->prepareEditor();

		// Final bits for the template, category's, styles and permission profiles
		$this->loadProfileContext();

		// Attachments
		if ($context['attachments']['can']['post'])
		{
			$this->article_attachment();
			$this->article_attachment_dd();
		}

		// Set the globals, spplugin will be called with editor init to set mode
		addConversionJS($context['article']['type']);

		// Finally the main template
		loadTemplate('PortalAdminArticles');
		$context['sub_template'] = 'articles';

		// The article above/below template
		$template_layers = Template_Layers::instance();
		$template_layers->add('articles_edit');

		// Page out values
		$context[$context['admin_menu_name']]['current_subsection'] = 'add';
		$context['article']['style'] = sportal_select_style($context['article']['styles']);
		$context['is_new'] = $this->_is_aid;
		$context['article']['body'] = sportal_parse_content($context['article']['body'], $context['article']['type'], 'return');
		$context['page_title'] = !$this->_is_aid ? $txt['sp_admin_articles_add'] : $txt['sp_admin_articles_edit'];

		return true;
	}

	/**
	 * Loads in dependency's for saving or editing an article
	 */
	private function editInit()
	{
		global $modSettings, $context;

		$this->_is_aid = empty($_REQUEST['article_id']) ? false : (int) $_REQUEST['article_id'];

		// Going to use editor, attachment and post functions
		require_once(SUBSDIR . '/Post.subs.php');
		require_once(SUBSDIR . '/Editor.subs.php');
		require_once(SUBSDIR . '/Attachments.subs.php');

		loadLanguage('Post');
		loadLanguage('Errors');

		// Errors are likely
		$this->article_errors = ErrorContext::context('article', 0);
		$this->attach_errors = AttachmentErrorContext::context();
		$this->attach_errors->activate();

		$context['attachments']['can']['post'] = !empty($modSettings['attachmentEnable']) && $modSettings['attachmentEnable'] == 1 && (allowedTo('post_attachment'));
	}

	/**
	 * Does the actual saving of the article data
	 *
	 * - Validates the data is safe to save
	 * - Updates existing articles or creates new ones
	 */
	private function _sportal_admin_article_edit_save()
	{
		global $context, $txt, $modSettings;

		checkSession();

		// Use our standard validation functions in a few spots
		require_once(SUBSDIR . '/DataValidator.class.php');
		$validator = new Data_Validator();

		// If it exists, load the current data
		if ($this->_is_aid)
		{
			$_POST['article_id'] = $this->_is_aid;
			$context['article'] = sportal_get_articles($this->_is_aid);
		}

		// Clean and Review the post data for compliance
		$validator->sanitation_rules(array(
			'title' => 'trim|Util::htmlspecialchars',
			'namespace' => 'trim|Util::htmlspecialchars',
			'article_id' => 'intval',
			'category_id' => 'intval',
			'permissions' => 'intval',
			'styles' => 'intval',
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

		// If you messed this up, tell them why
		if (!$validator->validate($_POST))
		{
			foreach ($validator->validation_errors() as $id => $error)
			{
				$this->article_errors->addError($error);
			}
		}

		// Lets make sure this namespace (article id) is unique
		$has_duplicate = sp_duplicate_articles($validator->article_id, $validator->namespace);
		if (!empty($has_duplicate))
		{
			$this->article_errors->addError('sp_error_article_namespace_duplicate');
		}

		// And we can't have just a numeric namespace (article id)
		if (preg_replace('~[0-9]+~', '', $validator->namespace) === '')
		{
			$this->article_errors->addError('sp_error_article_namespace_numeric');
		}

		// Posting some PHP code, and allowed? Then we need to validate it will run
		if ($_POST['type'] === 'php' && !empty($_POST['content']) && empty($modSettings['sp_disable_php_validation']))
		{
			$validator_php = new Data_Validator();
			$validator_php->validation_rules(array('content' => 'php_syntax'));

			// Bad PHP code
			if (!$validator_php->validate(array('content' => $_POST['content'])))
			{
				$this->article_errors->addError($validator_php->validation_errors());
			}
		}

		// Check / Prep attachments
		$this->processArticleAttachments();

		// None shall pass ... with errors
		if ($this->article_errors->hasErrors() || $this->attach_errors->hasErrors())
		{
			unset($_POST['submit']);

			return false;
		}

		// No errors then, prepare the data for saving
		$article_info = array(
			'id' => $validator->article_id,
			'id_category' => $validator->category_id,
			'namespace' => $validator->namespace,
			'title' => $validator->title,
			'body' => Util::htmlspecialchars($_POST['content'], ENT_QUOTES),
			'type' => in_array($validator->type, array('bbc', 'html', 'php', 'markdown')) ? $_POST['type'] : 'bbc',
			'permissions' => $validator->permissions,
			'styles' => $validator->styles,
			'status' => !empty($_POST['status']) ? 1 : 0,
		);

		if ($article_info['type'] === 'bbc')
		{
			PreparseCode::instance()->preparsecode($article_info['body'], false);
		}

		// Bind attachments to the article if existing, create any needed thumbnails,
		// move them to the sp attachment directory
		$attachIDs = $this->finalizeArticleAttachments($article_info);

		// Save the article
		$this->_is_aid = sp_save_article($article_info, empty($this->_is_aid));

		// If this was a new article, with attachments, bind them to one another.
		if (empty($validator->article_id) && !empty($attachIDs))
		{
			bindArticleAttachments($this->_is_aid, $attachIDs);
		}

		// And return to the listing
		redirectexit('action=admin;area=portalarticles');
	}

	/**
	 * Save attachments based on the form inputs
	 *
	 * - Remove existing ones that have been "unchecked" in the form
	 * - Performs security, size, type, etc checks
	 * - Moves files to the current attachment directory, we will move it again to sp attachment in
	 * the following steps.
	 */
	private function processArticleAttachments()
	{
		global $user_info, $context, $modSettings;

		// First see if they are trying to delete current attachments.
		if (isset($_POST['attach_del']))
		{
			$keep_temp = array();
			$keep_ids = array();
			foreach ($_POST['attach_del'] as $idRemove)
			{
				$attachID = getAttachmentIdFromPublic($idRemove);

				if (strpos($attachID, 'post_tmp_' . $user_info['id']) !== false)
				{
					$keep_temp[] = $attachID;
				}
				else
				{
					$keep_ids[] = (int) $attachID;
				}
			}

			if (isset($_SESSION['temp_attachments']))
			{
				foreach ($_SESSION['temp_attachments'] as $attachID => $attachment)
				{
					if ((isset($_SESSION['temp_attachments']['post']['files'], $attachment['name'])
							&& in_array($attachment['name'], $_SESSION['temp_attachments']['post']['files']))
						|| in_array($attachID, $keep_temp)
						|| strpos($attachID, 'post_tmp_' . $user_info['id']) === false
					)
					{
						continue;
					}

					unset($_SESSION['temp_attachments'][$attachID]);
					@unlink($attachment['tmp_name']);
				}
			}

			if (!empty($this->_is_aid))
			{
				$attachmentQuery = array(
					'id_article' => $this->_is_aid,
					'not_id_attach' => $keep_ids,
					'id_folder' => $modSettings['sp_articles_attachment_dir'],
				);
				removeArticleAttachments($attachmentQuery);
			}
		}

		// Upload any new attachments.
		$context['attachments']['can']['post'] = allowedTo('post_attachment')
			|| ($modSettings['postmod_active'] && allowedTo('post_unapproved_attachments'));

		if ($context['attachments']['can']['post'])
		{
			list($context['attachments']['quantity'], $context['attachments']['total_size']) = attachmentsSizeForArticle($this->_is_aid);

			// A little razzle-dazzle to point the system to the article attachment directory
			$attachmentUploadDirSave = $modSettings['attachmentUploadDir'];
			$modSettings['automanage_attachments'] = 0;
			$modSettings['currentAttachmentUploadDir'] = 1;
			$modSettings['attachmentUploadDir'] = [1 => $modSettings['sp_articles_attachment_dir']];
			processAttachments();
			$modSettings['attachmentUploadDir'] = $attachmentUploadDirSave;
		}
	}

	/**
	 * Handle the final processing of attachments, including any thumbnail generation
	 * and linking attachments to the specific article.  Saves the articles in the SP
	 * attachment directory.
	 */
	private function finalizeArticleAttachments(&$article_info)
	{
		global $context, $user_info, $modSettings, $ignore_temp;

		$attachIDs = array();
		if (empty($ignore_temp) && $context['attachments']['can']['post'] && !empty($_SESSION['temp_attachments']))
		{
			foreach ($_SESSION['temp_attachments'] as $attachID => $attachment)
			{
				if ($attachID !== 'initial_error' && strpos($attachID, 'post_tmp_' . $user_info['id']) === false)
				{
					continue;
				}

				// If there was an initial error just show that message.
				if ($attachID === 'initial_error')
				{
					unset($_SESSION['temp_attachments']);
					break;
				}

				// No errors, then try to create the attachment
				if (empty($attachment['errors']))
				{
					// Load the attachmentOptions array with the data needed to create an attachment
					$attachmentOptions = array(
						'article' => !empty($this->_is_aid) ? $this->_is_aid : 0,
						'poster' => $user_info['id'],
						'name' => $attachment['name'],
						'tmp_name' => $attachment['tmp_name'],
						'size' => $attachment['size'] ?? 0,
						'mime_type' => $attachment['type'] ?? '',
						'id_folder' => $modSettings['sp_articles_attachment_dir'],
						'approved' => true,
						'errors' => array(),
					);

					if (createArticleAttachment($attachmentOptions))
					{
						$attachIDs[] = $attachmentOptions['id'];
						if (!empty($attachmentOptions['thumb']))
						{
							$attachIDs[] = $attachmentOptions['thumb'];
						}

						// Replace ila attach tags with the new valid attachment id and [spattach] tag
						$article_info['body'] = preg_replace('~\[attach(.*?)\]' . $attachment['public_attachid'] . '\[\/attach\]~', '[spattach$1]' . $attachmentOptions['id'] . '[/spattach]', $article_info['body']);
					}
				}
				// We have errors on this file, build out the issues for display to the user
				else
				{
					@unlink($attachment['tmp_name']);
				}
			}

			unset($_SESSION['temp_attachments']);
		}

		return $attachIDs;
	}

	/**
	 * Setup the add/edit article template values
	 */
	private function prepareArticleForm()
	{
		global $txt, $context;

		$context['attachments']['current'] = array();

		// Just taking a look before you save, or tried to save with errors?
		if (!empty($_POST['preview']) || $this->article_errors->hasErrors() || $this->attach_errors->hasErrors())
		{
			$context['article'] = $this->_sportal_admin_article_preview();

			// If there are attachment errors. Let's show a list to the user.
			if ($this->attach_errors->hasErrors())
			{
				loadTemplate('Errors');
				$errors = $this->attach_errors->prepareErrors();
				foreach ($errors as $key => $error)
				{
					$context['attachment_error_keys'][] = $key . '_error';
					$context[$key . '_error'] = $error;
				}
			}

			// Showing errors or a preview?
			if ($this->article_errors->hasErrors())
			{
				$context['article_errors'] = array(
					'errors' => $this->article_errors->prepareErrors(),
					'type' => $this->article_errors->getErrorType() == 0 ? 'minor' : 'serious',
					'title' => $txt['sp_form_errors_detected'],
				);
			}

			// Preview needs a flag
			if (!empty($_POST['preview']))
			{
				// We reuse this template for the preview
				loadTemplate('PortalArticles');
				$context['preview'] = true;

				// The editor will steal focus so we have to delay
				addInlineJavascript('setTimeout(() => $("html, body").animate({scrollTop: $("#preview_section").offset().top}, 250), 750);', true);
			}
		}
		// Something new?
		elseif (!$this->_is_aid)
		{
			$context['article'] = array(
				'id' => 0,
				'article_id' => 'article' . random_int(1, 5000),
				'category' => array('id' => 0),
				'title' => $txt['sp_articles_default_title'],
				'body' => '',
				'type' => 'bbc',
				'permissions' => 3,
				'styles' => 4,
				'status' => 1,
			);
		}
		// Something used
		else
		{
			$_REQUEST['article_id'] = $this->_is_aid;
			$context['article'] = sportal_get_articles($this->_is_aid);
			$attach = sportal_get_articles_attachments($this->_is_aid, true);
			$context['attachments']['current'] = !empty($attach[$this->_is_aid]) ? $attach[$this->_is_aid] : array();
		}
	}

	/**
	 * Sets up for an article preview
	 */
	private function _sportal_admin_article_preview()
	{
		global $scripturl, $user_info;

		// Existing article will have some data
		if ($this->_is_aid)
		{
			$_POST['article_id'] = $this->_is_aid;
			$current = sportal_get_articles($this->_is_aid);
			$author = $current['author'];
			$date = standardTime($current['date']);
			list($views, $comments) = sportal_get_article_views_comments($this->_is_aid);
		}
		// New ones we set defaults
		else
		{
			$author = array('link' => '<a href="' . $scripturl . '?action=profile;u=' . $user_info['id'] . '">' . $user_info['name'] . '</a>');
			$date = standardTime(time());
			$views = 0;
			$comments = 0;
		}

		$article = array(
			'id' => $_POST['article_id'],
			'article_id' => $_POST['namespace'],
			'category' => sportal_get_categories((int) $_POST['category_id']),
			'author' => $author,
			'title' => Util::htmlspecialchars($_POST['title'], ENT_QUOTES),
			'body' => Util::htmlspecialchars($_POST['content'], ENT_QUOTES),
			'type' => $_POST['type'],
			'permissions' => $_POST['permissions'],
			'styles' => $_POST['styles'],
			'date' => $date,
			'status' => !empty($_POST['status']),
			'view_count' => $views,
			'comment_count' => $comments,
		);

		if ($article['type'] === 'bbc')
		{
			PreparseCode::instance()->preparsecode($article['body'], false);
		}

		return $article;
	}

	/**
	 * Sets up editor options as needed for SP, temporarily ignores any user options
	 * so we can enable it in the proper mode bbc/php/html
	 */
	private function prepareEditor()
	{
		global $context, $options;

		if ($context['article']['type'] !== 'bbc')
		{
			// Override user preferences for wizzy mode if they don't need it
			$temp_editor = !empty($options['wysiwyg_default']);
			$options['wysiwyg_default'] = false;
		}

		// Fire up the editor with the values
		$editorOptions = array(
			'id' => 'content',
			'value' => $context['article']['body'],
			'width' => '100%',
			'height' => '275px',
			'preview_type' => 1,
		);
		$editorOptions['plugin_addons'] = array();
		$editorOptions['plugin_addons'][] = 'spplugin';
		create_control_richedit($editorOptions);
		$context['post_box_name'] = $editorOptions['id'];
		$context['post_box_class'] = $context['article']['type'] !== 'bbc' ? 'sceditor-container' : 'sp-sceditor-container';
		$context['attached'] = '';

		// Bit of a cheat, so look away, but we need to use our DD handler
		$context['js_files']['dropAttachments.js']['filename'] = str_replace('dropAttachments.js', 'spDropAttachments.js', $context['js_files']['dropAttachments.js']['filename']);
		$context['js_files']['dropAttachments.js']['options']['basename'] = 'spDropAttachments';

		// Restore their settings
		if (isset($temp_editor))
		{
			$options['wysiwyg_default'] = $temp_editor;
		}
	}

	/**
	 * Loads in permission, visibility and style profiles for use in the template
	 * If unable to load profiles, simply dies as "somethings broke"tm
	 */
	private function loadProfileContext()
	{
		global $context;

		$context['article']['permission_profiles'] = sportal_get_profiles(null, 1, 'name');
		if (empty($context['article']['permission_profiles']))
		{
			throw new Elk_Exception('error_sp_no_permission_profiles', false);
		}

		$context['article']['style_profiles'] = sportal_get_profiles(null, 2, 'name');
		if (empty($context['article']['style_profiles']))
		{
			throw new Elk_Exception('error_sp_no_style_profiles', false);
		}

		$context['article']['categories'] = sportal_get_categories();
		if (empty($context['article']['categories']))
		{
			throw new Elk_Exception('error_sp_no_category', false);
		}
	}

	/**
	 * Handles the checking of uploaded attachments and loads valid ones
	 * into context
	 */
	private function article_attachment()
	{
		global $context, $txt;

		// If there are attachments, calculate the total size and how many.
		$this->_attachments = array();
		$this->_attachments['total_size'] = 0;
		$this->_attachments['quantity'] = 0;

		// If this isn't a new article, account for any current attachments.
		if ($this->_is_aid && !empty($context['attachments']))
		{
			$this->_attachments['quantity'] = count($context['attachments']['current']);
			foreach ($context['attachments']['current'] as $attachment)
			{
				$this->_attachments['total_size'] += $attachment['size'];
			}
		}

		// Any failed/aborted attachments left in session that we should clear
		if (!empty($_SESSION['temp_attachments']) && empty($_POST['preview']) && empty($_POST['submit']) && !$this->_is_aid)
		{
			foreach ($_SESSION['temp_attachments'] as $attachID => $attachment)
			{
				unset($_SESSION['temp_attachments'][$attachID]);
				@unlink($attachment['tmp_name']);
			}
		}
		// New attachments to add
		elseif (!empty($_SESSION['temp_attachments']))
		{
			foreach ($_SESSION['temp_attachments'] as $attachID => $attachment)
			{
				// PHP upload error means we drop the file
				if ($attachID === 'initial_error')
				{
					$txt['error_attach_initial_error'] = $txt['attach_no_upload'] . '<div class="attachmenterrors">' . (is_array($attachment) ? vsprintf($txt[$attachment[0]], $attachment[1]) : $txt[$attachment]) . '</div>';
					$this->attach_errors->addError('attach_initial_error');
					unset($_SESSION['temp_attachments']);
					break;
				}

				// Show any errors which might have occurred.
				if (!empty($attachment['errors']))
				{
					$txt['error_attach_errors'] = empty($txt['error_attach_errors']) ? '<br />' : '';
					$txt['error_attach_errors'] .= vsprintf($txt['attach_warning'], $attachment['name']) . '<div class="attachmenterrors">';
					foreach ($attachment['errors'] as $error)
					{
						$txt['error_attach_errors'] .= (is_array($error) ? vsprintf($txt[$error[0]], $error[1]) : $txt[$error]) . '<br  />';
					}
					$txt['error_attach_errors'] .= '</div>';

					$this->attach_errors->addError('attach_errors');

					// Take out the trash.
					unset($_SESSION['temp_attachments'][$attachID]);
					@unlink($attachment['tmp_name']);

					continue;
				}

				// In session but the file is missing, then some house cleaning
				if (isset($attachment['tmp_name']) && !file_exists($attachment['tmp_name']))
				{
					unset($_SESSION['temp_attachments'][$attachID]);
					continue;
				}

				$this->_attachments['name'] = !empty($this->_attachments['name']) ? $this->_attachments['name'] : '';
				$this->_attachments['size'] = !empty($this->_attachments['size']) ? $this->_attachments['size'] : 0;
				$this->_attachments['quantity']++;
				$this->_attachments['total_size'] += $this->_attachments['size'];

				$context['attachments']['current'][] = array(
					'name' => '<span class="underline">' . htmlspecialchars($this->_attachments['name'], ENT_COMPAT) . '</span>',
					'size' => $this->_attachments['size'],
					'id' => $attachID,
					'unchecked' => false,
					'approved' => 1,
				);
			}
		}
	}

	/**
	 * Prepares template values for the attachment area such as space left,
	 * types allowed, etc.
	 *
	 * Prepares the D&D JS initialization values
	 */
	private function article_attachment_dd()
	{
		global $context, $modSettings, $txt;

		// If they've unchecked an attachment, they may still want to attach that many more files, but don't allow more than num_allowed_attachments.
		$context['attachments']['num_allowed'] = empty($modSettings['attachmentNumPerPostLimit']) ? 50 : $modSettings['attachmentNumPerPostLimit'];
		$context['attachments']['can']['post_unapproved'] = allowedTo('post_attachment');
		$context['attachments']['total_size'] = $this->_attachments['total_size'];
		$context['attachments']['quantity'] = $this->_attachments['quantity'];
		$context['attachments']['restrictions'] = array();
		$context['attachments']['ila_enabled'] = true;

		if (!empty($modSettings['attachmentCheckExtensions']))
		{
			$context['attachments']['allowed_extensions'] = strtr(strtolower($modSettings['attachmentExtensions']), array(',' => ', '));
		}
		else
		{
			$context['attachments']['allowed_extensions'] = '';
		}

		$context['attachments']['templates'] = array(
			'existing' => 'template_article_existing_attachments',
			'add_new' => 'template_article_new_attachments',
		);

		$attachmentRestrictionTypes = array('attachmentNumPerPostLimit', 'attachmentPostLimit', 'attachmentSizeLimit');
		foreach ($attachmentRestrictionTypes as $type)
		{
			if (!empty($modSettings[$type]))
			{
				$context['attachments']['restrictions'][] = $type === 'attachmentNumPerPostLimit'
					? sprintf($txt['attach_restrict_' . $type], comma_format($modSettings[$type], 0))
					: sprintf($txt['attach_restrict_' . $type], byte_format($modSettings[$type] * 1024));

				// Show some numbers. If they exist.
				if ($type === 'attachmentNumPerPostLimit' && $this->_attachments['quantity'] > 0)
				{
					$context['attachments']['restrictions'][] = sprintf($txt['attach_remaining'], '<span id="' . $type . '">' . ($modSettings['attachmentNumPerPostLimit'] - $this->_attachments['quantity']) . '</span>');
				}
				elseif ($type === 'attachmentPostLimit' && $this->_attachments['total_size'] > 0)
				{
					$context['attachments']['restrictions'][] = sprintf($txt['attach_available'], '<span id="' . $type . '">' . byte_format(max(($modSettings['attachmentPostLimit'] * 1024) - $this->_attachments['total_size'], 0)) . '</span>');
				}
			}
		}

		// The portal articles always allow "ila" style attachments, so show that insert interface
		addInlineJavascript('
		let IlaDropEvents = {
			UploadSuccess: function($button, data) {
				let inlineAttach = ElkInlineAttachments(\'#postAttachment2,#postAttachment\', \'' . $context['post_box_name'] . '\', {
					trigger: $(\'<div class="share icon i-share" />\'),
					template: ' . JavaScriptEscape('<div class="insertoverlay">
						<input type="button" class="button" value="' . $txt['insert'] . '">
						<ul data-group="tabs" class="tabs">
							<li data-tab="size">' . $txt['ila_opt_size'] . '</li><li data-tab="align">' . $txt['ila_opt_align'] . '</li>
						</ul>
						<div class="container" data-visual="size">
							<label><input data-size="thumb" type="radio" name="imgmode">' . $txt['ila_opt_size_thumb'] . '</label>
							<label><input data-size="full" type="radio" name="imgmode">' . $txt['ila_opt_size_full'] . '</label>
							<label><input data-size="cust" type="radio" name="imgmode">' . $txt['ila_opt_size_cust'] . '</label>
							<div class="customsize">
								<input type="range" class="range" min="100" max="500"><input type="text" class="visualizesize" disabled="disabled">
							</div>
						</div>
						<div class="container" data-visual="align">
							<label><input data-align="none" type="radio" name="align">' . $txt['ila_opt_align_none'] . '</label>
							<label><input data-align="left" type="radio" name="align">' . $txt['ila_opt_align_left'] . '</label>
							<label><input data-align="center" type="radio" name="align">' . $txt['ila_opt_align_center'] . '</label>
							<label><input data-align="right" type="radio" name="align">' . $txt['ila_opt_align_right'] . '</label>
						</div>
					</div>') . '
				});
				inlineAttach.addInterface($button, data.attachid);
			},
			RemoveSuccess: function(attachid) {
				var inlineAttach = ElkInlineAttachments(\'#postAttachment2,#postAttachment\', \'' . $context['post_box_name'] . '\', {
					trigger: $(\'<div class="share icon i-share" />\')
				});
				inlineAttach.removeAttach(attachid);
			}
		};', true);

		// Load up the drag and drop attachment magic
		addInlineJavascript('
		var dropAttach = dragDropAttachment({
			board: 0,
			allowedExtensions: ' . JavaScriptEscape($context['attachments']['allowed_extensions']) . ',
			totalSizeAllowed: ' . (empty($modSettings['attachmentPostLimit']) ? 0 : $modSettings['attachmentPostLimit'] * 1024) . ',
			totalAttachSizeUploaded: ' . $context['attachments']['total_size'] . ',
			individualSizeAllowed: ' . (empty($modSettings['attachmentSizeLimit']) ? 0 : $modSettings['attachmentSizeLimit'] * 1024) . ',
			numOfAttachmentAllowed: ' . $context['attachments']['num_allowed'] . ',
			numAttachUploaded: ' . $context['attachments']['quantity'] . ',
			resizeImageEnabled: ' . (empty($modSettings['attachment_image_resize_enabled']) ? 0 : 1) . ',
			fileDisplayTemplate: \'<div class="statusbar"><div class="info"></div><div class="progressBar"><div></div></div><div class="control icon i-close"></div></div>\',
			oTxt: ({
				allowedExtensions : ' . JavaScriptEscape(sprintf($txt['cant_upload_type'], $context['attachments']['allowed_extensions'])) . ',
				totalSizeAllowed : ' . JavaScriptEscape($txt['attach_max_total_file_size']) . ',
				individualSizeAllowed : ' . JavaScriptEscape(sprintf($txt['file_too_big'], comma_format($modSettings['attachmentSizeLimit'], 0))) . ',
				numOfAttachmentAllowed : ' . JavaScriptEscape(sprintf($txt['attachments_limit_per_post'], $modSettings['attachmentNumPerPostLimit'])) . ',
				postUploadError : ' . JavaScriptEscape($txt['post_upload_error']) . ',
				areYouSure: ' . JavaScriptEscape($txt['ila_confirm_removal']) . ',
				uploadAbort: ' . JavaScriptEscape($txt['attachment_upload_abort']) . ',
				processing: ' . JavaScriptEscape($txt['attachment_processing']) . '
			}),
			existingSelector: ".inline_insert",
			events: IlaDropEvents' . (!empty($this->_is_aid) ? ',
			topic: ' . $this->_is_aid : '') . '
		});', true);
	}

	/**
	 * Toggle an articles active status on/off
	 */
	public function action_status()
	{
		global $context;

		checkSession(isset($_REQUEST['xml']) ? '' : 'get');

		$article_id = !empty($_REQUEST['article_id']) ? (int) $_REQUEST['article_id'] : 0;
		$state = sp_changeState('article', $article_id);

		// Doing this the ajax way?
		if (isset($_REQUEST['xml']))
		{
			$context['item_id'] = $article_id;
			$context['status'] = !empty($state) ? 'active' : 'deactive';

			// Clear out any template layers, add the xml response
			loadTemplate('PortalAdmin');
			$template_layers = Template_Layers::instance();
			$template_layers->removeAll();
			$context['sub_template'] = 'change_status';

			obExit();
		}

		redirectexit('action=admin;area=portalarticles');
	}

	/**
	 * Remove an article from the system
	 *
	 * - Removes the article
	 * - Removes attachments associated with an article
	 * - Updates category totals to reflect removed items
	 */
	public function action_delete()
	{
		$article_ids = array();

		// Get the article id's to remove
		if (!empty($_POST['remove_articles']) && !empty($_POST['remove']) && is_array($_POST['remove']))
		{
			checkSession();

			foreach ($_POST['remove'] as $index => $article_id)
			{
				$article_ids[(int) $index] = (int) $article_id;
			}
		}
		elseif (!empty($_REQUEST['article_id']))
		{
			checkSession('get');
			$article_ids[] = (int) $_REQUEST['article_id'];
		}

		// If we have some to remove ....
		if (!empty($article_ids))
		{
			// Update the category counts as we are about to remove some articles
			foreach ($article_ids as $article_id)
			{
				$article_info = sportal_get_articles($article_id);
				sp_category_update_total($article_info['category']['id']);
			}

			sp_delete_articles($article_ids);
		}

		redirectexit('action=admin;area=portalarticles');
	}
}
