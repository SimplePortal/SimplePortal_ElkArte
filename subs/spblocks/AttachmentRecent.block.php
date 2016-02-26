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
	die('No access...');

/**
 * Attachment Block, Displays a list of recent attachments (by name)
 *
 * @param mixed[] $parameters
 *		'limit' => Board(s) to select posts from
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
class Attachment_Recent_Block extends SP_Abstract_Block
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

		$limit = empty($parameters['limit']) ? 5 : (int) $parameters['limit'];

		$this->data['items'] = ssi_recentAttachments($limit, array(), 'array');

		// No attachments, at least none that they can see
		if (empty($this->data['items']))
		{
			$this->data['error_msg'] = $txt['error_sp_no_attachments_found'];
			$this->setTemplate('template_sp_attachmentRecent_error');

			return;
		}

		$this->setTemplate('template_sp_attachmentRecent');
	}
}

/**
 * Error template for this block
 */
function template_sp_attachmentRecent_error()
{
	global $txt;

	echo '
								', $txt['error_sp_no_attachments_found'];
}

/**
 * Main template for this block
 *
 * @param mixed[] $data
 */
function template_sp_attachmentRecent($data)
{
	global $txt;

	echo '
								<ul class="sp_list">';

	$embed_class = sp_embed_class('attach');
	foreach ($data['items'] as $item)
		echo '
									<li ', $embed_class, '>
										<a href="', $item['file']['href'], '">', $item['file']['filename'], '</a>
									</li>
									<li class="smalltext">', $txt['downloads'], ': ', $item['file']['downloads'], '</li>
									<li class="smalltext">', $txt['filesize'], ': ', $item['file']['filesize'], '</li>';

	echo '
								</ul>';
}