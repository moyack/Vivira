<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file PortaMx_AdminArticlesClass.php
 * Global Articles Admin class
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* @class PortaMxC_AdminArticles
* The Global Class for Articles Administration.
* @see PortaMx_AdminArticlesClass.php
*/
class PortaMxC_AdminArticles
{
	var $cfg;						///< common config

	/**
	* The Contructor.
	* Saved the config, load the article css file if exist.
	* Have the article a css file, the class definition is extracted from ccs header
	*/
	function __construct($config)
	{
		// get the article config array
		if(isset($config['config']))
			$config['config'] = pmx_json_decode($config['config'], true);
		$this->cfg = $config;
	}
}

/**
* @class PortaMxC_SystemAdminArticle
* This is the Global Admin class to create or edit a Article.
* This class prepare the settings screen and the and content.
* @see PortaMx_AdminArticlesClass.php
*/
class PortaMxC_SystemAdminArticle extends PortaMxC_AdminArticles
{
	var $pmx_groups;				///< all usergroups
	var $title_icons;				///< array with title icons
	var $custom_css;				///< custom css definitions
	var $usedClass;					///< used class types
	var $categories;				///< all exist categories

	/**
	* This Methode is called on loadtime.
	* After all variables initiated, it calls the block dependent init methode.
	* Finaly the css is loaded if exist
	*/
	function pmxc_AdmArticle_loadinit()
	{
		global $context;
	
		$this->pmx_groups = PortaMx_getUserGroups();										// get all usergroups
		$this->title_icons = PortaMx_getAllTitleIcons();								// get all title icons
		$this->custom_css = PortaMx_getCustomCssDefs();									// custom css definitions
		$this->usedClass = PortaMx_getdefaultClass(false, true);				// default class types
		$this->categories = PortaMx_getCategories();										// exist categories

		addInlineJavascript(str_replace("\n", "\n\t", PortaMx_compressJS('
		function pack(chrstr) {
			var hexstr = "";
			for(var i = 0; i < chrstr.length; i++)
			{
				var c = chrstr.charCodeAt(i);
				var h = "00" + c.toString(16);
				hexstr += h.substr(h.length-2);
			}
			return hexstr;
		}
		function php_syntax(elmid)
		{
			document.getElementById("check_" + elmid).innerHTML = "<img onclick=\"Hide_SyntaxCheck(this.parentNode)\" style=\"padding-left:10px;cursor:pointer;\" alt=\"close\" src=\"'. $context['pmx_imageurl'] .'cross.png\" class=\"pmxright\" />";
			document.getElementById("check_" + elmid).className = "info_frame";
			var postData = {};
			postData["value"] = pack(document.getElementById(elmid).value);
			var result = pmxXMLpost(pmx_scripturl +"?jscook&mode=syntax&xml", postData);
			result = result.replace(/@elm@/, elmid);
			result = result.replace(/<br \/>/gi, "");
			temp = result.replace(/<b>/gi, "");
			temp = temp.replace(/<\/b>/gi, "");
			var patt = /(on\s+line\D+)(\d+)/;
			res = patt.exec(temp);
			LineNo = "";
			if(res)
				LineNo = res[2];
			if(result.indexOf("in <b>") != -1)
			{
				result = result.substring(result.indexOf("<b>Parse error</b>:  "), result.indexOf("in <b>"));
				result = result.replace(/<b>/gi, "");
				result = result + " on line " + LineNo;
			}
			document.getElementById("check_" + elmid).innerHTML = document.getElementById("check_" + elmid).innerHTML + result;
			Show_help("check_" + elmid);
			php_showerrline(elmid, LineNo);
		}
		function php_showerrline(elmid, errLine)
		{
			if(errLine != "" && errLine != "0" && !isNaN(errLine))
			{
				errLine = parseInt(errLine);
				var lines = document.getElementById(elmid).value.split("\n");
				var count = 0;
				for(var i = 0; i < errLine -1; i++)
					count += lines[i].length +1;

				if(document.getElementById(elmid).setSelectionRange)
				{
					document.getElementById(elmid).focus();
					document.getElementById(elmid).setSelectionRange(count, count+lines[i].length);
				}
				else if(document.getElementById(elmid).createTextRange)
				{
					range=document.getElementById(elmid).createTextRange();
					range.collapse(true);
					range.moveStart("character", count);
					range.moveEnd("character", count+lines[i].length);
					range.select();
				}
			}
		}')));
	}

	/**
	* Output the Article config screen
	*/
	function pmxc_ShowAdmArticleConfig()
	{
		global $context, $settings, $modSettings, $boarddir, $boardurl, $scripturl, $user_info, $options, $txt;

		echo '
				<tr>
					<td>
						<div class="windowbg edit_main">
						<div class="pmx_scrolldiv">
						<table class="pmx_table pmx_tbl_overflow">
							<tr>
								<td class="pmxfloattd">
									<input type="hidden" name="id" value="'. $this->cfg['id'] .'" />
									<input type="hidden" name="owner" value="'. $this->cfg['owner'] .'" />
									<input type="hidden" name="contenttype" value="'. $this->cfg['ctype'] .'" />
									<input type="hidden" name="config[settings]" value="" />
									<input type="hidden" name="active" value="'. $this->cfg['active'] .'" />
									<input type="hidden" name="approved" value="'. $this->cfg['approved'] .'" />
									<input type="hidden" name="approvedby" value="'. $this->cfg['approvedby'] .'" />
									<input type="hidden" name="created" value="'. $this->cfg['created'] .'" />
									<input type="hidden" name="updated" value="'. $this->cfg['updated'] .'" />
									<input type="hidden" name="updatedby" value="'. $this->cfg['updatedby'] .'" />
									<input type="hidden" name="check_num_vars[]" value="[config][maxheight], \'\'" />';

		echo '
									<div style="height:37px;">
										<div style="float:left;width:100px; padding-top:1px;">
											<span>&nbsp;'. $txt['pmx_edit_title'] .'</span>
											<a style="float:left;" href="', $scripturl, '?action=helpadmin;help=pmx_edit_titlehelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
										</div>';

		// all titles depend on language
		$curlang = '';
		foreach($context['pmx']['languages'] as $lang => $sel)
		{
			$curlang = !empty($sel) ? $lang : $curlang;
			echo '
											<span id="'. $lang .'" style="white-space:nowrap;'. (!empty($sel) ? '' : ' display:none;') .'">
												<input style="width:60%" type="text" name="config[title]['. $lang .']" value="'. (isset($this->cfg['config']['title'][$lang]) ? htmlspecialchars($this->cfg['config']['title'][$lang], ENT_QUOTES) : '') .'" />
											</span>';
		}

		echo '
											<input id="curlang" type="hidden" value="'. $curlang .'" />
											<div style="clear:both;"></div>
											<img style="float:left;padding-left:18px;" src="'. $boardurl .'/Themes/default/Portal/SysCss/Images/arrow_down.gif" alt="*" title="">
										</div>
										<div style="float:left;width:100px;">
											<span>'. $txt['pmx_edit_title_lang'] .'</span>
										</div>
										<div style="float:left;height:30px;">
											<select style="float:left;width:168px;" size="1" onchange="setTitleLang(this)">';

		foreach($context['pmx']['languages'] as $lang => $sel)
			echo '
												<option value="'. $lang .'"' .(!empty($sel) ? ' selected="selected"' : '') .'>'. $lang .'</option>';

		echo '
											</select>
										<div style="margin-left:180px;padding-top:1px;">
											<input type="hidden" id="titlealign" name="config[title_align]" value="'. $this->cfg['config']['title_align'] .'" />';

		foreach($txt['pmx_edit_title_align_types'] as $key => $val)
			echo '
											<img id="img'. $key .'" src="'. $context['pmx_imageurl'] .'text_align_'. $key .'.gif" alt="*" title="'. $txt['pmx_edit_title_helpalign']. $val .'" style="cursor:pointer;vertical-align:1px;padding-bottom:'. ($val == 'left' ? '1' : '0') .'px;'.($this->cfg['config']['title_align'] == $key ? 'background-color:#e02000;' : '').'" onclick="setAlign(\'\', \''. $key .'\')" /><br />';

		echo '
										</div>
									</div>
									<div class="clear"></div>';

			// Title icons
		$this->cfg['config']['title_icon'] = (empty($this->cfg['config']['title_icon']) || $this->cfg['config']['title_icon'] == 'none.gif') ? 'none.png' : $this->cfg['config']['title_icon'];
		echo '
									<div style="float:left;height:40px;margin-top:-3px;">
										<div style="float:left;width:100px; padding-top:8px;height:26px;">'. $txt['pmx_edit_titleicon'] .'</div>
										<div class="ttliconDiv" onclick="setNewIcon(document.getElementById(\'pWind.icon_sel\'), event)">
											<input id="post_image" type="hidden" name="config[title_icon]" value="'. $this->cfg['config']['title_icon'] .'" />
											<input id="iconDD" value="'. ucfirst(str_replace('.png', '', $this->cfg['config']['title_icon'])) .'" readonly />
											<img id="pWind.icon" class="pwindiconBlk" src="'. $context['pmx_Iconsurl'] . $this->cfg['config']['title_icon'] .'" alt="*" />
											<img class="ddImageBlk" src="'. $context['pmx_imageurl'] .'state_expand.png" alt="*" title="" />
										</div>
										<ul class="ttlicondd Blkedit'. ($modSettings['isMobile'] ? '_mb' : '') .'" id="pWind.icon_sel" onclick="updIcon(this)">';

		foreach($this->title_icons as $file => $name)
			echo '
											<li id="'. $file .'" class="ttlicon'. ($this->cfg['config']['title_icon'] == $file ? ' active' : '') .'">
												<img src="'. $context['pmx_Iconsurl'] . $file .'" alt="*" /><span>'. $name .'</span>
											</li>';

		echo '
										</ul>
										<script>$("li").hover(function(){pmxToggleClass(this, "active")});</script>
									</div>';

		// show article types
		echo '
								</td>
								<td class="pmxfloattd">
									<div>
										<div style="float:left;width:130px;margin-top:2px;">
											<span><a href="', $scripturl, '?action=helpadmin;help=pmx_article_select_help" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
											<span>'. $txt['pmx_article_type'] .'</span>
										</div>';

		$RegBlocks = $context['pmx']['RegBlocks'];
		foreach($RegBlocks as $key =>$val)
			if(!in_array($key, array('html', 'script', 'bbc_script', 'php')))
				unset($RegBlocks[$key]);

		function cmpBDesc($a, $b){return strcasecmp(str_replace(' ', '', $a["description"]), str_replace(' ', '', $b["description"]));}
		uasort($RegBlocks, 'cmpBDesc');

		if(allowPmx('pmx_admin, pmx_create'))
		{
			echo '
										<select style="width:200px;margin-top:1px;" size="1" name="ctype" onchange="ajax_indicator(true);FormFunc(\'edit_change\', \'1\')">';

			foreach($RegBlocks as $type => $articleType)
				echo '
											<option value="'. $type .'"'. ($this->cfg['ctype'] == $type ? ' selected="selected"' : '') .'>'. $articleType['description'] .'</option>';

			echo '
										</select>
									</div>
								</div>';
		}
		else
			echo '
									<input type="hidden" name="ctype" value="'. $this->cfg['ctype'] .'" />
									<input style="width:60%;" value="'. $RegBlocks[$this->cfg['ctype']]['description'] .'" disabled="disabled" />';

		// all exist categories
		$selcats = array_merge(array(PortaMx_getDefaultCategory($txt['pmx_categories_none'])), $this->categories);
		$ordercats = array_merge(array(0), $context['pmx']['catorder']);
		$isWriter = allowPmx('pmx_create, pmx_articles', true);
		$isAdm = allowPmx('pmx_admin');
		echo '
								<div>
									<div style="height:32px;">
										<div style="float:left;width:130px;margin-top:12px;">
											<span><a href="', $scripturl, '?action=helpadmin;help=pmx_articlecat_help" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
											<span>'. $txt['pmx_article_cats'] .'</span>
										</div>
										<div style="margin-top:0px;height:27px;">
											<select id="pmxallcats" style="width:200px;margin-top:12px;" size="1" name="catid">';

		foreach($ordercats as $catorder)
		{
			$cat = PortaMx_getCatByOrder($selcats, $catorder);
			$cfg = pmx_json_decode($cat['config'], true);
			if(!empty($isAdm) || (!empty($isWriter) && empty($cfg['global'])))
			{
				if(isset($_POST['add_new_article']))
					echo '
												<option value="'. $cat['id'] .'"'. ($cat['id'] == $this->cfg['catid'] ? ' selected="selected"' : '') .'>'. str_repeat('&bull;', $cat['level']).' '. $cat['name'] .'</option>';            
				else
					echo '
												<option value="'. $cat['id'] .'"'. ($cat['id'] == $this->cfg['catid'] ? ' selected="selected"' : '') .'>'. str_repeat('&bull;', $cat['level']).' '. $cat['name'] .'</option>';
			}
		}

		echo '
												</select>
												<script>initSelect(document.getElementById("pmxallcats"));</script>
											</div>
										</div>
									</div>';

		// articlename
		echo '
										<div>
											<div style="float:left;width:130px;margin-top:15px;">
												<a href="', $scripturl, '?action=helpadmin;help=pmx_edit_pagenamehelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
												<span>'. $txt['pmx_article_name'] .'</span>
											</div>
											<input id="check.name" style="width:200px;margin-top:15px" onkeyup="check_requestname(this)" onkeypress="check_requestname(this)" type="text" name="name" value="'. $this->cfg['name'] .'" />
											<span id="check.name.error" style="display:none;">'. sprintf($txt['namefielderror'], $txt['pmx_article_name']) .'</span>
										</div>
									</div>
								</td>
							</tr>';

		// the editor area dependent on article type
		echo '
							<tr>
								<td colspan="2" style="padding:4px 4px 10px 4px;">';

		// show the editor
		if($this->cfg['ctype'] == 'html')
		{
			$allow = allowPmx('pmx_admin, pmx_articles, pmx_create');
			$fnd = explode('/', str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']));
			$pmxpath = str_replace('\\', '/', $boarddir);
			foreach($fnd as $key => $val) { $fnd[$key] = $val; $rep[] = ''; }
			$filepath = trim(str_replace($fnd, $rep, $pmxpath), '/') .'/CustomImages';
			if(count($fnd) != count(explode('/', $pmxpath)))
				$filepath = '/'. $filepath;
			$_SESSION['pmx_ckfm'] = array('ALLOW' => $allow, 'FILEPATH' => str_replace('//', '/', $filepath));

			// the editor language (we have en & de)
			if(in_array($txt['lang_dictionary'], array('en', 'de')))
				$edLang = $txt['lang_dictionary'];
			else
				$edLang = 'en';

			echo '
								<div class="cat_bar catbg_grid">
									<h4 class="catbg catbg_grid"><span class="cat_msg_title">'. $txt['pmx_edit_content'] .'</span></h4>
								</div>
								<textarea name="'. $context['pmx']['htmledit']['id'] .'">'. convertSmileysToUser($context['pmx']['htmledit']['content']) .'</textarea>
								<script>
									CKEDITOR.replace("'. $context['pmx']['htmledit']['id'] .'", {
										filebrowserBrowseUrl: "ckeditor/fileman/index.php",
										smiley_path: CKEDITOR.basePath +"../Smileys/'. $user_info['smiley_set'] .'/",
										language: "'. $edLang .'"});
								</script>';
		}

		// show the content area
		elseif($this->cfg['ctype'] == 'bbc_script')
			echo '
								<style type="text/css">
									.sceditor-container iframe{width:99.1% !important;}
									.sceditor-container{max-width:inherit;width:inherit !important; margin-right:-2px;}
									textarea{max-width:99% !important;width:99.2% !important;}
								</style>
								<div class="cat_bar catbg_grid" style="margin-right:1px;">
									<h4 class="catbg catbg_grid"><span class="cat_msg_title">'. $txt['pmx_edit_content'] .'</span></h4>
								</div>
								<input type="hidden" id="smileyset" value="PortaMx" />
								<div id="bbcBox_message"></div>
								<div id="smileyBox_message"></div>
								<div style="padding-right:3px;margin-top:-10px;">', template_control_richedit($context['pmx']['editorID'], 'smileyBox_message', 'bbcBox_message'), '</div>';

		elseif($this->cfg['ctype'] == 'php')
		{
			addInlineJavascript(str_replace("\n", "\n\t", PortaMx_compressJS('
			function pack(chrstr) {
				var hexstr = "";
				for(var i = 0; i < chrstr.length; i++)
				{
					var c = chrstr.charCodeAt(i);
					var h = "00" + c.toString(16);
					hexstr += h.substr(h.length-2);
				}
				return hexstr;
			}
			function php_syntax(elmid)
			{
				document.getElementById("check_" + elmid).innerHTML = "<img onclick=\"Hide_SyntaxCheck(this.parentNode)\" style=\"padding-left:10px;cursor:pointer;\" alt=\"close\" src=\"'. $context['pmx_imageurl'] .'cross.png\" class=\"pmxright\" />";
				document.getElementById("check_" + elmid).className = "info_frame";
				var postData = {};
				postData["value"] = pack(document.getElementById(elmid).value);
				var result = pmxXMLpost(pmx_scripturl +"?jscook&mode=syntax&xml", postData);
				result = result.replace(/@elm@/, elmid);
				result = result.replace(/<br \/>/gi, "");
				temp = result.replace(/<b>/gi, "");
				temp = temp.replace(/<\/b>/gi, "");
				var patt = /(on\s+line\D+)(\d+)/;
				res = patt.exec(temp);
				LineNo = "";
				if(res)
					LineNo = res[2];
				if(result.indexOf("in <b>") != -1)
				{
					result = result.substring(result.indexOf("<b>Parse error</b>:  "), result.indexOf("in <b>"));
					result = result.replace(/<b>/gi, "");
					result = result + " on line " + LineNo;
				}
				document.getElementById("check_" + elmid).innerHTML = document.getElementById("check_" + elmid).innerHTML + result;
				Show_help("check_" + elmid);
				php_showerrline(elmid, LineNo);
			}
			function php_showerrline(elmid, errLine)
			{
				if(errLine != "" && errLine != "0" && !isNaN(errLine))
				{
					errLine = parseInt(errLine) -1;
					var lines = document.getElementById(elmid).value.split("\n");
					var count = 0;
					for(var i = 0; i < errLine -1; i++)
						count += lines[i].length +1;

					if(document.getElementById(elmid).setSelectionRange)
					{
						document.getElementById(elmid).focus();
						document.getElementById(elmid).setSelectionRange(count, count+lines[i].length);
					}
					else if(document.getElementById(elmid).createTextRange)
					{
						range=document.getElementById(elmid).createTextRange();
						range.collapse(true);
						range.moveStart("character", count);
						range.moveEnd("character", count+lines[i].length);
						range.select();
					}
				}
			}')));

			$options['collapse_phpinit'] = empty($context['pmx']['phpInit']['havecont']);

			echo '
								<div class="cat_bar catbg_grid">
									<h4 class="catbg catbg_grid">
										<span style="float:right;display:block;margin-top:-2px;">
											<img onclick="php_syntax(\''. $context['pmx']['phpShow']['id'] .'\',\''. str_replace('/', '|', str_replace($boardurl, '', $context['pmx_imageurl'])) .'\')" style="padding:3px 5px 3px 10px;cursor:pointer;" title="'. $txt['pmx_check_phpsyntax'] .'" alt="Syntax check" src="'. $context['pmx_imageurl'] .'syntaxcheck.png" class="pmxright" />
										</span>
										<span class="cat_msg_title">'. $txt['pmx_edit_content'] .'
										<span id="upshrinkPHPinitCont"'. (empty($options['collapse_phpinit']) ? '' : ' style="display:none;"') .'>'. $txt['pmx_edit_content_show'] .'</span></span>
									</h4>
								</div>
								<div id="check_'. $context['pmx']['phpShow']['id'] .'" class="info_frame" style="line-height:1.4em;margin:1px 0;">
										<img onclick="Hide_SyntaxCheck(this.parentNode)" style="padding-left:10px;cursor:pointer;" alt="close" src="'. $context['pmx_imageurl'] .'cross.png" class="pmxright" />
								</div>

								<textarea name="'. $context['pmx']['phpShow']['id'] .'" id="'. $context['pmx']['phpShow']['id'] .'" style="display:block;resize:vertical;width:'. $context['pmx']['phpShow']['width'] .';height:'. $context['pmx']['phpShow']['height'] .';">'. $context['pmx']['phpShow']['value'] .'</textarea>

								<div class="plainbox info_text" style="margin-top:5px;margin-right:0px;padding:5px 0 7px 0;display:block;">
									<div class="normaltext" style="margin:0 10px;">
									'.(empty($context['pmx']['phpInit']['havecont']) ? '<span style="margin-top:0px;margin-right:-4px;" id="upshrinkPHPshowImg" class="floatleft '.
											(empty($options['collapse_visual']) ? 'toggle_up" align="bottom"' : 'toggle_down" align="bottom"') .' title="'.
											(empty($options['collapse_visual']) ? $txt['pmx_collapse'] : $txt['pmx_expand']) . $txt['pmx_php_partblock'] .'">
										</span>' : '') .'
										<span style="padding-left:10px;vertical-align:-1px;">'. $txt['pmx_php_partblock_note'] .'
											<img class="info_toggle" onclick=\'Toggle_help("pmxPHPH01")\' src="'. $context['pmx_imageurl'] .'information.png" alt="*" title="'. $txt['pmx_information_icon'] .'" style="vertical-align: -3px;" />
										</span>
									</div>
									<div id="pmxPHPH01" style="display:none; margin:4px 10px 0;">'. $txt['pmx_php_partblock_help'] .'</div>
								</div>

								<div id="upshrinkPHPshowCont"' .(empty($options['collapse_phpinit']) ? '' : ' style="margin-top:5px;display:none;"') .'>
									<div class="cat_bar catbg_grid">
										<h4 class="catbg catbg_grid">
											<span style="float:right;display:block;margin-top:-2px;">
												<img onclick="php_syntax(\''. $context['pmx']['phpInit']['id'] .'\')" style="padding:3px 5px 3px 10px;cursor:pointer;" title="'. $txt['pmx_check_phpsyntax'] .'" alt="Syntax check" src="'. $context['pmx_imageurl'] .'syntaxcheck.png" class="pmxright" />
											</span>
											<span class="cat_msg_title">'. $txt['pmx_edit_content'] . $txt['pmx_edit_content_init'] .'</span>
										</h4>
									</div>
									<div id="check_'. $context['pmx']['phpInit']['id'] .'" class="info_frame" style="line-height:1.4em;margin:1px 0;">
										<img onclick="Hide_SyntaxCheck(this.parentNode)" style="padding-left:10px;cursor:pointer;" alt="close" src="'. $context['pmx_imageurl'] .'cross.png" class="pmxright" />
									</div>

									<textarea name="'. $context['pmx']['phpInit']['id'] .'" id="'. $context['pmx']['phpInit']['id'] .'" style="display:block;width:'. $context['pmx']['phpInit']['width'] .';height:'. $context['pmx']['phpInit']['height'] .';">'. $context['pmx']['phpInit']['value'] .'</textarea>
								</div>';

			if(empty($context['pmx']['phpInit']['havecont']))
				addInlineJavascript("\t". str_replace("\n", "\n\t", PortaMx_compressJS('
						var upshrinkPHPshow = new pmxc_Toggle({
							bToggleEnabled: true,
							bCurrentlyCollapsed: '. (empty($options['collapse_phpinit']) ? 'false' : 'true') .',
							aSwappableContainers: [
								\'upshrinkPHPshowCont\',
								\'upshrinkPHPinitCont\'
							],
							aSwapImages: [
								{
									sId: \'upshrinkPHPshowImg\',
									altCollapsed: '. JavaScriptEscape($txt['pmx_expand'] . $txt['pmx_php_partblock']) .',
									altExpanded: '. JavaScriptEscape($txt['pmx_collapse'] . $txt['pmx_php_partblock']) .'
								}
							],
							oCookieOptions: {
									bUseCookie: false
								}
						});')), true);
		}
		else
			echo '
									<div class="cat_bar catbg_grid">
										<h4 class="catbg catbg_grid"><span class="cat_msg_title">'. $txt['pmx_edit_content'] .'</span></h4>
									</div>
									<textarea name="'. $context['pmx']['script']['id'] .'" id="'. $context['pmx']['script']['id'] .'" style="display:block;width:'. $context['pmx']['script']['width'] .';height:'. $context['pmx']['script']['height'] .';">'. $context['pmx']['script']['value'] .'</textarea>';

		echo '
								</td>
							</tr>
							<tr>
								<td class="pmxfloattd">
									<div style="min-height:192px;">
										<input type="hidden" name="config[settings]" value="" />';

			// show the settings area
			echo '
										<div class="cat_bar catbg_grid grid_padd">
											<h4 class="catbg catbg_grid"><span class="cat_msg_title">'. $txt['pmx_articles_types'][$this->cfg['ctype']] .' '. $txt['pmx_article_settings_title'] .'</span></h4>
										</div>
										<div>';

		if($this->cfg['ctype'] == 'html')
			echo '
											<div class="adm_check">
												<span class="adm_w80">&nbsp;'. $txt['pmx_html_teaser'] .'
													<a href="', $scripturl, '?action=helpadmin;help=pmx_html_teasehelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
												</span>
												<input type="hidden" name="config[settings][teaser]" value="0" />
												<div><input style="margin-left:-1px;" class="input_check" type="checkbox" name="config[settings][teaser]" value="1"' .(isset($this->cfg['config']['settings']['teaser']) && !empty($this->cfg['config']['settings']['teaser']) ? ' checked="checked"' : ''). ' /></div>
											</div>';

		elseif($this->cfg['ctype'] != 'php')
			echo '
											<div class="adm_check">
												<span class="adm_w80" style="margin-top:6px;">&nbsp;'. sprintf($txt['pmx_article_teaser'], $txt['pmx_teasemode'][intval(!empty($context['pmx']['settings']['teasermode']))]) .'
													<a href="', $scripturl, '?action=helpadmin;help=pmx_adm_teasehelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
												</span>
												<div><input style="margin-left:-1px;" type="text" size="5" name="config[settings][teaser]" value="'. (isset($this->cfg['config']['settings']['teaser']) ? $this->cfg['config']['settings']['teaser'] : '') .'" /></div>
											</div>';

		echo '
											<div class="adm_check">
												<span class="adm_w80">'. $txt['pmx_content_print'] .'</span>
												<input type="hidden" name="config[settings][printing]" value="0" />
												<div><input class="input_check" type="checkbox" name="config[settings][printing]" value="1"' .(!empty($this->cfg['config']['settings']['printing']) ? ' checked="checked"' : ''). ' /></div>
											</div>

											<div class="adm_check">
												<span class="adm_w80">&nbsp;'. $txt['pmx_article_footer'] .'
													<a href="', $scripturl, '?action=helpadmin;help=pmx_article_footerhelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
												</span>
												<input type="hidden" name="config[settings][showfooter]" value="0" />
												<div><input class="input_check" type="checkbox" name="config[settings][showfooter]" value="1"' .(isset($this->cfg['config']['settings']['showfooter']) && !empty($this->cfg['config']['settings']['showfooter']) ? ' checked="checked"' : ''). ' /></div>
											</div>
											<input type="hidden" name="config[show_sitemap]" value="0" />';

			if($this->cfg['ctype'] != 'php')
				echo '
											<div class="adm_check">
												<span class="adm_w80">&nbsp;'. $txt['pmx_articles_disableHSimage'] .'
													<a href="', $scripturl, '?action=helpadmin;help=pmx_disable_lightbox_help" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
												</span>
												<input type="hidden" name="config[settings][disableHSimg]" value="0" />
												<div><input class="input_check" type="checkbox" name="config[settings][disableHSimg]" value="1"' .(isset($this->cfg['config']['settings']['disableHSimg']) && !empty($this->cfg['config']['settings']['disableHSimg']) ? ' checked="checked"' : '').(!empty($context['pmx']['settings']['disableHS']) ? ' disabled="disabled"' : '') .' /></div>
											</div>';

		if($this->cfg['ctype'] == 'bbc_script')
			echo '
											<div class="adm_check">
												<span class="adm_w80">&nbsp;'. $txt['pmx_boponews_disableYoutube'] .'
													<a href="', $scripturl, '?action=helpadmin;help=pmx_boponews_disableYoutubehelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
												</span>
												<input type="hidden" name="config[disableYoutube]" value="0" />
												<div><input class="input_check" type="checkbox" name="config[disableYoutube]" value="1"' .(isset($this->cfg['config']['disableYoutube']) && !empty($this->cfg['config']['disableYoutube']) ? ' checked="checked"' : '') .' /></div>
											</div>';

			echo '
										</div>
									</div>';

		// the group access
		echo '
									<div class="cat_bar catbg_grid grid_padd grid_top">
										<h4 class="catbg catbg_grid">
											<a href="', $scripturl, '?action=helpadmin;help=pmx_article_groupshelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
											<span class="cat_msg_title">'. $txt['pmx_article_groups'] .'</span>
										</h4>
									</div>
									<select id="pmxgroups" onchange="changed(\'pmxgroups\');" style="width:83%;" name="acsgrp[]" multiple="multiple" size="5">';

		if(!empty($this->cfg['acsgrp']))
			list($grpacs, $denyacs) = Pmx_StrToArray($this->cfg['acsgrp'], ',', '=');
		else
			$grpacs = $denyacs = array();

		foreach($this->pmx_groups as $grp)
			echo '
										<option value="'. $grp['id'] .'='. intval(!in_array($grp['id'], $denyacs)) .'"'. (in_array($grp['id'], $grpacs) ? ' selected="selected"' : '') .'>'. (in_array($grp['id'], $denyacs) ? '^' : '') . $grp['name'] .'</option>';

		echo '
									</select>
									<script>
										var pmxgroups = new MultiSelect("pmxgroups");
									</script>';

		// article moderate
		if(!isset($this->cfg['config']['can_moderate']))
			$this->cfg['config']['can_moderate'] = 1;

		if(allowPmx('pmx_articles, pmx_create', true))
			echo '
									<input type="hidden" name="config[can_moderate]" value="'. $this->cfg['config']['can_moderate'] .'" />';

		if(allowPmx('pmx_admin, pmx_articles, pmx_create'))
			echo '
									<div class="cat_bar catbg_grid grid_padd grid_top">
										<h4 class="catbg catbg_grid">
											<span class="cat_msg_title">'. $txt['pmx_article_moderate_title'] .'</span>
										</h4>
									</div>';

		if(allowPmx('pmx_admin'))
				echo '
									<div class="adm_check">
										<input type="hidden" name="config[can_moderate]" value="0" />
										<span class="adm_w80">&nbsp;'. $txt['pmx_article_moderate'] .'
											<a href="', $scripturl, '?action=helpadmin;help=pmx_article_moderatehelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
										</span>
										<div><input class="input_check" type="checkbox" name="config[can_moderate]" value="1"' .(!empty($this->cfg['config']['can_moderate']) ? ' checked="checked"' : ''). ' /></div>
									</div>';

		echo '
									<input type="hidden" name="config[check_ecl]" value="0" />
									<input type="hidden" name="config[check_eclbots]" value="0" />';

		if(allowPmx('pmx_admin, pmx_articles, pmx_create') && !empty($modSettings['ecl_enabled']))
			echo '
									<div class="adm_check">
										<span class="adm_w80">&nbsp;'. $txt['pmx_check_artelcmode'] .'
											<a href="', $scripturl, '?action=helpadmin;help=pmx_art_eclcheckhelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
										</span>
										<div><input class="input_check" type="checkbox" name="config[check_ecl]" value="1"' .(!empty($this->cfg['config']['check_ecl']) ? ' checked="checked"' : ''). ' /></div>
									</div>

									<div class="adm_check">
										<span class="adm_w80">&nbsp;'. $txt['pmx_check_artelcbots'] .'
											<a href="', $scripturl, '?action=helpadmin;help=pmx_art_eclcheckbotshelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
										</span>
										<div><input id="eclextendinp" class="input_check" type="checkbox" name="config[check_eclbots]" value="1"' .(!empty($this->cfg['config']['check_eclbots']) ? ' checked="checked"' : ''). ' /></div>
									</div>';

		echo '
								</td>';

		// the visual options
		echo '
								<td id="set_col" class="pmxfloattd">
									<div class="cat_bar catbg_grid grid_padd">
										<h4 class="catbg catbg_grid"><span class="cat_msg_title">'. $txt['pmx_edit_visuals'] .'</span></h4>
									</div>
									<div style="float:left;height:30px;width:180px;">'. $txt['pmx_edit_cancollapse'] .'</div>
									<input type="hidden" name="config[collapse]" value="0" />
									<input class="input_check" id="collapse" type="checkbox" name="config[collapse]" value="1"'. ($this->cfg['config']['visuals']['header'] == 'none' ? ' disabled="disabled"' : ($this->cfg['config']['collapse'] == 1 ? ' checked="checked"' : '')) .' />
									<div style="clear:both;" /></div>
									<div style="float:left;width:180px;margin-top:1px;">'. $txt['pmx_edit_collapse_state'] .'</div>
									<select style="width:45%;" size="1" name="config[collapse_state]">';

		foreach($txt['pmx_collapse_mode'] as $key => $text)
			echo '
										<option value="'. $key .'"'. (isset($this->cfg['config']['collapse_state']) && $this->cfg['config']['collapse_state'] == $key ? ' selected="selected"' : '') .'>'. $text .'</option>';
		echo '
									</select>
									<div style="float:left; height:30px;margin-top:7px;width:180px;">'. $txt['pmx_edit_overflow'] .'</div>
									<select style="width:45%;margin-top:6px;" size="1" id="mxhgt" name="config[overflow]" onchange="checkMaxHeight(this);">';

		foreach($txt['pmx_overflow_actions'] as $key => $text)
			echo '
										<option value="'. $key .'"'. (isset($this->cfg['config']['overflow']) && $this->cfg['config']['overflow'] == $key ? ' selected="selected"' : '') .'>'. $text .'</option>';
		echo '
									</select>
									<div style="float:left; min-height:32px; width:99%;">
										<div style="float:left; min-height:30px; width:180px;">'. $txt['pmx_edit_height'] .'</div>
										<div style="float:left; max-width:45%">
											<input onkeyup="check_numeric(this)" id="maxheight" type="text" style="width:25%" name="config[maxheight]" value="'. (isset($this->cfg['config']['maxheight']) ? $this->cfg['config']['maxheight'] : '') .'"'. (!isset($this->cfg['config']['overflow']) || empty($this->cfg['config']['overflow']) ? ' disabled="disabled"' : '') .' /><span class="smalltext">'. $txt['pmx_pixel'] .'</span><span style="display:inline-block; width:3px;"></span>
											<select id="maxheight_sel" style="float:right;width:52%;margin-right:-1%;" size="1" name="config[height]">';

		foreach($txt['pmx_edit_height_mode'] as $key => $text)
			echo '
												<option value="'. $key .'"'. (isset($this->cfg['config']['height']) && $this->cfg['config']['height'] == $key ? ' selected="selected"' : '') .'>'. $text .'</option>';
		echo '
											</select>
										</div>
									</div>
									<script>
										checkMaxHeight(document.getElementById("mxhgt"));
									</script>

									<div style="height:30px;width:180px;">'. $txt['pmx_edit_innerpad'] .'</div>
									<input style="float:left;margin-left:180px;" onkeyup="check_numeric(this, \',\')" type="text" size="4" name="config[innerpad]" value="'. (isset($this->cfg['config']['innerpad']) ? $this->cfg['config']['innerpad'] : '4') .'" /><span class="smalltext" style="padding-left:4px">'. $txt['pmx_pixel'] .' (xy/y,x)</span>
									<div style="clear:both;height:12px;"></div>';

		// CSS class settings
			echo '
									<div class="cat_bar catbg_grid grid_padd">
										<h4 class="catbg catbg_grid grid_botpad">
											<div style="float:left; width:177px;">
												<a href="', $scripturl, '?action=helpadmin;help=pmx_used_stylehelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
												<span class="cat_msg_title">'. $txt['pmx_edit_usedclass_type'] .'</span>
											</div>
											<span class="cat_msg_left">'. $txt['pmx_edit_usedclass_style'] .'</span>
										</h4>
									</div>
									<div style="margin:0px 2px;">';

		// write out the classes
		foreach($this->usedClass as $ucltyp => $ucldata)
		{
			echo '
										<div style="float:left; width:180px; height:30px; padding-top:2px;">'. $ucltyp .'</div>
										<select'. ($ucltyp == 'frame' || $ucltyp == 'postframe' ? ' id="pmx_'. $ucltyp .'" ' : ' ') .'style="width:45%;margin-bottom:8px" name="config[visuals]['. $ucltyp .']" onchange="checkCollapse(this)">';

			foreach($ucldata as $cname => $class)
					echo '
											<option value="'. $class .'"'. (!empty($this->cfg['config']['visuals'][$ucltyp]) ? ($this->cfg['config']['visuals'][$ucltyp] == $class ? ' selected="selected"' : '') : (substr($cname,0,1) == '+' ? ' selected="selected"' : '')) .'>'. substr($cname, 1) .'</option>';
			echo '
										</select>
										<br style="clear:both;" />';

		}

		echo '
									</div>
									<div class="cat_bar catbg_grid grid_padd">
										<h4 class="catbg catbg_grid"><span class="cat_msg_title">
											<a href="', $scripturl, '?action=helpadmin;help=pmx_custom_css_filehelp" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>
											<span>'. $txt['pmx_edit_canhavecssfile'] .'</span>
										</h4>
									</div>
									<div style="float:left; width:180px; height:30px; padding-top:2px;">'. $txt['pmx_edit_cssfilename'] .'</div>
									<select id="sel.css.file" style="width:45%;margin-bottom:2px;" name="config[cssfile]" onchange="pmxChangeCSS(this)">
										<option value="">'. $txt['pmx_default_none'] .'</option>';

		// custon css files exist ?
		if(!empty($this->custom_css))
		{
			// write out custom mpt/css definitions
			foreach($this->custom_css as $custcss)
			{
				if(is_array($custcss))
					echo '
											<option value="'. $custcss['file'] .'"'. ($this->cfg['config']['cssfile'] == $custcss['file'] ? ' selected="selected"' : '') .'>'. $custcss['file'] .'</option>';
			}
			echo '
										</select>
									</div>
									<div style="clear:both; height:2px;"></div>';

			// write out all class definitions (hidden)
			foreach($this->custom_css as $custcss)
			{
				if(is_array($custcss))
				{
					echo '
									<div id="'. $custcss['file'] .'" style="display:none;">';

					foreach($custcss['class'] as $key => $val)
					{
						if(in_array($key, array_keys($this->usedClass)))
							echo '
										<div style="float:left; width:180px; padding:0 2px;">'. $key .'</div>'. (empty($val) ? sprintf($txt['pmx_edit_nocss_class'], $settings['theme_id']) : $val) .'<br />';
					}

					echo '
									</div>';
				}
			}
			echo '
									<script>
										var elm = document.getElementById("sel.css.file");
										var fname = elm.options[elm.selectedIndex].value;
										if(document.getElementById(fname))
											document.getElementById(fname).style.display = "";
										function pmxChangeCSS(elm)
										{
											for(i=0; i<elm.length; i++)
											{
												if(document.getElementById(elm.options[i].value))
													document.getElementById(elm.options[i].value).style.display = "none";
											}
											var fname = elm.options[elm.selectedIndex].value;
											if(document.getElementById(fname))
												document.getElementById(fname).style.display = "";
										}
									</script>';
		}
		else
			echo '
									</select>
									<div style="clear:both; height:6px;"></div>';

									echo '
								</td>
							</tr>
							<tr>
								<td colspan="2" style="text-align:center;padding:4px;"><hr class="pmx_hr" />
									<input class="button_submit" type="button" value="'. $txt['pmx_save_exit'] .'" onclick="FormFunc(\'save_edit\', \'1\')" />
									<input class="button_submit" type="button" style="margin-right:10px;" value="'. $txt['pmx_save_cont'] .'" onclick="FormFunc(\'save_edit_continue\', \'1\')" />
									<input class="button_submit" type="button" style="margin-right:10px;" value="'. $txt['pmx_cancel'] .'" onclick="FormFunc(\'cancel_edit\', \'1\')" />
								</td>
							</tr>
						</table>
						</div>
						</div>
					</td>
				</tr>';
	}
}
?>