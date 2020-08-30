<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file bbc_script_adm.php
 * Admin Systemblock BBC_SCRIPT
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

global $context, $txt;

/**
* @class pmxc_bbc_script_adm
* Admin Systemblock BBC_SCRIPT
* @see bbc_script_adm.php
*/
class pmxc_bbc_script_adm extends PortaMxC_SystemAdminBlock
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
								<h4 class="catbg catbg_grid"><span class="cat_msg_title">'. sprintf($txt['pmx_blocks_settings_title'], $this->register_blocks[$this->cfg['blocktype']]['description']) .'</span></h4>
							</div>

							<div class="adm_check" style="min-height:25px;padding-top:0;">
								<span class="adm_w80">'. $txt['pmx_content_print'] .'</span>
								<input type="hidden" name="config[settings][printing]" value="0" />
								<div><input class="input_check" type="checkbox" name="config[settings][printing]" value="1"' .(!empty($this->cfg['config']['settings']['printing']) ? ' checked="checked"' : ''). ' /></div>
							</div>
							<div class="adm_check" style="min-height:25px;">
								<span class="adm_w80">&nbsp;'. $txt['pmx_boponews_disableHSimage'] .'
									<a href="', $scripturl, '?action=helpadmin;help=pmx_disable_lightbox_help" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
								</span>
								<input type="hidden" name="config[settings][disableHSimg]" value="0" />
								<div><input class="input_check" type="checkbox" name="config[settings][disableHSimg]" value="1"' .(isset($this->cfg['config']['settings']['disableHSimg']) && !empty($this->cfg['config']['settings']['disableHSimg']) ? ' checked="checked"' : '').(!empty($context['pmx']['settings']['disableHS']) ? ' disabled="disabled"' : '') .' /></div>
							</div>
							<div class="adm_check">
								<span class="adm_w80">&nbsp;'. $txt['pmx_boponews_disableYoutube'] .'
									<a href="', $scripturl, '?action=helpadmin;help=pmx_boponews_disableYoutubehelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
								</span>
								<input type="hidden" name="config[settings][disableYoutube]" value="0" />
								<div><input class="input_check" type="checkbox" name="config[settings][disableYoutube]" value="1"' .(isset($this->cfg['config']['settings']['disableYoutube']) && !empty($this->cfg['config']['settings']['disableYoutube']) ? ' checked="checked"' : '') .' /></div>
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
		global $context, $txt;

		// show the content area
		echo '
					<td align="top" colspan="2" style="padding:4px;">
						<div class="cat_bar catbg_grid" style="margin-right:1px;">
							<h4 class="catbg catbg_grid"><span class="cat_msg_title">'. $txt['pmx_edit_content'] .'</span></h4>
						</div>
						<input type="hidden" id="smileyset" value="PortaMx" />
						<div id="bbcBox_message"></div>
						<div id="smileyBox_message"></div>
						<div id="tempcont" style="display:none"></div>
						<div class="red_content">', template_control_richedit($context['pmx']['editorID'], 'smileyBox_message', 'bbcBox_message'), '</div>
						<script>
							var oEditorID = "', $context['pmx']['editorID'] ,'";
							var oEditorObject = oEditorHandle_', $context['pmx']['editorID'], ';
						</script>
					</td>
				</tr>
				<tr>';

		// return the default settings
		return $this->pmxc_AdmBlock_settings();
	}
}
?>