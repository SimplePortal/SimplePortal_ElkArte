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
 * The all template
 */
function template_shoutbox_all()
{
	template_shoutbox_all_default();
}

/**
 * Template to display all of the shouts in a system
 */
function template_shoutbox_all_default()
{
	global $context, $txt;

	echo '
	<h3 class="category_header">
		', $context['SPortal']['shoutbox']['name'], '
	</h3>
		<div class="sp_content_padding">';

	template_pagesection();

	echo '
			<div class="shoutbox_body">
				<ul class="shoutbox_list_all" id="shouts">';

	if (!empty($context['SPortal']['shouts_history']))
		foreach ($context['SPortal']['shouts_history'] as $shout)
			echo '
					', !$shout['is_me']
			? '<li class="smalltext"><strong>' . $shout['author']['link'] . ':</strong></li>' : '', '
					<li class="smalltext">', str_replace('ignored_shout', 'history_ignored_shout', $shout['text']), '</li>
					<li class="smalltext shoutbox_time">', $shout['delete_link'], $shout['time'], '</li>';
	else
		echo '
					<li class="smalltext">', $txt['sp_shoutbox_no_shout'], '</li>';

	echo '
				</ul>
			</div>';

	template_pagesection();

	echo '
	</div>';
}

/**
 * Embeds a shoutbox block on a page
 *
 * @param mixed[] $shoutbox
 */
