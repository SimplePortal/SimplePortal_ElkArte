<?php

/**
 * @package SimplePortal ElkArte
 *
 * @author SimplePortal Team
 * @copyright 2015 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.0 Beta 2
 */

function template_pages_edit()
{
	global $context, $scripturl, $txt;

	// Previewing the page before submitting?
	echo '
	<div id="preview_section" class="forumposts"', !empty($context['SPortal']['preview']) ? '' : ' style="display: none;"', '>',
		!empty($context['SPortal']['preview']) ? template_view_page() : '', '
	</div>';

	// If there were errors creating the page, show them.
	template_show_error('pages_errors');

	// Adding or editing a page
	echo '
	<div id="sp_edit_page">
		<form id="admin_form_wrapper" action="', $scripturl, '?action=admin;area=portalpages;sa=edit" method="post" accept-charset="UTF-8" onsubmit="submitonce(this);">
			<h3 class="category_header">
				', $context['SPortal']['is_new'] ? $txt['sp_admin_pages_add'] : $txt['sp_admin_pages_edit'], '
			</h3>
				<div class="editor_wrapper">
					<dl class="sp_form">
						<dt>
							<label for="page_title">', $txt['sp_admin_pages_col_title'], ':</label>
						</dt>
						<dd>
							<input type="text" name="title" id="page_title" value="', $context['SPortal']['page']['title'], '" class="input_text" />
						</dd>
						<dt>
							<label for="page_namespace">', $txt['sp_admin_pages_col_namespace'], ':</label>
						</dt>
						<dd>
							<input type="text" name="namespace" id="page_namespace" value="', $context['SPortal']['page']['page_id'], '" class="input_text" />
						</dd>
						<dt>
							<label for="page_type">', $txt['sp_admin_pages_col_type'], ':</label>
						</dt>
						<dd>
							<select name="type" id="page_type">';

	$content_types = array('bbc', 'html', 'php');
	foreach ($content_types as $type)
		echo '
								<option value="', $type, '"', $context['SPortal']['page']['type'] == $type ? ' selected="selected"' : '', '>', $txt['sp_pages_type_' . $type], '</option>';

	echo '
							</select>
						</dd>
						<dt>
							<label for="page_permissions">', $txt['sp_admin_pages_col_permissions'], ':</label>
						</dt>
						<dd>
							<select name="permissions" id="page_permissions">';

	foreach ($context['SPortal']['page']['permission_profiles'] as $profile)
		echo '
								<option value="', $profile['id'], '"', $profile['id'] == $context['SPortal']['page']['permissions'] ? ' selected="selected"' : '', '>', $profile['label'], '</option>';

	echo '
						</select>
					</dd>
					<dt>
						<label for="page_styles">', $txt['sp_admin_pages_col_styles'], ':</label>
					</dt>
					<dd>
						<select name="styles" id="page_styles">';

	foreach ($context['SPortal']['page']['style_profiles'] as $profile)
		echo '
							<option value="', $profile['id'], '"', $profile['id'] == $context['SPortal']['page']['styles'] ? ' selected="selected"' : '', '>', $profile['label'], '</option>';

	echo '
						</select>
					</dd>
					<dt>
						<label for="page_status">', $txt['sp_admin_pages_col_status'], ':</label>
					</dt>
					<dd>
						<input type="checkbox" name="status" id="page_status" value="1"', $context['SPortal']['page']['status'] ? ' checked="checked"' : '', ' class="input_check" />
					</dd>
					<dt>
						', $txt['sp_admin_pages_col_body'], ':
					</dt>
					<dd>
					</dd>
				</dl>
				<div>',
					template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message'), '
				</div>
			</div>
			<input type="submit" name="submit" value="', $context['page_title'], '" class="right_submit" />
			<input type="submit" name="preview" value="', $txt['sp_admin_pages_preview'], '" class="right_submit" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			<input type="hidden" name="page_id" value="', $context['SPortal']['page']['id'], '" />
		</form>
	</div>';

	addInlineJavascript('sp_editor_change_type("page_type");', true);
}