<?php

/**
 * @package SimplePortal ElkArte
 *
 * @author SimplePortal Team
 * @copyright 2015-2021 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.0 RC2
 */
function template_information()
{
	global $context, $txt;

	if ($context['in_admin'])
	{
		echo '
	<div id="sp_admin_main">
		<div id="admin_main_section">
			<div id="sp_live_info" class="floatleft">
				<h3 class="category_header hdicon cat_img_talk">
					', $txt['sp-info_live'], '
				</h3>
				<div class="content">
					<div id="spAnnouncements">', $txt['sp-info_no_live'], '</div>
				</div>
			</div>
			<div id="sp_general_info" class="floatright">
				<h3 class="category_header hdicon cat_img_config">
					', $txt['sp-info_general'], '
				</h3>
				<div class="content">
					<strong>', $txt['sp-info_versions'], ':</strong><br />
					', $txt['sp-info_your_version'], ':
					<em id="spYourVersion" style="white-space: nowrap;">', $context['sp_version'], '</em><br />
					', $txt['sp-info_current_version'], ':
					<em id="spCurrentVersion" style="white-space: nowrap;">???</em><br />
					<strong>', $txt['sp-info_managers'], ':</strong>
					', implode(', ', $context['sp_managers']), '
				</div>
			</div>
		</div>
		<script>
			var func = function ()
			{
				sp_currentVersion();
			}
			addLoadEvent(func);
		</script>';
	}

	echo '
		<div class="quick_tasks" id="sp_credits">';

	foreach ($context['sp_credits'] as $section)
	{
		if (isset($section['pretext']))
			echo '
			<p>', $section['pretext'], '</p>';

		foreach ($section['groups'] as $group)
		{
			if (empty($group['members']))
				continue;

			echo '
			<p>';

			if (isset($group['title']))
				echo '
				<strong>', $group['title'], ':</strong> ';

			echo implode(', ', $group['members']), '
			</p>';
		}


		if (isset($section['posttext']))
			echo '
			<p>', $section['posttext'], '</p>';
	}

	echo '
			<hr />
			<p>', sprintf($txt['sp-info_contribute'], 'https://simpleportal.net/index.php?page=contribute'), '</p>
		</div>
	</div>';
}

/**
 * Provide an xml response to an active on/off toggle
 */
function template_change_status()
{
	global $context, $txt;

	echo '<', '?xml version="1.0" encoding="UTF-8" ?', '>
	<elk>
		<id>', $context['item_id'], '</id>
		<status>', $context['status'], '</status>
		<label>', $txt['sp-blocks' . ($context['status'] === 'active' ? 'Deactivate' : 'Activate')] . '</label>
	</elk>';
}
