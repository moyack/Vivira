/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file PortalPopup.js
 * Common Javascript functions
 *
 * @version 1.41
 */

// data objects
var pWind;
var pWindPos;
var oPmxTitle;
var oPmxCats = [];
var oPmxAcs;
var oPmxCatsUnMod = [];
var oPmxCatNameUnMod = [];
var selCurElm;

/* get the icon name from a path */
function getIconName(link)
{
	var idx = link.lastIndexOf('/')+1;
	return link.substring(idx);
}

/* ckeck if a className exists */
function pmxHasClass(el, className)
{
	if(el.classList)
		return el.classList.contains(className);
	else
		return !!el.className.match(new RegExp('(\\s|^)' + className + '(\\s|$)'));
}

/* add a className */
function pmxAddClass(el, className)
{
	if(el.classList)
		el.classList.add(className);
	else if (!pmxHasClass(el, className))
		el.className += ' '+ className;
}

/* remove a className */
function pmxRemoveClass(el, className)
{
	if(el.classList)
		el.classList.remove(className)
	else if(pmxHasClass(el, className))
	{
		var reg = new RegExp('(\\s|^)' + className + '(\\s|$)')
		el.className = el.className.replace(reg, ' ')
	}
}

/* toggle a className */
function pmxToggleClass(el, className)
{
	if(pmxHasClass(el, className))
		pmxRemoveClass(el, className);
	else
		pmxAddClass(el, className)
}

/* Init Select-size-1 DD */
function initSelect(selElm)
{
	if(isWebKit && !mobile_device && selElm.childElementCount > 1)
	{
		selElm.closedHeight = parseInt($('select').css('height'));
		selCurElm = selElm;
		selElm.onmousedown = function(){setSelectSize(selElm)};
		selElm.onclick = function(){};
		pmxRemoveClass(selElm, (selElm.id == 'pmxallcats' ? 'singleddabs' : 'singledd'));
	}
}

// Mousedown - Open Select-size-1 Dropdown
function setSelectSize(selElm)
{
	pmxAddClass(selElm, (selElm.id == 'pmxallcats' ? 'singleddabs' : 'singledd'));
	selElm.onmousedown = function(){};

	optH = parseInt($('.dropdown option').css('height'));
	if(isNaN(optH))
		optH = parseInt($('option').css('height'));
	selElm.optH = optH;

	if(selElm.childElementCount > 10)
	{
		aniheight = (10 * selElm.optH) + 6;
		selElm.size = 10;
	}
	else
	{
		aniheight = (selElm.childElementCount * selElm.optH) + 6;
		selElm.size = selElm.childElementCount;
	}
	$(selElm).animate({height: aniheight},{duration: 50, easing: 'linear', complete: function(){selElm.scrollTop = (selElm.selectedIndex * selElm.optH)}});
	selElm.onclick = function(){firstClick(selElm)};
	selElm.scrollTop = (selElm.selectedIndex * selElm.optH);
}

// first click after Mousedown on Select-size-1 Dropdown
function firstClick(selElm)
{
	selElm.scrollTop = (selElm.selectedIndex * selElm.options[0].clientHeight);
	document.addEventListener('click', outsideSelectSize);
	selElm.onclick = function(){getSelected(selElm)};
	selCurElm = selElm;
}

// click on a Select-size-1 Dropdown element
function getSelected(selElm)
{
	$(selElm).animate({height: selElm.closedHeight},{duration: 50, easing: 'linear', complete: function(){selElm.size = 1;}});
	selElm.onclick = function(){};
	document.removeEventListener('click', outsideSelectSize);
	initSelect(selElm);
}

// click outside the Select-size-1 Dropdown
function outsideSelectSize(e)
{
	if(typeof(e) != 'undefined')
	{
		var Rect = selCurElm.getBoundingClientRect();
		var outrect = false;

		if(e.clientY < Rect.top)
			outrect = true;
		else if(e.clientX < Rect.left)
			outrect = true;
		else if(e.clientY > Rect.bottom)
			outrect = true;
		else if(e.clientX > Rect.right)
			outrect = true;

		// remove the click handler
		if(outrect)
			getSelected(selCurElm);
	}
}

/* Init the Overview Popup data array */
function InitPopup()
{
	pWind = {};
	pWindPos = {};
	oPmxTitle = [];
	oPmxAcs = [];
	$('#pmx_form').css('style', '');
}
InitPopup();

/* Restore Popup settings. Called after close */
function restorePopup()
{
	if(pWind)
	{
		document.getElementById('pWind.name').value = '';
		document.getElementById('pmx_form').style.height = 'initial';

		pWind.style.position = '';
		pWind.style.top = 0;
		pWind.style.margin = 0;
		pWind.style.left = 0;
		$('#pmx_form').css('style', '');

		if(document.getElementById('pWind.select.'+ document.getElementById('pWind.side').value))
			document.getElementById('pWind.select.'+ document.getElementById('pWind.side').value).style.display = 'none';
	}
	InitPopup();
}

// init a special select dropdown handling
// set mousedown hander
function InitDropdownHandler(elm)
{
	if(mobile_device)
	{
		elm.style.padding = '2px 2px 3px 2px';
		return;
	}
	pWind.dropdown = {};
	pWind.dropdown.elm = elm;
	pWind.dropdown.elm.size = 1;
	pWind.dropdown.elmClass = pWind.dropdown.elm.className;
	pmxAddClass(pWind.dropdown.elm, 'dropdown');
	pWind.dropdown.elm.onmousedown = function(){setHandleDropdown();};
	optH = parseInt($('.dropdown option').css('height')) + 2;
	if(isNaN(optH))
		optH = parseInt($('option').css('height')) + 2;
	pWind.dropdown.optHeight = optH;
	pWind.dropdown.closedHeight = parseInt($('select').css('height'));
	pWind.dropdown.ignore_click = !isFireFox;
	rect = pWind.dropdown.elm.getBoundingClientRect();
	if(isFireFox)
		pWind.dropdown.elm.style.padding = '0px';
	pWind.dropdown.elm.style.height = pWind.dropdown.closedHeight +'px';
	if(pWind.dropdown.elm.id == 'pmx.check.type')
		setHandleDropdown();
}

