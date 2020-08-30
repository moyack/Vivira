<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file AdminCategories.php
 * AdminCategories reached all Posts from Categorie Manager.
 * Checks the values and saved the parameter to the database.
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* Receive all the Posts from Categories Manager, check and save it.
* Finally the categories are prepared and the templare loaded.
*/
function Portal_AdminCategories()
{
	global $pmxcFunc, $context, $scripturl, $txt;

	$admMode = isset($_GET['action']) ? $_GET['action'] : '';

	if(($admMode == 'admin' || $admMode == 'portal') && allowPmx('pmx_admin') && isset($_GET['area']) && $_GET['area'] == 'pmx_categories')
	{
		require_once($context['pmx_sourcedir'] .'AdminSubs.php');

		$context['pmx']['subaction'] = isset($_POST['sa']) ? $_POST['sa'] : 'overview';

		// From template ?
		if(PortaMx_checkPOST())
		{
			// check the Post session
			checkSession('post');

			// actions from overview ?
			if($context['pmx']['subaction'] == 'overview' && empty($_POST['cancel_overview']))
			{
				// updates from overview popups ?
				if(!empty($_POST['upd_overview']))
				{
					if(isset($_POST['xml']))
						$xmlResult = '';

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
					foreach($updates as $id => $values)
					{
						$request = $pmxcFunc['db_query']('', '
							SELECT config, acsgrp
							FROM {db_prefix}portal_categories
							WHERE id = {int:id}',
							array('id' => $id)
						);
						$row = $pmxcFunc['db_fetch_assoc']($request);
						$pmxcFunc['db_free_result']($request);

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
									UPDATE {db_prefix}portal_categories
									SET config = {string:config}
									WHERE id = {int:id}',
									array(
										'id' => $id,
										'config' => json_encode($cfg, true))
								);
							}

							// update category name
							elseif($rowname == 'catname')
							{
								$pmxcFunc['db_query']('', '
									UPDATE {db_prefix}portal_categories
									SET name = {string:val}
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
									UPDATE {db_prefix}portal_categories
									SET acsgrp = {string:val}
									WHERE id = {int:id}',
									array(
										'id' => $id,
										'val' => implode(',', $newacs))
								);

								// send by xml?
								if(isset($_POST['xml']))
								{
									$newacs = implode(',', $newacs);
									$count = !empty($newacs) ? count(explode(',', $newacs)) : 0;
									$xmlResult .= (!empty($xmlResult) ? '&' : '') . $id .'|'. $newacs .'|'. $count .'|' . intval(allowPmxGroup($newacs)) .'|1';
								}
							}
						}
					}

					// clear cache
					clearBlocksCache(null, true);

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

				// add new category
				if(!empty($_POST['add_new_category']))
				{
					$category = PortaMx_getDefaultCategory();
					$context['pmx']['subaction'] = 'editnew';
				}

				// edit / clone Categories
				elseif(!empty($_POST['edit_category']) || !empty($_POST['clone_category']))
				{
					$id = PortaMx_makeSafe(!empty($_POST['clone_category']) ? $_POST['clone_category'] : $_POST['edit_category']);

					// load the category for edit/clone
					$request = $pmxcFunc['db_query']('', '
						SELECT *
						FROM {db_prefix}portal_categories
						WHERE id = {int:id}',
						array(
							'id' => $id
						)
					);
					$row = $pmxcFunc['db_fetch_assoc']($request);
					$category = array(
						'id' => $row['id'],
						'name' => $row['name'],
						'parent' => $row['parent'],
						'level' => $row['level'],
						'catorder' => $row['catorder'],
						'acsgrp' => $row['acsgrp'],
						'artsort' => $row['artsort'],
						'config' => $row['config'],
					);
					$pmxcFunc['db_free_result']($request);

					if(!empty($_POST['clone_category']))
					{
						$category['id'] = 0;
						$category['parent'] = 0;
						$category['level'] = 0;
						$category['catorder'] = 0;
						$context['pmx']['subaction'] = 'editnew';
					}
					else
						$context['pmx']['subaction'] = 'edit';
				}

				// delete category ?
				elseif(!empty($_POST['delete_category']))
				{
					pmx_delete_cat(PortaMx_makeSafe($_POST['delete_category']));

					// set catid in articles to none (0)
					$pmxcFunc['db_query']('', '
						UPDATE {db_prefix}portal_articles
						SET catid = 0
						WHERE catid = {int:id}',
						array('id' => PortaMx_makeSafe($_POST['delete_category']))
					);

					// clear cache
					clearBlocksCache(null, true);
				}

				// move category ?
				elseif(!empty($_POST['move_category']))
				{
					pmx_move_cat(PortaMx_makeSafe($_POST['move_category']), PortaMx_makeSafe($_POST['catplace']), PortaMx_makeSafe($_POST['movetocat']));

					// clear cache
					clearBlocksCache(null, true);
				}
			}

			// edit category canceled ?
			elseif(!empty($_POST['cancel_edit']) || !empty($_POST['cancel_overview']))
				$context['pmx']['subaction'] = 'overview';

			// actions from edit category
			elseif($context['pmx']['subaction'] == 'editnew' || $context['pmx']['subaction'] == 'edit')
			{
				// check defined numeric vars (check_num_vars holds the posted array to check like [varname][varname] ...)
				if(isset($_POST['check_num_vars']))
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

				// get all data
				$category = array(
					'id' => $_POST['id'],
					'name' => PortaMx_makeSafe($_POST['name']),
					'parent' => $_POST['parent'],
					'level' => $_POST['level'],
					'catorder' => $_POST['catorder'],
					'acsgrp' => (!empty($_POST['acsgrp']) ? implode(',', $_POST['acsgrp']) : ''),
					'artsort' => (!empty($_POST['artsort']) ? implode(',', $_POST['artsort']) : ''),
					'config' => json_encode($_POST['config'], true),
				);

				// save category.
				if(empty($_POST['edit_change']) && (!empty($_POST['save_edit']) || !empty($_POST['save_edit_continue'])))
				{
					// if new category get the last id and catorder
					if($context['pmx']['subaction'] == 'editnew')
					{
						$category = pmx_insert_cat(PortaMx_makeSafe($_POST['catplace']), PortaMx_makeSafe($_POST['catid']), $category);

						// get max catid
						$request = $pmxcFunc['db_query']('', '
							SELECT MAX(id)
							FROM {db_prefix}portal_categories',
							array()
						);
						list($maxid) = $pmxcFunc['db_fetch_row']($request);
						$pmxcFunc['db_free_result']($request);
						$category['id'] = strval(1 + ($maxid === null ? $category['id'] : $maxid));
					}

					// now save all data
					$pmxcFunc['db_insert']('replace', '
						{db_prefix}portal_categories',
						array(
							'id' => 'int',
							'name' => 'string',
							'parent' => 'int',
							'level' => 'int',
							'catorder' => 'int',
							'acsgrp' => 'string',
							'artsort' => 'string',
							'config' => 'string',
						),
						array(
							$category['id'],
							$category['name'],
							$category['parent'],
							$category['level'],
							$category['catorder'],
							$category['acsgrp'],
							$category['artsort'],
							$category['config'],
						),
						array('id')
					);

					// clear cache
					clearBlocksCache(null, true);

					$context['pmx']['subaction'] = 'edit';
					$_SESSION['saved_successful'] = true;
				}

				// continue edit ?
				if(!empty($_POST['save_edit']))
					$context['pmx']['subaction'] = 'overview';
			}
			if($context['pmx']['subaction'] == 'overview')
				redirectexit('action='. $admMode .';area=pmx_categories;'. $context['session_var'] .'=' .$context['session_id']);
		}

		// load template, setup pagetitle
		loadTemplate($context['pmx_templatedir'] .'AdminCategories');
		$context['page_title'] = $txt['pmx_categories'];
		$context['pmx']['AdminMode'] = $admMode;

		// direct edit request?
		if(isset($_GET['sa']) && PortaMx_makeSafe($_GET['sa']) == 'edit' && !empty($_GET['id']))
		{
			// load the category for edit
			$request = $pmxcFunc['db_query']('', '
				SELECT *
				FROM {db_prefix}portal_categories
				WHERE id = {int:id}',
				array(
					'id' => PortaMx_makeSafe($_GET['id'])
				)
			);
			if($pmxcFunc['db_num_rows']($request) > 0)
			{
				$row = $pmxcFunc['db_fetch_assoc']($request);
				$category = array(
					'id' => $row['id'],
					'name' => $row['name'],
					'parent' => $row['parent'],
					'level' => $row['level'],
					'catorder' => $row['catorder'],
					'acsgrp' => $row['acsgrp'],
					'artsort' => $row['artsort'],
					'config' => $row['config'],
				);
				$pmxcFunc['db_free_result']($request);

				$context['pmx']['subaction'] = 'edit';
			}
		}

		// continue edit or overview ?
		if($context['pmx']['subaction'] == 'overview')
		{
			// load all categories
			$context['pmx']['categories'] = PortaMx_getCategories(true);

			// load popup js and css for overview
			loadJavascriptFile(PortaMx_loadCompressed('PortalPopup.js'), array('external' => true));
			loadCSSFile(PortaMx_loadCompressed('portal_cmpopup.css'), array('external' => true));
		}
		elseif(empty($_POST['save_edit']))
		{
			// load the class file and create the object
			loadCSSFile(PortaMx_loadCompressed('portal_cmpopup.css'), array('external' => true));
			require_once($context['pmx_sysclassdir']. 'AdminCategoriesClass.php');
			$context['pmx']['editcategory'] = new PortaMxC_SystemAdminCategories($category);
			$context['pmx']['editcategory']->pmxc_AdmCategories_loadinit();
		}
	}
	else
		fatal_error($txt['pmx_acces_error']);
}

