<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx & Simple Machines
 * @copyright 2018 PortaMx,  Simple Machines and individual contributors
 * @license http://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 1.41
 */

/**
 * This displays a help popup thingy
 */
function template_popup()
{
	global $context, $settings, $txt, $modSettings;

	// Since this is a popup of its own we need to start the html, etc.
	echo '<!DOCTYPE html>
<html', $context['right_to_left'] ? ' dir="rtl"' : '', '>
	<head>
		<meta charset="', $context['character_set'], '">
		<meta name="robots" content="noindex">
		<title>', $context['page_title'], '</title>
		<link rel="stylesheet" href="', $settings['theme_url'], '/css/index', $context['theme_variant'], '.css', $modSettings['browser_cache'] ,'">
		<script src="', $settings['default_theme_url'], '/scripts/script.js', $modSettings['browser_cache'] ,'"></script>
	</head>
	<body id="help_popup">
		<div class="windowbg description">
			', $context['help_text'], '<br>
			<br>
			<a href="javascript:self.close();">', $txt['close_window'], '</a>
		</div>
	</body>
</html>';
}

/**
 * The template for the popup for finding members
 */
function template_find_members()
{
	global $context, $settings, $scripturl, $modSettings, $txt;

	echo '<!DOCTYPE html>
<html', $context['right_to_left'] ? ' dir="rtl"' : '', '>
	<head>
		<title>', $txt['find_members'], '</title>
		<meta charset="', $context['character_set'], '">
		<meta name="robots" content="noindex">
		<link rel="stylesheet" href="', $settings['theme_url'], '/css/index', $context['theme_variant'], '.css', $modSettings['browser_cache'] ,'">
		<script src="', $settings['default_theme_url'], '/scripts/script.js', $modSettings['browser_cache'] ,'"></script>
		<script>
			var membersAdded = [];
			function addMember(name)
			{
				var theTextBox = window.opener.document.getElementById("', $context['input_box_name'], '");

				if (name in membersAdded)
					return;

				// If we only accept one name don\'t remember what is there.
				if (', JavaScriptEscape($context['delimiter']), ' != \'null\')
					membersAdded[name] = true;

				if (theTextBox.value.length < 1 || ', JavaScriptEscape($context['delimiter']), ' == \'null\')
					theTextBox.value = ', $context['quote_results'] ? '"\"" + name + "\""' : 'name', ';
				else
					theTextBox.value += ', JavaScriptEscape($context['delimiter']), ' + ', $context['quote_results'] ? '"\"" + name + "\""' : 'name', ';

				window.focus();
			}
		</script>
	</head>
	<body id="help_popup">
		<form action="', $scripturl, '?action=findmember;', $context['session_var'], '=', $context['session_id'], '" method="post" accept-charset="', $context['character_set'], '" class="padding description">
			<div class="roundframe">
				<div class="cat_bar">
					<h3 class="catbg">', $txt['find_members'], '</h3>
				</div>
				<div class="padding">
					<strong>', $txt['find_username'], ':</strong><br>
					<input type="text" name="search" id="search" value="', isset($context['last_search']) ? $context['last_search'] : '', '" style="margin-top: 4px; width: 96%;" class="input_text"><br>
					<span class="smalltext"><em>', $txt['find_wildcards'], '</em></span><br>';

	// Only offer to search for buddies if we have some!
	if (!empty($context['show_buddies']))
		echo '
					<span class="smalltext"><label for="buddies"><input type="checkbox" class="input_check" name="buddies" id="buddies"', !empty($context['buddy_search']) ? ' checked' : '', '> ', $txt['find_buddies'], '</label></span><br>';

	echo '
					<div class="padding righttext">
						<input type="submit" value="', $txt['search'], '" class="button_submit">
						<input type="button" value="', $txt['find_close'], '" onclick="window.close();" class="button_submit">
					</div>
				</div>
			</div>
			<br>
			<div class="roundframe">
				<div class="cat_bar">
					<h3 class="catbg">', $txt['find_results'], '</h3>
				</div>';

	if (empty($context['results']))
		echo '
				<p class="error">', $txt['find_no_results'], '</p>';
	else
	{
		echo '
				<ul class="padding">';

		foreach ($context['results'] as $result)
		{
			echo '
					<li class="windowbg">
						<a href="', $result['href'], '" target="_blank" rel="noopener" class="new_win"> <span class="generic_icons profile_sm"></span>
						<a href="javascript:void(0);" onclick="addMember(this.innerHTML); return false;">', $result['name'], '</a>
					</li>';
		}

		echo '
				</ul>
				<div class="pagesection">
					<div class="pagelinks floatleft">', $context['page_index'], '</div>
				</div>';
	}

	echo '

			</div>
			<input type="hidden" name="input" value="', $context['input_box_name'], '">
			<input type="hidden" name="delim" value="', $context['delimiter'], '">
			<input type="hidden" name="quote" value="', $context['quote_results'] ? '1' : '0', '">
		</form>';

	if (empty($context['results']))
		echo '
		<script>
			document.getElementById("search").focus();
		</script>';

	echo '
	</body>
</html>';
}

/**
 * The main help page
 */
function template_manual()
{
	global $context, $scripturl, $txt;

	echo '
			<div class="cat_bar">
				<h3 class="catbg">', $txt['manual_user_help'], '</h3>
			</div>
			<div id="help_container">
				<div id="helpmain" class="informtion">
					<p style="text-align:center;font-weight:bold;">', sprintf($txt['manual_welcome'], $context['forum_name_html_safe']), '</p>
					<p style="text-align:center;">', $txt['manual_not_available'] ,'</p>
				</div>
			</div>';
}

/**
 * The rules page
 */
function template_terms()
{
	global $txt, $context, $modSettings;

	if (!empty($modSettings['requireAgreement']))
		echo '
			<div class="cat_bar">
				<h3 class="catbg" style="text-align:center">
					', $txt['terms_and_rules'], '
				</h3>
			</div>
			<div class="information">
				', $context['agreement'], '
			</div>';
	else
		echo '
			<div class="noticebox">
				', $txt['agreement_disabled'], '
			</div>';
}

/**
 * Impressum (imprint)
 */
function template_imprint()
{
	global $txt, $context;

		echo '
			<div class="cat_bar">
				<h3 class="catbg" style="text-align:center">
					', $txt['disclaimer_title'], '
				</h3>
			</div>
			<div class="information">
				', $context['imprint'], '
			</div>';
}

?>