// called on the mousedown event
function setHandleDropdown()
{
	// now mousedown do nothing more
	pWind.dropdown.elm.onmousedown = function(){};
	pWind.dropdown.elm.style.height = pWind.dropdown.closedHeight +'px';
	pWind.dropdown.elm.size = pWind.dropdown.elm.options.length > 10 ? 10 : pWind.dropdown.elm.options.length;
	aniheight = (pWind.dropdown.elm.size * pWind.dropdown.optHeight) + (isFireFox ? 3 : 6);
	$(pWind.dropdown.elm).animate({height: aniheight},{duration: 50, easing: 'linear', complete: function(){moveDropdownElmTop()}});

	// set a click handler for selection and outside
	pWind.dropdown.elm.onclick = function(){getHandleDropdown();};
	document.addEventListener('click', closeHandleDropdown);
	pWind.dropdown.haveEvent = true;
}

// event handler for click on dropdown selection
// get selected element, set size to 1
// remove outside and click handler
// set mousedown handler
function getHandleDropdown()
{
	// Ignore the first click after mousedown
	if(pWind.dropdown.ignore_click)
		pWind.dropdown.ignore_click = false;
	else
	{
		moveDropdownElmTop();
		$(pWind.dropdown.elm).animate({height: pWind.dropdown.closedHeight +'px'},{duration: 50, easing: 'linear', complete: function(){pWind.dropdown.elm.size=1;pWind.dropdown.elm.style.height=pWind.dropdown.closedHeight +'px'}});

		// remove click handler and outside click
		pWind.dropdown.elm.onclick = function(){};
		document.removeEventListener('click', closeHandleDropdown);
		pWind.dropdown.haveEvent = false;

		// set a mousedown event handler
		pWind.dropdown.ignore_click = !isFireFox;
		pWind.dropdown.elm.onmousedown = function(){setHandleDropdown()};
	}
}

// move selected element to top
function moveDropdownElmTop()
{
	if(!isIE)
		pWind.dropdown.elm.scrollTop = pWind.dropdown.elm.selectedIndex * pWind.dropdown.optHeight;
	pWind.dropdown.elm.focus();
}

// event handler for click outside the selection
function closeHandleDropdown(e)
{
	if(typeof(e) != 'undefined')
	{
		var Rect = pWind.dropdown.elm.getBoundingClientRect();
		var outrect = false;

		if(e.clientY < Rect.top)
			outrect = true;
		else if(e.clientX < Rect.left)
			outrect = true;
		else if(e.clientY > Rect.bottom)
			outrect = true;
		else if(e.clientX > Rect.right)
			outrect = true;

		// remove the click handler, set mousedown handler
		if(outrect)
		{
			$(pWind.dropdown.elm).animate({height: pWind.dropdown.closedHeight +'px'},{duration: 50, easing: 'linear', complete: function(){pWind.dropdown.elm.size = 1}});
			document.removeEventListener('click', closeHandleDropdown);
			pWind.dropdown.elm.onclick = function(){};
			pWind.dropdown.haveEvent = false;

			pWind.dropdown.elm.onmousedown=function(){setHandleDropdown()};
			pWind.dropdown.ignore_click = !isFireFox;
		}
	}
}

// this is called even if a popup closed
// clear the dropdown function && remove the outside event
function clearHandleDropdown()
{
	// the title popup image dropdown
	if(haveEvent)
	{
		document.removeEventListener('click', closeTTLDropDown);
		$(DropDownElm).slideUp('fast');
		TtlpopupInit();
	}

	// all other custom popup dropdown
	else if(pWind.dropdown && pWind.dropdown.elm)
	{
		pWind.dropdown.elm.size = 1;
		pWind.dropdown.elm.style.height = pWind.dropdown.closedHeight +'px';
		pWind.dropdown.elm.className = pWind.dropdown.elmClass;
		pWind.dropdown.elm.onmousedown = function(){};
		pWind.dropdown.elm.onclick = function(){};
		if(pWind.dropdown.haveEvent)
		{
			document.removeEventListener('click', closeHandleDropdown);
			pWind.dropdown.haveEvent = false;
		}
		pWind.dropdown = {};
	}
}

// Toggle languages on overview
function pWindToggleLang(pSide)
{
	var side = ((pSide) ? pSide.replace(/\./, '') : '');
	var currlang = document.getElementById('pWind.def.lang.'+ side).innerHTML;
	var elm = document.getElementById('pWind.lang.sel');
	var allid = [];
	for(var idx = 0; idx < elm.length; idx++)
	{
		if(elm.options[idx].value == currlang)
			var nextlang = (idx + 1 < elm.length ? elm.options[idx +1].value : elm.options[0].value);
	}
	if(document.getElementById('pWind.all.ids.'+ side))
	{
		var tmp = document.getElementById('pWind.all.ids.'+ side).value;
		if(tmp.indexOf(',') > 0)
			allid = tmp.split(',');
		else
			allid[0] = tmp;
		for(var idx = 0; idx < allid.length; idx++)
		{
			if(document.getElementById('sTitle.text.'+ nextlang +'.'+ allid[idx] +'.'+ side))
				document.getElementById('sTitle.text.'+ allid[idx] +'.'+ side).innerHTML = pmxHtmlSpecialChars(document.getElementById('sTitle.text.'+ nextlang +'.'+ allid[idx] +'.'+ side).value);
		}
		document.getElementById('pWind.def.lang.'+ side).innerHTML = nextlang;
		document.getElementById('pWind.language.'+ side).value = nextlang;
	}
}

/*
	Show the popwindow
	yofs can by: nn or -nn and means offset top
*/
function pmx_PopUpPos(posID, yofs, elm)
{
	var formRect = document.getElementById('pmx_form').getBoundingClientRect();
	document.getElementById('pmx_form').style.height = Math.round(formRect.height) +'px';
	var elmRect = elm.getBoundingClientRect();
	pWind.style.top = ((yofs + elmRect.top -7) - formRect.bottom) +'px';
	pWind.style.left = '0';
	pWind.style.margin = '0px auto';

	// show the popup
	$(pWind).fadeIn(300, function(){$('.pmx_popupfrm').blur();});
}

