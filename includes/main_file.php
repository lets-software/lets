<?php
function removeEvilAttributes($tagSource) {
    $stripAttrib = "' (style|class|id)=\"(.*?)\"'i";
    $tagSource = stripslashes($tagSource);
    $tagSource = preg_replace($stripAttrib, '', $tagSource);
    return $tagSource;
}
function createRandomPassword() {
    $chars = "abcdefghijkmnopqrstuvwxyz023456789";
    srand((double)microtime()*1000000);
    $i = 0;
    $pass = '' ;

    while ($i <= 15) {
        $num = rand() % 33;
        $tmp = substr($chars, $num, 1);
        $pass = $pass . $tmp;
        $i++;
    }
    return $pass;

}

function setLanguage($language){
    if(isset($language) && $language != "") {
        switch($language) {
            case "FRA":
                return "FRA";
                break;
            default:
                return "ENG";
        }
    }
}

function let_to_num($v){ //This function transforms the php.ini notation for numbers (like '2M') to an integer (2*1024*1024 in this case)
    $l = substr($v, -1);
    $ret = substr($v, 0, -1);
    switch(strtoupper($l)){
    case 'P':
        $ret *= 1024;
    case 'T':
        $ret *= 1024;
    case 'G':
        $ret *= 1024;
    case 'M':
        $ret *= 1024;
    case 'K':
        $ret *= 1024;
    break;
        }
    return $ret;
}
function remove_bad_tags($source) {
    $allowedTags='<a><br><b><h1><h2><h3><h4><i><img><li><ol><p><strong><table><tr><td><th><u><ul>';
    $source = strip_tags($source, $allowedTags);
    return preg_replace('/<(.*?)>/ie', "'<'.removeEvilAttributes('\\1').'>'", $source);
}
function delete_leading_zero($var) {
    if ($var{1} == 0) {
        return substr($var,1);
    } else {
        return $var;
    }
}
function variable_exists($var) {
    if (!isset($var)) {
        return 0;
    } else {
        if ($var) {
            return 1;
        } else {
            return 0;
        }
    }
}
if(!function_exists('scandir')) {
    function scandir($dir, $sortorder = 0) {
        if(is_dir($dir)) {
            $dirlist = opendir($dir);
            while( ($file = readdir($dirlist)) !== false) {
                if(!is_dir($file)) {
                    $files[] = $file;
                }
            }
            ($sortorder == 0) ? asort($files) : arsort($files);
            return $files;
        } else {
            return FALSE;
            break;
        }
    }
}
function post_to_get($add_question_mark = '') {
    if (!count($_POST)) return $add_question_mark;
    $post_post = remove_slashes($_POST);
    $keys = array_keys($post_post);
    $get = '';
    foreach ($keys as $key) {
        if ($key != 'SID' and $key != 'submit' and $post_post[$key]) {
            $get .= '&'.$key.'='.$post_post[$key];
        }
    }
    return $get;
}
function send_single_email($from_address,$from_name,$to_address,$to_name,$subject,$text_message,$html_message) {
    $mail = new PHPMailer();
    if (USE_SMTP) {    
        $mail->IsSMTP();                                      // set mailer to use SMTP
    }
    if (EMAIL_SMTP_HOST) {
        if (EMAIL_SMTP_HOST_BACKUP) {
            $mail->Host = EMAIL_SMTP_HOST.';'.EMAIL_SMTP_HOST_BACKUP;  // specify main and backup server
        } else {
            $mail->Host = EMAIL_SMTP_HOST;
        }
    } else {
        $mail->Host = 'localhost';
    }
    if ($GLOBALS['smtp_port']) {
        $mail->Port = $GLOBALS['smtp_port'];
    }
    if (USE_SMTP) {
        $mail->SMTPAuth = true;     // turn on SMTP authentication
        $mail->Username = SMTP_USER_NAME;  // SMTP username
        $mail->Password = SMTP_PASSWORD; // SMTP password
    }
    $mail->From = $from_address;
    $mail->FromName = $from_name;
    $mail->AddAddress($to_address,$to_name);
    $mail->AddReplyTo($to_address,$to_name);
    
    $mail->WordWrap = 50;                                 // set word wrap to 50 characters
    $mail->IsHTML(true);                                  // set email format to HTML
    
    $mail->Subject = $subject;
    $mail->Body    = $html_message;
    $mail->AltBody = $text_message;
    
    if($mail->Send()) {
        return '';
    } else {
        return $mail->ErrorInfo;
    }
}
function bulk_membership_email($criteria,$text_message,$html_message,$subject,$from_name) {
    $links = new links;
    $links->build_url(1,2);
    $mail = new PHPMailer();
    if (EMAIL_SMTP_HOST) {
        if (EMAIL_SMTP_HOST_BACKUP) {
            $mail->Host = EMAIL_SMTP_HOST.';'.EMAIL_SMTP_HOST_BACKUP;  // specify main and backup server
        } else {
            $mail->Host = EMAIL_SMTP_HOST;
        }
    } else {
        $mail->Host = 'localhost';
    }
    if (USE_SMTP) {
        $mail->Mailer   = "smtp";
        $mail->SMTPAuth = true;     // turn on SMTP authentication
        $mail->Username = SMTP_USER_NAME;  // SMTP username
        $mail->Password = SMTP_PASSWORD; // SMTP password
    } else {
        $mail->Mailer   = "mail";
    }
    $mail->From = UPDATE_EMAIL;
    $mail->FromName = $from_name;
    $mail->Subject = $subject;
    $mail->Body    = $html_message.'<br /><br />';
    
    $mail->Body    .= T_('Change your email delivery options <a href="'.URL.$links->complete_url.'">here</a>');
    $mail->AltBody = $text_message."\r\n\r\n\r\n\r\n Change your email delivery options here: ".URL.$links->complete_url;
    $mail->IsHTML(true);
    
    if ($criteria) {
        $q = 'SELECT email_address,first_name,last_name FROM accounts WHERE '.$criteria.' = 1 AND email_address != \'\'';
    } else {
        $q = 'SELECT email_address,first_name,last_name FROM accounts WHERE email_address != \'\'';
    }
    
    $mysql = new mysql;
    $mysql->build_array($q);
    if (is_array($mysql->result)) {
        foreach($mysql->result as $member) {
            $mail->AddAddress($member['email_address'], $member['first_name'].' '.$member['last_name']);
            if(!$mail->Send()) {
                if (LOG_ERRORS) log_error('Email could not be delivered to '.$member['first_name'].' '.$member['last_name'].' <br />Error: '.$mail->ErrorInfo);
            }
            $mail->ErrorInfo = '';
            $mail->ClearAddresses();
        }
    }
}
function log_action($text) {
    if (isset($_SESSION['member_id'])) {
        $log_text = time_stamp().' ['.$_SESSION['member_id'].'/'.$_SERVER['REMOTE_ADDR'].'] '.$text."\r\n";
    } else {
        $log_text = time_stamp().' [GUEST/'.$_SERVER['REMOTE_ADDR'].'] '.$text."\r\n";
    }
    $old_content = file_get_contents(PATH.'logs/'.str_replace(' ','_',SITE_NAME).'.log');
    $log_file_handle_write = fopen(PATH.'logs/'.str_replace(' ','_',SITE_NAME).'.log',"w");
    if (!$log_file_handle_write) { 
        return false;
    } else {
        if (fwrite($log_file_handle_write,$log_text.$old_content) === false) {
            return false;
        }
        fclose($log_file_handle_write);
    }
    return true;
}
function array_report($array) {
    ob_start();
    print_r($array);
    $buffer = ob_get_contents();
    ob_end_clean();
    return $buffer;
}
function bad_user() {
    $mysql = new mysql;
    $bool = false;
    if ($mysql->build_array("SELECT id FROM bad_logins WHERE ip = '".$_SERVER['REMOTE_ADDR']."'")) {
        if (count($mysql->result) > 20) {
            $bool = true;
        }
    }
    $q = 'DELETE FROM bad_logins WHERE ( year != '.$GLOBALS['date']['year'].' ) OR ( year = '.$GLOBALS['date']['year'].' AND month != '.$GLOBALS['date']['month'].') OR ( month = '.$GLOBALS['date']['month'].' AND day != '.$GLOBALS['date']['day'].' )';
    $mysql->query($q);
    return $bool;

}
function wrong_password() {
    $mysql = new mysql;
    $mysql->query("INSERT INTO bad_logins VALUES( '','".$_SERVER['REMOTE_ADDR']."','".$GLOBALS['date']['year']."','".$GLOBALS['date']['month']."','".$GLOBALS['date']['day']."','".$GLOBALS['date']['hour']."','".$GLOBALS['date']['minutes']."' )");
}
function log_error($text,$bypass_email = false) {
    $log_text = time_stamp()." - An error has occurred:\r\n";
    if (isset($_SESSION['member_id'])) {
        $log_text .= 'MEMBER: '.$_SESSION['member_id'].' ('.$_SESSION['member_name'].")\r\n";
    } else {
        $log_text .= "GUEST\r\n";
    }
    $log_text .= 'IP: '.$_SERVER['REMOTE_ADDR']."\r\n";
    $log_text .= 'Requested Page: '.$_SERVER['REQUEST_URI']."\r\n";
    $log_text .= 'Referrer: '.$_SERVER['HTTP_REFERER']."\r\n";
    $log_text .= 'Script Error: '.strip_tags(str_replace('<br />',"\n",$text))."\r\n";
    if (LOG_POST_DUMP) {
        if (is_array($_POST) and count($_POST) > 0) {
            $log_text .= 'POST Data: '.array_report($_POST)."\r\n";
        } else {
            $log_text .= 'No POST Data'."\r\n";
        }
        if (is_array($_GET) and count($_GET) > 0) {
            $log_text .= 'GET Data: '.array_report($_GET)."\r\n";
        } else {
            $log_text .= 'No GET Data'."\r\n";
        }
    }
    $log_text .= '*********** end of error report ************'."\r\n\r\n";
    
    if (EMAIL_TECHNICAL_ERRORS and !$bypass_email) {
        $email_text = T_(time_stamp()." - <strong>An error has occurred:</strong><br />");
        if (isset($_SESSION['member_id'])) {
            $email_text .= T_('<strong>MEMBER:</strong> ' . $_SESSION['member_id'] . ' (' . $_SESSION['member_name'] . ')<br />');
        } else {
            $email_text .= T_('<strong>GUEST</strong><br />');
        }
        $email_text .= T_('<strong>IP:</strong> ' . $_SERVER['REMOTE_ADDR'] . '<br />');
        $email_text .= T_('<strong>Requested Page:</strong> ' . $_SERVER['REQUEST_URI'] . '<br />');
        $email_text .= T_('<strong>Referrer:</strong> ' . $_SERVER['HTTP_REFERER'] . '<br />');
        $email_text .= T_('<strong>Script Error:</strong> ' . $text . '\<br />');
        if (LOG_POST_DUMP) {
            if (is_array($_POST) and count($_POST) > 0) {
                $email_text .= T_('<strong>POST Data:</strong> ' . indent_variable(' ',array_report($_POST),false) . '<br />');
            } else {
                $email_text .= T_('<strong>No POST Data</strong><br />');
            }
            if (is_array($_GET) and count($_GET) > 0) {
                    $email_text .= T_('<strong>GET Data:</strong> ' . indent_variable(' ',array_report($_GET),false) . '<br />');
            } else {
                $email_text .= T_('<strong>No GET Data</strong><br />');
            }
        }
        $email_text .= T_('*********** end of error report ************<br /><br />');
        
        send_single_email(UPDATE_EMAIL,SITE_NAME.' Error Reporter',TECHNICAL_EMAIL,'Technical Administrator','There is an error to report',$log_text,$email_text);
    }    
    
    $old_content = file_get_contents(PATH.'logs/'.str_replace(' ','_',SITE_NAME).'_Errors.log');
    $log_file_handle_write = fopen(PATH.'logs/'.str_replace(' ','_',SITE_NAME).'_Errors.log',"w");
    if (!$log_file_handle_write) { 
        return false;
    } else {
        if (fwrite($log_file_handle_write,$log_text.$old_content) === false) {
            return false;
        }
        fclose($log_file_handle_write);
    }
    return true;
}
function return_folders($path) {
    $files = scandir($path,1);
    $folders = array();
    $i = 0;
    if (is_array($files)) {
        foreach ($files as $file) {
            if (!strpos(' '.$file,'.')) {
                $folders[$i] = $file;
                $i++;
            }
        }
        return $folders;
    }
    return '';
}
function verify_email_address($email) {
    $email = htmlspecialchars(stripslashes(strip_tags($email)));     
    if ( eregi ( '[a-z||0-9]@[a-z||0-9].[a-z]', $email ) ) {
        $domain = explode( "@", $email );         
        if ( @fsockopen ($domain[1],80,$errno,$errstr,3)) { 
            return true; 
        } else { 
            echo '.';
            return false;
        } 
    } else { 
        return false;
    } 
}
function add_leading_zero($var) {
    // called by tranasctions->member_history,
    if (strlen($var) == 1) {
        return '0'.$var;
    } else {
        return $var;
    }
}
function set_checked($var) {
    if ($var) {
        return ' checked="checked"';
    }
}
function a($word) {
    $word = strtolower($word);
    if ($word{0} == 'a' or $word{0} == 'e' or $word{0} == 'i' or $word{0} == 'o' or $word{0} == 'u')
        return 'an'; else return 'a'; 
}
function remove_s($k) {
    if  (strrpos($k,"s") == (strlen($k) - 1)) {
        if ((strrpos($k,"e") == (strlen($k) - 2)) and (strrpos($k,"i") == (strlen($k) - 3))) {
            $k = str_replace('ies','y',$k);
        } else {
            $k = rtrim($k,"s");
        }            
    }
    return $k;
}
function return_time($h,$m) {
    $am_pm = '';
    if ($h > 12) {
        $am_pm = 'PM';
    } else {
        $am_pm = 'AM';
    }
    if (TWELVE_HOUR_CLOCK and $h > 12) {
        $h = $h - 12;
    }
    if (TWELVE_HOUR_CLOCK and !$h) $h = 12;
    return $h.':'.return_minute($m).' '.$am_pm;
}
function lastday($m) {
    if ($m == 1) { return "31"; }
    if ($m == 2) { return "28"; }
    if ($m == 3) { return "31"; }
    if ($m == 4) { return "30"; }
    if ($m == 5) { return "31"; }
    if ($m == 6) { return "30"; }
    if ($m == 7) { return "31"; }
    if ($m == 8) { return "31"; }
    if ($m == 9) { return "30"; }
    if ($m == 10) { return "31"; }
    if ($m == 11) { return "30"; }
    if ($m == 12) { return "31"; }
}

