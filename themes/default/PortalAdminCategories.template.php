<?php

/**
 * @package SimplePortal ElkArte
 *
 * @author SimplePortal Team
 * @copyright 2015 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.0 Beta 2
 */

function template_categories_edit()
{
	global $context, $scripturl, $txt;

	echo '
	<div id="sp_edit_category">
		<form id="admin_form_wrapper" action="', $scripturl, '?action=admin;area=portalcategories;sa=edit" method="post" accept-charset="UTF-8" onsubmit="submitonce(this);">
			<h3 class="category_header">
				', $context['page_title'], '
			</h3>
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
							<textarea class="sp_content" name="description" id="category_description" rows="5" cols="45">', $context['category']['description'], '</textarea>
						</dd>
						<dt>
							<label for="category_permissions">', $txt['sp_admin_categories_col_permissions'], ':</label>
						</dt>
						<dd>
							<select name="permissions" id="category_permissions">';

	foreach ($context['category']['permission_profiles'] as $profile)
		echo '
								<option value="', $profile['id'], '"', $profile['id'] == $context['category']['permissions'] ? ' selected="selected"' : '', '>', $profile['label'], '</option>';

	echo '
							</select>
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
		</form>
	</div>';
}