<?
/***********************************************************************************************************************************
*		Page:			register.php
*		Access:			Public
*		Purpose:		Registration Page
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
			$styles 					.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/edit_account.css);\n";
			$styles 					.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/edit_images.css);\n";
		}
/*		Dynamic Styling:																										*/
/*		Set Universal Page elements:																							*/
			$links->page_info(1,1);
			$page_name 					= 		$links->name;
			$url 						= 		$links->url;
			$blurb 						= 		$links->body;
/*		Page Title:																												*/
			$title 						= 		'Join '.SITE_NAME;

/*
************************************************************************************************************************************/


if (!isset($_POST["accepted_tos"])) $_POST["accepted_tos"] = false;
if (!isset($_POST["submit"])) $_POST["submit"] = '';
if ($_POST["accepted_tos"] != "true") {
/*		Page Heading																											*/
	$main_html 					= 		$i.'<h1>Join '.SITE_NAME."</h1>\n";
	$main_html .= $i."<fieldset>\n";
	$main_html .= indent_variable($i,REGISTER_TERMS);
	$main_html .= "$i<form id=\"tos\" action=\"".URL."members/register/\" method=\"post\">\n";
	$main_html .= "$i <input type=\"hidden\" name=\"accepted_tos\" value=\"true\" />\n";
	$main_html .= "$i <input type=\"submit\" id=\"button\" name=\"submit\" value=\"Accept\" />\n";
	$main_html .= "$i</form>\n";
	$main_html .= $i."</fieldset>\n";
} elseif ($_POST["submit"] == "Accept") {
/*		Page Heading																											*/
	$main_html = $i.'<h1>'.SITE_NAME." Registration</h1>\n";
	$main_html .= $i."<strong>Please fill out the following form.</strong><br /><br />\n";
	$main_html .= $user->register_html("add",false,$i);
	if (ENABLE_LOG) log_action('Membership terms were accepted');
} elseif ($_POST["submit"] == "Submit") {
/*		Page Heading																											*/
	$main_html = $i.'<h1>'.SITE_NAME." Registration</h1>\n";
	if (!$user->add($i,$site->enc_key)) {
		$user->rebuild_form();
		$main_html .= $i."<h2>The following errors were found:</h2>\n";
		$main_html .= $user->error_message."<br />\n";
		$main_html .= $i."Please correct these errors and resubmit the form.<br /><br />\n";
		$main_html .= $user->register_html("add",true,$i);
	} else {
		$user->build_dataset($user->id);
		if ($image->substantiate($user->id,'accountID',$user->id,IMAGE_WIDTH_THUMB_MEMBER,IMAGE_HEIGHT_THUMB_MEMBER,IMAGE_WIDTH_PAGE_MEMBER,IMAGE_HEIGHT_PAGE_MEMBER,$user->first_name)) {
			$image_message .= $image->validate_form($i);
			if (empty($image_message)) {
				if (!$image->add()) {
					$image_message .= $image->error;
				} else {
					if ($image->id) {
						if (!$user->set_default_image($user->id,$image->id)) {
							$image_message .= $user->error;
						} else {
							// We did it !!!!!!
							$image_message .= "image added";
						}
					}
				}
			} else {
				$image_message .= $image->error;
			}
		}
		$main_html .= $i."Welcome ".$user->first_name.". Your account has been added successfully";		
		if ($image_message) if ($image_message == 'image added') $main_html .= " and image also added.<br />\n"; else $main_html .= " but ".$image_message.".<br />\n"; else $main_html .= ".<br />\n";
		$main_html .= $i."Your ".ucwords(MEMBERS_NAME_SINGULAR)." Number is:<strong>".$user->id."</strong><br /><br />\n";
		$main_html .= $i."Please use this number to login to the system.<br />\n";
		if (VALIDATE_MEMBERS) {
			$main_html .= $i."Your account needs to be validated. Please be patient while the ".strtolower(ADMIN_NAME_SINGULAR)." completes the registration process.\n";
		}
		if (ENABLE_LOG) log_action('Membership terms were accepted');
		if (ENABLE_EMAIL) {
			if ($user->email_address) {
				send_single_email(UPDATE_EMAIL,EMAIL_FROM_NAME,$user->email_address,ucwords($user->first_name).' '.ucwords($user->last_name),'Welcome to '.SITE_NAME,'Dear '.ucwords($user->first_name).",\r\n\r\n\r\n".$site->return_config_html($i,'new_member_message')."\r\n\r\n\r\nYour ".ucwords(MEMBERS_NAME_SINGULAR)." Number is: ".$user->id,'Dear '.ucwords($user->first_name).",<br /><br />".indent_variable('',$site->return_config_html($i,'new_member_message'),false)."<br /><br />Your ".ucwords(MEMBERS_NAME_SINGULAR)." Number is: ".$user->id);
			}
			send_single_email(UPDATE_EMAIL,EMAIL_FROM_NAME,VALIDATION_EMAIL,ucwords(ADMIN_NAME_SINGULAR),'A new '.strtolower(MEMBERS_NAME_SINGULAR).' has joined',ucwords($user->first_name).' '.ucwords($user->last_name).' ('.ucwords(MEMBERS_NAME_SINGULAR).' #'.$user->id.') has joined '.SITE_NAME."!\r\n\r\n\r\nYou can validate their account here: ".URL.MEMBER_LIST_URL.'/'.$user->id.'/',ucwords($user->first_name).' '.ucwords($user->last_name).' ('.ucwords(MEMBERS_NAME_SINGULAR).' #'.$user->id.') has joined '.SITE_NAME."!<br /><br />You can validate their account <a href=\"".URL.MEMBER_LIST_URL.'/'.$user->id.'/">here</a>');
			if (ENABLE_LOG) log_action(ucwords(MEMBERS_NAME_SINGULAR).' Registration Completed by '.ucwords($user->first_name).' '.ucwords($user->last_name).' ( #'.$user->id.')');
		}
	}
}



?>