// toggle the block status (active/passive)
function pToggleStatus(id, side)
{
	var newState = document.getElementById('status.'+ id).className == 'pmx_clickrow pmx_active' ? 'pmx_notactive' : 'pmx_active';
	curTop = $(window).scrollTop();
	var status;

	if(side == 'head' || side == 'top')
	{
		if(document.getElementById('pmx_'+ side + '_panel'))
			oldHeight = document.getElementById('pmx_'+ side +'_panel').scrollHeight;
		else
			oldHeight = 0;
	}

	// fill in the postData
	var postData = {};
	postData['xml'] = '1',
	postData['sc'] = pmx_session_id;
	postData['function'] = 'overview';
	postData['sa'] = side;
	postData['chg_status'] = id;

	result = pmxXMLpost(window.location.href.replace(/\;/g, '&'), postData);
	status = result.split(',');

	document.getElementById('status.'+ id).className = 'pmx_clickrow '+ (status[0] == '1' ? 'pmx_active' : 'pmx_notactive');
	document.getElementById('status.'+ id).title = (status[0] == '1' ? BlockActive : BlockInactive);
	if(document.getElementById('block.id.'+ id))
		document.getElementById('block.id.'+ id).style.display = (status[0] == '1' && status[1] == '1' ? 'block' : 'none');

	if(pmx_blockOnOff_enabled && (side == 'head' || side == 'top'))
		AdjustTop(side, curTop, oldHeight)
}

// toggle the Article status/approve (active/passive)
function pToggleArtStatus(elm, id, mode)
{
	var postData = {};
	postData['xml'] = '1',
	postData['sc'] = pmx_session_id;
	postData['sa'] = 'overview';
	postData['sa'] = 'overview';
	postData['chg_'+ mode] = id;
	result = pmxXMLpost(window.location.href.replace(/\;/g, '&'), postData);

	var status = result.split(',');
	if(status[0] == id)
	{
		var idx = (status[1] != '0' ? mode : 'not'+ mode);
		elm.className = 'pmx_clickrow pmx_'+ idx;
		elm.title = Art[idx];
	}
}

// Block Manager Select BlockType popup
function SetpmxBlockType(side, sidedesc, poselm)
{
	if(pWind && pWind.id)
		return false;

	pWind.id = true;
	pWind = pmxPopupWindow('pmxBlockType', 0, side, true);
	if(pWind)
	{
		document.getElementById('pWind.title.bar').innerHTML = document.getElementById('pWind.blocktype.title').value.replace(/%s/, sidedesc);

		selm = document.getElementById('pmx.block.type');
		for(var i=0; i < selm.length; i++)
			selm[i].selected = false;
		selm[0].selected = true;

		// show the popup
		pmx_PopUpPos(side, 29, poselm);

		// setup special dropdown handling
		InitDropdownHandler(selm);
	}
}

// Block Manager Send Selected BlockType
function pmxSendBlockType()
{
	// remove event listener for blocktype selection
	clearHandleDropdown();

	var side = document.getElementById('pWind.side').value;
	var selm = document.getElementById('pmx.block.type');
	var blocktype = selm.options[selm.selectedIndex].value;

	var elm = document.getElementById('addnodes.'+ side);
	var newelm = document.createElement('input');
	newelm.id = 'add_new_block';
	newelm.name = 'add_new_block['+ side +']';
	newelm.type = 'hidden';
	elm.appendChild(newelm);
	document.getElementById('add_new_block').value = blocktype;

	FormFunc('create', side);
	pmxRemovePopup();
}

// Block Manager RowMove
function pmxRowMove(id, side, poselm)
{
	if(pWind && pWind.id)
		return false;

	var allid = document.getElementById('pWind.all.ids.'+ side).value.split(',');
	if(allid.length > 1)
	{
		pWind = pmxPopupWindow('pmxRowMove', id, side);
		if(pWind)
		{
			document.getElementById('pWind.move.blocktyp').innerHTML = '[<b>'+ document.getElementById('pWind.pos.'+ side +'.'+ id).innerHTML +'</b>] '+ document.getElementById('pWind.desc.'+ side +'.'+ id).innerHTML;
			document.getElementById('pWind.desc.'+ side +'.'+ id).innerHTML;
			document.getElementById('pWind.place.0').checked = true;

			// show the popup
			pmx_PopUpPos(id, 0, poselm);
			document.getElementById('pWind.select.'+ side).style.display = 'block';
			document.getElementById('pWind.select.'+ side).style.position = 'absolute';
			document.getElementById('pWind.select.'+ side).selectedIndex = 0;

			// init dropdown handling
			InitDropdownHandler(document.getElementById('pWind.select.'+ side));
		}
	}
}

// Block Manager Send RowMove
function pmxSendRowMove()
{
	var id = document.getElementById('pWind.id').value;
	var side = document.getElementById('pWind.side').value;

	for(var i = 0; i < 2; i++)
		var place = (document.getElementById('pWind.place.'+ i).checked == true ? document.getElementById('pWind.place.'+ i).value : place);

	var rowpos = [];
	var selelm = document.getElementById('pWind.select.'+ side)
	for(var i = 0; i < selelm.length; i++)
	{
		if(selelm.options[i].selected == true)
			var iSel = i;
		rowpos[selelm.options[i].value] = selelm.options[i].text.substr(1, selelm.options[i].text.indexOf(']')-1);
	}
	var toID = selelm.options[iSel].value;

	if(rowpos[toID] < rowpos[id] && place == 'after')
		var toID = selelm.options[iSel +1].value;
	if(rowpos[toID] > rowpos[id] && place == 'before')
		var toID = selelm.options[iSel -1].value;

	// check..
	var delta = Math.abs(rowpos[toID] - rowpos[id]);
	if(Math.abs(rowpos[toID] - rowpos[id]) == 0)
	{
		if(confirm(document.getElementById('pWind.move.error').value) == true)
			return;
		else
		{
			pmxRemovePopup();
			return;
		}
	}

	var elm = document.getElementById('addnodes.'+ side);
	if(!document.getElementById('upd_rowpos.'+ id))
	{
		var newelm = document.createElement('input');
		newelm.id = 'upd_rowpos.'+ id;
		newelm.name = 'upd_rowpos['+ side +'][rowpos]';
		newelm.type = 'hidden';
		elm.appendChild(newelm);
	}
	document.getElementById('upd_rowpos.'+ id).value = id +','+ place +','+ toID;

	pmxCookie('set', 'YOfs', pmxWinGetTop());
	FormFunc('', 0);
	pmxRemovePopup();
}

