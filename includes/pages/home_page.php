<?
/***********************************************************************************************************************************
*		Page:			home_page.php
*		Access:			Public
*		Purpose:		Home Page. 
*		Template File:																											*/
			$template_filename 				= 		'home_page';
/*		Classes:																												*/
		if (HOMEPAGE_HTML_ARTICLES) {
			require_once('includes/classes/articles.class.php');
			$articles 						= 		new articles;
		}
		if (HOMEPAGE_HTML_EVENTS) {
			require_once('includes/classes/events.class.php');
			$events 						= 		new events;
		}
		if (HOMEPAGE_HTML_NOTICEBOARD) {
			require_once('includes/classes/noticeboard.class.php');
			$noticeboard 					= 		new noticeboard;
		}
		if (HOMEPAGE_HTML_FAQ) {
			require_once('includes/classes/faq.class.php');
			$faq		 					= 		new faq;
		}
		if (HOMEPAGE_HTML_LINKS) {
			require_once('includes/classes/link.class.php');
			$lets_links 					= 		new lets_links;
		}
/*		Indentation:																											*/
			$message_indent					=		'   ';
			$articles_html_indent			=		'   ';
			$noticeboard_html_indent		=		'   ';
			$events_html_indent				=		'   ';
			$faq_html_indent				=		'   ';
			$links_html_indent				=		'   ';
/*		Disable "Print Page" Link on this page:																					*/
			$disable_print_page				=		false;
/*		CSS Files Called by script:																								*/
	if (!$print) {
		if (HOMEPAGE_HTML_ARTICLES) {
			$styles 						.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/article_search_form.css);\n";
			$styles 						.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/articles_display_1.css);\n";
			$styles 						.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/articles_display_2.css);\n";
			$styles 						.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/articles_display_3.css);\n";
			$styles 						.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/thumbs_list.css);\n";
		}
		

		if (HOMEPAGE_HTML_EVENTS) {
			$styles 						.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/events_list.css);\n";
		}
		if (HOMEPAGE_HTML_NOTICEBOARD) {
			$styles 						.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/noticeboard_search_form.css);\n";
		}
		if (HOMEPAGE_HTML_FAQ) {
			$styles 						.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/faq_list.css);\n";
		}
		if (HOMEPAGE_HTML_LINKS) {
			$styles 						.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/links_list.css);\n";
		}
			$styles 						.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/results_table.css);\n";
/*		Dynamic Styling:																										*/
			if (FONT_SIZE > 14) {
				$local_font_size = 14;
			} else {
				$local_font_size = FONT_SIZE;
			} 
//			$style->dynamic_elements 				.= 		" table {font-size: ".$local_font_size."px;}\n";
//			$style->dynamic_elements 				.= 		" th.h {background-color:".TAB_COLOUR."; color:".LINK_COLOUR.";}\n";
//			$style->dynamic_elements 				.= 		" td {border-bottom:1px solid ".TAB_BORDER_COLOUR."; border-right: 1px solid ".TAB_COLOUR."; }\n";
			$style->dynamic_elements 		.= 		" div.thumbs {float:left; width:".IMAGE_WIDTH_THUMB_ARTICLE."px; margin-right:5px; }\n";
		if (HOMEPAGE_HTML_FAQ) {
			$style->dynamic_elements 				.= 		" span.faq_category {color:".LINK_COLOUR."; }\n";
			$style->dynamic_elements 				.= 		" div#faq_list {border-top: 1px solid ".TAB_BORDER_COLOUR."; }\n";
		}
		if (HOMEPAGE_HTML_LINKS) {
			$style->dynamic_elements 				.= 		" span.link_category {color:".LINK_COLOUR."; }\n";
			$style->dynamic_elements 				.= 		" div#link_list {border-top: 1px solid ".TAB_BORDER_COLOUR."; }\n";			
		}
	}
/*		Javascript:																												*/
/*		Set Universal Page elements:																							*/
/*		Page Title:																												*/
			$title 							= 		SITE_NAME.' Home';
/*		Page Heading																											*/
			$main_html 						= 		$i."<h1>".SITE_NAME.' Home'."</h1>\n";
//		Clear HTML Holders
			$message						=		'';
			$articles_html					=		'';
			$noticeboard_html				=		'';
			$events_html					=		'';
			$faq_html						=		'';
			$links_html						=		'';
/*
************************************************************************************************************************************/
$message .= $site->return_config_html($i,'visitor_message');


if (HOMEPAGE_HTML_ARTICLES) {
	// 2nd parameter is display type. 1 is short listing, 2 is medium listing (blurb), 3 is full article with all images
	// 4rth parameter is LIMIT (number or articles to display)
	// 11th parameter is ORDER BY: accepts 'posted', 'category', 'member', 'title'
	// for more info see the function in full use in pages/article.php
	$articles_html = $articles->xhtml($articles_html_indent,1,'',10,0,0,0,0,0,0,'posted','DESC',$show_results = false);
	if ($articles_html) {
		$articles_html = $articles_html_indent.'<h2>'.ARTICLES_NAME."</h2>\n".$articles_html;
	}
}
if (HOMEPAGE_HTML_EVENTS) {
	$events_html = $events->event_list($events_html_indent,URL.EVENTS_URL.'/','',false,'','',false,false,false);
	if ($events_html) {
		$events_html = $events_html_indent.'<h2>'.EVENTS_NAME."</h2>\n".$events_html;
	}
}
if (HOMEPAGE_HTML_NOTICEBOARD) {
	$noticeboard_html = $noticeboard->noticeboard_list($noticeboard_html_indent.' ','',10,0,'','','','','','','','','','posted','DESC',false);
	if ($noticeboard_html) {
		$noticeboard_html = $noticeboard_html_indent.'<h2>'.NOTICEBOARD_NAME."</h2>\n".$noticeboard_html;
	}
}
if (HOMEPAGE_HTML_FAQ) {
	$faq_html = $faq->faq_list($faq_html_indent,'',false,0);
	if ($faq_html) {
		$faq_html = $faq_html_indent.'<h2>'.FAQ_NAME."</h2>\n".$faq_html;
	}
}
if (HOMEPAGE_HTML_LINKS) {
	$links_html = $lets_links->link_list($links_html_indent,'',false,0);
	if ($links_html) {
		$links_html =$links_html_indent.'<h2>'.LINKS_NAME."</h2>\n".$links_html;
	}
}


?>