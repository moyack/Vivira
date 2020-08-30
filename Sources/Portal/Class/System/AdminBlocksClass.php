<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file PortaMx_AdminBlocksClass.php
 * Global Blocks Admin class
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* @class PortaMxC_AdminBlocks
* The Global Class for Block Administration.
* @see PortaMx_AdminBlocksClass.php
*/
class PortaMxC_AdminBlocks
{
	var $cfg;						///< common config
	var $cache_time;		///< block cache time

	/**
	* The Contructor.
	* Saved the config, load the block css if exist.
	* Have the block a css file, the class definition is extracted from ccs header
	*/
	function __construct($blockconfig)
	{
		global $context;

		// get the block config array
		if(isset($blockconfig['config']))
			$blockconfig['config'] = pmx_json_decode($blockconfig['config'], true);
		$this->cfg = $blockconfig;

		// get the cache time if exist
		if(isset($context['pmx']['cache']['blocks'][$this->cfg['blocktype']]['time']))
			$this->cache_time = $context['pmx']['cache']['blocks'][$this->cfg['blocktype']]['time'];
	}

	/**
	* This Methode is called on loadtime.
	* After all variables initiated, it calls the block dependent init methode.
	* Finaly the css and language is loaded if exist
	*/
	function pmxc_AdmBlock_loadinit()
	{
		global $context;

		$this->pmx_groups = PortaMx_getUserGroups();										// get all usergroups
		$this->pmx_boards = PortaMx_getBoards();												// get all smf boards
		$this->register_blocks = $context['pmx']['RegBlocks'];					// get all registered block
		$this->block_classdef = PortaMx_getdefaultClass();							// get default classes
		$this->title_icons = PortaMx_getAllTitleIcons();								// get all title icons
		$this->custom_css = PortaMx_getCustomCssDefs();									// custom css definitions
		$this->can_cached = 0;																					// default no caching

		// sort the registered blocks
		ksort($this->register_blocks, SORT_STRING);
		function cmpBDesc($a, $b){return strcasecmp(str_replace(' ', '', $a["description"]), str_replace(' ', '', $b["description"]));}
		uasort($this->register_blocks, 'cmpBDesc');

		// call the blockdepend init methode
		$this->pmxc_AdmBlock_init();
	}

	/**
	* The default init Methode.
	* Note: Most blocks overwrite this methode.
	*/
	function pmxc_AdmBlock_init()
	{
		$this->can_cached = 0;		// disable caching
		return '';
	}

	/**
	* The default content Methode.
	* returns the blocksettings.
	* Note: Most blocks overwrite this methode.
	*/
	function pmxc_AdmBlock_content()
	{
		// default .. no content
		return $this->pmxc_AdmBlock_settings();
	}

	/**
	* The default settings Methode.
	* returns the block css class definition.
	* Note: Most blocks overwrite this methode.
	*/
	function pmxc_AdmBlock_settings()
	{
		global $scripturl, $txt;

		// the default settings
		echo '
					<td class="pmxfloattd">
						<input type="hidden" name="config[settings]" value="" />
						<div style="height:169px;">
							<div class="cat_bar catbg_grid grid_padd grid_top">
								<h4 class="catbg catbg_grid"><span class="cat_msg_title">'. sprintf($txt['pmx_blocks_settings_title'], $this->register_blocks[$this->cfg['blocktype']]['description']) .'</span></h4>
							</div>'.
							$txt['pmx_defaultsettings'] .'
						</div>';

		// return the default classnames
		return $this->block_classdef;
	}

	/**
	* check the extend options.
	* returns true if any found
	**/
	function getExtoptions()
	{
		$extOpts = !empty($this->cfg['config']['maintenance_mode']);
		if(!empty($this->cfg['config']['ext_opts']))
		{
			foreach($this->cfg['config']['ext_opts'] as $k => $v)
				$extOpts = (isset($v) && !empty($v) ? true : $extOpts);
		}
		if($this->cfg['side'] == 'front')
			$extOpts = isset($this->cfg['config']['frontplace']) && ($this->cfg['config']['frontplace'] == 'before' || $this->cfg['config']['frontplace'] == 'after') ? true : $extOpts;

		return $extOpts;
	}

