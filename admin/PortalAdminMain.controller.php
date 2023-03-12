<?php

/**
 * @package SimplePortal ElkArte
 *
 * @author SimplePortal Team
 * @copyright 2015-2023 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.1
 */

use BBC\ParserWrapper;
use BBC\PreparseCode;

/**
 * SimplePortal Configuration controller class.
 * This class handles the general, blocks and articles configuration screens
 */
class ManagePortalConfig_Controller extends Action_Controller
{
	/** @var Settings_Form General settings form */
	protected $_generalSettingsForm;

	/** @var Settings_Form Block settings form */
	protected $_blockSettingsForm;

	/** @var Settings_Form Article settings form */
	protected $_articleSettingsForm;

	/**
	 * Main dispatcher.
	 *
	 * This function checks permissions and passes control through.
	 * If the passed section is not found it shows the information page.
	 */
	public function action_index()
	{
		global $context, $txt;

		// You need to be an admin or have manage setting permissions to change anything
		if (!allowedTo('sp_admin'))
		{
			isAllowedTo('sp_manage_settings');
		}

		// Some helpful friends
		require_once(SUBSDIR . '/PortalAdmin.subs.php');
		require_once(SUBSDIR . '/Portal.subs.php');
		require_once(ADMINDIR . '/ManageServer.controller.php');
		loadCSSFile('portal.css', ['stale' => SPORTAL_STALE]);

		// Load the Simple Portal Help file.
		loadLanguage('SPortalHelp');

		$subActions = array(
			'information' => array($this, 'action_information'),
			'generalsettings' => array($this, 'action_general_settings'),
			'blocksettings' => array($this, 'action_block_settings'),
			'articlesettings' => array($this, 'action_article_settings'),
			'formatchange' => array($this, 'action_format_change'),
		);

		// Start up the controller, provide a hook since we can
		$action = new Action('portal_main');

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['sp-adminConfiguration'],
			'help' => 'sp_ConfigurationArea',
			'description' => $txt['sp-adminConfigurationDesc'],
		);

		// Set the default to the information tab
		$subAction = $action->initialize($subActions, 'information');

