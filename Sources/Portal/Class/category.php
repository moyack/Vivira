<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file category.php
 * Systemblock Category
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* @class pmxc_category
* Systemblock Category
* @see category.php
*/
class pmxc_category extends PortaMxC_SystemBlock
{
	var $categories;
	var $articles;
	var $curCat;
	var $firstCat;
	var $postarray;
	var $php_content;
	var $php_vars;
	var $eclCook;

	/**
	* InitContent.
	* Checks the cache status and create the content.
	*/
	function pmxc_InitContent()
	{
		global $context, $pmxCacheFunc;

		// requested category and limited to admins??
		if(!empty($this->cfg['config']['static_block']))
		{
			$this->cfg['blocktype'] = 'catblock';
			if(!empty($this->cfg['config']['request']) && !allowPmx('pmx_admin'))
				$this->visible = false;

			// requested category ecl check
			if(!empty($this->cfg['config']['check_ecl']) && !checkECL_Cookie(!empty($this->cfg['config']['check_eclbots'])))
				$this->visible = false;
		}

		// if visible init the content
		if($this->visible)
		{
			$this->postKey = 'pmxpost_'. $this->cfg['blocktype'] . $this->cfg['id'];
			$this->postarray[$this->postKey] = array('cat' => '', 'child' => '', 'art' => '', 'pg' => '0');

			// cleanup $_GET for vars we need here
			$GETpost = null;
			if(!empty($_GET))
			{
				$GETpost = $_GET;
				$tmp = array_diff(array_keys($this->postarray[$this->postKey]), array_keys($GETpost));
				while(list($d, $key) = pmx_each($tmp))
					unset($GETpost[$key]);
			}

			if(empty($GETpost) && empty($_POST[$this->postKey]) && !empty($_SESSION['PortaMx'][$this->postKey]))
				$this->postarray[$this->postKey] = array_merge($this->postarray[$this->postKey], $_SESSION['PortaMx'][$this->postKey]);
			if(empty($this->postarray[$this->postKey]['cat']) && !empty($this->cfg['static_block']))
				$this->postarray[$this->postKey]['cat'] = $this->cfg['config']['settings']['category'];

			// reset current page ?
			if(!empty($_POST[$this->postKey]))
			{
				$data = array('cat' => '', 'child' => '', 'art' => '', 'pg' => '0');
				if(!empty($_POST[$this->postKey]))
					$this->postarray[$this->postKey] = pmx_GetPostKey($this->postKey, $data);
				else
				{
					if(isset($_SESSION['PortaMx'][$this->postKey]))
						$this->postarray[$this->postKey] = $_SESSION['PortaMx'][$this->postKey];
					$data = $this->postarray[$this->postKey] = array_merge($this->postarray[$this->postKey], $GETpost);
				}
			}
			elseif(empty($GETpost) && empty($_POST[$this->postKey]) && isset($_SESSION['PortaMx'][$this->postKey]))
				$this->postarray[$this->postKey] = $_SESSION['PortaMx'][$this->postKey];

			if(!empty($GETpost))
				$this->postarray[$this->postKey] = array_merge($this->postarray[$this->postKey], $GETpost);

			$_SESSION['PortaMx'][$this->postKey] = $this->postarray[$this->postKey];

			// called from static category block?
			if(!empty($this->cfg['config']['static_block']))
			{
				$this->cfg['blocktype'] = 'catblock';

				// get the category
				if(!empty($this->cfg['cache']))
					$cachedata = $pmxCacheFunc['get']($this->cache_key, $this->cache_mode);
				else
					$cachedata = null;

				if($cachedata !== null)
				{
					$cats['config'] = pmx_json_decode($cats['config'], true);
					list($this->categories, $this->articles) = $cachedata;
					$cats = $this->categories[$this->cfg['config']['settings']['category']];
				}
				else
				{
					$cats = PortaMx_getCatByID(null, $this->cfg['config']['settings']['category']);
					if(!empty($cats))
					{
						$cats['config'] = pmx_json_decode($cats['config'], true);

						// inherit acs from block?
						if(!empty($this->cfg['config']['settings']['inherit_acs']))
								$cats['acsgrp'] = $this->cfg['acsgrp'];

						// Main cat can show?
						if(!allowPmxGroup($cats['acsgrp']))
							return false;

						// Main cat ecl check
						if(!empty($cats['config']['check_ecl']) && !checkECL_Cookie(!empty($cats['config']['check_eclbots'])))
							return false;

						// get category(s) and articles
						$this->getCatsAndChilds($cats, $cats['acsgrp'], !empty($cats['config']['settings']['inherit_acs']));

						if(!empty($this->cfg['cache']))
							$pmxCacheFunc['put']($this->cache_key, array($this->categories, $this->articles), $this->cache_time, $this->cache_mode);
					}
				}

				// nothing found
				if(empty($cats))
					return false;

				// check category elc
				foreach($this->categories as $eclKey => $eclCat)
				{
					if(!empty($eclCat['config']['check_ecl']) && !checkECL_Cookie(!empty($eclCat['config']['check_eclbots'])))
						unset($this->categories[$eclKey]);
				}

				$this->postarray[$this->postKey]['cat'] = $cats['name'];
				$this->firstcat = (!empty($this->postarray[$this->postKey]['child']) ? $this->postarray[$this->postKey]['child'] : $cats['name']);
				$this->curCat = null;
				if(isset($this->categories[$this->firstcat]))
					$this->curCat = $this->categories[$this->firstcat];

				if(!is_null($this->curCat) && !empty($this->postarray[$this->postKey]['art']))
				{
					$found = false;
					foreach($this->articles[$this->curCat['name']] as $article)
						$found = $article['name'] == $this->postarray[$this->postKey]['art'] ? true : $found;

					if(empty($found))
					{
						$this->postarray[$this->postKey]['art'] = $this->articles[$this->curCat['name']][0]['name'];
						$found = true;
					}
				}
				else
					$found = true;

				if(!is_null($this->curCat) && !empty($found))
				{
					if($this->cfg['config']['settings']['usedframe'] == 'block')
					{
						$this->curCat['config']['visuals'] = $this->cfg['config']['visuals'];
						$this->curCat['config']['csfile'] = $this->cfg['config']['cssfile'];
					}

					// check framemode
					if($this->cfg['config']['settings']['usedframe'] == 'cat')
					{
						$this->cfg['config']['skip_outerframe'] = true;
						$this->curCat['catid'] = $this->curCat['id'];
					}
					else
					{
						$this->curCat['config']['skip_outerframe'] = true;
						$this->curCat['config']['visuals']['frame'] = $this->cfg['config']['visuals']['frame'];
						$this->cfg['catid'] = $this->cfg['id'];
						$this->cfg['blocktype'] = 'catblock';
					}
				}
				else
					$this->visible = false;
			}

			// else requested cat
			else
			{
				// get cat data and all childs
				$cats = PortaMx_getCatByID(null, $this->postarray[$this->postKey]['cat']);

				// Main cat can show?
				if(!allowPmxGroup($cats['acsgrp']))
					return false;

				if(!is_array($cats['config']))
					$cats['config'] = pmx_json_decode($cats['config'], true);

				// Main cat ecl check
				if(!empty($cats['config']['check_ecl']) && !checkECL_Cookie(!empty($cats['config']['check_eclbots'])))
					return false;

				// get categoy(s) and articles
				$this->getCatsAndChilds($cats, $cats['acsgrp'], !empty($cats['config']['settings']['inherit_acs']));
				$this->firstcat = (!empty($this->postarray[$this->postKey]['child']) ? $this->postarray[$this->postKey]['child'] : $this->postarray[$this->postKey]['cat']);

				$this->curCat = null;
				if(isset($this->categories[$this->firstcat]))
					$this->curCat = $this->categories[$this->firstcat];

				if(!is_null($this->curCat) && !empty($this->postarray[$this->postKey]['art']))
				{
					$found = false;
					foreach($this->articles[$this->curCat['name']] as $article)
						$found = $article['name'] == $this->postarray[$this->postKey]['art'] ? true : $found;
				}
				else
					$found = true;

				if(!is_null($this->curCat) && !empty($found))
				{
					// save titles for linktree
					$context['pmx']['pagenames']['cat'] = $this->categories[$cats['name']]['title'];
					if(empty($context['pmx']['pagenames']['cat']))
						$context['pmx']['pagenames']['cat'] = htmlspecialchars($cats['name'], ENT_QUOTES);

					if(!empty($this->postarray[$this->postKey]['child']))
					{
						$context['pmx']['pagenames']['child'] = $this->curCat['title'];
						if(empty($context['pmx']['pagenames']['child']))
							$context['pmx']['pagenames']['child'] = htmlspecialchars($this->curCat['name'], ENT_QUOTES);
					}

					$this->cfg['uniID'] = 'cat'. $this->categories[$cats['name']]['id'];
					$this->cfg['config']['skip_outerframe'] = true;
					$this->curCat['catid'] = $this->curCat['id'];
				}
				else
					$this->visible = false;
			}
		}

		if(!empty($this->visible) && !empty($this->articles))
		{
			// handle special php articles
			foreach($this->articles as $cn => $artlist)
			{
				foreach($artlist as $id => $article)
				{
					if($article['ctype'] == 'php' && preg_match('~\[\?pmx_initphp(.*)pmx_initphp\?\]~is', $article['content'], $match))
						eval($match[1]);

					if(!empty($article['config']['cssfile']))
						$this->getCustomCSS($article);
				}
			}
		}
		return $this->visible;
	}

