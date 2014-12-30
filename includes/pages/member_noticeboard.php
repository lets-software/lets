<?php
/***********************************************************************************************************************************
*		Page:			member_noticeboard.php
*		Access:			Member
*		Purpose:		Member admin of noticeboard entries
*		HTML Holders:	$main_html	:	Entire Contents
*		Template File:																											*/
			$template_filename 			= 		'default';
/*		Classes:																												*/
			require_once('includes/classes/noticeboard.class.php');
			$noticeboard = new noticeboard;
			$existing_noticeboard = new noticeboard;
/*		Indentation:																											
			Set $main_indent on index.php
/*		Disable "Print Page" Link on this page:																					*/
			$disable_print_page			=		true;
/*		CSS Files Called by script:																								*/
		if (!$print) {
			$styles 					.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/noticeboard_form.css);\n";
			$styles 					.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/noticeboard_memberlist.css);\n";
			$styles 					.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/edit_images.css);\n";
/*		Dynamic Styling:																										*/
			if ((IMAGE_WIDTH_THUMB_NOTICEBOARD + 90) < 210) $image_box_width = 210; else $image_box_width = IMAGE_WIDTH_THUMB_NOTICEBOARD + 90;
			$style->dynamic_elements 	.= 		" div.edit_image_box { float:left; width:".$image_box_width."px; margin-left:5px; margin-bottom:5px; padding:2px 2px 2px 2px; border:1px solid ".TAB_COLOUR."; }\n";
			$style->dynamic_elements 	.= 		" img.edit_image_form {float:right; width:".IMAGE_WIDTH_THUMB_NOTICEBOARD."px; }\n";
		}
/*		Javascript:																												*/
			$javascript 				.= 		$noticeboard->form_javascript();
/*		Set Universal Page elements:																							*/
			$links->page_info(1,5);
			$page_name 					= 		$links->name;
			$url 						= 		$links->url;
			$blurb 						= 		$links->body;
/*		Page Title:																												*/
			$title 						= 		SITE_NAME.' '.$page_name;
/*		Page Heading																											*/
			$heading 					= 		$i."<h1>".SITE_NAME.' '.$page_name."</h1>\n";
			$main_html					=		'';
/*
************************************************************************************************************************************/


$noticeboard_edited = 0;
$inserted_noticeboard_id = 0;
$inserted_noticeboard_name = '';
$listing_added = false;

if (!isset($_POST['submit'])) $_POST['submit'] = '';

