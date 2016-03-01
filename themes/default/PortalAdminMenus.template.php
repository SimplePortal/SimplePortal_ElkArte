<?php

/**
 * @package SimplePortal ElkArte
 *
 * @author SimplePortal Team
 * @copyright 2015 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.0 Beta 2
 */

function template_menus_custom_menu_edit()
{
	global $context, $scripturl, $txt;

	echo '
	<div id="sp_edit_custom_menu">
		<form id="admin_form_wrapper" action="', $scripturl, '?action=admin;area=portalmenus;sa=editcustommenu" method="post" accept-charset="UTF-8" onsubmit="submitonce(this);">
			<h3 class="category_header">
				', $context['page_title'], '
			</h3>
			<div class="sp_content_padding">
				<dl class="sp_form">
					<dt>
						<label for="menu_name">', $txt['sp_admin_menus_col_name'], ':</label>
					</dt>
					<dd>
						<input type="text" name="name" id="menu_name" value="', $context['menu']['name'], '" class="input_text" />
					</dd>
				</dl>
				<div class="submitbutton">
					<input type="submit" name="submit" value="', $context['page_title'], '" class="button_submit" />
				</div>
			</div>
			<input type="hidden" name="menu_id" value="', $context['menu']['id'], '" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>
	</div>';
}

function template_menus_custom_item_edit()
{
	global $context, $scripturl, $txt;

	echo '
	<div id="sp_edit_custom_item">
		<form id="admin_form_wrapper" action="', $scripturl, '?action=admin;area=portalmenus;sa=editcustomitem;menu_id=', $context['menu']['id'], '" method="post" accept-charset="UTF-8" onsubmit="submitonce(this);">
			<h3 class="category_header">
				', $context['page_title'], '
			</h3>
			<div class="sp_content_padding">
				<dl class="sp_form">
					<dt>
						<label for="item_title">', $txt['sp_admin_menus_col_title'], ':</label>
					</dt>
					<dd>
						<input type="text" name="title" id="item_title" value="', $context['item']['title'], '" class="input_text" />
					</dd>
					<dt>
						<label for="item_namespace">', $txt['sp_admin_menus_col_namespace'], ':</label>
					</dt>
					<dd>
						<input type="text" name="namespace" id="item_namespace" value="', $context['item']['namespace'], '" class="input_text" />
					</dd>
					<dt>
						<label for="item_link_type">', $txt['sp_admin_menus_col_link_type'], ':</label>
					</dt>
					<dd>
						<select name="link_type" id="item_link_type" onchange="sp_update_link();">
							<option value="custom">', $txt['sp_admin_menus_link_type_custom'], '</option>';

	foreach ($context['items'] as $type => $items)
	{
		if (empty($items))
			continue;

		echo '
							<option value="', $type, '">', $txt['sp_admin_menus_link_type_' . $type], '</option>';
	}

	echo '
						</select>
					</dd>
					<dt id="item_link_dt">
						<label for="item_link_item">', $txt['sp_admin_menus_col_link_item'], ':</label>
					</dt>
					<dd id="item_link_dd">
						<select name="link_item" id="item_link_item">
						</select>
					</dd>
					<dt id="item_url_dt">
						<label for="item_url">', $txt['sp_admin_menus_col_url'], ':</label>
					</dt>
					<dd id="item_url_dd">
						<input type="text" name="url" id="item_url" value="', $context['item']['url'], '" class="input_text" size="40" />
					</dd>
					<dt>
						<label for="item_target">', $txt['sp_admin_menus_col_target'], ':</label>
					</dt>
					<dd>
						<select name="target" id="item_target">
							<option value="0"', $context['item']['target'] == 0 ? ' selected="selected"' : '', '>', $txt['sp_admin_menus_link_target_0'], '</option>
							<option value="1"', $context['item']['target'] == 1 ? ' selected="selected"' : '', '>', $txt['sp_admin_menus_link_target_1'], '</option>
						</select>
					</dd>
				</dl>
				<div class="submitbutton">
					<input type="submit" name="submit" class="button_submit" value="', $context['page_title'], '" />
				</div>
			</div>
			<input type="hidden" name="item_id" value="', $context['item']['id'], '" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>
	</div>
	<script><!-- // --><![CDATA[
		var sp_link_items = {';

	$sets = array();
	foreach ($context['items'] as $type => $items)
	{
		if (empty($items))
			continue;

		$set = array();
		foreach ($items as $id => $title)
		{
			$set[] = JavaScriptEscape($id) . ': ' . JavaScriptEscape($title);
		}

		$sets[] = JavaScriptEscape($type) . ': {
				' . implode(',
				', $set) . '
			}';
	}

	echo '
			', implode(',
			', $sets), '
		};

		function sp_update_link()
		{
			var type_select = document.getElementById("item_link_type"),
				item_select = document.getElementById("item_link_item"),
				new_value = type_select.options[type_select.selectedIndex].value;

			while (item_select.options.length)
				item_select.options[0] = null;

			for (var key in sp_link_items[new_value])
				item_select.options[item_select.length] = new Option(sp_link_items[new_value][key], key);

			document.getElementById("item_link_dt").style.display = document.getElementById("item_link_dd").style.display = new_value == "custom" ? "none" : "block";
			document.getElementById("item_url_dt").style.display = document.getElementById("item_url_dd").style.display = new_value != "custom" ? "none" : "block";
		}
	// ]]></script>';
}