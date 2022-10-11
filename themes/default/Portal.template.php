<?php

/**
 * @package SimplePortal ElkArte
 *
 * @author SimplePortal Team
 * @copyright 2015-2022 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.1
 */

use BBC\ParserWrapper;

/**
 * Used to display articles on the portal
 */
function template_portal_index()
{
	global $context, $txt;

	if (empty($context['articles']))
	{
		return;
	}

	echo '
	<div id="sp_index" class="sp_article_block_container">';

	foreach ($context['articles'] as $article)
	{
		echo '
		<h3 class="category_header">
			', $article['link'], '
		</h3>
		<div class="sp_block_section">
			<div class="sp_content_padding">
 				<div class="sp_article_detail">';

		if (!empty($article['author']['avatar']['image']))
		{
			echo $article['author']['avatar']['image'];
		}

		echo '
					<span class="floatright">
						', sprintf($txt['sp_posted_in_on_by'], $article['category']['link'], $article['date'], $article['author']['link']);

		if (!empty($article['author']['avatar']['image']))
		{
			echo '
						<br />';
		}
		else
		{
			echo '
					</span>
					<span class="floatright">';
		}

		echo '
						', sprintf($article['view_count'] == 1
							? $txt['sp_viewed_time'] : $txt['sp_viewed_times'], $article['view_count']), ', ', sprintf($article['comment_count'] == 1
							? $txt['sp_commented_on_time'] : $txt['sp_commented_on_times'], $article['comment_count']), '
					</span>
				</div>
				<div id="msg_', $article['id'], '" class="post inner sp_inner sp_article_content">';

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

		echo
				$article['preview'], '
				<div class="sp_article_extra clear">
					<a class="linkbutton" href="', $article['href'], '">', $txt['sp_read_more'], '</a>
					<a class="linkbutton" href="', $article['href'], '#sp_view_comments">', $txt['sp_write_comment'], '</a>
				</div>
			</div>
		</div>
		</div>';
	}

	// Pages as well?
	if (!empty($context['article_page_index']))
	{
		echo '
		<div class="sp_page_index">',
			template_pagesection(), '
		</div>';
	}

	echo '
	</div>';
}

/**
 * Displays the above blocks, this includes header, left and center-top blocks
 */
function template_portal_above()
{
	global $context, $modSettings, $scripturl, $txt;

	// Allowing side collapsing? if so show the buttons
	if (empty($modSettings['sp_disable_side_collapse']) && ($context['SPortal']['sides'][1]['active'] || $context['SPortal']['sides'][4]['active']))
	{
		echo '
	<div class="righttext sp_fullwidth">';
		if ($context['SPortal']['sides'][4]['active'])
		{
			echo '
		<a id="sp_collapse_side4" class="icon ', $context['SPortal']['sides'][1]['collapsed']
				? 'expand' : 'collapse', '" href="#side" onclick="return sp_collapseSide(4)"></a>';
		}

		if ($context['SPortal']['sides'][1]['active'])
		{
			echo '
		<a id="sp_collapse_side1" class="icon ', $context['SPortal']['sides'][1]['collapsed']
			? 'expand' : 'collapse', '" href="#side" onclick="return sp_collapseSide(1)"></a>';
		}

		if (!empty($context['SPortal']['blocks']['custom_arrange']) &&
			($context['site_action'] === 'sportal' || $context['site_action'] === 'portalmain'))
		{
			echo '
		<a id="sp_reset_blocks" class="icon reset" title="', $txt['sp_reset blocks'], '" href="' . $scripturl . '?action=portal;sa=resetlayout;' . $context['session_var'] . '=' . $context['session_id'] . '"></a>';
		}

		echo '
	</div>';
	}
	// No side collapsing but a custom arrangement?
	elseif (!empty($context['SPortal']['blocks']['custom_arrange']) && $context['site_action'] === 'sportal' && ($context['SPortal']['sides'][1]['active'] || $context['SPortal']['sides'][4]['active']))
	{
		echo '
	<div class="righttext sp_fullwidth">
		<a id="sp_reset_blocks" class="icon reset" title="', $txt['sp_reset blocks'], '" href="' . $scripturl . '?action=portal;sa=resetlayout;' . $context['session_var'] . '=' . $context['session_id'] . '"></a>
	</div>';
	}

	// Output any header blocks
	if (!empty($context['SPortal']['blocks'][5]))
	{
		echo '
	<div id="sp_header" class="sp_column">';

		foreach ($context['SPortal']['blocks'][5] as $block)
		{
			template_block($block, 5);
		}

		echo '
	</div>';
	}

	// The flex main portal div
	echo '
	<div id="sp_main">';

	// Output all the Left blocks
	if (!empty($modSettings['showleft']) && !empty($context['SPortal']['blocks'][1]))
	{
		echo '
		<div id="sp_left" class="sp_main_cell"', !empty($modSettings['leftwidth']) ? ' style="width:' . $modSettings['leftwidth'] . '"'
			: '', $context['SPortal']['sides'][1]['collapsed'] && empty($modSettings['sp_disable_side_collapse'])
			? ' style="display: none;"' : '', '>
			<div id="sp_left_div" class="sp_column">';

		foreach ($context['SPortal']['blocks'][1] as $block)
		{
			template_block($block, 1);
		}

		echo '
			</div>
		</div>';
	}

	// No left or right? Then force the center section to go full witch
	$flex = '';
	if ((empty($modSettings['showleft']) || empty($context['SPortal']['blocks'][1]))
		&& (empty($modSettings['showright']) || empty($context['SPortal']['blocks'][4])))
	{
		$flex = 'flex: 0 0 100%; min-width: 100%;';
	}

	// Our container for the center section's
	echo '
		<div id="sp_center" style="' . $flex . '">';

	// Followed by all the Center-Top Blocks
	echo '
			<div id="sp_center_top" class="sp_main_cell">
				<div id="sp_top_div" class="sp_column">';

	if (!empty($context['SPortal']['blocks'][2]))
	{
		foreach ($context['SPortal']['blocks'][2] as $block)
		{
			template_block($block, 2);
		}

		if (empty($context['SPortal']['on_portal']))
		{
			echo '
					<br class="sp_side_clear" />';
		}
	}

	echo '
				</div>
			</div>';
}

