<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file script_adm.php
 * Admin Systemblock script
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* @class pmxc_script_adm
* Admin Systemblock script_adm
* @see script_adm.php
*/
class pmxc_script_adm extends PortaMxC_SystemAdminBlock
{
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
									<span class="cat_msg_title">'. sprintf($txt['pmx_blocks_settings_title'], $this->register_blocks[$this->cfg['blocktype']]['description']) .'</span>
								</h4>
							</div>

							<div class="adm_check">
								<span class="adm_w80">'. $txt['pmx_content_print'] .'</span>
								<input type="hidden" name="config[settings][printing]" value="0" />
								<input class="input_check" type="checkbox" name="config[settings][printing]" value="1"' .(!empty($this->cfg['config']['settings']['printing']) ? ' checked="checked"' : ''). ' />
							</div>
							<div class="adm_check">
								<span class="adm_w80">&nbsp;'. $txt['pmx_boponews_disableHSimage'] .'
									<a href="', $scripturl, '?action=helpadmin;help=pmx_disable_lightbox_help" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
								</span>
								<input type="hidden" name="config[settings][disableHSimg]" value="0" />
								<input class="input_check" type="checkbox" name="config[settings][disableHSimg]" value="1"' .(isset($this->cfg['config']['settings']['disableHSimg']) && !empty($this->cfg['config']['settings']['disableHSimg']) ? ' checked="checked"' : '').(!empty($context['pmx']['settings']['disableHS']) ? ' disabled="disabled"' : '') .' />
							</div>
							<input type="hidden" name="config[show_sitemap]" value="0" />
						</div>';

		// return the used classnames
		return $this->block_classdef;
	}

	/**
	* AdmBlock_content().
	* Open a textarea, to create or edit the content.
	* Returns the AdmBlock_settings
	*/
	function pmxc_AdmBlock_content()
	{
		global $context, $txt;

		// show the content area
		echo '
					<td valign="top" colspan="2" style="padding:4px;">
						<div class="cat_bar catbg_grid">
							<h4 class="catbg catbg_grid"><span class="cat_msg_title">'. $txt['pmx_edit_content'] .'</span></h4>
						</div>
						<textarea name="'. $context['pmx']['script']['id'] .'" id="'. $context['pmx']['script']['id'] .'" style="display:block;width:'. $context['pmx']['script']['width'] .';height:'. $context['pmx']['script']['height'] .';">'. convertSmileysToUser($context['pmx']['script']['value']) .'</textarea>
					</td>
				</tr>
				<tr>';

		// return the default settings
		return $this->pmxc_AdmBlock_settings();
	}
}
?>