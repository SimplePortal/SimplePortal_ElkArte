<?php

/**
 * @package SimplePortal
 *
 * @author SimplePortal Team
 * @copyright 2015-2023 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.0
 */

use BBC\ParserWrapper;

/**
 * Generic BBC Block, creates a BBC formatted block with parse_bbc
 *
 * @param array $parameters -  not used in this block
 * @param int $id - not used in this block
 * @param bool $return_parameters if true returns the configuration options for the block
 */
class Bbc_Block extends SP_Abstract_Block
{
	/**
	 * Constructor, used to define block parameters
	 *
	 * @param Database|null $db
	 */
	public function __construct($db = null)
	{
		$this->block_parameters = array(
			'content' => 'bbc',
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
		$parser = ParserWrapper::instance();
		$this->data['content'] = !empty($parameters['content']) ? $parser->parseMessage($parameters['content'], true) : '';

		$this->setTemplate('template_sp_bbc');
	}
}

/**
 * Main template for this block
 *
 * @param array $data
 */
function template_sp_bbc($data)
{
	echo $data['content'];
}
