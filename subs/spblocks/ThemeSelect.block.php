<?php

/**
 * @package SimplePortal
 *
 * @author SimplePortal Team
 * @copyright 2015-2023 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.2
 */


/**
 * Theme Selection Block, Displays themes available for user selection
 *
 * @param array $parameters not used in this block
 * @param int $id - not used in this block
 * @param bool $return_parameters if true returns the configuration options for the block
 */
class Theme_Select_Block extends SP_Abstract_Block
{
	/**
	 * Constructor, used to define block parameters
	 *
	 * @param Database|null $db
	 */
	public function __construct($db = null)
	{
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
	public function setup($parameters, $id)
	{
		global $user_info, $settings, $language, $txt, $modSettings;

		// Going to need some help to pick themes
		loadLanguage('Profile');
		loadLanguage('ManageThemes');
		require_once(SUBSDIR . '/Themes.subs.php');

		if (!empty($_SESSION['id_theme']) && (!empty($this->_modSettings['theme_allow']) || allowedTo('admin_forum')))
		{
			$current_theme = (int) $_SESSION['id_theme'];
		}
		else
		{
			$current_theme = $user_info['theme'];
		}

		// Load in all the themes in the system
		$current_theme = empty($current_theme) ? -1 : $current_theme;
		$available_themes = installedThemes();
		$knownThemes = !empty($modSettings['knownThemes']) ? explode(',', $modSettings['knownThemes']) : array();
		$knownThemes = array_map('intval', $knownThemes);

		// Set the guest theme
		if (!isset($available_themes[$this->_modSettings['theme_guests']]))
		{
			$available_themes[0] = array(
				'num_users' => 0
			);
			$guest_theme = 0;
		}
		else
		{
			$guest_theme = $this->_modSettings['theme_guests'];
		}

		// Drop any installed ones that they can not pick
		foreach ($available_themes as $id_theme)
		{
			if (!in_array((int) $id_theme['id'], $knownThemes, true))
			{
				unset($available_themes[$id_theme['id']]);
			}
		}

		$current_images_url = $settings['images_url'];

		foreach ($available_themes as $id_theme => $theme_data)
		{
			if ($id_theme === 0)
			{
				continue;
			}

			$settings['images_url'] = &$theme_data['images_url'];
			$txt['theme_thumbnail_href'] = '';

			// Set the description in their language if available
			if (file_exists($theme_data['theme_dir'] . '/languages/' . $user_info['language'] . '/Settings.' . $user_info['language'] . '.php'))
			{
				include($theme_data['theme_dir'] . '/languages/' . $user_info['language'] . '/Settings.' . $user_info['language'] . '.php');
			}
			elseif (file_exists($theme_data['theme_dir'] . '/languages/' . $language . '/Settings.' . $language . '.php'))
			{
				include($theme_data['theme_dir'] . '/languages/' . $language . '/Settings.' . $language . '.php');
			}
			else
			{
				$txt['theme_description'] = '';
			}

			if (empty($txt['theme_thumbnail_href']))
			{
				$txt['theme_thumbnail_href'] = $theme_data['images_url'] . '/thumbnail.png';
			}

			$available_themes[$id_theme]['thumbnail_href'] = str_replace('{images_url}', $settings['images_url'], $txt['theme_thumbnail_href']);
			$available_themes[$id_theme]['description'] = $txt['theme_description'];

			// Set the name, keep it short so it does not break our list
			$available_themes[$id_theme]['name'] = preg_replace('~\stheme$~i', '', $theme_data['name']);
			if (Util::strlen($available_themes[$id_theme]['name']) > 18)
			{
				$available_themes[$id_theme]['name'] = Util::substr($available_themes[$id_theme]['name'], 0, 18) . '&hellip;';
			}
		}

		$settings['images_url'] = $current_images_url;

		if ($guest_theme != 0)
		{
			$available_themes[-1] = $available_themes[$guest_theme];
		}

		$available_themes[-1]['id'] = -1;
		$available_themes[-1]['name'] = $txt['theme_forum_default'];
		$available_themes[-1]['selected'] = $current_theme == 0;
		$available_themes[-1]['description'] = $txt['theme_global_description'];

		ksort($available_themes);

		// Validate the selected theme id.
		if (!array_key_exists($current_theme, $available_themes))
		{
			$current_theme = -1;
			$available_themes[-1]['selected'] = true;
		}

		if (!empty($_POST['sp_ts_submit']) && !empty($_POST['sp_ts_permanent']) && !empty($_POST['theme']) && isset($available_themes[$_POST['theme']]) && (!empty($this->_modSettings['theme_allow']) || allowedTo('admin_forum')))
		{
			updateMemberData($user_info['id'], array('id_theme' => $_POST['theme'] == -1 ? 0 : (int) $_POST['theme']));
		}

		$this->data['available_themes'] = $available_themes;
		$this->data['current_theme'] = $current_theme;
		$this->setTemplate('template_sp_theme_select');
	}
}

/**
 * Main template for this block
 *
 * @param array $data
 */
function template_sp_theme_select($data)
{
	global $txt;

	echo '
		<form method="post" action="?" accept-charset="UTF-8">
			<div class="centertext">
				<select name="theme" onchange="sp_theme_select(this)">';

	foreach ($data['available_themes'] as $theme)
	{
		echo '
					<option value="', $theme['id'], '"', $theme['id'] == $data['current_theme'] ? ' selected="selected"' : '', '>', $theme['name'], '</option>';
	}

	echo '
				</select>
				<br /><br />
				<img src="', $data['available_themes'][$data['current_theme']]['thumbnail_href'], '" alt="', $data['available_themes'][$data['current_theme']]['name'], '" id="sp_ts_thumb" />
				<br /><br />
				<input type="checkbox" class="input_check" name="sp_ts_permanent" value="1" /> ', $txt['sp-theme_permanent'], '
				<br />
				<input type="submit" name="sp_ts_submit" value="', $txt['sp-theme_change'], '" class="button_submit" />
			</div>
		</form>';

	$javascript = '
		var sp_ts_thumbs = [];';

	foreach ($data['available_themes'] as $id => $theme_data)
	{
		$javascript .= '
		sp_ts_thumbs[' . $id . '] = "' . $theme_data['thumbnail_href'] . '";';
	}

	addInlineJavascript($javascript, true);
}
