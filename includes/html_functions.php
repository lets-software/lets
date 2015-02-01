<?php

function login_html($i,$use_uri = true) {
	global $user;
	global $links;
	$links->build_url(1,1);
	$z = $i."<!-- login_html -->\n";
	if (!user_type()) {
		
		$links->build_url(1,1);
		$z .= "$i <span>Welcome <strong>Guest</strong></span><br /><span>Please Login or <a href=\"".URL.$links->complete_url."\">Register to Join</a>:</span>\n";
		
		if (TEMPLATE == 'viclets') {
			$links->build_url(13,0);
			$z .= "$i <br /><span><a href=\"".URL.$links->complete_url."\">Lost Password?</a></span>\n";
			$z .= "$i</td>$i\n$i<td align=\"left\" valign=\"bottom\">\n";
		}
			
		$z .= "$i <form id=\"login\" action=\"";
		if ($use_uri == true) {
			$z .= $_SERVER["REQUEST_URI"];
		} else {
			$z .= URL.MEMBERS_URL."/";
		}
		$z .= "\" method=\"post\">\n";		
		$z .= "$i  <span id=\"login_id_label\">".ucwords(MEMBERS_NAME_SINGULAR)." No:</span>\n";
		$z .= "$i  <input type=\"text\" id=\"login_id\" name=\"login_id\" />\n";
		$z .= "$i  <span id=\"login_password_label\">Password:</span>\n";
		$z .= "$i  <input type=\"password\" id=\"login_password\" name=\"login_password\" />\n";
		$z .= "$i  <input id=\"login_button\" type=\"submit\" name=\"login\" value=\"Login\" /><br class=\"right\"/>\n";
		$z .= "$i </form>\n";
		if (TEMPLATE != 'viclets') {
			$links->build_url(13,0);
			$z .= "$i <span><a href=\"".URL.$links->complete_url."\">Lost Password?</a></span>\n";
		}
	} else {
		$z .= "$i <span id=\"login_message\">Welcome <strong>".$_SESSION["member_name"]."</strong></span><br /><span>Please <a id=\"login_link\" href=\"".URL."logout/\">logout here</a></span>\n";
		$z .= "$i</td>$i\n$i<td width=\"600px\" align=\"left\" valign=\"bottom\">&nbsp;\n";
	}
	$z .= "$i<!-- /login_html -->\n";
	return $z;
}
function first_name($id) {
	global $user;
	$z = '<a href="'.URL.MEMBER_LIST_URL.'/'.$id.'/'.append_url(0).'">';
	if (user_type() and !$_SESSION['member_suspended'] and $_SESSION['member_validated']) {
		$z .= $user->first_name($id);
	} else {
		$z .= ucwords(MEMBERS_NAME_SINGULAR).' '.$id;
	}
	$z .= "</a>";
	return $z;
}
function no_database($i) {
	return "{$i}database problems, please come back";
}

/**
* Select a drop down menu or a check box if $val and $var matches
*
* @param $val   Value of the drop down menu or check box
* @param $var   Value which should be selected
*
* @return string : if $val and $var matches
**/
function check_selected($val,$var) {
	if ($val == $var) {
		return ' selected="selected"';
	} else {
		return "";
	}
}

