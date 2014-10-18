<?
/***********************************************************************************************************************************
*		Page:			contact.php
*		Access:			Public
*		Purpose:		Visitors can contact the admin through this form
*		HTML Holders:	$main_html		:	Entire Contents
*		Template File:																											*/
			$template_filename 			= 		'default';
/*		Classes:		all intergral			
/*		Indentation:																											
			Set $main_indent on index.php
/*		Disable "Print Page" Link on this page:																					*/
			$disable_print_page			=		true;
/*		CSS Files Called by script:																								*/
		if (!$print) {
			$styles 					.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/contact.css);\n";
		}
/*		Dynamic Styling:																										*/
/*		Set Universal Page elements:																							*/
			$links->page_info(11,0);
			$page_name 					= 		$links->name;
			$url 						= 		$links->url;
			$blurb 						= 		$links->body;
/*		Page Title:																												*/
			$title 						= 		SITE_NAME.' '.$page_name;
/*		Page Heading													*/
			if ($blurb)  {
				$blurb 					= 		$i."<h1>".SITE_NAME.' '.$page_name."</h1>\n".$blurb;
			} else {														
				$main_html 				= 		$i."<h1>".SITE_NAME.' '.$page_name."</h1>\n";
			}
/*
************************************************************************************************************************************/

//  ACTIONS

$submitted_encrypted_email = false;
// ****************************************************************
//         submit email


if (!user_type() and bad_user()) {
	$message .= $i.'<span class="message">You aren\'t allowed to send any more emails through our system. Please use your own email solution.<br /><br /><strong>If you are not able to properly see the email addresses on this website you need to enable javascript.</strong></span><br /><br />'."\n";
	if (ENABLE_LOG) log_action('This user has been banned from sending anymore emails through our website.');
} else {
	if (isset($_POST['email_address']) and isset($_POST['email_subject']) and isset($_POST['email_message'])) {
		if ($_POST['email_address'] and $_POST['email_subject'] and $_POST['email_message']) {
			if (!user_type()) {
				wrong_password();
				wrong_password();
				wrong_password();
			}
			$to_email = ADMIN_EMAIL;
			if ($_POST['n'] and $_POST['d'] and $_POST['t']) {
				$email = strip_tags(str_rot13($_POST['n']) . '@' . str_rot13($_POST['d']) . '.' . str_rot13($_POST['t']));
				if (verify_email_address($email)) {
					$to_email = $email;
				}
			}
			$mail_error = send_single_email($_POST['email_address'],'Website Submission',$to_email,SITE_NAME.' '.ADMIN_NAME_SINGULAR,strip_tags($_POST['email_subject']),strip_tags($_POST['email_message']),remove_bad_tags($_POST['email_message']));
			if (!$mail_error) {
				$message .= $i.'<span class="message">Your message has been successfully sent.</span><br /><br />'."\n";
				if (ENABLE_LOG) log_action('Someone attempted to contact '.SITE_NAME.'.');
			} else {
				$message .= $i.'<span class="message">Sorry your email could not be sent.</span><br /><br />'."\n";
				//echo $mail_error;
				log_error('Failed to send an email from contact.php');
			}
		} else {
			$message .= $i.'<span class="message">All fields are required.</span><br /><br />'."\n";
		}
	} else {
		if ($_GET['n'] and $_GET['d'] and $_GET['t']) {
			$email = strip_tags(str_rot13($_GET['n']) . '@' . str_rot13($_GET['d']) . '.' . str_rot13($_GET['t']));
			$display_email = strip_tags(substr(str_rot13($_GET['n']),0,-3) . '...&#64;' . str_rot13($_GET['d']) . '.' . str_rot13($_GET['t']));
			if (verify_email_address($email)) {
				$submitted_encrypted_email = true;
			}
		}
	}
}
// ****************************************************************
//			Main Page
if ($submitted_encrypted_email) {
	$message .= $message_indent."<br />\n";
	$message .= $i."<!-- contact_form -->\n";
	$message .= $i."<div id=\"contact_form\">\n";
	$message .= $i.' <strong>Please submit the following form</strong> to contact: '.$display_email."\n";
	$message .= $i." <fieldset>\n";
	$message .= $i.' <form name="contact" method="post" action="'.URL.$url.append_url($url).'">'."\n";
	$message .= $i.'  <label for="email_address">Your email Address:</label>'."\n";
	$message .= $i.'  <input type="text" id="email_address" name="email_address" /><br class="left" />'."\n";
	$message .= $i.'  <label for="email_subject">Subject:</label>'."\n";
	$message .= $i.'  <input type="text" id="email_subject" name="email_subject" /><br class="left" />'."\n";
	$message .= $i.'  <label for="email_message">Message:</label>'."\n";
	$message .= $i.'  <textarea id="email_message" name="email_message"></textarea><br class="left" /><br class="left" />'."\n";
	$message .= $i.'  <input type="hidden" name="n" value="'.$_GET['n'].'" />'."\n";
	$message .= $i.'  <input type="hidden" name="d" value="'.$_GET['d'].'" />'."\n";
	$message .= $i.'  <input type="hidden" name="t" value="'.$_GET['t'].'" />'."\n";
	$message .= $i.'  <input class="email_button" type="submit" name="submit" value="Submit" />'."\n";
	$message .= $i.' </form>'."\n";
	$message .= $i." </fieldset>\n";
	$message .= $i.'</div>'."\n";
	$message .= $i."<!-- /contact_form -->\n";
	
} else {
	$main_html .= $i."<!-- contact_form -->\n";
	$main_html .= $i."<div id=\"contact_form\">\n";
	$main_html .= $i." <fieldset>\n";
	$main_html .= $i.' <form name="contact" method="post" action="'.URL.$url.append_url($url).'">'."\n";
	$main_html .= $i.'  <label for="email_address">Your email Address:</label>'."\n";
	$main_html .= $i.'  <input type="text" id="email_address" name="email_address" /><br class="left" />'."\n";
	$main_html .= $i.'  <label for="email_subject">Subject:</label>'."\n";
	$main_html .= $i.'  <input type="text" id="email_subject" name="email_subject" /><br class="left" />'."\n";
	$main_html .= $i.'  <label for="email_message">Message:</label>'."\n";
	$main_html .= $i.'  <textarea id="email_message" name="email_message"></textarea><br class="left" /><br class="left" />'."\n";
	$main_html .= $i.'  <input class="email_button" type="submit" name="submit" value="Submit" />'."\n";
	$main_html .= $i.' </form>'."\n";
	$main_html .= $i." </fieldset>\n";
	$main_html .= $i.'</div>'."\n";
	$main_html .= $i."<!-- /contact_form -->\n";
}

?>