<?php

/**
 * @package SimplePortal ElkArte
 *
 * @author SimplePortal Team
 * @copyright 2015 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.0 Beta 2
 */

/**
 * Used to view articles on the portal
 */
function template_view_articles()
{
	global $context, $txt;

	echo '
	<div id="sp_view_articles">
		<h3 class="category_header">
			', $context['page_title'], '
		</h3>';

	if (empty($context['articles']))
	{
		echo '
		<div class="sp_content_padding">',
			$txt['error_sp_no_articles'], '
		</div>';
	}

	foreach ($context['articles'] as $id => $article)
	{
		echo '
			<div class="sp_content_padding">
				<div class="sp_article_detail">';

		// Start off with the avatar
		if (!empty($article['author']['avatar']['image']))
		{
			echo $article['author']['avatar']['image'];
		}

		// And now the article
		echo '
					<span class="sp_article_latest">
						', sprintf(!empty($context['using_relative_time']) ? $txt['sp_posted_on_in_by'] : $txt['sp_posted_in_on_by'], $article['category']['link'], $article['date'], $article['author']['link']), '
						<br />
						', sprintf($article['views'] == 1 ? $txt['sp_viewed_time'] : $txt['sp_viewed_times'], $article['views']), ', ', sprintf($article['comments'] == 1 ? $txt['sp_commented_on_time'] : $txt['sp_commented_on_times'], $article['comments']), '
					</span>
					<h4>', $article['link'], '</h4>
				</div>
				<hr />
				<div id="msg_', $id, '" class="inner sp_inner">', $article['preview'], '<a href="', $article['href'], '">...</a></div>
				<div class="sp_article_extra">
					<a class="linkbutton" href="', $article['href'], '">', $txt['sp_read_more'], '</a>
					<a class="linkbutton" href="', $article['href'], '#sp_view_comments">', $txt['sp_write_comment'], '</a>
				</div>
		</div>';
	}

	echo '
	</div>';

	// Pages as well?
	if (!empty($context['page_index']))
		template_pagesection();

	if (!empty($context['using_relative_time']))
		addInlineJavascript('$(\'.sp_article_latest\').addClass(\'relative\');', true);
}

/**
 * Template for viewing a specific article in the system
 * Also used to preview an article from the new article form
 */
function template_view_article()
{
	global $context, $txt;

	echo '
	<article id="sp_view_article">';

	if (empty($context['article']['style']['no_title']))
	{
		echo '
		<h1', strpos($context['article']['style']['title']['class'], 'custom') === false ? ' class="' . $context['article']['style']['title']['class'] . '"' : '', !empty($context['article']['style']['title']['style']) ? ' style="' . $context['article']['style']['title']['style'] . '"' : '', '>
			', $context['article']['title'], '
		</h1>';
	}

	echo '
		<div class="sp_content_padding ', $context['article']['style']['body']['class'], '"', !empty($context['article']['style']['body']['style']) ? ' style="' . $context['article']['style']['body']['style'] . '"' : '', '>
			<div class="sp_article_detail">';

	if (!empty($context['article']['author']['avatar']['image']))
		echo $context['article']['author']['avatar']['image'];

	echo '
					<span class="sp_article_latest">
						', sprintf(!empty($context['using_relative_time']) ? $txt['sp_posted_on_in_by'] : $txt['sp_posted_in_on_by'], $context['article']['category']['link'], $context['article']['date'], $context['article']['author']['link']);

	if (!empty($context['article']['author']['avatar']['image']))
		echo '
						<br />';
	else
		echo '
					</span><br>
					<span class="floatright">';

	echo '
					', sprintf($context['article']['view_count'] == 1 ? $txt['sp_viewed_time'] : $txt['sp_viewed_times'], $context['article']['view_count']), ', ',
					sprintf($context['article']['comment_count'] == 1 ? $txt['sp_commented_on_time'] : $txt['sp_commented_on_times'], $context['article']['comment_count']), '
					</span>
				</div>
				<hr />
				<div id="msg_', $context['article']['id'], '" class="messageContent inner sp_inner">' ,
					$context['article']['body'];

	// Assuming there are attachments...
	if (!empty($context['article']['attachment']))
	{
		template_sp_display_attachments($context['article'], false);
	}

	echo '		
				</div>
		</div>';

	// Not just previewing the new article, then show comments etc
	if (empty($context['preview']))
	{
		echo '
		<section id="sp_view_comments">
			<h3 class="category_header">
				', $txt['sp-comments'], '
			</h3>';

		if (empty($context['article']['comments']))
		{
			echo '
			<div class="infobox">
					', $txt['error_sp_no_comments'], '
			</div>';
		}

		foreach ($context['article']['comments'] as $comment)
		{
			echo '
			<div id="comment', $comment['id'], '" class="content">
				<div class="sp_content_padding flow_auto">
					<div class="sp_comment_detail">';

			if (!empty($comment['author']['avatar']['image']))
				echo $comment['author']['avatar']['image'];

			// Show the edit icons if they are allowed
			if ($comment['can_moderate'])
				echo '
						<div class="floatright">
							<a href="', $context['article']['href'], ';modify=', $comment['id'], ';', $context['session_var'], '=', $context['session_id'], '#sp_comment">', sp_embed_image('modify'), '</a>
							<a href="', $context['article']['href'], ';delete=', $comment['id'], ';', $context['session_var'], '=', $context['session_id'], '">', sp_embed_image('delete'), '</a>
						</div>';

			echo '
						<span class="sp_article_latest">', sprintf($txt['sp_posted_by'], $comment['time'], $comment['author']['link']), '</span>
					</div>
					<hr />
					<p class="sp_comment_body">
						', $comment['body'], '
					</p>
				</div>
			</div>';
		}

		// Pages as well?
		if (!empty($context['page_index']))
			template_pagesection();

		// Show the comment box
		if ($context['article']['can_comment'])
		{
			echo '
			<section id="sp_comment" class="sp_content_padding">
					<form action="', $context['article']['href'], '" method="post" accept-charset="UTF-8">
					<textarea name="body" rows="5" cols="50" style="width: 100%;padding: 0.1em 0.2em" tabindex="', $context['tabindex']++, '">', !empty($context['article']['comment']['body']) ? $context['article']['comment']['body'] : '', '</textarea>
					<div class="submitbutton">
						<input type="submit" name="submit" value="', !empty($context['article']['comment']) ? $txt['sp_modify'] : $txt['sp_submit'], '" class="right_submit" />
						<input type="hidden" name="comment" value="', !empty($context['article']['comment']['id']) ? $context['article']['comment']['id'] : 0, '" />
						<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				</div>
				</form>
			</section>';
		}

		echo '
		</section>';
	}

	echo '
	</article>';

	template_article_schema_script();

	if (!empty($context['using_relative_time']))
		addInlineJavascript('$(\'.sp_article_latest\').addClass(\'relative\');', true);
}

