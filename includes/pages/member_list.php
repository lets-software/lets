<?
/***********************************************************************************************************************************
*		Page:			member_list.php
*		Access:			Public
*		Purpose:		Shows a list of members
*		HTML Holders:	$heading				:		Page Heading
*						$blurb					:		Dynamically entered text
*						$members_search_form	:		Parameters for searching articles
*						$members_list			:		The resulting list of articles
*		Template File:																											*/
			$template_filename 					= 		'member_list';
/*		Classes:		
			Only Integral
/*		Indentation:																											*/
			$heading_indent 					= 		'   ';
			$members_search_form_indent 		=		'   ';
			$members_list_indent 				= 		'   ';
			$blurb_indent 						= 		'   ';
/*		Disable "Print Page" Link on this page:																					*/
			$disable_print_page					=		false;
/*		CSS Files Called by script:																								*/
		if (!$print) {
			$styles 							.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/member_search_form.css);\n";
			$styles 							.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/results_table.css);\n";
/*		Dynamic Styling:																										*/
			/* if (FONT_SIZE > 14) {
				$local_font_size 		= 		14;
			} else {
				$local_font_size 		= 		FONT_SIZE;
			} 
			$style->dynamic_elements 	.= 		" table {font-size: ".$local_font_size."px;}\n"; */
			$style->dynamic_elements 			.= 		" th.h_o {background-color:".TAB_COLOUR.";}\n";
			$style->dynamic_elements 			.= 		" th.h_d {background-color:".TAB_COLOUR.";}\n";
			$style->dynamic_elements 			.= 		" td.l_o {border-bottom:1px solid ".TAB_BORDER_COLOUR."; border-right: 2px solid ".LINK_COLOUR."; }\n";
			$style->dynamic_elements 			.= 		" td.l_d {border-bottom:1px solid ".TAB_BORDER_COLOUR."; border-right: 1px solid ".TAB_COLOUR.";}\n";
			$style->dynamic_elements 			.= 		" td.l_o_end {border-bottom:1px solid ".TAB_BORDER_COLOUR."; border-left: 2px solid ".LINK_COLOUR.";}\n";
			$style->dynamic_elements 			.= 		" td.l_d_end {border-bottom:1px solid ".TAB_BORDER_COLOUR."; }\n";
		}
/*		Javascript:																												*/

/*		Set Universal Page elements:																							*/
			$links->page_info(7,0);
			$page_name 							= 		$links->name;
			$url 								= 		$links->url;
			$blurb 								= 		$links->body;
/*		Page Title:																												*/
			$title 								= 		SITE_NAME.' '.$page_name;
/*		Page Heading																											*/
			$heading 							= 		$heading_indent."<h1>".SITE_NAME.' '.$page_name."</h1>\n";
/*
************************************************************************************************************************************/

// Develop query from variables, but first figure out what the variables are

$order_by_default = "accountID";
$direction_default = "ASC";
$start_default = 0;
$limit_default = 100;
$order_by = return_link_variable("order_by",$order_by_default);
$direction = return_link_variable("direction",$direction_default);
$start = return_link_variable("start",$start_default);
$limit = return_link_variable("show",$limit_default);
$member_search_term = return_link_variable("member_search_term",'');
$member_search_type = return_link_variable("member_search_type",'');
if ($member_search_term) {
	$member_search_term = trim(str_replace('_',' ',$member_search_term));
	$member_search_term = strtolower(str_replace('%22','"',$member_search_term));
	$member_search_term = preg_replace("/[^0-9a-z\" ]/",'',$member_search_term);
	$member_search_term = eregi_replace(" +", ' ', $member_search_term);
} elseif (isset($_POST['member_search_term'])) {
	$member_search_term = strtolower(trim($_POST['member_search_term']));
	$member_search_term = html_entity_decode($member_search_term);
	$member_search_term = preg_replace("/[^0-9a-z\" ]/",'',$member_search_term);
	$member_search_term = eregi_replace(" +", ' ', $member_search_term); 
}
if (!$member_search_type and isset($_POST['member_search_type'])) {
	$member_search_type = $_POST['member_search_type'];
}
$conditions = '';
if ($member_search_term) {
	if (is_numeric($member_search_term) and $member_search_term > 0) {
		$conditions = ' accountID = '.$member_search_term.' AND ';
	} else {
		$member_search_terms = search_terms($member_search_term);
		$num_searches = count($member_search_terms);
		if ($num_searches > 1) {
			$ii = 1;
			$conditions .= ' (';
			foreach($member_search_terms as $search_term) {
				$conditions .= "(first_name LIKE '%".$search_term."%' OR last_name LIKE '%".$search_term."%')";
				if ($ii <  $num_searches) {
					$conditions .= ' OR ';
				}
				$ii++;
			}
			$conditions .= ') AND ';
		} else {
			$conditions .= " (first_name LIKE '%".$member_search_terms[0]."%' OR last_name LIKE '%".$member_search_terms[0]."%') AND ";
		}
	}
}

