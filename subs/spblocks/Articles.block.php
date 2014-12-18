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
 * Article Block, show the list of articles in the system
 *
 * @param mixed[] $parameters
 *		'category' => list of categories to choose article from
 *		'limit' => number of articles to show
 *		'type' => 0 latest 1 random
 *		'length' => length for the body text preview
 *		'avatar' => whether to show the author avatar or not
 *
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
class Articles_Block extends SP_Abstract_Block
{
	public function __construct($db = null)
	{
		$this->block_parameters = array(
			'category' => array(),
			'limit' => 'int',
			'type' => 'select',
			'length' => 'int',
			'avatar' => 'check',
		);

		parent::__construct($db);
	}

	public function parameters()
	{
		global $txt;

		require_once(SUBSDIR . '/PortalAdmin.subs.php');

		$categories = sp_load_categories();
		$this->block_parameters['category'][0] = $txt['sp_all'];
		foreach ($categories as $category)
			$this->block_parameters['category'][$category['id']] = $category['name'];

		return $this->block_parameters;
	}

	function setup($parameters)
	{
		global $txt, $color_profile;

		require_once(SUBSDIR . '/Post.subs.php');

		// Set up for the query
		$category = empty($parameters['category']) ? 0 : (int) $parameters['category'];
		$limit = empty($parameters['limit']) ? 5 : (int) $parameters['limit'];
		$type = empty($parameters['type']) ? 0 : 1;
		$this->data['length'] = isset($parameters['length']) ? (int) $parameters['length'] : 250;
		$this->data['avatar'] = empty($parameters['avatar']) ? 0 : (int) $parameters['avatar'];

		$this->data['articles'] = sportal_get_articles(null, true, true, $type ? 'RAND()' : 'spa.date DESC', $category, $limit);

		$colorids = array();
		foreach ($this->data['articles'] as $article)
		{
			if (!empty($article['author']['id']))
				$colorids[$article['author']['id']] = $article['author']['id'];
		}

		// No articles in the system or none they can see
		if (empty($this->data['articles']))
		{
			$this->data['error_msg'] = $txt['error_sp_no_articles_found'];
			$this->setTemplate('template_sp_articles_error');

			return;
		}

		// Doing the color thing
		if (!empty($colorids) && sp_loadColors($colorids) !== false)
		{
			foreach ($this->data['articles'] as $k => $p)
			{
				if (!empty($color_profile[$p['author']['id']]['link']))
					$this->data['articles'][$k]['author']['link'] = $color_profile[$p['author']['id']]['link'];
			}
		}
		$this->setTemplate('template_sp_articles');
	}
}

function template_sp_articles_error($data)
{
		echo '
								', $data['error_msg'];
}

function template_sp_articles($data)
{
	global $scripturl;

	// Not showing avatars, just use a compact link view
	if (empty($this->data['avatar']))
	{
		echo '
			<ul class="sp_list">';

		foreach ($data['articles'] as $article)
			echo '
				<li>', sp_embed_image('topic'), ' ', $article['link'], '</li>';

		echo '
			</ul>';
	}
	// Or the full monty!
	else
	{
		echo '
							<table class="sp_fullwidth">';

		foreach ($data['articles'] as $article)
		{
			// Shorten it for the preview
			$article['body'] = sportal_parse_content($article['body'], $article['type'], 'return');
			$article['body'] = Util::shorten_html($article['body'], $this->data['length']);

			echo '
								<tr class="sp_articles_row">
									<td class="sp_articles centertext">';

			// If we have an avatar to show, show it
			if (!empty($article['author']['avatar']['href']))
				echo '
										<a href="', $scripturl, '?action=profile;u=', $article['author']['id'], '">
											<img src="', $article['author']['avatar']['href'], '" alt="', $article['author']['name'], '" style="max-width:40px" />
										</a>';

			echo '
									</td>
									<td>
										<span class="sp_articles_title">', $article['author']['link'], '</span><br />
										', $article['link'], '
									</td>
									<td>',
										$article['body'],
									'</td>
								</tr>
								<tr>
									<td colspan="3" class="sp_articles_row"></td>
								</tr>';
		}

		echo '
							</table>';
	}
}