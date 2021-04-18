<?php

/**
 * @package SimplePortal ElkArte
 *
 * @author SimplePortal Team
 * @copyright 2015-2017 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.0 RC1
 */


/**
 * Category controller.
 *
 * - This class handles requests for Category Functionality
 */
class PortalCategories_Controller extends Action_Controller
{
	/**
	 * Default method
	 */
	public function action_index()
	{
		// Where do you want to go today?
		$this->action_sportal_categories();
	}

	/**
	 * This method is executed before any action handler.
	 * Loads common things for all methods
	 */
	public function pre_dispatch()
	{
		loadTemplate('PortalCategories');
	}

	/**
	 * Display a list of categories for selection
	 */
	public function action_sportal_categories()
	{
		global $context, $scripturl, $txt;

		$context['categories'] = sportal_get_categories(0, true, true);

		$context['linktree'][] = array(
			'url' => $scripturl . '?action=portal;sa=categories',
			'name' => $txt['sp-categories'],
		);

		$context['page_title'] = $txt['sp-categories'];
		$context['sub_template'] = 'view_categories';
	}

	/**
	 * View a specific category, showing all the articles it contains
	 */
	public function action_sportal_category()
	{
		global $context, $scripturl, $modSettings, $txt;

		// Basic article support
		require_once(SUBSDIR . '/PortalArticle.subs.php');

		$category_id = !empty($_REQUEST['category']) ? $_REQUEST['category'] : 0;

		if (!is_int($category_id))
		{
			$category_id = Util::htmlspecialchars($category_id, ENT_QUOTES);
		}

		$context['category'] = sportal_get_categories($category_id, true, true);

		if (empty($context['category']['id']))
		{
			throw new Elk_Exception('error_sp_category_not_found', false);
		}

		// Set up the pages
		$total_articles = sportal_get_articles_in_cat_count($context['category']['id']);
		$per_page = min($total_articles, !empty($modSettings['sp_articles_per_page']) ? $modSettings['sp_articles_per_page'] : 10);
		$start = !empty($_REQUEST['start']) ? (int) $_REQUEST['start'] : 0;

		if ($total_articles > $per_page)
		{
			$context['page_index'] = constructPageIndex($context['category']['href'] . ';start=%1$d', $start, $total_articles, $per_page, true);
		}

		// Load the articles in this category
		$context['articles'] = sportal_get_articles(0, true, true, 'spa.id_article DESC', $context['category']['id'], $per_page, $start);
		foreach ($context['articles'] as $article)
		{
			$context['articles'][$article['id']]['preview'] = censor($article['body']);
			$context['articles'][$article['id']]['date'] = htmlTime($article['date']);
			$context['articles'][$article['id']]['time'] = $article['date'];

			// Parse / shorten as required
			$context['article']['id'] = $article['id'];
			$context['articles'][$article['id']]['cut'] = sportal_parse_cutoff_content($context['articles'][$article['id']]['preview'], $article['type'], $modSettings['sp_articles_length'], $context['articles'][$article['id']]['article_id']);
		}

		// Auto video embedding enabled?
		if (!empty($modSettings['enableVideoEmbeding']))
		{
			addInlineJavascript('
				$(document).ready(function() {
					$().linkifyvideo(oEmbedtext);
				});', true
			);
		}

		// Needed for basic Lightbox functionality
		loadJavascriptFile('topic.js', ['defer' => true]);

		$context['linktree'][] = array(
			'url' => $scripturl . '?category=' . $context['category']['category_id'],
			'name' => $context['category']['name'],
		);

		$context['page_title'] = sprintf($txt['sp_articles_in_category'], $context['category']['name']);
		$context['sub_template'] = 'view_category';
	}
}
