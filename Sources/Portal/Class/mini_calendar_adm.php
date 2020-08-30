<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file mini_calendar_adm.php
 * Admin Systemblock mini_calendar
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* @class pmxc_mini_calendar_adm
* Admin Systemblock mini_calendar_adm
* @see mini_calendar_adm.php
*/
class pmxc_mini_calendar_adm extends PortaMxC_SystemAdminBlock
{
	/**
	* AdmBlock_init().
	* Setup caching.
	*/
	function pmxc_AdmBlock_init()
	{
		$this->can_cached = 1;		// enable caching
	}

	/**
	* AdmBlock_settings().
	* Setup the config vars and output the block settings.
	* Returns the css classes they are used.
	*/
	function pmxc_AdmBlock_settings()
	{
		global $txt;

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
								<span style="width:148px">'. $txt['pmx_minical_birthdays'] .'</span>
								<input type="hidden" name="config[settings][birthdays][show]" value="0" />
								<input style="margin-left:0px;" class="input_check" type="checkbox" name="config[settings][birthdays][show]" value="1"' .(!empty($this->cfg['config']['settings']['birthdays']['show']) ? ' checked="checked"' : ''). ' />
							</div>
							<div class="adm_input">
								<span style="width:148px">'. $txt['pmx_minical_bdays_before_after'] .'</span>
								<input style="margin-right:9px" class="input_text" size="2" type="text" name="config[settings][birthdays][before]" value="'. (isset($this->cfg['config']['settings']['birthdays']['before']) ? $this->cfg['config']['settings']['birthdays']['before'] : '') .'" />
								<b>-</b>
								<input style="margin-left:10px" class="input_text" size="2" type="text" name="config[settings][birthdays][after]" value="'. (isset($this->cfg['config']['settings']['birthdays']['after']) ? $this->cfg['config']['settings']['birthdays']['after'] : '') .'" />
							</div>

							<div class="adm_check">
								<span style="width:148px">'. $txt['pmx_minical_holidays'] .'</span>
								<input type="hidden" name="config[settings][holidays][show]" value="0" />
								<input style="margin-left:0px;" class="input_check" type="checkbox" name="config[settings][holidays][show]" value="1"' .(!empty($this->cfg['config']['settings']['holidays']['show']) ? ' checked="checked"' : ''). ' />
							</div>
							<div class="adm_input">
								<span style="width:148px">'. $txt['pmx_minical_bdays_before_after'] .'</span>
								<input style="margin-right:9px" class="input_text" size="2" type="text" name="config[settings][holidays][before]" value="'. (isset($this->cfg['config']['settings']['holidays']['before']) ? $this->cfg['config']['settings']['holidays']['before'] : '') .'" />
								<b>-</b>
								<input style="margin-left:10px" class="input_text" size="2" type="text" name="config[settings][holidays][after]" value="'. (isset($this->cfg['config']['settings']['holidays']['after']) ? $this->cfg['config']['settings']['holidays']['after'] : '') .'" />
							</div>

							<div class="adm_check">
								<span style="width:148px">'. $txt['pmx_minical_events'] .'</span>
								<input type="hidden" name="config[settings][events][show]" value="0" />
								<input style="margin-left:0px;" class="input_check" type="checkbox" name="config[settings][events][show]" value="1"' .(!empty($this->cfg['config']['settings']['events']['show']) ? ' checked="checked"' : ''). ' />
							</div>
							<div class="adm_input">
								<span style="width:148px">'. $txt['pmx_minical_bdays_before_after'] .'</span>
								<input style="margin-right:9px" class="input_text" size="2" type="text" name="config[settings][events][before]" value="'. (isset($this->cfg['config']['settings']['events']['before']) ? $this->cfg['config']['settings']['events']['before'] : '') .'" />
								<b>-</b>
								<input style="margin-left:10px" class="input_text" size="2" type="text" name="config[settings][events][after]" value="'. (isset($this->cfg['config']['settings']['events']['after']) ? $this->cfg['config']['settings']['events']['after'] : '') .'" />
							</div>
							<input type="hidden" name="config[show_sitemap]" value="0" />
						</div>';

		// return the classnames to use
		return $this->block_classdef;
	}
}
?>