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

function template_view_pages()
{
	global $context, $txt;

	echo '
	<div id="sp_view_pages">
		<h3 class="category_header">
			', $context['page_title'], '
		</h3>';

	if (empty($context['SPortal']['pages']))
	{
		echo '
		<div class="windowbg2">
			<div class="sp_content_padding">', $txt['error_sp_no_pages'], '</div>
		</div>';
	}

	foreach ($context['SPortal']['pages'] as $page)
	{
		echo '
		<div class="windowbg2">
			<div class="sp_content_padding">', $page['link'], ' - ', sprintf($page['views'] == 1 ? $txt['sp_viewed_time'] : $txt['sp_viewed_times'], $page['views']) ,'</div>
		</div>';
	}

	echo '
	</div>';
}

function template_view_page()
{
	global $context;

	if (empty($context['SPortal']['page']['style']['no_title']))
	{
		echo '
			<div class="', in_array($context['SPortal']['page']['style']['title']['class'], array('titlebg', 'titlebg2')) ? 'title_bar' : 'cat_bar', '"', !empty($context['SPortal']['page']['style']['title']['style']) ? ' style="' . $context['SPortal']['page']['style']['title']['style'] . '"' : '', '>
				<h3 class="', $context['SPortal']['page']['style']['title']['class'], '">
					', $context['SPortal']['page']['title'], '
				</h3>
			</div>';
	}

	echo '
				<div class="', $context['SPortal']['page']['style']['body']['class'], '">
					<div class="sp_content_padding"', !empty($context['SPortal']['page']['style']['body']['style']) ? ' style="' . $context['SPortal']['page']['style']['body']['style'] . '"' : '', '>',
						$context['SPortal']['page']['body'], '
					</div>
				</div>';
}