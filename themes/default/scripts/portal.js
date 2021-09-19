/**
 * @package SimplePortal ElkArte
 *
 * @author SimplePortal Team
 * @copyright 2015-2021 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.0.0
 */

/** global: editor, start_state */

/**
 * Used to collapse an individual block
 *
 * @param {string} id
 */
function sp_collapseBlock(id)
{
	$("#sp_block_" + id).slideToggle(300).promise().done(function ()
	{
		let mode = false;

		if ($("#sp_block_" + id).is(":visible"))
		{
			mode = true;
		}

		// Save the choice, one way for guest, or theme options for members
		if (elk_member_id === 0)
		{
			document.cookie = "sp_block_" + id + "=" + (mode ? 0 : 1);
		}
		else
		{
			elk_setThemeOption("sp_block_" + id, mode ? 0 : 1, null, null);
		}

		// Swap the class to change the icon
		$("#sp_collapse_" + id).attr("class", mode ? "chevricon i-chevron-up" : "chevricon i-chevron-down");
	});
}

/**
 * Used to collapse sides (if enabled)
 *
 * @param {string} id
 */
function sp_collapseSide(id)
{
	let sp_sides = [];

	sp_sides[1] = "sp_left";
	sp_sides[4] = "sp_right";

	let mode = document.getElementById(sp_sides[id]).style.display === "" ? 0 : 1;

	// Guests use a cookie, members a theme option to remember the choice
	if (elk_member_id === 0)
	{
		document.cookie = sp_sides[id] + "=" + (mode ? 0 : 1);
	}
	else
	{
		elk_setThemeOption(sp_sides[id], mode ? 0 : 1, null, null);
	}

	// Update the side expand/collapse image
	document.getElementById("sp_collapse_side" + id).className = (mode ? "icon collapse" : "icon expand");

	// Hide the side with a touch of animation
	$('#' + sp_sides[id]).toggle(400);
}

/**
 * Used to collapse the smiley box in the shoutbox
 *
 * @param {string} id
 * @param {boolean} has_image
 */
function sp_collapse_object(id, has_image)
{
	let mode = document.getElementById("sp_object_" + id).style.display === '' ? 0 : 1;

	$("#sp_object_" + id).toggle(300);

	if (typeof (has_image) === "undefined" || has_image === true)
	{
		document.getElementById("sp_collapse_" + id).src = elk_images_url + (mode ? '/collapse.png' : '/expand.png');
	}
}

function sp_image_resize()
{
	let possible_images = document.getElementsByTagName("img");

	for (var i = 0; i < possible_images.length; i++)
	{
		if (possible_images[i].className !== "bbc_img sp_article")
		{
			continue;
		}

		let temp_image = new Image();
		temp_image.src = possible_images[i].src;

		if (temp_image.width > 300)
		{
			possible_images[i].height = (300 * temp_image.height) / temp_image.width;
			possible_images[i].width = 300;
		}
		else
		{
			possible_images[i].width = temp_image.width;
			possible_images[i].height = temp_image.height;
		}
	}

	if (typeof window_oldSPImageOnload !== "undefined" && window_oldSPImageOnload)
	{
		window_oldSPImageOnload();
		window_oldSPImageOnload = null;
	}
}

/**
 * Send in a shout for display in a shoutbox
 *
 * @param {string} shoutbox_id
 * @param {string} sSessionVar
 * @param {string} sSessionId
 */
