<?php
/***********************************************************************************************************************************
*		Page:			validate_events.php
*		Access:			Admin
*		Purpose:		If required an admin can review and validate un-validated events
*		HTML Holders:	$main_html	:	Entire Contents
*		Template File:																											*/
			$template_filename 				= 		'default';
/*		Classes:																												*/
			require_once('includes/classes/events.class.php');
			$events 						= 		new events;
/*		Indentation:																											
			Set $main_indent on index.php
/*		Disable "Print Page" Link on this page:																					*/
			$disable_print_page				=		true;
/*		CSS Files Called by script:																								*/
			if (!$print) {
				$styles 					.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/events_list.css);\n";
				$styles 					.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/event_form.css);\n";
			}
/*		Dynamic Styling:																										*/
/*		Javascript:																												*/
/*		Set Universal Page elements:																							*/
			$links->page_info(1,107);
			$page_name 						= 		$links->name;
			$url 							= 		$links->url;
			$blurb 							= 		$links->body;
/*		Page Title:																												*/
			$title 							= 		SITE_NAME.' '.$page_name;
/*		Page Heading																											*/
			$main_html 						= 		$i."<h1>".SITE_NAME.' '.$page_name."</h1>\n";
/*
************************************************************************************************************************************/

// ******************
// actions
// ******************
if (!isset($_POST['submit'])) $_POST['submit'] = '';
if ($_POST['submit'] == 'Validate '.ucwords(EVENTS_NAME_SINGULAR) and $_POST['event_id']) {
	if ($events->validate($_POST['event_id'])) {
		$main_html .= $i.'<span class="message">'.ucwords(EVENTS_NAME_SINGULAR).' Validated</span><br /><br />'."\n";
		if (ENABLE_EMAIL and $events->build_event($_POST['event_id'])) {
			bulk_membership_email('receive_email_events',$_SESSION['member_name'].' has added '.a(EVENTS_NAME_SINGULAR).' '.strtolower(EVENTS_NAME_SINGULAR).' entitled "'.$events->title."\"\r\n\r\n\r\nClick the following link to view this ".strtolower(EVENTS_NAME_SINGULAR).": ".URL.EVENTS_URL.'/'.$events->id.'/',$_SESSION['member_name'].' has added '.a(EVENTS_NAME_SINGULAR).' '.strtolower(EVENTS_NAME_SINGULAR).' entitled <strong>'.$events->title.'</strong><br /><br />Click <a href="'.URL.EVENTS_URL.'/'.$events->id.'/">here</a> to view this '.strtolower(EVENTS_NAME_SINGULAR).'.',ucfirst(a(EVENTS_NAME_SINGULAR)).' new '.strtolower(EVENTS_NAME_SINGULAR).' has been posted',EMAIL_FROM_NAME);
		}
	}
}
if ($_POST['submit'] == 'Delete '.ucwords(EVENTS_NAME_SINGULAR) and $_POST['event_id']) {
	if ($events->delete($_POST['event_id'])) {
		$main_html .= $i.'<span class="message">'.ucwords(EVENTS_NAME_SINGULAR).' Deleted</span><br /><br />'."\n";
	}
}

// ******************
// main page
// ******************

$main_html .= $events->event_list($i,URL.EVENTS_URL.'/',0,0,0,0,false,true);


?>