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
 * Gallery Block, show a gallery box with gallery items
 *
 * @param mixed[] $parameters
 *		'limit' => number of gallery items to show
 *		'type' =>
 *		'direction' => 0 horizontal or 1 vertical display in the block
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
class Gallery_Block extends SP_Abstract_Block
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
			'type' => 'select',
			'direction' => 'select',
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
		global  $txt, $modSettings;

		$limit = empty($parameters['limit']) ? 1 : (int) $parameters['limit'];
		$type = empty($parameters['type']) ? 0 : 1;
		$this->data['direction'] = empty($parameters['direction']) ? 0 : 1;

		// Find what gallery is installed
		$this->data['mod'] = $this->_getGallery();

		// No gallery, no images I'm afraid
		if (empty($this->data['mod']))
		{
			$this->data['error_msg'] = $txt['error_sp_no_gallery_found'];
			$this->setTemplate('template_sp_gallery_error');

			return;
		}

		// Set the approraite template for the gallery in use
		$this->data['gallery_template'] = 'template_sp_gallery_' . $this->data['mod'];

		$this->data['items'] = $this->_getItems($limit, $type);
		$this->data['fancybox_enabled'] = !empty($modSettings['fancybox_enabled']);

		// No items in the gallery?
		if (empty($this->data['items']))
		{
			$this->data['error_msg'] = $txt['error_sp_no_pictures_found'];
			$this->setTemplate('template_sp_gallery_error');

			return;
		}
	}

	/**
	 * Returns of list of gallery's that this block can work with
	 *
	 * @return string
	 */
	protected function _getGallery()
	{
		$mod = '';

		// Right now we only know about one gallery, but more may be added,
		// maybe even ones we can tell folks about :P
		if (file_exists(SOURCEDIR . '/Aeva-Media.php'))
			$mod = 'aeva_media';

		return $mod;
	}

	/**
	 * Load a set of media items from the gallery
	 *
	 * @param int $limit
	 * @param string $type
	 */
	protected function _getItems($limit, $type)
	{
		$data = array();

		if ($this->data['mod'] === 'aeva_media')
		{
			require_once(SUBSDIR . '/Aeva-Subs.php');

			$data = aeva_getMediaItems(0, $limit, $type ? 'RAND()' : 'm.id_media DESC');
		}

		return $data;
	}
}

/**
 * Error template for this block
 *
 * @param mixed[] $data
 */
function template_sp_gallery_error($data)
{
	echo '
		', $data['error_msg'];
}

/**
 * Main template for aeva block
 *
 * @param mixed[] $data
 */
function template_sp_gallery_aeva_media($item, $data)
{
	global $scripturl, $txt;

	echo '
		<a href="', $scripturl, '?action=media;sa=item;in=', $item['id'], '">', $item['title'], '</a><br />' . ($data['fancybox_enabled'] ? '
		<a href="' . $scripturl . '?action=media;sa=media;in=' . $item['id'] . '" rel="gallery" class="fancybox">' : '
		<a href="' . $scripturl . '?action=media;sa=item;in=' . $item['id'] . '">') . '
		<img src="', $scripturl, '?action=media;sa=media;in=', $item['id'], ';thumb" alt="" /></a><br />
		', $txt['aeva_views'], ': ', $item['views'], '<br />
		', $txt['aeva_posted_by'], ': <a href="', $scripturl, '?action=profile;u=', $item['poster_id'], '">', $item['poster_name'], '</a><br />
		', $txt['aeva_in_album'], ': <a href="', $scripturl, '?action=media;sa=album;in=', $item['id_album'], '">', $item['album_name'], '</a>';
}

/**
 * Main wrapper template for this block
 *
 * @param mixed[] $data
 */
function template_sp_gallery($data)
{
	// We have gallery items to show!
	echo '
		<table class="sp_auto_align">', $data['direction'] ? '
			<tr>' : '';

	$before = $data['direction'] ? '' : '
			<tr>';

	$after = $data['direction'] ? '' : '
			</tr>';

	foreach ($data['items'] as $item)
	{
		echo $before, '
				<td>
					<div class="sp_image smalltext">';

		$data['gallery_template']($item, $data);

		echo '
					</div>
				</td>', $after;
	}

	echo $data['direction'] ? '
			</tr>' : '', '
		</table>';
}