	/**
	* Get category and his childs
	*/
	function getCatsAndChilds($cats, $acs, $acs_inherit = false)
	{
		global $context, $pmxcFunc;

		$catIDs = array();
		$catNames = array();
		$this->categories = array();
		$corder = $cats['catorder'];
		$cat = PortaMx_getCatByOrder(array($cats), $corder);

		while(is_array($cat))
		{
			if(!is_array($cat['config']))
				$cat['config'] = pmx_json_decode($cat['config'], true);

			// check ecl
			if(empty($cat['config']['check_ecl']) || !empty($cat['config']['check_ecl']) && checkECL_Cookie(!empty($cat['config']['check_eclbots'])))
			{
				if(!empty($cat['artsum']))
				{
					// get custom css if set
					if(!empty($cat['config']['cssfile']))
						$this->getCustomCSS($cat);

					// inherit acs from block?
					if(!empty($acs_inherit))
							$cat['acsgrp'] = $acs;

					if(allowPmxGroup($cat['acsgrp']))
					{
						$ttl = $this->getUserTitle($cat, $cat['name']);
						$this->categories[$cat['name']] = array(
							'id' => $cat['id'],
							'name' => $cat['name'],
							'artsort' => $cat['artsort'],
							'acsgrp' => $cat['acsgrp'],
							'config' => $cat['config'],
							'side' => $this->cfg['side'],
							'blocktype' => 'category',
							'customclass' => '',
							'title' => $ttl,
						);
						$catIDs[] = $cat['id'];
						$catNames[$cat['id']] = $cat['name'];
					}
				}
			}
			else
				break;

			$addSub = (!empty($cat['config']['settings']['addsubcats']) && $cat['config']['settings']['showmode'] == 'sidebar') || (!empty($cat['config']['settings']['showsubcats']) && $cat['config']['settings']['showmode'] == 'pages');
			if(!empty($addSub))
			{
				$corder = PortaMx_getNextCat($corder);
				$cat = PortaMx_getCatByOrder(array($cats), $corder);
			}
			else
				break;
		}

		if(!empty($catIDs))
		{
			// get articles for any cat
			$request = $pmxcFunc['db_query']('', '
				SELECT a.id, a.name, a.acsgrp, a.catid, a.ctype, a.config, a.owner, a.active, a.created, a.updated, a.approved, a.content, CASE WHEN m.real_name = {string:empty} THEN m.member_name ELSE m.real_name END AS mem_name
				FROM {db_prefix}portal_articles AS a
				LEFT JOIN {db_prefix}members AS m ON (a.owner = m.id_member)
				WHERE a.catid IN ({array_int:cats}) AND a.active > 0 AND a.approved > 0
				ORDER BY a.id',
				array(
					'cats' => $catIDs,
					'empty' => '',
				)
			);

			while($row = $pmxcFunc['db_fetch_assoc']($request))
			{
				$row['config'] = pmx_json_decode($row['config'], true);

				// check ecl
				if(empty($row['config']['check_ecl']) || !empty($row['config']['check_ecl']) && checkECL_Cookie(!empty($row['config']['check_eclbots'])))
				{
					if(!empty($this->categories[$catNames[$row['catid']]]['config']['settings']['inherit_acs']))
						$row['acsgrp'] = $this->categories[$catNames[$row['catid']]]['acsgrp'];

					if(allowPmxGroup($row['acsgrp']))
					{
						if(!empty($this->categories[$catNames[$row['catid']]]['config']['settings']['catstyle']))
						{
							$row['config']['visuals'] = $this->categories[$catNames[$row['catid']]]['config']['visuals'];
							$row['config']['cssfile'] = $this->categories[$catNames[$row['catid']]]['config']['cssfile'];
						}

						$row['side'] = $this->cfg['side'];
						$row['blocktype'] = !empty($this->cfg['config']) ? 'static_article' : 'article';
						$row['member_name'] = $row['mem_name'];

						// get custom css if set
						if(!empty($row['config']['cssfile']))
							$this->getCustomCSS($row);

						$this->articles[$catNames[$row['catid']]][] = $row;
					}
				}
			}
			$pmxcFunc['db_free_result']($request);
		}

		// articles found?
		$ccats = $this->categories;
		foreach($ccats as $cname => $cdata)
		{
			if(!empty($this->articles[$cname]))
			{
				$this->articles[$cname] = PortaMx_ArticleSort($this->articles[$cname], $this->categories[$cname]['artsort']);

				// if article reqested, get the tile
				if(!empty($this->postarray[$this->postKey]['art']) && $cname == (empty($this->postarray[$this->postKey]['child']) ? $this->postarray[$this->postKey]['cat'] : $this->postarray[$this->postKey]['child']))
				{
					foreach($this->articles[$cname] as $art)
					{
						if($art['name'] == $this->postarray[$this->postKey]['art'])
						{
							$context['pmx']['pagenames']['art'] = $this->getUserTitle($art);
							if(empty($context['pmx']['pagenames']['art']))
								$context['pmx']['pagenames']['art'] = htmlspecialchars($art['name'], ENT_QUOTES);
							break;
						}
					}
				}
			}
			else
				unset($this->categories[$cname]);
		}
	}

