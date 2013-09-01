<?php

/**
 * @package SimplePortal
 *
 * @author SimplePortal Team
 * @copyright 2013 SimplePortal Team
 * @license BSD 3-clause
 *
 * @version 2.4
 */
function template_information()
{
	global $context, $txt;

	if ($context['in_admin'])
	{
		echo '
	<div id="sp_admin_main">
		<div id="admin_main_section">
			<div id="sp_live_info" class="sp_float_left">
				<div class="cat_bar">
					<h3 class="catbg">
						', $txt['sp-info_live'], '
					</h3>
				</div>
				<div class="windowbg">
					<div id="spAnnouncements" style="">', $txt['sp-info_no_live'], '</div>
				</div>
			</div>
			<div id="sp_general_info" class="sp_float_right">
				<div class="cat_bar">
					<h3 class="catbg">
						', $txt['sp-info_general'], '
					</h3>
				</div>
				<div class="windowbg">
					<div class="sp_content_padding">
						<strong>', $txt['sp-info_versions'], ':</strong><br />
						', $txt['sp-info_your_version'], ':
						<em id="spYourVersion" style="white-space: nowrap;">', $context['sp_version'], '</em><br />
						', $txt['sp-info_current_version'], ':
						<em id="spCurrentVersion" style="white-space: nowrap;">??</em><br />
						<strong>', $txt['sp-info_managers'], ':</strong>
						', implode(', ', $context['sp_managers']), '
					</div>
				</div>
			</div>
		</div>
		<script type="text/javascript" src="http://www.simpleportal.net/sp/current-version.js"></script>
		<script type="text/javascript" src="http://www.simpleportal.net/sp/latest-news.js"></script>
		<script type="text/javascript"><!-- // --><![CDATA[
			function spSetAnnouncements()
			{
				if (typeof(window.spAnnouncements) == "undefined" || typeof(window.spAnnouncements.length) == "undefined")
					return;

				var str = "<div style=\"margin: 4px; font-size: 0.85em;\">";

				for (var i = 0; i < window.spAnnouncements.length; i++)
				{
					str += "\n	<div style=\"padding-bottom: 2px;\"><a hre" + "f=\"" + window.spAnnouncements[i].href + "\">" + window.spAnnouncements[i].subject + "<" + "/a> ', $txt['on'], ' " + window.spAnnouncements[i].time + "<" + "/div>";
					str += "\n	<div style=\"padding-left: 2ex; margin-bottom: 1.5ex; border-top: 1px dashed;\">"
					str += "\n		" + window.spAnnouncements[i].message;
					str += "\n	<" + "/div>";
				}

				setInnerHTML(document.getElementById("spAnnouncements"), str + "<" + "/div>");
			}

			function spCurrentVersion()
			{
				var spVer, yourVer;

				if (typeof(window.spVersion) != "string")
					return;

				spVer = document.getElementById("spCurrentVersion");
				yourVer = document.getElementById("spYourVersion");

				setInnerHTML(spVer, window.spVersion);

				var currentVersion = getInnerHTML(yourVer);
				if (currentVersion != window.spVersion)
					setInnerHTML(yourVer, "<span class=\"alert\">" + currentVersion + "<" + "/span>");
			}';

		echo '
			var func = function ()
			{
				spSetAnnouncements();
				spCurrentVersion();
			}
			addLoadEvent(func);
		// ]]></script>';
	}

	echo '
		<div class="windowbg2 quick_tasks">
			<div class="sp_content_padding" id="sp_credits">';

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
				<p>', sprintf($txt['sp-info_contribute'], 'http://www.simpleportal.net/index.php?page=contribute'), '</p>
			</div>
		</div>
	</div>';
}