<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file mini_calendar.php
 * Systemblock mini_calendar
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* @class pmxc_mini_calendar
* Systemblock mini_calendar
* @see mini_calendar.php
*/
class pmxc_mini_calendar extends PortaMxC_SystemBlock
{
	var $today;
	var $cal_startday;
	var $calgrid;
	var $calbirthdays;
	var $calholidays;
	var $calevents;

	/**
	* checkCacheStatus.
	* Clear calendar cache on post/clear/modify a event.
	*/
	function pmxc_checkCacheStatus()
	{
		global $pmxCacheFunc;

		$result = false;
		if(isset($this->cfg['cache']) && $this->cfg['cache'] > 0)
		{
			if(getREQcnt('calendar,evtitle,eventid', true) == 3)
			{
				$pmxCacheFunc['drop']($this->cache_key .'-0', false);
				$pmxCacheFunc['drop']($this->cache_key .'-1', false);
				$pmxCacheFunc['drop']($this->cache_key .'-6', false);
				$result = array('action' => true);
			}
		}
		return $result;
	}

	/**
	* InitContent.
	* Checks the cache status and create the content.
	*/
	function pmxc_InitContent()
	{
		global $sourcedir, $pmxCacheFunc, $options, $user_info, $txt;

		// if visible init the content
		if($this->visible)
		{
			$this->today = array(
				'day' => (int) strftime('%d', forum_time()),
				'month' => (int) strftime('%m', forum_time()),
				'year' => (int) strftime('%Y', forum_time()),
				'date' => strftime('%Y-%m-%d', forum_time()),
			);
			$this->cal_startday = $user_info['is_guest'] ? $txt['dp_firstday'] : (!empty($options['calendar_start_day']) ? $options['calendar_start_day'] : 0);
			$this->cache_key .= '-'. $this->cal_startday;

			$cachedata = null;
			if(!empty($this->cfg['cache']))
			{
				if(($cachedata = $pmxCacheFunc['get']($this->cache_key, $this->cache_mode)) !== null)
				{
					list($curday, $this->calgrid, $this->calbirthdays, $this->calholidays, $this->calevents) = $cachedata;
					if($curday != $this->today['date'])
					{
						$pmxCacheFunc['drop']($this->cache_key, $this->cache_mode);
						$cachedata = null;
					}
				}
			}

			if(empty($cachedata))
			{
				include_once($sourcedir .'/Subs-Calendar.php');

				$calendarOptions = array(
					'start_day' => $this->cal_startday,
					'show_birthdays' => !empty($this->cfg['config']['settings']['birthdays']['show']),
					'show_events' => !empty($this->cfg['config']['settings']['events']['show']),
					'show_holidays' => !empty($this->cfg['config']['settings']['holidays']['show']),
					'show_week_num' => false,
					'short_day_titles' => true,
					'show_next_prev' => false,
					'show_week_links' => false,
					'size' => 'small',
				);
				$this->calgrid = getCalendarGrid($this->today['month'], $this->today['year'], $calendarOptions);

				$this->calbirthdays = array();
				if(!empty($this->cfg['config']['settings']['birthdays']['show']))
				{
					$start_data = (isset($this->cfg['config']['settings']['birthdays']['before']) ? date('Y-m-d', time() - (86400 * intval($this->cfg['config']['settings']['birthdays']['after']))) : date('Y-m-d'));
					$end_data = (isset($this->cfg['config']['settings']['birthdays']['after']) ? date('Y-m-d', time() + (86400 * intval($this->cfg['config']['settings']['birthdays']['before']))) : date('Y-m-d'));
					$temp = getBirthdayRange($start_data, $end_data);
					foreach($temp as $key => $val)
					{
						$mnt = intval(substr($key, 5, 2));
						if(in_array($mnt, array(11, 12)) && in_array($this->today['month'], array(1, 12)))
							$nkey = strval($this->today['year'] -1) . substr($key, 4);
						else
							$nkey = strval($this->today['year']) . substr($key, 4);
						$this->calbirthdays[$nkey] = $val;
					}
					ksort($this->calbirthdays);
				}

				$this->calholidays = array();
				if(!empty($this->cfg['config']['settings']['holidays']['show']))
				{
					$start_data = (isset($this->cfg['config']['settings']['holidays']['before']) ? date('Y-m-d', time() - (86400 * intval($this->cfg['config']['settings']['holidays']['after']))) : date('Y-m-d'));
					$end_data = (isset($this->cfg['config']['settings']['holidays']['after']) ? date('Y-m-d', time() + (86400 * intval($this->cfg['config']['settings']['holidays']['before']))) : date('Y-m-d'));
					$this->calholidays = getHolidayRange($start_data, $end_data);
					ksort($this->calholidays);
				}

				$this->calevents = array();
				if(!empty($this->cfg['config']['settings']['events']['show']))
				{
					$start_data = (isset($this->cfg['config']['settings']['events']['before']) ? date('Y-m-d', time() - (86400 * intval($this->cfg['config']['settings']['events']['after']))) : date('Y-m-d'));
					$end_data = (isset($this->cfg['config']['settings']['events']['after']) ? date('Y-m-d', time() + (86400 * intval($this->cfg['config']['settings']['events']['before']))) : date('Y-m-d'));
					$events = getEventRange($start_data, $end_data);
					ksort($events);

					foreach($events as $event)
					{
						foreach($event as $data)
						{
							if(!array_key_exists($data['id'], $this->calevents))
								$this->calevents[$data['id']] = $data;
						}
					}
				}

				if(!empty($this->cfg['cache']))
				{
					$cachedata = array($this->today['date'], $this->calgrid, $this->calbirthdays, $this->calholidays, $this->calevents);
					$pmxCacheFunc['put']($this->cache_key, $cachedata, $this->cache_time, $this->cache_mode);
					unset($cachedata);
				}
			}
		}
		return $this->visible;
	}

