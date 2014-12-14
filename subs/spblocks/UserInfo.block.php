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
 * User info block, shows avatar, group, icons, posts, karma, etc
 *
 * @param mixed[] $parameters not used in this block
 * @param int $id - not used in this block
 * @param boolean $return_parameters if true returns the configuration options for the block
 */
class User_Info_Block extends SP_Abstract_Block
{
	public function setup()
	{
		global $context, $scripturl, $txt, $user_info, $color_profile, $memberContext, $modSettings;

		$this->data['session_id'] = $context['session_id'];
		$this->data['session_var'] = $context['session_var'];
		$this->data['tokens'] = array(
			'login_var' => $context['login_token_var'],
			'login' => $context['login_token']
		);
		$this->data['username'] = !empty($user_info['username']) ? $user_info['user_name'] : '';
		$this->data['urls'] = array(
			'pm' => $scripturl. '?action=pm',
			'login' => $scripturl . '?action=login2;quicklogin',
			'profile' => $scripturl . '?action=profile',
			'logout' => $scripturl . '?action=logout;' . $context['session_var'] . '=' . $context['session_id'],
			'unread' => $scripturl . '?action=unread',
			'unreadreplies' => $scripturl . '?action=unreadreplies',
		);

		if ($context['user']['is_guest'])
		{
			loadJavascriptFile('sha256.js');
			$this->data['is_guest'] = true;
			$this->data['disable_login_hashing'] = $context['disable_login_hashing'];
			$this->data['welcome_msg'] = replaceBasicActionUrl($txt[$context['can_register'] ? 'welcome_guest_register' : 'welcome_guest']);
		}
		else
		{
			$this->data['is_guest'] = false;

			// load up the members details
			// @todo why? $user_info data should already be loaded, otherwise we wouldn't even be here
			loadMemberData($user_info['id']);
			loadMemberContext($user_info['id'], true);

			$this->data['member_info'] = $memberContext[$user_info['id']];
			$this->data['member_info']['can_pm'] = allowedTo('pm_read');
			if ($this->data['member_info']['can_pm'])
			{
				$this->data['member_info']['pm'] = array(
					'messages' => $context['user']['messages'],
					'unread' => $context['user']['unread_messages'],
				);
			}

			if (sp_loadColors($this->data['member_info']['id']) !== false)
				$this->data['member_info']['colored_name'] = $color_profile[$this->data['member_info']['id']]['colored_name'];
			else
				$this->data['member_info']['colored_name'] = $this->data['member_info']['name'];

			// What do they like?
			if (!empty($modSettings['likes_enabled']))
				$this->data['show_likes'] = true;
			else
				$this->data['show_likes'] = false;

			if (!empty($modSettings['karmaMode']))
			{
				$this->data['show_karma'] = true;
				$this->data['member_info']['karma']['total'] = $this->data['member_info']['karma']['good'] - $this->data['member_info']['karma']['bad'];
														$this->data['karma_label'] = $modSettings['karmaLabel'];

				if ($modSettings['karmaMode'] == 1)
					$this->data['karma_value'] = $this->data['member_info']['karma']['total'];
				elseif ($modSettings['karmaMode'] == 2)
					$this->data['karma_value'] = '+' . $this->data['member_info']['karma']['good'] . '/-' . $this->data['member_info']['karma']['bad'];
			}
			else
				$this->data['show_karma'] = false;


			$this->data['member_info']['display_group'] = !empty($this->data['member_info']['group']) ? $this->data['member_info']['group'] : $this->data['member_info']['post_group'];
		}

		$this->setTemplate('template_sp_userInfo');
	}
}

