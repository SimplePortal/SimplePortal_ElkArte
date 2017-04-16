<?php

/**
 * @package SimplePortal ElkArte
 *
 * @author SimplePortal Team
 * @copyright 2015 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.0 Beta 2
 */

/**
 * The area above the article editor box,
 * Holds subject, preview, info messages, etc
 */
function template_articles_edit_above()
{
	global $context, $scripturl, $txt;

	// Taking a peek before you publish?
	echo '
	<div id="preview_section" class="forumposts"', !empty($context['preview']) ? '' : ' style="display: none;"', '>',
	!empty($context['preview']) ? template_view_article() : '', '
	</div>';

	// If an error occurred, explain what happened.
	template_show_error('article_errors');

	// Or perhaps an attachment error
	if (!empty($context['attachment_error_keys']))
	{
		template_attachment_errors();
	}

	echo '
	<div id="sp_edit_article">
		<form id="admin_form_wrapper" action="', $scripturl, '?action=admin;area=portalarticles;sa=edit" method="post" accept-charset="UTF-8" onsubmit="submitonce(this);">
			<h3 class="category_header">
				', $context['is_new'] ? $txt['sp_admin_articles_add'] : $txt['sp_admin_articles_edit'], '
			</h3>
			<div class="editor_wrapper">
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
	{
		echo '
								<option value="', $category['id'], '"', $context['article']['category']['id'] == $category['id'] ? ' selected="selected"' : '', '>', $category['name'], '</option>';
	}

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
	{
		echo '
								<option value="', $type, '"', $context['article']['type'] == $type ? ' selected="selected"' : '', '>', $txt['sp_articles_type_' . $type], '</option>';
	}

	echo '
							</select>
						</dd>
						<dt>
							<label for="article_permissions">', $txt['sp_admin_articles_col_permissions'], ':</label>
						</dt>
						<dd>
							<select name="permissions" id="article_permissions" onchange="sp_update_permissions();">';

	foreach ($context['article']['permission_profiles'] as $profile)
	{
		echo '
							<option value="', $profile['id'], '"', $profile['id'] == $context['article']['permissions'] ? ' selected="selected"' : '', '>', $profile['label'], '</option>';
	}

	echo '
							</select>
						</dd>
						<dt>
							<label for="article_styles">', $txt['sp_admin_articles_col_styles'], ':</label>
						</dt>
						<dd>
							<select name="styles" id="article_styles">';

	foreach ($context['article']['style_profiles'] as $profile)
	{
		echo '
								<option value="', $profile['id'], '"', $profile['id'] == $context['article']['styles'] ? ' selected="selected"' : '', '>', $profile['label'], '</option>';
	}

	echo '
							</select>
						</dd>
						<dt>
							<label for="article_status">', $txt['sp_admin_articles_col_status'], ':</label>
						</dt>
						<dd>
							<input type="checkbox" name="status" id="article_status" value="1"', $context['article']['status'] ? ' checked="checked"' : '', ' class="input_check" />
						</dd>
					</dl>';
}

/**
 * Used to add or edit an article
 */
function template_articles()
{
	global $context, $txt;

	echo '
					<div>', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message'), '</div>
					<div class="submitbutton">
						<input type="submit" name="submit" value="', $context['page_title'], '" accesskey="s" tabindex="', $context['tabindex']++, '" class="button_submit" />
						<input type="submit" name="preview" value="', $txt['sp_admin_articles_preview'], '" accesskey="p" tabindex="', $context['tabindex']++, '" class="button_submit" />
						<input type="hidden" name="article_id" value="', $context['article']['id'], '" />
						<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />';

	// Spellcheck button?
	if ($context['show_spellchecking'])
	{
		echo '
						<input type="button" value="', $txt['spell_check'], '" onclick="spellCheckStart()" tabindex="', $context['tabindex']++, '" class="button_submit" />';
	}

	echo '
					</div>';

	addInlineJavascript('sp_editor_change_type("article_type");', true);
}

/**
 * Show the adding of attachments, D&D, etc below the post box
 */
