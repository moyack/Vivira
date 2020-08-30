<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file polls.php
 * Systemblock polls
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* @class pmxc_polls
* Systemblock polls
* @see polls.php
* \author Copyright 2008-2017 by PortaMx - https://www.portamx.com
*/
class pmxc_polls extends PortaMxC_SystemBlock
{
	var $polls;							///< polls
	var $pollquestions;			///< poll questions
	var $currentpoll;				///< array(id, state)
	var $polldata;					///< polldata
	var $boardsAllowed;			///< poll view access
	var $pollChoices;				///< users poll choices

	/**
	* InitContent.
	* Get the poll data and load the header.
	*/
	function pmxc_InitContent()
	{
		global $context, $pmxcFunc, $user_info, $txt, $modSettings, $pmxCacheFunc;

		if($this->visible)
		{
			$this->pmxc_checkCacheStatus();		// call the cache trigger

			if(!empty($this->cfg['config']['settings']['polls']))
			{
				if(isset($_POST['pollchanged'. $this->cfg['id']]))
				{
					if($this->cfg['cache'] > 0)
						$pmxCacheFunc['drop']($this->cache_key, $this->cache_mode);

					redirectexit(pmx_http_build_query($_GET) .'#ptop', true);
				}
			}
			else
				$this->visible = false;
		}

		// member can see polls?
		$this->boardsAllowed = boardsAllowedTo('poll_view');
		if(empty($this->boardsAllowed) || empty($this->visible))
		{
			$this->visible = false;
			return $this->visible;
		}

		// check the block cache
		if(($cachedata = $pmxCacheFunc['get']($this->cache_key, $this->cache_mode)) !== null)
		{
			if(!isset($cachedata[$user_info['id']]))
			{
				$this->Create_polldata();
				if($this->visible)
				{
					$cachedata[$user_info['id']] = array($this->polldata, $this->currentpoll, $this->polls, $this->pollquestions);
					$pmxCacheFunc['put']($this->cache_key, $cachedata, $this->cache_time, $this->cache_mode);
				}
			}
			else
				list($this->polldata, $this->currentpoll, $this->polls, $this->pollquestions) = $cachedata[$user_info['id']];
		}

		if($cachedata === null)
		{
			// get content data
			$this->Create_polldata();
			if($this->visible)
			{
				// cache the block if enabled
				if($this->cfg['cache'] > 0)
				{
					$cachedata[$user_info['id']] = array($this->polldata, $this->currentpoll, $this->polls, $this->pollquestions);
					$pmxCacheFunc['put']($this->cache_key, $cachedata, $this->cache_time, $this->cache_mode);
					unset($cachedata);
				}
			}
		}

		if($this->visible)
		{
			// get users pollchoices
			$this->PollChoices = array();

			$request = $pmxcFunc['db_query']('', '
					SELECT id_poll, id_choice
					FROM {db_prefix}log_polls
					WHERE id_poll IN ({array_int:polls}) AND id_member = {int:idmem}',
				array(
					'polls' => $this->cfg['config']['settings']['polls'],
					'idmem' => $user_info['id'],
				)
			);

			if($pmxcFunc['db_num_rows']($request) > 0)
			{
				while($row = $pmxcFunc['db_fetch_assoc']($request))
				{
					if(isset($this->PollChoices[$row['id_poll']]))
						$this->PollChoices[$row['id_poll']] .= $row['id_choice'] .',';
					else
						$this->PollChoices[$row['id_poll']] = $row['id_choice'] .',';
				}
				$pmxcFunc['db_free_result']($request);
			}
			foreach($this->PollChoices as $key => $val)
				$this->PollChoices[$key] = explode(',', trim($val, ','));

			// load javascript to header
			if(empty($modSettings['pmxPollsLoaded']))
			{
				loadJavascriptFile(PortaMx_loadCompressed('PortalPolls.js'), array('external' => true));
				$modSettings['pmxPollsLoaded'] = true;
			}
		}
		// return the visibility flag (true/false)
		return $this->visible;
	}

