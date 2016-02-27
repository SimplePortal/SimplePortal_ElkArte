<?php

/**
 * @package SimplePortal ElkArte
 *
 * @author SimplePortal Team
 * @copyright 2015 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.0 Beta 2
 */

function template_shoutbox_edit()
{
	global $context, $settings, $scripturl, $txt;

	echo '
	<div id="sp_edit_shoutbox">
		<form  id="admin_form_wrapper" action="', $scripturl, '?action=admin;area=portalshoutbox;sa=edit" method="post" accept-charset="UTF-8">
			<h3 class="category_header">
				', $context['page_title'], '
			</h3>
				<div class="sp_content_padding">
					<dl class="sp_form">
						<dt>
							<label for="shoutbox_name">', $txt['sp_admin_shoutbox_col_name'], ':</label>
						</dt>
						<dd>
							<input type="text" name="name" id="shoutbox_name" value="', $context['SPortal']['shoutbox']['name'], '" class="input_text" />
						</dd>
						<dt>
							<label for="shoutbox_permissions">', $txt['sp_admin_shoutbox_col_permissions'], ':</label>
						</dt>
						<dd>
							<select name="permissions" id="shoutbox_permissions">';

	foreach ($context['SPortal']['shoutbox']['permission_profiles'] as $profile)
		echo '
								<option value="', $profile['id'], '"', $profile['id'] == $context['SPortal']['shoutbox']['permissions'] ? ' selected="selected"' : '', '>', $profile['label'], '</option>';

	echo '
							</select>
						</dd>
						<dt>
							', $txt['sp_admin_shoutbox_col_moderators'], ':
						</dt>
						<dd>
							<fieldset id="moderators">
								<legend><a href="javascript:void(0);" onclick="document.getElementById(\'moderators\').style.display = \'none\';document.getElementById(\'moderators_groups_link\').style.display = \'block\'; return false;">', $txt['avatar_select_permission'], '</a></legend>
								<ul class="permission_groups">';

	foreach ($context['moderator_groups'] as $group)
	{
		echo '
									<li><input type="checkbox" name="moderator_groups[', $group['id'], ']" id="moderator_groups_', $group['id'], '" value="', $group['id'], '"', !empty($group['checked']) ? ' checked="checked"' : '', ' class="input_check" /> <label for="moderator_groups_', $group['id'], '"', $group['is_post_group'] ? ' style="font-style: italic;"' : '', '>', $group['name'], '</label></li>';
	}
	echo '
									<li><input type="checkbox" id="moderator_groups_all" onclick="invertAll(this, this.form, \'moderator_groups\');" class="input_check" /> <label for="moderator_groups_all"><em>', $txt['check_all'], '</em></label></li>
								</ul>
							</fieldset>
							<a href="javascript:void(0);" onclick="document.getElementById(\'moderators\').style.display = \'block\'; document.getElementById(\'moderators_groups_link\').style.display = \'none\'; return false;" id="moderators_groups_link" style="display: none;">[ ', $txt['avatar_select_permission'], ' ]</a>
							<script"><!-- // --><![CDATA[
								document.getElementById("moderators").style.display = "none";
								document.getElementById("moderators_groups_link").style.display = "";
							// ]]></script>
						</dd>
						<dt>
							<a href="', $scripturl, '?action=quickhelp;help=sp-shoutboxesWarning" onclick="return reqOverlayDiv(this.href);" class="help">
								<img src="', $settings['images_url'], '/helptopics.png" alt="', $txt['help'], '" class="icon" />
							</a>
							<label for="shoutbox_warning">', $txt['sp_admin_shoutbox_col_warning'], ':</label>
						</dt>
						<dd>
							<input type="text" name="warning" id="shoutbox_warning" value="', $context['SPortal']['shoutbox']['warning'], '" size="25" class="input_text" />
						</dd>
						<dt>
							<a href="', $scripturl, '?action=quickhelp;help=sp-shoutboxesBBC" onclick="return reqOverlayDiv(this.href);" class="help">
								<img src="', $settings['images_url'], '/helptopics.png" alt="', $txt['help'], '" class="icon" />
							</a>
							<label for="shoutbox_bbc">', $txt['sp_admin_shoutbox_col_bbc'], ':</label>
						</dt>
						<dd>
							<select name="allowed_bbc[]" id="shoutbox_bbc" size="7" multiple="multiple">';

	foreach ($context['allowed_bbc'] as $tag => $label)
		if (!isset($context['disabled_tags'][$tag]))
			echo '
								<option value="', $tag, '"', in_array($tag, $context['SPortal']['shoutbox']['allowed_bbc']) ? ' selected="selected"' : '', '>[', $tag, '] - ', $label, '</option>';

	echo '
							</select>
						</dd>
						<dt>
							<label for="shoutbox_height">', $txt['sp_admin_shoutbox_col_height'], '</label>
						</dt>
						<dd>
							<input type="text" name="height" id="shoutbox_height" value="', $context['SPortal']['shoutbox']['height'], '" size="10" class="input_text" />
						</dd>
						<dt>
							<label for="shoutbox_num_show">', $txt['sp_admin_shoutbox_col_num_show'], ':</label>
						</dt>
						<dd>
							<input type="text" name="num_show" id="shoutbox_num_show" value="', $context['SPortal']['shoutbox']['num_show'], '" size="10" class="input_text" />
						</dd>
						<dt>
							<label for="shoutbox_num_max">', $txt['sp_admin_shoutbox_col_num_max'], ':</label>
						</dt>
						<dd>
							<input type="text" name="num_max" id="shoutbox_num_max" value="', $context['SPortal']['shoutbox']['num_max'], '" size="10" class="input_text" />
						</dd>
						<dt>
							<label for="shoutbox_reverse">', $txt['sp_admin_shoutbox_col_reverse'], ':</label>
						</dt>
						<dd>
							<input type="checkbox" name="reverse" id="shoutbox_reverse" value="1"', $context['SPortal']['shoutbox']['reverse'] ? ' checked="checked"' : '', ' class="input_check" />
						</dd>
						<dt>
							<label for="shoutbox_caching">', $txt['sp_admin_shoutbox_col_caching'], ':</label>
						</dt>
						<dd>
							<input type="checkbox" name="caching" id="shoutbox_caching" value="1"', $context['SPortal']['shoutbox']['caching'] ? ' checked="checked"' : '', ' class="input_check" />
						</dd>
						<dt>
							<label for="shoutbox_refresh">', $txt['sp_admin_shoutbox_col_refresh'], '</label>
						</dt>
						<dd>
							<input type="text" name="refresh" id="shoutbox_refresh" value="', $context['SPortal']['shoutbox']['refresh'], '" size="10" class="input_text" />
						</dd>
						<dt>
							<label for="shoutbox_status">', $txt['sp_admin_shoutbox_col_status'], ':</label>
						</dt>
						<dd>
							<input type="checkbox" name="status" id="shoutbox_status" value="1"', $context['SPortal']['shoutbox']['status'] ? ' checked="checked"' : '', ' class="input_check" />
						</dd>
					</dl>
					<input type="submit" name="submit" value="', $context['page_title'], '" class="right_submit" />
				</div>
			<input type="hidden" name="shoutbox_id" value="', $context['SPortal']['shoutbox']['id'], '" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>
	</div>';
}

