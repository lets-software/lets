<?php
/***********************************************************************************************************************************
*		Page:			edit_account.php
*		Access:			Member
*		Purpose:		Members can edit their accounts and admins can edit any account
*		HTML Holders:	$main_html		:		Entire Contents
*		Template File:																											*/
			$template_filename 			= 		'default';
/*		Classes:		
			only integral objects
/*		Indentation:																											
			Set $main_indent on index.php
/*		Disable "Print Page" Link on this page:																					*/
			$disable_print_page			=		true;
/*		CSS Files Called by script:																								*/
		if (!$print) {
			$styles 					.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/edit_account.css);\n";
			$styles 					.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/edit_images.css);\n";
/*		Dynamic Styling:																										*/
			if (ENABLE_IMAGES) {
				$style->dynamic_elements 	.= 		" div.edit_image_box {border:1px solid ".TAB_COLOUR.";}\n";
				$style->dynamic_elements 	.= 		" img.edit_image_form {float:right; width:".IMAGE_WIDTH_THUMB_MEMBER."px; }\n";
				if ((IMAGE_WIDTH_THUMB_MEMBER + 90) < 210) $image_box_width = 210; else $image_box_width = IMAGE_WIDTH_THUMB_MEMBER + 90;
				$style->dynamic_elements 	.= 		" div.edit_image_box { float:left; width:".$image_box_width."px; margin-left:5px; margin-bottom:5px; padding:2px 2px 2px 2px; border:1px solid ".TAB_COLOUR."; }\n";
	/*			$style->dynamic_elements 	.= 		" input.default_image_button {width: 100px }\n";
				$style->dynamic_elements 	.= 		" input.edit_image_button {width: ".$image_box_width."px }\n";
				$style->dynamic_elements 	.= 		" input.delete_image_button {width: ".$image_box_width."px }\n"; */
			}
		}
/*		Javascript:																												*/
/*		Set Universal Page elements:																							*/
			$links->page_info(1,2);
			$page_name 					= 		$links->name;
			$url 						= 		$links->url;
			$blurb 						= 		$links->body;
/*		Page Title:																												*/
			$title 						= 		SITE_NAME.' '.$page_name;
/*		Page Heading																											*/
			$main_html 					= 		$i."<h1>".SITE_NAME.' '.$page_name."</h1>\n";
/*
************************************************************************************************************************************/

// *************************
//		Setup page, determine is it's a member editing 
//		their own account or an edmin editing a member's account
// *************************
$member_id_to_edit = 0;
if (!isset($_POST['submit'])) $_POST['submit'] = '';
if (isset($_POST['member_id_to_edit']) and $_POST['submit'] == 'Find '.ucwords(MEMBERS_NAME_SINGULAR) and user_type() == 2) {
	if ($_POST['member_id_to_edit']) {
		$member_id_to_edit = $_POST['member_id_to_edit'];
	} else {
		$member_id_to_edit = $_SESSION["member_id"];
	}
} elseif (user_type() == 2 and isset($_GET['member_id_to_edit'])) {
	if ($_GET['member_id_to_edit']) {
		$member_id_to_edit = $_GET['member_id_to_edit'];
	} else {
		$member_id_to_edit = $_SESSION["member_id"];
	}
} else {
	$member_id_to_edit = $_SESSION["member_id"];
}

if (!$user->build_dataset($member_id_to_edit)) {
	if (!$mysql->num_rows('SELECT accountID FROM accounts WHERE accountID = '.$member_id_to_edit.' LIMIT 1')) {
		if ($mysql->num_rows) {
			$main_html .= $i.'<span class="message">This acount could not be accessed</span><br /><br />'."\n";
		} else {
			$main_html .= $i.'<span class="message">The account was not found</span><br /><br />'."\n";
		}
	} else {
		$main_html .= $i.'<span class="message">The account was not found</span><br /><br />'."\n";
	}
}

// *************************
//     Actions
// *************************

// start image routine
$image_message = '';	
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
if ($_POST['submit'] == 'Edit Image') {
	$image_message .= $image->validate_form($i);
	if (empty($image_message)) if (!$image->edit()) $image_message .= $image->error;
}
if ($_POST['submit'] == 'Set Default') {
	$image_message .= $image->validate_form($i);
	if (empty($image_message)) if (!$image->make_default() or !$image->edit()) $image_message .= $image->error;
}
if ($_POST['submit'] == 'Delete Image') {
	$image_message .= $image->validate_form($i);
	if (empty($image_message)) if (!$image->delete()) $image_message .= $image->error;
}

if (!empty($image_message)) {
	if ($image_message == 'image added') {
		$main_html .= $i.'<span class="message">Image Added</span><br /><br />'."\n";
	} else {
		$main_html .= $i.'<span class="message">The following errors were found with your image information:</span><br />'."\n";
		$main_html .= $image_message.'<br /><br />';
	}
}
// end image routine


if ($_POST["submit"] == 'Edit') {
	if (!$user->edit($i,SITE_KEY)) {
		$user->rebuild_form();
		$title = SITE_NAME.' '.ucwords(MEMBERS_NAME_SINGULAR).'\'s Area - Form incomplete';
		$main_html .= $i.'<span class="message">The following errors were found with your form submission:</span><br />'."\n";
		$main_html .= $user->error_message."<br /><br />\n";
		$main_html .= $user->register_html("edit",true,$i);
	} else {
		$title = SITE_NAME.' '.ucwords(MEMBERS_NAME_SINGULAR).'\'s Area - Your account has been edited successfully';
		$user->build_dataset($member_id_to_edit);
		$main_html .= $i."<span class=\"message\">Your Account had been edited successfully</span><br /><br />\n";
		$main_html .= $user->register_html("edit",false,$i);
	}
} else {
	$main_html .= $i."<strong>Please fill out the following form.</strong><br /><br />\n";
	$main_html .= $user->register_html("edit",false,$i);
}

?>