	/**
	* Create_polldata.
	* Get the poll data and save it to the content.
	*/
	function Create_polldata()
	{
		global $pmxcFunc, $user_info, $modSettings, $txt;

		$this->polls = array();
		$this->pollquestions = array();
		$this->polldata = array();
		$this->currentpoll = array('id' => 0, 'state' => 0);

		// ckeck if a pollcookie exist
		$cookname = 'poll'. $this->cfg['id'];
		if(($cook = get_cookie($cookname)) && !is_null($cook))
		{
			$tmp = explode(',', $cook);
			if(count($tmp) == 2)
				$this->currentpoll = array('id' => (int) $tmp[0], 'state' => (int) $tmp[1]);
			else
				$this->currentpoll = array('id' => 0, 'state' => 0);
		}

		// member has voted?
		$membervote = array();
		$request = $pmxcFunc['db_query']('', '
				SELECT id_poll
				FROM {db_prefix}log_polls
				WHERE id_poll IN ({array_int:polls}) AND id_member = {int:current_member}',
			array(
				'polls' => $this->cfg['config']['settings']['polls'],
				'current_member' => $user_info['id'],
			)
		);
		while($row = $pmxcFunc['db_fetch_assoc']($request))
			$membervote[$row['id_poll']] = true;
		$pmxcFunc['db_free_result']($request);

		// get all poll data
		$request = $pmxcFunc['db_query']('', '
				SELECT t.id_topic, b.id_board, p.id_poll, p.question, p.voting_locked, p.hide_results, p.expire_time, p.guest_vote, p.change_vote, p.num_guest_voters
				FROM {db_prefix}topics AS t
				INNER JOIN {db_prefix}polls AS p ON (p.id_poll = t.id_poll)
				INNER JOIN {db_prefix}boards AS b ON (b.id_board = t.id_board)
				WHERE t.id_poll IN ({array_int:polls})
					AND {query_see_board} '. (!in_array(0, $this->boardsAllowed) ? '
					AND b.id_board IN ({array_int:boards_allowed_see})' : '') . ($modSettings['postmod_active'] ? '
					AND t.approved = {int:is_approved}' : '') .'
				ORDER BY t.id_poll DESC',
			array(
				'polls' => $this->cfg['config']['settings']['polls'],
				'boards_allowed_see' => $this->boardsAllowed,
				'is_approved' => 1,
			)
		);
		if($pmxcFunc['db_num_rows']($request) > 0)
		{
			while($row = $pmxcFunc['db_fetch_assoc']($request))
			{
				$pid = array_search($row['id_poll'], $this->cfg['config']['settings']['polls']);
				if(is_numeric($pid))
				{
					// can vote?
					if(checkECL_Cookie(true))
					{
						$cook = isset($_COOKIE['guest_poll_vote']) ? $_COOKIE['guest_poll_vote'] : '';
						if(!empty($row['expire_time']) && $row['expire_time'] < time())
							$allow_vote = false;
						elseif($user_info['is_guest'] && $row['guest_vote'] && (empty($cook) || (!empty($cook) && preg_match('~^[0-9,;]+$~', $cook) && strpos($cook, ';'. $row['id_poll'] .',') === false)))
							$allow_vote = true;
						elseif($user_info['is_guest'])
							$allow_vote = false;
						elseif(!empty($row['voting_locked']) || !allowedTo('poll_vote', $row['id_board']))
							$allow_vote = false;
						else
							$allow_vote = !isset($membervote[$row['id_poll']]);
					}
					else
						$allow_vote = false;

					// poll expired
					$is_expired = !empty($row['expire_time']) && $row['expire_time'] < time();
					$is_locked = !empty($row['voting_locked']);

					// can view?
					$allow_view_results = allowedTo('moderate_board') || $row['hide_results'] == 0 || ($row['hide_results'] == 1 && !$allow_vote) || $is_expired || $is_locked;

					// save data
					$this->polldata[$pid] = array(
						'is_locked' => $is_locked,
						'allow_vote' => $allow_vote && !$is_expired && !$is_locked,
						'allow_view_results' => $allow_view_results,
						'allow_change_vote' => !$is_expired && !$is_locked && !$user_info['is_guest'] && !$allow_vote && !empty($row['change_vote']),
						'is_expired' => $is_expired,
						'expired' => !empty($row['expire_time']) ? timeformat($row['expire_time']) : 0,
						'guestvotes' => $row['num_guest_voters'],
					);

					$polltopic[$pid] = $row['id_topic'];
					$this->pollquestions[$pid] = $row['question'];
				}
			}
			$pmxcFunc['db_free_result']($request);

			// secure...
			if(!isset($polltopic[$this->currentpoll['id']]))
			{
				$this->currentpoll = array('id' => 0, 'state' => 0);
				$polltopic[$this->currentpoll['id']] = 0;
			}

			// get poll data
			$request = $pmxcFunc['db_query']('', '
					SELECT p.id_poll, p.question, p.max_votes
					FROM {db_prefix}topics AS t
					INNER JOIN {db_prefix}polls AS p ON (p.id_poll = t.id_poll)
					WHERE t.id_topic = {int:current_topic}',
				array(
					'current_topic' => $polltopic[$this->currentpoll['id']],
				)
			);

			// get Member votes
			if($pmxcFunc['db_num_rows']($request) > 0)
			{
				$row = $pmxcFunc['db_fetch_assoc']($request);
				$pmxcFunc['db_free_result']($request);

				$request = $pmxcFunc['db_query']('', '
						SELECT COUNT(DISTINCT id_member)
						FROM {db_prefix}log_polls
						WHERE id_poll = {int:current_poll} AND id_member != 0',
					array(
						'current_poll' => $row['id_poll'],
					)
				);
				list ($total_votes) = $pmxcFunc['db_fetch_row']($request);
				$pmxcFunc['db_free_result']($request);

				$request = $pmxcFunc['db_query']('', '
						SELECT id_choice, label, votes
						FROM {db_prefix}poll_choices
						WHERE id_poll = {int:current_poll}',
					array(
						'current_poll' => $row['id_poll'],
					)
				);
				$polloptions = array();
				$total = 0;
				while($rowChoice = $pmxcFunc['db_fetch_assoc']($request))
				{
					censorText($rowChoice['label']);
					$polloptions[$rowChoice['id_choice']] = array($rowChoice['label'], $rowChoice['votes']);
					$total += $rowChoice['votes'];
				}
				$pmxcFunc['db_free_result']($request);
				$total_votes += $this->polldata[$this->currentpoll['id']]['guestvotes'];

				$this->polls = array(
					'id' => $row['id_poll'],
					'question' => $row['question'],
					'total_votes' => $total_votes,
					'topic' => $polltopic[$this->currentpoll['id']],
				);
				$this->polls = array_merge($this->polls, $this->polldata[$this->currentpoll['id']]);

				// Calculate the percentages ..
				$divisor = ($total == 0 ? 1 : $total);
				$tablen = 1;
				foreach ($polloptions as $i => $opt)
				{
					$bar = floor(($opt[1] * 100) / $divisor);
					$tablen = $tablen < $bar ? $bar : $tablen;
					$this->polls['options'][$i] = array(
						'id' => 'options'. $this->cfg['id'] .'-' . $i,
						'percent' => round((($opt[1] * 100) / $divisor), 1),
						'votes' => $opt[1],
						'option' => parse_bbc($opt[0]),
						'vote_button' => '<input type="'. ($row['max_votes'] > 1 ? 'checkbox' : 'radio') .'" name="options[]" id="options'. $this->cfg['id'] .'-'. $i .'" value="'. $i .'" class="'. ($row['max_votes'] > 1 ? 'input_check' : 'input_radio') .'" />'
					);
				}
				$this->polls['allowed_warning'] = $row['max_votes'] > 1 ? sprintf($txt['poll_options6'], min(count($opt), $row['max_votes'])) : '';
				$this->polls['tablen'] = (int) $tablen;
			}
			else
				$this->visible = false;	// hide the block
		}
		else
			$this->visible = false;	// hide the block
	}

