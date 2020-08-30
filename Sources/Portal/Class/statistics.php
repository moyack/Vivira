<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file statistics.php
 * Systemblock statistics
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* @class pmxc_statistics
* Systemblock statistics
* @see statistics.php
*/
class pmxc_statistics extends PortaMxC_SystemBlock
{
	var $online;

	/**
	* checkCacheStatus.
	* If the cache enabled, the cache trigger will be checked.
	*/
	function pmxc_checkCacheStatus()
	{
		global $pmxCacheFunc;

		$result = true;
		if($this->cfg['cache'] > 0 && !empty($this->cache_trigger))
		{
			if(($data = $pmxCacheFunc['get']($this->cache_key, $this->cache_mode)) !== null)
			{
				$res = eval($this->cache_trigger);
				if(!empty($res))
					$pmxCacheFunc['drop']($this->cache_key, $this->cache_mode);

				unset($data);
				$result = ($res === null);
			}
		}
		return $result;
	}

	/**
	* InitContent.
	*/
	function pmxc_InitContent()
	{
		global $sourcedir, $pmxCacheFunc;

		// if visible init the content
		if($this->visible)
		{
			$memOpts = array(
				'show_hidden' => allowedTo('moderate_forum'),
				'sort' => 'log_time',
				'reverse_sort' => true,
			);
			if($this->cfg['cache'] > 0)
			{
				// check the block cache
				if(($this->online = $pmxCacheFunc['get']($this->cache_key, $this->cache_mode)) === null)
				{
					require_once($sourcedir . '/Subs-MembersOnline.php');
					$this->online = getMembersOnlineStats($memOpts);
					$pmxCacheFunc['put']($this->cache_key, $this->online, $this->cache_time, $this->cache_mode);
				}
			}
			else
			{
				require_once($sourcedir . '/Subs-MembersOnline.php');
				$this->online = getMembersOnlineStats($memOpts);
			}
		}
		// return the visibility flag (true/false)
		return $this->visible;
	}

