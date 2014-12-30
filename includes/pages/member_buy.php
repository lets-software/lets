<?php
/***********************************************************************************************************************************
*		Page:			member_buy.php
*		Access:			Member
*		Purpose:		Allow a member to make a transaction
*		HTML Holders:	$main_html	:	Entire Contents
*		Template File:																											*/
			$template_filename 			= 		'default';
/*		Classes:																												*/
			require_once('includes/classes/transactions.class.php');
			$transactions 				= 		new transactions;
/*		Indentation:																											
			Set $main_indent on index.php
/*		Disable "Print Page" Link on this page:																					*/
			$disable_print_page			=		true;
/*		CSS Files Called by script:																								*/
		if (!$print) {
			$styles 					.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/transaction_form.css);\n";
		}
/*		Dynamic Styling:																										*/
/*		Javascript:																												*/
/*		Set Universal Page elements:																							*/
			$links->page_info(1,4);
			$page_name 					= 		$links->name;
			$url 						= 		$links->url;
			$blurb 						= 		$links->body;
/*		Page Title:																												*/
			$title 						= 		SITE_NAME.' '.$page_name;
/*		Page Heading																											*/
			$main_html 					= 		$i."<h1>".SITE_NAME.' '.$page_name."</h1>\n";
/*
************************************************************************************************************************************/

// Actions
$message = process_transaction();
if ($message) {
	$main_html .= $i.'<span class="message">'.indent_variable($i,$message).'</span><br /><br />'."\n";
}
// Main Page
$main_html .= $transactions->html($i,'member','',$url);


?>