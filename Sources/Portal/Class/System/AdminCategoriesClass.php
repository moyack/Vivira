<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file PortaMx_AdminCategoriesClass.php
 * Global Categories Admin class
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* @class PortaMxC_AdminCategories
* The Global Class for Categories Administration.
* @see PortaMx_AdminCategoriesClass.php
*/
class PortaMxC_AdminCategories
{
	var $cfg;						///< common config

	/**
	* The Contructor.
	* Saved the config, load the category css file if exist.
	* Have the category a css file, the class definition is extracted from ccs header
	*/
	function __construct($config)
	{
		// get the block config array
		if(isset($config['config']))
			$config['config'] = pmx_json_decode($config['config'], true);
		$this->cfg = $config;
	}
}

/**
* @class PortaMxC_SystemAdminCategories
* This is the Global Admin class to create or edit a Categories.
* This class prepare the settings screen.
* @see PortaMx_AdminCategoriesClass.php
*/
class PortaMxC_SystemAdminCategories extends PortaMxC_AdminCategories
{
	var $pmx_groups;				///< all usergroups
	var $title_icons;				///< array with title icons
	var $custom_css;				///< custom css definitions
	var $usedClass;					///< used class types
	var $categories;				///< all exist categories

	/**
	* This Methode is called on loadtime.
	* After all variables initiated, it calls the block dependent init methode.
	* Finaly the css is loaded if exist
	*/
	function pmxc_AdmCategories_loadinit()
	{
		$this->pmx_groups = PortaMx_getUserGroups();										// get all usergroups
		$this->title_icons = PortaMx_getAllTitleIcons();								// get all title icons
		$this->custom_css = PortaMx_getCustomCssDefs();									// custom css definitions
		$this->usedClass = PortaMx_getdefaultClass(false, true);				// default class types
		$this->categories = PortaMx_getCategories();										// exist categories
	}