function return_link_variable($var,$default) {
    if (strpos($_SERVER["REQUEST_URI"],$var."=")) {
        $link = explode($var."=",$_SERVER["REQUEST_URI"]);
        if (strpos($link[1],"&")) {
            $a = explode("&",$link[1]);
            $z = $a[0];
        } else {
            $z = $link[1];
        }
        return $z;
    } else {
        return $default;
    }
}

function format_date($date) {
    $date = explode("-",$date);
    $z = return_month($date[0])." ".return_day($date[1]).", 20".$date[2];
    return $z;
}
function time_left($current,$expiry,$interval) {
    $add_day = 0;
    $add_month = 0;
    $add_year = 0;
    $add_hour = 0;
    if ($interval > 24) {
        $int_day = floor($interval / 24);
        if ($int_day > 30) {
            $int_mon = floor($int_day / 30);
            if ($int_mon > 12) {
                $add_year = floor($int_mon / 12);
                $add_month = $int_mon;
                $add_day = $int_day;
            } else {
                $add_month = $int_mon;
                $add_day = $int_day - ($int_mon * 30);
                $add_hour = $interval - ($int_day * 24);
            }
        } else {
            $add_day = $int_day;
            $add_hour = $interval - ($int_day * 24);
        }
    } else {
        $add_hour = $interval;
    }
    $expiry['day'] = $expiry['day'] + $add_day;
    $expiry['month'] = $expiry['month'] + $add_month;
    $expiry['year'] = $expiry['year'] + $add_year;
    $expiry['hour'] = $expiry['hour'] + $add_hour;
    if (($expiry['year'] < $current['year']) or ($expiry['year'] == $current['year'] and $expiry['month'] < $current['month']) or ($expiry['year'] == $current['year'] and $expiry['month'] == $current['month'] and $expiry['day'] < $current['day']) or ($expiry['year'] == $current['year'] and $expiry['month'] == $current['month'] and $expiry['day'] == $current['day'] and $expiry['hour'] < $current['hour'])) {
        return false;
    } else {
        return true;
    }
}
function change_date($element,$action,$value) {
    if ($element == "year") {
        if ($action == "+") {
            $date = date("j-n-Y",mktime(0, 0, 0, date("m"),  date("d"),  date("y")+$value));
            $date = explode('-',$date);
            return array('day' => $date[0], 'month' => $date[1], 'year' => $date[2]);
        }
        if ($action == "-") {
            $date = date("j-n-Y",mktime(0, 0, 0, date("m"),  date("d"),  date("y")-$value));
            $date = explode('-',$date);
            return array('day' => $date[0], 'month' => $date[1], 'year' => $date[2]);
        }
    }
    if ($element == "month") {
        if ($action == "+") {
            $date = date("j-n-Y",mktime(0, 0, 0, date("m")+$value,  date("d"),  date("y")));
            $date = explode('-',$date);
            return array('day' => $date[0], 'month' => $date[1], 'year' => $date[2]);
        }
        if ($action == "-") {
            $date = date("j-n-Y",mktime(0, 0, 0, date("m")-$value,  date("d"),  date("y")));
            $date = explode('-',$date);
            return array('day' => $date[0], 'month' => $date[1], 'year' => $date[2]);
        }
    }
    if ($element == "day") {
        if ($action == "+") {
            $date = date("j-n-Y",mktime(0, 0, 0, date("m"),  date("d")+$value,  date("y")));
            $date = explode('-',$date);
            return array('day' => $date[0], 'month' => $date[1], 'year' => $date[2]);
        }
        if ($action == "-") {
            $date = date("j-n-Y",mktime(0, 0, 0, date("m"),  date("d")-$value,  date("y")));
            $date = explode('-',$date);
            return array('day' => $date[0], 'month' => $date[1], 'year' => $date[2]);
        }
    }
}
function date_difference($start,$end,$minute = 0) {
    if (!isset($start['year'])) $start['year'] = 0;
    if (!isset($start['month'])) $start['month'] = 0;
    if (!isset($start['day'])) $start['day'] = 0;
    if (!isset($start['hour'])) $start['hour'] = 0;
    if (!isset($start['minutes'])) $start['minutes'] = 0;
    if (!isset($start['seconds'])) $start['seconds'] = 0;
    if (!isset($end['year'])) $end['year'] = 0;
    if (!isset($end['month'])) $end['month'] = 0;
    if (!isset($end['day'])) $end['day'] = 0;
    if (!isset($end['hour'])) $end['hour'] = 0;
    
    $year_difference = $end['year'] - $start['year'];
    
    if ($year_difference) {
        $month_difference = (12 - $start['month']) + ($end['month']);
        if ($month_difference > 12) {
            $month_difference = $month_difference - 12;
        } else {
            $year_difference--;
        }
    } else {
        $month_difference = $end['month'] - $start['month'];
    }
    if ($month_difference) {
        $day_difference = (30 - $start['day']) + ($end['day']);
        if ($day_difference > 30) {
            $day_difference = $day_difference - 30;
        } else {
            $month_difference--;
        }
    } else {
        $day_difference = $end['day'] - $start['day'];
    }
    if ($day_difference) {
        $hour_difference = (24 - $start['hour']) + ($end['hour']);
        if ($hour_difference > 24) {
            $hour_difference = $hour_difference - 24;
        } else {
            $day_difference--;
        }
    } else {
        $hour_difference = $end['hour'] - $start['hour'];
    }
    if (!$minute) {
        if ($start['minutes']) {
            $minute_difference = 60 - $start['minutes'];
            $hour_difference--;
        } else {
            $minute_difference = '';
        }
    } else {
        $minute_difference = $minute - $start['minutes'];
        if ($minute_difference < 0) {
            $minute_difference = $minute_difference + 60;
            $hour_difference--;
        }
        if ($minute_difference > 59) {
            $minute_difference = $minute_difference - 60;
            $hour_difference++;
        }
    }
    
    $msg = '';
    
    if ($year_difference) {
        if ($year_difference == 1) {
            $msg .= T_('1 year, ');
        } else {
            $msg .= T_($year_difference.' years, ');
        }
    }
    if ($month_difference) {
        if ($month_difference == 1) {
            $msg .= T_('1 month, ');
        } else {
            $msg .= T_($month_difference.' months, ');
        }
    }
    if ($day_difference) {
        if ($day_difference == 1) {
            $msg .= T_('1 day, ');
        } else {
            $msg .= T_($day_difference.' days, ');
        }
    }
    if ($hour_difference) {
        if ($hour_difference == 1) {
            $msg .= T_('1 hour, ');
        } else {
            $msg .= T_($hour_difference.' hours, ');
        }
    }
    if ($minute_difference) {
        if ($minute_difference == 1) {
            $msg .= T_('1 minute');
        } else {
            $msg .= T_($minute_difference.' minutes');
        }
    } else {
//        $msg .= 'no minutes';
    }
    $msg = rtrim($msg,', ');
    return $msg;
}
function return_month($month) {
    switch ($month) {
    case "01":
        return T_('January');
    case "02":
        return T_('Febuaury');
    case "03":
        return T_('March');
    case "04":
        return T_('April');
    case "05":
        return T_('May');
    case "06":
        return T_('June');
    case "07":
        return T_('July');
    case "08":
        return T_('August');
    case "09":
        return T_('September');
    case "10":
        return T_('October');
    case "11":
        return T_('November');
    case "12":
        return T_('December');
    }
}
function return_day($day) {
    if ($day{1} == 0) {
        return substr($day,1);
    } else {
        return $day;
    }
}
function return_minute($min) {
    if (($min > 0 and $min < 10) and (strlen($min) < 2)) return '0'.$min;
    if (!$min) return '00';
    return $min;

}

