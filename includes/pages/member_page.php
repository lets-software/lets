<?
/***********************************************************************************************************************************
*		Page:			member_page.php
*		Access:			Public
*		Purpose:		Displays a list of articles if no specific
*						Article is called otherwise it prints a
*						Single Article
*		HTML Holders:	$heading				:	Page Heading
*						$messages				:	Messages Resulting from actions
*						$img_html				:	Main image HTML
*						$thumb_html				:	Thumbnails HTML
*						$member_quicklink_html	:	Quicklinks
*						$member_profile			:	Profile
*						$member_details			:	Detailed info on member
*						$transaction			:	Transaction Form HTML
						
*		Template File:																											*/
			$template_filename 					= 		'member_page';
/*		Classes:																												*/
			require_once('includes/classes/transactions.class.php');
			$transactions = new transactions;
/*		Indentation:																											*/
			$heading_indent 					= 		'    ';
			$messages_indent 					= 		'    ';
			$admin_tools_indent					= 		'    ';
			$img_html_indent 				 	= 		'    ';
			$thumb_html_indent 				 	= 		'    ';
			$member_quicklink_html_indent		= 		'    ';
			$member_profile_indent 				= 		'    ';
			$member_details_indent				= 		'    ';
			$transaction_indent 				= 		'    ';
/*		Disable "Print Page" Link on this page:																					*/
			$disable_print_page					=		false;
/*		CSS Files Called by script:																								*/
		if (!$print) {
			$styles 							.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/member_info.css);\n";
			$styles 							.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/thumbs_list.css);\n";
			if (user_type()) 		$styles		.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/transaction_form.css);\n";
			if (user_type() == 2) 	$styles		.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/admin_member_tools.css);\n";
//			comments styling called below as needed
/*		Dynamic Styling:																										*/
			$style->dynamic_elements 			.= 		" div.thumbs {float:left; width:".IMAGE_WIDTH_THUMB_MEMBER."; margin-right:5px; }\n";
			$style->dynamic_elements 			.= 		" img.member_profile_image {float:right; margin-top:10px;}\n";
			$style->dynamic_elements 			.= 		" div.thumbs {float:right; width:".IMAGE_WIDTH_THUMB_MEMBER.";}\n";
			$style->dynamic_elements 			.= 		" div.member_info_member_profile {border-top: 1px solid ".TAB_BORDER_COLOUR."; border-bottom: 1px solid ".TAB_BORDER_COLOUR.";}\n";
		}
/*		Set Universal Page elements:																							*/
			$links->page_info(7,0);
			$page_name 							= 		$links->name;
			$url 								= 		$links->url;
/*		Page Title:																												*/
			$title 								= 		SITE_NAME.' '.$page_name;
/*		Page Heading																											*/
			$heading 							= 		$heading_indent."<h1>".SITE_NAME.' '.$page_name."</h1>\n";
/*
************************************************************************************************************************************/

// check for transaction or validation (included forms on this page):
$member_message = process_transaction();

