<?php

/**
 * @package SimplePortal
 *
 * @author SimplePortal Team
 * @copyright 2015 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.0 Beta 2
 */

if (!defined('ELK'))
{
	die('No access...');
}

/**
 * Menu Block, creates a sidebar menu block based on the system main menu
 * @todo needs updating so it knows right vs left block for the flyout
 *
 * @param mixed[] $parameters -  not used in this block
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
class Menu_Block extends SP_Abstract_Block
{
	/**
	 * Initializes a block for use.
	 *
	 * - Called from portal.subs as part of the sportal_load_blocks process
	 *
	 * @param mixed[] $parameters
	 * @param int $id
	 */
	public function setup($parameters, $id)
	{
		global $context;

		if (empty($context['menu_buttons']))
		{
			setupMenuContext();
		}

		$this->setTemplate('template_sp_menu');
	}
}

/**
 * Main template for this block
 *
 * @param mixed[] $data
 */
function template_sp_menu($data)
{
	global $context;

	echo '
		<ul id="sp_menu" class="sp_list">';

	foreach ($context['menu_buttons'] as $act => $button)
	{
		echo '
			<li ', sp_embed_class('dot'), '>
				<a title="', strip_tags($button['title']), '" href="', $button['href'], '">', ($button['active_button'] ? '<strong>' : ''), $button['title'], ($button['active_button'] ? '</strong>' : ''), '</a>';

		if (!empty($button['sub_buttons']))
		{
			echo '
				<ul class="sp_list">';

			foreach ($button['sub_buttons'] as $sub_button)
			{
				echo '
					<li ', sp_embed_class('dot', '', 'sp_list_indent'), '>
						<a title="', $sub_button['title'], '" href="', $sub_button['href'], '">', $sub_button['title'], '</a></li>';
}

			echo '
				</ul>';
		}

		echo '</li>';
	}

	echo '
		</ul>';

	// Superfish the menu
	$javascript = "
	$(document).ready(function() {
		if (use_click_menu)
			$('#sp_menu').superclick({speed: 150, animation: {opacity:'show', height:'toggle'}, speedOut: 0, activeClass: 'sfhover'});
		else
			$('#sp_menu').superfish({delay : 300, speed: 175, hoverClass: 'sfhover'});
	});";

	addInlineJavascript($javascript, true);
}