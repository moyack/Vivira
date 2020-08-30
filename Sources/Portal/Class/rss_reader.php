<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file rss_reader.php
 * Systemblock RSS Feed Reader
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* @class pmxc_rss_reader
* Systemblock RSS Feed Reader
* @see rss_reader.php

*/
class pmxc_rss_reader extends PortaMxC_SystemBlock
{
	var $TimeToLife;		///< Feed TTL if send
	var $feedheader;		///< header info
	var $rsscontent;		///< content info
	var $postarray;			///< paginate

	/**
	* checkCacheStatus.
	* do nothing
	*/
	function pmxc_checkCacheStatus()
	{
		return true;
	}

	/**
	* InitContent.
	* Checks the cache status and create the content.
	*/
	function pmxc_InitContent()
	{
		global $scripturl, $boardurl, $modSetting, $pmxCacheFunc;

		if($this->visible)
		{
			// content cached ?
			if($this->cfg['cache'] > 0)
			{
				// check if the block cached
				if(($cachedata = $pmxCacheFunc['get']($this->cache_key, $this->cache_mode)) !== null)
					list($this->feedheader, $this->rsscontent) = $cachedata;
				else
				{
					$this->rssreader_Content();
					$cachedata = array($this->feedheader, $this->rsscontent);
					$pmxCacheFunc['put']($this->cache_key, $cachedata, $this->TimeToLife, $this->cache_mode);
				}

				unset($cachedata);
			}
			else
				$this->rssreader_Content();

			// create page index if set ..
			if(!empty($this->cfg['config']['settings']['onpage']) && count($this->rsscontent) > $this->cfg['config']['settings']['onpage'])
			{
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
					if(intval($_SESSION['PortaMx'][$this->postKey]['pg'] * $this->cfg['config']['settings']['onpage']) > count($this->rsscontent))
						$this->startpage = 0;
					else
						$this->startpage = $_SESSION['PortaMx'][$this->postKey]['pg'];
				}
				else
					$this->startpage = 0;

				$baseurl = !empty($modSettings['sef_enabled']) ? $boardurl .'/' : $scripturl .'?';
				$this->pmxc_constructPageIndex(count($this->rsscontent), $this->cfg['config']['settings']['onpage'], false, $this->startpage);
				$this->pageindex = str_replace('<a', '<a onclick="pmx_StaticBlockSub(\''. $this->postKey .'\', this, \'/'. rtrim($baseurl, '?/') .'/\', \''. $this->cfg['uniID'] .'\')"', $this->pageindex);
			}
		}

