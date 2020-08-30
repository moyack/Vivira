<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file Frames.template.php
 * Template for the Block/Category/Article frame.
 *
 * @version 1.41
 */

/**
* Top frame
**/
function Pmx_Frame_top($cfg, $count, $isSB = false)
{
	global $context, $scripturl, $options, $txt;

	$context['pmx_framecount']++;
	$context['pmx_frames'][$context['pmx_framecount']] = true;

	if(!empty($cfg['config']['skip_outerframe']))
	{
		$context['pmx_frames'][$context['pmx_framecount']] = false;
		return null;
	}

	// get the block title for user language have it or forum default
	$blocktitle = PortaMx_getTitle($cfg['config']);

	// the title align
	$ttladjust = '';
	switch($cfg['config']['title_align'])
	{
		case 'left':
			$imgalign = 'right';
			$txtalign = 'left';
			$ttlimg = $txtalign;
			$toggleClass = 'class="float'. $imgalign;
			break;

		case 'right':
			$imgalign = 'left';
			$txtalign = 'right';
			$ttlimg = $txtalign;
			$toggleClass = 'class="float'. $imgalign;
			break;

		case 'center':
			$imgalign = 'right';
			$txtalign = 'center';
			$ttlimg = 'left';
			$toggleClass = 'class="float'. $imgalign;
	}

	if($cfg['config']['title_icon'] == 'none.png' || empty($cfg['config']['title_icon']))
	{
		$cfg['config']['title_icon'] = '';
		$ttladjust = ' pmxadj';
	}
	else if($cfg['config']['title_align'] == 'center')
	{
		if(!empty($cfg['config']['title_icon']) && !empty($cfg['config']['collapse']) && $context['pmx']['settings']['shrinkimages'] != 2)
			$ttladjust = ' pmxadj_center';
		elseif(empty($cfg['config']['title_icon']) && empty($cfg['config']['collapse']))
			$ttladjust = '';
		elseif(empty($cfg['config']['title_icon']))
			$ttladjust = ' pmxadj_'. $imgalign;
		else
			$ttladjust = ' pmxadj_'. $ttlimg;
	}
	$cfg['config']['innerpad'] = (isset($cfg['config']['innerpad']) ? $cfg['config']['innerpad'] : '4');
	$innerPad = Pmx_getInnerPad($cfg['config']['innerpad']);

	// custom css ?
	if(!empty($cfg['customclass']))
	{
		$isCustHeader = !empty($cfg['customclass']['header']);
		$isCustFrame = !empty($cfg['customclass']['frame']);
	}
	else
		$isCustHeader = $isCustFrame = false;

	$IDtype = $cfg['blocktype'] . $cfg['id'];
	$frame = false;
	$cfg['noID'] = in_array($cfg['blocktype'], array('category', 'article', 'static_category', 'static_article'));
	$showAcs = allowPmxGroup($cfg['acsgrp']);
	$cfg['active'] = !isset($cfg['active']) ? true : $cfg['active'];

	if(in_array($cfg['side'], array('bottom', 'foot')))
		echo '
						<div'. (empty($cfg['noID']) ? ' id="block.id.'. $cfg['id'] .'" ' : '') .' style="padding-top:'. (empty($count) && empty($isSB) ? '0' : $context['pmx']['settings']['panelpad']) .'px; overflow:hidden;' . (empty($cfg['active']) || empty($showAcs) ? 'display:none;' : '') .'">';
	else
		echo '
						<div'. (empty($cfg['noID']) ? ' id="block.id.'. $cfg['id'] .'" ' : '') .' style="padding-bottom:'. (empty($count) ? '0' : $context['pmx']['settings']['panelpad']) .'px; overflow:hidden;'. (in_array(strtolower($cfg['side']), array('left', 'right')) ? 'width:'. $context['pmx']['settings'][strtolower($cfg['side']).'_panel']['size'] .'px; padding-'. (strtolower($cfg['side']) == 'left' ? 'right:' : 'left:') . $context['pmx']['settings']['panelpad'] .'px;' : '') . (empty($cfg['active']) || empty($showAcs) ? 'display:none;' : '') .'">';

	// show the collapse, if set and have a header
	$head_bar = !empty($cfg['config']['visuals']['header']) && $cfg['config']['visuals']['header'] !== 'hide' ? str_replace('bg', '_bar', $cfg['config']['visuals']['header']) : '';

	if((!empty($cfg['config']['visuals']['header']) && $cfg['config']['visuals']['header'] != 'none') || (empty($cfg['config']['visuals']['header']) && !empty($cfg['config']['visuals']['body'])))
	{
		echo '
							<div class="'. (!empty($head_bar) ? $head_bar : 'title_no_bar') .'">
								<h3';

		if(!empty($cfg['config']['collapse']) && $context['pmx']['settings']['shrinkimages'] != 2)
		{
			if(!isset($options['collapse'. $IDtype]))
			{
				$cook = get_cookie('upshr'. $IDtype);
				$options['collapse'. $IDtype] = is_null($cook) ? '0' : $cook;
			}
		}
		else
			$options['collapse'. $IDtype] = '0';

		echo ' class="'. (!empty($cfg['config']['visuals']['header']) ? $cfg['config']['visuals']['header'] : $cfg['config']['visuals']['body']) .' cbodypad">';

		// show the collapse / expand icon
		if(!empty($cfg['config']['collapse']))
			echo '
									<span id="upshrink_'. $IDtype .'_Img" '. (empty($options['collapse'. $IDtype]) ? $toggleClass . $context['pmx_img_expand'] : $toggleClass . $context['pmx_img_colapse']) .' title="'. (empty($options['collapse'. $IDtype]) ? $txt['pmx_collapse'] : $txt['pmx_expand']) . $blocktitle .'"></span>';

		// show the title icon is set
		if(!empty($cfg['config']['title_icon']))
			echo '
									<img class="title_images pmx'. $ttlimg .'" src="'. $context['pmx_Iconsurl'] . $cfg['config']['title_icon'] .'" alt="*" title="'. $blocktitle . '" />';

		echo '
									<span class="pmxtitle pmx'. $txtalign . $ttladjust .'">';

		// if quickedit link the title to blockedit?
		if(!empty($context['pmx']['settings']['manager']['qedit']) && allowPmx('pmx_admin') && !empty($blocktitle))
		{
			$btyp = str_replace('static_', '', $cfg['blocktype']);
			echo '
										<a href="'. $scripturl .'?action='. (allowPmx('pmx_admin', true) ? 'portal' : 'admin') .';area=pmx_'. (in_array($btyp, array('category', 'article')) ? ($btyp == 'category' ? 'categories;sa=edit;id='. preg_replace('/_[0-9]+/', '', $cfg['catid']) : 'articles;sa=edit;id='. preg_replace('/_[0-9]+/', '', $cfg['id'])) : 'blocks;sa='. $cfg['side']) .';edit='. preg_replace('/_[0-9]+/', '', $cfg['id']) .';'. $context['session_var'] .'=' .$context['session_id'] .'">'. $blocktitle .'</a>';
		}
		// else show the title normal
		else
			echo '
									'. (empty($blocktitle) ? '&nbsp;' : $blocktitle);

		echo '
									</span>
								</h3>
							</div>';
	}

	// show content frame
	$frameclass = $cfg['config']['visuals']['frame'] .' '. $cfg['config']['visuals']['body'] .(strpos($head_bar, 'notrnd') !== false ? ' notrnd' : '');
	if(!empty($cfg['config']['visuals']['frame']) || $isCustFrame)
		echo '
							<div'. (!empty($cfg['config']['collapse']) ? ' id="upshrink_'. $IDtype .'"'. (empty($options['collapse'. $IDtype]) ? '' : ' style="display:none;"') : '') .'>
								<div class="'. $frameclass .'" style="padding:'. $innerPad[0] .'px '. $innerPad[1] .'px !important; margin-top:0px;">
									<div';
	else
		echo '
							<div'. (!empty($cfg['config']['collapse']) ? ' id="upshrink_'. $IDtype .'"'. (empty($options['collapse'. $IDtype]) ? '' : ' style="display:none;"') : '') . (!empty($cfg['config']['visuals']['body']) ? ' class="blockcontent fr_'. $head_bar .' '. $cfg['config']['visuals']['body'] .'"' : '') .'>
								<div'. (!empty($hashead) ? ' class="pmx_noframe_'. $cfg['blocktype'] .'"' : '') .' style="padding:'. $innerPad[0] .'px '. $innerPad[1] .'px !important;">
									<div';

	// have a bodytext class ?
	if(!empty($cfg['config']['visuals']['bodytext']))
		echo ' class="'. $cfg['config']['visuals']['bodytext'] .'"';

	// have overflow and (min-/max-)height?
	if(!empty($cfg['config']['overflow']))
		echo ' style="'. (isset($cfg['config']['maxheight']) && !empty($cfg['config']['maxheight']) ? (empty($cfg['config']['height']) ? 'max-height' : $cfg['config']['height']) .':'. $cfg['config']['maxheight'] .'px;' : '') .'overflow-y:'. $cfg['config']['overflow'] .';"';

	echo '>';


	// if header or frame and can collaps?
	if(!empty($cfg['config']['collapse']) && $cfg['config']['visuals']['header'] != 'none')
	{
		$tmp = '
		var '. $IDtype .' = new pmxc_Toggle({
		bToggleEnabled: true,
		bCurrentlyCollapsed: '. (empty($options['collapse'. $IDtype]) ? 'false' : 'true') .',
		aSwappableContainers: [
			\'upshrink_'. $IDtype .'\'
		],
		aSwapImages: [
			{
				sId: \'upshrink_'. $IDtype .'_Img\',';

		if($context['pmx']['settings']['shrinkimages'] == '0')
			$tmp .= '
				srcCollapsed: \''. $context['pmx_img_colapse'] .'\',
				altCollapsed: '. (JavaScriptEscape($txt['pmx_expand'] . $blocktitle)) .',';
		else
			$tmp .= '
				altCollapsed: '. (JavaScriptEscape($txt['pmx_expand'] . $blocktitle)) .',';

		if($context['pmx']['settings']['shrinkimages'] == '0')
			$tmp .= '
				srcExpanded: \''. $context['pmx_img_expand']  .'\',
				altExpanded: '. (JavaScriptEscape($txt['pmx_collapse'] . $blocktitle)) .'
			}';
		else
			$tmp .= '
				altExpanded: '. (JavaScriptEscape($txt['pmx_collapse'] . $blocktitle)) .'
			}';

		$tmp .= '
		],
		oCookieOptions: {
			bUseCookie: true,
			sCookieName: \''. 'upshr'. $IDtype .'\',
			sCookieValue: \''. $options['collapse'. $IDtype] .'\'
		}
	});';

		addInlineJavascript("\t". str_replace("\n", "\n\t", PortaMx_compressJS($tmp)), true);
		unset($tmp);
	}

	if($cfg['side'] == 'front')
		unset($context['pmx']['viewblock']['front'][$cfg['id']]); 
}

/**
* Bottom frame
**/
function Pmx_Frame_bottom()
{
	global $context;

	if(empty($context['pmx_frames'][$context['pmx_framecount']]))
	{
		$context['pmx_framecount']--;
		return;
	}

	$context['pmx_frames'][$context['pmx_framecount']] = false;
	$context['pmx_framecount']--;

	echo '
									</div>
								</div>
						</div>
					</div>';
}
?>