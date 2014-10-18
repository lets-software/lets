<?
/***********************************************************************************************************************************
*		Page:			form_settings.php
*		Access:			Admin
*		Purpose:		Set required fields
*		HTML Holders:	$main_html		:	Entire Contents
*		Template File:																											*/
			$template_filename 			= 		'default';
/*		Classes:		all intergral			
/*		Indentation:																											
			Set $main_indent on index.php
/*		Disable "Print Page" Link on this page:																					*/
			$disable_print_page			=		true;
/*		CSS Files Called by script:																								*/
		if (!$print) {
			$styles 					.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/form_settings.css);\n";
		}
/*		Dynamic Styling:																										*/
/*		Set Universal Page elements:																							*/
			$links->page_info(1,102);
			$page_name 					= 		$links->name;
			$url 						= 		$links->url;
			$blurb 						= 		$links->body;
/*		Page Title:																												*/
			$title 						= 		SITE_NAME.' '.$page_name;
/*		Page Heading																											*/
			$main_html 					= 		$i."<h1>".SITE_NAME.' '.$page_name."</h1>\n";
/*
************************************************************************************************************************************/
if (isset($_POST['submit'])) {
	if ($_POST['submit'] == 'Submit') {
		$site->update_form_setting();
		if (ENABLE_LOG) log_action($_SESSION['member_full_name'].' updated Form Settings "');
	}
}


$main_html .= $site->form_settings_html($i,$url);




?>