function template_shoutbox_embed($shoutbox)
{
	global $context, $scripturl, $settings, $txt;

	echo '
	<form method="post">
		<div class="shoutbox_container">
			<div class="shoutbox_info">
				<div id="shoutbox_load_', $shoutbox['id'], '" style="float: right; display: none;"><i class="fa fa-spinner fa-spin"></i></div>
				<a ', sp_embed_class('refresh'), ' href="', $scripturl, '?action=shoutbox;shoutbox_id=', $shoutbox['id'], '" onclick="sp_refresh_shout(', $shoutbox['id'], ', last_refresh_', $shoutbox['id'], '); return false;"></a>
				<a ', sp_embed_class('history'), ' href="', $scripturl, '?action=shoutbox;shoutbox_id=', $shoutbox['id'], '"></a>';

	if ($context['can_shout'])
		echo ' <a ', sp_embed_class('smiley'), ' href="#smiley" onclick="sp_collapse_object(\'sb_smiley_', $shoutbox['id'], '\', false); return false;"></a> <a ', sp_embed_class('style'), ' href="#style" onclick="sp_collapse_object(\'sb_style_', $shoutbox['id'], '\', false); return false;"></a>';

	echo '
			</div>';

	if ($context['can_shout'])
	{
		// Smiley box
		echo '
			<div id="sp_object_sb_smiley_', $shoutbox['id'], '" style="display: none;">';

		foreach ($shoutbox['smileys']['normal'] as $smiley)
			echo '
				<a href="javascript:void(0);" onclick="replaceText(\' ', $smiley['code'], '\', document.getElementById(\'new_shout_', $shoutbox['id'], '\')); return false;"><img src="', $settings['smileys_url'], '/', $smiley['filename'], '" alt="', $smiley['description'], '" title="', $smiley['description'], '" /></a>';

		if (!empty($shoutbox['smileys']['popup']))
			echo '
					<a onclick="sp_showMoreSmileys(\'', $shoutbox['id'], '\', \'', $txt['more_smileys_title'], '\', \'', $txt['more_smileys_pick'], '\', \'', $txt['more_smileys_close_window'], '\', \'', $settings['theme_url'], '\', \'', $settings['smileys_url'], '\'); return false;" href="javascript:void(0);">[', $txt['more_smileys'], ']</a>';

		// BBC box
		echo '
			</div>
			<div id="sp_object_sb_style_', $shoutbox['id'], '" class="shoutbox_bbc_container" style="display: none;">';

		// For each bbc code we allow in this shoutbox
		foreach ($shoutbox['bbc'] as $image => $tag)
		{
			if (!in_array($tag['code'], $shoutbox['allowed_bbc']))
				continue;

			// Add all enabled shoutbox BBC buttons
			echo '
				<a href="#" class="shoutbox-button shoutbox-', $image, '" title="', $tag['description'], '" ';

			// Set the button click action
			if (!isset($tag['after']))
				echo 'onclick="replaceText(\'', $tag['before'], '\', document.getElementById(\'new_shout_', $shoutbox['id'], '\')); return false;">';
			else
				echo 'onclick="sp_surroundText(\'', $tag['before'], '\', \'', $tag['after'], '\', document.getElementById(\'new_shout_', $shoutbox['id'], '\')); return false;">';

			echo '
					<div></div>
				</a>';
		}

		echo '
			</div>';
	}

	// The shouts!
	echo '
			<div class="shoutbox_body">
				<ul class="shoutbox_list_compact" id="shouts_', $shoutbox['id'], '"', !empty($shoutbox['height'])
		? ' style="height: ' . $shoutbox['height'] . 'px;"' : '', '>';

	if (!empty($shoutbox['warning']))
		echo '
					<li class="shoutbox_warning">', $shoutbox['warning'], '</li>';

	if (!empty($shoutbox['shouts']))
		foreach ($shoutbox['shouts'] as $shout)
			echo '
					<li>', !$shout['is_me'] ? '<strong>' . $shout['author']['link'] . ':</strong> '
				: '', $shout['text'], '<br />', !empty($shout['delete_link_js'])
				? '<span class="shoutbox_delete">' . $shout['delete_link_js'] . '</span>'
				: '', '<span class="smalltext shoutbox_time">', $shout['time'], '</span></li>';
	else
		echo '
					<li>', $txt['sp_shoutbox_no_shout'], '</li>';

	echo '
				</ul>
			</div>';

	// Show an input box, if they can use it
	if ($context['can_shout'])
		echo '
			<div id="new_shout">
				<input type="text" name="new_shout" id="new_shout_', $shoutbox['id'], '" class="shoutbox_input floatleft input_text"', isBrowser('is_ie8')
			? ' onkeypress="if (sp_catch_enter(event)) { sp_submit_shout(' . $shoutbox['id'] . ', \'' . $context['session_var'] . '\', \'' . $context['session_id'] . '\'); return false; }"'
			: '', ' />
				<button type="submit" name="submit_shout" value="', $txt['sp_shoutbox_button'], '" class="right_submit" onclick="sp_submit_shout(', $shoutbox['id'], ', \'', $context['session_var'], '\', \'', $context['session_id'], '\'); return false;">', $txt['sp_shoutbox_button'], '</button>
			</div>';

	echo '
		</div>
		<input type="hidden" name="shoutbox_id" value="', $shoutbox['id'], '" />
		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
	</form>

	<script><!-- // --><![CDATA[
		var last_refresh_', $shoutbox['id'], ' = ', time(), ';';

	if ($shoutbox['reverse'])
		echo '
		var objDiv = document.getElementById("shouts_', $shoutbox['id'], '");
		objDiv.scrollTop = objDiv.scrollHeight;';

	if (!empty($shoutbox['refresh']))
		echo '
		var interval_id_', $shoutbox['id'], ' = setInterval("sp_auto_refresh_', $shoutbox['id'], '()", ', $shoutbox['refresh'], ' * 1000);
		function sp_auto_refresh_', $shoutbox['id'], '()
		{
			if (window.XMLHttpRequest)
			{
				sp_refresh_shout(', $shoutbox['id'], ', last_refresh_', $shoutbox['id'], ');
				last_refresh_', $shoutbox['id'], ' += ', $shoutbox['refresh'], ';
			}
			else
				clearInterval(interval_id_', $shoutbox['id'], ');
		}';

	// Setup the data for the popup smileys.
	if (!empty($shoutbox['smileys']['popup']))
	{
		echo '
		if (sp_smileys == undefined)
			var sp_smileys = [';

		foreach ($shoutbox['smileys']['popup'] as $smiley)
		{
			echo '
					["', $smiley['code'], '","', $smiley['filename'], '","', $smiley['js_description'], '"]';
			if (empty($smiley['last']))
				echo ',';
		}

		echo ']';

		echo '
		if (sp_moreSmileysTemplate == undefined)
		{
			var sp_moreSmileysTemplate = ', JavaScriptEscape('<!DOCTYPE html>
					<html>
						<head>
							<title>' . $txt['more_smileys_title'] . '</title>
		<link rel="stylesheet" type="text/css" href="' . $settings['theme_url'] . '/css/index' . $context['theme_variant'] . '.css" />
						</head>
						<body id="help_popup">
		<div class="padding">
								<h3 class="category_header">
									' . $txt['more_smileys_pick'] . '
								</h3>
								<div class="padding">
									%smileyRows%
								</div>
								<div class="smalltext centertext">
									<a href="javascript:window.close();">' . $txt['more_smileys_close_window'] . '</a>
								</div>
							</div>
						</body>
					</html>'), '
		}';
	}

	echo '
	// ]]></script>';
}

/**
 * Return an xml response to a shoutbox
 */
function template_shoutbox_xml()
{
	global $context, $txt;

	echo '<', '?xml version="1.0" encoding="UTF-8"?', '>
<elk>
	<shoutbox>', $context['SPortal']['shoutbox']['id'], '</shoutbox>';

	if ($context['SPortal']['updated'])
	{
		echo '
	<updated>1</updated>
	<error>', empty($context['SPortal']['shouts']) ? $txt['sp_shoutbox_no_shout'] : 0, '</error>
	<warning>', !empty($context['SPortal']['shoutbox']['warning'])
			? Util::htmlspecialchars($context['SPortal']['shoutbox']['warning'], ENT_COMPAT, 'UTF-8', true) : 0,
	'</warning>
	<reverse>', !empty($context['SPortal']['shoutbox']['reverse']) ? 1 : 0, '</reverse>';

		foreach ($context['SPortal']['shouts'] as $shout)
			echo '
	<shout>
		<id>', $shout['id'], '</id>
		<author>', Util::htmlspecialchars($shout['author']['link'], ENT_COMPAT, 'UTF-8', true), '</author>
		<time>', Util::htmlspecialchars($shout['time'], ENT_COMPAT, 'UTF-8', true), '</time>
		<timeclean>', Util::htmlspecialchars(strip_tags($shout['time']), ENT_COMPAT, 'UTF-8', true), '</timeclean>
		<delete>', !empty($shout['delete_link_js']) ? Util::htmlspecialchars($shout['delete_link_js'], ENT_COMPAT, 'UTF-8', true) : ' ', '</delete>
		<content>', Util::htmlspecialchars($shout['text'], ENT_COMPAT, 'UTF-8', true), '</content>
		<is_me>', $shout['is_me'] ? 1 : 0, '</is_me>
	</shout>';
	}
	else
		echo '
	<updated>0</updated>';

	echo '
</elk>';
}