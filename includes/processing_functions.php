<?



// ******* Action Functions *******

function begin($i,$database_host,$database_name,$database_user,$database_password) {
	// these classes are needed by all pages, others are created as needed
	global $doc_root;
	require_once($doc_root.'includes/classes/style.class.php');
	require_once($doc_root.'includes/classes/mysql.class.php');
	require_once($doc_root.'includes/classes/links.class.php');
	require_once($doc_root.'includes/classes/member.class.php');
	require_once($doc_root.'includes/classes/images.class.php');
	require_once($doc_root.'includes/classes/site.class.php');
	
	global $site;
	global $style;
	global $mysql;
	global $user;
	global $links;
	global $image;
	global $date;
	
	$site = new site;
	$site->open_db($database_host,$database_name,$database_user,$database_password);
	if (!$site->db_open) {
		$z = "$i<h1>Database Error</h1><br />\n";
		$z .= "$i".$site->error;
		$_GET["page_type"] = 0;
		$_GET["page_id"] = 0;
		return $z;
	} else {
		$site->build_constants();
	}
	$date['day'] = date('j',mktime(date('H')+HOUR_OFFSET, 0, 0, date('m'), date('d'), date('y')));
	$date['month'] = date('n',mktime(date('H')+HOUR_OFFSET, 0, 0, date('m'), date('d'), date('y')));
	$date['year'] = date('Y',mktime(date('H')+HOUR_OFFSET, 0, 0, date('m'), date('d'), date('y')));
	$date['hour'] = date('G',mktime(date('H')+HOUR_OFFSET, 0, 0, date('m'), date('d'), date('y')));
	$date['minutes'] = date('i');
	$date['seconds'] = date('s');
	$date['seconds'] = ltrim($date['seconds'],'0');

	$mysql = new mysql;
	$links = new links;
	$style = new style;
	$user = new member;
	if (ENABLE_IMAGES) $image = new images;
	
	// Logout -> remove cookie, no session
	if (isset($_GET['logout'])) {
		if ($_GET['logout'] == 1) {
			if (!empty($_COOKIE['SID'])) {
				setcookie('SID','',time()-42000,"/");
			}
			$style->exists(1);
			$GLOBALS['styles'] = $style->style_header();
			header ("Location: ".URL);
			return '';
		}
	}
	// check for cookies
	if (strpos($_SERVER["REQUEST_URI"],'cookie_test=1')) {
		$split_url = explode('?',$_SERVER["REQUEST_URI"]);
		$url = $split_url[0];
		if (append_url()) {
			if(!ENABLE_URL_SESSION_IDS) {
				$z = "$i<h1>Login Failed</h1><br />\n";
				$z .= $i."Cookies are required to access the ".MEMBER_NAME_SINGULAR."'s area.<br />Please check your settings.";
				$_GET["page_type"] = 0;
				$_GET["page_id"] = 0;
				return $z;
			} else {
				header ("Location: ".$url.append_url());
			}
		} else {
			header ("Location: ".$url);
		}
	}
	// Login or rebuild the session
	if (isset($_POST["login"])) {
		if (empty($_POST["login_id"])) {
			$z = "$i<h1>Login Failed</h1><br />\n";
			$z .= $i."You did not provide ".a(MEMBERS_NAME_SINGULAR)." ".ucwords(MEMBERS_NAME_SINGULAR)." ID";
			$_GET["page_type"] = 0;
			$_GET["page_id"] = 0;
			$style->exists(1);
			$GLOBALS['styles'] = $style->style_header();
			return $z;
		}
		
		if (!$user->login()) {
			$z = "$i<h1>Login Failed</h1><br />\n";
			$z .= "$i".$user->error_message;
			$_GET["page_type"] = 0;
			$_GET["page_id"] = 0;
			$style->exists(1);
			$GLOBALS['styles'] = $style->style_header();
			return $z;
		} else {
			$user->set_last_login();
			if (strpos($_SERVER["REQUEST_URI"],'?')) {
				header ("Location: ".$_SERVER["REQUEST_URI"].'&cookie_test=1'.append_url());
			} else {
				header ("Location: ".$_SERVER["REQUEST_URI"].'?cookie_test=1'.append_url(1));
			}
		}
	} elseif (isset($_COOKIE["SID"])) {
		$lets_session_name = session_name('SID');
		session_id($_COOKIE["SID"]);
		session_start();
		header("Cache-Control: private");
		if (!empty($_SESSION["member_id"]) and $user->build_dataset($_SESSION['member_id'])) {
			if (TIME_OUT) {
				if (!time_left($GLOBALS['date'],array('day' => $user->ll_day, 'month' => $user->ll_month, 'year' => $user->ll_year, 'hour' => $user->ll_hour),TIME_OUT)) {
					if (!empty($_COOKIE["SID"])) {
						setcookie("SID","",time()-42000,"/");
					}
					session_destroy();
					$style->exists(1);
					$GLOBALS['styles'] = $style->style_header();
					$_GET['page_type'] = 0;
					$_GET['page_id'] = 0;
					$_SESSION['member_type'] = 0;
					return "$i<h1>Your session has timed out, please login again.</h1><br />\n";
				}
			}			
			$_SESSION['member_validated'] = $user->validated;
			$_SESSION['member_suspended'] = $user->suspended;
			$_SESSION['member_suspended_message'] = $user->suspended_message;
			if ($style->exists($_SESSION["member_id"])) {
				$GLOBALS['styles'] = $style->style_header();
			} else {
				$style->exists(1);
				$GLOBALS['styles'] = $style->style_header();
			}
			
			if (SUSPEND_ON_EXPIRY and !$user->suspended and $user->type != 2) {
				if (($user->expiry_year < $GLOBALS['date']['year']) or ($user->expiry_year == $GLOBALS['date']['year'] and $user->expiry_month < $GLOBALS['date']['month']) or ($user->expiry_year == $GLOBALS['date']['year'] and $user->expiry_month == $GLOBALS['date']['month'] and $user->expiry_day < $GLOBALS['date']['day'])) {
					$user->suspend($_SESSION['member_id']);
					$_SESSION['member_suspended'] = $user->suspended;
					$_SESSION['member_suspended_message'] = $user->suspended_message;
				}			
			}
		} else {
			$style->exists(1);
			$GLOBALS['styles'] = $style->style_header();
		}
		return "";
	} elseif (strpos($_SERVER["REQUEST_URI"],"SID=") and ENABLE_URL_SESSION_IDS) {
		$uri = explode("SID=",$_SERVER["REQUEST_URI"]);
		$lets_session_name = session_name('SID');
		session_id($uri[1]);
		session_start();
		header("Cache-Control: private");
		if (!empty($_SESSION["member_id"]) and $user->build_dataset($_SESSION['member_id'])) {
			if (TIME_OUT) {
				if (!time_left($GLOBALS['date'],array('day' => $user->ll_day, 'month' => $user->ll_month, 'year' => $user->ll_year, 'hour' => $user->ll_hour),TIME_OUT)) {
					session_destroy();
					$style->exists(1);
					$GLOBALS['styles'] = $style->style_header();
					$_GET['page_type'] = 0;
					$_GET['page_id'] = 0;
					$_SESSION['member_type'] = 0;
					return "$i<h1>Your session has timed out, please login again.</h1><br />\n";
				}
			}			
			$_SESSION['member_validated'] = $user->validated;
			$_SESSION['member_suspended'] = $user->suspended;
			$_SESSION['member_suspended_message'] = $user->suspended_message;
			if ($style->exists($_SESSION["member_id"])) {
				$GLOBALS['styles'] = $style->style_header();
			} else {
				$style->exists(1);
				$GLOBALS['styles'] = $style->style_header();
			}
			if (SUSPEND_ON_EXPIRY and !$user->suspended and $user->type != 2) {
				if (($user->expiry_year < $GLOBALS['date']['year']) or ($user->expiry_year == $GLOBALS['date']['year'] and $user->expiry_month < $GLOBALS['date']['month']) or ($user->expiry_year == $GLOBALS['date']['year'] and $user->expiry_month == $GLOBALS['date']['month'] and $user->expiry_day < $GLOBALS['date']['day'])) {
					$user->suspend($_SESSION['member_id']);
					$_SESSION['member_suspended'] = $user->suspended;
					$_SESSION['member_suspended_message'] = $user->suspended_message;
				}			
			}
		} else {
			$style->exists(1);
			$GLOBALS['styles'] = $style->style_header();
		}
		return "";
	} else {
		$style->exists(1);
		$GLOBALS['styles'] = $style->style_header();
	}
}

