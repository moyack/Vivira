<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file php_adm.php
 * Admin Systemblock php
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* @class pmxc_php_adm
* Admin Systemblock php_adm
* @see php_adm.php
*/
class pmxc_php_adm extends PortaMxC_SystemAdminBlock
{
	/**
	* AdmBlock_init().
	* Setup caching.
	*/
	function pmxc_AdmBlock_init()
	{
		global $context;

		$this->can_cached = 0;			// disable caching

		addInlineJavascript(str_replace("\n", "\n\t", PortaMx_compressJS('
		function pack(chrstr) {
			var hexstr = "";
			for(var i = 0; i < chrstr.length; i++)
			{
				var c = chrstr.charCodeAt(i);
				var h = "00" + c.toString(16);
				hexstr += h.substr(h.length-2);
			}
			return hexstr;
		}
		function php_syntax(elmid)
		{
			document.getElementById("check_" + elmid).innerHTML = "<img onclick=\"Hide_SyntaxCheck(this.parentNode)\" style=\"padding-left:10px;cursor:pointer;\" alt=\"close\" src=\"'. $context['pmx_imageurl'] .'cross.png\" class=\"pmxright\" />";
			document.getElementById("check_" + elmid).className = "info_frame";
			var postData = {};
			postData["value"] = pack(document.getElementById(elmid).value);
			var result = pmxXMLpost(pmx_scripturl +"?jscook&mode=syntax&xml", postData);
			result = result.replace(/@elm@/, elmid);
			result = result.replace(/<br \/>/gi, "");
			temp = result.replace(/<b>/gi, "");
			temp = temp.replace(/<\/b>/gi, "");
			var patt = /(on\s+line\D+)(\d+)/;
			res = patt.exec(temp);
			LineNo = "";
			if(res)
				LineNo = res[2];
			if(result.indexOf("in <b>") != -1)
			{
				result = result.substring(result.indexOf("<b>Parse error</b>:  "), result.indexOf("in <b>"));
				result = result.replace(/<b>/gi, "");
				result = result + " on line " + LineNo;
			}
			document.getElementById("check_" + elmid).innerHTML = document.getElementById("check_" + elmid).innerHTML + result;
			Show_help("check_" + elmid);

			php_showerrline(elmid, LineNo);
		}
		function php_showerrline(elmid, errLine)
		{
			if(errLine != "" && errLine != "0" && !isNaN(errLine))
			{
				errLine = parseInt(errLine);
				var lines = document.getElementById(elmid).value.split("\n");
				var count = 0;
				for(var i = 0; i < errLine -1; i++)
					count += lines[i].length +1;

				if(document.getElementById(elmid).setSelectionRange)
				{
					document.getElementById(elmid).focus();
					document.getElementById(elmid).setSelectionRange(count, count+lines[i].length);
				}
				else if(document.getElementById(elmid).createTextRange)
				{
					range=document.getElementById(elmid).createTextRange();
					range.collapse(true);
					range.moveStart("character", count);
					range.moveEnd("character", count+lines[i].length);
					range.select();
				}
			}
		}')));
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
							<input type="hidden" name="config[settings]" value="" />
							<div class="cat_bar catbg_grid grid_padd">
								<h4 class="catbg catbg_grid"><span class="cat_msg_title">'. sprintf($txt['pmx_blocks_settings_title'], $this->register_blocks[$this->cfg['blocktype']]['description']) .'</span></h4>
							</div>
							<div class="adm_check">
								<span class="adm_w80">'. $txt['pmx_content_print'] .'</span>
								<input type="hidden" name="config[settings][printing]" value="0" />
								<input class="input_check" type="checkbox" name="config[settings][printing]" value="1"' .(!empty($this->cfg['config']['settings']['printing']) ? ' checked="checked"' : ''). ' />
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
		global $context, $options, $txt, $boardurl;

		// show the content area
		$options['collapse_phpinit'] = empty($context['pmx']['phpInit']['havecont']);

		echo '
					<td valign="top" colspan="2" style="padding:4px;">
						<div class="cat_bar catbg_grid">
							<h4 class="catbg catbg_grid">
								<span style="float:right;display:block;margin-top:-2px;">
									<img onclick="php_syntax(\''. $context['pmx']['phpShow']['id'] .'\')" style="padding:3px 5px 3px 10px;cursor:pointer;" alt="Syntax check" title="'. $txt['pmx_check_phpsyntax'] .'" src="'. $context['pmx_imageurl'] .'syntaxcheck.png" class="pmxright" />
								</span>
								<span class="cat_msg_title">'. $txt['pmx_edit_content'] .'
								<span id="upshrinkPHPinitCont"'. (empty($options['collapse_phpinit']) ? '' : ' style="display:none;"') .'>'. $txt['pmx_edit_content_show'] .'</span></span>
							</h4>
						</div>
						<div id="check_'. $context['pmx']['phpShow']['id'] .'" class="info_frame" style="line-height:1.4em;margin:1px 0;">
							<img onclick="Hide_SyntaxCheck(this.parentNode)" style="padding-left:10px;cursor:pointer;" alt="close" src="'. $context['pmx_imageurl'] .'cross.png" class="pmxright" />
						</div>

						<textarea name="'. $context['pmx']['phpShow']['id'] .'" id="'. $context['pmx']['phpShow']['id'] .'" style="display:block;width:'. $context['pmx']['phpShow']['width'] .';height:'. $context['pmx']['phpShow']['height'] .';">'. $context['pmx']['phpShow']['value'] .'</textarea>

						<div class="plainbox info_text" style="margin-top:5px;margin-right:0px;padding:5px 0 7px 0;display:block;">
							<div class="normaltext" style="margin:0 10px;">
							'.(empty($context['pmx']['phpInit']['havecont']) ? '<span style="margin-top:0px;margin-right:-4px;" id="upshrinkPHPshowImg" class="floatleft '.
									(empty($options['collapse_visual']) ? 'toggle_up" align="bottom"' : 'toggle_down" align="bottom"') .' title="'.
									(empty($options['collapse_visual']) ? $txt['pmx_collapse'] : $txt['pmx_expand']) . $txt['pmx_php_partblock'] .'">
								</span>' : '') .'
								<span style="padding-left:10px;">'. $txt['pmx_php_partblock_note'] .'
									<img class="info_toggle" onclick=\'Toggle_help("pmxPHPH01")\' src="'. $context['pmx_imageurl'] .'helptopics.png" alt="*" title="'. $txt['pmx_information_icon'] .'" style="vertical-align: -3px;" />
								</span>
							</div>
							<div id="pmxPHPH01" style="display:none; margin:4px 10px 0;">'. $txt['pmx_php_partblock_help'] .'</div>
						</div>

						<div id="upshrinkPHPshowCont"' .(empty($options['collapse_phpinit']) ? '' : ' style="display:none;"') .'>
							<div class="cat_bar catbg_grid">
								<h4 class="catbg catbg_grid">
									<span style="float:right;display:block;margin-top:-2px;">
										<img onclick="php_syntax(\''. $context['pmx']['phpInit']['id'] .'\')" style="padding:3px 5px 3px 10px;cursor:pointer;" title="'. $txt['pmx_check_phpsyntax'] .'" alt="Syntax check" src="'. $context['pmx_imageurl'] .'syntaxcheck.png" class="pmxright" />
									</span>
									<span class="cat_msg_title">'. $txt['pmx_edit_content'] . $txt['pmx_edit_content_init'] .'</span>
								</h4>
							</div>
							<div id="check_'. $context['pmx']['phpInit']['id'] .'" class="info_frame" style="line-height:1.4em;margin:1px 0;">
								<img onclick="Hide_SyntaxCheck(this.parentNode)" style="padding-left:10px;cursor:pointer;" alt="close" src="'. $context['pmx_imageurl'] .'cross.png" class="pmxright" />
							</div>

							<textarea name="'. $context['pmx']['phpInit']['id'] .'" id="'. $context['pmx']['phpInit']['id'] .'" style="display:block;width:'. $context['pmx']['phpInit']['width'] .';height:'. $context['pmx']['phpInit']['height'] .';">'. $context['pmx']['phpInit']['value'] .'</textarea>

						</div>';

		if(empty($context['pmx']['phpInit']['havecont']))
			addInlineJavascript("\t". str_replace("\n", "\n\t", PortaMx_compressJS('
						var upshrinkPHPshow = new pmxc_Toggle({
							bToggleEnabled: true,
							bCurrentlyCollapsed: '. (empty($options['collapse_phpinit']) ? 'false' : 'true') .',
							aSwappableContainers: [
								\'upshrinkPHPshowCont\',
								\'upshrinkPHPinitCont\'
							],
							aSwapImages: [
								{
									sId: \'upshrinkPHPshowImg\',
									altCollapsed: '. JavaScriptEscape($txt['pmx_expand'] . $txt['pmx_php_partblock']) .',
									altExpanded: '. JavaScriptEscape($txt['pmx_collapse'] . $txt['pmx_php_partblock']) .'
								}
							],
							oCookieOptions: {
									bUseCookie: false
								}
						});')), true);

		echo '
					</td>
				</tr>
				<tr>';

		// return the default settings
		return $this->pmxc_AdmBlock_settings();
	}
}
?>