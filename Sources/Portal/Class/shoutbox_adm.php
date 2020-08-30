<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file shoutbox_adm.php
 * Admin Systemblock shoutbox_adm
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* @class pmxc_shoutbox_adm
* Admin Systemblock shoutbox_adm
* @see shoutbox_adm.php
*/
class pmxc_shoutbox_adm extends PortaMxC_SystemAdminBlock
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
							<input type="hidden" name="config[settings]" value="" />
							<textarea style="display:none;" name="content">'. $this->cfg['content'] .'</textarea>';

		// define numeric vars to check
		echo '
							<input type="hidden" name="check_num_vars[]" value="[config][settings][maxlen], 100" />
							<input type="hidden" name="check_num_vars[]" value="[config][settings][maxshouts], 50" />
							<input type="hidden" name="check_num_vars[]" value="[config][settings][maxheight], 250" />
							<input type="hidden" name="config[settings][boxcollapse]" value="1" />';

		// show the settings screen
		echo '
							<div class="cat_bar catbg_grid grid_padd">
								<h4 class="catbg catbg_grid">
									<span class="cat_msg_title">'. sprintf($txt['pmx_blocks_settings_title'], $this->register_blocks[$this->cfg['blocktype']]['description']) .'</span>
								</h4>
							</div>

							<div class="adm_input">
								<span class="adm_w80">'. $txt['pmx_shoutbox_maxlen'] .'</span>
								<div><input onkeyup="check_numeric(this);" size="3" type="text" name="config[settings][maxlen]" value="' .(isset($this->cfg['config']['settings']['maxlen']) ? $this->cfg['config']['settings']['maxlen'] : '100'). '" /></div>
							</div>
							<div class="adm_input">
								<span class="adm_w80">&nbsp;'. $txt['pmx_shoutbox_maxshouts'] .'
									<a href="', $scripturl, '?action=helpadmin;help=pmx_shoutbox_maxshouthelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
								</span>
								<div><input onkeyup="check_numeric(this);" size="3" type="text" name="config[settings][maxshouts]" value="' .(isset($this->cfg['config']['settings']['maxshouts']) ? $this->cfg['config']['settings']['maxshouts'] : '50'). '" /></div>
							</div>
							<div class="adm_input">
								<span class="adm_w80">'. $txt['pmx_shoutbox_maxheight'] .'</span>
								<div><input onkeyup="check_numeric(this);" size="3" type="text" name="config[settings][maxheight]" value="' .(isset($this->cfg['config']['settings']['maxheight']) ? $this->cfg['config']['settings']['maxheight'] : '250'). '" /></div>
							</div>
							<div class="adm_check">
								<span class="adm_w80">'. $txt['pmx_shoutbox_allowedit'] .'</span>
								<div><input class="input_check" type="checkbox" name="config[settings][allowedit]" value="1"' .(isset($this->cfg['config']['settings']['allowedit']) && $this->cfg['config']['settings']['allowedit'] == 1 ? ' checked="checked"' : ''). ' /></div>
							</div>
							<input type="hidden" name="config[show_sitemap]" value="0" />
							<div class="adm_input adm_sel">
								<span>'. $txt['pmx_shoutbox_canshout'] .'</span>
								<input type="hidden" name="config[settings][shout_acs][]" value="" />
								<select style="width:83%;" name="config[settings][shout_acs][]" multiple="multiple" size="5">';

		foreach($this->pmx_groups as $grp)
			if($grp['id'] != '-1')
				echo '
									<option value="'. $grp['id'] .'"'. (!empty($this->cfg['config']['settings']['shout_acs']) && in_array($grp['id'], $this->cfg['config']['settings']['shout_acs']) ? ' selected="selected"' : '') .'>'. $grp['name'] .'</option>';
		echo '
								</select>
							</div>
						</div>';

		// return the default classnames
		return $this->block_classdef;
	}
}
?>