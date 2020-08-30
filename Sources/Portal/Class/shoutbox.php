<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file shoutbox.php
 * Systemblock shoutbox
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* @class pmxc_shoutbox
* Systemblock shoutbox
* @see shoutbox.php
*/
class pmxc_shoutbox extends PortaMxC_SystemBlock
{
	var $smileys;			///< all smileys
	var $bb_code;			///< all bb codes
	var $bb_colors;		///< all bbc colors
	var $memdata;			///< shout memberdata
	var $shouts;			///< shout data
	var $legalcodes;	///< all legal bbc codes
	var $canShout;

	/**
	* checkCacheStatus.
	* If the cache enabled, the cache trigger will be checked.
	*/
	function pmxc_checkCacheStatus()
	{
		global $pmxCacheFunc;

		$result = true;
		if($this->cfg['cache'] > 0 && !empty($this->cache_trigger))
		{
			if(($data = $pmxCacheFunc['get']($this->cache_key, $this->cache_mode)) !== null)
			{
				$res = eval($this->cache_trigger);
				if($res == 'clr')
					$pmxCacheFunc['drop']($this->cache_key, $this->cache_mode);

				unset($data);
				$result = ($res === null);
			}
		}
		return $result;
	}

	/**
	* InitContent.
	* Checked is a shout received ($_POST).
	*/
	function pmxc_InitContent()
	{
		global $user_info, $pmxcFunc, $pmxCacheFunc;

		$this->pmxc_ShoutSetup();

		// shout send?
		if(isset($_POST['pmx_shout']) && !empty($_POST['pmx_shout']) && $_POST['shoutbox_id'] == $this->cfg['id'])
		{
			if(!empty($this->canShout))
			{
				checkSession('post');

				$shoutcmd = PortaMx_makeSafe($_POST['pmx_shout']);
				$update = false;

				// get the shouts
				$shouts = pmx_json_decode($this->cfg['content'], true);
				$shouts = is_array($shouts) ? $shouts : array();

				// delete a shout?
				if($shoutcmd == 'delete')
				{
					$id = PortaMx_makeSafe($_POST['shoutid']);
					if(isset($shouts[$id]))
					{
						unset($shouts[$id]);
						if(!empty($shouts))
						{
							$new = array();
							foreach($shouts as $data)
								$new[] = $data;
							$shouts = $new;
						}
						$this->cfg['content'] = json_encode($shouts, true);
						unset($new);
						$update = true;
					}
				}

				// update a shout?
				if($shoutcmd == 'update')
				{
					$id = PortaMx_makeSafe($_POST['shoutid']);
					if(isset($shouts[$id]))
					{
						// clean the input stream
						$post = PortaMx_makeSafeContent(str_replace(array("\n", "\t"), array('[br]', ' '), $_POST['post']));
						$post = $this->ShortenBBCpost($post, intval($this->cfg['config']['settings']['maxlen']));
						if($this->BBCtoHTML($post) != '')
						{
							// convert html to char
							$post = $this->HTMLtoChar($post);
							$shouts[$id]['post'] = $this->ChartoHTML($post, true);
							$this->cfg['content'] = json_encode($shouts, true);
							$update = true;
						}
					}
				}

				// save a new shout ?
				if($shoutcmd == 'save')
				{
					// clean the input stream
					$post = PortaMx_makeSafeContent(str_replace(array("\n", "\t"), array('[br]', ' '), $_POST['post']));
					$post = $this->ShortenBBCpost($post, intval($this->cfg['config']['settings']['maxlen']));
					if($this->BBCtoHTML($post) != '')
					{
						// get the shouts
						$shout = array(
							'uid' => $user_info['id'],
							'ip' => $user_info['ip'],
							'time' => forum_time(false),
							'post' => $this->ChartoHTML($post, true),
						);

						array_unshift($shouts, $shout);

						// max shouts reached?
						if(isset($this->cfg['config']['settings']['maxshouts']) && count($shouts) > $this->cfg['config']['settings']['maxshouts'])
						{
							array_splice($shouts, $this->cfg['config']['settings']['maxshouts']);

							// resort
							$new = array();
							foreach($shouts as $data)
								$new[] = $data;
							$shouts = $new;
							unset($new);
						}
						$this->cfg['content'] = json_encode($shouts, true);
						$update = true;
					}
				}

				// need to save?
				if(!empty($update))
				{
					$pmxcFunc['db_query']('', '
							UPDATE {db_prefix}portal_blocks
							SET content = {string:content}
							WHERE id = {int:id}',
						array(
							'id' => $this->cfg['id'],
							'content' => $this->cfg['content'],
						)
					);
				}

				// cleanup
				unset($shouts);

				if($this->cfg['cache'] > 0)
					$pmxCacheFunc['drop']($this->cache_key, $this->cache_mode);
			}
		}

		if($this->visible)
		{
			// get the shouts
			$this->shouts = pmx_json_decode($this->cfg['content'], true);
			$this->shouts = is_array($this->shouts) ? $this->shouts : array();

			// get member data
			if($this->cfg['cache'] > 0)
			{
				if(($this->memdata = $pmxCacheFunc['get']($this->cache_key, $this->cache_mode)) === null)
				{
					$this->get_memberdata();
					$pmxCacheFunc['put']($this->cache_key, $this->memdata, $this->cache_time, $this->cache_mode);
				}
			}
			else
				$this->get_memberdata();
		}

		// call the show content on Post
		if(isset($_POST['pmx_shout']) && !empty($_POST['pmx_shout']) && $_POST['shoutbox_id'] == $this->cfg['id'])
		{
			unset($_POST);
			$_POST['reload'] = true;
			$this->pmxc_ShowContent();
		}

		// return the visibility flag (true/false)
		return $this->visible;
	}