	/**
	* create a url for requested or static block
	**/
	function GetUrl($data = '', $onclick = true)
	{
		global $scripturl, $modSettings, $boardurl;

		$baseurl = !empty($modSettings['sef_enabled']) ? $boardurl .'/' : $scripturl .'?';
		$data = strpos($data, 'child') !== false && !empty($this->postarray[$this->postKey]['cat']) ? 'cat='. $this->postarray[$this->postKey]['cat'] .';'. $data : $data;
		$data = function_exists('create_sefurl') ? str_replace($baseurl, '', create_sefurl($scripturl .'?'. $data)) : $data;
		return (!empty($data) ? $baseurl . $data .'"': '') . (!empty($onclick) ? ' onclick="pmx_StaticBlockSub(\''. $this->postKey .'\', this, \'/'. rtrim($baseurl, '?/') .'/\', \''. $this->cfg['uniID'] .'\')"' : '');
	}

	/**
	* ShowContent
	*/
	function pmxc_ShowContent()
	{
		global $context;

		if(!empty($this->cfg['static_block']))
			echo '
			<div id="block.id.'. $this->cfg['id'] .'" style="display:'. (empty($this->cfg['active']) ? 'none': 'block') ,'">';

		echo '
			<form id="'. $this->postKey .'_form" accept-charset="'. $context['character_set'] .'" method="post">
				<input type="hidden" id="'. $this->postKey .'" name="'. $this->postKey .'" value="" />';

		// show all articles on a page
		if($this->curCat['config']['settings']['showmode'] == 'pages')
		{
			$artCount = count($this->articles[$this->curCat['name']]);
			if(empty($this->curCat['config']['settings']['pages']))
				$this->curCat['config']['settings']['pages'] = $artCount;

			$subcats = array();
			foreach($this->categories as $name => $cat)
			{
				if($this->firstcat == $name)
					$sbCat = $cat;
				if($this->curCat['name'] == $name)
					$artcat = '<a href="'. $this->GetUrl($this->postarray[$this->postKey]['cat'] == $name ? 'cat='. $this->postarray[$this->postKey]['cat'] : 'cat='. $this->postarray[$this->postKey]['cat'] .';child='. $name) .'>'. $this->categories[$this->curCat['name']]['title'] .'</a>';

				if(!empty($this->curCat['config']['settings']['addsubcats']))
				{
					if($this->curCat['name'] != $name)
					{
						if($this->postarray[$this->postKey]['cat'] != $name)
							$subcats[] = array('name' => $name, 'link' => '<a href="'. $this->GetUrl('child='. $name) .'>'. $cat['title'] .'</a><br />');
						else
							$subcats[] = array('name' => $name, 'link' => '<a href="'. $this->GetUrl('cat='. $name) .'>'. $cat['title'] .'</a><br />');
					}
				}
			}

			// create the pageindex
			$this->curCat['page'] = 0;
			if(!empty($this->curCat['config']['settings']['pageindex']) || $artCount > $this->curCat['config']['settings']['pages'])
			{
				if(!is_null($this->postarray[$this->postKey]['pg']))
					$this->curCat['page'] = $this->postarray[$this->postKey]['pg'];

				if(empty($this->postarray[$this->postKey]['cat']))
					$this->postarray[$this->postKey]['cat'] = $this->cfg['config']['settings']['category'];

				$url = 'cat='. $this->postarray[$this->postKey]['cat'] .';'. (!empty($this->postarray[$this->postKey]['child']) ? 'child='. $this->postarray[$this->postKey]['child'] .';' : '') .'pgkey='. $this->cfg['uniID'] .';pg=%1$d';
				$url = preg_replace('~pgkey='. $this->cfg['uniID'] .';pg=[a-zA-Z0-0;]+~', '', getCurrentUrl(true)) . $url;
				$pageindex = $this->pmxc_makePageIndex($url, $this->curCat['page'], $artCount, $this->curCat['config']['settings']['pages']);
				$pageindex = str_replace('href="', $this->GetUrl() .' href="', $pageindex);
			}

			// show category frame?
			if(in_array($this->curCat['config']['settings']['framemode'], array('both', 'category')))
			{
				$this->curCat['id'] = !empty($this->cfg['static_block']) ? $this->cfg['id'] : $this->curCat['id'];
				$order = array_flip(array_keys($context['pmx']['viewblocks']['front']));

				Pmx_Frame_top($this->curCat, intval(isset($order[$this->curCat['id']]) && $order[$this->curCat['id']] <= count($order)));
			}

			// top pageindex
			if(!empty($pageindex))
				echo '
					<div class="pagelinks pmx_pageTop">', $pageindex, '</div>';

			echo '
					<table class="pmx_table">
						<tr>';

			if(!empty($this->curCat['config']['settings']['showsubcats']))
			{
				$subcats = array();
				$firstcat = false;
				foreach($this->categories as $name => $cat)
				{
					if($this->firstcat == $name && empty($firstcat))
					{
						$firstcat = true;
						$sbCat = $cat;
						$subcats[] = array('name' => $name, 'link' => '<b>'. PortaMx_getTitle($cat) .'</b><br />');
					}
					else
					{
						if($this->postarray[$this->postKey]['cat'] != $name)
							$subcats[] = array('name' => $name, 'link' => '<a href="'. $this->GetUrl('child='. $name) .'>'. PortaMx_getTitle($cat) .'</a><br />');
						else
							$subcats[] =  array('name' => $name, 'link' => '<a href="'. $this->GetUrl('cat='. $name) .'>'. PortaMx_getTitle($cat) .'</a><br />');
					}
				}

				$sbCat['config']['visuals']['header'] = 'none';
				$sbCat['config']['visuals']['bodytext'] = 'smalltext';
				$sbCat['config']['visuals']['body'] = 'windowbg sidebar';
				$sbCat['config']['visuals']['frame'] = 'border';
				$sbCat['config']['innerpad'] = '3,5';
				$sbCat['config']['collapse'] = 0;

				if(!empty($this->curCat['config']['settings']['sbpalign'])&& !empty($subcats))
				{
					echo '
							<td style="text-align:left">
								<div  class="smalltext" style="width:'. $this->curCat['config']['settings']['catsbarwidth'] .'px; margin-right:'. $context['pmx']['settings']['panelpad'] .'px;">';

					$this->WriteSidebar($sbCat, $subcats, '', '');

					echo '
								</div>
							</td>';
				}
			}

			// output the article content
			echo '
							<td style="width:100%">';

			$sumart = null;
			foreach($this->articles[$this->curCat['name']] as $cnt => $article)
			{
				if($cnt >= $this->curCat['page'] && $cnt - $this->curCat['page'] < $this->curCat['config']['settings']['pages'])
				{
					$sumart = is_null($sumart) ? ($artCount - $this->curCat['page'] > $this->curCat['config']['settings']['pages'] ? $this->curCat['config']['settings']['pages'] : ($artCount < $this->curCat['config']['settings']['pages'] ? $artCount : $artCount - ($this->curCat['page'] +1))) : $sumart;
					$sumart--;

					// show article frame?
					$article['config']['collapse'] = $this->curCat['config']['settings']['showmode'] != 'pages' ? 0 : $article['config']['collapse'];
					if(in_array($this->curCat['config']['settings']['framemode'], array('both', 'article')))
					{
						Pmx_Frame_top($article, $sumart);
						$this->WriteContent($article);
						Pmx_Frame_bottom();
					}
					else
						$this->WriteContent($article);

					if(empty($sumart))
						echo '
								<div id="botcat'. $this->cfg['uniID'] .'"></div>';
				}
			}

			echo '
							</td>';

			// show childcats in the sidebar?
			if(empty($this->curCat['config']['settings']['sbpalign']) && !empty($subcats))
			{
				echo '
							<td style="text-align:right">
								<div  class="smalltext" style="width:'. $this->curCat['config']['settings']['catsbarwidth'] .'px; margin-left:'. $context['pmx']['settings']['panelpad'] .'px;">';

					$this->WriteSidebar($sbCat, $subcats, '', '');

				echo '
								</div>
							</td>';
			}

			echo '
						</tr>
					</table>';

			// bottom pageindex
			if(!empty($pageindex))
				echo '
					<div class="pagelinks pmx_pageBot">', $pageindex, '</div>';

			// show category frame?
			if(in_array($this->curCat['config']['settings']['framemode'], array('both', 'category')))
				Pmx_Frame_bottom();
		}

		// first article and titles in the sidebar
		else
		{
			// show category frame?
			if(in_array($this->curCat['config']['settings']['framemode'], array('both', 'category')))
			{
				$this->curCat['id'] = !empty($this->cfg['static_block']) ? $this->cfg['id'] : $this->curCat['id'];
				$order = array_flip(array_keys($context['pmx']['viewblocks']['front']));

				Pmx_Frame_top($this->curCat, intval(isset($order[$this->curCat['id']]) && $order[$this->curCat['id']] <= count($order)));
			}

			$subcats = array();
			foreach($this->categories as $name => $cat)
			{
				if($this->firstcat == $name)
					$sbCat = $cat;
				if($this->curCat['name'] == $name)
					$artcat = '<a href="'. $this->GetUrl($this->postarray[$this->postKey]['cat'] == $name ? 'cat='. $this->postarray[$this->postKey]['cat'] : 'cat='. $this->postarray[$this->postKey]['cat'] .';child='. $name, !empty($this->cfg['static_block'])) .'>'. PortaMx_getTitle($cat) .'</a>';

				if(!empty($this->curCat['config']['settings']['addsubcats']))
				{
					if($this->curCat['name'] != $name)
					{
						if($this->postarray[$this->postKey]['cat'] != $name)
							$subcats[] = array('name' => $name, 'link' => '<a href="'. $this->GetUrl('child='. $name) .'>'. PortaMx_getTitle($cat) .'</a><br />');
						else
							$subcats[] = array('name' => $name, 'link' => '<a href="'. $this->GetUrl('cat='. $name) .'>'. PortaMx_getTitle($cat) .'</a><br />');
					}
				}
			}

			$curart = empty($this->postarray[$this->postKey]['art']) ? $this->articles[$this->curCat['name']][0]['name'] : $this->postarray[$this->postKey]['art'];
			$sbCat['config']['visuals']['header'] = 'none';
			$sbCat['config']['visuals']['bodytext'] = 'smalltext';
			$sbCat['config']['visuals']['body'] = 'windowbg sidebar';
			$sbCat['config']['visuals']['frame'] = 'border';
			$sbCat['config']['innerpad'] = '3,5';
			$sbCat['config']['collapse'] = 0;

			echo '
					<table class="pmx_table">
						<tr>';

			// subcategory list at left
			if(!empty($this->curCat['config']['settings']['sbmalign']))
			{
				echo '
							<td style="text-align:left">
								<div class="smalltext" style="width:'. $this->curCat['config']['settings']['sidebarwidth'] .'px; margin-right:' . $context['pmx']['settings']['panelpad'] .'px;">';

				$this->WriteSidebar($sbCat, $subcats, $artcat, $curart);

				echo '
								</div>
							</td>';
			}
			echo '
							<td style="width:100%">';

			$count = 0;
			foreach($this->articles[$this->curCat['name']] as $article)
				$count += intval($article['name'] == $curart);

			foreach($this->articles[$this->curCat['name']] as $article)
			{
				if($article['name'] == $curart)
				{
					$count--;

					// show article frame?
					$article['config']['collapse'] = $this->curCat['config']['settings']['showmode'] != 'pages' ? 0 : $article['config']['collapse'];
					if(in_array($this->curCat['config']['settings']['framemode'], array('both', 'article')))
					{
						Pmx_Frame_top($article, $count);
						$this->WriteContent($article);
						Pmx_Frame_bottom();
					}
					else
						$this->WriteContent($article);
					break;
				}
			}

			echo '
						</td> ';

			// subcategory list at right
			if(empty($this->curCat['config']['settings']['sbmalign']))
			{
				echo '
							<td style="text-align:right">
								<div  class="smalltext" style="width:'. $this->curCat['config']['settings']['sidebarwidth'] .'px; margin-left:'. $context['pmx']['settings']['panelpad'] .'px;">';

				$this->WriteSidebar($sbCat, $subcats, $artcat, $curart);

				echo '
								</div>
							</td>';
			}

			echo '
						</tr>
					</table>';

			Pmx_Frame_bottom();
		}

		echo '
				</form>';

		if(!empty($this->cfg['static_block']))
			echo '
			</div>';

		return 1;
	}

