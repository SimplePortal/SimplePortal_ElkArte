<?php

/**
 * @package SimplePortal
 *
 * @author SimplePortal Team
 * @copyright 2015-2017 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.0 RC1
 */

/**
 * Article Block, show the list of articles in the system
 *
 * @param mixed[] $parameters
 * 		'category' => list of categories to choose article from
 * 		'limit' => number of articles to show
 * 		'type' => 0 latest 1 random
 * 		'view' => 0 compact 1 full
 * 		'length' => length for the body text preview
 * 		'avatar' => whether to show the author avatar or not
 *
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
class Articles_Block extends SP_Abstract_Block
{
	/**
	 * @var array
	 */
	protected $attachments = array();

	/**
	 * Constructor, used to define block parameters
	 *
	 * @param Database|null $db
	 */
	public function __construct($db = null)
	{
		require_once(SUBSDIR . '/PortalArticle.subs.php');

		$this->block_parameters = array(
			'category' => array(),
			'limit' => 'int',
			'type' => 'select',
			'view' => 'select',
			'length' => 'int',
			'avatar' => 'check',
		);

		parent::__construct($db);
	}

	/**
	 * Sets / loads optional parameters for a block
	 *
	 * - Called from PortalAdminBlocks.controller as part of block add/save/edit
	 */
	public function parameters()
	{
		global $txt;

		require_once(SUBSDIR . '/PortalAdmin.subs.php');

		// Load the sp categories for selection in the block
		$categories = sp_load_categories();

		$this->block_parameters['category'][0] = $txt['sp_all'];
		foreach ($categories as $category)
		{
			$this->block_parameters['category'][$category['id']] = $category['name'];
		}

		return $this->block_parameters;
	}

	/**
	 * Initializes the block for use.
	 *
	 * - Called from portal.subs as part of the sportal_load_blocks process
	 *
	 * @param mixed[] $parameters
	 * @param int $id
	 */
	public function setup($parameters, $id)
	{
		global $txt;

		require_once(SUBSDIR . '/Post.subs.php');

		// Set up for the query
		$category = empty($parameters['category']) ? 0 : (int) $parameters['category'];
		$limit = empty($parameters['limit']) ? 5 : (int) $parameters['limit'];
		$type = empty($parameters['type']) ? 0 : 1;
		$this->data['view'] = empty($parameters['view']) ? 0 : 1;
		$this->data['length'] = isset($parameters['length']) ? (int) $parameters['length'] : 250;
		$this->data['avatar'] = empty($parameters['avatar']) ? 0 : (int) $parameters['avatar'];

		// Fetch some articles
		$this->data['articles'] = sportal_get_articles(null, true, true, $type ? 'RAND()' : 'spa.date DESC', $category, $limit);

		// No articles in the system or none they can see
		if (empty($this->data['articles']))
		{
			$this->data['error_msg'] = $txt['error_sp_no_articles_found'];
			$this->setTemplate('template_sp_articles_error');

			return;
		}

		// Doing the color thing
		$this->_color_ids();

		// And prepare the body text
		if (!empty($this->data['view']))
		{
			$this->prepare_view();
		}

		// Set the template name
		$this->setTemplate('template_sp_articles');
	}

	/**
	 * Prepares the body text when using the full view
	 */
	private function prepare_view()
	{
		global $context;

		require_once(SUBSDIR . '/Post.subs.php');

		// Needed for basic Lightbox functionality
		loadJavascriptFile('topic.js', ['defer' => true]);

		foreach ($this->data['articles'] as $aid => $article)
		{
			// Good time to do this is ... now
			censor($article['title']);
			censor($article['body']);

			// Parse and optionally shorten the result
			$context['article']['id'] = $article['id'];
			$article['cut'] = sportal_parse_cutoff_content($article['body'], $article['type'], $this->_modSettings['sp_articles_length'], $article['article_id']);

			if ($this->_modSettings['sp_resize_images'])
			{
				$article['body'] = str_ireplace('class="bbc_img', 'class="bbc_img sp_article', $article['body']);
			}

			// Account for embedded videos
			$this->data['embed_videos'] = !empty($this->_modSettings['enableVideoEmbeding']);

			// Add / replace the data
			$this->data['articles'][$aid] = array_merge($this->data['articles'][$aid], $article);
		}
	}

	/**
	 * Provide the color profile id's
	 */
	private function _color_ids()
	{
		global $color_profile;

		$color_ids = array();
		foreach ($this->data['articles'] as $article)
		{
			if (!empty($article['author']['id']))
			{
				$color_ids[$article['author']['id']] = $article['author']['id'];
			}
		}

		if (sp_loadColors($color_ids) !== false)
		{
			foreach ($this->data['articles'] as $k => $p)
			{
				if (!empty($color_profile[$p['author']['id']]['link']))
				{
					$this->data['articles'][$k]['author']['link'] = $color_profile[$p['author']['id']]['link'];
				}
			}
		}
	}
}

