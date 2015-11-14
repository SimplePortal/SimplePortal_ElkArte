<?php

/**
 * @package SimplePortal ElkArte
 *
 * @author SimplePortal Team
 * @copyright 2015 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.1.0 Beta 1
 */

if (!defined('ELK'))
	die('No access...');

/**
 * Article controller.
 * This class handles requests for Article Functionality
 */
class Article_Controller extends Action_Controller
{
	/**
	 * This method is executed before any action handler.
	 * Loads common things for all methods
	 */
	public function pre_dispatch()
	{
		loadTemplate('PortalArticles');
		require_once(SUBSDIR . '/PortalArticle.subs.php');
	}

	/**
	 * Default method
	 */
	public function action_index()
	{
		// Where do you want to go today? :P
		$this->action_sportal_articles();
	}

	/**
	 * Load all articles for selection
	 */
	public function action_sportal_articles()
	{
		global $context, $scripturl, $txt, $modSettings;

		// Set up for pagination
		$total_articles = sportal_get_articles_count();
		$per_page = min($total_articles, !empty($modSettings['sp_articles_per_page'])
			? $modSettings['sp_articles_per_page']
			: 10);
		$start = !empty($_REQUEST['start']) ? (int) $_REQUEST['start'] : 0;

		if ($total_articles > $per_page)
			$context['page_index'] = constructPageIndex($scripturl . '?action=portal;sa=articles;start=%1$d', $start, $total_articles, $per_page, true);

		// Fetch the article page
		$context['articles'] = sportal_get_articles(0, true, true, 'spa.id_article DESC', 0, $per_page, $start);

		foreach ($context['articles'] as $article)
		{
			// Want to cut this one a bit short?
			if (($cutoff = Util::strpos($article['body'], '[cutoff]')) !== false)
			{
				$article['body'] = Util::substr($article['body'], 0, $cutoff);
				if ($article['type'] === 'bbc')
				{
					require_once(SUBSDIR . '/Post.subs.php');
					preparsecode($article['body']);
				}
			}

			$context['articles'][$article['id']]['preview'] = sportal_parse_content($article['body'], $article['type'], 'return');
			$context['articles'][$article['id']]['date'] = htmlTime($article['date']);
		}

		$context['linktree'][] = array(
			'url' => $scripturl . '?action=portal;sa=articles',
			'name' => $txt['sp-articles'],
		);

		$context['page_title'] = $txt['sp-articles'];
		$context['sub_template'] = 'view_articles';
	}

	/**
	 * Display a chosen article
	 *
	 * - Update the stats, like #views etc
	 */
	public function action_sportal_article()
	{
		global $context, $scripturl, $user_info;

		$article_id = !empty($_REQUEST['article']) ? $_REQUEST['article'] : 0;

		if (is_int($article_id))
			$article_id = (int) $article_id;
		else
			$article_id = Util::htmlspecialchars($article_id, ENT_QUOTES);

		// Fetch and render the article
		$context['article'] = sportal_get_articles($article_id, true, true);
		if (empty($context['article']['id']))
			fatal_lang_error('error_sp_article_not_found', false);

		$context['article']['style'] = sportal_select_style($context['article']['styles']);
		$context['article']['body'] = sportal_parse_content($context['article']['body'], $context['article']['type'], 'return');

		// Set up for the comment pagination
		$total_comments = sportal_get_article_comment_count($context['article']['id']);
		$per_page = min($total_comments, !empty($modSettings['sp_articles_comments_per_page'])
			? $modSettings['sp_articles_comments_per_page']
			: 20);
		$start = !empty($_REQUEST['comments']) ? (int) $_REQUEST['comments'] : 0;

		if ($total_comments > $per_page)
			$context['page_index'] = constructPageIndex($scripturl . '?article=' . $context['article']['article_id'] . ';comments=%1$d', $start, $total_comments, $per_page, true);

		// Load in all the comments for the article
		$context['article']['comments'] = sportal_get_comments($context['article']['id'], $per_page, $start);

		// Prepare the final template details
		$context['article']['date'] = htmlTime($context['article']['date']);
		$context['article']['can_comment'] = $context['user']['is_logged'];
		$context['article']['can_moderate'] = allowedTo('sp_admin') || allowedTo('sp_manage_articles');

		// Commenting, new or an update perhaps
		if ($context['article']['can_comment'] && !empty($_POST['body']))
		{
			checkSession();
			sp_prevent_flood('spacp', false);

			require_once(SUBSDIR . '/Post.subs.php');

			// Prep the body / comment
			$body = Util::htmlspecialchars(trim($_POST['body']));
			preparsecode($body);

			// Update or add a new comment
			if (!empty($body) && trim(strip_tags(parse_bbc($body, false), '<img>')) !== '')
			{
				if (!empty($_POST['comment']))
				{
					list ($comment_id, $author_id, ) = sportal_fetch_article_comment((int) $_POST['comment']);
					if (empty($comment_id) || (!$context['article']['can_moderate'] && $user_info['id'] != $author_id))
						fatal_lang_error('error_sp_cannot_comment_modify', false);

					sportal_modify_article_comment($comment_id, $body);
				}
				else
					sportal_create_article_comment($context['article']['id'], $body);
			}

			// Set a anchor
			$anchor = '#comment' . (!empty($comment_id) ? $comment_id : ($total_comments > 0 ? $total_comments - 1 : 1));
			redirectexit('article=' . $context['article']['article_id']. $anchor);
		}

		// Prepare to edit an existing comment
		if ($context['article']['can_comment'] && !empty($_GET['modify']))
		{
			checkSession('get');

			list ($comment_id, $author_id, $body) = sportal_fetch_article_comment((int) $_GET['modify']);
			if (empty($comment_id) || (!$context['article']['can_moderate'] && $user_info['id'] != $author_id))
				fatal_lang_error('error_sp_cannot_comment_modify', false);

			require_once(SUBSDIR . '/Post.subs.php');

			$context['article']['comment'] = array(
				'id' => $comment_id,
				'body' => str_replace(array('"', '<', '>', '&nbsp;'), array('&quot;', '&lt;', '&gt;', ' '), un_preparsecode($body)),
			);
		}

		// Want to delete a comment?
		if ($context['article']['can_comment'] && !empty($_GET['delete']))
		{
			checkSession('get');

			if (sportal_delete_article_comment((int) $_GET['delete']) === false)
				fatal_lang_error('error_sp_cannot_comment_delete', false);

			redirectexit('article=' . $context['article']['article_id']);
		}

		// Increase the article view counter
		if (empty($_SESSION['last_viewed_article']) || $_SESSION['last_viewed_article'] != $context['article']['id'])
		{
			sportal_increase_viewcount('article', $context['article']['id']);
			$_SESSION['last_viewed_article'] = $context['article']['id'];
		}

		// Build the breadcrumbs
		$context['linktree'] = array_merge($context['linktree'], array(
			array(
				'url' => $scripturl . '?category=' . $context['article']['category']['category_id'],
				'name' => $context['article']['category']['name'],
			),
			array(
				'url' => $scripturl . '?article=' . $context['article']['article_id'],
				'name' => $context['article']['title'],
			)
		));

		// Off to the template we go
		$context['page_title'] = $context['article']['title'];
		$context['sub_template'] = 'view_article';
	}
}