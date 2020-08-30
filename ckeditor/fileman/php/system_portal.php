<?php
define('PMX', 'SSI');

// We're going to want a few globals... these are all set later.
global $time_start, $maintenance, $msubject, $mmessage, $mbname, $language;
global $boardurl, $boarddir, $sourcedir, $webmaster_email, $cookiename;
global $db_server, $db_name, $db_user, $db_prefix, $db_persist, $db_error_send, $db_last_error;
global $db_connection, $modSettings, $context, $sc, $user_info, $topic, $board, $txt;
global $pmxcFunc, $ssi_db_user, $scripturl, $ssi_db_passwd, $db_passwd, $cachedir;

// Remember the current configuration so it can be set back.
$ssi_magic_quotes_runtime = function_exists('get_magic_quotes_gpc') && get_magic_quotes_runtime();
if (function_exists('set_magic_quotes_runtime'))
	@set_magic_quotes_runtime(0);
$time_start = microtime();

// Get the forum's settings for database and file paths.
require_once(substr(dirname(__FILE__), 0, strpos(dirname(__FILE__), 'ckeditor')) .'Settings.php');

// Make absolutely sure the cache directory is defined.
if ((empty($cachedir) || !file_exists($cachedir)) && file_exists($boarddir . '/cache'))
	$cachedir = $boarddir . '/cache';

// Fix for using the current directory as a path.
if (substr($sourcedir, 0, 1) == '.' && substr($sourcedir, 1, 1) != '.')
	$sourcedir = dirname(__FILE__) . substr($sourcedir, 1);

// Load the important includes.
require_once($sourcedir . '/Subs-Cache.php');
require_once($sourcedir . '/Session.php');
require_once($sourcedir . '/Subs.php');
require_once($sourcedir . '/Subs-Auth.php');
require_once($sourcedir . '/Load.php');

// Create a variable to store some PORTAMX Portal specific functions in.
$pmxcFunc = array();
loadDatabase();
reloadSettings();

// start the session
loadSession();
?>