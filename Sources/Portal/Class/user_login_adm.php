<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file user_login_adm.php
 * Admin Systemblock user_login
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* @class pmxc_user_login_adm
* Admin Systemblock user_login_adm
* @see user_login_adm.php
*/
class pmxc_user_login_adm extends PortaMxC_SystemAdminBlock
{
	/**
	* AdmBlock_init().
	* Setup caching.
	*/
	function pmxc_AdmBlock_init()
	{
		$this->can_cached = 0;		// enable caching
	}

	/**
	* AdmBlock_settings().
	* Setup the config vars and output the block settings.
	* Returns the css classes they are used.
	*/
	function pmxc_AdmBlock_settings()
	{
		global $context, $scripturl, $txt;

		// define additional classnames and styles
		$used_classdef = $this->block_classdef;
		$used_classdef['hellotext'] = array(
			' '. $txt['pmx_default_none'] => '',
			' smalltext' => 'smalltext',
			' middletext' => 'middletext',
			'+normaltext' => 'normaltext',
			' largetext' => 'largetext',
		);

		// define the settings options
		echo '
					<td class="pmxfloattd">
						<div class="bmcustheight">
							<input type="hidden" name="config[settings]" value="" />';

		// show the settings screen
		echo '
							<div class="cat_bar catbg_grid grid_padd">
								<h4 class="catbg catbg_grid"><span class="cat_msg_title">'. sprintf($txt['pmx_blocks_settings_title'], $this->register_blocks[$this->cfg['blocktype']]['description']) .'</span></h4>
							</div>

							<div class="adm_check">
								<span class="adm_w80">'. $txt['show_avatar'] .'</span>
								<div><input class="input_check" type="checkbox" name="config[settings][show_avatar]" value="1"' .(isset($this->cfg['config']['settings']['show_avatar']) && $this->cfg['config']['settings']['show_avatar'] == 1 ? ' checked="checked"' : ''). ' /></div>
							</div>

							<div class="adm_check">
								<span class="adm_w80">'. $txt['show_pm'] .'</span>
								<div><input class="input_check" type="checkbox" name="config[settings][show_pm]" value="1"' .(isset($this->cfg['config']['settings']['show_pm']) && $this->cfg['config']['settings']['show_pm'] == 1 ? ' checked="checked"' : ''). ' /></div>
							</div>

							<div class="adm_check">
								<span class="adm_w80">'. $txt['show_posts'] .'</span>
								<div><input class="input_check" type="checkbox" name="config[settings][show_posts]" value="1"' .(isset($this->cfg['config']['settings']['show_posts']) && $this->cfg['config']['settings']['show_posts'] == 1 ? ' checked="checked"' : ''). ' /></div>
							</div>

							<div class="adm_check">
								<span class="adm_w80">'. $txt['show_logtime'] .'</span>
								<div><input class="input_check" type="checkbox" name="config[settings][show_logtime]" value="1"' .(isset($this->cfg['config']['settings']['show_logtime']) && $this->cfg['config']['settings']['show_logtime'] == 1 ? ' checked="checked"' : ''). ' /></div>
							</div>

							<div class="adm_check">
								<span class="adm_w80">'. $txt['show_time'] .'</span>
								<div><input class="input_check" type="checkbox" name="config[settings][show_time]" value="1"' .(isset($this->cfg['config']['settings']['show_time']) && $this->cfg['config']['settings']['show_time'] == 1 ? ' checked="checked"' : ''). ' onchange="checkTimeEnabled(this)" /></div>
							</div>

							<div class="adm_check">
								<span class="adm_w80">'. $txt['show_realtime'] .'</span>
								<div><input class="input_check" type="checkbox" name="config[settings][show_realtime]" value="1"' .(isset($this->cfg['config']['settings']['show_realtime']) && $this->cfg['config']['settings']['show_realtime'] == 1 ? ' checked="checked"' : ''). ' /></div>
							</div>
							<input type="hidden" name="config[settings][rtc_format]" value="" />

							<div class="adm_check">
								<span class="adm_w80">'. $txt['show_login'] .'</span>
								<div><input class="input_check" type="checkbox" name="config[settings][show_login]" value="1"' .(isset($this->cfg['config']['settings']['show_login']) && $this->cfg['config']['settings']['show_login'] == 1 ? ' checked="checked"' : ''). ' /></div>
							</div>

							<div class="adm_check">
								<span class="adm_w80">'. $txt['show_logout'] .'</span>
								<div><input class="input_check" type="checkbox" name="config[settings][show_logout]" value="1"' .(isset($this->cfg['config']['settings']['show_logout']) && $this->cfg['config']['settings']['show_logout'] == 1 ? ' checked="checked"' : ''). ' /></div>
							</div>
							<input type="hidden" name="config[show_sitemap]" value="0" />
						</div>';

		// return the classnames to use
		return $used_classdef;
	}
}
?>