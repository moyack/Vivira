/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file PortalAdmin.js
 * Common Javascript functions
 *
 * @version 1.41
 */

// data objects
var pWind;
var pWindPos;
var oPmxTitle;
var oPmxCats;
var oPmxCatsData;
var oPmxAcs;
var oPmxTitleUnMod = [];
var oPmxCatsUnMod = [];
var oPmxCatNameUnMod = [];

var isMouseClick = false;
var haveEvent = false;
var FirstSelect = false;
var DropDownElm = false;
var selectedIcon = false;

function TtlpopupInit()
{
	isMouseClick = false;
	haveEvent = false;
	FirstSelect = false;
	DropDownElm = false;
	selectedIcon = false;
}
TtlpopupInit();

// Categorie: page mode or sidebar mode
function check_PageMode(elm, mode)
{
	var shm_id = elm.id.replace('shm.', '');
	document.getElementById('opt.'+ shm_id).style.display = (elm.checked == true ? '' : 'none');
	document.getElementById('opt.'+ mode).style.display =  (elm.checked == true ? 'none' : '');
	if(mode == 'sidebar' && document.getElementById('opt.pages.sbar.check').checked == true)
		document.getElementById('opt.pages.sbar').style.display = '';
	else
		document.getElementById('opt.pages.sbar').style.display = 'none';
}
function set_PageMode(elm)
{
	document.getElementById('opt.pages.sbar').style.display = (elm.checked == true ? '' : 'none');
}

// onMouseClick -> open the dropdown with image
function setNewIcon(elm, event)
{
	// mouseclick event not set?
	if(!isMouseClick)
	{
		TtlpopupInit();
		DropDownElm = elm
		isMouseClick = event['type'] == 'click';

		if(FirstSelect == false)
			FirstSelect = elm.id;

		// show the IconName
		var currIcon = getIconName(document.getElementById('pWind.icon').src);
		document.getElementById('iconDD').value = ucFirst(currIcon);

		var icons = document.getElementById('pWind.icon_sel').children;
		var offsetElement = 0;
		for(var i = 0; i < icons.length; i++)
		{
			if(icons[i].id == currIcon)
			{
				selectedIcon = icons[i];
				icons[i].className = 'ttlicon selected';
				offsetElement = (22 * i);
			}
			else
				icons[i].className = 'ttlicon';
		}

		// show the dropdown
		if(isIE)
			elm.style.marginTop = '1px';
		if(isFireFox)
			elm.style.marginTop = '2px';
		$(elm).slideDown(0, function(){elm.scrollTop = offsetElement;elm.focus();});
		document.getElementById('iconDD').blur();

		// add EventHandler for click outside
		if(!haveEvent)
		{
			document.addEventListener('click', closeTTLDropDown);
			haveEvent = true;
			isMouseClick = false;
		}
	}
}

// onChange - save the new icon
function updIcon(elm)
{
	// remove the eventlistener
	document.removeEventListener('click', closeTTLDropDown);

	// find current active
	var icons = document.getElementById('pWind.icon_sel').children;
	for(var i = 0; i < icons.length; i++)
	{
		if(icons[i].className.indexOf('active') >= 0)
		{
			newIcon = icons[i].id;
			break;
		}
	}

	// show the Icon and IconName
	document.getElementById('pWind.icon').src = ttliconurl + newIcon;
	document.getElementById('iconDD').value = ucFirst(newIcon);

	// this exists only in the popup
	if(document.getElementById('sTitle.icon.'+ document.getElementById('pWind.id')))
		document.getElementById('sTitle.icon.'+ document.getElementById('pWind.id').value).value = ttliconurl + newIcon;

	// this exists only Block/Cat/Article edit
	if(document.getElementById('post_image'))
		document.getElementById('post_image').value = newIcon;

	// collapse dropdown & init
	$(DropDownElm).slideUp(0);
	document.getElementById('iconDD').blur();
	TtlpopupInit();
}

// MouseClick outside
function closeTTLDropDown(e)
{
	if(haveEvent && isMouseClick)
	{
		var Rect = DropDownElm.getBoundingClientRect();
		var outside = false;

		if(typeof(e) != 'undefined')
		{
			if(e.clientY < Rect.top)
				outside = true;
			else if(e.clientX < Rect.left)
				outside = true;
			else if(e.clientY > Rect.bottom)
				outside = true;
			else if(e.clientX > Rect.right)
				outside = true;

			if(outside)
			{
				// remove the eventlistener
				document.removeEventListener('click', closeTTLDropDown);

				// collapse dropdown
				$(DropDownElm).slideUp(0);
				document.getElementById('iconDD').blur();
				// init for the next run
				TtlpopupInit();
			}
		}
	}
	else
		isMouseClick = true;
}

// First Letter to Uppercase
function ucFirst(Image) {
	var idx = Image.lastIndexOf('.');
	var IconName = Image.substring(0, idx);
	return IconName.substring(0, 1).toUpperCase() + IconName.substring(1);
}

// get IconName from path
function getIconName(link)
{
	var idx = link.lastIndexOf('/')+1;
	return link.substring(idx);
}

