<?php

/**
 * @package SimplePortal ElkArte
 *
 * @author SimplePortal Team
 * @copyright 2015 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.0 Beta 2
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
		<form id="admin_form_wrapper" action="', $scripturl, '?action=admin;area=portalprofiles;sa=editstyle" method="post" accept-charset="UTF-8"" onsubmit="submitonce(this);">
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

/**
 * Template for use when editing or adding a new, visibility profile
 */
function template_visibility_profiles_edit()
{
	global $context, $scripturl, $settings, $txt;

	echo '
		<form id="admin_form_wrapper" action="', $scripturl, '?action=admin;area=portalprofiles;sa=editvisibility" method="post" accept-charset="UTF-8" onsubmit="submitonce(this);">
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
				</dl>
				<div id="sp_select_visibility">';

	$types = array('actions', 'boards', 'pages', 'categories', 'articles');
	foreach ($types as $type)
	{
		if (empty($context['profile'][$type]))
			continue;

		echo '
					<a href="javascript:void(0);" onclick="sp_collapseObject(\'', $type, '\')">
						<img id="sp_collapse_', $type, '" src="', $settings['images_url'], '/selected_open.png" alt="*" />
					</a> ', $txt['sp_admin_profiles_visibility_select_' . $type], '
					<ul id="sp_object_', $type, '" class="sp_visibility_list" style="display: none;">';

		foreach ($context['profile'][$type] as $id => $label)
		{
			echo '
						<li>
							<input type="checkbox" name="', $type, '[]" id="', $type, $id, '" value="', $id, '"', in_array($id, $context['profile']['selections']) ? ' checked="checked"' : '', ' class="input_check" />
							<label for="', $type, $id, '">', $label, '</label>
						</li>';
		}

		echo '
						<li>
							<input type="checkbox" onclick="invertAll(this, this.form, ', $type, '[]);" class="input_check" /><em>', $txt['check_all'], '</em>
						</li>
					</ul>
					<br />';
	}

	echo '
				</div>
				<dl class="sp_form">
					<dt>
						<a href="', $scripturl, '?action=quickhelp;help=sp-blocksCustomDisplayOptions" onclick="return reqOverlayDiv(this.href);" class="help">
							<img src="' . $settings['images_url'] . '/helptopics.png" class="icon" alt="' . $txt['help'] . '" />
						</a>
						<label for="profile_query">', $txt['sp_admin_profiles_col_query'], ':</label>
					</dt>
					<dd>
						<input type="text" name="query" id="profile_query" value="', $context['profile']['query'], '" class="input_text" size="50" />
						<select id="query_list">';

	// Select list used to populate the profile_query input field
	$options = array($txt['sp-adminother'] => '', 'all' => 'all', 'allaction' => 'allaction', 'allboard' => 'allboard',	'allpage' => 'allpage', 'allcategory' => 'allcategory', 'allarticle' => 'allarticle');
	foreach ($options as $option => $value)
	{
		echo '
							<option value="', $value, '"', $context['profile']['query'] === $value ? ' selected="selected"' : '', '>', $option, '</option>';
	}

	echo '
						</select>
					</dd>
					<dt>
						<label for="mobile">', $txt['sp-blocksMobile'], ':</label>
					</dt>
					<dd>
						<input type="checkbox" name="block_mobile" id="mobile" value="1"', $context['profile']['mobile_view'] ? ' checked="checked"' : '', ' class="input_check" />
					</dd>
				</dl>
				<div class="submitbutton">
					<input type="submit" name="submit" value="', $context['page_title'], '" class="button_submit" />
					<input type="hidden" name="profile_id" value="', $context['profile']['id'], '" />
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				</div>
			</div>
		</form>';

	// Swap the select value in to the query text box
	addInlineJavascript('
	$("#query_list").change(function() {
		$("#profile_query").val(this.value);
	}).change();',true);
}