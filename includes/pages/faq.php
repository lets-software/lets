<?
/***********************************************************************************************************************************
*		Page:			faq.php
*		Access:			Public
*		Purpose:		Displays the FAQs
*		HTML Holders:	$heading			:		Page Heading
*						$faq_html			:		The FAQ itself
*						$blurb				:		Additional dynamic text
*		Template File:																											*/
			$template_filename 				= 		'faq_page';
/*		Classes:																												*/
			require_once('includes/classes/faq.class.php');
			$faq 							= 		new faq;
			
/*		Indentation:																											*/
			$heading_indent 				= 		'   ';
			$faq_html_indent 				= 		'   ';
			$blurb_indent 					= 		'   ';
/*		Disable "Print Page" Link on this page:																					*/
			$disable_print_page				=		false;
/*		CSS Files Called by script:																								*/
		if (!$print) {
			$styles 						.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/faq_list.css);\n";
/*		Dynamic Styling:																										*/
			$style->dynamic_elements 		.= 		" span.faq_category {color:".LINK_COLOUR."; }\n";
			$style->dynamic_elements 		.= 		" div#faq_list {border-top: 1px solid ".TAB_BORDER_COLOUR."; }\n";
		}
/*		Set Universal Page elements:																							*/
			$links->page_info(5,0);
			$page_name 						= 		$links->name;
			$url 							= 		$links->url;
			$blurb 							= 		$links->body;
/*		Page Title:																												*/
			$title 							= 		SITE_NAME.' '.$page_name;
/*		Page Heading																											*/
			$heading 						= 		$heading_indent."<h1>".SITE_NAME.' '.$page_name."</h1>\n";
/*
************************************************************************************************************************************/


$faq_html = $faq->faq_list($faq_html_indent,'',false,0);
?>