// Title Settings popup
function pmxSetTitle(id, Bside, poselm)
{
	if(pWind && pWind.id)
		return false;

	var side = ((Bside) ? Bside.replace(/\./, '') : '');
	pWind = pmxPopupWindow('pmxSetTitle', id, side);
	if(pWind)
	{
		// check the need object
		if(!oPmxTitle[id])
			oPmxTitle[id] = {};
		if(!oPmxTitle[id].text)
			oPmxTitle[id].text = {};
		if(!oPmxTitle[id].lang)
			oPmxTitle[id].lang = [];

		// Get the input values
		oPmxTitle[id].Language = document.getElementById('pWind.language.'+ side).value;
		var elm = document.getElementById('pWind.lang.sel');
		for(var idx = 0; idx < elm.length; idx++)
		{
			oPmxTitle[id].lang[idx] = elm.options[idx].value;
			oPmxTitle[id].text[oPmxTitle[id].lang[idx]] = document.getElementById('sTitle.text.'+ oPmxTitle[id].lang[idx] +'.'+ id +'.'+ side).value;
			elm.options[idx].selected = elm.options[idx].value == oPmxTitle[id].Language;
		}
		oPmxTitle[id].icon = getIconName(document.getElementById('uTitle.icon.'+ id).src);
		oPmxTitle[id].align = document.getElementById('sTitle.align.'+ id).value;
		if(!oPmxTitle[id].align_keys)
			oPmxTitle[id].align_keys = ['left', 'center', 'right'];

		// Set the values in the Popup
		document.getElementById('pWind.text').value = oPmxTitle[id].text[oPmxTitle[id].Language];
		document.getElementById('pWind.icon').src = ttliconurl + (oPmxTitle[id].icon == '' ? 'none.png' : oPmxTitle[id].icon);
		document.getElementById('pWind.icon').style.width = '16px';
		document.getElementById('pWind.icon').style.height = '16px';
		document.getElementById('iconDD').value = ucFirst(oPmxTitle[id].icon == '' ? 'none.png' : oPmxTitle[id].icon);
		document.getElementById('pWindID').value = id;
		for(var i = 0; i < oPmxTitle[id].align_keys.length; i++)
			document.getElementById('pWind.align.'+ oPmxTitle[id].align_keys[i]).style.backgroundColor = '';
		document.getElementById('pWind.align.'+ oPmxTitle[id].align).style.backgroundColor = '#e02000';

		pmx_PopUpPos(id, 0, poselm);
	}
}

// Update Titles
function pmxUpdateTitles()
{
	var id = document.getElementById('pWind.id').value;
	var side = document.getElementById('pWind.side').value.replace(/\./, '');
	oPmxTitle[id].text[oPmxTitle[id].Language] = document.getElementById('pWind.text').value;

	var postData = {};
	postData['xml'] = '1',
	postData['result'] = 'ok',
	postData['sc'] = pmx_session_id;

	// if article or category?
	if(side == '' || side == 'cat')
	{
		if(side == 'cat')
			postData['save_overview'] = '1';

		postData['sa'] = 'overview';
		postData['upd_overview'] = {}
		postData['upd_overview']['title'] = {}
		postData['upd_overview']['title'][id] = {}
		postData['upd_overview']['title'][id]['icon'] = getIconName(document.getElementById('pWind.icon').src);
		postData['upd_overview']['title'][id]['align'] = oPmxTitle[id].align;
		postData['upd_overview']['title'][id]['lang'] = {};

		for(var i = 0; i < oPmxTitle[id].lang.length; i++)
			postData['upd_overview']['title'][id]['lang'][oPmxTitle[id].lang[i]] = oPmxTitle[id].text[oPmxTitle[id].lang[i]];
	}
	else
	{
		postData['function'] = 'overview';
		postData['sa'] = side;
		postData['save_overview'] = '1';
		postData['upd_overview'] = {};
		postData['upd_overview'][side] = {};
		postData['upd_overview'][side]['title'] = {};
		postData['upd_overview'][side]['title'][id] = {};
		postData['upd_overview'][side]['title'][id]['icon'] = getIconName(document.getElementById('pWind.icon').src);
		postData['upd_overview'][side]['title'][id]['align'] = oPmxTitle[id].align;
		postData['upd_overview'][side]['title'][id]['lang'] = {};

		for(var i = 0; i < oPmxTitle[id].lang.length; i++)
			postData['upd_overview'][side]['title'][id]['lang'][oPmxTitle[id].lang[i]] = oPmxTitle[id].text[oPmxTitle[id].lang[i]];
	}

	// update the overview screen
	XML_Result = pmxXMLpost(window.location.href.replace(/\;/g, '&'), postData);

	if(XML_Result == 'ok')
	{
		var elm = document.getElementById('pWind.lang.sel');
		for(var idx = 0; idx < elm.length; idx++)
			document.getElementById('sTitle.text.'+ elm.options[idx].value +'.'+ id +'.'+ side).value = oPmxTitle[id].text[elm.options[idx].value];

		var lang = document.getElementById('pWind.language.'+ side).value;
		document.getElementById('sTitle.text.'+ id +'.'+ side).innerHTML = pmxHtmlSpecialChars(oPmxTitle[id].text[lang]);
		document.getElementById('sTitle.align.'+ id).value = oPmxTitle[id].align;
		document.getElementById('sTitle.icon.'+ id).value = oPmxTitle[id].icon;
		document.getElementById('uTitle.align.'+ id).src = document.getElementById('pWind.image.url').value +'text_align_'+ oPmxTitle[id].align +'.gif';
		document.getElementById('uTitle.icon.'+ id).src = document.getElementById('pWind.icon').src;

		pmxRemovePopup();
	}
}

// Title align isModify
function pmxChgTitles_Align(key)
{
	var id = document.getElementById('pWind.id').value;
	document.getElementById('pWind.align.'+ oPmxTitle[id].align).style.backgroundColor = '';
	document.getElementById('pWind.align.'+ key).style.backgroundColor = '#e02000';
	oPmxTitle[id].align = key;
}