function process_transaction($noticeboard_id = 0) {
	global $user;
	global $transactions;
	if (!isset($_POST['submit'])) $_POST['submit'] = '';
	if ($_POST['submit'] == 'Submit '.ucwords(TRANSACTION_NAME_SINGULAR) ) {
		if (user_type() == 1) {
			if ($transactions->buy("",$_SESSION['member_id'],$_POST['seller_id'],$_POST['transaction_amount'],$_POST['transaction_description'],1,$noticeboard_id)) {
				if ($transactions->balance($_SESSION['member_id'])) {
					$user->update_balance($transactions->balance,$_SESSION['member_id']);
				} else {
					return ucfirst(TRANSACTION_NAME_SINGULAR).' successfull but balance not updated.';
				}
				if ($transactions->balance($_POST['seller_id'])) {
					$user->update_balance($transactions->balance,$_POST['seller_id']);
				} else {
					return ucfirst(TRANSACTION_NAME_SINGULAR).' successfull but balance not updated.';
				}
				if ($transactions->balance(1)) {
					$user->update_balance($transactions->balance,1);
				} else {
					return ucfirst(TRANSACTION_NAME_SINGULAR).' successfull but balance not updated.';
				}
				return ucfirst(TRANSACTION_NAME_SINGULAR).' # '.$transactions->id.' was recorded:<br />'.$user->full_name($_SESSION['member_id']).' paid '.$user->full_name($_POST['seller_id']).' $'.$transactions->amount.' '.CURRENCY_NAME.' for the following:<br />'.$transactions->description."\n";
			} else {
				return ucfirst(TRANSACTION_NAME_SINGULAR).' not completed.';
			}
		} elseif (user_type() == 2) {
			if (isset($_POST['buyer_id'])) $buyer_id = $_POST['buyer_id']; else $buyer_id = $_SESSION['member_id'];
			if ($transactions->buy("",$buyer_id,$_POST['seller_id'],$_POST['transaction_amount'],$_POST['transaction_description'],1,$noticeboard_id)) {
				if ($transactions->balance($buyer_id)) {
					$user->update_balance($transactions->balance,$buyer_id);
				} else {
					return ucfirst(TRANSACTION_NAME_SINGULAR).' successfull but balance not updated.';
				}
				if ($transactions->balance($_POST['seller_id'])) {
					$user->update_balance($transactions->balance,$_POST['seller_id']);
				} else {
					return ucfirst(TRANSACTION_NAME_SINGULAR).' successfull but balance not updated.';
				}
				if ($transactions->balance(1)) {
					$user->update_balance($transactions->balance,1);
				} else {
					return ucfirst(TRANSACTION_NAME_SINGULAR).' successfull but balance not updated.';
				}
				return ucfirst(TRANSACTION_NAME_SINGULAR).' # '.$transactions->id.' was recorded:<br />'.$user->full_name($buyer_id).' paid '.$user->full_name($_POST['seller_id']).' $'.$transactions->amount.' '.CURRENCY_NAME.' for the following:<br />'.$transactions->description;
			} else {
				return ucfirst(TRANSACTION_NAME_SINGULAR).' not completed.';
			}
		}
	}
	return '';
}






?>