<?php
/***********************************************************************************************************************************
*		Page:			member_noticeboard.php
*		Access:			Admin
*		Purpose:		Member admin of noticeboard entries
*		HTML Holders:	$main_html			:		Entire Contents
*		Template File:																											*/
			$template_filename 				= 		'default';
/*		Classes:		
/*		Indentation:																											
			Set $main_indent on index.php
/*		Disable "Print Page" Link on this page:																					*/
			$disable_print_page				=		true;
/*		CSS Files Called by script:																								*/
		if (!$print) {
			$styles 						.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/contact.css);\n";
		}
/*		Dynamic Styling:																										*/
/*		Javascript:																												*/
/*		Set Universal Page elements:																							*/
			$links->page_info(1,109);
			$page_name 						= 		$links->name;
			$url 							= 		$links->url;
			$blurb 							= 		$links->body;
/*		Page Title:																												*/
			$title 							= 		SITE_NAME.' '.$page_name;
/*		Page Heading																											*/
			$main_html 						= 		$i."<h1>".SITE_NAME.' '.$page_name."</h1>\n";
/*
************************************************************************************************************************************/
if (isset($_POST['member_search_type'])) $member_search_type = $_POST['member_search_type']; else $member_search_type = 1;
if (isset($_POST['email_subject'])) $subject = remove_slashes($_POST['email_subject']); else $subject = '';
if (isset($_POST['email_message'])) $message = remove_slashes($_POST['email_message']); else $subject = '';
if ($member_search_type and $subject and $message) {
	if ($member_search_type > 1) {
		if ($member_search_type == 2) {
			$query = "SELECT accountID, email_address, first_name, last_name FROM accounts WHERE validated = 0 AND suspended = 0 AND email_address != ''";
		}
		if ($member_search_type == 3) {
			$query = "SELECT accountID, email_address, first_name, last_name FROM accounts WHERE validated = 1 AND suspended = 1 AND email_address != '' AND deleted = 0";
		}
		if ($member_search_type == 4) {
			$query = "SELECT accountID, email_address, first_name, last_name FROM accounts WHERE validated = 1 AND ".'suspended = 0 AND email_address != \'\' AND ((expiry_year = '.$date['year'].' AND expiry_month = '.$date['month'].') OR (expiry_year = '.$date['year'].' AND expiry_month = '.($date['month'] + 1).' AND expiry_day < '.($date['day']).'))';
		}
		if ($member_search_type == 5) {
			$query = "SELECT accountID, email_address, first_name, last_name FROM accounts WHERE validated = 1 AND ".'suspended = 0 AND email_address != \'\' AND ((expiry_year < '.$date['year'].') OR (expiry_year = '.$date['year'].' AND expiry_month < '.$date['month'].') OR (expiry_year = '.$date['year'].' AND expiry_month = '.$date['month'].' AND expiry_day < '.($date['day']).'))';
		}
		if ($member_search_type == 6) {
			$query = "SELECT accountID, email_address, first_name, last_name FROM accounts WHERE validated = 1 AND deleted = 1 AND email_address != ''";
		}
	} else {
		$query = "SELECT accountID, email_address, first_name, last_name FROM accounts WHERE validated = 1 AND suspended = 0 AND email_address != ''";
	}
	$email_errors = '';
	$fails = 0;
	if ($mysql->build_array($query)) {
		$num_members = $mysql->num_rows;
		if ($num_members) {
			foreach($mysql->result as $member) {
				$error = send_single_email(UPDATE_EMAIL,EMAIL_FROM_NAME,$member['email_address'],$member['first_name'].' '.$member['last_name'],$subject,nl2br($message),$message);
				if ($error) {
					$fails++;
					$email_errors .= $error;
				}
			}
			if (ENABLE_LOG) log_action($_SESSION['member_full_name'].' sent a group email.');
			if ($email_errors) {
				$main_html .= $i.'<span class="message">'.($num_members - $fails).' of '.$num_members.' '.strtolower(MEMBERS_NAME_PLURAL).' mailed successfully. Errors:<br />'.$email_errors.'</span><br /><br />'."\n";
			} else {
				$main_html .= $i.'<span class="message">'.$num_members.' '.strtolower(MEMBERS_NAME_PLURAL).' mailed successfully</span><br /><br />'."\n";
			}
		} else {
			$main_html .= $i.'<span class="message">No '.strtolower(MEMBERS_NAME_PLURAL).' found</span><br /><br />'."\n";
		}
	} else {
		$main_html .= $i.'<span class="message">No '.strtolower(MEMBERS_NAME_PLURAL).' found</span><br /><br />'."\n";
	}
}


// ****************************************************************
//			Main Page


$main_html .= $i."<!-- send_email_form -->\n";
$main_html .= $i."<div id=\"send_email_form\">\n";
$main_html .= $i." <fieldset>\n";
$main_html .= $i.' <form name="contact" method="post" action="'.URL.$url.append_url($url).'">'."\n";
$main_html .= $i.'  <label for="member_search_type">Send to '.ucwords(MEMBERS_NAME_PLURAL).':</label>'."\n";
$main_html .= "$i   <select id=\"member_search_type\" name=\"member_search_type\">\n";
$main_html .= "$i    <option value=\"1\"".check_selected($member_search_type,"1").">Normal and Expired</option>\n";
$main_html .= "$i    <option value=\"2\"".check_selected($member_search_type,"2").">Unvalidated</option>\n";
$main_html .= "$i    <option value=\"3\"".check_selected($member_search_type,"3").">Suspended</option>\n";
$main_html .= "$i    <option value=\"4\"".check_selected($member_search_type,"4").">Expiring within one month</option>\n";
$main_html .= "$i    <option value=\"5\"".check_selected($member_search_type,"5").">Expired</option>\n";
$main_html .= "$i    <option value=\"6\"".check_selected($member_search_type,"6").">Deleted</option>\n";
$main_html .= "$i   </select><br class=\"left\" />\n";
$main_html .= $i.'  <label for="email_subject">Subject</label>'."\n";
$main_html .= $i.'  <input type="text" id="email_subject" name="email_subject" /><br class="left" />'."\n";
$main_html .= $i.'  <label for="email_message">Message:</label>'."\n";
$main_html .= $i.'  <textarea id="email_message" name="email_message"></textarea><br class="left" /><br class="left" />'."\n";
$main_html .= $i.'  <input class="email_button" type="submit" name="submit" value="Submit" />'."\n";
$main_html .= $i.' </form>'."\n";
$main_html .= $i." </fieldset>\n";
$main_html .= $i.'</div>'."\n";
$main_html .= $i."<!-- /send_email_form -->\n";




?>