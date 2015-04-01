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
 * Quick Search Block, Displays a quick search box
 *
 * @param mixed[] $parameters not used in this block
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
class QuickSearch_Block extends SP_Abstract_Block
{
	function setup($parameters, $id)
	{
		// @todo isn't the search subject to verification?
		$this->setTemplate('template_sp_quickSearch');
	}
}

function template_sp_quickSearch($data)
{
	global $txt, $scripturl;

	echo '
								<form action="', $scripturl, '?action=search2" method="post" accept-charset="UTF-8">
									<div class="centertext">
										<input type="text" name="search" value="" class="sp_search" /><br />
										<input type="submit" name="submit" value="', $txt['search'], '" class="button_submit" />
										<input type="hidden" name="advanced" value="0" />
									</div>
								</form>';
}