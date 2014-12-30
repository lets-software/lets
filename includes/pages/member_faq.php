<?php
/***********************************************************************************************************************************
*		Page:			member_faq.php
*		Access:			Member
*		Purpose:		Member admin of FAQ
*		HTML Holders:	$main_html	:	Entire Contents
*		Template File:																											*/
			$template_filename 			= 		'default';
/*		Classes:																												*/
			require_once('includes/classes/faq.class.php');
			$faq 						= 		new faq;

/*		Indentation:																											
			Set $main_indent on index.php
/*		Disable "Print Page" Link on this page:																					*/
			$disable_print_page			=		true;
/*		CSS Files Called by script:																								*/
		if (!$print) {
			$styles 					.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/faq_form.css);\n";
			$styles 					.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/faq_list.css);\n";
/*		Dynamic Styling:																										*/
/*		Javascript:																												*/
			$javascript 				.= 		$faq->category_javascript;
			$javascript 				.= 		$faq->answer_javascript();
			$javascript_in_body 		.= 		'onload="disable_answer();"';
		}
/*		Set Universal Page elements:																							*/
			$links->page_info(1,9);
			$page_name 					= 		$links->name;
			$url 						= 		$links->url;
			$blurb 						= 		$links->body;
/*		Page Title:																												*/
			$title 						= 		SITE_NAME.' '.$page_name;
/*		Page Heading																											*/
			$main_html 					= 		$i."<h1>".SITE_NAME.' '.$page_name."</h1>\n";
/*
************************************************************************************************************************************/

// establish ownership of a faq entry if specified

$current_faq_id = 0;
$owner = false;
if (isset($_GET['faq_id'])) {
	$current_faq_id = $_GET['faq_id'];
	$existing_faq = new faq;
	if ($existing_faq->build_faq($current_faq_id)) {
		if (($_SESSION['member_id'] == $existing_faq->member_id) or (user_type() == 2)) {
			$owner = true;
		}
	}
}
// get any variables from the URL for moving up or down
$id_to_move = 0;
$id_to_replace = 0;
if (strpos(' '.$_SERVER['REQUEST_URI'],'?')) {
	$y = explode('?',$_SERVER['REQUEST_URI']);
	if (strpos(' '.$y[1],'&')) {
		$pairs = explode('&',$y[1]);
		foreach($pairs as $pair) {
			if (strpos(' '.$pair,'=')) {
				$x = explode('=',$pair);
				$variable = $x[0];
				$value = $x[1];
				if ($variable == 'faq_id') $id_to_move = $value;
				if ($variable == 'replace_id') $id_to_replace = $value;
			}		
		}
	}
}
// if we have variables move the FAQ as long as we're allowed
if ($id_to_move and $id_to_replace and (!RESTRICT_UPDOWN_LINKS or user_type() == 2)) {
	$faq->move_faq($id_to_move,$id_to_replace);
}