$mysql = new mysql;
$image_field = '';
if (ENABLE_IMAGES) {
	if ($mysql->num_rows('SELECT accountID FROM accounts WHERE validated = 1 and suspended = 0 and imageID != 0')) {
		if ($mysql->num_rows) {
			$image_field = ', imageID';
		}
	}
}
$balance_field = '';
if (ENABLE_NOTICEBOARD) {
	$balance_field = ', balance';
}
//*****************************************************
//			Main Page

	
if (!user_type() or !$_SESSION["member_validated"] or $_SESSION["member_suspended"]) {
	$query1 = "SELECT accountID, neighborhood, city FROM accounts WHERE public_profile_enabled = 1 and validated = 1 and suspended = 0 ORDER BY ".$order_by." ".$direction." LIMIT ".$start.",".$limit;
	$query2 = "SELECT accountID, neighborhood, city FROM accounts WHERE public_profile_enabled = 1 and validated = 1 and suspended = 0 ORDER BY ".$order_by." ".$direction;
	$members_list = $members_list_indent."Only active ".strtolower(MEMBERS_NAME_PLURAL)." are able to view the entire ".strtolower(MEMBER_LIST_NAME)." though some ".strtolower(MEMBERS_NAME_PLURAL)." have created a public profile to share. <br /><br />Click on ".a(MEMBERS_NAME_SINGULAR)." ".strtolower(MEMBERS_NAME_SINGULAR)."'s number to learn more about them.<br /><br />\n";
	$members_list .= query_output($members_list_indent,$order_by,$order_by_default,$direction,$direction_default,$start,$start_default,$limit,$limit_default,$query1,$query2,URL.MEMBER_LIST_URL.'/'.append_url());
} else {
	if (user_type() == 2) {
		if ($member_search_type > 1) {
			if ($member_search_type == 2) {
				$query1 = "SELECT accountID".$image_field.", first_name, last_name, address, city, home_phone_number, email_address, url FROM accounts WHERE validated = 0 AND deleted = 0 AND ".$conditions."suspended = 0 ORDER BY ".$order_by." ".$direction." LIMIT ".$start.",".$limit;
				$query2 = "SELECT accountID".$image_field.", first_name, last_name, address, city, home_phone_number, email_address, url FROM accounts WHERE validated = 0 AND deleted = 0 AND ".$conditions."suspended = 0";
			}
			if ($member_search_type == 3) {
				$query1 = "SELECT accountID".$image_field.", first_name, last_name".$balance_field.", address, city, home_phone_number, email_address, url FROM accounts WHERE validated = 1 AND ".$conditions."suspended = 1 AND deleted = 0 ORDER BY ".$order_by." ".$direction." LIMIT ".$start.",".$limit;
				$query2 = "SELECT accountID".$image_field.", first_name, last_name".$balance_field.", address, city, home_phone_number, email_address, url FROM accounts WHERE validated = 1 AND ".$conditions."suspended = 1 AND deleted = 0";
			}
			if ($member_search_type == 4) {
				$query1 = "SELECT accountID".$image_field.", first_name, last_name".$balance_field.", home_phone_number, email_address, expiry_day, expiry_month, expiry_year FROM accounts WHERE validated = 1 AND ".$conditions.'suspended = 0 AND ((expiry_year = '.$date['year'].' AND expiry_month = '.$date['month'].') OR (expiry_year = '.$date['year'].' AND expiry_month = '.($date['month'] + 1).' AND expiry_day < '.($date['day']).')) ORDER BY '.$order_by." ".$direction." LIMIT ".$start.",".$limit;
				$query2 = "SELECT accountID".$image_field.", first_name, last_name".$balance_field.", home_phone_number, email_address, expiry_day, expiry_month, expiry_year FROM accounts WHERE validated = 1 AND ".$conditions.'suspended = 0 AND ((expiry_year = '.$date['year'].' AND expiry_month = '.$date['month'].') OR (expiry_year = '.$date['year'].' AND expiry_month = '.($date['month'] + 1).' AND expiry_day < '.($date['day']).'))';
			}
			if ($member_search_type == 5) {
				$query1 = "SELECT accountID".$image_field.", first_name, last_name".$balance_field.", home_phone_number, email_address, expiry_day, expiry_month, expiry_year FROM accounts WHERE validated = 1 AND ".$conditions.'suspended = 0 AND ((expiry_year < '.$date['year'].') OR (expiry_year = '.$date['year'].' AND expiry_month < '.$date['month'].') OR (expiry_year = '.$date['year'].' AND expiry_month = '.$date['month'].' AND expiry_day < '.($date['day']).')) ORDER BY '.$order_by." ".$direction." LIMIT ".$start.",".$limit;
				$query2 = "SELECT accountID".$image_field.", first_name, last_name".$balance_field.", home_phone_number, email_address, expiry_day, expiry_month, expiry_year FROM accounts WHERE validated = 1 AND ".$conditions.'suspended = 0 AND ((expiry_year < '.$date['year'].') OR (expiry_year = '.$date['year'].' AND expiry_month < '.$date['month'].') OR (expiry_year = '.$date['year'].' AND expiry_month = '.$date['month'].' AND expiry_day < '.($date['day']).'))';
			}
			if ($member_search_type == 6) {
				$query1 = "SELECT accountID".$image_field.", first_name, last_name".$balance_field.", address, city, home_phone_number, email_address, url FROM accounts WHERE validated = 1 AND ".$conditions."deleted = 1 ORDER BY ".$order_by." ".$direction." LIMIT ".$start.",".$limit;
				$query2 = "SELECT accountID".$image_field.", first_name, last_name".$balance_field.", address, city, home_phone_number, email_address, url FROM accounts WHERE validated = 1 AND ".$conditions."deleted = 1";
			}
		} else {
			$query1 = "SELECT accountID".$image_field.", first_name, last_name".$balance_field.", address, city, home_phone_number, work_phone_number, mobile_phone_number, email_address, url FROM accounts WHERE validated = 1 AND ".$conditions."suspended = 0 ORDER BY ".$order_by." ".$direction." LIMIT ".$start.",".$limit;
			$query2 = "SELECT accountID".$image_field.", first_name, last_name".$balance_field.", address, city, home_phone_number, work_phone_number, mobile_phone_number, email_address, url FROM accounts WHERE validated = 1 AND ".$conditions."suspended = 0";
		}
	} else {
		$query1 = "SELECT accountID".$image_field.", first_name, last_name".$balance_field.", address, city, home_phone_number, work_phone_number, mobile_phone_number, email_address, url FROM accounts WHERE validated = 1 AND ".$conditions."suspended = 0 ORDER BY ".$order_by." ".$direction." LIMIT ".$start.",".$limit;
		$query2 = "SELECT accountID".$image_field.", first_name, last_name".$balance_field.", address, city, home_phone_number, work_phone_number, mobile_phone_number, email_address, url FROM accounts WHERE validated = 1 AND ".$conditions."suspended = 0";
	}
	$members_search_form = $user->search_form($members_search_form_indent,ucwords($member_search_term),$member_search_type);
	$members_list = query_output($members_list_indent,$order_by,$order_by_default,$direction,$direction_default,$start,$start_default,$limit,$limit_default,$query1,$query2,URL.MEMBER_LIST_URL.'/'.append_url(),$member_search_term,$member_search_type);

	
}
?>