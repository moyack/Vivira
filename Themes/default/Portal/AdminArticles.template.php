<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file AdminArticles.template.php
 * Template for the Articles Manager.
 *
 * @version 1.41
 */

/**
* The main Subtemplate.
*/
function template_main()
{
	global $context, $txt, $modSettings, $scripturl;

	$curarea = isset($_GET['area']) ? $_GET['area'] : 'pmx_center';

	if(allowPmx('pmx_admin, pmx_create, pmx_articles', true) && !in_array($context['pmx']['subaction'], array('edit', 'editnew')))
		echo '
			<div class="cat_bar"><h3 class="catbg">'. $txt['pmx_adm_articles'] .'</h3></div>
			<p class="information">'. $txt['pmx_articles_desc'] .'</p>';

	if(!isset($context['pmx']['articlestart']))
		$context['pmx']['articlestart'] = 0;

	if($context['pmx']['subaction'] == 'overview')
	{
		// create the pageindex
		$cururl = (!empty($_GET) ? pmx_http_build_query($_GET, '', ';') .';' : '');
		$pageindex = constructPageIndex($scripturl . '?'. $cururl .'pg=%1$d', $context['pmx']['articlestart'], $context['pmx']['totalarticles'], $context['pmx']['settings']['manager']['artpage'], true);
		$pageindex = str_replace(';start=%1$d', '', $pageindex);
		$pageindex = str_replace('#ptop', '', $pageindex);

		echo '
		<span id="arttop"></span>';
	}

	if (isset($_SESSION['saved_successful']))
	{
		unset($_SESSION['saved_successful']);
		echo '
		<div class="infobox">', $txt['settings_saved'], '</div>';
	}

	echo '
		<form id="pmx_form" accept-charset="'. $context['character_set'] .'" name="PMxAdminArticles" action="' . $scripturl . '?action='. $context['pmx']['AdminMode'] .';area=pmx_articles;'. $context['session_var'] .'=' .$context['session_id'] .'" method="post" style="margin:0px;display:block;" onsubmit="submitonce(this);">
			<input type="hidden" name="sc" value="'. $context['session_id'] .'" />
			<input type="hidden" name="sa" value="'. $context['pmx']['subaction'] .'" />
			<input type="hidden" name="articlestart" value="'. $context['pmx']['articlestart'] .'" />
			<input type="hidden" name="fromblock" value="'. (!empty($context['pmx']['fromblock']) ? $context['pmx']['fromblock'] : '') .'" />
			<input type="hidden" id="pWind.all.cats" value="'. pmx_getAllCatID() .'" />
			<input id="common_field" type="hidden" value="" />
			<input id="extra_cmd" type="hidden" value="" />
			<script>
				var Art = [];
				Art["active"] = "'. $txt['pmx_status_activ'] .' - '. $txt['pmx_status_change'] .'";
				Art["notactive"] = "'. $txt['pmx_status_inactiv'] .' - '. $txt['pmx_status_change'] .'";
				Art["approved"] = "'. $txt['pmx_article_approved'] .' - '. $txt['pmx_status_change'] .'";
				Art["notapproved"] = "'. $txt['pmx_article_not_approved'] .' - '. $txt['pmx_status_change'] .'";
			</script>';

	// ---------------------
	// all articles overview
	// ---------------------
	if($context['pmx']['subaction'] == 'overview')
	{
		$cfg_titleicons = PortaMx_getAllTitleIcons();
		$cfg_pmxgroups = PortaMx_getUserGroups();
		$categories = PortaMx_getCategories();
		$allNames = array();
		$allGroups = array();
		foreach($cfg_pmxgroups as $key => $grp)
		{
			$allGroups[] = $grp['id'];
			$allNames[] = str_replace(' ', '_', $grp['name']);
		}

		// common Popup input fields
		echo '
			<input id="pWind.language." type="hidden" value="'. $context['pmx']['currlang'] .'" />
			<input id="pWind.icon.url" type="hidden" value="'. $context['pmx_Iconsurl'] .'" />
			<input id="pWind.image.url" type="hidden" value="'. $context['pmx_imageurl'] .'" />
			<input id="pWind.name" type="hidden" value="" />
			<input id="pWind.id" type="hidden" value="" />
			<input id="pWind.catsel" type="hidden" value="" />
			<input id="pWind.side" type="hidden" value="" />
			<input id="set.filter.category" type="hidden" name="filter[category]" value="'. $_SESSION['PortaMx']['filter']['category'] .'" />
			<input id="set.filter.approved" type="hidden" name="filter[approved]" value="'. $_SESSION['PortaMx']['filter']['approved'] .'" />
			<input id="set.filter.active" type="hidden" name="filter[active]" value="'. $_SESSION['PortaMx']['filter']['active'] .'" />
			<input id="set.filter.myown" type="hidden" name="filter[myown]" value="'. $_SESSION['PortaMx']['filter']['myown'] .'" />
			<input id="set.filter.member" type="hidden" name="filter[member]" value="'. $_SESSION['PortaMx']['filter']['member'] .'" />
			<input id="allAcsGroups" type="hidden" value="'. implode(',', $allGroups) .'" />
			<input id="allAcsNames" type="hidden" value="'. implode(',', $allNames) .'" />
			<div id="addnodes" style="display:none"></div>
			<script>
				var acs_title = "'. $txt['pmx_article_groupaccess'] .'";
			</script>';

		$filterActive = ($_SESSION['PortaMx']['filter']['category'] != '' || $_SESSION['PortaMx']['filter']['approved'] != 0 || $_SESSION['PortaMx']['filter']['active'] != 0 || $_SESSION['PortaMx']['filter']['myown'] != 0 || $_SESSION['PortaMx']['filter']['member'] != '');

		// top pageindex
		echo '
			<div class="fixpagesbar">
				<a href="#pbot" class="topbottom floatleft">', $txt['go_down'], '</a>
				<div class="pagelinks floatleft">', $pageindex, '</div>
			</div>
			<div class="clear_left"></div>';

		echo '
			<div class="cat_bar catbg_grid">
				<h4 class="catbg catbg_grid">
					<span'. (allowPmx('pmx_create, pmx_articles, pmx_admin') ? ' class="pmx_clickaddnew" title="'. $txt['pmx_articles_add'] .'" onclick="SetpmxArticleType(this)"' : '') .'></span>
					<span class="cat_msg_title_center">'. $txt['pmx_articles_overview'] .'</span>
				</h4>
			</div>

			<div class="windowbg2 pmx_tblfrm">
				<div class="pmx_tbl_grid pmx_tbl_overflow">
					<div class="pmx_tbl_tr normaltext bm_headrow">
						<div class="pmx_tbl_tdgrid bm_order"><b>'. $txt['pmx_article_order'] .'</b></div>
						<div class="pmx_tbl_tdgrid art_ttl" style="cursor:pointer;" onclick="pWindToggleLang(\'\')" title="'. $txt['pmx_toggle_language'] .'"><b>'. $txt['pmx_title'] .' [<b id="pWind.def.lang.">'. $context['pmx']['currlang'] .'</b>]</b></div>

						<div class="pmx_tbl_tdgrid art_typ"><b>'. $txt['pmx_articles_type'] .'</b></div>
						<div class="pmx_tbl_tdgrid art_cat"><b>'. $txt['pmx_articles_catname'] .'</b>
							<span class="pmx_'. (empty($filterActive) ? 'nofilter' : 'filter') .'" title="'. $txt['pmx_article_filter'] .'" onclick="pmxSetFilter(this)"></span>
						</div>
						<div class="pmx_tbl_tdgrid art_opt"><div class="art_opt"><b>'. $txt['pmx_options'] .'</b></div></div>
						<div class="pmx_tbl_tdgrid art_stat"><div class="art_stat"><b>'. $txt['pmx_status'] .'</b></div></div>
						<div class="pmx_tbl_tdgrid art_func"><div class="art_func"><b>'. $txt['pmx_functions'] .'</b></div></div>
					</div>';

		// call PmxArticleOverview for each article
		$articleCnt = count($context['pmx']['articles']);
		$artIDs = array();
		$pgCount = 0;

		// filter out articles in categories they have a "Gobal use"
		if(allowPmx('pmx_create, pmx_articles', true))
		{
			foreach($context['pmx']['articles'] as $aID => $article)
			{
				foreach($categories as $cat)
				{
					if($cat['id'] == $article['catid'] && strpos($cat['config'], '"global":"1"') !== false)
						unset($context['pmx']['articles'][$aID]);
				}
			}
		}

		// call PmxArticleOverview for each article
		foreach($context['pmx']['articles'] as $article)
		{
			if($pgCount >= $context['pmx']['articlestart'] && $pgCount < $context['pmx']['articlestart'] + $context['pmx']['settings']['manager']['artpage'])
				PmxArticleOverview($article, $cfg_titleicons, $cfg_pmxgroups, $categories);

			$pgCount++;
			$artIDs[] = $article['id'];
		}

		echo '
					<span id="pbot"></span>
				</div>
			</div>';

		// bottom pageindex
	if($context['pmx']['subaction'] == 'overview')
		echo '
			<div class="fixpagesbar" style="margin-top:5px;">
				<a href="#ptop" class="topbottom floatleft">', $txt['go_up'], '</a>
				<div class="pagelinks pmx_pageBot">', $pageindex, '</div>
			</div>
			<div class="clear_left"></div>';

/**
* Popup windows for overview
**/
		echo '
			<input id="pWind.all.ids." type="hidden" value="'. implode(',', $artIDs) .'" />
			<div class="popup_rows">';

		// start row move popup
		echo '
				<div id="pmxRowMove" class="smalltext">
					'. pmx_popupHeader('pmxArtMove', $txt['pmx_rowmove_title']) .'
						<input id="pWind.move.error" type="hidden" value="'. $txt['pmx_rowmove_error'] .'" />
						<div class="artrm0">
							'. $txt['pmx_rowmove'] .'
							<div class="artrm2">'. $txt['pmx_rowmove_place'] .'</div>
							<div class="artrm3">'. $txt['pmx_rowmove_to'] .'</div>
						</div>
						<div class="artrm4">
							<div class="pmxshorttxt artrm5" id="pWind.move.pos"></div>
							<div class="artrm6">
								<input id="pWind.place.0" class="artrm6cb input_radio" type="radio" name="_" value="before" /><span class="artrm7">'. $txt['pmx_rowmove_before'] .'</span>
								<input id="pWind.place.1" class="input_radio" type="radio" name="_" value="after" checked="checked" /><span class="artrm7">'. $txt['pmx_rowmove_after'] .'</span><br />
							</div>
							<select id="pWind.sel" class="artrm8" size="1">';
		foreach($context['pmx']['article_rows'] as $id => $data)
			echo '
								<option value="'. $id .'">['. $id .'] '. $data['name'] .' - ['. (empty($data['cat']) ? $txt['pmx_default_none'] : $data['cat']) .']</option>';

		echo '
							</select>
						</div>
						<input class="button_submit" type="button" value="'. $txt['pmx_save'] .'" onclick="pmxSendArtMove()" />
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

		// categorie popup
		echo '
				<div id="pmxSetCats" class="smalltext" style="z-index:9999;width:220px;display:none;">
					'. pmx_popupHeader('pmxSetCats', $txt['pmx_category_popup']) .'
						<select id="pWind.cats.sel" onchange="pmxChgCats(this)" class="artsc0" size="4">';

		$selcats = array_merge(array(PortaMx_getDefaultCategory($txt['pmx_categories_none'])), $categories);
		$ordercats = array_merge(array(0), $context['pmx']['catorder']);
		$isWriter = allowPmx('pmx_create, pmx_articles', true);
		$isAdm = allowPmx('pmx_admin');

		$allcats = array();
		foreach($ordercats as $catorder)
		{
			$cat = PortaMx_getCatByOrder($selcats, $catorder);
			$cfg = pmx_json_decode($cat['config'], true);

			// allcats html
			$details = PortaMx_getCatDetails($cat, $categories);
			$allcats[] = $cat['id'].'|<div class="'. $details['class'] .'"><b>'. $details['level'] .'</b><span><span class="cat_names">'. $cat['name'] .'</span></span></div>';

			if(!empty($isAdm) || (!empty($isWriter) && empty($cfg['global'])))
			{
				if(empty($cat))
				{
					$cat['id'] = 0;
					$cat['name'] = $txt['pmx_categories_none'];
				}
				$details['parent'] .= $txt['pmx_chg_articlcats'];

				echo '
							<option value="'. $cat['id'] .'">'. str_repeat('&bull;', $cat['level']) .' '. $cat['name'] .'</option>';
			}
		}

		echo '
						</select><br />
						<div class="artsc2">
							<input class="button_submit pmxpopup_btn" type="button" value="'. $txt['pmx_update_save'] .'" onclick="pmxUpdateCats()" />&nbsp;
							<input class="button_submit pmxpopup_btn" type="button" value="'. $txt['pmx_update_all'] .'" onclick="pmxUpdateCats(\'all\')" />
						</div>
					</div>
				</div>';
		// end categorie popup

		// start filter popup
		echo '
				<div id="pmxSetFilter" class="smalltext">
					'. pmx_popupHeader('pmxSetFilter', $txt['pmx_article_setfilter']) .'
						<div class="artflt0">'. $txt['pmx_article_filter_category'] .'<span class="artflt2" onclick="pmxSetFilterCatClr()">[<b>'. $txt['pmx_article_filter_categoryClr'] .'</b>]</span></div>
						<select id="pWind.filter.category" class="artflt3" size="3" multiple="multiple">';

		$selcats = array_merge(array(PortaMx_getDefaultCategory($txt['pmx_categories_none'])), $categories);
		$ordercats = array_merge(array(0), $context['pmx']['catorder']);
		$catfilter = Pmx_StrToArray($_SESSION['PortaMx']['filter']['category']);
		$isWriter = allowPmx('pmx_create, pmx_articles', true);
		$isAdm = allowPmx('pmx_admin');
		foreach($ordercats as $catorder)
		{
			$cat = PortaMx_getCatByOrder($selcats, $catorder);
			$cfg = pmx_json_decode($cat['config'], true);
			if(!empty($isAdm) || (!empty($isWriter) && empty($cfg['global'])))
				echo '
							<option value="'. $cat['id'] .'"'. (in_array($cat['id'], $catfilter) ? ' selected="selected"' : '') .'>'. str_repeat('&bull;', $cat['level']) .' '. $cat['name'] .'</option>';
		}
		echo '
						</select><br />
						<div class="artflt4">
							'. $txt['pmx_article_filter_approved'] .'
							<input id="pWind.filter.approved" class="artfltcb" type="checkbox" value="1"'. (!empty($_SESSION['PortaMx']['filter']['approved']) ? ' checked="checked"' : '') .' />
						</div>
						<div class="artflt5">
							'. $txt['pmx_article_filter_active'] .'
							<input id="pWind.filter.active" class="artfltcb" type="checkbox" value="1"'. (!empty($_SESSION['PortaMx']['filter']['active']) ? ' checked="checked"' : '') .' />
						</div>';

		if(allowPmx('pmx_articles, pmx_admin, pmx_create'))
			echo '
						<div class="artflt5">
							'. $txt['pmx_article_filter_myown'] .'
							<input id="pWind.filter.myown" class="artfltcb" type="checkbox" value="1"'. (!empty($_SESSION['PortaMx']['filter']['myown']) ? ' checked="checked"' : '') .' />
						</div>';

		if(allowPmx('pmx_admin, pmx_articles'))
			echo '
						<div class="artflt6">
							'. $txt['pmx_article_filter_member'] .'
								<input id="pWind.filter.member" class="artfltinp input_text" type="text" value="'. $_SESSION['PortaMx']['filter']['member'] .'" />
						</div>'. $txt['pmx_article_filter_membername'];

		echo '
						<div class="'. (allowPmx('pmx_articles, pmx_admin') ? 'artflt7' : 'artflt8') .'">
							<input class="button_submit pmxpopup_btn" type="button" value="'. $txt['set_article_filter'] .'" onclick="pmxSendFilter()" />
							<div class="artflt9"></div>
						</div>
					</div>
				</div>';

		$filterActive = ($_SESSION['PortaMx']['filter']['category'] != '' || $_SESSION['PortaMx']['filter']['approved'] != 0 || $_SESSION['PortaMx']['filter']['active'] != 0 || $_SESSION['PortaMx']['filter']['myown'] != 0 || $_SESSION['PortaMx']['filter']['member'] != '');
		// end filter popup

		// start blocktype selection popup
		$RegBlocks = $context['pmx']['RegBlocks'];
		foreach($RegBlocks as $key =>$val)
			if(!in_array($key, array('html', 'script', 'bbc_script', 'php')))
				unset($RegBlocks[$key]);

		function cmpBDesc($a, $b){return strcasecmp(str_replace(' ', '', $a["description"]), str_replace(' ', '', $b["description"]));}
		uasort($RegBlocks, 'cmpBDesc');

		// start articletype selection popup
		echo '
				<div id="pmxArticleType" class="smalltext">
					'. pmx_popupHeader('pmxArticleType', $txt['pmx_add_new_articletype'], '55px') .'
						<div class="artnewf0">'. $txt['pmx_articles_articletype'] .'</div>
						<select id="pmx.article.type" class="artnewf01" size="1">';

		foreach($RegBlocks as $type => $articleType)
			echo '
							<option value="'. $type .'">'. $articleType['description'] .'</option>';

		echo '
						</select>
						<div class="artnewf2">
							<input class="button_submit pmxpopup_btn" type="button" value="'. $txt['pmx_create'] .'" onclick="pmxSendArticleType()" />
						</div>
					</div>
				</div>';
		// end popup

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

		// start clone popup
		echo '
				<div id="pmxSetArtClone" class="smalltext">
					'. pmx_popupHeader('pmxSetArtClone', $txt['pmx_art_clone']) .'
						<div>'. $txt['pmx_confirm_artclone'] .'</div>
						<input id="pWind.artcloneid" type="hidden" value="" />
						<input class="button_submit pmxpopup_btn artcl0" type="button" value="'. $txt['pmx_delete_button'] .'" onclick="pmxSendArtClone()" />
						<div class="artcl2"></div>
					</div>
				</div>';
		// end clone popup

		// start delete popup
		echo '
				<div id="pmxSetArtDelete" class="smalltext">
					'. pmx_popupHeader('pmxSetArtDelete', $txt['pmx_art_delete']) .'
						<div>'. $txt['pmx_confirm_artdelete'] .'</div>
						<input id="pWind.artdelid" type="hidden" value="" />
						<input class="artdel0 button_submit pmxpopup_btn" type="button" value="'. $txt['pmx_delete_button'] .'" onclick="pmxSendArtDelete()" />
						<div class="artdel2"></div>
					</div>
				</div>';
		// end delete popup

		echo '
			</div>';
	}

	// --------------------
	// singleblock edit
	// --------------------
	elseif($context['pmx']['subaction'] == 'edit' || $context['pmx']['subaction'] == 'editnew')
	{
		echo '
			<table class="pmx_table" style="margin-bottom:5px;table-layout:fixed;">
				<tr>
					<td style="text-align:center">
						<div class="cat_bar">
							<h3 class="catbg centertext">
							'. $txt['pmx_article_edit'. ($context['pmx']['subaction'] == 'editnew' ? '_new' : '')] .' '. $txt['pmx_articles_types'][$context['pmx']['editarticle']->cfg['ctype']] .'
							</h3>
						</div>
					</td>
				</tr>';

		// call the ShowAdmArticleConfig() methode
		$context['pmx']['editarticle']->pmxc_ShowAdmArticleConfig();

		echo '
			</table>';
	}

	echo '
		</form>';
}