// *******  actions ***********
$faq_added = false;
$faq_edited = false;
if (!isset($_POST['submit'])) $_POST['submit'] = '';
if ($_POST['submit']) {
	// *******************************************
	//      faq actions
	// *******************************************
	// validate form data
	if ($_POST['submit'] == 'Add '.ucwords(FAQ_NAME_SINGULAR) or $_POST['submit'] == 'Edit '.ucwords(FAQ_NAME_SINGULAR)) {
		if ($faq->validate_form()) {
			if ($_POST['submit'] == 'Add '.ucwords(FAQ_NAME_SINGULAR)) {
				if ($faq->add()) {
					$faq_added = true;
					$owner = true;
					$main_html .= $i.'<span class="message">'.ucwords(FAQ_NAME_SINGULAR).' Added';
					if (VALIDATE_FAQ and user_type() != 2) {
						$main_html .= ' - Your submission will not show up until it has been validated';
						if (EMAIL_VALIDATION_SUBMISSIONS and $links->build_url(1,9)) {
							send_single_email(UPDATE_EMAIL,EMAIL_FROM_NAME,VALIDATION_EMAIL,ucwords(FAQ_NAME_SINGULAR).' Reviewer','A new '.strtolower(FAQ_NAME_SINGULAR).' has been submitted','A new '.strtolower(FAQ_NAME_SINGULAR).' entitled "'.$faq->question.'" has been submitted'."\r\n\r\n You can review and validate this ".strtolower(FAQ_NAME_SINGULAR).' here: '.URL.$links->complete_url,'A new '.strtolower(FAQ_NAME_SINGULAR).' entitled "'.$faq->question.'" has been submitted<br /><br />You can review and validate this '.strtolower(FAQ_NAME_SINGULAR).' <a href="'.URL.$links->complete_url.'">here</a>');
						}
					} else {
						if (ENABLE_EMAIL) {
							bulk_membership_email('receive_email_faq',$_SESSION['member_name'].' has added '.a(FAQ_NAME_SINGULAR).' '.strtolower(FAQ_NAME_SINGULAR).' entitled "'.$faq->question."\"\r\n\r\n\r\nClick the following link to view this ".strtolower(FAQ_NAME_SINGULAR).": ".URL.FAQ_URL.'/#'.$faq->id,$_SESSION['member_name'].' has added '.a(FAQ_NAME_SINGULAR).' '.strtolower(FAQ_NAME_SINGULAR).' entitled <strong>'.$faq->question.'</strong><br /><br />Click <a href="'.URL.FAQ_URL.'/#'.$faq->id.'">here</a> to view this '.strtolower(FAQ_NAME_SINGULAR).'.','A new '.strtolower(FAQ_NAME_SINGULAR).' has been posted',EMAIL_FROM_NAME);
						}
					}
					$main_html .= '</span><br /><br />'."\n";
					if (ENABLE_LOG and LOG_ADDITIONS and LOG_FAQ) log_action(ucwords(FAQ_NAME_SINGULAR).' ID:'.$faq->id.' ('.$faq->question.') added.');
				} else {
					$main_html .= $i.'<span class="message">'.ucwords(FAQ_NAME_SINGULAR).' could not be added</span><br /><br />'."\n";
					if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: '.ucwords(FAQ_NAME_SINGULAR).' ID:'.$faq->id.' ('.$faq->question.') added.');
					if (ENABLE_ERROR_LOG) log_error('FAILED: '.ucwords(FAQ_NAME_SINGULAR).' ID:'.$faq->id.' ('.$faq->question.') added.<br />Error:'.$faq->error);
				}
			} // add faq
			if ($_POST['submit'] == 'Edit '.ucwords(FAQ_NAME_SINGULAR) and $current_faq_id and $owner) {
				$faq->id = $current_faq_id;
				if ($faq->edit()) {
					$main_html .= $i.'<span class="message">'.ucwords(FAQ_NAME_SINGULAR).' <strong>'.$faq->question.'</strong> edited</span><br /><br />'."\n";
					$faq_edited = true;
					if (ENABLE_LOG and LOG_EDITS and LOG_FAQ) log_action(ucwords(FAQ_NAME_SINGULAR).' ID:'.$faq->id.' ('.$faq->question.') edited.');
				} else {
					$main_html .= $i.'<span class="message">'.ucwords(FAQ_NAME_SINGULAR).' <strong>'.$faq->question.'</strong> could not be edited</span><br /><br />'."\n";
					if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: '.ucwords(FAQ_NAME_SINGULAR).' ID:'.$faq->id.' ('.$faq->question.') edited.');
					if (ENABLE_ERROR_LOG) log_error('FAILED: '.ucwords(FAQ_NAME_SINGULAR).' ID:'.$faq->id.' ('.$faq->question.') edited.<br />Error:'.$faq->error);
				}				
			} // edit faq
			if ($_POST['submit'] == 'Validate '.ucwords(FAQ_NAME_SINGULAR) and $current_faq_id and $owner) {
				if ($faq->validate($current_faq_id)) {
					$main_html .= $i.'<span class="message">'.ucwords(FAQ_NAME_SINGULAR).' <strong>'.$existing_faq->question.'</strong> validated</span><br /><br />'."\n";
					if (ENABLE_EMAIL) {
						bulk_membership_email('receive_email_faq',$_SESSION['member_name'].' has added '.a(FAQ_NAME_SINGULAR).' '.strtolower(FAQ_NAME_SINGULAR).' entitled "'.$existing_faq->question."\"\r\n\r\n\r\nClick the following link to view this ".strtolower(FAQ_NAME_SINGULAR).": ".URL.FAQ_URL.'/#'.$current_faq_id,$_SESSION['member_name'].' has added '.a(FAQ_NAME_SINGULAR).' '.strtolower(FAQ_NAME_SINGULAR).' entitled <strong>'.$existing_faq->question.'</strong><br /><br />Click <a href="'.URL.FAQ_URL.'/#'.$current_faq_id.'">here</a> to view this '.strtolower(FAQ_NAME_SINGULAR).'.','A new '.strtolower(FAQ_NAME_SINGULAR).' has been posted',EMAIL_FROM_NAME);
					}
				} else {
					$main_html .= $i.'<span class="message">Could not validate '.ucwords(FAQ_NAME_SINGULAR).'</span><br /><br />'."\n";
					if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: '.ucwords(FAQ_NAME_SINGULAR).' ID:'.$current_faq_id.' ('.$existing_faq->question.') validated.');
					if (ENABLE_ERROR_LOG) log_error('FAILED: '.ucwords(FAQ_NAME_SINGULAR).' ID:'.$current_faq_id.' ('.$existing_faq->question.') validated.<br />Error:'.$faq->error);
				}
			} // validate faq
		} else { // form errors
			$main_html .= $i.'<span class="message">'.$faq->error.'</span><br /><br />'."\n";
		}// validate form data
	} // add/edit faq
}
if ($_POST['submit'] == 'Delete '.ucwords(FAQ_NAME_SINGULAR) and $current_faq_id and $owner) {
	if ($faq->get_dependents($current_faq_id)) {
		if ($_POST['deletion_confirmed']) {
			if ($faq->delete($current_faq_id)) {
				$faq->clear();
				$main_html .= $i.'<span class="message">'.ucwords(FAQ_NAME_SINGULAR).' <strong>'.$existing_faq->question.'</strong> deleted</span><br /><br />'."\n";
				if (ENABLE_LOG and LOG_DELETIONS and LOG_FAQ) log_action(ucwords(FAQ_NAME_SINGULAR).' ID:'.$current_faq_id.' deleted.');
			} else {
				$main_html .= $i.'<span class="message">Could not delete '.ucwords(FAQ_NAME_SINGULAR).'</span><br /><br />'."\n";
				if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: '.ucwords(FAQ_NAME_SINGULAR).' ID:'.$current_faq_id.' deleted.');
				if (ENABLE_ERROR_LOG) log_error('FAILED: '.ucwords(FAQ_NAME_SINGULAR).' ID:'.$current_faq_id.' deleted.<br />Error:'.$faq->error);
			}
		}					
	} else {
		if ($faq->delete($current_faq_id)) {
			$faq->clear();
			$main_html .= $i.'<span class="message">'.ucwords(FAQ_NAME_SINGULAR).' <strong>'.$faq->question.'</strong> deleted</span><br /><br />'."\n";
			if (ENABLE_LOG and LOG_DELETIONS and LOG_FAQ) log_action(ucwords(FAQ_NAME_SINGULAR).' ID:'.$current_faq_id.' deleted.');
		} else {
			$main_html .= $i.'<span class="message">Could not delete '.ucwords(FAQ_NAME_SINGULAR).'</span><br /><br />'."\n";
			if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: '.ucwords(FAQ_NAME_SINGULAR).' ID:'.$current_faq_id.' deleted.');
			if (ENABLE_ERROR_LOG) log_error('FAILED: '.ucwords(FAQ_NAME_SINGULAR).' ID:'.$current_faq_id.' deleted.<br />Error:'.$faq->error);
		}
	}
} // delete faq


if (isset($_POST['deletion_confirmed']) and $_POST['submit'] == 'Delete '.ucwords(FAQ_NAME_SINGULAR) and $current_faq_id and ($faq->num_sub_categories or $faq->num_links)) {
	$main_html .= $faq->confirm_deletion($i,$current_faq_id,$url);
} else {
	$main_html .= $faq->faq_list($i,$url,1,2,$_SESSION['member_id']);
	$main_html .= $i.'<br /><br />'."\n";
	if ($current_faq_id and !$faq_edited) {
		if ($faq->build_faq($current_faq_id)) {
			if (($_SESSION['member_id'] == $faq->member_id) or (user_type() == 2)) {
				$faq->id = $current_faq_id;
				$main_html .= $faq->form_html($i,'edit',$url);
			} else {
				$main_html .= $i.'<span class="message">You are not authorized to edit this '.strtolower(FAQ_NAME_SINGULAR).'</span><br /><br />'."\n";
			}
		} else {
			$main_html .= $faq->form_html($i,'add',$url);
		}	
	} else {
		if ($faq_added or $faq_edited) $faq->clear();
		$main_html .= $faq->form_html($i,'add',$url);
	}
}




?>