/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file PortalShouts.js
 * Javascript functions for ShoutBox
 *
 * @version 1.41
 */

// insert smiley code
function InsertSmiley(sbID, smcode)
{
	ShoutInsert(sbID, " " + smcode, " ");
}

// insert BB code
function InsertBBCode(sbID, smcode)
{
	var aTag = "["+ smcode +"]";
	if(smcode == "hr")
		var eTag = '';
	else
		var eTag = "[/"+ smcode +"]";
	ShoutInsert(sbID, aTag, eTag);
}

// insert bb color code
function InsertBBColor(sbID, elm)
{
	var idx = elm.selectedIndex;
	if(idx != 0)
	{
		var color = elm.options[idx].value;
		var aTag = "[c="+ color +"]";
		var eTag = "[/c]";
		ShoutInsert(sbID, aTag, eTag);
		elm.selectedIndex = 0;
	}
}

// insert a bbc element
function ShoutInsert(sbID, aTag, eTag)
{
	document.getElementById("shoutcontent"+ sbID).focus();
	var input = document.getElementById("shoutcontent"+ sbID);

	// IE
	if(typeof document.selection != "undefined")
	{
		var range = document.selection.createRange();
		var insText = range.text;
		range.text = aTag + insText + eTag;

		// adjust cursor
		range = document.selection.createRange();
		if (insText.length == 0)
			range.move("character", -eTag.length);
		else
			range.moveStart("character", aTag.length + insText.length + eTag.length);
		range.select();
	}

	// Gecko
	else
	{
		if(typeof input.selectionStart != "undefined")
		{
			var start = input.selectionStart;
			var end = input.selectionEnd;
			var insText = input.value.substring(start, end);
			input.value = input.value.substr(0, start) + aTag + insText + eTag + input.value.substr(end);

			// adjust cursor
			var pos;
			if (insText.length == 0)
				pos = start + aTag.length;
			else
				pos = start + aTag.length + insText.length + eTag.length;
			input.selectionStart = pos;
			input.selectionEnd = pos;
		}
	}
}

// popup for bb code and smileys
function ShoutPopup(sbID)
{
	var element = document.getElementById("bbcodes"+ sbID);
	if(element.style.display == "none")
	{
		$(element).fadeIn('fast', function(){element.style.display = ""});
		var pos = $("#shoutcontdiv"+ sbID).offset();
		element.style.top = parseInt(pos.top) + "px";

		if(document.getElementById("shoutframe"+ sbID).className == "left")
			element.style.left = parseInt(pos.left) + document.getElementById("upshrinkLeftBar").clientWidth - 13 +"px";
		else
		{
			if(document.getElementById("shoutframe"+ sbID).className == "right")
				element.style.left = parseInt(pos.left) - element.clientWidth -1 + "px";
			else
			{
				element.style.left =  parseInt(pos.left) +"px";
				element.style.top = parseInt(pos.top) - element.clientHeight + 11 + "px";
			}
		}
	}
	else
		$(element).fadeOut('fast', function(){element.style.display = "none"});

	document.getElementById("shoutcontent"+ sbID).focus();
}

// get a pixel value from styles
function getPixVal(value)
{
	var find = /(\d+)/;
	find.exec(value);
	return parseInt(RegExp.$1);
}

// submit any
function SubmitAnyShout(sbID, state)
{
	// check the shoutpost
	var smileys = ['(:1)', '(:2)', '(:3)', '(:4)', '(:5)', '(:6)', '(:7)', '(:8)', '(:9)', '(:0)', '(;1)', '(;2)', '(;3)', '(;4)', '(;5)', '(;6)', '(;7)', '(;8)', '(;9)', '(;0)'];
	var post = document.getElementById('shoutcontent'+ sbID).value;

	// adjust line breaks
	post = post.replace(/[\s\n]+\[hr\][\s\n]+/g, '[hr]');
	post = post.replace(/\[hr\][\s\n]+/g, '[hr]');
	post = post.replace(/[\s\n]+\[hr\]/g, '[hr]');
	post = post.replace(/\n/g, '[br]');
	post = post.replace(/\s+\[br\]/g, '[br]');
	post = post.replace(/\[br\]\s+/g, '[br]');

	// smileys needs a space before and after
	for(i = 0; i < smileys.length; i++)
		post = post.replace(smileys[i], ' '+smileys[i]+' ');

	// remove duplicate spaces
	post = post.replace(/\s+/g, ' ');

	// now send the data
	var postData = {};
	postData['sc'] = pmx_session_id;
	postData['shoutbox_id'] = sbID;
	postData['pmx_shout'] = document.getElementById('shout'+ sbID).value;
	postData['shoutid'] = document.getElementById('shoutid'+ sbID).value;
	postData['post'] = post;
	document.getElementById("shout"+ sbID).value = '';

	pmxXMLpost(window.location.href.replace(/\;/g, '&'), postData);
	ShoutAdmin(sbID, state);

	// reload the shoutbox content
	$('#shoutframe'+ sbID).load(pmx_boardurl +' #shoutframe'+ sbID);

	$(document.getElementById('bbcodes'+ sbID)).fadeOut('fast', function(){document.getElementById('bbcodes'+ sbID).style.display = 'none'});
	$(document.getElementById('shoutcontdiv'+ sbID)).slideUp(300,function(){document.getElementById('shoutcontdiv'+ sbID).style.display = 'none'});
	document.getElementById("shoutcontent"+ sbID).value = '';
	document.getElementById("shoutbbon"+ sbID).style.display = "none";
	document.getElementById("shoutbboff"+ sbID).style.display = "";
}