/**
* Move categories
**/
function pmx_move_cat($id, $place, $toid)
{
	global $pmxcFunc;

	// first delete from cat
	$allcats = PortaMx_getCategories();
	$movecat = PortaMx_getCatByID($allcats, $id);
	unset($movecat['childs']);
	unset($movecat['artsum']);
	pmx_delete_cat($id);

	// now insert at place
	$movecat = pmx_insert_cat($place, $toid, $movecat);
	$pmxcFunc['db_insert']('replace', '
		{db_prefix}portal_categories',
		array(
			'id' => 'int',
			'name' => 'string',
			'parent' => 'int',
			'level' => 'int',
			'catorder' => 'int',
			'acsgrp' => 'string',
			'artsort' => 'string',
			'config' => 'string',
		),
		array(
			$movecat['id'],
			$movecat['name'],
			$movecat['parent'],
			$movecat['level'],
			$movecat['catorder'],
			$movecat['acsgrp'],
			$movecat['artsort'],
			$movecat['config'],
		),
		array('id')
	);

	// cleanup..
	unset($allcats);
	unset($movecat);

	// clear cache
	clearBlocksCache(null, true);
}

/**
* Delete categories
**/
function pmx_delete_cat($id)
{
	global $pmxcFunc;

	$allcats = PortaMx_getCategories();
	$deleteCat = PortaMx_getCatByID($allcats, $id);
	$before = PortaMx_getCatByOrder($allcats, PortaMx_getPrevCat($deleteCat['catorder']));
	if(!is_array($before))
	{
		$before['id'] = 0;
		$before['level'] = 0;
		$before['parent'] = 0;
	}

	$catorder = PortaMx_getNextCat($deleteCat['catorder']);
	while(is_array($current = PortaMx_getCatByOrder($allcats, $catorder)))
	{
		// relink parent
		if($current['parent'] == $id)
		{
			// update first child parent/level
			$pmxcFunc['db_query']('', '
				UPDATE {db_prefix}portal_categories
				SET parent = {int:parent}, level = {int:level}
				WHERE id = {int:id}',
				array(
					'parent' => empty($deleteCat['level']) ? 0 : $before['id'],
					'level' => $deleteCat['level'],
					'id' => $current['id'])
			);

			// adjust child levels
			if(is_array($current['childs']))
			{
				$level = $deleteCat['level'];
				do
				{
					$level += 1;
					$catorder = PortaMx_getNextCat($current['catorder']);
					$current = PortaMx_getCatByOrder($allcats, $catorder);
					$pmxcFunc['db_query']('', '
						UPDATE {db_prefix}portal_categories
						SET level = {int:level}
						WHERE id = {int:id}',
						array(
							'id' => $current['id'],
							'level' => $level)
					);
				} while(is_array($current['childs']));
			}
		}
		$catorder = PortaMx_getNextCat($current['catorder']);
	}

	// delet category
	$pmxcFunc['db_query']('', '
		DELETE FROM {db_prefix}portal_categories
		WHERE id = {int:id}',
		array('id' => $id)
	);

	// shiftdown the catorder
	$pmxcFunc['db_query']('', '
		UPDATE {db_prefix}portal_categories
		SET catorder = catorder - 1
		WHERE catorder >= {int:corder}',
		array('corder' => $deleteCat['catorder'])
	);

	// cleanup..
	unset($allcats);
	unset($deleteCat);
	unset($before);
	unset($current);

	// clear cache
	clearBlocksCache(null, true);
}

