<?php
/***********************************************************************************************************************************
*		Page:			admin_lets_tools.php
*		Access:			Admin
*		Purpose:		Category admin for Events, Noticeboard and Articles
*						Global Trades where the entire membership buys from a single member
*						Bulk membership tools
*						Transaction Reversal
*		HTML Holders:	$main_html		:		Entire Contents
*		Template File:																											*/
			$template_filename 			= 		'default';
/*		Classes:		transaction for global trades and reversals
						articles, noticeboard and events to edit their categories												*/
			require_once('includes/classes/transactions.class.php');
			$transactions 				= 		new transactions;
			require_once('includes/classes/articles.class.php');
			$articles 					= 		new articles;
			require_once('includes/classes/noticeboard.class.php');
			$noticeboard 				= 		new noticeboard;
			require_once('includes/classes/events.class.php');
			$events 					= 		new events;
/*		Indentation:																											
			Set $main_indent and $blurb_indent on index.php
/*		Disable "Print Page" Link on this page:																					*/
			$disable_print_page			=		true;
/*		CSS Files Called by script:																								*/
		if (!$print) {
			$styles 					.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/lets_tools.css);\n";
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
		}
/*		Javascript:
			Included at end of file																								*/
/*		Set Universal Page elements:																							*/
			$links->page_info(1,101);
			$page_name 					= 		$links->name;
			$url 						= 		$links->url;
			$blurb 						= 		$links->body;
/*		Page Title:																												*/
			$title 						= 		SITE_NAME.' '.$page_name;
/*		Page Heading																											*/
			$main_html 					= 		$i."<h1>".SITE_NAME.' '.$page_name."</h1>\n";
/*
************************************************************************************************************************************/

$confirmation_screen = false;

// ******************************************************
//            start of categories routine
$table = '';
$field = '';
$name = '';

if (!isset($_POST['event'])) $_POST['event'] = 0;
if (!isset($_POST['noticeboard'])) $_POST['noticeboard'] = 0;
if (!isset($_POST['article'])) $_POST['article'] = 0;

if ($_POST['event']) {
	$table = 'event_categories';
	$field = 'event_categoryID';
	$name = 'name';
	$child_table = 'events';
	$child_field = 'event_categoryID';
	$txt = ucwords(EVENTS_NAME_SINGULAR).' Categories';
}
if ($_POST['noticeboard']) {
	$table = 'categories';
	$field = 'categoryID';
	$name = 'name';
	$child_table = 'noticeboard';
	$child_field = 'categoryID';
	$txt = ucwords(NOTICEBOARD_NAME_SINGULAR).' Categories';
}
if ($_POST['article']) {
	$table = ' article_categories';
	$field = 'art_catID';
	$name = 'art_cat';
	$child_table = 'articles';
	$child_field = 'articleID';
	$txt = ucwords(ARTICLES_NAME_SINGULAR).' Categories';
}

if (!isset($_POST['submit'])) $_POST['submit'] = '';
if ($_POST['submit'] == 'Add Category') {
	if ($table and $name and $_POST['name']) {
		$post_name = remove_slashes($_POST['name']);
		$mysql->num_rows = 0;
		$mysql->num_rows('SELECT * FROM '.$table.' WHERE '.$name." = '".addslashes($post_name)."' LIMIT 1");
		if (!$mysql->num_rows) {
			if (!$mysql->query('INSERT INTO '.$table." VALUES ('','".ucwords(addslashes($post_name))."')")) {
				log_error('Failed to add a category on admin_lets_tools.php.<br />'.$mysql->error);
			} else {
				if (ENABLE_LOG) log_action($_SESSION['member_full_name'].' added "'.ucwords($post_name).'" to '.$txt.'.');
			}
		}
	}
}
if ($_POST['submit'] == 'Edit Category') {
	if ($table and $field and $name and $_POST['name'] and $_POST['category']) {
		$post_name = remove_slashes($_POST['name']);
		if (!$mysql->query('UPDATE '.$table.' SET '.$name." = '".addslashes($post_name)."' WHERE ".$field.' = '.$_POST['category'].' LIMIT 1')) {
			log_error('Failed to edit a category on admin_lets_tools.php.<br />'.$mysql->error);
		} else {
			if (ENABLE_LOG) log_action($_SESSION['member_full_name'].' edited "'.ucwords($post_name).'" in '.$txt.'.');
		}
	}
}
if ($_POST['submit'] == 'Delete Category') {
	if ($table and $field and $child_table and $child_field and $_POST['category']) {
		$mysql->num_rows = 0;
		$mysql->num_rows('SELECT * FROM '.$child_table.' WHERE '.$child_field." = '".$_POST['category']."' LIMIT 1");
		
		if (!$mysql->num_rows) {
			if (!$mysql->query('DELETE FROM '.$table.' WHERE '.$field.' = '.$_POST['category'].' LIMIT 1')) {
				log_error('Failed to delete a category on admin_lets_tools.php.<br />'.$mysql->error);
			} else {
				if (ENABLE_LOG) log_action($_SESSION['member_full_name'].' deleted "'.remove_slashes($_POST['name']).'" from '.$txt.'.');
			}
		}
	}
}

