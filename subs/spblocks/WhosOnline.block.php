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
			'avatars' => 'check',
			'refresh_value' => 'int'
		);

		parent::__construct($db);
	}

	/**
	 * Initializes a block for use.
	 *
	 * - Called from portal.subs as part of the sportal_load_blocks process
	 *
	 * @param mixed[] $parameters
	 * 		'online_today' => shows all users that were online today (requires user online today addon)
	 * 		'avatars' => shows the user avatar if available
	 * 		'refresh' => if we auto refresh the block
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

		// Show the avatar next to the online name?
		if (!empty($parameters['avatars']))
		{
			$this->_online_avatars();
			$this->data['avatars'] = true;
		}

		// The output template
		$this->setTemplate('template_sp_whosOnline');

		// Enabling auto refresh?
		if (!empty($parameters['refresh_value']))
		{
			$this->refresh = array('sa' => 'whos', 'class' => '.whos_online', 'id' => $id, 'refresh_value' => $parameters['refresh_value']);
			$this->auto_refresh();
		}
	}

	/**
	 * Fetch the avatars for the online users
	 */
	private function _online_avatars()
	{
		if (empty($this->data['stats']['users_online']))
		{
			return;
		}

		$users = array();
		foreach ($this->data['stats']['users_online'] as $user)
		{
			$users[] = $user['id'];
		}

		$request = $this->_db->query('', '
			SELECT
				m.id_member,  m.avatar, m.email_address,
				a.id_attach, a.attachment_type, a.filename
			FROM {db_prefix}members AS m
				LEFT JOIN {db_prefix}attachments AS a ON (a.id_member = m.id_member)
			WHERE m.id_member IN ({array_int:users})',
			array(
				'users' => $users,
			)
		);
		$avatars = array();
		while ($row = $this->_db->fetch_assoc($request))
		{
			// Load the member data
			$avatars[$row['id_member']] = determineAvatar(array(
				'avatar' => $row['avatar'],
				'filename' => $row['filename'],
				'id_attach' => $row['id_attach'],
				'email_address' => $row['email_address'],
				'attachment_type' => $row['attachment_type'])
			);
		}
		$this->_db->free_result($request);

		foreach ($this->data['stats']['users_online'] as $key => $user)
		{
			$this->data['stats']['users_online'][$key]['avatar'] = $avatars[$this->data['stats']['users_online'][$key]['id']];
		}
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
			if (!empty($data['avatars']))
			{
				echo '
					<li class="sp_who_avatar">', !empty($user['avatar']['href']) ? '
						<a href="' . $scripturl . '?action=profile;u=' . $user['id'] . '">
							<img src="' . $user['avatar']['href'] . '" alt="' . $user['name'] . '" />
						</a>' : '',
				$user['hidden'] ? '<em>' . $user['link'] . '</em>' : $user['link'],
				'</li>';
			}
			else
			{
				echo '
					<li ', sp_embed_class($user['name'] === 'H' ? 'tux' : 'user', '', 'sp_list_indent'), '>', $user['hidden'] ? '<em>' . $user['link'] . '</em>' : $user['link'], '</li>';
			}
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