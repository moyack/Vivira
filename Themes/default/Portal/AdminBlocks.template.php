<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file AdminBlocks.template.php
 * Template for the Blocks Manager.
 *
 * @version 1.41
 */

/**
* The main Subtemplate.
*/
function template_main()
{
	global $context, $txt, $modSettings, $scripturl;

	$sections = (!isset($_REQUEST['sa']) || (isset($_REQUEST['sa']) && $_REQUEST['sa'] == 'all') ? array_keys($txt['pmx_admBlk_sides']) : Pmx_StrToArray($_REQUEST['sa']));

	if(!allowPmx('pmx_admin', true) && allowPmx('pmx_blocks', true))
	{
		if(!isset($context['pmx']['settings']['manager']['admin_pages']))
			$showBlocks = $context['pmx']['settings']['manager']['admin_pages'] = array();

		$showBlocks = array_intersect($sections, $context['pmx']['settings']['manager']['admin_pages']);
		if(count($context['pmx']['settings']['manager']['admin_pages']) > 1)
			$MenuTabs = array_merge(array('all'), $context['pmx']['settings']['manager']['admin_pages']);
		else
			$MenuTabs = $context['pmx']['settings']['manager']['admin_pages'];
	}
	else
	{
		$showBlocks = $sections;
		$MenuTabs = array_keys($txt['pmx_admBlk_panels']);
	}
	if($context['pmx']['function'] == 'edit' || $context['pmx']['function'] == 'editnew')
		$active = array($context['pmx']['editblock']->getConfigData('side'));
	else
		$active = explode(',', $context['pmx']['subaction']);

	if(allowPmx('pmx_admin, pmx_blocks', true) && !in_array($context['pmx']['function'], array('edit', 'editnew')))
	{
		echo '
		<div class="cat_bar"><h3 class="catbg">'. $txt['pmx_adm_blocks'] .'</h3></div>
		<p class="information">'. $txt['pmx_admBlk_desc'] .'</p>
		<div class="generic_menu">
			<ul class="dropmenu sf-js-enabled">';

		foreach($txt['pmx_admBlk_panels'] as $name => $desc)
		{
			if(in_array($name, $MenuTabs))
				echo '
				<li class="subsections">
					<a class="firstlevel'. ($name == $context['pmx']['subaction'] ? ' active' : '') .'" href="'. $scripturl .'?action='. $context['pmx']['AdminMode'] .';area=pmx_blocks;sa='. $name .';'. $context['session_var'] .'='. $context['session_id'] .';">
						<span class="firstlevel">'. $desc .'</span>
					</a>
				</li>';
		}

		echo '
			</ul>
		</div>

		<div><a class="menu_icon mobile_generic_menu_panels" style="margin-top:-5px;"></a></div>
		<div id="mobile_generic_menu_panels" class="popup_container">
			<div class="popup_window description">
				<div class="popup_heading">', $txt['pmx_allpanels'] ,'<a href="javascript:void(0);" class="generic_icons hide_popup"></a></div>
				<div class="generic_menu">
					<ul class="dropmenu sf-js-enabled">';

		foreach($txt['pmx_admBlk_panels'] as $name => $desc)
		{
			if(in_array($name, $MenuTabs))
				echo '
						<li class="subsections">
							<a class="firstlevel'. ($name == $context['pmx']['subaction'] ? ' active' : '') .'" href="'. $scripturl .'?action='. $context['pmx']['AdminMode'] .';area=pmx_blocks;sa='. $name .';'. $context['session_var'] .'='. $context['session_id'] .';">
								<span class="firstlevel">'. $desc .'</span>
							</a>
						</li>';
		}

		echo '
					</ul>
				</div>
			</div>
		</div>
		<script>
			$(".mobile_generic_menu_panels" ).click(function(){$("#mobile_generic_menu_panels" ).show();});
			$(".hide_popup" ).click(function(){$( "#mobile_generic_menu_panels" ).hide();});
		</script>';
	}

	if (isset($_SESSION['saved_successful']))
	{
		unset($_SESSION['saved_successful']);
		echo '
		<div class="infobox">', $txt['settings_saved'], '</div>';
	}

	echo '
		<form id="pmx_form" accept-charset="'. $context['character_set'] .'" name="PMxAdminBlocks" action="' . $scripturl . '?action='. $context['pmx']['AdminMode'] .';area=pmx_blocks;sa='. $context['pmx']['subaction'] .';'. $context['session_var'] .'=' .$context['session_id'] .'" method="post" style="margin:0px;display:block;" onsubmit="submitonce(this);">
			<input type="hidden" name="sc" value="'. $context['session_id'] .'" />
			<input type="hidden" name="function" value="'. $context['pmx']['function'] .'" />
			<input type="hidden" name="sa" value="'. $context['pmx']['subaction'] .'" />
			<input id="common_field" type="hidden" value="" />
			<input id="extra_cmd" type="hidden" value="" />';

	// ---------------------
	// all Blocks overview
	// ---------------------
	if($context['pmx']['function'] == 'overview')
	{
		$cfg_titleicons = PortaMx_getAllTitleIcons();
		$cfg_pmxgroups = PortaMx_getUserGroups();
		$allNames = array();
		$allGroups = array();

		foreach($cfg_pmxgroups as $key => $grp)
		{
			$allGroups[] = $grp['id'];
			$allNames[] = str_replace(' ', '_', $grp['name']);
		}

		// common Popup input fields
		echo '
			<input id="pWind.icon.url" type="hidden" value="'. $context['pmx_Iconsurl'] .'" />
			<input id="pWind.image.url" type="hidden" value="'. $context['pmx_imageurl'] .'" />
			<input id="pWind.name" type="hidden" value="" />
			<input id="pWind.side" type="hidden" value="" />
			<input id="pWind.id" type="hidden" value="" />
			<input id="allAcsGroups" type="hidden" value="'. implode(',', $allGroups) .'" />
			<input id="allAcsNames" type="hidden" value="'. implode(',', $allNames) .'" />
			<script>
				var BlockActive = "'. $txt['pmx_status_activ'] .' - '. $txt['pmx_status_change'] .'";
				var BlockInactive = "'. $txt['pmx_status_inactiv'] .' - '. $txt['pmx_status_change'] .'";
				var acs_title = "'. $txt['pmx_have_groupaccess'] .'";
			</script>';

		$sidescnt = count($showBlocks);
		foreach($showBlocks as $side)
		{
			$sidescnt--;
			if($side == 'all')
				continue;

			$blockCnt = (!empty($context['pmx']['blocks'][$side]) ? count($context['pmx']['blocks'][$side]) : 0);
			$paneldesc = htmlentities($txt['pmx_admBlk_sides'][$side], ENT_QUOTES, $context['pmx']['encoding']);
			echo '
			<div id="addnodes.'. $side .'"></div>
			<div style="margin-bottom:'. (!empty($sidescnt) ? $context['pmx']['settings']['panelpad'] .'px;' : '0px;') .'">
				<div id="paneltop-'. $side .'" class="cat_bar catbg_grid">
					<h4 class="catbg catbg_grid">
						<span'. (allowPmx('pmx_admin') ? ' class="pmx_clickaddnew" title="'. sprintf($txt['pmx_add_sideblock'], $txt['pmx_admBlk_sides'][$side]) .'" onclick="SetpmxBlockType(\''. $side .'\', \''. $paneldesc .'\', this)"' : '') .'></span>
						<span class="cat_msg_title_center">
							<a href="'. $scripturl .'?action='. $context['pmx']['AdminMode'] .';area=pmx_blocks;sa='. $side .';'. $context['session_var'] .'=' .$context['session_id'] .'">'. $txt['pmx_admBlk_sides'][$side] .'</a>
						</span>
					</h4>
				</div>

				<div class="windowbg2 pmx_tblfrm">
					<div class="pmx_tbl_grid pmx_tbl_overflow">
						<div class="pmx_tbl_tr normaltext bm_headrow">
							<div class="pmx_tbl_tdgrid bm_order"><b>'. $txt['pmx_admBlk_order'] .'</b></div>';

			if(!empty($blockCnt))
				echo '
							<div class="pmx_tbl_tdgrid bm_ttl bm_ttl_act" onclick="pWindToggleLang(\'.'. $side .'\')" title="'. $txt['pmx_toggle_language'] .'"><b>'. $txt['pmx_title'] .' [<b id="pWind.def.lang.'. $side .'">'. $context['pmx']['currlang'] .'</b>]</b></div>';
			else
				echo '
							<div class="pmx_tbl_tdgrid bm_ttl" title="'. $txt['pmx_toggle_language'] .'"><b>'. $txt['pmx_title'] .' [<b id="pWind.def.lang.'. $side .'">'. $context['pmx']['currlang'] .'</b>]</b></div>';

			echo '
							<div class="pmx_tbl_tdgrid bm_typ"><b class="bm_typ">'. $txt['pmx_admBlk_type'] .'</b></div>
							<div class="pmx_tbl_tdgrid bm_opts" id="RowMove-'. $side .'"><b>'. $txt['pmx_options'] .'</b></div>
							<div class="pmx_tbl_tdgrid bm_stats"><div class="bm_stats"><b>'. $txt['pmx_status'] .'</b></div></div>
							<div class="pmx_tbl_tdgrid bm_func"><div class="bm_func"><b>'. $txt['pmx_functions'] .'</b></div></div>
						</div>';

			// call PmxBlocksOverview for each side / block
			$blockIDs = array();
			if(!empty($blockCnt))
			{
				foreach($context['pmx']['blocks'][$side] as $block)
				{
					if(PmxBlocksOverview($block, $side, $cfg_titleicons, $cfg_pmxgroups) == true)
					{
						$blockIDs[] = $block['id'];
						$blocktypes[$side][$block['id']] = array(
							'type' => $block['blocktype'],
							'pos' => $block['pos'],
						);
					}
				}
			}

			echo '
					</div>';

			if(count($blockIDs) > 0)
			{
				// common Popup input fields
				echo '
					<input id="pWind.language.'. $side .'" type="hidden" value="'. $context['pmx']['currlang'] .'" />
					<input id="pWind.all.ids.'. $side .'" type="hidden" value="'. implode(',', $blockIDs) .'" />';

				$blockCnt = (!empty($context['pmx']['blocks'][$side]) ? count($context['pmx']['blocks'][$side]) : 0);
				$paneldesc = htmlentities($txt['pmx_admBlk_sides'][$side], ENT_QUOTES, $context['pmx']['encoding']);

				if(count($blockIDs) == 1 && allowPmx('pmx_admin'))
					echo '
					<script>
						document.getElementById("Img.RowMove-'. $blockIDs[0] .'").className = "pmx_clickrow";
						document.getElementById("Img.RowMove-'. $blockIDs[0] .'").title = "";
					</script>';
			}

			echo '
				</div>
			</div>';
		}

/**
* Popup windows for overview
**/

	// start row move popup
	echo '
			<div class="popup_rows">
				<div id="pmxRowMove" class="smalltext">
					'. pmx_popupHeader('pmxRowMove', $txt['pmx_rowmove_title']) .'
						<input id="pWind.move.error" type="hidden" value="'. $txt['pmx_block_move_error'] .'" />
						<div class="bmrowmv0">
							'. $txt['pmx_block_rowmove'] .'<br />
							<div class="bmrowmv2">'. $txt['pmx_blockmove_place'] .'</div><br />
							<div class="bmrowmv3">'. $txt['pmx_blockmove_to'] .'</div>
						</div>
						<div class="bmrowmv4">
							<div id="pWind.move.blocktyp" class="bmrowmv5"></div>
							<div class="bmrowmv6">
								<input id="pWind.place.0" class="input_check bmrowmv7" type="radio" value="before" name="_" checked="checked" />
								<span style="padding-right:10px;">'. $txt['rowmove_before'] .'</span>
								<input id="pWind.place.1" class="input_check bmrowmv7" type="radio" value="after"  name="_" />
								<span>'. $txt['rowmove_after'] .'</span><br />
							</div>
							<div class="bmrowmv8">';

	// output blocktypes
	foreach($txt['pmx_admBlk_sides'] as $side => $d)
	{
		if(isset($blocktypes[$side]))
		{
			echo '
								<select id="pWind.select.'. $side .'" class="bmrowmv9">';

			// output blocktypes
			foreach($blocktypes[$side] as $id => $data)
				echo '
									<option value="'. $id .'">['. $data['pos'] .'] '. $context['pmx']['RegBlocks'][$data['type']]['description'] .'</option>';

			echo '
								</select>';
			}
		}
		echo '
							</div>
						</div>
						<div class="bmrowmv10">
							<input class="button_submit pmxpopup_btn" type="button" value="'. $txt['pmx_save'] .'" onclick="pmxSendRowMove()" />
						</div>
					</div>
				</div>';
		// end Move popup

		// start title edit popup
		echo '
				<div id="pmxSetTitle" class="smalltext">
					'. pmx_popupHeader('pmxSetTitle', $txt['pmx_edit_titles'], '105px') .'
						<div class="bmsetttl0">'. $txt['pmx_edit_title'] .'</div>
						<input id="pWind.text" class="bmsetttl2" type="text" value="" />
						<input id="pWindID" type="hidden" value="" />
						<input id="pWind_event_status" type="hidden" value="" />
						<div class="bmsetttl3">
							<img class="bmsetttl4" src="'. $context['pmx_imageurl'] .'arrow_down.gif" alt="*" title="" />
						</div>
						<div class="bmsetttl5">'. $txt['pmx_edit_title_lang'] .'</div>
						<select id="pWind.lang.sel" class="bmsetttl6" size="1" onchange="pmxChgTitles_Lang(this)">';

		// languages
		foreach($context['pmx']['languages'] as $lang => $sel)
			echo '
							<option value="'. $lang .'">'. $lang .'</option>';

		echo '
						</select>
						<div class="bmsetttl7">';

		// Title align
		foreach($txt['pmx_edit_title_align_types'] as $key => $val)
			echo '
							<img id="pWind.align.'. $key .'" src="'. $context['pmx_imageurl'] .'text_align_'. $key .'.gif" alt="*" title="'. $txt['pmx_edit_title_helpalign']. $val .'" class="bmsetttl8" onclick="pmxChgTitles_Align(\''. $key .'\')" />';

		echo '
						</div>
						<br class="bmsetttl9" />
						<input class="bmsetttl10 button_submit pmxpopup_btn" type="button" value="'.$txt['pmx_update_save'].'"  onclick="pmxUpdateTitles()" />
						<div style="float:left;width:75px; padding-top:8px;">'. $txt['pmx_edit_titleicon'] .'</div>';

		// Title icons
		echo '
						<div class="ttliconDiv" onclick="setNewIcon(document.getElementById(\'pWind.icon_sel\'), event)" style="margin-top:-2px;">
							<input id="post_image" type="hidden" name="config[title_icon]" value="" />
							<input id="iconDD" value="'. (isset($block['config']['title_icon']) ? ucfirst(str_replace('.png', '', $block['config']['title_icon'])) : 'NoneF') .'" readonly />
							<img id="pWind.icon" class="pwindicon" src="'. $context['pmx_shortIconsurl'] .'none.png" alt="*" />
							<img class="ddImage" src="'. $context['pmx_imageurl'] .'state_expand.png" alt="*" title="" />
						</div>
						<ul class="ttlicondd bm_ttldd'. ($modSettings['isMobile'] ? '_mb' : '') .'" id="pWind.icon_sel" onclick="updIcon(this)">';

		foreach($cfg_titleicons as $file => $name)
			echo '
							<li id="'. $file .'" class="ttlicon'. (isset($block['config']['title_icon']) && $block['config']['title_icon'] == $file ? ' active' : '') .'">
								<img src="'. $context['pmx_shortIconsurl'] . $file .'" alt="*" /><span>'. $name .'</span>
							</li>';

		echo '
						</ul>
						<script>$("li").hover(function(){pmxToggleClass(this, "active")});</script>
					</div>
				</div>';
		// end title edit popup

		// start Access popup
		echo '
				<div id="pmxSetAcs" class="smalltext">
					'. pmx_popupHeader('pmxSetAcs', $txt['pmx_article_groups']) .'
						<div class="acspu0">
							<select id="pWindAcsGroup" class="acspu2" multiple="multiple" size="5" onchange="changed(\'pWindAcsGroup\');">';

		foreach($cfg_pmxgroups as $grp)
			echo '
								<option value="'. $grp['id'] .'=1">'. $grp['name'] .'</option>';

		echo '
							</select>
						</div>
						<div class="acspu3">
							<div class="acspu4">
								<div class="acspu5"><input id="pWindAcsModeupd" onclick="pmxSetAcsMode(\'upd\')" class="input_check" type="radio" name="_" value="" /><div class="acspu6">'. $txt['pmx_acs_repl'] .'</div></div>
								<div class="acspu5"><input id="pWindAcsModeadd" onclick="pmxSetAcsMode(\'add\')" class="input_check" type="radio" name="_" value="" /><div class="acspu6">'. $txt['pmx_acs_add'] .'</div></div>
								<div class="acspu5"><input id="pWindAcsModedel" onclick="pmxSetAcsMode(\'del\')" class="input_check" type="radio" name="_" value="" /><div class="acspu6">'. $txt['pmx_acs_rem'] .'</div></div>
							</div>
							<div class="acspu7">
								<input id="acs_all_button" class="button_submit pmxpopup_btn" type="button" value="'. $txt['pmx_update_all'] .'" onclick="pmxUpdateAcs(\'all\')" />
								<input class="button_submit pmxpopup_btn acspu8" type="button" value="'. $txt['pmx_update_save'] .'" onclick="pmxUpdateAcs()" />
							</div>
						</div>
						<script>
							var pWindAcsGroup = new MultiSelect("pWindAcsGroup");
							var BlockActive = "'. $txt['pmx_status_activ'] .' - '. $txt['pmx_status_change'] .'";
							var BlockInactive = "'. $txt['pmx_status_inactiv'] .' - '. $txt['pmx_status_change'] .'";
						</script>
					</div>
				</div>';
		// end Access popup

		// start Clone / Move popup
		echo '
				<div id="pmxSetCloneMove" class="smalltext">
					'. pmx_popupHeader('pmxSetCloneMove', '<span id="title.clone.move"></span>') .'
						<input id="pWind.txt.clone" type="hidden" value="'. $txt['pmx_text_clone'] .'" />
						<input id="pWind.txt.move" type="hidden" value="'. $txt['pmx_text_move'] .'" />
						<input id="pWind.worktype" type="hidden" value="" />
						<input id="pWind.addoption" type="hidden" value="'. $txt['pmx_clone_move_toarticles'] .'" />
						<div id="pWind.clone.move.blocktype" class="bmclmv0"><b></b></div>
						<div class="bmclmv2"></div>
						<div>'. $txt['pmx_clone_move_side'] .'</div>
						<select id="pWind.sel.sides" class="bmclmv3" size="1">';

		$sel = true;
		foreach($txt['pmx_admBlk_sides'] as $side => $desc)
		{
			echo '
							<option value="'. $side .'"'. (!empty($sel) ? ' selected="selected"' : '') .'>'. $desc .'</option>';
			$sel = false;
		}

		echo '
						</select>
						<input class="bmclmv4 button_submit pmxpopup_btn" type="button" value="'. $txt['pmx_save'] .'" onclick="pmxSendCloneMove()" />
					</div>
				</div>';
		// end Clone / Move popup

		// start delete popup
		echo '
				<div id="pmxSetDelete" class="smalltext">
					'. pmx_popupHeader('pmxSetDelete', $txt['pmx_delete_block']) .'
						<div><span id="pWind.delete.blocktype"></span></div>
						<div>'. $txt['pmx_confirm_blockdelete'] .'</div>
						<input id="pWind.blockid" type="hidden" value="" />
						<div class="bmsetdl0"><input class="bmsetdl2 button_submit pmxpopup_btn" type="button" value="'. $txt['pmx_delete_button'] .'" onclick="pmxSendDelete()" /></div>
					</div>
				</div>';
		// end delete popup

		// start blocktype selection popup
		$RegBlocks = eval($context['pmx']['registerblocks']);
		ksort($RegBlocks, SORT_STRING);
		function cmpBDesc($a, $b){return strcasecmp(str_replace(' ', '', $a["description"]), str_replace(' ', '', $b["description"]));}
		uasort($RegBlocks, 'cmpBDesc');

		echo '
				<div id="pmxBlockType" class="smalltext">
					'. pmx_popupHeader('pmxBlockType', '') .'
						<div class="bmsel0">'. $txt['pmx_blocks_blocktype'] .'</div>
						<input id="pWind.blocktype.title" type="hidden" value="'. $txt['pmx_add_new_blocktype'] .'" />
						<select id="pmx.block.type" size="1" class="bmsel2">';

		foreach($RegBlocks as $blocktype => $blockDesc)
			echo '
							<option value="'. $blocktype .'">'. $blockDesc['description'] .'</option>';

		echo '
						</select>
						<div class="bmsel3">
							<input id="BType" class="button_submit pmxpopup_btn" type="button" value="'. $txt['pmx_create'] .'" onclick="pmxSendBlockType()" />
						</div>
					</div>
				</div>';
		// end blocktype popup

echo '
			</div>
		</form>';
	}

	// --------------------
	// single block edit
	// --------------------
	elseif($context['pmx']['function'] == 'edit' || $context['pmx']['function'] == 'editnew')
	{
		echo '
		<script>pmx_edit_mode=true;</script>
			<div class="cat_bar">
				<h3 class="catbg centertext">
				'. $txt['pmx_editblock'. ($context['pmx']['function'] == 'editnew' ? '_new' : '')] .' '. $context['pmx']['RegBlocks'][$context['pmx']['editblock']->cfg['blocktype']]['description'] .'
				</h3>
			</div>';

		// call the ShowAdmBlockConfig() methode
		$context['pmx']['editblock']->pmxc_ShowAdmBlockConfig();

		echo '
		</form>';
	}
}

