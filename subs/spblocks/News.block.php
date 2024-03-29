<?php

/**
 * @package SimplePortal
 *
 * @author SimplePortal Team
 * @copyright 2015-2023 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.0
 */


/**
 * News Block, Displays the forum news
 *
 * @param array $parameters not used in this block
 * @param int $id - not used in this block
 * @param bool $return_parameters if true returns the configuration options for the block
 */
class News_Block extends SP_Abstract_Block
{
	/**
	 * Initializes a block for use.
	 *
	 * - Called from portal.subs as part of the sportal_load_blocks process
	 *
	 * @param array $parameters
	 * @param int $id
	 */
	public function setup($parameters, $id)
	{
		$this->setTemplate('template_sp_news');
	}
}

/**
 * Main template for this block
 *
 * @param array $data
 */
function template_sp_news($data)
{
	global $context;

	echo '
		<div class="centertext">', !empty($context['random_news_line']) ? $context['random_news_line'] : '', '</div>';
}