function template_sp_userInfo($data)
{
	global $txt;

	echo '
								<div class="centertext">';

	// Show the guests a login area
	if ($data['is_guest'])
	{
		echo '
									<form action="', $data['urls']['login'], '" method="post" accept-charset="UTF-8"', empty($data['disable_login_hashing']) ? ' onsubmit="hashLoginPassword(this, \'' . $data['session_id'] . '\');"' : '', ' >
									<table>
											<tr>
												<td class="righttext">
													<label for="sp_user">', $txt['username'], ':</label>&nbsp;
												</td>
												<td>
													<input type="text" id="sp_user" name="user" size="8" value="', $data['username'], '" />
												</td>
											</tr>
											<tr>
												<td class="righttext">
													<label for="sp_passwrd">', $txt['password'], ':</label>&nbsp;
												</td>
												<td>
													<input type="password" name="passwrd" id="sp_passwrd" size="8" />
												</td>
											</tr>
											<tr>
												<td>
													<select name="cookielength">
														<option value="60">', $txt['one_hour'], '</option>
														<option value="1440">', $txt['one_day'], '</option>
														<option value="10080">', $txt['one_week'], '</option>
														<option value="43200">', $txt['one_month'], '</option>
														<option value="-1" selected="selected">', $txt['forever'], '</option>
													</select>
												</td>
												<td>
													<input type="submit" value="', $txt['login'], '" class="button_submit" />
												</td>
											</tr>
										</table>
										<input type="hidden" name="hash_passwrd" value="" />
										<input type="hidden" name="old_hash_passwrd" value="" />
										<input type="hidden" name="', $data['session_var'], '" value="', $data['session_id'], '" />
										<input type="hidden" name="', $data['tokens']['login_var'], '" value="', $data['tokens']['login'], '" />
									</form>', $data['welcome_msg'];
	}
	// A logged in member then
	else
	{
		echo '
									',  $txt['hello_member'], ' <strong>', $data['member_info']['colored_name'], '</strong>
									<br /><br />';

		if (!empty($data['member_info']['avatar']['image']))
			echo '
									<a href="', $data['urls']['profile'], '">', $data['member_info']['avatar']['image'], '</a>
									<br /><br />';

			echo '
									', $data['member_info']['display_group'], '<br />
									', $data['member_info']['group_icons'], '
									<br />
									<br />
									<ul class="sp_list">
										<li ', sp_embed_class('dot'), '>
											<strong>', $txt['posts'], ':</strong> ', $data['member_info']['posts'], '
										</li>';

		if ($data['show_karma'])
		{
			echo '
										<li ', sp_embed_class('dot'), '>
											<strong>', $data['karma_label'], '
										</strong> ', $data['karma_value'], '</li>';
		}

		if ($data['likes_enabled'])
		{
			echo '
										<li ', sp_embed_class('dot'), '>
											<strong>', $txt['likes'], ': </strong>' . $data['member_info']['likes']['given'] . ' <span ', sp_embed_class('given'), '></span> / ', $data['member_info']['likes']['received'], ' <span ', sp_embed_class('received'), '></span></li>';
		}

		if ($data['member_info']['can_pm'])
		{
			echo '
										<li ', sp_embed_class('dot'), '>
											<strong>', $txt['sp-usertmessage'], ': </strong><a href="', $data['urls']['pm'], '">', $data['member_info']['pm']['messages'], '</a>
										</li>
										<li ', sp_embed_class('dot'), '>
											<strong>', $txt['sp-usernmessage'], ': </strong> ', $data['member_info']['pm']['unread'], '
										</li>';
		}

		echo '
										<li ', sp_embed_class('dot'), '>
											<a href="', $data['urls']['unread'], '">', $txt['unread_topics_visit'], '</a>
										</li>
										<li ', sp_embed_class('dot'), '>
											<a href="', $data['urls']['unreadreplies'], '">', $txt['unread_replies'], '</a>
										</li>
									</ul>
									<br />
									<a class="dot arrow" href="', $data['urls']['profile'], '">', $txt['profile'], '</a>
									<a class="dot arrow" href="', $data['urls']['logout'], '">', $txt['logout'], '</a>';
	}

	echo '
								</div>';
}