		// return the visibility flag (true/false)
		return $this->visible;
	}

	/**
	* rssreader_Content.
	* Prepare the content and save in $this->rsscontent.
	*/
	function rssreader_Content()
	{
		$this->TimeToLife = $this->cache_time;
		$this->rsscontent = '';
		$this->cfg['content'] = '';

		// get all articles from feed
		if(!empty($this->cfg['config']['settings']['rssfeedurl']))
		{
			// get All posts from feed
			$this->rsscontent = getRSSfeedPosts($this->feedheader, $this->cfg['config']['settings']['rssfeedurl'], $this->cfg['config']['settings']['rssmaxitems'], $this->cfg['config']['settings']['rsstimeout']);

			if(empty($this->feedheader['title']))
			{
				$this->feedheader['title'] = $this->cfg['config']['settings']['rssfeed_name'];
				$this->feedheader['link'] = $this->cfg['config']['settings']['rssfeed_link'];
				$this->feedheader['desc'] = $this->cfg['config']['settings']['rssfeed_desc'];
			}
			// Time To Life send ?
			if(!empty($this->cfg['config']['settings']['usettl']) && !empty($this->feedheader['ttl']))
				$this->TimeToLife = intval($this->feedheader['ttl']) * 60;
		}
	}

	/**
	* ShowContent.
	* Output the content and add necessary javascript
	*/
	function pmxc_ShowContent()
	{
		global $context, $modSettings, $txt;

		if(!empty($this->rsscontent))
		{
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
			$this->is_last = (!empty($this->pageindex) ? ($this->startpage + $this->postspage > count($this->rsscontent) ? count($this->rsscontent) - $this->startpage : $this->postspage) : count($this->rsscontent));
			$this->half = (!empty($this->is_Split) ? ceil($this->is_last / 2) : $this->is_last);
			$this->spanlast = intval(!empty($this->is_Split) && ($this->half * 2) > $this->is_last && count($this->rsscontent) > 1);
			$this->half = $this->half - $this->spanlast;
			$this->halfpad = ceil($context['pmx']['settings']['panelpad'] / 2);
			$this->fullpad = $context['pmx']['settings']['panelpad'];

			// create the classes
			if(!empty($this->cfg['customclass']))
				$this->isCustFrame = !empty($this->cfg['customclass']['postframe']);
			else
				$this->isCustFrame = false;
			$this->spanclass = $this->isCustFrame && !empty($this->cfg['config']['visuals']['postbody']) ? $this->cfg['config']['visuals']['postbody'] .' ' : '';
			$this->postbody = trim($this->cfg['config']['visuals']['postbody'] .' '. $this->cfg['config']['visuals']['postframe']);

			// write out the content
			if(!empty($this->cfg['config']['settings']['showhead']))
			{
				
				echo '
				
				<div class="smalltext"'. (empty($this->pageindex) || empty($this->cfg['config']['settings']['pgidxtop']) ? ' style="padding-bottom:3px;"': '') .'>
					'. (!empty($this->feedheader['link']) ? '<a href="'. $this->feedheader['link'] .'" target="_blank" rel="noopener"><b>'. $this->feedheader['title'] .'</b></a>' : '<b>'. $this->feedheader['link'] .'</b>') .'
					'. (!empty($this->feedheader['desc']) ? '<br />'. $this->feedheader['desc'] : '');

				if(!empty($this->pageindex) && !empty($this->cfg['config']['settings']['pgidxtop']))
					echo '
					<hr class="pmx_hrclear" />';

				echo '
				</div>';
			}

			// find the first post
			reset($this->rsscontent);
			for($i = 0; $i < $this->startpage; $i++)
				next($this->rsscontent);

			// only one? .. clear split
			if(count($this->rsscontent) - $this->startpage == 1)
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
					list($pid, $post) = pmx_each($this->rsscontent);
					$this->pmxc_ShowPost($pid, $post, (!empty($isEQ) ? $isEQ .'L' : ''), !empty($this->spanlast) || $this->half > 1, $this->half == 1 && empty($this->spanlast));
					next($this->rsscontent);
					$this->half--;
					$this->is_last--;
				}

				echo '
							</div>
							<div class="pmx_tbl_td">';

				// shift post by 1..
				reset($this->rsscontent);
				for($i = -1; $i < $this->startpage; $i++)
					next($this->rsscontent);

				// write out the right part..
				while($this->is_last - $this->spanlast > 0)
				{
					list($pid, $post) = pmx_each($this->rsscontent);
					$this->pmxc_ShowPost($pid, $post, (!empty($isEQ) ? $isEQ .'R' : ''), !empty($this->spanlast) || $this->is_last > 1, false);
					list($pid, $post) = pmx_each($this->rsscontent);
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
							<div class="pmx_tbl_td">';

				// each post in a row
				while(!empty($this->is_last))
				{
					list($pid, $post) = pmx_each($this->rsscontent);
					$this->pmxc_ShowPost($pid, $post, false, $this->is_last == 1, $this->is_last == 1);
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
		else
		{
			if(!empty($context['pmx']['feed_error_text']))
				echo $txt['error_occured'] .'<br />'. $context['pmx']['feed_error_text'];
			else
				echo $txt['pmx_rssreader_error'];
		}
	}

	/**
	* Show one Post.
	*/
	function pmxc_ShowPost($pid, $post, $setQE, $lastrow, $setid)
	{
		global $context, $txt;

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
			// no postframe, use bodyclass if set
			$frameClass = strpos($frameClass, 'windowbg2') !== false ? str_replace('windowbg2', 'windowbg', $frameClass) : str_replace('windowbg', 'windowbg2', $frameClass);
			echo '
							<div class="'. $frameClass .' roundtitle" style="padding:'. ($this->cfg['config']['visuals']['postheader'] == 'none' ? '0px 5px 4px 5px' : '5px 5px 4px 5px') .' !important;">';

			// cols set to equal height?
			if(!empty($setQE))
				echo '
								<div class="'. $setQE .'">';

			// postheader .. icon and subject
			if(empty($this->cfg['config']['visuals']['postheader']))
			{
				echo '
									<div class="pmx_postheader">
										<img style="float:left;" src="'. $context['pmx_imageurl'] .'rssfeed.gif" alt="*" title="" />
										<span class="normaltext cat_msg_title rss_feed">';

				if(!empty($post['tlink']) || !empty($post['slink']))
					echo '
											<a href="'. (!empty($post['tlink']) ? $post['tlink'] : $post['slink']) .'" target="_blank" rel="noopener" title="'. str_replace('"', '\"', $post['subject']) .'"><b>'. $post['subject'] .'</b></a>';
				else
					echo $post['subject'];

				echo '
										</span>
									</div>';
			}
		}

		// ok, we have postheader .. put icon and subject on it
		else
		{
			echo '
									<div class="'. str_replace('bg', '_bar', $this->cfg['config']['visuals']['postheader']) .' catbg_grid">
										<h4 class="'. $this->cfg['config']['visuals']['postheader'] .' catbg_grid">
											<img style="float:left;" src="'. $context['pmx_imageurl'] .'rssfeed.gif" alt="*" title="" />
											<span class="normaltext cat_msg_title rss_feed">';

			if(!empty($post['tlink']) || !empty($post['slink']))
				echo '
												<a href="'. (!empty($post['tlink']) ? $post['tlink'] : $post['slink']) .'" target="_blank" rel="noopener" title="'. str_replace('"', '\"', $post['subject']) .'">'. $post['subject'] .'</a>';
			else
				echo $post['subject'];

			echo '
											</span>
										</h4>
									</div>
									<div class="'. $frameClass .'" style="padding:'. (empty($this->cfg['config']['visuals']['postframe']) && empty($this->cfg['config']['visuals']['postbody']) ? '2px 0' : '0px 5px 4px 5px') .' !important;">';

			// cols set to equal height?
			if(!empty($setQE))
				echo '
										<div class="'. $setQE .'">';
		}

		if($this->cfg['config']['visuals']['postheader'] != 'none')
		{
			if(!empty($post['poster']))
			{
				echo $txt['pmx_text_postby'];

				if(!empty($post['plink']))
					echo '
											<a href="'. $post['plink'] .'" target="_blank" rel="noopener">'. $post['poster'] .'</a>';
				else
					echo $post['poster'];
			}

			if(!empty($post['date']))
			{
				if(empty($post['poster']))
					echo $txt['pmx_rssreader_postat'];
				else
					echo ', ';
				echo $post['date'];
			}

			if(!empty($post['board']) || !empty($post['category']))
				echo '<br />'. (!empty($post['board']) ? $txt['pmx_text_board'] . (!empty($post['blink']) ? '<a href="'. $post['blink'] .'" target="_blank" rel="noopener">'. $post['board'] .'</a>' : $post['board']) : $txt['pmx_text_category'] .$post['category']);

			echo '
											<hr class="pmx_hrclear" />';
		}

		echo '
											<div class="'. $this->cfg['config']['visuals']['bodytext'] .'" style="overflow:hidden;">';

		if(!empty($this->cfg['config']['settings']['cont_encode']) && !empty($post['contenc']))
		{
			if(!empty($this->cfg['config']['settings']['delimage']))
				$post['contenc'] = PortaMx_revoveLinks($post['contenc'], false, true);

			if(!empty($this->cfg['config']['settings']['teaser']))
				echo PortaMx_Tease_posts($post['contenc'], $this->cfg['config']['settings']['teaser']);

			else
				echo $post['contenc'];
		}
		else
		{
			if(!empty($this->cfg['config']['settings']['delimage']))
				$post['message'] = PortaMx_revoveLinks($post['message'], false, true);

			if(!empty($this->cfg['config']['settings']['teaser']))
				echo PortaMx_Tease_posts($post['message'], $this->cfg['config']['settings']['teaser'], '', false, !empty($this->cfg['config']['settings']['delimages']));

			else
				echo $post['message'];
		}

		echo '
												</div>';

		// close the equal height div is set
		if(!empty($setQE))
			echo '
											</div>';

		// the read more link..
		if(!empty($post['slink']))
			echo '
											<div class="smalltext pmxp_button">
												<a style="float:left;" href="'. $post['slink'] .'" target="_blank" rel="noopener">'. $txt['pmx_text_readmore'] .'</a>
											</div>';

		echo '
										</div>
									</div>';
	}
}
?>