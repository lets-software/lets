<?php
/***********************************************************************************************************************************
*		Page:			site_structure.php
*		Access:			Admin
*		Purpose:		Edits the positioning and naming of site sections. Manages additional pages
*		HTML Holders:	$main_html		:	Entire Contents
*		Template File:																											*/
			$template_filename 			= 		'default';
/*		Classes:		only integral objects
/*		Indentation:																											
			Set $main_indent on index.php
/*		Disable "Print Page" Link on this page:																					*/
			$disable_print_page			=		true;
/*		CSS Files Called by script:																								*/
		if (!$print) {
			$styles 					.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/results_table.css);\n";
			$styles 					.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/site_structure.css);\n";
/*		Dynamic Styling:																										*/
			/* if (FONT_SIZE > 14) {
				$local_font_size 		= 		14;
			} else {
				$local_font_size 		= 		FONT_SIZE;
			} 
			$style->dynamic_elements 	.= 		" table {font-size: ".$local_font_size."px;}\n"; */
			$style->dynamic_elements 	.= 		" th.h {background-color:".TAB_COLOUR."; color:".LINK_COLOUR.";}\n";
			// $style->dynamic_elements 	.= 		" td {border-bottom:1px solid ".TAB_BORDER_COLOUR."; border-right: 1px solid ".TAB_COLOUR."; }\n";
		}
/*		Javascript:																												*/
/*		Set Universal Page elements:																							*/
			$links->page_info(1,104);
			$page_name 					= 		$links->name;
			$url 						= 		$links->url;
			$blurb 						= 		$links->body;
/*		Page Title:																												*/
			$title 						= 		SITE_NAME.' '.$page_name;
/*		Page Heading																											*/
			$main_html 					= 		$i."<h1>".SITE_NAME.' '.$page_name."</h1>\n";
/*
************************************************************************************************************************************/

//  ACTIONS

// ****************************************************************
//              set post/get variables
$id_to_move = 0;
$id_to_replace = 0;
$page = 0;
$change_made = 0;
if (strpos(' '.$_SERVER['REQUEST_URI'],'?')) {
	$y = explode('?',$_SERVER['REQUEST_URI']);
	if (strpos(' '.$y[1],'&')) {
		$pairs = explode('&',$y[1]);
		foreach($pairs as $pair) {
			if (strpos(' '.$pair,'=')) {
				$x = explode('=',$pair);
				$variable = $x[0];
				$value = $x[1];
				if ($variable == 'link_id') $id_to_move = $value;
				if ($variable == 'replace_id') $id_to_replace = $value;
				if ($variable == 'page') $page = $value;
			}		
		}
	}
}
$page = return_link_variable("page",0);
if (isset($_POST['page'])) {
	if ($_POST['page']) {
		$page = $_POST['page'];
	}
}
// ****************************************************************
//              change positions
if ($id_to_move and $id_to_replace) {
	if ($links->move_link($id_to_move,$id_to_replace)) {
		$change_made = 1;
		if (ENABLE_LOG) log_action($_SESSION['member_full_name'].' adjusted the positioning of links.');
	}
}
// ****************************************************************
//              update structure
if (!isset($_POST['submit'])) $_POST['submit'] = '';
if ($_POST['submit'] == 'Submit' and $links->htaccess_writable()) {
	if (!$links->update_structure()) {
		$main_html .= $i.'<span class="message">'.$links->error.'</span><br /><br />Please Go <strong>Back</strong> to correct the errors or start over here.<br /><br />'."\n";
	} else {
		if (!$links->rebuild_htaccess()) {
			$main_html .= $i.'<span class="message">'.$links->error.'</span><br /><br />'."\n";
		}
		$change_made = 1;
		if (ENABLE_LOG) log_action($_SESSION['member_full_name'].' updated the structure of '.SITE_NAME.'.');
	}	
}
// ****************************************************************
//              add/edit pages
if ($_POST['submit'] == 'Edit Page' or $_POST['submit'] == 'Add Page' or $_POST['submit'] == 'Delete Page' ) {
	if (!$links->validate_extra_page()) {
		$main_html .= $i.'<span class="message">'.$links->error.'</span><br /><br />Please Go <strong>Back</strong> to correct the errors or start over here.<br /><br />'."\n";
	} else {
		if ($_POST['submit'] == 'Edit Page') {
			if (!$links->edit_extra_page()) {
				$main_html .= $i.'<span class="message">'.$links->error.'</span><br /><br />'."\n";
			} else {
				$main_html .= $i.'<span class="message">Page Edited</span><br /><br />'."\n";
				$change_made = 1;
				if (ENABLE_LOG) log_action($_SESSION['member_full_name'].' updated the page: '.$_POST['page_name'].'.');
			}
		}
		if ($_POST['submit'] == 'Add Page') {
			if (!$links->add_extra_page()) {
				$main_html .= $i.'<span class="message">'.$links->error.'</span><br /><br />'."\n";
			} else {
				$main_html .= $i.'<span class="message">Page Added</span><br /><br />'."\n";
				$change_made = 1;
				if (ENABLE_LOG) log_action($_SESSION['member_full_name'].' added the page: '.$_POST['page_name'].'.');
			}
		}
	}
	if ($_POST['submit'] == 'Delete Page') {
		if (!$links->delete_extra_page()) {
			$main_html .= $i.'<span class="message">Page Not Deleted</span><br /><br />'."\n";
		} else {
			$main_html .= $i.'<span class="message">Page Deleted</span><br /><br />'."\n";
			$change_made = 1;
			if (ENABLE_LOG) log_action($_SESSION['member_full_name'].' deleted the page: '.$_POST['page_name'].'.');
		}
	}
	if ($change_made) {
		if (!$links->rebuild_htaccess()) {
			$main_html .= $i.'<span class="message">'.$links->error.'</span><br /><br />'."\n";
		} else {
			header ("Location: ".$_SERVER["REQUEST_URI"].append_url());
		}
	}
}

// ****************************************************************


// PAGE

if ($change_made) {
	if (strpos(' '.$_SERVER["REQUEST_URI"],'?')) {
		$tmp_arr = explode('?',$_SERVER["REQUEST_URI"]);
		header ("Location: ".$tmp_arr[0].append_url());
	} else {
		header ("Location: ".$_SERVER["REQUEST_URI"].append_url());
	}
}
$main_html .= $links->positioning_html($i,$url);
if ($page) {
	$main_html .= $links->extra_pages_html($i,$url,'edit',$page);
} else {
	$main_html .= $links->extra_pages_html($i,$url,'add',0);
}
$main_html .= $links->admin_html($i,$url);




?>