/**
* AdmArticleOverview
* Called for each artile.
*/
function PmxArticleOverview($article, &$cfg_titleicons, &$cfg_pmxgroups, $categories)
{
	global $context, $user_info, $txt;

	if(empty($article['config']['title_align']))
		$article['config']['title_align'] = 'left';

	if(empty($article['config']['title_icon']))
		$article['config']['title_icon'] = 'none.png';

	if(!empty($article['acsgrp']))
		list($grpacs, $denyacs) = Pmx_StrToArray($article['acsgrp'], ',', '=');
	else
		$grpacs = $denyacs = array();

	// ID row
	echo '
							<div class="pmx_tbl_tr bm_row">
								<div class="pmx_tbl_tdgrid bm_order" id="RowMove-'. $article['id'] .'">';

	if(count($context['pmx']['article_rows']) > 1)
		echo '
									<div'. (allowPmx('pmx_articles, pmx_admin') ? ' onclick="pmxArtMove(\''. $article['id'] .'\', \'<b>'. $article['name'] .'</b> - ['. (empty($article['cat']) ? $txt['pmx_default_none'] : $article['cat']) .']\', this)"' : '') .'>
										<div id="Img.ArtMove-'. $article['id'] .'" style="white-space:nowrap;" class="pmx_clickrow'. (allowPmx('pmx_articles, pmx_admin') ? ' pmx_moveimg" title="'. $txt['pmx_rowmove_updown'] : '') .'">
											<div style="padding-left:20px;margin-top:-2px;width:22px;">'. $article['id'] .'</div>
										</div>
									</div>';
	echo '
								</div>';

	// title row
	echo '
								<div class="pmx_tbl_tdgrid art_ttl" id="pWind.ypos.'. $article['id'] .'">
									<div class="admshortstr"'. (allowPmx('pmx_admin, pmx_articles') || (allowPmx('pmx_create') && $article['owner'] == $user_info['id']) ? ' onclick="pmxSetTitle(\''. $article['id'] .'\', \'\', this)"  title="'. $txt['pmx_click_edit_ttl'] .'" style="cursor:pointer;"' : '') .'>
										<img id="uTitle.icon.'. $article['id'] .'" style="padding-right:4px;" src="'. $context['pmx_Iconsurl'] . $article['config']['title_icon'] .'" alt="*" title="'. substr($txt['pmx_edit_titleicon'], 0, -1) .'" />
										<img id="uTitle.align.'. $article['id'] .'" src="'. $context['pmx_imageurl'] .'text_align_'. $article['config']['title_align'] .'.gif" alt="*" title="'. $txt['pmx_edit_title_align'] . $txt['pmx_edit_title_align_types'][$article['config']['title_align']] .'" />
										<span class="admshortstr" id="sTitle.text.'. $article['id'] .'.">'. (isset($article['config']['title'][$context['pmx']['currlang']]) ? htmlspecialchars($article['config']['title'][$context['pmx']['currlang']], ENT_QUOTES) : '') .'</span>';

	foreach($context['pmx']['languages'] as $lang => $sel)
		echo '
										<input id="sTitle.text.'. $lang .'.'. $article['id'] .'." type="hidden" value="'. (isset($article['config']['title'][$lang]) ? htmlspecialchars($article['config']['title'][$lang], ENT_QUOTES) : '') .'" />';

	echo '
										<input id="sTitle.icon.'. $article['id'] .'" type="hidden" value="'. $article['config']['title_icon'] .'" />
										<input id="sTitle.align.'. $article['id'] .'" type="hidden" value="'. $article['config']['title_align'] .'" />
									</div>
								</div>';

	// type row
	echo '
								<div class="pmx_tbl_tdgrid art_typ">
									<div style="cursor:default;" title="'. $context['pmx']['RegBlocks'][$article['ctype']]['blocktype'] .' '. $context['pmx']['RegBlocks'][$article['ctype']]['description'] .'"><img style="padding-right:5px;" src="'. $context['pmx_imageurl'] .'type_'. $article['ctype'] .'.gif" alt="*" title="'. $article['ctype'] .'" /><span style="cursor:default;">'. $context['pmx']['RegBlocks'][$article['ctype']]['description'] .'</span></div>
								</div>';

	$detais = array();
	$cat = !empty($article['catid']) ? $article['catid'] : '0';
	pmx_getAllCatDetais(PortaMx_getCategories(), $detais, $txt['pmx_chg_articlcats']);
	$detais['0'] = array(
		'class' => 'cat_none',
		'level' => '0',
		'parent' => $txt['pmx_categories_none'],
		'name' => $txt['pmx_categories_none'],
	);

	// category row
	echo '
								<div class="pmx_tbl_tdgrid art_cat">
									<input id="pWind.catid.'. $article['id'] .'" type="hidden" value="'. $cat .'" />
									<div onclick="pmxSetCats(\''. $article['id'] .'\', this)" style="cursor:pointer;">';

	foreach($detais as $cid => $catsDetais)
	{
		echo'
										<div id="pWind.cat.'. $cid .'.'. $article['id'] .'" title="'. $catsDetais['parent'] . $txt['pmx_chg_articlcats'] .'" class="'. $catsDetais['class'] .'"  style="display:'. ($cid == $cat ? 'block' : 'none') .';">
											<b>'. $catsDetais['level'] .'</b>
											<span><span class="cat_names">'. $catsDetais['name'] .'</span></span>
										</div>';
	}
	echo '
									</div>
								</div>';

	// create acs groups for acs Popup
	if(!empty($article['acsgrp']))
		list($grpacs, $denyacs) = Pmx_StrToArray($article['acsgrp'], ',', '=');
	else
		$grpacs = $denyacs = array();

	// options row
	echo '
								<div class="pmx_tbl_tdgrid art_opt">
									<input id="grpAcs.'. $article['id'] .'" type="hidden" value="'. implode(',', $grpacs) .'" />
									<input id="denyAcs.'. $article['id'] .'" type="hidden" value="'. implode(',', $denyacs) .'" />
									<div id="pmxSetArtDelete.'. $article['id'] .'"><span id="pmxSetArtClone.'. $article['id'] .'"></span>
										<div id="pWind.grp.'. $article['id'] .'" class="pmx_clickrow'. (!empty($article['acsgrp']) ? ' pmx_access" title="'. $txt['pmx_article_groupaccess'] : '') .'"></div>
										<div class="pmx_clickrow'. (!empty($article['config']['can_moderate']) ? ' pmx_moderate"  title="'. $txt['pmx_article_modaccess'] : '') .'"></div>
										<div class="pmx_clickrow'. (!empty($article['config']['check_ecl']) ? ' pmx_eclsettings" title="'. $txt['pmx_have_artecl_settings'] : '') .'"></div>
										<div class="pmx_clickrow'. (!empty($article['config']['cssfile']) ? ' pmx_custcss" title="'. $txt['pmx_article_cssfile'] : '') .'"></div>
									</div>
								</div>';

	// status row
	echo '
								<div class="pmx_tbl_tdgrid art_stat">
									<div class="pmx_clickrow'. ($article['approved'] ? ' pmx_approved" title="'. $txt['pmx_article_approved'] : ' pmx_notapproved" title="'. $txt['pmx_article_not_approved']) . (allowPmx('pmx_articles, pmx_admin') ? ' - '. $txt['pmx_status_change'] .'" onclick="pToggleArtStatus(this,'. $article['id'] .',\'approved\')': '" style="cursor:default') .'"></div>
									<div class="pmx_clickrow'. ($article['active'] ? ' pmx_active" title="'. $txt['pmx_status_activ'] : ' pmx_notactive" title="'. $txt['pmx_status_inactiv']) .' - '. $txt['pmx_status_change'] .'" onclick="pToggleArtStatus(this,'. $article['id'] .',\'active\')"></div>
								</div>';

	// functions row
	echo '
								<div class="pmx_tbl_tdgrid art_func">
									<div class="pmx_clickrow'. (allowPmx('pmx_admin, pmx_articles') || (allowPmx('pmx_create') && $article['owner'] == $user_info['id']) ? ' pmx_pgedit" title="'. $txt['pmx_edit_article'].'" onclick="FormFunc(\'edit_article\', \''. $article['id'] .'\')"' : '"') .'></div>
									<div class="pmx_clickrow'. (allowPmx('pmx_admin, pmx_articles') || (allowPmx('pmx_create') && $article['owner'] == $user_info['id']) ? ' pmx_grpacs" title="'. $txt['pmx_chg_articleaccess'] .'" onclick="pmxSetAcs(\''. $article['id'] .'\', \'\', this)"' : '"') .'></div>
									<div class="pmx_clickrow'. (allowPmx('pmx_admin, pmx_articles') || (allowPmx('pmx_create') && $article['owner'] == $user_info['id']) ? ' pmx_pgclone" title="'. $txt['pmx_clone_article'] .'" onclick="pmxSetArtClone(\''. $article['id'] .'\', this)"' : '"') .'></div>
									<div class="pmx_clickrow'. (allowPmx('pmx_admin, pmx_articles') || (allowPmx('pmx_create') && $article['owner'] == $user_info['id']) ? ' pmx_pgdelete" title="'. $txt['pmx_delete_article'] .'" onclick="pmxSetArtDelete(\''. $article['id'] .'\', this)"' : '"') .'></div>
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