<?
// NOTE: This is 2 pages in one. Either it is an article or a list.

// unset article ID if it doesn't exist
if (!isset($_GET['article_id'])) {
	$_GET['article_id'] = 0;
}
if ($_GET['article_id']) {
	if ($mysql->num_rows('SELECT articleID FROM articles WHERE articleID = '.$_GET['article_id'].' LIMIT 1')) {
		if (!$mysql->num_rows) {
			$_GET['article_id'] = 0;
		}
	}
}


if (!$_GET['article_id']) {
/***********************************************************************************************************************************
*		Page:			articles.php
*		Access:			Public
*		Purpose:		Displays a list of articles if no specific
*						Article is called otherwise it prints a
*						Single Article
*		HTML Holders:	$heading				:		Page Heading
*						$articles_search_form	:		Parameters for searching articles
*						$articles_xhtml			:		The resulting list of articles
*						$blurb					:		Dynamically entered text
*		Template File:																											*/
			$template_filename 					=		'article_list';
/*		Classes:		articles																								*/
			require_once('includes/classes/articles.class.php');
			$articles = new articles;
/*		Indentation:																											*/
			$heading_indent 					= 		'   ';
			$articles_search_form_indent 		=		'   ';
			$articles_xhtml_indent 				= 		'   ';
			$blurb_indent 						= 		'   ';
/*		Disable "Print Page" Link on this page:																					*/
			$disable_print_page					=		false;
/*		CSS Files Called by script:																								*/
			if (!$print) {
				$styles 						.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/article_search_form.css);\n";
				$styles 						.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/thumbs_list.css);\n";
/*		Dynamic Styling:																										*/
				$style->dynamic_elements 		.= 		" div.thumbs {float:left; width:".IMAGE_WIDTH_THUMB_ARTICLE."px; margin-right:5px; }\n";
				$style->dynamic_elements 		.= 		" div.article_image_holder {float:left; width:".IMAGE_WIDTH_THUMB_ARTICLE."px; margin-right:5px; }\n";
				$style->dynamic_elements 		.=		" #article_page_image_holder {float:left; width:".IMAGE_WIDTH_PAGE_ARTICLE."px; }\n";
			}
/*		Set Universal Page elements:																							*/
			$links->page_info(3,0);
			$page_name 							=		$links->name;
			$url 								=		$links->url;
			$blurb 								=		$links->body;
/*		Page Title:																												*/
			$title 								=		SITE_NAME.' '.$page_name;
/*		Page Heading																											*/
			$heading 							=		$heading_indent."<h1>".SITE_NAME.' '.$page_name."</h1>\n";
/*
************************************************************************************************************************************/

	// check to see if there are any variables in the URL
	// ** because of using mod-rewrite we have to do this manually **
	$start_default = 0;
	$limit_default = 10;
	$category = return_link_variable("category",'');
	$start = return_link_variable("start",$start_default);
	$limit = return_link_variable("limit",$limit_default);
	$article_member_id = return_link_variable('member','');
	$format = return_link_variable('format',1);
	$day = return_link_variable('day',0);
	$month = return_link_variable('month',0);
	$year = return_link_variable('year',0);
	$orderby = return_link_variable('orderby','');
	$orderdir = return_link_variable('orderdir','');

	if (isset($_POST['category'])) $category = $_POST['category'];
	if (isset($_POST['start'])) $start = $_POST['start'];
	if (isset($_POST['limit'])) $limit = $_POST['limit'];
	if (isset($_POST['member'])) $article_member_id = $_POST['member'];
	if (isset($_POST['format'])) $format = $_POST['format'];
	if (isset($_POST['day'])) $day = $_POST['day'];
	if (isset($_POST['month'])) $month = $_POST['month'];
	if (isset($_POST['year'])) $year = $_POST['year'];
	if (isset($_POST['orderby'])) $orderby = $_POST['orderby'];
	if (isset($_POST['orderdir'])) $orderdir = $_POST['orderdir'];
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
	
	
	//*************************
	//   main page
	
	// now we have all our variables to pass to the form
	$articles_search_form = $articles->search_form($articles_search_form_indent,$format,$keyword,$limit,$start,$article_member_id,$category,$month,$year,$day,$orderby,$orderdir);
	// and the results-generator
	$articles_xhtml = $articles->xhtml($articles_xhtml_indent,$format,$keyword,$limit,$start,$article_member_id,$category,$month,$year,$day,$orderby,$orderdir,$show_results = true);
	// calling now as a special case because we need var:$format
	if (!$print) $styles .= " @import url(".URL.'templates/'.TEMPLATE."/styles/articles_display_".$format.".css);\n";
} else { 
//************* an item-specific page *****************************
/***********************************************************************************************************************************
*		Page:			articles.php
*		Access:			Public
*		Purpose:		Displays a list of articles if no specific
*						Article is called otherwise it prints a
*						Single Article
*		HTML Holders:	$heading					:		Page Heading
*						$messages					:		Messages Resulting from actions
*						$article_page				:		Parameters for searching articles
*						$comment_html				:		Comments for this article
						
*		Template File:																											*/
			$template_filename 						= 		'article_page';
/*		Classes:		articles (called above)																					*/
			require_once('includes/classes/articles.class.php');
			$articles 								= 		new articles;
			if (ENABLE_COMMENTS) {
				require_once('includes/classes/comments.class.php');
				$comments 							= 		new comments;
			}
/*		Indentation:																											*/
			$heading_indent 						= 		'   ';
			$messages_indent 						= 		'   ';
			$article_page_indent 					= 		'   ';
			$comment_html_indent 					= 		'   ';
/*		Disable "Print Page" Link on this page:																					*/
			$disable_print_page						=		false;
/*		CSS Files Called by script:																								*/
			if (!$print) {
				$styles 							.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/articles_page.css);\n";
				if (ENABLE_IMAGES) {
					$styles 						.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/thumbs_list.css);\n";
				}
				if (ENABLE_COMMENTS) {
					$styles 						.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/comment_form.css);\n";
					$styles 						.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/comment_list.css);\n";
				}
