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
 * Gallery Block, show a gallery box with gallery items
 *
 * @param array $parameters
 *		'limit' => number of gallery items to show
 *		'type' => 0 recent 1 random
 *		'direction' => 0 horizontal or 1 vertical display in the block
 * @param int $id - not used in this block
 * @param bool $return_parameters if true returns the configuration options for the block
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
	 * @param array $parameters
	 * @param int $id
	 */
	public function setup($parameters, $id = 0)
	{
		global $txt;

		$limit = empty($parameters['limit']) ? 1 : (int) $parameters['limit'];
		$type = empty($parameters['type']) ? 0 : (int) $parameters['type'];
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

		// Set the appropriate template for the gallery in use
		$this->data['gallery_template'] = 'template_sp_gallery_' . $this->data['mod'];
		$this->data['items'] = $this->_getItems($limit, $type);
		$this->data['fancybox_enabled'] = !empty($this->_modSettings['fancybox_enabled']);

		// No items in the gallery?
		if (empty($this->data['items']))
		{
			$this->data['error_msg'] = $txt['error_sp_no_pictures_found'];
			$this->setTemplate('template_sp_gallery_error');
		}
		else
		{
			$this->setTemplate('template_sp_gallery');
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

		// This does exist and is available!
		if (file_exists(SOURCEDIR . '/levgal_src/LevGal-Bootstrap.php'))
		{
			$mod = 'levgal';
		}

		// This does exist for ElkArte, but can't be shared I'm afraid
		if (file_exists(SOURCEDIR . '/Aeva-Media.php'))
		{
			$mod = 'aeva_media';
		}

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
			require_once(SUBSDIR . '/Aeva-Subs-Vital.php');

			aeva_loadSettings();

			// Just images
			return aeva_getMediaItems(0, $limit, $type ? 'RAND()' : 'm.id_media DESC', true, array(), 'm.type = "image"');
		}

		if ($this->data['mod'] === 'levgal')
		{
			if (!allowedTo(array('lgal_view', 'lgal_manage')))
			{
				return array();
			}

			switch ($type)
			{
				case 0:
				default:
					$itemList = LevGal_Bootstrap::getModel('LevGal_Model_ItemList');
					$data = $itemList->getLatestItems($limit);
					break;
				case 1:
					$itemList = LevGal_Bootstrap::getModel('LevGal_Model_ItemList');
					$data = $itemList->getRandomItems($limit);
					break;
				case 2:
					$itemList = LevGal_Bootstrap::getModel('LevGal_Model_ItemList');
					$data = $itemList->getLatestImages($limit);
					break;
				case 3:
					$itemList = LevGal_Bootstrap::getModel('LevGal_Model_ItemList');
					$data = $itemList->getRandomImages($limit);
					break;
			}
		}

		return $data;
	}
}

/**
 * Error template for this block
 *
 * @param array $data
 */
function template_sp_gallery_error($data)
{
	echo '
		', $data['error_msg'];
}

/**
 * Main template for Levertine Block
 *
 * @param array $data
 */
function template_sp_gallery_levgal($item, $data)
{
	global $scripturl, $txt;

	echo '
		<a class="sp_attach_title" href="', $item['item_url'], '">', $item['item_name'], '</a>
		<br />' . ($data['fancybox_enabled']
			? '<a href="' . $item['item_base'] . '" rel="gallery" data-fancybox="1">'
			: '<a href="' . $item['item_url'] . '">') . '
			<img src="', $item['thumbnail'], '"', $item['thumb_html'] ?? '', ' alt="', $item['item_name'], '" title="', $item['item_name'], '" /></a>
		</a>
		<br />
		<div class="sp_image_topic">
		', $txt['lgal_sort_by_views'], ': ', $item['num_views'], '<br />
		', $txt['lgal_posted_by'], ' ', !empty($item['id_member']) ? '<a href="' . $scripturl . '?action=profile;u=' . $item['id_member'] . '">' . $item['poster_name'] . '</a>' : $item['poster_name'], '<br />
 		', $txt['lgal_posted_in'], ' <a href="', $item['album_url'], '">', $item['album_name'], '</a>
		</div>';
}

/**
 * Template for aeva block
 *
 * @param array $data
 */
function template_sp_gallery_aeva_media($item, $data)
{
	global $scripturl, $txt;

	echo '
		<a class="sp_attach_title" href="', $scripturl, '?action=media;sa=item;in=', $item['id'], '">', $item['title'], '</a>
		<br />' . ($data['fancybox_enabled']
			? '<a href="' . $scripturl . '?action=media;sa=media;in=' . $item['id'] . '" rel="gallery" data-fancybox="1">'
			: '<a href="' . $scripturl . '?action=media;sa=item;in=' . $item['id'] . '">') . '
			<img src="', $scripturl, '?action=media;sa=media;in=', $item['id'], ';thumb" alt="" />
		</a>
		<br />
		<div class="sp_image_topic">
		', $txt['aeva_views'], ': ', $item['views'], '<br />
		', $txt['aeva_posted_by'], ': <a href="', $scripturl, '?action=profile;u=', $item['poster_id'], '">', $item['poster_name'], '</a><br />
		', $txt['aeva_in_album'], ': <a href="', $scripturl, '?action=media;sa=album;in=', $item['id_album'], '">', $item['album_name'], '</a>
		</div>';
}

/**
 * Main wrapper template for this block
 *
 * @param array $data
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
