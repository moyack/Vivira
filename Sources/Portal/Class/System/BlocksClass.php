<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file PortaMx_BlocksClass.php
 * Global Blocks class
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* @class PortaMxC_Blocks
* The Global Blocks class.
* @see PortaMx_BlocksClass.php
*/
class PortaMxC_Blocks
{
	var $cfg;							///< common config
	var $visible;					///< visibility flag
	var $cache_key;				///< cache key
	var $cache_mode;			///> cache mode
	var $cache_time;			///< cache time
	var $cache_trigger;		///< cache trigger
	var $cache_status;		///< current cache status
	var $startpage;				///< pageindex start page
	var $postspage;				///< items on a page
	var $pageindex;				///< pageindex sting

	/**
	* The Contructor.
	* Saved the config and checks the visiblity access.
	* If access true, the block css file is loaded if exist.
	*/
	function __construct($blockconfig, &$visible)
	{
		global $context, $modSettings, $user_info, $maintenance;

		// load the config
		if(isset($blockconfig['config']))
			$blockconfig['config'] = pmx_json_decode($blockconfig['config'], true);
		$this->cfg = $blockconfig;
		$visible = false;

		// check active or in Admin mode
		if(!empty($this->cfg['active']) || (!empty($context['In_Administration']) && isset($_REQUEST['area']) && $_REQUEST['area'] == 'pmx_blocks'))
		{
			// no ecl check set, or ecl accepted .. done
			if(empty($this->cfg['config']['check_ecl']) || !empty($this->cfg['config']['check_ecl']) && checkECL_Cookie(!empty($this->cfg['config']['check_eclbots'])))
				$visible = true;
		}

		// hide on maintenance?
		if(!empty($maintenance) && !empty($this->cfg['config']['maintenance_mode']))
			$visible = false;
		// Hide on Bots?
		if(!empty($user_info['possibly_robot']) && !empty($this->cfg['config']['check_eclbots']))
			$visible = false;

		if(!empty($visible))
		{
			if(isset($this->cfg['config']['static_block']))
				$this->cfg['static_block'] = $this->cfg['config']['static_block'];

			$this->startpage = 0;
			$this->cfg['uniID'] = 'blk'. $this->cfg['id'];

			// set the cache_key, cache time and trigger
			$this->cache_key = $this->cfg['blocktype'] . $this->cfg['id'];
			if(in_array($this->cfg['blocktype'], array_keys($context['pmx']['cache']['blocks'])))
			{
				$this->cache_mode = $context['pmx']['cache']['blocks'][$this->cfg['blocktype']]['mode'];
				$this->cache_time = !empty($this->cfg['cache']) ? $this->cfg['cache'] : $context['pmx']['cache']['blocks'][$this->cfg['blocktype']]['time'];
				if($context['pmx']['cache']['blocks'][$this->cfg['blocktype']]['trigger'] == 'default')
					$this->cache_trigger = $context['pmx']['cache']['default']['trigger'];
				else
					$this->cache_trigger = $context['pmx']['cache']['blocks'][$this->cfg['blocktype']]['trigger'];

				// call cache trigger
				$this->cache_status = $this->pmxc_checkCacheStatus();
				if(!empty($this->cache_status['action']))
					$visible = false;
			}
			else
			{
				$this->cache_mode = false;
				$this->cache_time = 3600;
				$this->cache_trigger = '';
			}

			// check the block visible access
			if($visible)
			{
				// check group access only, if we not in admin section
				if(empty($context['In_Administration']))
				{
					if(isset($this->cfg['inherit_acs']))
						$visible = !empty($this->cfg['inherit_acs']) || allowPmxGroup($this->cfg['acsgrp']);
					else
						$visible = allowPmxGroup($this->cfg['acsgrp']);
				}

				// Show "Home - Community" Buttons?
				if($visible && $context['pmx']['settings']['frontpage'] != 'none' && $this->cfg['side'] == 'front')
					$context['pmx']['showhome'] += (empty($context['In_Administration']) ? intval(!empty($visible)) : intval(!empty($this->cfg['active'])));

				// hide block on request?
				if(!empty($context['pmx']['pageReq']) && $this->cfg['side'] == 'front' && isset($this->cfg['config']['frontplace']) && $this->cfg['config']['frontplace'] == 'hide')
					$visible = false;

				// disable frontpage blocks before init if the frontpage not shown
				if($visible && $this->cfg['side'] == 'front' && empty($context['pmx']['pageReq']) && !empty($_GET))
					$visible = false;

				// hide frontblock on pagerequest?
				if($visible && $this->cfg['side'] == 'front' && (array_key_exists('spage', $context['pmx']['pageReq'])))
					$visible = (isset($this->cfg['config']['frontplace']) && !empty($this->cfg['config']['frontplace'] && $this->cfg['config']['frontplace'] != 'hide'));

				// check page request
				if($visible && $this->cfg['side'] == 'pages')
				{
					$visible = !empty($context['pmx']['pageReq']) || empty($context['pmx']['forumReq']);
					if(!empty($this->cfg['config']['static_block']) || !in_array($this->cfg['blocktype'], array('article', 'category')))
						$this->cfg['config']['ext_opts']['pmxcust'] .= empty($this->cfg['config']['ext_opts']['pmxcust']) ? '@' : '';
				}

				// check dynamic visibility options
				if($visible && !empty($this->cfg['config']['ext_opts']))
				{
					// check mobile / desktop devices
					if(!empty($this->cfg['config']['ext_opts']['device']))
					{
						if(!empty($modSettings['isMobile']) && $this->cfg['config']['ext_opts']['device'] != '1')
							$visible = false;
						if(empty($modSettings['isMobile']) && $this->cfg['config']['ext_opts']['device'] != '2')
							$visible = false;
					}

					// continue on other visibility options
					if($visible)
						$visible = pmx_checkExtOpts(true, $this->cfg['config']['ext_opts'], isset($this->cfg['config']['pagename']) ? $this->cfg['config']['pagename'] : '');
				}
			}

			// if visible check for a custom cssfile
			if(!empty($visible))
				$this->getCustomCSS($this->cfg);

			if(!empty($visible) && $this->cfg['side'] == 'pages' && array_key_exists('spage', $context['pmx']['pageReq']))
			{
				$context['pmx']['pagenames']['spage'] = $this->getUserTitle();
				if(empty($context['pmx']['pagenames']['spage']))
					$context['pmx']['pagenames']['spage'] = htmlspecialchars($this->cfg['config']['pagename'], ENT_QUOTES);
			}
		}
		$this->visible = $visible;

		// setup 2 col blocks for JS
		if(!empty($visible) && empty($context['pmx']['have2colblocks']) && in_array($blockconfig['blocktype'], array('promotedposts', 'boardnews', 'boardnewsmult', 'newposts', 'rss_reader')))
		{
			if(!empty($this->cfg['config']['settings']['split']) && !isset($context['pmx']['have2colblocks']))
			{
				$context['pmx']['have2colblocks'] = true;
				addInlineJavascript("\n\t" .'var have2colblocks = true;');
			}
		}
	}