// ***********************************************************
//                    Admin Actions
// ***********************************************************
$admin_message = '';
if ($_GET["page_id"] and user_type() == 2 and isset($_POST['submit'])) {
	if ($_POST['submit'] == 'Validate '.ucwords(MEMBERS_NAME_SINGULAR)) {
		if ($user->validate($_GET["page_id"])) {
			$admin_message .= $messages_indent.'<span class="message">'.ucwords(MEMBERS_NAME_SINGULAR).' Validated</span><br /><br />'."\n";
		} else {
			$admin_message .= $messages_indent.'<span class="message">Could Not Validate '.ucwords(MEMBERS_NAME_SINGULAR).'</span><br /><br />'."\n";
		}
	}
	if ($_POST['submit'] == 'Delete '.ucwords(MEMBERS_NAME_SINGULAR)) {
		if ($user->delete($_GET["page_id"])) {
			$admin_message .= $messages_indent.'<span class="message">'.ucwords(MEMBERS_NAME_SINGULAR).' Deleted</span><br /><br />'."\n";
		} else {
			$admin_message .= $messages_indent.'<span class="message">Could Not Delete '.ucwords(MEMBERS_NAME_SINGULAR).'</span><br /><br />'."\n";
		}
	}
	if ($_POST['submit'] == 'Restore '.ucwords(MEMBERS_NAME_SINGULAR)) {
		if ($user->restore($_GET["page_id"])) {
			$admin_message .= $messages_indent.'<span class="message">'.ucwords(MEMBERS_NAME_SINGULAR).' Restored</span><br /><br />'."\n";
		} else {
			$admin_message .= $messages_indent.'<span class="message">Could Not Restore '.ucwords(MEMBERS_NAME_SINGULAR).'</span><br /><br />'."\n";
		}
	}
	if ($_POST['submit'] == 'Reactivate '.ucwords(MEMBERS_NAME_SINGULAR)) {
		if ($user->reactivate($_GET["page_id"])) {
			$admin_message .= $messages_indent.'<span class="message">'.ucwords(MEMBERS_NAME_SINGULAR).' Reactivated</span><br /><br />'."\n";
		} else {
			$admin_message .= $messages_indent.'<span class="message">Could Not Reactivate '.ucwords(MEMBERS_NAME_SINGULAR).'</span><br /><br />'."\n";
		}
	}
	if ($_POST['submit'] == 'Suspend '.ucwords(MEMBERS_NAME_SINGULAR)) {
		if ($user->suspend($_GET["page_id"])) {
			$admin_message .= $messages_indent.'<span class="message">'.ucwords(MEMBERS_NAME_SINGULAR).' Suspended</span><br /><br />'."\n";
		} else {
			$admin_message .= $messages_indent.'<span class="message">Could Not Suspend '.ucwords(MEMBERS_NAME_SINGULAR).'</span><br /><br />'."\n";
		}
	}
	if ($_POST['submit'] == 'Update Expiry') {
		if ($user->set_expiry($_GET["page_id"])) {
			$admin_message .= $messages_indent.'<span class="message">'.ucwords(MEMBERS_NAME_SINGULAR).' Expiry Updated</span><br /><br />'."\n";
		} else {
			$admin_message .= $messages_indent.'<span class="message">Could Not Update '.ucwords(MEMBERS_NAME_SINGULAR).'\'s Expiry</span><br /><br />'."\n";
		}
	}
}


$admin_html = '';
$img_html = '';
$thumb_html = '';
$messages = '';
$member_quicklink_html = '';
$member_details = '';
$member_profile_html = '';
$transaction_html = '';

