<?php // Translate.php

define('LOCALE_DIR', LETS_ROOT .'locales');
define('DEFAULT_LOCALE', 'en_US');

require_once(LETS_ROOT.'includes/lib/gettext/gettext.inc');

$supported_locales = array('en_US', 'sr_CS', 'fr_FR');
$encoding = 'UTF-8';

$locale = $_SESSION['lang'];//(isset($_GET['lang']))? $_GET['lang'] : DEFAULT_LOCALE;



// gettext setup
T_setlocale(LC_MESSAGES, 'messages');
// Set the text domain as 'messages'
$domain = $locale;
T_bindtextdomain($domain, LOCALE_DIR);
T_bind_textdomain_codeset($domain, $encoding);
T_textdomain($domain);

header("Content-type: text/html; charset=$encoding");