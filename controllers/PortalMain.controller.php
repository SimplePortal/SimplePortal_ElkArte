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
 * Portal controller.
 * This class handles requests that allow viewing the main portal or the portal credits
 */
class Sportal_Controller extends Action_Controller
{
	/**
	 * Default method, just forwards
	 */
	public function action_index()
	{
		// Where do you want to go today? :P
		$this->action_sportal_index();
	}

	/**
	 * Common actions for all methods in the class
	 */
	public function pre_dispatch()
	{
		global $context;

		$context['page_title'] = $context['forum_name'];

		if (isset($context['page_title_html_safe']))
			$context['page_title_html_safe'] = Util::htmlspecialchars(un_htmlspecialchars($context['page_title']));

		if (!empty($context['standalone']))
			setupMenuContext();
	}

	/**
	 * Loads article previews for display with the portal index template
	 */
	public function action_sportal_index()
	{
		global $context, $modSettings;

		$context['articles'] = sportal_get_articles(0, true, true, 'spa.id_article DESC');

		// If we have some articles
		foreach ($context['articles'] as $article)
		{
			// Just the preview
			if (!empty($modSettings['articlelength']))
				$article['body'] = shorten_text($article['body'], $modSettings['articlelength'], true);

			$context['articles'][$article['id']]['preview'] = parse_bbc($article['body']);
			$context['articles'][$article['id']]['date'] = standardTime($article['date']);
		}

		$context['sub_template'] = 'portal_index';
	}

	/**
	 * Displays the credit page outside of the admin area
	 */
	public function action_sportal_credits()
	{
		global $context, $txt;

		require_once(ADMINDIR . '/PortalAdminMain.php');
		loadLanguage('SPortalAdmin', sp_languageSelect('SPortalAdmin'));

		sportal_information(false);

		$context['page_title'] = $txt['sp-info_title'];
		$context['sub_template'] = 'information';
	}

	/**
	 * Reorders the front page blocks in response to a D&D ajax request
	 */
	public function action_userblockorder()
	{
		global $context, $txt, $user_info, $settings, $modSettings;

		// Should not happen, but no guest processing
		if ($user_info['is_guest'] || $user_info['id'] == 0)
			return;

		// Start off with nothing
		$context['xml_data'] = array();
		$errors = array();
		$order = array();

		// Chances are
		loadLanguage('SPortal');

		// You have to be allowed to do this
		$validation_session = checkSession();
		if (empty($validation_session))
		{
			// No questions that we are rearranging the blocks
			if (isset($_POST['order'], $_POST['received'], $_POST['moved']))
			{
				$column_numbers = array('sp_left_div' => 1, 'sp_top_div' => 2, 'sp_bottom_div' => 3, 'sp_right_div' => 4, 'sp_header_div' => 5, 'sp_footer_div' => 6);

				// What block was drag and dropped? e.g. block_2,4
				list ($block_moved, ) = explode(',', $_POST['moved']);
				$block_moved = (int) str_replace('block_', '', $block_moved);

				// Where is it going
				$target_column = $column_numbers[$_POST['received']];

				// The block ids arrive in 1-n view order ... block,column
				$block_tree = array();
				foreach ($_POST['block'] as $id)
				{
					list ($block, $column) = explode(',', $id);

					// Update the moved blocks column
					if ($block == $block_moved)
						$column = $target_column;

					$block_tree[$column][] = $block;
				}
			}

			// Update the option so its remembered
			require_once(SUBSDIR . '/Themes.subs.php');
			updateThemeOptions(array($settings['theme_id'], $user_info['id'], 'sp_block_layout', serialize($block_tree)));

			if (!empty($modSettings['cache_enable']) && $modSettings['cache_enable'] >= 2)
				cache_put_data('theme_settings-' . $settings['theme_id'] . ':' . $user_info['id'], null, 60);

			$order[] = array(
				'value' => $txt['sp-blocks_success_arrange'],
			);
		}
		// Failed validation, tough day for you
		else
			$errors[] = array('value' => $txt['sp-blocks_fail_arrange']);

		// Return the response
		$context['sub_template'] = 'generic_xml';
		$context['xml_data'] = array(
			'orders' => array(
				'identifier' => 'order',
				'children' => $order,
			),
			'errors' => array(
				'identifier' => 'error',
				'children' => $errors,
			),
		);
	}
}