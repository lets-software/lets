<?
/***********************************************************************************************************************************
*		Page:			lost password.php
*		Access:			Public
*		Purpose:		Use this page to recover a password. 
*		HTML Holders:	$main_html			:		Entire Contents
*		Template File:																											*/
			$template_filename 				= 		'default';
/*		Classes:		all intergral			
/*		Indentation:																											
			Set $main_indent on index.php
/*		Disable "Print Page" Link on this page:																					*/
			$disable_print_page				=		true;
/*		CSS Files Called by script:																								*/
		if (!$print) {
			$styles 							.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/lost_password.css);\n";
		}
/*		Dynamic Styling:																										*/
/*		Javascript:																												*/
/*		Set Universal Page elements:																							*/
			$links->page_info(13,0);
			$page_name 						= 		$links->name;
			$url 							= 		$links->url;
			$blurb 							= 		$links->body;
/*		Page Title:																												*/
			$title 							= 		SITE_NAME.' '.$page_name;
/*		Page Heading																											*/
			$main_html 						= 		$i."<h1>".SITE_NAME.' '.$page_name."</h1>\n";
/*
************************************************************************************************************************************/
$success = false;
if (isset($_POST["submit"]) and isset($_POST["email_address"]) and isset($_POST["submitted_member_id"])) {
	if ($_POST['submit'] == 'Submit' and $_POST['email_address'] and $_POST["submitted_member_id"]) {
		if ($mysql->result("SELECT email_address, password, first_name, last_name FROM accounts WHERE email_address = '".$_POST['email_address']."' AND accountID = ".$_POST["submitted_member_id"]." LIMIT 1")) {
			$new_password = createRandomPassword();
			$error = '';
			if (!$mysql->query("UPDATE accounts SET password = '".crypt(md5($new_password),md5(SITE_KEY))."' WHERE accountID = ".$_POST["submitted_member_id"]." LIMIT 1")) {
				
			}
			$email_message = 'Dear '.$mysql->result['first_name'].', <br />'.SITE_NAME.' has generated a new password for you. It is "'.$new_password.'"';
			$error = send_single_email(UPDATE_EMAIL,EMAIL_FROM_NAME,$mysql->result['email_address'],$mysql->result['first_name'].' '.$mysql->result['last_name'],'Your Account Details',strip_tags(str_replace('<br />',"\r\n\r\n",$email_message)),$email_message);
			if ($error) {
				$main_html .= $i.'<span class="message">There was a problem sending the email:<br />'.$error.'</span><br /><br />'."\n";
				if (ENABLE_ERROR_LOG) log_error('There was a problem sending the email with the lost_password script:'.$error);
			} else {
				$main_html .= $i.'<span class="message">Email sent to: '.$_POST['email_address'].'</span><br /><br />'."\n";
				$member_info = new member;
				if (ENABLE_LOG) log_action(ucwords(MEMBERS_NAME_SINGULAR).' ID:'.$_POST["submitted_member_id"].' ('.$member_info->full_name($_POST["submitted_member_id"]).') has reset their password.');
				$success = true;
			}
		} else {
			$main_html .= $i.'<span class="message">Email "'.$_POST['email_address'].'" not found.</span><br /><br />'."\n";
		}
	}
}
if (!$success) {
	$main_html .= $i."<!-- lost_password_form -->\n";
	$main_html .= $i."<div id=\"lost_password_form\">\n";
	$main_html .= $i."This will reset your password and email it to you.<br /><br />\n";
	$main_html .= $i." <fieldset>\n";
	$main_html .= $i.' <form name="lost_password" method="post" action="'.URL.$url.append_url($url).'">'."\n";
	$main_html .= $i.'  <label for="submitted_member_id">'.ucwords(MEMBERS_NAME_SINGULAR).' ID</label>'."\n";
	$main_html .= $i.'  <input type="text" id="submitted_member_id" name="submitted_member_id" /><br class="left" />'."\n";
	$main_html .= $i.'  <label for="email_address">Email Address</label>'."\n";
	$main_html .= $i.'  <input type="text" id="email_address" name="email_address" /><br class="left" />'."\n";
	$main_html .= $i.'  <input class="email_button" type="submit" name="submit" value="Submit" />'."\n";
	$main_html .= $i.' </form>'."\n";
	$main_html .= $i." </fieldset>\n";
	$main_html .= $i.'</div>'."\n";
	$main_html .= $i."<!-- /lost_password_form -->\n";
}
?>