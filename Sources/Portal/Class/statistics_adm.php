<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file statistics_adm.php
 * Admin Systemblock statistics
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* @class pmxc_statistics_adm
* Admin Systemblock statistics_adm
* @see statistics_adm.php
*/
class pmxc_statistics_adm extends PortaMxC_SystemAdminBlock
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
		global $context, $scripturl, $txt;

		// define additional classnames and styles
		$used_classdef = $this->block_classdef;
		$used_classdef['stats_text'] = array(
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

		// define numeric vars to check
		echo '
							<input type="hidden" name="check_num_vars[]" value="[config][settings][stat_olheight], 10" />';

		// show the settings screen
		echo '
							<div class="cat_bar catbg_grid grid_padd">
								<h4 class="catbg catbg_grid"><span class="cat_msg_title">'. sprintf($txt['pmx_blocks_settings_title'], $this->register_blocks[$this->cfg['blocktype']]['description']) .'</span></h4>
							</div>

							<div class="adm_check">
								<span class="adm_w80">'. $txt['pmx_admstat_member'] .'</span>
								<input type="hidden" name="config[settings][stat_member]" value="0" />
								<div><input class="input_check" type="checkbox" name="config[settings][stat_member]" value="1"' .(isset($this->cfg['config']['settings']['stat_member']) && !empty($this->cfg['config']['settings']['stat_member']) ? ' checked="checked"' : ''). ' /></div>
							</div>
							<div class="adm_check">
								<span class="adm_w80">'. $txt['pmx_admstat_stats'] .'</span>
								<input type="hidden" name="config[settings][stat_stats]" value="0" />
								<div><input class="input_check" type="checkbox" name="config[settings][stat_stats]" value="1"' .(isset($this->cfg['config']['settings']['stat_stats']) && !empty($this->cfg['config']['settings']['stat_stats']) ? ' checked="checked"' : ''). ' /></div>
							</div>
							<div class="adm_check">
								<span class="adm_w80">'. $txt['pmx_admstat_users'] .'</span>
								<input type="hidden" name="config[settings][stat_users]" value="0" />
								<div><input class="input_check" type="checkbox" name="config[settings][stat_users]" value="1"' .(isset($this->cfg['config']['settings']['stat_users']) && !empty($this->cfg['config']['settings']['stat_users']) ? ' checked="checked"' : ''). ' /></div>
							</div>
							<div class="adm_check">
								<span class="adm_w80">'. $txt['pmx_admstat_spider'] .'</span>
								<input type="hidden" name="config[settings][stat_spider]" value="0" />
								<div><input class="input_check" type="checkbox" name="config[settings][stat_spider]" value="1"' .(isset($this->cfg['config']['settings']['stat_spider']) && !empty($this->cfg['config']['settings']['stat_spider']) ? ' checked="checked"' : ''). ' /></div>
							</div>
							<div class="adm_input">
								<span class="adm_w80">&nbsp;'. $txt['pmx_admstat_olheight'] .'
									<a href="', $scripturl, '?action=helpadmin;help=pmx_admstat_olheight_help" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
								</span>
								<div><input onkeyup="check_numeric(this);" size="3" type="text" name="config[settings][stat_olheight]" value="' .(isset($this->cfg['config']['settings']['stat_olheight']) ? $this->cfg['config']['settings']['stat_olheight'] : '10'). '" /></div>
							</div>
								<input type="hidden" name="config[show_sitemap]" value="0" />
							</div>
						</div>';

		// return the used classnames
		return $used_classdef;
	}
}
?>