<?
/***********************************************************************************************************************************
*		Page:			edit_comment.php
*		Access:			Member
*		Purpose:		A page to edit comments
*						*must be enabled with ENABLE_COMMENTS
*		HTML Holders:	$main_html		:		Entire Contents
*		Template File:																											*/
			$template_filename 			= 		'default';
/*		Classes:																												*/
			require_once('includes/classes/comments.class.php');
			$comments 					= 		new comments;
/*		Indentation:																											
			Set $main_indent on index.php
/*		Disable "Print Page" Link on this page:																					*/
			$disable_print_page			=		true;
/*		CSS Files Called by script:																								*/
		if (!$print) {
			$styles 					.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/transaction_form.css);\n";
			$styles 					.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/comment_form.css);\n";
		}
/*		Dynamic Styling:																										*/
/*		Javascript:																												*/
/*		Set Universal Page elements:																							*/
			$links->page_info(1,11);
			$page_name 					= 		$links->name;
			$url 						= 		$links->url;
			$blurb 						= 		$links->body;
/*		Page Title:																												*/
			$title 						= 		SITE_NAME.' '.$page_name;
/*		Page Heading																											*/
			$main_html 					= 		$i."<h1>".SITE_NAME.' '.$page_name."</h1>\n";
/*
************************************************************************************************************************************/
//			Main Page


if ($_GET['comment_id']) {
	if ($comments->build_comment($_GET['comment_id'])) {
		if (($_SESSION['member_id'] == $comments->member_id) or user_type() == 2) {
			$main_html .= $comments->form_html($i,'edit',$_SERVER['HTTP_REFERER'],$comments->title,$comments->noticeboard_id,$comments->article_id,$comments->event_id);
		}		
	}
	
}


?>