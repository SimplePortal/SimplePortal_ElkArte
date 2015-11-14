/**
 * @package SimplePortal ElkArte
 *
 * @author SimplePortal Team
 * @copyright 2015 SimplePortal Team
 * @license BSD 3-clause
 * @version 1.1.0 Beta 1
 */

/**
 * Used to collapse an individual block
 *
 * @param {string} id
 */
function sp_collapseBlock(id)
{
	$("#sp_block_" + id).slideToggle(300).promise().done(function() {
		var mode = false;

		if ($("#sp_block_" + id).is(":visible"))
			mode = true;

		// Save the choice, one way for guest, or theme options for members
		if (elk_member_id === 0)
			document.cookie = "sp_block_" + id + "=" + (mode ? 0 : 1);
		else
			elk_setThemeOption("sp_block_" + id, mode ? 0 : 1, null, null);

		// Swap the class to change the icon
		$("#sp_collapse_" + id).attr("class", mode ? "collapse" : "expand");
	});
}

/**
 * Used to collapse side (if enabled)
 *
 * @param {string} id
 */
function sp_collapseSide(id)
{
	var sp_sides = [];

	sp_sides[1] = "sp_left";
	sp_sides[4] = "sp_right";

	mode = document.getElementById(sp_sides[id]).style.display === "" ? 0 : 1;

	// Guests use a cookie, members a theme option to remember the choice
	if (elk_member_id === 0)
		document.cookie = sp_sides[id] + "=" + (mode ? 0 : 1);
	else
		elk_setThemeOption(sp_sides[id], mode ? 0 : 1, null, null);

	// Update the side expand/collapse image
	document.getElementById("sp_collapse_side" + id).className = (mode ? "dot collapse" : "dot expand");

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
	var mode = document.getElementById("sp_object_" + id).style.display === '' ? 0 : 1;

	$("#sp_object_" + id).toggle(300);

	if (typeof(has_image) === "undefined" || has_image === true)
		document.getElementById("sp_collapse_" + id).src = elk_images_url + (mode ? '/collapse.png' : '/expand.png');
}

function sp_image_resize()
{
	var possible_images = document.getElementsByTagName("img");

	for (var i = 0; i < possible_images.length; i++)
	{
		if (possible_images[i].className !== "bbc_img sp_article")
			continue;

		var temp_image = new Image();
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

	if (typeof(window_oldSPImageOnload) !== "undefined" && window_oldSPImageOnload)
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

		var shout_body = escape(document.getElementById('new_shout_' + shoutbox_id).value.replace(/&#/g, "&#").php_to8bit()).replace(/\+/g, "%2B");

		sendXMLDocument(elk_prepareScriptUrl(sp_script_url) + 'action=shoutbox;xml', 'shoutbox_id=' + shoutbox_id + '&shout=' + shout_body + '&' + sSessionVar + '=' + sSessionId, onShoutReceived);

		document.getElementById('new_shout_' + shoutbox_id).value = '';

		return false;
	}
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

		return false;
	}
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

		return false;
	}
}

/**
 * Refresh the shoutbox based on the XML response
 *
 * @param {object} XMLDoc
 */
