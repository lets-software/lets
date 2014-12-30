<?php
/***********************************************************************************************************************************
*		Page:			bulk_transactions.php
*		Access:			Admin
*		Purpose:		Bulk transactions
*		HTML Holders:	$main_html		:		Entire Contents
*		Template File:																											*/
			$template_filename 			= 		'default';
/*		Classes:																												*/
			require_once('includes/classes/transactions.class.php');
			$transactions 				= 		new transactions;
/*		Indentation:																											
			Set $main_indent and $blurb_indent on index.php
/*		Disable "Print Page" Link on this page:																					*/
			$disable_print_page			=		true;
/*		CSS Files Called by script:																								*/
		if (!$print) {
			$styles 					.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/results_table.css);\n";
			$styles 					.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/bulk_transaction_form.css);\n";
/*		Dynamic Styling:																										*/
			/* if (FONT_SIZE > 14) {
				$local_font_size 		= 		14;
			} else {
				$local_font_size 		= 		FONT_SIZE;
			} 
			$style->dynamic_elements 	.= 		" table {font-size: ".$local_font_size."px;}\n"; */
			$style->dynamic_elements 	.= 		" th.h {background-color:".TAB_COLOUR."; color:".LINK_COLOUR.";}\n";
			// $style->dynamic_elements 	.= 		" td {border-bottom:1px solid ".TAB_BORDER_COLOUR."; border-right: 1px solid ".TAB_COLOUR."; }\n";
		}
/*		Javascript:																										*/

/*		Set Universal Page elements:																							*/
			$links->page_info(1,100);
			$page_name 					= 		$links->name;
			$url 						= 		$links->url;
			$blurb 						= 		$links->body;
/*		Page Title:																												*/
			$title 						= 		SITE_NAME.' '.$page_name;
/*		Page Heading																											*/
			$main_html 					= 		$i."<h1>".SITE_NAME.' '.$page_name."</h1>\n";
/*
************************************************************************************************************************************/


$fresh = true;
// ****************************************
//			Actions
// ****************************************
if (!isset($_POST['submit'])) $_POST['submit'] = '';
	if ($_POST['submit'] == 'Submit '.ucwords(TRANSACTION_NAME_PLURAL) or $_POST['submit'] == 'Confirm '.ucwords(TRANSACTION_NAME_PLURAL) or $_POST['submit'] == 'Edit '.ucwords(TRANSACTION_NAME_PLURAL)) {
		if ($transactions->process_bulk_post($i,$url)) {
			if ($_POST['submit'] == 'Submit '.ucwords(TRANSACTION_NAME_PLURAL) and BULK_TRADING_CONFIRM) {
				if ($transactions->num_bulk_transactions == 1) {
					$main_html .= $i.'<span class="message">1 '.ucwords(TRANSACTION_NAME_SINGULAR).' Ready to Process:</span><br /><br />'."\n";
				} else {
					$main_html .= $i.'<span class="message">'.$transactions->num_bulk_transactions.' '.ucwords(TRANSACTION_NAME_PLURAL).' Ready to Process:</span><br /><br />'."\n";
				}
				$main_html .= $i.$transactions->bulk_summary;
				$main_html .= $i.$transactions->bulk_confirm_form;
				$fresh = false;
			}
			if (($_POST['submit'] == 'Submit '.ucwords(TRANSACTION_NAME_PLURAL) and !BULK_TRADING_CONFIRM) or $_POST['submit'] == 'Confirm '.ucwords(TRANSACTION_NAME_PLURAL)) {
				$transactions->process_bulk_post($i,$url,true);
				if ($transactions->num_bulk_transactions_processed) {
					if ($transactions->num_bulk_transactions_processed == 1) {
						$main_html .= $i.'<span class="message">1 '.ucwords(TRANSACTION_NAME_SINGULAR).' Processed:</span><br /><br />'."\n";
					} else {
						$main_html .= $i.'<span class="message">'.$transactions->num_bulk_transactions_processed.' '.ucwords(TRANSACTION_NAME_PLURAL).' Processed:</span><br /><br />'."\n";
					}
					unset($transactions->bulk_post);
				} else {
					$main_html .= $i.'<span class="message">No '.ucwords(TRANSACTION_NAME_PLURAL).' Processed:</span><br /><br />'."\n";
				}
				$main_html .= $transactions->bulk_summary;
				$main_html .= $i.'<br />'."\n";
			}
		} else {
			$main_html .= $i.$transactions->bulk_submission_errors."<br />\n";
		}
	}
// ****************************************
//			main page
// ****************************************
	
	if ($fresh) {
		$main_html .= $transactions->bulk_trading_form($i,$url,10);
	}





?>