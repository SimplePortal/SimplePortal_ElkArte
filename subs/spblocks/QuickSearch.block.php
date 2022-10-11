<?php

/**
 * @package SimplePortal
 *
 * @author SimplePortal Team
 * @copyright 2015-2022 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.0
 */


/**
 * Quick Search Block, Displays a quick search box
 *
 * @param array $parameters not used in this block
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
class Quick_Search_Block extends SP_Abstract_Block
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
		// @todo isn't the search subject to verification?
		$this->setTemplate('template_sp_quickSearch');
	}
}

/**
 * Main template for this block
 */
function template_sp_quickSearch()
{
	global $txt, $scripturl;

	echo '
		<form action="', $scripturl, '?action=search;sa=results" method="post" accept-charset="UTF-8">
			<div class="centertext">
				<input type="text" name="search" value="" class="sp_search" placeholder="', $txt['search'], '" /><br />
				<input type="submit" name="submit" value="', $txt['search'], '" class="button_submit" />
				<input type="hidden" name="advanced" value="0" />
			</div>
		</form>';
}