	/**
	* Get the members name and onlinecolor
	*/
	function get_memberdata()
	{
		global $pmxcFunc, $modSettings, $txt;

		$this->memdata = array();
		if(!empty($this->shouts))
		{
			// get all member id's
			foreach($this->shouts as $data)
				$members[] = intval($data['uid']);

			// get member name and online color
			$request = $pmxcFunc['db_query']('', '
				SELECT mem.id_member, CASE WHEN mem.real_name = {string:empty} THEN mem.member_name ELSE mem.real_name END AS name, mg.online_color AS color
				FROM {db_prefix}members AS mem
				LEFT JOIN {db_prefix}membergroups AS mg ON ('. (!empty($modSettings['permission_enable_postgroups']) ? '(mg.id_group = 0 AND mg.id_group = mem.id_post_group OR mg.id_group > 0 AND mg.id_group = mem.id_group)' : 'mg.id_group = mem.id_group') .' OR FIND_IN_SET(mg.id_group, mem.additional_groups) != 0)
				WHERE mem.id_member IN ({array_int:members})
				GROUP BY mem.id_member, mg.online_color',
				array(
					'members' => array_unique($members),
					'empty' => '',
				)
			);

			// save member data
			while($row = $pmxcFunc['db_fetch_assoc']($request))
				$this->memdata[$row['id_member']] = array('name' => $row['name'], 'color' => $row['color']);
			$pmxcFunc['db_free_result']($request);

			// add Guest shout
			$this->memdata[0] = array('name' => $txt['guest_title'], 'color' => '');
		}
	}

