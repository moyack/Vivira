<?php
// Version: 1.41; EclPrivacynotice

/**
	Additional informations for this file format:
	We have 3 tokens, they replaced at run time:
	@site@   - replace with the Forum name
	@host@   - replaced with the Domain name
	@cookie@ - replaced with the cookie you have setup
*/

/* Header text */
$txt['ecl_header'] = '
	To the legislation of the <a href="https://ec.europa.eu/info/law/law-topic/data-protection_en" target="_blank"rel="noopener" class="ecl_link">European Union</a>
	to comply with the rules on the protection of personal data, we are required to inform users accessing "@host@" about the cookies that this site uses and the
	information they contain and also provide them with the means to "opt-in" - in other words, permit the site to set cookies.<br>
	Cookies are small files that are stored by your browser and all browsers have an option whereby you can inspect
	the content of these files and delete them if you wish.<br><br>
	The following table details the name of each cookie, where it comes from and what we know about the information that cookie stores:<br><br>';

/*
	All cookie informations
	if you have more cookies, add them at the end with the same format
*/
$txt['ecl_headrows'] = array(
	array(
		'<div><strong>Cookie</strong></div>',
		'<div><strong>Origin</strong></div>',
		'<div><strong>Persistency</strong></div>',
		'<div><strong>Usage</strong></div>',
	),
	array(
		'eclauth',
		'@host@',
		'Expires after 30 days',
		'This cookie contains the text "LiPF_cookies_authorised".
			Without this cookie, the Website software is prevented from setting any cookies.',
	),
	array(
		'@cookie@',
		'@host@',
		'Expires according to user-chosen session duration',
		'If you log-in as a member of this site, this cookie contains your user name, an encrypted hash of
			your password and the time you logged-in. It is used by the Website software to ensure that features such as indicating
			new Forum and Private messages are indicated to you.',
	),
	array(
		'PHPSESSID',
		'@host@',
		'Current session only',
		'This cookie contains a unique Session Identification value. It is set for both members and
			non-members (guests) and it is essential for the site software to work completely. This cookie is not persistent
			and should be automatically removed when you close the browser window.',
	),
	array(
		'cbtstat{ID}',
		'@host@',
		'Current session only',
		'These cookies are set to records the expand/collapse state for the CBT Navigator block content.',
	),
	array(
		'poll{ID}',
		'@host@',
		'Current session only',
		'These cookies are set to records the id for the current poll for a multiple Poll block.',
	),
	array(
		'upshr{ID}',
		'@host@',
		'Current session only',
		'These cookies are set to records your display preferences for the sides, if a panel
			or individual block is collapsed or expanded.',
	),
	array(
		'oFader{ID}',
		'@host@',
		'Current session only',
		'These cookies are set to records the state for the Opac-Fader block.',
	),
	array(
		'shout{ID}',
		'@host@',
		'Current session only',
		'These cookies are set to records the current state of the Shout box block.',
	),
	array(
		'language',
		'@host@',
		'Current session only',
		'This cookie is only used for guests and contains the current language settings.',
	),
	array(
		'screen',
		'@host@',
		'Current session only',
		'This cookie contains the orientation and width of the screen and is only used on Mobile Devices.',
	),
	array(
		'upshrIC',
		'@host@',
		'Current session only',
		'This cookie contains the state of the "@site@ - Info Center" and is set only for guests.',
	),
	array(
		'YOfs',
		'@host@',
		'Page load time',
		'These cookies will probably never see you.
			It is used to restore the vertical screen position, if you click on a Page number on the Frontpage.
			The cookies is deleted when the desired page is loaded.',
	),
);

/* footer header */
$txt['ecl_footertop'] = '
	<span><strong>Notes:</strong></span><br>';

/* footer informations */
$txt['ecl_footrows'] = array(
// Remove the comment (/* and */) if you use Google Adsense
/*
	'We use Google AdSense, therefore cookies are set by Google, to analyze visits to our website and personalize content and ads.
		Informations on the use of our website are anonymous distributed to our partner for social media, to adapt advertising and analysis.',
*/
	'If you are accessing this site using someone else\'s device, please ask the owner\'s permission before
		accepting cookies.',

	'Your browser provides you with the ability to inspect all cookies stored on your device. In addition your browser
		is responsible for removing "current session only" cookies and those that have expired; if your browser is
		not doing this, you should report the matter to your browser\'s authors.',
);

/* last line for ecl privacy */
$txt['ecl_footer'] = '
	<br>For further and fuller information about cookies and their use, please visit
		<a target="_blank" rel="noopener" class="ecl_link" href="http://www.allaboutcookies.org">All About Cookies</a>';
?>