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

function template_categories_edit()
{
	global $context, $scripturl, $settings, $txt;

	echo '
	<div id="sp_edit_category">
		<form id="admin_form_wrapper" action="', $scripturl, '?action=admin;area=portalcategories;sa=edit" method="post" accept-charset="UTF-8" onsubmit="submitonce(this);">
			<div class="cat_bar">
				<h3 class="catbg">
					', $context['page_title'], '
				</h3>
			</div>
			<div class="windowbg">
				<div class="sp_content_padding">
					<dl class="sp_form">
						<dt>
							<label for="category_name">', $txt['sp_admin_categories_col_name'], ':</label>
						</dt>
						<dd>
							<input type="text" name="name" id="category_name" value="', $context['category']['name'], '" class="input_text" />
						</dd>
						<dt>
							<label for="category_namespace">', $txt['sp_admin_categories_col_namespace'], ':</label>
						</dt>
						<dd>
							<input type="text" name="namespace" id="category_namespace" value="', $context['category']['category_id'], '" class="input_text" />
						</dd>
						<dt>
							<label for="category_description">', $txt['sp_admin_categories_col_description'], ':</label>
						</dt>
						<dd>
							<textarea name="description" id="category_description" rows="5" cols="45">', $context['category']['description'], '</textarea>
						</dd>
						<dt>
							<a href="', $scripturl, '?action=quickhelp;help=sp_permissions" onclick="return reqOverlayDiv(this.href);" class="help"><img src="', $settings['images_url'], '/helptopics.png" alt="', $txt['help'], '" class="icon" /></a>
							<label for="category_permission_set">', $txt['sp_admin_categories_col_permissions'], ':</label>
						</dt>
						<dd>
							<select name="permission_set" id="category_permission_set" onchange="sp_update_permissions();">';

	$permission_sets = array(1 => 'guests', 2 => 'members', 3 => 'everyone', 0 => 'custom');
	foreach ($permission_sets as $id => $label)
		echo '
								<option value="', $id, '"', $id == $context['category']['permission_set'] ? ' selected="selected"' : '', '>', $txt['sp_admin_categories_permissions_set_' . $label], '</option>';

	echo '
							</select>
						</dd>
						<dt id="category_custom_permissions_label">
							', $txt['sp_admin_categories_col_custom_permissions'], ':
						</dt>
						<dd id="category_custom_permissions_input">
							<table>
								<tr>
									<th>', $txt['sp_admin_categories_custom_permissions_membergroup'], '</td>
									<th title="', $txt['sp_admin_categories_custom_permissions_allowed'], '">', $txt['sp_admin_categories_custom_permissions_allowed_short'], '</th>
									<th title="', $txt['sp_admin_categories_custom_permissions_disallowed'], '">', $txt['sp_admin_categories_custom_permissions_disallowed_short'], '</th>
									<th title="', $txt['sp_admin_categories_custom_permissions_denied'], '">', $txt['sp_admin_categories_custom_permissions_denied_short'], '</th>
								</tr>';

	foreach ($context['category']['groups'] as $id => $label)
	{
		$current = 0;
		if (in_array($id, $context['category']['groups_allowed']))
			$current = 1;
		elseif (in_array($id, $context['category']['groups_denied']))
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
						<dt>
							<label for="category_status">', $txt['sp_admin_categories_col_status'], ':</label>
						</dt>
						<dd>
							<input type="checkbox" name="status" id="category_status" value="1"', $context['category']['status'] ? ' checked="checked"' : '', ' class="input_check" />
						</dd>
					</dl>
					<input type="submit" name="submit" value="', $context['page_title'], '" class="right_submit" />
					<input type="hidden" name="category_id" value="', $context['category']['id'], '" />
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				</div>
			</div>
		</form>
	</div>
	<script type="text/javascript"><!-- // --><![CDATA[
		sp_update_permissions();

		function sp_update_permissions()
		{
			var new_state = document.getElementById("category_permission_set").value;
			document.getElementById("category_custom_permissions_label").style.display = new_state != 0 ? "none" : "";
			document.getElementById("category_custom_permissions_input").style.display = new_state != 0 ? "none" : "";
		}
	// ]]></script>';
}