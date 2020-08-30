<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file download_adm.php
 * Admin Systemblock download
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* @class pmxc_download_adm
* Admin Systemblock download_adm
* @see download_adm.php
*/
class pmxc_download_adm extends PortaMxC_SystemAdminBlock
{
	/**
	* AdmBlock_init().
	* Setup caching.
	*/
	function pmxc_AdmBlock_init()
	{
		$this->can_cached = 0;		// disable caching
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
		$dlboard = isset($this->cfg['config']['settings']['download_board']) ? $this->cfg['config']['settings']['download_board'] : 0;
		echo '
							<div class="cat_bar catbg_grid grid_padd">
								<h4 class="catbg catbg_grid"><span class="cat_msg_title">'. sprintf($txt['pmx_blocks_settings_title'], $this->register_blocks[$this->cfg['blocktype']]['description']) .'</span></h4>
							</div>

							<div class="adm_input">
								<span>'. $txt['pmx_download_board'] .'</span>
								<select class="adm_w90 adm_select" name="config[settings][download_board]">
									<option value="none"'. ($dlboard == 0 ? ' selected="selected"' : '') .'>'. $txt['pmx_dl_noboard'] .'</option>';

		$dlboard = isset($this->cfg['config']['settings']['download_board']) ? $this->cfg['config']['settings']['download_board'] : 0;
		foreach($this->pmx_boards as $brd)
			echo '
									<option value="'. $brd['id'] .'"'. ($brd['id'] == $dlboard ? ' selected="selected"' : '') .'>'. $brd['name'] .'</option>';

		echo '
								</select>
							</div>

							<div class="adm_input adm_select">
								<span>'. $txt['pmx_download_groups'] .'</span>
								<input type="hidden" name="config[settings][download_acs][]" value="" />
								<select class="adm_w90 notdbut" name="config[settings][download_acs][]" multiple="multiple" size="5">';

		foreach($this->pmx_groups as $grp)
			echo '
									<option value="'. $grp['id'] .'"'. (!empty($this->cfg['config']['settings']['download_acs']) && in_array($grp['id'], $this->cfg['config']['settings']['download_acs']) ? ' selected="selected"' : '') .'>'. $grp['name'] .'</option>';
		echo '
								</select>
							</div>
							<input type="hidden" name="config[show_sitemap]" value="0" />
						</div>';

		// return the used classnames
		return $this->block_classdef;
	}

	/**
	* AdmBlock_content().
	* Load the BBC Editor, to create or edit the content.
	* Returns the AdmBlock_settings
	*/
	function pmxc_AdmBlock_content()
	{
		global $context, $user_info, $boarddir, $txt;

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
		if(count($fnd) == count(explode('/', $pmxpath)))
			$filepath = '/'. $filepath;
		$_SESSION['pmx_ckfm'] = array('ALLOW' => $allow, 'FILEPATH' => $filepath);

		echo '
						<textarea name="'. $context['pmx']['htmledit']['id'] .'">'. $context['pmx']['htmledit']['content'] .'</textarea>
						<script>
							CKEDITOR.replace("'. $context['pmx']['htmledit']['id'] .'", {
								filebrowserBrowseUrl: "ckeditor/fileman/index.php",
								smiley_path: CKEDITOR.basePath +"../Smileys/'. $user_info['smiley_set'] .'/"});
						</script>
					</td>
				</tr>
				<tr>';

		// return the default settings
		return $this->pmxc_AdmBlock_settings();
	}
}
?>