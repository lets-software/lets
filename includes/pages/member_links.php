<?
/***********************************************************************************************************************************
*		Page:			member_links.php
*		Access:			Member
*		Purpose:		Member admin of Links
*		HTML Holders:	$main_html		:		Entire Contents
*		Template File:																											*/
			$template_filename 			= 		'default';
/*		Classes:																												*/
			require_once('includes/classes/link.class.php');
			$lets_links = new lets_links;
/*		Indentation:																											
			Set $main_indent index.php
/*		Disable "Print Page" Link on this page:																					*/
			$disable_print_page			=		true;
/*		CSS Files Called by script:																								*/
		if (!$print) {
			$styles 					.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/links_form.css);\n";
			$styles 					.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/links_list.css);\n";
/*		Dynamic Styling:																										*/
/*		Javascript:																												*/
			$javascript 				.= 		$lets_links->category_javascript;
			$javascript 				.= 		$lets_links->description_javascript();
			$javascript_in_body 		.= 		'onload="disable_description();"';
		}
/*		Set Universal Page elements:																							*/
			$links->page_info(1,10);
			$page_name 					= 		$links->name;
			$url 						= 		$links->url;
			$blurb 						= 		$links->body;
/*		Page Title:																												*/
			$title 						= 		SITE_NAME.' '.$page_name;
/*		Page Heading																											*/
			$main_html 					= 		$i."<h1>".SITE_NAME.' '.$page_name."</h1>\n";
/*
************************************************************************************************************************************/

// establish ownership of a link entry if specified

$current_link_id = 0;
$owner = false;
if (isset($_GET['link_id'])) {
	$current_link_id = $_GET['link_id'];
	$existing_links = new lets_links;
	if ($existing_links->build_link($current_link_id)) {
		if (($_SESSION['member_id'] == $existing_links->member_id) or (user_type() == 2)) {
			$owner = true;
		}
	}
}

$id_to_move = return_link_variable("id_to_move",0);
$id_to_replace = return_link_variable("id_to_replace",0);

if ($id_to_move and $id_to_replace and (!RESTRICT_UPDOWN_LINKS or user_type() == 2)) {
	$lets_links->move_link($id_to_move,$id_to_replace);
}

