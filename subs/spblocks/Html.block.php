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
 * Generic HTML Block, creates a formatted block with HTML
 *
 * @param mixed[] $parameters -  not used in this block
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
class Html_Block extends SP_Abstract_Block
{
	public function __construct($db = null)
	{
		$this->block_parameters = array(
			'content' => 'textarea',
		);

		parent::__construct($db);
	}

	function setup($parameters, $id)
	{
		$this->data['content'] = !empty($parameters['content']) ? un_htmlspecialchars($parameters['content']) : '';

		$this->setTemplate('template_sp_html');
	}
}

function template_sp_html($data)
{
	echo '
								', $data['content'];
}
