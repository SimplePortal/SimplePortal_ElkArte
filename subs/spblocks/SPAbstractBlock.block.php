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
 * Abstract Simple Portal block
 */
abstract class SP_Abstract_Block // implements Sp_Block
{
	protected $_db = null;
	protected $block_parameters = array();
	protected $data = array();
	protected $template = '';

	public function __construct($db = null)
	{
		$this->_db = $db;
	}

	public function parameters()
	{
		return $this->block_parameters;
	}

	public function setTemplate($template)
	{
		$this->template = $template;
	}

	abstract public function setup($parameters);

	public function render()
	{
		if (is_callable($this->template))
			call_user_func_array($this->template, $this->data);
	}
}