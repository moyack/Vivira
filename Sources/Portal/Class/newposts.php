<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file newposts.php
 * Systemblock Newposts
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* @class pmxc_newposts
* Systemblock Newposts
* @see newposts.php
*/
class pmxc_newposts extends PortaMxC_SystemBlock
{
	var $posts;				///< all posts
	var $imgName;			///< rescale image name
	var $postarray;		///< paginate
	var $attachments;	///< attaches
	var $footNote;		///< used for not see iamges
	var $noLB;				///< lightbox enable/disable

	/**
	* InitContent.
	* Checks the cache status and create the content.
	*/
	function pmxc_InitContent()
	{
		global $scripturl, $boardurl, $user_info, $context, $modSetting, $pmxCacheFunc;

		// if visible init the content
		if($this->visible)
		{
			if($this->cfg['cache'] > 0)
			{
				// check the block cache
				if(($cachedata = $pmxCacheFunc['get']($this->cache_key, $this->cache_mode)) !== null)
					list($this->posts, $this->attachments, $this->footNote, $this->imgName, $this->noLB) = $cachedata;
				else
				{
					$cachedata = $this->fetch_data();
					$pmxCacheFunc['put']($this->cache_key, $cachedata, $this->cache_time, $this->cache_mode);
				}
				unset($cachedata);
			}
			else
				$this->fetch_data();

			// no posts .. disable the block
			if(empty($this->posts))
				$this->visible = false;

			// paging...
			else
			{
				// create page index if set ..
				if(!empty($this->cfg['config']['settings']['onpage']) && count($this->posts) > $this->cfg['config']['settings']['onpage'])
				{
					// paging key
					$this->postKey = 'pmxpost_'. $this->cfg['blocktype'] . $this->cfg['id'];
					$this->postarray = array('pg' => 0);

					if(isset($_POST[$this->postKey]))
					{
						pmx_GetPostKey($this->postKey, $this->postarray);
						$_SESSION['PortaMx'][$this->postKey] = $this->postarray;
						$this->startpage = $this->postarray['pg'];
					}
					elseif(isset($_SESSION['PortaMx'][$this->postKey]))
					{
						if(intval($_SESSION['PortaMx'][$this->postKey]['pg'] * $this->cfg['config']['settings']['onpage']) > count($this->posts))
							$this->startpage = 0;
						else
							$this->startpage = $_SESSION['PortaMx'][$this->postKey]['pg'];
					}
					else
						$this->startpage = 0;

					$baseurl = !empty($modSettings['sef_enabled']) ? $boardurl .'/' : $scripturl .'?';
					$this->pmxc_constructPageIndex(count($this->posts), $this->cfg['config']['settings']['onpage'], false, $this->startpage);
					$this->pageindex = str_replace('<a', '<a onclick="pmx_StaticBlockSub(\''. $this->postKey .'\', this, \'/'. rtrim($baseurl, '?/') .'/\', \''. $this->cfg['uniID'] .'\')"', $this->pageindex);
				}
			}

			// image rescale
			if(!empty($this->imgName))
			{
				if(empty($this->cfg['config']['settings']['rescale']) && !is_numeric($this->cfg['config']['settings']['rescale']))
					addInlineCss('
	.'. $this->imgName .'{}');
				else
				{
					$vals = explode(',', $this->cfg['config']['settings']['rescale']);
					addInlineCss('
	.'. $this->imgName .'{'. (empty($vals[0]) ? 'width:auto' : 'max-width:'. (strpos($vals[0], '%') === false ? $vals[0] .'px' : $vals[0])) .';'. (empty($vals[1]) ? 'height:auto' : 'max-height:'. (strpos($vals[1], '%') === false ? $vals[1] .'px' : $vals[1])) .';}');
				}
			}
		}
		// return the visibility
		return $this->visible;
	}

	/**
	* fetch_data.
	* Fetch Messages and Attaches.
	*/
	function fetch_data()
	{
		global $context, $pmxcFunc, $user_info, $modSettings, $settings, $scripturl, $txt;

		$this->posts = null;
		$this->attachments = array();
		$this->footNote = array();
		$this->imgName = (!empty($this->cfg['config']['settings']['rescale']) || ($this->cfg['config']['settings']['rescale'] !== '' && intval($this->cfg['config']['settings']['rescale']) !== 0) ? $this->cfg['blocktype'] .'_'. $this->cfg['uniID'] : '');
		$this->noLB = !empty($modSettings['dont_use_lightbox']) || !empty($this->cfg['config']['settings']['disableHSimg']);

		if(isset($this->cfg['config']['settings']['boards']) && !empty($this->cfg['config']['settings']['boards']))
		{
			$boards = is_array($this->cfg['config']['settings']['boards']) ? $this->cfg['config']['settings']['boards'] : array($this->cfg['config']['settings']['boards']);
			$topics = '';
			$icon_sources = array();
			foreach ($context['stable_icons'] as $icon)
				$icon_sources[$icon] = 'images_url';

			// Find all posts in topics
			$request = $pmxcFunc['db_query']('substring', '
				SELECT t.id_topic, b.id_board, b.name AS board_name
				FROM {db_prefix}topics AS t
					INNER JOIN {db_prefix}messages AS ml ON (ml.id_msg = t.id_last_msg)
					LEFT JOIN {db_prefix}boards AS b ON (b.id_board = t.id_board)
				WHERE t.id_last_msg >= {int:min_message_id}
					AND b.id_board IN ({array_int:include_boards})
					AND {query_wanna_see_board}'. ($modSettings['postmod_active'] ? '
					AND t.approved = {int:is_approved}
					AND ml.approved = {int:is_approved}' : '') .'
				ORDER BY t.id_last_msg DESC
				LIMIT {int:max_messages}',
				array(
					'include_boards' => $this->cfg['config']['settings']['boards'],
					'min_message_id' => $modSettings['maxMsgID'] - (!empty($context['min_message_topics']) ? $context['min_message_topics'] : 35) * $this->cfg['config']['settings']['total'],
					'max_messages' => $this->cfg['config']['settings']['total'],
					'is_approved' => 1,
				)
			);
			while($row = $pmxcFunc['db_fetch_assoc']($request))
				$topics[$row['id_topic']] = $row;
			$pmxcFunc['db_free_result']($request);

			// We find anything?
			if(!empty($topics))
			{
				$request = $pmxcFunc['db_query']('substring', '
					SELECT
						mf.poster_time, mf.subject, ml.id_topic, mf.id_member, ml.id_msg, t.num_replies, t.num_views, mg.online_color,
						IFNULL(mem.real_name, mf.poster_name) AS poster_name, '. ($user_info['is_guest'] ? '1 AS is_read' : '
						IFNULL(lt.id_msg, IFNULL(lmr.id_msg, 0)) >= ml.id_msg_modified AS is_read') .',
						mf.body AS body, mf.smileys_enabled, mf.icon
					FROM {db_prefix}topics AS t
						INNER JOIN {db_prefix}messages AS ml ON (ml.id_msg = t.id_last_msg)
						INNER JOIN {db_prefix}messages AS mf ON (mf.id_msg = t.id_last_msg)
						LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = mf.id_member)'. (!$user_info['is_guest'] ? '
						LEFT JOIN {db_prefix}log_topics AS lt ON (lt.id_topic = t.id_topic AND lt.id_member = {int:current_member})
						LEFT JOIN {db_prefix}log_mark_read AS lmr ON (lmr.id_board = t.id_board AND lmr.id_member = {int:current_member})' : '') .'
						LEFT JOIN {db_prefix}membergroups AS mg ON (mg.id_group = mem.id_group)
					WHERE t.id_topic IN ({array_int:topic_list})
					ORDER BY ml.id_msg DESC',
					array(
						'current_member' => $user_info['id'],
						'topic_list' => array_keys($topics),
					)
				);
				while($row = $pmxcFunc['db_fetch_assoc']($request))
				{
					if(!isset($icon_sources[$row['icon']]))
						$icon_sources[$row['icon']] = file_exists($settings['theme_dir'] . '/images/post/' . $row['icon'] . '.png') ? 'images_url' : 'default_images_url';

					$this->posts[$row['id_msg']] = array(
						'id_msg' => $row['id_msg'],
						'id_topic' => $row['id_topic'],
						'icon' => '<img src="' . $settings[$icon_sources[$row['icon']]] . '/post/' . $row['icon'] . '.png" alt="' . $row['icon'] . '" style="vertical-align:text-bottom;">',
						'time' => timeformat($row['poster_time']),
						'smileys_enabled' => $row['smileys_enabled'],
						'body' => $row['body'],
						'link' => '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.0;#msg'. $row['id_msg'] .'" title="'. str_replace('"', '\"', $row['subject']) .'"><b>'. $row['subject'] .'</b></a>',
						'href' => $scripturl . '?topic=' . $row['id_topic'] . '.0;#msg'. $row['id_msg'],
						'replies' => $row['num_replies'],
						'views' => $row['num_views'],
						'poster' => array(
							'link' => !empty($row['id_member']) ? '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['poster_name'] . '</a>' : $row['poster_name']
						),
						'board' => array(
							'link' => '<a href="'. $scripturl .'?board='. $topics[$row['id_topic']]['id_board'] .'.0#ptop">'. $topics[$row['id_topic']]['board_name'] .'</a>',  
						),
					);
				}
				$pmxcFunc['db_free_result']($request);

				// prepare the post..
				$class = (!empty($this->cfg['config']['settings']['rescale']) || ($this->cfg['config']['settings']['rescale'] !== '' && intval($this->cfg['config']['settings']['rescale']) !== 0) ? $this->imgName : '');
				$modSettings['dont_show_attach_under_post'] = true;
				$this->attachments = array(0 => 0);

				$context['youtube_text'] = '{youtube_text}';
				foreach($this->posts as $mid => $post)
				{
					$context['lbimage_data'] = array('lightbox_id' => (empty($this->noLB) ? $this->cfg['blocktype'] .'-'. $this->cfg['id'] .'-'. $post['id_msg'] : null), 'class' => $class);
					$context['show_attach_under_post'] = array();
					$context['msg_footnote'] = '';

					censorText($this->posts[$mid]['body']);
					$this->posts[$mid]['body'] = parse_bbc($post['body'], $post['smileys_enabled'], $post['id_msg'], array(), ($user_info['is_guest'] || !empty($this->cfg['config']['settings']['disableYoutube'])));

					if(count($context['show_attach_under_post']) > 0)
						$this->attachments[$mid] = $context['show_attach_under_post'];
					else
						$this->attachments[$mid] = 0;

					if(preg_match_all('/'. preg_quote($txt['attachments_not_allowed_to_see']) .'/', $this->posts[$mid]['body'], $match) > 0)
					{
						$this->posts[$mid]['body'] = preg_replace('/'. preg_quote($txt['attachments_not_allowed_to_see']) .'/', '', $this->posts[$mid]['body']);
						$this->footNote[$mid] = $txt['attachments_not_allowed_to_see'];
					}
					else
					{
						if(!empty($context['msg_footnote']))
							$this->footNote[$mid] = $txt['attachments_not_allowed_to_see'];
					}

					// if rescale 0, remove images from posts
					if(is_numeric($this->cfg['config']['settings']['rescale']) && intval($this->cfg['config']['settings']['rescale'])  == 0)
						$this->posts[$mid]['body'] = PortaMx_revoveLinks($this->posts[$mid]['body'], false, true);

					// teaser enabled ?
					if(!empty($this->cfg['config']['settings']['teaser']))
						$this->posts[$mid]['body'] = PortaMx_Tease_posts($this->posts[$mid]['body'], $this->cfg['config']['settings']['teaser'], '', false, false);
				}
				unset($context['youtube_text']);

				// finally get the attachments
				$this->attachments = pmx_GetAttachments($this->attachments);
				$context['youtube_link'] = false;

				return array($this->posts, $this->attachments, $this->footNote, $this->imgName, $this->noLB);
			}
		}
	}

	/**
	* ShowContent.
	* Output the content and add necessary javascript
	*/
	function pmxc_ShowContent()
	{
		global $context, $modSettings;

		// ini all vars
		$doSplit = true;
		if(!empty($context['pmx']['settings']['colminwidth']) && !empty($modSettings['isMobile']))
		{
			$scrMode = get_cookie('screen');
			if(!empty($scrMode))
			{
				$scrMode = explode('-', $scrMode);
				$doSplit = isset($scrMode[1]) && intval($scrMode[1]) >= intval($context['pmx']['settings']['colminwidth']);
			}
		}
		$this->is_Split = ($doSplit && $this->cfg['config']['settings']['split']);
		$this->is_last = (!empty($this->pageindex) ? ($this->startpage + $this->postspage > count($this->posts) ? count($this->posts) - $this->startpage : $this->postspage) : count($this->posts));
		$this->half = (!empty($this->is_Split) ? ceil($this->is_last / 2) : $this->is_last);
		$this->spanlast = intval(!empty($this->is_Split) && ($this->half * 2) > $this->is_last && count($this->posts) > 1);
		$this->half = $this->half - $this->spanlast;
		$this->halfpad = ceil($context['pmx']['settings']['panelpad'] / 2);
		$this->fullpad = $context['pmx']['settings']['panelpad'];

		// create the classes
		$this->postbody = trim($this->cfg['config']['visuals']['postbody'] .' '. $this->cfg['config']['visuals']['postframe']);

		// find the first post
		reset($this->posts);
		for($i = 0; $i < $this->startpage; $i++)
			next($this->posts);

		// only one? .. clear split
		if(count($this->posts) - $this->startpage == 1)
			$this->is_Split = false;

		// show the pageindex line
		if(!empty($this->pageindex))
		{
			echo '
					<form id="'. $this->postKey .'_form" accept-charset="'. $context['character_set'] .'" method="post">
					<input type="hidden" id="'. $this->postKey .'" name="'. $this->postKey .'" value="" />';

			if(!empty($this->cfg['config']['settings']['pgidxtop']))
				echo '
					<div class="pagelinks pmx_pageTop">', $this->pageindex, '</div>';
		}

		// the maintable
		echo '
					<div class="pmx_tbl">
						<div class="pmx_tbl_tr">';

		// show posts in two cols?
		if(!empty($this->is_Split))
		{
			$isEQ = (!empty($this->cfg['config']['settings']['equal']) && !empty($this->cfg['config']['settings']['split']) ? 'pmxEQH' : '');

			echo '
							<div class="pmx_tbl_td">';

			// write out the left part..
			while(!empty($this->half))
			{
				list($pid, $post) = pmx_each($this->posts);
				$this->pmxc_ShowPost($pid, $post, (!empty($isEQ) ? $isEQ .'L' : ''), !empty($this->spanlast) || $this->half > 1, $this->half == 1 && empty($this->spanlast));
				next($this->posts);
				$this->half--;
				$this->is_last--;
			}

			echo '
							</div>
							<div class="pmx_table_td">';

			// shift post by 1..
			reset($this->posts);
			for($i = -1; $i < $this->startpage; $i++)
				next($this->posts);

			// write out the right part..
			while($this->is_last - $this->spanlast > 0)
			{
				list($pid, $post) = pmx_each($this->posts);
				$this->pmxc_ShowPost($pid, $post, (!empty($isEQ) ? $isEQ .'R' : ''), !empty($this->spanlast) || $this->is_last > 1, false);
				list($pid, $post) = pmx_each($this->posts);
				$this->is_last--;
			}

			echo '
							</div>
						</div>
					</div>';

			// we have a single post at least?
			if(!empty($this->spanlast))
			{
				echo '
					<div>';

				// clear split and write the last post
				$this->is_Split = false;
				$this->pmxc_ShowPost($pid, $post, false, false, true);

			echo '
					</div>';
			}
		}

		// single col
		else
		{
			echo '
							<div class="pmx_table_td">';

			// each post in a row
			while(!empty($this->is_last))
			{
				list($pid, $post) = pmx_each($this->posts);
				$this->pmxc_ShowPost($pid, $post, false, $this->is_last > 1, $this->is_last == 1);
				$this->half--;
				$this->is_last--;
			}

			echo '
							</div>
						</div>
					</div>';
		}

		// show pageindex if exists
		if(!empty($this->pageindex))
			echo '
					<div class="pagelinks pmx_pageBot">', $this->pageindex, '</div>
					</form>';
	}

	/**
	* Show one Post.
	*/
	function pmxc_ShowPost($pid, $post, $setQE, $lastrow, $setid)
	{
		global $context, $settings, $scripturl, $user_info, $txt;

		if($this->cfg['config']['visuals']['postheader'] == 'as_body')
			$this->cfg['config']['visuals']['postheader'] = '';

		// the post main division..
		if($this->cfg['config']['visuals']['postbody'] == 'none' && $this->cfg['config']['visuals']['postframe'] != 'none')
			$this->cfg['config']['visuals']['postbody'] = 'windowbg nobg';
		$frameClass = ($this->cfg['config']['visuals']['postframe'] == 'pmxborder' ? 'border ' : ''). $this->cfg['config']['visuals']['postbody'] .' blockcontent '. $this->cfg['config']['visuals']['postframe'] .' fr_'. $this->cfg['config']['visuals']['postheader'];
		echo '
						<div'. (!empty($setid) ? ' id="bot'. $this->cfg['uniID'] .'"' : '') .' style="margin-'. (!empty($this->is_Split) ? (!empty($this->half) ? 'right' : 'left') .':'. $this->halfpad .'px; margin-' : '') . (empty($lastrow) ? 'bottom:0' : 'bottom:'. $this->fullpad) .'px;'. (!empty($newRow) ? ' margin-top:-'. $this->halfpad .'px;' : '') .'">';

		// post header .. can have none, titlebg/catbg or as body
		if(empty($this->cfg['config']['visuals']['postheader']) || $this->cfg['config']['visuals']['postheader'] == 'none')
		{
			// no postheader, use bodyclass if set
			$frameClass = strpos($frameClass, 'windowbg2') !== false ? str_replace('windowbg2', 'windowbg', $frameClass) : str_replace('windowbg', 'windowbg2', $frameClass);
			echo '
							<div class="'. $frameClass .' roundtitle" style="padding:'. ($this->cfg['config']['visuals']['postheader'] == 'none' ? '0px 5px 4px 5px' : '5px 5px 4px 5px') .' !important;">';

			// cols set to equal height?
			if(!empty($setQE))
				echo '
							<div class="'. $setQE .'">';

			// postheader .. icon and subject
			if(empty($this->cfg['config']['visuals']['postheader']))
				echo '
						<div class="pmx_postheader">'. preg_replace('/align=\"[^\"]*\"/', '', $post['icon']) .'
							<span class="normaltext cat_msg_title">'. $post['link'] .'</span>
						</div>';
		}

		// we have postheader .. put icon and subject on it
		else
		{
			echo '
						<div class="'. str_replace('bg', '_bar', $this->cfg['config']['visuals']['postheader']) .' catbg_grid">
							<h4 class="'. $this->cfg['config']['visuals']['postheader'] .' catbg_grid">
								'. $post['icon'] .'<span class="normaltext cat_msg_title">'. $post['link'] .'</span>
							</h4>
						</div>
						<div class="'. $frameClass .'" style="padding:'. (empty($this->cfg['config']['visuals']['postframe']) && empty($this->cfg['config']['visuals']['postbody']) ? '2px 0' : '0px 5px 4px 5px') .' !important;">';

			// cols set to equal height?
			if(!empty($setQE))
				echo '
							<div class="'. $setQE .'">';
		}

		// show the postinfo lines if enabled
		if(!empty($this->cfg['config']['settings']['postinfo']))
		{
			if(!empty($this->cfg['config']['settings']['postviews']))
				echo '
							<div class="smalltext" style="float:left;">'. $txt['pmx_text_postby'] . $post['poster']['link'] .', '. $post['time'] .'</div>
							<div class="smalltext" style="float:right;">'. $txt['pmx_text_views'] . $post['views'] .'</div>
							<br style="clear:both;" />
							<div class="smalltext msg_bot_pad" style="float:left;">'. $txt['pmx_text_board'] . $post['board']['link'] .'</div>
							<div class="smalltext msg_bot_pad" style="float:right;">'. $txt['pmx_text_replies'] . $post['replies'] .'</div>
							<hr class="pmx_hrclear" />';
			else
			{
				echo '
							<div class="smalltext" style="float:left;">'. $txt['pmx_text_postby'] . $post['poster']['link'] .', '. $post['time'] .'</div>';

				if(empty($this->is_Split))
					echo'
							<div class="smalltext msg_bot_pad" style="float:right;">';
				else
					echo '
							<br style="clear:both;" />
							<div class="smalltext msg_bot_pad" style="float:left;">';

				echo $txt['pmx_text_board'] . $post['board']['link'] .'</div>
							<hr class="pmx_hrclear" />';
			}
		}

		echo '
						<div class="pmxhs_imglink '. $this->cfg['config']['visuals']['bodytext'] .'">';

		// output the message
		echo str_replace('{youtube_text}', $txt['play_on_youtube'], $post['body']);

		// post has attach and we will show it?
		$haveattaches = false;
		if(empty($this->footNote[$post['id_msg']]) && !empty($this->cfg['config']['settings']['thumbs']) && isset($this->attachments[$post['id_msg']]) && is_array($this->attachments[$post['id_msg']]))
		{
			$context['lbimage_data'] = array('lightbox_id' => (empty($this->noLB) ? $this->cfg['blocktype'] .'-'. $this->cfg['id'] .'-'. $post['id_msg'] . (!empty($this->cfg['config']['settings']['hidethumbs']) ? '-att' : '') : null));
			$haveattaches = true;
			$style = '';
			if(!empty($this->cfg['config']['settings']['thumbsize']))
			{
				$tmp = Pmx_StrToArray($this->cfg['config']['settings']['thumbsize']);
				if(@count($tmp) == 2)
					$style = ' style="'. (!empty($tmp[0]) ? 'max-width:'. $tmp[0] .'px;' : 'height:auto;') . (!empty($tmp[1]) ? 'max-height:'. $tmp[1] .'px;' : 'width:auto;') .'"';
			}
			echo '
							<div id="npatt'. $this->cfg['id'] .'.'. $post['id_msg'] .'"'. (!empty($this->cfg['config']['settings']['hidethumbs']) ? ' style="text-align:left;margin-top:5px;display:none;"' : '') .'>
								<hr />
								<div class="pmxhs_posting">';

			$thumbCnt = intval($this->cfg['config']['settings']['thumbcnt']);

			foreach($this->attachments[$post['id_msg']] as $data) 
			{
				if(isset($context['lbimage_data']['lightbox_id']))
					echo '
								<a href="'. $scripturl .'?action=dlattach;topic='. $post['id_topic'] .'.0;attach='. $data['id_attach'] .';image" title="'. $txt['pmx_hs_expand'] .'" data-lightbox="'. $context['lbimage_data']['lightbox_id'] .'" data-title="'. $data['filename'] .'" class="pmxhs_img">
									<img src="'. $scripturl .'?action=dlattach;topic='. $post['id_topic'] .'.0;attach='. (isset($data['id_thumb'])  && !empty($data['id_thumb']) ? $data['id_thumb'] : $data['id_attach']) .';image" alt="'. $data['filename'] .'"'. $style .' />
								</a>';
				else
					echo '
								<img src="'. $scripturl .'?action=dlattach;topic='. $post['id_topic'] .'.0;attach='. (isset($data['id_thumb']) && !empty($data['id_thumb']) ? $data['id_thumb'] : $data['id_attach']) .';image" alt="'. $data['filename'] .'"'. $style .' class="pmxhs_img" oncontextmenu="return false" />';

				$thumbCnt--;
				if(empty($thumbCnt))
					break;
			}
			echo '
							</div>
						</div>';
		}

		// ataches done..
		echo '
					</div>';

		// close the equal height div is set
		if(!empty($setQE))
			echo '
					</div>';

		// the read more link..
		echo '
					<hr class="pmx_hr">
					<div class="smalltext pmxp_button">
						<a style="float:left;" href="'. $post['href'] .'">'. $txt['pmx_text_readmore'] .'</a>';

		// we have attaches and collapse set?
		if($haveattaches && !empty($this->cfg['config']['settings']['hidethumbs']))
			echo '
						<a style="float:right;" href="javascript:void(0)" onclick="ShowMsgAtt(this, \'npatt'. $this->cfg['id'] .'.'. $pid .'\', \'pmxEQH'. $this->cfg['id'] .'\')">'. $txt['pmx_text_show_attach'] .'</a>
						<a style="float:right; display:none;" href="javascript:void(0)" onclick="ShowMsgAtt(this, \'npatt'. $this->cfg['id'] .'.'. $pid .'\', \'pmxEQH'. $this->cfg['id'] .'\')">'. $txt['pmx_text_hide_attach'] .'</a>';

		elseif(!empty($this->footNote[$post['id_msg']]))
			echo '
								<span class="pmxp_attnote">'. $this->footNote[$post['id_msg']] .'</span>';

		echo '
					</div>
				</div>
			</div>';
	}
}
?>