// delete a shout
function DeleteShout(sbID, Id, state)
{
	if(confirm(pmx_shoutbox_confirm) == true)
	{
		document.getElementById("shout"+ sbID).value = "delete";
		document.getElementById("shoutid"+ sbID).value = Id;
		SubmitAnyShout(sbID, state);
	}
}

// edit a shout
function EditShout(sbID, Id, post)
{
	post = post.replace(/\[br\]/g, "\n");
	post = post.replace(/\[hr\]/g, "\n[hr]\n");
	$(document.getElementById("shoutcontdiv"+ sbID)).slideDown('fast', function(){document.getElementById("shoutcontdiv"+ sbID).style.display = ""});
	document.getElementById("shout"+ sbID).value = "update";
	document.getElementById("shoutid"+ sbID).value = Id;
	document.getElementById("shoutcontent"+ sbID).value = post;
	document.getElementById("shoutcontent"+ sbID).focus();
	document.getElementById("shoutbbon"+ sbID).style.display = "";
	document.getElementById("shoutbboff"+ sbID).style.display = "none";
	document.getElementById("shout_key"+ sbID).title = pmx_shoutbox_send_title;
	document.getElementById("shout_key"+ sbID).value = pmx_shoutbox_button;
}

// send (submit) a shout
function SendShout(sbID, state)
{
	if(state == 1)
	{
		if(document.getElementById("shoutcontdiv"+ sbID).style.display == "none")
		{
			document.getElementById("shout_key"+ sbID).title = pmx_shoutbox_send_title;
			document.getElementById("shout_key"+ sbID).value = pmx_shoutbox_button;
			$(document.getElementById("shoutcontdiv"+ sbID)).slideDown('fast', function(){document.getElementById("shoutcontdiv"+ sbID).style.display = "block"});
			document.getElementById("shoutbbon"+ sbID).style.display = "";
			document.getElementById("shoutbboff"+ sbID).style.display = "none";
			document.getElementById("shoutcontent"+ sbID).focus();
		}
		else
		{
			document.getElementById("shout_key"+ sbID).title = pmx_shoutbox_button_title;
			document.getElementById("shout_key"+ sbID).value = pmx_shoutbox_button_open;
			var cont = document.getElementById("shoutcontent"+ sbID).value;
			if(cont && cont.replace(/\[[^\]]*\]/g, "").match(/\S/g))
			{
				pmxCookie("set", "shout"+ sbID, "none");
				if(document.getElementById("shout"+ sbID).value == "")
					document.getElementById("shout"+ sbID).value = "save";
				document.getElementById("shoutbbon"+ sbID).style.display = "";
				document.getElementById("shoutbboff"+ sbID).style.display = "none";
				SubmitAnyShout(sbID, state);
			}
			else
			{
				document.getElementById("shoutcontent"+ sbID).value = "";
				$(document.getElementById("shoutcontdiv"+ sbID)).slideUp(300, function(){document.getElementById("shoutcontdiv"+ sbID).style.display = "none"});
				if(document.getElementById("bbcodes"+ sbID).style.display == "")
					ShoutPopup(sbID);
				document.getElementById("shoutbbon"+ sbID).style.display = "none";
				document.getElementById("shoutbboff"+ sbID).style.display = "";
			}

			if(!document.getElementById("shout_toggle"+ sbID).src.match(/empty.gif/))
				document.getElementById("shout_toggle"+ sbID).src = pmx_shoutbox_admimg[0];
			for(var i = 0; i < document.getElementById("shoutcount"+ sbID).value; i++)
			{
				if(document.getElementById(i +"shoutimg"+ sbID))
					document.getElementById(i +"shoutimg"+ sbID).style.display = 'none';
			}
			pmxCookie("set", "shout"+ sbID, "none");
		}
	}
}

// toggle the edit mode
function ShoutAdmin(sbID, state)
{
	if(state == "check")
		state = pmxCookie("get", "shout"+ sbID, '', false);
	state = (typeof state == "undefined" ? "block" : (document.getElementById("shout_toggle"+ sbID).src.match(/shout_admon.gif/) ? "block" : "none"));

	var i = 0;
	for(var i = 0; i < document.getElementById("shoutcount"+ sbID).value; i++)
	{
		if(document.getElementById(i +"shoutimg"+ sbID))
			document.getElementById(i +"shoutimg"+ sbID).style.display = state;
	}

	if(state == "none")
	{
		if(document.getElementById("shout_toggle"+ sbID))
		{
			if(!document.getElementById("shout_toggle"+ sbID).src.match(/empty.gif/))
				document.getElementById("shout_toggle"+ sbID).src = pmx_shoutbox_admimg[0];
			document.getElementById("shoutcontent"+ sbID).value = "";

			document.getElementById("shout_key"+ sbID).title = pmx_shoutbox_button_title;
			document.getElementById("shout_key"+ sbID).value = pmx_shoutbox_button_open;

			$(document.getElementById("shoutcontdiv"+ sbID)).slideUp(300, function(){document.getElementById("shoutcontdiv"+ sbID).style.display = "none"});
			$(document.getElementById('bbcodes'+ sbID)).fadeOut('fast', function(){document.getElementById('bbcodes'+ sbID).style.display = 'none'});
			document.getElementById("shoutbbon"+ sbID).style.display = "none";
			document.getElementById("shoutbboff"+ sbID).style.display = "";
		}
	}
	else
	{
		if(!document.getElementById("shout_toggle"+ sbID).src.match(/empty.gif/))
			document.getElementById("shout_toggle"+ sbID).src = pmx_shoutbox_admimg[1];
	}
	pmxCookie("set", "shout"+ sbID, state == "none" ? "block" : "none", '', false);
}
/* eof */