//            end of categories routine
// ******************************************************
//            start of distribution routine

if ($_POST['submit'] == 'Submit '.ucwords(TRANSACTION_NAME_SINGULAR) or $_POST['submit'] == 'Confirm '.ucwords(TRANSACTION_NAME_SINGULAR)) {
	if (!is_numeric($_POST['amount'])) {
		$main_html .= $i.'<span class="message">Amount given is not a number</span><br /><br />'."\n";
	} elseif($_POST['amount'] < 0) {
		$main_html .= $i.'<span class="message">Amount must be positive</span><br /><br />'."\n";
	} elseif(!$_POST['description']) {
		$main_html .= $i.'<span class="message">Please provide a description</span><br /><br />'."\n";
	} elseif(!$_POST['member_id']) {
		$main_html .= $i.'<span class="message">Please provide '.a(MEMBERS_NAME_SINGULAR).' '.strtolower(MEMBERS_NAME_SINGULAR).' ID</span><br /><br />'."\n";
	} elseif(!$mysql->num_rows('SELECT accountID FROM accounts WHERE accountID = '.$_POST['member_id'].' LIMIT 1')) {
		$main_html .= $i.'<span class="message">Database Problem</span><br /><br />'."\n";
	} elseif(!$mysql->num_rows) {
		$main_html .= $i.'<span class="message">'.ucwords(MEMBERS_NAME_SINGULAR).' ID not found</span><br /><br />'."\n";
	} else {
		$xhtml_report = '';
		$_POST['description'] = remove_slashes($_POST['description']);
		if (VALIDATE_XHTML) {
			$xhtml_report = valid_XHTML($_POST['description']);
		}
		if ($xhtml_report) {
			$main_html .= $i.$xhtml_report.'<br /><br />'."\n";
		} else {
			if ($_POST['submit'] == 'Submit '.ucwords(TRANSACTION_NAME_SINGULAR)) {
				$confirmation_screen = true;
				$main_html .= $transactions->confirm_distribution_html($i,$url);
			}
			if ($_POST['submit'] == 'Confirm '.ucwords(TRANSACTION_NAME_SINGULAR)) {
				$main_html .= $i.'<span class="message">'.$transactions->distribute_account().'</span><br /><br />'."\n";
			}
		}
	}
}
if ($_POST['submit'] == 'Reverse All Global '.ucwords(TRANSACTION_NAME_PLURAL).' Made Today') {
	$transactions->undo_distributions();
}

//            end of distribution routine
// ******************************************************
//            start of bulk member routine

if ($_POST['submit'] == 'Delete All Suspended Accounts' or $_POST['submit'] == 'Restore All Suspended Accounts' or $_POST['submit'] == 'Reactivate All Deleted Accounts' or $_POST['submit'] == 'Suspend All Expired Accounts' or $_POST['submit'] == 'Renew All Expired Accounts') {
	$confirmation_screen = true;
	if ($_POST['expiry_adjustment'] and !is_numeric($_POST['expiry_adjustment'])) {
		$confirmation_screen = false;
		$main_html .= $i.'<span class="message">You entered a letter when it should have been a number (Expiry Adjustment)</span><br /><br />'."\n";
	}
	if (isset($_POST['expiry_adjustment']) and isset($_POST['new_expiry'])) {
		$confirmation_screen = false;
		$main_html .= $i.'<span class="message">Either set an expiry for all accounts or adjust each account a specific amount</span><br /><br />'."\n";
	}
	if ($confirmation_screen) {
		if ($_POST['submit'] == 'Delete All Suspended Accounts') {
			$user->bulk_membership_tools($i,$url,'delete',false);
		}
		if ($_POST['submit'] == 'Restore All Suspended Accounts') {
			$user->bulk_membership_tools($i,$url,'restore',false);
		}
		if ($_POST['submit'] == 'Reactivate All Deleted Accounts') {
			$user->bulk_membership_tools($i,$url,'reactivate',false);
		}
		if ($_POST['submit'] == 'Suspend All Expired Accounts') {
			$user->bulk_membership_tools($i,$url,'suspend',false);
		}
		if ($_POST['submit'] == 'Renew All Expired Accounts') {
			$user->bulk_membership_tools($i,$url,'renew_expiry',false);
		}
	}
	if ($confirmation_screen) {
		$main_html .= $user->bulk_summary;
		$main_html .= $user->bulk_confirm_form;
	}
}

