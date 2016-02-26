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
 * Who's online block, shows count of users online names
 *
 * @param mixed[] $parameters
 *        'online_today' => shows all users that were online today (requires user online today addon)
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
class Whos_Online_Block extends SP_Abstract_Block
{
	/**
	 * Constructor, used to define block parameters
	 *
	 * @param Database|null $db
	 */
	public function __construct($db = null)
	{
		$this->block_parameters = array(
			'online_today' => 'check',
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
		global $modSettings, $context;

		// Interface with the online today addon?
		if (!empty($parameters['online_today']) && !empty($modSettings['onlinetoday']) && file_exists(SUBSDIR . '/OnlineToday.class.php'))
		{
			require_once(SUBSDIR . '/OnlineToday.class.php');

			$context['info_center_callbacks'] = array();
			Online_Today_Integrate::get();
		}

		// Spiders
		$this->data['show_spiders'] = !empty($modSettings['show_spider_online']) && ($modSettings['show_spider_online'] < 3 || allowedTo('admin_forum'));

		loadLanguage('index', '', false, true);

		$this->data['stats'] = ssi_whosOnline('array');

		$this->setTemplate('template_sp_whosOnline');
	}
}

/**
 * Main template for this block
 *
 * @param mixed[] $data
 */
function template_sp_whosOnline($data)
{
	global $scripturl, $modSettings, $txt, $context;

	echo '
			<ul class="sp_list">
				<li ', sp_embed_class('dot'), '> ', $txt['guests'], ': ', $data['stats']['num_guests'], '</li>';

	// Spiders
	if ($data['show_spiders'])
	{
		echo '
				<li ', sp_embed_class('dot'), '> ', $txt['spiders'], ': ', $data['stats']['num_spiders'], '</li>';
	}

	echo '
				<li ', sp_embed_class('dot'), '> ', $txt['hidden'], ': ', $data['stats']['num_users_hidden'], '</li>
				<li ', sp_embed_class('dot'), '> ', $txt['users'], ': ', $data['stats']['num_users_online'], '</li>';

	// Show the users online, if any
	if (!empty($data['stats']['users_online']))
	{
		echo '
				<li ', sp_embed_class('dot'), '> ', allowedTo('who_view') && !empty($modSettings['who_enabled']) ? '<a href="' . $scripturl . '?action=who">' : '', $txt['online_users'], allowedTo('who_view') && !empty($modSettings['who_enabled']) ? '</a>' : '', ':</li>
			</ul>
			<div class="sp_online_flow">
				<ul class="sp_list">';

		foreach ($data['stats']['users_online'] as $user)
		{
			echo '
					<li ', sp_embed_class($user['name'] === 'H' ? 'tux' : 'user', '', 'sp_list_indent'), '>', $user['hidden'] ? '<em>' . $user['link'] . '</em>' : $user['link'], '</li>';
		}

		echo '
				</ul>
			</div>';
	}
	else
	{
		echo '
			</ul>
			<br />
			<div class="sp_fullwidth centertext">', $txt['error_sp_no_online'], '</div>';
	}

	// Does the online today addon exist
	if (!empty($context['num_onlinetoday']))
	{
		echo '
			<ul class="sp_list">
				<li ', sp_embed_class('dot'), '> ', $txt['sp-online_today'], ': ', $context['num_onlinetoday'], '</li>
			</ul>
			<div class="sp_online_flow">
				<ul class="sp_list">';

		foreach ($context['onlinetoday'] as $user)
		{
			echo '
					<li ', sp_embed_class('user', '', 'sp_list_indent'), '>', $user, '</li>';
		}

		echo '
				</ul>
			</div>';
	}
}