function template_shoutbox_prune()
{
	global $context, $scripturl, $txt;

	echo '
	<form id="admin_form_wrapper" action="', $scripturl, '?action=admin;area=portalshoutbox;sa=prune" method="post" accept-charset="UTF-8">
		<h3 class="category_header">
			', $context['page_title'], '
		</h3>
			<div class="sp_content_padding">
				<dl class="sp_form">
					<dt>
					<input type="radio" name="type" id="type_all" value="all" class="input_radio" />
					<label for="type_all">', $txt['sp_admin_shoutbox_opt_all'], '</label>
					</dt>
					<dd>
					</dd>
					<dt>
					<input type="radio" name="type" id="type_days" value="days" class="input_radio" />
					<label for="type_days">', $txt['sp_admin_shoutbox_opt_days'], '</label>
					</dt>
					<dd>
						<input type="text" name="days" value="" size="5" onfocus="document.getElementById(\'type_days\').checked = true;" class="input_text" />
					</dd>
					<dt>
					<input type="radio" name="type" id="type_member" value="member" class="input_radio" />
					<label for="type_member">', $txt['sp_admin_shoutbox_opt_member'], '</label>
					</dt>
					<dd>
						<input type="text" name="member" id="member" value="" onclick="document.getElementById(\'type_member\').checked = true;" size="15" class="input_text" />
					</dd>
				</dl>
				<input type="submit" name="submit" value="', $context['page_title'], '" class="right_submit" />
			</div>
		<input type="hidden" name="shoutbox_id" value="', $context['shoutbox']['id'], '" />
		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
	</form>';

	addInlineJavascript('
		var oPruneSuggest = new smc_AutoSuggest({
			sSelf: \'oPruneSuggest\',
			sSessionId: elk_session_id,
			sSessionVar: elk_session_var,
			sSuggestId: \'member\',
			sControlId: \'member\',
			sSearchType: \'member\',
			sTextDeleteItem: \'' . $txt['autosuggest_delete_item'] . '\',
			bItemList: false
		});', true);
}

/**
 * Once a shoutbox has been created, displays a success message with instructions
 */
function template_shoutbox_block_redirect()
{
	global $context;

	echo '
	<div id="sp_shoutbox_redirect">
		<h3 class="category_header">
			', $context['page_title'], '
		</h3>
		<div class="infobox">
			<div class="sp_content_padding">
				', $context['redirect_message'], '
			</div>
		</div>
	</div>';
}