// show the language assigned title input
function setTitleLang(elm, id)
{
	var curlangid = 'curlang' + (id ? id : '');
	var idx = elm.selectedIndex;
	var show = elm.options[idx].value;
	var hide = document.getElementById(curlangid).value;
	document.getElementById(hide + (id ? '_' + id : '')).style.display = 'none';
	document.getElementById(show + (id ? '_' + id : '')).style.display = '';
	document.getElementById(curlangid).value = show;
}

// set the title align
function setAlign(IDpref, align)
{
	var old_active = 'img' + IDpref + document.getElementById('titlealign' + IDpref).value;
	var new_active = 'img' + IDpref + align;
	document.getElementById('titlealign' + IDpref).value = align;
	document.getElementById(old_active).style.backgroundColor = '';
	document.getElementById(new_active).style.backgroundColor = '#e02000';
}

function showSelectValue(e)
{
	if (e.target.id != 'init_ttlicon')
	{
		var oldico = document.getElementById('pmxttlicon').src;
		var newico = oldico.substring(0, oldico.lastIndexOf('/')+1) + e.target.value;
		document.getElementById('pmxttlicon').src = newico;
	}
}

// show the title icon if not empty
function setTitleIcon(elm, id)
{
	var idx = elm.selectedIndex;
	var idx_val = elm.options[idx].value;
	var oldico = document.getElementById('pmxttlicon' + id).src;
	var url = oldico.substring(0, oldico.lastIndexOf('/')+1) + (idx_val == '' ? 'none.gif' : idx_val);
	document.getElementById('pmxttlicon' + id).src = url;
}

// check if the header can collapse
function checkCollapse(elm)
{
	var idx = elm.selectedIndex;
	var elm_name = elm.name;
	var idx_val = elm.options[idx].value;
	if(elm_name == 'config[visuals][header]')
	{
		if(idx_val == 'none')
			document.getElementById('collapse').disabled = true;
		else
			document.getElementById('collapse').disabled = false;
	}
}

// check if the maxheigt field enabled
function checkMaxHeight(elm)
{
	var idx = elm.selectedIndex;
	var idx_val = elm.options[idx].value;
	if(idx_val == '')
	{
		document.getElementById('maxheight').style.backgroundColor = '#8898b0';
		document.getElementById('maxheight_sel').style.backgroundColor = '#8898b0';
		document.getElementById('maxheight').disabled = true;
		document.getElementById('maxheight_sel').disabled = true;
	}
	else
	{
		document.getElementById('maxheight').style.backgroundColor = '';
		document.getElementById('maxheight_sel').style.backgroundColor = '';
		document.getElementById('maxheight').disabled = false;
		document.getElementById('maxheight_sel').disabled = false;
	}
}

// check if pmxcache enabled
function checkPmxCache(elm, val)
{
	if(elm.checked == false)
	{
		if(document.getElementById('cachehelp'))
			document.getElementById("cachehelp").style.display = "none";
		else
		{
			document.getElementById('cacheval').style.backgroundColor = '#8898b0';
			document.getElementById('cacheval').value = 0;
			document.getElementById('cache_value').value = 0;
		}
	}
	else
	{
		if(document.getElementById('cachehelp'))
			document.getElementById("cachehelp").style.display = "block";
		else
		{
			document.getElementById('cacheval').style.backgroundColor = '';
			document.getElementById('cacheval').value = val;
			document.getElementById('cache_value').value = 1;
		}
	}
}

// check blockpos if changed
function checkBlockPos(elm, cVal)
{
	if(cVal != elm.value)
		document.getElementById('set_savepos').value++;
	else if(document.getElementById('set_savepos').value > 0)
		document.getElementById('set_savepos').value--;
}

// numeric field check
function check_numeric(elm, multchr)
{
	var elm_value = elm.value;
	if(elm_value.length > 0)
	{
		if(multchr == '%')
			var Check = /([\%\,\.0-9])+/;
		else if(multchr == '*')
			var Check = /([\*0-9])+/;
		else if(multchr == ',')
			var Check = /([\,0-9])+/;
		else if(multchr == '.')
			var Check = /([\.0-9])+/;
		else
			var Check = /([0-9])+/;

		if(!elm_value.match(Check) || elm_value.match(Check)[0] != elm_value)
			elm.value = (elm_value.match(Check) ? elm_value.match(Check)[0] : '');
	}
}

// name input check
function check_requestname(elm)
{
	var elm_value = elm.value;
	if(elm_value.length > 0)
	{
		var legal_chars = /([\.\-\_a-zA-Z0-9])+/;
		var check = elm_value.match(legal_chars);
		if(check == null)
			elm.value = ''
		else if(elm_value != check[0])
		{
			var bad_pos = elm_value.match(legal_chars)[0].length;
			var bad_char = elm_value.slice(bad_pos, bad_pos +1);
			elm.value = elm_value.replace(bad_char, '');
			// move cursor back to last pos
			if(elm.setSelectionRange)
			{
				elm.focus();
				elm.setSelectionRange(bad_pos, bad_pos);
			}
			else if (elm.createTextRange) {
				var range = elm.createTextRange();
				range.collapse(true);
				range.moveEnd('character', bad_pos);
				range.moveStart('character', bad_pos);
				range.select();
			}
		}
	}
}