/**
 * Output schema.org data as ld+json
 */
function template_article_schema_script()
{
	global $context, $boardurl;

	$post = trim(preg_replace('~<[^>]+>~', ' ', $context['article']['body']));
	$description = Util::shorten_text(preg_replace('~\s\s+~', ' ', $post));

	$smd = array(
		'@context' => 'http://schema.org',
		'@type' => 'Article',
		'headline' => $context['article']['title'],
		'author' => array(
			'@type' => 'Person',
			'name' => $context['article']['author']['name'],
		),
		'url' => $context['article']['href'],
		'commentCount' => $context['article']['comment_count'],
		'datePublished' => standardTime($context['article']['time']),
		'description' => $description,
		'wordCount' => str_word_count($post),
		'publisher' => array(
			'@type' => 'Organization',
			'name' => $context['forum_name'],
			'logo' => array(
				'@type' => 'ImageObject',
				'url' => $context['header_logo_url_html_safe'],
				'width' => 120,
				'height' => 60,
			),
		),
		'mainEntityOfPage' => array(
			'@type' => 'WebPage',
			'@id' => !empty($context['canonical_url']) ? $context['canonical_url'] : $boardurl,
		),
	);

	// If there are attachments, use the first as the image
	if (!empty($context['article']['attachment']))
	{
		foreach ($context['article']['attachment'] as $attachment)
		{
			$smd['image'] = array(
				'@type' => 'ImageObject',
				'url' => $attachment['href'],
				'width' => $attachment['real_width'],
				'height' => $attachment['real_height']
			);

			break;
		}
	}

	echo '
	<script type="application/ld+json">
    ', json_encode($smd, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), '
    </script>';
}

/**
 * Used to display attachments below an article body
 *
 * @param array $article
 * @param bool $ignoring
 */
function template_sp_display_attachments($article, $ignoring)
{
	echo '
							<div id="msg_', $article['id'], '_footer" class="attachments"', $ignoring ? ' style="display:none;"' : '', '>';

	foreach ($article['attachment'] as $attachment)
	{
		echo '
								<figure class="attachment_block">';

		if ($attachment['is_image'])
		{
			if ($attachment['thumbnail']['has_thumb'])
				echo '
										<a href="', $attachment['href'], ';image" id="link_', $attachment['id'], '" onclick="', $attachment['thumbnail']['javascript'], '">
											<img class="attachment_image" src="', $attachment['thumbnail']['href'], '" alt="" id="thumb_', $attachment['id'], '" />
										</a>';
			else
				echo '
										<img class="attachment_image" src="', $attachment['href'], ';image" alt="" style="width:', $attachment['width'], 'px; height:', $attachment['height'], 'px;" />';
		}

		echo '
										<figcaption><a href="', $attachment['href'], '" class="attachment_name">', $attachment['name'], '</a>
											<span class="attachment_details">', $attachment['size'], ($attachment['is_image'] ? ' / ' . $attachment['real_width'] . 'x' . $attachment['real_height'] : ''), '</span>
										</figcaption>';
		echo '
								</figure>';
	}

	echo '
							</div>';
}