	/**
	* Write out the Sidebar
	*/
	function WriteSidebar($sbCat, $subcats, $artcat, $curart)
	{
		global $txt;

		Pmx_Frame_top($sbCat, 0, true);

		if(!empty($curart))
		{
			echo '
							<em class="pmx_emsbtop">'. $txt['pmx_more_articles'] .'</em><strong>'. strip_tags($artcat) .'</strong><hr class="pmx_hrsb" />';

			foreach($this->articles[$this->curCat['name']] as $article)
			{
				$ttl = PortaMx_getTitle($article);
				if(empty($ttl))
					$ttl = htmlspecialchars($article['name'], ENT_QUOTES);

				if($curart == $article['name'])
					echo '
							<b>'. $ttl .'</b><br />';
				else
					echo '
							<a href="'. $this->GetUrl((!empty($this->postarray[$this->postKey]['child']) ? 'child='. $this->postarray[$this->postKey]['child'] .';' : 'cat='. $this->postarray[$this->postKey]['cat'] .';') .'art='. $article['name']) .'>'. $ttl .'</a><br />';
			}
		}

		if(!empty($subcats))
		{
			if($subcats[0]['name'] == $this->postarray[$this->postKey]['cat'])
			{
				echo '
							<em class="pmx_emsb'. (empty($curart) ? 'top' : '') .'">'. $txt['pmx_main_category'] .'</em><hr class="pmx_hrsb" />'. $subcats[0]['link'];
				unset($subcats[0]);
			}

			if(count($subcats) > 0)
				echo '
							<em class="pmx_emsb">'. $txt['pmx_more_categories'] .'</em><hr class="pmx_hrsb" />';

			foreach($subcats as $cat)
				echo $cat['link'];
		}

		Pmx_Frame_bottom();
	}

