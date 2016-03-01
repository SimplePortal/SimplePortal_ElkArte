<?php

/**
 * @package SimplePortal
 *
 * @author SimplePortal Team
 * @copyright 2015 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.0 Beta 2
 */

if (!defined('ELK'))
{
	die('No access...');
}

/**
 * Abstract Simple Portal block
 *
 * - Implements Sp_Block
 * - Sets base functionality for use in blocks
 */
abstract class SP_Abstract_Block
{
	/**
	 * Database object
	 * @var object
	 */
	protected $_db = null;

	/**
	 * Block parameters
	 * @var array
	 */
	protected $block_parameters = array();

	/**
	 * Data array for use in the blocks
	 * @var mixed[]
	 */
	protected $data = array();

	/**
	 * Name of the template function to call
	 * @var string
	 */
	protected $template = '';

	/**
	 * If the block supports refreshing, sets the time in seconds
	 * @var array
	 */
	protected $refresh = array();

	/**
	 * Class constructor, makes db available
	 *
	 * @param Database|null $db
	 */
	public function __construct($db = null)
	{
		$this->_db = $db;
	}

	/**
	 * Returns optional block parameters
	 *
	 * @return mixed[]
	 */
	public function parameters()
	{
		return $this->block_parameters;
	}

	/**
	 * Sets the template name that will be called via render
	 *
	 * @param string $template
	 */
	public function setTemplate($template)
	{
		$this->template = $template;
	}

	/**
	 * @todo
	 */
	abstract public function setup($parameters, $id);

	/**
	 * Renders a block with a given template and data
	 */
	public function render()
	{
		if (is_callable($this->template))
		{
			call_user_func_array($this->template, array($this->data));
		}
	}

	/**
	 * Validates that a user can access a block
	 *
	 * @return string[]
	 */
	public static function permissionsRequired()
	{
		return array();
	}

	/**
	 * Adds javascript to make a block refresh call in the background
	 */
	public function auto_refresh()
	{
		// Lets be reasonable on the refresh, let not beat on the server
		$refresh = ((int) $this->refresh['refresh_value'] < 30 ? 30 : (int) $this->refresh['refresh_value']) * 1000;

		addInlineJavascript('
			$(document).ready(function()
			{
				var $block = $("#sp_block_' . (int) $this->refresh['id'] . '"),
					$container = $block.find("' . $this->refresh['class'] . '"),
					params = {"block" : ' . (int) $this->refresh['id'] . '};

				params[elk_session_var] = elk_session_id;

				setInterval(function()
				{
					$container.load(
						elk_prepareScriptUrl(sp_script_url) + "action=PortalRefresh;sa=' . $this->refresh['sa'] . ';api",
						params);
				}, ' . $refresh . ');
			});
		');
	}
}