function onShoutReceived(XMLDoc)
{
	var shout, shoutbox_id, updated, error, warning, reverse, id, author, time, timeclean, delete_link, content, is_me, new_body = '';

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
			new_body += '<li class="shoutbox_warning smalltext">' + warning + '</li>';

		if (error !== "0")
			document.getElementById('shouts_' + shoutbox_id).innerHTML = new_body + '<li class="smalltext">' + error + '</li>';
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
				document.getElementById('shouts_' + shoutbox_id).scrollTop = document.getElementById('shouts_' + shoutbox_id).scrollHeight;
			else
				document.getElementById('shouts_' + shoutbox_id).scrollTop = 0;
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

function sp_catch_enter(key)
{
	var keycode;

	if (window.event)
		keycode = window.event.keyCode;
	else if (key)
		keycode = key.which;

	if (keycode === 13)
		return true;
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
	if (typeof(this.oSmileyPopupWindow) !== "undefined" && 'closed' in this.oSmileyPopupWindow && !this.oSmileyPopupWindow.closed)
	{
		this.oSmileyPopupWindow.focus();
		return;
	}

	if (typeof(sp_smileyRowsContent) === "undefined")
	{
		var sp_smileyRowsContent = '';

		for (i = 0; i < sp_smileys.length; i++)
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
 * When using html or php, disable the editor so it does not "fight" with what
 * the user wants to enter.
 *
 * @param {string} new_state
 * @param {string} original set to true on first invocation from controller
 */
function sp_update_editor(new_state, original)
{
	var $_textarea = $("textarea"),
		instance = $_textarea.sceditor("instance"),
		val = '';

	// Going back to BBC
	if (new_state === "bbc" && typeof(instance) === "undefined")
	{
		// Get the current textbox contents, treat as if html
		if (original === 'html')
			val =  $_textarea.html().php_unhtmlspecialchars();
		else
			val = '[code]' +  $_textarea.val().replace(/\n/g, '<br \>') + '[/code]';

		// Start the editor again
		elk_editor();

		// load the editor with the html contents, toggle back to bbc so the editor converts it
		instance =  $_textarea.sceditor("instance");
		instance.sourceMode(false);
		instance.setWysiwygEditorValue(val);
		instance.sourceMode(true);
	}
	// Toggling from BBC to html or php
	else if (new_state !== "bbc" && typeof(instance) !== "undefined" && original !== '')
	{
		// Update the the original text area with current editor contents and stop the editor
		if (new_state === 'html')
		{
			// Get the editors html value, bypass the bbc plugin, this html will have lost
			// its formatting but it is html
			if (instance.getSourceEditorValue() !== '')
			{
				val = instance.getWysiwygEditorValue(false);
				val = val.replace(/<span .*>\s?<\/span>/g, '').replace(/<br( \\)?>/g, "\n");
			}
		}
		// From bbc to php
		else
		{
			val = instance.getSourceEditorValue(false).replace(/<br( \\)?>/g, "\n").php_unhtmlspecialchars().replace('[code]', '').replace('[/code]', '');
		}

		// Don't need the editor any longer, back to a text box and set the value we determined
		instance.destroy();
		$("textarea").val(val);
	}
	// Load html to the text area
	else if (new_state !== "bbc" && typeof(instance) !== "undefined")
	{
		// Update the the original text area with current editor contents and stop the editor
		if (new_state === 'html')
			instance.updateOriginal();

		instance.destroy();
	}
}

/**
 * Monitors the onchange and focus events for an element
 *
 * @param {string} element ID of element to attach change/focus events
 */
function sp_editor_change_type(element) {
    var previous;

    $('#' + element).on('focus', function () {
        // Store the current value on focus and on change
        previous = this.value;
    }).change(function() {
        // Handle the editor change
		sp_update_editor(this.value, previous);

        // Make sure the previous value is updated
        previous = this.value;
    });
}

/**
 * Used by the theme selection block to swap the preview image
 * @param {type} obj
 */
function sp_theme_select(obj)
{
	var id = obj.options[obj.selectedIndex].value;
	document.getElementById("sp_ts_thumb").src = sp_ts_thumbs[id];
}

/**
 * Used to swap the day on the calendar to update the days events
 * @param {type} id
 */
function sp_collapseCalendar(id)
{
	new_day = "sp_calendar_" + id;

	if (new_day === current_day)
		return false;

	document.getElementById(current_day).style.display = "none";
	document.getElementById(new_day).style.display = "";
	current_day = new_day;
}

/**
 * Admin Blocks area, used to expand the areas under advanced
 * @param {type} id
 */
function sp_collapseObject(id)
{
	var mode = document.getElementById("sp_object_" + id).style.display;
	mode = (mode === "" | mode === "block") ? false : true;

	// Make it close smoothly
	$("#sp_object_" + id).slideToggle(300);

	document.getElementById("sp_collapse_" + id).src = elk_images_url + (!mode ? "/selected_open.png" : "/selected.png");
}

/**
 * Used to enable / disable the style options for body and title
 */
function check_style_options()
{
	var	noTitle = document.getElementById("no_title").checked,
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
			oTextHandle.focus(caretPos);
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
				oTextHandle.setSelectionRange(newCursorPos + text1.length, newCursorPos + text1.length);
			else
				oTextHandle.setSelectionRange(newCursorPos, newCursorPos + text1.length + selection.length + text2.length);

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