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
 * Generic PHP Block, creates a PHP block to do whatever you can imagine :D
 *
 * @param array $parameters
 *        'textarea' =>
 * @param int $id - not used in this block
 * @param bool $return_parameters if true returns the configuration options for the block
 */
class Php_Block extends SP_Abstract_Block
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
	public function setup($parameters, $id = 0)
	{
		$this->data['content'] = !empty($parameters['content']) ? $parameters['content'] : '';
		$this->data['content'] = trim(un_htmlspecialchars($this->data['content']));

		// Strip leading / trailing php wrapper
		if (strpos($this->data['content'], '<?php') === 0)
		{
			$this->data['content'] = substr($this->data['content'], 5);
		}

		if (substr($this->data['content'], -2) == '?>')
		{
			$this->data['content'] = substr($this->data['content'], 0, -2);
		}

		$this->setTemplate('template_sp_php');
	}

	/**
	 * Permissions check fo access to this one
	 */
	public static function permissionsRequired()
	{
		return array('admin_forum');
	}
}

/**
 * Main template for this block, outputs eval !
 *
 * @param array $data
 */
function template_sp_php($data)
{
	try
	{
		eval($data['content']);
	}
	catch (\Throwable $e)
	{
		// Pass through
	}
}