	/**
	* Get Config data (name=value format)
	* return result array
	*/
	function getConfigData($itemstr = '')
	{
		$item = Pmx_StrToArray($itemstr);
		$result = array();

		$ptr = &$this->cfg;
		foreach($item as $key)
			$ptr = &$ptr[$key];

		if(isset($ptr))
		{
			if(is_array($ptr))
			{
				foreach($ptr as $val)
				{
					$tmp = Pmx_StrToArray($val, '=');
					if(isset($tmp[0]) && isset($tmp[1]))
						$result[$tmp[0]] = $tmp[1];
				}
			}
			else
				$result = $ptr;
		}
		return $result;
	}
}

/**
* @class PortaMxC_SystemAdminBlock
* This is the Global Admin class to create or edit a single Block.
* This class prepare the settings screen and call the user dependent settings and content classes.
* @see PortaMx_AdminBlocksClass.php
*/
class PortaMxC_SystemAdminBlock extends PortaMxC_AdminBlocks
{
	var $blockcssfiles;			///< all block cssfiles
	var $pmx_groups;				///< all usergroups
	var $pmx_boards;				///< all boards
	var $register_blocks;		///< all registered blocks
	var $block_classdef;		///< all default classes
	var $can_cached;				///< block can cached (1), not cached (0)
	var $title_icons;				///< array with title icons
	var $custom_css;				///< custom css definitions

