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
 * Top Boards Block, shows top boards by number of posts
 *
 * @param mixed[] $parameters
 *        'limit' => number of boards to show
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
class Top_Boards_Block extends SP_Abstract_Block
{
	/**
	 * Constructor, used to define block parameters
	 *
	 * @param Database|null $db
	 */
	public function __construct($db = null)
	{
		$this->block_parameters = array(
			'limit' => 'int',
		);

		parent::__construct($db);
	}

	/**
	 * Initializes a block for use.
	 *
	 * - Called from portal.subs as part of the sportal_load_blocks process
	 *
	 * @param mixed[] $parameters
	 * @param int $id
	 */
	public function setup($parameters, $id)
	{
		global $txt;

		// Use ssi to get the top boards
		$limit = !empty($parameters['limit']) ? $parameters['limit'] : 5;
		$this->data['boards'] = ssi_topBoards($limit, 'array');

		if (!empty($this->data['boards']))
		{
			end($this->data['boards']);
			$this->data['boards'][key($this->data['boards'])]['is_last'] = true;
		}

		if (empty($this->data['boards']))
		{
			$this->data['error_msg'] = $txt['error_sp_no_boards_found'];
			$this->setTemplate('template_sp_topBoards_error');
		}
		else
		{
			$this->setTemplate('template_sp_topBoards');
		}
	}
}

/**
 * Error template for this block
 *
 * @param mixed[] $data
 */
function template_sp_topBoards_error($data)
{
	echo '
		', $data['error_msg'];
}

/**
 *  Main template for this block
 *
 * @param mixed[] $data
 */
function template_sp_topBoards($data)
{
	global $txt;

	echo '
		<ul class="sp_list">';

	$embed_class = sp_embed_class('board', '', 'sp_list_top');
	foreach ($data['boards'] as $board)
	{
		echo '
			<li ', $embed_class, '>',
				$board['link'], '
			</li>
			<li class="sp_list_indent', empty($board['is_last']) ? ' sp_list_bottom' : '', ' smalltext">',
				(empty($board['num_topics']) && !empty($board['num_posts'])) ? $txt['redirects'] : ($txt['topics'] . ': ' . $board['num_topics'] . ' | ' . $txt['posts']), ': ', $board['num_posts'], '
			</li>';
	}

	echo '
		</ul>';
}