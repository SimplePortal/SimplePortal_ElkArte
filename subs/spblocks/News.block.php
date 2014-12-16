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
 * News Block, Displays the forum news
 *
 * @param mixed[] $parameters not used in this block
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
class News_Block extends SP_Abstract_Block
{
	function setup($parameters)
	{
		$this->setTemplate('template_sp_news');
	}
}

function template_sp_news($data)
{
	global $context;

	echo '
								<div class="centertext">', $context['random_news_line'], '</div>';
}