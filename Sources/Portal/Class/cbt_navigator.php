<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file cbt_navigator.php
 * Systemblock cbt_navigator (Categorie-Board-Topic)
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* @class pmxc_cbt_navigator
* Systemblock cbt_navigator
* @see cbt_navigator.php
*/
class pmxc_cbt_navigator extends PortaMxC_SystemBlock
{
	var $boards;					///< all boards
	var $cbtopics;				///< all topics
	var $cat_board;				///< all cats
	var $isRead;					///< unread topics by member

	/**
	* InitContent.
	*/
	function pmxc_InitContent()
	{
		global $context, $user_info, $modSettings, $pmxCacheFunc;

		// if visible init the content
		if($this->visible)
		{
			$curtopic = isset($_GET['topic']) ? $_GET['topic'] : 0;
			if($this->cfg['cache'] > 0)
			{
				// check the block cache
				if(($cachedata = $pmxCacheFunc['get']($this->cache_key, $this->cache_mode)) !== null)
				{
					list($this->topics, $this->isRead, $this->boards, $this->cat_board) = $cachedata;
					if(!isset($this->isRead[$user_info['id']]))
					{
						$cachedata = $this->cbt_fetchdata();
						list($this->topics, $this->isRead, $this->boards, $this->cat_board) = $cachedata;
						$pmxCacheFunc['put']($this->cache_key, array($this->topics, $this->isRead, $this->boards, $this->cat_board), $this->cache_time, $this->cache_mode);
					}
					elseif(isset($this->isRead[$user_info['id']][$curtopic]) && $this->isRead[$user_info['id']][$curtopic] != '1')
					{
						$this->isRead[$user_info['id']][$curtopic] = '1';
						$pmxCacheFunc['put']($this->cache_key, array($this->topics, $this->isRead, $this->boards, $this->cat_board), $this->cache_time, $this->cache_mode);
					}
				}
				else
				{
					$cachedata = $this->cbt_fetchdata();
					$pmxCacheFunc['put']($this->cache_key, $cachedata, $this->cache_time, $this->cache_mode);
					list($this->topics, $this->isRead, $this->boards, $this->cat_board) = $cachedata;
				}
			}
			else
			{
				$cachedata = $this->cbt_fetchdata();
				list($this->topics, $this->isRead, $this->boards, $this->cat_board) = $cachedata;
			}
			unset($cachedata);

			// no boards .. disable the block
			if(!empty($this->boards))
			{
				if(empty($modSettings['pmxCBTNavLoaded']))
				{
					addInlineJavascript('
	var pmxCBTimages = new Array("'. $context['pmx_imageurl'] .'minus.png", "'. $context['pmx_imageurl'] .'plus.png");
	var pmxCBTallBoards = {};');

					loadJavascriptFile(PortaMx_loadCompressed('PortalCBTNav.js'), array('external' => true));
					$modSettings['pmxCBTNavLoaded'] = true;
				}

				addInlineJavascript('
	pmxCBTallBoards['. $this->cfg['id'] .'] = new Array("'. implode('", "', $this->boards) .'");');
			}
			else
				$this->visible = false;
		}
		// return the visibility flag (true/false)
		return $this->visible;
	}

	/**
	* cbt__fetchdata.
	* Fetch all Categories, Boards and Topics.
	*/
	function cbt_fetchdata()
	{
		global $pmxcFunc, $user_info, $modSettings, $topic;

		$this->boards = null;
		$this->topics = null;
		$this->cat_board = null;
		$this->isRead = null;

		if(isset($this->cfg['config']['settings']['recentboards']) && !empty($this->cfg['config']['settings']['recentboards']))
		{
			// get Categories, Board, Topics and Messages .. what a monstrous query ;-)
			$request = $pmxcFunc['db_query']('', '
					SELECT c.name AS catname, c.id_cat,
						b.name AS boardname, b.id_board, b.child_level, b.redirect, t.is_sticky, t.locked,
						COALESCE(t.id_topic, 0) AS id_topic, COALESCE(t.id_last_msg, 0) AS id_last_msg,
						COALESCE(t.id_first_msg, 0) AS id_first_msg,
						m.id_msg, mf.subject, m.poster_name, m.poster_time, '. ($user_info['is_guest'] ? '1 AS isRead, 0 AS new_from' : '
						COALESCE(lt.id_msg, COALESCE(lmr.id_msg, 0)) >= m.id_msg_modified AS isRead,
						COALESCE(lt.id_msg, COALESCE(lmr.id_msg, -1)) + 1 AS new_from') .'
					FROM {db_prefix}boards AS b
					LEFT JOIN {db_prefix}topics AS t ON (t.id_board = b.id_board)
					LEFT JOIN {db_prefix}categories AS c ON (b.id_cat = c.id_cat)
					LEFT JOIN {db_prefix}messages AS mf ON (t.id_first_msg = mf.id_msg)
					LEFT JOIN {db_prefix}messages AS m ON (t.id_last_msg = m.id_msg)'. (!$user_info['is_guest'] ? '
					LEFT JOIN {db_prefix}log_topics AS lt ON (t.id_topic = lt.id_topic AND lt.id_member = {int:idmem})
					LEFT JOIN {db_prefix}log_mark_read AS lmr ON (lmr.id_board = m.id_board AND lmr.id_member = {int:idmem})' : '') .'
					WHERE b.id_board IN ({array_int:boards}) AND {query_wanna_see_board}
					'. ($modSettings['postmod_active'] ? ' AND m.approved = {int:approv}' : '') .'
					AND (b.id_last_msg = m.id_msg OR t.id_last_msg >= {int:min_msg} OR t.id_last_msg IS NULL)
					ORDER BY c.cat_order ASC, b.board_order ASC, t.is_sticky DESC, m.id_msg DESC',
				array(
					'idmem' => $user_info['id'],
					'boards' => $this->cfg['config']['settings']['recentboards'],
					'min_msg' => $modSettings['maxMsgID'] - 100 * $this->cfg['config']['settings']['numrecent'],
					'approv' => 1,
				)
			);

			// now sort out all the records
			$bID = 0;
			$cID = 0;
			$cbtopic = null;
			$currtopic = isset($_GET['topic']) ? $_GET['topic'] : 0;
			$rowsRead = $pmxcFunc['db_num_rows']($request);

			while($row = $pmxcFunc['db_fetch_assoc']($request))
			{
				// new categorie ?
				if($cID != $row['id_cat'])
				{
					// yes, save topics from previous cat / board
					if(!empty($cID) && !empty($bID))
					{
						$this->cat_board[$cID]['boards'][$bID]['topics'] = $cbtopic;
						$cbtopic = array();
					}

					// save the new cat
					$cID = $row['id_cat'];
					$this->cat_board[$cID]['name'] = $row['catname'];
					$bID = 0;
				}

				// same categorie ?
				if($cID == $row['id_cat'])
				{
					// yes, new board?
					if($bID != $row['id_board'])
					{
						// yes, save topics from previous board
						if(!empty($bID))
						{
							$this->cat_board[$cID]['boards'][$bID]['topics'] = $cbtopic;
							$cbtopic = array();
						}

						// save the new board
						$bID = $row['id_board'];
						if(empty($row['redirect']) && !empty($row['id_topic']))
							$this->boards[] = $bID;

						$this->cat_board[$cID]['boards'][$bID] = array(
							'name' => $row['boardname'],
							'level' => $row['child_level'],
							'isredir' => !empty($row['redirect']),
							'hastopics' => !empty($row['id_topic']),
						);

						// setup the topic count
						$count = (!empty($row['id_topic']) ? $this->cfg['config']['settings']['numrecent'] : 0);
					}
				}

				// count the topics
				if($count > 0)
				{
					$this->topics[] = $row['id_topic'];
					$this->isRead[$user_info['id']][$row['id_topic']] = ($currtopic == $row['id_topic'] ? '1' : $row['isRead']);

					censorText($row['subject']);
					$cbtopic[$row['id_topic']] = array(
						'subject' => $row['subject'],
						'post_name' => $row['poster_name'],
						'post_time' => preg_replace('~<[^>]*>~i', '', timeformat($row['poster_time'])),
						'id_msg' => $row['id_msg'],
						'id_first_msg' => $row['id_first_msg'],
						'id_last_msg' => $row['id_last_msg'],
						'new_from' => $row['new_from'],
						'isread' => $row['isRead'],
						'islocked' => $row['locked'],
						'issticky' => $row['is_sticky']
					);
					$count --;
				}
			}

			// save last topics
			if(!empty($bID))
				$this->cat_board[$cID]['boards'][$bID]['topics'] = $cbtopic;

			// done
			$pmxcFunc['db_free_result']($request);

			return array($this->topics, $this->isRead, $this->boards, $this->cat_board);
		}
	}

	/**
	* ShowContent.
	* Output the content from Categories, Boards and Topics.
	*/
	function pmxc_ShowContent()
	{
		global $context, $user_info, $modSettings, $scripturl, $txt;

		echo '
				<div style="height:20px;text-align:center;margin:0 auto;">
					<span class="cbtclicklink" onclick="NavCatToggleALL('. $this->cfg['id'] .', \'1\')">'. $txt['pmx_cbt_expandall'] .'</span> - <span  class="cbtclicklink" onclick="NavCatToggleALL('. $this->cfg['id'] .', \'0\')">'. $txt['pmx_cbt_collapseall'] .'</span>
				</div>
				<hr class="pmx_hr" />
				<div style="margin:-2px 0 2px 0;">';

		// loop through all cats, boards and topics
		$found = get_Cookie('cbtstat'. $this->cfg['id']);
		$isInit = is_null($found) && empty($user_info['is_guest']);
		$cook = array();

		if($isInit && !empty($this->cfg['config']['settings']['initexpandnew']))
			$exp = array();
		elseif($isInit && !empty($this->cfg['config']['settings']['initexpand']))
			$exp = $this->boards;
		else
			$exp = !empty($found) ? explode('.', $found) : array();

		foreach($this->cat_board as $cid => $cats)
		{
			if(!empty($cats['boards']))
			{
				echo '
					<div class="cbtshorttxt">
						<a class="cbtshorttxt" href="'. $scripturl . (!empty($modSettings['pmx_frontmode']) ? '?action=community;' : '') .'#c'. $cid .'" title="'. $txt['pmx_text_category'] . $cats['name'] .'"><b>'. $cats['name'].'</b></a>
					</div>';

				foreach($cats['boards'] as $bid => $cbtboard)
				{
					$cbtboard['unread'] = 0;
					if($cbtboard['hastopics'])
					{
						foreach($cbtboard['topics'] as $tid => $cbtopic)
							if(empty($this->isRead[$user_info['id']][$tid]))
								$cbtboard['unread'] = '1';
					}

					if($isInit && !empty($cbtboard['unread']) && !empty($this->cfg['config']['settings']['initexpandnew']))
						$exp[] = $bid;

					echo '
					<div style="margin-bottom:-2px; padding-left:'.($cbtboard['level'] * 8).'px;">';

					if($cbtboard['isredir'] || empty($cbtboard['hastopics']))
						echo '
						<img src="'. $context['pmx_imageurl'] . ($cbtboard['isredir'] ? 'redir' : 'notopic') .'.png" alt="*" title="" />';

					else
						echo '
						<img class="cbtshortcat" id="pmxcbt'. $this->cfg['id'] .'.img.'. $bid .'" onclick="NavCatToggle('. $this->cfg['id'] .', '. $bid .')" src="'. $context['pmx_imageurl'] . (in_array($bid, $exp) ? 'minus' : 'plus') .'.png" alt="*" title="'. $txt['pmx_cbt_colexp'] . $cbtboard['name'] .'" />';

					echo '
						<div class="cbtshorttxt">
							<a class="cbtshorttxt" href="'. $scripturl .'?board='. $bid .'.0#ptop" title="'. $txt['pmx_text_board'] . $cbtboard['name'] .'">'. $cbtboard['name'] .'</a>
							'.($cbtboard['unread'] ? '<img src="'. $context['pmx_imageurl'] .'unread.gif" alt="*" title="" />' : '').'
						</div>
					</div>';

					if($cbtboard['hastopics'])
					{
						echo '
					<div id="pmxcbt'. $this->cfg['id'] .'.brd.'. $bid .'" style="margin-bottom:2px;'. (!in_array($bid, $exp) ? ' display:none' : '') .'">';

						foreach($cbtboard['topics'] as $tid => $cbtopic)
						{
							$ttl = ($cbtopic['islocked'] && $cbtopic['issticky']
								? '['. $txt['pmx_cbt_sticky'] .' & '. $txt['pmx_cbt_locked'] .'] '
								: ($cbtopic['islocked'] ? '['. $txt['pmx_cbt_locked'] .'] ' : ($cbtopic['issticky'] ? '['. $txt['pmx_cbt_sticky'] .'] ' : ''))
							) . $cbtopic['subject'] .', '. $txt['by'] .' '. $cbtopic['post_name'] .', '. $cbtopic['post_time'];
							echo '
						<div class="cbtshorttxt" style="padding-left:'. (17 + ($cbtboard['level'] * 5)).'px;">
							<a class="cbtshorttxt" href="'. $scripturl .'?topic='. $tid .'.msg'. (empty($this->isRead[$user_info['id']][$tid]) ? $cbtopic['id_first_msg'] .'#msg'.$cbtopic['id_last_msg'] : $cbtopic['id_first_msg'] .'#msg'. $cbtopic['id_first_msg']) .'" title="'. $ttl .'">'. ($cbtopic['issticky'] ? '<b>' : '') . ($cbtopic['islocked'] ? '<i>' : '') . $cbtopic['subject'] . ($cbtopic['islocked'] ? '</i>' : '') . ($cbtopic['issticky'] ? '</b>' : '') .'</a>
							'. (empty($this->isRead[$user_info['id']][$tid]) ? '<img src="'. $context['pmx_imageurl'] .'unread.gif" alt="*" title="" />' : '') .'
						</div>';
						}

						echo '
					</div>';
					}
				}
			}
		}
		// done
		echo '
				</div>';
	}
}
?>