	/**
	* Shout setup variables
	*/
	function pmxc_ShoutSetup()
	{
		global $context, $modSettings, $user_info, $txt;

		// check if member can shout
		if(isset($this->cfg['config']['settings']['shout_acs']))
			$this->canShout = allowPmxGroup(implode(',', $this->cfg['config']['settings']['shout_acs']));
		else
			$this->canShout = false;

		// disable shout if a user banned
		foreach(array('cannot_access', 'cannot_login', 'cannot_post') as $bannmode)
			$this->canShout = (isset($_SESSION['ban'][$bannmode]) ? false : $this->canShout);

		// we CAN shout ...
		if($this->canShout)
		{
			$this->canShout = !show_gdpr_agreement();
			if(empty($modSettings['pmxShoutBoxLoaded']))
			{
				addInlineJavascript('
	var pmx_shoutbox_confirm = "'. $txt['pmx_shoutbox_shoutconfirm'] .'";
	var pmx_shoutbox_send_title = "'. $txt['pmx_shoutbox_send_title'] .'";
	var pmx_shoutbox_button = "'. $txt['pmx_shoutbox_button'] .'";
	var pmx_shoutbox_button_title = "'. $txt['pmx_shoutbox_button_title'] .'";
	var pmx_shoutbox_button_open = "'. $txt['pmx_shoutbox_button_open'] .'";
	var pmx_shoutbox_admimg = new Array("'. $context['pmx_imageurl'] .'shout_admon.gif", "'. $context['pmx_imageurl'] .'shout_admoff.gif");');

				loadJavascriptFile(PortaMx_loadCompressed('PortalShouts.js'), array('external' => true));
				$modSettings['pmxShoutBoxLoaded'] = true;
			}
		}

		// setup bb codes
		$this->bb_code = array(
			array(
				'code' => 'b',
				'image' => $context['pmx_imageurl'] . 'shoutbbc_b.gif',
				'title' => $txt['pmx_shoutbbc_b'],
				),
			array(
				'code' => 'i',
				'image' => $context['pmx_imageurl'] . 'shoutbbc_i.gif',
				'title' => $txt['pmx_shoutbbc_i'],
				),
			array(
				'code' => 'u',
				'image' => $context['pmx_imageurl'] . 'shoutbbc_u.gif',
				'title' => $txt['pmx_shoutbbc_u'],
				),
			array(
				'code' => 'center',
				'image' => $context['pmx_imageurl'] . 'shoutbbc_center.gif',
				'title' => $txt['pmx_shoutbbc_center'],
				),
			array(
				'code' => 'hr',
				'image' => $context['pmx_imageurl'] . 'shoutbbc_hr.gif',
				'title' => $txt['pmx_shoutbbc_hr'],
				),
			array(
				'code' => 'sub',
				'image' => $context['pmx_imageurl'] . 'shoutbbc_sub.gif',
				'title' => $txt['pmx_shoutbbc_sub'],
				),
			array(
				'code' => 'sup',
				'image' => $context['pmx_imageurl'] . 'shoutbbc_sup.gif',
				'title' => $txt['pmx_shoutbbc_sup'],
				),
		);

		foreach($this->bb_code as $data)
			$this->legalcodes[] = $data['code'];

		// setup bbc colors codes
		$this->bb_colors = array(
			$txt['pmx_shoutbbc_changecolor'] => $txt['pmx_shoutbbc_changecolor'],
			$txt['pmx_shoutbbc_colorBlack'] => '#000000',
			$txt['pmx_shoutbbc_colorRed'] => '#ff0000',
			$txt['pmx_shoutbbc_colorYellow'] => '#ffff00',
			$txt['pmx_shoutbbc_colorPink'] => '#ff00ff',
			$txt['pmx_shoutbbc_colorGreen'] => '#008000',
			$txt['pmx_shoutbbc_colorOrange'] => '#FFA500',
			$txt['pmx_shoutbbc_colorPurple'] => '#800080',
			$txt['pmx_shoutbbc_colorBlue'] => '#0000ff',
			$txt['pmx_shoutbbc_colorBeige'] => '#F5F5DC',
			$txt['pmx_shoutbbc_colorBrown'] => '#A52A2A',
			$txt['pmx_shoutbbc_colorTeal'] => '#008080',
			$txt['pmx_shoutbbc_colorNavy'] => '#000080',
			$txt['pmx_shoutbbc_colorMaroon'] => '#800000',
			$txt['pmx_shoutbbc_colorLimeGreen'] => '#00ff00',
			$txt['pmx_shoutbbc_colorWhite'] => '#ffffff',
		);

		// setup the  smileys
		$codes = array('(:1)', '(:2)', '(:3)', '(:4)', '(:5)', '(:6)', '(:7)', '(:8)', '(:9)', '(:0)', '(;1)', '(;2)', '(;3)', '(;4)', '(;5)', '(;6)', '(;7)', '(;8)', '(;9)', '(;0)');
		$files = array('smiley', 'wink', 'cheesy', 'grin', 'angry', 'sad', 'shocked', 'cool', 'huh', 'rolleyes', 'tongue', 'embarrassed', 'lipsrsealed', 'undecided', 'kiss', 'cry', 'evil', 'azn',  'afro', 'laugh');
		$smPath = $modSettings['smileys_url'] . '/'. $user_info['smiley_set'] .'/';
		foreach($codes as $i => $code)
			$this->smileys[] = array(
				'code' => $code,
				'image' => $smPath. $files[$i] .'.gif',
				'title' => ucfirst($files[$i]),
			);
	}

	/**
	* ShowContent
	*/
	function pmxc_ShowContent()
	{
		global $context, $scripturl, $user_info, $txt;

		$context['pmx']['shout_edit'] = -1;

		// smiley && bb codes popup
		$innerPad = Pmx_getInnerPad($this->cfg['config']['innerpad']);
		$bodyclass = ($this->cfg['config']['visuals']['body'] == 'windowbg' ? 'windowbg2 ' : 'windowbg ');

		echo '
			<div id="bbcodes'. $this->cfg['id'] .'" style="position:absolute;z-index:9999;width:340px;height:110px;display:none">
				<div>
					<div class="'. $bodyclass .' roundframe shoutbox_round '. str_replace('pmxborder', '', $this->cfg['config']['visuals']['frame']) .'" style="margin:auto;text-align:center;border-radius:6px;border-width:1px;box-shadow:none;">';

		echo '
						<div style="height:25px;">';

		$half = 10;
		foreach($this->smileys as $sm)
		{
			echo '
							<img onclick="InsertSmiley('. $this->cfg['id'] .', \''. addslashes($sm['code']) .'\')" src="'. $sm['image'] .'" alt="*" title="'. $sm['title'] .'" style="float:left;cursor:pointer;padding:2px 7px;" />';
			$half--;
			if($half == 0)
				echo '
						</div>
						<div style="height:25px;">';
		}
		echo '
						</div>
						<hr class="pmx_hr" />
						<div style="height:28px;">';

		foreach($this->bb_code as $sm)
			echo '
							<img onclick="InsertBBCode('. $this->cfg['id'] .', \''. $sm['code'] .'\'); return false;" src="'. $sm['image'] .'" alt="*" title="'. $sm['title'] .'" class="shoutbox_bbcimg" />';

		echo '
							<select id="shout_color'. $this->cfg['id'] .'" size="1" onchange="InsertBBColor('. $this->cfg['id'] .', this); return false;" style="float:right; margin:3px 4px 0 0px; width:100px;">';

		foreach($this->bb_colors as $coltxt => $colname)
			echo '
								<option value="'. $colname .'"'. ($colname == $coltxt ? ' selected="selected"' : '') .'>'. $coltxt .'</option>';

		echo '
							</select>
						</div>
					</div>
				</div>
			</div>';

		echo '
			<div class="'. $this->cfg['side'] .'" id="shoutframe'. $this->cfg['id'] .'" style="'. (isset($this->cfg['config']['settings']['maxheight']) ? 'max-height:'. $this->cfg['config']['settings']['maxheight'] .'px; overflow:auto; ' : '') .'padding:0px;">';

		$haveshouts = false;
		$allowAdmin = allowPmx('pmx_admin');

		$cnt = 0;
		foreach($this->shouts as $id => $data)
		{
			echo '
				<div id="shoutitem'. $this->cfg['id'] .'-'. $id .'">
					<div class="tborder shoutbox_user">';

			// show the ip image
			if($allowAdmin && !empty($data['ip']))
			{
				echo '
						<a id="'. $cnt .'shoutimg'. $this->cfg['id'] .'" href="'. $scripturl .'?action=trackip;searchip='. $data['ip'] .'" style="display:none;">
							<img src="'. $context['pmx_imageurl'] .'ip.gif" style="padding:4px 3px 0 4px;float:right;margin-top:-3px;" title="'. $data['ip'] .'" alt="*" />
						</a>';
				$cnt++;
			}

			// show the edit/delete images
			if($allowAdmin || ($user_info['id'] == $data['uid'] && !empty($this->cfg['config']['settings']['allowedit']) && $this->canShout))
			{
				$haveshouts = $allowAdmin || $user_info['id'] == $data['uid'] ? true : $haveshouts;
				echo '
						<img id="'. $cnt .'shoutimg'. $this->cfg['id'] .'" onclick="DeleteShout('. $this->cfg['id'] .', '. $id .', '. intval(!empty($this->cfg['config']['settings']['boxcollapse'])) .', '. intval(!empty($this->cfg['config']['settings']['boxcollapse'])) .');" style="cursor:pointer; margin-top:2px; display:none;float:right;margin-right:2px;" src="'. $context['pmx_imageurl'] .'shout_del.gif" alt="*" title="'. $txt['pmx_shoutbox_shoutdelete'] .'" />';
				$cnt++;
				echo '
						<img id="'. $cnt .'shoutimg'. $this->cfg['id'] .'" onclick="EditShout('. $this->cfg['id'] .', '. $id .', \''. addslashes($data['post']) .'\');" style="cursor:pointer;margin-top:2px;padding-right:4px;display:none;float:right;" src="'. $context['pmx_imageurl'] .'shout_edit.gif" alt="*" title="'. $txt['pmx_shoutbox_shoutedit'] .'" />';
				$cnt++;
			}

			// convert smileys and bb codes
			$data['post'] = $this->BBCtoHTML($data['post'], true);

			// Guest shout?
			if($data['uid'] != 0 && isset($this->memdata[$data['uid']]))
				echo '
						<a style="display:block; margin-top:-2px;width:fit-content;" href="'. $scripturl .'?action=profile;u='. $data['uid'] .'"><span'. (isset($this->memdata[$data['uid']]['color']) && !empty($this->memdata[$data['uid']]['color']) ? ' style="color:'. $this->memdata[$data['uid']]['color'] .';"' : '') .'>'. $this->memdata[$data['uid']]['name'] .'</span></a>';
			else
			{
				$data['uid'] = 0;
				echo '
						'. $this->memdata[$data['uid']]['name'];
			}

			$tempTXT = $txt['default_time_format'];
			$txt['default_time_format'] = str_replace('%B', '%b.', $txt['default_time_format']);
			echo '
						<div class="smalltext shoutbox_date">'. timeformat($data['time']) .'</div>
					</div>
					<div class="shoutbox_post">
						'. $data['post'] .'
					</div>
				</div>';
			$txt['default_time_format'] = $tempTXT;
		}

		echo '
				<input type="hidden" id="shoutcount'. $this->cfg['id'] .'" value="'. $cnt .'" />
			</div>';

		// have shout access?
		if($this->canShout)
		{
			$canEdit = !$user_info['is_guest'] && (($allowAdmin && $haveshouts) || ($haveshouts && !empty($this->cfg['config']['settings']['allowedit'])));
			$Admimg[0] = $context['pmx_imageurl'] . ($canEdit ? 'shout_admon.gif' : 'empty.gif');
			$Admimg[1] = $context['pmx_imageurl'] . ($canEdit ? 'shout_admoff.gif' : 'empty.gif');

			if(checkECL_Cookie(true))
			{
				echo '
			<div style="overflow:hidden;margin-bottom:2px;margin-top:4px;">
				<input type="hidden" name="shoutbox_action" value="shout" />
				<input type="hidden" name="shoutbox_id" value="'. $this->cfg['id'] .'" />
				<input type="hidden" name="sc" value="'. $context['session_id'] .'" />
				<input type="hidden" id="shout'. $this->cfg['id'] .'" name="pmx_shout" value="" />
				<input type="hidden" id="shoutid'. $this->cfg['id'] .'" name="shoutid" value="" />
				<div id="shoutcontdiv'. $this->cfg['id'] .'" style="display:none;">
					<textarea class="sboxcont" id="shoutcontent'. $this->cfg['id'] .'"  style="height:80px;min-height:80px;max-height:250px;width:100%;resize:vertical;" name="post"></textarea>
				</div>
				<div style="border-top:1px solid #ddd;margin-top:2px;padding-top:2px;">
					<img id="shoutbbon'. $this->cfg['id'] .'" style="cursor:pointer;margin-top:6px;float:left;'. (!empty($this->cfg['config']['settings']['boxcollapse']) ? 'display:none;' : '') .'" onclick="ShoutPopup('. $this->cfg['id'] .');" src="'. $context['pmx_imageurl'] . 'type_bbc.gif" alt="*" title="'. $txt['pmx_shoutbox_bbc_code'] .'" />';

				if(!empty($this->cfg['config']['settings']['boxcollapse']))
					echo '
					<img id="shoutbboff'. $this->cfg['id'] .'" style="margin-top:6px;float:left;" src="'. $context['pmx_imageurl'] . 'empty.gif" alt="*" title="" />';

				echo '
					<img id="shout_toggle'. $this->cfg['id'] .'" style="'. ($canEdit ? 'cursor:pointer;' : '') .'margin-top:6px;float:right;"'. ($canEdit ? ' onclick="ShoutAdmin('. $this->cfg['id'] .',\'check\');"' : '') .' src="'. $Admimg[0] .'" alt="*" title="'. $txt['pmx_shoutbox_toggle'] .'" />
					<input id="shout_key'. $this->cfg['id'] .'" onclick="SendShout('. $this->cfg['id'] .', '. intval(!empty($this->cfg['config']['settings']['boxcollapse'])) .')" class="button_submit shoutbutton" type="button" name="button" value="'. (!empty($this->cfg['config']['settings']['boxcollapse']) ? $txt['pmx_shoutbox_button_open'] : $txt['pmx_shoutbox_button']) .'" title="'. (!empty($this->cfg['config']['settings']['boxcollapse']) ? $txt['pmx_shoutbox_button_title'] : $txt['pmx_shoutbox_send_title']) .'" />
				</div>
			</div>';
			}
		}
	}

	// decode html chars
	function HTMLtoChar($value)
	{
		$value = str_replace(array('&#039;', '&quot;', '&lt;', '&gt;'), array("'", '\"', '<', '>'), $value);
		return  str_replace('&amp;', '&', $value);
	}

	/**
	* encode html chars
	*/
	function ChartoHTML($value, $strip_spaces = false)
	{
		global $pmxcFunc;

		if($strip_spaces)
		{
			$value = trim($value);
			$i = 0;
			while($i < $pmxcFunc['strlen']($value))
			{
				if($value{$i} == ' ')
				{
					$l = 1;
					while($i + $l < $pmxcFunc['strlen']($value) && $value{$i + $l} == ' ')
						$l++;
					if($l > 1)
						$value = $pmxcFunc['substr']($value, 0, $i) . $pmxcFunc['substr']($value, ($i + $l) -1);
				}
				$i++;
			}
		}
		return htmlspecialchars($value);
	}

	/**
	* Get the color and inner content from color tag
	*/
	function getBBC_Color($value, &$col)
	{
		global $pmxcFunc;

		$col = '';
		$i = $pmxcFunc['strpos']($value, ']');
		if($i !== false)
		{
			$col = trim($pmxcFunc['substr']($value, 0, $i));
			$value = $pmxcFunc['substr']($value, $i +1);
		}
		return $value;
	}

	/**
	* Shorten a shout entry
	*/
	function ShortenBBCpost($value, $maxlen)
	{
		global $pmxcFunc;

		// adjust maxlen for smileys
		foreach($this->smileys as $smiley)
		{
			if($cnt = preg_match_all('~'. preg_quote($smiley['code']) .'\s~', $value))
				$maxlen += ($cnt * 6);
		}

		// Check length and remove illegal bbc codes
		if(preg_match_all('~\[(.*?)(\]|\=[a-z0-9]+|])~i', $value, $matches, PREG_OFFSET_CAPTURE) > 0)
		{
			// remove all bbc tags to get a clean stream to check the lenght
			$tmp = preg_replace('~\[[^\]]*\]~', '', $value);
			if($pmxcFunc['strlen']($tmp) > $maxlen)
			{
				$value =  $pmxcFunc['substr']($tmp, 0, $maxlen);
				foreach($matches[0] as $id => $data)
					if($data[1] <= strlen($value))
						$value = substr($value, 0, $data[1]) . $data[0] . substr($value, $data[1]);
			}

			// check all bbc codes
			if(preg_match_all('~\[([a-zA-Z\=\#0-9]+)\]~i', $value, $open) > 0)
			{
				preg_match_all('~\[(\/[a-zA-Z]+)\]~i', $value, $close);
				foreach($open[1] as $id => $tag)
				{
					if(in_array($tag, array('hr', 'br')))
						unset($open[1][$id]);
					else
					{
						if(substr($tag, 0, 2) == 'c=')
							$open[1][$id] = '/c';
						else
							$open[1][$id] = '/'. $tag;
					}
				}
				$close = array_diff($open[1], $close[1]);
				foreach($close as $tag)
					$value .= '['. $tag .']';
			}

			$matches = array_reverse($matches[1]);
			foreach($matches as $id => $tag)
			{
				$tag = strtolower($tag[0]);
				if($tag{0} != '/' && $tag != 'br' && $tag != 'hr')
				{
					if($tag{0} == 'c' && $tag{1} == '=')
						$cid = array_search('/c', $matches);
					else
						$cid = array_search('/'. $tag, $matches);
					if(is_null($cid) || $cid > $id)
						$value .= '[/'. ($tag{0} == 'c' ? 'c' : $tag) .']';
				}

				if(!in_array($tag, $this->legalcodes) && !($tag{0} == 'c' && $tag{1} == '=') && !$tag == 'br' && !$tag == 'hr')
				{
					$value = str_replace('['. $tag .']', '', $value);
					$value = str_replace('[/'. $tag .']', '', $value);
				}
			}
		}

		//strip code at end
		while(true)
		{
			$value = trim($value);
			if(substr($value, strlen($value)- 4) == '[br]')
				$value = rtrim(substr($value, 0, -4));
			else
				break;
		}
		return rtrim($value);
	}

	/**
	* Convert BB codes to html
	*/
	function BBCtoHTML($value, $addSmiley = false)
	{
		$value = str_replace('[br]', '<br />', $value);
		$value = str_replace('[hr]', '<hr class="pmx_shouthr" />', $value);
		$value = str_replace('[center]', '<div style="text-align:center">', $value);
		$value = str_replace('[/center]', '</div>', $value);

		while(preg_match_all('~\[(.*)(\]|\=)(.*)\[\/\\1\]~U', $value, $matches, PREG_PATTERN_ORDER) > 0)
		{
			if(!empty($matches[0]) && count($matches) == 4)
			{
				if(preg_match_all('~\[(.*)(\]|\=)(.*)\[\/\\1\]~U', $matches[3][0], $match, PREG_PATTERN_ORDER) > 0)
				{
					$tmp = $this->BBCtoHTML($matches[3][0]);
					$value = str_replace($matches[3][0], $tmp, $value);
				}
				else
				{
					foreach($matches[0] as $id => $repl)
					{
						if($matches[2][$id] == '=')
						{
							$coltxt = $this->getBBC_Color($matches[3][$id], $col);
							if($col != '')
								$value = str_replace($repl, '<span style="color:'. $col .';">'. $coltxt .'</span>', $value);
							else
								$value = str_replace($repl, '', $value);
						}
						else
						{
							if(trim($matches[3][$id]) != '')
							{
								if(in_array(strtolower($matches[1][$id]), $this->legalcodes))
									$value = str_replace($repl, '<'. $matches[1][$id] .'>'. $matches[3][$id] .'</'. $matches[1][$id] .'>', $value);
								else
									$value = str_replace($repl, $matches[3][$id], $value);
							}
							else
								$value = str_replace($repl, '', $value);
						}
					}
				}
			}
		}
		if($addSmiley)
			$value = $this->convertSmileys($value);

		return $value;
	}

	/**
	* Convert the Smileys
	*/
	function convertSmileys($value)
	{
		global $smReplace, $smImage;

		foreach($this->smileys as $data)
		{
			$code = $this->ChartoHTML($data['code']);
			$smImage[$code] = '<img src="'. $data['image'] .'" title="'. $data['title'] .'" alt="*" />';
			$smReplace[$code] = preg_quote($code, '>');
		}
		$smPregSearch = '/('. implode('|', $smReplace) . ')(\s|$)+/m';
		return preg_replace_callback($smPregSearch, function($matches){global $smReplace, $smImage; return isset($smReplace[$matches[1]]) ? $smImage[$matches[1]] . $matches[2] : "";}, $value);
	}
}
?>