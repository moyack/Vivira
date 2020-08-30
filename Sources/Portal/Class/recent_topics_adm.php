<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file recent_topics_adm.php
 * Admin Systemblock recent_topics
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* @class pmxc_recent_topics_adm
* Admin Systemblock recent_topics_adm
* @see recent_topics_adm.php
*/
class pmxc_recent_topics_adm extends PortaMxC_SystemAdminBlock
{
	/**
	* AdmBlock_init().
	* Setup caching.
	*/
	function pmxc_AdmBlock_init()
	{
		$this->can_cached = 1;			// enable caching
	}

	/**
	* AdmBlock_settings().
	* Setup the config vars and output the block settings.
	* Returns the css classes they are used.
	*/
	function pmxc_AdmBlock_settings()
	{
		global $context, $scripturl, $txt;

		// define the settings options
		echo '
					<td class="pmxfloattd">
						<div class="bmcustheight">
							<input type="hidden" name="config[settings]" value="" />';

		// define numeric vars to check
		echo '
							<input type="hidden" name="check_num_vars[]" value="[config][settings][numrecent], 5" />';

		// show the settings screen
		echo '
							<div class="cat_bar catbg_grid grid_padd">
								<h4 class="catbg catbg_grid"><span class="cat_msg_title">'. sprintf($txt['pmx_blocks_settings_title'], $this->register_blocks[$this->cfg['blocktype']]['description']) .'</span></h4>
							</div>

							<div class="adm_input adm_sel" style="margin-top:3px;">
								<span>&nbsp;'. $txt['pmx_recent_boards'] .'
									<a href="', $scripturl, '?action=helpadmin;help=pmx_recent_boards_help" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
								</span>
								<select class="adm_w90 notdbut" name="config[settings][recentboards][]" multiple="multiple" size="4">';

		$boards = isset($this->cfg['config']['settings']['recentboards']) ? $this->cfg['config']['settings']['recentboards'] : array();
		foreach($this->pmx_boards as $brd)
			echo '
									<option value="'. $brd['id'] .'"'. (in_array($brd['id'], $boards) ? ' selected="selected"' : '') .'>'. $brd['name'] .'</option>';

		echo '
								</select>
							</div>

							<div class="adm_input">
								<span class="adm_w80">'. $txt['pmx_recenttopicnum'] .'</span>
								<div><input onkeyup="check_numeric(this);" size="2" type="text" name="config[settings][numrecent]" value="' .(isset($this->cfg['config']['settings']['numrecent']) ? $this->cfg['config']['settings']['numrecent'] : '5'). '" /></div>
							</div>
							<div class="adm_check">
								<span class="adm_w80">'. $txt['pmx_recent_showboard'] .'</span>
								<input type="hidden" name="config[settings][showboard]" value="0" />
								<div><input class="input_check" type="checkbox" name="config[settings][showboard]" value="1"' .(!empty($this->cfg['config']['settings']['showboard']) ? ' checked="checked"' : '') .' /></div>
							</div>
							<div class="adm_check">
								<span class="adm_w80">&nbsp;'. $txt['pmx_recentsplit'] .'
									<a href="', $scripturl, '?action=helpadmin;help=pmx_recentsplit_help" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
								</span>
								<div><input class="input_check" type="checkbox" name="config[settings][recentsplit]" value="1"' .(!empty($this->cfg['config']['settings']['recentsplit']) ? ' checked="checked"' : '') .' /></div>
							</div>
							<input type="hidden" name="config[show_sitemap]" value="0" />
						</div>';

		// return the default classnames
		return $this->block_classdef;
	}
}
?>