<?php
$i = $main_indent;
// initialize links first because it sets a bunch of constants (url-related)
if (!$links->initialize($nav_links_indent)) {
	require_once($doc_root.'includes/installer.php');

	exit();
}

// now we have our constants! we can use them
if (ENABLE_EMAIL) {
	require_once($doc_root.'includes/classes/class.phpmailer.php');
}
$print = return_link_variable('print','');
if (isset($_GET["page_type"])) {
	$page_type = $_GET["page_type"];
} else {
	$page_type = 0;
}
if (!isset($_GET['page_id'])) $_GET['page_id'] = 0;
switch ($page_type) {
case 1: // /members directory
	// First check if visitor is a un-validated member
	if (isset($_SESSION["member_validated"]) and $_SESSION["member_validated"] == 0 and $_GET["page_id"] != 2) {
		$title = "Account not validated";
		$main_html = $main_indent."<h1>Your account is not validated</h1><br />\n";
		$main_html .= $main_indent."Please allow time for the admin to activate it.<br />\n";
		break;
	}
	// Check if member's account is suspended
	if (isset($_SESSION["member_suspended"]) and $_SESSION["member_suspended"] == 1 and $_GET["page_id"] != 2) {
		$title = "Account suspended";
		$main_html = $main_indent."<h1>Your account has been suspended</h1><br />\n";
		$main_html .= $main_indent."The following reason was given:<br /><br />".$_SESSION["member_suspended_message"]."\n";
		break;
	}
	// Security: Visitors must login if page_id is other than 1.
	if (user_type() == 0) {
		if ($_GET["page_id"] != 1) {
			$restricted_page = true;
			$title = SITE_NAME." restricted page";
			$main_html = $main_indent."<h1>Restricted page</h1><br />\n";
			$main_html .= $main_indent."Please login to continue<br />\n";
			break;
		}
	}
	// If a member trys to reach the register page they will go to members root instead
	if (user_type() != 0 and $_GET["page_id"] == 1) {
		unset($_GET["page_id"]);
	}
	// Now stop non-admins from reaching an admin page
	// (admin pages will start at a page id of 100)
	if (user_type() == 1 and $_GET["page_id"] > 99) {
		$title = SITE_NAME." restricted page";
		$main_html = $main_indent."<h1>Restricted Page</h1><br />\n";
		$main_html .= $main_indent."You do not have administrative privileges <br /><br />Please contact the admin <a href=\"mailto:".ADMIN_EMAIL."\">here</a>\n";
		break;
	}
	// Now fetch the member page (if any)
	switch ($_GET["page_id"]) {
	case 1: // /members/register
		require_once('includes/pages/register.php');
		break;
	case 2: // /members/edit_account/
		require_once('includes/pages/edit_account.php');
		break;
	case 3: // transaction history
		require_once('includes/pages/transaction_history.php');
		break;
	case 4: // transactions
		require_once('includes/pages/member_buy.php');
		break;
	case 5: //noticeboard
		require_once('includes/pages/member_noticeboard.php');
		break;
	case 7: //articles
		require_once('includes/pages/member_articles.php');
		break;
	case 8: //events
		require_once('includes/pages/member_events.php');
		break;
	case 9: //faq
		require_once('includes/pages/member_faq.php');
		break;
	case 10: //links
		require_once('includes/pages/member_links.php');
		break;
	case 11: //edit comment
		require_once('includes/pages/edit_comment.php');
		break;
	case 12: //edit comment
		require_once('includes/pages/edit_categories.php');
		break;
	// admin pages
	case 100:
		require_once('includes/pages/bulk_transactions.php');
		break;
	case 101:
		require_once('includes/pages/admin_lets_tools.php');
		break;
	case 102: // Validate articles
		require_once('includes/pages/form_settings.php');
		break;
	break;
	case 103: // Validate events
		require_once('includes/pages/lets_settings.php');
		break;
	break;
	case 104: // Validate events
		require_once('includes/pages/site_structure.php');
		break;
	break;
	case 105: // Validate events
		require_once('includes/pages/validate_articles.php');
		break;
	break;
	case 106: // Site settings
		require_once('includes/pages/website_settings.php');
		break;
	break;
	case 107: // Validate events
		require_once('includes/pages/validate_events.php');
		break;
	break;
	case 108: // Log
		require_once('includes/pages/log.php');
		break;
	break;
	case 109: // Send Email
		require_once('includes/pages/send_email.php');
		break;
	break;
	default: // /members/ (home)
		require_once('includes/pages/member_home.php');
		break;
	}
	break;
	
case 2: // /noticeboard
	require_once('includes/pages/noticeboard.php');
	break;
case 3: // /articles
	require_once('includes/pages/article.php');
	break;
case 4: // /events
	require_once('includes/pages/events.php');
	break;
case 5: // /faq
	require_once('includes/pages/faq.php');
	break;
case 6: // /links
	require_once('includes/pages/links.php');
	break;
case 7: // /member_list
	if (empty($_GET["page_id"])) {
		require_once('includes/pages/member_list.php');
		break;
	} else {
		require_once('includes/pages/member_page.php');
		break;
	}
case 10: // extra page
	require_once('includes/pages/extra_pages.php');
	break;
case 11: // contact
	require_once('includes/pages/contact.php');
	break;
case 12: // search
	require_once('includes/pages/search.php');
	break;
case 13: // lost password
	require_once('includes/pages/lost_password.php');
	break;
case 14: // login
	require_once('includes/pages/login.php');
	break;
case 15: // help
	require_once('includes/pages/help.php');
	break;
case 99:
	$title = SITE_NAME." home";
	$main_html = $main_indent."<h1>Your page was not found</h1><br />\n";
	break;
	
default:
	if (!empty($errors)) {
		$title = "An error has occurred";
		$main_html = $i.$errors;
	} else {
		require_once('includes/pages/home_page.php');
	}
	break;
break;
}
if (!isset($disable_print_page)) $disable_print_page = false;
if (!$print and !$disable_print_page) {
	if (strpos($_SERVER['REQUEST_URI'],'?')) {
		$print_button = $print_button_indent.'<span class="print_button"><a href="'.rtrim(URL,'/').$_SERVER['REQUEST_URI'].'&print=1'.post_to_get().append_url(' ?').'">Print</a> this page</span>'."\n";
	} else {
		$print_button = $print_button_indent.'<span class="print_button"><a href="'.rtrim(URL,'/').$_SERVER['REQUEST_URI'].'?print=1'.post_to_get().append_url(' ?').'">Print</a> this page</span>'."\n";
	}
} elseif ($print and !$disable_print_page){
	$print_button = '';
	$cleaned_return_url = '';
	if (strpos($_SERVER['REQUEST_URI'],'?print=1&')) {
		$tmp_arr = explode('?print=1&',$_SERVER['REQUEST_URI']);
		$cleaned_return_url = $tmp_arr[0].'?'.$tmp_arr[1];
	} elseif (strpos($_SERVER['REQUEST_URI'],'?print=1')) {
		$cleaned_return_url = str_replace('?print=1','',$_SERVER['REQUEST_URI']);
	} elseif (strpos($_SERVER['REQUEST_URI'],'&print=1')) {
		$cleaned_return_url = str_replace('&print=1','',$_SERVER['REQUEST_URI']);
	} else {
		$cleaned_return_url = $_SERVER['REQUEST_URI'];
	}

	$message = $i.'<a href="'.rtrim(URL,'/').$cleaned_return_url.post_to_get().append_url(' ?').'">Cancel Print Page</a><br /><br />'."\n";
}

