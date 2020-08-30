<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file AdminArticles.php
 * AdminArticles reached all Posts from Articles Manager.
 * Checks the values and saved the parameter to the database.
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* Receive all the posts from the articles manager, check it, then save it.
* Finally the articles are prepared and the template loaded.
*/
function Portal_AdminArticles()
{
	global $pmxcFunc, $context, $sourcedir, $scripturl, $modSettings, $user_info, $txt;

	$admMode = isset($_GET['action']) ? $_GET['action'] : '';

	if(($admMode == 'admin' || $admMode == 'portal') && isset($_GET['area']) && $_GET['area'] == 'pmx_articles')
	{
		if(allowPmx('pmx_admin, pmx_articles, pmx_create'))
		{
			require_once($context['pmx_sourcedir'] .'AdminSubs.php');

			$context['pmx']['subaction'] = !empty($_POST['sa']) ? $_POST['sa'] : 'overview';

			// From template ?
			if(PortaMx_checkPOST())
			{
				// Make sure we have a valid session...
				checkSession('post');

				// get current pageindex
				if(isset($_POST['articlestart']))
					$context['pmx']['articlestart'] = $_POST['articlestart'];

				// actions from overview?
				if($context['pmx']['subaction'] == 'overview' && empty($_POST['cancel_overview']))
				{
					// from xml on overview?
					if(isset($_POST['xml']))
						$xmlResult = '';

					// filter set ?
					if(isset($_POST['filter']))
						$_SESSION['PortaMx']['filter'] = $_POST['filter'];

					// Row pos updates from overview?
					if(!empty($_POST['upd_rowpos']))
					{
						list($fromID, $place, $idto) = Pmx_StrToArray($_POST['upd_rowpos']);

						$request = $pmxcFunc['db_query']('', '
							SELECT id
							FROM {db_prefix}portal_articles
							WHERE id '. ($place == 'before' ? '<' : '>') .' {int:id}
							LIMIT 1',
							array('id' => $idto)
						);
						list($toID) = $pmxcFunc['db_fetch_row']($request);
						$pmxcFunc['db_free_result']($request);
						$toID = (is_null($toID) ? ($place == 'before' ? -1 : 0) : $toID);

						$request = $pmxcFunc['db_query']('', '
							SELECT MAX(id) +1
							FROM {db_prefix}portal_articles',
							array()
						);
						list($maxID) = $pmxcFunc['db_fetch_row']($request);
						$pmxcFunc['db_free_result']($request);

						// create the query...
						if($toID == -1) // move from to first
							$query = array(
								'SET id = 0 WHERE id = '. $fromID,
								'SET id = id + 1 WHERE id >= 1 AND id <= '. $fromID,
								'SET id = 1 WHERE id = 0',
							);

						elseif($toID == 0) // move from to end
							$query = array(
								'SET id = '. $maxID .' WHERE id = '. $fromID,
								'SET id = id - 1 WHERE id >= '. $fromID,
							);

						elseif($toID > $fromID) // to > from - move to after from
							$query = array(
								'SET id = id + 1 WHERE id >= '. $toID,
								'SET id = '. $toID .' WHERE id = '. $fromID,
								'SET id = id - 1 WHERE id >= '. $fromID,
							);

						else // to < from - move to before from
							$query = array(
								'SET id = 0 WHERE id = '. $fromID,
								'SET id = id + 1 WHERE id >= '. $toID .' AND id <= '. $fromID,
								'SET id = '. $toID .' WHERE id = 0',
							);

						// execute
						foreach($query as $qdata)
							$pmxcFunc['db_query']('', 'UPDATE {db_prefix}portal_articles '. $qdata, array());
					}

					// updates from overview popups ?
					if(!empty($_POST['upd_overview']))
					{
						$updates = array();
						foreach($_POST['upd_overview'] as $updkey => $updvalues)
						{
							foreach($updvalues as $id => $values)
							{
								if($updkey == 'title')
								{
									foreach($values as $key => $val)
									{
										if($key == 'lang')
										{
											foreach($val as $langname => $langvalue)
												$updates[$id]['config'][$updkey][$langname] = $langvalue;
										}
										else
											$updates[$id]['config'][$updkey .'_'. $key] = $val;
									}
								}
								else
									$updates[$id][$updkey] = $values;
							}
						}

						// save all updates
						$idList = array();
						$catList = array();
						foreach($updates as $id => $values)
						{
							$idList[] = $id;
							foreach($values as $rowname => $data)
							{
								$request = $pmxcFunc['db_query']('', '
									SELECT config, catid, acsgrp
									FROM {db_prefix}portal_articles
									WHERE id = {int:id}',
									array('id' => $id)
								);
								$row = $pmxcFunc['db_fetch_assoc']($request);
								$pmxcFunc['db_free_result']($request);
								$catList[] = $row['catid'];

									// update config
								if($rowname == 'config')
								{
									$cfg = pmx_json_decode($row['config'], true);
									foreach($data as $ckey => $cval)
									{
										if($ckey == 'title')
											foreach($cval as $lang => $val)
												$cfg[$ckey][$lang] = $val;
										else
											$cfg[$ckey] = $cval;
									}
									$pmxcFunc['db_query']('', '
										UPDATE {db_prefix}portal_articles
										SET config = {string:config}
										WHERE id = {int:id}',
									  array(
											'id' => $id,
											'config' => json_encode($cfg, true))
									);
								}

								// update cat id
								elseif($rowname == 'category')
								{
									$pmxcFunc['db_query']('', '
										UPDATE {db_prefix}portal_articles
										SET catid = {int:val}
										WHERE id = {int:id}',
										array(
											'id' => $id,
											'val' => $data)
									);
								}

								// access groups
								else
								{
									$mode = substr($rowname, 0, 3);

									// update (replace)
									if($mode == 'upd')
										$newacs = explode(',', $data);

									// add group(s)
									elseif($mode == 'add')
										$newacs = array_unique(array_merge(explode(',', $row['acsgrp']), explode(',', $data)));

									// delete group(s)
									else
										$newacs = array_unique(array_diff(explode(',', $row['acsgrp']), explode(',', $data)));

									$pmxcFunc['db_query']('', '
										UPDATE {db_prefix}portal_articles
										SET acsgrp = {string:val}
										WHERE id = {int:id}',
										array(
											'id' => $id,
											'val' => implode(',', $newacs))
									);

									// send by xml?
									if(isset($_POST['xml']))
									{
										$request = $pmxcFunc['db_query']('', '
											SELECT active
											FROM {db_prefix}portal_articles
											WHERE id = {int:id}',
											array('id' => $id)
										);
										list($active) = $pmxcFunc['db_fetch_row']($request);
										$pmxcFunc['db_free_result']($request);

										$newacs = implode(',', $newacs);
										$count = !empty($newacs) ? count(explode(',', $newacs)) : 0;
										$xmlResult .= (!empty($xmlResult) ? '&' : '') . $id .'|'. $newacs .'|'. $count .'|' . intval(allowPmxGroup($newacs)) .'|'. $active;
									}
								}
							}
						}

						// clear cached blocks && Cat/Art Session Keys
						clearBlocksCache(null, true);
						if(isset($_SESSION['PortaMx']))
						{
							foreach($_SESSION['PortaMx'] as $key => $val)
								if(strpos($key, 'pmxpost_') !== false)
									unset($_SESSION['PortaMx'][$key]);
						}

						if(isset($_POST['xml']))
						{
							// return update result
							ob_start();
							if(!empty($_POST['result']))
								echo $_POST['result'];
							else
								echo $xmlResult;

							ob_end_flush();
							exit;
						}
					}

					// add a new article
					if(!empty($_POST['add_new_article']))
					{
						$article = PortaMx_getDefaultArticle($_POST['add_new_article']);
						$context['pmx']['subaction'] = 'editnew';
					}

					// edit / clone an article
					elseif(!empty($_POST['edit_article']) || !empty($_POST['clone_article']))
					{
						$id =!empty($_POST['clone_article']) ? $_POST['clone_article'] : $_POST['edit_article'];

						// load the article for edit/clone
						$request = $pmxcFunc['db_query']('', '
							SELECT *
							FROM {db_prefix}portal_articles
							WHERE id = {int:id}',
							array(
								'id' => $id
							)
						);
						$row = $pmxcFunc['db_fetch_assoc']($request);
						$article = array(
							'id' => $row['id'],
							'name' => $row['name'],
							'catid' => $row['catid'],
							'acsgrp' => $row['acsgrp'],
							'ctype' => $row['ctype'],
							'config' => $row['config'],
							'content' => $row['content'],
							'active' => $row['active'],
							'owner' => $row['owner'],
							'created' => $row['created'],
							'approved' => $row['approved'],
							'approvedby' => $row['approvedby'],
							'updated' => $row['updated'],
							'updatedby' => $row['updatedby'],
						);
						$pmxcFunc['db_free_result']($request);

						if(!empty($_POST['clone_article']))
						{
							$article['id'] = 0;
							$article['active'] = 0;
							$article['approved'] = 0;
							$article['owner'] = $user_info['id'];
							$article['created'] = 0;
							$article['updated'] = 0;
							$article['updatedby'] = 0;
							$context['pmx']['subaction'] = 'editnew';
						}
						else
							$context['pmx']['subaction'] = 'edit';
					}

					// delete article?
					elseif(!empty($_POST['delete_article']))
					{
						$delid = $_POST['delete_article'];

						// get the current page
						$context['pmx']['articlestart'] = getCurrentPage($delid, $context['pmx']['settings']['manager']['artpage'], true);

						$pmxcFunc['db_query']('', '
							DELETE FROM {db_prefix}portal_articles
							WHERE id = {int:id}',
							array('id' => $delid)
						);

						// clear cached blocks
						clearBlocksCache(null, true);
					}

					// toggle approve?
					elseif(!empty($_POST['chg_approved']))
					{
						$pmxcFunc['db_query']('', '
							UPDATE {db_prefix}portal_articles
							SET approved = CASE WHEN approved = 0 THEN {int:apptime} ELSE 0 END, approvedby = {int:appmember}
							WHERE id = {int:id}',
							array(
								'id' => $_POST['chg_approved'],
								'apptime' => forum_time(),
								'appmember' => $user_info['id'])
						);

						// clear cached blocks
						clearBlocksCache(null, true);
					}

					// toggle active ?
					elseif(!empty($_POST['chg_active']))
					{
						$pmxcFunc['db_query']('', '
							UPDATE {db_prefix}portal_articles
							SET active = CASE WHEN active = 0 THEN 1 ELSE 0 END
							WHERE id = {int:id}',
							array(
								'id' => $_POST['chg_active'])
						);

						// clear cached blocks
						clearBlocksCache(null, true);
					}

					if(isset($_POST['xml']) && (!empty($_POST['chg_active']) || !empty($_POST['chg_approved'])))
					{
						$id = !empty($_POST['chg_active']) ? $_POST['chg_active'] : $_POST['chg_approved'];
						$request = $pmxcFunc['db_query']('', '
							SELECT active, approved
							FROM {db_prefix}portal_articles
							WHERE id = {int:id}',
							array('id' => $id)
						);
						list($active, $approved) = $pmxcFunc['db_fetch_row']($request);
						$pmxcFunc['db_free_result']($request);

						// return update result
						ob_start();
						echo $id .','. (!empty($_POST['chg_active']) ? intval(!empty($active)) : intval(!empty($approved)));
						ob_end_flush();
						exit;
					}
				}

				// editing the article was canceled ?
				elseif(!empty($_POST['cancel_edit']) || !empty($_POST['cancel_overview']))
				{
					// called fron blocks move/clone ?
					if(!empty($_POST['fromblock']))
					{
						// on cancel after saved remove the article
						if($_POST['sa'] == 'edit' && !empty($_POST['id']))
						{
							$pmxcFunc['db_query']('', '
								DELETE FROM {db_prefix}portal_articles
								WHERE id = {int:id}',
								array('id' => $_POST['id'])
							);

							clearBlocksCache(null, true);
						}

						// redirect back to the blocks manager
						@list($mode, $side, $bid) = explode('.', $_POST['fromblock']);
						redirectexit('action='. $admMode .';area=pmx_blocks;sa='. $side .';'. $context['session_var'] .'=' .$context['session_id']);
					}

					// Otherwise let's load the overview
					$context['pmx']['subaction'] = 'overview';
				}

				// actions from edit article
				elseif($context['pmx']['subaction'] == 'editnew' || $context['pmx']['subaction'] == 'edit')
				{
					$context['pmx']['fromblock'] = $_POST['fromblock'];

					// check defined numeric vars (check_num_vars holds the posted array to check like [varname][varname] ...)
					if(isset($_POST['check_num_vars']))
					{
						foreach($_POST['check_num_vars'] as $val)
						{
							$data = explode(',', $val);
							$post = '$_POST'. str_replace(array('[', ']'), array('[\'', '\']'), $data[0]);
							if(eval("return isset($post);") && eval("return !is_numeric($post);"))
									eval("$post = $data[1];");
						}
					}

					if(isset($_POST['content']) && PortaMx_makeSafeContent($_POST['content'], $_POST['ctype']))
					{
						// convert html/script to bbc
						if($_POST['ctype'] == 'bbc_script' && in_array($_POST['contenttype'], array('html', 'script')))
						{
							// replace with/height styles
							if(preg_match_all('/<img.*style=\"[^\"]*\"[^>]*>/U', $_POST['content'], $match, PREG_SET_ORDER) > 0)
							{
								foreach($match as $data)
									$_POST['content'] = str_replace($data[0], str_replace(array('style="', ': ', 'px; ', 'px;"'), array('', '="', '" ', '"'), $data[0]), $_POST['content']);
							}
							// replace YT-Player html
							if(preg_match_all('~<div.*[^<].*<iframe.*<\/div>~Ums', $_POST['content'], $match) > 0)
							{
								foreach($match[0] as $embed)
								{
									if(preg_match('/\/embed([^\?]*\?)/U', $embed, $ytid) > 0 && isset($ytid[1]))
										$_POST['content'] = str_replace($embed, '[youtube]'. trim($ytid[1], '/?') .'[/youtube]', $_POST['content']);
								}
							}
							require_once($sourcedir . '/Subs-Editor.php');
							$modSettings['smiley_enable'] = true;
							$_POST['content'] = html_to_bbc(PortaMx_SmileyToBBC($_POST['content']));
						}

						// convert bbc to html/script
						elseif($_POST['contenttype'] == 'bbc_script' && in_array($_POST['ctype'], array('html', 'script')))
						{
							if(!empty($modSettings['dont_use_lightbox']) || !empty($_POST['config']['settings']['disableHSimg']))
								$context['lbimage_data'] = null;
							else
								$context['lbimage_data'] = array('lightbox_id' => $_POST['ctype'] .'-'. $_POST['id']);

							$_POST['content'] = parse_bbc($_POST['content'], true, 0, array(), true);
							$_POST['content'] = preg_replace_callback('/<\/[^>]*>|<[^\/]*\/>|<ul[^>]*>|<ol[^>]*>/', function($matches){return $matches[0] ."\n";}, $_POST['content']);
						}

						// handling special php blocks
						elseif($_POST['ctype'] == 'php' && $_POST['contenttype'] == 'php')
							pmxPHP_convert();

						if(in_array($_POST['ctype'], array('html', 'script')) && $_POST['contenttype'] == $_POST['ctype'])
						{
							if(preg_match_all('~<img[^>]*>~', $_POST['content'], $match) > 0)
							{
								foreach($match[0] as $key => $val)
								{
									if(strpos($val, $modSettings['smileys_url']) === false && preg_match('/<img[^c]*class?=?[^r]*resized[^\"]*\"[^>]*>/U', $val) == 0)
									{
										if(strpos($val, 'noexp') === false)
											$_POST['content'] = str_replace($val, str_replace('<img', '<img class="bbc_img resized"', $val), $_POST['content']);
									}
								}
							}
						}
					}

					if($_POST['ctype'] == 'bbc_script')
					{
						require_once($sourcedir .'/Subs-Post.php');
						preparsecode($_POST['content'], false);
					}

					// get all data
					$article = array(
						'id' => $_POST['id'],
						'name' => $_POST['name'],
						'catid' => $_POST['catid'],
						'acsgrp' => (!empty($_POST['acsgrp']) ? implode(',', $_POST['acsgrp']) : ''),
						'ctype' => $_POST['ctype'],
						'config' => json_encode($_POST['config'], true),
						'content' => $_POST['content'],
						'active' => $_POST['active'],
						'owner' => $_POST['owner'],
						'created' => $_POST['created'],
						'approved' => $_POST['approved'],
						'approvedby' => $_POST['approvedby'],
						'updated' => $_POST['updated'],
						'updatedby' => $_POST['updatedby'],
					);

					// save article if have content..
					if(!empty($article['content']) && empty($_POST['edit_change']) && (!empty($_POST['save_edit']) || (!empty($article['content']) && !empty($_POST['save_edit_continue']))))
					{
						// if new article get the last id
						if($context['pmx']['subaction'] == 'editnew')
						{
							$request = $pmxcFunc['db_query']('', '
								SELECT MAX(id)
								FROM {db_prefix}portal_articles',
								array()
							);
							list($dbid) = $pmxcFunc['db_fetch_row']($request);
							$pmxcFunc['db_free_result']($request);
							$article['id'] = strval(1 + ($dbid === null ? $article['id'] : $dbid));
							$article['created'] = forum_time();

							// auto approve for admins
							if(allowPmx('pmx_admin'))
							{
								$article['approved'] = forum_time();
								$article['approvedby'] = $user_info['id'];
							}

							// insert new article
							$pmxcFunc['db_insert']('ignore', '
								{db_prefix}portal_articles',
								array(
									'id' => 'int',
									'name' => 'string',
									'catid' => 'int',
									'acsgrp' => 'string',
									'ctype' => 'string',
									'config' => 'string',
									'content' => 'string',
									'active' => 'int',
									'owner' => 'int',
									'created' => 'int',
									'approved' => 'int',
									'approvedby' => 'int',
									'updated' => 'int',
									'updatedby' => 'int',
								),
								$article,
								array()
							);

							// clear cache
							clearBlocksCache(null, true);
						}
						else
						{
							$article['updated'] = forum_time();
							$article['updatedby'] = $user_info['id'];

							// update the article
							$pmxcFunc['db_query']('', '
								UPDATE {db_prefix}portal_articles
								SET name = {string:name}, catid = {int:catid}, acsgrp = {string:acsgrp}, ctype = {string:ctype}, config = {string:config},
										content = {string:content}, active = {int:active}, owner = {int:owner}, created = {int:created}, approved = {int:approved},
										approvedby = {int:approvedby}, updated = {int:updated}, updatedby = {int:updatedby}
								WHERE id = {int:id}',
								array(
									'id' => $article['id'],
									'name' => $article['name'],
									'catid' => $article['catid'],
									'acsgrp' => $article['acsgrp'],
									'ctype' => $article['ctype'],
									'config' => $article['config'],
									'content' => $article['content'],
									'active' => $article['active'],
									'owner' => $article['owner'],
									'created' => $article['created'],
									'approved' => $article['approved'],
									'approvedby' => $article['approvedby'],
									'updated' => $article['updated'],
									'updatedby' => $article['updatedby']
								)
							);
						}

						// clear cache
						clearBlocksCache(null, true);
						$context['pmx']['subaction'] = 'edit';
					}

					// continue edit ?
					if(!empty($_POST['save_edit']) || !empty($_POST['save_edit_continue']))
					{
						$_SESSION['saved_successful'] = true;
						if(empty($_POST['save_edit_continue']))
						{
							// edit done, is it a move/clone from blocks?
							if(!empty($context['pmx']['fromblock']))
							{
								@list($mode, $side, $bid) = explode('.', $context['pmx']['fromblock']);

								// was block moved?
								if($mode == 'move')
								{
									$request = $pmxcFunc['db_query']('', '
										SELECT pos, blocktype
										FROM {db_prefix}portal_blocks
										WHERE id = {int:bid}',
										array('bid' => $bid)
									);
									$block = $pmxcFunc['db_fetch_assoc']($request);
									$pmxcFunc['db_free_result']($request);

									// update all pos >= moved id
									$pmxcFunc['db_query']('', '
										UPDATE {db_prefix}portal_blocks
										SET pos = pos - 1
										WHERE side = {string:side} AND pos >= {int:pos}',
										array('side' => $side, 'pos' => $block['pos'])
									);

									// delete the block
									$pmxcFunc['db_query']('', '
										DELETE FROM {db_prefix}portal_blocks
										WHERE id = {int:id}',
										array('id' => $bid)
									);

									// clear cache and SEF pages list
									clearBlocksCache(null, true);
								}
							}

							// go to article overview
							$context['pmx']['subaction'] = 'overview';
							$context['pmx']['articlestart'] = getCurrentPage($article['id'], $context['pmx']['settings']['manager']['artpage']);
						}
					}

					// clear cached blocks
					clearBlocksCache(null, true);
				}

				if($context['pmx']['subaction'] == 'overview')
				{
					if(!isset($context['pmx']['articlestart']))
						$context['pmx']['articlestart'] = 0;
					redirectexit('action='. $admMode .';area=pmx_articles;'. $context['session_var'] .'=' .$context['session_id'] .';pg='. $context['pmx']['articlestart']);
				}
			}

			// load the template, initialize the page title
			loadTemplate($context['pmx_templatedir'] .'AdminArticles');
			$context['page_title'] = $txt['pmx_articles'];
			$context['pmx']['AdminMode'] = $admMode;
			$context['pmx']['RegBlocks'] = eval($context['pmx']['registerblocks']);

			// direct edit request?
			if(isset($_GET['sa']) && PortaMx_makeSafe($_GET['sa']) == 'edit' && !empty($_GET['id']))
			{
				// move or clone from blocks?
				if(isset($_GET['from']))
				{
					$context['pmx']['fromblock'] = PortaMx_makeSafe($_GET['from']) .'.'. PortaMx_makeSafe($_GET['id']);

					// load the block
					$request = $pmxcFunc['db_query']('', '
						SELECT *
						FROM {db_prefix}portal_blocks
						WHERE id = {int:id}',
						array(
							'id' => PortaMx_makeSafe($_GET['id'])
						)
					);
					$row = $pmxcFunc['db_fetch_assoc']($request);
					$pmxcFunc['db_free_result']($request);

					// modify the config array
					$cfg = pmx_json_decode($row['config'], true);
					if(isset($cfg['pagename']))
					{
						$pgname = $cfg['pagename'];
						unset($cfg['pagename']);
					}
					else
						$pgname = '';
					unset($cfg['ext_opts']);
					$cfg['can_moderate'] = allowedTo('admin_forum') ? 0 : 1;

					$article = array(
						'id' => 0,
						'name' => $pgname,
						'catid' => 0,
						'acsgrp' => $row['acsgrp'],
						'ctype' => $row['blocktype'],
						'config' => json_encode($cfg, true),
						'content' => $row['content'],
						'active' => 0,
						'owner' => $user_info['id'],
						'created' => 0,
						'approved' => 0,
						'approvedby' => 0,
						'updated' => 0,
						'updatedby' => 0,
					);

					$context['pmx']['subaction'] = 'editnew';
					$context['pmx']['articlestart'] = 0;
				}

				// load the article for editing
				else
				{
					$context['pmx']['fromblock'] = '';

					$request = $pmxcFunc['db_query']('', '
						SELECT *
						FROM {db_prefix}portal_articles
						WHERE id = {int:id}',
						array(
							'id' => PortaMx_makeSafe($_GET['id'])
						)
					);

					if($pmxcFunc['db_num_rows']($request) > 0)
					{
						$row = $pmxcFunc['db_fetch_assoc']($request);
						$article = array(
							'id' => $row['id'],
							'name' => $row['name'],
							'catid' => $row['catid'],
							'acsgrp' => $row['acsgrp'],
							'ctype' => $row['ctype'],
							'config' => $row['config'],
							'content' => $row['content'],
							'active' => $row['active'],
							'owner' => $row['owner'],
							'created' => $row['created'],
							'approved' => $row['approved'],
							'approvedby' => $row['approvedby'],
							'updated' => $row['updated'],
							'updatedby' => $row['updatedby'],
						);
						$pmxcFunc['db_free_result']($request);

						$context['pmx']['subaction'] = 'edit';
						$context['pmx']['articlestart'] = 0;
					}
				}
			}

			// continue edit or overview?
			if($context['pmx']['subaction'] == 'overview')
			{
				// load article data for overview
				if(!allowPmx('pmx_articles') && allowPmx('pmx_create', true))
					$where = 'WHERE a.owner = {int:owner}';
				else
					$where = '';

				if(!isset($_SESSION['PortaMx']['filter']))
					$_SESSION['PortaMx']['filter'] = array('category' => '', 'approved' => 0, 'active' => 0, 'myown' => 0, 'member' => '');

				if($_SESSION['PortaMx']['filter']['category'] != '')
					$where .= (empty($where) ? 'WHERE ' : ' AND '). 'a.catid IN ({array_int:catfilter})';

				if($_SESSION['PortaMx']['filter']['approved'] != 0)
				{
					$where .= (empty($where) ? 'WHERE ' : ' AND ');
					if($_SESSION['PortaMx']['filter']['active'] != 0)
						$where .= '(a.approved = 0 OR a.active = 0)';
					else
						$where .= 'a.approved = 0';
				}

				if($_SESSION['PortaMx']['filter']['active'] != 0)
				{
					$where .= (empty($where) ? 'WHERE ' : ' AND ');
					if($_SESSION['PortaMx']['filter']['approved'] != 0)
						$where .= '(a.active = 0 OR a.approved = 0)';
					else
						$where .= 'a.active = 0';
				}

				if($_SESSION['PortaMx']['filter']['myown'] != 0)
					$where .= (empty($where) ? 'WHERE ' : ' AND ') .'a.owner = {int:owner}';

				if($_SESSION['PortaMx']['filter']['member'] != '')
					$where .= (empty($where) ? 'WHERE ' : ' AND ') .'m.member_name LIKE {string:memname}';

				if(isset($_GET['pg']) && !is_array($_GET['pg']))
				{
					$context['pmx']['articlestart'] = PortaMx_makeSafe($_GET['pg']);
					unset($_GET['pg']);
				}
				elseif(!isset($context['pmx']['articlestart']))
					$context['pmx']['articlestart'] = 0;

				$cansee = allowPmx('pmx_articles, pmx_create', true);
				$isadmin = allowPmx('pmx_admin');

				$memerIDs = array();
				$context['pmx']['articles'] = array();
				$context['pmx']['article_rows'] = array();
				$context['pmx']['totalarticles'] = 0;
				$result = null;

				$request = $pmxcFunc['db_query']('', '
					SELECT a.id, a.name, a.catid, a.acsgrp, a.ctype, a.config, a.active, a.owner, a.created, a.approved, a.approvedby, a.updated, a.updatedby, a.content, c.artsort, c.level, c.name AS catname
					FROM {db_prefix}portal_articles AS a'. ($_SESSION['PortaMx']['filter']['member'] != '' ? '
					LEFT JOIN {db_prefix}members AS m ON (a.owner = m.id_member)' : '') .'
					LEFT JOIN {db_prefix}portal_categories AS c ON (a.catid = c.id)
					'. $where .'
					ORDER BY a.id',
					array(
						'catfilter' => Pmx_StrToArray($_SESSION['PortaMx']['filter']['category']),
						'memname' => str_replace('*', '%', $_SESSION['PortaMx']['filter']['member']),
						'owner' => $user_info['id'])
				);
				if($pmxcFunc['db_num_rows']($request) > 0)
				{
					while($row = $pmxcFunc['db_fetch_assoc']($request))
					{
						$cfg = pmx_json_decode($row['config'], true);
						if(!empty($isadmin) || ($cansee && !empty($cfg['can_moderate'])))
						{
							$memerIDs[] = $row['owner'];
							$memerIDs[] = $row['approvedby'];
							$memerIDs[] = $row['updatedby'];

							$context['pmx']['article_rows'][$row['id']] = array(
								'name' => $row['name'],
								'cat' => str_repeat('&bull;', $row['level']) . $row['catname'],
							);

							$result[] = array(
								'id' => $row['id'],
								'name' => $row['name'],
								'catid' => $row['catid'],
								'cat' => str_repeat('&bull;', $row['level']) . $row['catname'],
								'acsgrp' => $row['acsgrp'],
								'ctype' => $row['ctype'],
								'config' => $cfg,
								'active' => $row['active'],
								'owner' => $row['owner'],
								'created' => $row['created'],
								'approved' => $row['approved'],
								'approvedby' => $row['approvedby'],
								'updated' => $row['updated'],
								'updatedby' => $row['updatedby'],
								'content' => $row['content'],
							);
						}
					}
					$pmxcFunc['db_free_result']($request);

					if(!empty($result))
					{
						foreach($result as $st => $data)
							$context['pmx']['articles'][$st] = $data;

						$context['pmx']['totalarticles'] = count($result);
						if($context['pmx']['totalarticles'] <= $context['pmx']['articlestart'])
							$context['pmx']['articlestart'] = 0;

						// get all members names
						$request = $pmxcFunc['db_query']('', '
							SELECT id_member, member_name
							FROM {db_prefix}members
							WHERE id_member IN ({array_int:members})',
							array('members' => array_unique($memerIDs))
						);
						if($pmxcFunc['db_num_rows']($request) > 0)
						{
							while($row = $pmxcFunc['db_fetch_assoc']($request))
								$context['pmx']['articles_member'][$row['id_member']] = $row['member_name'];
							$pmxcFunc['db_free_result']($request);
						}
					}
				}

				// load popup js and css for overview
				loadJavascriptFile(PortaMx_loadCompressed('PortalPopup.js'), array('external' => true));
				loadCSSFile(PortaMx_loadCompressed('portal_ampopup.css'), array('external' => true));
			}
			elseif(empty($_POST['save_edit']))
			{
				// prepare the editor
				PortaMx_EditArticle($article['ctype'], 'content', $article['content']);

				// load the class file and create the object
				loadCSSFile(PortaMx_loadCompressed('portal_ampopup.css'), array('external' => true));
				require_once($context['pmx_sysclassdir']. 'AdminArticlesClass.php');
				$context['pmx']['editarticle'] = new PortaMxC_SystemAdminArticle($article);
				$context['pmx']['editarticle']->pmxc_AdmArticle_loadinit();
			}
		}
		else
			fatal_error($txt['pmx_acces_error']);
	}
}

/**
* Find the currect page
**/
function getCurrentPage($id, $numPage, $delmode = false)
{
	global $pmxcFunc;

	$start = 0;
	$articlestart = 0;

	$request = $pmxcFunc['db_query']('', '
		SELECT id
		FROM {db_prefix}portal_articles
		ORDER BY id ASC',
		array()
	);
	while(($row = $pmxcFunc['db_fetch_assoc']($request)) && $row['id'] != $id)
	{
		$start++;
		if($start >= $numPage)
		{
			$articlestart += $numPage;
			$start = 0;
		}
	}
	$pmxcFunc['db_free_result']($request);

	if(!empty($delmode) && !empty($articlestart))
	{
		$request = $pmxcFunc['db_query']('', '
			SELECT COUNT(id)
			FROM {db_prefix}portal_articles',
			array()
		);
		list($maxart) = $pmxcFunc['db_fetch_row']($request);
		$pmxcFunc['db_free_result']($request);
		$maxart = $maxart === null ? 0 : $maxart -1;

		if($maxart <= $articlestart)
			$articlestart -= $numPage;
	}

	return $articlestart;
}
?>