function template_articles_edit_below()
{
	global $context;

	echo '
					<div id="postAdditionalOptions">';

	// If this post already has attachments on it - give information about them.
	if (!empty($context['attachments']['current']))
	{
		$context['attachments']['templates']['existing']();
	}

	// Is the user allowed to post any additional ones? If so give them the boxes to do it!
	if ($context['attachments']['can']['post'])
	{
		$context['attachments']['templates']['add_new']();
	}

	echo '
					</div>
				</div>
			</div>
		</form>
	</div>';
}

/**
 * Creates a list of attachments already attached to this message
 */
function template_article_existing_attachments()
{
	global $context, $txt, $modSettings;

	echo '
						<dl id="postAttachment">
							<dt>
								', $txt['attached'], ':
							</dt>
							<dd class="smalltext" style="width: 100%;">
								<input type="hidden" name="attach_del[]" value="0" />
								', $txt['uncheck_unwatchd_attach'], ':
							</dd>';

	foreach ($context['attachments']['current'] as $attachment)
	{
		echo '
							<dd class="smalltext">
								<label for="attachment_', $attachment['id'], '"><input type="checkbox" id="attachment_', $attachment['id'], '" name="attach_del[]" value="', $attachment['id'], '"', empty($attachment['unchecked']) ? ' checked="checked"' : '', ' class="input_check" /> ', $attachment['name'],
								!empty($modSettings['attachmentPostLimit']) || !empty($modSettings['attachmentSizeLimit']) ? sprintf($txt['attach_kb'], comma_format(round(max($attachment['size'], 1028) / 1028), 0)) : '', '</label>
							</dd>';
	}

	echo '
						</dl>';
}

/**
 * Creates the interface to upload attachments
 */
function template_article_new_attachments()
{
	global $context, $txt, $modSettings;

	echo '
						<dl id="postAttachment2">';

	// But, only show them if they haven't reached a limit
	if ($context['attachments']['num_allowed'] > 0)
	{
		echo '
							<dt class="drop_area">
								<i class="fa fa-upload"></i> ', $txt['attach_drop_files'], '
								<input class="drop_area_fileselect input_file" type="file" multiple="multiple" name="attachment_click[]" id="attachment_click" />
							</dt>
							<dd class="progress_tracker"></dd>
							<dd class="drop_attachments_error"></dd>
							<dt class="drop_attachments_no_js">
								', $txt['attach'], ':
							</dt>
							<dd class="smalltext drop_attachments_no_js">
								', empty($modSettings['attachmentSizeLimit']) ? '' : ('<input type="hidden" name="MAX_FILE_SIZE" value="' . $modSettings['attachmentSizeLimit'] * 1028 . '" />'), '
								<input type="file" multiple="multiple" name="attachment[]" id="attachment1" class="input_file" /> (<a href="javascript:void(0);" onclick="cleanFileInput(\'attachment1\');">', $txt['clean_attach'], '</a>)';

		// Show more boxes if they aren't approaching that limit.
		if ($context['attachments']['num_allowed'] > 1)
		{
			echo '
								<script>
									var allowed_attachments = ', $context['attachments']['num_allowed'], ',
										current_attachment = 1,
										txt_more_attachments_error = "', $txt['more_attachments_error'], '",
										txt_more_attachments = "', $txt['more_attachments'], '",
										txt_clean_attach = "', $txt['clean_attach'], '";
								</script>
							</dd>
							<dd class="smalltext drop_attachments_no_js" id="moreAttachments">
								<a href="#" onclick="addAttachment(); return false;">(', $txt['more_attachments'], ')</a>
							</dd>';
		}
		else
		{
			echo '
							</dd>';
		}
	}

	echo '
							<dd class="smalltext">';

	// Show some useful information such as allowed extensions, maximum size and amount of attachments allowed.
	if (!empty($context['attachments']['allowed_extensions']))
	{
		echo '
								', $txt['allowed_types'], ': ', $context['attachments']['allowed_extensions'], '<br />';
	}

	if (!empty($context['attachments']['restrictions']))
	{
		echo '
								', $txt['attach_restrictions'], ' ', implode(', ', $context['attachments']['restrictions']), '<br />';
	}

	if ($context['attachments']['num_allowed'] == 0)
	{
		echo '
								', $txt['attach_limit_nag'], '<br />';
	}

	echo '
							</dd>
						</dl>';
}
