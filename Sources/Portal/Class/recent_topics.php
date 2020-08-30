<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file recent_topics.php
 * Systemblock recent_topics
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* @class pmxc_recent_topics
* Systemblock recent_topics
* @see recent_topics.php
*/
class pmxc_recent_topics extends PortaMxC_SystemBlock
{
	var $posts;				///< all posts
	var $topics;			///< all topics
	var $isRead;			///< unread topics by member

	/**
	* InitContent.
	* Checks the cache status and create the content.
	*/
	function pmxc_InitContent()
	{
		global $user_info, $pmxCacheFunc;

		// if visible init the content
		if($this->visible)
		{
			if($this->cfg['cache'] > 0)
			{
				// check the block cache
				if(($cachedata = $pmxCacheFunc['get']($this->cache_key, $this->cache_mode)) !== null)
				{
					$curtopic = isset($_GET['topic']) ? $_GET['topic'] : 0;
					list($this->topics, $this->isRead, $this->posts) = $cachedata;
					if(!isset($this->isRead[$user_info['id']]))
					{
						$cachedata = $this->fetch_data();
						list($this->topics, $this->isRead, $this->posts) = $cachedata;
						$pmxCacheFunc['put']($this->cache_key, array($this->topics, $this->isRead, $this->posts), $this->cache_time, $this->cache_mode);
					}
					elseif(isset($this->isRead[$user_info['id']][$curtopic]) && $this->isRead[$user_info['id']][$curtopic] != '1')
					{
						$this->isRead[$user_info['id']][$curtopic] = '1';
						$pmxCacheFunc['put']($this->cache_key, array($this->topics, $this->isRead, $this->posts), $this->cache_time, $this->cache_mode);
					}
				}
				else
				{
					$cachedata = $this->fetch_data();
					$pmxCacheFunc['put']($this->cache_key, $cachedata, $this->cache_time, $this->cache_mode);
					list($this->topics, $this->isRead, $this->posts) = $cachedata;
				}
			}
			else
			{
				$cachedata = $this->fetch_data();
				list($this->topics, $this->isRead, $this->posts) = $cachedata;
			}
			unset($cachedata);

			// no posts .. disable the block
			if(empty($this->posts))
				$this->visible = false;
		}
		// return the visibility flag (true/false)
		return $this->visible;
	}

	/**
	* fetch_data().
	* Prepare the content and save in $this->cfg['content'].
	*/
	function fetch_data()
	{
		global $user_info;

		$this->posts = null;
		$this->topics = null;
		$this->isRead = null;

		if(empty($this->cfg['config']['settings']['recentboards']))
			$this->cfg['config']['settings']['recentboards'] = null;

		$this->posts = ssi_recentTopics($this->cfg['config']['settings']['numrecent'], null, $this->cfg['config']['settings']['recentboards'], '');
		if(!empty($this->posts))
		{
			$rtopic = isset($_GET['topic']) ? $_GET['topic'] : 0;
			foreach($this->posts as $id => $post)
			{
				if(preg_match('/board\=[0-9\.]+/', $post['board']['link'], $match) > 0)
					$this->posts[$id]['board']['link'] = str_replace($match[0], $match[0] .'#ptop', $post['board']['link']);

				if(preg_match('/msg([0-9]+)/', $post['href'], $msg) > 0)
					$this->posts[$id]['href'] = str_replace(';topicseen#new', '#msg'. $msg[1], $post['href']);

				$this->topics[] = $post['topic'];
				$this->isRead[$user_info['id']][$post['topic']] = empty($post['new']) ? '0' : '1';
			}
			if(isset($this->isRead[$user_info['id']][$rtopic]) && $this->isRead[$user_info['id']][$rtopic] != '1')
				$this->isRead[$user_info['id']][$rtopic] = '1';

			return array($this->topics, $this->isRead, $this->posts);
		}
	}

	/**
	* ShowContent.
	* Output the content and add necessary javascript
	*/
	function pmxc_ShowContent()
	{
		global $context, $user_info, $txt;

		$numpost = count($this->posts);
		echo '
			<div style="margin:-2px 0 2px 0;">';

		foreach($this->posts as $post)
		{
			$numpost--;
			if(!empty($this->cfg['config']['settings']['showboard']))
				echo '
				<div class="pmxshorttxt"><b>'. $txt['pmx_text_board'] .'</b>'. $post['board']['link'] .'</div>';

			if(preg_match('~msg[0-9]+~i', $post['href'], $match) > 0)
				$post['href'] = str_replace('#new', '#'. $match[0], $post['href']);

			echo '
				<div class="pmxshorttxt'. (empty($this->isRead[$user_info['id']][$post['topic']]) ? 'new">
					<img src="'. $context['pmx_imageurl'] .'unread.gif" alt="*" title="" />' : '">') .'
					<b>'. $txt['pmx_text_topic'] .'</b>
					<a href="'. $post['href'] .'">'. $post['subject'] .'</a>
				</div>
				<div class="pmxshorttxt"><b>'. $txt['by'] .'</b> '. $post['poster']['link'] . (!empty($this->cfg['config']['settings']['recentsplit']) ? ', ' : '<br />') .'['. $post['time'] .']</div>'. ($numpost > 0 ? '<hr class="pmx_hr" />' : '');
		}
		echo '
			</div>';
	}
}
?>