if ($_POST['submit'] == 'Confirm') {
	if ($_POST['delete']) {
		$user->bulk_membership_tools($i,$url,'delete',true);
		if (ENABLE_LOG) log_action($_SESSION['member_full_name'].' deleted all suspended "'.strtolower(MEMBER_NAME_PLURAL));
	}
	if ($_POST['restore']) {
		$user->bulk_membership_tools($i,$url,'restore',true);
		if (ENABLE_LOG) log_action($_SESSION['member_full_name'].' restored all suspended "'.strtolower(MEMBER_NAME_PLURAL));
	}
	if ($_POST['reactivate']) {
		$user->bulk_membership_tools($i,$url,'reactivate',true);
		if (ENABLE_LOG) log_action($_SESSION['member_full_name'].' reactivated all deleted "'.strtolower(MEMBER_NAME_PLURAL));
	}
	if ($_POST['suspend']) {
		$user->bulk_membership_tools($i,$url,'suspend',true);
		if (ENABLE_LOG) log_action($_SESSION['member_full_name'].' suspended all expired "'.strtolower(MEMBER_NAME_PLURAL));
	}
	if ($_POST['renew_expiry']) {
		$user->bulk_membership_tools($i,$url,'renew_expiry',true);
		if (ENABLE_LOG) log_action($_SESSION['member_full_name'].' renewed all expired "'.strtolower(MEMBER_NAME_PLURAL));
	}
	if ($user->bulk_routine_errors) {
		$main_html .= $i.'<span class="message">'.$user->bulk_routine_errors.'</span><br /><br />'."\n";
	}
	$main_html .= $user->bulk_summary;
}
//            end of bulk member routine
// ******************************************************
//            start of reverse transaction routine

if ($_POST['submit'] == 'Reverse' and isset($_POST['transaction_id'])) {
	$mysql->num_rows('SELECT transactionID FROM transactions WHERE transactionID = '.$_POST['transaction_id'].' LIMIT 1');
	if (!$mysql->num_rows) {
		$main_html .= $i.'<span class="message">'.ucfirst(TRANSACTION_NAME_SINGULAR).' not found</span><br /><br />'."\n";
	} else {
		$confirmation_screen = true;
		$main_html .= $transactions->confirm_reverse_transaction($i,$url,$_POST['transaction_id']);
	}
}
if ($_POST['submit'] == 'Confirm Reversal' and isset($_POST['transaction_id'])) {
	if ($transactions->reverse($_POST['transaction_id'])) {
		$main_html .= $i.'<span class="message">'.ucfirst(TRANSACTION_NAME_SINGULAR).' deleted</span><br /><br />'."\n";
		if (ENABLE_LOG) log_action($_SESSION['member_full_name'].' reversed "'.strtolower(TRANSACTION_NAME_SINGULAR).' #'.$_POST['transaction_id']);
	} else {
		$main_html .= $i.'<span class="message">'.ucfirst(TRANSACTION_NAME_SINGULAR).' could not be deleted:</span><br /><br />'."\n";
	}
}

//            end of reverse transaction routine
// ******************************************************
//            start of check balances routine

if ($_POST['submit'] == 'Check Balances') {
	$main_html .= $transactions->check_all_balances($i);
	if (ENABLE_LOG) log_action($_SESSION['member_full_name'].' ran the "Check Balances routine.');
}

//            start of check balances routine
// ******************************************************
//            Main Page
// ******************************************************
if(!$confirmation_screen) {
	// categories
	$events->get_event_categories();
	$noticeboard->get_categories();
	$articles->get_art_cats();
	$main_html .= $i.'<h2>Category Admin</h2>'."\n";
	$main_html .= $i.'<strong>Note:</strong> It is not possible to delete a category once '.a(EVENTS_NAME_SINGULAR).' '.strtolower(EVENTS_NAME_SINGULAR).', '.strtolower(NOTICEBOARD_NAME_SINGULAR).' or '.strtolower(ARTICLES_NAME_SINGULAR).' has been assigned to it.'."\n";
	$main_html .= $noticeboard->categories_html($i,$url);
	if (ENABLE_EVENTS) {
		$main_html .= $events->categories_html($i,$url);
	}
	if (ENABLE_ARTICLES) {
		$main_html .= $articles->categories_html($i,$url);
	}
	// global transactions
	$main_html .= $i.'<h2>Make a Global '.ucwords(TRANSACTION_NAME_SINGULAR).'</h2>'."\n";
	$main_html .= $transactions->member_distribute_html($i,$url);
	$user->bulk_membership_tools($i,$url,'delete',false);
	$main_html .= $i.'<h2>Bulk '.ucwords(MEMBERS_NAME_SINGULAR).' Tools</h2>'."\n";
	$main_html .= $user->bulk_tools_html($i,$url);
	$main_html .= $i.'<h2>Reverse A '.ucwords(TRANSACTION_NAME_SINGULAR).'</h2>'."\n";
	$main_html .= $transactions->reverse_transaction_html($i,$url);
	$main_html .= $i.'<br /><br />'."\n";
	$main_html .= $i.'<h2>Check Balances</h2>'."\n";
	$main_html .= $i.'Use this function to ensure stored balances are accurate<br />'."\n";
	$main_html .= $i.'<form name="check_balances" id="check_balances" method="post" action="'.rtrim(URL,'/').$_SERVER['REQUEST_URI'].append_url().'">'."\n";
	$main_html .= $i.' <input class="check_balances_button" type="submit" name="submit" value="Check Balances" />'."\n";
	$main_html .= $i.'</form>'."\n";
	$javascript .= $events->category_javascript;
	$javascript .= $noticeboard->category_javascript;
	$javascript .= $articles->category_javascript;
}

?>