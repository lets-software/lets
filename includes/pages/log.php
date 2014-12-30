<?php
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
			$styles 					.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/log.css);\n";
		}
/*		Dynamic Styling:																										*/
/*		Set Universal Page elements:																							*/
			$links->page_info(1,108);
			$page_name 					= 		$links->name;
			$url 						= 		$links->url;
			$blurb 						= 		$links->body;
/*		Page Title:																												*/
			$title 						= 		SITE_NAME.' '.$page_name;
/*		Page Heading																											*/
			$main_html 					= 		$i."<h1>".SITE_NAME.' '.$page_name."</h1>\n";
/*
************************************************************************************************************************************/

$criteria = urldecode(return_link_variable("criteria",''));
$delete = return_link_variable("delete",'');
if (($criteria == SITE_NAME or $criteria == SITE_NAME.'_Errors') and $print) {
	if ($delete) {
		$mtime = array_sum(explode(' ',microtime()));
		$mtime = ceil($mtime);
		copy(PATH.'logs/'.$criteria.'.log',PATH.'logs/'.$criteria.'.'.$mtime.'.log.bak');
//		$err = shell_exec('cp -r '.PATH.'logs/'.$criteria.'.log'.' '.PATH.'logs/'.$criteria.'.log.'.$mtime.'.bak'.''); 
//		echo $err;
		$log_file_handle = fopen(PATH.'logs/'.$criteria.'.log',"w");
		if ($log_file_handle) { 
			fwrite($log_file_handle,time_stamp()." Log File deleted by ".$_SESSION['member_name']." (".ucwords(MEMBERS_NAME_SINGULAR)." ".$_SESSION['member_id'].")\n");
			fclose($log_file_handle);
		}
	}
	
	if ($log = file_get_contents(PATH.'logs/'.$criteria.'.log')) {
		$main_html .= $i.'<textarea id="log">'.$log.'</textarea><br /><br />';
		$main_html .= $i.'<a class="log_link" href="'.URL.$url.'?print=1&criteria='.$criteria.'&delete=1'.append_url(' ?').'">Delete this log</a>'."\n";
	}
} else {
	$main_html .= $i.'<a class="log_link" href="'.URL.$url.'?print=1&criteria='.urlencode(SITE_NAME).append_url(' ?').'">Log</a> / <a class="log_link" href="'.URL.$url.'?print=1&criteria='.urlencode(SITE_NAME).'_Errors'.append_url(' ?').'">Error Log</a>'."\n";
}
















?>