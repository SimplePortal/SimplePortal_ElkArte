<?php

/**
 * @package SimplePortal ElkArte
 *
 * @author SimplePortal Team
 * @copyright 2015 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.1.0 Beta 1
 */

function template_permission_profiles_edit()
{
	global $context, $scripturl, $txt;

	echo '
		<form id="admin_form_wrapper" action="', $scripturl, '?action=admin;area=portalprofiles;sa=editpermission" method="post" accept-charset="UTF-8" onsubmit="submitonce(this);">
			<h3 class="category_header">
				', $context['page_title'], '
			</h3>
				<div class="sp_content_padding">
					<dl class="sp_form">
						<dt>
							<label for="profile_name">', $txt['sp_admin_profiles_col_name'], ':</label>
						</dt>
						<dd>
							<input type="text" name="name" id="profile_name" value="', $context['profile']['name'], '" class="input_text" />
						</dd>
						<dt>
							', $txt['sp_admin_profiles_col_permissions'], ':
						</dt>
						<dd>
							<table>
								<tr>
									<th>', $txt['sp_admin_profiles_permissions_membergroup'], '</td>
									<th title="', $txt['sp_admin_profiles_permissions_allowed'], '">', $txt['sp_admin_profiles_permissions_allowed_short'], '</th>
									<th title="', $txt['sp_admin_profiles_permissions_disallowed'], '">', $txt['sp_admin_profiles_permissions_disallowed_short'], '</th>
									<th title="', $txt['sp_admin_profiles_permissions_denied'], '">', $txt['sp_admin_profiles_permissions_denied_short'], '</th>
								</tr>';

	foreach ($context['profile']['groups'] as $id => $label)
	{
		$current = 0;
		if (in_array($id, $context['profile']['groups_allowed']))
			$current = 1;
		elseif (in_array($id, $context['profile']['groups_denied']))
			$current = -1;

		echo '
								<tr>
									<td>', $label, '</td>
									<td><input type="radio" name="membergroups[', $id, ']" value="1"', $current == 1 ? ' checked="checked"' : '', ' class="input_radio"></td>
									<td><input type="radio" name="membergroups[', $id, ']" value="0"', $current == 0 ? ' checked="checked"' : '', ' class="input_radio"></td>
									<td><input type="radio" name="membergroups[', $id, ']" value="-1"', $current == -1 ? ' checked="checked"' : '', ' class="input_radio"></td>
								</tr>';
	}

	echo '
							</table>
						</dd>
					</dl>
				</div>
								<div class="submitbutton">
						<input type="submit" name="submit" value="', $context['page_title'], '" class="button_submit" />
						<input type="hidden" name="profile_id" value="', $context['profile']['id'], '" />
						<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					</div>
		</form>';
}

/**
 * Template for use when editing or adding a new, style profile
 */
function template_style_profiles_edit()
{
	global $context, $scripturl, $txt;

	echo '
		<form action="', $scripturl, '?action=admin;area=portalprofiles;sa=editstyle" method="post" accept-charset="UTF-8"" onsubmit="submitonce(this);">
			<h3 class="category_header">
				', $context['page_title'], '
			</h3>
			<div class="sp_content_padding">
				<dl class="sp_form">
					<dt>
						<label for="profile_name">', $txt['sp_admin_profiles_col_name'], ':</label>
					</dt>
					<dd>
						<input type="text" name="name" id="profile_name" value="', $context['profile']['name'], '" class="input_text" />
					</dd>
					<dt>
						<label for="title_default_class">', $txt['sp_admin_profiles_styles_title_default_class'], ':</label>
					</dt>
					<dd>
						<select name="title_default_class" id="title_default_class">';

	foreach ($context['profile']['classes']['title'] as $class)
		echo '
							<option value="', $class, '"', $context['profile']['title_default_class'] == $class ? ' selected="selected"' : '', '>', $class, '</option>';

	echo '
						</select>
					</dd>
					<dt>
						<label for="title_custom_class">', $txt['sp_admin_profiles_styles_title_custom_class'], ':</label>
					</dt>
					<dd>
						<input type="text" name="title_custom_class" id="title_custom_class" value="', $context['profile']['title_custom_class'], '" class="input_text" size="40" />
					</dd>
					<dt>
						<label for="title_custom_style">', $txt['sp_admin_profiles_styles_title_custom_style'], ':</label>
					</dt>
					<dd>
						<input type="text" name="title_custom_style" id="title_custom_style" value="', $context['profile']['title_custom_style'], '" class="input_text" size="40" />
					</dd>
					<dt>
						<label for="no_title">', $txt['sp_admin_profiles_styles_no_title'], ':</label>
					</dt>
					<dd>
						<input type="checkbox" name="no_title" id="no_title" value="1" onclick="document.getElementById(\'title_default_class\').disabled = document.getElementById(\'title_custom_class\').disabled = document.getElementById(\'title_custom_style\').disabled = this.checked"', $context['profile']['no_title'] ? ' checked="checked"' : '', ' class="input_check" />
					</dd>
					<dt>
						<label for="body_default_class">', $txt['sp_admin_profiles_styles_body_default_class'], ':</label>
					</dt>
					<dd>
						<select name="body_default_class" id="body_default_class">';

	foreach ($context['profile']['classes']['body'] as $class)
		echo '
							<option value="', $class, '"', $context['profile']['body_default_class'] == $class ? ' selected="selected"' : '', '>', $class, '</option>';

	echo '
						</select>
					</dd>
					<dt>
						<label for="body_custom_class">', $txt['sp_admin_profiles_styles_body_custom_class'], ':</label>
					</dt>
					<dd>
						<input type="text" name="body_custom_class" id="body_custom_class" value="', $context['profile']['body_custom_class'], '" class="input_text" size="40" />
					</dd>
					<dt>
						<label for="body_custom_style">', $txt['sp_admin_profiles_styles_body_custom_style'], ':</label>
					</dt>
					<dd>
						<input type="text" name="body_custom_style" id="body_custom_style" value="', $context['profile']['body_custom_style'], '" class="input_text" size="40" />
					</dd>
					<dt>
						<label for="no_body">', $txt['sp_admin_profiles_styles_no_body'], ':</label>
					</dt>
					<dd>
						<input type="checkbox" name="no_body" id="no_body" value="1" onclick="document.getElementById(\'body_default_class\').disabled = this.checked"', $context['profile']['no_body'] ? ' checked="checked"' : '', ' class="input_check" />
					</dd>
				</dl>
				<div class="submitbutton">
					<input type="submit" name="submit" value="', $context['page_title'], '" class="button_submit" />
					<input type="hidden" name="profile_id" value="', $context['profile']['id'], '" />
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				</div>
			</div>
		</form>';

		addInlineJavascript('check_style_options();', true);
}