	/**
	* ShowContent
	*/
	function pmxc_ShowContent()
	{
		global $pmxcFunc, $scripturl, $modSettings, $txt;

		// writeout the head
		$this->cfg['dateform'] = $txt['pmx_minical_dateform'];

		echo '
				<div class="calgrid_head normaltext">';

		if(!empty($modSettings['cal_enabled']))
			echo '
					<a href="'. $scripturl .'?action=calendar;year='. $this->calgrid['current_year'] .';month='. $this->calgrid['current_month'] .'">'. $txt['months_titles'][intval($this->calgrid['current_month'])] .' '. $this->calgrid['current_year'] .'</a>';
		else
			echo $txt['months_titles'][intval($this->calgrid['current_month'])] .' '. $this->calgrid['current_year'];

		echo '
				</div>
				<div class="pmx_tbl">
					<div class="pmx_tbl_tr">';

		// writeout the day names
		foreach ($this->calgrid['week_days'] as $day)
		{
			echo '
						<div class="pmx_tbl_td calgrid';

			// is weekend?
			if(in_array($day, array(0, 6)))
				echo ' calgrid_day'. $day;

			echo '"><b>'. $pmxcFunc['substr']($txt['days_short'][intval($day)], 0, 2) .'</b></div> ';
		}
		echo '
					</div>';

		// now the entires calendar
		foreach ($this->calgrid['weeks'] as $week)
		{
			echo '
					<div class="pmx_tbl_tr">';
			$wd = 0;
			foreach($week['days'] as $days)
			{
				$class = '';
				echo '
						<div class="pmx_tbl_td calgrid">';

				// is today?
				if($this->today['day'] == $days['day'])
					$class = 'calgrid_today';

				// is weekend?
				if(in_array($this->calgrid['week_days'][$wd], array(0, 6)))
					$class .= ' calgrid_day'. $this->calgrid['week_days'][$wd];

				// any event?
				$have_event = false;
				if(is_array($days['holidays']) && count($days['holidays']) > 0 && isset($this->calholidays[$days['date']]))
				{
					foreach($days['holidays'] as $tmp)
						if(array_search($tmp, $this->calholidays[$days['date']], true) !== false)
							$have_event = true;
				}
				if(is_array($days['events']) && count($days['events']) > 0 && is_array($this->calevents) && count($this->calevents) > 0)
				{
					foreach($days['events'] as $tmp)
						if(isset($this->calevents[$tmp['id']]))
							$have_event = true;
				}
				if(is_array($days['birthdays']) && count($days['birthdays']) > 0 && is_array($this->calbirthdays) && count($this->calbirthdays) > 0)
				{
					foreach($days['birthdays'] as $id => $tmp)
					{
						foreach($this->calbirthdays as $dat)
						{
							foreach($dat as $bday)
								if($tmp['name'] == $bday['name'])
									$have_event = true;
						}
					}
				}
				if(!empty($have_event))
					$class .= ' calgrid_event';

				echo '<div class="'. trim($class) .'">'. (!empty($days['day']) ? $days['day'] : '') .'</div>';

				echo '
						</div>';
				$wd++;
			}
			echo '
					</div>';
		}

		echo '
				</div>';

		// we have birthdays ?
		if(!empty($this->calbirthdays))
		{
			echo '
				<fieldset class="calgrid_field">
					<legend>'. $txt['pmx_cal_birthdays'] .'</legend>';
			foreach($this->calbirthdays as $cdate => $data)
			{
				foreach($data as $vals)
					echo $this->caldateform($cdate) .': <a href="'. $scripturl .'?action=profile;u='. $vals['id'] .'">'. $vals['name'] .(!empty($vals['age']) ? ' ('. $vals['age'] .')' : '') .'</a><br />';
			}
			echo '
				</fieldset>';
		}

		// we have holidays ?
		if(!empty($this->calholidays))
		{
			echo '
				<fieldset class="calgrid_field">
					<legend>'. $txt['pmx_cal_holidays'] .'</legend>';
			foreach($this->calholidays as $cdate => $data)
			{
				foreach($data as $vals)
					echo $this->caldateform($cdate) .': '. $vals .'<br />';
			}
			echo '
				</fieldset>';
		}

		// we have events ?
		if(!empty($this->calevents))
		{
			echo '
				<fieldset class="calgrid_field">
					<legend>'. $txt['pmx_cal_events'] .'</legend>';

			foreach($this->calevents as $data)
			{
				// single event
				if($data['end_date'] == $data['start_date'])
			echo $this->caldateform($data['start_date']) .': <a href="'. $scripturl .'?action=calendar;viewlist;">'. trim($data['link']) .'</a><br />';

				// spaned event
				else
					echo $this->caldateform($data['start_date'], $data['end_date']) .': <a href="'. $scripturl .'?action=calendar;viewlist;">'. trim($data['link']) .'</a><br />';
			}
			echo '
				</filedset>';
		}
	}

