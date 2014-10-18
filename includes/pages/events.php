<?
// NOTE: This is 2 pages in one. Either it is an article or a list.

require_once('includes/classes/events.class.php');
$events = new events;

$current_event_id = 0;
$owner = false;
if (!isset($_GET['event_id'])) $_GET['event_id'] = 0;
if ($_GET['event_id']) {
	$current_event_id = $_GET['event_id'];
	$existing_event = new events;
	if ($existing_event->build_event($current_event_id)) {
		if (isset($_SESSION['member_id'])) {
			if (($_SESSION['member_id'] == $existing_event->member_id) or (user_type() == 2)) {
				$owner = true;
			}
		}
	}
}


if (!$_GET['event_id']) {

/***********************************************************************************************************************************
*		Page:			events.php
*		Access:			Public
*		Purpose:		Displays a list of events if no specific
*						event is called otherwise it prints a
*						Single event
*		HTML Holders:	$heading				:		Page Heading
*						$events_search_form		:		Parameters for searching articles
*						$event_list				:		The resulting list of articles
*						$blurb					:		Additional dynamic text
*		Template File:																											*/
			$template_filename 					= 		'event_list';
/*		Classes:		events, called above to initialize the page																*/
/*		Indentation:																											*/
			$heading_indent 					= 		'   ';
			$events_search_form_indent 			= 		'   ';
			$event_list_indent 					= 		'   ';
			$blurb_indent 						= 		'   ';
/*		Disable "Print Page" Link on this page:																					*/
			$disable_print_page					=		false;
/*		CSS Files Called by script:																								*/
		if (!$print) {
			$styles 							.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/event_search_form.css);\n";
			$styles 							.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/events_list.css);\n";
		}
/*		Dynamic Styling:																										*/
/*		Set Universal Page elements:																							*/
			$links->page_info(4,0);
			$page_name 							= 		$links->name;
			$url 								= 		$links->url;
			$blurb 								= 		$links->body;
/*		Page Title:																												*/
			$title 								= 		SITE_NAME.' '.$page_name;
/*		Page Heading																											*/
			$heading 							= 		$heading_indent."<h1>".SITE_NAME.' '.$page_name."</h1>\n";
/*
************************************************************************************************************************************/

	// check to see if there are any variables in the URL
	// ** because of using mod-rewrite we have to do this manually **
	$category = return_link_variable("category",'');
	$event_member_id = return_link_variable("member",'');
	$past_events = return_link_variable("past_events",'');
	if (isset($_POST['category'])) $category = $_POST['category'];
	if (isset($_POST['member'])) $event_member_id = $_POST['member'];
	if (isset($_POST['past_events'])) $past_events = $_POST['past_events'];
	$keyword = return_link_variable("keyword",'');
	if ($keyword) {
		$keyword = trim(str_replace('_',' ',$keyword));
		$keyword = str_replace('%22','"',$keyword);
		$keyword = preg_replace("/[^0-9a-z\" ]/",'',$keyword);
		$keyword = eregi_replace(" +", ' ', $keyword);
	}
	if (isset($_POST['keyword'])) {	
		$keyword = trim($_POST['keyword']);
		$keyword = html_entity_decode($keyword);
		$keyword = preg_replace("/[^0-9a-z\" ]/",'',$keyword);
		$keyword = eregi_replace(" +", ' ', $keyword); 
	}
	
	
	// *********************************
	// 		Main Page

	// now we have all our variables to pass to the form
	$events_search_form = $events->search_form($events_search_form_indent,$event_member_id,$past_events,$category,$keyword);
	// and the results-generator
	$event_list = $event_list_indent." <span class=\"event_list_title\">Upcoming ".ucwords(EVENTS_NAME_PLURAL)."</span><br /><br />\n";
	$event_list .= $events->event_list($event_list_indent,URL.EVENTS_URL.'/',$event_member_id,$past_events,$category,$keyword,false,false,false);
} else {
/***********************************************************************************************************************************
*		Page:			articles.php
*		Access:			Public
*		Purpose:		Displays a single event
*		HTML Holders:	$heading				:	Page Heading
*						$event_page				:	Parameters for searching articles
*						$messages				:	Messages including validation HTML
						$comment_html			:	Comments for this event
*		Template File:																											*/
			$template_filename 					= 'event_page';
/*		Classes:		comments (called below as needed)																		*/
			if (ENABLE_COMMENTS) {
				require_once('includes/classes/comments.class.php');
				$comments 						= 		new comments;
			}
/*		Indentation:																											*/
			$heading_indent 					= 		'   ';
			$event_page_indent		 			= 		'   ';
			$messages_indent 					= 		'   ';
			$comment_html_indent				= 		'   ';
/*		Disable "Print Page" Link on this page:																					*/
			$disable_print_page					=		false;
/*		CSS Files Called by script:
			comment validation styling called below as needed
			also comment styling called below as needed																			*/
		if (!$print) {
			$styles 							.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/event_page.css);\n";
			if (user_type() == 2) $styles 		.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/event_form.css);\n";
			if (ENABLE_COMMENTS) {
				$styles 						.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/comment_form.css);\n";
				$styles 						.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/comment_list.css);\n";
