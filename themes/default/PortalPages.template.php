<?php

/**
 * @package SimplePortal ElkArte
 *
 * @author SimplePortal Team
 * @copyright 2015 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.1.0 Beta 1
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
		<div class="sp_content_padding">', $txt['error_sp_no_pages'], '</div>';
	}

	foreach ($context['SPortal']['pages'] as $page)
	{
		echo '
			<div class="sp_content_padding">', $page['link'], ' - ', sprintf($page['views'] == 1 ? $txt['sp_viewed_time'] : $txt['sp_viewed_times'], $page['views']) ,'</div>';
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
			<div', !empty($context['SPortal']['page']['style']['title']['style']) ? ' style="' . $context['SPortal']['page']['style']['title']['style'] . '"' : '', '>
				<h3', strpos($context['SPortal']['page']['style']['title']['class'], 'custom') === false ? ' class="' . $context['SPortal']['page']['style']['title']['class'] . '"' : '', '>
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