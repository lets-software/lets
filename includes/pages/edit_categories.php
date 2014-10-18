<?
/***********************************************************************************************************************************
*		Page:			edit_categories.php
*		Access:			Member
*		Purpose:		Category admin for Events, Noticeboard and Articles
*						*must be enabled with ALLOW_MEMBER_ADMIN_CATEGORIES
*		HTML Holders:	$main_html		:		Entire Contents
*		Template File:																											*/
			$template_filename 			= 		'default';
/*		Classes:																												*/
			require_once('includes/classes/articles.class.php');
			$articles 					= 		new articles;
			require_once('includes/classes/noticeboard.class.php');
			$noticeboard 				= 		new noticeboard;
			require_once('includes/classes/events.class.php');
			$events 					= 		new events;
/*		Indentation:																											
			Set $main_indent on index.php
/*		Disable "Print Page" Link on this page:																					*/
			$disable_print_page			=		true;
/*		CSS Files Called by script:
			we are stealing this from admin_lets_tools.php																		*/
		if (!$print) {
			$styles 					.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/lets_tools.css);\n";
		}
/*		Dynamic Styling:																										*/
/*		Javascript:																										*/
/*		Set Universal Page elements:																							*/
			$links->page_info(1,12);
			$page_name 					= 		$links->name;
			$url 						= 		$links->url;
			$blurb 						= 		$links->body;
/*		Page Title:																												*/
			$title 						= 		SITE_NAME.' '.$page_name;
/*		Page Heading																											*/
			$main_html 					= 		$i."<h1>".SITE_NAME.' '.$page_name."</h1>\n";
/*
************************************************************************************************************************************/

// ******************************************************
//            start of categories routine
$table = '';
$field = '';
$name = '';

if (!isset($_POST['event'])) $_POST['event'] = 0;
if (!isset($_POST['noticeboard'])) $_POST['noticeboard'] = 0;
if (!isset($_POST['article'])) $_POST['article'] = 0;

if ($_POST['event']) {
	$table = 'event_categories';
	$field = 'event_categoryID';
	$name = 'name';
	$child_table = 'events';
	$child_field = 'event_categoryID';
	$txt = ucwords(EVENTS_NAME_SINGULAR).' Categories';
}
if ($_POST['noticeboard']) {
	$table = 'categories';
	$field = 'categoryID';
	$name = 'name';
	$child_table = 'noticeboard';
	$child_field = 'categoryID';
	$txt = ucwords(NOTICEBOARD_NAME_SINGULAR).' Categories';
}
if ($_POST['article']) {
	$table = ' article_categories';
	$field = 'art_catID';
	$name = 'art_cat';
	$child_table = 'articles';
	$child_field = 'articleID';
	$txt = ucwords(ARTICLES_NAME_SINGULAR).' Categories';
}

if (!isset($_POST['submit'])) $_POST['submit'] = '';
if ($_POST['submit'] == 'Add Category') {
	if ($table and $name and $_POST['name']) {
		$post_name = remove_slashes($_POST['name']);
		$mysql->num_rows = 0;
		$mysql->num_rows('SELECT * FROM '.$table.' WHERE '.$name." = '".addslashes($post_name)."' LIMIT 1");
		if (!$mysql->num_rows) {
			if (!$mysql->query('INSERT INTO '.$table." VALUES ('','".ucwords(addslashes($post_name))."')")) {
				log_error('Failed to add a category on edit_categories.php.<br />'.$mysql->error);
			} else {
				if (ENABLE_LOG) log_action($_SESSION['member_full_name'].' added "'.ucwords($post_name).'" to '.$txt.'.');
			}
		}
	}
}
if ($_POST['submit'] == 'Edit Category') {
	if ($table and $field and $name and $_POST['name'] and $_POST['category']) {
		$post_name = remove_slashes($_POST['name']);
		if (!$mysql->query('UPDATE '.$table.' SET '.$name." = '".addslashes($post_name)."' WHERE ".$field.' = '.$_POST['category'].' LIMIT 1')) {
			log_error('Failed to edit a category on edit_categories.php.<br />'.$mysql->error);
		} else {
			if (ENABLE_LOG) log_action($_SESSION['member_full_name'].' edited "'.ucwords($post_name).'" in '.$txt.'.');
		}
	}
}
if ($_POST['submit'] == 'Delete Category') {
	if ($table and $field and $child_table and $child_field and $_POST['category']) {
		$mysql->num_rows = 0;
		$mysql->num_rows('SELECT * FROM '.$child_table.' WHERE '.$child_field." = '".$_POST['category']."' LIMIT 1");
		if (!$mysql->num_rows) {
			if (!$mysql->query('DELETE FROM '.$table.' WHERE '.$field.' = '.$_POST['category'].' LIMIT 1')) {
				log_error('Failed to delete a category on edit_categories.php.<br />'.$mysql->error);
			} else {
				if (ENABLE_LOG) log_action($_SESSION['member_full_name'].' deleted "'.remove_slashes($_POST['name']).'" from '.$txt.'.');
			}
		}
	}
}

//            end of categories routine
// ******************************************************
//				Main Page
	// categories
	$events->get_event_categories();
	$noticeboard->get_categories();
	$articles->get_art_cats();
	$main_html .= $i.'<h2>Category Admin</h2>'."\n";
	$main_html .= $i.'<strong>Note:</strong> It is not possible to delete a category once '.a(EVENTS_NAME_SINGULAR).' '.strtolower(EVENTS_NAME_SINGULAR).', '.strtolower(NOTICEBOARD_NAME_SINGULAR).' or '.strtolower(ARTICLES_NAME_SINGULAR).' has been assigned to it.'."\n";
	$main_html .= $noticeboard->categories_html($i,$url);
	if (ENABLE_EVENTS) {
		$main_html .= $events->categories_html($i,$url);
	}
	if (ENABLE_ARTICLES) {
		$main_html .= $articles->categories_html($i,$url);
	}
	$javascript .= $events->category_javascript;
	$javascript .= $noticeboard->category_javascript;
	$javascript .= $articles->category_javascript;
	
?>