/**
 * Error template
 *
 * @param mixed[] $data
 */
function template_sp_articles_error($data)
{
	echo $data['error_msg'];
}

/**
 * Main template
 *
 * @param mixed[] $data
 */
function template_sp_articles($data)
{
	global $scripturl, $txt, $context;

	// Not showing avatars, just use a compact link view
	if (empty($data['view']))
	{
		echo '
			<ul class="sp_list">';

		foreach ($data['articles'] as $article)
		{
			echo '
				<li>', sp_embed_image('topic'), ' ', $article['link'], '</li>';
		}

		echo '
			</ul>';
	}
	// Or the full monty!
	else
	{
		// Auto video embedding enabled?
		if ($data['embed_videos'])
		{
			addInlineJavascript('
			$(document).ready(function() {
				$().linkifyvideo(oEmbedtext);
			});', true);
		}

		// Relative times?
		if (!empty($context['using_relative_time']))
		{
			addInlineJavascript('$(\'.sp_article_latest\').addClass(\'relative\');', true);
		}

		// Show the articles.
		foreach ($data['articles'] as $article)
		{
			echo '
			<h3 class="secondary_header">',
				$article['title'], '
			</h3>
			<div id="msg_', $article['article_id'], '" class="sp_article_content">
				<div class="sp_content_padding">';

			if (!empty($article['author']['avatar']['href']) && $data['avatar'])
			{
				echo '
					<a href="', $scripturl, '?action=profile;u=', $article['author']['id'], '">
						<img src="', $article['author']['avatar']['href'], '" alt="', $article['author']['name'], '" style="max-width:40px" class="floatright" />
					</a>
					<span>
						', sprintf(!empty($context['using_relative_time']) ? $txt['sp_posted_on_in_by'] : $txt['sp_posted_in_on_by'], $article['category']['link'], htmlTime($article['date']), $article['author']['link']), '
						<br />
						', sprintf($article['view_count'] == 1 ? $txt['sp_viewed_time'] : $txt['sp_viewed_times'], $article['view_count']), ', ', sprintf($article['comment_count'] == 1 ? $txt['sp_commented_on_time'] : $txt['sp_commented_on_times'], $article['comment_count']), '
					</span>';
			}
			else
			{
				echo '
					<span>
						', sprintf(!empty($context['using_relative_time']) ? $txt['sp_posted_on_in_by'] : $txt['sp_posted_in_on_by'], $article['category']['link'], htmlTime($article['date']), $article['author']['link']), '
						<br />
						', sprintf($article['view_count'] == 1 ? $txt['sp_viewed_time'] : $txt['sp_viewed_times'], $article['view_count']), ', ', sprintf($article['comment_count'] == 1 ? $txt['sp_commented_on_time'] : $txt['sp_commented_on_times'], $article['comment_count']), '
					</span>';
			}

			echo '
					<div class="post inner sp_inner">
						<hr />';

			if (!empty($article['attachments']))
			{
				echo '
						<div class="sp_attachment_thumb">';

				// If you want Fancybox to tag this, remove nfb_ from the id
				echo '
							<a href="', $article['href'], '" id="nfb_link_', $article['attachments']['id'], '">
								<img src="', $article['attachments']['href'], '" alt="" title="', $article['attachments']['name'], '" id="thumb_', $article['attachments']['id'], '" />
							</a>';

				echo '
						</div>';
			}

			echo '
					<div class="sp_article_block">',
			$article['body'], '
					</div>
				</div>
				<div class="righttext">
					<a class="linkbutton" href="', $article['href'], '">', $txt['sp_read_more'], '</a>
				</div>
			</div>
		</div>';
		}
	}
}