function sp_submit_shout(shoutbox_id, sSessionVar, sSessionId)
{
	if (window.XMLHttpRequest)
	{
		shoutbox_indicator(shoutbox_id, true);

		let shout_body = document.getElementById('new_shout_' + shoutbox_id).value.replace(/&#/g, "&#38;#").php_urlencode();

		sendXMLDocument(elk_prepareScriptUrl(sp_script_url) + 'action=shoutbox;xml', 'shoutbox_id=' + shoutbox_id + '&shout=' + shout_body + '&' + sSessionVar + '=' + sSessionId, onShoutReceived);

		document.getElementById('new_shout_' + shoutbox_id).value = '';
	}

	return false;
}

/**
 * Remove a previous entered shout
 *
 * @param {string} shoutbox_id
 * @param {string} shout_id
 * @param {string} sSessionVar
 * @param {string} sSessionId
 */
function sp_delete_shout(shoutbox_id, shout_id, sSessionVar, sSessionId)
{
	if (window.XMLHttpRequest)
	{
		shoutbox_indicator(shoutbox_id, true);

		sendXMLDocument(elk_prepareScriptUrl(sp_script_url) + 'action=shoutbox;xml', 'shoutbox_id=' + shoutbox_id + '&delete=' + shout_id + '&' + sSessionVar + '=' + sSessionId, onShoutReceived);
	}

	return false;
}

/**
 * Manually refresh the shoutbox ahead of the auto refresh action
 *
 * @param {string} shoutbox_id
 * @param {int} last_refresh
 */
function sp_refresh_shout(shoutbox_id, last_refresh)
{
	if (window.XMLHttpRequest)
	{
		shoutbox_indicator(shoutbox_id, true);

		getXMLDocument(elk_prepareScriptUrl(sp_script_url) + 'action=shoutbox;shoutbox_id=' + shoutbox_id + ';time=' + last_refresh + ';xml', onShoutReceived);
	}

	return false;
}

/**
 * Refresh the shoutbox based on the XML response
 *
 * @param {object} XMLDoc
 */
function onShoutReceived(XMLDoc)
{
	let shout, shouts, shoutbox_id, updated, error, warning, reverse, id, author, time,
		timeclean, delete_link, content, is_me, new_body = '';

	// All valid response will have these
	shoutbox_id = XMLDoc.getElementsByTagName("elk")[0].getElementsByTagName("shoutbox")[0].childNodes[0].nodeValue;
	updated = XMLDoc.getElementsByTagName("elk")[0].getElementsByTagName("updated")[0].childNodes[0].nodeValue;

	// Updated response will have the full shoutbox data
	if (updated === "1")
	{
		shouts = XMLDoc.getElementsByTagName("elk")[0].getElementsByTagName("shout");
		error = XMLDoc.getElementsByTagName("elk")[0].getElementsByTagName("error")[0].childNodes[0].nodeValue;
		warning = XMLDoc.getElementsByTagName("elk")[0].getElementsByTagName("warning")[0].childNodes[0].nodeValue;
		reverse = XMLDoc.getElementsByTagName("elk")[0].getElementsByTagName("reverse")[0].childNodes[0].nodeValue;

		// SHow the "warning" box at the top of the shoutbox
		if (warning !== "0")
		{
			new_body += '<li class="shoutbox_warning smalltext">' + warning + '</li>';
		}

		if (error !== "0")
		{
			document.getElementById('shouts_' + shoutbox_id).innerHTML = new_body + '<li class="smalltext">' + error + '</li>';
		}
		else
		{
			// Display all the shouts
			for (var i = 0; i < shouts.length; i++)
			{
				shout = XMLDoc.getElementsByTagName("elk")[0].getElementsByTagName("shout")[i];
				id = shout.getElementsByTagName("id")[0].childNodes[0].nodeValue;
				author = shout.getElementsByTagName("author")[0].childNodes[0].nodeValue;
				time = shout.getElementsByTagName("time")[0].childNodes[0].nodeValue;
				timeclean = shout.getElementsByTagName("timeclean")[0].childNodes[0].nodeValue;
				delete_link = shout.getElementsByTagName("delete")[0].childNodes[0].nodeValue;
				content = shout.getElementsByTagName("content")[0].childNodes[0].nodeValue;
				is_me = shout.getElementsByTagName("is_me")[0].childNodes[0].nodeValue;

				// Something you said
				new_body += '<li>' + (is_me === "0" ? '<strong>' + author + ':</strong> ' : '') + content + '<br />' + (delete_link !== 0 ? ('<span class="shoutbox_delete">' + delete_link + '</span>') : '') + '<span class="smalltext shoutbox_time">' + time + '</span></li>';
			}

			document.getElementById('shouts_' + shoutbox_id).innerHTML = new_body;

			// Set the display direction
			if (reverse !== "0")
			{
				document.getElementById('shouts_' + shoutbox_id).scrollTop = document.getElementById('shouts_' + shoutbox_id).scrollHeight;
			}
			else
			{
				document.getElementById('shouts_' + shoutbox_id).scrollTop = 0;
			}
		}
	}

	// Turn off the spinner
	shoutbox_indicator(shoutbox_id, false);

	return false;
}

/**
 * Toggle the shoutbox spinner for a given box
 *
 * @param {int} shoutbox_id id of the box to work on
 * @param {boolean} turn_on showing or hiding
 */
function shoutbox_indicator(shoutbox_id, turn_on)
{
	document.getElementById('shoutbox_load_' + shoutbox_id).style.display = turn_on ? '' : 'none';
}

function sp_show_ignored_shout(shout_id)
{
	document.getElementById('ignored_shout_' + shout_id).style.display = '';
	document.getElementById('ignored_shout_link_' + shout_id).style.display = 'none';
}

function sp_show_history_ignored_shout(shout_id)
{
	document.getElementById('history_ignored_shout_' + shout_id).style.display = '';
	document.getElementById('history_ignored_shout_link_' + shout_id).style.display = 'none';
}

function sp_showMoreSmileys(postbox, sTitleText, sPickText, sCloseText, elk_theme_url, elk_smileys_url)
{
	if (typeof (this.oSmileyPopupWindow) !== "undefined" && 'closed' in this.oSmileyPopupWindow && !this.oSmileyPopupWindow.closed)
	{
		this.oSmileyPopupWindow.focus();
		return;
	}

	if (typeof (sp_smileyRowsContent) === "undefined")
	{
		var sp_smileyRowsContent = '';

		for (let i = 0; i < sp_smileys.length; i++)
		{
			sp_smileys[i][2] = sp_smileys[i][2].replace(/"/g, '&quot;');
			sp_smileys[i][0] = sp_smileys[i][0].replace(/"/g, '&quot;');
			sp_smileyRowsContent += '<a href="javascript:void(0);" onclick="window.opener.replaceText(\' ' + sp_smileys[i][0].php_addslashes() + '\', window.opener.document.getElementById(\'new_shout_' + postbox + '\')); window.focus(); return false;"><img src="' + elk_smileys_url + '/' + sp_smileys[i][1] + '" id="sml_' + sp_smileys[i][1] + '" alt="' + sp_smileys[i][2] + '" title="' + sp_smileys[i][2] + '" style="padding: 4px;" border="0" /></a> ';
		}
	}

	this.oSmileyPopupWindow = window.open('', 'add_smileys', 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,width=480,height=220,resizable=yes');

	this.oSmileyPopupWindow.document.open('text/html', 'replace');
	this.oSmileyPopupWindow.document.write(sp_moreSmileysTemplate.easyReplace({
		smileyRows: sp_smileyRowsContent
	}));

	this.oSmileyPopupWindow.document.close();
}

/**
 * Used by the theme selection block to swap the preview image
 *
 * @param {type} obj
 */
function sp_theme_select(obj)
{
	var id = obj.options[obj.selectedIndex].value;
	document.getElementById("sp_ts_thumb").src = sp_ts_thumbs[id];
}

/**
 * Used to swap the day on the calendar to update the days events
 *
 * @param {type} id
 */
function sp_collapseCalendar(id)
{
	let new_day = "sp_calendar_" + id;

	if (new_day === current_day)
	{
		return false;
	}

	document.getElementById(current_day).style.display = "none";
	document.getElementById(new_day).style.display = "";
	current_day = new_day;
}

/**
 * Admin Blocks area, used to expand the areas under advanced
 *
 * @param {type} id
 */
function sp_collapseObject(id)
{
	var mode = document.getElementById("sp_object_" + id).style.display;
	mode = (!(mode === "" || mode === "block"));

	// Make it close smoothly
	$("#sp_object_" + id).slideToggle(300);

	document.getElementById("sp_collapse_" + id).src = elk_images_url + (!mode ? "/selected_open.png" : "/selected.png");
}

/**
 * Used to enable / disable the style options for body and title
 */
function check_style_options()
{
	var noTitle = document.getElementById("no_title").checked,
		noBody = document.getElementById("no_body").checked;

	document.getElementById("title_default_class").disabled = noTitle;
	document.getElementById("title_custom_class").disabled = noTitle;
	document.getElementById("title_custom_style").disabled = noTitle;
	document.getElementById("body_default_class").disabled = noBody;
	document.getElementById("body_custom_class").disabled = noBody;
	document.getElementById("body_custom_style").disabled = noBody;
}

/**
 * Surrounds the selected text with text1 and text2.
 *  - If no text is selected, simply appends text1/text2 to the end
 *
 * @param {string} text1
 * @param {string} text2
 * @param {object} oTextHandle
 */
function sp_surroundText(text1, text2, oTextHandle)
{
	// Can a text range be created, start off with Internet explorer < 9.
	if ('caretPos' in oTextHandle && 'createTextRange' in oTextHandle)
	{
		var caretPos = oTextHandle.caretPos,
			temp_length = caretPos.text.length;

		caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) === ' ' ? text1 + caretPos.text + text2 + ' ' : text1 + caretPos.text + text2;

		if (temp_length === 0)
		{
			caretPos.moveStart('character', -text2.length);
			caretPos.moveEnd('character', -text2.length);
			caretPos.select();
		}
		else
		{
			oTextHandle.focus(caretPos);
		}
	}
	// Compliant text range wrap.
	else if ('selectionStart' in oTextHandle)
	{
		var begin = oTextHandle.value.substr(0, oTextHandle.selectionStart),
			selection = oTextHandle.value.substr(oTextHandle.selectionStart, oTextHandle.selectionEnd - oTextHandle.selectionStart),
			end = oTextHandle.value.substr(oTextHandle.selectionEnd),
			newCursorPos = oTextHandle.selectionStart,
			scrollPos = oTextHandle.scrollTop;

		oTextHandle.value = begin + text1 + selection + text2 + end;

		if (oTextHandle.setSelectionRange)
		{
			if (selection.length === 0)
			{
				oTextHandle.setSelectionRange(newCursorPos + text1.length, newCursorPos + text1.length);
			}
			else
			{
				oTextHandle.setSelectionRange(newCursorPos, newCursorPos + text1.length + selection.length + text2.length);
			}

			oTextHandle.focus();
		}

		oTextHandle.scrollTop = scrollPos;
	}
	// Just put them on the end, then.
	else
	{
		oTextHandle.value += text1 + text2;
		oTextHandle.focus(oTextHandle.value.length - 1);
	}
}

/**
 * Updates the current version container with the current version found in the repository
 */
function sp_currentVersion()
{
	let oSPVersionContainer = document.getElementById("spCurrentVersion"),
		oinstalledVersionContainer = document.getElementById("spYourVersion"),
		sCurrentVersion = oinstalledVersionContainer.innerHTML;

	$.getJSON('https://api.github.com/repos/SimplePortal/SimplePortal_ElkArte/releases', {format: "json"},
		function (data, textStatus, jqXHR)
		{
			let mostRecent = {},
				init_news = false;

			$.each(data, function (idx, elem)
			{
				// No drafts, thank you
				if (elem.draft)
				{
					return;
				}

				mostRecent = elem;

				// Load announcements for this release
				sp_setAnnouncement(init_news, elem);
				init_news = true;
			});

			let spVersion = mostRecent.tag_name.replace(/simpleportal/i, '').trim();

			oSPVersionContainer.innerHTML = spVersion;
			if (sCurrentVersion !== spVersion)
			{
				oinstalledVersionContainer.innerHTML = '<span class="alert">' + sCurrentVersion + '</span>';
			}
		}
	);
}

/**
 * Load in any announcements
 *
 * @param init_news
 * @param announcement
 */
function sp_setAnnouncement(init_news, announcement)
{
	var oElem = document.getElementById('spAnnouncements'),
		sMessages = init_news ? oElem.innerHTML : '',
		sAnnouncementTemplate = '<dl>%content%</dl>',
		sAnnouncementMessageTemplate = '<dt><a href="%href%">%subject%</a> :: %time%</dt><dd>%message%</dd>';

	var sMessage = sAnnouncementMessageTemplate.replace('%href%', announcement.html_url).replace('%subject%', announcement.name).replace('%time%', announcement.published_at.replace(/[TZ]/g, ' ')).replace('%message%', announcement.body).replace(/\n/g, '<br />').replace(/\r/g, '');

	oElem.innerHTML = sMessages + sAnnouncementTemplate.replace('%content%', sMessage);
}

/**
 * Sends an xml request to enable / disable pages, categories, articles, etc.
 *
 * @param {int} id
 * @param {string} type
 * @returns {boolean}
 */
function sp_change_status(id, type)
{
	if (type === 'articles')
	{
		sendXMLDocument(elk_prepareScriptUrl(elk_scripturl) + 'action=admin;area=portalarticles;sa=status;xml', 'article_id=' + id + '&' + elk_session_var + '=' + elk_session_id, sp_on_status_received);
	}

	if (type === 'category')
	{
		sendXMLDocument(elk_prepareScriptUrl(elk_scripturl) + 'action=admin;area=portalcategories;sa=status;xml', 'category_id=' + id + '&' + elk_session_var + '=' + elk_session_id, sp_on_status_received);
	}

	if (type === 'page')
	{
		sendXMLDocument(elk_prepareScriptUrl(elk_scripturl) + 'action=admin;area=portalpages;sa=status;xml', 'page_id=' + id + '&' + elk_session_var + '=' + elk_session_id, sp_on_status_received);
	}

	if (type === 'block')
	{
		sendXMLDocument(elk_prepareScriptUrl(elk_scripturl) + 'action=admin;area=portalblocks;sa=statechange;xml', 'block_id=' + id + '&' + elk_session_var + '=' + elk_session_id, sp_on_status_received);
	}

	return false;
}

/**
 * Callback function for XML enable ... updates the UI enabled/disabled image as needed.
 *
 * @param XMLDoc
 * @returns {boolean}
 */
function sp_on_status_received(XMLDoc)
{
	// If it is not valid then clean up
	if (!XMLDoc || !XMLDoc.getElementsByTagName('elk'))
	{
		return false;
	}

	let xml = XMLDoc.getElementsByTagName('elk')[0],
		id = xml.getElementsByTagName('id')[0].childNodes[0].nodeValue,
		status = xml.getElementsByTagName('status')[0].childNodes[0].nodeValue,
		label = xml.getElementsByTagName('label')[0].childNodes[0].nodeValue,
		old = status === 'active' ? 'deactive.png' : 'active.png';

	if (id !== 0)
	{
		status_image = document.getElementById('status_image_' + id);
		status_image.src = status_image.src.replace(old, status + '.png');
		status_image.alt = status_image.title = label;
	}

	return false;
}

/**
 * Monitors the onchange and focus events for the article/page type select box
 * keeps track of previous and new states (bbc, html, markdown, etc) so conversion
 * can be attempted.
 *
 * @param {string} element ID of element to attach change/focus events
 */
function sp_editor_change_type(element)
{
	var initial_state;

	$('#' + element).on('focus', function ()
	{
		// Store the current value on focus
		initial_state = this.value;
	}).change(function ()
	{
		// Handle the editor change of format
		$.sceditor.plugins.spplugin(initial_state, this.value);

		// Make sure the previous value is updated
		initial_state = this.value;
	});
}

/**
 * Convert the current editor formatting syntax to another language
 *
 * @param {string} initial_state one of bbc, html, markdown, php
 * @param {string} new_state one of bbc, html, markdown, php
 */
function sp_to_new(initial_state, new_state) {
	// Get the current contents and send to off for conversion
	let val = editor.getSourceEditorValue(false);

	// Send it to the server for conversion
	sp_change_format(val, initial_state, new_state);

	// If BBC show the editor toolbar
	document.getElementById("editor_toolbar_container").style.display = (new_state === 'bbc' ? 'block' : 'none');
}

/**
 * Sends an xml request to change the format of the editor box
 *
 * @param {string} text The current text
 * @param {string} from Going to bbc, html, php, markdown
 * @param {string} to Going to bbc, html, php, markdown
 * @returns {boolean}
 */
function sp_change_format(text, from, to)
{
	text = text.replace(/&#/g, "&#38;#").php_urlencode();
	sendXMLDocument(elk_prepareScriptUrl(elk_scripturl) + 'action=admin;area=portalconfig;sa=formatchange;xml', 'text=' + text + '&' + 'from=' + from + '&' + 'to=' + to + '&' + elk_session_var + '=' + elk_session_id, sp_on_format_received);

	return false;
}

/**
 * Callback function for XML format ... updates the editor as needed.
 *
 * @param XMLDoc
 * @returns {boolean}
 */
function sp_on_format_received(XMLDoc)
{
	// If it is not valid then clean up
	if (!XMLDoc || !XMLDoc.getElementsByTagName('elk'))
	{
		return false;
	}

	let xml = XMLDoc.getElementsByTagName('elk')[0],
		val = xml.getElementsByTagName('format')[0].firstChild;

	val = val !== null ? val.nodeValue : '';

	// Put the response in the editor wizzy and then toggle back to source
	editor.sourceMode(false);
	editor.val(val, true);
	editor.sourceMode(true);

	return false;
}