	/**
	* Handle a block pageindex
	*/
	function pmxc_constructPageIndex($items, $pageitems, $addRestoreTop = true, $startpage = null)
	{
		// hide pageindex if only one page..
		if($items > $pageitems)
		{
			if(!is_null($startpage))
				$this->startpage = $startpage;
			else
				$this->startpage = 0;
			$cururl = preg_replace('~pgkey[a-zA-Z0-9_\-\;\=\/]+pg[0-9\=\/]+~', '', getCurrentUrl(true)) .'pgkey='. $this->cfg['uniID'] .';pg=%1$d;';
			$this->postspage = $pageitems;
			$this->pageindex = constructPageIndex($cururl, $this->startpage, $items, $pageitems, true);
			$this->pageindex = preg_replace('/\;start\=([\%\$a-z0-9]+)/', '', $this->pageindex);

			if(!empty($addRestoreTop))
				$this->pageindex = str_replace('href="', 'onclick="pmxWinGetTop(\''. $this->cfg['uniID'] .'\')" href="', $this->pageindex);
		}
	}

	/**
	* Create the pageindex
	*/
	function pmxc_makePageIndex($url, $start, $items, $pageitems)
	{
		$pageindex = constructPageIndex($url, $start, $items, $pageitems, true);
		$pageindex = preg_replace('/\;start\=([\%\$a-z0-9]+)/', '', $pageindex);
		return $pageindex;
	}

	/**
	* Get a config item.
	* The item can be empty, a single value or a array
	*/
	function getBlockConfig($itemstr = '')
	{
		$item = Pmx_StrToArray($itemstr);
		$result = null;

		if(empty($item))								// no Item, get all
			$result = $this->cfg;
		elseif(!is_array($item))				// no array, get item
			$result = $this->cfg[$item];
		else														// array, find the item
		{
			$ptr = &$this->cfg;
			foreach($item as $key)
				$ptr = &$ptr[$key];

			if(isset($ptr))
				$result = $ptr;
		}
		return $result;
	}

