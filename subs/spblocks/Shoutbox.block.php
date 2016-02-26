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
 * Shoutbox Block, show the shoutbox thoughts box
 *
 * @param mixed[] $parameters
 *        'shoutbox' => list of shoutboxes to choose from
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
class Shoutbox_Block extends SP_Abstract_Block
{
	/**
	 * Constructor, used to define block parameters
	 *
	 * @param Database|null $db
	 */
	public function __construct($db = null)
	{
		require_once(SUBSDIR . '/PortalShoutbox.subs.php');

		$this->block_parameters = array(
			'shoutbox' => array(),
		);

		parent::__construct($db);
	}

	/**
	 * Returns optional block parameters
	 *
	 * @return mixed[]
	 */
	public function parameters()
	{
		global $scripturl, $txt;

		$shoutboxes = sportal_get_shoutbox();
		$in_use = array();

		$request = $this->_db->query('', '
			SELECT
				id_block, value
			FROM {db_prefix}sp_parameters
			WHERE variable = {string:name}',
			array(
				'name' => 'shoutbox',
			)
		);
		while ($row = $this->_db->fetch_assoc($request))
		{
			if (empty($_REQUEST['block_id']) || $_REQUEST['block_id'] != $row['id_block'])
			{
				$in_use[] = $row['value'];
			}
		}
		$this->_db->free_result($request);

		// Load up all the shoutboxes that are NOT being used
		foreach ($shoutboxes as $shoutbox)
		{
			if (!in_array($shoutbox['id'], $in_use))
			{
				$this->block_parameters['shoutbox'][$shoutbox['id']] = $shoutbox['name'];
			}
		}

		if (empty($this->block_parameters['shoutbox']))
		{
			fatal_error(allowedTo(array('sp_admin', 'sp_manage_shoutbox')) ? $txt['error_sp_no_shoutbox'] . '<br />' . sprintf($txt['error_sp_no_shoutbox_sp_moderator'], $scripturl . '?action=admin;area=portalshoutbox;sa=add') : $txt['error_sp_no_shoutbox_normaluser'], false);
		}

		return $this->block_parameters;
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
		global $context, $modSettings, $user_info, $settings, $txt, $editortxt;

		loadTemplate('PortalShoutbox');
		loadLanguage('Editor');
		loadLanguage('Post');

		$this->data = sportal_get_shoutbox($parameters['shoutbox'], true, true);

		// To add a shoutbox you must have one or more defined for use
		if (empty($this->data))
		{
			$this->data['error_msg'] = $txt['error_sp_shoutbox_not_exist'];
			$this->setTemplate('template_sp_shoutbox_error');

			return;
		}

		// Going to add to the shoutbox \
		if (!empty($_POST['new_shout']) && !empty($_POST['submit_shout']) && !empty($_POST['shoutbox_id']) && $_POST['shoutbox_id'] == $this->data['id'])
		{
			// Make sure things are in order
			checkSession();
			is_not_guest();

			if (!($flood = sp_prevent_flood('spsbp', false)))
			{
				require_once(SUBSDIR . '/Post.subs.php');

				$_POST['new_shout'] = Util::htmlspecialchars(trim($_POST['new_shout']));
				preparsecode($_POST['new_shout']);

				if (!empty($_POST['new_shout']))
				{
					sportal_create_shout($this->data, $_POST['new_shout']);
				}
			}
			else
			{
				$this->data['warning'] = $flood;
			}
		}

		// Can they moderate the shoutbox (delete shouts?)
		$can_moderate = allowedTo('sp_admin') || allowedTo('sp_manage_shoutbox');
		if (!$can_moderate && !empty($this->data['moderator_groups']))
		{
			$can_moderate = count(array_intersect($user_info['groups'], $this->data['moderator_groups'])) > 0;
		}

		$shout_parameters = array(
			'limit' => $this->data['num_show'],
			'bbc' => $this->data['allowed_bbc'],
			'reverse' => $this->data['reverse'],
			'cache' => $this->data['caching'],
			'can_moderate' => $can_moderate,
		);
		$this->data['shouts'] = sportal_get_shouts($this->data['id'], $shout_parameters);

		$this->data['warning'] = parse_bbc($this->data['warning']);
		$context['can_shout'] = $context['user']['is_logged'];

		if ($context['can_shout'])
		{
			// Set up the smiley tags for the shoutbox
			$settings['smileys_url'] = $modSettings['smileys_url'] . '/' . $user_info['smiley_set'];
			$this->data['smileys'] = array('normal' => array(), 'popup' => array());
			if (empty($modSettings['smiley_enable']))
			{
				$this->data['smileys']['normal'] = $this->_smileys();
			}
			else
			{
				if (($temp = cache_get_data('shoutbox_smileys', 3600)) == null)
				{
					$request = $this->_db->query('', '
						SELECT
							code, filename, description, smiley_row, hidden
						FROM {db_prefix}smileys
						WHERE hidden IN ({array_int:hidden})
						ORDER BY smiley_row, smiley_order',
						array(
							'hidden' => array(0, 2),
						)
					);
					while ($row = $this->_db->fetch_assoc($request))
					{
						$row['filename'] = htmlspecialchars($row['filename']);
						$row['description'] = htmlspecialchars($row['description']);
						$row['code'] = htmlspecialchars($row['code']);
						$this->data['smileys'][empty($row['hidden']) ? 'normal' : 'popup'][] = $row;
					}
					$this->_db->free_result($request);

					cache_put_data('shoutbox_smileys', $this->data['smileys'], 3600);
				}
				else
				{
					$this->data['smileys'] = $temp;
				}
			}

			foreach (array_keys($this->data['smileys']) as $location)
			{
				$n = count($this->data['smileys'][$location]);
				for ($i = 0; $i < $n; $i++)
				{
					$this->data['smileys'][$location][$i]['code'] = addslashes($this->data['smileys'][$location][$i]['code']);
					$this->data['smileys'][$location][$i]['js_description'] = addslashes($this->data['smileys'][$location][$i]['description']);
				}

				if (!empty($this->data['smileys'][$location]))
				{
					$this->data['smileys'][$location][$n - 1]['last'] = true;
				}
			}

			// Basic shoutbox bbc we allow
			$this->data['bbc'] = $this->_bbc();
		}

		$this->setTemplate('template_shoutbox_embed');
	}