	// Birthday, Holiday, Event date format
	function caldateform($dat1, $dat2 = '')
	{
		global $txt;

		$datearray = array('%M', '%m', '%d', '%j');
		list($d1['year'], $d1['month'], $d1['day']) = explode('-', $dat1);
		if(empty($dat2))
			return str_replace($datearray, array($txt['months_short'][intval($d1['month'])], $d1['month'], $d1['day'], intval($d1['day'])), $txt['pmx_minical_dateform'][0]);
		else
		{
			list($d2['year'], $d2['month'], $d2['day']) = explode('-', $dat2);
			if($d1['month'] == $d2['month'])
			{
				$tmp = str_replace($datearray, array($txt['months_short'][intval($d1['month'])], $d1['month'], $d1['day'], intval($d1['day'])), $txt['pmx_minical_dateform'][1]);
				return $tmp . str_replace($datearray, array($txt['months_short'][intval($d2['month'])], $d2['month'], $d2['day'], intval($d2['day'])), $txt['pmx_minical_dateform'][2]);
			}
			else
			{
				$tmp = str_replace($datearray, array($txt['months_short'][intval($d1['month'])], $d1['month'], $d1['day'], intval($d1['day'])), $txt['pmx_minical_dateform'][3]);
				return $tmp . str_replace($datearray, array($txt['months_short'][intval($d2['month'])], $d2['month'], $d2['day'], intval($d2['day'])), $txt['pmx_minical_dateform'][4]);
			}
		}
	}
}
?>