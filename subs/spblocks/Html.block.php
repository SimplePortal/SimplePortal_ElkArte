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
 * Generic HTML Block, creates a formatted block with HTML
 *
 * @param array $parameters -  not used in this block
 * @param int $id - not used in this block
 * @param bool $return_parameters if true returns the configuration options for the block
 */
class Html_Block extends SP_Abstract_Block
{
	/**
	 * Constructor, used to define block parameters
	 *
	 * @param Database|null $db
	 */
	public function __construct($db = null)
	{
		$this->block_parameters = array(
			'content' => 'textarea',
		);

		parent::__construct($db);
	}

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
		$this->data['content'] = !empty($parameters['content']) ? un_htmlspecialchars($parameters['content']) : '';

		$this->setTemplate('template_sp_html');
	}
}

/**
 * Main template for this block, simply outputs the html
 *
 * @param array $data
 */
function template_sp_html($data)
{
	echo '
		', $data['content'];
}
