<?
/***********************************************************************************************************************************
*		Page:			member_events.php
*		Access:			Member
*		Purpose:		Member admin of events
*		HTML Holders:	$main_html	:	Entire Contents
*		Template File:																											*/
			$template_filename 			= 		'default';
/*		Classes:																												*/
			require_once('includes/classes/events.class.php');
			$events 					= 		new events;
/*		Indentation:																											
			Set $main_indent on index.php
/*		Disable "Print Page" Link on this page:																					*/
			$disable_print_page			=		true;
/*		CSS Files Called by script:																								*/
		if (!$print) {
			$styles 					.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/event_form.css);\n";
			$styles 					.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/events_list.css);\n";
		}
/*		Dynamic Styling:																										*/
/*		Javascript:																												*/
/*		Set Universal Page elements:																							*/
			$links->page_info(1,8);
			$page_name 					= 		$links->name;
			$url 						= 		$links->url;
			$blurb 						= 		$links->body;
/*		Page Title:																												*/
			$title 						= 		SITE_NAME.' '.$page_name;
/*		Page Heading																											*/
			$main_html 					= 		$i."<h1>".SITE_NAME.' '.$page_name."</h1>\n";
/*
************************************************************************************************************************************/

// establish if a specific event is being viewed and if that is allowed
$current_event_id = 0;
$owner = false;
if (!isset($_GET['event_id'])) $_GET['event_id'] = 0;
if ($_GET['event_id']) {
	$current_event_id = $_GET['event_id'];
	$existing_event = new events;
	if ($existing_event->build_event($current_event_id)) {
		if (($_SESSION['member_id'] == $existing_event->member_id) or (user_type() == 2)) {
			$owner = true;
		}
	}
}



