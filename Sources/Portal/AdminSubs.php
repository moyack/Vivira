<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file AdminSubs.php
 * AdminSubs holds all subroutines for the Admin part.
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* check if it is a legal POST session.
* Skip on admin security logon
*/
function PortaMx_checkPOST()
{
	global $context;

	// cleanup POST array
	if(empty($_POST))
		return false;

	$_POST = PortaMx_makeSafe($_POST);

	// id admin security logon ?
	if(isset($_POST['admin_pass']))
	{
		// yes .. remove the posts
		unset($_POST['admin_pass']);
		if(isset($_POST['admin_hash_pass']))
			unset($_POST['admin_hash_pass']);
		if(isset($_POST[$context['session_var']]))
			unset($_POST[$context['session_var']]);
	}
	return !empty($_POST);
}

/**
* Loads a block for the block manager Editblock.
*/
function PortaMx_getAdmEditBlock($id = null, $block = null, $side = null)
{
	global $pmxcFunc, $context, $sourcedir, $boardurl, $modSettings, $user_info, $options, $txt;

	// Is it a new block ?
	if(is_null($id))
		$block = PortaMx_getdefaultBlock($side, $block);

	// no, get the block by id
	elseif(is_null($block))
	{
		$result = null;
		$request = $pmxcFunc['db_query']('', '
			SELECT * FROM {db_prefix}portal_blocks
			WHERE id = {int:id}',
			array('id' => $id)
		);

		if($pmxcFunc['db_num_rows']($request) > 0)
		{
			$block = $pmxcFunc['db_fetch_assoc']($request);
			$pmxcFunc['db_free_result']($request);
		}
	}

	if(in_array($block['blocktype'], array('bbc_script', 'script', 'fader', 'php')))
	{
		if(in_array($block['blocktype'], array('script', 'fader')))
		{
			addInlineCss('
	textarea{min-height:100px;resize:vertical;}');

			$context['pmx']['script'] = array(
				'id' => 'content',
				'value' => !empty($block['content'])? convertSmileysToUser($block['content']) : '',
				'width' => '100%',
				'height' => '150px',
				'havecont' => !empty($cont),
			);
		}

		elseif($block['blocktype'] == 'php')
		{
			addInlineCss('
	textarea{min-height:100px;resize:vertical;}');

			if(preg_match('~\[\?pmx_initphp(.*)pmx_initphp\?\]~is', $block['content'], $match))
				$cont = trim($match[1]);
			else
				$cont = '';

			$context['pmx']['phpInit'] = array(
				'id' => 'content_init',
				'value' => $pmxcFunc['htmlspecialchars']($cont, ENT_NOQUOTES),
				'width' => '100%',
				'height' => '150px',
				'havecont' => !empty($cont),
			);

			if(preg_match('~\[\?pmx_showphp(.*)pmx_showphp\?\]~is', $block['content'], $match))
				$cont = trim($match[1]);
			else
				$cont = $block['content'];

			$context['pmx']['phpShow'] = array(
				'id' => 'content',
				'value' => $pmxcFunc['htmlspecialchars']($cont, ENT_NOQUOTES),
				'width' => '100%',
				'height' => '150px',
				'havecont' => !empty($cont),
			);
		}
		else
		{
			// Let's load the SCEditor.
			$modSettings['smiley_enable'] = true;
			require_once($sourcedir . '/Subs-Editor.php');

			if(!empty($block['content']))
			{
				require_once($sourcedir . '/Subs-Post.php');
				$block['content'] = un_preparsecode($block['content']);
			}

			// Now create the editor.
			$editorOptions = array(
				'id' => 'content',
				'value' => !empty($block['content'])? $block['content'] : '',
				'disable_smiley_box' => (in_array($block['blocktype'], array('script', 'fader')) ? true : false),
				'labels' => array(),
				// add height and width for the editor
				'height' => '250px',
				'width' => '100%',
				'preview_type' => 0,
				'bbc_level' => (in_array($block['blocktype'], array('script', 'fader')) ? 0 : 'full'),
				'disable_smiley_box' => (in_array($block['blocktype'], array('script', 'fader')) ? 1 : 0),
				'locale' => !empty($txt['lang_locale']) && substr($txt['lang_locale'], 0, 5) != 'en_US' ? $txt['lang_locale'] : '',
				'form' => 'pxmedit',
				'required' => true,
			);
			$_REQUEST['content_mode'] = '';
			create_control_richedit($editorOptions);
			$context['pmx']['editorID'] = $editorOptions['id'];
		}
	}

	// for html blocks
	elseif(in_array($block['blocktype'], array('html', 'download')))
	{
		loadJavascriptFile($boardurl .'/ckeditor/ckeditor.js', array('external' => true));

		$context['pmx']['htmledit'] = array(
			'id' => 'content',
			'content' => convertSmileysToUser($block['content']),
		);
	}

	require_once($context['pmx_classdir']. $block['blocktype'] .'_adm.php');
	$block_type = 'pmxc_'. $block['blocktype'] .'_adm';
	$result = new $block_type($block);

	// Initialize the admin block
	$result->pmxc_AdmBlock_loadinit('');
	return $result;
}

/**
* load the article editor by article type.
* field: name of a input element.
* content: the content in the editor or empty.
*/
function PortaMx_EditArticle($type, $field, $content)
{
	global $context, $sourcedir, $boardurl, $modSettings, $user_info, $options, $pmxcFunc, $txt;

	// for html blocks
	if($type == 'html')
	{
		loadJavascriptFile($boardurl .'/ckeditor/ckeditor.js', array('external' => true));

		$context['pmx']['htmledit'] = array(
			'id' => 'content',
			'content' => convertSmileysToUser($content),
		);
	}
	else
	{
		if($type == 'script')
		{
			addInlineCss('
	textarea{min-height:100px;resize:vertical;}');

			$context['pmx']['script'] = array(
				'id' => 'content',
				'value' => !empty($content) ? convertSmileysToUser($content) : '',
				'width' => '100%',
				'height' => '150px',
			);
		}

		elseif($type == 'php')
		{
			addInlineCss('
	textarea{min-height:100px;}');

			if(preg_match('~\[\?pmx_initphp(.*)pmx_initphp\?\]~is', $content, $match))
					$cont = trim($match[1]);
				else
					$cont = '';

				$context['pmx']['phpInit'] = array(
					'id' => 'content_init',
					'value' => $pmxcFunc['htmlspecialchars']($cont, ENT_NOQUOTES),
					'width' => '100%',
					'height' => '150px',
					'havecont' => !empty($cont),
				);

				if(preg_match('~\[\?pmx_showphp(.*)pmx_showphp\?\]~is', $content, $match))
					$cont = trim($match[1]);
				else
					$cont = $content;

				$context['pmx']['phpShow'] = array(
					'id' => 'content',
					'value' => $pmxcFunc['htmlspecialchars']($cont, ENT_NOQUOTES),
					'width' => '100%',
					'height' => '150px',
					'havecont' => !empty($cont),
				);
		}
		else
		{
			// Let's load the SCEditor editor.
			$modSettings['smiley_enable'] = true;
			require_once($sourcedir . '/Subs-Editor.php');

			// Remove special formatting we don't want anymore.
			if(!empty($content))
			{
				require_once($sourcedir . '/Subs-Post.php');
				$content = un_preparsecode($content);
			}

			// Now create the editor.
			$editorOptions = array(
				'id' => 'content',
				'value' => !empty($content)? $content : '',
				'disable_smiley_box' => (in_array($type, array('script', 'fader')) ? true : false),
				'labels' => array(),
				// add height and width for the editor
				'height' => '250px',
				'width' => '100%',
				'preview_type' => 0,
				'bbc_level' => 'full',
				'disable_smiley_box' => 0,
				'locale' => !empty($txt['lang_locale']) && substr($txt['lang_locale'], 0, 5) != 'en_US' ? $txt['lang_locale'] : '',
				'form' => 'pxmedit',
				'required' => true,
			);
			$_REQUEST['content_mode'] = '';
			create_control_richedit($editorOptions);
			$context['pmx']['editorID'] = $editorOptions['id'];
		}
	}
}

/**
* convert php content
**/
function pmxPHP_convert()
{
	$find = array('/^<\?php/i', '/^<\?/', '/\?>$/');
	$initcont = !empty($_POST['content_init']) ? trim(preg_replace($find, '', trim($_POST['content_init']))) : '';
	$showcont = !empty($_POST['content']) ? trim(preg_replace($find, '', trim($_POST['content']))) : '';
	if(!empty($initcont))
		$_POST['content'] = '[?pmx_initphp'."\n". $initcont ."\n".'pmx_initphp?]' ."\n". '[?pmx_showphp'."\n". $showcont ."\n".'pmx_showphp?]';
	else
		$_POST['content'] = $showcont;

	unset($_POST['content_init']);
	unset($_POST['content_show']);
}

/**
* get the setup for default Css classes.
*/
function PortaMx_getdefaultClass($extended = false, $isarticle = false)
{
	global $txt;

	$result = array(
		'header' => array(
			' '. $txt['pmx_default_header_none'] => 'none',
			'+'. $txt['pmx_default_header_titlebg'] => 'catbg notrnd',
			' '. $txt['pmx_default_header_catbg'] => 'catbg rnd',
		),
		'frame' => array(
			' '. $txt['pmx_default_header_none'] => 'none',
			'+border' => 'pmxborder',
			),
		'body' => array(
			' '.$txt['pmx_default_none'] => 'windowbg nobg',
			'+windowbg' => 'windowbg',
			' windowbg2' => 'windowbg2',
		),
	);

	$article = array(
		'bodytext' => array(
			' '.$txt['pmx_default_none'] => 'none',
			'+small' => 'smalltext',
			' middle' => 'middletext',
			' large' => 'largetext',
		)
	);
	$result = array_merge($result, $article);

	if(!empty($extended))
	{
		$extend = array(
			'postheader' => array(
				' '. $txt['pmx_default_header_none'] => 'none',
				' '. $txt['pmx_default_header_asbody'] => 'as_body',
				'+'. $txt['pmx_default_header_titlebg'] => 'catbg',
			),
			'postframe' => array(
				' '. $txt['pmx_default_header_none'] => 'none',
				'+border' => 'pmxborder',
			),
			'postbody' => array(
				' '. $txt['pmx_default_none'] => 'windowbg nobg',
				'+windowbg' => 'windowbg2',
				' windowbg2' => 'windowbg',
			),
		);
		$result = array_merge($result, $extend);
	}
	return $result;
}

/**
* get the default Block class & config.
*/
function PortaMx_getdefaultBlock($side, $blocktype)
{
	global $context;

	$lang = array();
	foreach($context['pmx']['languages'] as $lng => $sel)
		$lang[$lng] = '';

	$config = array(
		'title' => $lang,
		'title_align' => 'left',
		'title_icon' => '',
		'collapse' => 0,
		'overflow' => '',
		'innerpad' => 4,
		'visuals' => array(
			'header' => '',
			'frame' => '',
			'body' => '',
			'bodytext' => '',
		),
		'cssfile' => '',
		'ext_opts' => array(
			'pmxact' => array(),
			'pmxcust' => '',
			'pmxbrd' => array(),
			'pmxlng' => array(),
			'pmxthm' => array(),
		),
		'can_moderate' => allowedTo('admin_forum') ? 0 : 1,
		'settings' => array(),
	);

	$data = array(
		'id' => 0,
		'side' => $side,
		'pos' => 0,
		'active' => 0,
		'cache' => 0,
		'blocktype' => $blocktype,
		'acsgrp' => '',
		'config' => json_encode($config, true),
		'content' => '',
	);

	return $data;
}

/**
* get the default article Config.
*/
function PortaMx_getdefaultArticle($arttype)
{
	global $context, $user_info;

	$lang = array();
	foreach($context['pmx']['languages'] as $lng => $sel)
		$lang[$lng] = '';

	$config = array(
		'title' => $lang,
		'title_align' => 'left',
		'title_icon' => '',
		'collapse' => 0,
		'overflow' => '',
		'innerpad' => 4,
		'visuals' => array(
			'header' => '',
			'frame' => '',
			'body' => '',
			'bodytext' => '',
		),
		'cssfile' => '',
		'can_moderate' => allowedTo('admin_forum') ? 0 : 1,
		'settings' => array(),
	);

	$result = array(
		'id' => 0,
		'name' => '',
		'catid' => 0,
		'acsgrp' => '',
		'ctype' => $arttype,
		'config' => json_encode($config, true),
		'content' => '',
		'active' => 0,
		'owner' => $user_info['id'],
		'created' => 0,
		'approved' => 0,
		'approvedby' => 0,
		'updated' => 0,
		'updatedby' => 0,
	);

	return $result;
}

/**
* get the default category Config.
*/
function PortaMx_getDefaultCategory($name = '')
{
	global $context;

	$lang = array();
	foreach($context['pmx']['languages'] as $lng => $sel)
		$lang[$lng] = '';

	$config = array(
		'title' => $lang,
		'title_align' => 'left',
		'title_icon' => '',
		'collapse' => 0,
		'overflow' => '',
		'innerpad' => 4,
		'visuals' => array(
			'header' => '',
			'frame' => '',
			'body' => '',
			'bodytext' => '',
		),
		'cssfile' => '',
		'can_moderate' => allowedTo('admin_forum') ? 0 : 1,
		'settings' => array(
			'framemode' => 'both',
			'global' => 0,
			'request' => 0,
			'showmode' => 'sidebar',
			'sidebarwidth' => 140,
			'addsubcats' => 0,
			'pages' => '20',
			'showsubcats' => 0,
			'catsbarwidth' => 140,
			'inherit_acs' => 0,
		),
	);

	$result = array(
		'id' => 0,
		'name' => $name,
		'parent' => 0,
		'level' => 0,
		'catorder' => 0,
		'acsgrp' => '',
		'artsort' => array(),
		'config' => json_encode($config, true),
	);

	return $result;
}

/**
* Get all categorie id's
**/
function pmx_getAllCatID()
{
	global $pmxcFunc;

	$result = array();
	$request = $pmxcFunc['db_query']('', '
			SELECT id
			FROM {db_prefix}portal_categories
			ORDER BY id',
		array()
	);
	while($row = $pmxcFunc['db_fetch_assoc']($request))
		$result[] = $row['id'];

	$pmxcFunc['db_free_result']($request);
	$result[] = '0';

	return implode(',', $result);
}

/**
* Get all Category detais
**/
function pmx_getAllCatDetais($allcats, &$detais)
{
	global $txt;

	foreach($allcats as $cat)
	{
		$detais[$cat['id']] = PortaMx_getCatDetails($cat, PortaMx_getCategories());
		if(!empty($cat['childs']))
			pmx_getAllCatDetais($cat['childs'], $detais);
	}

	$detais[0] = array(
		'class' => 'cat_none',
		'level' => '0',
		'parent' => $txt['pmx_categories_none'],
		'name' => $txt['pmx_categories_none'],
	);
}

/**
* get category datails.
*/
function PortaMx_getCatDetails($category, $allcats)
{
	global $txt;

	if(empty($category['id']) || !is_array($category))
	{
		$catclass = 'cat_none';
		$parent = $txt['pmx_chg_articlnocats'];
		$level = '0';
		$name = $category['name'];
	}
	elseif(is_array($category['childs']))
	{
		$catclass = 'cat_child';
		if(empty($category['parent']))
		{
			$catclass = 'cat_rootchild';
			$parent =  $txt['pmx_categories_rootchild'];
			$level = '0';
			$name = $category['name'];
		}
		else
		{
			$pcat = PortaMx_getCatByID($allcats, $category['parent']);
			$catclass = 'cat_child';
			$parent = sprintf($txt['pmx_categories_child'], $pcat['name']);
			$level = $category['level'];
			$name = $category['name'];
		}
	}
	elseif(!empty($category['parent']))
	{
		$pcat = PortaMx_getCatByID($allcats, $category['parent']);
		$catclass = 'cat_level';
		$parent = sprintf($txt['pmx_categories_child'], $pcat['name']);
		$level = $category['level'];
		$name = $category['name'];
	}
	else
	{
		$catclass = 'cat_root';
		$parent = $txt['pmx_categories_root'];
		$level = '0';
		$name = $category['name'];
	}

	return array(
		'class' => $catclass,
		'parent' => $parent,
		'level' => $level,
		'name' => $name ,
	);
}

/**
* get all user groups.
*/
function PortaMx_getUserGroups($noGuest = false, $showPostcount = true)
{
	global $pmxcFunc, $context, $txt;

	// guest & normal members
	if(empty($noGuest))
	{
		$i = 2;
		$result[0] = array(
			'id' => '-1',
			'name' => $txt['pmx_guest'],
		);
		$result[1] = array(
			'id' => '0',
			'name' => $txt['pmx_ungroupedmembers'],
		);
		$where = (!empty($showPostcount) && !empty($context['pmx']['settings']['postcountacs'])) ? '' : 'WHERE min_posts = -1';
	}
	else
	{
		$i = 0;
		$result = array();
		$where = 'WHERE min_posts = -1';
	}

	// get membergroups
	$where = (!empty($showPostcount) && !empty($context['pmx']['settings']['postcountacs'])) ? '' : 'WHERE min_posts = -1';
	$request = $pmxcFunc['db_query']('', '
			SELECT id_group, group_name
			FROM {db_prefix}membergroups
			'. $where .'
			ORDER BY id_group DESC',
		array()
	);
	while($row = $pmxcFunc['db_fetch_assoc']($request))
	{
		$result[$i] = array(
			'id' => $row['id_group'],
			'name' => $row['group_name'],
		);
		$i++;
	}
	$pmxcFunc['db_free_result']($request);

	return $result;
}

/**
* get all boards.
*/
function PortaMx_getBoards($redir_boards = false, $add_recycle_boards = false)
{
	global $modSettings, $pmxcFunc;

	$result = null;
	$request = $pmxcFunc['db_query']('', '
			SELECT id_board, name, child_level
			FROM {db_prefix}boards
			WHERE id_board != {int:excl}'. (empty($redir_boards) ? ' AND redirect = {string:nullstr}' : '') .'
			ORDER BY board_order',
		array(
			'excl' => !empty($modSettings['recycle_enable']) && isset($modSettings['recycle_board']) && empty($add_recycle_boards) ? $modSettings['recycle_board'] : '0',
			'nullstr' => ''
		)
	);
	while($row = $pmxcFunc['db_fetch_assoc']($request))
		$result[] = array(
			'id' => $row['id_board'],
			'name' => (!empty($row['child_level']) ? str_repeat('&bull;', $row['child_level']).' ' : '') . $row['name']
		);

	$pmxcFunc['db_free_result']($request);
	return $result;
}

/**
* get all title icons.
*/
function PortaMx_getAllTitleIcons()
{
	global $context;

	$result = array();
	if(is_dir($context['pmx_Iconsdir']))
	{
		if($dh = opendir($context['pmx_Iconsdir']))
		{
			while(($file = readdir($dh)) !== false)
			{
				if($file != 'none.png' && $file != 'index.php' && $file != '..' && $file != '.')
					$result[$file] = ucfirst(str_replace('.png', '', $file));
			}
		}
		closedir($dh);
	}
	natsort($result);
	$result = array('none.png' => 'None') + $result;
	return $result;
}
?>