	/**
	* Setting a config item.
	* The item can be a single value or a array
	*/
	function setBlockConfig($itemstr = '', $value = '')
	{
		$result = $this->getBlockConfig($itemstr);
		if(!is_null($result))
		{
			$item = Pmx_StrToArray($itemstr);
			$base = &$this->cfg;
				foreach($item as $val)
					$base = &$base[$val];
			$base = $value;
		}
	}

	/**
	* Get custom css definitions
	*/
	function getCustomCSS(&$cfg)
	{
		// load the custom css
		$cfg['customclass'] = array();

		$result = PortaMx_loadCustomCss($cfg['config']['cssfile'], true);
		if(!empty($result))
		{
			foreach($result['class'] as $key => $val)
			{
				if(!empty($val) && isset($cfg['config']['visuals'][$key]) && $cfg['config']['visuals'][$key] != 'none' && !empty($cfg['config']['visuals'][$key]))
				{
					$cfg['config']['visuals'][$key] = $val;
					$cfg['customclass'][$key] = $val;
				}
			}
		}
	}

	/**
	* Get user title with fallback
	*/
	function getUserTitle($cfg = null, $fallback = '')
	{
		global $language, $user_info;

		if(is_null($cfg))
			$titles = isset($this->cfg['config']['title']) ? $this->cfg['config']['title'] : null;
		else
			$titles = isset($cfg['config']['title']) ? $cfg['config']['title'] : null;

		if(is_array($titles))
		{
			if(isset($user_info['language']) && isset($titles[$user_info['language']]) && !empty($titles[$user_info['language']]))
				return htmlspecialchars($titles[$user_info['language']], ENT_QUOTES);
			else if(!is_array($language) && !empty($titles[$language]))
				return htmlspecialchars($titles[$language], ENT_QUOTES);
			else if(isset($titles['english']) && !empty($titles['english']))
				return htmlspecialchars($titles['english'], ENT_QUOTES);
		}
		elseif(!empty($fallback))
			return htmlspecialchars($fallback, ENT_QUOTES);
		else
			return '';
	}
}

/**
* @class PortaMxC_SystemBlock
* The Global Systemblock Class.
* @see PortaMx_BlocksClass.php
*/
 class PortaMxC_SystemBlock extends PortaMxC_Blocks
{
	/**
	* The display block Methode.
	* ShowBlock prepare the frame, header and the body of each block.
	* Load the a css file if available.
	* After frame, header and body is prepared, the block depended content output is called.
	*/
	function pmxc_ShowBlock($count = 0, $placement = '')
	{
		global $options, $context;

		if(empty($context['pmx']['inAdmin']) && isset($this->cfg['active']) && empty($this->cfg['active']))
			return 0;

		// set block upshrink
		$cook = 'upshr'. $this->cfg['blocktype'] . $this->cfg['id'];
		$cookval = get_cookie($cook);
		if(!empty($this->cfg['config']['collapse_state']) && is_null($cookval))
		{
			$cookval = $options['collapse'. $this->cfg['blocktype'] . $this->cfg['id']] = ($this->cfg['config']['collapse_state'] == '1' ? 1 : 0);
			set_cookie($cook, $cookval);
		}
		else
			$options['collapse'. $this->cfg['blocktype'] . $this->cfg['id']] = intval(!empty($cookval));

		// Placement for Frontpage blocks?
		if(function_exists('Pmx_Frame_top') && (empty($placement) || (!empty($placement) && ($placement == $this->cfg['config']['frontplace'] || empty($this->cfg['config']['frontplace'])))))
		{
			if($this->cfg['blocktype'] == 'category');
				$this->getCustomCSS($this->cfg);

			Pmx_Frame_top($this->cfg, $count);

			// whe have now to call the block depended methode.
			$this->pmxc_ShowContent($count);

			Pmx_Frame_bottom();

			return 1;
		}
		else
			return 0;
	}

	/**
	* checkCacheStatus.
	* If the cache enabled, the cache trigger will be checked.
	* This is often overwrite.
	*/
	function pmxc_checkCacheStatus()
	{
		global $pmxCacheFunc;

		$result = null;
		if(isset($this->cfg['cache']) && $this->cfg['cache'] > 0 && !empty($this->cache_trigger))
		{
			$result = eval($this->cache_trigger);
			if(!empty($result['action']))
				$pmxCacheFunc['drop']($this->cache_key, $this->cache_mode);
		}
		return $result;
	}

	/**
	* InitContent returns the visibility flag.
	* This is mostly overwrite.
	*/
	function pmxc_InitContent()
	{
		// return the visibility flag (true/false)
		return $this->visible;
	}

	/**
	* ShowContent outputs the content of a block.
	* This is often overwrite.
	*/
	function pmxc_ShowContent()
	{
		// Write out the content
		echo $this->cfg['content'];
	}
}
?>