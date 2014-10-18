<?
/***********************************************************************************************************************************
*		Page:			member_buy.php
*		Access:			Member
*		Purpose:		Allow a member to make a transaction
*		HTML Holders:	$main_html	:	Entire Contents
*		Template File:																											*/
			$template_filename 			= 		'default';
/*		Classes:																												*/
		if (ENABLE_NOTICEBOARD) {
			require_once('includes/classes/transactions.class.php');
			$transactions 				= 		new transactions;
			require_once('includes/classes/noticeboard.class.php');
			$noticeboard 				= 		new noticeboard;
		}
/*		Indentation:																											
			Set $main_indent on index.php
/*		Disable "Print Page" Link on this page:																					*/
			$disable_print_page			=		true;
/*		CSS Files Called by script:																								*/
		if (!$print) {
			
		}
/*		Dynamic Styling:																										*/
/*		Javascript:																												*/
/*		Set Universal Page elements:																							*/
/*		Page Title:																												*/
			$title 						= 		SITE_NAME.' '.MEMBERS_NAME.' Home';
/*		Page Heading																											*/
			$main_html 					= 		$i."<h1>".SITE_NAME.' '.MEMBERS_NAME." Home</h1>\n";
/*
************************************************************************************************************************************/

$main_html .= $site->return_config_html($i,'member_message')."<br /><br />\n";
if (ENABLE_NOTICEBOARD) {
	if ($transactions->balance($_SESSION["member_id"])) {
		$main_html .= $i.'Your current balance is <strong>'.number_format($transactions->balance,2,'.',',')."</strong> ".ucwords(CURRENCY_NAME)."<br /><br />\n";
	} else {
		$main_html .= $i.$transactions->error."<br />\n";
	}
}
$main_html .= member_quicklinks($i,$_SESSION['member_id']);

if (ENABLE_NOTICEBOARD) {
	$auction_info = $noticeboard->winning_auctions($i,$_SESSION['member_id']);
	if ($auction_info) {
		$main_html .= $i."<br />\n".$auction_info;
	}
}

if (user_type() == 2) {
	$validation_html = '';
	// check if unvalidated submissions
	$mysql->num_rows('SELECT accountID FROM accounts WHERE validated = 0');
	if ($mysql->num_rows and $links->build_url(1,105)) {
		$validation_html .= $i.'<a href="'.URL.MEMBER_LIST_URL.'/?member_search_type=2'.append_url(' ?').'">'.$mysql->num_rows.' '.ucwords(MEMBERS_NAME_PLURAL)."</a><br />\n";
	}
	if (ENABLE_ARTICLES) {
		$mysql->num_rows('SELECT articleID FROM articles WHERE validated = 0');
		if ($mysql->num_rows and $links->build_url(1,105)) {
			$validation_html .= $i.'<a href="'.URL.$links->complete_url.append_url(0).'">'.$mysql->num_rows.' '.ucwords(ARTICLES_NAME_PLURAL)."</a><br />\n";
		}
	}
	if (ENABLE_EVENTS) {
		$mysql->num_rows('SELECT eventID FROM events WHERE validated = 0 AND (start_year > '.$GLOBALS['date']['year'].' OR 
							(start_year = '.$GLOBALS['date']['year'].' AND start_month > '.$GLOBALS['date']['month'].') OR 
							(start_year = '.$GLOBALS['date']['year'].' AND start_month = '.$GLOBALS['date']['month'].' AND start_day > '.$GLOBALS['date']['day'].') OR 
							(start_year = '.$GLOBALS['date']['year'].' AND start_month = '.$GLOBALS['date']['month'].' AND start_day = '.$GLOBALS['date']['day'].' AND start_hour > '.$GLOBALS['date']['hour'].'))');
		if ($mysql->num_rows and $links->build_url(1,107)) {
			$validation_html .= $i.'<a href="'.URL.$links->complete_url.append_url(0).'">'.$mysql->num_rows.' '.ucwords(EVENTS_NAME_PLURAL)."</a><br />\n";
		}
	}
	if (ENABLE_FAQ) {
		$mysql->num_rows('SELECT faqID FROM faq WHERE validated = 0');
		if ($mysql->num_rows and $links->build_url(1,9)) {
			$validation_html .= $i.'<a href="'.URL.$links->complete_url.append_url(0).'">'.$mysql->num_rows.' '.ucwords(FAQ_NAME_PLURAL)."</a><br />\n";
		}
	}
	if (ENABLE_LINKS) {
		$mysql->num_rows('SELECT linkID FROM links WHERE validated = 0');
		if ($mysql->num_rows and $links->build_url(1,10)) {
			$validation_html .= $i.'<a href="'.URL.$links->complete_url.append_url(0).'">'.$mysql->num_rows.' '.ucwords(LINKS_NAME_PLURAL)."</a><br />\n";
		}
	}
	if ($validation_html) {
		$main_html .= $i."<strong>Attention Admin</strong>, the following submissions are waiting for your validation:<br />\n";
		$main_html .= $validation_html;
	}
	
	
	
	
	
	
	
}








?>