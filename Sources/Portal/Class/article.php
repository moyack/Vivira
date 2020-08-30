<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file article.php
 * Systemblock Article
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* @class pmxc_article
* Systemblock Article
* @see article.php
*/
class pmxc_article extends PortaMxC_SystemBlock
{
	var $articles;
	var $php_content;
	var $php_vars;
	var $eclCook;

	/**
	* InitContent.
	* Checks the cache status and create the content.
	*/
	function pmxc_InitContent()
	{
		global $context, $pmxcFunc, $pmxCacheFunc;

		// if visible init the content
		if($this->visible)
		{
			// called from static article block?
			if(!empty($this->cfg['config']['static_block']))
			{
				$this->cfg['name'] = $this->cfg['config']['settings']['article'];
				$this->cfg['blocktype'] = 'artblock';

				if($this->cfg['config']['settings']['usedframe'] == 'article')
					$this->cfg['config']['skip_outerframe'] = true;
				$this->cfg['config']['visuals']['bodytext'] = '';

				if(!empty($this->cfg['cache']))
					$this->articles = $pmxCacheFunc['get']($this->cache_key, $this->cache_mode);
				else
					$this->articles = array();

				$this->printID = 'artblk'. $this->cfg['id'];
			}

			// requested
			else
			{
				$this->cache_key = 'req-'. $this->cache_key;
				$this->articles = array();
				$this->cfg['config']['skip_outerframe'] = true;
				$this->cfg['config']['settings']['usedframe'] = 'article';
				$this->printID = 'artreq'. $this->cfg['id'];
			}

			// get the articles
			if(empty($this->articles))
			{
				if(!empty($this->cfg['name']))
				{
					$request = $pmxcFunc['db_query']('', '
						SELECT a.id, a.name, a.acsgrp, a.ctype, a.config, a.owner, a.active, a.created, a.updated, a.content, CASE WHEN m.real_name = {string:empty} THEN m.member_name ELSE m.real_name END AS mem_name
						FROM {db_prefix}portal_articles AS a
						LEFT JOIN {db_prefix}members AS m ON (a.owner = m.id_member)
						WHERE a.name = {string:art} AND a.active > 0 AND a.approved > 0
						ORDER BY a.id',
						array(
							'art' => $this->cfg['name'],
							'empty' => '',
						)
					);

					if($pmxcFunc['db_num_rows']($request) > 0)
					{
						while($row = $pmxcFunc['db_fetch_assoc']($request))
						{
							$row['config'] = pmx_json_decode($row['config'], true);
							if(!empty($this->cfg['config']['settings']['inherit_acs']))
								$row['acsgrp'] = $this->cfg['acsgrp'];

							if(allowPmxGroup($row['acsgrp']))
							{
								// have a custom cssfile, load
								if(!empty($row['config']['cssfile']))
									$this->getCustomCSS($row);
								else
									$row['customclass'] = '';

								$row['side'] = $this->cfg['side'];
								$row['blocktype'] = (!empty($this->cfg['config']['static_block']) ? 'static_article' : 'article');
								$row['member_name'] = $row['mem_name'];
								$this->articles[] = $row;
							}
						}
						$pmxcFunc['db_free_result']($request);
					}

					// static block?
					if(!empty($this->cfg['config']['static_block']) && !empty($this->cfg['cache']))
						$pmxCacheFunc['put']($this->cache_key, $this->articles, $this->cache_time, $this->cache_mode);
				}
			}

			// articles found?
			if(count($this->articles) > 0)
			{
				// ecl check
				foreach($this->articles as $aKey => $aVal)
				{
					if(!empty($this->articles[$aKey]['config']['check_ecl']) && !checkECL_Cookie(!empty($this->articles[$aKey]['config']['check_eclbots'])))
						unset($this->articles[$aKey]);
					else
					{
						// static article block ?
						if(!empty($this->cfg['config']['static_block']))
						{
							$this->cfg['blocktype'] = 'artblock';
							if($this->cfg['config']['settings']['usedframe'] == 'block')
							{
								$this->articles[$aKey]['config']['visuals'] = $this->cfg['config']['visuals'];
								$this->articles[$aKey]['config']['cssfile'] = $this->cfg['config']['cssfile'];
							}
						}
						else
							$context['pmx']['pagenames']['art'] = $this->getUserTitle($this->articles[0], $this->articles[0]['name']);
					}
				}
			}

			$this->visible = count($this->articles) > 0;
			if(!empty($this->visible))
			{
				// check for special php content
				foreach($this->articles as $art)
					if($art['ctype'] == 'php' && preg_match('~\[\?pmx_initphp(.*)pmx_initphp\?\]~is', $art['content'], $match))
						eval($match[1]);
			}
		}
		return $this->visible;
	}

	/**
	* ShowContent
	*/
	function pmxc_ShowContent()
	{
		global $context;

		$count = count($this->articles);
		if(empty($this->cfg['static_block']) && empty($context['pmx']['viewblocks']['front']))
		{ 
			if($this->cfg['config']['settings']['usedframe'] == 'block' || empty($context['pmx']['viewblocks']['front']))
				$count--;
		}

		foreach($this->articles as $cnt => $article)
		{
			if($this->cfg['config']['settings']['usedframe'] == 'block')
			{
				if(count($this->articles) > 1)
				{
					$article['config']['collapse'] = 0;
					Pmx_Frame_top($article, $count);
					$this->WriteContent($article);
					Pmx_Frame_bottom();
				}
				else
					$this->WriteContent($article);
			}
			else
			{
				Pmx_Frame_top($article, $count);
				$this->WriteContent($article);
				Pmx_Frame_bottom();
			}
			$count--;
		}
	}

	/**
	* Write out the Content
	*/
	function WriteContent($article)
	{
		global $context, $modSettings, $scripturl, $user_info, $txt;

		$statID = 'art'. $article['id'] . $this->cfg['side'];
		$printdir = 'ltr';
		$printChars = $context['character_set'];
		$tease = 0;
		$phpcount = 0;
		$noLB = !empty($modSettings['dont_use_lightbox']) || !empty($article['config']['settings']['disableHSimg']);
		$context['lbimage_data'] = array('lightbox_id' => (empty($noLB) ? $article['blocktype'] .'-'. $article['id'] : null));

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
			// Prepare the BBC content
			if($article['ctype'] == 'bbc_script')
			{
				prepare_bbc_content($article['content']);
				$article['content'] = parse_bbc($article['content'], true, $article['id'], array(), ($user_info['is_guest'] || !empty($article['config']['disableYoutube'])));
				$tease = $article['config']['settings']['teaser'];
			}

			elseif($article['ctype'] == 'html')
			{
				$article['content'] = '<div class="htmlblock">'. $article['content'] .'</div>';
				$tease = !empty($article['config']['settings']['teaser']) ? -1 : 0;
			}

			else
				$tease = $article['config']['settings']['teaser'];

			if($article['ctype'] != 'bbc_script')
				$article['content'] = pmx_ContentLightBox($article['content']);
		}

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
									<img class="pmx_printimg" src="'. $context['pmx_imageurl'] .'Print.png" alt="Print" title="'. $txt['pmx_text_printing'] .'" onclick="PmxPrintPage(\''. $printdir .'\', \''. $this->cfg['id'] .'\', \''. $printChars .'\', \''. $this->getUserTitle($article, $article['name']) .'\', \''. $txt['lightbox_help'] .'\', \''. $txt['lightbox_label'] .'\')" />
									<div id="print'. $this->printID .'">'.
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
								</div>'). $tmp;
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