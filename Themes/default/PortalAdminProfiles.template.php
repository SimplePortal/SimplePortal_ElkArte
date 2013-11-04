<?php

/**
 * @package SimplePortal
 *
 * @author SimplePortal Team
 * @copyright 2013 SimplePortal Team
 * @license BSD 3-clause
 *
 * @version 2.4
 */

function template_permission_profiles_edit()
{
	global $context, $scripturl, $txt;

	echo '
	<div id="sp_edit_profile">
		<form id="admin_form_wrapper" action="', $scripturl, '?action=admin;area=portalprofiles;sa=editpermission" method="post" accept-charset="', $context['character_set'], '" onsubmit="submitonce(this);">
			<h3 class="category_header">
				', $context['page_title'], '
			</h3>
			<div class="windowbg2">
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
					<div class="submitbutton">
						<input type="submit" name="submit" value="', $context['page_title'], '" class="button_submit" />
						<input type="hidden" name="profile_id" value="', $context['profile']['id'], '" />
						<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					</div>
				</div>
			</div>
		</form>
	</div>';
}