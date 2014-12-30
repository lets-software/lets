<?php
/***********************************************************************************************************************************
*		Page:			transaction_history.php
*		Access:			Member
*		Purpose:		View a member's transactions over a given time period
*		HTML Holders:	$main_html		:		Entire Contents
*		Template File:																											*/
			$template_filename 			= 		'default';
/*		Classes:																												*/
			require_once('includes/classes/transactions.class.php');
			$transactions = new transactions;
/*		Indentation:																											
			Set $main_indent on index.php
/*		Disable "Print Page" Link on this page:																					*/
			$disable_print_page			=		false;
/*		CSS Files Called by script:																								*/
		if (!$print) {
			$styles 					.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/transaction_search_form.css);\n";
			$styles 					.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/results_table.css);\n";
/*		Dynamic Styling:																										*/
			/* if (FONT_SIZE > 14) {
				$local_font_size 		= 		14;
			} else {
				$local_font_size 		= 		FONT_SIZE;
			} 
			$style->dynamic_elements 	.= 		" table {font-size: ".$local_font_size."px;}\n"; */
			$style->dynamic_elements 	.= 		" th.h {background-color:".TAB_COLOUR."; color:".LINK_COLOUR.";}\n";
			// $style->dynamic_elements 	.= 		" td {border-bottom:1px solid ".TAB_BORDER_COLOUR."; border-right: 1px solid ".TAB_COLOUR."; }\n";
			$style->dynamic_elements 	.= 		" span.sale {color:green}\n";
			$style->dynamic_elements 	.= 		" span.purchase {color:red}\n";
		}
/*		Javascript:																												*/
/*		Set Universal Page elements:																							*/
			$links->page_info(1,3);
			$page_name 					= 		$links->name;
			$url 						= 		$links->url;
			$blurb 						= 		$links->body;
/*		Page Title:																												*/
			$title 						= 		SITE_NAME.' '.$page_name;
/*		Page Heading																											
			set below depending on who's transaction history is viewed
/*
************************************************************************************************************************************/

//****************************************
// the first part of this page establishes who is being viewed, who is doing the viewing and whether or not it's allowed
$member_id_to_view = 0;
if (isset($_POST['member_id_to_view']) and (user_type() == 2 or ALLOW_VIEW_OTHER_TRANSACTION_HISTORY)) {
	if ($_POST['member_id_to_view']) {
		$member_id_to_view = $_POST['member_id_to_view'];
	} else {
		$member_id_to_view = $_SESSION["member_id"];
	}
} elseif ((user_type() == 2 or ALLOW_VIEW_OTHER_TRANSACTION_HISTORY) and isset($_GET['member_id_to_view'])) {
	if ($_GET['member_id_to_view']) {
		$member_id_to_view = $_GET['member_id_to_view'];
	} else {
		$member_id_to_view = $_SESSION["member_id"];
	}
} else {
	$member_id_to_view = $_SESSION["member_id"];
}

$name = '';
if (!$user->build_dataset($member_id_to_view)) {
	if (!$mysql->num_rows('SELECT accountID FROM accounts WHERE accountID = '.$member_id_to_view.' LIMIT 1')) {
		if ($mysql->num_rows) {
			$main_html .= $i.'<span class="message">This acount could not be accessed</span><br /><br />'."\n";
		} else {
			$main_html .= $i.'<span class="message">The account was not found</span><br /><br />'."\n";
		}
	} else {
		$main_html .= $i.'<span class="message">The account was not found</span><br /><br />'."\n";
	}
} else {
	$name = $user->first_name;
}

if (!$name) {
	$title = SITE_NAME.' - '.ucwords(TRANSACTION_NAME_SINGULAR).' History';
	$main_html = $i.'<h1>'.SITE_NAME.' - '.ucwords(TRANSACTION_NAME_SINGULAR)." History</h1>\n";
} else {
	$title = SITE_NAME.' - '.$name.'\'s '.ucwords(TRANSACTION_NAME_SINGULAR).' History';
	$main_html = $i.'<h1>'.SITE_NAME.' - '.$name.'\'s '.ucwords(TRANSACTION_NAME_SINGULAR)." History</h1>\n";
}

if (isset($_POST['start_year'])) {
	$start_year = $_POST['start_year']; 
} else {
	$start_year = 2000;
}
if (isset($_POST['start_day'])) {
	$start_day = $_POST['start_day']; 
} else {
	$start_day = $date['day'];
}
if (isset($_POST['start_month'])) {
	$start_month = $_POST['start_month']; 
} else {
	$start_month = $date['month'];
}

$transaction_type = return_link_variable("transaction_type",'');
if (isset($_POST['end_day'])) $end_day = $_POST['end_day']; else $end_day = $date['day'];
if (isset($_POST['end_month'])) $end_month = $_POST['end_month']; else $end_month = $date['month'];
if (isset($_POST['end_year'])) $end_year = $_POST['end_year']; else $end_year = $date['year'];
if (isset($_POST['transaction_type'])) $transaction_type = $_POST['transaction_type'];
if (isset($_POST['order'])) $order = $_POST['order']; else $order = 2;
if (!$transaction_type) {
	$transaction_type = 1;
}
//****************************************
//	Main Page

$main_html .= $transactions->search_html($i,$url,$start_day,$start_month,$start_year,$end_day,$end_month,$end_year,$transaction_type,$member_id_to_view,$order);
$transaction_history_html = $transactions->member_history_array($i,$member_id_to_view,$start_day,$start_month,$start_year,$end_day,$end_month,$end_year,$transaction_type,$order);
if ($transaction_history_html) {
	$main_html .= $transaction_history_html;
} else {
	if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('Failed to generate '.strtolower(TRANSACTION_NAME_SINGULAR).' history on transaction_history.php');
	if (ENABLE_ERROR_LOG) log_error('Failed to generate '.strtolower(TRANSACTION_NAME_SINGULAR).' history on transaction_history.php<br />Error:'.$comments->error);
}


?>