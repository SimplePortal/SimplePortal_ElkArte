<?php

/**
 * @package SimplePortal
 *
 * @author SimplePortal Team
 * @copyright 2015-2021 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.1
 */

use ElkArte\ValuesContainer;

/**
 * Abstract Simple Portal block
 *
 * - Implements Sp_Block
 * - Sets base functionality for use in blocks
 */
abstract class SP_Abstract_Block
{
	/** @var \Database*/
	protected $_db;

	/** @var array|\ElkArte\ValuesContainer */
	protected $_modSettings = array();

	/** @var array Block parameters */
	protected $block_parameters = array();

	/** @var array Data array for use in the blocks */
	protected $data = array();

	/** @var string Name of the template function to call */
	protected $template = '';

	/** @var array If the block supports refreshing, sets the time in seconds */
	protected $refresh = array();

	/**
	 * Class constructor, makes db and modSettings available to the blocks
	 *
	 * - Called by sp_instantiate_block function (via block constructor)
	 *
	 * @param Database|null $db
	 */
	public function __construct($db = null)
	{
		global $modSettings;

		$this->_db = $db;

		$this->_modSettings = new ValuesContainer($modSettings ?: array());
	}

	/**
	 * Returns block parameters used to build the ACP block "options" configuration page
	 *
	 * - Possible Params include boards|boards_select|check|int|select|text|textarea
	 *
	 * @return array
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
	 * Called as part of the sportal_load_blocks process to initiate a block prior
	 * to its being displayed.
	 *
	 * @param array $parameters
	 * @param int $id
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
	 * Validates that a user can access a block, defaults to yes they can
	 *
	 * @return string[]
	 */
	public static function permissionsRequired()
	{
		return array();
	}

	/**
	 * Sets the name of the block in $txt string for use with custom
	 * blocks. $txt['sp_function_Block_Name_label']
	 *
	 * @return string
	 */
	public static function blockName()
	{
		return '';
	}

	/**
	 * Sets the description of the block in $txt for use with custom
	 * blocks.  $txt['sp_function_Block_Name_desc']
	 *
	 * @return string
	 */
	public static function blockDescription()
	{
		return '';
	}

	/**
	 * Adds javascript to make a block refresh call in the background
	 */
	public function auto_refresh()
	{
		// Lets be reasonable on the refresh, lets not beat on the server
		$refresh = (max((int) $this->refresh['refresh_value'], 30)) * 1000;

		addInlineJavascript('
			$(document).ready(function()
			{
				let $block = $("#sp_block_' . (int) $this->refresh['id'] . '"),
					$container = $block.find("' . $this->refresh['class'] . '"),
					spRefreshParams = {"block" : ' . (int) $this->refresh['id'] . '};

				spRefreshParams[elk_session_var] = elk_session_id;

				setInterval(function()
				{
					$.ajax({
						type: "POST",
						url: elk_prepareScriptUrl(sp_script_url) + "action=PortalRefresh;sa=' . $this->refresh['sa'] . ';api",
						data: spRefreshParams,
					})
					.done(function(result, textStatus) {
						if (textStatus === "success" && result !== "")
						{
							$container.html(result);
						}
					});
				}, ' . $refresh . ');
			});
		');
	}
}