function search_sidebar($i) {
	global $links;
	$links->build_url(12,0);
	$keyword = return_link_variable('keyword','');
	if (isset($_POST['keyword'])) {
		if ($_POST['keyword']) {
			$keyword = $_POST['keyword'];
		}
	}
	$z = $i."<!-- search_sidebar -->\n";
	$z .= $i."<div id=\"search_sidebar\">\n";
	$z .= $i." <form id=\"search\" action=\"".URL.$links->complete_url.append_url(0)."\" method=\"post\">\n";
	$z .= $i."  <input id=\"search_box_sidebar\" type=\"text\" name=\"keyword\" value=\"".$keyword."\" /><br />\n";
	$z .= $i."  <input id=\"search_box_sidebar_button\" type=\"submit\" name=\"submit\" value=\"Search ".SITE_NAME."\" />\n";
	$z .= $i." </form>\n";
	$z .= $i.'</div>'."\n";
	$z .= $i."<!-- /search_sidebar -->\n";
	return $z;
}
function time_stamp() {
	global $date;
	return return_month($date['month']).' '.$date['day'].', '.$date['year'].' - '.$date['hour'].':'.return_minute($date['minutes']).':'.return_minute($date['seconds']);
}
function check_empty($value) {
	if (!empty($value)) {
		return $value;
	} else {
		return "&nbsp;";
	}
}
function return_table_header_link($url,$var,$val,$direction,$start,$start_default,$limit,$limit_default,$member_search_term,$member_search_type) {
	if (!strpos($url,"?")) {
		$a = "?";
	} else {
		$a = "&";
	}
	$z = "";
	if ($member_search_term) {
		$member_search_term = htmlentities($member_search_term);
		$z .= '&member_search_term='.str_replace(' ','_',$member_search_term);
	}
	if ($member_search_type) {
		$z .= '&member_search_type='.$member_search_type;
	}
	if ($start != $start_default) {
		$z .= "&start=".$start;
	}
	if ($limit != $limit_default) {
		$z .= "&show=".$limit;
	}
	
	if ($var == $val) {
		if ($direction == "ASC") {
			return $url.$a."order_by=".$var."&direction=DESC".$z;
		} else {
			return $url.$a."order_by=".$var."&direction=ASC".$z;
		}
	} else {
		return $url.$a."order_by=".$var."&direction=ASC".$z;
	}
}
function return_table_page_link($url,$type,$start,$start_default,$limit,$limit_default,$order_by,$order_by_default,$direction,$direction_default,$num_rows,$member_search_term,$member_search_type) {
	if (!strpos($url,"?")) {
		$a = "?";
	} else {
		$a = "&";
	}
	$z = "";
	if ($member_search_term) {
		$member_search_term = htmlentities($member_search_term);
		$z .= '&member_search_term='.str_replace(' ','_',$member_search_term);
	}
	if ($member_search_type) {
		$z .= '&member_search_type='.$member_search_type;
	}
	if ($order_by != $order_by_default) {
		$z .= "&order_by=".$order_by;
	}
	if ($direction != $direction_default) {
		$z .= "&direction=".$direction;
	}
	if ($type == "start") {
		if ($limit != $limit_default) {
			return $url.$a."start=".$start."&show=".$limit.$z;
		} else {
			return $url.$a."start=".$start.$z;
		}
	}
	if ($type == "limit") {
		if ($start != $start_default) {
			if (($start + $limit) > $num_rows) {
				$start = $num_rows - $limit;
				if ($start < 0) {
					$start = 0;
				}
			}
			return $url.$a."start=".$start."&show=".$limit.$z;
		} else {
			return $url.$a."show=".$limit.$z;
		}
	}
}

