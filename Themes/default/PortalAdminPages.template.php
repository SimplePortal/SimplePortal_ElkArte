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

function template_pages_edit()
{
	global $context, $scripturl, $txt;

	// Reviewing the page before submitting?
	if (!empty($context['SPortal']['preview']))
	{
		echo '
	<div class="sp_auto_align" style="width: 90%; padding-bottom: 1em;">';

		template_view_page();

		echo '
	</div>';
	}

	// Adding or editing a page
	echo '
	<div id="sp_edit_page">
		<form id="admin_form_wrapper" action="', $scripturl, '?action=admin;area=portalpages;sa=edit" method="post" accept-charset="UTF-8" onsubmit="submitonce(this);">
			<h3 class="category_header">
				', $context['SPortal']['is_new'] ? $txt['sp_admin_pages_add'] : $txt['sp_admin_pages_edit'], '
			</h3>
			<div class="windowbg">
				<div class="sp_content_padding">
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
							<select name="type" id="page_type" onchange="sp_update_editor(\'page_type\');">';

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
							<label for="page_blocks">', $txt['sp_admin_pages_col_blocks'], ':</label>
						</dt>
						<dd>
							<select name="blocks[]" id="page_blocks" size="7" multiple="multiple">';

	foreach ($context['sides'] as $side => $label)
	{
		if (empty($context['page_blocks'][$side]))
			continue;

		echo '
								<optgroup label="', $label, '">';

		foreach ($context['page_blocks'][$side] as $block)
		{
			echo '
									<option value="', $block['id'], '"', $block['shown'] ? ' selected="selected"' : '', '>', $block['label'], '</option>';
		}

		echo '
								</optgroup>';
	}

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
					<div id="sp_rich_editor">
						<div id="sp_rich_bbc"', $context['SPortal']['page']['type'] != 'bbc' ? ' style="display: none;"' : '', '></div>
						<div id="sp_rich_smileys"', $context['SPortal']['page']['type'] != 'bbc' ? ' style="display: none;"' : '', '></div>
						<div>', template_control_richedit($context['post_box_name'], 'sp_rich_smileys', 'sp_rich_bbc'), '</div>
					</div>
					<input type="submit" name="submit" value="', $context['page_title'], '" class="right_submit" />
					<input type="submit" name="preview" value="', $txt['sp_admin_pages_preview'], '" class="right_submit" />
				</div>
			</div>';

	$style_sections = array('title' => 'left', 'body' => 'right');
	$style_types = array('default' => 'DefaultClass', 'class' => 'CustomClass', 'style' => 'CustomStyle');
	$style_parameters = array(
		'title' => array('category_header', 'secondary_header'),
		'body' => array('windowbg',  'windowbg2', 'information', 'roundframe'),
	);

	echo '
			<br />
			<h3 class="category_header">
				', $txt['sp_admin_pages_style'], '
			</h3>
			<div class="windowbg2">
				<div class="sp_content_padding">';

	foreach ($style_sections as $section => $float)
	{
		echo '
					<dl id="sp_edit_style_', $section, '" class="sp_form sp_float_', $float, '">';

		foreach ($style_types as $type => $label)
		{
			echo '
						<dt>
							', $txt['sp-blocks' . ucfirst($section) . $label], ':
						</dt>
						<dd>';

			if ($type == 'default')
			{
				echo '
							<select name="', $section, '_default_class" id="', $section, '_default_class">';

				foreach ($style_parameters[$section] as $class)
					echo '
								<option value="', $class, '"', $context['SPortal']['page']['style'][$section . '_default_class'] == $class ? ' selected="selected"' : '', '>', $class, '</option>';

				echo '
							</select>';
			}
			else
				echo '
							<input type="text" name="', $section, '_custom_', $type, '" id="', $section, '_custom_', $type, '" value="', $context['SPortal']['page']['style'][$section . '_custom_' . $type], '" class="input_text" />';

			echo '
						</dd>';
		}

		echo '
						<dt>
							', $txt['sp-blocksNo' . ucfirst($section)], ':
						</dt>
						<dd>
							<input type="checkbox" name="no_', $section, '" id="no_', $section, '" value="1"', !empty($context['SPortal']['page']['style']['no_' . $section]) ? ' checked="checked"' : '', ' onclick="document.getElementById(\'', $section, '_default_class\').disabled', $section == 'title' ? ' = document.getElementById(\'title_custom_class\').disabled = document.getElementById(\'title_custom_style\').disabled' : '', ' = this.checked;" class="input_check" />
						</dd>
					</dl>';
	}

	echo '
					<input type="submit" name="submit" value="', $context['page_title'], '" class="right_submit" />
					<input type="submit" name="preview" value="', $txt['sp_admin_pages_preview'], '" class="right_submit" />
				</div>
			</div>
			<input type="hidden" name="page_id" value="', $context['SPortal']['page']['id'], '" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>
	</div>
	<script type="text/javascript"><!-- // --><![CDATA[
		document.getElementById("title_default_class").disabled = document.getElementById("no_title").checked;
		document.getElementById("title_custom_class").disabled = document.getElementById("no_title").checked;
		document.getElementById("title_custom_style").disabled = document.getElementById("no_title").checked;
		document.getElementById("body_default_class").disabled = document.getElementById("no_body").checked;
	// ]]></script>';
}