// finish building $styles
$styles .= $style->style_footer();

//*******************************************************************
//		Persistent HTML (sidebar or other)

// login html
if (PERSISTENT_HTML_LOGIN) {
	$login_html = login_html($login_html_indent,$restricted_page);
}
if (PERSISTENT_HTML_NOTICEBOARD) {
	if (!isset($noticeboard)) {
		require_once('includes/classes/noticeboard.class.php');
		$noticeboard = new noticeboard;
	}
	$noticeboard_sidebar = $noticeboard->sidebar($noticeboard_sidebar_indent,'Latest '.ucwords(NOTICEBOARD_NAME_PLURAL));
}
if (PERSISTENT_HTML_ARTICLES) {
	if (!isset($articles)) {
		require_once('includes/classes/articles.class.php');
		$articles = new articles;
	}
	$articles_sidebar = $articles->sidebar($articles_sidebar_indent,'Latest '.ucwords(ARTICLES_NAME_PLURAL));
}
if (PERSISTENT_HTML_EVENTS) {
	if (!isset($events)) {
		require_once('includes/classes/events.class.php');
		$events = new events;
	}
	$events_sidebar = $events->sidebar($events_sidebar_indent,'Upcomming '.ucwords(EVENTS_NAME_PLURAL));
}
if (PERSISTENT_HTML_FAQ) {
	if (!isset($faq)) {
		require_once('includes/classes/faq.class.php');
		$faq = new faq;
	}
	$faq_sidebar = $faq->sidebar($faq_sidebar_indent,'Latest '.ucwords(FAQ_NAME_PLURAL));
}
if (PERSISTENT_HTML_LINKS) {
	if (!isset($lets_links)) {
		require_once('includes/classes/link.class.php');
		$lets_links = new lets_links;
	}
	$links_sidebar = $lets_links->sidebar($links_sidebar_indent,'Latest '.ucwords(LINKS_NAME_PLURAL));
}
if (PERSISTENT_HTML_SEARCH) {
	$search_sidebar = search_sidebar($search_sidebar_indent);
}
//*******************************************************************
$nav_html = $links->xhtml();


?>