if ($_POST['submit']) {
	// ************ actions ********************
	// validate form data
	if ($_POST['submit'] == 'Add' or $_POST['submit'] == 'Edit') {
		$message = $noticeboard->validate_form($i);
	}
	
	// if there are no messages data is valid and we can procceed
	if (empty($message)) {
		$listing_added = true;
		if ($_POST['submit'] == 'Add') {
			if ($noticeboard->add($_SESSION["member_id"])) {
				$main_html .= $i.'<span class="message">'.ucwords(NOTICEBOARD_NAME_SINGULAR).' added</span><br /><br />'."\n";
				$inserted_noticeboard_id = $noticeboard->id;
				$inserted_noticeboard_name = $noticeboard->title;
				$noticeboard_edited = 1;
				if (ENABLE_EMAIL) {
					bulk_membership_email('receive_email_noticeboard',$_SESSION['member_name'].' has added '.a(NOTICEBOARD_NAME_SINGULAR).' '.strtolower(NOTICEBOARD_NAME_SINGULAR).' entitled "'.$noticeboard->title."\"\r\n\r\n\r\nClick the following link to view this ".strtolower(NOTICEBOARD_NAME_SINGULAR).": ".URL.NOTICEBOARD_URL.'/'.$noticeboard->id.'/',$_SESSION['member_name'].' has added '.a(NOTICEBOARD_NAME_SINGULAR).' '.strtolower(NOTICEBOARD_NAME_SINGULAR).' entitled <strong>'.$noticeboard->title.'</strong><br /><br />Click <a href="'.URL.NOTICEBOARD_URL.'/'.$noticeboard->id.'/">here</a> to view this '.strtolower(NOTICEBOARD_NAME_SINGULAR).'.','A new '.strtolower(NOTICEBOARD_NAME_SINGULAR).' has been posted',EMAIL_FROM_NAME);
				}
				if (ENABLE_LOG and LOG_ADDITIONS and LOG_NOTICEBOARD) log_action(ucwords(NOTICEBOARD_NAME_SINGULAR).' ID:'.$noticeboard->id.' ('.$noticeboard->title.') added.');
			} else {
				$listing_added = false;
				$main_html .= $i.'<span class="message">'.ucwords(NOTICEBOARD_NAME_SINGULAR).' could not be added</span><br /><br />'."\n";
				if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: '.ucwords(NOTICEBOARD_NAME_SINGULAR).' ID:'.$noticeboard->id.' ('.$noticeboard->title.') added.');
				if (ENABLE_ERROR_LOG) log_error('FAILED: '.ucwords(NOTICEBOARD_NAME_SINGULAR).' ID:'.$noticeboard->id.' ('.$noticeboard->title.') added.<br />Error: '.$noticeboard->error);
			}
		}
		if ($_POST['submit'] == 'Edit') {
			if ($noticeboard->edit()) {
				$main_html .= $i.'<span class="message">'.ucwords(NOTICEBOARD_NAME_SINGULAR).' edited</span><br /><br />'."\n";
				$noticeboard_edited = 1;
				if (ENABLE_LOG and LOG_EDITS and LOG_NOTICEBOARD) log_action(ucwords(NOTICEBOARD_NAME_SINGULAR).' ID:'.$noticeboard->id.' ('.$noticeboard->title.') edited.');
			} else {
				$listing_added = false;
				$main_html .= $i.'<span class="message">'.ucwords(NOTICEBOARD_NAME_SINGULAR).' could not be edited</span><br /><br />'."\n";
				if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: '.ucwords(NOTICEBOARD_NAME_SINGULAR).' ID:'.$noticeboard->id.' ('.$noticeboard->title.') edited.');
				if (ENABLE_ERROR_LOG) log_error('FAILED: '.ucwords(NOTICEBOARD_NAME_SINGULAR).' ID:'.$noticeboard->id.' ('.$noticeboard->title.') edited.<br />Error: '.$noticeboard->error);
			}
		}
		
	}
	
	if ($_POST['submit'] == 'Delete' and isset($_POST['noticeboard_id'])) {
		if ($noticeboard->delete($_POST['noticeboard_id'])) {
			$main_html .= $i.'<span class="message">'.ucwords(NOTICEBOARD_NAME_SINGULAR).' deleted</span><br /><br />'."\n";
			if (ENABLE_LOG and LOG_DELETIONS and LOG_NOTICEBOARD) log_action(ucwords(NOTICEBOARD_NAME_SINGULAR).' ID:'.$_POST['noticeboard_id'].' deleted.');
		} else {
			$main_html .= $i.'<span class="message">'.ucwords(NOTICEBOARD_NAME_SINGULAR).' could not be deleted</span><br /><br />'."\n";
			if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: '.ucwords(NOTICEBOARD_NAME_SINGULAR).' ID:'.$_POST['noticeboard_id'].' deleted.');
			if (ENABLE_ERROR_LOG) log_error('FAILED: '.ucwords(NOTICEBOARD_NAME_SINGULAR).' ID:'.$_POST['noticeboard_id'].' deleted.<br />Error: '.$noticeboard->error);
		}
	}

	// check if right owner
	$owner = false;
	if (!isset($_GET['noticeboard_id'])) $_GET['noticeboard_id'] = 0;
	if ($_GET['noticeboard_id']) {
		if ($existing_noticeboard->info($_GET['noticeboard_id'])) {
			if (user_type() == 1 and ($existing_noticeboard->member_id == $_SESSION["member_id"])) {
				$owner = true;
			}
		}
	}
	if ($inserted_noticeboard_id) {
		if ($existing_noticeboard->info($inserted_noticeboard_id)) {
			if (user_type() == 1 and ($existing_noticeboard->member_id == $_SESSION["member_id"])) {
				$owner = true;
			}
		}
	}
	if (user_type() == 2) {
		$owner = true;
	}
	
	// validate image
	if (ENABLE_IMAGES and $owner) {
		$image_message = '';
		if (($_POST['submit'] == 'Add' or $_POST['submit'] == 'Add Image') and ($inserted_noticeboard_id or $_GET['noticeboard_id'])) {
			if ($inserted_noticeboard_name) {
				$name = $inserted_noticeboard_name; 
			} elseif ($_GET['noticeboard_id']) {
				if ($noticeboard->info($_GET['noticeboard_id'])) $noticeboard->title; else $name = '';
			} else {
				$name = '';
			}
			if ($inserted_noticeboard_id) $field_value = $inserted_noticeboard_id; else $field_value = $_GET['noticeboard_id'];
			if ($image->substantiate($_SESSION["member_id"],'noticeboardID',$field_value,IMAGE_WIDTH_THUMB_NOTICEBOARD,IMAGE_HEIGHT_THUMB_NOTICEBOARD,IMAGE_WIDTH_PAGE_NOTICEBOARD,IMAGE_HEIGHT_PAGE_NOTICEBOARD,$name)) {
				$image_message .= $image->validate_form($i);
				if (empty($image_message)) {
					if (!$image->add()) {
						$image_message .= $image->error;
					} else {
						if ($image->id) {
							if (!$noticeboard->set_default_image($field_value,$image->id)) {
								$image_message .= $noticeboard->error;
							} else {
							// We did it !!!!!!
								$main_html .= $i."Image added<br />\n";
							}
						}
					}
				} else {
					$image_message .= $image->error;
				}
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
			if (empty($image_message) and !empty($_GET['noticeboard_id'])) {
				$noticeboard->rebuild_default_image($_GET['noticeboard_id']);
			}
		}
		
		if (!empty($image_message)) $message .= $image_message;
	}
	// ************ end of actions ********************
}

//**********************************************
//			Main Page
if ($message) {
	$message = $i.'<span class="message">'.indent_variable($i,$message)."</span><br /><br />\n";
}
// list if entries
$main_html .= $i."<div id=\"noticeboard_member_holder\">\n";
$q = 'SELECT * FROM noticeboard WHERE accountID = '.$_SESSION["member_id"].' AND bought = 0 AND expired = 0';
if ($mysql->build_array($q)) {
	$main_html .= $i."<h2>Your Current ".ucwords(NOTICEBOARD_NAME_PLURAL)."</h2>\n";
	if ($mysql->num_rows) {
		$main_html .= $noticeboard->member_list($i,remove_slashes($mysql->result),$url);
	} else {
		$main_html .= $i."You have no current ".strtolower(NOTICEBOARD_NAME_PLURAL)."\n";
	}
	$main_html .= $i."<br class=\"left\" />\n";
}

// entry form and message if any
if (!empty($_GET['noticeboard_id'])) {
	if ($existing_noticeboard->info($_GET['noticeboard_id'])) {
		if ($noticeboard_edited) {
			$noticeboard->clear();
			$main_html .= $i."<h2>Add a new ".ucwords(NOTICEBOARD_NAME_SINGULAR)."</h2>\n";
			$main_html .= $noticeboard->form_html($i,'add',$url);
		} else {
			$main_html .= $i."<h2>Edit entry #".$_GET['noticeboard_id']." or <a href=\"".URL.$url.append_url(0)."\">Add a new ".strtolower(NOTICEBOARD_NAME_SINGULAR)."</a></h2>\n";
			if (!$_POST['submit']) {
				$main_html .= $existing_noticeboard->form_html($i,'edit',$url.$_GET['noticeboard_id'].'/');
			} else {
				$main_html .= $noticeboard->form_html($i,'edit',$url.$_GET['noticeboard_id'].'/');
			}
		}
	} else {
		if ($listing_added) {
			$existing_noticeboard->clear();
		}
		$main_html .= $i."<h2>Add a new ".ucwords(NOTICEBOARD_NAME_SINGULAR)."</h2>\n";
		$main_html .= $existing_noticeboard->form_html($i,'add',$url);
	}
} else {
	if ($listing_added) {
		$noticeboard->clear();
	}
	$main_html .= $i."<h2>Add a new ".ucwords(NOTICEBOARD_NAME_SINGULAR)."</h2>\n";
	$main_html .= $noticeboard->form_html($i,'add',$url);
}
$main_html .= $i."</div>\n";



?>