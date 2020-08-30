<?php
/**
 * Template for modal and none modal ecl accept.
 *
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx https://www.portamx.com
 * @copyright 2018 PortaMx
 * @license BSD
 *
 * @version 1.41
 */

function template_eclmain_above()
{
	global $context, $modSettings, $settings, $language, $cookiename, $boardurl, $txt;

	$ecl_cookie_time = strtotime('+3 month') .'000';
	$replaces = array('@host@' => $_SERVER['SERVER_NAME'], '@cookie@' => $cookiename, '@site@' => $context['forum_name']);
	echo '
	<div id="ecl_outer" style="top:'. $modSettings['ecl_topofs'] .'px;">
		<div id="ecl_inner" class="ecl_outer">';

	echo '
			<div class="ecl_head">
				'. sprintf($txt['ecl_needAccept'], $txt['ecl_device'][intval($modSettings['isMobile'])]) .'
			</div>
			<div class="ecl_accept">
				<input type="button" name="accept" value="'. $txt['ecl_button'] .'" onclick="pmxCookie(\'set\', \'eclauth\', \'\', \'ecl\');" />&nbsp;
				<input id="privbut" class="eclbutclose" type="button" name="accept" value="'. $txt['ecl_privacy'] .'" title="'. $txt['ecl_privacy_ttlopen'] .'" onclick="show_eclprivacy()" />';

	if(empty($modSettings['ecl_nomodal']) || (!empty($modSettings['isMobile']) && empty($modSettings['ecl_nomodal_mobile'])))
		echo '
				<div class="eclmodal"><strong id="eclmodal">&nbsp;'. $txt['ecl_agree'] .'&nbsp;</strong></div>';

	echo '
			</div>
			<div id="ecl_privacy" style="display:none">';

	$curLang = 'english';
	if (!empty($context['languages']) && count($context['languages']) > 1)
	{
		foreach ($context['languages'] as $language)
			if(isset($context['user']['language']) && $context['user']['language'] == $language['filename'])
			{
				$curLang = strtolower($language['name']);
				break;
			}}

	$privacyfile = $settings['default_theme_dir'] .'/languages/EclPrivacynotice.'. $curLang .'.php';
	if(file_exists($privacyfile))
	{
		include_once($privacyfile);

		echo '
				<div id="ecl_privacytext">
			'. strtr($txt['ecl_header'], $replaces) .'
					<table class="ecl_table">';

		foreach($txt['ecl_headrows'] as $ecltextrows)
		{
			echo '
						<tr>';

			foreach($ecltextrows as $ecltext)
				echo '
						<td>'. strtr($ecltext, $replaces) .'</td>';

			echo '
						</tr>';
		}

		echo '
					</table>
					<br />';

		echo '
			'. $txt['ecl_footertop'] .'
					<table class="ecl_table">';

		$number = 1;
		foreach($txt['ecl_footrows'] as $ecltext)
		{
			echo '
						<tr>
							<td>'. $number .'.</td>
							<td>'. $ecltext .'</td>
						</tr>';

			$number++;
		}

		echo '
					</table>';

		echo '
			'. $txt['ecl_footer'] .'
				</div>';
	}
	else
		echo '
				<div>'. $txt['ecl_privacy_failed'] .'</div>';

	echo '
			</div>
		</div>
	</div>
	<script>
		function show_eclprivacy()
		{
			if(document.getElementById("ecl_privacy"))
			{
				if(document.getElementById("ecl_privacy").style.display == "none")
				{
					ofs = (mobile_device ? 20 : 35);
					document.getElementById("ecl_privacy").style.maxHeight = window.innerHeight - (ofs + eclofsTop + document.getElementById("ecl_outer").clientHeight) +"px";
					$(document.getElementById("ecl_privacy")).slideDown(400, function(){document.getElementById("privbut").className="eclbutopen";document.getElementById("privbut").title = "'. $txt['ecl_privacy_ttlclose'] .'"});
				}
				else
					$(document.getElementById("ecl_privacy")).slideUp(400, function(){document.getElementById("privbut").className="eclbutclose";document.getElementById("privbut").title = "'. $txt['ecl_privacy_ttlopen'] .'"});
			}
		}
		$(document).ready(function() {if(typeof eclOverlay != "undefined") setTimeout(function(){window.$("#ecl_outer").fadeIn(500)}, 500);});
	</script>';
}

function template_eclmain_below(){}
?>