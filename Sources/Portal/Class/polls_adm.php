<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file polls_adm.php
 * Admin Systemblock polls
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* @class pmxc_polls_adm
* Admin Systemblock polls_adm
* @see polls_adm.php
*/
class pmxc_polls_adm extends PortaMxC_SystemAdminBlock
{
	var $pmx_polls;

	/**
	* AdmBlock_init().
	* get all available Polls
	* Setup caching and exist polls.
	*/
	function pmxc_AdmBlock_init()
	{
		global $modSettings, $pmxcFunc;

		// get all Polls
		$this->pmx_polls = array();

		$request = $pmxcFunc['db_query']('', '
				SELECT t.id_poll, p.question, p.voting_locked, p.expire_time
				FROM {db_prefix}topics as t
				LEFT JOIN {db_prefix}polls as p on (t.id_poll = p.id_poll)
				WHERE t.id_poll > 0'. (!empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0 ? ' AND t.id_board != {int:recyleboard}' : '') .'
				ORDER BY p.id_poll DESC',
			array(
				'recyleboard' => $modSettings['recycle_board'],
			)
		);
		while($row = $pmxcFunc['db_fetch_assoc']($request))
			$this->pmx_polls[$row['id_poll']] = array(
				'question' => $row['question'],
				'locked' => !empty($row['voting_locked']),
				'expired' => !empty($row['expire_time']) && $row['expire_time'] < time(),
			);
		$pmxcFunc['db_free_result']($request);

		$this->can_cached = 1;		// enable cache
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
		$used_classdef['questiontext'] = array(
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
							<input type="hidden" name="config[settings]" value="" />
							<input type="hidden" name="content" value="" />';

		// show the settings screen
		echo '
							<div class="cat_bar catbg_grid grid_padd">
								<h4 class="catbg catbg_grid"><span class="cat_msg_title">'. sprintf($txt['pmx_blocks_settings_title'], $this->register_blocks[$this->cfg['blocktype']]['description']) .'</span></h4>
							</div>

							<div class="adm_input">
								<span>&nbsp;'. $txt['pmx_select_polls'] .'
									<a href="', $scripturl, '?action=helpadmin;help=pmx_polls_hint" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
								</span>';

		if(!empty($this->pmx_polls))
		{
			echo '
								<select class="adm_w90 notdbut" name="config[settings][polls][]" size="3" multiple="multiple">';

			foreach($this->pmx_polls as $pid => $data)
				echo '
									<option value="'. $pid .'"'. (!empty($this->cfg['config']['settings']['polls']) && in_array($pid, $this->cfg['config']['settings']['polls']) ? ' selected="selected"' : '') .'>'. $data['question'] .($data['locked'] ? $txt['pmx_poll_select_locked'] : '').($data['expired'] ? $txt['pmx_poll_select_expired'] : '') .'</option>';

			echo '
								</select>';
		}
		else
			echo '
								<br /><div class="tborder adm_w90" style="margin-top:25px; height:1.3em;">'. $txt['pmx_no_polls'] .'</div>';

		echo '
							</div>
							<input type="hidden" name="config[show_sitemap]" value="0" />
						</div>';

		// return the default classnames
		return $used_classdef;
	}
}
?>