// Toggle all checkboxes
function ToggleCheckbox(elm, what, init)
{
	if(!elm)
		return;

	if(what == 'xsel')
		var sides = ['head', 'left', 'right', 'top', 'bottom', 'foot'];	// xbars
	else
		var sides = ['head', 'left', 'right', 'top', 'bottom', 'foot', 'front', 'pages'];	// Panel moderate
	var ischeck = false;
	var ico = elm.src;
	var url = ico.substring(0, ico.lastIndexOf('/')+1);
	for(var i = 0; i < sides.length; i++)
		ischeck = (document.getElementById(what +  sides[i]).checked == true ? true : ischeck);
	if(init == 0)
	{
		ischeck = !ischeck;
		for(var i = 0; i < sides.length; i++)
			document.getElementById(what + sides[i]).checked = ischeck;
	}
	elm.src = url + (ischeck ? 'bullet_minus.gif' : 'bullet_plus.gif');
}

// Toggle Help messages
function Toggle_help(elm)
{
	if(document.getElementById(elm).style.display == 'none')
		$(document.getElementById(elm)).slideDown('fast');
	else
		$(document.getElementById(elm)).slideUp('fast');
}

// Hide Syntaxcheck
function Hide_SyntaxCheck(elm)
{
	$(elm).slideUp('fast', function(){elm.className = 'info_frame'});
}

// Show Help messages
function Show_help(elm, txtdir)
{
	if(txtdir)
		txtdir = '_'+ txtdir;
	else
		txtdir = '';
	var match = /(\S+)/;
	match.exec(document.getElementById(elm).className);
	if(RegExp.$1 == 'info_frame')
	{
		document.getElementById(elm).className = 'info_text'+ txtdir +' plainbox';
		$(document.getElementById(elm)).slideDown('fast');
	}
	else
		$(document.getElementById(elm)).slideUp('fast', function(){document.getElementById(elm).className = 'info_frame'});
}

// check the request name field
function FormFuncCheck(elm, id)
{
	if(document.getElementById('check.name').value == '')
	{
		alert(document.getElementById('check.name.error').innerHTML);
		return false;
	}
	else
		FormFunc(elm, id)
}

// common func on forms
function FormFunc(Func, Val, Msg)
{
	if(Msg)
	{
		if(!confirm(Msg) == true)
			return false;
	}

	else
	{
		if(Func == 'save_pos')
			Val = document.getElementById('set_savepos').value;

		if(Func == 'move' || Func == 'clone')
		{
			moveTo = Val.split(',');
			Val = moveTo[0];
		}

		if(Func == 'create')
			Val = '';

		if(Func.indexOf('cancel') == 0)
			Func = 'cancel_edit';
	}

	// submit the form
	document.getElementById('common_field').name = Func;
	document.getElementById('common_field').value = Val;
	$('form#pmx_form').submit();
}

// the multiple selection show/hide toggle object
function MultiSelect(action_name)
{
	// setup Timer vars
	this.timerClicks = 600;

	// the action array
	this.actioOpts = [];
	this.action = action_name;
	this.elm = document.getElementById(action_name);

	// self init
	this.init();
}

// called by element change
MultiSelect.prototype.changed = function()
{
	var isChg = -1;
	for(var idx = 0; idx < this.elm.length; idx++)
	{
		if(this.actioOpts['stat'][idx] != this.elm.options[idx].selected)
		{
			this.actioOpts['stat'][idx] = this.elm.options[idx].selected;
			if(this.actioOpts['chng'][idx] > 0)
				isChg = idx;
			this.actioOpts['chng'][idx]++;
		}
	}

	if(isChg != -1)
	{
		for(var idx = 0; idx < this.actioOpts['stat'].length; idx++)
			this.elm.options[idx].selected = this.actioOpts['stat'][idx];

		var dat = this.elm.options[isChg].value.split('=');
		var txt = this.elm.options[isChg].text;
		this.elm.options[isChg].value = dat[0] +'='+ (dat[1] == '1' ? '0' : '1');
		this.elm.options[isChg].text = (dat[1] == '1' ? '^'+txt : txt.substr(1));
	}
	else
		setTimeout('stopTimer(' +this.action+ ')', this.timerClicks);
}

// the init func
MultiSelect.prototype.init = function()
{
	this.actioOpts = {};
	this.actioOpts['stat'] = [];
	this.actioOpts['chng'] = [];
	this.setupArray();
}

// setup the array
MultiSelect.prototype.setupArray = function()
{
	for(var idx = 0; idx < this.elm.length; idx++)
	{
		this.actioOpts['stat'][idx] = this.elm.options[idx].selected;
		this.actioOpts['chng'][idx] = 0;
	}
}

function stopTimer(action)
	{eval(action).setupArray();}
function changed(action)
	{eval(action).changed();}
function ReInitMSel(action)
	{eval(action).init();}
/* eof */