function append_url($url = '') {
    if (!user_type()) {
        return '';
    } else {
        if (ENABLE_URL_SESSION_IDS) {
            if (isset($_COOKIE['SID'])) {
                return '';
            } else {
                if ($url) {
                    if (strpos($url,'?') or $url == 1) {
                        return '&'.strip_tags(SID);
                    }
                }
                return '?'.strip_tags(SID);
            }
        }
        return '';
    }
}
function stripslashes_deep($value) {
    $value = is_array($value) ? array_map('stripslashes_deep', $value) : (isset($value) ? stripslashes($value) : null);
    return $value;
}
function obscure_email_addresses($input) { // Replaces e-mail links with user defined URL patterns and insert JavaScript reference
    $parsed = preg_replace("/[\"\']mailto:([A-Za-z0-9._%-]+)\@([A-Za-z0-9._%-]+)\.([A-Za-z.]{2,4})[\"\'\?]/e", "'\"/contact/'.str_rot13('\\1').'+'.str_rot13('\\2').'+'.str_rot13('\\3').'\" rel=\"nofollow\" '", $input);
    $parsed = preg_replace("/([A-Za-z0-9._%-]+)\@/e", "substr('\\1',0,-3).'...&#64;'", $parsed); // To be sure, truncate e-mail addresses that are *not* linked (bill.ga...@microsoft.com)
    return $parsed;
}

