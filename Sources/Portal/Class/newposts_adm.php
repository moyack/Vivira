<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file newposts_adm.php
 * Admin Systemblock newposts
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* @class pmxc_newposts_adm
* Admin Systemblock newposts_adm
* @see newposts_adm.php
*/
class pmxc_newposts_adm extends PortaMxC_SystemAdminBlock
{
	/**
	* AdmBlock_init().
	* Setup caching and returns the language file name.
	*/
	function pmxc_AdmBlock_init()
	{
		$this->block_classdef = PortaMx_getdefaultClass(true);	// extended classdef
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

		// define numeric vars to check
		echo '
							<input type="hidden" name="check_num_vars[]" value="[config][settings][total], 10" />';

		// show the settings screen
		echo '
							<div class="cat_bar catbg_grid grid_padd">
								<h4 class="catbg catbg_grid"><span class="cat_msg_title">'. sprintf($txt['pmx_blocks_settings_title'], $this->register_blocks[$this->cfg['blocktype']]['description']) .'</span></h4>
							</div>

							<div class="adm_input adm_sel">
								<span>'. $txt['pmx_postnews_boards'] .'</span>
								<select class="adm_w90 adm_sel notdbut" name="config[settings][boards][]" multiple="multiple" size="4">';

		$boards = isset($this->cfg['config']['settings']['boards']) ? $this->cfg['config']['settings']['boards'] : array();
		foreach($this->pmx_boards as $brd)
			echo '
									<option value="'. $brd['id'] .'"'. (in_array($brd['id'], $boards) ? ' selected="selected"' : '') .'>'. $brd['name'] .'</option>';

		echo '
								</select>
							</div>

							<div class="adm_input">
								<span class="adm_w80">'. $txt['pmx_multbonews'] .'</span>
								<div><input onkeyup="check_numeric(this);" size="2" type="text" name="config[settings][total]" value="' .(isset($this->cfg['config']['settings']['total']) ? $this->cfg['config']['settings']['total'] : '10'). '" /></div>
							</div>

							<div class="adm_check">
								<span class="adm_w80">'. $txt['pmx_boponews_postinfo'] .'</span>
								<input type="hidden" name="config[settings][postinfo]" value="0" />
								<div><input class="input_check" type="checkbox" name="config[settings][postinfo]" value="1"' .(!empty($this->cfg['config']['settings']['postinfo']) ? ' checked="checked"' : ''). ' /></div>
							</div>

							<div class="adm_check">
								<span class="adm_w80">'. $txt['pmx_boponews_postviews'] .'</span>
								<input type="hidden" name="config[settings][postviews]" value="0" />
								<div><input class="input_check" type="checkbox" name="config[settings][postviews]" value="1"' .(!empty($this->cfg['config']['settings']['postviews']) ? ' checked="checked"' : ''). ' /></div>
							</div>

							<div class="adm_input">
								<span class="adm_w80">&nbsp;'. $txt['pmx_boponews_page'] .'
									<a href="', $scripturl, '?action=helpadmin;help=pmx_pageindex_help" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
								</span>
								<div><input onkeyup="check_numeric(this);" size="2" type="text" name="config[settings][onpage]" value="' .(isset($this->cfg['config']['settings']['onpage']) ? $this->cfg['config']['settings']['onpage'] : ''). '" /></div>
							</div>

							<div class="adm_check">
								<span class="adm_w80">&nbsp;'. $txt['pmx_pageindex_pagetop'] .'
									<a href="', $scripturl, '?action=helpadmin;help=pmx_pageindex_tophelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
								</span>
								<input type="hidden" name="config[settings][pgidxtop]" value="0" />
								<div><input class="input_check" type="checkbox" name="config[settings][pgidxtop]" value="1"' .(isset($this->cfg['config']['settings']['pgidxtop']) && !empty($this->cfg['config']['settings']['pgidxtop']) ? ' checked="checked"' : ''). ' /></div>
							</div>

							<div class="adm_input">
								<span class="adm_w80">&nbsp;'. sprintf($txt['pmx_adm_teaser'], $txt['pmx_teasemode'][intval(!empty($context['pmx']['settings']['teasermode']))]) .'
									<a href="', $scripturl, '?action=helpadmin;help=pmx_adm_teasehelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
								</span>
								<div><input onkeyup="check_numeric(this);" size="2" type="text" name="config[settings][teaser]" value="' .(isset($this->cfg['config']['settings']['teaser']) ? $this->cfg['config']['settings']['teaser'] : ''). '" /></div>
							</div>

							<div class="adm_input">
								<span class="adm_w80">&nbsp;'. $txt['pmx_boponews_rescale'] .'
									<a href="', $scripturl, '?action=helpadmin;help=pmx_boponews_rescalehelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
								</span>
								<div><input onkeyup="check_numeric(this,\'%\');" style="width:75px;" type="text" name="config[settings][rescale]" value="' .(isset($this->cfg['config']['settings']['rescale']) ? $this->cfg['config']['settings']['rescale'] : ''). '" /></div>
							</div>

							<div class="adm_check">
								<span class="adm_w80">'. $txt['pmx_boponews_showthumbs'] .'</span>
								<input type="hidden" name="config[settings][thumbs]" value="0" />
								<div><input class="input_check" type="checkbox" name="config[settings][thumbs]" value="1"' .(isset($this->cfg['config']['settings']['thumbs']) && !empty($this->cfg['config']['settings']['thumbs']) ? ' checked="checked"' : ''). ' /></div>
							</div>

							<div class="adm_input">
								<span class="adm_w80">&nbsp;'. $txt['pmx_boponews_thumbcnt'] .'
									<a href="', $scripturl, '?action=helpadmin;help=pmx_boponews_thumbcnthelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
								</span>
								<div><input onkeyup="check_numeric(this);" size="2" type="text" name="config[settings][thumbcnt]" value="' .(isset($this->cfg['config']['settings']['thumbcnt']) ? $this->cfg['config']['settings']['thumbcnt'] : ''). '" /></div>
							</div>

							<div class="adm_input">
								<span class="adm_w80">&nbsp;'. $txt['pmx_boponews_thumbsize'] .'
									<a href="', $scripturl, '?action=helpadmin;help=pmx_boponews_thumbsizehelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
								</span>
								<div><input onkeyup="check_numeric(this,\',\');" style="width:75px;" type="text" name="config[settings][thumbsize]" value="' .(isset($this->cfg['config']['settings']['thumbsize']) ? $this->cfg['config']['settings']['thumbsize'] : ''). '" /></div>
							</div>

							<div class="adm_check">
								<span class="adm_w80">&nbsp;'. $txt['pmx_boponews_hidethumbs'] .'
									<a href="', $scripturl, '?action=helpadmin;help=pmx_boponews_hidethumbshelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
								</span>
								<input type="hidden" name="config[settings][hidethumbs]" value="0" />
								<div><input class="input_check" type="checkbox" name="config[settings][hidethumbs]" value="1"' .(isset($this->cfg['config']['settings']['hidethumbs']) && !empty($this->cfg['config']['settings']['hidethumbs']) ? ' checked="checked"' : ''). ' /></div>
							</div>

							<div class="adm_check">
								<span class="adm_w80">'. $txt['pmx_boponews_split'] .'</span>
								<input type="hidden" name="config[settings][split]" value="0" />
								<div><input class="input_check" type="checkbox" name="config[settings][split]" value="1"' .(isset($this->cfg['config']['settings']['split']) && !empty($this->cfg['config']['settings']['split']) ? ' checked="checked"' : ''). ' /></div>
							</div>

							<div class="adm_check">
								<span class="adm_w80">'. $txt['pmx_boponews_equal'] .'</span>
								<input type="hidden" name="config[settings][equal]" value="0" />
								<div><input class="input_check" type="checkbox" name="config[settings][equal]" value="1"' .(isset($this->cfg['config']['settings']['equal']) && !empty($this->cfg['config']['settings']['equal']) ? ' checked="checked"' : ''). ' /></div>
							</div>

							<div class="adm_check">
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
}
?>