	/**
	* Output the Category config screen
	*/
	function pmxc_ShowAdmCategoryConfig()
	{
		global $context, $scripturl, $settings, $modSettings, $boardurl, $txt;

		echo '
				<tr>
					<td>
						<div class="windowbg edit_main">
						<div class="pmx_scrolldiv">
						<table class="pmx_table pmx_tbl_overflow">
							<tr>
								<td class="pmxfloattd">
									<input type="hidden" name="id" value="'. $this->cfg['id'] .'" />
									<input type="hidden" name="parent" value="'. $this->cfg['parent'] .'" />
									<input type="hidden" name="level" value="'. $this->cfg['level'] .'" />
									<input type="hidden" name="catorder" value="'. $this->cfg['catorder'] .'" />
									<input type="hidden" name="config[settings]" value="" />
									<input type="hidden" name="check_num_vars[]" value="[config][maxheight], \'\'" />';

		echo '
									<div style="height:37px;">
										<div style="float:left;width:100px; padding-top:1px;">
											<span>&nbsp;'. $txt['pmx_edit_title'] .'</span>
											<a style="float:left;" href="', $scripturl, '?action=helpadmin;help=pmx_edit_titlehelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
										</div>';

		// all titles depend on language
		$curlang = '';
		foreach($context['pmx']['languages'] as $lang => $sel)
		{
			$curlang = !empty($sel) ? $lang : $curlang;
			echo '
											<span id="'. $lang .'" style="white-space:nowrap;'. (!empty($sel) ? '' : ' display:none;') .'">
												<input style="width:60%" type="text" name="config[title]['. $lang .']" value="'. (isset($this->cfg['config']['title'][$lang]) ? htmlspecialchars($this->cfg['config']['title'][$lang], ENT_QUOTES) : '') .'" />
											</span>';
		}

		echo '
											<input id="curlang" type="hidden" value="'. $curlang .'" />
											<div style="clear:both;"></div>
											<img style="float:left;padding-left:18px;" src="'. $boardurl .'/Themes/default/Portal/SysCss/Images/arrow_down.gif" alt="*" title="">
										</div>
										<div style="float:left;width:100px;">
											<span>'. $txt['pmx_edit_title_lang'] .'</span>
										</div>
										<div style="float:left;height:30px;">
											<select style="float:left;width:168px;" size="1" onchange="setTitleLang(this)">';

		foreach($context['pmx']['languages'] as $lang => $sel)
			echo '
												<option value="'. $lang .'"' .(!empty($sel) ? ' selected="selected"' : '') .'>'. $lang .'</option>';

		echo '
											</select>
										<div style="margin-left:180px;padding-top:1px;">
											<input type="hidden" id="titlealign" name="config[title_align]" value="'. $this->cfg['config']['title_align'] .'" />';

		foreach($txt['pmx_edit_title_align_types'] as $key => $val)
			echo '
											<img id="img'. $key .'" src="'. $context['pmx_imageurl'] .'text_align_'. $key .'.gif" alt="*" title="'. $txt['pmx_edit_title_helpalign']. $val .'" style="cursor:pointer;vertical-align:1px;padding-bottom:'. ($val == 'left' ? '1' : '0') .'px;'.($this->cfg['config']['title_align'] == $key ? 'background-color:#e02000;' : '').'" onclick="setAlign(\'\', \''. $key .'\')" /><br />';

		echo '
										</div>
									</div>
									<div class="clear"></div>';

			// Title icons
		$this->cfg['config']['title_icon'] = (empty($this->cfg['config']['title_icon']) || $this->cfg['config']['title_icon'] == 'none.gif') ? 'none.png' : $this->cfg['config']['title_icon'];
		echo '
									<div style="float:left;height:40px;margin-top:-3px;">
										<div style="float:left;width:100px; padding-top:8px;height:26px;">'. $txt['pmx_edit_titleicon'] .'</div>
										<div class="ttliconDiv" onclick="setNewIcon(document.getElementById(\'pWind.icon_sel\'), event)">
											<input id="post_image" type="hidden" name="config[title_icon]" value="'. $this->cfg['config']['title_icon'] .'" />
											<input id="iconDD" value="'. ucfirst(str_replace('.png', '', $this->cfg['config']['title_icon'])) .'" readonly />
											<img id="pWind.icon" class="pwindiconBlk" src="'. $context['pmx_Iconsurl'] . $this->cfg['config']['title_icon'] .'" alt="*" />
											<img class="ddImageBlk" src="'. $context['pmx_imageurl'] .'state_expand.png" alt="*" title="" />
										</div>
										<ul class="ttlicondd Blkedit'. ($modSettings['isMobile'] ? '_mb' : '') .'" id="pWind.icon_sel" onclick="updIcon(this)">';

		foreach($this->title_icons as $file => $name)
			echo '
											<li id="'. $file .'" class="ttlicon'. ($this->cfg['config']['title_icon'] == $file ? ' active' : '') .'">
												<img src="'. $context['pmx_Iconsurl'] . $file .'" alt="*" /><span>'. $name .'</span>
											</li>';

		echo '
										</ul>
										<script>$("li").hover(function(){pmxToggleClass(this, "active")});</script>
									</div>';

		// show article types
		echo '
								</td>
								<td class="pmxfloattd">';

		// show placement for new categories
		if($context['pmx']['subaction'] == 'editnew')
		{
			if(!empty($this->categories))
			{
				echo '
									<div style="min-height:72px;">
										<div style="padding-top:1px;">
											<div style="float:left; width:130px;">'. $txt['pmx_categories_type'] .'</div>';

				$opt = 0;
				foreach($txt['pmx_categories_places'] as $artType => $artDesc)
				{
					echo '
												<input style="vertical-align:0 !important;" id="pWind.place.'. $opt .'" class="input_check" type="radio" name="catplace" value="'. $artType .'"'. ($artType == 'after' ? ' checked="checked"' : '') .' /><span style="vertical-align:3px; padding:0 10px 0 3px;">'. $artDesc .'</span>';
					$opt++;
				}

				// all exist categories
				echo '
										</div>
										<div style="float:left; width:130px;padding-top:14px;padding-bottom:8px;">'. $txt['pmx_categories_cats'] .'</div>';

				$allcats = array();
				foreach($context['pmx']['catorder'] as $order)
					$allcats[] = PortaMx_getCatByOrder($this->categories, $order);

				// output cats
				if(count($allcats) > 1)
				{
					echo '
											<select id="pmxallcats" style="width:200px;margin-top:14px;position:absolute;" size="1" name="catid">';

					foreach($allcats as $cat)
						echo '
													<option value="'. $cat['id'] .'"' .($this->cfg['id'] == $cat['id'] ? ' selected="selected"' : '') .'>'. str_repeat('&bull;', $cat['level']) .' '. $cat['name'] .'</option>';

					echo '
											</select>
											<script>initSelect(document.getElementById("pmxallcats"));</script>';
				}
				else
				{
					list($id, $cat) = pmx_each($allcats);
					echo '
											<input type="text" style="width:200px;margin-top:14px;position:absolute;" value="'. $cat['name'] .'" readonly="true" />
											<input type="hidden" name="catid" value="'. $cat['id'] .'" />';
				}

				echo '
										</div>';
			}
			else
			 echo '
										<input type="hidden" name="catid" value="0" />
										<input type="hidden" name="catplace" value="0" />
										<div style="height:61px;">&nbsp;</div>';
		}

		// category name
		echo '
										<div class="adm_clear" style="float:left;width:130px;">
											<a href="', $scripturl, '?action=helpadmin;help=pmx_edit_pagenamehelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
											<span>'. $txt['pmx_categories_name'] .':</span>
										</div>
										<span id="check.name.error" style="display:none;">'. sprintf($txt['namefielderror'], $txt['pmx_categories_name']) .'</span>
										<input id="check.name" style="width:200px; margin-top:0px;" onkeyup="check_requestname(this)" onkeypress="check_requestname(this)" type="text" name="name" value="'. $this->cfg['name'] .'" />
									</div>
								</td>
							</tr>

							<tr>
								<td class="pmxfloattd">
									<input type="hidden" name="config[settings]" value="" />';

		// show the settings area
		echo '
									<div class="cat_bar catbg_grid grid_padd">
										<h4 class="catbg catbg_grid"><span class="cat_msg_title">'. $txt['pmx_categories_settings_title'] .'</span></h4>
									</div>';

		// show mode (titelbar/frame)
		foreach($txt['pmx_categories_showmode'] as $key => $value)
			echo '
									<div class="adm_check" style="min-height:38px;">
										<img align="left" src="'. $context['pmx_imageurl'] .'ca_frame_'. $key .'.png" alt="*" />
										<div style="float:left; width:72%; padding:0 10px;">'. $value .'</div>
										<input style="float:right;margin-right:20px;" name="config[settings][framemode]" class="input_radio" type="radio" value="'. $key .'"'. ($this->cfg['config']['settings']['framemode'] == $key ? ' checked="checked"' : '') .' />
									</div>';

		// show mode also for articles
		echo '
									<div class="adm_check" style="height:20px;">
										<div style="float:left; width:82%;">
											<a href="', $scripturl, '?action=helpadmin;help=pmx_cat_to_art_design_help" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
											&nbsp;'. $txt['pmx_categories_visual'].'
										</div>
										<input style="float:right;margin-right:20px!important;min-height:21px;" name="config[settings][catstyle]" type="checkbox" class="input_check" value="1"'. (!empty($this->cfg['config']['settings']['catstyle']) ? ' checked="checked"' : '') .' />
									</div>';

		// show mode (sidebar)
		echo '
									<div class="adm_check" style="height:20px;">
										<div style="float:left; width:82%;">'. $txt['pmx_categories_modsidebar'] .'</div>
										<input id="shm.sidebar" style="float:right;margin-right:20px;" onclick="check_PageMode(this, \'pages\')" name="config[settings][showmode]" class="input_radio" type="radio" value="sidebar"'. ($this->cfg['config']['settings']['showmode'] == 'sidebar' ? ' checked="checked"' : '') .' />
									</div>';

		// show mode (pages)
		echo '
									<div class="adm_check" style="height:20px;">
										<div style="float:left; width:82%;">'. $txt['pmx_categories_modpage'] .'</div>
										<input id="shm.pages" style="float:right;margin-right:20px;" onclick="check_PageMode(this, \'sidebar\')" name="config[settings][showmode]" class="input_radio" type="radio" value="pages"'. ($this->cfg['config']['settings']['showmode'] == 'pages' ? ' checked="checked"' : '') .' />
									</div>';

		// options for SideBar mode
		echo '
									<div id="opt.sidebar" class="adm_clear" style="padding-top:0px;'. ($this->cfg['config']['settings']['showmode'] == 'pages' ? ' display:none;' : '') .'">
										<div style="height:25px;margin-top:0px;">
											<div style="float:left;">'. $txt['pmx_categories_sidebarwith'] .'</div>
											<input style="float:right;margin-right:20px;margin-top:-2px;" onkeyup="check_numeric(this)" type="text" size="3" name="config[settings][sidebarwidth]" value="'. (!empty($this->cfg['config']['settings']['sidebarwidth']) ? $this->cfg['config']['settings']['sidebarwidth'] : '') .'" />
										</div>
										<div style="height:25px;margin-top:2px;">
											<div style="float:left;">'. $txt['pmx_categories_sidebaralign'] .'</div>
											<div style="float:right; margin-right:20px;margin-top:0px;">
												<input class="input_radio" type="radio" name="config[settings][sbmalign]" value="1"'. (!empty($this->cfg['config']['settings']['sbmalign']) ? ' checked="checked"' : '') .' /><span style="display:inline-block;margin-top:2px;">'. $txt['pmx_categories_sbalign'][0] .'&nbsp;&nbsp;&nbsp;</span>
												<input class="input_radio" type="radio" name="config[settings][sbmalign]" value="0"'. (empty($this->cfg['config']['settings']['sbmalign']) ? ' checked="checked"' : '') .' /><span style="display:inline-block;margin-top:2px;">'. $txt['pmx_categories_sbalign'][1] .'</span>
											</div>
										</div>
										<div style="height:20px;margin-top:0px;">
											<div style="float:left; width:82%;">'. $txt['pmx_categories_addsubcats'] .'</div>
											<input type="hidden" name="config[settings][addsubcats]" value="0" />
											<input style="float:right;margin-right:20px!important;margin-top:3px!important;" type="checkbox" class="input_check" name="config[settings][addsubcats]" value="1"'. (!empty($this->cfg['config']['settings']['addsubcats']) ? ' checked="checked"' : '') .' />
										</div>
									</div>';

		// options for Pages mode
		echo '
									<div id="opt.pages" class="adm_clear" style="padding-top:0px;'. ($this->cfg['config']['settings']['showmode'] == 'sidebar' ? 'display:none;' : '') .'">
										<div style="height:20px;">
											<div style="float:left;">'. $txt['pmx_categories_modpage_count'] .'</div>
											<input style="float:right;margin-right:20px!important;margin-top:-2px;" onkeyup="check_numeric(this)" type="text" size="3" name="config[settings][pages]" value="'. (!empty($this->cfg['config']['settings']['pages']) ? $this->cfg['config']['settings']['pages'] : '') .'" />
										</div>
										<div style="height:20px;margin-top:7px;">
											<div style="float:left;">'. $txt['pmx_categories_modpage_pageindex'] .'</div>
											<input style="float:right;margin-right:20px!important;margin-top:2px!important;" type="checkbox" class="input_check" name="config[settings][pageindex]" value="1"'. (!empty($this->cfg['config']['settings']['pageindex']) ? ' checked="checked"' : '') .' />
										</div>
										<div style="height:20px;margin-top:5px;">
											<div style="float:left; width:82%;">'. $txt['pmx_categories_showsubcats'] .'</div>
											<input type="hidden" name="config[settings][showsubcats]" value="0" />
											<input id="opt.pages.sbar.check" style="float:right;margin-right:20px!important;" type="checkbox" class="input_check" name="config[settings][showsubcats]" value="1"'. (!empty($this->cfg['config']['settings']['showsubcats']) ? ' checked="checked"' : '') .' onclick="set_PageMode(this)" />
										</div>
									</div>
									<div id="opt.pages.sbar" class="adm_clear" style="padding-top:2px;'. ($this->cfg['config']['settings']['showmode'] == 'pages' && !empty($this->cfg['config']['settings']['showsubcats']) ? '' : 'display:none;') .'">
										<div style="height:23px;margin-top:3px;">
											<div style="float:left;">'. $txt['pmx_categories_sidebarwith'] .'</div>
											<input style="float:right;margin-right:20px;" onkeyup="check_numeric(this)" type="text" size="3" name="config[settings][catsbarwidth]" value="'. (!empty($this->cfg['config']['settings']['catsbarwidth']) ? $this->cfg['config']['settings']['catsbarwidth'] : '') .'" />
										</div>
										<div style="height:20px;margin-top:3px;">
											<div style="float:left;">'. $txt['pmx_categories_sidebaralign'] .'</div>
											<div style="float:right; margin-right:20px;margin-top:0px;">
												<input class="input_radio" type="radio" name="config[settings][sbpalign]" value="1"'. (!empty($this->cfg['config']['settings']['sbpalign']) ? ' checked="checked"' : '') .' /><span style="vertical-align:2px;">'. $txt['pmx_categories_sbalign'][0] .'&nbsp;&nbsp;&nbsp;</span>
												<input class="input_radio" type="radio" name="config[settings][sbpalign]" value="0"'. (empty($this->cfg['config']['settings']['sbpalign']) ? ' checked="checked"' : '') .' /><span style="vertical-align:2px;">'. $txt['pmx_categories_sbalign'][1] .'</span>
											</div>
										</div>
									</div>
									<div class="adm_check" style="height:20px; padding-top:4px;">
										<div style="float:left; width:82%;">
											<a href="', $scripturl, '?action=helpadmin;help=pmx_categories_inherithelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
											&nbsp;'. $txt['pmx_categorie_inherit'] .'
										</div>
										<input style="float:right;margin-right:20px!important;margin-top:2px!important;" name="config[settings][inherit_acs]" class="input_check" type="checkbox" value="1"'. (!empty($this->cfg['config']['settings']['inherit_acs']) ? ' checked="checked"' : '') .' />
									</div>';

		// article sort
		echo '
									<div class="adm_clear" style="height:86px;">
										<div style="float:left; width:150px;">
											<a href="', $scripturl, '?action=helpadmin;help=pmx_categories_sorthelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
											'. $txt['pmx_categorie_articlesort'] .'
										</div>
										<select style="float:right;margin-right:20px; width:45%;margin-top:3px;" name="artsort[]" id="pmxartsort" onchange="changed(\'pmxartsort\');" size="4" multiple="multiple">';

		if(!empty($this->cfg['artsort']))
		{
			$sortdata = array();
			$sortval = Pmx_StrToArray($this->cfg['artsort']);
			foreach($sortval as $sort)
			{
				@list($k, $v) = Pmx_StrToArray($sort, '=');
					$sortdata[$k] = $v;
			}
		}
		else
			$sortdata = array('id' => 1);

		foreach($txt['pmx_categories_artsort'] as $key => $value)
			echo '
											<option value="'. $key .'='. (array_key_exists($key, $sortdata) ? $sortdata[$key] .'" selected="selected' : '1') .'">'. (array_key_exists($key, $sortdata) ? ($sortdata[$key] == '0' ? '^' : '') : '') . $value .'</option>';

		echo '
										</select>
									</div>
									<div class"adm_clear"></div>
									<script>
										var pmxartsort = new MultiSelect("pmxartsort");
									</script>';

		// Categorie for common use
		echo '
									<input type="hidden" name="config[check_ecl]" value="0" />
									<input type="hidden" name="config[check_eclbots]" value="0" />
									<div class="cat_bar catbg_grid grid_padd" style="margin-top:10px;">
										<h4 class="catbg catbg_grid"><span class="cat_msg_title">'. $txt['pmx_categories_globalcat'] .'</span></h4>
									</div>
									<div class="adm_check" style="min-height:25px;">
										<span>&nbsp;'. $txt['pmx_categorie_global'] .'
											<a href="', $scripturl, '?action=helpadmin;help=pmx_categories_gloablcathelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
										</span>
										<input class="input_check" style="float:right; margin-right:20px!important;" type="checkbox" name="config[global]" value="1"' .(!empty($this->cfg['config']['global']) ? ' checked="checked"' : ''). ' />
									</div>
									<div class="adm_check" style="min-height:25px;">
										<span>&nbsp;'. $txt['pmx_categorie_request'] .'
											<a href="', $scripturl, '?action=helpadmin;help=pmx_categorie_requesthelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
										</span>
										<input class="input_check" style="float:right; margin-right:20px!important;" type="checkbox" name="config[request]" value="1"' .(!empty($this->cfg['config']['request']) ? ' checked="checked"' : ''). ' />
									</div>
									<div class="adm_check" style="min-height:25px;">
										<span>&nbsp;'. $txt['pmx_check_catelcmode'] .'
											<a href="', $scripturl, '?action=helpadmin;help=pmx_cat_eclcheckhelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
										</span>
										<input class="input_check" style="float:right; margin-right:20px!important;" type="checkbox" name="config[check_ecl]" value="1"' .(!empty($this->cfg['config']['check_ecl']) ? ' checked="checked"' : ''). ' onclick="showeclbots(this)" />
									</div>
									<div class="adm_check" id="eclextend" style="min-height:25px;">
										<span>&nbsp;'. $txt['pmx_check_catelcbots'] .'
											<a href="', $scripturl, '?action=helpadmin;help=pmx_cat_eclcheckbotshelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
										</span>
										<input id="eclextendinp" class="input_check" style="float:right; margin-right:20px!important;" type="checkbox" name="config[check_eclbots]" value="1"' .(!empty($this->cfg['config']['check_eclbots']) ? ' checked="checked"' : ''). ' />
									</div>
								</td>';

		// the visual options
		echo '
								<td id="set_col" class="pmxfloattd">
									<div class="cat_bar catbg_grid grid_padd">
										<h4 class="catbg catbg_grid"><span class="cat_msg_title">'. $txt['pmx_edit_visuals'] .'</span></h4>
									</div>
									<div style="float:left; height:30px; width:180px;">'. $txt['pmx_edit_cancollapse'] .'</div>
									<input style="padding-left:141px;" type="hidden" name="config[collapse]" value="0" />
									<input class="input_check" id="collapse" type="checkbox" name="config[collapse]" value="1"'. ($this->cfg['config']['visuals']['header'] == 'none' ? ' disabled="disabled"' : ($this->cfg['config']['collapse'] == 1 ? ' checked="checked"' : '')) .' />
									<div style="clear:both;" /></div>
									<div style="float:left; height:30px; width:180px;">'. $txt['pmx_edit_collapse_state'] .'</div>
									<select style="width:46%;margin-bottom:6px" size="1" name="config[collapse_state]">';

		foreach($txt['pmx_collapse_mode'] as $key => $text)
			echo '
										<option value="'. $key .'"'. (isset($this->cfg['config']['collapse_state']) && $this->cfg['config']['collapse_state'] == $key ? ' selected="selected"' : '') .'>'. $text .'</option>';
		echo '
									</select>
									<br style="clear:both;" />
									<div style="float:left; height:30px; width:180px;">'. $txt['pmx_edit_overflow'] .'</div>
									<select style="width:46%;" size="1" id="mxhgt" name="config[overflow]" onchange="checkMaxHeight(this);">';

		foreach($txt['pmx_overflow_actions'] as $key => $text)
			echo '
										<option value="'. $key .'"'. (isset($this->cfg['config']['overflow']) && $this->cfg['config']['overflow'] == $key ? ' selected="selected"' : '') .'>'. $text .'</option>';
		echo '
									</select>
									<br style="clear:both;" />
									<div style="float:left; min-height:30px; width:99%;">
										<div style="float:left; min-height:30px; width:180px;">'. $txt['pmx_edit_height'] .'</div>
										<div style="float:left; max-width:46%;">
											<input onkeyup="check_numeric(this)" id="maxheight" type="text" style="width:20%" name="config[maxheight]" value="'. (isset($this->cfg['config']['maxheight']) ? $this->cfg['config']['maxheight'] : '') .'"'. (!isset($this->cfg['config']['overflow']) || empty($this->cfg['config']['overflow']) ? ' disabled="disabled"' : '') .' /><span class="smalltext">'. $txt['pmx_pixel'] .'</span><span style="display:inline-block; width:3px;"></span>
											<select id="maxheight_sel" style="float:right;width:52%;margin-right:-1%;" size="1" name="config[height]">';

		foreach($txt['pmx_edit_height_mode'] as $key => $text)
			echo '
												<option value="'. $key .'"'. (isset($this->cfg['config']['height']) && $this->cfg['config']['height'] == $key ? ' selected="selected"' : '') .'>'. $text .'</option>';
		echo '
											</select>
										</div>
									</div>
									<br style="clear:both;" />
									<script>
										checkMaxHeight(document.getElementById("mxhgt"));
									</script>

									<div style="float:left; height:30px; width:180px;">'. $txt['pmx_edit_innerpad'] .'</div>
									<input onkeyup="check_numeric(this, \',\')" type="text" size="4" name="config[innerpad]" value="'. (isset($this->cfg['config']['innerpad']) ? $this->cfg['config']['innerpad'] : '4') .'" /><span class="smalltext">'. $txt['pmx_pixel'] .' (xy/y,x)</span>
									<br style="clear:both;" />';

		// CSS class settings
		echo '
									<div class="cat_bar catbg_grid grid_padd">
										<h4 class="catbg catbg_grid grid_botpad">
											<div style="float:left; width:174px;">
												<a href="', $scripturl, '?action=helpadmin;help=pmx_used_style2help" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
												<span class="cat_msg_title">'. $txt['pmx_edit_usedclass_type'] .'</span>
										</h4>
									</div>
									<div style="margin:2px 0px">';

		// write out the classes
		foreach($this->usedClass as $ucltyp => $ucldata)
		{
			echo '
										<div style="float:left; width:180px; height:30px; padding-top:2px;">'. $ucltyp .'</div>
										<select'. ($ucltyp == 'frame' || $ucltyp == 'postframe' ? ' id="pmx_'. $ucltyp .'" ' : ' ') .'style="width:46%;margin-bottom:8px;" name="config[visuals]['. $ucltyp .']" onchange="checkCollapse(this)">';

			foreach($ucldata as $cname => $class)
					echo '
											<option value="'. $class .'"'. (!empty($this->cfg['config']['visuals'][$ucltyp]) ? ($this->cfg['config']['visuals'][$ucltyp] == $class ? ' selected="selected"' : '') : (substr($cname,0,1) == '+' ? ' selected="selected"' : '')) .'>'. substr($cname, 1) .'</option>';

			echo '
										</select>
										<br style="clear:both;" />';
		}

		echo '
									</div>
									<div class="cat_bar catbg_grid grid_padd" style="margin-top:-2px;">
										<h4 class="catbg catbg_grid"><span class="cat_msg_title">
											<a href="', $scripturl, '?action=helpadmin;help=pmx_custom_css_filehelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
											<span>'. $txt['pmx_edit_canhavecssfile'] .'</span>
										</h4>
									</div>
									<div style="float:left; margin:0px 2px; width:176px;">'. $txt['pmx_edit_cssfilename'] .'</div>
									<select id="sel.css.file" style="width:46%;margin-bottom:2px;" name="config[cssfile]" onchange="pmxChangeCSS(this)">
										<option value="">'. $txt['pmx_default_none'] .'</option>';

		// custon css files exist ?
		if(!empty($this->custom_css))
		{
			// write out custom mpt/css definitions
			foreach($this->custom_css as $custcss)
			{
				if(is_array($custcss))
					echo '
										<option value="'. $custcss['file'] .'"'. ($this->cfg['config']['cssfile'] == $custcss['file'] ? ' selected="selected"' : '') .'>'. $custcss['file'] .'</option>';
			}
			echo '
									</select>
									<div style="clear:both; height:2px;"></div>';

			// write out all class definitions (hidden)
			foreach($this->custom_css as $custcss)
			{
				if(is_array($custcss))
				{
					echo '
									<div id="'. $custcss['file'] .'" style="display:none;">';

					foreach($custcss['class'] as $key => $val)
					{
						if(in_array($key, array_keys($this->usedClass)))
							echo '
										<div style="float:left; width:176px; padding:0 2px;">'. $key .'</div>'. (empty($val) ? sprintf($txt['pmx_edit_nocss_class'], $settings['theme_id']) : $val) .'<br />';
					}

					echo '
									</div>';
				}
			}
			echo '
									<script>
										var elm = document.getElementById("sel.css.file");
										var fname = elm.options[elm.selectedIndex].value;
										if(document.getElementById(fname))
											document.getElementById(fname).style.display = "";
										function pmxChangeCSS(elm)
										{
											for(i=0; i<elm.length; i++)
											{
												if(document.getElementById(elm.options[i].value))
													document.getElementById(elm.options[i].value).style.display = "none";
											}
											var fname = elm.options[elm.selectedIndex].value;
											if(document.getElementById(fname))
												document.getElementById(fname).style.display = "";
										}
									</script>';
		}
		else
			echo '
									</select>
									<div style="clear:both; height:6px;"></div>';


		// the group access
		echo '
									<div class="adm_clear cat_bar catbg_grid grid_padd" style="margin-top:2px;">
										<h4 class="catbg catbg_grid">
											<a href="', $scripturl, '?action=helpadmin;help=pmx_categories_groupshelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
											<span class="cat_msg_title">'. $txt['pmx_categories_groups'] .'</span>
										</h4>
									</div>
									<select name="acsgrp[]" id="pmxgroups" onchange="changed(\'pmxgroups\');" style="float:left;width:47%;margin-left:178px;" multiple="multiple" size="5">';

		if(!empty($this->cfg['acsgrp']))
			list($grpacs, $denyacs) = Pmx_StrToArray($this->cfg['acsgrp'], ',', '=');
		else
			$grpacs = $denyacs = array();

		foreach($this->pmx_groups as $grp)
			echo '
										<option value="'. $grp['id'] .'='. intval(!in_array($grp['id'], $denyacs)) .'"'. (in_array($grp['id'], $grpacs) ? ' selected="selected"' : '') .'>'. (in_array($grp['id'], $denyacs) ? '^' : '') . $grp['name'] .'</option>';

		echo '
									</select>
									<script>
										var pmxgroups = new MultiSelect("pmxgroups");
									</script>
								</td>
							</tr>
							<tr>
							<tr>
								<td colspan="2" style="text-align:center;padding:4px;"><hr class="pmx_hr" />
									<input class="button_submit" type="button" value="'. $txt['pmx_save_exit'] .'" onclick="FormFunc(\'save_edit\', \'1\')" />
									<input class="button_submit" type="button" style="margin-right:10px;" value="'. $txt['pmx_save_cont'] .'" onclick="FormFunc(\'save_edit_continue\', \'1\')" />
									<input class="button_submit" type="button" style="margin-right:10px;" value="'. $txt['pmx_cancel'] .'" onclick="FormFunc(\'cancel_edit\', \'1\')" />
								</td>
							</tr>
						</table>
						</div>
						</div>
					</td>
				</tr>';
	}
}
?>