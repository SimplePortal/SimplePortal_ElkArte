<?php

/**
 * @package SimplePortal ElkArte
 *
 * @author SimplePortal Team
 * @copyright 2015-2023 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.3
 */

use BBC\ParserWrapper;
use BBC\PreparseCode;

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

		// add subaction array to act accordingly
		$subActions = array(
			'article' => array($this, 'action_sportal_article'),
			'articles' => array($this, 'action_sportal_articles'),
			'spattach' => array($this, 'action_sportal_attach'),
			'rmattach' => array($this, 'action_sportal_rmattach'),
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
		$per_page = min($total_articles, !empty($modSettings['sp_articles_per_page']) ? $modSettings['sp_articles_per_page'] : 10);
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

		if (!is_int($article_id))
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
			$preparse = PreparseCode::instance();
			$preparse->preparsecode($body, false);

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

		// Needed for basic Lightbox functionality
		loadJavascriptFile('topic.js', ['defer' => false]);

		$context['description'] = trim(preg_replace('~<[^>]+>~', ' ', $context['article']['body']));
		$context['description'] = Util::shorten_text(preg_replace('~\s\s+|&nbsp;|&quot;|&#039;~', ' ', $context['description']), 384, true);

		// Off to the template we go
		$context['page_title'] = $context['article']['title'];
		$context['sub_template'] = 'view_article';
	}

	/**
	 * Downloads / shows an article attachment
	 *
	 * It is accessed via the query string ?action=portal;sa=spattach.
	 */
	public function action_sportal_attach()
	{
		global $txt, $modSettings, $context, $user_info;

		// Some defaults that we need.
		$context['no_last_modified'] = true;

		// Make sure some attachment was requested, and they can view them
		if (!isset($_GET['article'], $_GET['attach']))
		{
			throw new Elk_Exception('no_access', false);
		}

		// No funny business, you need to have access to the article to see its attachments
		if (sportal_article_access($_GET['article']) === false)
		{
			throw new Elk_Exception('no_access', false);
		}

		// Temporary attachment, special case...
		if (strpos($_GET['attach'], 'post_tmp_' . $user_info['id'] . '_') !== false)
		{
			$modSettings['automanage_attachments'] = 0;
			$modSettings['attachmentUploadDir'] = [1 => $modSettings['sp_articles_attachment_dir']];

			return (new Attachment_Controller())->action_tmpattach();
		}

		$id_article = (int) $_GET['article'];
		$id_attach = (int) $_GET['attach'];

		if (isset($_GET['thumb']))
		{
			$attachment = sportal_get_attachment_thumb_from_article($id_article, $id_attach);
		}
		else
		{
			$attachment = sportal_get_attachment_from_article($id_article, $id_attach);
		}

		if (empty($attachment))
		{
			throw new Elk_Exception('no_access', false);
		}

		list ($real_filename, $file_hash, $file_ext, $id_attach, $attachment_type, $mime_type, $width, $height) = $attachment;
		$filename = $modSettings['sp_articles_attachment_dir'] . '/' . $id_attach . '_' . $file_hash . '.elk';

		// No file, generate a bland its missing image
		if (!file_exists($filename))
		{
			$this->sp_no_attach();
			obExit(false);
		}

		require_once(SUBSDIR . '/Attachments.subs.php');
		$eTag = '"' . substr($id_attach . $real_filename . filemtime($filename), 0, 64) . '"';
		$use_compression = $this->useCompression($mime_type);
		$disposition = !isset($_GET['image']) ? 'attachment' : 'inline';
		$do_cache = (!isset($_GET['image']) && getValidMimeImageType($file_ext) !== '') === false;

		// Make sure the mime type warrants an inline display.
		if (isset($_GET['image']) && !empty($mime_type) && strpos($mime_type, 'image/') !== 0)
		{
			unset($_GET['image']);
			$mime_type = '';
		}
		// Does this have a mime type?
		elseif (empty($mime_type) || !(isset($_GET['image']) || getValidMimeImageType($file_ext) === ''))
		{
			$mime_type = '';
			unset($_GET['image']);
		}

		$this->send_headers($filename, $eTag, $mime_type, $use_compression, $disposition, $real_filename, $do_cache);
		$this->send_file($filename, $mime_type);

		obExit(false);
	}

	/**
	 * Sends the requested file to the user.  If the file is compressible e.g.
	 * has a mine type of text/??? may compress the file prior to sending.
	 *
	 * @param string $filename
	 * @param string $mime_type
	 */
	public function send_file($filename, $mime_type)
	{
		$body = file_get_contents($filename);
		$use_compression = $this->useCompression($mime_type);
		$length = filesize($filename);

		// If we can/should compress this file
		if ($use_compression && strlen($body) > 250)
		{
			$body = gzencode($body, 2);
			$length = strlen($body);
			header('Content-Encoding: gzip');
			header('Vary: Accept-Encoding');
		}

		// Someone is getting a present
		if (!empty($length))
		{
			header('Content-Length: ' . $length);
		}

		// Forcibly end any output buffering going on.
		while (ob_get_level() > 0)
		{
			@ob_end_clean();
		}

		echo $body;
	}

	/**
	 * If the mime type benefits from compression e.g. text/xyz and gzencode is
	 * available and the user agent accepts gzip, then return true, else false
	 *
	 * @param string $mime_type
	 * @return bool if we should compress the file
	 */
	public function useCompression($mime_type)
	{
		global $modSettings;

		// Support is available on the server
		if (!function_exists('gzencode') || empty($modSettings['enableCompressedOutput']))
		{
			return false;
		}

		// Not compressible, or not supported / requested by client
		if (!preg_match('~^(?:text/|application/(?:json|xml|rss\+xml)$)~i', $mime_type)
			|| (!isset($_SERVER['HTTP_ACCEPT_ENCODING']) || strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') === false))
		{
			return false;
		}

		return true;
	}

	/**
	 * Takes care of sending out the most common headers.
	 *
	 * @param string $filename Full path+file name of the file in the filesystem
	 * @param string $eTag ETag cache validator
	 * @param string $mime_type The mime-type of the file
	 * @param bool $use_compression If use gzip compression - Deprecated since 1.1.9
	 * @param string $disposition The value of the Content-Disposition header
	 * @param string $real_filename The original name of the file
	 * @param bool $do_cache If send the a max-age header or not
	 * @param bool $check_filename When false, any check on $filename is skipped
	 */
	public function send_headers($filename, $eTag, $mime_type, $use_compression, $disposition, $real_filename, $do_cache, $check_filename = true)
	{
		global $txt;

		// No point in a nicer message, because this is supposed to be an attachment anyway...
		if ($check_filename === true && !file_exists($filename))
		{
			loadLanguage('Errors');

			header((preg_match('~HTTP/1\.[01]~i', $_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 404 Not Found');
			header('Content-Type: text/plain; charset=UTF-8');

			// We need to die like this *before* we send any anti-caching headers as below.
			die('404 - ' . $txt['attachment_not_found']);
		}

		// If it hasn't been modified since the last time this attachment was retrieved, there's no need to display it again.
		if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']))
		{
			list ($modified_since) = explode(';', $_SERVER['HTTP_IF_MODIFIED_SINCE']);
			if ($check_filename === false || strtotime($modified_since) >= filemtime($filename))
			{
				@ob_end_clean();

				// Answer the question - no, it hasn't been modified ;).
				header('HTTP/1.1 304 Not Modified');
				exit;
			}
		}

		// Check whether the ETag was sent back, and cache based on that...
		if (!empty($_SERVER['HTTP_IF_NONE_MATCH']) && strpos($_SERVER['HTTP_IF_NONE_MATCH'], $eTag) !== false)
		{
			@ob_end_clean();

			header('HTTP/1.1 304 Not Modified');
			exit;
		}

		// Send the attachment headers.
		header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 525600 * 60) . ' GMT');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $check_filename === true ? filemtime($filename) : time() - 525600 * 60) . ' GMT');
		header('Accept-Ranges: bytes');
		header('Connection: close');
		header('ETag: ' . $eTag);

		if (!empty($mime_type) && strpos($mime_type, 'image/') === 0)
		{
			header('Content-Type: ' . $mime_type);
		}
		else
		{
			header('Content-Type: application/octet-stream');
		}

		// Different browsers like different standards...
		$filename = str_replace('"', '', $real_filename);

		// Send as UTF-8 if the name requires that
		$altName = '';
		if (preg_match('~[\x80-\xFF]~', $filename))
			$altName = "; filename*=UTF-8''" . rawurlencode($filename);

		header('Content-Disposition: ' . $disposition . '; filename="' . $filename . '"' . $altName);

		// If this has an "image extension" - but isn't actually an image - then ensure it isn't cached cause of silly IE.
		if ($do_cache === true)
		{
			header('Cache-Control: max-age=' . (525600 * 60) . ', private');
		}
		else
		{
			header('Pragma: no-cache');
			header('Cache-Control: no-cache');
		}

		// Try to buy some time...
		detectServer()->setTimeLimit(600);
	}

	/**
	 * Function to remove attachments via ajax calls
	 */
	public function action_sportal_rmattach()
	{
		global $context, $txt, $modSettings;

		// Prepare the template so we can respond with json
		$template_layers = Template_Layers::instance();
		$template_layers->removeAll();
		loadTemplate('Json');
		$context['sub_template'] = 'send_json';

		// Make sure the session is valid
		if (checkSession('request', '', false) !== '')
		{
			loadLanguage('Errors');
			$context['json_data'] = array('result' => false, 'data' => $txt['session_timeout']);

			return false;
		}

		// Temp attachment or one that was already saved?
		if (isset($this->_req->post->attachid))
		{
			$result = false;
			if (!empty($_SESSION['temp_attachments']))
			{
				require_once(SUBSDIR . '/Attachments.subs.php');

				$attachId = getAttachmentIdFromPublic($this->_req->post->attachid);

				$result = removeTempAttachById($attachId);
				if ($result === true)
				{
					$context['json_data'] = array('result' => true);
				}
			}

			if ($result !== true)
			{
				$attachId = $this->_req->getPost('attachid', 'intval');
				$articleId = $this->_req->getPost('articleid', 'intval');
				sportal_load_permissions();
				if (sportal_article_access($articleId))
				{
					$keep_ids = array();
					$attach_ids = sportal_get_articles_attachments($articleId);
					$attach_ids = !empty($attach_ids[$articleId]) ? $attach_ids[$articleId] : array();
					foreach ($attach_ids as $id => $value)
					{
						if ($id !== $attachId)
						{
							$keep_ids[] = $id;
						}
					}

					$attachmentQuery = array(
						'id_article' => $articleId,
						'not_id_attach' => $keep_ids,
						'id_folder' => $modSettings['sp_articles_attachment_dir'],
					);

					$result_tmp = removeArticleAttachments($attachmentQuery);
					if (!empty($result_tmp))
					{
						$context['json_data'] = array('result' => true);
						$result = true;
					}
					else
					{
						$result = $result_tmp;
					}
				}
			}

			if ($result !== true)
			{
				loadLanguage('Errors');
				$context['json_data'] = array('result' => false, 'data' => $txt[!empty($result) ? $result : 'attachment_not_found']);
			}
		}
		else
		{
			loadLanguage('Errors');
			$context['json_data'] = array('result' => false, 'data' => $txt['attachment_not_found']);
		}
	}

	/**
	 * Generates a language image based on text for display.
	 *
	 * @param null|string $text
	 * @throws \Elk_Exception
	 */
	public function sp_no_attach($text = null)
	{
		global $txt;

		require_once(SUBSDIR . '/Graphics.subs.php');
		if ($text === null)
		{
			loadLanguage('Errors');
			$text = $txt['attachment_not_found'];
		}

		$this->send_headers('no_image', 'no_image', 'image/png', false, 'inline', 'no_image.png', true, false);

		$img = generateTextImage($text, 200);

		if ($img === false)
		{
			throw new Elk_Exception('no_access', false);
		}

		echo $img;
	}
}