/*		Dynamic Styling:																										*/
				$style->dynamic_elements 		.= 		" div#comment_list {border-top: 1px solid ".TAB_BORDER_COLOUR."; margin-top:10px; padding-top:5px; }\n";
			}
		}
/*		Set Universal Page elements:																							*/
			$links->page_info(4,0);
			$page_name 	= $links->name;
			$url 		= $links->url;
			
/*		Page Title:																												*/
			$title = SITE_NAME.' '.$page_name;
/*		Page Heading																											*/
			$heading = $heading_indent."<h1>".SITE_NAME.' '.$page_name."</h1>\n";
/*
************************************************************************************************************************************/

if (!isset($_POST['submit'])) $_POST['submit'] = '';
//			Actions:
	$messages = '';
	if (user_type() == 2) {
		// admin can validate events on this page
		if ($_POST['submit'] == 'Validate '.ucwords(EVENTS_NAME_SINGULAR) and $_POST['event_id']) {
			if ($events->validate($_POST['event_id'])) {
				$messages .= $messages_indent.'<span class="message">'.ucwords(EVENTS_NAME_SINGULAR).' Validated</span><br /><br />'."\n";
			}
		}
		if ($_POST['submit'] == 'Delete '.ucwords(EVENTS_NAME_SINGULAR) and $_POST['event_id']) {
			if ($events->delete($_POST['event_id'])) {
				$messages .= $messages_indent.'<span class="message">'.ucwords(EVENTS_NAME_SINGULAR).' Deleted</span><br /><br />'."\n";
			}
		}
	}
	// ********************************
	// ensure this is a validated event
	// ********************************
	if ($events->build_event($_GET['event_id'])) {
		if ($events->validated or user_type() == 2) {

			// *******************
			//  comment actions
			// *******************
			if ((ENABLE_COMMENTS and user_type() and !$_SESSION['member_suspended'] and $_SESSION['member_validated']) or (ENABLE_COMMENTS and ENABLE_GUEST_COMMENTS)) {
				$comment_edited = false;
				if ($_POST['submit'] == 'Make '.ucwords(COMMENT_NAME_SINGULAR)) {
					if ($comments->validate_form('Re: '.$events->title)) {
						if ($comments->add_comment()) {
							$messages .= $messages_indent.'<span class="message">'.ucwords(COMMENT_NAME_SINGULAR).' Added</span><br /><br />'."\n";
							if (ENABLE_LOG and LOG_ADDITIONS and LOG_COMMENTS) log_action(ucwords(COMMENT_NAME_SINGULAR).' added to '.ucwords(EVENTS_NAME_SINGULAR).' ID:'.$_GET['event_id'].' ('.$events->title.')');
						} else {
							$messages .= $messages_indent.'<span class="message">Could not Add '.ucwords(COMMENT_NAME_SINGULAR).'.</span><br /><br />'."\n";
							if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: '.ucwords(COMMENT_NAME_SINGULAR).' added to '.ucwords(EVENTS_NAME_SINGULAR).' ID:'.$_GET['event_id'].' ('.$events->title.')');
							if (ENABLE_ERROR_LOG) log_error('FAILED: '.ucwords(COMMENT_NAME_SINGULAR).' added to '.ucwords(EVENTS_NAME_SINGULAR).' ID:'.$_GET['event_id'].' ('.$events->title.')<br />Error: '.$comments->error);
						}
					} else {
						$messages .= $messages_indent.'<span class="message">'.$comments->error.'</span><br /><br />'."\n";
					}
				} elseif ($_POST['submit'] == 'Edit '.ucwords(COMMENT_NAME_SINGULAR)) {
					if ($comments->validate_form('Re: '.$events->title)) {
						if ($comments->edit_comment()) {
							$messages .= $messages_indent.'<span class="message">'.ucwords(COMMENT_NAME_SINGULAR).' Edited</span><br /><br />'."\n";
							if (ENABLE_LOG and LOG_EDITS and LOG_COMMENTS) log_action(ucwords(COMMENT_NAME_SINGULAR).' edited in '.ucwords(EVENTS_NAME_SINGULAR).' ID:'.$_GET['event_id'].' ('.$events->title.')');
							$comment_edited = true;
						} else {
							$messages .= $messages_indent.'<span class="message">Could not Edit '.ucwords(COMMENT_NAME_SINGULAR).'.<br /><strong>GO Back in your browser to make the changes</strong></span><br /><br />'."\n";
							if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: '.ucwords(COMMENT_NAME_SINGULAR).' edited in '.ucwords(EVENTS_NAME_SINGULAR).' ID:'.$_GET['event_id'].' ('.$events->title.')');
							if (ENABLE_ERROR_LOG) log_error('FAILED: '.ucwords(COMMENT_NAME_SINGULAR).' edited in '.ucwords(EVENTS_NAME_SINGULAR).' ID:'.$_GET['event_id'].' ('.$events->title.')<br />Error: '.$comments->error);
						}
					} else {
						$messages .= $messages_indent.'<span class="message">'.$comments->error.'</span><br /><br />'."\n";
					}
				} elseif (ALLOW_COMMENT_DELETION and $_POST['submit'] == 'Delete '.ucwords(COMMENT_NAME_SINGULAR) and $_POST['comment_id']) {
					if ($comments->delete_comment($_POST['comment_id'])) {
						$messages .= $messages_indent.'<span class="message">'.ucwords(COMMENT_NAME_SINGULAR).' Deleted</span><br /><br />'."\n";
						if (ENABLE_LOG and LOG_DELETIONS and LOG_COMMENTS) log_action(ucwords(COMMENT_NAME_SINGULAR).' deleted from '.ucwords(EVENTS_NAME_SINGULAR).' ID:'.$_GET['event_id'].' ('.$events->title.')');
					} else {
						if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: '.ucwords(COMMENT_NAME_SINGULAR).' deleted from '.ucwords(EVENTS_NAME_SINGULAR).' ID:'.$_GET['event_id'].' ('.$events->title.')');
						if (ENABLE_ERROR_LOG) log_error('FAILED: '.ucwords(COMMENT_NAME_SINGULAR).' deleted from '.ucwords(EVENTS_NAME_SINGULAR).' ID:'.$_GET['event_id'].' ('.$events->title.')<br />Error: '.$comments->error);
						$messages .= $messages_indent.'<span class="message">'.ucwords(COMMENT_NAME_SINGULAR).' could not be deleted</span><br /><br />'."\n";
					}					
				}
			}
			// ****************************
			// start of page
			// ****************************	
			if (!$events->validated) {
				$messages .= $messages_indent.'<span class="message">This '.strtolower(EVENTS_NAME_SINGULAR).' has not been validated</span><br /><br />'."\n";
				$messages .= $events->validation_html($messages_indent,$events->id,URL.EVENTS_URL.'/'.$events->id.'/');
			}
			
			$event_page = $events->event_page($i);

			if (ENABLE_COMMENTS) {
				$comment_html = $comments->comments_list($comment_html_indent,0,0,$events->id);
				if ($comment_edited) $comments->clear();
				if (user_type() or ENABLE_GUEST_COMMENTS) {
					if (!$comment_html and !$print) {
						$style->dynamic_elements .= " div#event_page {border-bottom: 1px solid ".TAB_BORDER_COLOUR."; margin-bottom:10px; padding-bottom:5px; }\n";
					} else {
						$style->dynamic_elements .= " div#comment_list {border-bottom: 1px solid ".TAB_BORDER_COLOUR."; margin-bottom:10px; padding-bottom:5px; }\n";
					}
					$comment_html .= $comments->form_html($comment_html_indent,'add',EVENTS_URL.'/'.$events->id.'/','Re: '.$events->title,0,0,$events->id);
				}
			}			
		} else {
			$messages .= $messages_indent.'<span class="message">This '.strtolower(EVENTS_NAME_SINGULAR).' needs validation. Please be patient.</span><br /><br />'."\n";
		}
	} else {
		$messages .= $messages_indent.'<span class="message">Could not find '.a(EVENTS_NAME_SINGULAR).' '.strtolower(EVENTS_NAME_SINGULAR).'.</span><br /><br />'."\n";
	}
	
	
}
?>