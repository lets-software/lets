<?
/***********************************************************************************************************************************
*		Page:			login.php
*		Access:			Public
*		Purpose:		Login through this page. 
						Requires SHOW_LOGIN_LINK to display link (may be appropriate if SHOW_LOGIN_HTML is false)
*		HTML Holders:	$main_html			:		Entire Contents
*		Template File:																											*/
			$template_filename 				= 		'default';
/*		Classes:		all intergral			
/*		Indentation:																											
			Set $main_indent on index.php
/*		Disable "Print Page" Link on this page:																					*/
			$disable_print_page				=		true;
/*		CSS Files Called by script:																								*/
		if (!$print) {
			$styles 						.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/login_form.css);\n";
		}
/*		Dynamic Styling:																										*/
/*		Javascript:																												*/
/*		Set Universal Page elements:																							*/
			$links->page_info(14,0);
			$page_name 						= 		$links->name;
			$url 							= 		$links->url;
			$blurb 							= 		$links->body;
/*		Page Title:																												*/
			$title 							= 		SITE_NAME.' '.$page_name;
/*		Page Heading																											*/
			$main_html 						= 		$i."<h1>".SITE_NAME.' '.$page_name."</h1>\n";
/*
************************************************************************************************************************************/

$main_html .= $i."<!-- login_page_html -->\n";
if (!user_type() and $links->build_url(1,1)) {
	$main_html .= "$i Welcome <strong>Guest</strong>. Please Login or <a href=\"".URL.$links->complete_url."\">Register</a>:<br >\n";
	$main_html .= "$i <form class=\"login_form\"action=\"".URL.MEMBERS_URL."\" method=\"post\">\n";
	$main_html .= "$i  <span>".ucwords(MEMBERS_NAME_SINGULAR)." No:</span>\n";
	$main_html .= "$i  <input type=\"text\" id=\"login_id\" name=\"login_id\" /><br class=\"left\" >\n";
	$main_html .= "$i  <span>Password:</span>\n";
	$main_html .= "$i  <input type=\"password\" id=\"login_password\" name=\"login_password\" /><br class=\"left\" >\n";	
	$main_html .= "$i  <input id=\"login_button\" type=\"submit\" name=\"login\" value=\"Login\" />\n";
	$main_html .= "$i </form>\n";
} else {
	$main_html .= "$i You are already Logged In\n";
}
$main_html .= $i."<!-- /login_page_html -->\n";
?>