	private function _bbc()
	{
		global $editortxt;

		return array(
			'bold' => array('code' => 'b', 'before' => '[b]', 'after' => '[/b]', 'description' => $editortxt['Bold']),
			'italicize' => array('code' => 'i', 'before' => '[i]', 'after' => '[/i]', 'description' => $editortxt['Italic']),
			'underline' => array('code' => 'u', 'before' => '[u]', 'after' => '[/u]', 'description' => $editortxt['Underline']),
			'strike' => array('code' => 's', 'before' => '[s]', 'after' => '[/s]', 'description' => $editortxt['Strikethrough']),
			'pre' => array('code' => 'pre', 'before' => '[pre]', 'after' => '[/pre]', 'description' => $editortxt['Preformatted Text']),
			'img' => array('code' => 'img', 'before' => '[img]', 'after' => '[/img]', 'description' => $editortxt['Insert an image']),
			'url' => array('code' => 'url', 'before' => '[url]', 'after' => '[/url]', 'description' => $editortxt['Insert a link']),
			'email' => array('code' => 'email', 'before' => '[email]', 'after' => '[/email]', 'description' => $editortxt['Insert an email']),
			'sup' => array('code' => 'sup', 'before' => '[sup]', 'after' => '[/sup]', 'description' => $editortxt['Superscript']),
			'sub' => array('code' => 'sub', 'before' => '[sub]', 'after' => '[/sub]', 'description' => $editortxt['Subscript']),
			'tele' => array('code' => 'tt', 'before' => '[tt]', 'after' => '[/tt]', 'description' => $editortxt['Teletype']),
			'code' => array('code' => 'code', 'before' => '[code]', 'after' => '[/code]', 'description' => $editortxt['Code']),
			'quote' => array('code' => 'quote', 'before' => '[quote]', 'after' => '[/quote]', 'description' => $editortxt['Insert a Quote']),
		);
	}

	/**
	 * Load up the standard smileys for use
	 * @return array
	 */
	private function _smileys()
	{
		global $txt;

		return array(
			array('code' => ':)', 'filename' => 'smiley.gif', 'description' => $txt['icon_smiley']),
			array('code' => ';)', 'filename' => 'wink.gif', 'description' => $txt['icon_wink']),
			array('code' => ':D', 'filename' => 'cheesy.gif', 'description' => $txt['icon_cheesy']),
			array('code' => ';D', 'filename' => 'grin.gif', 'description' => $txt['icon_grin']),
			array('code' => '>:(', 'filename' => 'angry.gif', 'description' => $txt['icon_angry']),
			array('code' => ':(', 'filename' => 'sad.gif', 'description' => $txt['icon_sad']),
			array('code' => ':o', 'filename' => 'shocked.gif', 'description' => $txt['icon_shocked']),
			array('code' => '8)', 'filename' => 'cool.gif', 'description' => $txt['icon_cool']),
			array('code' => '???', 'filename' => 'huh.gif', 'description' => $txt['icon_huh']),
			array('code' => '::)', 'filename' => 'rolleyes.gif', 'description' => $txt['icon_rolleyes']),
			array('code' => ':P', 'filename' => 'tongue.gif', 'description' => $txt['icon_tongue']),
			array('code' => ':-[', 'filename' => 'embarrassed.gif', 'description' => $txt['icon_embarrassed']),
			array('code' => ':-X', 'filename' => 'lipsrsealed.gif', 'description' => $txt['icon_lips']),
			array('code' => ':-\\', 'filename' => 'undecided.gif', 'description' => $txt['icon_undecided']),
			array('code' => ':-*', 'filename' => 'kiss.gif', 'description' => $txt['icon_kiss']),
			array('code' => ':\'(', 'filename' => 'cry.gif', 'description' => $txt['icon_cry'])
		);
	}
}

/**
 * Error template for this block
 *
 * @param mixed[] $data
 */
function template_sp_shoutbox_error($data)
{
	echo '
		', $data['error_msg'];
}