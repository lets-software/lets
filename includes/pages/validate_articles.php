<?
/***********************************************************************************************************************************
*		Page:			validate_articles.php
*		Access:			Admin
*		Purpose:		If required an admin can review and validate un-validated articles
*		HTML Holders:	$main_html		:		Entire Contents
*		Template File:																											*/
			$template_filename 			= 		'default';
/*		Classes:																												*/
			require_once('includes/classes/articles.class.php');
			$articles = new articles;
/*		Indentation:																											
			Set $main_indent on index.php
/*		Disable "Print Page" Link on this page:																					*/
			$disable_print_page			=		true;
/*		CSS Files Called by script:																								*/
		if (!$print) {
			$styles 					.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/articles_display_1.css);\n";
			$styles 					.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/thumbs_list.css);\n";
/*		Dynamic Styling:																										*/
			$style->dynamic_elements 		.= 		" div.thumbs {float:left; width:".IMAGE_WIDTH_THUMB_ARTICLE."px; margin-right:5px; }\n";
		}
/*		Javascript:																												*/
/*		Set Universal Page elements:																							*/
			$links->page_info(1,105);
			$page_name 					= 		$links->name;
			$url 						= 		$links->url;
			$blurb 						= 		$links->body;
/*		Page Title:																												*/
			$title 						= 		SITE_NAME.' '.$page_name;
/*		Page Heading																											*/
			$main_html 					= 		$i."<h1>".SITE_NAME.' '.$page_name."</h1>\n";
/*
************************************************************************************************************************************/

// ******************
// actions
// ******************
if (!isset($_POST['submit'])) $_POST['submit'] = '';
if ($_POST['submit'] == 'Validate' and $_POST['article_id']) {
	if ($articles->validate($_POST['article_id'])) {
		$main_html .= $i.'<span class="message">'.ucwords(ARTICLES_NAME_SINGULAR).' Validated</span><br /><br />'."\n";
	}
}
if ($_POST['submit'] == 'Delete' and $_POST['article_id']) {
	if ($articles->delete($_POST['article_id'])) {
		$main_html .= $i.'<span class="message">'.ucwords(ARTICLES_NAME_SINGULAR).' Deleted</span><br /><br />'."\n";
	}
}
// ******************
// main page
// ******************

$main_html .= $articles->xhtml($main_indent,3,'',10,0,0,0,0,0,0,'posted','ASC',true,true);


?>