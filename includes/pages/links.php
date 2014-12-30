<?php
/***********************************************************************************************************************************
*		Page:			links.php
*		Access:			Public
*		Purpose:		Displays the Links
*		HTML Holders:	$heading			:		Page Heading
*						$links_html			:		The FAQ itself
*						$blurb				:		Additional dynamic text
*		Template File:																											*/
			$template_filename 				= 		'links_page';
/*		Classes:																												*/
			require_once('includes/classes/link.class.php');
			$lets_links 					= 		new lets_links;
/*		Indentation:																											*/
			$heading_indent 				= 		'   ';
			$links_html_indent 				= 		'   ';
			$blurb_indent 					= 		'   ';
/*		Disable "Print Page" Link on this page:																					*/
			$disable_print_page				=		false;
/*		CSS Files Called by script:																								*/
		if (!$print) {
			$styles 						.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/links_list.css);\n";
/*         	Object lets_links calls this internally:																			*/
			$style->dynamic_elements 		.= 		" span.link_category {color:".LINK_COLOUR."; }\n";
			$style->dynamic_elements 		.= 		" div#link_list {border-top: 1px solid ".TAB_BORDER_COLOUR."; }\n";
		}
/*		Dynamic Styling:																										*/
/*		Set Universal Page elements:																							*/
			$links->page_info(6,0);
			$page_name 						= 		$links->name;
			$url 							= 		$links->url;
			$blurb 							= 		$links->body;
/*		Page Title:																												*/
			$title 							= 		SITE_NAME.' '.$page_name;
/*		Page Heading																											*/
			$heading 						= 		$heading_indent."<h1>".SITE_NAME.' '.$page_name."</h1>\n";
/*
************************************************************************************************************************************/


$links_html = $lets_links->link_list($links_html_indent,'',false,0);

?>