// Title lang Toggle
function pmxChgTitles_Lang(elm)
{
	var id = document.getElementById('pWind.id').value;
	var lang = elm.options[elm.selectedIndex].value;
	oPmxTitle[id].text[oPmxTitle[id].Language] = document.getElementById('pWind.text').value;
	document.getElementById('pWind.text').value = oPmxTitle[id].text[lang];
	oPmxTitle[id].Language = lang;
}

// Create Access popup
function pmxSetAcs(id, pSide, poselm)
{
	if(pWind && pWind.id)
		return false;

	var side = ((pSide) ? pSide : '');
	pWind = pmxPopupWindow('pmxSetAcs', id, side);
	if(pWind)
	{
		pWind.BlockSide = side;
		pWind.BlockID = id;
		pWind.selected = [];
		pWind.elmOpt = [];
		var selcount = 0;

		// set default checked
		document.getElementById('pWindAcsModeupd').checked = true;
		pWind.mode = 'upd';

		// get all groups
		pWind.allAcsGrp = document.getElementById('allAcsGroups').value.split(',');
		pWind.allNames = document.getElementById('allAcsNames').value.split(',');
		pWind.grpAcs = document.getElementById('grpAcs.'+ id).value.split(',');
		pWind.denyAcs = document.getElementById('denyAcs.'+ id).value.split(',');

		var access = [];
		var acsNames = [];
		var Iacs = 0;
		var Ideny = 0;
		var selelm = document.getElementById('pWindAcsGroup');

		for(var i = 0; i < selelm.length; i++)
		{
			selelm[i].value = pWind.allAcsGrp[i] + '=1';
			selelm[i].innerHTML = pWind.allNames[i].replace(/_/g, ' ');
			selelm[i].selected = false;
			selelm[i].disabled = false;

			if(pWind.grpAcs.indexOf(pWind.allAcsGrp[i]) != -1 && pWind.denyAcs.indexOf(pWind.allAcsGrp[i]) == -1)
				selelm[i].selected = true;

			if(pWind.denyAcs.indexOf(pWind.allAcsGrp[i]) != -1)
			{
				selelm[i].value = pWind.allAcsGrp[i] + '=0';
				selelm[i].innerHTML = '^'+ pWind.allNames[i];
				selelm[i].selected = true;
			}
			pWind.elmOpt[i] = selelm[i];
		}

		// set update mode
		document.getElementById('pWindAcsMode'+ pWind.mode).checked = true;

		// show the popup
		pmx_PopUpPos(side, -1, poselm);
	}
}

// access handing mode
function pmxSetAcsMode(mode)
{
	pWind.mode = mode;

	var elm = document.getElementById('pWindAcsGroup');
	for(var i = 0; i < elm.length; i++)
	{
		elm[i] = pWind.elmOpt[i];

		if(mode == 'upd')
			elm[i].disabled = false;

		if(mode == 'add')
		{
			if(elm[i].selected)
				elm[i].disabled = true;
			else
				elm[i].disabled = false;
		}

		if(mode == 'del')
		{
			if(elm[i].selected == false)
				elm[i].disabled = true;
			else
				elm[i].disabled = false;
		}
	}
}

// Update Access
function pmxUpdateAcs(all)
{
	var id = pWind.BlockID;
	var side = pWind.BlockSide;

	if(all)
		var allid = document.getElementById('pWind.all.ids.'+ side).value.split(',');
	else
		var allid = document.getElementById('pWind.id').value.split(',');

	var mode = pWind.mode;
	var grpstr = '';
	var updates = '';
	var selelm = document.getElementById('pWindAcsGroup');

	for(var i = 0; i < selelm.length; i++)
		if(selelm.options[i].selected == true)
			grpstr += (grpstr == '' ? selelm.options[i].value : ','+ selelm.options[i].value);

	// fill in the postData
	var postData = {};
	postData['xml'] = '1',
	postData['sc'] = pmx_session_id;

	if(side != '' && side != 'cat')
	{
		postData['function'] = 'overview';
		postData['sa'] = side;
		postData['save_overview'] = '1';
		postData['upd_overview'] = {};
		postData['upd_overview'][side] = {};
		postData['upd_overview'][side][mode +'access'] = {};

		for(var i = 0; i < allid.length; i++)
			postData['upd_overview'][side][mode +'access'][allid[i]] = grpstr;
	}
	else
	{
		postData['sa'] = 'overview';
		postData['upd_overview'] = {}
		postData['upd_overview'][mode +'access'] = {};

		for(var i = 0; i < allid.length; i++)
			postData['upd_overview'][mode +'access'][allid[i]] = grpstr;
	}

	// send data
	xmlResult = pmxXMLpost(window.location.href.replace(/\;/g, '&'), postData);

	// process returned data
	var data = xmlResult.split('&');
	for(d = 0; d < data.length; d++)
	{
		// record we have updated
		var id_acs_img = data[d].split('|');
		var block = id_acs_img[0];
		var newAcs = '';
		var newDeny = ''

		// update the acs/denyacs
		var acsdeny = id_acs_img[1].split(',');
		for(var i = 0; i < acsdeny.length; i++)
		{
			if(acsdeny[i].indexOf('=1') >= 0)
				newAcs += (newAcs != '' ? ',' : '') + acsdeny[i].substr(0, acsdeny[i].indexOf('='));
			else
				newDeny += (newDeny != '' ? ',' : '') + acsdeny[i].substr(0, acsdeny[i].indexOf('='));
		}
		if(document.getElementById('grpAcs.'+ block))
			document.getElementById('grpAcs.'+ block).value = newAcs;
		if(document.getElementById('denyAcs.'+ block))
			document.getElementById('denyAcs.'+ block).value = newDeny;

		// update the ACS Image
		if(document.getElementById('pWind.grp.'+ block))
		{
			if(id_acs_img[2] == '0')
			{
				document.getElementById('pWind.grp.'+ block).className = 'pmx_clickrow';
				document.getElementById('pWind.grp.'+ block).title = '';
			}
			else
			{
				document.getElementById('pWind.grp.'+ block).className = 'pmx_clickrow pmx_access';
				document.getElementById('pWind.grp.'+ block).title = acs_title;
			}
		}

		// show / hide Block on new ACS
		if(side != '' && side != 'cat')
		{
			if(document.getElementById('block.id.'+ block))
			{
				if(id_acs_img[3] == '1' && id_acs_img[4] == '1')
					document.getElementById('block.id.'+ block).style.display = 'block';
				else
					document.getElementById('block.id.'+ block).style.display = 'none';
			}
		}
	}

	pWind.elmOpt = null;
	pmxRemovePopup();
}

