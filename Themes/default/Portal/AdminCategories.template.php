<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file AdminCategories.template.php
 * Template for the Categories Manager.
 *
 * @version 1.41
 */

/**
* The main Subtemplate.
*/
function template_main()
{
	global $context, $txt, $scripturl, $modSettings;
	global $cfg_titleicons, $cfg_pmxgroups;

	if(allowPmx('pmx_admin', true) && !in_array($context['pmx']['subaction'], array('edit', 'editnew')))
		echo '
		<div class="cat_bar">
			<h3 class="catbg">'. $txt['pmx_adm_categories'] .'</h3>
		</div>
		<p class="information">'. $txt['pmx_categories_desc'] .'</p>';

	if (isset($_SESSION['saved_successful']))
	{
		unset($_SESSION['saved_successful']);
		echo '
		<div class="infobox">', $txt['settings_saved'], '</div>';
	}

	echo '
		<form id="pmx_form" accept-charset="', $context['character_set'], '" name="PMxAdminCategories" action="' . $scripturl . '?action='. $context['pmx']['AdminMode'] .';area=pmx_categories;'. $context['session_var'] .'=' .$context['session_id'] .'" method="post" style="margin:0px;display:block;">
			<input type="hidden" name="sc" value="', $context['session_id'], '" />
			<input type="hidden" name="sa" value="', $context['pmx']['subaction'], '" />
			<input id="common_field" type="hidden" value="" />
			<input id="extra_cmd" type="hidden" value="" />';

	// ------------------------
	// all categories overview
	// ------------------------
	if($context['pmx']['subaction'] == 'overview')
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
		$categoryCnt = 0;
		$catIDs = array();

		// common Popup input fields
		echo '
			<input id="pWind.language.cat" type="hidden" value="'. $context['pmx']['currlang'] .'" />
			<input id="pWind.icon.url" type="hidden" value="'. $context['pmx_Iconsurl'] .'" />
			<input id="pWind.image.url" type="hidden" value="'. $context['pmx_imageurl'] .'" />
			<input id="pWind.name" type="hidden" value="" />
			<input id="pWind.id" type="hidden" value="" />
			<input id="pWind.side" type="hidden" value="" />
			<input id="allAcsGroups" type="hidden" value="'. implode(',', $allGroups) .'" />
			<input id="allAcsNames" type="hidden" value="'. implode(',', $allNames) .'" />
			<div id="addnodes" style="display:none"></div>
			<script>
				var acs_title = "'. $txt['pmx_categories_groupaccess'] .'";
			</script>';

		echo '
			<div class="cat_bar catbg_grid">
				<h4 class="catbg catbg_grid">
					<span class="pmx_clickaddnew" title="'. $txt['pmx_categories_add'] .'" onclick="FormFunc(\'add_new_category\', \'1\')"></span>
					<span class="cat_msg_title_center">'. $txt['pmx_categories_overview'] .'</span>
				</h4>
			</div>

			<div class="windowbg2 pmx_tblfrm">
				<div class="pmx_tbl_grid pmx_tbl_overflow">
					<div class="pmx_tbl_tr normaltext bm_headrow">
						<div class="pmx_tbl_tdgrid bm_order"><b>'. $txt['pmx_categories_order'] .'</b></div>
						<div class="pmx_tbl_tdgrid cat_ttl" style="cursor:pointer;" onclick="pWindToggleLang(\'cat\')" title="'. $txt['pmx_toggle_language'] .'"><b>'. $txt['pmx_title'] .' [<b id="pWind.def.lang.cat">'. $context['pmx']['currlang'] .'</b>]</b></div>
						<div class="pmx_tbl_tdgrid cat_cat"><b>'. $txt['pmx_categories_name'] .'</b></div>
						<div class="pmx_tbl_tdgrid cat_opt"><b>'. $txt['pmx_options'] .'</b></div>
						<div class="pmx_tbl_tdgrid cat_func"><b>'. $txt['pmx_functions'] .'</b></div>
					</div>';

		// call PmxCategoryOverview for each category
		foreach($context['pmx']['catorder'] as $catorder)
		{
			$cat = PortaMx_getCatByOrder($context['pmx']['categories'], $catorder);
			PmxCategoryOverview($cat);
			$catIDs[] = $cat['id'];
		}

		echo '
				</div>
				<input id="pWind.all.ids.cat" type="hidden" value="'. implode(',', $catIDs) .'" />
			</div>';

/**
* Popup windows for overview
**/
		echo '
			<div class="popup_rows">';

		// start Move popup
		echo '
				<div id="pmxSetMove" class="smalltext">
					'. pmx_popupHeader('pmxSetMove', $txt['pmx_categories_movecat']) .'
						<input id="pWind.move.error" type="hidden" value="'. $txt['pmx_categories_move_error'] .'" />
						<div class="catrm0">'. $txt['pmx_categories_move'] .'</div>
						<div class="catrm2" id="pWind.move.catname">&nbsp;</div>
						<div class="catrm3">'. $txt['pmx_categories_moveplace'] .'</div>
						<div class="catrm4">';

		$opt = 0;
		foreach($txt['pmx_categories_places'] as $artType => $artDesc)
		{
			echo '
							<input id="pWind.place.'. $opt .'" class="input_check" type="radio" name="_" value="'. $artType .'"'. ($artType == 'after' ? ' checked="checked"' : '') .' /><span class="catrm5">'. $artDesc .'</span>';
			$opt++;
		}

		// all exist categories
		echo '
						</div>
						<div class="catrm6">'. $txt['pmx_categories_tomovecat'] .'</div>
						<div class="catrm7">
							<select id="pWind.sel.destcat" class="catrm8" size="1">';

		// output cats
		foreach($context['pmx']['catorder'] as $catorder)
		{
			$cat = PortaMx_getCatByOrder($context['pmx']['categories'], $catorder);
			echo '
								<option value="'. $cat['id'] .'">['. $catorder .']'. str_repeat('&bull;', $cat['level']) .' '. $cat['name'] .'</option>';
		}

		echo '
							</select>
						</div>
						<div class="catrm9">
							<input class="button_submit pmxpopup_btn" type="button" value="'. $txt['pmx_save'] .'" onclick="pmxSaveMove()" />
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

		// Categorie name popup
		echo '
				<div id="pmxSetCatName" class="smalltext">
					'. pmx_popupHeader('pmxSetCatName', $txt['pmx_categories_setname']) .'
						<div class="catsn0">'. $txt['pmx_categories_name'] .':
							<a href="', $scripturl, '?action=helpadmin;help=pmx_edit_pagenamehelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
						</div>
						<span id="check.name.error" style="display:none;">'. sprintf($txt['namefielderror'], $txt['pmx_categories_name']) .'</span>
						<div class="catsn2">
							<input id="check.name" class="catsn3" onkeyup="check_requestname(this)" onkeypress="check_requestname(this)" type="text" value="" />
						</div>
						<div class="catsn4">
							<input class="button_submit pmxpopup_btn" type="button" value="'. $txt['pmx_update_save'] .'" onclick="pmxUpdateCatName()" />
						</div>
					</div>
				</div>';
		// end Categorie name popup

		// start articles in cat popup
		echo '
				<div id="pmxShowArt" class="smalltext" style="width:250px;z-index:9999;display:none;">
					'. pmx_popupHeader('pmxShowArt', $txt['pmx_categories_showarts']) .'
						<div id="artsorttxt" class="catart0"></div>
						<div id="artsort" class="smalltext catart2"></div><hr class="pmx_hr" />
						<div id="showarts" class="catart3"></div>
					</div>
				</div>';

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

		// start Clone popup
		echo '
				<div id="pmxSetCatClone" class="smalltext">
					'. pmx_popupHeader('pmxSetCatClone', $txt['pmx_cat_clone']) .'
						<div>'. $txt['pmx_confirm_catclone'] .'</div>
						<input id="pWind.catcloneid" type="hidden" value="" />
						<input class="button_submit pmxpopup_btn catcl0" type="button" value="'. $txt['pmx_delete_button'] .'" onclick="pmxSendCatClone()" />
						<br />
					</div>
				</div>';
		// end Clone popup

			// start delete popup
			echo '
				<div id="pmxSetCatDelete" class="smalltext">
					'. pmx_popupHeader('pmxSetCatDelete', $txt['pmx_cat_delete']) .'
						<div>'. $txt['pmx_confirm_catdelete'] .'</div>
						<input id="pWind.catdelid" type="hidden" value="" />
						<input class="button_submit pmxpopup_btn catdel0" type="button" value="'. $txt['pmx_delete_button'] .'" onclick="pmxSendCatDelete()" />
						<br />
					</div>
				</div>';
			// end delete popup

		echo '
			</div>';
	}

	// --------------------
	// singlecategorie edit
	// --------------------
	elseif($context['pmx']['subaction'] == 'edit' || $context['pmx']['subaction'] == 'editnew')
	{
		echo '
			<script>pmx_edit_mode=true;</script>
			<table class="pmx_table" style="margin-bottom:5px;table-layout:fixed;">
				<tr>
					<td style="text-align:center">
						<div class="cat_bar">
							<h3 class="catbg centertext">
								'. $txt['pmx_categories_edit'. ($context['pmx']['subaction'] == 'editnew' ? '_new' : '')] .'
							</h3>
						</div>
					</td>
				</tr>';

		// call the ShowAdmCategoryConfig() methode
		$context['pmx']['editcategory']->pmxc_ShowAdmCategoryConfig();

		echo '
			</table>';
	}

	echo '
		</form>';
}

