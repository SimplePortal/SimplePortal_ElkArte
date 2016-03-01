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
 * Show the list of available blocks in the system
 * If no specific area is provided will show all areas with available blocks in each
 * otherwise will just show the chosen area
 */
function template_block_list()
{
	global $context, $scripturl, $txt;

	$sortables = array();

	echo '
	<div id="sp_manage_blocks">';

	// Show each portal area with the blocks in each one
	foreach($context['sides'] as $id => $side)
	{
		$sortables[] = '#side_' . $side['id'];

		echo '
		<h3 class="category_header">
			<a class="floatright" href="', $scripturl, '?action=admin;area=portalblocks;sa=add;col=', $side['id'], '">', sp_embed_image('add', sprintf($txt['sp-blocksCreate'], $side['label'])), '</a>
			<a class="hdicon cat_img_helptopics help" href="', $scripturl, '?action=quickhelp;help=', $side['help'], '" onclick="return reqOverlayDiv(this.href);" title="', $txt['help'], '"></a>
			<a href="', $scripturl, '?action=admin;area=portalblocks;sa=', $id, '">', $side['label'], ' ', $txt['sp-blocksBlocks'], '</a>
		</h3>
		<table class="table_grid">
			<thead>
				<tr class="table_head">';

		foreach ($context['columns'] as $column)
			echo '
					<th scope="col"', isset($column['class']) ? ' class="' . $column['class'] . '"' : '', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', '>', $column['label'], '</th>';

		echo '
				</tr>
			</thead>
			<tbody id="side_', $side['id'] ,'" class="sortme">';

		if (empty($context['blocks'][$side['name']]))
		{
			echo '
				<tr class="content">
					<td class="centertext noticebox" colspan="4"></td>
				</tr>';
		}

		foreach($context['blocks'][$side['name']] as $block)
		{
			echo '
				<tr id="block_',$block['id'],'" class="content">
					<td>', $block['label'], '</td>
					<td>', $block['type_text'], '</td>
					<td class="centertext">', implode(' ', $block['actions']), '</td>
				</tr>';
		}

		echo '
			</tbody>
		</table>';
	}

	// Engage sortable to allow drag/drop arrangement of the blocks
	echo '
	</div>
	<script>
		// Set up our sortable call
		$().elkSortable({
			sa: "blockorder",
			error: "' . $txt['admin_order_error'] . '",
			title: "' . $txt['admin_order_title'] . '",
			token: {token_var: "' . $context['admin-sort_token_var'] . '", token_id: "' . $context['admin-sort_token'] . '"},
			tag: "' . implode(',', $sortables) . '",
			connect: ".sortme",
			containment: "#sp_manage_blocks",
			href: "?action=admin;area=portalblocks",
			placeholder: "ui-state-highlight",
			axis: "y"
		});
	</script>';
}

/**
 * Used to edit a blocks details when using the block on the portal
 */