function clean_table_header_anchor_text($string) {
	if ($string == "accountID") {
		return ucwords(MEMBERS_NAME_SINGULAR).' Number';
	}
	if ($string == "imageID") {
		return "Image";
	}
	$z = str_replace("_"," ",$string);
	return ucwords($z);
}
function query_output($i,$order_by,$order_by_default,$direction,$direction_default,$start,$start_default,$limit,$limit_default,$query1,$query2,$url,$member_search_term = '',$member_search_type = '') {
	// NOTE: This function is used exclusively my member_list.php
	
	global $user;
	global $style;
	$image = new images;
	$result = mysql_query($query1);
	if (!$result) {
		return $i."Database error: ".mysql_error();
	}
	$num_rows_this_query = mysql_num_rows($result);
	$num_rows_query = mysql_query($query2);
	$num_rows = mysql_num_rows($num_rows_query);
	
	if ($num_rows == 0) {
		return $i."No data was found";
	}
	// query successfull

	$field_array = "";
	$num_fields = mysql_num_fields($result);
	
	if (($start + $limit) < $num_rows) {
		$finish = $start + $limit;
	} else {
		$finish = $num_rows;
	}
	
	$page = ceil($start/$limit) + 1;
	$pages = ceil($num_rows/$limit);
	
	$z = $i."<!-- member_list -->\n";
	$z .= $i."<div id=\"query_result\">\n";
	$z .= $i." Viewing ".($start + 1)." to ".$finish." of ".$num_rows." results<br /><br />\n";
	
	$page_html = '';
	if ($pages > 1) {
		$z .= $i."";
		for ($s=1;$s<($pages+1);$s++) {
			if ($s == $page) {
				$z .= " Page ".$s." ";
				$page_html .= " Page ".$s." ";
			} else {
				$z .= "<a class=\"table_page\" href=\"".return_table_page_link($url,"start",(($limit * $s) - $limit),$start_default,$limit,$limit_default,$order_by,$order_by_default,$direction,$direction_default,$num_rows,$member_search_term,$member_search_type).append_url(' ?')."\">Page ".$s."</a> ";
				$page_html .= "<a class=\"table_page\" href=\"".return_table_page_link($url,"start",(($limit * $s) - $limit),$start_default,$limit,$limit_default,$order_by,$order_by_default,$direction,$direction_default,$num_rows,$member_search_term,$member_search_type).append_url(' ?')."\">Page ".$s."</a> ";
			}
		}
		$z .= "<br /><br />\n";
	}
	
	$z .= $i." <table>\n";
	$z .= $i."  <tr>\n";
	for ($j=0;$j<$num_fields;$j++) {
		$column_name = mysql_field_name($result,$j);
		$field_array[$j] = $column_name;
		if ($column_name == $order_by) {
			if ($direction == "ASC") {
				$arrow = "<img class=\"column_direction\" src=\"".URL."images/asc.gif\"  width=\"5\" height=\"15\" alt=\"Ascending\" /> ";
				$class = "h_o";
			} else {
				$arrow = "<img class=\"column_direction\" src=\"".URL."images/desc.gif\"  width=\"5\" height=\"15\" alt=\"Descending\" /> ";
				$class = "h_o";
			}
		} else {
			$arrow = "";
			$class = "h_d";
		}
		$z .= $i."   <th class=\"".$class."\">".$arrow."\n";
		$z .= $i."    <a class=\"table_header\" href=\"".return_table_header_link($url,$column_name,$order_by,$direction,$start,$start_default,$limit,$limit_default,$member_search_term,$member_search_type).append_url(' ?')."\">".clean_table_header_anchor_text($column_name)."</a>\n";
		$z .= $i."   </th>\n";
	}
	$z .= $i."  </tr>\n";
	for ($k=0;$k<$num_rows_this_query;$k++) {
		$z .= $i."  <tr>\n";
		for ($b=0;$b<$num_fields;$b++) {
			if ($field_array[$b] == $order_by) {
				$class = "l_o";
			} else {
				$class = "l_d";
			}
			if ($b == ($num_fields - 1)) {
				$class .= '_end';
			}
			
			if ($field_array[$b] == "accountID") {
                $id = stripslashes(mysql_result($result,$k,$field_array[$b]));
				$z .= $i."   <td class=\"".$class."\"><a class=\"member_list_link\" href=\"".$url.stripslashes(mysql_result($result,$k,$field_array[$b])).append_url($url.stripslashes(mysql_result($result,$k,$field_array[$b])))."/\">".stripslashes(mysql_result($result,$k,$field_array[$b]))."</a></td>\n";
				
			} elseif ($field_array[$b] == 'imageID') {
				if (mysql_result($result,$k,$field_array[$b])) {
					if ($image->img(stripslashes(mysql_result($result,$k,$field_array[$b])),'t',0,'member_image_thumb')) {
						$img = "<a href=\"".$url.$id."/".append_url(0)."\">".$image->img."</a>";
					} else {
						$img = '&nbsp;';
					}
				} else {
					$img = '&nbsp;';
				}				
				$z .= $i."   <td class=\"".$class."\">".$img."</td>\n";
			} elseif ($field_array[$b] == 'email_address') {
				$email = check_empty(stripslashes(mysql_result($result,$k,$field_array[$b])));
				if (strlen($email) > 25) {
					$display_email = substr_replace($email,'...',25);
				} else {
					$display_email = $email;
				}
				if ($email == "&nbsp;") {
					$z .= $i."   <td class=\"".$class."\">&nbsp;</td>\n";
				} else {
					$z .= $i."   <td class=\"".$class."\"><a href=\"mailto:".$email."\">".$display_email."</a></td>\n";
				}
			} elseif ($field_array[$b] == 'url') {
				$url_check = check_empty(stripslashes(mysql_result($result,$k,$field_array[$b])));
				if (strlen($url_check) > 35) {
					$display_url = substr_replace($url_check,'...',35);
				} else {
					$display_url = $url_check;
				}
				$display_url = str_replace('http://','',$display_url);
				if ($url_check == "&nbsp;") {
					$z .= $i."   <td class=\"".$class."\">&nbsp;</td>\n";
				} else {
					$z .= $i."   <td class=\"".$class."\"><a href=\"".$url_check.append_url($url_check)."\">".$display_url."</a></td>\n";
				}
			} else {
				$z .= $i."   <td class=\"".$class."\">".check_empty(stripslashes(mysql_result($result,$k,$field_array[$b])))."</td>\n";
			}
		}
		$z .= $i."  </tr>\n";
	}
	$z .= $i." </table>\n";
	$z .= $i." </div>\n";
	
	if ($limit_default < $num_rows) {
		$z .= $page_html."<br /><br />\n";
		$z .= $i." Show: ";
		for ($p=$limit_default;$p<200;$p=$p+$limit_default) {
			if (($p - $limit_default) < $num_rows) {
				if ($p != $limit) {
					$z .= "<a class=\"table_page\" href=\"".return_table_page_link($url,"limit",$start,$start_default,$p,$limit_default,$order_by,$order_by_default,$direction,$direction_default,$num_rows,$member_search_term,$member_search_type).append_url(' ?')."\">".$p."</a> ";
				} else {
					$z .= $p." ";
				}
			}
		}
		$z .= "<br />\n";
	}
	$z .= $i."<!-- /member_list -->\n";
	return $z;
}
function date_form($i,$date,$prefix,$allow_all = false,$disabled = 'false',$title = '') {
	if ($disabled == 'false') {
		$disabled_string = '';
	} elseif ($disabled == 'true') {
		$disabled_string = ' disabled="true"';
	}
	
	$z = '';
	if ($title) {
		$current_indent = $i;
		$i = $i.' ';
		$z .= $current_indent.'<div class="'.$title."\">\n";
		$z .= $current_indent."Month:<br />\n";
	}
	
	$z .= $i."<select name=\"".$prefix."month\"".$disabled_string.">\n";
	if ($allow_all) {
		$z .= $i." <option value=\"0\"".check_selected(0,$date['month']).">All</option>\n";
	}
	$z .= $i." <option value=\"1\"".check_selected(1,$date['month']).">January</option>\n";
	$z .= $i." <option value=\"2\"".check_selected(2,$date['month']).">Febuary</option>\n";
	$z .= $i." <option value=\"3\"".check_selected(3,$date['month']).">March</option>\n";
	$z .= $i." <option value=\"4\"".check_selected(4,$date['month']).">April</option>\n";
	$z .= $i." <option value=\"5\"".check_selected(5,$date['month']).">May</option>\n";
	$z .= $i." <option value=\"6\"".check_selected(6,$date['month']).">June</option>\n";
	$z .= $i." <option value=\"7\"".check_selected(7,$date['month']).">July</option>\n";
	$z .= $i." <option value=\"8\"".check_selected(8,$date['month']).">August</option>\n";
	$z .= $i." <option value=\"9\"".check_selected(9,$date['month']).">September</option>\n";
	$z .= $i." <option value=\"10\"".check_selected(10,$date['month']).">October</option>\n";
	$z .= $i." <option value=\"11\"".check_selected(11,$date['month']).">November</option>\n";
	$z .= $i." <option value=\"12\"".check_selected(12,$date['month']).">December</option>\n";
	$z .= $i."</select>\n";
	if ($title) {
		$z .= $current_indent."</div>\n";
		$z .= $current_indent.'<div class="'.$title."\">\n";
		$z .= $current_indent."Day:<br />\n";
	}
	$z .= $i."<select name=\"".$prefix."day\"".$disabled_string.">\n";
	if ($allow_all) {
		$z .= $i." <option value=\"0\"".check_selected(0,$date['day']).">All</option>\n";
	}
	$z .= $i." <option value=\"1\"".check_selected(1,$date['day']).">1</option>\n";
	$z .= $i." <option value=\"2\"".check_selected(2,$date['day']).">2</option>\n";
	$z .= $i." <option value=\"3\"".check_selected(3,$date['day']).">3</option>\n";
	$z .= $i." <option value=\"4\"".check_selected(4,$date['day']).">4</option>\n";
	$z .= $i." <option value=\"5\"".check_selected(5,$date['day']).">5</option>\n";
	$z .= $i." <option value=\"6\"".check_selected(6,$date['day']).">6</option>\n";
	$z .= $i." <option value=\"7\"".check_selected(7,$date['day']).">7</option>\n";
	$z .= $i." <option value=\"8\"".check_selected(8,$date['day']).">8</option>\n";
	$z .= $i." <option value=\"9\"".check_selected(9,$date['day']).">9</option>\n";
	$z .= $i." <option value=\"10\"".check_selected(10,$date['day']).">10</option>\n";
	$z .= $i." <option value=\"11\"".check_selected(11,$date['day']).">11</option>\n";
	$z .= $i." <option value=\"12\"".check_selected(12,$date['day']).">12</option>\n";
	$z .= $i." <option value=\"13\"".check_selected(13,$date['day']).">13</option>\n";
	$z .= $i." <option value=\"14\"".check_selected(14,$date['day']).">14</option>\n";
	$z .= $i." <option value=\"15\"".check_selected(15,$date['day']).">15</option>\n";
	$z .= $i." <option value=\"16\"".check_selected(16,$date['day']).">16</option>\n";
	$z .= $i." <option value=\"17\"".check_selected(17,$date['day']).">17</option>\n";
	$z .= $i." <option value=\"18\"".check_selected(18,$date['day']).">18</option>\n";
	$z .= $i." <option value=\"19\"".check_selected(19,$date['day']).">19</option>\n";
	$z .= $i." <option value=\"20\"".check_selected(20,$date['day']).">20</option>\n";
	$z .= $i." <option value=\"21\"".check_selected(21,$date['day']).">21</option>\n";
	$z .= $i." <option value=\"22\"".check_selected(22,$date['day']).">22</option>\n";
	$z .= $i." <option value=\"23\"".check_selected(23,$date['day']).">23</option>\n";
	$z .= $i." <option value=\"24\"".check_selected(24,$date['day']).">24</option>\n";
	$z .= $i." <option value=\"25\"".check_selected(25,$date['day']).">25</option>\n";
	$z .= $i." <option value=\"26\"".check_selected(26,$date['day']).">26</option>\n";
	$z .= $i." <option value=\"27\"".check_selected(27,$date['day']).">27</option>\n";
	$z .= $i." <option value=\"28\"".check_selected(28,$date['day']).">28</option>\n";
	$z .= $i." <option value=\"29\"".check_selected(29,$date['day']).">29</option>\n";
	$z .= $i." <option value=\"30\"".check_selected(30,$date['day']).">30</option>\n";
	$z .= $i." <option value=\"31\"".check_selected(31,$date['day']).">31</option>\n";
	$z .= $i."</select>\n";
	if ($title) {
		$z .= $current_indent."</div>\n";
		$z .= $current_indent.'<div class="'.$title."\">\n";
		$z .= $current_indent."Year:<br />\n";
	}
	$z .= $i."<select name=\"".$prefix."year\"".$disabled_string.">\n";
	if ($allow_all) {
		$z .= $i." <option value=\"0\"".check_selected(0,$date['year']).">All</option>\n";
	}
	$z .= $i." <option value=\"2000\"".check_selected(2000,$date['year']).">2000</option>\n";
	$z .= $i." <option value=\"2001\"".check_selected(2001,$date['year']).">2001</option>\n";
	$z .= $i." <option value=\"2002\"".check_selected(2002,$date['year']).">2002</option>\n";
	$z .= $i." <option value=\"2003\"".check_selected(2003,$date['year']).">2003</option>\n";
	$z .= $i." <option value=\"2004\"".check_selected(2004,$date['year']).">2004</option>\n";
	$z .= $i." <option value=\"2005\"".check_selected(2005,$date['year']).">2005</option>\n";
	$z .= $i." <option value=\"2006\"".check_selected(2006,$date['year']).">2006</option>\n";
	$z .= $i." <option value=\"2007\"".check_selected(2007,$date['year']).">2007</option>\n";
	$z .= $i." <option value=\"2008\"".check_selected(2008,$date['year']).">2008</option>\n";
	$z .= $i." <option value=\"2009\"".check_selected(2009,$date['year']).">2009</option>\n";
	$z .= $i." <option value=\"2010\"".check_selected(2010,$date['year']).">2010</option>\n";
	$z .= $i." <option value=\"2011\"".check_selected(2011,$date['year']).">2011</option>\n";
	$z .= $i." <option value=\"2012\"".check_selected(2012,$date['year']).">2012</option>\n";
	$z .= $i." <option value=\"2013\"".check_selected(2013,$date['year']).">2013</option>\n";
	$z .= $i." <option value=\"2014\"".check_selected(2014,$date['year']).">2014</option>\n";
	$z .= $i." <option value=\"2015\"".check_selected(2015,$date['year']).">2015</option>\n";
	$z .= $i." <option value=\"2016\"".check_selected(2016,$date['year']).">2016</option>\n";
	$z .= $i." <option value=\"2017\"".check_selected(2017,$date['year']).">2017</option>\n";
	$z .= $i." <option value=\"2018\"".check_selected(2018,$date['year']).">2018</option>\n";
	$z .= $i." <option value=\"2019\"".check_selected(2019,$date['year']).">2019</option>\n";
	$z .= $i." <option value=\"2020\"".check_selected(2020,$date['year']).">2020</option>\n";
	$z .= $i."</select>\n";	return $z;
	if ($title) {
		$z .= $current_indent."</div>\n";
	}
}