	/**
	* Write out the Content
	*/
	function WriteContent($article)
	{
		global $context, $modSettings, $scripturl, $user_info, $txt;

		$printdir = 'ltr';
		$printID = 'catart'. $article['id'];
		$printChars = $context['character_set'];
		$statID = 'art'. $article['id'] . $this->cfg['side'];
		$tease = 0;
		$phpcount = 0;
		$noLB = !empty($modSettings['dont_use_lightbox']) || !empty($article['config']['settings']['disableHSimg']);
		$context['lbimage_data'] = array('lightbox_id' => (empty($noLB) ? 'cat-'. $article['blocktype'] .'-'. $article['id'] : null));

		if($article['ctype'] == 'php')
		{
			// Check we have a show part
			if(preg_match('~\[\?pmx_showphp(.*)pmx_showphp\?\]~is', $article['content'], $match))
			{
				$article['content'] = $match[1];
				$phpcount = 1;
			}
		}

		else
		{
			if($article['ctype'] == 'bbc_script')
			{
				// youtube tag as link ?
				$article['content'] = PortaMx_BBCsmileys(parse_bbc($article['content'], true, '', array(), ($user_info['is_guest'] || !empty($article['config']['disableYoutube']))));
				$tease = $article['config']['settings']['teaser'];
			}

			elseif($article['ctype'] == 'html')
			{
				$article['content'] = '<div class="htmlblock">'. $article['content'] .'</div>';
				$tease = !empty($article['config']['settings']['teaser']) ? -1 : 0;
			}

			else
				$tease = $article['config']['settings']['teaser'];
		}

		// remove or add highslide code
		if($article['ctype'] != 'bbc_script')
			$article['content'] = pmx_ContentLightBox($article['content']);

		// article teaser set?
		if(!empty($tease))
		{
			$tmp = '
								<div id="short_'. $statID .'">
								'. PortaMx_Tease_posts($article['content'], $tease, '<div class="smalltext" style="text-align:right;"><a id="href_short_'. $statID .'" href="'.$scripturl .'" style="padding: 0 5px;" onclick="ShowHTML(\''. $statID .'\')">'. $txt['pmx_readmore'] .'</a></div>') .'
								</div>';

			// if teased?
			if(!empty($context['pmx']['is_teased']))
			{
				$article['content'] = preg_replace('~<div style="page-break-after\:(.*)<\/div>~i', '', $article['content']);
				$article['content'] = ''.
								(!empty($article['config']['settings']['printing'])
								? '
								<div id="full_'. $statID .'" style="display:none;">
									<img class="pmx_printimg" src="'. $context['pmx_imageurl'] .'Print.png" alt="Print" title="'. $txt['pmx_text_printing'] .'" onclick="PmxPrintPage(\''. $printdir .'\', \''. $printID .'\', \''. $printChars .'\', \''. $this->getUserTitle($article, $article['name']) .'\', \''. $txt['lightbox_help'] .'\', \''. $txt['lightbox_label'] .'\')" />
									<div id="print'. $printID .'">'.
										$article['content'] .'
									</div>
									<div class="smalltext" style="text-align:right;">
										<a id="href_full_'. $statID .'" href="'.$scripturl .'" style="padding: 0 5px;" onclick="ShowHTML(\''. $statID .'\')">'. $txt['pmx_readclose'] .'</a>
									</div>
								</div>'
								: '
								<div id="full_'. $statID .'" style="display:none;">'.
									$article['content'] .'
									<div class="smalltext" style="text-align:right;">
										<a id="href_full_'. $statID .'" href="'.$scripturl .'" style="padding: 0 5px;" onclick="ShowHTML(\''. $statID .'\')">'. $txt['pmx_readclose'] .'</a>
									</div>
								</div>'). $tmp ;
			}
			unset($tmp);
		}

		elseif(!empty($article['config']['settings']['printing']))
		{
			$article['content'] = '
								<img class="pmx_printimg" src="'. $context['pmx_imageurl'] .'Print.png" alt="Print" title="'. $txt['pmx_text_printing'] .'" onclick="PmxPrintPage(\''. $printdir .'\', \''. $this->cfg['id'] .'\', \''. $printChars .'\', \''. $this->getUserTitle($article, $article['name']) .'\', \''. $txt['lightbox_help'] .'\', \''. $txt['lightbox_label'] .'\')" />
								<div id="print'. $this->cfg['id'] .'">'.
									preg_replace('~<div style="page-break-after\:(.*)<\/div>~i', '', $article['content']) .'
								</div>';
		}

		// check for inside php code
		if($article['ctype'] == 'html' || $article['ctype'] == 'script')
			$havePHP = PortaMx_GetInsidePHP($article['content']);

		if(!empty($havePHP))
			eval($article['content']);
		else
			echo $article['content'];

		if(!empty($article['config']['settings']['showfooter']))
		{
			echo '
							<div style="clear:both;min-height:20px;margin-top:7px;"><hr class="pmx_hr" />
								<div class="smalltext" style="float:left;">
									'. $txt['pmx_text_createdby'] . (!empty($article['member_name']) ? '<a href="'. $scripturl .'?action=profile;u='. $article['owner'] .'">'. $article['member_name'] .'</a>' : $txt['pmx_user_unknown']) .', '. timeformat($article['created']) .'
								</div>';

			if(!empty($article['updated']))
			{
				echo '
								<div class="smalltext" style="float:right;">
									'. $txt['pmx_text_updated'] . timeformat($article['updated']) .'
								</div>';
			}
			echo '
							</div>';
		}
	}
}
?>