	/**
	* Output the Block config screen
	*/
	function pmxc_ShowAdmBlockConfig()
	{
		global $context, $settings, $modSettings, $options, $scripturl, $boardurl, $txt;

		echo '
				<tr>
					<td>
						<div class="windowbg edit_main">
						<div class="pmx_scrolldiv">
						<table class="pmx_table pmx_tbl_overflow">
							<tr>
								<td class="pmxfloattd">
									<input type="hidden" name="id" value="'. $this->cfg['id'] .'" />
									<input type="hidden" name="pos" value="'. $this->cfg['pos'] .'" />
									<input type="hidden" name="side" value="'. $this->cfg['side'] .'" />
									<input type="hidden" name="active" value="'. $this->cfg['active'] .'" />
									<input type="hidden" name="cache" value="'. $this->cfg['cache'] .'" />
									<input type="hidden" name="contenttype" value="'. ($this->cfg['blocktype'] == 'download' ? 'bbc_script' : $this->cfg['blocktype']) .'" />
									<input type="hidden" name="check_num_vars[]" value="[config][maxheight], \'\'" />

									<div style="height:38px;">
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

		// show registered block types
		echo '
								</td>
								<td class="pmxfloattd">
									<div style="min-height:62px;">
										<div style="float:left; width:127px; padding-top:2px;">
											<span><a href="', $scripturl, '?action=helpadmin;help=pmx_block_select_help" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
											<span>'. $txt['pmx_edit_type'] .'</span>
										</div>';

		if(allowPmx('pmx_admin'))
		{
			echo '
										<div style="height:34px;">
											<select class="bmblkseldd" id="pmx.check.type" size="1" name="blocktype" onchange="ajax_indicator(true);FormFunc(\'chg_blocktype\', 1)">';

			foreach($this->register_blocks as $blocktype => $blockDesc)
				echo '
												<option value="'. $blocktype .'"' .($this->cfg['blocktype'] == $blocktype ? ' selected="selected"' : '') .'>'. $blockDesc['description'] .'</option>';
			echo '
											</select>
											<script>initSelect(document.getElementById("pmx.check.type"));</script>
										</div>
										<input id="cache_value" type="hidden" name="cache" value="0" />';
		}
		else
			echo '
										<input type="hidden" name="blocktype" value="'. $this->cfg['blocktype'] .'" />
										<input style="width:50%;" type="text" value="'. $this->cfg['blocktype'] .'" disabled="disabled" />';

		// cache settings
		if(!empty($this->can_cached))
		{
			echo '
										<div style="float:left; width:127px; padding-top:4px;">
											<span><a href="', $scripturl, '?action=helpadmin;help=pmx_edit_pmxcachehelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>&nbsp;'. $txt['pmx_edit_cache'] .'
										</div>';

			if(in_array($this->cfg['blocktype'], array_keys($context['pmx']['cache']['blocks'])))
			{
				echo '
										<input style="float:left; margin-top:9px !important;" id="cacheflag" class="input_check" type="checkbox" name="cacheflag" onclick="checkPmxCache(this, '. $this->cache_time .')" value="1"'. (!empty($this->cfg['cache']) ? ' checked="checked"' : ''). ' />';

				if($this->cfg['blocktype'] != 'mini_calendar')
					echo '
										<div style="float:left; margin-left:15px; padding-top:5px;">'. $txt['pmx_edit_cachetime'] .'</div>
										<input style="float:left; margin-top:3px; margin-left:6px;'. (empty($this->cfg['cache']) ? 'background-color:#8898b0;' : '') .'" onkeyup="check_numeric(this)" id="cacheval" type="text" name="cache" value="'.(empty($this->cfg['cache']) ? '0' : (empty($this->cfg['cache']) ? $this->cache_time : $this->cfg['cache'])) .'" size="7" />
										<div class="smalltext" style="float:left; margin-left:3px; padding-top:7px;">'. $txt['pmx_edit_cachetimesec'] .'</div>';
				else
					echo '
										<input type="hidden" name="cache" value="86400" />
										<div id="cachehelp" style="float:left;padding-top:5px;margin-left:10px;display:none;">'. $txt['pmx_cache_autoclear'] .'</div>';
			}

			echo '
										<div style="clear:both; line-height:4px; margin-top:-4px;">&nbsp;</div>
									</div>
									<script>checkPmxCache(document.getElementById("cacheflag"),'. $this->cache_time .');</script>';
		}
		else
		{
			echo '
									<div style="float:left; margin-top:4px; height:27px;">';

			if(empty($this->can_cached))
				echo '
										'. $txt['pmx_edit_nocachehelp'];
			echo '
									</div>';
		}

			// Pagename
		if($this->cfg['side'] == 'pages')
			echo '
									<div class="adm_clear">
										<div class="adm_clear" style="float:left;width:127px; padding-top:8px;">
											<a href="', $scripturl, '?action=helpadmin;help=pmx_edit_pagenamehelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>&nbsp'. $txt['pmx_edit_pagename'] .'
										</div>
										<input style="width:56%; margin-top:8px;" onkeyup="check_requestname(this)" type="text" name="config[pagename]" value="'. (!empty($this->cfg['config']['pagename']) ? $this->cfg['config']['pagename'] : '') .'" />
									</div>';

		else
			echo '
									<input type="hidden" name="config[pagename]" value="'. (!empty($this->cfg['config']['pagename']) ? $this->cfg['config']['pagename'] : '') .'" />';

		echo '
								</td>
							</tr>
							<tr>';

		/**
		* Call the block depended settings.
		* Because each block can have his own settings, we have to call the settings now.
		*/
		$usedClass = $this->pmxc_AdmBlock_content();

		// the group access
		echo '
									<div class="cat_bar catbg_grid grid_padd grid_top">
										<h4 class="catbg catbg_grid">
											<a href="', $scripturl, '?action=helpadmin;help=pmx_edit_groups_help" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
											<span class="cat_msg_title">'. $txt['pmx_edit_groups'] .'</span>
										</h4>
									</div>
									<select id="pmxgroups" onchange="changed(\'pmxgroups\');" style="width:82.5%;" name="acsgrp[]" multiple="multiple" size="5">';

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
									</script>';

		// Block moderate && ECL block hidden
		if(!isset($this->cfg['config']['can_moderate']))
			$this->cfg['config']['can_moderate'] = (allowPmx('pmx_admin') ? 0 : 1);

		if(allowPmx('pmx_blocks', true))
			echo '
									<input type="hidden" name="config[can_moderate]" value="'. $this->cfg['config']['can_moderate'] .'" />';

		echo '
									<input type="hidden" name="config[check_ecl]" value="0" />';

		// Block moderate and ECL
		if(!empty($modSettings['ecl_enabled']) || !allowPmx('pmx_blocks', true))
			echo '
									<div class="cat_bar catbg_grid grid_padd grid_top">
										<h4 class="catbg catbg_grid">
											<span class="cat_msg_left">'. $txt['pmx_block_other_settings'] .'</span>
											</h4>
									</div>';

		if(!allowPmx('pmx_blocks', true))
			echo '
									<div class="adm_check">
										<span class="adm_w80">&nbsp;'. $txt['pmx_block_moderate'] .'
											<a href="', $scripturl, '?action=helpadmin;help=pmx_block_moderatehelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
										</span>
										<input type="hidden" name="config[can_moderate]" value="0" />
										<input class="input_check" type="checkbox" name="config[can_moderate]" value="1"' .(!empty($this->cfg['config']['can_moderate']) ? ' checked="checked"' : ''). ' />
									</div>
									<input type="hidden" name="config[check_ecl]" value="0" />
									<input type="hidden" name="config[check_eclbots]" value="0" />';

		// Block hidden on ECL
		if(!empty($modSettings['ecl_enabled']))
			echo '
									<div class="adm_check">
										<span class="adm_w80">&nbsp;'. $txt['pmx_check_elcmode'] .'
											<a href="', $scripturl, '?action=helpadmin;help=pmx_block_eclcheckhelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
										</span>
										<input class="input_check" type="checkbox" name="config[check_ecl]" value="1"' .(!empty($this->cfg['config']['check_ecl']) ? ' checked="checked"' : '') .' />
									</div>';
		echo '
									<div class="adm_check">
										<span class="adm_w80">&nbsp'. $txt['pmx_check_elcbots'] .'
											<a href="', $scripturl, '?action=helpadmin;help=pmx_block_eclcheckbotshelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
										</span>
										<input id="eclextendinp" class="input_check" type="checkbox" name="config[check_eclbots]" value="1"' .(!empty($this->cfg['config']['check_eclbots']) ? ' checked="checked"' : ''). ' /></div>
									</div>';

		// the visual options
		echo '
								</td>
								<td class="pmxfloattd">
									<div class="cat_bar catbg_grid grid_padd">
										<h4 class="catbg catbg_grid"><span class="cat_msg_title">'. $txt['pmx_edit_visuals'] .'</span></h4>
									</div>
									<div style="float:left; height:30px; width:180px;">'. $txt['pmx_edit_cancollapse'] .'</div>
									<input style="padding-left:141px;" type="hidden" name="config[collapse]" value="0" />
									<input class="input_check" id="collapse" type="checkbox" name="config[collapse]" value="1"'. ($this->cfg['config']['visuals']['header'] == 'none' ? ' disabled="disabled"' : ($this->cfg['config']['collapse'] == 1 ? ' checked="checked"' : '')) .' />
									<div style="clear:both;" /></div>
									<div style="float:left; height:30px; width:180px;">'. $txt['pmx_edit_collapse_state'] .'</div>
									<select style="width:46%;margin-bottom:6px;" size="1" name="config[collapse_state]">';

		foreach($txt['pmx_collapse_mode'] as $key => $text)
			echo '
										<option value="'. $key .'"'. (isset($this->cfg['config']['collapse_state']) && $this->cfg['config']['collapse_state'] == $key ? ' selected="selected"' : '') .'>'. $text .'</option>';
		echo '
									</select>
									<br style="clear:both;" />
									<div style="float:left; height:30px; width:180px;">'. $txt['pmx_edit_overflow'] .'</div>
									<select style="width:46%;margin-bottom:3px;" size="1" id="mxhgt" name="config[overflow]" onchange="checkMaxHeight(this);">';

		foreach($txt['pmx_overflow_actions'] as $key => $text)
			echo '
										<option value="'. $key .'"'. (isset($this->cfg['config']['overflow']) && $this->cfg['config']['overflow'] == $key ? ' selected="selected"' : '') .'>'. $text .'</option>';
		echo '
									</select>
									<br style="clear:both;" />
									<div style="float:left; min-height:30px; width:99%;">
										<div style="float:left; min-height:30px; width:180px;">'. $txt['pmx_edit_height'] .'</div>
										<div style="float:left; max-width:46%;margin-bottom:3px;">
											<input onkeyup="check_numeric(this)" id="maxheight" type="text" style="width:20%" name="config[maxheight]" value="'. (isset($this->cfg['config']['maxheight']) ? $this->cfg['config']['maxheight'] : '') .'"'. (!isset($this->cfg['config']['overflow']) || empty($this->cfg['config']['overflow']) ? ' disabled="disabled"' : '') .' /><span class="smalltext">'. $txt['pmx_pixel'] .'</span><span style="display:inline-block; width:3px;"></span>
											<select id="maxheight_sel" style="float:right;width:52%;margin-right:-1%;" size="1" name="config[height]">';

		foreach($txt['pmx_edit_height_mode'] as $key => $text)
			echo '
												<option value="'. $key .'"'. (isset($this->cfg['config']['height']) && $this->cfg['config']['height'] == $key ? ' selected="selected"' : '') .'>'. $text .'</option>';
		echo '
											</select>
										</div>
									</dv>
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
											<div style="float:left; width:180px;">
												<a href="', $scripturl, '?action=helpadmin;help=pmx_used_stylehelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
												<span class="cat_msg_title">'. $txt['pmx_edit_usedclass_type'] .'</span>
											</div>
											<span class="cat_msg_left">'. $txt['pmx_edit_usedclass_style'] .'</span>
										</h4>
									</div>
									<div style="margin:0px">';

		// write out the classes
		foreach($usedClass as $ucltyp => $ucldata)
		{
			echo '
										<div style="float:left; width:180px; height:30px; padding-top:2px;">'. $ucltyp .'</div>
										<select'. ($ucltyp == 'frame' || $ucltyp == 'postframe' ? ' id="pmx_'. $ucltyp .'" ' : ' ') .'style="width:46%;margin-bottom:8px" name="config[visuals]['. $ucltyp .']" onchange="checkCollapse(this)">';

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
										<h4 class="catbg catbg_grid floatleft"><span class="cat_msg_title">
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
									<div style="clear:both;"></div>';

			// write out all class definitions (hidden)
			foreach($this->custom_css as $custcss)
			{
				if(is_array($custcss))
				{
					echo '
									<div id="'. $custcss['file'] .'" style="display:none;">';

					foreach($custcss['class'] as $key => $val)
					{
						if(in_array($key, array_keys($usedClass)))
							echo '
										<div style="float:left; width:180px; padding:0 2px;">'. $key .'</div>'. (empty($val) ? sprintf($txt['pmx_edit_nocss_class'], $settings['theme_id']) : $val) .'<br />';
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
											document.getElementById(fname).style.display = "block";
										function pmxChangeCSS(elm)
										{
											for(i=0; i<elm.length; i++)
											{
												if(document.getElementById(elm.options[i].value))
													document.getElementById(elm.options[i].value).style.display = "none";
											}
											var fname = elm.options[elm.selectedIndex].value;
											if(document.getElementById(fname))
												document.getElementById(fname).style.display = "block";
										}
									</script>';
		}
		else
			echo '
									</select>
									<div style="clear:both; height:6px;"></div>';

		echo '
								</td>
							</tr>
							<tr>
								<td colspan="2" style="text-align:center;padding:4px;"><hr class="pmx_hr" />
									<input class="button_submit" type="button" value="'. $txt['pmx_save_exit'] .'" onclick="FormFunc(\'save_edit\', \'1\')" />
									<input class="button_submit" type="button" style="margin-right:10px;" value="'. $txt['pmx_save_cont'] .'" onclick="FormFunc(\'save_edit_continue\', \'1\')" />
									<input class="button_submit" type="button" style="margin-right:10px;" value="'. $txt['pmx_cancel'] .'" onclick="FormFunc(\'cancel_edit\', \'1\')" />
								</td>
							</tr>';

		// the dynamic visibility options
		if(empty($context['pmx']['settings']['manager']['collape_visibility']))
			$options['collapse_visual'] = 1;
		else
			$options['collapse_visual'] = intval($this->getExtoptions());

		echo '
							<tr>
								<td colspan="2" style="padding:15px 4px 0 4px;">
									<div class="title_bar">
										<h3 class="catbg">
											<span id="upshrinkImgVisual" class="floatleft '. (!empty($options['collapse_visual']) ? 'toggle_up"' : 'toggle_down"') .' align="bottom"></span>
											<span class="pmxtitle pmxcenter pmxadj_right"><span>'. $txt['pmx_edit_ext_opts'] .'</span></span>
										</h3>
									</div>

											<div id="upshrinkVisual" style="display:' .(!empty($options['collapse_visual']) ? 'block' : 'none') .';">
										<div class="info_text plainbox" style="margin:5px 0 0;display:block">
											<img style="vertical-align:-3px;" onclick=\'Toggle_help("pmxBH05")\' src="'. $context['pmx_imageurl'] .'helptopics.png" alt="*" title="'. $txt['pmx_information_icon'] .'" />
											<span>'. $txt['pmx_edit_ext_opts_help'] .'</span>
											<div id="pmxBH05" style="display:none;"><hr class="pmx_hr" />'. $txt['pmx_edit_ext_opts_morehelp'] .'</div>
										</div>
									</div>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<div id="upshrinkVisual1"' .(!empty($options['collapse_visual']) ? '' : ' style="display:none;"'). '>
										<table class="pmx_table" style="table-layout:fixed;">
											<tr>';

		// on default actions
		echo '
												<td class="pmxfloattd">
													<div class="cat_bar catbg_grid grid_padd">
														<h4 class="catbg catbg_grid">
															<a href="', $scripturl, '?action=helpadmin;help=pmx_edit_ext_opts_selnote" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
															<span class="cat_msg_title">'. $txt['pmx_edit_ext_opts_action'] .'</span>
														</h4>
													</div>
													<select id="pmxact" onchange="changed(\'pmxact\');" style="width:82.5%;" name="config[ext_opts][pmxact][]" multiple="multiple" size="10">';

		// get config data
		$data = $this->getConfigData('config, ext_opts, pmxact');
		foreach($txt['pmx_action_names'] as $act => $actdesc)
			echo '
														<option value="'. $act .'='. (array_key_exists($act, $data) ? $data[$act] .'" selected="selected' : '1') .'">'. (array_key_exists($act, $data) ? ($data[$act] == 0 ? '^' : '') : '') . $actdesc .'</option>';

		echo '
													</select>
													<script>
														var pmxact = new MultiSelect("pmxact");
													</script>';

		// on boards
		echo '
													<div class="cat_bar catbg_grid grid_padd grid_top">
														<h4 class="catbg catbg_grid">
															<a href="', $scripturl, '?action=helpadmin;help=pmx_edit_ext_opts_selnote" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
															<span class="cat_msg_title">'. $txt['pmx_edit_ext_opts_boards'] .'</span>
														</h4>
													</div>
													<select id="pmxbrd" onchange="changed(\'pmxbrd\');" style="width:82.5%;" name="config[ext_opts][pmxbrd][]" multiple="multiple" size="8">';

		// get config data
		$data = $this->getConfigData('config, ext_opts, pmxbrd');
		foreach($this->pmx_boards as $brd)
			echo '
														<option value="'. $brd['id'] .'='. (array_key_exists($brd['id'], $data) ? $data[$brd['id']] .'" selected="selected' : '1') .'">'. (array_key_exists($brd['id'], $data) ? ($data[$brd['id']] == '0' ? '^' : '') : '') . $brd['name'] .'</option>';

		echo '
													</select>
													<script>
														var pmxbrd = new MultiSelect("pmxbrd");
													</script>
												</td>';

		// custom action
		echo '
												<td class="pmxfloattd">
													<div class="cat_bar catbg_grid grid_padd">
														<h4 class="catbg catbg_grid">
															<a href="', $scripturl, '?action=helpadmin;help=pmx_edit_ext_opts_custhelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
															<span class="cat_msg_title">'. $txt['pmx_edit_ext_opts_custaction'] .'</span>
														</h4>
													</div>
													<textarea class="adm_textarea" style="min-height:20px;width:82.5% !important;" rows="1" name="config[ext_opts][pmxcust]">'. $this->cfg['config']['ext_opts']['pmxcust'] .'</textarea>';

		// hide on Maintenance?
		echo '
													<div class="adm_check grid_top">
														<span class="adm_w80">'. $txt['pmx_edit_ext_maintenance'] .'</span>
														<input type="hidden" name="config[maintenance_mode]" value="0" />
														<input class="input_check" type="checkbox" name="config[maintenance_mode]" value="1"' .(!empty($this->cfg['config']['maintenance_mode']) ? ' checked="checked"' : ''). ' />
													</div>';

		// on language
		echo '
													<div class="cat_bar catbg_grid grid_padd grid_top">
														<h4 class="catbg catbg_grid">
															<a href="', $scripturl, '?action=helpadmin;help=pmx_edit_ext_opts_selnote" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
															<span class="cat_msg_title">'. $txt['pmx_edit_ext_opts_languages'] .'</span>
														</h4>
													</div>
													<select id="pmxlng" onchange="changed(\'pmxlng\');" style="width:82.5%;" name="config[ext_opts][pmxlng][]" multiple="multiple" size="2">';

		// get config data
		$data = $this->getConfigData('config, ext_opts, pmxlng');
		foreach($context['pmx']['languages'] as $lang => $sel)
			echo '
														<option value="'. $lang .'='. (array_key_exists($lang, $data) ? $data[$lang] .'" selected="selected' : '1') .'">'. (array_key_exists($lang, $data) ? ($data[$lang] == 0 ? '^' : '') : '') . ucfirst($lang) .'</option>';

		echo '
													</select>
													<script>
														var pmxlng = new MultiSelect("pmxlng");
													</script>';

		// mobile or other devices
		echo '
													<div class="cat_bar catbg_grid grid_padd" style="margin-top:10px;">
														<h4 class="catbg catbg_grid">
															<a href="', $scripturl, '?action=helpadmin;help=pmx_blocks_deviceshelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
															<span class="cat_msg_title">'. $txt['pmx_blocks_devices'] .'</span>
														</h4>
													</div>
													<input type="hidden" name="config[ext_opts][device]" value="0" />
													<div class="adm_check"><span class="adm_w80">'. $txt['pmx_devices']['all'] .'</span>
														<input class="input_check" type="radio" name="config[ext_opts][device]" value="0"'. (empty($this->cfg['config']['ext_opts']['device']) ? ' checked="checked"' : '') .' />
													</div>
													<div class="adm_check"><span class="adm_w80">'. $txt['pmx_devices']['mobil'] .'</span>
														<input class="input_check" type="radio" name="config[ext_opts][device]" value="1"'. ((!empty($this->cfg['config']['ext_opts']['device']) && $this->cfg['config']['ext_opts']['device'] == '1') ? ' checked="checked"' : '') .' />
													</div>
													<div class="adm_check"><span class="adm_w80">'. $txt['pmx_devices']['desk'] .'</span>
														<input class="input_check" type="radio" name="config[ext_opts][device]" value="2"'. ((!empty($this->cfg['config']['ext_opts']['device']) && $this->cfg['config']['ext_opts']['device'] == '2') ? ' checked="checked"' : '') .' />
													</div>
													<input type="hidden" name="config[frontmode]" value="" />
													<input type="hidden" name="config[frontview]" value="" />';

		// Frontpage block placing on Page request
		if($this->cfg['side'] == 'front')
			echo '
													<div class="cat_bar catbg_grid grid_padd">
														<h4 class="catbg catbg_grid">
															<a href="', $scripturl, '?action=helpadmin;help=pmx_edit_frontplacinghelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
															<span class="cat_msg_title">'. $txt['pmx_edit_frontplacing'] .'</span>
														</h4>
													</div>
													<input type="hidden" name="config[frontplace]" value="hide">
													<div class="adm_check"><span class="adm_w80">'. $txt['pmx_edit_frontplacing_hide'] .'</span>
														<input class="input_check" type="radio" name="config[frontplace]" value="hide"'. (isset($this->cfg['config']['frontplace']) && $this->cfg['config']['frontplace'] == 'hide' || empty($this->cfg['config']['frontplace']) ? ' checked="checked"' : '') .' />
													</div>
													<div class="adm_check"><span class="adm_w80">'. $txt['pmx_edit_frontplacing_before'] .'</span>
														<input class="input_check" type="radio" name="config[frontplace]" value="before"'. (isset($this->cfg['config']['frontplace']) && $this->cfg['config']['frontplace'] == 'before' ? ' checked="checked"' : '') .' />
													</div>
													<div class="adm_check"><span class="adm_w80">'. $txt['pmx_edit_frontplacing_after'] .'</span>
														<input class="input_check" type="radio" name="config[frontplace]" value="after"'. (isset($this->cfg['config']['frontplace']) && $this->cfg['config']['frontplace'] == 'after' ? ' checked="checked"' : '') .' />
													</div>';

		echo '
												</td>
											</tr>
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
							</tr>
						</table>
						</div>
						</div>
					</td>
				</tr>';

		// add visual upshrink
		$tmp = '
		var upshrinkVis = new pmxc_Toggle({
			bToggleEnabled: true,
			bCurrentlyCollapsed: '. (empty($options['collapse_visual']) ? 'true' : 'false') .',
			aSwappableContainers: [
				\'upshrinkVisual\',
				\'upshrinkVisual1\'
			],
			aSwapImages: [{
					sId: \'upshrinkImgVisual\',
					altCollapsed: '. (JavaScriptEscape($txt['pmx_expand'] . $txt['pmx_edit_ext_opts'])) .',
					altExpanded: '. (JavaScriptEscape($txt['pmx_collapse'] . $txt['pmx_edit_ext_opts'])) .'
			}],
			oCookieOptions: {
				bUseCookie: false
			},
			oThemeOptions: {
				bUseThemeSettings: true,
				sOptionName: \'collapse_visual\',
				sSessionVar: '. (JavaScriptEscape($context['session_var'])) .',
				sSessionId: '. (JavaScriptEscape($context['session_id'])) .',
				sThemeId: \'1\'
			}
		});';

		addInlineJavascript(str_replace("\n", "\n\t", PortaMx_compressJS($tmp)), true);
		unset($tmp);
	}
}
?>