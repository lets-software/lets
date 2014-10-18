<?
/***********************************************************************************************************************************
*		Page:			extra_pages.php
*		Access:			Public
*		Purpose:		Very simple page just displays the page name as title and heading and dynamically entered body
*		HTML Holders:	$main_html			:	All Contents
*		Template File:																											*/
			$template_filename 				= 'default';
/*		Classes:		all integral
	
/*		Indentation:																											
			Set $main_indent and $blurb_indent on index.php
/*		Disable "Print Page" Link on this page:																					*/
			$disable_print_page				=		false;
/*		CSS Files Called by script:																								*/
/*		Dynamic Styling:																										*/
/*		Set Universal Page elements:																							
			This will be done below as it is irregular
/*		Page Title:																												
			This will be done below as it is irregular
/*		Page Heading																											
			This will be done below as it is irregular
/*
************************************************************************************************************************************/
$page_name = '';
$page_body = '';

if ($_GET['id']) {
	if ($mysql->result('SELECT name,body FROM sections WHERE sectionID = '.$_GET['id'].' LIMIT 1')) {
		$page_name = $mysql->result['name'];
		$page_body = $mysql->result['body'];
	}
}

$title = SITE_NAME.' '.$page_name;
$main_html = $i."<h1>".SITE_NAME.' '.$page_name."</h1><br />\n";
$main_html .= indent_variable($i,$page_body);

?>