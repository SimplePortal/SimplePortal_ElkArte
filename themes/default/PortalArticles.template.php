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
		<div class="windowbg">
			<div class="sp_content_padding">', $txt['error_sp_no_articles'], '</div>
		</div>';
	}

	foreach ($context['articles'] as $article)
	{
		echo '
		<div class="windowbg">
			<div class="sp_content_padding">
				<div class="sp_article_detail">';

		if (!empty($article['author']['avatar']['image']))
			echo $article['author']['avatar']['image'];

		echo '
					<span style="text-align: right; float: right;">
						', sprintf($txt['sp_posted_in_on_by'], $article['category']['link'], $article['date'], $article['author']['link']), '
						<br />
						', sprintf($article['views'] == 1 ? $txt['sp_viewed_time'] : $txt['sp_viewed_times'], $article['views']), ', ', sprintf($article['comments'] == 1 ? $txt['sp_commented_on_time'] : $txt['sp_commented_on_times'], $article['comments']), '
					</span>
					<h4>', $article['link'], '</h4>
				</div>
				<hr />
				<p>', $article['preview'], '<a href="', $article['href'], '">...</a></p>
				<div class="sp_article_extra">
					<a href="', $article['href'], '">', $txt['sp_read_more'], '</a> | <a href="', $article['href'], '#sp_view_comments">', $txt['sp_write_comment'], '</a>
				</div>
			</div>
		</div>';
	}

	echo '
	</div>';
}

/**
 * Template for viewing a specific article in the system
 * Also used to preivew an article from the new article form
 */
function template_view_article()
{
	global $context, $txt;

	echo '
	<div id="sp_view_article">
		<h3 class="category_header">
			', $context['article']['title'], '
		</h3>
		<div class="windowbg">
			<div class="sp_content_padding">
				<div class="sp_article_detail">';

	if (!empty($context['article']['author']['avatar']['image']))
		echo $context['article']['author']['avatar']['image'];

	echo '
					<span>
						', sprintf($txt['sp_posted_in_on_by'], $context['article']['category']['link'], $context['article']['date'], $context['article']['author']['link']);

	if (!empty($context['article']['author']['avatar']['image']))
		echo '
						<br />';
	else
		echo '
					</span>
					<span class="sp_float_right">';

	echo '
						', sprintf($context['article']['views'] == 1 ? $txt['sp_viewed_time'] : $txt['sp_viewed_times'], $context['article']['views']), ', ', sprintf($context['article']['comments'] == 1 ? $txt['sp_commented_on_time'] : $txt['sp_commented_on_times'], $context['article']['comments']), '
					</span>
				</div>
				<hr />
				<div>';

	sportal_parse_content($context['article']['body'], $context['article']['type']);

	echo '
				</div>
			</div>
		</div>';

	// Just previewing the new article?
	if (empty($context['preview']))
	{
		echo '
		<div id="sp_view_comments">
			<h3 class="category_header">
				', $txt['sp-comments'], '
			</h3>';

		if (empty($context['article']['comments']))
		{
			echo '
			<div class="windowbg">
				<div class="sp_content_padding">
					', $txt['error_sp_no_comments'], '
				</div>
			</div>';
		}

		foreach ($context['article']['comments'] as $comment)
		{
			echo '
			<div id="comment', $comment['id'], '" class="windowbg">
				<div class="sp_content_padding flow_auto">
					<div class="sp_comment_detail">';

			if (!empty($comment['author']['avatar']['image']))
				echo $comment['author']['avatar']['image'];

			if ($comment['can_moderate'])
				echo '
						<div class="sp_float_right">
							<a href="', $context['article']['href'], ';modify=', $comment['id'], ';', $context['session_var'], '=', $context['session_id'], '">', sp_embed_image('modify'), '</a>
							<a href="', $context['article']['href'], ';delete=', $comment['id'], ';', $context['session_var'], '=', $context['session_id'], '">', sp_embed_image('delete'), '</a>
						</div>';

			echo '
						<span>', sprintf($txt['sp_posted_on_by'], $comment['time'], $comment['author']['link']), '</span>
					</div>
					<hr />
					<p>
						', $comment['body'], '
					</p>
				</div>
			</div>';
		}

		if ($context['article']['can_comment'])
		{
			echo '
			<div class="windowbg2">
				<div class="sp_content_padding">
					<form action="', $context['article']['href'], '" method="post" accept-charset="UTF-8">
						<textarea name="body" rows="5" cols="50" style="', isBrowser('is_ie8') ? 'width: 635px; max-width: 99%; min-width: 99%' : 'width: 99%', ';">', !empty($context['article']['comment']['body']) ? $context['article']['comment']['body'] : '', '</textarea>
						<div class="sp_center">
							<input type="submit" name="submit" value="', !empty($context['article']['comment']) ? $txt['sp_modify'] : $txt['sp_submit'], '" class="right_submit" />
						</div>
						<input type="hidden" name="comment" value="', !empty($context['article']['comment']['id']) ? $context['article']['comment']['id'] : 0, '" />
						<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					</form>
				</div>
			</div>';
		}

		echo '
		</div>';
	}

	echo '
	</div>';
}
