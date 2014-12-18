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
 * Attachment Block, Displays a list of recent attachments (by name)
 *
 * @param mixed[] $parameters
 *		'limit' => Board(s) to select posts from
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
class Attachment_Recent_Block extends SP_Abstract_Block
{
	public function __construct($db = null)
	{
		$this->block_parameters = array(
			'limit' => 'int',
		);

		parent::__construct($db);
	}

	function setup($parameters, $id, $return_parameters = false)
	{
		$limit = empty($parameters['limit']) ? 5 : (int) $parameters['limit'];

		$this->data['items'] = ssi_recentAttachments($limit, array(), 'array');

		$this->setTemplate('template_sp_attachmentRecent');
	}
}

function template_sp_attachmentRecent($data)
{
	global $txt;

	// No items they can see
	if (empty($data['items']))
	{
		echo '
								', $txt['error_sp_no_attachments_found'];
		return;
	}

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