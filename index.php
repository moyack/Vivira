<?php
/**
 * This, as you have probably guessed, is the crux on which SMF functions.
 * Everything should start here, so all the setup and security is done
 * properly.  The most interesting part of this file is the action array in
 * the pmx_main() function.  It is formatted as so:
 * 	'action-in-url' => array('Source-File.php', 'FunctionToCall'),
 *
 * Then, you can access the FunctionToCall() function from Source-File.php
 * with the URL index.php?action=action-in-url.  Relatively simple, no?
 *
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx & Simple Machines
 * @copyright 2018 PortaMx,  Simple Machines and individual contributors
 * @license http://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 1.41
 */

$forum_version = 'PortaMx-Forum 1.41';
$software_year = '2018';

// Get everything started up...
define('PMX', 1);
error_reporting(E_ALL);
$time_start = microtime();

// This makes it so headers can be sent!
ob_start();

// Do some cleaning, just in case.
foreach (array('db_character_set', 'cachedir') as $variable)
	if (isset($GLOBALS[$variable]))
		unset($GLOBALS[$variable], $GLOBALS[$variable]);

// Load the settings...
require_once(dirname(__FILE__) . '/Settings.php');

// Make absolutely sure the cache directory is defined.
if ((empty($cachedir) || !file_exists($cachedir)) && file_exists($boarddir . '/cache'))
	$cachedir = $boarddir . '/cache';

// Without those we can't go anywhere
require_once($sourcedir . '/Subs-Cache.php');
require_once($sourcedir . '/QueryString.php');
require_once($sourcedir . '/Subs.php');
require_once($sourcedir . '/Subs-Auth.php');
require_once($sourcedir . '/Errors.php');
require_once($sourcedir . '/Security.php');
require_once($sourcedir . '/Load.php');

// If $maintenance is set specifically to 2, then we're upgrading or something.
if (!empty($maintenance) && $maintenance == 2)
	display_maintenance_message();

// Create a variable to store some PMX specific functions in.
$pmxcFunc = array();

// Initiate the database connection and define some database functions to use.
loadDatabase();

// Load the settings from the settings table, and perform operations like optimizing.
$context = array();
reloadSettings();

// Clean the request variables, add slashes, etc.
cleanRequest();

// handle Javascript cookie requests
jsCookieHandling();

// Seed the random generator.
if (empty($modSettings['rand_seed']) || mt_rand(1, 250) == 69)
	pmx_seed_generator();

// load default lang time/date settings
loadLanguage('LangSettings', $language, false);

// Before we get carried away, are we doing a scheduled task? If so save CPU cycles by jumping out!
if (isset($_GET['scheduled']))
{
	require_once($sourcedir . '/ScheduledTasks.php');
	AutoTask();
}

// Recapcha class autoload
spl_autoload_register(function ($class) use ($sourcedir)
{
	$classMap = array('ReCaptcha\\' => 'ReCaptcha/');
	$prefix = 'ReCaptcha\\';
	$dirName = 'ReCaptcha/';

	// get the relative class name
	$len = strlen($prefix);
	$relativeClass = substr($class, $len);

	// replace the namespace prefix with the base directory, replace namespace
	$fileName = $dirName . strtr($relativeClass, '\\', '/') . '.php';

	// if the file exists, require it
	if (file_exists($fileName = $sourcedir .'/'. $fileName))
	{
		require_once $fileName;
		return;
	}
});

// important includes.
require_once($sourcedir . '/Session.php');
require_once($sourcedir . '/Errors.php');
require_once($sourcedir . '/Logging.php');
require_once($sourcedir . '/Class-BrowserDetect.php');

// Check if compressed output is enabled, supported, and not already being done.
if (!empty($modSettings['enableCompressedOutput']) && !headers_sent())
{
	// If zlib is being used, turn off output compression.
	if (ini_get('zlib.output_compression') >=  1 || ini_get('output_handler') == 'ob_gzhandler')
		$modSettings['enableCompressedOutput'] = '0';
	else
	{
		ob_end_clean();
		ob_start('ob_gzhandler');
	}
}

// Register an error handler.
set_error_handler('pmx_error_handler');

// Start the session. (assuming it hasn't already been.)
loadSession();

// What function shall we execute? (done like this for memory's sake.)
call_user_func(pmx_main());

