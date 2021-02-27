<?php

/**
 * @package SimplePortal ElkArte
 *
 * @author SimplePortal Team
 * @copyright 2015-2017 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.0 RC1
 */

use BBC\ParserWrapper;


/**
 * Article controller.
 *
 * - This class handles requests for Article Functionality
 */
class PortalArticles_Controller extends Action_Controller
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
		require_once(SUBSDIR . '/Action.class.php');

		// add an subaction array to act accordingly
		$subActions = array(
			'article' => array($this, 'action_sportal_article'),
			'articles' => array($this, 'action_sportal_articles'),
			'spattach' => array($this, 'action_sportal_attach'),
		);

		// Setup the action handler
		$action = new Action();
		$subAction = $action->initialize($subActions, 'article');

		// Call the action
		$action->dispatch($subAction);
	}

	/**
	 * Load all articles for selection, not used just yet
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
		{
			$context['page_index'] = constructPageIndex($scripturl . '?action=portal;sa=articles;start=%1$d', $start, $total_articles, $per_page, true);
		}

		// Fetch the article page
		$context['articles'] = sportal_get_articles(0, true, true, 'spa.id_article DESC', 0, $per_page, $start);
		foreach ($context['articles'] as $article)
		{
			$context['articles'][$article['id']]['preview'] = censor($article['body']);
			$context['articles'][$article['id']]['date'] = htmlTime($article['date']);
			$context['articles'][$article['id']]['time'] = $article['date'];

			// Parse and cut as needed
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

		$context['linktree'][] = array(
			'url' => $scripturl . '?action=portal;sa=articles',
			'name' => $txt['sp-articles'],
		);

		$context['page_title'] = $txt['sp-articles'];
		$context['sub_template'] = 'view_articles';
	}

	/**
	 * Display a chosen article, called from frontpage hook
	 *
	 * - Update the stats, like #views etc
	 */
	public function action_sportal_article()
	{
		global $context, $scripturl, $user_info, $modSettings;

		$article_id = !empty($_REQUEST['article']) ? $_REQUEST['article'] : 0;

		if (is_int($article_id))
		{
			$article_id = (int) $article_id;
		}
		else
		{
			$article_id = Util::htmlspecialchars($article_id, ENT_QUOTES);
		}

		// Fetch and render the article
		$context['article'] = sportal_get_articles($article_id, true, true);
		if (empty($context['article']['id']))
		{
			throw new Elk_Exception('error_sp_article_not_found', false);
		}

		$context['article']['style'] = sportal_select_style($context['article']['styles']);
		$context['article']['body'] = censor($context['article']['body']);
		$context['article']['body'] = sportal_parse_content($context['article']['body'], $context['article']['type'], 'return');

		// Fetch attachments, if there are any
		if (!empty($modSettings['attachmentEnable']) && !empty($context['article']['has_attachments']))
		{
			loadJavascriptFile('topic.js');
			$context['article']['attachment'] = sportal_load_attachment_context($context['article']['id']);
		}

		// Set up for the comment pagination
		$total_comments = sportal_get_article_comment_count($context['article']['id']);
		$per_page = min($total_comments, !empty($modSettings['sp_articles_comments_per_page']) ? $modSettings['sp_articles_comments_per_page'] : 20);
		$start = !empty($_REQUEST['comments']) ? (int) $_REQUEST['comments'] : 0;

		if ($total_comments > $per_page)
		{
			$context['page_index'] = constructPageIndex($scripturl . '?article=' . $context['article']['article_id'] . ';comments=%1$d', $start, $total_comments, $per_page, true);
		}

		// Load in all the comments for the article
		$context['article']['comments'] = sportal_get_comments($context['article']['id'], $per_page, $start);

		// Prepare the final template details
		$context['article']['time'] = $context['article']['date'];
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
			$parser = ParserWrapper::instance();
			if (!empty($body) && trim(strip_tags($parser->parseMessage($body, false), '<img>')) !== '')
			{
				if (!empty($_POST['comment']))
				{
					list ($comment_id, $author_id,) = sportal_fetch_article_comment((int) $_POST['comment']);
					if (empty($comment_id) || (!$context['article']['can_moderate'] && $user_info['id'] != $author_id))
					{
						throw new Elk_Exception('error_sp_cannot_comment_modify', false);
					}

					sportal_modify_article_comment($comment_id, $body);
				}
				else
				{
					sportal_create_article_comment($context['article']['id'], $body);
				}
			}

			// Set a anchor
			$anchor = '#comment' . (!empty($comment_id) ? $comment_id : ($total_comments > 0 ? $total_comments - 1 : 1));
			redirectexit('article=' . $context['article']['article_id'] . $anchor);
		}

		// Prepare to edit an existing comment
		if ($context['article']['can_comment'] && !empty($_GET['modify']))
		{
			checkSession('get');

			list ($comment_id, $author_id, $body) = sportal_fetch_article_comment((int) $_GET['modify']);
			if (empty($comment_id) || (!$context['article']['can_moderate'] && $user_info['id'] != $author_id))
			{
				throw new Elk_Exception('error_sp_cannot_comment_modify', false);
			}

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
			{
				throw new Elk_Exception('error_sp_cannot_comment_delete', false);
			}

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

		// Auto video embedding enabled?
		if (!empty($modSettings['enableVideoEmbeding']))
		{
			addInlineJavascript('
				$(document).ready(function() {
					$().linkifyvideo(oEmbedtext);
				});', true
			);
		}

		$context['description'] = trim(preg_replace('~<[^>]+>~', ' ', $context['article']['body']));
		$context['description'] = Util::shorten_text(preg_replace('~\s\s+|&nbsp;|&quot;|&#039;~', ' ', $context['description']), 384, true);

		// Off to the template we go
		$context['page_title'] = $context['article']['title'];
		$context['sub_template'] = 'view_article';
	}

	/**
	 * Downloads / shows an article attachment
	 *
	 * It requires the view_attachments permission.
	 * It disables the session parser, and clears any previous output.
	 * It is accessed via the query string ?action=portal;sa=spattach.
	 */
	public function action_sportal_attach()
	{
		global $txt, $modSettings, $context;

		// Some defaults that we need.
		$context['no_last_modified'] = true;

		// Make sure some attachment was requested and they can view them
		if (!isset($_GET['article'], $_GET['attach']))
		{
			throw new Elk_Exception('no_access', false);
		}

		// No funny business, you need to have access to the article to see its attachments
		if (sportal_article_access($_GET['article']) === false)
		{
			throw new Elk_Exception('no_access', false);
		}

		// We need to do some work on attachments.
		$id_article = (int) $_GET['article'];
		$id_attach = (int) $_GET['attach'];
		$attachment = sportal_get_attachment_from_article($id_article, $id_attach);
		if (empty($attachment))
		{
			throw new Elk_Exception('no_access', false);
		}

		list ($real_filename, $file_hash, $file_ext, $id_attach, $attachment_type, $mime_type, $width, $height) = $attachment;
		$filename = $modSettings['sp_articles_attachment_dir'] . '/' . $id_attach . '_' . $file_hash . '.elk';

		// This is done to clear any output that was made before now.
		while (ob_get_level() > 0)
		{
			@ob_end_clean();
		}

		ob_start();
		header('Content-Encoding: none');

		// No point in a nicer message, because this is supposed to be an attachment anyway...
		if (!file_exists($filename))
		{
			loadLanguage('Errors');

			header((preg_match('~HTTP/1\.[01]~i', $_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 404 Not Found');
			header('Content-Type: text/plain; charset=UTF-8');

			// We need to die like this *before* we send any anti-caching headers as below.
			die('404 - ' . $txt['attachment_not_found']);
		}

		// If it hasn't been modified since the last time this attachment was retrieved,
		// there's no need to display it again.
		if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']))
		{
			list ($modified_since) = explode(';', $_SERVER['HTTP_IF_MODIFIED_SINCE']);
			if (strtotime($modified_since) >= filemtime($filename))
			{
				@ob_end_clean();

				// Answer the question - no, it hasn't been modified ;).
				header('HTTP/1.1 304 Not Modified');
			}
			exit(0);
		}

		// Check whether the ETag was sent back, and cache based on that...
		$eTag = '"' . substr($id_attach . $real_filename . filemtime($filename), 0, 64) . '"';
		if (!empty($_SERVER['HTTP_IF_NONE_MATCH']) && strpos($_SERVER['HTTP_IF_NONE_MATCH'], $eTag) !== false)
		{
			@ob_end_clean();

			header('HTTP/1.1 304 Not Modified');
			exit(0);
		}

		// Send the attachment headers.
		header('Content-Transfer-Encoding: binary');
		header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 525600 * 60) . ' GMT');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($filename)) . ' GMT');
		header('Accept-Ranges: bytes');
		header('Connection: close');
		header('ETag: ' . $eTag);

		// Make sure the mime type warrants an inline display.
		if (isset($_GET['image']) && !empty($mime_type) && strpos($mime_type, 'image/') !== 0)
		{
			unset($_GET['image']);
		}
		// Does this have a mime type?
		elseif (!empty($mime_type) && (isset($_GET['image']) || !in_array($file_ext, array('jpg', 'gif', 'jpeg', 'x-ms-bmp', 'png', 'psd', 'tiff', 'iff'))))
		{
			header('Content-Type: ' . strtr($mime_type, array('image/bmp' => 'image/x-ms-bmp')));
		}
		else
		{
			header('Content-Type: ' . (isBrowser('ie') || isBrowser('opera') ? 'application/octetstream' : 'application/octet-stream'));
			unset($_GET['image']);
		}

		$disposition = !isset($_GET['image']) ? 'attachment' : 'inline';

		// Different browsers like different standards...
		if (isBrowser('firefox'))
		{
			header('Content-Disposition: ' . $disposition . '; filename*=UTF-8\'\'' . rawurlencode(preg_replace_callback('~&#(\d{3,8});~', 'fixchar__callback', $real_filename)));
		}
		elseif (isBrowser('opera'))
		{
			header('Content-Disposition: ' . $disposition . '; filename="' . preg_replace_callback('~&#(\d{3,8});~', 'fixchar__callback', $real_filename) . '"');
		}
		elseif (isBrowser('ie'))
		{
			header('Content-Disposition: ' . $disposition . '; filename="' . urlencode(preg_replace_callback('~&#(\d{3,8});~', 'fixchar__callback', $real_filename)) . '"');
		}
		else
		{
			header('Content-Disposition: ' . $disposition . '; filename="' . $real_filename . '"');
		}

		// If this has an "image extension" - but isn't actually an image - then ensure it isn't cached cause of silly IE.
		if (!isset($_GET['image']) && in_array($file_ext, array('gif', 'jpg', 'bmp', 'png', 'jpeg', 'tiff')))
		{
			header('Pragma: no-cache');
			header('Cache-Control: no-cache');
		}
		else
		{
			header('Cache-Control: max-age=' . (525600 * 60) . ', private');
		}

		if (empty($modSettings['enableCompressedOutput']) || filesize($filename) > 4194304)
		{
			header('Content-Length: ' . filesize($filename));
		}

		// Try to buy some time...
		@set_time_limit(600);

		// Since we don't do output compression for files this large...
		if (filesize($filename) > 4194304)
		{
			// Forcibly end any output buffering going on.
			while (ob_get_level() > 0)
			{
				@ob_end_clean();
			}

			$fp = fopen($filename, 'rb');
			while (!feof($fp))
			{
				echo fread($fp, 8192);

				flush();
			}
			fclose($fp);
		}
		// On some of the less-bright hosts, readfile() is disabled.  It's just a faster, more byte safe, version of what's in the if.
		elseif (@readfile($filename) === null)
		{
			echo file_get_contents($filename);
		}

		obExit(false);
	}
}
