<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file user_login.php
 * Systemblock user_login
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* @class pmxc_user_login
* Systemblock user_login
* @see user_login.php
*/
class pmxc_user_login extends PortaMxC_SystemBlock
{
	/**
	* InitContent.
	*/
	function pmxc_InitContent()
	{
		global $user_info, $modSettings, $txt;

		if(!checkECL_Cookie())
			$this->visible = false;

		if($this->visible)
		{
			// show current time as realtime?
			if(!empty($this->cfg['config']['settings']['show_time']) && !empty($this->cfg['config']['settings']['show_realtime']))
			{
				$cdate = date('Y,n-1,j,G,', Forum_Time(true)) . intval(date('i', Forum_Time(true))) .','. intval(date('s', Forum_Time(true)));

				if(empty($modSettings['pmxUserLoginLoaded']))
					addInlineJavascript('
	var pmx_rtcFormat = {};');

				if(empty($this->cfg['config']['settings']['rtc_format']))
					addInlineJavascript('
	pmx_rtcFormat['. $this->cfg['id'] .'] = "'. $modSettings['time_format'] .'";');
				else
					addInlineJavascript('
	pmx_rtcFormat['. $this->cfg['id'] .'] = "'. $modSettings['time_format'] .'";');

				if(empty($modSettings['pmxUserLoginLoaded']))
				{
					addInlineJavascript('
	var pmx_rctMonths = new Array("'. implode('","', $txt['months_titles']) .'");
	var pmx_rctShortMonths = new Array("'. implode('","', $txt['months_short']) .'");
	var pmx_rctDays = new Array("'. implode('","', $txt['days']) .'");
	var pmx_rctShortDays = new Array("'. implode('","', $txt['days_short']) .'");
	var pmx_rtcFormatTypes = new Array("%a", "%A", "%d", "%b", "%B", "%m", "%Y", "%y", "%H", "%I", "%M", "%S", "%p", "%%", "%D", "%e", "%R", "%T");
	var pmx_rtcOffset = new Date('. $cdate .') - new Date();');

					loadJavascriptFile(PortaMx_loadCompressed('PortalUser.js'), array('external' => true));
					$modSettings['pmxUserLoginLoaded'] = true;
				}
			}
		}

		// return the visibility flag (true/false)
		return $this->visible;
	}

	/**
	* ShowContent
	* Output the content.
	*/
	function pmxc_ShowContent()
	{
		global $context, $scripturl, $txt;

		// User logged in?
		if($context['user']['is_logged'])
		{
			// avatar
			if(!empty($context['user']['avatar']) && !empty($this->cfg['config']['settings']['show_avatar']))
			{
				echo '
										<div style="display:table-cell;vertical-align:top;">
											<a class="pmx_avatar" href="'. $scripturl .'?action=profile;u='. $context['user']['id'] .'" title="'. $context['user']['name'] .'">'. $context['user']['avatar']['image'] .'</a>
										</div>
										<div  style="display:table-cell;vertical-align:top;padding-left:6px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"' .(isset($this->cfg['config']['visuals']['hellotext']) ? ' class="'. $this->cfg['config']['visuals']['hellotext'] .'"' : ''). '>'.
												$txt['pmx_hello'] .'<br /><a href="'. $scripturl .'?action=profile;u='. $context['user']['id'] .'" title="'. $context['user']['name'] .'"><b>'. $context['user']['name'] .'</b></a>
										</div>';
			}
			else
				echo '
										<div' .(isset($this->cfg['config']['visuals']['hellotext']) ? 'block;" class="'. $this->cfg['config']['visuals']['hellotext'] .'"' : ''). '>'.
											$txt['pmx_hello'] .'<a href="'. $scripturl .'?action=profile;u='. $context['user']['id'] .'"><b>'. $context['user']['name'] .'</b></a>
										</div>';

			$img = '<img src="'. $context['pmx_syscssurl'].'Images/bullet_blue.gif" alt="*" title="" />';
			$img1 = '<img src="'. $context['pmx_syscssurl'].'Images/bullet_red.gif" alt="*" title="" />';

			if(!empty($context['user']['avatar']) && !empty($this->cfg['config']['settings']['show_avatar']))
				echo '
									<ul style="margin-top:0px;" class="userlogin">';
			else
				echo '
									<ul class="userlogin smalltext">';

			// show pm?
			if(!empty($this->cfg['config']['settings']['show_pm']) && $context['allow_pm'])
				echo '
										<li>'.($context['user']['unread_messages'] > 0 ? $img1 : $img).'<span><a href="'. $scripturl .'?action=pm">'. $txt['pmx_pm'] .($context['user']['unread_messages'] > 0 ? ': '.$context['user']['unread_messages'].' <img src="'. $context['pmx_imageurl'].'newpm.gif" alt="*" title="'. $context['user']['unread_messages'] .'" />' : '').'</a></span></li>';

			// show post?
			if(!empty($this->cfg['config']['settings']['show_posts']))
			{
				echo '
										<li>'.$img.'<span><a href="'. $scripturl .'?action=unread">'. $txt['pmx_unread'] .'</a></span></li>
										<li>'.$img.'<span><a href="'. $scripturl .'?action=unreadreplies">'. $txt['pmx_replies'] .'</a></span></li>
										<li>'.$img.'<span><a href="'. $scripturl .'?action=profile;area=showposts;u='. $context['user']['id'] .'">'. $txt['pmx_showownposts'] .'</a></span></li>';
			}
			echo '
									</ul>';

			// Is the forum in maintenance mode?
			if($context['in_maintenance'] && $context['user']['is_admin'])
				echo '
									<b>'. $txt['pmx_maintenace'] .'</b><br />';

			// Show the total time logged in?
			if(!empty($context['user']['total_time_logged_in']) && isset($this->cfg['config']['settings']['show_logtime']) && $this->cfg['config']['settings']['show_logtime'] == 1)
			{
				$totm = $context['user']['total_time_logged_in'];
				$form = '%s: %s%s %s%s %s%s';
				echo sprintf($form, $txt['pmx_loggedintime'], $totm['days'], $txt['pmx_Ldays'], $totm['hours'], $txt['pmx_Lhours'], $totm['minutes'], $txt['pmx_Lminutes']);
				echo '<br />';
			}
		}

		// Otherwise they're a guest, ask them to register or login.
		else
		{
			if(!empty($this->cfg['config']['settings']['show_login']) && checkECL_Cookie(true))
			{
				echo '
									<div style="padding-top:4px;">
										<form action="', $scripturl, '?action=login2;quicklogin" method="post" accept-charset="', $context['character_set'], '">
											<input id="username" type="text" name="user" size="10" class="input_text" style="width:48%;float:left;margin-bottom:3px;" value="" />
											<input type="password" name="passwrd" size="10" class="input_password" value="" style="width:48%;float:right;margin-bottom:3px;margin-right:4px;" />
											<select name="cookielength">
												<option value="60">', $txt['one_hour'], '</option>
												<option value="1440">', $txt['one_day'], '</option>
												<option value="10080">', $txt['one_week'], '</option>
												<option value="43200">', $txt['one_month'], '</option>
												<option value="-1" selected="selected">', $txt['forever'], '</option>
											</select>
											<input type="hidden" name="hash_passwrd" value="" />
											<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
											<input type="hidden" name="', $context['login_token_var'], '" value="', $context['login_token'], '" />
											<input style="float:right;margin-right:4px;" type="submit" value="'. $txt['login'] .'" />
										</form>
									'. $txt['login_dec'] .'
									</div>';
			}
		}

		// show current time?
		if(!empty($this->cfg['config']['settings']['show_time']))
		{
			if(!empty($this->cfg['config']['settings']['show_realtime']))
			{
				$cdate = date('Y,n-1,j,G,', Forum_Time()) . intval(date('i', Forum_Time())) .','. intval(date('s', Forum_Time()));
				echo '
								<span id="ulClock'. $this->cfg['id'] .'"></span>
								<script>
									ulClock("'. $this->cfg['id'] .'");
								</script>';
			}
			else
				echo $context['current_time'];
		}

		// show logout button?
		if($context['user']['is_logged'] && !empty($this->cfg['config']['settings']['show_logout']))
			echo '
								<br />
								<div style="text-align:center;margin-top:5px;">
									<input class="button_submit" type="button" value="'. $txt['logout'] .'" onclick="DoLogout()" />
								</div>
								<script>
									function DoLogout(){window.location = "'. $scripturl .'?action=logout;'. $context['session_var'] .'='. $context['session_id'] .'";}
								</script>';
	}
}
?>