// *******  actions ***********
$link_added = false;
$link_edited = false;
if (!isset($_POST['submit'])) $_POST['submit'] = '';
if ($_POST['submit']) {
	// *******************************************
	//      article actions
	// *******************************************
	if ($_POST['submit'] == 'Add '.ucwords(LINKS_NAME_SINGULAR) or $_POST['submit'] == 'Edit '.ucwords(LINKS_NAME_SINGULAR) or $_POST['submit'] == 'Validate '.ucwords(LINKS_NAME_SINGULAR)) {
		// validate form data
		if ($lets_links->validate_form()) {
			if ($_POST['submit'] == 'Add '.ucwords(LINKS_NAME_SINGULAR)) {
				if ($lets_links->add()) {
					$link_added = true;
					$owner = true;
					$main_html .= $i.'<span class="message">'.ucwords(LINKS_NAME_SINGULAR).' Added';
					if (VALIDATE_LINKS and user_type() != 2) {
						$main_html .= ' - Your submission will not show up until it has been validated';
						if (EMAIL_VALIDATION_SUBMISSIONS and $links->build_url(1,10)) {
							send_single_email(UPDATE_EMAIL,EMAIL_FROM_NAME,VALIDATION_EMAIL,ucwords(LINKS_NAME_SINGULAR).' Reviewer','A new '.strtolower(LINKS_NAME_SINGULAR).' has been submitted','A new '.strtolower(LINKS_NAME_SINGULAR).' entitled "'.$lets_links->title.'" has been submitted'."\r\n\r\n You can review and validate this ".strtolower(LINKS_NAME_SINGULAR).' here: '.URL.$links->complete_url,'A new '.strtolower(LINKS_NAME_SINGULAR).' entitled "'.$lets_links->title.'" has been submitted<br /><br />You can review and validate this '.strtolower(LINKS_NAME_SINGULAR).' <a href="'.URL.$links->complete_url.'">here</a>');
						}
					} else {
						if (ENABLE_EMAIL) {
							bulk_membership_email('receive_email_url',$_SESSION['member_name'].' has added '.a(LINKS_NAME_SINGULAR).' '.strtolower(LINKS_NAME_SINGULAR).' entitled "'.$lets_links->title."\"\r\n\r\n\r\nClick the following link to view this ".strtolower(LINKS_NAME_SINGULAR).": ".URL.LINKS_URL.'/#'.$lets_links->id,$_SESSION['member_name'].' has added '.a(LINKS_NAME_SINGULAR).' '.strtolower(LINKS_NAME_SINGULAR).' entitled <strong>'.$lets_links->title.'</strong><br /><br />Click <a href="'.URL.LINKS_URL.'/#'.$lets_links->id.'">here</a> to view this '.strtolower(LINKS_NAME_SINGULAR).'.','A new '.strtolower(LINKS_NAME_SINGULAR).' has been posted',EMAIL_FROM_NAME);
						}
					}
					$main_html .= '</span><br /><br />'."\n";
					if (ENABLE_LOG and LOG_ADDITIONS and LOG_LINKS) log_action(ucwords(LINKS_NAME_SINGULAR).' ID:'.$lets_links->id.' ('.$lets_links->title.') added.');
				} else {
					$main_html .= $i.'<span class="message">'.ucwords(LINKS_NAME_SINGULAR).' could not be added</span><br /><br />'."\n";
					if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: '.ucwords(LINKS_NAME_SINGULAR).' ID:'.$lets_links->id.' ('.$lets_links->title.') added.');
					if (ENABLE_ERROR_LOG) log_error('FAILED: '.ucwords(LINKS_NAME_SINGULAR).' ID:'.$lets_links->id.' ('.$lets_links->title.') added.<br />Error:'.$lets_links->error);
				}
			} // add article
			if ($_POST['submit'] == 'Edit '.ucwords(LINKS_NAME_SINGULAR) and $current_link_id and $owner) {
				$lets_links->id = $current_link_id;
				if ($lets_links->edit()) {
					$main_html .= $i.'<span class="message">'.ucwords(LINKS_NAME_SINGULAR).' <strong>'.$lets_links->title.'</strong> edited</span><br /><br />'."\n";
					$link_edited = true;
					if (ENABLE_LOG and LOG_EDITS and LOG_LINKS) log_action(ucwords(LINKS_NAME_SINGULAR).' ID:'.$lets_links->id.' ('.$lets_links->title.') edited.');
				} else {
					$main_html .= $i.'<span class="message">'.ucwords(LINKS_NAME_SINGULAR).' <strong>'.$lets_links->title.'</strong> could not be edited</span><br /><br />'."\n";
					if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: '.ucwords(LINKS_NAME_SINGULAR).' ID:'.$lets_links->id.' ('.$lets_links->title.') edited.');
					if (ENABLE_ERROR_LOG) log_error('FAILED: '.ucwords(LINKS_NAME_SINGULAR).' ID:'.$lets_links->id.' ('.$lets_links->title.') edited.<br />Error:'.$lets_links->error);
				}				
			} // edit article
			if ($_POST['submit'] == 'Validate '.ucwords(LINKS_NAME_SINGULAR) and $current_link_id and $owner) {
				if ($lets_links->validate($current_link_id)) {
					$main_html .= $i.'<span class="message">'.ucwords(LINKS_NAME_SINGULAR).' <strong>'.$existing_links->title.'</strong> validated</span><br /><br />'."\n";
					if (ENABLE_EMAIL) {
						bulk_membership_email('receive_email_url',$_SESSION['member_name'].' has added '.a(LINKS_NAME_SINGULAR).' '.strtolower(LINKS_NAME_SINGULAR).' entitled "'.$existing_links->title."\"\r\n\r\n\r\nClick the following link to view this ".strtolower(LINKS_NAME_SINGULAR).": ".URL.LINKS_URL.'/#'.$current_link_id,$_SESSION['member_name'].' has added '.a(LINKS_NAME_SINGULAR).' '.strtolower(LINKS_NAME_SINGULAR).' entitled <strong>'.$existing_links->title.'</strong><br /><br />Click <a href="'.URL.LINKS_URL.'/#'.$current_link_id.'">here</a> to view this '.strtolower(LINKS_NAME_SINGULAR).'.','A new '.strtolower(LINKS_NAME_SINGULAR).' has been posted',EMAIL_FROM_NAME);
					}
				} else {
					$main_html .= $i.'<span class="message">Could not validate '.ucwords(LINKS_NAME_SINGULAR).'</span><br /><br />'."\n";
					if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: '.ucwords(LINKS_NAME_SINGULAR).' ID:'.$current_link_id.' ('.$existing_links->title.') validated.');
					if (ENABLE_ERROR_LOG) log_error('FAILED: '.ucwords(LINKS_NAME_SINGULAR).' ID:'.$current_link_id.' ('.$existing_links->title.') validated.<br />Error:'.$lets_links->error);
				}
			} // validate article
		} else { // form errors
			$main_html .= $i.'<span class="message">'.$lets_links->error.'</span><br /><br />'."\n";
		}// validate form data
	} // add/edit article
	
	if ($_POST['submit'] == 'Delete '.ucwords(LINKS_NAME_SINGULAR) and $current_link_id and $owner) {
		if ($lets_links->get_dependents($current_link_id)) {
			if ($_POST['deletion_confirmed']) {
				if ($lets_links->delete($current_link_id)) {
					$lets_links->clear();
					$main_html .= $i.'<span class="message">'.ucwords(LINKS_NAME_SINGULAR).' <strong>'.$existing_links->title.'</strong> deleted</span><br /><br />'."\n";
					if (ENABLE_LOG and LOG_DELETIONS and LOG_LINKS) log_action(ucwords(LINKS_NAME_SINGULAR).' ID:'.$current_link_id.' ('.$existing_links->title.') deleted.');
				} else {
					$main_html .= $i.'<span class="message">Could not delete '.ucwords(LINKS_NAME_SINGULAR).'</span><br /><br />'."\n";
					if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: '.ucwords(LINKS_NAME_SINGULAR).' ID:'.$current_link_id.' ('.$existing_links->title.') deleted.');
					if (ENABLE_ERROR_LOG) log_error('FAILED: '.ucwords(LINKS_NAME_SINGULAR).' ID:'.$current_link_id.' ('.$existing_links->title.') deleted.<br />Error:'.$lets_links->error);
				}
			}					
		} else {
			if ($lets_links->delete($current_link_id)) {
				$lets_links->clear();
				$main_html .= $i.'<span class="message">'.ucwords(LINKS_NAME_SINGULAR).' <strong>'.$existing_links->title.'</strong> deleted</span><br /><br />'."\n";
				if (ENABLE_LOG and LOG_DELETIONS and LOG_LINKS) log_action(ucwords(LINKS_NAME_SINGULAR).' ID:'.$current_link_id.' ('.$existing_links->title.') deleted.');
			} else {
				$main_html .= $i.'<span class="message">Could not delete '.ucwords(LINKS_NAME_SINGULAR).'</span><br /><br />'."\n";
				if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: '.ucwords(LINKS_NAME_SINGULAR).' ID:'.$current_link_id.' ('.$existing_links->title.') deleted.');
				if (ENABLE_ERROR_LOG) log_error('FAILED: '.ucwords(LINKS_NAME_SINGULAR).' ID:'.$current_link_id.' ('.$existing_links->title.') deleted.<br />Error:'.$lets_links->error);
			}
		}
	} // delete article
}


if (isset($_POST['deletion_confirmed']) and $_POST['submit'] == 'Delete '.ucwords(LINKS_NAME_SINGULAR) and $current_link_id and ($lets_links->num_sub_categories or $lets_links->num_links)) {
	$main_html .= $lets_links->confirm_deletion($i,$current_link_id,$url);
} else {
	$main_html .= $lets_links->link_list($i,$url,1,2,$_SESSION['member_id']);
	$main_html .= $i.'<br /><br />'."\n";
	if ($current_link_id and !$link_edited) {
		if ($lets_links->build_link($current_link_id)) {
			if (($_SESSION['member_id'] == $lets_links->member_id) or (user_type() == 2)) {
				$lets_links->id = $current_link_id;
				$main_html .= $lets_links->form_html($i,'edit',$url);
			} else {
				$main_html .= $i.'<span class="message">You are not authorized to edit this '.strtolower(LINKS_NAME_SINGULAR).'</span><br /><br />'."\n";
			}
		} else {
			$main_html .= $lets_links->form_html($i,'add',$url);
		}	
	} else {
		if ($link_added or $link_edited) $lets_links->clear();
		$main_html .= $lets_links->form_html($i,'add',$url);
	}
}



?>