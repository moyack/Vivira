<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file html_adm.php
 * Admin Systemblock html
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* @class pmxc_html_adm
* Admin Systemblock html_adm
* @see html_adm.php
*/
class pmxc_html_adm extends PortaMxC_SystemAdminBlock
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
							<div class="adm_check">
								<span class="adm_w80">&nbsp;'. $txt['pmx_html_teaser'] .'
									<a href="', $scripturl, '?action=helpadmin;help=pmx_html_teasehelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
								</span>
								<div>
									<input type="hidden" name="config[settings][teaser]" value="0" />
									<input class="input_check" type="checkbox" name="config[settings][teaser]" value="1"' .(!empty($this->cfg['config']['settings']['teaser']) ? ' checked="checked"' : ''). ' />
								</div>
							</div>
							<div class="adm_check">
								<span class="adm_w80">'. $txt['pmx_content_print'] .'</span>
								<div>
									<input type="hidden" name="config[settings][printing]" value="0" />
									<input class="input_check" type="checkbox" name="config[settings][printing]" value="1"' .(!empty($this->cfg['config']['settings']['printing']) ? ' checked="checked"' : ''). ' />
								</div>
							</div>
							<div class="adm_check">
								<span class="adm_w80">&nbsp;'. $txt['pmx_boponews_disableHSimage'] .'
									<a href="', $scripturl, '?action=helpadmin;help=pmx_disable_lightbox_help" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
								</span>
								<input type="hidden" name="config[settings][disableHSimg]" value="0" />
								<div><input class="input_check" type="checkbox" name="config[settings][disableHSimg]" value="1"' .(isset($this->cfg['config']['settings']['disableHSimg']) && !empty($this->cfg['config']['settings']['disableHSimg']) ? ' checked="checked"' : '').(!empty($context['pmx']['settings']['disableHS']) ? ' disabled="disabled"' : '') .' /></div>
							</div>
							<input type="hidden" name="config[show_sitemap]" value="0" />
						</div>';

		// return the used classnames
		return $this->block_classdef;
	}

	/**
	* AdmBlock_content().
	* Load the WYSIWYG Editor, to create or edit the content.
	* Returns the AdmBlock_settings
	*/
	function pmxc_AdmBlock_content()
	{
		global $context, $txt, $user_info, $boarddir;

		// show the content area
		echo '
					<td valign="top" colspan="2" style="padding:4px;">
						<div class="cat_bar catbg_grid">
							<h4 class="catbg catbg_grid"><span class="cat_msg_title">'. $txt['pmx_edit_content'] .'</span></h4>
						</div>';

		// show the editor
		$allow = allowPmx('pmx_admin') || allowPmx('pmx_blocks');
		$fnd = explode('/', str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']));
		$pmxpath = str_replace('\\', '/', $boarddir);
		foreach($fnd as $key => $val) { $fnd[$key] = $val; $rep[] = ''; }
		$filepath = trim(str_replace($fnd, $rep, $pmxpath), '/') .'/CustomImages';
		if(count($fnd) != count(explode('/', $pmxpath)))
			$filepath = '/'. $filepath;
		$_SESSION['pmx_ckfm'] = array('ALLOW' => $allow, 'FILEPATH' => str_replace('//', '/', $filepath));

		// the editor language (we have en & de)
		if(in_array($txt['lang_dictionary'], array('en', 'de', 'ru')))
			$edLang = $txt['lang_dictionary'];
		else
			$edLang = 'en';

		// Change the editor config ..
		echo '
						<textarea name="'. $context['pmx']['htmledit']['id'] .'">'. convertSmileysToUser($context['pmx']['htmledit']['content']) .'</textarea>
						<script>
							CKEDITOR.replace("'. $context['pmx']['htmledit']['id'] .'", {
								filebrowserBrowseUrl: "ckeditor/fileman/index.php",
								smiley_path: CKEDITOR.basePath +"../Smileys/'. $user_info['smiley_set'] .'/",
								language: "'. $edLang .'"});
						</script>
					</td>
				</tr>
				<tr>';

		// return the default settings
		return $this->pmxc_AdmBlock_settings();
	}
}
?>