$exists = true;
$member_info = new member;
$member_info->build_dataset($_GET["page_id"]);
if (!empty($member_info->error_message)) {
	$messages .= $messages_indent.'<span class="message">'.$member_info->error_message.'</span><br /><br />'."\n";
} else {

	if (!empty($admin_message)) {
		$messages .= $admin_message;
	}
	if (!empty($member_message)) {
		$messages .= $messages_indent.'<span class="message">'.$member_message.'</span><br /><br />'."\n";
	}
	
	// end of action routine
	if ((user_type() == 0 or $_SESSION["member_validated"] == 0 or $_SESSION["member_suspended"] == 1)) {
		if (!$member_info->public_profile_enabled) {
			if (!user_type()) {
				$messages .= $messages_indent."This ".strtolower(MEMBERS_NAME_SINGULAR)." does not have a public profile<br />\n";
			} elseif (!$_SESSION["member_validated"]) {
				$messages .= $messages_indent."This ".strtolower(MEMBERS_NAME_SINGULAR)." does not have a public profile. Your account must be validated before you can view ".strtolower(MEMBERS_NAME_PLURAL)."' private details.<br />\n";
			} elseif ($_SESSION["member_suspended"]) {
				$messages .= $messages_indent."This ".strtolower(MEMBERS_NAME_SINGULAR)." does not have a public profile. Your account must be restored before you can view ".strtolower(MEMBERS_NAME_PLURAL)."' private details.<br />\n";
			}
		} else {
			$member_details .= $member_info->display_info($i,'','public');
			if (!empty($member_info->member_profile)) {
				$member_profile_html = $member_profile_indent."<br class=\"right\" /><span class=\"member_profile\">".ucwords(MEMBERS_NAME_SINGULAR)." Profile:</span>\n";
				$member_profile_html .= $member_profile_indent."<div class=\"member_info_member_profile\">\n";
				$member_profile_html .= indent_variable($member_profile_indent." ",$member_info->public_profile)."\n";
				$member_profile_html .= $member_profile_indent."</div>\n";
			}
		}
		$title .= ' - '.ucwords(MEMBERS_NAME_SINGULAR).' '.$_GET["page_id"];
	} elseif (user_type()) {
		if (!$member_info->validated and user_type() == 1) {
			$messages .= $messages_indent."This ".strtolower(MEMBERS_NAME_SINGULAR)." has not been validated<br />\n";
		} elseif ($member_info->suspended and user_type() == 1) {
			$messages .= $messages_indent."This account is not active<br />\n";
		} else {
			if (user_type() == 2) {
				$admin_html .= $member_info->admin_tools_html($admin_tools_indent);
			}
			$title .= ' - '.ucwords($member_info->first_name).' '.ucwords($member_info->last_name);
			if (ENABLE_IMAGES) {
				$image_id = return_link_variable("image",$member_info->image_id);
				if ($image_id) {
					if ($image->img($image_id,'p',$member_info->first_name,'member_profile_image')) {
						$img_html = '<a href="/images/'.$image->name.'.png" target="_blank">'.$image->img."</a><br />\n";
					}
				}
				if ($mysql->build_array('SELECT * FROM images WHERE accountID = '.$member_info->id.' AND noticeboardID = 0 AND articleID = 0')) {
					if ($mysql->num_rows > 1) {
						$thumb_html = $image->thumbs($thumb_html_indent,remove_slashes($mysql->result),URL.MEMBER_LIST_URL.'/'.$member_info->id.'/');
					}
				}
			}
			
			if ($img_html) $img_html = $img_html_indent."<!-- img_html -->\n".$img_html_indent.$img_html."\n". $img_html_indent."<!-- /img_html -->\n";
			$member_quicklink_html = member_quicklinks($member_quicklink_html_indent,$member_info->id);
			
			if (!empty($member_info->member_profile)) {
				$member_profile_html .= $member_profile_indent."<!-- member_profile_holder -->\n";
				$member_profile_html .= $member_profile_indent."<div class=\"member_profile_holder\">\n";
				$member_profile_html .= $member_profile_indent." <span class=\"member_profile\">".ucwords(MEMBERS_NAME_SINGULAR)." Profile:</span>\n";
				$member_profile_html .= $member_profile_indent." <div class=\"member_info_member_profile\">\n";
				$member_profile_html .= indent_variable($member_profile_indent."  ",$member_info->member_profile)."\n";
				$member_profile_html .= $member_profile_indent." </div>\n";
				$member_profile_html .= $member_profile_indent."</div>\n";
				$member_profile_html .= $member_profile_indent."<!-- /member_profile_holder -->\n";
			}
			
			if ($_SESSION['member_id'] == $_GET["page_id"]) {
				$member_details .= $member_info->display_info($member_details_indent,'',"private");
			} else {
				$member_details .= $member_info->display_info($member_details_indent,'',"private");
				if (ENABLE_NOTICEBOARD) {
					$transaction_html = $transaction_indent.'<span class="member_profile">'.ucwords(TRANSACTION_NAME_SINGULAR).' with '.$member_info->first_name."</span>\n";
					if (user_type() == 2) {
						$transaction_html .= $transactions->html($transaction_indent,'admin',$_GET["page_id"],MEMBER_LIST_URL.'/'.$_GET["page_id"].'/');
					} else {
						$transaction_html .= $transactions->html($transaction_indent,'member',$_GET["page_id"],MEMBER_LIST_URL.'/'.$_GET["page_id"].'/');
					}
				}
			}
		}
	}
}

?>