/**
 * Display below blocks, this includes our right, bottom and footer blocks
 */
function template_portal_below()
{
	global $context, $modSettings;

	// Onto the Center-Bottom blocks
	echo '
			<div id="sp_center_bottom" class="sp_main_cell">
				<div id="sp_bottom_div" class="sp_column">';

	// Output all the Bottom blocks
	if (!empty($context['SPortal']['blocks'][3]))
	{
		if (empty($context['SPortal']['on_portal']))
		{
			echo '
					<br class="sp_side_clear" />';
		}

		foreach ($context['SPortal']['blocks'][3] as $block)
		{
			template_block($block, 3);
		}
	}

	echo '
				</div>
			</div>';

	// Close our sp_center container
	echo '
		</div>';

	// Then the right blocks
	if (!empty($modSettings['showright']) && !empty($context['SPortal']['blocks'][4]))
	{
		echo '
		<div id="sp_right" class="sp_main_cell"', !empty($modSettings['rightwidth'])
			? ' style="width:' . $modSettings['rightwidth'] . '"' : '',
		$context['SPortal']['sides'][4]['collapsed'] && empty($modSettings['sp_disable_side_collapse'])
			? ' style="display: none;"' : '', '>
			<div id="sp_right_div" class="sp_column">';

		foreach ($context['SPortal']['blocks'][4] as $block)
		{
			template_block($block, 4);
		}

		echo '
			</div>
		</div>';
	}

	// Close the sp_main portal div
	echo '
	</div>';

	// Footer Blocks
	if (!empty($context['SPortal']['blocks'][6]))
	{
		echo '
	<div id="sp_footer" class="sp_column">';

		foreach ($context['SPortal']['blocks'][6] as $block)
		{
			template_block($block, 6);
		}

		echo '
	</div>';
	}
}

/**
 * Generic template to wrap blocks
 *
 * @param mixed[] $block
 * @param int $side
 */
function template_block($block, $side = -1)
{
	global $context, $modSettings, $txt;

	// Make sure that we have some valid block data.
	if (empty($block) || empty($block['type']))
	{
		return;
	}

	// Board news gets special formatting, really intended to be at the top of a column
	// @todo move the sp_boardNews-specific style to the board news template
	// This prevents it from being in a collapsible div?  Has not been working in years as the 'type'
	// was wrong (fixed now) but wanted ?
	/*
	if ($block['type'] === 'Board_News')
	{
		echo '
			<div id="sp_block_', $block['id'], '" class="sp_block_section', isset($context['SPortal']['sides'][$block['column']]['last']) && $context['SPortal']['sides'][$block['column']]['last'] == $block['id'] && ($block['column'] != 2 || empty($modSettings['sp_articles_index'])) ? '_last' : '', '">';

		$block['instance']->render();

		echo '
			</div>';

		return;
	}
	*/

	if (isset($txt['sp_custom_block_title_' . $block['id']]))
	{
		$block['label'] = $txt['sp_custom_block_title_' . $block['id']];
	}

	template_block_default($block, $side);
}

/**
 * Wrap a block in our default wrapper
 *
 * @param mixed[] $block
 * @param int $side
 */
function template_block_default($block, $side)
{
	echo '
					<div class="sp_block_container sp_block_' . strtolower($block['type']) . '" id="block_' . $block['id'] . ',' . $side . '">';

	// Show a title bar or not, some blocks have their own bars
	if (empty($block['style']['no_title']))
	{
		$parser = ParserWrapper::instance();

		echo '
						<h3 class="sp_category_header ', strpos($block['style']['title']['class'], 'custom') === false ? $block['style']['title']['class'] : '', ' sp_drag_header"', !empty($block['style']['title']['style']) ? ' style="' . $block['style']['title']['style'] . '"' : '', '>';

		if (empty($block['force_view']))
		{
			echo '
							<span class="sp_category_toggle">&nbsp;
								<a href="javascript:sp_collapseBlock(\'', $block['id'], '\')">
									<span id="sp_collapse_', $block['id'], '" class="chevricon i-chevron-', $block['collapsed'] ? 'down' : 'up', '"></span>
								</a>
							</span>';
		}

		echo '
							', $parser->parseMessage($block['label'], true), '
						</h3>';
	}

	echo '
						<div id="sp_block_' . $block['id'] . '" class="sp_block_section', empty($block['style']['no_body'])
		? (empty($block['style']['body']['class']) ? '"' : ' ' . $block['style']['body']['class'] . '"')
		: ' sp_no_body_style"', $block['collapsed'] && empty($block['force_view'])
		? ' style="display: none;"' : '', '>
							<div class="',
		$block['type'] !== 'Menu' ? 'sp_block sp_' : 'sp_content_padding sp_', strtolower($block['type']), '"',
		!empty($block['style']['body']['style']) ? ' style="' . $block['style']['body']['style'] . '"' : '', '>';

	// Call the block routine
	$block['instance']->render();

	// Close this block up
	echo '
							</div>
						</div>
					</div>';
}
