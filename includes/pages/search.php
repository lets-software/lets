<?php
/***********************************************************************************************************************************
*		Page:			search.php
*		Access:			Public
*		Purpose:		Searches the entire site. 
						Requires SHOW_SEARCH_LINK to display link
*		HTML Holders:	$main_html			:		Entire Contents
*		Template File:																											*/
			$template_filename 				= 		'default';
/*		Classes:																												*/
			require_once('includes/classes/articles.class.php');
			$articles 						= 		new articles;
			require_once('includes/classes/events.class.php');
			$events 						= 		new events;
			require_once('includes/classes/noticeboard.class.php');
			$noticeboard 					= 		new noticeboard;
/*		Indentation:																											
			Set $main_indent on index.php
/*		Disable "Print Page" Link on this page:																					*/
			$disable_print_page				=		false;
/*		CSS Files Called by script:																								*/
		if (!$print) {
			$styles 						.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/article_search_form.css);\n";
			$styles 						.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/events_list.css);\n";
			$styles 						.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/noticeboard_search_form.css);\n";
			$styles 						.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/results_table.css);\n";
			$styles 						.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/search.css);\n";
/*		Dynamic Styling:																										*/
			/* if (FONT_SIZE > 14) {
				$local_font_size 		= 		14;
			} else {
				$local_font_size 		= 		FONT_SIZE;
			} 
			$style->dynamic_elements 	.= 		" table {font-size: ".$local_font_size."px;}\n"; */
			$style->dynamic_elements 				.= 		" th.h {background-color:".TAB_COLOUR."; color:".LINK_COLOUR.";}\n";
			// $style->dynamic_elements 				.= 		" td {border-bottom:1px solid ".TAB_BORDER_COLOUR."; border-right: 1px solid ".TAB_COLOUR."; }\n";
		}
/*		Javascript:																												*/
/*		Set Universal Page elements:																							*/
			$links->page_info(12,0);
			$page_name 						= 		$links->name;
			$url 							= 		$links->url;
			$blurb 							= 		$links->body;
/*		Page Title:																												*/
			$title 							= 		SITE_NAME.' '.$page_name;
/*		Page Heading																											*/
			$main_html 						= 		$i."<h1>".SITE_NAME.' '.$page_name."</h1>\n";
/*
************************************************************************************************************************************/

// check to see if there are any variables in the URL
// ** because of using mod-rewrite we have to do this manually **
$keyword = '';
if (strpos(' '.$_SERVER['REQUEST_URI'],'?')) {
	$y = explode('?',$_SERVER['REQUEST_URI']);
	if (strpos(' '.$y[1],'&')) {
		$pairs = explode('&',$y[1]);
		foreach($pairs as $pair) {
			if (strpos(' '.$pair,'=')) {
				$x = explode('=',$pair);
				$variable = $x[0];
				$value = $x[1];
				if ($variable == 'keyword') {
					$keyword = trim(str_replace('_',' ',$value));
					$keyword = str_replace('%22','"',$keyword);
					$keyword = preg_replace("/[^0-9a-z\" ]/",'',$keyword);
					$keyword = eregi_replace(" +", ' ', $keyword); 
				}
			}		
		}
	} else {
		if (strpos(' '.$y[1],'=')) {
			$x = explode('=',$y[1]);
			$variable = $x[0];
			$value = $x[1];
			if ($variable == 'keyword') {
				$keyword = trim(str_replace('_',' ',$value));
				$keyword = str_replace('%22','"',$keyword);
				$keyword = preg_replace("/[^0-9a-z\" ]/",'',$keyword);
				$keyword = eregi_replace(" +", ' ', $keyword); 
		}
		}
	}		
}
if (isset($_POST['keyword'])) {
	$keyword = trim($_POST['keyword']);
	$keyword = html_entity_decode($keyword);
	$keyword = preg_replace("/[^0-9a-z\" ]/",'',$keyword);
	$keyword = eregi_replace(" +", ' ', $keyword); 
}



$noticeboard_html = '';
$articles_html = '';
$events_html = '';
if ($keyword) {
	$noticeboard_html = $noticeboard->noticeboard_list($i.' ',$keyword,5,0,'','','','','','','','','','posted','DESC');
	$articles_html = $articles->xhtml($i.' ',1,$keyword,5,0,'','','','','','','DESC',$show_results = true);
	$events_html = $events->event_list($i.' ',URL.EVENTS_URL.'/','',false,'',$keyword,false,false,true);
}
//if (!PERSISTENT_HTML_SEARCH) {
	$main_html .= $i."<!-- search_form -->\n";
	$main_html .= $i."<div id=\"search_form\">\n";
	$main_html .= $i." <form id=\"search\" action=\"".URL.$url.append_url(0)."\" method=\"post\">\n";
	$main_html .= $i."  <input type=\"text\" id=\"keyword\" name=\"keyword\" value=\"".htmlspecialchars($keyword)."\" />\n";
	$main_html .= $i."  <input id=\"search_button\" type=\"submit\" name=\"submit\" value=\"Search\" />\n";
	$main_html .= $i." </form>\n";
	$main_html .= $i.'</div>'."\n";
	$main_html .= $i."<!-- /search_form -->\n";
//}
if ($keyword) {
	$main_html .= $i."<!-- search_html -->\n";
	$main_html .= $i."<div id=\"search_html\">\n";
	if (strpos(' '.$noticeboard_html,'No '.strtolower(NOTICEBOARD_NAME_PLURAL)." found")) {
		$main_html .= $i.' <h2><a href="'.URL.NOTICEBOARD_URL.'/'.append_url(0).'">'.NOTICEBOARD_NAME."</a></h2>\n";
	} else {
		$main_html .= $i.' <h2><a href="'.URL.NOTICEBOARD_URL.'/?keyword='.urlencode(str_replace(' ','_',$keyword)).append_url(0).'">'.NOTICEBOARD_NAME."</a></h2>\n";
	}
	$main_html .= $noticeboard_html;
	if (strpos(' '.$articles_html,'No '.strtolower(ARTICLES_NAME_PLURAL)." found")) {
		$main_html .= $i.' <h2><a href="'.URL.ARTICLES_URL.'/'.append_url(0).'">'.ARTICLES_NAME."</a></h2>\n";
	} else {
		$main_html .= $i.' <h2><a href="'.URL.ARTICLES_URL.'/?keyword='.urlencode(str_replace(' ','_',$keyword)).append_url(0).'">'.ARTICLES_NAME."</a></h2>\n";
	}
	$main_html .= $articles_html;
	if (strpos(' '.$events_html,'No '.strtolower(EVENTS_NAME_PLURAL)." found")) {
		$main_html .= $i.' <h2><a href="'.URL.EVENTS_URL.'/'.append_url(0).'">'.EVENTS_NAME."</a></h2>\n";
	} else {
		$main_html .= $i.' <h2><a href="'.URL.EVENTS_URL.'/?keyword='.urlencode(str_replace(' ','_',$keyword)).append_url(0).'">'.EVENTS_NAME."</a></h2>\n";
	}
	$main_html .= $events_html;
	$main_html .= $i.'</div>'."\n";
	$main_html .= $i."<!-- /search_html -->\n";
}















?>