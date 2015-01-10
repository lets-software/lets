<?php
class site {

	var	$db_open,
		$error;

	function site() {
		if (strpos(' '.PHP_OS, 'WIN')) {
			define("CURRENT_OS",'WIN');
		} else {
			define("CURRENT_OS",'UNIX');
		}
		$this->db_open = false;
		$this->error = "No errors";
	}

	function is_open() {
		return $this->db_open;
	}
	
	function open_db($host,$dbName,$user,$password) {
		$database_link = @mysql_connect("$host","$user","$password") or die('<strong>'.T_('Cannot connect to the database!!').'</strong><br /><br />'.T_('Please setup a MySQL database then enter the login information in the file: <strong>includes/dbconfig.php</strong>'));
		if (!$database_link) {
			$this->db_open = false;
			$this->error = T_("Could not connect to $host ");
		} else {
			$this->error = T_("Connected to $host ");
			if (!mysql_select_db("$dbName")) {
				$this->db_open = false;
				$this->error .= T_("and could not connect to $dbName.");
			} else {
				$this->error .= T_("and connected to $dbName");
				$this->db_open = true;
			}
		}
	}
	function build_constants() {
		$mysql = new mysql;
		$mysql->result('SELECT * FROM config');
		if ($mysql->result['template']) {
			define('TEMPLATE',$mysql->result['template']);
		} else {
			define('TEMPLATE','default');
		}
		define('SITE_KEY',												$mysql->result['site_key']);
		define('PATH',													$mysql->result['path']);
		define('URL',													$mysql->result['url']);
		define('CURRENCY_NAME',											$mysql->result['currency_name']);
		define('CURRENCY_NAME_SINGULAR',								$mysql->result['currency_name_singular']);
		define('ENABLE_URL_SESSION_IDS',								$mysql->result['enable_url_session_ids']);
		define('ADMIN_EMAIL',											$mysql->result['admin_email']);
		define('HOUR_OFFSET',											$mysql->result['hour_offset']);
		define('SITE_NAME',												$mysql->result['site_name']);
		define('ENABLE_AUCTIONS',										$mysql->result['enable_auctions']);
		define('FREEZE_AUCTION_AFTER_BID',								$mysql->result['freeze_auction_after_bid']);
		define('PREVENT_EDIT_AFTER_TRANSACTION',						$mysql->result['prevent_edit_after_transaction']);
		define('PREVENT_DELETION_AFTER_TRANSACTION',					$mysql->result['prevent_deletion_after_transaction']);
		define('LOCK_BUY_NOW_PRICE',									$mysql->result['lock_buy_now_price']);
		define('VISITOR_MESSAGE',										$mysql->result['visitor_message']);
		define('MEMBER_MESSAGE',										$mysql->result['member_message']);
		define('SETUP_FEE',												$mysql->result['setup_fee']);
		define('ENABLE_IMAGES',											$mysql->result['enable_images']);
		define('ENABLE_INSTANT_BUY',									$mysql->result['enable_instant_buy']);
		define('ENABLE_TRANSACTION_SERVICE_FEE',						$mysql->result['enable_transaction_service_fee']);
		define('TRANSACTION_SERVICE_FEE_BUYER',							$mysql->result['transaction_service_fee_buyer']);
		define('TRANSACTION_SERVICE_FEE_SELLER',						$mysql->result['transaction_service_fee_seller']);
		define('IMAGE_QUALITY',											$mysql->result['image_quality']);
		define('IMAGE_WIDTH_THUMB_NOTICEBOARD',							$mysql->result['image_width_thumb_noticeboard']);
		define('IMAGE_HEIGHT_THUMB_NOTICEBOARD',						$mysql->result['image_height_thumb_noticeboard']);
		define('IMAGE_WIDTH_PAGE_NOTICEBOARD',							$mysql->result['image_width_page_noticeboard']);
		define('IMAGE_HEIGHT_PAGE_NOTICEBOARD',							$mysql->result['image_height_page_noticeboard']);
		define('IMAGE_WIDTH_THUMB_ARTICLE',								$mysql->result['image_width_thumb_article']);
		define('IMAGE_HEIGHT_THUMB_ARTICLE',							$mysql->result['image_height_thumb_article']);
		define('IMAGE_WIDTH_PAGE_ARTICLE',								$mysql->result['image_width_page_article']);
		define('IMAGE_HEIGHT_PAGE_ARTICLE',								$mysql->result['image_height_page_article']);
		define('IMAGE_WIDTH_THUMB_MEMBER',								$mysql->result['image_width_thumb_member']);
		define('IMAGE_HEIGHT_THUMB_MEMBER',								$mysql->result['image_height_thumb_member']);
		define('IMAGE_WIDTH_PAGE_MEMBER',								$mysql->result['image_width_page_member']);
		define('IMAGE_HEIGHT_PAGE_MEMBER',								$mysql->result['image_height_page_member']);
		define('ENABLE_COMMENTS',										$mysql->result['enable_comments']);
		define('LOCATION',												$mysql->result['location']);
		define('VALIDATE_ARTICLES',										$mysql->result['validate_articles']);
		define('VALIDATE_EVENTS',										$mysql->result['validate_events']);
		define('VALIDATE_FAQ',											$mysql->result['validate_faq']);
		define('VALIDATE_LINKS',										$mysql->result['validate_links']);
		define('VALIDATE_MEMBERS',										$mysql->result['validate_members']);
		define('ENABLE_RSS',											$mysql->result['enable_rss']);
		define('VALIDATE_XHTML',										$mysql->result['validate_xhtml']);
		define('SHOW_COMMENT_EDITED',									$mysql->result['show_comment_edited']);
		define('ALLOW_COMMENT_DELETION',								$mysql->result['allow_comment_deletion']);
		define('TWELVE_HOUR_CLOCK',										$mysql->result['twelve_hour_clock']);
		define('SHOW_FAQ_DETAILS',										$mysql->result['show_faq_details']);
		define('SHOW_LINK_DETAILS',										$mysql->result['show_link_details']);
		define('NEGATIVE_BALANCE_LIMIT',								$mysql->result['negative_balance_limit']);
		define('COMMENT_MEMBER_IMAGES',									$mysql->result['comment_member_images']);
		define('COMMENT_NAME',											$mysql->result['comment_name']);
		define('COMMENT_NAME_SINGULAR',									$mysql->result['comment_name_singular']);
		define('COMMENT_NAME_PLURAL',									$mysql->result['comment_name_plural']);
		define('TRANSACTION_NAME_SINGULAR',								$mysql->result['transaction_name_singular']);
		define('TRANSACTION_NAME_PLURAL',								$mysql->result['transaction_name_plural']);
		define('CANADIAN',												$mysql->result['canadian']);
		define('ALLOW_VIEW_OTHER_TRANSACTION_HISTORY',					$mysql->result['allow_view_other_transaction_history']);
		define('SUSPEND_ON_EXPIRY',										$mysql->result['suspend_on_expiry']);
		define('TIME_OUT',												$mysql->result['time_out']);
		define('RESTRICT_UPDOWN_LINKS',									$mysql->result['restrict_updown_links']);
		define('MEMBER_EXPIRY_HIDDEN',									$mysql->result['member_expiry_hidden']);
		define('BULK_TRADING_CONFIRM',									$mysql->result['bulk_trading_confirm']);
		define('DEFAULT_BULK_TRANSACTION_DESCRIP',						$mysql->result['default_bulk_transaction_descrip']);
		define('ALLOW_MEMBER_ADMIN_CATEGORIES',							$mysql->result['allow_member_admin_categories']);
		define('ENABLE_GUEST_COMMENTS',									$mysql->result['enable_guest_comments']);
		define('ENABLE_NOTICEBOARD',									$mysql->result['enable_noticeboard']);
		define('ENABLE_ARTICLES',										$mysql->result['enable_articles']);
		define('ENABLE_EVENTS',											$mysql->result['enable_events']);
		define('ENABLE_FAQ',											$mysql->result['enable_faq']);
		define('ENABLE_LINKS',											$mysql->result['enable_links']);
		define('ENABLE_MEMBER_LIST',									$mysql->result['enable_member_list']);
		define('ENABLE_MEMBER_EDIT_COLOURS',							$mysql->result['enable_member_edit_colours']);
		define('ENABLE_LOG',											$mysql->result['enable_log']);
		define('ENABLE_ERROR_LOG',										$mysql->result['enable_error_log']);
		define('LOG_ADDITIONS',											$mysql->result['log_additions']);
		define('LOG_EDITS',												$mysql->result['log_edits']);
		define('LOG_DELETIONS',											$mysql->result['log_deletions']);
		define('LOG_TRIMMED_ERROR',										$mysql->result['log_trimmed_error']);
		define('LOG_NOTICEBOARD',										$mysql->result['log_noticeboard']);
		define('LOG_TRANSACTIONS',										$mysql->result['log_transactions']);
		define('LOG_ARTICLES',											$mysql->result['log_articles']);
		define('LOG_EVENTS',											$mysql->result['log_events']);
		define('LOG_FAQ',												$mysql->result['log_faq']);
		define('LOG_LINKS',												$mysql->result['log_links']);
		define('LOG_COMMENTS',											$mysql->result['log_comments']);
		define('LOG_POST_DUMP',											$mysql->result['log_post_dump']);
		define('ENABLE_EMAIL',											$mysql->result['enable_email']);
		define('USE_SMTP',												$mysql->result['use_smtp']);
		define('EMAIL_SMTP_HOST',										$mysql->result['email_smtp_host']);
		define('EMAIL_SMTP_HOST_BACKUP',								$mysql->result['email_smtp_host_backup']);
		define('SMTP_USER_NAME',										$mysql->result['smtp_user_name']);
		define('EMAIL_FROM_NAME',										$mysql->result['email_from_name']);
		define('UPDATE_EMAIL',											$mysql->result['update_email']);
		define('EMAIL_TECHNICAL_ERRORS',								$mysql->result['email_technical_errors']);
		define('EMAIL_VALIDATION_SUBMISSIONS',							$mysql->result['email_validation_submissions']);
		define('SMTP_PASSWORD',											md5_decrypt($mysql->result['smtp_password'],SITE_KEY));
		define('TECHNICAL_EMAIL',										$mysql->result['technical_email']);
		define('VALIDATION_EMAIL',										$mysql->result['validation_email']);
		define('ENABLE_EMAIL_CONTACT',									$mysql->result['enable_email_contact']);
		define('HOMEPAGE_HTML_ARTICLES',								$mysql->result['homepage_html_articles']);
		define('HOMEPAGE_HTML_NOTICEBOARD',								$mysql->result['homepage_html_noticeboard']);
		define('HOMEPAGE_HTML_EVENTS',									$mysql->result['homepage_html_events']);
		define('HOMEPAGE_HTML_FAQ',										$mysql->result['homepage_html_faq']);
		define('HOMEPAGE_HTML_LINKS',									$mysql->result['homepage_html_links']);
		define('PERSISTENT_HTML_SEARCH',								$mysql->result['persistant_html_search']);
		define('PERSISTENT_HTML_LOGIN',									$mysql->result['persistant_html_login']);
		define('PERSISTENT_HTML_NOTICEBOARD',							$mysql->result['persistant_html_noticeboard']);
		define('PERSISTENT_HTML_ARTICLES',								$mysql->result['persistant_html_articles']);
		define('PERSISTENT_HTML_EVENTS',								$mysql->result['persistant_html_events']);
		define('PERSISTENT_HTML_FAQ',									$mysql->result['persistant_html_faq']);
		define('PERSISTENT_HTML_LINKS',									$mysql->result['persistant_html_links']);
		define('SHOW_SEARCH_LINK',										$mysql->result['show_search_link']);
		define('SHOW_LOGIN_LINK',										$mysql->result['show_login_link']);
		define('SHOW_HELP_LINK',										$mysql->result['show_help_link']);
		define('FTP_HOST',												$mysql->result['ftp_host']);
		define('FTP_LOGIN',												$mysql->result['ftp_login']);
		define('ALLOW_DUPLICATE_EMAILS',								$mysql->result['allow_duplicate_emails']);
		define('REGISTER_TERMS',										$mysql->result['register_terms']);
		define('FTP_PASSWORD',											md5_decrypt($mysql->result['ftp_password'],SITE_KEY));
	}
	function return_config_html($i,$field) {
		$mysql = new mysql;
		if ($mysql->result('SELECT '.$field.' FROM config')) {
			return indent_variable($i,$mysql->result[$field]);
		}
	
	}
	function update_form_setting() {
		$mysql = new mysql;
		$query = 'UPDATE config SET ';
		if (isset($_POST['member_address_required'])) 				$query .= 'member_address_required = 1, '; 				else $query .= 'member_address_required = 0, ';
		if (isset($_POST['member_city_required'])) 					$query .= 'member_city_required = 1, '; 				else $query .= 'member_city_required = 0, ';
		if (isset($_POST['member_province_required'])) 				$query .= 'member_province_required = 1, '; 			else $query .= 'member_province_required = 0, ';
		if (isset($_POST['member_postal_code_required'])) 			$query .= 'member_postal_code_required = 1, '; 			else $query .= 'member_postal_code_required = 0, ';
		if (isset($_POST['member_neighborhood_required'])) 			$query .= 'member_neighborhood_required = 1, '; 		else $query .= 'member_neighborhood_required = 0, ';
		if (isset($_POST['member_home_phone_number_required'])) 	$query .= 'member_home_phone_number_required = 1, '; 	else $query .= 'member_home_phone_number_required = 0, ';
		if (isset($_POST['member_work_phone_number_required'])) 	$query .= 'member_work_phone_number_required = 1, '; 	else $query .= 'member_work_phone_number_required = 0, ';
		if (isset($_POST['member_mobile_phone_number_required'])) 	$query .= 'member_mobile_phone_number_required = 1, '; 	else $query .= 'member_mobile_phone_number_required = 0, ';
		if (isset($_POST['member_email_address_required'])) 		$query .= 'member_email_address_required = 1, '; 		else $query .= 'member_email_address_required = 0, ';
		if (isset($_POST['member_member_profile_required'])) 		$query .= 'member_member_profile_required = 1, '; 		else $query .= 'member_member_profile_required = 0, ';
		if (isset($_POST['member_url_required'])) 					$query .= 'member_url_required = 1, '; 					else $query .= 'member_url_required = 0, ';
		if (isset($_POST['noticeboard_title_required'])) 			$query .= 'noticeboard_title_required = 1, '; 			else $query .= 'noticeboard_title_required = 0, ';
		if (isset($_POST['noticeboard_blurb_required'])) 			$query .= 'noticeboard_blurb_required = 1, '; 			else $query .= 'noticeboard_blurb_required = 0, ';
		if (isset($_POST['noticeboard_description_required'])) 		$query .= 'noticeboard_description_required = 1, '; 	else $query .= 'noticeboard_description_required = 0, ';
		if (isset($_POST['noticeboard_amount_required'])) 			$query .= 'noticeboard_amount_required = 1, '; 			else $query .= 'noticeboard_amount_required = 0, ';
		if (isset($_POST['article_title_required'])) 				$query .= 'article_title_required = 1, '; 				else $query .= 'article_title_required = 0, ';
		if (isset($_POST['article_blurb_required'])) 				$query .= 'article_blurb_required = 1, '; 				else $query .= 'article_blurb_required = 0, ';
		if (isset($_POST['article_body_required'])) 				$query .= 'article_body_required = 1, '; 				else $query .= 'article_body_required = 0, ';
		if (isset($_POST['event_description_required'])) 			$query .= 'event_description_required = 1, '; 			else $query .= 'event_description_required = 0, ';
		if (isset($_POST['event_location_required'])) 				$query .= 'event_location_required = 1, '; 				else $query .= 'event_location_required = 0, ';
		if (isset($_POST['require_link_title'])) 					$query .= 'require_link_title = 1, '; 					else $query .= 'require_link_title = 0, ';
		if (isset($_POST['require_link_description'])) 				$query .= 'require_link_description = 1, '; 			else $query .= 'require_link_description = 0, ';
		if (isset($_POST['require_link_url'])) 						$query .= 'require_link_url = 1, '; 					else $query .= 'require_link_url = 0, ';
		if (isset($_POST['require_comment_title'])) 				$query .= 'require_comment_title = 1, '; 				else $query .= 'require_comment_title = 0, ';
		if (isset($_POST['require_comment_body'])) 					$query .= 'require_comment_body = 1, '; 				else $query .= 'require_comment_body = 0, ';
		if (isset($_POST['image_title_required'])) 					$query .= ' = 1, '; 									else $query .= 'image_title_required = 0, ';
		if (isset($_POST['image_blurb_required'])) 					$query .= 'image_blurb_required = 1, '; 				else $query .= 'image_blurb_required = 0, ';
		if (isset($_POST['image_description_required'])) 			$query .= 'image_description_required = 1 '; 			else $query .= 'image_description_required = 0 ';
		if ($mysql->query($query)) {
			return true;
		} else {
			return false;
		}
	}
	function form_settings_html($i,$url) {
		$mysql = new mysql;
		if (mysql_num_rows(mysql_query("SHOW TABLES LIKE 'config'")) == 0 && !$mysql->result('SELECT * FROM config LIMIT 1')) {
			return $mysql->error;
		}
		$z = $i."<!-- form_settings_form -->\n";
		$z .= $i."<div id=\"form_settings_form\">\n";
		$z .= $i.T_(" Checked fields will be required on their respected form. No submissions will be accepted without all these fields completed.<br />\n");
		$z .= $i.T_(" <strong>Note:</strong> It is recommended that title be required in all applicable elements (except images).<br /><br />\n");
		$z .= $i." <fieldset>\n";
		$z .= $i.' <form name="form_settings" method="post" action="'.URL.$url.append_url($url).'">'."\n";
		$z .= $i." <h3>".ucwords(MEMBERS_NAME_SINGULAR)." ".T_('Fields')."</h3>\n";
		$z .= $i.'  <label for="member_address_required">'.T_('Address').':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="member_address_required" name="member_address_required" value="1"'.set_checked($mysql->result['member_address_required']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="member_city_required">'.T_('City').':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="member_city_required" name="member_city_required" value="1"'.set_checked($mysql->result['member_city_required']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="member_province_required">'.T_('State/Province').':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="member_province_required" name="member_province_required" value="1"'.set_checked($mysql->result['member_province_required']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="member_postal_code_required">'.T_('Postal Code').':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="member_postal_code_required" name="member_postal_code_required" value="1"'.set_checked($mysql->result['member_postal_code_required']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="member_neighborhood_required">'.T_('Neighbourhood').':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="member_neighborhood_required" name="member_neighborhood_required" value="1"'.set_checked($mysql->result['member_neighborhood_required']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="member_home_phone_number_required">'.T_('Home Phone').':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="member_home_phone_number_required" name="member_home_phone_number_required" value="1"'.set_checked($mysql->result['member_home_phone_number_required']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="member_work_phone_number_required">'.T_('Work Phone').':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="member_work_phone_number_required" name="member_work_phone_number_required" value="1"'.set_checked($mysql->result['member_work_phone_number_required']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="member_mobile_phone_number_required">'.T_('Mobile Phone').':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="member_mobile_phone_number_required" name="member_mobile_phone_number_required" value="1"'.set_checked($mysql->result['member_mobile_phone_number_required']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="member_email_address_required">'.T_('Email').':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="member_email_address_required" name="member_email_address_required" value="1"'.set_checked($mysql->result['member_email_address_required']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="member_member_profile_required">'.ucwords(MEMBERS_NAME_SINGULAR).' '.T_('Profile').':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="member_member_profile_required" name="member_member_profile_required" value="1"'.set_checked($mysql->result['member_member_profile_required']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="member_url_required">'.T_('Website').':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="member_url_required" name="member_url_required" value="1"'.set_checked($mysql->result['member_url_required']).' /><br class="left" />'."\n";
		$z .= $i." <h3>".ucwords(NOTICEBOARD_NAME_SINGULAR)." ".T_('Fields')."</h3>\n";
		$z .= $i.'  <label for="noticeboard_title_required">'.T_('Title').':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="noticeboard_title_required" name="noticeboard_title_required" value="1"'.set_checked($mysql->result['noticeboard_title_required']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="noticeboard_blurb_required">'.T_('Short Description (used on list page)').':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="noticeboard_blurb_required" name="noticeboard_blurb_required" value="1"'.set_checked($mysql->result['noticeboard_blurb_required']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="noticeboard_description_required">'.T_('Long Description (used on item page)').':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="noticeboard_description_required" name="noticeboard_description_required" value="1"'.set_checked($mysql->result['noticeboard_description_required']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="noticeboard_amount_required">'.T_('Amount: (only applies to offers)').'</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="noticeboard_amount_required" name="noticeboard_amount_required" value="1"'.set_checked($mysql->result['noticeboard_amount_required']).' /><br class="left" />'."\n";
		$z .= $i." <h3>".ucwords(ARTICLES_NAME_SINGULAR).' '.T_('Fields')."</h3>\n";
		$z .= $i.'  <label for="article_title_required">'.T_('Title: (recommended)').'</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="article_title_required" name="article_title_required" value="1"'.set_checked($mysql->result['article_title_required']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="article_blurb_required">'.T_('Summary: (recommended)').'</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="article_blurb_required" name="article_blurb_required" value="1"'.set_checked($mysql->result['article_blurb_required']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="article_body_required">'.T_('Body: (recommended)').'</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="article_body_required" name="article_body_required" value="1"'.set_checked($mysql->result['article_body_required']).' /><br class="left" />'."\n";
		$z .= $i." <h3>".ucwords(EVENTS_NAME_SINGULAR).' '.T_('Fields')."</h3>\n";
		$z .= $i.'  <label for="event_description_required">'.T_('Description').':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="event_description_required" name="event_description_required" value="1"'.set_checked($mysql->result['event_description_required']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="event_location_required">'.T_('Location').':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="event_location_required" name="event_location_required" value="1"'.set_checked($mysql->result['event_location_required']).' /><br class="left" />'."\n";
		$z .= $i." <h3>".ucwords(LINKS_NAME_SINGULAR).' '.T_('Fields')."</h3>\n";
		$z .= $i.'  <label for="require_link_title">'.T_('Title').':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="require_link_title" name="require_link_title" value="1"'.set_checked($mysql->result['require_link_title']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="require_link_description">'.T_('Description').':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="require_link_description" name="require_link_description" value="1"'.set_checked($mysql->result['require_link_description']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="require_link_url">'.T_('URL').':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="require_link_url" name="require_link_url" value="1"'.set_checked($mysql->result['require_link_url']).' /><br class="left" />'."\n";
		$z .= $i." <h3>".ucwords(COMMENT_NAME_SINGULAR).' '.T_('Fields')."</h3>\n";
		$z .= $i.'  <label for="require_comment_title">'.T_('Title').':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="require_comment_title" name="require_comment_title" value="1"'.set_checked($mysql->result['require_comment_title']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="require_comment_body">'.T_('Body').':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="require_comment_body" name="require_comment_body" value="1"'.set_checked($mysql->result['require_comment_body']).' /><br class="left" />'."\n";
		$z .= $i.' <h3>'.T_('Image Fields')."</h3>\n";
		$z .= $i.'  <label for="image_title_required">'.T_('Title').':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="image_title_required" name="image_title_required" value="1"'.set_checked($mysql->result['image_title_required']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="image_blurb_required">'.T_('Short Description').'</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="image_blurb_required" name="image_blurb_required" value="1"'.set_checked($mysql->result['image_blurb_required']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="image_description_required">'.T_('Long Description').'</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="image_description_required" name="image_description_required" value="1"'.set_checked($mysql->result['image_description_required']).' /><br class="left" />'."\n";
		$z .= $i.'  <input class="form_settings_button" type="submit" name="submit" value="'.T_('Submit').'" />'."\n";
		$z .= $i.' </form>'."\n";
		$z .= $i." </fieldset>\n";
		$z .= $i.'</div>'."\n";
		$z .= $i."<!-- /form_settings_form -->\n";
		return $z;
	}
	function update_lets_settings() {
		$mysql = new mysql;
		$post_post = remove_slashes($_POST);
		
		if (!$post_post['currency_name'] or !$post_post['currency_name_singular'] or !$post_post['transaction_name_singular'] or !$post_post['transaction_name_plural'] or !$post_post['default_expiry_message']) {
			$this->error = T_('Required Field Missing');
			return false;
		}
		if (($post_post['transaction_service_fee_seller'] < 0 or !is_numeric($post_post['transaction_service_fee_seller'])) 
		or ($post_post['transaction_service_fee_seller'] < 0 or !is_numeric($post_post['transaction_service_fee_seller'])) or !is_numeric($post_post['setup_fee'])) {
			$this->error = T_('Problem with fee');
			return false;
		}
		if ($post_post['negative_balance_limit'] < 0) {
			$this->error = T_('Negative balance limit must be a positive number');
			return false;
		}
		if (!$post_post['dump_balance_accountID'] or !is_numeric($post_post['dump_balance_accountID'])) {
			$post_post['dump_balance_accountID'] = 1;
		}
		
		$query = 'UPDATE config SET ';
		if (isset($_POST['lock_buy_now_price'])) 						$query .= 'lock_buy_now_price = 1, '; 					else $query .= 'lock_buy_now_price = 0, ';
		if (isset($_POST['prevent_edit_after_transaction'])) 			$query .= 'prevent_edit_after_transaction = 1, '; 		else $query .= 'prevent_edit_after_transaction = 0, ';
		if (isset($_POST['prevent_deletion_after_transaction'])) 		$query .= 'prevent_deletion_after_transaction = 1, '; 	else $query .= 'prevent_deletion_after_transaction = 0, ';
		if (isset($_POST['allow_view_other_transaction_history'])) 		$query .= 'allow_view_other_transaction_history = 1, '; else $query .= 'allow_view_other_transaction_history = 0, ';
		if (isset($_POST['member_expiry_hidden'])) 						$query .= 'member_expiry_hidden = 1, '; 				else $query .= 'member_expiry_hidden = 0, ';
		if (isset($_POST['allow_member_admin_categories'])) 			$query .= 'allow_member_admin_categories = 1, '; 		else $query .= 'allow_member_admin_categories = 0, ';
		if (isset($_POST['suspend_on_expiry'])) 						$query .= 'suspend_on_expiry = 1, '; 					else $query .= 'suspend_on_expiry = 0, ';
		if (isset($_POST['bulk_trading_confirm'])) 						$query .= 'bulk_trading_confirm = 1, '; 				else $query .= 'bulk_trading_confirm = 0, ';
		if (isset($_POST['enable_transaction_service_fee'])) 			$query .= 'enable_transaction_service_fee = 1, '; 		else $query .= 'enable_transaction_service_fee = 0, ';
		if (isset($_POST['enable_auctions'])) 							$query .= 'enable_auctions = 1, '; 						else $query .= 'enable_auctions = 0, ';
		if (isset($_POST['freeze_auction_after_bid'])) 					$query .= 'freeze_auction_after_bid = 1, '; 			else $query .= 'freeze_auction_after_bid = 0, ';
		if (isset($_POST['enable_instant_buy'])) 						$query .= 'enable_instant_buy = 1, '; 					else $query .= 'enable_instant_buy = 0, ';
		
		if (!isset($_POST['currency_name'])) 						$query .= "currency_name = '', "; 					else $query .= "currency_name = '".						mysql_real_escape_string($post_post['currency_name'])."', ";
		if (!isset($_POST['currency_name_singular'])) 				$query .= "currency_name_singular = '', "; 			else $query .= "currency_name_singular = '".			mysql_real_escape_string($post_post['currency_name_singular'])."', ";
		if (!isset($_POST['transaction_name_singular'])) 			$query .= "transaction_name_singular = '', "; 		else $query .= "transaction_name_singular = '".			mysql_real_escape_string($post_post['transaction_name_singular'])."', ";
		if (!isset($_POST['transaction_name_plural'])) 				$query .= "transaction_name_plural = '', "; 		else $query .= "transaction_name_plural = '".			mysql_real_escape_string($post_post['transaction_name_plural'])."', ";
		if (!isset($_POST['default_expiry_message'])) 				$query .= "default_expiry_message = '', "; 			else $query .= "default_expiry_message = '".			mysql_real_escape_string($post_post['default_expiry_message'])."', ";
		if (!isset($_POST['setup_fee'])) 							$query .= "setup_fee = '', "; 						else $query .= "setup_fee = '".							number_format($post_post['setup_fee'],2)."', ";
		if (!isset($_POST['transaction_service_fee_seller'])) 		$query .= "transaction_service_fee_seller = '', "; 	else $query .= "transaction_service_fee_seller = '".	number_format($post_post['transaction_service_fee_seller'],2)."', ";
		if (!isset($_POST['transaction_service_fee_buyer'])) 		$query .= "transaction_service_fee_buyer = '', "; 	else $query .= "transaction_service_fee_buyer = '".		number_format($post_post['transaction_service_fee_buyer'],2)."', ";
		if (!isset($_POST['negative_balance_limit'])) 				$query .= "negative_balance_limit = '', "; 			else $query .= "negative_balance_limit = ".				number_format($post_post['negative_balance_limit'],2,'.','').", ";
		if (!isset($_POST['dump_balance_accountID'])) 				$query .= "dump_balance_accountID = '' "; 			else $query .= "dump_balance_accountID = '".			number_format($post_post['dump_balance_accountID'],0)."'";
		if ($mysql->query($query)) {
			return true;
		} else {
			$this->error = $mysql->error;
			return false;
		}			
	}
	function lets_settings_html($i,$url) {
		$mysql = new mysql;
		$links = new links;
		$links->build_url(1,106);
		if (mysql_num_rows(mysql_query("SHOW TABLES LIKE 'config'")) == 0 && !$mysql->result('SELECT * FROM config LIMIT 1')) {
			return $mysql->error;
		}
		$z = $i."<!-- lets_settings_form -->\n";
		$z .= $i."<div id=\"lets_settings_form\">\n";
		$z .= $i.T_(' Only settings directly related to LETS are here. For more general website settings go <a href="').URL.$links->complete_url.append_url(0).'">'.T_('here')."</a><br /><br />\n";
		$z .= $i." <fieldset>\n";
		$z .= $i.' <form name="lets_settings" method="post" action="'.URL.$url.append_url($url).'">'."\n";
		$z .= $i.'  <h3>'.T_('Currency Information')."</h3>\n";
		$z .= $i.'  <label for="currency_name">'.T_('Currency Name').' '.T_('(plural)').':</label>'."\n";
		$z .= $i.'  <input type="text" id="currency_name" name="currency_name" value="'.htmlspecialchars($mysql->result['currency_name']).'" /><br class="left" />'."\n";
		$z .= $i.'  <label for="currency_name_singular">'.T_('Currency Name').' '.T_('(singular)').':</label>'."\n";
		$z .= $i.'  <input type="text" id="currency_name_singular" name="currency_name_singular" value="'.htmlspecialchars($mysql->result['currency_name_singular']).'" /><br class="left" />'."\n";
		$z .= $i.'  <h3>'.T_('Transaction Information')."</h3>\n";
		$z .= $i.'  <label for="transaction_name_singular">'.T_('Transaction Name').' '.T_('(singular)').':</label>'."\n";
		$z .= $i.'  <input type="text" id="transaction_name_singular" name="transaction_name_singular" value="'.htmlspecialchars($mysql->result['transaction_name_singular']).'" /><br class="left" />'."\n";
		$z .= $i.'  <label for="transaction_name_plural">'.T_('Transaction Name').' '.T_('(plural)').':</label>'."\n";
		$z .= $i.'  <input type="text" id="transaction_name_plural" name="transaction_name_plural" value="'.htmlspecialchars($mysql->result['transaction_name_plural']).'" /><br class="left" />'."\n";
		$z .= $i.'  <h3>'.T_('Transaction Fees')."</h3>\n";
		$z .= $i.'  '.T_('Enter a positive transaction amount at startup to charge').' '.a(MEMBERS_NAME_SINGULAR).' '.strtolower(MEMBERS_NAME_SINGULAR).' '.T_('a fee when they join. Enter a negative value to give them a bonus.').'<br class="left" /><br class="left" />'."\n";
		$z .= $i.'  <label for="setup_fee">'.T_('Transaction amount at startup').':</label>'."\n";
		$z .= $i.'  <input type="text" id="setup_fee" name="setup_fee" value="'.$mysql->result['setup_fee'].'" /><br class="left" />'."\n";
		$z .= $i.'  <label for="enable_transaction_service_fee">'.T_('Enable Transaction Fees').':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="enable_transaction_service_fee" name="enable_transaction_service_fee" value="1"'.set_checked($mysql->result['enable_transaction_service_fee']).' /><br class="left" /><br class="left" /><br class="left" />'."\n";
		$z .= $i.'  '.T_('Buyer and seller fees are only applicable if they are enabled above. If enabled they will only work if a positive amount is entered.').'<br class="left" /><br class="left" />'."\n";
		$z .= $i.'  <label for="transaction_service_fee_seller">'.T_('Seller Fee').':</label>'."\n";
		$z .= $i.'  <input type="text" id="transaction_service_fee_seller" name="transaction_service_fee_seller" value="'.$mysql->result['transaction_service_fee_seller'].'" /><br class="left" />'."\n";
		$z .= $i.'  <label for="transaction_service_fee_buyer">'.T_('Buyer Fee').':</label>'."\n";
		$z .= $i.'  <input type="text" id="transaction_service_fee_buyer" name="transaction_service_fee_buyer" value="'.$mysql->result['transaction_service_fee_buyer'].'" /><br class="left" />'."\n";
		$z .= $i.'  <h3>'.T_('Auctions')."</h3>\n";
		$z .= $i.'  <label for="enable_auctions">'.T_('Enable Auctions').':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="enable_auctions" name="enable_auctions" value="1"'.set_checked($mysql->result['enable_auctions']).' /><br class="left" /><br class="left" /><br class="left" />'."\n";
		$z .= $i.'  '.T_('If auctions are enabled the following option will prevent').' '.a(MEMBERS_NAME_SINGULAR).' '.strtolower(MEMBERS_NAME_SINGULAR).' '.T_('from editing their auction details once a bid has been placed on it.').'<br class="left" /><br class="left" />'."\n";
		$z .= $i.'  <label for="freeze_auction_after_bid">'.T_('Freeze Auction').':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="freeze_auction_after_bid" name="freeze_auction_after_bid" value="1"'.set_checked($mysql->result['freeze_auction_after_bid']).' /><br class="left" />'."\n";
		$z .= $i.'  <h3>"'.T_('Buy Now')."\"</h3>\n";
		$z .= $i.'  '.T_('This option can work with or without auctions. If').' '.a(NOTICEBOARD_NAME_SINGULAR).' '.strtolower(NOTICEBOARD_NAME_SINGULAR).
		T_(' is set for "Buy it Now" they will be automatically removed from the active listings when the transaction is completed.').'<br class="left" /><br class="left" />'."\n";
		$z .= $i.'  <label for="enable_instant_buy">'.T_('Enable Buy Now').'</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="enable_instant_buy" name="enable_instant_buy" value="1"'.set_checked($mysql->result['enable_instant_buy']).' /><br class="left" /><br class="left" /><br class="left" />'."\n";
		$z .= $i.'  '.T_('If "Buy it Now" is enabled this option will only allow the transaction to take place at the original asking price. If not enabled a Buy Now transaction can be completed at any amount.').'<br class="left" /><br class="left" />'."\n";
		$z .= $i.'  <label for="lock_buy_now_price">'.T_('Lock Buy Now Price').':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="lock_buy_now_price" name="lock_buy_now_price" value="1"'.set_checked($mysql->result['lock_buy_now_price']).' /><br class="left" />'."\n";
		$z .= $i.'  <h3>'.T_('Noticeboard Access')."</h3>\n";
		$z .= $i.'  '.T_('If either auctions or "Buy Now" is enabled the').' '.strtolower(NOTICEBOARD_NAME_SINGULAR).' '.T_('acts as a kind of receipt as it is linked to in the transaction history. The following options will restrict what can be done to the ').
		strtolower(NOTICEBOARD_NAME_SINGULAR).' '.T_('once the transaction has been completed.').'<br class="left" /><br class="left" />'."\n";
		$z .= $i.'  <label for="prevent_edit_after_transaction">'.T_('Prevent editing after transaction').'</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="prevent_edit_after_transaction" name="prevent_edit_after_transaction" value="1"'.set_checked($mysql->result['prevent_edit_after_transaction']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="prevent_deletion_after_transaction">'.T_('Prevent deleting after transaction').'</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="prevent_deletion_after_transaction" name="prevent_deletion_after_transaction" value="1"'.set_checked($mysql->result['prevent_deletion_after_transaction']).' /><br class="left" />'."\n";
		$z .= $i."  <h3>".ucwords(MEMBERS_NAME_SINGULAR).' '.T_('Access')."</h3>\n";
		$z .= $i.'  <label for="negative_balance_limit">'.T_('Negative Balance Limit (leave blank or zero for none)').'</label>'."\n";
		$z .= $i.'  <input type="text" id="negative_balance_limit" name="negative_balance_limit" value="'.$mysql->result['negative_balance_limit'].'" /><br class="left" />'."\n";
		$z .= $i.'  <label for="allow_view_other_transaction_history">'.T_('Allow').' '.strtolower(MEMBERS_NAME_PLURAL).' '.T_('to view each other\'s').' '.strtolower(TRANSACTION_NAME_SINGULAR).' '.T_('history').'</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="allow_view_other_transaction_history" name="allow_view_other_transaction_history" value="1"'.set_checked($mysql->result['allow_view_other_transaction_history']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="member_expiry_hidden">'.T_('Hide').' '.strtolower(MEMBERS_NAME_PLURAL).' '.T_('expiry date from each other.').'</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="member_expiry_hidden" name="member_expiry_hidden" value="1"'.set_checked($mysql->result['allow_view_other_transaction_history']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="allow_member_admin_categories">'.T_('Allow').' '.strtolower(MEMBERS_NAME_PLURAL).' '.T_('to administer categories.').'</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="allow_member_admin_categories" name="allow_member_admin_categories" value="1"'.set_checked($mysql->result['allow_member_admin_categories']).' /><br class="left" /><br class="left" /><br class="left" />'."\n";
		$z .= $i.'  '.T_('Expired').' '.strtolower(MEMBERS_NAME_PLURAL).' '.T_('have all the privlidges of non-expired').' '.strtolower(MEMBERS_NAME_PLURAL).'. '.T_('A suspended').' '.strtolower(MEMBERS_NAME_SINGULAR).' '.T_('looses all access to the site and their ').
		strtolower(NOTICEBOARD_NAME_PLURAL).' '.T_('become hidden.').'<br class="left" /><br class="left" />'."\n";
		$z .= $i.'  <label for="suspend_on_expiry">'.T_('Automatically suspend').' '.strtolower(MEMBERS_NAME_PLURAL).' '.T_('when expired?').':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="suspend_on_expiry" name="suspend_on_expiry" value="1"'.set_checked($mysql->result['suspend_on_expiry']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="default_expiry_message">'.T_('Message when automatically suspended for expiry').':</label>'."\n";
		$z .= $i.'  <input type="text" id="default_expiry_message" name="default_expiry_message" value="'.htmlspecialchars($mysql->result['default_expiry_message']).'" /><br class="left" />'."\n";
		$z .= $i.'  <h3>'.T_('Misc')."</h3>\n";
		$z .= $i.'  '.T_('When').' '.a(MEMBERS_NAME_SINGULAR).' '.strtolower(MEMBERS_NAME_SINGULAR).' '.T_('is deleted they are not really deleted but their balance is brought to zero. ')
		.T_('They can later be reactivated and their balance restored to it\'s former amount (positive or negative). ')
		.T_('Enter which account this transaction should be done with. If left empty the #1 account will be used.').'<br class="left" /><br class="left" />'."\n";
		$z .= $i.'  <label for="dump_balance_accountID">'.T_('Account ID for transferring accounts to').':</label>'."\n";
		$z .= $i.'  <input type="text" id="dump_balance_accountID" name="dump_balance_accountID" value="'.$mysql->result['dump_balance_accountID'].'" /><br class="left" />'."\n";
		$z .= $i.'  <label for="bulk_trading_confirm">'.T_('Confirm Bulk').' '.ucwords(TRANSACTION_NAME_PLURAL).'?:</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="bulk_trading_confirm" name="bulk_trading_confirm" value="1"'.set_checked($mysql->result['bulk_trading_confirm']).' /><br class="left" />'."\n";
		
		$z .= $i.'  <input class="form_settings_button" type="submit" name="submit" value="Submit" />'."\n";
		$z .= $i.' </form>'."\n";
		$z .= $i." </fieldset>\n";
		$z .= $i.'</div>'."\n";
		$z .= $i."<!-- /lets_settings_form -->\n";
		return $z;
	}
	function update_site_settings() {
		$mysql = new mysql;
		$post_post = remove_slashes($_POST);
		if (!isset($post_post['site_name']) or !isset($post_post['location']) or !isset($post_post['url']) or !isset($post_post['path']) or !isset($post_post['time_out']) or !isset($post_post['image_quality'])
			or !isset($post_post['image_width_thumb_noticeboard']) or !isset($post_post['image_height_thumb_noticeboard']) or !isset($post_post['image_width_page_noticeboard']) or !isset($post_post['image_height_page_noticeboard'])
			or !isset($post_post['image_width_thumb_article']) or !isset($post_post['image_height_thumb_article']) or !isset($post_post['image_width_page_article']) or !isset($post_post['image_height_page_article'])
			or !isset($post_post['image_width_thumb_member']) or !isset($post_post['image_height_thumb_member']) or !isset($post_post['image_width_page_member']) or !isset($post_post['image_width_page_article'])
			or !isset($post_post['image_height_page_member']) or !isset($post_post['comment_name']) or !isset($post_post['comment_name_singular']) or !isset($post_post['comment_name_plural'])) {
				$this->error = 'Required Field Missing';
				return false;
		}
		if (!$post_post['site_name'] or !$post_post['location'] or !$post_post['url'] or !$post_post['path'] or !$post_post['time_out'] or !$post_post['image_quality']
			or !$post_post['image_width_thumb_noticeboard'] or !$post_post['image_height_thumb_noticeboard'] or !$post_post['image_width_page_noticeboard'] or !$post_post['image_height_page_noticeboard']
			or !$post_post['image_width_thumb_article'] or !$post_post['image_height_thumb_article'] or !$post_post['image_width_page_article'] or !$post_post['image_height_page_article']
			or !$post_post['image_width_thumb_member'] or !$post_post['image_height_thumb_member'] or !$post_post['image_width_page_member'] or !$post_post['image_width_page_article']
			or !$post_post['image_height_page_member'] or !$post_post['comment_name'] or !$post_post['comment_name_singular'] or !$post_post['comment_name_plural']) {
				$this->error = 'Required Field Missing';
				return false;
		}
		if (!isset($post_post['time_out']) or !isset($post_post['time_out']) or !isset($post_post['image_quality']) or !isset($post_post['image_quality'])) {
			$this->error = 'Problem with a numeric field';
			return false;
		}
		if (($post_post['time_out'] < 0 or !is_numeric($post_post['time_out'])) or ($post_post['image_quality'] < 0 or !is_numeric($post_post['image_quality']))) {
			$this->error = 'Problem with a numeric field';
			return false;
		}
		if (isset($post_post['template'])) {
			if (!$post_post['template']) {
				$post_post['template'] = 'default';
			}
		}
		if (isset($post_post['smtp_password']) and isset($post_post['smtp_password_confirm'])) {
			if ($post_post['smtp_password'] != $post_post['smtp_password_confirm']) {
				$this->error = T_('SMTP Passwords do not match');
				return false;
			}
		}
		if (isset($post_post['ftp_password']) and isset($post_post['ftp_password_confirm'])) {
			if ($post_post['ftp_password'] != $post_post['ftp_password_confirm']) {
				$this->error = T_('FTP Passwords do not match');
				return false;
			}
		}
		
		$query = 'UPDATE config SET ';
		if (!isset($post_post['twelve_hour_clock'])) 					$query .= 'twelve_hour_clock = 0, '; 			else { if ($post_post['twelve_hour_clock']) 			$query .= 'twelve_hour_clock = 1, '; 			else $query .= 'twelve_hour_clock = 0, '; }
		if (!isset($post_post['canadian'])) 							$query .= 'canadian = 0, '; 					else { if ($post_post['canadian']) 						$query .= 'canadian = 1, '; 					else $query .= 'canadian = 0, '; }
		if (!isset($post_post['enable_url_session_ids'])) 				$query .= 'enable_url_session_ids = 0, '; 		else { if ($post_post['enable_url_session_ids']) 		$query .= 'enable_url_session_ids = 1, '; 		else $query .= 'enable_url_session_ids = 0, '; }
		if (!isset($post_post['enable_noticeboard'])) 					$query .= 'enable_noticeboard = 0, ';			else { if ($post_post['enable_noticeboard']) 			$query .= 'enable_noticeboard = 1, '; 			else $query .= 'enable_noticeboard = 0, '; }
		if (!isset($post_post['enable_articles'])) 						$query .= 'enable_articles = 0, '; 				else { if ($post_post['enable_articles']) 				$query .= 'enable_articles = 1, '; 				else $query .= 'enable_articles = 0, '; }
		if (!isset($post_post['enable_events'])) 						$query .= 'enable_events = 0, '; 				else { if ($post_post['enable_events']) 				$query .= 'enable_events = 1, '; 				else $query .= 'enable_events = 0, '; }
		if (!isset($post_post['enable_faq'])) 							$query .= 'enable_faq = 0, '; 					else { if ($post_post['enable_faq']) 					$query .= 'enable_faq = 1, '; 					else $query .= 'enable_faq = 0, '; }
		if (!isset($post_post['enable_links'])) 						$query .= 'enable_links = 0, '; 				else { if ($post_post['enable_links']) 					$query .= 'enable_links = 1, '; 				else $query .= 'enable_links = 0, '; }
		if (!isset($post_post['enable_member_list'])) 					$query .= 'enable_member_list = 0, '; 			else { if ($post_post['enable_member_list']) 			$query .= 'enable_member_list = 1, '; 			else $query .= 'enable_member_list = 0, '; }
		if (!isset($post_post['validate_articles']))					$query .= 'validate_articles = 0, '; 			else { if ($post_post['validate_articles']) 			$query .= 'validate_articles = 1, '; 			else $query .= 'validate_articles = 0, '; }
		if (!isset($post_post['validate_events'])) 						$query .= 'validate_events = 0, '; 				else { if ($post_post['validate_events']) 				$query .= 'validate_events = 1, '; 				else $query .= 'validate_events = 0, '; }
		if (!isset($post_post['validate_faq'])) 						$query .= 'validate_faq = 0, '; 				else { if ($post_post['validate_faq']) 					$query .= 'validate_faq = 1, '; 				else $query .= 'validate_faq = 0, '; }
		if (!isset($post_post['validate_links'])) 						$query .= 'validate_links = 0, '; 				else { if ($post_post['validate_links']) 				$query .= 'validate_links = 1, '; 				else $query .= 'validate_links = 0, '; }
		if (!isset($post_post['validate_xhtml'])) 						$query .= 'validate_xhtml = 0, '; 				else { if ($post_post['validate_xhtml']) 				$query .= 'validate_xhtml = 1, '; 				else $query .= 'validate_xhtml = 0, '; }
		if (!isset($post_post['validate_members'])) 					$query .= 'validate_members = 0, '; 			else { if ($post_post['validate_members']) 				$query .= 'validate_members = 1, '; 			else $query .= 'validate_members = 0, '; }
		if (!isset($post_post['enable_images'])) 						$query .= 'enable_images = 0, '; 				else { if ($post_post['enable_images']) 				$query .= 'enable_images = 1, '; 				else $query .= 'enable_images = 0, '; }
		if (!isset($post_post['enable_comments'])) 						$query .= 'enable_comments = 0, '; 				else { if ($post_post['enable_comments']) 				$query .= 'enable_comments = 1, '; 				else $query .= 'enable_comments = 0, '; }
		if (!isset($post_post['show_comment_edited'])) 					$query .= 'show_comment_edited = 0, '; 			else { if ($post_post['show_comment_edited']) 			$query .= 'show_comment_edited = 1, '; 			else $query .= 'show_comment_edited = 0, '; }
		if (!isset($post_post['allow_comment_deletion'])) 				$query .= 'allow_comment_deletion = 0, '; 		else { if ($post_post['allow_comment_deletion']) 		$query .= 'allow_comment_deletion = 1, '; 		else $query .= 'allow_comment_deletion = 0, '; }
		if (!isset($post_post['comment_member_images'])) 				$query .= 'comment_member_images = 0, '; 		else { if ($post_post['comment_member_images']) 		$query .= 'comment_member_images = 1, '; 		else $query .= 'comment_member_images = 0, '; }
		if (!isset($post_post['show_faq_details'])) 					$query .= 'show_faq_details = 0, '; 			else { if ($post_post['show_faq_details']) 				$query .= 'show_faq_details = 1, '; 			else $query .= 'show_faq_details = 0, '; }
		if (!isset($post_post['show_link_details'])) 					$query .= 'show_link_details = 0, '; 			else { if ($post_post['show_link_details']) 			$query .= 'show_link_details = 1, '; 			else $query .= 'show_link_details = 0, '; }
		if (!isset($post_post['restrict_updown_links'])) 				$query .= 'restrict_updown_links = 0, '; 		else { if ($post_post['restrict_updown_links']) 		$query .= 'restrict_updown_links = 1, '; 		else $query .= 'restrict_updown_links = 0, '; }
		if (!isset($post_post['enable_guest_comments'])) 				$query .= 'enable_guest_comments = 0, '; 		else { if ($post_post['enable_guest_comments']) 		$query .= 'enable_guest_comments = 1, '; 		else $query .= 'enable_guest_comments = 0, '; }
		if (!isset($post_post['enable_member_edit_colours'])) 			$query .= 'enable_member_edit_colours = 0, '; 	else { if ($post_post['enable_member_edit_colours']) 	$query .= 'enable_member_edit_colours = 1, '; 	else $query .= 'enable_member_edit_colours = 0, '; }
		if (!isset($post_post['enable_rss'])) 							$query .= 'enable_rss = 0, '; 					else { if ($post_post['enable_rss']) 					$query .= 'enable_rss = 1, '; 					else $query .= 'enable_rss = 0, '; }
		if (!isset($post_post['enable_log'])) 							$query .= 'enable_log = 0, '; 					else { if ($post_post['enable_log']) 					$query .= 'enable_log = 1, '; 					else $query .= 'enable_log = 0, '; }
		if (!isset($post_post['enable_error_log'])) 					$query .= 'enable_error_log = 0, '; 			else { if ($post_post['enable_error_log']) 				$query .= 'enable_error_log = 1, '; 			else $query .= 'enable_error_log = 0, '; }
		if (!isset($post_post['log_additions'])) 						$query .= 'log_additions = 0, '; 				else { if ($post_post['log_additions']) 				$query .= 'log_additions = 1, '; 				else $query .= 'log_additions = 0, '; }
		if (!isset($post_post['log_edits'])) 							$query .= 'log_edits = 0, '; 					else { if ($post_post['log_edits']) 					$query .= 'log_edits = 1, '; 					else $query .= 'log_edits = 0, '; }
		if (!isset($post_post['log_deletions'])) 						$query .= 'log_deletions = 0, '; 				else { if ($post_post['log_deletions']) 				$query .= 'log_deletions = 1, '; 				else $query .= 'log_deletions = 0, '; }
		if (!isset($post_post['log_trimmed_error'])) 					$query .= 'log_trimmed_error = 0, '; 			else { if ($post_post['log_trimmed_error']) 			$query .= 'log_trimmed_error = 1, '; 			else $query .= 'log_trimmed_error = 0, '; }
		if (!isset($post_post['log_noticeboard'])) 						$query .= 'log_noticeboard = 0, '; 				else { if ($post_post['log_noticeboard']) 				$query .= 'log_noticeboard = 1, '; 				else $query .= 'log_noticeboard = 0, '; }
		if (!isset($post_post['log_transactions'])) 					$query .= 'log_transactions = 0, '; 			else { if ($post_post['log_transactions']) 				$query .= 'log_transactions = 1, '; 			else $query .= 'log_transactions = 0, '; }
		if (!isset($post_post['log_articles'])) 						$query .= 'log_articles = 0, '; 				else { if ($post_post['log_articles']) 					$query .= 'log_articles = 1, '; 				else $query .= 'log_articles = 0, '; }
		if (!isset($post_post['log_events'])) 							$query .= 'log_events = 0, '; 					else { if ($post_post['log_events']) 					$query .= 'log_events = 1, '; 					else $query .= 'log_events = 0, '; }
		if (!isset($post_post['log_faq'])) 								$query .= 'log_faq = 0, '; 						else { if ($post_post['log_faq']) 						$query .= 'log_faq = 1, '; 						else $query .= 'log_faq = 0, '; }
		if (!isset($post_post['log_links'])) 							$query .= 'log_links = 0, '; 					else { if ($post_post['log_links']) 					$query .= 'log_links = 1, '; 					else $query .= 'log_links = 0, '; }
		if (!isset($post_post['log_comments'])) 						$query .= 'log_comments = 0, '; 				else { if ($post_post['log_comments']) 					$query .= 'log_comments = 1, '; 				else $query .= 'log_comments = 0, '; }
		if (!isset($post_post['log_post_dump'])) 						$query .= 'log_post_dump = 0, '; 				else { if ($post_post['log_post_dump']) 				$query .= 'log_post_dump = 1, '; 				else $query .= 'log_post_dump = 0, '; }
		if (!isset($post_post['enable_email'])) 						$query .= 'enable_email = 0, '; 				else { if ($post_post['enable_email']) 					$query .= 'enable_email = 1, '; 				else $query .= 'enable_email = 0, '; }
		if (!isset($post_post['enable_email_contact'])) 				$query .= 'enable_email_contact = 0, '; 		else { if ($post_post['enable_email_contact']) 			$query .= 'enable_email_contact = 1, '; 		else $query .= 'enable_email_contact = 0, '; }
		if (!isset($post_post['email_technical_errors'])) 				$query .= 'email_technical_errors = 0, '; 		else { if ($post_post['email_technical_errors']) 		$query .= 'email_technical_errors = 1, '; 		else $query .= 'email_technical_errors = 0, '; }
		if (!isset($post_post['email_validation_submissions'])) 		$query .= 'email_validation_submissions = 0, '; else { if ($post_post['email_validation_submissions']) 	$query .= 'email_validation_submissions = 1, '; else $query .= 'email_validation_submissions = 0, '; }
		if (!isset($post_post['use_smtp'])) 							$query .= 'use_smtp = 0, '; 					else { if ($post_post['use_smtp']) 						$query .= 'use_smtp = 1, '; 					else $query .= 'use_smtp = 0, '; }
		if (!isset($post_post['show_search_link'])) 					$query .= 'show_search_link = 0, '; 			else { if ($post_post['show_search_link']) 				$query .= 'show_search_link = 1, '; 			else $query .= 'show_search_link = 0, '; }
		if (!isset($post_post['show_login_link'])) 						$query .= 'show_login_link = 0, '; 				else { if ($post_post['show_login_link']) 				$query .= 'show_login_link = 1, '; 				else $query .= 'show_login_link = 0, '; }
		if (!isset($post_post['show_help_link'])) 						$query .= 'show_help_link = 0, '; 				else { if ($post_post['show_help_link']) 				$query .= 'show_help_link = 1, '; 				else $query .= 'show_help_link = 0, '; }
		if (!isset($post_post['homepage_html_articles'])) 				$query .= 'homepage_html_articles = 0, '; 		else { if ($post_post['homepage_html_articles']) 		$query .= 'homepage_html_articles = 1, '; 		else $query .= 'homepage_html_articles = 0, '; }
		if (!isset($post_post['homepage_html_noticeboard'])) 			$query .= 'homepage_html_noticeboard = 0, '; 	else { if ($post_post['homepage_html_noticeboard']) 	$query .= 'homepage_html_noticeboard = 1, '; 	else $query .= 'homepage_html_noticeboard = 0, '; }
		if (!isset($post_post['homepage_html_events'])) 				$query .= 'homepage_html_events = 0, '; 		else { if ($post_post['homepage_html_events']) 			$query .= 'homepage_html_events = 1, '; 		else $query .= 'homepage_html_events = 0, '; }
		if (!isset($post_post['homepage_html_faq'])) 					$query .= 'homepage_html_faq = 0, '; 			else { if ($post_post['homepage_html_faq']) 			$query .= 'homepage_html_faq = 1, '; 			else $query .= 'homepage_html_faq = 0, '; }
		if (!isset($post_post['homepage_html_links'])) 					$query .= 'homepage_html_links = 0, '; 			else { if ($post_post['homepage_html_links']) 			$query .= 'homepage_html_links = 1, '; 			else $query .= 'homepage_html_links = 0, '; }
		if (!isset($post_post['persistant_html_search'])) 				$query .= 'persistant_html_search = 0, '; 		else { if ($post_post['persistant_html_search']) 		$query .= 'persistant_html_search = 1, '; 		else $query .= 'persistant_html_search = 0, '; }
		if (!isset($post_post['persistant_html_login'])) 				$query .= 'persistant_html_login = 0, '; 		else { if ($post_post['persistant_html_login']) 		$query .= 'persistant_html_login = 1, '; 		else $query .= 'persistant_html_login = 0, '; }
		if (!isset($post_post['persistant_html_articles'])) 			$query .= 'persistant_html_articles = 0, '; 	else { if ($post_post['persistant_html_articles']) 		$query .= 'persistant_html_articles = 1, '; 	else $query .= 'persistant_html_articles = 0, '; }
		if (!isset($post_post['persistant_html_noticeboard'])) 			$query .= 'persistant_html_noticeboard = 0, '; 	else { if ($post_post['persistant_html_noticeboard']) 	$query .= 'persistant_html_noticeboard = 1, '; 	else $query .= 'persistant_html_noticeboard = 0, '; }
		if (!isset($post_post['persistant_html_events'])) 				$query .= 'persistant_html_events = 0, '; 		else { if ($post_post['persistant_html_events']) 		$query .= 'persistant_html_events = 1, '; 		else $query .= 'persistant_html_events = 0, '; }
		if (!isset($post_post['persistant_html_faq'])) 					$query .= 'persistant_html_faq = 0, '; 			else { if ($post_post['persistant_html_faq']) 			$query .= 'persistant_html_faq = 1, '; 			else $query .= 'persistant_html_faq = 0, '; }
		if (!isset($post_post['persistant_html_links'])) 				$query .= 'persistant_html_links = 0, ';		else { if ($post_post['persistant_html_links']) 		$query .= 'persistant_html_links = 1, '; 		else $query .= 'persistant_html_links = 0, '; }
		if (!isset($post_post['allow_duplicate_emails'])) 				$query .= 'allow_duplicate_emails = 0, ';		else { if ($post_post['allow_duplicate_emails']) 		$query .= 'allow_duplicate_emails = 1, '; 		else $query .= 'allow_duplicate_emails = 0, '; }
		

		if (!isset($post_post['site_name']))						$query .= "site_name = '', ";						else	$query .= "site_name = '".						mysql_real_escape_string($post_post['site_name'])."', ";
		if (!isset($post_post['location']))							$query .= "location = '', ";						else	$query .= "location = '".						mysql_real_escape_string($post_post['location'])."', ";
		if (!isset($post_post['hour_offset']))						$query .= "hour_offset = '', ";						else	$query .= "hour_offset = '".					mysql_real_escape_string($post_post['hour_offset'])."', ";
		if (!isset($post_post['url']))								$query .= "url = '', ";								else	$query .= "url = '".							mysql_real_escape_string($post_post['url'])."', ";
		if (!isset($post_post['path']))								$query .= "path = '', ";							else	$query .= "path = '".							mysql_real_escape_string($post_post['path'])."', ";
		if (!isset($post_post['time_out']))							$query .= "time_out = '', ";						else	$query .= "time_out = '".						mysql_real_escape_string($post_post['time_out'])."', ";
		if (!isset($post_post['visitor_message']))					$query .= "visitor_message = '', ";					else	$query .= "visitor_message = '".				mysql_real_escape_string($post_post['visitor_message'])."', ";
		if (!isset($post_post['register_terms']))					$query .= "register_terms = '', ";					else	$query .= "register_terms = '".					mysql_real_escape_string($post_post['register_terms'])."', ";
		if (!isset($post_post['member_message']))					$query .= "member_message = '', ";					else	$query .= "member_message = '".					mysql_real_escape_string($post_post['member_message'])."', ";
		if (!isset($post_post['new_member_message']))				$query .= "new_member_message = '', ";				else	$query .= "new_member_message = '".				mysql_real_escape_string($post_post['new_member_message'])."', ";
		if (!isset($post_post['image_quality']))					$query .= "image_quality = '', ";					else	$query .= "image_quality = '".					mysql_real_escape_string($post_post['image_quality'])."', ";
		if (!isset($post_post['image_width_thumb_noticeboard']))	$query .= "image_width_thumb_noticeboard = '', ";	else	$query .= "image_width_thumb_noticeboard = '".	mysql_real_escape_string($post_post['image_width_thumb_noticeboard'])."', ";
		if (!isset($post_post['image_height_thumb_noticeboard']))	$query .= "image_height_thumb_noticeboard = '', ";	else	$query .= "image_height_thumb_noticeboard = '".	mysql_real_escape_string($post_post['image_height_thumb_noticeboard'])."', ";
		if (!isset($post_post['image_width_page_noticeboard']))		$query .= "image_width_page_noticeboard = '', ";	else	$query .= "image_width_page_noticeboard = '".	mysql_real_escape_string($post_post['image_width_page_noticeboard'])."', ";
		if (!isset($post_post['image_height_page_noticeboard']))	$query .= "image_height_page_noticeboard = '', ";	else	$query .= "image_height_page_noticeboard = '".	mysql_real_escape_string($post_post['image_height_page_noticeboard'])."', ";
		if (!isset($post_post['image_width_thumb_article']))		$query .= "image_width_thumb_article = '', ";		else	$query .= "image_width_thumb_article = '".		mysql_real_escape_string($post_post['image_width_thumb_article'])."', ";
		if (!isset($post_post['image_height_thumb_article']))		$query .= "image_height_thumb_article = '', ";		else	$query .= "image_height_thumb_article = '".		mysql_real_escape_string($post_post['image_height_thumb_article'])."', ";
		if (!isset($post_post['image_width_page_article']))			$query .= "image_width_page_article = '', ";		else	$query .= "image_width_page_article = '".		mysql_real_escape_string($post_post['image_width_page_article'])."', ";
		if (!isset($post_post['image_height_page_article']))		$query .= "image_height_page_article = '', ";		else	$query .= "image_height_page_article = '".		mysql_real_escape_string($post_post['image_height_page_article'])."', ";
		if (!isset($post_post['image_width_thumb_member']))			$query .= "image_width_thumb_member = '', ";		else	$query .= "image_width_thumb_member = '".		mysql_real_escape_string($post_post['image_width_thumb_member'])."', ";
		if (!isset($post_post['image_height_thumb_member']))		$query .= "image_height_thumb_member = '', ";		else	$query .= "image_height_thumb_member = '".		mysql_real_escape_string($post_post['image_height_thumb_member'])."', ";
		if (!isset($post_post['image_width_page_member']))			$query .= "image_width_page_member = '', ";			else	$query .= "image_width_page_member = '".		mysql_real_escape_string($post_post['image_width_page_member'])."', ";
		if (!isset($post_post['image_height_page_member']))			$query .= " = 'image_height_page_member', ";		else	$query .= "image_height_page_member = '".		mysql_real_escape_string($post_post['image_height_page_member'])."', ";
		if (!isset($post_post['comment_name']))						$query .= "comment_name = '', ";					else	$query .= "comment_name = '".					mysql_real_escape_string($post_post['comment_name'])."', ";
		if (!isset($post_post['comment_name_singular']))			$query .= "comment_name_singular = '', ";			else	$query .= "comment_name_singular = '".			mysql_real_escape_string($post_post['comment_name_singular'])."', ";
		if (!isset($post_post['comment_name_plural']))				$query .= "comment_name_plural = '', ";				else	$query .= "comment_name_plural = '".			mysql_real_escape_string($post_post['comment_name_plural'])."', ";
		if (!isset($post_post['template']))							$query .= "template = '', ";						else	$query .= "template = '".						mysql_real_escape_string($post_post['template'])."', ";
		if (!isset($post_post['admin_email']))						$query .= "admin_email = '', ";						else	$query .= "admin_email = '".					mysql_real_escape_string($post_post['admin_email'])."', ";
		if (!isset($post_post['technical_email']))					$query .= "technical_email = '', ";					else	$query .= "technical_email = '".				mysql_real_escape_string($post_post['technical_email'])."', ";
		if (!isset($post_post['validation_email']))					$query .= "validation_email = '', ";				else	$query .= "validation_email = '".				mysql_real_escape_string($post_post['validation_email'])."', ";
		if (!isset($post_post['email_from_name']))					$query .= "email_from_name = '', ";					else	$query .= "email_from_name = '".				mysql_real_escape_string($post_post['email_from_name'])."', ";
		if (!isset($post_post['update_email']))						$query .= "update_email = '', ";					else	$query .= "update_email = '".					mysql_real_escape_string($post_post['update_email'])."', ";
		if (!isset($post_post['email_smtp_host']))					$query .= "email_smtp_host = '', ";					else	$query .= "email_smtp_host = '".				mysql_real_escape_string($post_post['email_smtp_host'])."', ";
		if (!isset($post_post['email_smtp_host_backup']))			$query .= "email_smtp_host_backup = '', ";			else	$query .= "email_smtp_host_backup = '".			mysql_real_escape_string($post_post['email_smtp_host_backup'])."', ";
		if (!isset($post_post['smtp_user_name']))					$query .= "smtp_user_name = '', ";					else	$query .= "smtp_user_name = '".					mysql_real_escape_string($post_post['smtp_user_name'])."', ";
		if (!isset($post_post['ftp_host']))							$query .= "ftp_host = '', ";						else	$query .= "ftp_host = '".						mysql_real_escape_string($post_post['ftp_host'])."', ";
		if (!isset($post_post['ftp_login']))						$query .= "ftp_login = '', ";						else	$query .= "ftp_login = '".						mysql_real_escape_string($post_post['ftp_login'])."' ";
		
		if (isset($post_post['ftp_password'])) {
			if ($post_post['ftp_password']) {
				$query .= " ftp_password = '".				md5_encrypt($post_post['ftp_password'],SITE_KEY)."' ";
			}
		}
		if (isset($post_post['smtp_password'])) {
			if ($post_post['smtp_password']) {
				$query .= " smtp_password = '".			md5_encrypt($post_post['smtp_password'],SITE_KEY)."' ";
			}
		}
		$query = rtrim($query,', ');
		
		if ($mysql->query($query)) {
			return true;
		} else {
			$this->error = $mysql->error;
			return false;
		}			
	}
	
	function site_settings_html($i,$url) {
		$mysql = new mysql;
		$links = new links;
		$links->build_url(1,103);
		if (mysql_num_rows(mysql_query("SHOW TABLES LIKE 'config'")) == 0 && !$mysql->result('SELECT * FROM config LIMIT 1')) {
			return $mysql->error;
		}
		$z = $i."<!-- site_settings_form -->\n";
		$z .= $i."<div id=\"site_settings_form\">\n";
		$z .= $i.' '.T_('For settings specific to LETS please go').' <a href="'.URL.$links->complete_url.append_url(0).'">'.T_('here')."</a><br /><br />\n";
		$z .= $i." <fieldset>\n";
		$z .= $i.' <form name="site_settings" method="post" action="'.URL.$url.append_url($url).'">'."\n";
		$z .= $i.T_(' <strong>Warning:</strong> For advanced use only. Follow instructions carefully. There is no validation to this form so please ensure all information is correct.').'<br class="left" /><br class="left" />'."\n";
		$z .= $i.T_(' Required fields are').' <span class="required_field">'.REQUIRED_DISPLAY.'</span>.<br /><br />'."\n";
		$z .= $i.'  <h3>'.T_('Website Information')."</h3>\n";
		
		$z .= $i.'  <label for="site_name"><span class="required_field">'.T_('Name').':</span></label>'."\n";
		$z .= $i.'  <input type="text" id="site_name" name="site_name" value="'.htmlspecialchars($mysql->result['site_name']).'" /><br class="left" />'."\n";
		$z .= $i.'  <label for="location">'.T_('Location').':</label>'."\n";
		$z .= $i.'  <input type="text" id="location" name="location" value="'.htmlspecialchars($mysql->result['location']).'" /><br class="left" />'."\n";
		$z .= $i.'  <label for="hour_offset">'.T_('Hour Offset').':</label>'."\n";
		$z .= $i.'  <input type="text" id="hour_offset" name="hour_offset" value="'.$mysql->result['hour_offset'].'" /><br class="left" />'."\n";
		$z .= $i.'  <label for="twelve_hour_clock">'.T_('12 Hour Clock?').':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="twelve_hour_clock" name="twelve_hour_clock" value="1"'.set_checked($mysql->result['twelve_hour_clock']).' /><br class="left" /><br class="left" />'."\n";
		$z .= $i.'  '.T_('<strong>Note:</strong> Selecting the "Canadian" option adds provinces to the user forms as well as validates postal codes.').'<br class="left" /><br class="left" />'."\n";
		$z .= $i.'  <label for="canadian">'.T_('Canadian').':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="canadian" name="canadian" value="1"'.set_checked($mysql->result['canadian']).' /><br class="left" />'."\n";
		
		$z .= $i.'  <h3>'.T_('Website Settings')."</h3>\n";
		$z .= $i.'  '.T_('<strong>Warning:</strong> URL and file path are crucial to the proper functioning of the site and must be correct.').'<br class="left" /><br class="left" />'."\n";
		$z .= $i.'  '.T_('<strong>Complete URL:</strong> Must contain the initial "http://" and the trailing "/". Example: "http://www.your_lets_website.ca/" or "http://www.some_website.ca/your_lets_website/".').'<br class="left" />'."\n";
		$z .= $i.'  '.T_('<strong>Complete File Path:</strong> Must lead from the server\'s root directory to the location of the index.php file. Example: "/home/your_site/public_html/" or "/home/some_site/public_html/your_site/.').'<br class="left" /><br class="left" />'."\n";
		$z .= $i.'  <label for="url"><span class="required_field">'.T_('Complete URL').':</span></label>'."\n";
		$z .= $i.'  <input type="text" id="url" name="url" value="'.$mysql->result['url'].'" /><br class="left" />'."\n";
		$z .= $i.'  <label for="path"><span class="required_field">'.T_('Complete Path').':</span></label>'."\n";
		$z .= $i.'  <input type="text" id="path" name="path" value="'.$mysql->result['path'].'" /><br class="left" /><br class="left" />'."\n";
		$z .= $i.'  '.T_('<strong>Warning:</strong> URL-based sessions are not as secure as cookie-based sessions. If disabled and ').a(MEMBERS_NAME_SINGULAR).' '.strtolower(MEMBERS_NAME_SINGULAR).
		T_(' tries to login while disallowing cookies the site will give a message stating their requirement. If URL-based sessions are enabled a lower session time-out would increase security.').'<br class="left" /><br class="left" />'."\n";
		$z .= $i.'  <label for="enable_url_session_ids">'.T_('Enable URL-Based Session?').':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="enable_url_session_ids" name="enable_url_session_ids" value="1"'.set_checked($mysql->result['enable_url_session_ids']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="time_out"><span class="required_field">'.T_('Session Time Out (minutes)').':</span></label>'."\n";
		$z .= $i.'  <input type="text" id="time_out" name="time_out" value="'.$mysql->result['time_out'].'" /><br class="left" />'."\n";
		
		$z .= $i.'  <h3>'.T_('Enabled Sections')."</h3>\n";
		$z .= $i.'  '.T_('<strong>Note:</strong> Disabling the').' '.NOTICEBOARD_NAME.' '.T_('eliminates the vast majority of the site\'s functions.').'<br class="left" /><br class="left" />'."\n";
		$z .= $i.'  <label for="enable_noticeboard">'.NOTICEBOARD_NAME.':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="enable_noticeboard" name="enable_noticeboard" value="1"'.set_checked($mysql->result['enable_noticeboard']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="enable_articles">'.ARTICLES_NAME.':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="enable_articles" name="enable_articles" value="1"'.set_checked($mysql->result['enable_articles']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="enable_events">'.EVENTS_NAME.':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="enable_events" name="enable_events" value="1"'.set_checked($mysql->result['enable_events']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="enable_faq">'.FAQ_NAME.':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="enable_faq" name="enable_faq" value="1"'.set_checked($mysql->result['enable_faq']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="enable_links">'.LINKS_NAME.':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="enable_links" name="enable_links" value="1"'.set_checked($mysql->result['enable_links']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="enable_member_list">'.MEMBER_LIST_NAME.':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="enable_member_list" name="enable_member_list" value="1"'.set_checked($mysql->result['enable_member_list']).' /><br class="left" />'."\n";
		
		$z .= $i.'  <h3>'.T_('Validation Settings')."</h3>\n";
		$z .= $i.'  '.T_('<strong>Note:</strong> Check the sections you wish to enable validation.').' '.ucwords(MEMBERS_NAME_SINGULAR).' '.T_('submissions will not be displayed on the main site until an admin validates them.').'<br class="left" /><br class="left" />'."\n";
		$z .= $i.'  <label for="validate_articles">'.ARTICLES_NAME.':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="validate_articles" name="validate_articles" value="1"'.set_checked($mysql->result['validate_articles']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="validate_events">'.EVENTS_NAME.':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="validate_events" name="validate_events" value="1"'.set_checked($mysql->result['validate_events']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="validate_faq">'.FAQ_NAME.':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="validate_faq" name="validate_faq" value="1"'.set_checked($mysql->result['validate_faq']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="validate_links">'.LINKS_NAME.':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="validate_links" name="validate_links" value="1"'.set_checked($mysql->result['validate_links']).' /><br class="left" /><br class="left" />'."\n";
		$z .= $i.'  '.T_('Enabling XHTML Validation will apply to all sections. Submissions with invalid XHTML will be rejected with a message containing some debugging information.').'<br class="left" /><br class="left" />'."\n";
		$z .= $i.'  <label for="validate_xhtml">XHTML:</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="validate_xhtml" name="validate_xhtml" value="1"'.set_checked($mysql->result['validate_xhtml']).' /><br class="left" />'."\n";
		$z .= $i.'  '.T_('<strong>Warning:</strong> No method of email confirmation currently exists. Not validating ').strtolower(MEMBERS_NAME_PLURAL).' '.T_('could result in accounts for the purpose of spam.').'<br class="left" /><br class="left" />'."\n";
		$z .= $i.'  <label for="validate_members">'.T_('Validate').' '.ucwords(MEMBERS_NAME_SINGULAR).':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="validate_members" name="validate_members" value="1"'.set_checked($mysql->result['validate_members']).' /><br class="left" />'."\n";
		
		$z .= $i.'  <h3>'.T_('Messages')."</h3>\n";
		$z .= $i.'  '.T_('Here you can enter HTML that will appear on the home or').' '.strtolower(MEMBERS_NAME_PLURAL).' '.T_('pages').'.<br class="left" /><br class="left" />'."\n";
		$z .= $i.'  <label for="visitor_message">'.T_('Home Page').':</label>'."\n";
		$z .= $i.'  <textarea id="visitor_message" name="visitor_message">'.htmlspecialchars($mysql->result['visitor_message']).'</textarea><br class="left" />'."\n";
		$z .= $i.'  <label for="member_message">'.ucwords(MEMBERS_NAME_SINGULAR).T_('\'s Home:</label>')."\n";
		$z .= $i.'  <textarea id="member_message" name="member_message">'.htmlspecialchars($mysql->result['member_message']).'</textarea><br class="left" /><br class="left" />'."\n";
		$z .= $i.'  <label for="new_member_message">'.T_('Register Terms').':</label>'."\n";
		$z .= $i.'  <textarea id="register_terms" name="register_terms">'.htmlspecialchars($mysql->result['register_terms']).'</textarea><br class="left" />'."\n";
		$z .= $i.'  '.T_('If email is enables you can enter a message that will be email to new').' '.strtolower(MEMBERS_NAME_PLURAL).' '.T_('when they register').'.<br class="left" /><br class="left" />'."\n";
		$z .= $i.'  <label for="new_member_message">'.T_('New').' '.ucwords(MEMBERS_NAME_SINGULAR).':</label>'."\n";
		$z .= $i.'  <textarea id="new_member_message" name="new_member_message">'.htmlspecialchars($mysql->result['new_member_message']).'</textarea><br class="left" />'."\n";
		
		$z .= $i.'  <h3>'.T_('Image Settings')."</h3>\n";
		$z .= $i.'  '.T_('<strong>Note:</strong> The image functions of this site depend on PHP compiled with the GD libraries and writable permissions in the images folder. ').
		T_('If images are not working disable them here so user\'s will not be shown image forms.').'<br class="left" /><br class="left" />'."\n";
		$z .= $i.'  <label for="enable_images">'.T_('Enable Images:</label>')."\n";
		$z .= $i.'  <input type="checkbox" id="enable_images" name="enable_images" value="1"'.set_checked($mysql->result['enable_images']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="image_quality">'.T_('Image Quality').' (0 - 100):</label>'."\n";
		$z .= $i.'  <input type="text" id="image_quality" name="image_quality" value="'.$mysql->result['image_quality'].'" /><br class="left" /><br class="left" />'."\n";
		$z .= $i.'  '.T_('<strong>Note:</strong> Changing the image sizes after images have been uploaded will distort the output so it is recommended these adjustments only be done during setup.').'<br class="left" /><br class="left" />'."\n";
		$z .= $i.'  <label for="image_width_thumb_noticeboard">'.ucwords(NOTICEBOARD_NAME_SINGULAR).' '.T_('Thumbnail Width').':</label>'."\n";
		$z .= $i.'  <input type="text" id="image_width_thumb_noticeboard" name="image_width_thumb_noticeboard" value="'.$mysql->result['image_width_thumb_noticeboard'].'" /><br class="left" />'."\n";
		$z .= $i.'  <label for="image_height_thumb_noticeboard">'.ucwords(NOTICEBOARD_NAME_SINGULAR).' '.T_('Thumbnail Height').':</label>'."\n";
		$z .= $i.'  <input type="text" id="image_height_thumb_noticeboard" name="image_height_thumb_noticeboard" value="'.$mysql->result['image_height_thumb_noticeboard'].'" /><br class="left" />'."\n";
		$z .= $i.'  <label for="image_width_page_noticeboard">'.ucwords(NOTICEBOARD_NAME_SINGULAR).' '.T_('Image Width').':</label>'."\n";
		$z .= $i.'  <input type="text" id="image_width_page_noticeboard" name="image_width_page_noticeboard" value="'.$mysql->result['image_width_page_noticeboard'].'" /><br class="left" />'."\n";
		$z .= $i.'  <label for="image_height_page_noticeboard">'.ucwords(NOTICEBOARD_NAME_SINGULAR).' '.T_('Image Height').':</label>'."\n";
		$z .= $i.'  <input type="text" id="image_height_page_noticeboard" name="image_height_page_noticeboard" value="'.$mysql->result['image_height_page_noticeboard'].'" /><br class="left" />'."\n";
		$z .= $i.'  <label for="image_width_thumb_article">'.ucwords(ARTICLES_NAME_SINGULAR).' '.T_('Thumbnail Width').':</label>'."\n";
		$z .= $i.'  <input type="text" id="image_width_thumb_article" name="image_width_thumb_article" value="'.$mysql->result['image_width_thumb_article'].'" /><br class="left" />'."\n";
		$z .= $i.'  <label for="image_height_thumb_article">'.ucwords(ARTICLES_NAME_SINGULAR).' '.T_('Thumbnail Height').':</label>'."\n";
		$z .= $i.'  <input type="text" id="image_height_thumb_article" name="image_height_thumb_article" value="'.$mysql->result['image_height_thumb_article'].'" /><br class="left" />'."\n";
		$z .= $i.'  <label for="image_width_page_article">'.ucwords(ARTICLES_NAME_SINGULAR).' '.T_('Image Width').':</label>'."\n";
		$z .= $i.'  <input type="text" id="image_width_page_article" name="image_width_page_article" value="'.$mysql->result['image_width_page_article'].'" /><br class="left" />'."\n";
		$z .= $i.'  <label for="image_height_page_article">'.ucwords(ARTICLES_NAME_SINGULAR).' '.T_('Image Height').':</label>'."\n";
		$z .= $i.'  <input type="text" id="image_height_page_article" name="image_height_page_article" value="'.$mysql->result['image_height_page_article'].'" /><br class="left" />'."\n";
		$z .= $i.'  <label for="image_width_thumb_member">'.ucwords(MEMBERS_NAME_SINGULAR).' '.T_('Thumbnail Width').':</label>'."\n";
		$z .= $i.'  <input type="text" id="image_width_thumb_member" name="image_width_thumb_member" value="'.$mysql->result['image_width_thumb_member'].'" /><br class="left" />'."\n";
		$z .= $i.'  <label for="image_height_thumb_member">'.ucwords(MEMBERS_NAME_SINGULAR).' '.T_('Thumbnail Height').':</label>'."\n";
		$z .= $i.'  <input type="text" id="image_height_thumb_member" name="image_height_thumb_member" value="'.$mysql->result['image_height_thumb_member'].'" /><br class="left" />'."\n";
		$z .= $i.'  <label for="image_width_page_member">'.ucwords(MEMBERS_NAME_SINGULAR).' '.T_('Image Width').':</label>'."\n";
		$z .= $i.'  <input type="text" id="image_width_page_member" name="image_width_page_member" value="'.$mysql->result['image_width_page_member'].'" /><br class="left" />'."\n";
		$z .= $i.'  <label for="image_height_page_member">'.ucwords(MEMBERS_NAME_SINGULAR).' '.T_('Image Height').':</label>'."\n";
		$z .= $i.'  <input type="text" id="image_height_page_member" name="image_height_page_member" value="'.$mysql->result['image_height_page_member'].'" /><br class="left" />'."\n";
		
		$z .= $i.'  <h3>'.T_('Display')."</h3>\n";
		$z .= $i.'  '.T_('<strong>Note:</strong> These checked sections will appear on the home page:').'<br class="left" /><br class="left" />'."\n";
		$z .= $i.'  <label for="homepage_html_articles">'.ARTICLES_NAME.':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="homepage_html_articles" name="homepage_html_articles" value="1"'.set_checked($mysql->result['homepage_html_articles']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="homepage_html_noticeboard">'.NOTICEBOARD_NAME.':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="homepage_html_noticeboard" name="homepage_html_noticeboard" value="1"'.set_checked($mysql->result['homepage_html_noticeboard']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="homepage_html_events">'.EVENTS_NAME.':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="homepage_html_events" name="homepage_html_events" value="1"'.set_checked($mysql->result['homepage_html_events']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="homepage_html_faq">'.FAQ_NAME.':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="homepage_html_faq" name="homepage_html_faq" value="1"'.set_checked($mysql->result['homepage_html_faq']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="homepage_html_links">'.LINKS_NAME.':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="homepage_html_links" name="homepage_html_links" value="1"'.set_checked($mysql->result['homepage_html_links']).' /><br class="left" /><br class="left" />'."\n";
		$z .= $i.'  '.T_('<strong>Note:</strong> These checked elements will be persistently displayed. To save processing time disable them here rather than removing them from the default template file.').'<br class="left" /><br class="left" />'."\n";
		$z .= $i.'  <label for="persistant_html_search">'.T_('Search').':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="persistant_html_search" name="persistant_html_search" value="1"'.set_checked($mysql->result['persistant_html_search']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="persistant_html_login">'.T_('Login').':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="persistant_html_login" name="persistant_html_login" value="1"'.set_checked($mysql->result['persistant_html_login']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="persistant_html_articles">'.ARTICLES_NAME.':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="persistant_html_articles" name="persistant_html_articles" value="1"'.set_checked($mysql->result['persistant_html_articles']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="persistant_html_noticeboard">'.NOTICEBOARD_NAME.':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="persistant_html_noticeboard" name="persistant_html_noticeboard" value="1"'.set_checked($mysql->result['persistant_html_noticeboard']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="persistant_html_events">'.EVENTS_NAME.':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="persistant_html_events" name="persistant_html_events" value="1"'.set_checked($mysql->result['persistant_html_events']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="persistant_html_faq">'.FAQ_NAME.':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="persistant_html_faq" name="persistant_html_faq" value="1"'.set_checked($mysql->result['persistant_html_faq']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="persistant_html_links">'.LINKS_NAME.':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="persistant_html_links" name="persistant_html_links" value="1"'.set_checked($mysql->result['persistant_html_links']).' /><br class="left" /><br class="left" />'."\n";
		$z .= $i.'  '.T_('<strong>Note:</strong> Check the following links to have them displayed. Use them in conjunction with the above settings (If Login HTML is persistent there is no need for a login link).').'<br class="left" /><br class="left" />'."\n";
		$z .= $i.'  <label for="show_search_link">'.T_('Search').':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="show_search_link" name="show_search_link" value="1"'.set_checked($mysql->result['show_search_link']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="show_login_link">'.T_('Login').':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="show_login_link" name="show_login_link" value="1"'.set_checked($mysql->result['show_login_link']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="show_help_link">'.T_('Help').':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="show_help_link" name="show_help_link" value="1"'.set_checked($mysql->result['show_help_link']).' /><br class="left" /><br class="left" />'."\n";
		
		$z .= $i.'  <h3>'.T_('Style and Access Settings')."</h3>\n";
		$z .= $i.'  <label for="enable_comments">'.T_('Enable').' '.ucwords(COMMENT_NAME_PLURAL).':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="enable_comments" name="enable_comments" value="1"'.set_checked($mysql->result['enable_comments']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="show_comment_edited">Show '.strtolower(COMMENT_NAME_PLURAL).' as edited?:</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="show_comment_edited" name="show_comment_edited" value="1"'.set_checked($mysql->result['show_comment_edited']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="allow_comment_deletion">Allow '.strtolower(MEMBERS_NAME_PLURAL).' to delete '.strtolower(COMMENT_NAME_PLURAL).':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="allow_comment_deletion" name="allow_comment_deletion" value="1"'.set_checked($mysql->result['allow_comment_deletion']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="comment_member_images">Include '.strtolower(MEMBERS_NAME_SINGULAR).'\'s image in '.strtolower(COMMENT_NAME_SINGULAR).' (not seen by guests):</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="comment_member_images" name="comment_member_images" value="1"'.set_checked($mysql->result['comment_member_images']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="comment_name">'.ucwords(COMMENT_NAME_SINGULAR).' Name (heading):</label>'."\n";
		$z .= $i.'  <input type="text" id="comment_name" name="comment_name" value="'.htmlspecialchars($mysql->result['comment_name']).'" /><br class="left" />'."\n";
		$z .= $i.'  <label for="comment_name_singular">'.ucwords(COMMENT_NAME_SINGULAR).' Name Singular:</label>'."\n";
		$z .= $i.'  <input type="text" id="comment_name_singular" name="comment_name_singular" value="'.htmlspecialchars($mysql->result['comment_name_singular']).'" /><br class="left" />'."\n";
		$z .= $i.'  <label for="comment_name_plural">'.ucwords(COMMENT_NAME_SINGULAR).' Name Plural:</label>'."\n";
		$z .= $i.'  <input type="text" id="comment_name_plural" name="comment_name_plural" value="'.htmlspecialchars($mysql->result['comment_name_plural']).'" /><br class="left" />'."\n";
		$z .= $i.'  <label for="show_faq_details">Show '.FAQ_NAME.' "Submitted by..." details:</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="show_faq_details" name="show_faq_details" value="1"'.set_checked($mysql->result['show_faq_details']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="show_link_details">Show '.LINKS_NAME.' "Submitted by..." details:</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="show_link_details" name="show_link_details" value="1"'.set_checked($mysql->result['show_link_details']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="restrict_updown_links">Disallow '.strtolower(MEMBERS_NAME_PLURAL).' from moving '.FAQ_NAME.'/'.LINKS_NAME.' up and down:</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="restrict_updown_links" name="restrict_updown_links" value="1"'.set_checked($mysql->result['restrict_updown_links']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="enable_guest_comments">Enable Guests to post '.strtolower(COMMENT_NAME_PLURAL).':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="enable_guest_comments" name="enable_guest_comments" value="1"'.set_checked($mysql->result['enable_guest_comments']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="enable_member_edit_colours">Allow '.strtolower(MEMBERS_NAME_PLURAL).' to edit their colours:</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="enable_member_edit_colours" name="enable_member_edit_colours" value="1"'.set_checked($mysql->result['enable_member_edit_colours']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="enable_rss">Enable RSS?:</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="enable_rss" name="enable_rss" value="1"'.set_checked($mysql->result['enable_rss']).' /><br class="left" />'."\n";
		$folders = return_folders(PATH.'templates/');
		if (is_array($folders)) {
			$z .= $i.'  <br class="left" /><strong>Note:</strong> Want to change the look of this site? Copy the folder "default" in "'.PATH.
			'templates/" to another name, edit the HTML and CSS files, and change the following field to the new template. <strong>DO NOT</strong> delete the default folder.<br class="left" /><br class="left" />'."\n";
			$z .= $i.'  <label for="template">Template:</label>'."\n";
			$z .= $i."  <select id=\"template\" name=\"template\">";
			foreach($folders as $template) {
				$z .= $i."   <option value=\"".$template."\"".selected(' ','',$template,$mysql->result['template']).">".$template."</option>\n";
			}
			$z .= $i."  </select><br class=\"left\" />\n";
		}
		
		$z .= $i."  <h3>Log Settings</h3>\n";
		$z .= $i.'  <label for="enable_log">Enable Log:</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="enable_log" name="enable_log" value="1"'.set_checked($mysql->result['enable_log']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="enable_error_log">Enable Error Log:</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="enable_error_log" name="enable_error_log" value="1"'.set_checked($mysql->result['enable_error_log']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="log_post_dump">Record POST variable in error log?:</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="log_post_dump" name="log_post_dump" value="1"'.set_checked($mysql->result['log_post_dump']).' /><br class="left" /><br class="left" />'."\n";
		$z .= $i.'  If logging is enabled choose the following events.<br class="left" /><br class="left" />'."\n";
		$z .= $i.'  <label for="log_additions">Log User Submissions:</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="log_additions" name="log_additions" value="1"'.set_checked($mysql->result['log_additions']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="log_edits">Log User Edits:</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="log_edits" name="log_edits" value="1"'.set_checked($mysql->result['log_edits']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="log_deletions">Log User Deletions:</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="log_deletions" name="log_deletions" value="1"'.set_checked($mysql->result['log_deletions']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="log_trimmed_error">Log Trimmed Errors:</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="log_trimmed_error" name="log_trimmed_error" value="1"'.set_checked($mysql->result['log_trimmed_error']).' /><br class="left" /><br class="left" />'."\n";
		$z .= $i.'  The above setting will apply to each of the following sections.<br class="left" /><br class="left" />'."\n";
		$z .= $i.'  <label for="log_noticeboard">Log '.NOTICEBOARD_NAME.':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="log_noticeboard" name="log_noticeboard" value="1"'.set_checked($mysql->result['log_noticeboard']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="log_transactions">Log '.ucwords(TRANSACTION_NAME_PLURAL).':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="log_transactions" name="log_transactions" value="1"'.set_checked($mysql->result['log_transactions']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="log_articles">Log '.ARTICLES_NAME.':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="log_articles" name="log_articles" value="1"'.set_checked($mysql->result['log_articles']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="log_events">Log '.EVENTS_NAME.':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="log_events" name="log_events" value="1"'.set_checked($mysql->result['log_events']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="log_faq">Log '.FAQ_NAME.':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="log_faq" name="log_faq" value="1"'.set_checked($mysql->result['log_faq']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="log_links">Log '.LINKS_NAME.':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="log_links" name="log_links" value="1"'.set_checked($mysql->result['log_links']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="log_comments">Log '.COMMENT_NAME.':</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="log_comments" name="log_comments" value="1"'.set_checked($mysql->result['log_comments']).' /><br class="left" /><br class="left" />'."\n";
		$z .= $i.'  <div class="setting_links">'."\n";
		$links->build_url(1,108);
		$z .= $i.'   View <a target="_blank" href="'.URL.$links->complete_url.'?print=1&criteria='.urlencode(SITE_NAME).append_url(' ?').'">Log</a> or <a target="_blank" href="'.
		URL.$links->complete_url.'?print=1&criteria='.urlencode(SITE_NAME).'_Errors'.append_url(' ?').'">Error Log</a><br />'."\n";
		$z .= $i.'  </div>'."\n";
		
		$z .= $i."  <h3>Email Settings</h3>\n";
		$z .= $i.'  <label for="enable_email">Enable email:</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="enable_email" name="enable_email" value="1"'.set_checked($mysql->result['enable_email']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="admin_email">'.ucwords(ADMIN_NAME_SINGULAR).' email:</label>'."\n";
		$z .= $i.'  <input type="text" id="admin_email" name="admin_email" value="'.$mysql->result['admin_email'].'" /><br class="left" /><br class="left" />'."\n";
		$z .= $i.'  <strong>Note:</strong> The '.strtolower(ADMIN_NAME_SINGULAR).' email address will never be shown on the website. The admin can be contacted through a contact page if enabled below.<br class="left" /><br class="left" />'."\n";
		$z .= $i.'  <label for="enable_email_contact">Show Contact Page?:</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="enable_email_contact" name="enable_email_contact" value="1"'.set_checked($mysql->result['enable_email_contact']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="technical_email">Technical email:</label>'."\n";
		$z .= $i.'  <input type="text" id="technical_email" name="technical_email" value="'.$mysql->result['technical_email'].'" /><br class="left" />'."\n";
		$z .= $i.'  <label for="email_technical_errors">Email technical errors?:</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="email_technical_errors" name="email_technical_errors" value="1"'.set_checked($mysql->result['email_technical_errors']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="validation_email">Validation email:</label>'."\n";
		$z .= $i.'  <input type="text" id="validation_email" name="validation_email" value="'.$mysql->result['validation_email'].'" /><br class="left" />'."\n";
		$z .= $i.'  <label for="email_validation_submissions">Send email when new submissions need to be validated?:</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="email_validation_submissions" name="email_validation_submissions" value="1"'.set_checked($mysql->result['email_validation_submissions']).' /><br class="left" /><br class="left" />'."\n";
		$z .= $i.'  <strong>Note:</strong> If "Allow duplicate emails" is set different members will be able to share the same email address.<br class="left" /><br class="left" />'."\n";
		$z .= $i.'  <label for="allow_duplicate_emails">Allow duplicate emails:</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="allow_duplicate_emails" name="allow_duplicate_emails" value="1"'.set_checked($mysql->result['allow_duplicate_emails']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="update_email">Send emails from this address:</label>'."\n";
		$z .= $i.'  <input type="text" id="update_email" name="update_email" value="'.$mysql->result['update_email'].'" /><br class="left" /><br class="left" />'."\n";
		$z .= $i.'  <label for="email_from_name">The "From" Name:</label>'."\n";
		$z .= $i.'  <input type="text" id="email_from_name" name="email_from_name" value="'.$mysql->result['email_from_name'].'" /><br class="left" /><br class="left" />'."\n";
		$z .= $i.'  <strong>Note:</strong> Not all web servers allow php to send mail from "nobody". If that is the case you must provide correct SMTP information for email to function properly.<br class="left" /><br class="left" />'."\n";
		$z .= $i.'  <label for="use_smtp">Use SMTP?:</label>'."\n";
		$z .= $i.'  <input type="checkbox" id="use_smtp" name="use_smtp" value="1"'.set_checked($mysql->result['use_smtp']).' /><br class="left" />'."\n";
		$z .= $i.'  <label for="email_smtp_host">SMTP Host:</label>'."\n";
		$z .= $i.'  <input type="text" id="email_smtp_host" name="email_smtp_host" value="'.$mysql->result['email_smtp_host'].'" /><br class="left" />'."\n";
		$z .= $i.'  <label for="email_smtp_host_backup">SMTP Backup Host:</label>'."\n";
		$z .= $i.'  <input type="text" id="email_smtp_host_backup" name="email_smtp_host_backup" value="'.$mysql->result['email_smtp_host_backup'].'" /><br class="left" />'."\n";
		$z .= $i.'  <label for="smtp_user_name">SMTP Username:</label>'."\n";
		$z .= $i.'  <input type="text" id="smtp_user_name" name="smtp_user_name" value="'.$mysql->result['smtp_user_name'].'" /><br class="left" /><br class="left" />'."\n";
		$z .= $i.'  <strong>Note:</strong> Leave blank to keep the password the same. It is stored in encrypted form.<br class="left" /><br class="left" />'."\n";
		$z .= $i.'  <label for="smtp_password">SMTP Password:</label>'."\n";
		$z .= $i.'  <input type="password" id="smtp_password" name="smtp_password" /><br class="left" />'."\n";
		$z .= $i.'  <label for="smtp_password_confirm">Confirm SMTP Password:</label>'."\n";
		$z .= $i.'  <input type="password" id="smtp_password_confirm" name="smtp_password_confirm" /><br class="left" />'."\n";
		/*
		I decided there is no use changing these settings. The installer should ensure all permissions
		are already set making this info irrelevant.
		
		$z .= $i."  <h3>FTP Settings</h3>\n";
		$z .= $i.'  <strong>Note:</strong> Enter FTP information to set proper permissions.<br class="left" /><br class="left" />'."\n";
		$z .= $i.'  <label for="ftp_host">FTP Host:</label>'."\n";
		$z .= $i.'  <input type="text" id="ftp_host" name="ftp_host" value="'.$mysql->result['ftp_host'].'" /><br class="left" /><br class="left" />'."\n";
		$z .= $i.'  <label for="ftp_login">FTP Username:</label>'."\n";
		$z .= $i.'  <input type="text" id="ftp_login" name="ftp_login" value="'.$mysql->result['ftp_login'].'" /><br class="left" /><br class="left" />'."\n";
		$z .= $i.'  <strong>Note:</strong> Leave blank to keep the password the same. It is stored in encrypted form.<br class="left" /><br class="left" />'."\n";
		$z .= $i.'  <label for="ftp_password">FTP Password:</label>'."\n";
		$z .= $i.'  <input type="password" id="ftp_password" name="ftp_password" /><br class="left" />'."\n";
		$z .= $i.'  <label for="ftp_password_confirm">Confirm FTP Password:</label>'."\n";
		$z .= $i.'  <input type="password" id="ftp_password_confirm" name="ftp_password_confirm" /><br class="left" />'."\n";
		*/
		
		$z .= $i.'  <input class="site_settings_button" type="submit" name="submit" value="Submit" />'."\n";
		$z .= $i.' </form>'."\n";
		$z .= $i." </fieldset>\n";
		$z .= $i.'</div>'."\n";
		$z .= $i."<!-- /site_settings_form -->\n";
		return $z;
		
		
	}
		
}

?>