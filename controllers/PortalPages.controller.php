<?php

/**
 * @package SimplePortal ElkArte
 *
 * @author SimplePortal Team
 * @copyright 2015 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.0 Beta 2
 */

if (!defined('ELK'))
{
	die('No access...');
}

/**
 * Pages controller.
 * This class handles requests for Page Functionality
 */
class Pages_Controller extends Action_Controller
{
	/**
	 * Default method
	 */
	public function action_index()
	{
		// Where do you want to go today?
		$this->action_sportal_page();
	}

	/**
	 * This method is executed before any action handler.
	 * Loads common things for all methods
	 */
	public function pre_dispatch()
	{
		loadTemplate('PortalPages');
	}

	/**
	 * List the available pages in the system
	 */
	public function action_sportal_pages()
	{
		global $context, $scripturl, $txt;

		$context['SPortal']['pages'] = sportal_get_pages(0, true, true);

		$context['linktree'][] = array(
			'url' => $scripturl . '?action=portal;sa=pages',
			'name' => $txt['sp-pages'],
		);

		$context['page_title'] = $txt['sp-pages'];
		$context['sub_template'] = 'view_pages';
	}

	/**
	 * View a specific page in the system
	 */
	public function action_sportal_page()
	{
		global $context, $scripturl;

		// Use the requested page id
		$page_id = !empty($_REQUEST['page']) ? $_REQUEST['page'] : 0;

		// Fetch the page
		$context['SPortal']['page'] = sportal_get_pages($page_id, true, true);
		if (empty($context['SPortal']['page']['id']))
		{
			fatal_lang_error('error_sp_page_not_found', false);
		}

		// Fetch any style associated with the page
		$context['SPortal']['page']['style'] = sportal_select_style($context['SPortal']['page']['styles']);

		// Prepare the body
		$context['SPortal']['page']['body'] = sportal_parse_content($context['SPortal']['page']['body'], $context['SPortal']['page']['type'], 'return');

		// Increase the view counter
		if (empty($_SESSION['last_viewed_page']) || $_SESSION['last_viewed_page'] != $context['SPortal']['page']['id'])
		{
			sportal_increase_viewcount('page', $context['SPortal']['page']['id']);
			$_SESSION['last_viewed_page'] = $context['SPortal']['page']['id'];
		}

		// Prep the template for display
		$context['linktree'][] = array(
			'url' => $scripturl . '?page=' . $page_id,
			'name' => $context['SPortal']['page']['title'],
		);

		$context['page_title'] = $context['SPortal']['page']['title'];
		$context['sub_template'] = 'view_page';
	}
}