function decode_email_addresses() {
    $z = 'window.onload = function () {
    geo();
}

function geo() {
    if (!document.getElementsByTagName)
            return false;
    var map = rot13init(); 
    var links = document.getElementsByTagName(\'a\');
    function geo_decode(anchor) {
        var href = anchor.getAttribute(\'href\');
        var address = href.replace(/.*contact\/([a-z0-9._%-]+)\+([a-z0-9._%-]+)\+([a-z.]+)/i, \'$1\' + \'@\' + \'$2\' + \'.\' + \'$3\');
        var linktext = anchor.innerHTML;
        if (href != address) {
            anchor.setAttribute(\'href\',\'mailto:\' + (rot13 ? str_rot13(address,map) : address));
            anchor.innerHTML = linktext;
        }
    }
    for (var l = 0 ; l < links.length ; l++) {
        links[l].onclick = function() {
            geo_decode(this);
        }
        links[l].onmouseover = function() {
            geo_decode(this);
        }
    }
}

var rot13 = 1;

function rot13init() {
    var map = new Array();
    var s = "abcdefghijklmnopqrstuvwxyz";
    for (var i = 0 ; i < s.length ; i++)
        map[s.charAt(i)] = s.charAt((i+13)%26);
    for (var i = 0 ; i < s.length ; i++)
        map[s.charAt(i).toUpperCase()] = s.charAt((i+13)%26).toUpperCase();
    return map;
}

function str_rot13(a,map) {
    var s = "";
    for (var i = 0 ; i < a.length ; i++) {
        var b = a.charAt(i);
        s += (b>=\'A\' && b<=\'Z\' || b>=\'a\' && b<=\'z\' ? map[b] : b);
    }
    return s;
}';
    return $z;
} 
function indent_variable($i,$var,$eo = true) {

    $z = '';
    $exploder = '';
    if (strpos($var,"\r\n")) {
        $exploder = "\r\n";
    } elseif (strpos($var,"\n")) {
        $exploder = "\n";
    } elseif (strpos($var,"\r")) {
        $exploder = "\r";
    }
    if ($exploder) {
        $var_array = explode($exploder,$var);
        foreach ($var_array as $line) {
            $z .= $i.trim($line)."<br />\n";
        }
    } else {
        $z = $i.$var."\n";
    }
    if (!user_type() and $eo) {
        $z_compare = obscure_email_addresses($z);
        if ($z_compare != $z) {
            if (!strpos(' '.$GLOBALS['javascript'],'function geo')) {
                $GLOBALS['javascript'] .= decode_email_addresses()."\n";
            }
        }
        $z = $z_compare;
    }

    $z = rtrim($z);
    return $z."\n";
} 
function open_text_file($i,$file) {
    $z = "";
    $array = file(PATH."files/".$file);
    foreach ($array as $l) {
        $z .= $i.trim($l)."<br />\n";
    }
    return $z;
}
function remove_slashes($string) {
    if (!get_magic_quotes_gpc()) {
        return $string;
    } else {
        if (is_array($string)) {
            foreach ($string as $k=>$v) if (is_string($v)) $string[$k] = stripslashes(trim($v));
            return $string;
        } else {
            $string = trim($string);
            return stripslashes($string);
        }
    }
}

function user_type() {
    if (isset($_SESSION["member_type"]) and !empty($_SESSION["member_type"])) {
        return $_SESSION["member_type"];
    } else {
        return 0;
    }
}
function highlight($x,$var) {//$x is the string, $var is the text to be highlighted
    $var = explode(" ",$var);
    for($j=0; $j< count($var); $j++){    
        $xtemp = "";
        $i=0;
        while($i<strlen($x)){
            if((($i + strlen($var[$j])) <= strlen($x)) && (strcasecmp($var[$j], substr($x, $i, strlen($var[$j]))) == 0)) {
                $xtemp .= "<b>" . substr($x, $i , strlen($var[$j])) . "</b>";
                $i += strlen($var[$j]);
            } else {
                $xtemp .= $x{$i};
                $i++;
            }
        }
        $x = $xtemp;
    }
    return $x;
} 
function search_terms($keyword) {
    $keyword = trim($keyword);
    $keyword = preg_replace("/[^\"0-9a-z ]/",'',$keyword);
    $keyword = eregi_replace(" +", ' ', $keyword);

    if (!strpos($keyword,' ')) {
        return array( 0 => $keyword );
    }
    $matches = array();
    if (strpos(' '.$keyword,'"')) {
        $pattern = '|\"[^>]+\"|U';
        preg_match_all($pattern, $keyword, $matches);
        $num_matches = count($matches[0]);
        if ($num_matches) {
            foreach($matches[0] as $phrase_match) {
                $keyword = str_replace($phrase_match,'',$keyword);
            }    
        } else {
            $keyword = str_replace('"','',$keyword);
        }
    }
    
    $keyword = trim($keyword);
    $keyword = eregi_replace(" +", ' ', $keyword);
    
    $search_terms = array();
    
    if ($keyword) {
        if (strpos($keyword,' ')) {
            $words = explode(' ',$keyword);
            $num_words = count($words);
            for($w=0;$w<$num_words;$w++) {
                if (strlen($words[$w]) > 3) {
                    $search_terms[$w] = $words[$w];
                }
            }
        } else {
            $search_terms[0] = $keyword;
        }
        if (isset($num_matches)) {
            if ($num_matches) {
                $num_search_terms = count($search_terms);
                for($w=$num_search_terms;$w<($num_search_terms + $num_matches);$w++) {
                    $search_terms[$w] = trim($matches[0][$w - $num_search_terms],'"');
                }
            }
        }
    } else {
        if ($num_matches) {
            for($w=0;$w<$num_matches;$w++) {
                $search_terms[$w] = trim($matches[0][$w - $num_search_terms],'"');
            }
        }
    
    }
    return array_map("mysql_real_escape_string", $search_terms);
}
function get_rnd_iv($iv_len) {
    $iv = '';
    while ($iv_len-- > 0) {
        $iv .= chr(mt_rand() & 0xff);
    }
}
function md5_encrypt($plain_text, $password, $iv_len = 16) {
    $plain_text .= "\x13";
    $n = strlen($plain_text);
    if ($n % 16) $plain_text .= str_repeat("\0", 16 - ($n % 16));
    $i = 0;
    $enc_text = get_rnd_iv($iv_len);
    $iv = substr($password ^ $enc_text, 0, 512);
    while ($i < $n) {
        $block = substr($plain_text, $i, 16) ^ pack('H*', md5($iv));
        $enc_text .= $block;
        $iv = substr($block . $iv, 0, 512) ^ $password;
        $i += 16;
    }
    return base64_encode($enc_text);
}

function md5_decrypt($enc_text, $password, $iv_len = 16) {
    $enc_text = base64_decode($enc_text);
    $n = strlen($enc_text);
    $i = $iv_len;
    $plain_text = '';
    $iv = substr($password ^ substr($enc_text, 0, $iv_len), 0, 512);
    while ($i < $n) {
        $block = substr($enc_text, $i, 16);
        $plain_text .= $block ^ pack('H*', md5($iv));
        $iv = substr($block . $iv, 0, 512) ^ $password;
        $i += 16;
    }
    return preg_replace('/\\x13\\x00*$/', '', $plain_text);
}
    
function valid_XHTML ($text) {
    /***************************************************************************
    *    Original Concept by RMCox (rmcox@artypapers.com)
    *    Please Visit: http://www.artypapers.com/
    *    If any drastic changes are made, please send a 
    *    copy to RMCox (rmcox@artypapers.com).
    ****************************************************************************/
    if (!VALIDATE_XHTML) {
        return '';
    }
    
    if (substr($text, 0, 1) == '<') {
        $text = 'x'.$text;
    }
    $left_bracket_array = explode("<", $text);
    if (count($left_bracket_array) > 1) {
        $all_tags = array();
        $begin_nesting_store = array();
        foreach ($left_bracket_array as $key => $lb_text) {
            $right_bracket_array = explode(">", $lb_text);
            $tag_only_array = explode(" ", $right_bracket_array[0]);
            $ending_tag_array = explode("/", $tag_only_array[0]);
            $b_tag_check = strtolower($tag_only_array[0]);
            $e_tag_check = strtolower($ending_tag_array[1]);
            if ($b_tag_check != $tag_only_array[0] && ($right_bracket_array[1] || $ending_tag_array[1])) {
                $HTML_report .= T_('<li class="error"><strong>['.$tag_only_array[0].']</strong> must be lower case.</li>');
                $tag_only_array[0] = $b_tag_check;
            }
            if ($b_tag_check == 'a' && strtolower(substr($text, 0, 1)) != 'a') {
                $quo_check = explode("\"", $tag_only_array[1]);
                if (count($quo_check) != 3) {
                    $HTML_report .= T_('<li class="error">Check your <strong>[a]</strong> tags. <em>TIP: Possible href missing quotes and/or misplaced spaces</em></li>'); 
                } else if ($tag_only_array[2] || substr($tag_only_array[1], 0, 5) != 'href=') {
                    $HTML_report .= T_('<li class="error">Only the href attribute (in lowercase) is available for <strong>[a]</strong> tags</li>'); 
                }
            }
            $ending_tag_array[1] = $e_tag_check;
            if($tag_only_array[0] && !$ending_tag_array[1]){
                $tag_to_pass = $tag_only_array[0];
                $begin_nesting_store[$Count_BNS] = $tag_to_pass;
                $Count_BNS++;
                $all_tags[$x] = $tag_to_pass;
            }
            if($ending_tag_array[1]){
                $all_tags[$x] = "/".$ending_tag_array[1];
                $Count_BNS--;
                if(($ending_tag_array[1] != $begin_nesting_store[$Count_BNS]) || (!$ending_tag_array[1] && $begin_nesting_store[$Count_BNS]) || ($ending_tag_array[1] && !$begin_nesting_store[$Count_BNS])){
                    if($ending_tag_array[1] && $begin_nesting_store[$Count_BNS]){
                         $HTML_report .= T_('<li class="error">Check your <strong>['.$ending_tag_array[1].']</strong> tags and your <strong>['.$begin_nesting_store[$Count_BNS].']</strong> tags. <em>TIP: Possible Improper Nesting or Missing Tag</em></li>');
                    }
                    if($ending_tag_array[1] && !$begin_nesting_store[$Count_BNS]){
                         $HTML_report .= T_('<li class="error">Check your <strong>['.$ending_tag_array[1].']</strong> tags. <em>TIP: Possible Non-Beginning Tag</em></li>');
                    }
                    if(!$ending_tag_array[1] && $begin_nesting_store[$Count_BNS]){
                         $HTML_report .= T_('<li class="error">Check your <strong>['.$begin_nesting_store[$Count_BNS].']</strong> tags. <em>TIP: Possible Non-Ending Tag</em></li>');
                    }
                }
                $begin_nesting_store[$Count_BNS] = "";
            }
        }// end of FOR loop
        $Count_BNS2 = count($begin_nesting_store);
        for($e = 0; $e <= $Count_BNS2; $e++){
             $last_BNS_check = $begin_nesting_store[$e];
             if($last_BNS_check && !is_null($last_BNS_check)){
                 $HTML_report .= T_('<li class="error">Check your <strong>['.$last_BNS_check.']</strong> tags. <em>TIP: Possible Non-Ending Tag</em></li>');
             }
        }
        if ($HTML_report) {
            $HTML_report = T_('Invalid Code » Please check for the following:').'\n\n<div class="indent"><ul>'.$HTML_report.'</ul></div>';
        }
        return $HTML_report;
    } else {
        return null;
    }
}


?>