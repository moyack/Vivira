<?php
// Version: 1.41; ToolsHelp

global $helptxt;

// Admin help messages
$helptxt['dont_use_lightbox'] = 'If <strong>enabled</strong>, images and attaches in messages can be displayed enlarged.<br>
If you have more than one image in a message, it will be shown like a gallery.<br>You can also disable any image or attach by adding a <strong>expand=off</strong> to the IMG or ATTACH bbc code, like [img expand=off] or [attach expand=off]';

$helptxt['image_download'] = 'If enabled, members with the rights "download attachments" can download attached Images.<br>
For more secure you should not enabled this option, so use this with care!';
$helptxt['image_addwatermark'] = 'if enabled, a small "Watermark" image is inserted on the lower-right edge in the downloaded image.<br>
	For Administrators is this option NOT active!';
$helptxt['watermark_image'] = 'Enter the name of the watermark image (.gif or .png file).<br>
	<strong>Note:</strong> Copy the image that you will use in the folder <strong>watermark</strong>.';

$helptxt['add_favicon_to_links'] = 'This settings add a favicon (if the site have one) to each link with the class "bbc_link".';

$helptxt['ecl_enabled'] = 'This make your PortaMx Forum compatible with the <strong>EU Cookie Law</strong>.<br>
If enabled, any visitor (except spider) must accept the storage of cookies before he can use the forum (completely).<br>
<strong>In order to use ECL, cache MUST be enabled. If the cache is deactivated, the ECL mode is automatically deactivated!</strong>';
$helptxt['ecl_nomodal'] = 'Normaly the Forum are not accessible until ECL is accepted.<br>
If you enable the <strong>none modal mode</strong>, the site is accessible and a Vistor can simple browse the forum.
 <strong>Note, that is this case any additional modification or adsense content can store cookies!</strong>';
$helptxt['ecl_nomodal_mobile'] = 'On Mobile devices the ECL mode is normaly switched to <strong>modal mode</strong>. Here you can disable this, so the <strong>none modal mode</strong> is used.';
$helptxt['ecl_topofs'] = 'Here you can set the top position for the ECL window. A value of 40 is a good choice.';

$helptxt['gdpr_enabled'] = 'This settings make your PortaMx Forum compatible with the <a href="https://gdpr-info.eu/"><strong>EU General Data Protection Regulation (GDPR)</strong></a>.<br>
 You must complete all subsequent information about you (website owner) as well as the postal and e-mail details of the hosting provider you are using.
 These data are transferred to the page to be called at runtime.';
$helptxt['gdpr_last_update'] = 'This date is saved in the user-table and will be used to decide if the user needs to re-display the GDPR agreement.
 This is e.g. the case if you have changed the content of your agreement.
 This field <strong>MUST</strong> be completed, the default is 2018-05-25 (entry into force of the GDPR).';

$helptxt['lang_autodetect'] = 'This function is used to display the pages (as far as possible) in the typical language of a visitor.
 <strong>To use this feature, the cache MUST be enabled. If Cache is deactivated, this function will be deactivated automatically!</strong>';
$helptxt['geoip_enabled'] = 'If the Browser language detection is not possible, you can use the service <strong>GEOIP</strong> to recognize the typical language of a visitor.';
$helptxt['geoip_sslkey'] = 'If you have buyed a <a href="http://www.geoplugin.com/webservices/ssl"><strong>SSL Key from Geoip</strong></a> (12 € per year), enter your key here.';
$helptxt['langdetect_log'] = 'When enabled, each language detection (dayname, time, detection mode, language code, IP address, user agent) is logged.
 For this purpose, the directory <strong>LangDetect</strong> is automatically created in the base directory of your forum.
 You will then find a log file for each day (<strong>data-yyyy-mm-dd.log</strong>) in this folder.
 Log files older then 7 days are removed automatically by the Task <strong>Daily Maintenance</strong>.';

$helptxt['portal_enabled'] = 'This settings will enable the integrated <strong>Portal System</strong>.<br>
The Portal System expand your forum with many functions like a Frontpage, Panels on left, right, top and bottom and a featured category/article system.';
$helptxt['webkit_scrollbars'] = 'If this enabled, the Browser Scollbars becomes a custom design.
 You can change this design by modify the file <strong>webkit.css</strong> in the Themes css folder.
 Note that this only works with a Browser they use the Webkit engine.
 Currently this works with the Browser <strong>Chrome</strong> and <strong>Opera</strong>.';

$helptxt['google_site_verification'] = 'If you are logged in to Google Search Console and have signed up for a "Property" for your site, you can enter the "Meta Tag" key here.
 This is inserted as <strong>&lt;meta name="google-site-verification" content="your key" /&gt;</strong> in your site header.';

$helptxt['imprint_enabled'] = 'Here you can activate an "About this site" (imprint) for your forum.
 This will use the data you entered above (your full name, street and house number, zip code, location, country and your e-mail address).';
?>