	/**
	* ShowContent
	*/
	function pmxc_ShowContent()
	{
		global $scripturl, $context, $user_info, $txt;

		echo '
				<div style="padding-bottom:4px;"'.(isset($this->cfg['config']['visuals']['questiontext']) ? ' class="'. $this->cfg['config']['visuals']['questiontext'] .'"' : ''). '>
					<a href="'. $scripturl .'?topic='. $this->polls['topic'] .'.0#ptop"><b>'. $this->polls['question'] .'</b></a>';

		echo '
				</div>';

		if(!empty($this->polls['allow_vote']))
		{
			echo '
				<div id="pxm_allowvotepoll'. $this->cfg['id'] .'"'. (!empty($this->polls['allow_view_results']) && $this->currentpoll['state'] == '1' ? ' style="display:none"' : '') .'>
					<form id="pmx_voteform'. $this->cfg['id'] .'" action="'. $scripturl .'?action=vote;topic='. $this->polls['topic'] .';poll='. $this->polls['id'] .'#ptop" method="post" accept-charset="', $context['character_set'], '">
						<input type="hidden" name="poll" value="'. $this->polls['id'] .'" />
						<input type="hidden" name="'. $context['session_var'] .'" value="'. $context['session_id'] .'" />
						<input type="hidden" name="pmx_votepoll" value="'. pmx_http_build_query($_GET) .'" />
						<div style="padding-top:4px;line-height:1em;">';

			$i = 0;
			foreach ($this->polls['options'] as $option)
			{
				echo '
							<div class="polloptions"><label id="pmx_pollopt'. $this->cfg['id'] .'_'. $i .'" style="border:none; background:transparent;" for="'. $option['id'] .'">'. $option['vote_button'] .' <span style="vertical-align:3px;">'. $option['option'], '</span></label></div>';
				$i++;
			}

			echo '
						</div>
						<div>'. $this->polls['allowed_warning'] .'</div>';

			if(!empty($this->polls['expired']))
				echo '
						<div style="padding-top:4px;"><b>'. $txt['poll_expires_on'] .':</b> '. $this->polls['expired'] .'</div>';

			echo '
						<hr class="pmx_hr" />
						<div style="min-height:25px;">
							<input style="margin:2px;float:right;" type="button" class="button_submit" name="button" value="'. $txt['poll_vote'] .'" onmouseup="pmx_VotePoll(\''. $this->cfg['id'] .'\', this)" />';

			if($this->polls['allow_view_results'])
				echo '
							<input style="margin:2px;float:left;" type="button" class="button_submit" name="button" value="'. $txt['pmx_poll_results'] .'" onmouseup="pmx_ShowPollResult(\''. $this->cfg['id'] .'\', this)" />';

			echo '
						</div>
					</form>
				</div>';
		}

		if(!empty($this->polls['allow_view_results']))
		{
			echo '
				<div id="pxm_allowviewpoll'. $this->cfg['id'] .'"'. (!empty($this->polls['allow_vote']) && $this->currentpoll['state'] == '0' ? ' style="display:none"' : '') .'>
					<table class="pmx_table">';

			$tablen = (100 / $this->polls['tablen']);
			$tablen = ($tablen > 100 ? 100 : $tablen);
			$ownpolls = isset($this->PollChoices[$this->polls['id']]) ? $this->PollChoices[$this->polls['id']] : array();

			// Guestpolls
			// ;id,timestamp,[vote,vote...]; etc
			$cook = get_cookie('guest_poll_vote');
			if(empty($cook))
				$guestpolls = array();
			else
			{
				$guestinfo = explode(';', trim($cook, ';'));

				// Find the poll we're after.
				foreach($guestinfo as $i => $guestvoted)
				{
					$temp = explode(',', $guestvoted);
					if($temp[0] != $this->polls['id'])
						continue;
					else
						$guestpolls[] = $temp[2];
				}
			}

			foreach($this->polls['options'] as $key => $option)
			{
				$barlen = ($option['percent'] == 0 ? '0' : ceil($option['percent'] * $tablen));
				$barlen = ($barlen > 100 ? 100 : $barlen);
				$strong = $option['percent'] > 0 && ($user_info['is_guest'] && is_array($guestpolls) && in_array($key, $guestpolls)) || (!$user_info['is_guest'] && is_array($ownpolls) && in_array($key, $ownpolls)) ? array(0 => '<b>', 1 => '</b>') : array(0 => '', 1 => '');

				echo '
				<tr>
					<td style="text-align:left;height:35px;width:95%;">'. $strong[0] . $option['option'] . $strong[1] .'
						<div style="height: 10px;width:'. $barlen .'%;"'. ($barlen > 0 ? ' class="poll_bar"' : '') .'></div>
					</td>
					<td style="text-align:right;">
						<div style="margin-top:14px;white-space:nowrap;margin-left:8px;">
						 '.	$strong[0] . $option['votes'] .' ('. $option['percent'] .'%)'. $strong[1] .'
						</div>
					</td>
				</tr>';
			}

			echo '
					</table>
					<div style="clear:both; padding-top:8px;"><b>'. $txt['poll_total_voters'] .':</b> '. $this->polls['total_votes'] .'</div>';

			if(!empty($this->polls['expired']))
				echo '
					<div style="padding-top:4px;"><b>'. (!empty($this->polls['is_expired']) ? $txt['pmx_poll_closed'] .'</b>' : $txt['poll_expires_on'] .':</b> '. $this->polls['expired']) .'</div>';

			if(!empty($this->polls['allow_vote']) || !empty($this->polls['allow_change_vote']))
			{
				echo '
					<hr class="pmx_hr" />
						<div style="min-height:25px;">';

				if(!empty($this->polls['allow_vote']))
					echo '
							<input style="margin:2px;float:right;" type="button" class="button_submit" name="button" value="'. $txt['poll_return_vote'] .'" onmouseup="pmx_ShowPollVote('. $this->cfg['id'] .', '. $this->currentpoll['id'] .')" />';

				if(!empty($this->polls['allow_change_vote']))
					echo '
							<input  style="margin:2px;float:right;" type="button" class="button_submit" name="button" value="'. $txt['poll_change_vote'] .'" onmouseup="pmx_ChangePollVote('. $this->cfg['id'] .', this)" />
							<form id="pmx_voteform'. $this->cfg['id'] .'" action="'. $scripturl .'?action=vote;topic='. $this->polls['topic'] .';poll='. $this->polls['id'] .'#ptop" method="post" accept-charset="', $context['character_set'], '">
								<input type="hidden" name="poll" value="'. $this->polls['id'] .'" />
								<input type="hidden" name="'. $context['session_var'] .'" value="'. $context['session_id'] .'" />
								<input type="hidden" name="pmx_votepoll" value="'. pmx_http_build_query($_GET) .'" />
							</form>';

				echo '
						</div>';
			}

			echo '
				</div>';
		}

		if(empty($this->polls['allow_view_results']) && empty($this->polls['allow_vote']) && empty($this->polls['is_expired']))
		{
			echo '
				<div style="padding:0 3px;">';

			foreach($this->polls['options'] as $option)
				echo '
					'. $option['option'] .'<div style="line-height:0.8em; padding-bottom:0.5em;">&nbsp;&laquo;&ndash;&raquo;</div>';

			if(!empty($this->polls['expired']))
				echo '
					<div style="padding-top:4px;"><b>'. (!empty($this->polls['is_expired']) ? $txt['pmx_poll_closed'] .'</b>' : $txt['poll_expires_on'] .':</b> '. $this->polls['expired']) .'</div>';

			echo '
				</div>';

			if(!empty($this->polls['allow_vote']) || !empty($this->polls['allow_change_vote']))
			{
				echo '
				<hr class="pmx_hr" />
					<div style="min-height:25px">';

				if(!empty($this->polls['allow_change_vote']))
					echo '
						<input  style="margin:2px;float:right;" type="button" class="button_submit" name="button" value="'. $txt['poll_change_vote'] .'" onmouseup="pmx_ChangePollVote('. $this->cfg['id'] .', this)" />
						<form id="pmx_voteform'. $this->cfg['id'] .'" action="'. $scripturl .'?action=vote;topic='. $this->polls['topic'] .';poll='. $this->polls['id'] .'" method="post" accept-charset="', $context['character_set'], '">
							<input type="hidden" name="poll" value="'. $this->polls['id'] .'" />
							<input type="hidden" name="'. $context['session_var'] .'" value="'. $context['session_id'] .'" />
							<input type="hidden" name="pmx_votepoll" value="'. pmx_http_build_query($_GET) .'" />
						</form>';

				echo '
					</div>';
			}
		}

		// multiple polls enabled?
		if(count($this->pollquestions) > 1)
		{
			$maxwidth = (in_array($this->cfg['side'], array('right', 'left')) ? '98%' : 0);
			$cact = (empty($_SERVER['QUERY_STRING']) ? '' : '?'. PortaMx_makeSafe($_SERVER['QUERY_STRING']));

			echo '
				<form id="pmx_votechange'. $this->cfg['id'] .'" action="'. $scripturl . $cact .'" method="post" accept-charset="', $context['character_set'], '">
					<input id="pollchanged'. $this->cfg['id'] .'" type="hidden" name="pollchanged'. $this->cfg['id'] .'" value="'. $this->polls['id'] .'" />
					<input type="hidden" name="'. $context['session_var'] .'" value="'. $context['session_id'] .'" />
					<div style="padding:5px 0 5px 0;">'. $txt['pmx_pollmultiview'] .'</div>
						<select class="notdbut" name="pollselect"'. (!empty($maxwidth) ? ' style="width:'. $maxwidth .';"' : '') .' onchange="pmx_ChangeCurrentPoll(\''. $this->cfg['id'] .'\', this);">';

			foreach($this->pollquestions as $id => $question)
				echo '
							<option value="'. $id .'"' .($id == $this->currentpoll['id'] ? ' selected="selected"' : '') .'>'. $question .'</option>';

			echo '
					</select>
				</form>';
		}
	}
}
?>