	/**
	* ShowContent
	* Prepare and output the content.
	*/
	function pmxc_ShowContent()
	{
		global $context, $scripturl, $settings, $modSettings, $txt;

		$is_adj = $context['browser']['is_ie'] || $context['browser']['is_opera'];
		$img = '<img src="'. $context['pmx_syscssurl'].'Images/bullet_blue.gif" alt="*" title="" />';
		$format = "$img<span>%1\$s:&nbsp;%2\$s</span>";
		$Rule = '';

		if(!empty($this->cfg['config']['settings']['stat_member']))
		{
			echo '
									<div'. (!empty($this->cfg['config']['visuals']['stats_text']) ? ' class="'. $this->cfg['config']['visuals']['stats_text'] .'"' : '') .'>
										<img src="'. $settings['theme_url'] .'/images/icons/members.png" alt="*" title="'. $txt['pmx_memberlist_icon'] .'" />
										<a href="'. $scripturl .'?action=mlist"><strong>'.  $txt['pmx_stat_member'] .'</strong></a>
									</div>
									<ul class="statistics">
										<li>'. sprintf($format, $txt['pmx_stat_totalmember'], (isset($modSettings['memberCount']) ? $modSettings['memberCount'] : $modSettings['totalMembers'])) .'</li>
										<li style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'. sprintf($format, $txt['pmx_stat_lastmember'], '<a href="'. $scripturl .'?action=profile;u='. $modSettings['latestMember'] .'"><strong>'. $modSettings['latestRealName'] .'</strong></a>') .'</li>
									</ul>';
			$Rule = '
									<hr class="pmx_hr" />';
		}

		if(!empty($this->cfg['config']['settings']['stat_stats']))
		{
			$tempTXT = $txt['default_time_format'];
			$txt['default_time_format'] = str_replace('%B', '%b.', $txt['default_time_format']);
			echo $Rule .'
									<div'. (!empty($this->cfg['config']['visuals']['stats_text']) ? ' class="'. $this->cfg['config']['visuals']['stats_text'] .'"' : '') .'>
										<img src="'. $context['pmx_imageurl'] .'stats.png" alt="*" title="'. $txt['pmx_statistics_icon'] .'" />
										<a href="'. $scripturl .'?action=stats"><strong>'.  $txt['pmx_stat_stats'] .'</strong></a>
									</div>
									<ul class="statistics">
										<li>'. sprintf($format, $txt['pmx_stat_stats_post'], $modSettings['totalMessages']) .'</li>
										<li>'. sprintf($format, $txt['pmx_stat_stats_topic'], $modSettings['totalTopics']) .'</li>
										<li>'. sprintf($format, $txt['pmx_stat_stats_ol_today'], $modSettings['mostOnlineToday']) .'</li>
										<li>'. sprintf($format, $txt['pmx_stat_stats_ol_ever'], $modSettings['mostOnline']) .'</li>
									</ul>
									&nbsp;&nbsp;&nbsp; ('. timeformat($modSettings['mostDate']) .')';
			$Rule = '
									<hr class="pmx_hr" />';
			$txt['default_time_format'] = $tempTXT;
		}

		if(!empty($this->cfg['config']['settings']['stat_users']) || !empty($this->cfg['config']['settings']['stat_olheight']))
		{
			if(!empty($this->cfg['config']['settings']['stat_users']))
			{
				echo $Rule .'
									<div'. (!empty($this->cfg['config']['visuals']['stats_text']) ? ' class="'. $this->cfg['config']['visuals']['stats_text'] .'"' : '') .'>
										<img src="'. $context['pmx_imageurl'] .'online.gif" alt="*" title="'. $txt['pmx_online_user_icon'] .'" />';

				if(checkECL_Cookie())
					echo '
										<a href="'. $scripturl .'?action=who"><strong>'.  $txt['pmx_stat_users'] .'</strong></a>';
				else
					echo '
										<strong>'.  $txt['pmx_stat_users'] .'</strong>';

				echo '
									</div>
									<ul class="statistics">
										<li>'. sprintf($format, $txt['pmx_stat_users_reg'], $this->online['num_users_online']) .'</li>';

				if(!empty($this->cfg['config']['settings']['stat_spider']) && (!empty($modSettings['show_spider_online']) && ($modSettings['show_spider_online'] < 3 || allowPmx('pmx_admin')) && !empty($modSettings['spider_name_cache'])))
					echo '
										<li>'. sprintf($format, $txt['pmx_stat_users_guest'], $this->online['num_guests'] - $this->online['num_spiders']) .'</li>
										<li>'. sprintf($format, $txt['pmx_stat_users_spider'], $this->online['num_spiders']) .'</li>';
				else
					echo '
										<li>'. sprintf($format, $txt['pmx_stat_users_guest'], $this->online['num_guests']) .'</li>';

				echo '
										<li>'. sprintf($format, $txt['pmx_stat_users_total'], $this->online['num_guests'] + $this->online['num_users_online']) .'</li>
									</ul>';
				$Rule = '
									<hr class="pmx_hr" />';
			}

			if(!empty($this->cfg['config']['settings']['stat_olheight']) && !empty($this->online['users_online']))
			{
				$img = '<img src="'. $context['pmx_syscssurl'].'Images/bullet_green.gif" alt="*" title="" />';
				echo $Rule .'
									<div id="olLst'. $this->cfg['id'] .'" class="onlinelist">
									<ul class="statistics">';

				$lines = (!empty($this->cfg['config']['settings']['stat_olheight']) ? $this->cfg['config']['settings']['stat_olheight'] : '5');
				foreach($this->online['users_online'] as $user)
					echo '
											<li'. ($user['is_last'] == true ? ' id="olElm'. $this->cfg['id'] .'"' : '') .' style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'. $img .'<span>'.($user['hidden'] ? '<i>'. $user['link'] .'</i>' : $user['link']).'</span></li>';

				echo '
										</ul>
									</div>
									<script>
										var olheight = (document.getElementById("olElm'. $this->cfg['id'] .'").clientHeight * '. $lines .');
										document.getElementById("olLst'. $this->cfg['id'] .'").style.maxHeight = olheight +"px";
									</script>';
			}
		}
	}
}
?>