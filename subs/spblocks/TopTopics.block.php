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
 * Top Topics Block, shows top topics by number of view or number of posts
 *
 * @param mixed[] $parameters
 *		'limit' => number of posts to show
 *		'type' => 0 replies or 1 views
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
class Whos_Online_Block extends SP_Abstract_Block
{
	public function __construct($db = null)
	{
		$this->block_parameters = array(
			'type' => 'select',
			'limit' => 'int',
		);

		parent::__construct($db);
	}

	function setup($parameters)
	{
		global $user_info, $user_info;

		$type = !empty($parameters['type']) ? $parameters['type'] : 0;
		$limit = !empty($parameters['limit']) ? $parameters['limit'] : 5;

		// Use the ssi function to get the data
		$this->data['topics'] = ssi_topTopics($type ? 'views' : 'replies', $limit, 'array');

		if (!empty($this->data['topics']))
		{
			end($this->data['topics']);
			$this->data['topics'][key($this->data['topics'])]['is_last'] = true;
		}

		$this->setTemplate('template_sp_topTopics');
	}
}

function template_sp_topTopics($data)
{
	global $txt;

	if (empty($data['topics']))
	{
		echo '
								', $txt['error_sp_no_topics_found'];

		return;
	}

	echo '
								<ul class="sp_list">';

	$embed_class = sp_embed_class('topic', '', 'sp_list_top');
	foreach ($data['topics'] as $topic)
		echo '
									<li ', $embed_class, '>', $topic['link'], '</li>
									<li class="sp_list_indent', empty($topic['is_last']) ? ' sp_list_bottom' : '', ' smalltext">', $txt['replies'], ': ', $topic['num_replies'], ' | ', $txt['views'], ': ', $topic['num_views'], '</li>';

	echo '
								</ul>';
}