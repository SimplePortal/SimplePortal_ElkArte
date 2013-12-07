<?php

/**
 * @package SimplePortal
 *
 * @author SimplePortal Team
 * @copyright 2013 SimplePortal Team
 * @license BSD 3-clause
 *
 * @version 2.4
 */

/**
 * Used to display articles
 */
function template_portal_index()
{
	global $context, $txt;

	echo '
	<div id="sp_index">';

	foreach ($context['articles'] as $article)
	{
		echo '
		<h3 class="category_header">
			', $article['link'], '
		</h3>
		<div class="windowbg2">
			<div class="sp_content_padding">
				<span>', sprintf($txt['sp_posted_in_on_by'], $article['category']['link'], $article['date'], $article['author']['link']), '</span>
				<p>', $article['preview'], '<a href="', $article['href'], '">...</a></p>
				<span>', sprintf($article['views'] == 1 ? $txt['sp_viewed_time'] : $txt['sp_viewed_times'], $article['views']) ,', ', sprintf($article['comments'] == 1 ? $txt['sp_commented_on_time'] : $txt['sp_commented_on_times'], $article['comments']), '</span>
			</div>
		</div>';
	}

	echo '
	</div>';
}

/**
 * Displays the above blocks, this includes header, left and top blocks
 */
function template_portal_above()
{
	global $context, $modSettings;

	// Allowing side collasping? if so show the buttons
	if (empty($modSettings['sp_disable_side_collapse']) && ($context['SPortal']['sides'][1]['active'] || $context['SPortal']['sides'][4]['active']))
	{
		echo '
	<div class="sp_right sp_fullwidth">';

		if ($context['SPortal']['sides'][1]['active'])
			echo '
		<a href="#side" onclick="return sp_collapseSide(1)">', sp_embed_image($context['SPortal']['sides'][1]['collapsed'] ? 'expand' : 'collapse', '', null, null, true, 'sp_collapse_side1'), '</a>';

		if ($context['SPortal']['sides'][4]['active'])
			echo '
		<a href="#side" onclick="return sp_collapseSide(4)">', sp_embed_image($context['SPortal']['sides'][4]['collapsed'] ? 'expand' : 'collapse', '', null, null, true, 'sp_collapse_side4'), '</a>';

		echo '
	</div>';
	}

	// Output any header blocks
	if (!empty($context['SPortal']['blocks'][5]))
	{
		echo '
	<div id="sp_header">';

		foreach ($context['SPortal']['blocks'][5] as $block)
			template_block($block);

		echo '
	</div>';
	}

	echo '
	<table id="sp_main">
		<tr>';

	// Output all the Left blocks
	if (!empty($modSettings['showleft']) && !empty($context['SPortal']['blocks'][1]))
	{
		echo '
			<td id="sp_left"', !empty($modSettings['leftwidth']) ? ' width="' . $modSettings['leftwidth'] . '"' : '', $context['SPortal']['sides'][1]['collapsed'] && empty($modSettings['sp_disable_side_collapse']) ? ' style="display: none;"' : '', '>';

		foreach ($context['SPortal']['blocks'][1] as $block)
			template_block($block);

		echo '
			</td>';
	}

	// Followed by all the Top Blocks
	echo '
			<td id="sp_center">';

	if (!empty($context['SPortal']['blocks'][2]))
	{
		foreach ($context['SPortal']['blocks'][2] as $block)
			template_block($block);

		if (empty($context['SPortal']['on_portal']))
			echo '
				<br class="sp_side_clear" />';
	}
}

/**
 * Display below blocks, this includes our right, bottom and footer blocks
 */
function template_portal_below()
{
	global $context, $modSettings;

	// Output all the Bottom blocks
	if (!empty($context['SPortal']['blocks'][3]))
	{
		if (empty($context['SPortal']['on_portal']) || !empty($context['SPortal']['blocks'][2]) || !empty($modSettings['articleactive']))
			echo '
				<br class="sp_side_clear" />';

		foreach ($context['SPortal']['blocks'][3] as $block)
			template_block($block);
	}

	echo '
			</td>';

	// And now all the Right Blocks
	if (!empty($modSettings['showright']) && !empty($context['SPortal']['blocks'][4]))
	{
		echo '
			<td id="sp_right"', !empty($modSettings['rightwidth']) ? ' width="' . $modSettings['rightwidth'] . '"' : '', $context['SPortal']['sides'][4]['collapsed'] && empty($modSettings['sp_disable_side_collapse']) ? ' style="display: none;"' : '', '>';

		foreach ($context['SPortal']['blocks'][4] as $block)
			template_block($block);

		echo '
			</td>';
	}

	echo '
		</tr>
	</table>';

	// Footer Blocks
	if (!empty($context['SPortal']['blocks'][6]))
	{
		echo '
	<div id="sp_footer">';

		foreach ($context['SPortal']['blocks'][6] as $block)
			template_block($block);

		echo '
	</div>
	<br />';
	}
}

/**
 * Generic template to wrap blocks in
 */
function template_block($block)
{
	global $context, $modSettings, $txt;

	if (empty($block) || empty($block['type']))
		return;

	if ($block['type'] == 'sp_boardNews')
	{
		echo '
			<div class="sp_block_section', isset($context['SPortal']['sides'][$block['column']]['last']) && $context['SPortal']['sides'][$block['column']]['last'] == $block['id'] && ($block['column'] != 2 || empty($modSettings['articleactive'])) ? '_last' : '', '">';

		$block['type']($block['parameters'], $block['id']);

		echo '
			</div>';

		return;
	}

	if (isset($txt['sp_custom_block_title_' . $block['id']]))
		$block['label'] = $txt['sp_custom_block_title_' . $block['id']];

	template_block_default($block);
}

/**
 * Wrap a block in our default wrapper
 */
function template_block_default($block)
{
	global $context, $modSettings, $settings;

	if (empty($block['style']['no_title']))
	{
		echo '
	<div class="', in_array($block['style']['title']['class'], array('titlebg', 'titlebg2')) ? 'title_bar' : 'cat_bar', '"', !empty($block['style']['title']['style']) ? ' style="' . $block['style']['title']['style'] . '"' : '', '>
		<h3 class="', $block['style']['title']['class'], '">';

		if (empty($block['force_view']))
			echo '
			<a class="sp_float_right" href="javascript:void(0);" onclick="sp_collapseBlock(\'', $block['id'], '\')"><img id="sp_collapse_', $block['id'], '" src="', $settings['images_url'], $block['collapsed'] ? '/expand.png' : '/collapse.png', '" alt="*" /></a>';

		echo '
			', parse_bbc($block['label']), '
		</h3>
	</div>';
	}

	echo '
	<div id="sp_block_' . $block['id'] . '" class="sp_block_section', isset($context['SPortal']['sides'][$block['column']]['last']) && $context['SPortal']['sides'][$block['column']]['last'] == $block['id'] && ($block['column'] != 2 || empty($modSettings['articleactive'])) ? '_last' : '', '" ', $block['collapsed'] && empty($block['force_view']) ? ' style="display: none;"' : '', '>';

	echo '
		<div', empty($block['style']['body']['class']) ? '' : ' class="' . $block['style']['body']['class'] . '"', '>';

	echo '
			<div class="', $block['type'] != 'sp_menu' ? 'sp_block' : 'sp_content_padding', '"', !empty($block['style']['body']['style']) ? ' style="' . $block['style']['body']['style'] . '"' : '', '>';

	// Call the block routine
	$block['type']($block['parameters'], $block['id']);

	echo '
			</div>
		</div>
	</div>';
}