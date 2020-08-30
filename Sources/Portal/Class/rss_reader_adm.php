<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file rss_reader_adm.php
 * Admin Systemblock rss_reader
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* @class pmxc_rss_reader_adm
* Admin Systemblock rss_reader_adm
* @see rss_reader_adm.php
*/
class pmxc_rss_reader_adm extends PortaMxC_SystemAdminBlock
{
	/**
	* AdmBlock_init().
	* Setup caching and classdef.
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
							<input type="hidden" name="check_num_vars[]" value="[config][settings][rssmaxitems], \'\'" />
							<input type="hidden" name="check_num_vars[]" value="[config][settings][rsstimeout], \'5\'" />';

		// show the settings screen
		echo '
							<div class="cat_bar catbg_grid grid_padd">
								<h4 class="catbg catbg_grid">
								<span class="cat_msg_title">'. sprintf($txt['pmx_blocks_settings_title'], $this->register_blocks[$this->cfg['blocktype']]['description']) .'</span>
								</h4>
							</div>

							<div class="adm_input adm_sel">
								<span class="adm_w80" style="margin-bottom:5px;">&nbsp;'. $txt['pmx_rssreader_url'] .'
									<a href="', $scripturl, '?action=helpadmin;help=pmx_rssreader_urlhelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
								</span>
								<input class="adm_w90" type="text" name="config[settings][rssfeedurl]" value="' .(!empty($this->cfg['config']['settings']['rssfeedurl']) ? $this->cfg['config']['settings']['rssfeedurl'] : ''). '" />
							</div>

							<div class="adm_input">
								<span class="adm_w80">&nbsp;'. $txt['pmx_rssreader_timeout'] .'
									<a href="', $scripturl, '?action=helpadmin;help=pmx_rssreader_timeouthelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
								</span>
								<div><input onkeyup="check_numeric(this);" size="2" style="width:10%;" type="text" name="config[settings][rsstimeout]" value="' .(!empty($this->cfg['config']['settings']['rsstimeout']) ? $this->cfg['config']['settings']['rsstimeout'] : '5'). '" /></div>
							</div>

							<div class="adm_check">
								<span class="adm_w80">&nbsp;'. $txt['pmx_rssreader_usettl'] .'
									<a href="', $scripturl, '?action=helpadmin;help=pmx_rssreader_usettlhelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
								</span>
								<input type="hidden" name="config[settings][usettl]" value="0" />
								<div><input class="input_check" type="checkbox" name="config[settings][usettl]" value="1"' .(!empty($this->cfg['config']['settings']['usettl']) ? ' checked="checked"' : ''). ' /></div>
							</div>

							<div class="adm_input">
								<span class="adm_w80">&nbsp;'. $txt['pmx_rssreader_maxitems'] .'
									<a href="', $scripturl, '?action=helpadmin;help=pmx_rssmaxitems_help" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
								</span>
								<div><input onkeyup="check_numeric(this);" size="2" style="width:10%;" type="text" name="config[settings][rssmaxitems]" value="' .(!empty($this->cfg['config']['settings']['rssmaxitems']) ? $this->cfg['config']['settings']['rssmaxitems'] : ''). '" /></div>
							</div>

							<div class="adm_input">
								<span class="adm_w80">'. $txt['pmx_rssreader_page'] .'</span>
								<div><input onkeyup="check_numeric(this);" size="2" style="width:10%;" type="text" name="config[settings][onpage]" value="' .(isset($this->cfg['config']['settings']['onpage']) ? $this->cfg['config']['settings']['onpage'] : ''). '" /></div>
							</div>

							<div class="adm_check">
								<span class="adm_w80">&nbsp;'. $txt['pmx_pageindex_pagetop'] .'
									<a href="', $scripturl, '?action=helpadmin;help=pmx_pageindex_tophelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
								</span>
								<input type="hidden" name="config[settings][pgidxtop]" value="0" />
								<div><input class="input_check" type="checkbox" name="config[settings][pgidxtop]" value="1"' .(isset($this->cfg['config']['settings']['pgidxtop']) && !empty($this->cfg['config']['settings']['pgidxtop']) ? ' checked="checked"' : ''). ' /></div>
							</div>

							<div class="adm_check">
								<span class="adm_w80">&nbsp;'. $txt['pmx_rssreader_cont_encode'] .'
									<a href="', $scripturl, '?action=helpadmin;help=pmx_rssreader_cont_encodehelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
								</span>
								<input type="hidden" name="config[settings][cont_encode]" value="0" />
								<div><input class="input_check" type="checkbox" name="config[settings][cont_encode]" value="1"' .(!empty($this->cfg['config']['settings']['cont_encode']) ? ' checked="checked"' : ''). ' /></div>
							</div>

							<div class="adm_check">
								<span class="adm_w80">'. $txt['pmx_rssreader_split'] .'</span>
								<input type="hidden" name="config[settings][split]" value="0" />
								<div><input class="input_check" type="checkbox" name="config[settings][split]" value="1"' .(!empty($this->cfg['config']['settings']['split']) ? ' checked="checked"' : ''). ' /></div>
							</div>

							<div class="adm_check">
								<span class="adm_w80">'. $txt['pmx_boponews_equal'] .'</span>
								<input type="hidden" name="config[settings][equal]" value="0" />
								<div><input class="input_check" type="checkbox" name="config[settings][equal]" value="1"' .(isset($this->cfg['config']['settings']['equal']) && !empty($this->cfg['config']['settings']['equal']) ? ' checked="checked"' : ''). ' /></div>
							</div>

							<div class="adm_check">
								<span class="adm_w80">&nbsp;'. $txt['pmx_rssreader_showhead'] .'
									<a href="', $scripturl, '?action=helpadmin;help=pmx_rssreader_help" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
								</span>
								<input type="hidden" name="config[settings][showhead]" value="0" />
								<div><input class="input_check" type="checkbox" name="config[settings][showhead]" value="1"' .(!empty($this->cfg['config']['settings']['showhead']) ? ' checked="checked"' : ''). ' /></div>
							</div>

							<div class="adm_input">
								<span class="adm_w30">'. $txt['pmx_rssreader_name'] .'</span>
								<input class="adm_w60" style="float:right;margin-right:10%;" type="text" name="config[settings][rssfeed_name]" value="' .(!empty($this->cfg['config']['settings']['rssfeed_name']) ? $this->cfg['config']['settings']['rssfeed_name'] : ''). '" />
							</div>

							<div class="adm_input">
								<span class="adm_w30">'. $txt['pmx_rssreader_link'] .'</span>
								<input class="adm_w60" style="float:right;margin-right:10%;" type="text" name="config[settings][rssfeed_link]" value="' .(!empty($this->cfg['config']['settings']['rssfeed_link']) ? $this->cfg['config']['settings']['rssfeed_link'] : ''). '" />
							</div>

							<div class="adm_input">
								<span class="adm_w30">'. $txt['pmx_rssreader_desc'] .'</span>
								<input class="adm_w60" style="float:right;margin-right:10%;" type="text" name="config[settings][rssfeed_desc]" value="' .(!empty($this->cfg['config']['settings']['rssfeed_desc']) ? $this->cfg['config']['settings']['rssfeed_desc'] : ''). '" />
							</div>

							<div class="adm_input" style="padding-top:2px;">
								<span class="adm_w80">&nbsp;'. sprintf($txt['pmx_adm_teaser'], $txt['pmx_teasemode'][intval(!empty($context['pmx']['settings']['teasermode']))]) .'
									<a href="', $scripturl, '?action=helpadmin;help=pmx_adm_teasehelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
								</span>
								<div><input onkeyup="check_numeric(this);" size="2" style="width:10%;" type="text" name="config[settings][teaser]" value="' .(isset($this->cfg['config']['settings']['teaser']) ? $this->cfg['config']['settings']['teaser'] : ''). '" /></div>
							</div>

							<div class="adm_check">
								<span class="adm_w80">&nbsp;'. $txt['pmx_rssreader_delimages'] .'
									<a href="', $scripturl, '?action=helpadmin;help=pmx_rssreader_delimagehelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
								</span>
								<input type="hidden" name="config[settings][delimage]" value="0" />
								<div><input class="input_check" type="checkbox" name="config[settings][delimage]" value="1"' .(!empty($this->cfg['config']['settings']['delimage']) ? ' checked="checked"' : ''). ' /></div>
							</div>
							<input type="hidden" name="config[show_sitemap]" value="0" />
						</div>';

		// return the used classnames
		return $this->block_classdef;
	}
}
?>