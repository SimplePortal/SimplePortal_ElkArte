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
 * Portal controller.
 * This class handles requests that allow viewing the main portal or the portal credits
 */
class Sportal_Controller extends Action_Controller
{
	/**
	 * Default method, just forwards
	 */
	public function action_index()
	{
		// Where do you want to go today? :P
		$this->action_sportal_index();
	}

	/**
	 * Common actions for all methods in the class
	 */
	public function pre_dispatch()
	{
		global $context;

		$context['page_title'] = $context['forum_name'];

		if (isset($context['page_title_html_safe']))
			$context['page_title_html_safe'] = Util::htmlspecialchars(un_htmlspecialchars($context['page_title']));

		if (!empty($context['standalone']))
			setupMenuContext();
	}

	/**
	 * Loads article previews for display with the portal index template
	 */
	public function action_sportal_index()
	{
		global $context, $modSettings;

		$context['articles'] = sportal_get_articles(0, true, true, 'spa.id_article DESC');

		// If we have some articles
		foreach ($context['articles'] as $article)
		{
			// Just the preview
			if (!empty($modSettings['articlelength']))
				$article['body'] = shorten_text($article['body'], $modSettings['articlelength'], true);

			$context['articles'][$article['id']]['preview'] = parse_bbc($article['body']);
			$context['articles'][$article['id']]['date'] = standardTime($article['date']);
		}

		$context['sub_template'] = 'portal_index';
	}

	/**
	 * Displays the credit page outside of the admin area
	 */
	public function action_sportal_credits()
	{
		global $context, $txt;

		require_once(ADMINDIR . '/PortalAdminMain.php');
		loadLanguage('SPortalAdmin', sp_languageSelect('SPortalAdmin'));

		sportal_information(false);

		$context['page_title'] = $txt['sp-info_title'];
		$context['sub_template'] = 'information';
	}
}