// Block Manager Clone/Move
function pmxSetCloneMove(id, side, wType, blockType, poselm)
{
	pWind = pmxPopupWindow('pmxSetCloneMove', id, side);
	if(pWind)
	{
		document.getElementById('title.clone.move').innerHTML = document.getElementById('pWind.txt.'+ wType).value;
		document.getElementById('pWind.clone.move.blocktype').innerHTML = document.getElementById('pWind.desc.'+ side +'.'+ id).innerHTML;
		document.getElementById('pWind.worktype').value = wType;

		var selm = document.getElementById('pWind.sel.sides');
		if(blockType == 'html' || blockType == 'bbc_script' || blockType == 'script' || blockType == 'php')
		{
			if(selm.options[selm.length -1].value != 'articles')
			{
				var addoption = new Option(document.getElementById('pWind.addoption').value, 'articles', false, false);
				selm.options[selm.length] = addoption;
			}
		}
		else
		{
			if(selm.options[selm.length -1].value == 'articles')
				selm.options[selm.length -1] = null;
		}

		for(var i=0; i < selm.length; i++)
			selm.options[i].selected = false;

		// setup special dropdown handling
		InitDropdownHandler(selm);

		// show the popup
		pmx_PopUpPos(side, -1, poselm);
	}
}

// Block Manager send Clone/Move
function pmxSendCloneMove()
{
	var id = document.getElementById('pWind.id').value;
	var side = document.getElementById('pWind.side').value;
	var selm = document.getElementById('pWind.sel.sides');
	var toSide = selm.options[selm.selectedIndex].value;
	var wType = document.getElementById('pWind.worktype').value;

	if(wType == 'move' && side == toSide)
	{
		if(confirm(document.getElementById('pWind.move.error').value) == true)
			return;
		else
		{
			pmxRemovePopup();
			return;
		}
	}

	var elm = document.getElementById('addnodes.'+ side);
	var newelm = document.createElement('input');
	newelm.name = wType +'_block';
	newelm.type = 'hidden';
	newelm.value = id +','+ toSide;
	elm.appendChild(newelm);
	if(wType == 'move')
		FormFunc(wType, toSide+','+side);
	else
		FormFunc(wType, toSide+','+side);
	pmxRemovePopup();
}

// Popup for Block Manager delete block
function pmxSetDelete(id, side, poselm)
{
	pWind = pmxPopupWindow('pmxSetDelete', id, side);
	if(pWind)
	{
		document.getElementById('pWind.blockid').value = id;
		document.getElementById('pWind.delete.blocktype').innerHTML = document.getElementById('pWind.desc.'+ side +'.'+ id).innerHTML;

		// show the popup right align
		pmx_PopUpPos(side, -1, poselm);
	}
}

// Send Block Manager delete block
function pmxSendDelete()
{
	FormFunc('block_delete', document.getElementById('pWind.blockid').value);
	pmxRemovePopup();
}

// Article Filter popup
function pmxSetFilter(poselm)
{
	if(pWind && pWind.id)
		return false;

	pWind = pmxPopupWindow('pmxSetFilter', 0);
	if(pWind)
	{
		// show the popup
		pmx_PopUpPos(0, (isFireFox ? -3 : -5), poselm);
	}
}

// clear category filter
function pmxSetFilterCatClr()
{
	var elm = document.getElementById('pWind.filter.category');
	for(var i = 0; i < elm.options.length; i++)
		elm.options[i].selected = false;
	document.getElementById('pWind.filter.approved').checked = false;
	document.getElementById('pWind.filter.active').checked = false;
	if(document.getElementById('pWind.filter.myown'))
		document.getElementById('pWind.filter.myown').checked = false;
	if(document.getElementById('pWind.filter.member'))
		document.getElementById('pWind.filter.member').value = '';
}

// Send Article Filter
function pmxSendFilter()
{
	document.getElementById('set.filter.category').value = '';
	var elm = document.getElementById('pWind.filter.category');
	for(var i = 0; i < elm.options.length; i++)
		if(elm.options[i].selected == true)
			document.getElementById('set.filter.category').value += elm.options[i].value + ',';

	document.getElementById('set.filter.approved').value = (document.getElementById('pWind.filter.approved').checked == true? 1 : 0);
	document.getElementById('set.filter.active').value = (document.getElementById('pWind.filter.active').checked == true ? 1 : 0);
	if(document.getElementById('pWind.filter.myown'))
		document.getElementById('set.filter.myown').value = (document.getElementById('pWind.filter.myown').checked == true ? 1 : 0);
	else
		document.getElementById('set.filter.myown').value = 0;
	if(document.getElementById('pWind.filter.member'))
		document.getElementById('set.filter.member').value = document.getElementById('pWind.filter.member').value;
	else
		document.getElementById('set.filter.member').value = '';

	pmxCookie('set', 'YOfs', pmxWinGetTop());
	FormFunc('null', 0);
	pmxRemovePopup();
}

// Category popup
function pmxSetCats(id, poselm)
{
	if(pWind && pWind.id)
		return false;

	pWind = pmxPopupWindow('pmxSetCats', id);
	if(pWind)
	{
		var allid = document.getElementById('pWind.all.ids.').value.split(',');
		var allCats = document.getElementById('pWind.all.cats').value.split(',');
		for(var c = 0; c < allCats.length; c++)
		{
			for(var i = 0; i < allid.length; i++)
			{
				elm = 'pWind.cat.'+ allCats[c] +'.'+ allid[i];
				if(document.getElementById(elm) && document.getElementById(elm).style.display == 'block')
					document.getElementById('pWind.catid.'+ allid[i]).value = allCats[c];
			}
		}

		var elm = document.getElementById('pWind.cats.sel');
		for(var s = 0; s < elm.length; s++)
			elm.options[s].selected = (elm.options[s].value == document.getElementById('pWind.catid.'+ id).value);

		// show the popup
		pmx_PopUpPos(id, 0, poselm);
	}
}

