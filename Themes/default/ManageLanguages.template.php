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
 * Download a new language file.
 */
function template_download_language()
{
	global $context, $settings, $txt, $scripturl, $modSettings;

	// Actually finished?
	if (!empty($context['install_complete']))
	{
		echo '
	<div id="admincenter">
		<div class="cat_bar">
			<h3 class="catbg">
				', $txt['languages_download_complete'], '
			</h3>
		</div>
		<div class="windowbg">
			', $context['install_complete'], '
		</div>
	</div>';
		return;
	}

	// An error?
	if (!empty($context['error_message']))
		echo '
	<div class="errorbox">
		', $context['error_message'], '
	</div>';

	// Provide something of an introduction...
	echo '
	<div id="admincenter">
		<form action="', $scripturl, '?action=admin;area=languages;sa=downloadlang;did=', $context['download_id'], ';', $context['session_var'], '=', $context['session_id'], '" method="post" accept-charset="', $context['character_set'], '">
			<div class="cat_bar">
				<h3 class="catbg">
					', $txt['languages_download'], '
				</h3>
			</div>
			<div class="windowbg totop" style="margin-bottom:7px;">
				<p>
					', $txt['languages_download_note'], '
				</p>
				<div class="smalltext">
					', $txt['languages_download_info'], '
				</div>
			</div>';

	// Show the main files.
	template_show_list('lang_main_files_list');

	// Do we want some FTP baby?
	// If the files are not writable, we might!
	if (!empty($context['still_not_writable']))
	{
		if (!empty($context['package_ftp']['error']))
			echo '
			<div class="errorbox">
				', $context['package_ftp']['error'], '
			</div>';

		echo '
			<div class="cat_bar">
				<h3 class="catbg">
					', $txt['package_ftp_necessary'], '
				</h3>
			</div>
			<div class="windowbg">
				<p>', $txt['package_ftp_why'],'</p>
				<dl class="settings">
					<dt
						<label for="ftp_server">', $txt['package_ftp_server'], ':</label>
					</dt>
					<dd>
						<div class="floatright" style="margin-right: 1px;"><label for="ftp_port" style="padding-top: 2px; padding-right: 2ex;">', $txt['package_ftp_port'], ':&nbsp;</label> <input type="text" size="3" name="ftp_port" id="ftp_port" value="', isset($context['package_ftp']['port']) ? $context['package_ftp']['port'] : (isset($modSettings['package_port']) ? $modSettings['package_port'] : '21'), '" class="input_text"></div>
						<input type="text" size="30" name="ftp_server" id="ftp_server" value="', isset($context['package_ftp']['server']) ? $context['package_ftp']['server'] : (isset($modSettings['package_server']) ? $modSettings['package_server'] : 'localhost'), '" style="width: 70%;" class="input_text">
					</dd>

					<dt>
						<label for="ftp_username">', $txt['package_ftp_username'], ':</label>
					</dt>
					<dd>
						<input type="text" size="50" name="ftp_username" id="ftp_username" value="', isset($context['package_ftp']['username']) ? $context['package_ftp']['username'] : (isset($modSettings['package_username']) ? $modSettings['package_username'] : ''), '" style="width: 99%;" class="input_text">
					</dd>

					<dt>
						<label for="ftp_password">', $txt['package_ftp_password'], ':</label>
					</dt>
					<dd>
						<input type="password" size="50" name="ftp_password" id="ftp_password" style="width: 99%;" class="input_text">
					</dd>

					<dt>
						<label for="ftp_path">', $txt['package_ftp_path'], ':</label>
					</dt>
					<dd>
						<input type="text" size="50" name="ftp_path" id="ftp_path" value="', $context['package_ftp']['path'], '" style="width: 99%;" class="input_text">
					</dd>
				</dl>
			</div>';
	}

	// Install?
	echo '
			<div class="righttext padding">
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
				<input type="hidden" name="', $context['admin-dlang_token_var'], '" value="', $context['admin-dlang_token'], '">
				<input type="submit" name="do_install" value="', $txt['add_language_pmx_install'], '" class="button_submit">
			</div>
		</form>
	</div>';
}

/**
 * Edit language entries.
 */
