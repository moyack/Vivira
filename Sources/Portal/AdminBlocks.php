<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file AdminBlocks.php
 * AdminBlocks reached all Posts from Blocks Manager.
 * Checks the values and saved the parameter to the database.
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* Receive all the Posts from Blocks Manager, check and save it.
* Finally the articles are prepared and the template loaded.
*/
function Portal_AdminBlocks()
{
	global $pmxcFunc, $context, $sourcedir, $scripturl, $user_info, $modSettings, $txt;

	$_GET = PortaMx_makeSafe($_GET);
	$admMode = $_GET['action'];
	$pmx_area = $_GET['area'];
	$newBlockSide = '';

	if(($admMode == 'admin' || $admMode == 'portal') && $pmx_area == 'pmx_blocks')
	{
		if(allowPmx('pmx_admin, pmx_blocks'))
		{
			require_once($context['pmx_sourcedir'] .'AdminSubs.php');
			$context['pmx']['subaction'] = isset($_POST['sa']) ? $_POST['sa'] : 'all';

			// From template ?
			if(PortaMx_checkPOST())
			{
				// check the Post array
				checkSession('post');
				$context['pmx']['function'] = $_POST['function'];

				// actions from overview ?
				if($context['pmx']['function'] == 'overview')
				{
					// update action from overview?
					if(!empty($_POST['upd_overview']))
					{
						$updates = array();
						$chgSides = array();
						foreach($_POST['upd_overview'] as $side => $sidevalues)
						{
							$chgSides[] = $side;
							foreach($sidevalues as $updkey => $updvalues)
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
						}

						// save all updates (title, access)
						foreach($updates as $id => $values)
						{
							$request = $pmxcFunc['db_query']('', '
								SELECT config, acsgrp, blocktype
								FROM {db_prefix}portal_blocks
								WHERE id = {int:id}',
								array('id' => $id)
							);
							$row = $pmxcFunc['db_fetch_assoc']($request);
							$pmxcFunc['db_free_result']($request);
							$blocktype = $row['blocktype'];

							foreach($values as $rowname => $data)
							{
								// update config array
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
										UPDATE {db_prefix}portal_blocks
										SET config = {string:config}
										WHERE id = {int:id}',
										array(
											'id' => $id,
											'config' => json_encode($cfg, true),
										)
									);
								}

								// access groups
								else
								{
									if(!empty($_POST['xml']) && !isset($xmlResult))
										$xmlResult = '';

									// update (replace)
									$mode = substr($rowname, 0, 3);
									if($mode == 'upd')
										$newacs = explode(',', $data);

									// add group(s)
									elseif($mode == 'add')
										$newacs = array_unique(array_merge(explode(',', $row['acsgrp']), explode(',', $data)));

									// delete group(s)
									else
										$newacs = array_unique(array_diff(explode(',', $row['acsgrp']), explode(',', $data)));

									$pmxcFunc['db_query']('', '
										UPDATE {db_prefix}portal_blocks
										SET acsgrp = {string:val}
										WHERE id = {int:id}',
										array(
											'id' => $id,
											'val' => implode(',', $newacs))
									);

									// send by xml?
									if(isset($xmlResult))
									{
										$request = $pmxcFunc['db_query']('', '
											SELECT active
											FROM {db_prefix}portal_blocks
											WHERE id = {int:id}',
											array('id' => $id)
										);
										list($active) = $pmxcFunc['db_fetch_row']($request);
										$pmxcFunc['db_free_result']($request);

										clearBlocksCache($id);

										$newacs = implode(',', $newacs);
										$count = !empty($newacs) ? count(explode(',', $newacs)) : 0;
										$xmlResult .= (!empty($xmlResult) ? '&' : '') . $id .'|'. $newacs .'|'. $count .'|' . intval(allowPmxGroup($newacs)) .'|'. $active;
									}
								}
							}

							// clear cache
							clearBlocksCache($id);
						}

						if(!empty($_POST['xml']) && isset($xmlResult))
						{
							// return update acces result
							ob_start();
							echo $xmlResult;
							ob_end_flush();
							exit;
						}
					}

					// toggle active ?
					elseif(!empty($_POST['chg_status']))
					{
						$id = PortaMx_makeSafe($_POST['chg_status']);
						$request = $pmxcFunc['db_query']('', '
							SELECT side, blocktype
							FROM {db_prefix}portal_blocks
							WHERE id = {int:id}',
							array('id' => $id)
						);

						list($side, $blocktype) = $pmxcFunc['db_fetch_row']($request);
						$pmxcFunc['db_free_result']($request);

						$pmxcFunc['db_query']('', '
							UPDATE {db_prefix}portal_blocks
							SET active = CASE WHEN active = 0 THEN 1 ELSE 0 END
							WHERE id = {int:id}',
							array('id' => $id)
						);

						// Post send by xml http ?
						if(!empty($_POST['xml']))
						{
							// check if we have active blocks in this panel
							$request = $pmxcFunc['db_query']('', '
								SELECT acsgrp, active
								FROM {db_prefix}portal_blocks
								WHERE id = {int:id}',
								array('id' => $id)
							);
							list($acs, $status) = $pmxcFunc['db_fetch_row']($request);
							$pmxcFunc['db_free_result']($request);

							// clear cache
							clearBlocksCache($id);

							// return result
							ob_start();
							echo $status .','. intval(allowPmxGroup($acs));
							ob_end_flush();
							exit;
						}
					}

					// add new block
					if(!empty($_POST['add_new_block']))
					{
						$id = null;
						$context['pmx']['function'] = 'editnew';
						list($newBlockSide) = array_keys($_POST['add_new_block']);
						list($block) = array_values($_POST['add_new_block']);
					}

					// move rowpos
					elseif(!empty($_POST['upd_rowpos']))
					{
						list($side) = pmx_each($_POST['upd_rowpos']);
						list($fromID, $place, $toID) = Pmx_StrToArray($_POST['upd_rowpos'][$side]['rowpos']);

						$request = $pmxcFunc['db_query']('', '
							SELECT id, pos
							FROM {db_prefix}portal_blocks
							WHERE id IN({array_int:ids})',
							array('ids' => array($fromID, $toID))
						);
						while($row = $pmxcFunc['db_fetch_assoc']($request))
							$moveData[$row['id']] = $row['pos'];
						$pmxcFunc['db_free_result']($request);

						// create the query...
						if($moveData[$fromID] > $moveData[$toID])
							$query = 'SET pos = pos + 1 WHERE side = \''. $side .'\' AND pos >= '. $moveData[$toID] .' AND pos <= '. $moveData[$fromID];
						else
							$query = 'SET pos = pos - 1 WHERE side = \''. $side .'\' AND pos >= '. $moveData[$fromID] .' AND pos <= '. $moveData[$toID];
						// .. and execute
						$pmxcFunc['db_query']('', 'UPDATE {db_prefix}portal_blocks '. $query, array());

						// update the fromID pos
						$pmxcFunc['db_query']('', '
							UPDATE {db_prefix}portal_blocks
							SET pos = {int:pos}
							WHERE id = {int:id}',
							array('id' => $fromID, 'pos' => $moveData[$toID])
						);
					}

					elseif(!empty($_POST['edit_block']))
					{
						$id = $_POST['edit_block'];
						$context['pmx']['function'] = 'edit';
						$block = null;
					}

					// move block, clone block
					elseif(!empty($_POST['clone_block']) || !empty($_POST['move_block']))
					{
						if(!empty($_POST['clone_block']))
							list($id, $side) = Pmx_StrToArray($_POST['clone_block']);
						else
							list($id, $side) = Pmx_StrToArray($_POST['move_block']);

						// load the block for move/clone
						$request = $pmxcFunc['db_query']('', '
							SELECT *
							FROM {db_prefix}portal_blocks
							WHERE id = {int:id}',
							array(
								'id' => $id
							)
						);
						$row = $pmxcFunc['db_fetch_assoc']($request);
						$pmxcFunc['db_free_result']($request);

						// redirect on move/clone to articles..
						if($side == 'articles')
							redirectexit('action='. $admMode .';area=pmx_articles;sa=edit;id='. $id .';from='. (!empty($_POST['clone_block']) ? 'clone.' : 'move.') . $_GET['sa'] .';'. $context['session_var'] .'=' .$context['session_id']);

						// block move
						if(!empty($_POST['move_block']))
						{
							// update all pos >= moved id
							$pmxcFunc['db_query']('', '
								UPDATE {db_prefix}portal_blocks
								SET pos = pos - 1
								WHERE side = {string:side} AND pos >= {int:pos}',
								array('side' => $row['side'], 'pos' => $row['pos'])
							);

							// get max pos for destination panel
							$request = $pmxcFunc['db_query']('', '
								SELECT MAX(pos)
								FROM {db_prefix}portal_blocks
								WHERE side = {string:side}',
								array('side' => $side)
							);
							list($dbpos) = $pmxcFunc['db_fetch_row']($request);
							$pmxcFunc['db_free_result']($request);
							$block['pos'] = strval(1 + ($dbpos === null ? 0 : $dbpos));
							$block['side'] = $side;

							// now update the block
							$pmxcFunc['db_query']('', '
								UPDATE {db_prefix}portal_blocks
								SET pos = {int:pos}, side = {string:side}
								WHERE id = {int:id}',
								array('id' => $id, 'pos' => $block['pos'], 'side' => $block['side'])
							);

							// clear cache
							clearBlocksCache($id);

							$context['pmx']['function'] = 'overview';
							$context['pmx']['subaction'] = $block['side'];
						}

						// clone block
						else
						{
							$block = array(
								'id' => $row['id'],
								'side' => $row['side'],
								'pos' => $row['pos'],
								'active' => $row['active'],
								'cache' => $row['cache'],
								'blocktype' => $row['blocktype'],
								'acsgrp' => $row['acsgrp'],
								'config' => $row['config'],
								'content' => $row['content'],
							);

							$block['side'] = $side;
							$block['active'] = 0;
							$context['pmx']['function'] = 'editnew';
						}
					}

					// delete block ?
					elseif(!empty($_POST['block_delete']))
					{
						$request = $pmxcFunc['db_query']('', '
							SELECT side, pos, blocktype
							FROM {db_prefix}portal_blocks
							WHERE id = {int:id}',
							array('id' => $_POST['block_delete'])
						);
						list($side, $pos, $blocktype) = $pmxcFunc['db_fetch_row']($request);
						$pmxcFunc['db_free_result']($request);

						// update all pos >= deleted id
						$pmxcFunc['db_query']('', '
							UPDATE {db_prefix}portal_blocks
							SET pos = pos - 1
							WHERE side = {string:side} AND pos >= {int:pos}',
							array('side' => $side, 'pos' => $pos)
						);

						// delete the block
						$pmxcFunc['db_query']('', '
							DELETE FROM {db_prefix}portal_blocks
							WHERE id = {int:id}',
							array('id' => $_POST['block_delete'])
						);

						// clear cache
						clearBlocksCache($_POST['block_delete']);
					}

					// Post send by xml http ?
					if(!empty($_POST['xml']))
					{
						// return result
						ob_start();
						echo $_POST['result'];
						ob_end_flush();
						exit;
					}

					// redirect ?
					if($context['pmx']['function'] == 'overview')
						redirectexit('action='. $admMode .';area='. $pmx_area .';sa='. $context['pmx']['subaction'] .';'. $context['session_var'] .'=' .$context['session_id']);
				}

				// edit block canceled ?
				if(!empty($_POST['cancel_edit']))
				{
					$context['pmx']['function'] = 'overview';
					redirectexit('action='. $admMode .';area='. $pmx_area .';sa='. $context['pmx']['subaction'] .';'. $context['session_var'] .'=' .$context['session_id']);
				}

				// actions for/from edit block
				elseif(empty($_POST['edit_block']) && empty($_POST['add_new_block']) && ($context['pmx']['function'] == 'editnew' || $context['pmx']['function'] == 'edit'))
				{
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

					if(!isset($_POST['clone_block']))
					{
						// add a change date to config array
						$_POST['config']['created'] = time();
						$_POST['content'] = PortaMx_makeSafeContent((isset($_POST['content']) ? $_POST['content'] : ''), $_POST['blocktype']); 

						// blocktype change?
						if(!empty($_POST['chg_blocktype']))
						{
							if(!empty($_POST['content']))
							{
								// convert html/script to bbc
								if($_POST['blocktype'] == 'bbc_script' && in_array($_POST['contenttype'], array('html', 'script')))
								{
									// replace with/height styles
									if(preg_match_all('/<img.*style=\"[^\"]*\"[^>]*>/Ums', $_POST['content'], $match, PREG_SET_ORDER) > 0)
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
								elseif($_POST['contenttype'] == 'bbc_script' && in_array($_POST['blocktype'], array('html', 'script')))
								{
									if(!empty($modSettings['dont_use_lightbox']) || !empty($_POST['config']['settings']['disableHSimg']))
										$context['lbimage_data'] = null;
									else
										$context['lbimage_data'] = array('lightbox_id' => $_POST['blocktype'] .'-'. $_POST['id']);

									$_POST['content'] = parse_bbc($_POST['content'], true, 0, array(), false);
									$_POST['content'] = preg_replace_callback('/<\/[^>]*>|<[^\/]*\/>|<ul[^>]*>|<ol[^>]*>/', function($matches){return $matches[0] ."\n";}, $_POST['content']);
								}

								// handling special php blocks
								elseif($_POST['blocktype'] == 'php' && $_POST['contenttype'] == 'php')
										pmxPHP_convert();
							}

							$id = $_POST['id'];
						}

						// Converting content data
						if(empty($_POST['move_block']) && (!empty($_POST['save_edit']) || !empty($_POST['save_edit_continue']) || !empty($_POST['chg_blocktype'])))
						{
							if($_POST['blocktype'] == 'php' && $_POST['contenttype'] == 'php')
								pmxPHP_convert();

							// modify html/script blocks for Lightbox and correct smiley path
							elseif($_POST['blocktype'] == 'html' || $_POST['blocktype'] == 'script')
							{
								if(isset($_POST['content']))
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
								else
									$_POST['content'] = '';
							}

							if($_POST['blocktype'] == 'bbc_script')
							{
								require_once($sourcedir .'/Subs-Post.php');
								preparsecode($_POST['content'], false);
							}

							$block = array(
								'id' => $_POST['id'],
								'side' => $_POST['side'],
								'pos' => $_POST['pos'],
								'active' => $_POST['active'],
								'cache' => $_POST['cache'],
								'blocktype' => $_POST['blocktype'],
								'acsgrp' => (!empty($_POST['acsgrp']) ? implode(',', $_POST['acsgrp']) : ''),
								'config' => json_encode($_POST['config'], true),
								'content' => (isset($_POST['content']) ? $_POST['content'] : ''),
							);

							$id = $_POST['id'];
						}

						// save block..
						if(!empty($_POST['save_edit']) || !empty($_POST['save_edit_continue']))
						{
							// if new block get the last id
							if($context['pmx']['function'] == 'editnew')
							{
								$request = $pmxcFunc['db_query']('', '
									SELECT MAX(a.id), MAX(b.pos)
									FROM {db_prefix}portal_blocks as a
									LEFT JOIN {db_prefix}portal_blocks as b ON(b.side = {string:side})
									GROUP BY b.side',
									array('side' => $block['side'])
								);
								list($dbid, $dbpos) = $pmxcFunc['db_fetch_row']($request);
								$pmxcFunc['db_free_result']($request);
								$block['id'] = strval(1 + ($dbid === null ? 0 : $dbid));
								$block['pos'] = strval(1 + ($dbpos === null ? 0 : $dbpos));
							}

							// now save all data
							$pmxcFunc['db_insert']('replace', '
								{db_prefix}portal_blocks',
								array(
									'id' => 'int',
									'side' => 'string',
									'pos' => 'int',
									'active' => 'int',
									'cache' => 'int',
									'blocktype' => 'string',
									'acsgrp' => 'string',
									'config' => 'string',
									'content' => 'string',
								),
								array(
									$block['id'],
									$block['side'],
									$block['pos'],
									$block['active'],
									$block['cache'],
									$block['blocktype'],
									$block['acsgrp'],
									$block['config'],
									$block['content'],
								),
								array('id')
							);

							// clear cache
							clearBlocksCache($block['id']);

							// clear fader block cookie
							if($block['blocktype'] == 'fader')
								set_cookie('oFader'. $block['id'], '');

							// clear poll block cookie
							if($block['blocktype'] == 'polls')
								set_cookie('poll'. $block['id'], '');

							$postKey = 'pmxpost_'. $block['blocktype'] . $block['id'];
							if(isset($_SESSION['PortaMx'][$postKey]))
								unset($_SESSION['PortaMx'][$postKey]);
							if(isset($_SESSION['PortaMx'][$postKey .'_0']))
								unset($_SESSION['PortaMx'][$postKey .'_0']);

							$context['pmx']['function'] = 'edit';
							$context['pmx']['subaction'] = $block['side'];
						}
					}

					// end edit ?
					if(!empty($_POST['save_edit']))
					{
						$_SESSION['saved_successful'] = true;
						$context['pmx']['function'] = 'overview';
						redirectexit('action='. $admMode .';area='. $pmx_area .';sa='. $context['pmx']['subaction'] .';'. $context['session_var'] .'=' .$context['session_id']);
					}
					elseif(!empty($_POST['save_edit_continue']))
					{
						$_SESSION['saved_successful'] = true;
						if(!empty($block['active']))
						{
							$_SESSION['pmx_save_edit_continue'] = $block['id'];
							redirectexit('action='. $admMode .';area='. $pmx_area .';sa='. $context['pmx']['subaction'] .';'. $context['session_var'] .'=' .$context['session_id']);
						}
					}
				}
			}
			else
			{
				$context['pmx']['subaction'] = (isset($_GET['sa']) && $_GET['sa'] != 'settings' ? $_GET['sa'] : 'all');
				$context['pmx']['function'] = 'overview';

				// direct edit request?
				if(isset($_GET['edit']) && intval($_GET['edit']) != 0)
				{
					$id = $_GET['edit'];
					$context['pmx']['function'] = 'edit';
					$block = null;
				}
				elseif(isset($_SESSION['pmx_save_edit_continue']))
				{
					$_SESSION['saved_successful'] = true;
					$block = null;
					$id = $_SESSION['pmx_save_edit_continue'];
					unset($_SESSION['pmx_save_edit_continue']);
					$context['pmx']['function'] = 'edit';
				}
			}

			// load template and languages, setup pagetitle
			loadTemplate($context['pmx_templatedir'] .'AdminBlocks');
			loadLanguage($context['pmx_languagedir'] .'AdminBlocks');
			$context['pmx']['RegBlocks'] = eval($context['pmx']['registerblocks']);
			$context['page_title'] = $txt['pmx_blocks'];
			$context['pmx']['AdminMode'] = $admMode;

			// continue edit or overview ?
			if($context['pmx']['function'] == 'overview')
			{
				// load blocks data for overview
				$context['pmx']['blocks'] = array();
				$request = $pmxcFunc['db_query']('', '
					SELECT id, side, pos, active, cache, blocktype, acsgrp, config
					FROM {db_prefix}portal_blocks
					WHERE side IN ({array_string:side})
					ORDER BY side, pos',
					array(
						'side' => Pmx_StrToArray(($context['pmx']['subaction'] == 'all' ? implode(',', array_keys($txt['pmx_admBlk_sides'])) : $context['pmx']['subaction'])),
					)
				);
				if($pmxcFunc['db_num_rows']($request) > 0)
				{
					while($row = $pmxcFunc['db_fetch_assoc']($request))
						$context['pmx']['blocks'][$row['side']][$row['pos']] = array(
							'id' => $row['id'],
							'side' => $row['side'],
							'pos' => $row['pos'],
							'active' => $row['active'],
							'cache' => $row['cache'],
							'blocktype' => $row['blocktype'],
							'acsgrp' => $row['acsgrp'],
							'config' => pmx_json_decode($row['config'], true),
						);
					$pmxcFunc['db_free_result']($request);
				}

				// load popup js and css for overview
				loadJavascriptFile(PortaMx_loadCompressed('PortalPopup.js'), array('external' => true));
				loadCSSFile(PortaMx_loadCompressed('portal_bmpopup.css'), array('external' => true));
			}

			elseif(empty($_POST['save_edit']))
			{
				// load the class file and create the object
				loadCSSFile(PortaMx_loadCompressed('portal_bmpopup.css'), array('external' => true));
				require_once($context['pmx_sysclassdir']. 'AdminBlocksClass.php');
				$context['pmx']['editblock'] = PortaMx_getAdmEditBlock($id, $block, $newBlockSide);
			}
		}
		else
			fatal_lang_error('pmx_acces_error', false);
	}
}
?>