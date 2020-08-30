<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file article_adm.php
 * Admin Systemblock article
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* @class pmxc_article_adm
* Admin Systemblock article_adm
* @see article_adm.php
*/
class pmxc_article_adm extends PortaMxC_SystemAdminBlock
{
	var $articles;

	/**
	* AdmBlock_init().
	* Setup caching and get the articles.
	*/
	function pmxc_AdmBlock_init()
	{
		global $pmxcFunc;

		$this->can_cached = 1;		// enable caching
		$this->articles = array();

		// get all active and approved articles
		$request = $pmxcFunc['db_query']('', '
			SELECT a.id, a.name, a.acsgrp, a.ctype, a.config, a.owner, a.active, a.created, a.updated, a.content, m.member_name
			FROM {db_prefix}portal_articles AS a
			LEFT JOIN {db_prefix}members AS m ON (a.owner = m.id_member)
			WHERE a.active > 0 AND a.approved > 0
			ORDER BY a.id',
			array()
		);

		if($pmxcFunc['db_num_rows']($request) > 0)
		{
			while($row = $pmxcFunc['db_fetch_assoc']($request))
			{
				$row['config'] = pmx_json_decode($row['config'], true);
				if(!empty($this->cfg['config']['settings']['inherit_acs']) || allowPmxGroup($row['acsgrp']))
				{
					$row['side'] = $this->cfg['side'];
					$this->articles[] = $row;
				}
			}
			$pmxcFunc['db_free_result']($request);
		}
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
							<input type="hidden" name="config[settings]" value="" />
							<input type="hidden" name="config[static_block]" value="1" />';

		// show the settings screen
		echo '
							<div class="cat_bar catbg_grid grid_padd">
								<h4 class="catbg catbg_grid">
									<span class="cat_msg_title">'. sprintf($txt['pmx_blocks_settings_title'], $this->register_blocks[$this->cfg['blocktype']]['description']) .'</span>
								</h4>
							</div>
							<div class="adm_input adm_sel">
								<span>'.$txt['pmx_artblock_arts'] .'</span>
								<select style="width:83%;" name="config[settings][article]" size="1">';

		// output articles
		foreach($this->articles as $art)
			echo '
										<option value="'. $art['name'] .'"' .(isset($this->cfg['config']['settings']['article']) && $this->cfg['config']['settings']['article'] == $art['name'] ? ' selected="selected"' : '') .'>'. $art['name'] .'</option>';

		echo '
									</select>
							</div>';

		// show mode (titelbar/frame)
		$this->cfg['config']['settings']['usedframe'] = !isset($this->cfg['config']['settings']['usedframe']) ? 'block' : $this->cfg['config']['settings']['usedframe'];
		echo '
							<div class="adm_check" style="padding-top:5px;">
								<span class="adm_w80">'. $txt['pmx_artblock_blockframe'] .'</span>
								<div><input class="input_check" type="radio" name="config[settings][usedframe]" value="block"' .(isset($this->cfg['config']['settings']['usedframe']) && $this->cfg['config']['settings']['usedframe'] == 'block' ? ' checked="checked"' : '') .' /></div>
							</div>

							<div class="adm_check">
								<span class="adm_w80">'. $txt['pmx_artblock_artframe'] .'</span>
								<div><input class="input_check" type="radio" name="config[settings][usedframe]" value="article"' .(isset($this->cfg['config']['settings']['usedframe']) && $this->cfg['config']['settings']['usedframe'] == 'article' ? ' checked="checked"' : '') .' /></div>
							</div>

							<div class="adm_check">
								<span class="adm_w80">&nbsp;'. $txt['pmx_artblock_inherit'] .'
									<a href="', $scripturl, '?action=helpadmin;help=pmx_artblock_inherithelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
								</span>
								<input type="hidden" name="config[settings][inherit_acs]" value="0" />
								<div><input class="input_check" type="checkbox" name="config[settings][inherit_acs]" value="1"' .(!empty($this->cfg['config']['settings']['inherit_acs']) ? ' checked="checked"' : '') .' /></div>
							</div>
							<input type="hidden" name="config[show_sitemap]" value="0" />
						</div>';

		// return the used classnames
		return PortaMx_getdefaultClass(false, true);  // default classdef
	}
}
?>