function template_modify_language_entries()
{
	global $context, $txt, $scripturl;

	echo '
	<div id="admincenter">
		<form action="', $scripturl, '?action=admin;area=languages;sa=editlang;lid=', $context['lang_id'], '" method="post" accept-charset="', $context['character_set'], '">
			<div class="cat_bar">
				<h3 class="catbg">
					', $txt['edit_languages'], '
				</h3>
			</div>
			<div id="editlang_desc" class="information">
				', $txt['edit_language_entries_primary'], '
			</div>';

	// Not writable?
	if (!empty($context['lang_file_not_writable_message']))
	{
		// Oops, show an error for ya.
		echo '
			<div class="errorbox">
				', $context['lang_file_not_writable_message'], '
			</div>';
	}

	loadLanguage('LangSettings', $context['lang_id'], false);

	// Show the language entries
	echo '
			<div class="windowbg">
				<fieldset>
					<legend>', $context['primary_settings']['name'], '</legend>
					<dl class="settings">
						<dt>
							<label for="character_set">', $txt['languages_character_set'], ':</label>
						</dt>
						<dd>
							<input type="text" name="character_set" id="character_set" size="20" value="', $context['primary_settings']['character_set'], '"', (empty($context['file_entries']) ? '' : ' disabled'), ' class="input_text">
						</dd>
						<dt>
							<label for="locale">', $txt['languages_locale'], ':</label>
						</dt>
						<dd>
							<input type="text" name="locale" id="locale" size="20" value="', $context['primary_settings']['locale'], '"', (empty($context['file_entries']) ? '' : ' disabled'), ' class="input_text">
						</dd>
						<dt>
							<label for="dictionary">', $txt['languages_dictionary'], ':</label>
						</dt>
						<dd>
							<input type="text" name="dictionary" id="dictionary" size="20" value="', $context['primary_settings']['dictionary'], '"', (empty($context['file_entries']) ? '' : ' disabled'), ' class="input_text">
						</dd>
						<dt>
							<label for="spelling">', $txt['languages_spelling'], ':</label>
						</dt>
						<dd>
							<input type="text" name="spelling" id="spelling" size="20" value="', $context['primary_settings']['spelling'], '"', (empty($context['file_entries']) ? '' : ' disabled'), ' class="input_text">
						</dd>
						<dt>
							<label for="rtl">', $txt['languages_rtl'], ':</label>
						</dt>
						<dd>
							<input type="checkbox" name="rtl" id="rtl"', $context['primary_settings']['rtl'] ? ' checked' : '', ' class="input_check"', (empty($context['file_entries']) ? '' : ' disabled'), '>
						</dd>
					</dl>
				</fieldset>
				<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
				<input type="hidden" name="', $context['admin-mlang_token_var'], '" value="', $context['admin-mlang_token'], '">
				<input type="submit" name="save_main" value="', $txt['save'], '"', $context['lang_file_not_writable_message'] || !empty($context['file_entries']) ? ' disabled' : '', ' class="button_submit">';

	// Allow deleting entries.
	if ($context['lang_id'] != 'english')
	{
		// English can't be deleted though.
		echo '
					<input type="submit" name="delete_main" value="', $txt['delete'], '"', $context['lang_file_not_writable_message'] || !empty($context['file_entries']) ? ' disabled' : '', ' onclick="confirm(\'', $txt['languages_delete_confirm'], '\');" class="button_submit">';
	}

	echo '
			</div>
		</form>
	</div>';
}

/**
 * Add a new language
 *
 */
function template_add_language()
{
	global $context, $txt, $scripturl;

	echo '
	<div id="admincenter">
		<form id="admin_form_wrapper"action="', $scripturl, '?action=admin;area=languages;sa=add;', $context['session_var'], '=', $context['session_id'], '" method="post" accept-charset="', $context['character_set'], '">
			<div class="cat_bar">
				<h3 class="catbg">
					', $txt['add_language'], '
				</h3>
			</div>
			<div class="windowbg2">
				<fieldset>
					<legend>', $txt['add_language_pmx'], '</legend>
					<label class="smalltext">', $txt['add_language_pmx_browse'], '</label>
					<input type="text" name="pmx_add" class="floatright" size="40" value="', !empty($context['pmx_search_term']) ? $context['pmx_search_term'] : '', '" class="input_text">';

	// Do we have some errors? Too bad.
	if (!empty($context['pmx_error']))
	{
		// Display a little error box.
		echo '
					<div><br><p class="errorbox">', $txt['add_language_error_' . $context['pmx_error']], '</p></div>';
	}

	echo '
				</fieldset>', isBrowser('is_ie') ? '<input type="text" name="ie_fix" style="display: none;" class="input_text"> ' : '', '
				<input type="submit" name="pmx_add_sub" value="', $txt['search'], '" class="button_submit">
				<br>
			</div>';

	// Had some results?
	if (!empty($context['pmx_languages']['rows']))
	{
		echo '
			<div class="cat_bar addtopspace"><h3 class="catbg">', $txt['add_language_found_title'], '</div><div class="information">', $txt['add_language_pmx_found'], '</div>';

		template_show_list('pmx_languages');
	}

	echo '
		</form>
	</div>';
}

?>