/**
* 
*
**/
function selected($fi,$bi,$val,$var) {
	if ($val == $var) return $fi.'selected="selected"'.$bi;
	return $bi;
}


function member_quicklinks($i,$id = 0) {
	$self = 0;
    if (empty($id) or $id == $_SESSION['member_id']) {
		$id = $_SESSION['member_id'];
		$self = 1;
    }
	$mysql = new mysql;
	global $links;
	
	$z = $i."<!-- member_quicklinks -->\n";
	if ($self) {
		$z .= $i."<div class=\"member_self_quicklinks\">\n";
        $z .= $i." You have:<br />\n";
	} else {
        $z .= $i."<div class=\"member_other_quicklinks\">\n";
        $z .= $i." This ".strtolower(MEMBERS_NAME_SINGULAR)." has:<br />\n";
	}

    $num_ads = 0;
	if ($mysql->result('SELECT COUNT(noticeboardID) as num FROM noticeboard WHERE accountID = '.$id.' AND expired = 0 AND bought = 0 AND (expiry_year > '.$GLOBALS['date']['year'].' OR ((expiry_year = '.$GLOBALS['date']['year'].' AND expiry_month > '.$GLOBALS['date']['month'].') OR (expiry_year = '.$GLOBALS['date']['year'].' AND expiry_month = '.$GLOBALS['date']['month'].' AND expiry_day > '.$GLOBALS['date']['day'].') OR (expiry_year = '.$GLOBALS['date']['year'].' AND expiry_month = '.$GLOBALS['date']['month'].' AND expiry_day = '.$GLOBALS['date']['day'].' AND expiry_hour > '.$GLOBALS['date']['hour'].')) OR (expiry_year = 0 AND expiry_month = 0 AND expiry_day = 0 AND expiry_hour = 0))')) {
		$num_ads = $mysql->result['num'];
	}
	if (ENABLE_NOTICEBOARD) {
	    if ($num_ads == 1) {
	       	$z .= $i.' <a href="'.URL.NOTICEBOARD_URL.'/?noticeboard_member='.$id.append_url(' ?').'">1 '.strtolower(NOTICEBOARD_NAME_SINGULAR)."</a>,<br />\n";
		} elseif ($num_ads > 1) {
	       	$z .= $i.' <a href="'.URL.NOTICEBOARD_URL.'/?noticeboard_member='.$id.append_url(' ?').'">'.$num_ads.' '.strtolower(NOTICEBOARD_NAME_PLURAL)."</a>,<br />\n";
		} else {
			if ($self and $links->build_url(1,5)) {
            	$z .= $i.' <a href="'.URL.$links->complete_url.append_url(0).'">No '.strtolower(NOTICEBOARD_NAME_PLURAL)."</a>,<br />\n";
			} else {
				$z .= $i.' <a href="'.URL.NOTICEBOARD_URL.'/'.append_url(0).'">No '.strtolower(NOTICEBOARD_NAME_PLURAL)."</a>,<br />\n";
			}
		}
	}

    $num_articles = 0;
	if ($mysql->result('SELECT COUNT(articleID) as num FROM articles WHERE accountID = '.$id)) {
		$num_articles = $mysql->result['num'];
	}
	if (ENABLE_ARTICLES) {
	    if ($num_articles == 1) {
	       	$z .= $i.' <a href="'.URL.ARTICLES_URL.'/?member='.$id.append_url(' ?').'">1 '.strtolower(ARTICLES_NAME_SINGULAR)."</a>,<br />\n";
		} elseif ($num_articles > 1) {
	       	$z .= $i.' <a href="'.URL.ARTICLES_URL.'/?member='.$id.append_url(' ?').'">'.$num_articles.' '.strtolower(ARTICLES_NAME_PLURAL)."</a>,<br />\n";
		} else {
			if ($self and $links->build_url(1,7)) {
            	$z .= $i.' <a href="'.URL.$links->complete_url.append_url(0).'">No '.strtolower(ARTICLES_NAME_PLURAL)."</a>,<br />\n";
			} else {
				$z .= $i.' <a href="'.URL.ARTICLES_URL.'/'.append_url(0).'">No '.strtolower(ARTICLES_NAME_PLURAL)."</a>,<br />\n";
			}
		}
	}

    $num_events = 0;
	if ($mysql->result('SELECT COUNT(eventID) as num FROM events WHERE accountID = '.$id.' AND ((end_year > '.$GLOBALS['date']['year'].') OR (end_year = '.$GLOBALS['date']['year'].' AND end_month > '.$GLOBALS['date']['month'].') OR (end_year = '.$GLOBALS['date']['year'].' AND end_month = '.$GLOBALS['date']['month'].' AND end_day > '.$GLOBALS['date']['day'].') OR (end_year = '.$GLOBALS['date']['year'].' AND end_month = '.$GLOBALS['date']['month'].' AND end_day = '.$GLOBALS['date']['day'].' AND end_hour > '.$GLOBALS['date']['hour'].') OR (end_year = '.$GLOBALS['date']['year'].' AND end_month = '.$GLOBALS['date']['month'].' AND end_day = '.$GLOBALS['date']['day'].' AND end_hour = '.$GLOBALS['date']['hour'].' AND end_minute > '.$GLOBALS['date']['minutes'].'))')) {
		$num_events = $mysql->result['num'];
	}
	if (ENABLE_EVENTS) {
	    if ($num_events == 1) {
	       	$z .= $i.' <a href="'.URL.EVENTS_URL.'/?member='.$id.append_url(' ?').'">1 upcoming '.strtolower(EVENTS_NAME_SINGULAR)."</a>,<br />\n";
		} elseif ($num_events > 1) {
	       	$z .= $i.' <a href="'.URL.EVENTS_URL.'/?member='.$id.append_url(' ?').'">'.$num_events.' upcoming '.strtolower(EVENTS_NAME_PLURAL)."</a>,<br />\n";
		} else {
			if ($self and $links->build_url(1,8)) {
				$z .= $i.' <a href="'.URL.$links->complete_url.append_url(0).'">No upcoming '.strtolower(EVENTS_NAME_PLURAL)."</a>,<br />\n";
			} else {
				$z .= $i.' <a href="'.URL.EVENTS_URL.'/'.append_url(0).'">No upcoming '.strtolower(EVENTS_NAME_PLURAL)."</a>,<br />\n";
			}
		}
	}

    $num_faq = 0;
	if ($mysql->result('SELECT COUNT(faqID) as num FROM faq WHERE accountID = '.$id)) $num_faq = $mysql->result['num'];
	if (ENABLE_FAQ) {
	    if ($num_faq == 1) {
	       	$z .= $i.' <a href="'.URL.FAQ_URL.'/'.append_url(0).'">1 '.strtolower(FAQ_NAME_SINGULAR)." contribution,</a><br />\n";
		} elseif ($num_faq > 1) {
			$z .= $i.' <a href="'.URL.FAQ_URL.'/'.append_url(0).'">'.$num_faq.' '.strtolower(FAQ_NAME_SINGULAR)." contributions,</a><br />\n";
		} else {
			if ($self and $links->build_url(1,9)) {
				$z .= $i.' <a href="'.URL.$links->complete_url.append_url(0).'">No '.strtolower(FAQ_NAME_SINGULAR)." contributions,</a><br />\n";
			} else {
				$z .= $i.' <a href="'.URL.FAQ_URL.'/'.append_url(0).'">No '.strtolower(FAQ_NAME_SINGULAR)." contributions,</a><br />\n";
			}
		}
	}

    $num_links = 0;
	if ($mysql->result('SELECT COUNT(linkID) as num FROM links WHERE accountID = '.$id)) $num_links = $mysql->result['num'];
	if (ENABLE_LINKS) {
	    if ($num_links == 1) {
	       	$z .= $i.' <a href="'.URL.LINKS_URL.'/'.append_url(0).'">1 '.strtolower(LINKS_NAME_SINGULAR)." contribution</a>,<br />\n";
		} elseif ($num_links > 1) {
	       	$z .= $i.' <a href="'.URL.LINKS_URL.'/'.append_url(0).'">'.$num_links.' '.strtolower(LINKS_NAME_SINGULAR)." contributions</a>,<br />\n";
		} else {
			if ($self and $links->build_url(1,10)) {
				$z .= $i.' <a href="'.URL.$links->complete_url.append_url(0).'">No '.strtolower(LINKS_NAME_SINGULAR)." contributions</a>,<br />\n";
			} else {
				$z .= $i.' <a href="'.URL.LINKS_URL.'/'.append_url(0).'">No '.strtolower(LINKS_NAME_SINGULAR)." contributions</a>,<br />\n";
			}
		}
	}
	
	$num_transactions = 0;
	if (ENABLE_NOTICEBOARD) {
		if ($mysql->result('SELECT COUNT(transactionID) as num FROM transactions WHERE (buyerID = '.$id.' OR sellerID = '.$id.') AND type != 4 AND type != 5')) {
			$num_transactions = $mysql->result['num'];
		}
	}
	$num_comments = 0;
	$possesive_word = '';
	$comments_blurb = '';
	if (ENABLE_COMMENTS) {
		if ($mysql->result('SELECT COUNT(commentID) as num FROM comments WHERE accountID = '.$id)) {
			$num_comments = $mysql->result['num'];
		}
		if ($num_comments == 1) {
			$comments_blurb = ' 1 comments and ';
		} elseif ($num_comments > 1) {
			$comments_blurb = ' '.$num_comments.' comments and ';
		}
		if ($self) {
			$possesive_word = ' have';
		} else {
			$possesive_word = ' has';
		}
	}
	$front_link = '';
	$back_link = '';
	if (ENABLE_NOTICEBOARD) {
		if (ALLOW_VIEW_OTHER_TRANSACTION_HISTORY or user_type() == 2) {
			if ($links->build_url(1,3)) {
				$front_link = '<a href="'.URL.$links->complete_url.$id.'/'.append_url(0).'">';
				$back_link = '</a>';
			}
		}
		if ($num_transactions == 1) {
			$z .= $i.' and'.$possesive_word.' made'.$comments_blurb.' '.$front_link.'1 '.strtolower(TRANSACTION_NAME_SINGULAR).$back_link.".<br />\n";
		} elseif ($num_transactions > 1) {
			$z .= $i.' and'.$possesive_word.' made'.$comments_blurb.' '.$front_link.$num_transactions.' '.strtolower(TRANSACTION_NAME_PLURAL).$back_link.".<br />\n";
		} else {
			$z .= $i.' and'.$possesive_word.' made'.$comments_blurb.' no '.strtolower(TRANSACTION_NAME_PLURAL).".<br />\n";
		}
	}
    $z .= $i."</div>\n";
	$z .= $i."<!-- /member_quicklinks -->\n";
	return $z;
}

?>
