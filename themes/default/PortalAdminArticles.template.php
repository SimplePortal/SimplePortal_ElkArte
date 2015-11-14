<?php

/**
 * @package SimplePortal ElkArte
 *
 * @author SimplePortal Team
 * @copyright 2015 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.1.0 Beta 1
 */

/**
 * Used to add or edit an article
 */
function template_articles_edit()
{
	global $context, $scripturl, $txt;

	// Taking a peek before you publish?
	echo '
	<div id="preview_section" class="forumposts"', !empty($context['preview']) ? '' : ' style="display: none;"', '>',
		!empty($context['preview']) ? template_view_article() : '', '
	</div>';

	// If there were errors creating the article, show them.
	template_show_error('article_errors');

	echo '
	<div id="sp_edit_article">
		<form id="admin_form_wrapper" action="', $scripturl, '?action=admin;area=portalarticles;sa=edit" method="post" accept-charset="UTF-8" onsubmit="submitonce(this);">
			<h3 class="category_header">
				', $context['is_new'] ? $txt['sp_admin_articles_add'] : $txt['sp_admin_articles_edit'], '
			</h3>
			<div class="content">
				<dl class="sp_form">
					<dt>
						<label for="article_title">', $txt['sp_admin_articles_col_title'], ':</label>
					</dt>
					<dd>
						<input type="text" name="title" id="article_title" value="', $context['article']['title'], '" class="input_text" />
					</dd>
					<dt>
						<label for="article_namespace">', $txt['sp_admin_articles_col_namespace'], ':</label>
					</dt>
					<dd>
						<input type="text" name="namespace" id="article_namespace" value="', $context['article']['article_id'], '" class="input_text" />
					</dd>
					<dt>
						<label for="article_category">', $txt['sp_admin_articles_col_category'], ':</label>
					</dt>
					<dd>
						<select name="category_id" id="article_category">';

	foreach ($context['article']['categories'] as $category)
		echo '
							<option value="', $category['id'], '"', $context['article']['category']['id'] == $category['id'] ? ' selected="selected"' : '', '>', $category['name'], '</option>';

	echo '
						</select>
					</dd>
					<dt>
						<label for="article_type">', $txt['sp_admin_articles_col_type'], ':</label>
					</dt>
					<dd>
						<select name="type" id="article_type">';

	$content_types = array('bbc', 'html', 'php');
	foreach ($content_types as $type)
		echo '
							<option value="', $type, '"', $context['article']['type'] == $type ? ' selected="selected"' : '', '>', $txt['sp_articles_type_' . $type], '</option>';

	echo '
						</select>
					</dd>
					<dt>
						<label for="article_permissions">', $txt['sp_admin_articles_col_permissions'], ':</label>
					</dt>
					<dd>
						<select name="permissions" id="article_permissions" onchange="sp_update_permissions();">';

	foreach ($context['article']['permission_profiles'] as $profile)
		echo '
							<option value="', $profile['id'], '"', $profile['id'] == $context['article']['permissions'] ? ' selected="selected"' : '', '>', $profile['label'], '</option>';

	echo '
						</select>
					</dd>
					<dt>
						<label for="article_styles">', $txt['sp_admin_articles_col_styles'], ':</label>
					</dt>
					<dd>
						<select name="styles" id="article_styles">';

	foreach ($context['article']['style_profiles'] as $profile)
		echo '
							<option value="', $profile['id'], '"', $profile['id'] == $context['article']['styles'] ? ' selected="selected"' : '', '>', $profile['label'], '</option>';

	echo '
						</select>
					</dd>
					<dt>
						<label for="article_status">', $txt['sp_admin_articles_col_status'], ':</label>
					</dt>
					<dd>
						<input type="checkbox" name="status" id="article_status" value="1"', $context['article']['status'] ? ' checked="checked"' : '', ' class="input_check" />
					</dd>
				</dl>
				<div id="sp_rich_editor">', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message'), '</div>
				<div class="submitbutton">
					<input type="submit" name="submit" value="', $context['page_title'], '" class="button_submit" />
					<input type="submit" name="preview" value="', $txt['sp_admin_articles_preview'], '" class="button_submit" />
					<input type="hidden" name="article_id" value="', $context['article']['id'], '" />
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				</div>
			</form>
		</div>
	</div>';

	addInlineJavascript('sp_editor_change_type("article_type");', true);
}