/**
* Called for each block.
*/
function PmxBlocksOverview($block, $side, $cfg_titleicons, $cfg_pmxgroups)
{
	global $context, $txt;

	if(!allowPmx('pmx_admin', true) && allowPmx('pmx_blocks', true))
	{
		if(empty($block['config']['can_moderate']))
			return false;
	}

	if(empty($block['config']['title_align']))
		$block['config']['title_align'] = 'left';
	if(empty($block['config']['title_icon']))
		$block['config']['title_icon'] = 'none.png';

	// pos row
	echo '
							<div class="pmx_tbl_tr bm_row">
								<div class="pmx_tbl_tdgrid bm_order" id="RowMove-'. $block['id'] .'">
									<div'. (allowPmx('pmx_admin') ? ' onclick="pmxRowMove(\''. $block['id'] .'\', \''. $side .'\', this)"' : '') .'>
										<div id="Img.RowMove-'. $block['id'] .'" style="white-space:nowrap;" class="pmx_clickrow'. (allowPmx('pmx_admin') ? ' pmx_moveimg" title="'. $txt['row_move_updown'] : '') .'">
											<div id="pWind.pos.'. $side .'.'. $block['id'] .'" style="padding-left:20px;margin-top:-2px;width:22px;">'. $block['pos'] .'</div>
										</div>
									</div>
								</div>';

	// title row
	echo '
								<div class="pmx_tbl_tdgrid bm_ttl">
									<div class="admshortstr" onclick="pmxSetTitle(\''. $block['id'] .'\', \''. $side .'\', this)"  title="'. $txt['pmx_click_edit_ttl'] .'" style="cursor:pointer;white-space:nowrap;">
										<img id="uTitle.icon.'. $block['id'] .'" style="text-align:left;padding-right:4px;" src="'. $context['pmx_Iconsurl'] . $block['config']['title_icon'] .'" alt="*" title="'. substr($txt['pmx_edit_titleicon'], 0, -1) .'" />
										<img id="uTitle.align.'. $block['id'] .'" style="text-align:right;" src="'. $context['pmx_imageurl'] .'text_align_'. $block['config']['title_align'] .'.gif" alt="*" title="'. $txt['pmx_edit_title_align'] . $txt['pmx_edit_title_align_types'][$block['config']['title_align']] .'" />
										<span class="admshortstr" id="sTitle.text.'. $block['id'] .'.'. $side .'">'. (isset($block['config']['title'][$context['pmx']['currlang']]) ? htmlspecialchars($block['config']['title'][$context['pmx']['currlang']], ENT_QUOTES) : '') .'</span>';

	foreach($context['pmx']['languages'] as $lang => $sel)
		echo '
										<input id="sTitle.text.'. $lang .'.'. $block['id'] .'.'. $side .'" type="hidden" value="'. (isset($block['config']['title'][$lang]) ? htmlspecialchars($block['config']['title'][$lang], ENT_QUOTES) : '') .'" />';

	echo '
										<input id="sTitle.icon.'. $block['id'] .'" type="hidden" value="'. $block['config']['title_icon'] .'" />
										<input id="sTitle.align.'. $block['id'] .'" type="hidden" value="'. $block['config']['title_align'] .'" />
									</div>
								</div>';

	// type row
	echo '
								<div class="pmx_tbl_tdgrid bm_typ">
									<div class="admshortstr short_typ" id="pWind.desc.'. $side .'.'. $block['id'] .'" title="'. $context['pmx']['RegBlocks'][$block['blocktype']]['blocktype'] .' '. $context['pmx']['RegBlocks'][$block['blocktype']]['description'] .' (ID:'. $block['id'] .')'. ($block['side'] == 'pages' ? ', Name: '. $block['config']['pagename'] : '') .'"><img src="'. $context['pmx_imageurl'] .'type_'. $context['pmx']['RegBlocks'][$block['blocktype']]['icon'] .'.gif" alt="*" />&nbsp;<span style="cursor:default;">'. $context['pmx']['RegBlocks'][$block['blocktype']]['description'] .'</span></div>
								</div>';

	// create acs groups for acs Popup
	if(!empty($block['acsgrp']))
		list($grpacs, $denyacs) = Pmx_StrToArray($block['acsgrp'], ',', '=');
	else
		$grpacs = $denyacs = array();

	// check extent options
	$extOpts = false;
	if(!empty($block['config']['ext_opts']))
	{
		foreach($block['config']['ext_opts'] as $k => $v)
			$extOpts = !empty($v) ? true : $extOpts;
	}

	// options row
	echo '
								<div class="pmx_tbl_tdgrid bm_opts" id="RowAccess.'. $block['id'] .'">
									<input id="grpAcs.'. $block['id'] .'" type="hidden" value="'. implode(',', $grpacs) .'" />
									<input id="denyAcs.'. $block['id'] .'" type="hidden" value="'. implode(',', $denyacs) .'" />
									<div style="display:flex;">
										<div id="pWind.grp.'. $block['id'] .'" class="pmx_clickrow'. (!empty($block['acsgrp']) ? ' pmx_access" title="'. $txt['pmx_have_groupaccess'] : '') .'"></div>
										<div class="pmx_clickrow'. (!empty($block['config']['can_moderate']) ? ' pmx_moderate" title="'. $txt['pmx_have_modaccess'] : '') .'"></div>
										<div class="pmx_clickrow'. (!empty($extOpts) ? ' pmx_dynopts" title="'. $txt['pmx_have_dynamics'] : '') .'"></div>
										<div class="pmx_clickrow'. (!empty($block['config']['cssfile']) ? ' pmx_custcss" title="'. $txt['pmx_have_cssfile'] : '') .'"></div>
										<div class="pmx_clickrow'. (!empty($block['config']['check_ecl']) ? ' pmx_eclsettings" title="'. $txt['pmx_have_ecl_settings'] : '') .'"></div>
										<div class="pmx_clickrow'. (!empty($block['cache']) ? ' pmx_cache" title="'. $txt['pmx_have_caching'] . $block['cache'] . $txt['pmx_edit_cachetimesec'] : '') .'"></div>
									</div>
								</div>';

	// status row
	echo '
								<div class="pmx_tbl_tdgrid bm_stats" style="text-align:center">
									<div style="margin-left:14px;" id="status.'. $block['id'] .'" class="pmx_clickrow'. ($block['active'] ? ' pmx_active" title="'. $txt['pmx_status_activ'] : ' pmx_notactive" title="'. $txt['pmx_status_inactiv']) .' - '. $txt['pmx_status_change'] .'" onclick="pToggleStatus('. $block['id'].', \''. $block['side'] .'\')"></div>
								</div>';

	// functions row
	echo '
								<div class="pmx_tbl_tdgrid bm_func">
									<div class="pmx_clickrow pmx_pgedit" title="'. $txt['pmx_edit_sideblock'].'" onclick="FormFunc(\'edit_block\', \''. $block['id'] .'\')"></div>
									<div class="pmx_clickrow pmx_grpacs" title="'. $txt['pmx_chg_blockaccess'] .'" onclick="pmxSetAcs(\''. $block['id'] .'\', \''. $block['side'] .'\', this)"></div>
									<div class="pmx_clickrow'. (allowPmx('pmx_admin') ? ' pmx_pgclone" title="'. $txt['pmx_clone_sideblock'] .'" onclick="pmxSetCloneMove(\''. $block['id'] .'\', \''. $block['side'] .'\', \'clone\', \''. $block['blocktype'] .'\', this)"' : '"') .'></div>
									<div class="pmx_clickrow'. (allowPmx('pmx_admin') ? ' pmx_pgmove" title="'. $txt['pmx_move_sideblock'] .'" onclick="pmxSetCloneMove(\''. $block['id'] .'\', \''. $block['side'] .'\', \'move\', \''. $block['blocktype'] .'\', this)"' : '"') .'></div>
									<div class="pmx_clickrow'. (allowPmx('pmx_admin') ? ' pmx_pgdelete" title="'. $txt['pmx_delete_sideblock'] .'" onclick="pmxSetDelete(\''. $block['id'] .'\', \''. $block['side'] .'\', this)"' : '"') .'></div>
								</div>
							</div>';
	return true;
}

/**
* Popup Header bar
**/
function pmx_popupHeader($tag, $title = '', $height = 0)
{
	global $context, $txt;

	return '
				<div class="cat_bar catbg_grid pmx_popuphead" onclick="pmxRemovePopup()" title="'. $txt['pmx_clickclose'] .'">
					<h4 class="catbg catbg_grid">
						<img class="grid_click_image pmxright pmx_popuphead_img" src="'. $context['pmx_imageurl'] .'cross.png" alt="close" />
						<span'. (empty($title) ? ' id="pWind.title.bar"' : '') .'>'. $title .'</span>
					</h4>
				</div>
				<div id="'. $tag .'.body" class="roundframe pmx_popupfrm"'. (!empty($height) ? ' style="height:'. $height .';overflow:hidden;"' : '') .'>';
}
?>