//			comments styling called below as needed
/*		Dynamic Styling:																										*/
				$style->dynamic_elements			.=		" div#nb_page_divider {border-top: 1px solid ".TAB_BORDER_COLOUR."; border-bottom: 1px solid ".TAB_BORDER_COLOUR."; }\n";
				if (ENABLE_IMAGES) {
					$style->dynamic_elements 		.=		" div.thumbs {float:left; width:".IMAGE_WIDTH_PAGE_ARTICLE."px; margin-right:5px; }\n";
					$style->dynamic_elements 		.=		" #article_page_image_holder {float:left; width:".IMAGE_WIDTH_PAGE_ARTICLE."px; }\n";
				}
				if (ENABLE_COMMENTS) {
					$style->dynamic_elements 		.= 		" div#comment_list {border-top: 1px solid ".TAB_BORDER_COLOUR."; margin-top:10px; padding-top:5px; }\n";
				}
			}
/*		Set Universal Page elements:																							*/
			$links->page_info(3,0);
			$page_name 								= 		$links->name;
			$url 									= 		$links->url;
			
/*		Page Title:																												*/
// 			called below to incorporate article title
/*		Page Heading																											*/
			$heading 								= 		$heading_indent."<h1>".SITE_NAME.' '.$page_name."</h1>\n";