/**
* AdmCategoryOverview
* Called for each category.
*/
function PmxCategoryOverview($category)
{
	global $context, $txt;
	global $cfg_pmxgroups;

	$category['config'] = pmx_json_decode($category['config'], true);
	if(empty($category['config']['title_align']))
		$category['config']['title_align'] = 'left';
	if(empty($category['config']['title_icon']))
		$category['config']['title_icon'] = 'none.png';

	echo '
					<div class="pmx_tbl_tr bm_row">';

	// Move row
	echo '
						<div class="pmx_tbl_tdgrid bm_order" id="RowMove-'. $category['id'] .'" style="white-space:nowrap;">
							<div class="pmx_clickrow'. (count($context['pmx']['catorder']) > 1 ? ' pmx_moveimg" title="'. $txt['pmx_move_categories'] .'" onclick="pmxSetMove(\''. $category['id'] .'\', this)"' : '"') .'>
								<div style="padding-left:20px;margin-top:-2px;width:22px;">'. $category['catorder'] .'</div>
							</div>
						</div>';

	// title row
	echo '
						<div class="pmx_tbl_tdgrid cat_ttl">
							<div class="admshortstr" onclick="pmxSetTitle(\''. $category['id'] .'\', \'cat\', this)"  title="'. $txt['pmx_click_edit_ttl'] .'" style="cursor:pointer;">
								<img id="uTitle.icon.'. $category['id'] .'" style="padding-right:4px;" src="'. $context['pmx_Iconsurl'] . $category['config']['title_icon'] .'" alt="*" title="'. substr($txt['pmx_edit_titleicon'], 0, -1) .'" />
								<img id="uTitle.align.'. $category['id'] .'" src="'. $context['pmx_imageurl'] .'text_align_'. $category['config']['title_align'] .'.gif" alt="*" title="'. $txt['pmx_edit_title_align'] . $txt['pmx_edit_title_align_types'][$category['config']['title_align']] .'" />
								<span class="admshortstr" id="sTitle.text.'. $category['id'] .'.cat">'. (isset($category['config']['title'][$context['pmx']['currlang']]) ? htmlspecialchars($category['config']['title'][$context['pmx']['currlang']], ENT_QUOTES) : '') .'</span>';

	foreach($context['pmx']['languages'] as $lang => $sel)
		echo '
								<input id="sTitle.text.'. $lang .'.'. $category['id'] .'.cat" type="hidden" value="'. (isset($category['config']['title'][$lang]) ? htmlspecialchars($category['config']['title'][$lang], ENT_QUOTES) : '') .'" />';

	echo '
								<input id="sTitle.icon.'. $category['id'] .'" type="hidden" value="'. $category['config']['title_icon'] .'" />
								<input id="sTitle.align.'. $category['id'] .'" type="hidden" value="'. $category['config']['title_align'] .'" />
							</div>
						</div>';

	// name row
	$details = PortaMx_getCatDetails($category, $context['pmx']['categories']);
	echo '
						<div class="pmx_tbl_tdgrid cat_cat" style="cursor:pointer;" onclick="pmxSetCatName(\''. $category['id'] .'\', this)">
							<input id="pWind.parent.id.'. $category['id'] .'" type="hidden" value="'. $category['parent'] .'" />
							<input id="pWind.move.cat.'. $category['id'] .'" type="hidden" value="['. $category['catorder'] .']'. ($category['level'] > 0 ? ' ' : '') . str_repeat('&bull;', $category['level']) .' '. $category['name'] .'" />
							<div id="pmxSetMove.'. $category['id'] .'" title="'. $details['parent'] . $txt['pmx_editname_categories'] .'" class="'. $details['class'] .'"><b>'. $details['level'] .'</b>
								<span id="pmxSetAcs.'. $category['id'] .'"><span id="pWind.cat.name.'. $category['id'] .'" class="cat_names">'. $category['name'] .'</span></span>
							</div>
						</div>';

	if(!empty($category['acsgrp']))
		list($grpacs, $denyacs) = Pmx_StrToArray($category['acsgrp'], ',', '=');
	else
		$grpacs = $denyacs = array();

	$groups = array();
	foreach($cfg_pmxgroups as $grp)
	{
		if(in_array($grp['id'], $grpacs))
			$groups[] = '+'. $grp['id'] .'='. intval(!in_array($grp['id'], $denyacs));
		else
			$groups[] = ':'. $grp['id'] .'=1';
	}

	$sort = array();
	$catarts = array();
	$sorts = explode(',', $category['artsort']);
	foreach($sorts as $s)
		$sort[] = htmlentities($txt['pmx_categories_artsort'][str_replace(array('=0', '=1'), array('', ''), $s)], ENT_QUOTES, $context['pmx']['encoding']) . $txt['pmx_artsort'][intval(substr($s, -1, 1))];

	if(!empty($category['articles']))
		foreach($category['articles'] as $arts)
			$catarts[] = '['. $arts['id'] .'] '. $arts['name'];

	// create acs groups for acs Popup
	if(!empty($category['acsgrp']))
		list($grpacs, $denyacs) = Pmx_StrToArray($category['acsgrp'], ',', '=');
	else
		$grpacs = $denyacs = array();

	// options row
	echo '
						<div class="pmx_tbl_tdgrid cat_opt">
							<input id="grpAcs.'. $category['id'] .'" type="hidden" value="'. implode(',', $grpacs) .'" />
							<input id="denyAcs.'. $category['id'] .'" type="hidden" value="'. implode(',', $denyacs) .'" />
							<input id="pWind.catarts.'. $category['id'] .'" type="hidden" value="'. implode('|', $catarts) .'" />
							<input id="pWind.artsorttxt.'. $category['id'] .'" type="hidden" value="'. $txt['pmx_categorie_articlesort'] .'" />
							<input id="pWind.artsort.'. $category['id'] .'" type="hidden" value="'. implode('|', $sort) .'" />
							<div id="pWind.grp.'. $category['id'] .'" class="pmx_clickrow'. (!empty($category['acsgrp']) ? ' pmx_access" title="'. $txt['pmx_categories_groupaccess'] : '') .'"></div>
							<div class="pmx_clickrow'. (!empty($category['config']['cssfile']) ? ' pmx_custcss" title="'. $txt['pmx_categories_cssfile'] : '') .'"></div>
							<div class="pmx_clickrow'. (!empty($category['config']['check_ecl']) ? ' pmx_eclsettings" title="'. $txt['pmx_have_catecl_settings'] : '') .'"></div>
							<div class="pmx_clickrow'. (!empty($category['artsum']) ? ' pmx_articles" title="'. sprintf($txt['pmx_categories_articles'], $category['artsum']) .'" onclick="pmxShowArt(\''. $category['id'] .'\', this)"' : '"') .'></div>
						</div>';

	// functions row
	echo '
						<div class="pmx_tbl_tdgrid cat_func">
							<div class="pmx_clickrow pmx_pgedit" title="'. $txt['pmx_edit_categories'].'" onclick="FormFunc(\'edit_category\', \''. $category['id'] .'\')"></div>
							<div class="pmx_clickrow pmx_grpacs" title="'. $txt['pmx_chg_categoriesaccess'] .'" onclick="pmxSetAcs(\''. $category['id'] .'\', \'cat\', this)"></div>
							<div class="pmx_clickrow pmx_pgclone" title="'. $txt['pmx_clone_categories'] .'"  onclick="pmxSetCatClone(\''. $category['id'] .'\', this)"></div>
							<div class="pmx_clickrow pmx_pgdelete" title="'. $txt['pmx_delete_categories'] .'" onclick="pmxSetCatDelete(\''. $category['id'] .'\', this)"></div>
						</div>
					</div>';
}

/**
* Popup Header bar
**/
function pmx_popupHeader($tag, $title = '', $height = 0)
{
	global $context, $txt;

	return '
				<div class="cat_bar catbg_grid" style="cursor:pointer;margin-bottom:0;margin-top:5px;" onclick="pmxRemovePopup()" title="'. $txt['pmx_clickclose'] .'">
					<h4 class="catbg catbg_grid">
						<img class="grid_click_image pmxright" src="'. $context['pmx_imageurl'] .'cross.png" alt="close" style="padding-left:10px;" />
						<span'. (empty($title) ? ' id="pWind.title.bar"' : '') .'>'. $title .'</span>
					</h4>
				</div>
				<div id="'. $tag .'.body" class="roundframe pmx_popupfrm"'. (!empty($height) ? ' style="height:'. $height .';overflow:hidden;"' : '') .'>';
}
?>