function template_block_edit()
{
	global $context, $settings, $options, $scripturl, $txt, $helptxt;

	// Want to take a look before you save?
	if (!empty($context['SPortal']['preview']))
	{
		if (!empty($context['SPortal']['error']))
			echo '
	<div class="errorbox">' , $context['SPortal']['error'], '</div>';

		echo '
	<div class="sp_auto_align" style="width: ', $context['widths'][$context['SPortal']['block']['column']], ';">';

		template_block($context['SPortal']['block']);

		echo '
	</div>';
	}

	echo '
	<div id="sp_edit_block">
		<form id="admin_form_wrapper" name="sp_edit_block_form" id="sp_edit_block_form" action="', $scripturl, '?action=admin;area=portalblocks;sa=edit" method="post" accept-charset="UTF-8" onsubmit="submitonce(this);">
			<h3 class="category_header">
				<a class="hdicon cat_img_helptopics help" href="', $scripturl, '?action=quickhelp;help=sp-blocks', $context['SPortal']['is_new'] ? 'Add' : 'Edit', '" onclick="return reqOverlayDiv(this.href);" title="', $txt['help'], '"></a>
				', $context['SPortal']['is_new'] ? $txt['sp-blocksAdd'] : $txt['sp-blocksEdit'], '
			</h3>
				<div class="sp_content_padding">
					<dl class="sp_form">
						<dt>
							', $txt['sp-adminColumnType'], ':
						</dt>
						<dd>
							', $context['SPortal']['block']['type_text'], '
						</dd>
						<dt>
							<label for="block_name">', $txt['sp-adminColumnName'], ':</label>
						</dt>
						<dd>
							<input type="text" name="block_name" id="block_name" value="', $context['SPortal']['block']['label'], '" size="30" class="input_text" />
						</dd>
						<dt>
							<label for="block_permissions">', $txt['sp_admin_blocks_col_permissions'], ':</label>
						</dt>
						<dd>
							<select name="permissions" id="block_permissions">';

	foreach ($context['SPortal']['block']['permission_profiles'] as $profile)
		echo '
									<option value="', $profile['id'], '"', $profile['id'] == $context['SPortal']['block']['permissions'] ? ' selected="selected"' : '', '>', $profile['label'], '</option>';

	echo '
						</select>
					</dd>
					<dt>
						<label for="block_styles">', $txt['sp_admin_blocks_col_styles'], ':</label>
					</dt>
					<dd>
						<select name="styles" id="block_styles">';

	foreach ($context['SPortal']['block']['style_profiles'] as $profile)
		echo '
							<option value="', $profile['id'], '"', $profile['id'] == $context['SPortal']['block']['styles'] ? ' selected="selected"' : '', '>', $profile['label'], '</option>';

	echo '
						</select>
					</dd>
					<dt>
						<label for="block_visibility">', $txt['sp_admin_blocks_col_visibility'], ':</label>
					</dt>
					<dd>
						<select name="visibility" id="block_visibility">';

	foreach ($context['SPortal']['block']['visibility_profiles'] as $profile)
		echo '
					<option value="', $profile['id'], '"', $profile['id'] == $context['SPortal']['block']['visibility'] ? ' selected="selected"' : '', '>', $profile['label'], '</option>';

	echo '
					</select>
				</dd>';

	// Display any options that are available for this block
	foreach ($context['SPortal']['block']['options'] as $name => $type)
	{
		if (empty($context['SPortal']['block']['parameters'][$name]))
			$context['SPortal']['block']['parameters'][$name] = '';

		echo '
						<dt>';

		if (!empty($helptxt['sp_param_' . $context['SPortal']['block']['type'] . '_' . $name]))
			echo '
							<a class="help" href="', $scripturl, '?action=quickhelp;help=sp_param_', $context['SPortal']['block']['type'] , '_' , $name, '" onclick="return reqOverlayDiv(this.href);">
								<img class="icon" src="', $settings['images_url'], '/helptopics.png" alt="', $txt['help'], '" />
							</a>';

		echo '
							<label for="', $type === 'bbc' ? 'bbc_content' : $name, '">', $txt['sp_param_' . $context['SPortal']['block']['type'] . '_' . $name], ':</label>
						</dt>
						<dd>';

		if ($type === 'bbc')
		{
			echo '
						</dd>
					</dl>
					<div id="sp_rich_editor">
						<div id="sp_rich_bbc"></div>
						<div id="sp_rich_smileys"></div>
						', template_control_richedit($context['SPortal']['bbc'], 'sp_rich_smileys', 'sp_rich_bbc'), '
						<input type="hidden" name="bbc_name" value="', $name, '" />
						<input type="hidden" name="bbc_parameter" value="', $context['SPortal']['bbc'], '" />
					</div>
					<dl class="sp_form">';
		}
		elseif ($type === 'boards' || $type === 'board_select')
		{
					echo '
							<input type="hidden" name="parameters[', $name, ']" value="" />';

				if ($type === 'boards')
					echo '
							<select name="parameters[', $name, '][]" id="', $name, '" size="7" multiple="multiple">';
				else
					echo '
							<select name="parameters[', $name, '][]" id="', $name, '">';

				foreach ($context['SPortal']['block']['board_options'][$name] as $option)
					echo '
								<option value="', $option['value'], '"', ($option['selected'] ? ' selected="selected"' : ''), ' >', $option['text'], '</option>';

				echo '
							</select>';
		}
		elseif ($type === 'int')
			echo '
							<input type="text" name="parameters[', $name, ']" id="', $name, '" value="', $context['SPortal']['block']['parameters'][$name],'" size="7" class="input_text" />';
		elseif ($type === 'text')
			echo '
							<input type="text" name="parameters[', $name, ']" id="', $name, '" value="', $context['SPortal']['block']['parameters'][$name],'" size="25" class="input_text" />';
		elseif ($type === 'check')
				echo '
							<input type="checkbox" name="parameters[', $name, ']" id="', $name, '"', !empty($context['SPortal']['block']['parameters'][$name]) ? ' checked="checked"' : '', ' class="input_check" />';
		elseif ($type === 'select')
		{
				$options = explode('|', $txt['sp_param_' . $context['SPortal']['block']['type'] . '_' . $name . '_options']);

				echo '
							<select name="parameters[', $name, ']" id="', $name, '">';

				foreach ($options as $key => $option)
					echo '
								<option value="', $key, '"', $context['SPortal']['block']['parameters'][$name] == $key ? ' selected="selected"' : '', '>', $option, '</option>';

				echo '
							</select>';
		}
		elseif (is_array($type))
		{
				echo '
							<select name="parameters[', $name, ']" id="', $name, '">';

				foreach ($type as $key => $option)
					echo '
								<option value="', $key, '"', $context['SPortal']['block']['parameters'][$name] == $key ? ' selected="selected"' : '', '>', $option, '</option>';

				echo '
							</select>';
		}
		elseif ($type === 'textarea')
		{
			echo '
						</dd>
					</dl>
					<div id="sp_text_editor">
						<textarea name="parameters[', $name, ']" id="', $name, '" cols="45" rows="10">', $context['SPortal']['block']['parameters'][$name], '</textarea>
						<input type="button" class="button_submit" value="-" onclick="document.getElementById(\'', $name, '\').rows -= 10" />
						<input type="button" class="button_submit" value="+" onclick="document.getElementById(\'', $name, '\').rows += 10" />
					</div>
					<dl class="sp_form">';
		}

		if ($type != 'bbc')
			echo '
						</dd>';
	}

	if (empty($context['SPortal']['block']['column']))
	{
		echo '
						<dt>
							<label for="block_column">', $txt['sp-blocksColumn'], ':</label>
						</dt>
						<dd>
							<select id="block_column" name="block_column">';

		$block_sides = array(5 => 'Header', 1 => 'Left', 2 => 'Top', 3 => 'Bottom', 4 => 'Right', 6 => 'Footer');
		foreach ($block_sides as $id => $side)
			echo '
								<option value="', $id, '">', $txt['sp-position' . $side], '</option>';

		echo '
							</select>
						</dd>';
	}

	if (count($context['SPortal']['block']['list_blocks']) > 1)
	{
		echo '
						<dt>
							', $txt['sp-blocksRow'], ':
						</dt>
						<dd>
							<select id="order" name="placement"', !$context['SPortal']['is_new'] ? ' onchange="this.form.block_row.disabled = this.options[this.selectedIndex].value == \'\';"' : '', '>
								', !$context['SPortal']['is_new'] ? '<option value="nochange">' . $txt['sp-placementUnchanged'] . '</option>' : '', '
								<option value="before"', (!empty($context['SPortal']['block']['placement']) && $context['SPortal']['block']['placement'] === 'before' ? ' selected="selected"' : ''), '>', $txt['sp-placementBefore'], '...</option>
								<option value="after"', (!empty($context['SPortal']['block']['placement']) && $context['SPortal']['block']['placement'] === 'after' ? ' selected="selected"' : ''), '>', $txt['sp-placementAfter'], '...</option>
							</select>
							<select id="block_row" name="block_row"', !$context['SPortal']['is_new'] ? ' disabled="disabled"' : '', '>';

		foreach ($context['SPortal']['block']['list_blocks'] as $block)
		{
			if ($block['id'] != $context['SPortal']['block']['id'])
				echo '
								<option value="', $block['row'], '"', (!empty($context['SPortal']['block']['row']) && $context['SPortal']['block']['row'] == $block['row'] ? ' selected="selected"' : ''), '>', $block['label'], '</option>';		}

		echo '
							</select>
						</dd>';
	}

	if ($context['SPortal']['block']['type'] != 'sp_boardNews')
	{
		echo '
						<dt>
							<label for="block_force">', $txt['sp-blocksForce'], ':</label>
						</dt>
						<dd>
							<input type="checkbox" name="block_force" id="block_force" value="1"', $context['SPortal']['block']['force_view'] ? ' checked="checked"' : '', ' class="input_check" />
						</dd>';
	}

	echo '
				<dt>
					<label for="block_active">', $txt['sp-blocksActive'], ':</label>
				</dt>
				<dd>
					<input type="checkbox" name="block_active" id="block_active" value="1"', $context['SPortal']['block']['state'] ? ' checked="checked"' : '', ' class="input_check" />
				</dd>
			</dl>
			<div class="submitbutton">
				<input type="submit" name="preview_block" value="', $txt['sp-blocksPreview'], '" class="button_submit" />
				<input type="submit" name="add_block" value="', !$context['SPortal']['is_new'] ? $txt['sp-blocksEdit'] : $txt['sp-blocksAdd'], '" class="button_submit" />
			</div>
		</div>';

	if (!empty($context['SPortal']['block']['column']))
		echo '
			<input type="hidden" name="block_column" value="', $context['SPortal']['block']['column'], '" />';

	echo '
			<input type="hidden" name="block_type" value="', $context['SPortal']['block']['type'], '" />
			<input type="hidden" name="block_id" value="', $context['SPortal']['block']['id'], '" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>
	</div>';
}

/**
 * Used to select one of our predefined blocks for use in the portal
 */
function template_block_select_type()
{
	global $context, $scripturl, $txt;

	echo '
	<div id="sp_select_block_type">
		<h3 class="category_header">
			<a class="hdicon cat_img_helptopics help" href="', $scripturl, '?action=quickhelp;help=sp-blocksSelectType" onclick="return reqOverlayDiv(this.href);" title="', $txt['help'], '"></a>
			', $txt['sp-blocksSelectType'], '
		</h3>
		<form id="admin_form_wrapper" action="', $scripturl, '?action=admin;area=portalblocks;sa=add" method="post" accept-charset="UTF-8">
			<ul class="reset">';

	// For every block type defined in the system
	foreach($context['SPortal']['block_types'] as $index => $type)
	{
		$this_block = isset($context['SPortal']['block_inuse'][$type['function']]) ? $context['SPortal']['block_inuse'][$type['function']] : false;
		$this_title = !empty($this_block) ? sprintf($txt['sp-adminBlockInuse'], $context['location'][$this_block['column']]) . ': ' . (!empty($this_block['state']) ? '(' . $txt['sp-blocksActive'] . ')' : '') : '';

		echo '
				<li class="content">
					<input type="radio" name="selected_type[]" id="block_', $type['function'], '" value="', $type['function'], '" class="input_radio" />
					<strong><label ', (!empty($this_block) ? 'class="sp_block_active" ' : ''), 'for="block_', $type['function'], '" title="', $this_title, '">', isset($txt['sp_function_' . $type['function'] . '_label']) ? $txt['sp_function_' . $type['function'] . '_label'] : $type['function'], '</label></strong>
					<p class="smalltext">', isset($txt['sp_function_' . $type['function'] . '_desc']) ? $txt['sp_function_' . $type['function'] . '_desc'] : $txt['not_applicable'], '</p>
				</li>';
	}

	echo '
				</ul>
				<input type="submit" name="select_type" value="', $txt['sp-blocksSelectType'], '" class="right_submit" />';

	if (!empty($context['SPortal']['block']['column']))
		echo '
			<input type="hidden" name="block_column" value="', $context['SPortal']['block']['column'], '" />';

	echo '
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>
	</div>';
}