/*
************************************************************************************************************************************/
if (!isset($_POST['submit'])) $_POST['submit'] = '';
$comment_edited = false;
	$messages = '';
	// we know it exists from an earlier check. Now we grab the data
	if ($mysql->result('SELECT * FROM articles WHERE articleID = '.$_GET['article_id'].' LIMIT 1')) {
		$article = $mysql->result;
		$title = SITE_NAME.' '.$page_name.' - '.$article['title'];
		// *******************
		//  comment actions
		// *******************
		if ((ENABLE_COMMENTS and user_type() and !$_SESSION['member_suspended'] and $_SESSION['member_validated']) or (ENABLE_COMMENTS and ENABLE_GUEST_COMMENTS)) {
			$comments = new comments;
			if ($_POST['submit'] == 'Make '.ucwords(COMMENT_NAME_SINGULAR)) {
				if ($comments->validate_form('Re: '.$article['title'])) {
					if ($comments->add_comment()) {
						$messages .= $messages_indent.'<span class="message">'.ucwords(COMMENT_NAME_SINGULAR).' Added</span><br /><br />'."\n";
						if (ENABLE_LOG and LOG_ADDITIONS and LOG_COMMENTS) log_action(ucwords(COMMENT_NAME_SINGULAR).' added to '.ucwords(ARTICLES_NAME_SINGULAR).' ID:'.$_GET['article_id'].' ('.$article['title'].')');
					} else {
						$messages .= $messages_indent.'<span class="message">Could not Add '.ucwords(COMMENT_NAME_SINGULAR).'</span><br /><br />'."\n";
						if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: '.ucwords(COMMENT_NAME_SINGULAR).' added to '.ucwords(ARTICLES_NAME_SINGULAR).' ID:'.$_GET['article_id'].' ('.$article['title'].')');
						if (ENABLE_ERROR_LOG) log_error('FAILED: '.ucwords(COMMENT_NAME_SINGULAR).' added to '.ucwords(ARTICLES_NAME_SINGULAR).' ID:'.$_GET['article_id'].' ('.$article['title'].')<br />Error:'.$comments->error);
					}
				} else {
					// this is not a bad error - just a form error
					$messages .= $messages_indent.'<span class="message">'.$comments->error.'</span><br /><br />'."\n";
				}
			} elseif ($_POST['submit'] == 'Edit '.ucwords(COMMENT_NAME_SINGULAR)) {
				if ($comments->validate_form('Re: '.$article['title'])) {
					if ($comments->edit_comment()) {
						$messages .= $messages_indent.'<span class="message">'.ucwords(COMMENT_NAME_SINGULAR).' Edited</span><br /><br />'."\n";
						if (ENABLE_LOG and LOG_EDITS and LOG_COMMENTS) log_action(ucwords(COMMENT_NAME_SINGULAR).' edited in '.ucwords(ARTICLES_NAME_SINGULAR).' ID:'.$_GET['article_id'].' ('.$article['title'].')');
						$comment_edited = true;
					} else {
						$messages .= $messages_indent.'<span class="message">Could not Edit '.ucwords(COMMENT_NAME_SINGULAR).' <br /><strong>GO Back in your browser to make the changes</strong></span><br /><br />'."\n";
						if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: '.ucwords(COMMENT_NAME_SINGULAR).' edited in '.ucwords(ARTICLES_NAME_SINGULAR).' ID:'.$_GET['article_id'].' ('.$article['title'].')');
						if (ENABLE_ERROR_LOG) log_error('FAILED: '.ucwords(COMMENT_NAME_SINGULAR).' edited in '.ucwords(ARTICLES_NAME_SINGULAR).' ID:'.$_GET['article_id'].' ('.$article['title'].')<br />Error:'.$comments->error);
					}
				} else {
					// this is not a bad error - just a form error
					$messages .= $messages_indent.'<span class="message">'.$comments->error.' <br /><strong>GO Back in your browser to make the changes</strong></span><br /><br />'."\n";
				}
			} elseif (ALLOW_COMMENT_DELETION and $_POST['submit'] == 'Delete '.ucwords(COMMENT_NAME_SINGULAR) and $_POST['comment_id']) {
				if ($comments->delete_comment($_POST['comment_id'])) {
					$messages .= $messages_indent.'<span class="message">'.ucwords(COMMENT_NAME_SINGULAR).' Deleted</span><br /><br />'."\n";
					if (ENABLE_LOG and LOG_DELETIONS and LOG_COMMENTS) log_action(ucwords(COMMENT_NAME_SINGULAR).' deleted from '.ucwords(ARTICLES_NAME_SINGULAR).' ID:'.$_GET['article_id'].' ('.$article['title'].')');
				} else {
					if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: '.ucwords(COMMENT_NAME_SINGULAR).' deleted from '.ucwords(ARTICLES_NAME_SINGULAR).' ID:'.$_GET['article_id'].' ('.$article['title'].')');
					if (ENABLE_ERROR_LOG) log_error('FAILED: '.ucwords(COMMENT_NAME_SINGULAR).' deleted from '.ucwords(ARTICLES_NAME_SINGULAR).' ID:'.$_GET['article_id'].' ('.$article['title'].')<br />Error:'.$comments->error);
					$messages .= $messages_indent.'<span class="message">'.ucwords(COMMENT_NAME_SINGULAR).' could not be deleted</span><br /><br />'."\n";
				}					
			}
		}
		
		// ****************************
		// start of page
		// ****************************
		$article_page = $articles->page_html($article_page_indent,$_GET['article_id']);
		
		if (ENABLE_COMMENTS) {
			$comment_html = $comments->comments_list($comment_html_indent,0,$article['articleID'],0);
			if ($comment_edited) $comments->clear();
			if (user_type() or ENABLE_GUEST_COMMENTS) {
				$comment_html .= $comments->form_html($comment_html_indent,'add',ARTICLES_URL.'/'.$article['articleID'].'/','Re: '.$article['title'],0,$article['articleID'],0);
			}
		}
	}
}






?>