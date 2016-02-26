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
 * Image Attachment Block, Displays a list of recent post image attachments
 *
 * @param mixed[] $parameters
 *        'limit' => Board(s) to select posts from
 *        'direction' => 0 Horizontal or 1 Vertical display
 *        'disablePoster' => don't show the poster of the attachment
 *        'disableDownloads' => don't show a download link
 *        'disableLink' => don't show a link to the post
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
class Attachment_Image_Block extends SP_Abstract_Block
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
			'direction' => 'select',
			'disablePoster' => 'check',
			'disableDownloads' => 'check',
			'disableLink' => 'check',
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
		$type = array('jpg', 'jpeg', 'png', 'gif', 'bmp');

		$this->data['direction'] = empty($parameters['direction']) ? 0 : 1;
		$this->data['showPoster'] = empty($parameters['disablePoster']);
		$this->data['showDownloads'] = empty($parameters['disableDownloads']);
		$this->data['showLink'] = empty($parameters['disableLink']);

		// Let ssi get the attachments
		$this->data['items'] = ssi_recentAttachments($limit, $type, 'array');

		// No attachments, at least none that they can see
		if (empty($this->data['items']))
		{
			$this->data['error_msg'] = $txt['error_sp_no_attachments_found'];
			$this->setTemplate('template_sp_attachmentImage_error');

			return;
		}

		// Color Id's
		$this->_color_ids();

		$this->setTemplate('template_sp_attachmentImage');
	}

	/**
	 * Provide the color profile id's
	 */
	private function _color_ids()
	{
		global $color_profile;

		$color_ids = array();
		foreach ($this->data['items'] as $item)
		{
			$color_ids[] = $item['member']['id'];
		}

		if (sp_loadColors($color_ids) !== false)
		{
			foreach ($this->data['items'] as $k => $p)
			{
				if (!empty($color_profile[$p['member']['id']]['link']))
				{
					$this->data['items'][$k]['member']['link'] = $color_profile[$p['member']['id']]['link'];
				}
			}
		}
	}
}

/**
 * Error template for this block
 *
 * @param mixed[] $data
 */
function template_sp_attachmentImage_error($data)
{
	echo $data['error_msg'];
}

/**
 * Main template for this block
 *
 * @param mixed[] $data
 */
function template_sp_attachmentImage($data)
{
	global $scripturl, $txt;

	// Build the output for display
	echo '
		<table class="sp_auto_align">', $data['direction'] ? '
			<tr>' : '';

	$before = $data['direction'] ? '' : '
			<tr>';
	$after = $data['direction'] ? '' : '
			</tr>';

	// For each image that was returned from ssi
	foreach ($data['items'] as $id => $item)
	{
		if (empty($item['file']['image']))
		{
			continue;
		}

		echo $before, '
				<td>
					<div class="sp_image smalltext">',
						($data['showLink'] ? '<a href="' . $item['file']['href'] . '">' . str_replace(array('_', '-'), ' ', $item['file']['filename']) . '</a><br />' : '') . '
						<a id="link_' . $id . '" href="' . $scripturl . '?action=dlattach;topic=' . $item['topic']['id'] . '.0;attach=' . $id . ';image">
						<img id="thumb_' . $id . '" src="' . $scripturl . '?action=dlattach;topic=' . $item['topic']['id'] . '.0;attach=' . $item['file']['image']['id'] . ';image" alt="' . $item['file']['filename'] . '" /></a>
						<br />' .
						($data['showLink'] ? '<div class="sp_image_topic">' . $item['topic']['link'] . '</div>' : '') .
						($data['showDownloads'] ? $txt['downloads'] . ': ' . $item['file']['downloads'] . '<br />' : ''),
						($data['showPoster'] ? $txt['posted_by'] . ': ' . $item['member']['link'] : ''), '
					</div>
				</td>', $after;
	}

	echo $data['direction'] ? '
			</tr>' : '';

	echo '
		</table>';
}