/**
* Insert categories
**/
function pmx_insert_cat($place, $id, $category)
{
	global $pmxcFunc;

	// get max catorder
	$request = $pmxcFunc['db_query']('', '
		SELECT MAX(catorder)
		FROM {db_prefix}portal_categories',
		array()
	);
	list($maxorder) = $pmxcFunc['db_fetch_row']($request);
	$pmxcFunc['db_free_result']($request);

	// category table empty?
	if(empty($maxorder))
	{
		$category['catorder'] = 1;
		return $category;
	}

	// handle the placement
	$allcats = PortaMx_getCategories();
	if($category['id'] == $id)
		$placeCat = $category;
	else
		$placeCat = PortaMx_getCatByID($allcats, $id);

	// insert before
	if($place == 'before')
	{
		$category['catorder'] = $placeCat['catorder'];
		$category['parent'] = $placeCat['parent'];
		$category['level'] = $placeCat['level'];
	}

	// insert after
	elseif($place == 'after')
	{
		$lastFnd = $placeCat;
		if(($placeCat['level'] < $category['level'] || $placeCat['level'] == 0) && is_array($placeCat['childs']))
		{
			while(is_array($lastFnd['childs']) || !empty($lastFnd['parent']))
				$lastFnd = PortaMx_getCatByOrder($allcats, PortaMx_getNextCat($lastFnd['catorder']));
		}
		else
			$lastFnd['catorder'] = PortaMx_getNextCat($lastFnd['catorder']);

		if(empty($lastFnd))
		{
			$category['catorder'] = $maxorder +1;
			$category['parent'] = 0;
			$category['level'] = 0;
		}
		else
		{
			$category['catorder'] = $lastFnd['catorder'];
			$category['parent'] = $placeCat['parent'];
			$category['level'] = $placeCat['level'];
		}
		unset($lastFnd);
	}

	// insert as child
	elseif($place == 'child')
	{
		$category['catorder'] = PortaMx_getNextCat($placeCat['catorder']);
		$category['parent'] = $placeCat['id'];
		$category['level'] = $placeCat['level'] +1;
	}

	// shiftup the catorder
	$pmxcFunc['db_query']('', '
		UPDATE {db_prefix}portal_categories
		SET catorder = catorder + 1
		WHERE catorder >= {int:corder}',
		array('corder' => $category['catorder'])
	);

	// cleanup..
	unset($allcats);
	unset($placeCat);

	// clear cache
	clearBlocksCache(null, true);

	return $category;
}
?>