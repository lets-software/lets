<?php
/***********************************************************************************************************************************
*		Page:			lets_settings.php
*		Access:			Admin
*		Purpose:		Sets LETS settings to the config table
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
			$links->page_info(1,103);
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
		if (!$site->update_lets_settings()) {
			$main_html .= $i.'<span class="message">'.$site->error.'</span><br /><br />'."\n";
		} else {
			if (ENABLE_LOG) log_action($_SESSION['member_full_name'].' updated the LETS settings "');
		}
	}
}

$main_html .= $site->lets_settings_html($i,$url);

?>