// Call obExit specially; we're coming from the main area ;).
obExit(null, null, true);

/**
 * The main dispatcher.
 * This delegates to each area.
 * @return array|string|void An array containing the file to include and name of function to call, the name of a function to call or dies with a fatal_lang_error if we couldn't find anything to do.
 */
function pmx_main()
{
	global $modSettings, $settings, $user_info, $board, $topic, $language, $context;
	global $board_info, $maintenance, $sourcedir, $scripturl, $pmxCacheFunc;

	// Special case: session keep-alive, output a transparent pixel.
	if (isset($_GET['action']) && $_GET['action'] == 'keepalive')
	{
		header('Content-Type: image/gif');
		die("\x47\x49\x46\x38\x39\x61\x01\x00\x01\x00\x80\x00\x00\x00\x00\x00\x00\x00\x00\x21\xF9\x04\x01\x00\x00\x00\x00\x2C\x00\x00\x00\x00\x01\x00\x01\x00\x00\x02\x02\x44\x01\x00\x3B");
	}

	// We should set our security headers now.
	frameOptionsHeader();

	// Load the user's cookie (or set as guest) and load their settings.
	loadUserSettings();

	// check if the visitor inside the EU
	$needECL_Overlay = ecl_ip_info();

	// load the userinfo language defs
	if(isset($user_info['language']))
		loadLanguage('LangSettings', $user_info['language'], false);

	// Load the current board's information.
	loadBoard();

	// Load the current user's permissions.
	loadPermissions();

	// Attachments don't require the entire theme to be loaded.
	if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'dlattach')
		detectBrowser();
	// Load the current theme.  (note that ?theme=1 will also work, may be used for guest theming.)
	else
		loadTheme();

	// Check if the user should be disallowed access.
	is_not_banned();

	// If we are in a topic and don't have permission to approve it then duck out now.
	if (!empty($topic) && empty($board_info['cur_topic_approved']) && !allowedTo('approve_posts') && ($user_info['id'] != $board_info['cur_topic_starter'] || $user_info['is_guest']))
		fatal_lang_error('not_a_topic', false);

	$no_stat_actions = array('clock', 'dlattach', 'findmember', 'jsoption', 'likes', 'loadeditorlocale', 'modifycat', 'requestmembers', 'suggest', 'about:unknown', '.xml', 'xmlhttp', 'verificationcode', 'viewquery', 'viewpmxfile');
	call_integration_hook('integrate_pre_log_stats', array(&$no_stat_actions));

	// Do some logging, unless this is an attachment, avatar, toggle of editor buttons, theme option, XML feed etc.
	if (empty($_REQUEST['action']) || !in_array($_REQUEST['action'], $no_stat_actions))
	{
		// Log this user as online.
		writeLog();

		// Track forum statistics and hits...?
		if (!empty($modSettings['hitStats']))
			trackStats(array('hits' => '+'));
	}
	unset($no_stat_actions);

	// if a GUEST not have ECL accepted, show the ECL overlay
	if(!empty($needECL_Overlay))
	{
		ecl_Init();
		// on Modal mode he can ony see the default entry page (board owerview or portal frontpage), the credits, rules and private policy
		if((empty($modSettings['isMobile']) && empty($modSettings['ecl_nomodal'])) || (!empty($modSettings['isMobile']) && empty($modSettings['ecl_nomodal_mobile'])))
		{
			if(getREQcnt('imprint,rules,login,signup,credits') == 0 && getREQcnt('action,board,topic', true) > 0)
				ecl_error('request');
		}
	}

	// start the Portal if enabled
	if(!empty($modSettings['portal_enabled']))
		Portal();

	// Is the forum in maintenance mode? (doesn't apply to administrators.)
	if (!empty($maintenance) && !allowedTo('admin_forum'))
	{
		// You can only login.... otherwise, you're getting the "maintenance mode" display.
		if (isset($_REQUEST['action']) && (in_array($_REQUEST['action'], array('login2', 'logintfa', 'logout'))))
		{
			require_once($sourcedir . '/LogInOut.php');
			return ($_REQUEST['action'] == 'login2' ? 'Login2' : ($_REQUEST['action'] == 'logintfa' ? 'LoginTFA' : 'Logout'));
		}
		// Don't even try it, sonny.
		elseif (getREQcnt('imprint') != 1 && getREQcnt('help,rules') != 2)
			return 'InMaintenance';
	}
	// If guest access is off, a guest can only do one of the very few following actions.
	elseif (empty($modSettings['allow_guestAccess']) && $user_info['is_guest'] && getREQcnt('coppa,login,login2,logintfa,reminder,activate,credits,help,helpadmin,verificationcode,signup,signup2,imprint') == 0)
	{
		if (!empty($modSettings['portal_enabled']))
			return Portal_allow_guest($board, $topic);
		else
			return 'KickGuest';
	}
	elseif (!empty($modSettings['gdpr_enabled']) && empty($user_info['is_guest']) && empty($user_info['is_admin']) && getREQcnt('profile,logout,themes') == 0 && show_gdpr_agreement())
	{
		$context['pmx']['settings']['dl_access'] = 0;
		require_once($sourcedir .'/Register.php');
		return 'ShowGDPR';
	}
	elseif (empty($_REQUEST['action']))
	{
		// Action and board are both empty... BoardIndex! Unless someone else wants to do something different.
		if (empty($board) && empty($topic))
		{
			$defaultAction = false;

			if(!empty($modSettings['portal_enabled']) && Portal_frontactions())
				return 'Portal';

			if (!empty($modSettings['integrate_default_action']))
			{
				$defaultAction = explode(',', $modSettings['integrate_default_action']);

				// Sorry, only one default action is needed.
				$defaultAction = $defaultAction[0];

				$call = call_helper($defaultAction, true);

				if (!empty($call))
					return $call;
			}

			// No default action huh? then go to our good old BoardIndex.
			else
			{
				require_once($sourcedir . '/BoardIndex.php');
				return 'BoardIndex';
			}
		}

		// Topic is empty, and action is empty.... MessageIndex!
		elseif (empty($topic))
		{
			require_once($sourcedir . '/MessageIndex.php');
			return 'MessageIndex';
		}

		// Board is not empty... topic is not empty... action is empty.. Display!
		else
		{
			require_once($sourcedir . '/Display.php');
			return 'Display';
		}
	}

	// Here's the monstrous $_REQUEST['action'] array - $_REQUEST['action'] => array($file, $function).
	$actionArray = array(
		'activate' => array('Register.php', 'Activate'),
		'admin' => array('Admin.php', 'AdminMain'),
		'announce' => array('Post.php', 'AnnounceTopic'),
		'attachapprove' => array('ManageAttachments.php', 'ApproveAttach'),
		'buddy' => array('Subs-Members.php', 'BuddyListToggle'),
		'calendar' => array('Calendar.php', 'CalendarMain'),
		'clock' => array('Calendar.php', 'clock'),
		'coppa' => array('Register.php', 'CoppaForm'),
		'credits' => array('Who.php', 'Credits'),
		'deletemsg' => array('RemoveTopic.php', 'DeleteMessage'),
		'dlattach' => array('ShowAttachments.php', 'showAttachment'),
		'editpoll' => array('Poll.php', 'EditPoll'),
		'editpoll2' => array('Poll.php', 'EditPoll2'),
		'findmember' => array('Subs-Auth.php', 'JSMembers'),
		'groups' => array('Groups.php', 'Groups'),
		'help' => array('Help.php', 'ShowHelp'),
		'helpadmin' => array('Help.php', 'ShowAdminHelp'),
		'imprint' => array('Help.php', 'ShowImprint'),
		'jsmodify' => array('Post.php', 'JavaScriptModify'),
		'jsoption' => array('Themes.php', 'SetJavaScript'),
		'likes' => array('Likes.php', 'Likes::call#'),
		'loadeditorlocale' => array('Subs-Editor.php', 'loadLocale'),
		'lock' => array('Topic.php', 'LockTopic'),
		'lockvoting' => array('Poll.php', 'LockVoting'),
		'login' => array('LogInOut.php', 'Login'),
		'login2' => array('LogInOut.php', 'Login2'),
		'logintfa' => array('LogInOut.php', 'LoginTFA'),
		'logout' => array('LogInOut.php', 'Logout'),
		'markasread' => array('Subs-Boards.php', 'MarkRead'),
		'mergetopics' => array('SplitTopics.php', 'MergeTopics'),
		'mlist' => array('Memberlist.php', 'Memberlist'),
		'moderate' => array('ModerationCenter.php', 'ModerationMain'),
		'modifycat' => array('ManageBoards.php', 'ModifyCat'),
		'movetopic' => array('MoveTopic.php', 'MoveTopic'),
		'movetopic2' => array('MoveTopic.php', 'MoveTopic2'),
		'notify' => array('Notify.php', 'Notify'),
		'notifyboard' => array('Notify.php', 'BoardNotify'),
		'notifytopic' => array('Notify.php', 'TopicNotify'),
		'pm' => array('PersonalMessage.php', 'MessageMain'),
		'post' => array('Post.php', 'Post'),
		'post2' => array('Post.php', 'Post2'),
		'printpage' => array('Printpage.php', 'PrintTopic'),
		'profile' => array('Profile.php', 'ModifyProfile'),
		'quotefast' => array('Post.php', 'QuoteFast'),
		'quickmod' => array('MessageIndex.php', 'QuickModeration'),
		'quickmod2' => array('Display.php', 'QuickInTopicModeration'),
		'recent' => array('Recent.php', 'RecentPosts'),
		'reminder' => array('Reminder.php', 'RemindMe'),
		'removepoll' => array('Poll.php', 'RemovePoll'),
		'removetopic2' => array('RemoveTopic.php', 'RemoveTopic2'),
		'reporttm' => array('ReportToMod.php', 'ReportToModerator'),
		'requestmembers' => array('Subs-Auth.php', 'RequestMembers'),
		'restoretopic' => array('RemoveTopic.php', 'RestoreTopic'),
		'search' => array('Search.php', 'PlushSearch1'),
		'search2' => array('Search.php', 'PlushSearch2'),
		'sendactivation' => array('Register.php', 'SendActivation'),
		'signup' => array('Register.php', 'Register'),
		'signup2' => array('Register.php', 'Register2'),
		'suggest' => array('Subs-Editor.php', 'AutoSuggestHandler'),
		'spellcheck' => array('Subs-Post.php', 'SpellCheck'),
		'splittopics' => array('SplitTopics.php', 'SplitTopics'),
		'stats' => array('Stats.php', 'DisplayStats'),
		'sticky' => array('Topic.php', 'Sticky'),
		'themes' => array('Themes.php', 'ThemesMain'),
		'trackip' => array('Profile-View.php', 'trackIP'),
		'about:unknown' => array('Likes.php', 'BookOfUnknown'),
		'unread' => array('Recent.php', 'UnreadTopics'),
		'unreadreplies' => array('Recent.php', 'UnreadTopics'),
		'uploadAttach' => array('Attachments.php', 'Attachments::call#'),
		'verificationcode' => array('Register.php', 'VerificationCode'),
		'viewprofile' => array('Profile.php', 'ModifyProfile'),
		'vote' => array('Poll.php', 'Vote'),
		'viewquery' => array('ViewQuery.php', 'ViewQuery'),
		'viewpmxfile' => array('Admin.php', 'DisplayAdminFile'),
		'who' => array('Who.php', 'Who'),
		'.xml' => array('News.php', 'ShowXmlFeed'),
		'xmlhttp' => array('Xml.php', 'XMLhttpMain'),
	);

	if(!empty($modSettings['portal_enabled']))
		Portal_Actions($actionArray);

	// Allow modifying $actionArray easily.
	call_integration_hook('integrate_actions', array(&$actionArray));

	// Get the function and file to include - if it's not there, do the board index.
	if (!isset($_REQUEST['action']) || !isset($actionArray[$_REQUEST['action']]))
	{
		// Catch the action with the theme?
		if (!empty($settings['catch_action']))
		{
			require_once($sourcedir . '/Themes.php');
			return 'WrapAction';
		}

		if (!empty($modSettings['integrate_fallback_action']))
		{
			$fallbackAction = explode(',', $modSettings['integrate_fallback_action']);

			// Sorry, only one fallback action is needed.
			$fallbackAction = $fallbackAction[0];

			$call = call_helper($fallbackAction, true);

			if (!empty($call))
				return $call;
		}

		// No fallback action, huh?
		else
			fatal_lang_error('not_found', false, array(), 404);
	}

	// Otherwise, it was set - so let's go to that action.
	require_once($sourcedir . '/' . $actionArray[$_REQUEST['action']][0]);

	// Do the right thing.
	return call_helper($actionArray[$_REQUEST['action']][1], true);
}
?>