		// Right then, off you go
		$action->dispatch($subAction);
	}

	/**
	 * General settings that control global portal actions
	 */
	public function action_general_settings()
	{
		global $context, $scripturl, $txt;

		$context['SPortal']['themes'] = sp_general_load_themes();

		// These are very likely to come in handy! (i.e. without them we're doomed!)
		require_once(ADMINDIR . '/ManagePermissions.controller.php');
		require_once(ADMINDIR . '/ManageServer.controller.php');
		require_once(SUBSDIR . '/SettingsForm.class.php');

		// Initialize the form
		$this->_initGeneralSettingsForm();

		if (isset($_GET['save']))
		{
			checkSession();

			if (!empty($_POST['sp_portal_mode']))
			{
				updateSettings(array('front_page' => 'PortalMain_Controller'));
			}
			else
			{
				updateSettings(array('front_page' => 'MessageIndex_Controller'));
			}

			$this->_generalSettingsForm->setConfigValues($_POST);
			$this->_generalSettingsForm->save();
			redirectexit('action=admin;area=portalconfig;sa=generalsettings');
		}

		$context['post_url'] = $scripturl . '?action=admin;area=portalconfig;sa=generalsettings;save';
		$context['settings_title'] = $txt['sp-adminGeneralSettingsName'];
		$context['page_title'] = $txt['sp-adminGeneralSettingsName'];
		$context['sub_template'] = 'show_settings';

		$this->_generalSettingsForm->prepare();
	}

	/**
	 * Initialize General Settings Form.
	 * Retrieve and return the general portal settings.
	 */
	private function _initGeneralSettingsForm()
	{
		global $txt, $context;

		// Instantiate the form
		$this->_generalSettingsForm = new Settings_Form(Settings_Form::DB_ADAPTER);

		$config_vars = array(
			array('select', 'sp_portal_mode', explode('|', $txt['sp_portal_mode_options'])),
			array('check', 'sp_maintenance'),
			array('text', 'sp_standalone_url'),
			'',
			array('select', 'portaltheme', $context['SPortal']['themes']),
			array('check', 'sp_disableColor'),
			array('check', 'sp_disableForumRedirect'),
			array('check', 'sp_disable_random_bullets'),
			array('check', 'sp_disable_php_validation', 'subtext' => $txt['sp_disable_php_validation_desc']),
			array('check', 'sp_disable_side_collapse'),
			array('check', 'sp_resize_images'),
			array('check', 'sp_disableMobile'),
		);

		$this->_generalSettingsForm->setConfigVars($config_vars);
	}

	/**
	 * Settings that control how blocks behave
	 */
	public function action_block_settings()
	{
		global $context, $scripturl, $txt;

		// These are very likely to come in handy! (i.e. without them we're doomed!)
		require_once(ADMINDIR . '/ManagePermissions.controller.php');
		require_once(ADMINDIR . '/ManageServer.controller.php');
		require_once(SUBSDIR . '/SettingsForm.class.php');

		// Initialize the form
		$this->_initBlockSettingsForm();
		$config_vars = $this->_blockSettingsForm->getConfigVars();

		if (isset($_GET['save']))
		{
			checkSession();

			$width_checkup = array('left', 'right');
			foreach ($width_checkup as $pos)
			{
				if (!empty($_POST[$pos . 'width']))
				{
					$suffix = 'px';
					if (strpos($_POST[$pos . 'width'], '%') !== false)
					{
						$suffix = '%';
					}

					preg_match_all('/(?:(\d+)|.)/', $_POST[$pos . 'width'], $matches);

					$number = (int) implode('', $matches[1]);
					if (!empty($number) && $number > 0)
					{
						$_POST[$pos . 'width'] = $number . $suffix;
					}
					else
					{
						$_POST[$pos . 'width'] = '';
					}
				}
				else
				{
					$_POST[$pos . 'width'] = '';
				}
			}

			unset($config_vars[7]);
			$config_vars = array_merge(
				$config_vars, array(
					array('check', 'sp_adminIntegrationHide'),
					array('check', 'sp_profileIntegrationHide'),
					array('check', 'sp_pmIntegrationHide'),
					array('check', 'sp_mlistIntegrationHide'),
					array('check', 'sp_searchIntegrationHide'),
					array('check', 'sp_calendarIntegrationHide'),
					array('check', 'sp_moderateIntegrationHide'),
				)
			);

			$this->_blockSettingsForm->setConfigVars($config_vars);
			$this->_blockSettingsForm->setConfigValues($_POST);
			$this->_blockSettingsForm->save();
			redirectexit('action=admin;area=portalconfig;sa=blocksettings');
		}

		$context['post_url'] = $scripturl . '?action=admin;area=portalconfig;sa=blocksettings;save';
		$context['settings_title'] = $txt['sp-adminBlockSettingsName'];
		$context['page_title'] = $txt['sp-adminBlockSettingsName'];
		$context['sub_template'] = 'show_settings';

		$this->_blockSettingsForm->prepare();
	}

	/**
	 * Initialize Block Settings Form.
	 * Retrieve and return the general portal settings.
	 */
	private function _initBlockSettingsForm()
	{
		global $txt;

		// instantiate the block form
		$this->_blockSettingsForm = new Settings_Form(Settings_Form::DB_ADAPTER);

		$config_vars = array(
			array('check', 'showleft'),
			array('check', 'showright'),
			array('text', 'leftwidth'),
			array('text', 'rightwidth'),
			'',
			array('multicheck',
				  'sp_IntegrationHide',
				  'subsettings' => array('sp_adminIntegrationHide' => $txt['admin'], 'sp_profileIntegrationHide' => $txt['profile'], 'sp_pmIntegrationHide' => $txt['personal_messages'], 'sp_mlistIntegrationHide' => $txt['members_title'], 'sp_searchIntegrationHide' => $txt['search'], 'sp_calendarIntegrationHide' => $txt['calendar'], 'sp_moderateIntegrationHide' => $txt['moderate']),
				  'subtext' => $txt['sp_IntegrationHide_desc']
			),
		);

		$this->_blockSettingsForm->setConfigVars($config_vars);
	}

	/**
	 * Settings to control articles
	 */
	public function action_article_settings()
	{
		global $context, $scripturl, $txt;

		// These are very likely to come in handy! (i.e. without them we're doomed!)
		require_once(ADMINDIR . '/ManagePermissions.controller.php');
		require_once(ADMINDIR . '/ManageServer.controller.php');
		require_once(SUBSDIR . '/SettingsForm.class.php');

		// Initialize the form
		$this->_initArticleSettingsForm();

		// Save away
		if (isset($_GET['save']))
		{
			checkSession();

			$this->_articleSettingsForm->setConfigValues($_POST);
			$this->_articleSettingsForm->save();
			redirectexit('action=admin;area=portalconfig;sa=articlesettings');
		}

		// Show the form
		$context['post_url'] = $scripturl . '?action=admin;area=portalconfig;sa=articlesettings;save';
		$context['settings_title'] = $txt['sp-adminArticleSettingsName'];
		$context['page_title'] = $txt['sp-adminArticleSettingsName'];
		$context['sub_template'] = 'show_settings';

		$this->_articleSettingsForm->prepare();
	}

	/**
	 * Initialize Article Settings Form.
	 * Retrieve and return the general portal settings.
	 */
	private function _initArticleSettingsForm()
	{
		// instantiate the article form
		$this->_articleSettingsForm = new Settings_Form(Settings_Form::DB_ADAPTER);

		$config_vars = array(
			array('check', 'sp_articles_index'),
			array('int', 'sp_articles_index_per_page'),
			array('int', 'sp_articles_index_total'),
			array('int', 'sp_articles_length'),
			'',
			array('int', 'sp_articles_per_page'),
			array('int', 'sp_articles_comments_per_page'),
			'',
			array('text', 'sp_articles_attachment_dir')
		);

		$this->_articleSettingsForm->setConfigVars($config_vars);
	}

	/**
	 * Our about page etc.
	 *
	 * @param boolean $in_admin
	 * @throws Elk_Exception
	 */
	public function action_information($in_admin = true)
	{
		global $context, $scripturl, $txt, $user_profile;

		loadTemplate('PortalAdmin');
		loadJavascriptFile(array('portal.js', 'admin.js'), array('defer' => true));

		$context['sp_credits'] = array(
			array(
				'pretext' => $txt['sp-info_intro'],
				'title' => $txt['sp-info_team'],
				'groups' => array(
					array(
						'title' => $txt['sp-info_groups_pm'],
						'members' => array(
							'Eliana Tamerin',
							'Huw',
						),
					),
					array(
						'title' => $txt['sp-info_groups_dev'],
						'members' => array(
							'<span onclick="if (getInnerHTML(this).indexOf(\'Sinan\') === -1) setInnerHTML(this, \'Sinan &quot;[SiNaN]&quot; &Ccedil;evik\'); return false;">Selman &quot;[SiNaN]&quot; Eser</span>',
							'Nathaniel Baxter',
							'&#12487;&#12451;&#12531;1031',
							'spuds',
							'emanuele',
						),
					),
					array(
						'title' => $txt['sp-info_groups_support'],
						'members' => array(
							'<span onclick="if (getInnerHTML(this).indexOf(\'Queen\') === -1) setInnerHTML(this, \'Angelina &quot;Queen of Support&quot; Belle\'); return false;">AngelinaBelle</span>',
							'Chen Zhen',
							'andy',
							'Ninja ZX-10RR',
							'phantomm',
						),
					),
					array(
						'title' => $txt['sp-info_groups_customize'],
						'members' => array(
							'Robbo',
							'Berat &quot;grafitus&quot; Do&#287;an',
							'Blue',
						),
					),
					array(
						'title' => $txt['sp-info_groups_language'],
						'members' => array(
							'Kryzen',
							'Jade &quot;Alundra&quot; Elizabeth',
							'<span onclick="if (getInnerHTML(this).indexOf(\'King\') === -1) setInnerHTML(this, \'130 &quot;King of Pirates&quot; 860\'); return false;">130860</span>',
						),
					),
					array(
						'title' => $txt['sp-info_groups_marketing'],
						'members' => array(
							'BryanD',
						),
					),
					array(
						'title' => $txt['sp-info_groups_beta'],
						'members' => array(
							'BurkeKnight',
							'ARG',
							'Old Fossil',
							'David',
							'sharks',
							'Willerby',
							'&#214;zg&#252;r',
							'c23_Mike',
						),
					),
				),
			),
			array(
				'title' => $txt['sp-info_special'],
				'posttext' => $txt['sp-info_anyone'],
				'groups' => array(
					array(
						'title' => $txt['sp-info_groups_translators'],
						'members' => array(
							$txt['sp-info_translators_message'],
						),
					),
					array(
						'title' => $txt['sp-info_groups_founder'],
						'members' => array(),
					),
					array(
						'title' => $txt['sp-info_groups_original_pm'],
						'members' => array(),
					),
					array(
						'title' => $txt['sp-info_fam_fam'],
						'members' => array(
							$txt['sp-info_fam_fam_message'],
						),
					),
				),
			),
		);

		if (!$in_admin)
		{
			$context['robot_no_index'] = true;
			$context['in_admin'] = false;
		}
		else
		{
			$context['in_admin'] = true;
			$context['sp_version'] = SPORTAL_VERSION;
			$context['sp_managers'] = array();

			require_once(SUBSDIR . '/Members.subs.php');
			$manager_ids = loadMemberData(membersAllowedTo('sp_admin'), false, 'minimal');

			if ($manager_ids)
			{
				foreach ($manager_ids as $member)
				{
					$context['sp_managers'][] = '<a href="' . $scripturl . '?action=profile;u=' . $user_profile[$member]['id_member'] . '">' . $user_profile[$member]['real_name'] . '</a>';
				}
			}
		}

		$context['sub_template'] = 'information';
		$context['page_title'] = $txt['sp-info_title'];
	}

	/**
	 * This is an ajax return function for articles and pages and anything else that
	 * wants to use it, for the purpose of text format conversion
	 *
	 * Intended to "munge" source formats from a <> b Used when changing the type from
	 * bbc to html or markdown to bbc or php to bbc ..... you get it.
	 */
	public function action_format_change()
	{
		global $context;

		// Pretty basic
		checkSession('request');

		// Responding to an ajax request, that is all we do
		$req = request();
		if ($req->is_xml())
		{
			loadTemplate('PortalAdmin');

			$format_parameters = array(
				'text' => urldecode($_REQUEST['text']),
				'from' => $_REQUEST['from'],
				'to' => $_REQUEST['to'],
			);

			// Do whatever format juggle is requested
			$func = 'formatTo' . strtoupper($format_parameters['to']);
			if (method_exists($this, $func))
			{
				$this->$func($format_parameters);
			}

			// Return an xml response
			Template_Layers::instance()->removeAll();
			$context['sub_template'] = 'format_xml';
			$context['SPortal']['text'] = $format_parameters['text'];
		}
	}

	/**
	 * Convert various formats to BBC
	 *
	 * Convert MD->BBC, PHP->BBC, and HTML->BBC
	 *
	 * @param $format_parameters
	 */
	private function formatToBBC(&$format_parameters)
	{
		// From MD to BBC, the round about way
		if ($format_parameters['from'] === 'markdown')
		{
			// MD to HTML
			require_once(EXTDIR . '/markdown/markdown.php');
			$format_parameters['text'] = Markdown($format_parameters['text']);

			// HTML to BBC
			$parser = new Html_2_BBC($format_parameters['text']);
			$format_parameters['text'] = $parser->get_bbc();
			$format_parameters['text'] = str_replace('[br]', "\n\n", $format_parameters['text']);
			$format_parameters['text'] = un_htmlspecialchars($format_parameters['text']);
			return;
		}

		// From php to BBC ?
		if ($format_parameters['from'] === 'php')
		{
			$format_parameters['text'] = htmlspecialchars($format_parameters['text']);
			$format_parameters['text'] = '[code=php]' . $format_parameters['text'] . '[/code]';
			return;
		}

		// HTML to BBC
		if ($format_parameters['from'] === 'html')
		{
			// The converter does not treat <Hx> tags as block level :(
			$format_parameters['text'] = preg_replace('~(<h\d>.*?<\/h\d>)~', '<br>$1<br>', $format_parameters['text']);

			$bbc_converter = new Html_2_BBC($format_parameters['text']);
			$bbc_converter->skip_tags(array('font', 'span'));
			$bbc_converter->skip_styles(array('font-family'));
			$format_parameters['text'] = $bbc_converter->get_bbc();
			$format_parameters['text'] = un_htmlspecialchars($format_parameters['text']);
			$format_parameters['text'] = str_replace('[br]', "\n", $format_parameters['text']);
		}
	}

	/**
	 * Convert various formats to HTML
	 *
	 * Markdown->HTML, PHP->HTML, and BBC->HTML
	 *
	 * @param array $format_parameters
	 */
	private function formatToHTML(&$format_parameters)
	{
		// From MD to HTML
		if ($format_parameters['from'] === 'markdown')
		{
			require_once(EXTDIR . '/markdown/markdown.php');
			$format_parameters['text'] = htmlspecialchars(Markdown($format_parameters['text']));
			return;
		}

		// From PHP to HTML ?
		if ($format_parameters['from'] === 'php')
		{
			$format_parameters['text'] = "<code>\n<cite>php</cite>\n" . $format_parameters['text'] . "\n</code>";
			$format_parameters['text'] = htmlspecialchars($format_parameters['text']);
			return;
		}

		// BBC to HTML
		if ($format_parameters['from'] === 'bbc')
		{
			PreparseCode::instance()->preparsecode($format_parameters['text'], false);
			$format_parameters['text'] = ParserWrapper::instance()->parseMessage($format_parameters['text'] , true);

			$format_parameters['text'] = strtr($format_parameters['text'], array('&nbsp;' => ' ', '<br />' => "\n<br />"));
			$format_parameters['text'] = htmlspecialchars($format_parameters['text']);
		}
	}

	/**
	 * Convert various formats to MarkDown
	 *
	 * Convert BBC->Markdown, PHP->Markdown, and HTML->Markdown
	 *
	 * @param array $format_parameters
	 */
	private function formatToMARKDOWN(&$format_parameters)
	{
		// From BBC to MD, should have a direct way, but ...
		if ($format_parameters['from'] === 'bbc')
		{
			PreparseCode::instance()->preparsecode($format_parameters['text'], false);
			$format_parameters['text'] = ParserWrapper::instance()->parseMessage($format_parameters['text'] , true);

			// Convert this to markdown
			$parser = new Html_2_Md($format_parameters['text']);
			$format_parameters['text'] = $parser->get_markdown();
			$format_parameters['text'] = htmlspecialchars($format_parameters['text']);
			return;
		}

		// From PHP to MD ?
		if ($format_parameters['from'] === 'php')
		{
			$format_parameters['text'] = '<code>' . un_htmlspecialchars($format_parameters['text']) . '</code>';
			$parser = new Html_2_Md($format_parameters['text']);
			$format_parameters['text'] = htmlspecialchars($parser->get_markdown());
			return;
		}

		// HTML to MD
		if ($format_parameters['from'] === 'html')
		{
			// Convert this to markdown
			$parser = new Html_2_Md($format_parameters['text']);
			$format_parameters['text'] = $parser->get_markdown();
		}
	}
}
