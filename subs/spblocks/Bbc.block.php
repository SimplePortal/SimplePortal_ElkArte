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
 * Generic BBC Block, creates a BBC formatted block with parse_bbc
 *
 * @param mixed[] $parameters -  not used in this block
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
class Bbc_Block extends SP_Abstract_Block
{
	public function __construct($db = null)
	{
		$this->block_parameters = array(
			'content' => 'bbc',
		);

		parent::__construct($db);
	}

	function setup($parameters)
	{
		$this->data['content'] = !empty($parameters['content']) ? parse_bbc($parameters['content']) : '';

		$this->setTemplate('template_sp_bbc');
	}
}

function template_sp_bbc($data)
{
	echo '
								', $data['content'];
}