// Change Category from popup
function pmxChgCats(elm)
{
	var id = document.getElementById('pWind.id').value;
	document.getElementById('pWind.catid.'+ id).value = elm.options[elm.selectedIndex].value;
}

// Update Categorys
function pmxUpdateCats(all)
{
	var id = document.getElementById('pWind.id').value;
	var SelCat = document.getElementById('pWind.catid.'+ id).value;

	// fill the Postdata
	var postData = {};
	postData['xml'] = '1',
	postData['result'] = 'ok',
	postData['sc'] = pmx_session_id;
	postData['sa'] = 'overview';
	postData['upd_overview'] = {};
	postData['upd_overview']['category'] = {};

	if(all)
		var allid = document.getElementById('pWind.all.ids.').value.split(',');
	else
		var allid = document.getElementById('pWind.id').value.split(',');

	for(var i = 0; i < allid.length; i++)
		postData['upd_overview']['category'][allid[i]] = document.getElementById('pWind.catid.'+ id).value;

	result = pmxXMLpost(window.location.href.replace(/\;/g, '&'), postData);

	if(result == 'ok')
	{
		var allCats = document.getElementById('pWind.all.cats').value.split(',');
		for(var c = 0; c < allCats.length; c++)
		{
			for(var i = 0; i < allid.length; i++)
			{
				elm = 'pWind.cat.'+ allCats[c] +'.'+ allid[i];
				if(document.getElementById(elm))
					document.getElementById(elm).style.display = (allCats[c] == SelCat ? 'block' : 'none');
			}
		}
	}
	pmxRemovePopup();
}

// Popup for Article Manager delete article
function pmxSetArtDelete(id, poselm)
{
	if(pWind && pWind.id)
		return false;

	pWind = pmxPopupWindow('pmxSetArtDelete', id);
	if(pWind)
	{
		// show the popup
		pmx_PopUpPos(id, -1, poselm);
	}
}

// Send Article Manager delete article
function pmxSendArtDelete()
{
	pmxCookie('set', 'YOfs', pmxWinGetTop());
	FormFunc('delete_article', document.getElementById('pWind.id').value);
	pmxRemovePopup();
}

// Popup for Article Manager clone article
function pmxSetArtClone(id, poselm)
{
	if(pWind && pWind.id)
		return false;

	pWind = pmxPopupWindow('pmxSetArtClone', id);
	if(pWind)
	{
		// show the popup
		pmx_PopUpPos(id, -1, poselm);
	}
}

// Send Article Manager clone article
function pmxSendArtClone()
{
	FormFunc('clone_article', document.getElementById('pWind.id').value);
	pmxRemovePopup();
}

// Create show arts in cat popup
function pmxShowArt(id, poselm)
{
	if(pWind && pWind.id)
		return false;

	pWind = pmxPopupWindow('pmxShowArt', id);
	if(pWind)
	{
		document.getElementById('artsorttxt').innerHTML = '';
		document.getElementById('artsort').innerHTML = '';
		document.getElementById('showarts').innerHTML = '';

		var arts = document.getElementById('pWind.catarts.'+ id).value.split('|');
		var sorts = document.getElementById('pWind.artsort.'+ id).value.split('|');
		document.getElementById('artsorttxt').innerHTML = '<b>'+ document.getElementById('pWind.artsorttxt.'+ id).value +'</b>';
		for(var i = 0; i < sorts.length; i++)
			document.getElementById('artsort').innerHTML += '<i>'+ sorts[i] + '</i><br />';
		for(var i = 0; i < arts.length; i++)
			document.getElementById('showarts').innerHTML += arts[i] + '<br />';

		// show the popup
		pmx_PopUpPos(id, -1, poselm);
	}
}

// Create category Move popup
function pmxSetMove(id, poselm)
{
	if(pWind && pWind.id)
		return false;

	pWind = pmxPopupWindow('pmxSetMove', id);
	if(pWind)
	{
		document.getElementById('pWind.move.catname').innerHTML = '<b>'+ document.getElementById('pWind.move.cat.'+ id).value +'</b>';
		document.getElementById('pWind.move.catname').title = document.getElementById('pmxSetMove.'+ id).title.substring(0, document.getElementById('pmxSetMove.'+ id).title.indexOf(' - '));

		// show the popup
		pmx_PopUpPos(id, -1, poselm);

		// init dropdown handling
		InitDropdownHandler(document.getElementById('pWind.sel.destcat'));
	}
}

// Save category Move
function pmxSaveMove()
{
	var id = document.getElementById('pWind.id').value;
	var elm = document.getElementById('pWind.sel.destcat');
	var destid = elm.options[elm.selectedIndex].value;

	if(id == destid)
	{
		if(confirm(document.getElementById('pWind.move.error').value) == true)
			return;
		else
		{
			pmxRemovePopup();
			return;
		}
	}

	var place = 0;
	for(var i = 0; i < 3; i++)
		var place = (document.getElementById('pWind.place.'+ i).checked == true ? document.getElementById('pWind.place.'+ i).value : place);

	var elm = document.getElementById('addnodes');
	if(!document.getElementById('catplace'))
	{
		var newelm = document.createElement('input');
		newelm.id = 'catplace';
		newelm.name = 'catplace';
		newelm.type = 'hidden';
		elm.appendChild(newelm);
	}
	document.getElementById('catplace').value = place;

	if(!document.getElementById('movetocat'))
	{
		var newelm = document.createElement('input');
		newelm.id = 'movetocat';
		newelm.name = 'movetocat';
		newelm.type = 'hidden';
		elm.appendChild(newelm);
	}
	document.getElementById('movetocat').value = destid;

	pmxRemovePopup();
	FormFunc('move_category', id);
}

// Create category Name popup
function pmxSetCatName(id, poselm)
{
	if(pWind && pWind.id)
		return false;

	pWind = pmxPopupWindow('pmxSetCatName', id);
	if(pWind)
	{
		document.getElementById('check.name').value = document.getElementById('pWind.cat.name.'+ id).innerHTML;

		// show the popup
		pmx_PopUpPos(id, 4, poselm);
	}
}

