<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file fader_adm.php
 * Admin Systemblock FADER
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

global $context, $txt;

/**
* @class pmxc_fader_adm
* Admin Systemblock FADER
* @see fader_adm.php
*/
class pmxc_fader_adm extends PortaMxC_SystemAdminBlock
{
	/**
	* AdmBlock_init().
	* Setup caching and classdef.
	*/
	function pmxc_AdmBlock_init()
	{
		$this->block_classdef = PortaMx_getdefaultClass(false);	// default classdef
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

		// define the settings options
		echo '
					<td class="pmxfloattd">
						<div class="bmcustheight">
							<input type="hidden" name="config[settings]" value="" />';

		// show the settings screen
		echo '
							<div class="cat_bar catbg_grid grid_padd">
								<h4 class="catbg catbg_grid">
									<a href="', $scripturl, '?action=helpadmin;help=pmx_fader_timehelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
									<span class="cat_msg_title">'. sprintf($txt['pmx_blocks_settings_title'], $this->register_blocks[$this->cfg['blocktype']]['description']) .'</span>
								</h4>
							</div>
	
							<div class="adm_input">
								<span class="adm_w60">'. $txt['pmx_fader_uptime'] .'</span>
								<div><input onkeyup="check_numeric(this, \'.\');" size="6" type="text" name="config[settings][uptime]" value="' .(!empty($this->cfg['config']['settings']['uptime']) ? $this->cfg['config']['settings']['uptime'] : '2.2'). '" />&nbsp;'. $txt['pmx_fader_units'] .'</div>
							</div>
							<div class="adm_input">
								<span class="adm_w60">'. $txt['pmx_fader_downtime'] .'</span>
								<div><input onkeyup="check_numeric(this, \'.\');" size="6" type="text" name="config[settings][downtime]" value="' .(!empty($this->cfg['config']['settings']['downtime']) ? $this->cfg['config']['settings']['downtime'] : '1.8'). '" />&nbsp;'. $txt['pmx_fader_units'] .'</div>
							</div>
							<div class="adm_input">
								<span class="adm_w60">'. $txt['pmx_fader_holdtime'] .'</span>
								<div><input onkeyup="check_numeric(this, \'.\');" size="6" type="text" name="config[settings][holdtime]" value="' .(!empty($this->cfg['config']['settings']['holdtime']) ? $this->cfg['config']['settings']['holdtime'] : '3.5'). '" />&nbsp;'. $txt['pmx_fader_units'] .'</div>
							</div>
							<input type="hidden" name="config[show_sitemap]" value="0" />
						</div>';

		// return the used classnames
		return $this->block_classdef;
	}

	/**
	* AdmBlock_content().
	* Open a the richtext editor, to create or edit the content.
	* Returns the AdmBlock_settings
	*/
	function pmxc_AdmBlock_content()
	{
		global $context, $scripturl, $txt;

		// show the content area
		echo '
					<td valign="top" colspan="2" style="padding:4px;">
						<div class="cat_bar catbg_grid">
							<h4 class="catbg catbg_grid">
								<a href="', $scripturl, '?action=helpadmin;help=pmx_fader_content_help" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
								<span class="cat_msg_title">'. $txt['pmx_fader_content'] .'</span>
							</h4>
						</div>
						<textarea name="'. $context['pmx']['script']['id'] .'" id="'. $context['pmx']['script']['id'] .'" style="display:block;width:'. $context['pmx']['script']['width'] .';height:'. $context['pmx']['script']['height'] .';">'. $context['pmx']['script']['value'] .'</textarea>
					</td>
				</tr>
				<tr>';

		// return the default settings
		return $this->pmxc_AdmBlock_settings();
	}
}
?>