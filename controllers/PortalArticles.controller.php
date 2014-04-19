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
 * Article controller.
 * This class handles requests for Article Functionality
 */
class Article_Controller extends Action_Controller
{
	/**
	 * Default method
	 */
	public function action_index()
	{
		// Where do you want to go today? :P
		$this->action_sportal_articles();
	}

	/**
	 * This method is executed before any action handler.
	 * Loads common things for all methods
	 */
	public function pre_dispatch()
	{
		loadTemplate('PortalArticles');
	}

	/**
	 * Load all articles for selection
	 */
	public function action_sportal_articles()
	{
		global $context, $scripturl, $txt, $modSettings;

		$context['articles'] = sportal_get_articles(0, true, true, 'spa.id_article DESC');

		foreach ($context['articles'] as $article)
		{
			if (!empty($modSettings['articlelength']))
				$article['body'] = shorten_text($article['body'], $modSettings['articlelength'], true);

			$context['articles'][$article['id']]['preview'] = parse_bbc($article['body']);
			$context['articles'][$article['id']]['date'] = standardTime($article['date']);
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

		$db = database();

		$article_id = !empty($_REQUEST['article']) ? $_REQUEST['article'] : 0;

		if (is_int($article_id))
			$article_id = (int) $article_id;
		else
			$article_id = Util::htmlspecialchars($article_id, ENT_QUOTES);

		$context['article'] = sportal_get_articles($article_id, true, true);

		if (empty($context['article']['id']))
			fatal_lang_error('error_sp_article_not_found', false);

		$context['article']['date'] = standardTime($context['article']['date']);
		$context['article']['comments'] = sportal_get_comments($context['article']['id']);
		$context['article']['can_comment'] = $context['user']['is_logged'];
		$context['article']['can_moderate'] = allowedTo('sp_admin') || allowedTo('sp_manage_articles');

		if ($context['article']['can_comment'] && !empty($_POST['body']))
		{
			checkSession();
			sp_prevent_flood('spacp', false);

			$db = database();
			require_once(SUBSDIR . '/Post.subs.php');

			// prep the body / comment
			$body = Util::htmlspecialchars(trim($_POST['body']));
			preparsecode($body);

			if (!empty($body) && trim(strip_tags(parse_bbc($body, false), '<img>')) !== '')
			{
				if (!empty($_POST['comment']))
				{
					$request = $db->query('', '
						SELECT id_comment, id_member
						FROM {db_prefix}sp_comments
						WHERE id_comment = {int:comment_id}
						LIMIT {int:limit}',
						array(
							'comment_id' => (int) $_POST['comment'],
							'limit' => 1,
						)
					);
					list ($comment_id, $author_id) = $db->fetch_row($request);
					$db->free_result($request);

					if (empty($comment_id) || (!$context['article']['can_moderate'] && $user_info['id'] != $author_id))
						fatal_lang_error('error_sp_cannot_comment_modify', false);

					sportal_modify_comment($comment_id, $body);
				}
				else
					sportal_create_comment($context['article']['id'], $body);
			}

			redirectexit('article=' . $context['article']['article_id']);
		}

		if ($context['article']['can_comment'] && !empty($_GET['modify']))
		{
			checkSession('get');

			$request = $db->query('', '
				SELECT id_comment, id_member, body
				FROM {db_prefix}sp_comments
				WHERE id_comment = {int:comment_id}
				LIMIT {int:limit}',
				array(
					'comment_id' => (int) $_GET['modify'],
					'limit' => 1,
				)
			);
			list ($comment_id, $author_id, $body) = $db->fetch_row($request);
			$db->free_result($request);

			if (empty($comment_id) || (!$context['article']['can_moderate'] && $user_info['id'] != $author_id))
				fatal_lang_error('error_sp_cannot_comment_modify', false);

			require_once(SOURCEDIR . '/Post.subs.php');

			$context['article']['comment'] = array(
				'id' => $comment_id,
				'body' => str_replace(array('"', '<', '>', '&nbsp;'), array('&quot;', '&lt;', '&gt;', ' '), un_preparsecode($body)),
			);
		}

		if ($context['article']['can_comment'] && !empty($_GET['delete']))
		{
			checkSession('get');

			$request = $db->query('', '
				SELECT id_comment, id_article, id_member
				FROM {db_prefix}sp_comments
				WHERE id_comment = {int:comment_id}
				LIMIT {int:limit}',
				array(
					'comment_id' => (int) $_GET['delete'],
					'limit' => 1,
				)
			);
			list ($comment_id, $article_id, $author_id) = $db->fetch_row($request);
			$db->free_result($request);

			if (empty($comment_id) || (!$context['article']['can_moderate'] && $user_info['id'] != $author_id))
				fatal_lang_error('error_sp_cannot_comment_delete', false);

			sportal_delete_comment($article_id, $comment_id);

			redirectexit('article=' . $context['article']['article_id']);
		}

		if (empty($_SESSION['last_viewed_article']) || $_SESSION['last_viewed_article'] != $context['article']['id'])
		{
			$db->query('', '
				UPDATE {db_prefix}sp_articles
				SET views = views + 1
				WHERE id_article = {int:current_article}',
				array(
					'current_article' => $context['article']['id'],
				)
			);

			$_SESSION['last_viewed_article'] = $context['article']['id'];
		}

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

		$context['page_title'] = $context['article']['title'];
		$context['sub_template'] = 'view_article';
	}
}