// Update category Name
function pmxUpdateCatName()
{
	var id = document.getElementById('pWind.id').value;
	if(document.getElementById('check.name').value != document.getElementById('pWind.cat.name.'+ id).innerHTML)
	{
		if(document.getElementById('check.name').value == '')
			alert(document.getElementById('check.name.error').innerHTML);
		else
		{
			// fill the Postdata
			var postData = {};
			postData['xml'] = '1',
			postData['result'] = 'ok',
			postData['sc'] = pmx_session_id;
			postData['sa'] = 'overview';
			postData['save_overview'] = '1';
			postData['upd_overview'] = {}
			postData['upd_overview']['catname'] = {}
			postData['upd_overview']['catname'][id] = document.getElementById('check.name').value;

			result = pmxXMLpost(window.location.href.replace(/\;/g, '&'), postData);
			if(result == 'ok')
			{
				document.getElementById('pWind.cat.name.'+ id).innerHTML = document.getElementById('check.name').value;
				pmxRemovePopup();
			}
		}
	}
	else
		pmxRemovePopup();
}

// Popup for Category Manager delete cat
function pmxSetCatDelete(id, poselm)
{
	if(pWind && pWind.id)
		return false;

	pWind = pmxPopupWindow('pmxSetCatDelete', id);
	if(pWind)
	{
		pWind.style.left = pWind.style.left.replace(/px/, '') - 55+'px';
		document.getElementById('pWind.catdelid').value = id;

		// show the popup
		pmx_PopUpPos(id, -1, poselm);
	}
}

// Send Category Manager delete category
function pmxSendCatDelete()
{
	FormFunc('delete_category', document.getElementById('pWind.catdelid').value);
	pmxRemovePopup();
}

// Popup for Category Manager Clone cat
function pmxSetCatClone(id, poselm)
{
	if(pWind && pWind.id)
		return false;

	pWind = pmxPopupWindow('pmxSetCatClone', id);
	if(pWind)
	{
		// show the popup
		pmx_PopUpPos(id, -1, poselm);
	}
}

// Send Category Manager Clone cat
function pmxSendCatClone()
{
	FormFunc('clone_category', document.getElementById('pWind.id').value);
	pmxRemovePopup();
}

// Article Manager Select ArticleType
function SetpmxArticleType(poselm)
{
	if(pWind && pWind.id)
		return false;

	pWind.id = true;
	pWind = pmxPopupWindow('pmxArticleType', 0);
	if(pWind)
	{
		// show the popup
		pmx_PopUpPos('', 29, poselm);

		// init dropdown handling
		InitDropdownHandler(document.getElementById('pmx.article.type'));
	}
}

// Article Manager Send Selected ArticleType
function pmxSendArticleType()
{
	pmxCookie('set', 'YOfs', '#pmx_form');

	var selm = document.getElementById('pmx.article.type');
	var blocktype = selm.options[selm.selectedIndex].value;

	var elm = document.getElementById('addnodes');
	var newelm = document.createElement('input');
	newelm.id = 'add_new_article';
	newelm.name = 'add_new_article';
	newelm.type = 'hidden';
	elm.appendChild(newelm);
	document.getElementById('add_new_article').value = blocktype;

	FormFunc('add_new_article', 0);
	pmxRemovePopup();
}

// Article Manager RowMove
function pmxArtMove(id, name, poselm)
{
	if(pWind && pWind.id)
		return false;

	pWind = pmxPopupWindow('pmxRowMove', id);
	if(pWind)
	{
		document.getElementById('pWind.move.pos').innerHTML = '[<b>'+ id +'</b>] '+ name +'';
		document.getElementById('pWind.place.1').checked = true;

		pmx_PopUpPos(id, 0, poselm);

		document.getElementById('pWind.sel').style.display = 'block';
		document.getElementById('pWind.sel').style.position = 'absolute';

		// init dropdown handling
		InitDropdownHandler(document.getElementById('pWind.sel'));
	}
}

// Article Manager Send RowMove
function pmxSendArtMove()
{
	var id = document.getElementById('pWind.id').value;

	for(var i = 0; i < 2; i++)
		var place = (document.getElementById('pWind.place.'+ i).checked == true ? document.getElementById('pWind.place.'+ i).value : place);

	var rowpos = [];
	var selelm = document.getElementById('pWind.sel')
	for(var i = 0; i < selelm.length; i++)
	{
		if(selelm.options[i].selected == true)
			var iSel = i;
	}

	// check..
	var toID = selelm.options[iSel].value;
	if(toID == id)
	{
		if(confirm(document.getElementById('pWind.move.error').value) == true)
			return;
		else
		{
			pmxRemovePopup();
			return;
		}
	}

	var elm = document.getElementById('addnodes');
	if(!document.getElementById('upd_rowpos'))
	{
		var newelm = document.createElement('input');
		newelm.id = 'upd_rowpos';
		newelm.name = 'upd_rowpos';
		newelm.value = id +','+ place +','+ toID;
		newelm.type = 'hidden';
		elm.appendChild(newelm);
	}

	pmxCookie('set', 'YOfs', pmxWinGetTop());
	FormFunc('', 0);
	pmxRemovePopup();
}

// Create a Popup window
function pmxPopupWindow(elmname, id, pSide)
{
	var side = ((pSide) ? pSide : '');
	pWind = document.getElementById(elmname);
	pWind.style.display = 'none';
	pWind.style.position = 'relative';

	if(pWind.style.display != '' && document.getElementById('pWind.name').value == 0)
	{
		document.getElementById('pWind.id').value = id;
		document.getElementById('pWind.side').value = side;
		document.getElementById('pWind.name').value = elmname;
		return pWind;
	}
	else
		return null;
}

// Remove a Popup window
function pmxRemovePopup()
{
	if(document.getElementById('pWind.name') !== null && document.getElementById('pWind.name').value != '')
	{
		$(pWind).fadeOut(300, function(){window.setTimeout(500, restorePopup())});

		// remove event listener for blocktype selection
		clearHandleDropdown();
	}
	else
		InitPopup();
}
window.onunload = pmxRemovePopup;

// convert specialchar in title
function pmxHtmlSpecialChars(text)
{
	text = text.replace(/&/g, "&amp;");
	text = text.replace(/"/g, "&quot;");
	text = text.replace(/'/g, "&#039;");
	text = text.replace(/</g, "&lt;");
	text = text.replace(/>/g, "&gt;");
	return text;
}
/* eof */