$event_added = false;
$event_edited = false;
$event_deleted = false;
if (isset($_POST['submit'])) {
	// *******************************************
	//      article actions
	// *******************************************
	if ($_POST['submit'] == 'Add '.ucwords(EVENTS_NAME_SINGULAR) or $_POST['submit'] == 'Edit '.ucwords(EVENTS_NAME_SINGULAR) or $_POST['submit'] == 'Delete '.ucwords(EVENTS_NAME_SINGULAR)) {
		// /validate form data
		if ($events->validate_form()) {
			if ($_POST['submit'] == 'Add '.ucwords(EVENTS_NAME_SINGULAR)) {
				if ($events->add()) {
					$event_added = true;
					$owner = true;
					$main_html .= $i.'<span class="message">'.ucwords(EVENTS_NAME_SINGULAR).' Added';
					if (VALIDATE_EVENTS and user_type() != 2) {
						$main_html .= ' - Your '.strtolower(EVENTS_NAME_SINGULAR).' will not show up until it has been validated';
						if (EMAIL_VALIDATION_SUBMISSIONS and $links->build_url(1,107)) {
							send_single_email(UPDATE_EMAIL,EMAIL_FROM_NAME,VALIDATION_EMAIL,ucwords(EVENTS_NAME_SINGULAR).' Reviewer','A new '.strtolower(EVENTS_NAME_SINGULAR).' has been submitted','A new '.strtolower(EVENTS_NAME_SINGULAR).' entitled "'.$events->title.'" has been submitted'."\r\n\r\n You can review and validate this ".strtolower(EVENTS_NAME_SINGULAR).' here: '.URL.$links->complete_url,'A new '.strtolower(EVENTS_NAME_SINGULAR).' entitled "'.$events->title.'" has been submitted<br /><br />You can review and validate this '.strtolower(EVENTS_NAME_SINGULAR).' <a href="'.URL.$links->complete_url.'">here</a>');
						}
					} else {
						if (ENABLE_EMAIL) {
							bulk_membership_email('receive_email_events',$_SESSION['member_name'].' has added '.a(EVENTS_NAME_SINGULAR).' '.strtolower(EVENTS_NAME_SINGULAR).' entitled "'.$events->title."\"\r\n\r\n\r\nClick the following link to view this ".strtolower(EVENTS_NAME_SINGULAR).": ".URL.EVENTS_URL.'/'.$events->id.'/',$_SESSION['member_name'].' has added '.a(EVENTS_NAME_SINGULAR).' '.strtolower(EVENTS_NAME_SINGULAR).' entitled <strong>'.$events->title.'</strong><br /><br />Click <a href="'.URL.EVENTS_URL.'/'.$events->id.'/">here</a> to view this '.strtolower(EVENTS_NAME_SINGULAR).'.',ucfirst(a(EVENTS_NAME_SINGULAR)).' new '.strtolower(EVENTS_NAME_SINGULAR).' has been posted',EMAIL_FROM_NAME);
						}
					}
					$main_html .= '</span><br /><br />'."\n";
					if (ENABLE_LOG and LOG_ADDITIONS and LOG_EVENTS) log_action(ucwords(EVENTS_NAME_SINGULAR).' ID:'.$events->id.' ('.$events->title.') added.');
				} else {
					$main_html .= $i.'<span class="message">'.ucwords(EVENTS_NAME_SINGULAR).' could not be added</span><br /><br />'."\n";
					if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: '.ucwords(EVENTS_NAME_SINGULAR).' ID:'.$events->id.' ('.$events->title.') added.');
					if (ENABLE_ERROR_LOG) log_error('FAILED: '.ucwords(EVENTS_NAME_SINGULAR).' ID:'.$events->id.' ('.$events->title.') added.<br />Error:'.$events->error);
				}
			} // /add article
			if ($_POST['submit'] == 'Edit '.ucwords(EVENTS_NAME_SINGULAR) and $current_event_id and $owner) {
				$events->id = $current_event_id;
				if ($events->edit()) {
					$main_html .= $i.'<span class="message">'.ucwords(EVENTS_NAME_SINGULAR).' <strong>'.$existing_event->title.'</strong> edited</span><br /><br />'."\n";
					$event_edited = true;
					if (ENABLE_LOG and LOG_EDITS and LOG_EVENTS) log_action(ucwords(EVENTS_NAME_SINGULAR).' ID:'.$events->id.' ('.$events->title.') edited.');
				} else {
					$main_html .= $i.'<span class="message">'.ucwords(EVENTS_NAME_SINGULAR).' could not be edited</span><br /><br />'."\n";
					if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: '.ucwords(EVENTS_NAME_SINGULAR).' ID:'.$events->id.' ('.$events->title.') edited.');
					if (ENABLE_ERROR_LOG) log_error('FAILED: '.ucwords(EVENTS_NAME_SINGULAR).' ID:'.$events->id.' ('.$events->title.') edited.<br />Error:'.$events->error);
				}				
			} // /edit article
			if ($_POST['submit'] == 'Delete '.ucwords(EVENTS_NAME_SINGULAR) and $current_event_id and $owner) {
				if ($events->delete($current_event_id)) {
					$main_html .= $i.'<span class="message">'.ucwords(EVENTS_NAME_SINGULAR).' <strong>'.$existing_event->title.'</strong> deleted</span><br /><br />'."\n";
					$event_deleted = true;
					if (ENABLE_LOG and LOG_DELETIONS and LOG_EVENTS) log_action(ucwords(EVENTS_NAME_SINGULAR).' ID:'.$current_event_id.' ('.$existing_event->title.') deleted.');
					$current_event_id = 0;
				} else {
					$main_html .= $i.'<span class="message">Could not delete '.ucwords(EVENTS_NAME_SINGULAR).'</span><br /><br />'."\n";
					if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: '.ucwords(EVENTS_NAME_SINGULAR).' ID:'.$current_event_id.' ('.$existing_event->title.') deleted.');
					if (ENABLE_ERROR_LOG) log_error('FAILED: '.ucwords(EVENTS_NAME_SINGULAR).' ID:'.$current_event_id.' ('.$existing_event->title.') deleted.<br />Error:'.$events->error);
				}
			} // /delete article
		} else { // form errors
			$main_html .= $i.'<span class="message">'.$events->error.'</span><br /><br />'."\n";
		}// /validate form data
	} // /add/edit article
}
$list_of_events = $events->event_list($i,URL.$url,$_SESSION['member_id'],false,0,'',true,true,false);
if ($list_of_events) {
	$main_html .= $list_of_events;
}
if ($current_event_id and !$event_edited) {
	if ($events->build_event($current_event_id)) {
		if (($_SESSION['member_id'] == $events->member_id) or (user_type() == 2)) {
			$events->id = $current_event_id;
			$main_html .= $events->form_html($i,'edit',$url);
		} else {
			$main_html .= $i.'<span class="message">You are not authorized to edit this '.strtolower(EVENTS_NAME_SINGULAR).'</span><br /><br />'."\n";
		}
	} else {
		$main_html .= $events->form_html($i,'add',$url);
	}	
} else {
	if ($event_added or $event_edited or $event_deleted) $events->clear();
	$main_html .= $events->form_html($i,'add',$url);
}



?>