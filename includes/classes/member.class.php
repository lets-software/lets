<?php


class member {

    var $id,
        // Entered by user...
        $first_name,
        $last_name,
        $password,
        $confirmed_password,
        $address,
        $city,
        $province,
        $postal_code,
        $neighborhood,
        $mailing_address,
        $mailing_city,
        $mailing_province,
        $mailing_postal_code,
        $home_phone_number,
        $home_phone_number_prefix,
        $home_area_code,
        $work_phone_number,
        $work_phone_number_prefix,
        $work_area_code,
        $mobile_phone_number,
        $mobile_phone_number_prefix,
        $mobile_area_code,
        $email_address,
        $member_profile,
        $public_profile,
        $public_profile_enabled,
        $url,
        $receive_email_outbid,
        $receive_email_newletter,
        $receive_email_events,
        $receive_email_url,
        $receive_email_faq,
        $receive_email_buy,
        $receive_email_sell,
        $receive_email_noticeboard,
        $receive_newsletter,
        $receive_statement,

        // Variable requirement config
        $first_name_required,
        $last_name_required,
        $password_required,
        $confirmed_password_required,
        $address_required,
        $city_required,
        $province_required,
        $postal_code_required,
        $neighborhood_required,
        $mailing_address_required,
        $mailing_city_required,
        $mailing_province_required,
        $mailing_postal_code_required,
        $home_phone_number_required,
        $home_phone_number_prefix_required,
        $home_area_code_required,
        $work_phone_number_required,
        $work_phone_number_prefix_required,
        $work_area_code_required,
        $mobile_phone_number_required,
        $mobile_phone_number_prefix_required,
        $mobile_area_code_required,
        $email_address_required,
        $member_profile_required,
        $url_required,

        // Entered by system...
        $created_day,
        $created_month,
        $created_year,
        $expiry_day,
        $expiry_month,
        $expiry_year,
        $ll_day,
        $ll_month,
        $ll_year,
        $type,
        $balance,
        $image_id,

        // Entered by admin...
        $validated,
        $suspended,
        $suspended_message,
        $num_active_members,
        $num_suspended_members,
        $num_deleted_members,
        $num_expired_members,
        $bulk_summary,
        $bulk_confirm_form,

        // Messages...
        $successful_login,
        $error_message,
        $error;

    // Actions

    function set_required_variables() {
    // enter a 1 on variables that are required for registration
        $this->password_required = 1;
        $this->confirmed_password_required = 1;
        $this->first_name_required = 1;
        $this->last_name_required = 1;

        $mysql = new mysql;
        $mysql->result('SELECT * FROM config');
        $this->address_required = $mysql->result['member_address_required'];
        $this->city_required = $mysql->result['member_city_required'];
        $this->province_required = $mysql->result['member_province_required'];
        $this->postal_code_required = $mysql->result['member_postal_code_required'];
        $this->neighborhood_required = $mysql->result['member_neighborhood_required'];
        $this->home_phone_number_required = $mysql->result['member_home_phone_number_required'];
        if ($this->home_phone_number_required) {
            $this->home_phone_number_prefix_required = 1;
            $this->home_area_code_required = 1;
        }
        $this->work_phone_number_required = $mysql->result['member_work_phone_number_required'];
        if ($this->work_phone_number_required) {
            $this->work_phone_number_prefix_required = 1;
            $this->work_area_code_required = 1;
        }
        $this->mobile_phone_number_required = $mysql->result['member_mobile_phone_number_required'];
        if ($this->mobile_phone_number_required) {
            $this->mobile_phone_number_prefix_required = 0;
            $this->mobile_area_code_required = 0;
        }
        $this->email_address_required = $mysql->result['member_email_address_required'];
        $this->member_profile_required = $mysql->result['member_member_profile_required'];
        $this->url_required = $mysql->result['member_url_required'];
    }

    function full_name($id) {
        $mysql = new mysql;
        if ($mysql->result('SELECT first_name, last_name FROM accounts WHERE accountID = '.$id.' LIMIT 1')) {
            return $mysql->result['first_name'].' '.$mysql->result['last_name'];
        }
    }
    function first_name($id) {
        $mysql = new mysql;
        if ($mysql->result('SELECT first_name FROM accounts WHERE accountID = '.$id.' LIMIT 1')) {
            return $mysql->result['first_name'];
        }
    }
    function update_balance($balance,$member_id) {
        if (mysql_query('UPDATE accounts SET balance = '.$balance.' WHERE accountID = '.$member_id)) {
            return true;
        } else {
            if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('There was a database error when trying to update '.a(MEMBERS_NAME_SINGULAR).' '.strtolower(MEMBERS_NAME_SINGULAR).'\'s balance in transactions.class.php');
            if (ENABLE_ERROR_LOG) log_error('There was a database error when trying to update '.a(MEMBERS_NAME_SINGULAR).' '.strtolower(MEMBERS_NAME_SINGULAR).'\'s balance in transactions.class.php<br />Error:'.mysql_error());
            return false;
        }
    }
    function num_active_members() {
        $mysql = new mysql;
        $mysql->num_rows('SELECT accountID FROM accounts WHERE validated = 1 AND suspended = 0');
        $this->num_active_members = $mysql->num_rows;
        return $this->num_active_members;
    }
    function num_suspended_members() {
        $mysql = new mysql;
        $mysql->num_rows('SELECT accountID FROM accounts WHERE validated = 1 AND suspended = 1 AND deleted = 0 AND accountID != 1');
        $this->num_suspended_members = $mysql->num_rows;
        return $this->num_suspended_members;
    }
    function num_deleted_members() {
        $mysql = new mysql;
        $mysql->num_rows('SELECT accountID FROM accounts WHERE validated = 1 AND suspended = 1 AND deleted = 1 AND accountID != 1');
        $this->num_deleted_members = $mysql->num_rows;
        return $this->num_deleted_members;
    }
    function num_expired_members() {
        $mysql = new mysql;
        $mysql->num_rows('SELECT accountID FROM accounts WHERE validated = 1 AND suspended = 0 AND deleted = 0 AND accountID != 1
                            AND (expiry_year < '.$GLOBALS['date']['year'].' OR (expiry_year = '.$GLOBALS['date']['year'].' AND expiry_month < '.$GLOBALS['date']['month'].')
                            OR (expiry_year = '.$GLOBALS['date']['year'].' AND expiry_month = '.$GLOBALS['date']['month'].' AND expiry_day = '.$GLOBALS['date']['day'].'))');
        $this->num_expired_members = $mysql->num_rows;
        return $this->num_expired_members;
    }
    function update_status() {
        $this->num_active_members();
        $this->num_suspended_members();
        $this->num_deleted_members();
        $this->num_expired_members();
    }
    function confirm_data($i,$type) {
        $mysql = new mysql;
        $this->set_required_variables();
        // $type should be "add" or "edit"
        $message = "";
        // $type "add" will include password requirements
        if ($type == "add") {
            if (empty($_POST["member_password"])) {
                $message .= "{$i}Required Field: Password<br />\n";
            }
            if (empty($_POST["member_confirmed_password"])) {
                $message .= "{$i}Required Field: Re-typed Password<br />\n";
            }
            if ($_POST["member_password"] != $_POST["member_confirmed_password"]) {
                $message .= "{$i}Passwords do not match<br />\n";
            }
        }

        // Requirements:
        if (empty($_POST["member_first_name"]) and $this->first_name_required == 1) {
            $message .= "{$i}Required Field: First Name<br />\n";
        }
        if (empty($_POST["member_last_name"]) and $this->last_name_required == 1) {
            $message .= "{$i}Required Field: Last Name<br />\n";
        }
        if (empty($_POST["member_address"]) and $this->address_required == 1) {
            $message .= "{$i}Required Field: Address<br />\n";
        }
        if (empty($_POST["member_city"]) and $this->city_required == 1) {
            $message .= "{$i}Required Field: City<br />\n";
        }
        if (empty($_POST["member_province"]) and $this->province_required == 1) {
            $message .= "{$i}Required Field: Province<br />\n";
        }
        if (empty($_POST["member_postal_code"]) and $this->postal_code_required == 1) {
            $message .= "{$i}Required Field: Postal Code<br />\n";
        }
        if (empty($_POST["member_neighborhood"]) and $this->neighborhood_required == 1) {
            $message .= "{$i}Required Field: Neighborhood<br />\n";
        }
        if (empty($_POST["member_home_phone_number"]) and $this->home_phone_number_required == 1) {
            $message .= "{$i}Required Field: Home phone number<br />\n";
        }
        if (empty($_POST["member_home_phone_number_prefix"]) and $this->home_phone_number_prefix_required == 1) {
            $message .= "{$i}Required Field: Home phone number<br />\n";
        }
        if (empty($_POST["member_home_area_code"]) and $this->home_area_code_required == 1) {
            $message .= "{$i}Required Field: Home phone number<br />\n";
        }
        if (empty($_POST["member_work_phone_number"]) and $this->work_phone_number_required == 1) {
            $message .= "{$i}Required Field: Work phone number<br />\n";
        }
        if (empty($_POST["member_work_phone_number_prefix"]) and $this->work_phone_number_prefix_required == 1) {
            $message .= "{$i}Required Field: Work phone number<br />\n";
        }
        if (empty($_POST["member_work_area_code"]) and $this->work_area_code_required == 1) {
            $message .= "{$i}Required Field: Work phone number<br />\n";
        }
        if (empty($_POST["member_mobile_phone_number"]) and $this->mobile_phone_number_required == 1) {
            $message .= "{$i}Required Field: Mobile phone number<br />\n";
        }
        if (empty($_POST["member_mobile_phone_number_prefix"]) and $this->mobile_phone_number_prefix_required == 1) {
            $message .= "{$i}Required Field: Mobile phone number<br />\n";
        }
        if (empty($_POST["member_mobile_area_code"]) and $this->mobile_area_code_required == 1) {
            $message .= "{$i}Required Field: Mobile phone number<br />\n";
        }
        if (empty($_POST["member_email_address"]) and $this->email_address_required == 1) {
            $message .= "{$i}Required Field: Email address<br />\n";
        }
        if (empty($_POST["member_member_profile"]) and $this->member_profile_required == 1) {
            $message .= "{$i}Required Field: Profile<br />\n";
        }
        if (empty($_POST["member_url"]) and $this->url_required == 1) {
            $message .= "{$i}Required Field: Website<br />\n";
        }

        //data check
        // $type "edit" will check that passwords are the same only if they aren't empty
        if ($type == "edit") {
            if (!empty($_POST["member_password"])) {
                if ($_POST["member_password"] != $_POST["member_confirmed_password"]) {
                    $message .= "{$i}Passwords do not match<br />\n";
                }
            }
        }

        if (!empty($_POST["member_work_phone_number"]) or !empty($_POST["member_work_phone_number_prefix"]) or !empty($_POST["member_work_area_code"])) {
            if (strlen($_POST["member_work_phone_number"]) != 4 or strlen($_POST["member_work_phone_number_prefix"]) != 3 or strlen($_POST["member_work_area_code"]) != 3) {
                $message .= "{$i}Work phone number not valid:<br />Please enter area code, prefix and then last 4 digits of phone number<br />\n";
            }
        }
        if (!empty($_POST["member_home_phone_number"]) or !empty($_POST["member_home_phone_number_prefix"]) or !empty($_POST["member_home_area_code"])) {
            if (strlen($_POST["member_home_phone_number"]) != 4 or strlen($_POST["member_home_phone_number_prefix"]) != 3 or strlen($_POST["member_home_area_code"]) != 3) {
                $message .= "{$i}Home phone number not valid:<br />Please enter area code, prefix and then last 4 digits of phone number<br />\n";
            }
        }
        if (!empty($_POST["member_mobile_phone_number"]) or !empty($_POST["member_mobile_phone_number_prefix"]) or !empty($_POST["member_mobile_area_code"])) {
            if (strlen($_POST["member_mobile_phone_number"]) != 4 or strlen($_POST["member_mobile_phone_number_prefix"]) != 3 or strlen($_POST["member_mobile_area_code"]) != 3) {
                $message .= "{$i}Mobile phone number not valid:<br />Please enter area code, prefix and then last 4 digits of phone number<br />\n";
            }
        }
        if (CANADIAN) {
            if (!empty($_POST["member_postal_code"]) and strlen($_POST["member_postal_code"]) != 6) {
                $message .= "{$i}Postal Code is the wrong length. It should be 6 characters.<br />\n";
            }
            if (!empty($_POST["member_postal_code"]) and strpos($_POST["member_postal_code"],"-")) {
                $message .= "{$i}Postal Code should not have a \"-\". Please use A1A1A1 format.<br />\n";
            }
            if (!empty($_POST["member_mailing_postal_code"]) and strpos($_POST["member_mailing_postal_code"],"-")) {
                $message .= "{$i}Mailing Postal Code should not have a \"-\". Please use A1A1A1 format.<br />\n";
            }
            if (!empty($_POST["member_mailing_postal_code"]) and strlen($_POST["member_mailing_postal_code"]) != 6) {
                $message .= "{$i}Postal Code is the wrong length. It should be 6 characters.<br />\n";
            }
        }
        if (strpos($_POST["member_password"],"'")) {
            $message .= "{$i}Password cannot contain the character: '<br />\n";
        }
        if (isset($_POST["member_url"])) {
            if ((addslashes($_POST["member_url"]) != $_POST["member_url"])) {
                $message .= 'URL contains disallowed characters<br />';
            }
        }
        if (isset($_POST["member_email_address"])) {
            if ($_POST["member_email_address"]) {
                if (!verify_email_address($_POST["member_email_address"])) {
                    $message .= 'email is not valid<br />';
                } else {
                    if (!ALLOW_DUPLICATE_EMAILS) {
                        if ($mysql->result('SELECT accountID FROM accounts WHERE email_address = \''.$_POST["member_email_address"].'\' LIMIT 1')) {
                            $message .= 'email address is being used by a different member<br />';
                        }
                    }
                }
            }
        }
        if (!empty($message)) {
            $this->error_message = $message;
            return false;
        } else {
            return true;
        }

    }
    function set_expiry($id) {
        $mysql = new mysql;
        if (!isset($_POST['expiry_day']) or !isset($_POST['expiry_month']) or !isset($_POST['expiry_year'])) {
            $this->error = 'Date not set properly';
            return false;
        }
        if (!$mysql->query("UPDATE accounts SET expiry_day = ".$_POST['expiry_day'].", expiry_month = ".$_POST['expiry_month'].", expiry_year = ".$_POST['expiry_year']." WHERE accountID = ".$id.' LIMIT 1')) {
            $this->error = 'Database Error';
            if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: '.ucwords(MEMBERS_NAME_SINGULAR).' ID:'.$id.' ('.$this->full_name($id).') Expiry Updated.');
            if (ENABLE_ERROR_LOG) log_error('FAILED: '.ucwords(MEMBERS_NAME_SINGULAR).' ID:'.$id.' ('.$this->full_name($id).') Expiry Updated.<br />Error: '.$mysql->error);
            return false;
        } else {
            $this->expiry_day = $_POST['expiry_day'];
            $this->expiry_month = $_POST['expiry_month'];
            $this->expiry_year = $_POST['expiry_year'];
            if (ENABLE_LOG) log_action(ucwords(MEMBERS_NAME_SINGULAR).' ID:'.$id.' ('.$this->full_name($id).') Expiry Updated.');
            return true;
        }
    }

    function validate($id) {
        $mysql = new mysql;
        global $links;
        global $transactions;
        if (!$mysql->query('UPDATE accounts SET validated = 1 WHERE accountID = '.$id.' LIMIT 1')) {
            $this->error = 'Database Error';
            if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: '.ucwords(MEMBERS_NAME_SINGULAR).' ID:'.$id.' ('.$this->full_name($id).') Validated.');
            if (ENABLE_ERROR_LOG) log_error('FAILED: '.ucwords(MEMBERS_NAME_SINGULAR).' ID:'.$id.' ('.$this->full_name($id).') Validated.<br />Error: '.$mysql->error);
            return false;
        } else {
            if (!$this->set_expiry($id)) {
                return false;
            } else {
                $transactions->start($id);
                $this->validated = 1;
                if (ENABLE_LOG) log_action(ucwords(MEMBERS_NAME_SINGULAR).' ID:'.$id.' ('.$this->full_name($id).') Validated.');
                if (ENABLE_EMAIL) {
                    if ($mysql->result('SELECT email_address, first_name, last_name FROM accounts WHERE accountID = '.$id.' AND email_address != \'\'') and $links->build_url(14,0)) {
                        $text_msg = 'Dear '.$mysql->result['first_name'].",\r\n\r\nYour account has been validated and is now fully functional.\r\n\r\nPease login here: ".URL.$links->complete_url;
                        $html_msg = 'Dear '.$mysql->result['first_name'].',<br /><br />Your account has been validated and is now fully functional.<br />Pease <a href="'.URL.$links->complete_url.'">login here:</a>';
                        $mail_error = send_single_email(UPDATE_EMAIL,EMAIL_FROM_NAME,$mysql->result['email_address'],$mysql->result['first_name'].' '.$mysql->result['last_name'],'Your account has been validated',$text_msg,$html_msg);
                    }
                }
                return true;
            }
        }
    }
    function restore($id,$disable_expiry = false) {
        $mysql = new mysql;
        global $transactions;
        global $links;
        if (!$mysql->query('UPDATE accounts SET suspended = 0, deleted = 0 WHERE accountID = '.$id.' LIMIT 1')) {
            $this->error = 'Database Error';
            if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: '.ucwords(MEMBERS_NAME_SINGULAR).' ID:'.$id.' ('.$this->full_name($id).') Restored.');
            if (ENABLE_ERROR_LOG) log_error('FAILED: '.ucwords(MEMBERS_NAME_SINGULAR).' ID:'.$id.' ('.$this->full_name($id).') Restored.<br />Error: '.$mysql->error);
            return false;
        } else {
            if (!$disable_expiry) {
                $this->set_expiry($id);
                $this->suspended = 0;
                $this->suspended_message = '';
                if (ENABLE_LOG) log_action(ucwords(MEMBERS_NAME_SINGULAR).' ID:'.$id.' ('.$this->full_name($id).') Restored.');
                return true;
            } else {
                $this->suspended = 0;
                $this->suspended_message = '';
                if (ENABLE_LOG) log_action(ucwords(MEMBERS_NAME_SINGULAR).' ID:'.$id.' ('.$this->full_name($id).') Restored.');
                if (ENABLE_EMAIL) {
                    if ($mysql->result('SELECT email_address, first_name, last_name FROM accounts WHERE accountID = '.$id.' AND email_address != \'\'') and $links->build_url(14,0)) {
                        $text_msg = 'Dear '.$mysql->result['first_name'].",\r\n\r\nYour account has been restored and is now fully functional.\r\n\r\nPease login here: ".URL.$links->complete_url;
                        $html_msg = 'Dear '.$mysql->result['first_name'].',<br /><br />Your account has been restored and is now fully functional.<br />Pease <a href="'.URL.$links->complete_url.'">login here:</a>';
                        $mail_error = send_single_email(UPDATE_EMAIL,EMAIL_FROM_NAME,$mysql->result['email_address'],$mysql->result['first_name'].' '.$mysql->result['last_name'],'Your account has been restored',$text_msg,$html_msg);
                    }
                }
                return true;
            }
        }
    }
    function reactivate($id,$disable_expiry = false) {
        global $transactions;
        global $links;
        $mysql = new mysql;
        $this->error = '';
        if (!$disable_expiry) {
            $this->set_expiry($id);
        }
        if (ENABLE_NOTICEBOARD) {
            if ($mysql->result('SELECT transactions.buyerID, transactions.sellerID, transactions.amount FROM transactions,accounts WHERE transactions.transactionID = accounts.deleted_transactionID and accountID = '.$id.' LIMIT 1')) {
                $buyer_id = $mysql->result['sellerID'];
                $seller_id = $mysql->result['buyerID'];
                $amount = $mysql->result['amount'];
                if ($_POST['amount'] > 0) {
                    $amount = $_POST['amount'];
                }
                if (!$transactions->make_transaction(7,$buyer_id,$seller_id,$amount,'Account Reactivated. Balance restored to former amount.',$GLOBALS['date']['day'],$GLOBALS['date']['month'],$GLOBALS['date']['year'],$GLOBALS['date']['hour'],$GLOBALS['date']['minutes'],$GLOBALS['date']['seconds'])) {
                    $this->error .= $transactions->error;
                }
            }
        }
        if (!$mysql->query('UPDATE accounts SET deleted = 0, deleted_transactionID = 0, suspended = 0 WHERE accountID = '.$id.' LIMIT 1')) {
            $this->error .= $mysql->error;
        }
        if ($this->error) {
            if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: '.ucwords(MEMBERS_NAME_SINGULAR).' ID:'.$id.' ('.$this->full_name($id).') Reactivated.');
            if (ENABLE_ERROR_LOG) log_error('FAILED: '.ucwords(MEMBERS_NAME_SINGULAR).' ID:'.$id.' ('.$this->full_name($id).') Reactivated.<br />Error: '.$this->error);
            return false;
        } else {
            $this->deleted = 0;
            $this->deleted_transactionID = 0;
            $this->suspended = 0;
            $this->suspended_message = '';
            if (ENABLE_LOG) log_action(ucwords(MEMBERS_NAME_SINGULAR).' ID:'.$id.' ('.$this->full_name($id).') Reactivated.');
            if (ENABLE_EMAIL) {
                if ($mysql->result('SELECT email_address, first_name, last_name FROM accounts WHERE accountID = '.$id.' AND email_address != \'\'') and $links->build_url(14,0)) {
                    $text_msg = 'Dear '.$mysql->result['first_name'].",\r\n\r\nYour account has been reactivated and is now fully functional.\r\n\r\nPease login here: ".URL.$links->complete_url;
                    $html_msg = 'Dear '.$mysql->result['first_name'].',<br /><br />Your account has been reactivated and is now fully functional.<br />Pease <a href="'.URL.$links->complete_url.'">login here:</a>';
                    $mail_error = send_single_email(UPDATE_EMAIL,EMAIL_FROM_NAME,$mysql->result['email_address'],$mysql->result['first_name'].' '.$mysql->result['last_name'],'Your account has been reactivated',$text_msg,$html_msg);
                }
            }
            return true;
        }
    }
    function suspend($id) {
        global $links;
        $mysql = new mysql;
        $suspended_message = '';
        if (isset($_POST['suspended_message'])) {
            $suspended_message = remove_slashes($_POST['suspended_message']);
        } elseif ($mysql->result('SELECT default_expiry_message FROM config')) {
            $suspended_message = $mysql->result['default_expiry_message'];
        }
        if (!$suspended_message) {
            $suspended_message = 'not given';
        }
        if (!$mysql->query('UPDATE accounts SET suspended = 1, suspended_message = \''.mysql_real_escape_string($suspended_message).'\' WHERE accountID = '.$id.' LIMIT 1')) {
            $this->error = 'Database Error';
            if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: '.ucwords(MEMBERS_NAME_SINGULAR).' ID:'.$id.' ('.$this->full_name($id).') Suspended.');
            if (ENABLE_ERROR_LOG) log_error('FAILED: '.ucwords(MEMBERS_NAME_SINGULAR).' ID:'.$id.' ('.$this->full_name($id).') Suspended.<br />Error: '.$mysql->error);
            return false;
        } else {
            $this->suspended = 1;
            $this->suspended_message = $suspended_message;
            if (ENABLE_LOG) log_action(ucwords(MEMBERS_NAME_SINGULAR).' ID:'.$id.' ('.$this->full_name($id).') Suspended.');
            if (ENABLE_EMAIL) {
                if ($mysql->result('SELECT email_address, first_name, last_name FROM accounts WHERE accountID = '.$id.' AND email_address != \'\'') and $links->build_url(14,0)) {
                    $text_msg = 'Dear '.$mysql->result['first_name'].",\r\n\r\nYour account has been suspended with the following reason:\r\n".$suspended_message;
                    $html_msg = 'Dear '.$mysql->result['first_name'].',<br /><br />Your account has been suspended with the following reason:<br />'.$suspended_message;
                    $mail_error = send_single_email(UPDATE_EMAIL,EMAIL_FROM_NAME,$mysql->result['email_address'],$mysql->result['first_name'].' '.$mysql->result['last_name'],'Your account has been suspended',$text_msg,$html_msg);
                }
            }
            return true;
        }
    }
    function delete($id) {
        global $transactions;
        $mysql = new mysql;
        $last_transaction_id = 0;
        if (ENABLE_NOTICEBOARD) {
            if ($mysql->result('SELECT dump_balance_accountID FROM config')) {
                $dump_account = $mysql->result['dump_balance_accountID'];
            } else {
                $dump_account = 1;
            }
            if (!$dump_account) {
                $dump_account = 1;
            }
            $balance = 0;
            if ($transactions->balance($id)) {
                $balance = $transactions->balance;
            }
            $last_transaction_id = 0;
            if ($balance != 0) {
                if ($balance < 0) {
                    $balance = $balance * -1;
                    $buyer_id = $dump_account;
                    $seller_id = $id;
                    $description = 'Account Deleted. '.number_format($balance,2).' '.ucwords(CURRENCY_NAME).' transfered from Account #'.$dump_account.' to bring this balance to zero.';
                } else {
                    $seller_id = $dump_account;
                    $buyer_id = $id;
                    $description = 'Account Deleted. '.number_format($balance,2).' '.ucwords(CURRENCY_NAME).' transfered to Account #'.$dump_account.' which can be transfered back if membership is re-instated.';
                }
                if (!$transactions->make_transaction(6,$buyer_id,$seller_id,$balance,$description,$GLOBALS['date']['day'],$GLOBALS['date']['month'],$GLOBALS['date']['year'],$GLOBALS['date']['hour'],$GLOBALS['date']['minutes'],$GLOBALS['date']['seconds'])) {
                    $this->error = $transactions->error;
                    return false;
                } else {
                    $last_transaction_id = $transactions->id;
                }
            }
        }
        if (!$mysql->query('UPDATE accounts SET deleted = 1, suspended = 1, deleted_transactionID = '.$last_transaction_id.' WHERE accountID = '.$id)) {
            $this->error = 'Database Error';
            if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: '.ucwords(MEMBERS_NAME_SINGULAR).' ID:'.$id.' ('.$this->full_name($id).') Deleted.');
            if (ENABLE_ERROR_LOG) log_error('FAILED: '.ucwords(MEMBERS_NAME_SINGULAR).' ID:'.$id.' ('.$this->full_name($id).') Deleted.<br />Error: '.$mysql->error);
            return false;
        } else {
            $this->deleted = 1;
            $this->deleted_transactionID = $last_transaction_id;
            if (ENABLE_LOG) log_action(ucwords(MEMBERS_NAME_SINGULAR).' ID:'.$id.' ('.$this->full_name($id).') Deleted.');
            return true;
        }
    }
    function bulk_tools_html($i,$url) {
        $this->update_status();
        if (!$this->num_suspended_members and !$this->num_deleted_members and !$this->num_expired_members) {
            return $i." All acounts are active\n";
        }
        $z = $i."<!-- bulk_member_form -->\n";
        $z .= $i."<div id=\"bulk_member_tools\">\n";
        $z .= $i." <strong>Notes:</strong><br />It is not possible to undo these operations. Please use them carefully!<br /><br />".ucfirst(MEMBERS_NAME_SINGULAR)." accounts can be edited individually through the ".
                    '<a href="'.URL.MEMBER_LIST_URL.'/'.append_url(0).'">'.MEMBER_LIST_NAME."</a>.<br /><br />The expiry options apply only to accounts that will be effected by this operation and only work with the Reactivating, Restoring or Renewing Expiry Buttons.<br /><br />\n";
        $z .= $i." <fieldset>\n";
        $z .= $i.' <form name="membership" method="post" action="'.URL.$url.append_url($url).'">'."\n";
        $z .= $i.'  <label for="set_expiry_date">Set Expiry:</label>'."\n";
        $z .= $i.'  <input type="checkbox" id="renew_expiry" name="renew_expiry" value="1" /><br class="left" />'."\n";
        $z .= $i.'  <label for="expiry_month">Set Expiry:</label>'."\n";
        $z .= date_form($i."  ",array('day' => $GLOBALS['date']['day'], 'month' => $GLOBALS['date']['month'], 'year' => ($GLOBALS['date']['year'] + 1)),'expiry_');
        $z .= $i.'  <br class="left" />'."\n";
        if ($this->num_suspended_members) {
            $z .= $i.'  <input class="bulk_member_button" type="submit" name="submit" value="Delete All Suspended Accounts" /><br class="left" />'."\n";
            $z .= $i.'  <input class="bulk_member_button" type="submit" name="submit" value="Restore All Suspended Accounts" /><br class="left" />'."\n";
        }
        if ($this->num_deleted_members) {
            $z .= $i.'  <input class="bulk_member_button" type="submit" name="submit" value="Reactivate All Deleted Accounts" /><br class="left" />'."\n";
        }
        if ($this->num_expired_members) {
            $z .= $i.'  <input class="bulk_member_button" type="submit" name="submit" value="Suspend All Expired Accounts" /><br class="left" />'."\n";
            $z .= $i.'  <input class="bulk_member_button" type="submit" name="submit" value="Renew All Expired Accounts" /><br class="left" />'."\n";
        }
        $z .= $i.' </form>'."\n";
        $z .= $i." </fieldset>\n";
        $z .= $i.'</div>'."\n";
        $z .= $i."<!-- /bulk_member_form -->\n";
        return $z;
    }
    function bulk_membership_tools($i,$url,$action,$process) {
        $mysql = new mysql;
        $mysql_secondary = new mysql;
        global $transactions;
        $errors = '';

        if ($action == 'delete' or $action == 'restore') {
            $query = 'SELECT * FROM accounts WHERE suspended = 1 AND deleted = 0 AND accountID != 1';
        } elseif ($action == 'suspend' or $action == 'renew_expiry') {
            $query = 'SELECT * FROM accounts WHERE suspended = 0 AND deleted = 0 AND accountID != 1
            AND (expiry_year < '.$GLOBALS['date']['year'].' OR (expiry_year = '.$GLOBALS['date']['year'].' AND expiry_month < '.$GLOBALS['date']['month'].')
            OR (expiry_year = '.$GLOBALS['date']['year'].' AND expiry_month = '.$GLOBALS['date']['month'].' AND expiry_day = '.$GLOBALS['date']['day'].'))';
        } elseif ($action == 'reactivate') {
            $query = 'SELECT * FROM accounts WHERE suspended = 1 AND deleted = 1 AND accountID != 1';
        } elseif ($action == 'restore') {
            $query = 'SELECT * FROM accounts WHERE suspended = 1 AND deleted = 0 AND accountID != 1';
        } else {
            return false;
        }

        if (!$mysql->build_array($query)) {
            $this->error = $mysql->error;
            return false;
        }
        if (is_array($mysql->result)) {
            $z = $i."<!-- bulk_membership_result -->\n";
            $z .= $i."<div id=\"bulk_membership_results\">\n";
            $z .= $i." <table>\n";
            $z .= $i."  <tr>\n";
            $z .= $i."   <th class=\"h\">".ucwords(MEMBERS_NAME_SINGULAR)."</th>\n";
            $z .= $i."   <th class=\"h\">Old Status</th>\n";
            $z .= $i."   <th class=\"h\">Old Expiry Date</th>\n";
            $z .= $i."   <th class=\"h\">Old Balance</th>\n";
            $z .= $i."   <th class=\"h\">New Status</th>\n";
            $z .= $i."   <th class=\"h\">New Expiry Date</th>\n";
            $z .= $i."   <th class=\"h\">New Balance</th>\n";
            $z .= $i."   <th class=\"h\">Status</th>\n";
            $z .= $i."  </tr>\n";

            $f = $i."<!-- bulk_membership_confirmation_form -->\n";
            $f .= $i."<div id=\"bulk_membership_confirmation_form\">\n";
            $f .= $i.' <form name="membership" method="post" action="'.URL.$url.append_url($url).'">'."\n";

            switch ($action) {
                case 'delete': $f .= $i.'  <input type="hidden" name="delete" value="1" />'."\n"; break;
                case 'suspend': $f .= $i.'  <input type="hidden" name="suspend" value="1" />'."\n"; break;
                case 'reactivate': $f .= $i.'  <input type="hidden" name="reactivate" value="1" />'."\n"; break;
                case 'restore': $f .= $i.'  <input type="hidden" name="restore" value="1" />'."\n"; break;
                case 'renew_expiry': $f .= $i.'  <input type="hidden" name="renew_expiry" value="1" />'."\n"; break;
            }
            if (($action == 'reactivate' or $action == 'restore' or $action == 'renew_expiry') and ($_POST['renew_expiry'])) {
                $f .= $i.'  <input type="hidden" name="expiry_day" value="'.$_POST['expiry_day'].'" />'."\n";
                $f .= $i.'  <input type="hidden" name="expiry_month" value="'.$_POST['expiry_month'].'" />'."\n";
                $f .= $i.'  <input type="hidden" name="expiry_year" value="'.$_POST['expiry_year'].'" />'."\n";
                $f .= $i.'  <input type="hidden" name="renew_expiry" value="1" />'."\n";
            }
            $bulk_routine_errors = '';
            foreach($mysql->result as $member) {
                $z .= $i."  <tr>\n";
                // name
                $z .= $i.'   <td>'.$this->full_name($member['accountID']).' (#'.$member['accountID'].')</td>'."\n";
                // old status
                switch ($action) {
                    case 'delete': $z .= $i."   <td>Suspended</td>\n"; break;
                    case 'suspend': $z .= $i."   <td>Expired</td>\n"; break;
                    case 'reactivate': $z .= $i."   <td>Deleted</td>\n"; break;
                    case 'restore': $z .= $i."   <td>Suspended</td>\n"; break;
                    case 'renew_expiry': $z .= $i."   <td>Expired</td>\n"; break;
                }
                // old expiry
                $z .= $i.'   <td>'.return_month($member['expiry_month']).' '.$member['expiry_day'].', '.$member['expiry_year'].'</td>'."\n";
                $transactions->balance($member['accountID']);
                $this->balance = $transactions->balance;
                // old balance
                $z .= $i.'   <td>'.number_format($this->balance,2).'</td>'."\n";
                // determine is active or expired
                if (!isset($_POST['renew_expiry'])) $_POST['renew_expiry'] = 0;
                if ($_POST['renew_expiry']) {
                    if ($_POST['expiry_year'] < $GLOBALS['date']['year']
                        or ($_POST['expiry_year'] == $GLOBALS['date']['year'] and $_POST['expiry_month'] < $GLOBALS['date']['month'])
                        or ($_POST['expiry_year'] == $GLOBALS['date']['year'] and $_POST['expiry_month'] == $GLOBALS['date']['month'] and $_POST['expiry_day'] < $GLOBALS['date']['day'])) {
                        $d = $i."   <td>Expired</td>\n";
                    } else {
                        $d = $i."   <td>Active</td>\n";
                    }
                } else {
                    if ($member['expiry_year'] < $GLOBALS['date']['year']
                        or ($member['expiry_year'] == $GLOBALS['date']['year'] and $member['expiry_month'] < $GLOBALS['date']['month'])
                        or ($member['expiry_year'] == $GLOBALS['date']['year'] and $member['expiry_month'] == $GLOBALS['date']['month'] and $member['expiry_day'] < $GLOBALS['date']['day'])) {
                        $d = $i."   <td>Expired</td>\n";
                    } else {
                        $d = $i."   <td>Active</td>\n";
                    }
                }
                // new status
                switch ($action) {
                    case 'delete': $z .= $i."   <td>Deleted</td>\n"; break;
                    case 'suspend': $z .= $i."   <td>Suspended</td>\n"; break;
                    case 'reactivate': $z .= $d; break;
                    case 'restore': $z .= $d; break;
                    case 'renew_expiry': $z .= $d; break;
                }
                // new expiry
                if ($_POST['renew_expiry']) {
                    $z .= $i.'   <td>'.return_month($_POST['expiry_month']).' '.$_POST['expiry_day'].', '.$_POST['expiry_year'].'</td>'."\n";
                } else {
                    $z .= $i.'   <td>'.return_month($member['expiry_month']).' '.$member['expiry_day'].', '.$member['expiry_year'].'</td>'."\n";
                }
                //new balance
                switch ($action) {
                    case 'delete': $z .= $i."   <td>".number_format(0,2)."</td>\n"; break;
                    case 'suspend': $z .= $i."   <td>".number_format($this->balance,2)."</td>\n"; break;
                    case 'reactivate':
                        if ($member['deleted_transactionID']) {
                            if ($mysql_secondary->result('SELECT * FROM transactions WHERE transactionID = '.$member['deleted_transactionID'].' LIMIT 1')) {
                                if ($mysql_secondary->result['sellerID'] == $member['accountID']) {
                                    $z .= $i."   <td>".number_format(($mysql_secondary->result['amount'] * -1),2)."</td>\n"; break;
                                } else {
                                    $z .= $i."   <td>".number_format($mysql_secondary->result['amount'],2)."</td>\n"; break;
                                }
                            } else {
                                $z .= $i."   <td>".number_format(0,2)."</td>\n"; break;
                            }
                        } else {
                            $z .= $i."   <td>".number_format(0,2)."</td>\n"; break;
                        }
                        break;
                    case 'restore': $z .= $i."   <td>".number_format($this->balance,2)."</td>\n"; break;
                    case 'renew_expiry': $z .= $i."   <td>".number_format($this->balance,2)."</td>\n"; break;
                }
                // status
                if ($process) {
                    $local_success = true;
                    switch ($action) {
                        case 'reactivate':
                            if (!$this->reactivate($member['accountID'],$_POST['renew_expiry'])) {
                                $bulk_routine_errors .= $this->error;
                                $local_success = false;
                            }
                            break;
                        case 'delete':
                            if (!$this->delete($member['accountID'])) {
                                $bulk_routine_errors .= $this->error;
                                $local_success = false;
                            }
                            break;
                        case 'suspend':
                            if (!$this->suspend($member['accountID'])) {
                                $bulk_routine_errors .= $this->error;
                                $local_success = false;
                            }
                            break;
                        case 'restore':
                            if (!$this->restore($member['accountID'],$_POST['renew_expiry'])) {
                                $bulk_routine_errors .= $this->error;
                                $local_success = false;
                            }
                            break;
                        case 'renew_expiry':
                            if (!$this->set_expiry($member['accountID'])) {
                                $bulk_routine_errors .= $this->error;
                                $local_success = false;
                            }
                            break;
                    }
                    if ($local_success) {
                        $z .= $i."   <td>Processed</td>\n";
                    } else {
                        $z .= $i."   <td>Errors</td>\n";
                    }
                } else {
                    $z .= $i."   <td>Pending</td>\n";
                }
                $z .= $i."  </tr>\n";
            }

            $z .= $i." </table>\n";
            $z .= $i."</div>\n";
            $z .= $i."<!-- /bulk_membership_result -->\n";
            $f .= $i.'  <br /><input class="member_confirmation_button" type="submit" name="submit" value="Confirm" />'."\n";
            $f .= $i.'  <input class="member_confirmation_button" type="submit" name="submit" value="Cancel" />'."\n";
            $f .= $i." </form>\n";
            $f .= $i."</div>\n";
            $f .= $i."<!-- /bulk_membership_confirmation_form -->\n";
            $this->bulk_summary = $z;
            $this->bulk_confirm_form = $f;
            if ($bulk_routine_errors) {
                $this->bulk_routine_errors = $bulk_routine_errors;
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }
    function check_required($bool,$position) {
        if ($bool == 1) {
            if ($position == 1) {
                return "<span class=\"required_field\">";
            } else {
                return "</span>";
            }
        }
    }

    function add($i) {
        global $date;
        global $mysql;
        $data = array();
        if (!$this->confirm_data($i,"add")) {
            return false;
        } else {
            if (!empty($_POST["member_home_area_code"])) {
                $fixed_home_phone_number = "(".$_POST["member_home_area_code"].") ".$_POST["member_home_phone_number_prefix"]."-".$_POST["member_home_phone_number"];
            } else {
                $fixed_home_phone_number = '';
            }
            if (!empty($_POST["member_work_area_code"])) {
                $fixed_work_phone_number = "(".$_POST["member_work_area_code"].") ".$_POST["member_work_phone_number_prefix"]."-".$_POST["member_work_phone_number"];
            } else {
                $fixed_work_phone_number = '';
            }
            if (!empty($_POST["member_mobile_area_code"])) {
                $fixed_mobile_phone_number = "(".$_POST["member_mobile_area_code"].") ".$_POST["member_mobile_phone_number_prefix"]."-".$_POST["member_mobile_phone_number"];
            } else {
                $fixed_mobile_phone_number = '';
            }


            if (!empty($_POST["member_url"])) {
                $fixed_member_url = strtolower($_POST["member_url"]);
                if (!strpos('  '.$fixed_member_url,"http://")) {
                    $fixed_member_url = "http://".$fixed_member_url;
                }
            } else {
                $fixed_member_url = '';
            }

            $data[0]['name'] = 'first_name';
            $data[0]['value'] = ucwords(mysql_real_escape_string($_POST["member_first_name"]));
            $data[1]['name'] = 'last_name';
            $data[1]['value'] = ucwords(mysql_real_escape_string($_POST["member_last_name"]));
            $data[2]['name'] = 'password';
            $data[2]['value'] = crypt(md5($_POST["member_password"]),md5(SITE_KEY));
            $data[3]['name'] = 'address';
            $data[3]['value'] = ucwords(mysql_real_escape_string($_POST["member_address"]));
            $data[4]['name'] = 'city';
            $data[4]['value'] = ucwords(mysql_real_escape_string($_POST["member_city"]));
            $data[5]['name'] = 'province';
            $data[5]['value'] = mysql_real_escape_string($_POST["member_province"]);
            $data[6]['name'] = 'postal_code';
            $data[6]['value'] = strtoupper(mysql_real_escape_string($_POST["member_postal_code"]));
            $data[7]['name'] = 'neighborhood';
            $data[7]['value'] = ucwords(mysql_real_escape_string($_POST["member_neighborhood"]));
            $data[8]['name'] = 'mailing_address';
            if (!empty($_POST["member_mailing_address"])) {
                $data[8]['value'] = ucwords(mysql_real_escape_string($_POST["member_mailing_address"]));
            } else {
                $data[8]['value'] = ucwords(mysql_real_escape_string($_POST["member_address"]));
            }
            $data[9]['name'] = 'mailing_city';
            if (!empty($_POST["member_mailing_city"])) {
                $data[9]['value'] = ucwords(mysql_real_escape_string($_POST["member_mailing_city"]));
            } else {
                $data[9]['value'] = ucwords(mysql_real_escape_string($_POST["member_city"]));
            }
            $data[10]['name'] = 'mailing_province';
            $data[10]['value'] = mysql_real_escape_string($_POST["member_mailing_province"]);
            $data[11]['name'] = 'mailing_postal_code';
            if (!empty($_POST["member_mailing_postal_code"])) {
                $data[11]['value'] = strtoupper(mysql_real_escape_string($_POST["member_mailing_postal_code"]));
            } else {
                $data[11]['value'] = strtoupper(mysql_real_escape_string($_POST["member_postal_code"]));
            }
            $data[12]['name'] = 'home_phone_number';
            $data[12]['value'] = $fixed_home_phone_number;
            $data[13]['name'] = 'work_phone_number';
            $data[13]['value'] = $fixed_work_phone_number;
            $data[14]['name'] = 'mobile_phone_number';
            $data[14]['value'] = $fixed_mobile_phone_number;
            $data[15]['name'] = 'email_address';
            $data[15]['value'] = strtolower($_POST["member_email_address"]);
            $data[16]['name'] = 'member_profile';
            $data[16]['value'] = mysql_real_escape_string($_POST["member_member_profile"]);
            $data[17]['name'] = 'public_profile';
            $data[17]['value'] = mysql_real_escape_string($_POST["member_public_profile"]);
            $data[18]['name'] = 'public_profile_enabled';
            $data[18]['value'] = mysql_real_escape_string($_POST["member_public_profile_enabled"]);
            $data[19]['name'] = 'url';
            $data[19]['value'] = $fixed_member_url;
            $data[20]['name'] = 'receive_email_newletter';
            $data[20]['value'] = mysql_real_escape_string($_POST["member_receive_email_newletter"]);
            $data[21]['name'] = 'receive_email_events';
            $data[21]['value'] = mysql_real_escape_string($_POST["member_receive_email_events"]);
            $data[22]['name'] = 'receive_email_url';
            $data[22]['value'] = mysql_real_escape_string($_POST["member_receive_email_url"]);
            $data[23]['name'] = 'receive_email_faq';
            $data[23]['value'] = mysql_real_escape_string($_POST["member_receive_email_faq"]);
            $data[24]['name'] = 'receive_email_buy';
            $data[24]['value'] = mysql_real_escape_string($_POST["member_receive_email_buy"]);
            $data[25]['name'] = 'receive_email_sell';
            $data[25]['value'] = mysql_real_escape_string($_POST["member_receive_email_sell"]);
            $data[26]['name'] = 'receive_email_noticeboard';
            $data[26]['value'] = mysql_real_escape_string($_POST["member_receive_email_noticeboard"]);
            $data[27]['name'] = 'receive_newsletter';
            $data[27]['value'] = mysql_real_escape_string($_POST["member_receive_newsletter"]);
            $data[28]['name'] = 'receive_statement';
            $data[28]['value'] = mysql_real_escape_string($_POST["member_receive_statement"]);

            $data[29]['name'] = 'created_day';
            $data[29]['value'] = $date['day'];
            $data[30]['name'] = 'created_month';
            $data[30]['value'] = $date['month'];
            $data[31]['name'] = 'created_year';
            $data[31]['value'] = $date['year'];
            $data[32]['name'] = 'expiry_day';
            $data[32]['value'] = $date['day'];
            $data[33]['name'] = 'expiry_month';
            $data[33]['value'] = $date['month'];
            $data[34]['name'] = 'expiry_year';
            $data[34]['value'] = $date['year'] + 1;
            $data[35]['name'] = 'type';
            $data[35]['value'] = 1;
            $data[36]['name'] = 'receive_email_outbid';
            $data[36]['value'] = mysql_real_escape_string($_POST["member_receive_email_outbid"]);

            if (!VALIDATE_MEMBERS) {
                $data[37]['name'] = 'validated';
                $data[37]['value'] = 1;
            }

            if (!$mysql->insert_values('accounts',$data)) {
                $this->error_message = 'Database Error';
                if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: New '.ucwords(MEMBERS_NAME_SINGULAR).' Added');
                if (ENABLE_ERROR_LOG) log_error('FAILED: New '.ucwords(MEMBERS_NAME_SINGULAR).' Added<br />Error: '.$mysql->error);
                return false;
            } else {
                $this->id = $mysql->inserted_id;
                return true;
            }
        }
    }

    function edit($i) {
        if (!$this->confirm_data($i,"edit")) {
            return false;
        } else {
            if (!empty($_POST["member_home_area_code"])) {
                $fixed_home_phone_number = "(".$_POST["member_home_area_code"].") ".$_POST["member_home_phone_number_prefix"]."-".$_POST["member_home_phone_number"];
            } else {
                $fixed_home_phone_number = '';
            }
            if (!empty($_POST["member_work_area_code"])) {
                $fixed_work_phone_number = "(".$_POST["member_work_area_code"].") ".$_POST["member_work_phone_number_prefix"]."-".$_POST["member_work_phone_number"];
            } else {
                $fixed_work_phone_number = '';
            }
            if (!empty($_POST["member_mobile_area_code"])) {
                $fixed_mobile_phone_number = "(".$_POST["member_mobile_area_code"].") ".$_POST["member_mobile_phone_number_prefix"]."-".$_POST["member_mobile_phone_number"];
            } else {
                $fixed_mobile_phone_number = '';
            }

            if (!empty($_POST["member_url"])) {
                $fixed_member_url = strtolower($_POST["member_url"]);
                if (!strpos('  '.$fixed_member_url,"http://")) {
                    $fixed_member_url = "http://".$fixed_member_url;
                }
            } else {
                $fixed_member_url = '';
            }

            $edit_member_query = "UPDATE accounts SET ";
            $edit_member_query .= "first_name = '".ucwords(mysql_real_escape_string($_POST["member_first_name"]))."'";
            $edit_member_query .= ", last_name = '".ucwords(mysql_real_escape_string($_POST["member_last_name"]))."'";
            $edit_member_query .= ", password = '".crypt(md5($_POST["member_password"]),md5(SITE_KEY))."'";
            $edit_member_query .= ", address = '".ucwords(mysql_real_escape_string($_POST["member_address"]))."'";
            $edit_member_query .= ", city = '".ucwords(mysql_real_escape_string($_POST["member_city"]))."'";
            $edit_member_query .= ", province = '".mysql_real_escape_string($_POST["member_province"])."'";
            $edit_member_query .= ", postal_code = '".strtoupper(mysql_real_escape_string($_POST["member_postal_code"]))."'";
            $edit_member_query .= ", neighborhood = '".ucwords(mysql_real_escape_string($_POST["member_neighborhood"]))."'";
            $edit_member_query .= ", mailing_address = '".ucwords(mysql_real_escape_string($_POST["member_mailing_address"]))."'";
            $edit_member_query .= ", mailing_city = '".ucwords(mysql_real_escape_string($_POST["member_mailing_city"]))."'";
            $edit_member_query .= ", mailing_province = '".mysql_real_escape_string($_POST["member_mailing_province"])."'";
            $edit_member_query .= ", mailing_postal_code = '".strtoupper(mysql_real_escape_string($_POST["member_mailing_postal_code"]))."'";
            $edit_member_query .= ", home_phone_number = '".$fixed_home_phone_number."'";
            $edit_member_query .= ", work_phone_number = '".$fixed_work_phone_number."'";
            $edit_member_query .= ", mobile_phone_number = '".$fixed_mobile_phone_number."'";
            $edit_member_query .= ", email_address = '".strtolower(mysql_real_escape_string($_POST["member_email_address"]))."'";
            $edit_member_query .= ", member_profile = '".mysql_real_escape_string($_POST["member_member_profile"])."'";
            $edit_member_query .= ", public_profile = '".mysql_real_escape_string($_POST["member_public_profile"])."'";
            if ($_POST["member_public_profile_enabled"] != 1) {
                $edit_member_query .= ", public_profile_enabled = '0'";
            } else {
                $edit_member_query .= ", public_profile_enabled = '1'";
            }
            $edit_member_query .= ", url = '".$fixed_member_url."'";
            if ($_POST["member_receive_email_newletter"] != 1) {
                $edit_member_query .= ", receive_email_newletter = '0'";
            } else {
                $edit_member_query .= ", receive_email_newletter = '1'";
            }
            if ($_POST["member_receive_email_events"] != 1) {
                $edit_member_query .= ", receive_email_events = '0'";
            } else {
                $edit_member_query .= ", receive_email_events = '1'";
            }
            if ($_POST["member_receive_email_url"] != 1) {
                $edit_member_query .= ", receive_email_url = '0'";
            } else {
                $edit_member_query .= ", receive_email_url = '1'";
            }
            if ($_POST["member_receive_email_faq"] != 1) {
                $edit_member_query .= ", receive_email_faq = '0'";
            } else {
                $edit_member_query .= ", receive_email_faq = '1'";
            }
            if ($_POST["member_receive_email_buy"] != 1) {
                $edit_member_query .= ", receive_email_buy = '0'";
            } else {
                $edit_member_query .= ", receive_email_buy = '1'";
            }
            if ($_POST["member_receive_email_sell"] != 1) {
                $edit_member_query .= ", receive_email_sell = '0'";
            } else {
                $edit_member_query .= ", receive_email_sell = '1'";
            }
            if ($_POST["member_receive_email_noticeboard"] != 1) {
                $edit_member_query .= ", receive_email_noticeboard = '0'";
            } else {
                $edit_member_query .= ", receive_email_noticeboard = '1'";
            }
            if ($_POST["member_receive_email_outbid"] != 1) {
                $edit_member_query .= ", receive_email_outbid = '0'";
            } else {
                $edit_member_query .= ", receive_email_outbid = '1'";
            }
            if ($_POST["member_receive_newsletter"] != 1) {
                $edit_member_query .= ", receive_newsletter = '0'";
            } else {
                $edit_member_query .= ", receive_newsletter = '1'";
            }
            if ($_POST["member_receive_statement"] != 1) {
                $edit_member_query .= ", receive_statement = '0'";
            } else {
                $edit_member_query .= ", receive_statement = '1'";
            }
            $edit_member_query .= " WHERE accountID = ".$this->id;
            if (!mysql_query($edit_member_query)) {
                $this->error_message = "Database Error";
                if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('Failed to edit '.strtolower(MEMBERS_NAME_SINGULAR).' data in member.class.php');
                if (ENABLE_ERROR_LOG) log_error('Failed to edit '.strtolower(MEMBERS_NAME_SINGULAR).' data in member.class.php<br />Error:'.mysql_error());
                return false;
            } else {
                if (ENABLE_LOG) log_action(ucwords(MEMBERS_NAME_SINGULAR).' ID:'.$this->id.' ('.$this->full_name($this->id).') Edited.');
                return true;
            }
        }
    }

    function build_dataset($id) {
        $mysql = new mysql;
        if (!$mysql->result('SELECT * FROM accounts WHERE accountID = '.$id.' LIMIT 1')) {
            return ucwords(MEMBERS_NAME_SINGULAR)." ".$id_number." does not exist";
        }
        $this->id = $id;
        $this->created_day                      =           $mysql->result['created_day'];
        $this->created_month                    =           $mysql->result['created_month'];
        $this->created_year                     =           $mysql->result['created_year'];
        $this->ll_day                           =           $mysql->result['ll_day'];
        $this->ll_month                         =           $mysql->result['ll_month'];
        $this->ll_year                          =           $mysql->result['ll_year'];
        $this->ll_hour                          =           $mysql->result['ll_hour'];
        $this->expiry_day                       =           $mysql->result['expiry_day'];
        $this->expiry_month                     =           $mysql->result['expiry_month'];
        $this->expiry_year                      =           $mysql->result['expiry_year'];
        $this->balance                          =           $mysql->result['balance'];
        $this->image_id                         =           $mysql->result['imageID'];
        $this->type                             =           $mysql->result['type'];
        $this->validated                        =           $mysql->result['validated'];
        $this->suspended                        =           $mysql->result['suspended'];
        $this->suspended_message                =           $mysql->result['suspended_message'];
        $this->deleted                          =           $mysql->result['deleted'];
        $this->deleted_transaction_id           =           $mysql->result['deleted_transactionID'];
        $this->first_name                       =           $mysql->result['first_name'];
        $this->last_name                        =           $mysql->result['last_name'];
        $this->address                          =           $mysql->result['address'];
        $this->city                             =           $mysql->result['city'];
        $this->province                         =           $mysql->result['province'];
        $this->postal_code                      =           $mysql->result['postal_code'];
        $this->neighborhood                     =           $mysql->result['neighborhood'];
        $this->mailing_address                  =           $mysql->result['mailing_address'];
        $this->mailing_city                     =           $mysql->result['mailing_city'];
        $this->mailing_province                 =           $mysql->result['mailing_province'];
        $this->mailing_postal_code              =           $mysql->result['mailing_postal_code'];
        $this->home_phone_number                =           $mysql->result['home_phone_number'];
        $this->work_phone_number                =           $mysql->result['work_phone_number'];
        $this->mobile_phone_number              =           $mysql->result['mobile_phone_number'];
        $this->email_address                    =           $mysql->result['email_address'];
        $this->member_profile                   =           $mysql->result['member_profile'];
        $this->public_profile                   =           $mysql->result['public_profile'];
        $this->public_profile_enabled           =           $mysql->result['public_profile_enabled'];
        $this->url                              =           $mysql->result['url'];
        $this->receive_email_newletter          =           $mysql->result['receive_email_newletter'];
        $this->receive_email_events             =           $mysql->result['receive_email_events'];
        $this->receive_email_url                =           $mysql->result['receive_email_url'];
        $this->receive_email_faq                =           $mysql->result['receive_email_faq'];
        $this->receive_email_buy                =           $mysql->result['receive_email_buy'];
        $this->receive_email_sell               =           $mysql->result['receive_email_sell'];
        $this->receive_email_noticeboard        =           $mysql->result['receive_email_noticeboard'];
        $this->receive_email_outbid             =           $mysql->result['receive_email_outbid'];
        $this->receive_newsletter               =           $mysql->result['receive_newsletter'];
        $this->receive_statement                =           $mysql->result['receive_statement'];
        return true;
    }

    function rebuild_form() {
        $this->date_created                     =           remove_slashes($_POST["member_date_created"]);
        $this->expiry_date                      =           remove_slashes($_POST["member_expiry_date"]);
        $this->first_name                       =           remove_bad_tags(remove_slashes($_POST["member_first_name"]));
        $this->last_name                        =           remove_bad_tags(remove_slashes($_POST["member_last_name"]));
        $this->address                          =           remove_bad_tags(remove_slashes($_POST["member_address"]));
        $this->city                             =           remove_bad_tags(remove_slashes($_POST["member_city"]));
        $this->province                         =           remove_bad_tags(remove_slashes($_POST["member_province"]));
        $this->postal_code                      =           remove_bad_tags(remove_slashes($_POST["member_postal_code"]));
        $this->neighborhood                     =           remove_bad_tags(remove_slashes($_POST["member_neighborhood"]));
        $this->mailing_address                  =           remove_bad_tags(remove_slashes($_POST["member_mailing_address"]));
        $this->mailing_city                     =           remove_bad_tags(remove_slashes($_POST["member_mailing_city"]));
        $this->mailing_province                 =           remove_bad_tags(remove_slashes($_POST["member_mailing_province"]));
        $this->mailing_postal_code              =           remove_bad_tags(remove_slashes($_POST["member_mailing_postal_code"]));
        $this->home_phone_number                =           remove_slashes($_POST["member_home_phone_number"]);
        $this->work_phone_number                =           remove_slashes($_POST["member_work_phone_number"]);
        $this->mobile_phone_number              =           remove_slashes($_POST["member_mobile_phone_number"]);
        $this->home_phone_number_prefix         =           remove_slashes($_POST["member_home_phone_number_prefix"]);
        $this->work_phone_number_prefix         =           remove_slashes($_POST["member_work_phone_number_prefix"]);
        $this->mobile_phone_number_prefix       =           remove_slashes($_POST["member_mobile_phone_number_prefix"]);
        $this->home_area_code                   =           remove_slashes($_POST["member_home_area_code"]);
        $this->work_area_code                   =           remove_slashes($_POST["member_work_area_code"]);
        $this->mobile_area_code                 =           remove_slashes($_POST["member_mobile_area_code"]);
        $this->email_address                    =           remove_slashes($_POST["member_email_address"]);
        $this->member_profile                   =           remove_bad_tags(remove_slashes($_POST["member_member_profile"]));
        $this->public_profile                   =           remove_bad_tags(remove_slashes($_POST["member_public_profile"]));
        $this->public_profile_enabled           =           remove_slashes($_POST["member_public_profile_enabled"]);
        $this->url                              =           strtolower(remove_slashes($_POST["member_url"]));
        if (!strpos(' '.$this->url,'http://') and $this->url) {
            $this->url                          =           'http://'.$this->url;
        }
        $this->receive_email_newletter          =           remove_slashes($_POST["member_receive_email_newletter"]);
        $this->receive_email_events             =           remove_slashes($_POST["member_receive_email_events"]);
        $this->receive_email_url                =           remove_slashes($_POST["member_receive_email_url"]);
        $this->receive_email_faq                =           remove_slashes($_POST["member_receive_email_faq"]);
        $this->receive_email_buy                =           remove_slashes($_POST["member_receive_email_buy"]);
        $this->receive_email_sell               =           remove_slashes($_POST["member_receive_email_sell"]);
        $this->receive_email_noticeboard        =           remove_slashes($_POST["member_receive_email_noticeboard"]);
        $this->receive_email_outbid             =           remove_slashes($_POST["member_receive_email_outbid"]);
        $this->receive_newsletter               =           remove_slashes($_POST["member_receive_newsletter"]);
        $this->receive_statement                =           remove_slashes($_POST["member_receive_statement"]);
    }

    function set_default_image($id,$image_id) {
        $mysql = new mysql;
        if (!$mysql->query('UPDATE accounts SET imageID = '.$image_id.' WHERE accountID = '.$id.' LIMIT 1')) {
            $this->error = $mysql->error;
            return false;
        }
        return true;
    }

    function add_selected($var,$value,$default) {
        if (empty($var)) {
            if ($default) {
                return " selected=\"selected\"";
            } else {
                return "";
            }
        } else {
            if ($var == $value) {
                return " selected=\"selected\"";
            } else {
                return "";
            }
        }
    }
    function add_checkbox($type,$error,$var,$default) {
        /* If error is true the form is being called a second time and
            and we won't worry about default */

        if ($type == "add" and !$error) {
            if ($default) {
                return " checked=\"checked\"";
            } else {
                return "";
            }
        } else {
            if (empty($var)) {
                return "";
            } else {
                return " checked=\"checked\"";
            }
        }
    }
    function admin_tools_html($i) {
        global $links;
        $z = $i."<!-- admin_tools -->\n";
        $z .= $i."<div id=\"admin_tools\">\n";
        $z .= $i."<fieldset>\n";
        $z .= $i."<legend>\n";
        $z .= $i."Admin Tools\n";
        $z .= $i."</legend>\n";
        $z .= $i." <form name=\"validate_member\" method=\"post\" action=\"".URL.MEMBER_LIST_URL.'/'.$this->id.'/'.append_url()."\">\n";

        $validate_button = $i."  <input id=\"admin_validate_button\" type=\"submit\" name=\"submit\" value=\"Validate ".ucwords(MEMBERS_NAME_SINGULAR)."\" />\n";
        $delete_button = $i."  <input id=\"admin_delete_button\" type=\"submit\" name=\"submit\" value=\"Delete ".ucwords(MEMBERS_NAME_SINGULAR)."\" />\n";
        $restore_button = $i."  <input id=\"admin_restore_button\" type=\"submit\" name=\"submit\" value=\"Restore ".ucwords(MEMBERS_NAME_SINGULAR)."\" />\n";
        $reactivate_button = $i."  <input id=\"admin_reactivate_button\" type=\"submit\" name=\"submit\" value=\"Reactivate ".ucwords(MEMBERS_NAME_SINGULAR)."\" />\n";
        $suspend_button = $i."  <input id=\"admin_suspend_button\" type=\"submit\" name=\"submit\" value=\"Suspend ".ucwords(MEMBERS_NAME_SINGULAR)."\" />\n";
        $expiry_button = $i."  <input id=\"admin_expiry_button\" type=\"submit\" name=\"submit\" value=\"Update Expiry\" />\n";

        if ($links->build_url(1,2)) {
            $edit_link = '<a href="'.URL.$links->complete_url.$this->id.'/'.append_url(0).'">edit</a>';
        }
        if ($links->build_url(1,3)) {
            $transaction_link = '<a href="'.URL.$links->complete_url.$this->id.'/'.append_url(0).'">view history</a>';
        }
        $admin_links = ' ('.$edit_link.' or '.$transaction_link.')';
        if ($this->deleted and !$this->validated) {
            $z .= $i.' <span class="admin_tools_status">This '.strtolower(MEMBERS_NAME_SINGULAR)." was deleted before being validated</span>".$admin_links."<br /><br />\n";
            $z .= $restore_button;
        }elseif (!$this->validated and !$this->deleted) {
            $z .= $i.'  <span class="admin_tools_status">This '.strtolower(MEMBERS_NAME_SINGULAR)." has not been validated</span>".$admin_links."<br /><br />\n";
            $z .= $i."  Validate with expiry date:\n";
            $z .= date_form($i."  ",array('day' => $GLOBALS['date']['day'], 'month' => $GLOBALS['date']['month'], 'year' => ($GLOBALS['date']['year'] + 1)),"expiry_");
            $z .= $validate_button;
            $z .= $delete_button;
        } elseif ($this->suspended and !$this->deleted) {
            $z .= $i.'  <span class="admin_tools_status">This '.strtolower(MEMBERS_NAME_SINGULAR)." has been suspended</span>".$admin_links."<br /><br />\n";
            $z .= $i."  Restore with expiry date:\n";
            $z .= date_form($i."  ",array('day' => $this->expiry_day, 'month' => $this->expiry_month, 'year' => ($this->expiry_year + 1)),"expiry_");
            $z .= $restore_button;
            $z .= $delete_button;
        } elseif ($this->suspended and $this->deleted) {
            $z .= $i.' <span class="admin_tools_status">This '.strtolower(MEMBERS_NAME_SINGULAR)." has been deleted</span>".$admin_links."<br /><br />\n";
            $mysql = new mysql;
            $restore_amount = '';
            $balance = '';
            if ($mysql->result('SELECT * FROM transactions WHERE transactionID = '.$this->deleted_transaction_id.' LIMIT 1')) {
                $restore_amount = $mysql->result['amount'];
                if ($mysql->result['buyerID'] == $this->id) {
                    $balance = 'positive ';
                } else {
                    $balance = 'negative ';
                }
                $z .= $i."  This ".strtolower(MEMBERS_NAME_SINGULAR)." was deleted with a ".$balance."balance of ".number_format($restore_amount,2)." ".ucwords(CURRENCY_NAME)."<br class=\"left\" />\n";
            }

            $z .= $i."  Reactivate ".strtolower(MEMBERS_NAME_SINGULAR)." with expiry date:\n";
            $z .= date_form($i."  ",array('day' => $this->expiry_day, 'month' => $this->expiry_month, 'year' => ($this->expiry_year + 1)),"expiry_");
            if (ENABLE_NOTICEBOARD) {
                $z .= $i."  and a ".$balance." balance of:\n";
                $z .= $i."  <input type=\"text\" name=\"amount\" id=\"amount\" value=\"".$restore_amount."\" />\n";
            } else {
                $z .= "\n";
            }
            $z .= $reactivate_button;
        } elseif (($this->expiry_year < $GLOBALS['date']['year']) or ($this->expiry_year == $GLOBALS['date']['year'] and $this->expiry_month < $GLOBALS['date']['month'])
                    or ($this->expiry_year == $GLOBALS['date']['year'] and $this->expiry_month == $GLOBALS['date']['month'] and $this->expiry_day < $GLOBALS['date']['day'])) {
            if ($this->id != 1) {
                $z .= $i.' <span class="admin_tools_status">This '.strtolower(MEMBERS_NAME_SINGULAR)." has expired</span>".$admin_links."<br /><br />\n";
            } else {
                $z .= $i.' <span class="admin_tools_status">The number 1 account has expired... That shouldn\'t happen! :)</span>'.$admin_links."<br /><br />\n";
            }
            $z .= $i." Update expiry date to:\n";
            $z .= date_form($i."  ",array('day' => $this->expiry_day, 'month' => $this->expiry_month, 'year' => ($this->expiry_year + 1)),"expiry_");
            $z .= $expiry_button;
            if (user_type() != 1) {
                $z .= $i."  <br class=\"left\" />Suspend or Delete with reason:\n";
                $z .= $i."  <input type=\"text\" name=\"suspended_message\" id=\"suspended_message\" /><br />\n";
                $z .= $suspend_button;
                $z .= $delete_button;
                $z .= $i."  <br class=\"left\" />\n";
            }
        } else {
            $z .= $i."  <span class=\"admin_tools_status\">This account's status is normal</span>".$admin_links."<br /><br />\n";
            if ($this->id != 1) {
                $z .= $i."  Suspend or Delete with reason:\n";
                $z .= $i."  <input type=\"text\" name=\"suspended_message\" id=\"suspended_message\" /><br />\n";
                $z .= $suspend_button;
                $z .= $delete_button;
                $z .= $i."  <br class=\"left\" />\n";
            }
            $z .= $i."  Update expiry date to:\n";
            $z .= date_form($i."  ",array('day' => $this->expiry_day, 'month' => $this->expiry_month, 'year' => ($this->expiry_year + 1)),"expiry_");
            $z .= $expiry_button;
        }
        $z .= $i." </form>\n";
        $z .= $i."</div>\n";
        $z .= $i."<!-- /admin_tools -->\n";
        return $z;
    }
    function display_info($i,$additional_html,$type) {
        $image = new images;
        $mysql = new mysql;

        $z = '';
        $z .= $i."<!-- member_display_info -->\n";
        if ($type == "private" or $type == "admin") {

            $z .= $i."<div id=\"member_profile_details\">\n";
            $z .= $i." Profile of <span class=\"member_info_name\">".$this->first_name." ".$this->last_name."</span><br /><br />\n";
            if (ENABLE_NOTICEBOARD) {
                $z .= $i." Current balance: <strong>".number_format($this->balance,2)."</strong><br /><br />\n";
            }
            if (!MEMBER_EXPIRY_HIDDEN or user_type() == 2 or $this->id == $_SESSION['member_id']) {
                $z .= $i." <span class=\"member_info_heading\">Expires:</span><span class=\"member_info_value\">".return_month($this->expiry_month).' '.$this->expiry_day.', '.$this->expiry_year."</span><br class=\"left\" /><br class=\"left\" />\n";
            }

            if (!empty($this->address)) {
                $z .= $i." <span class=\"member_info_heading\">Address:</span><span class=\"member_info_value\">".$this->address."</span><br class=\"left\" />\n";
            }
            if (!empty($this->city)) {
                $z .= $i." <span class=\"member_info_heading\">City:</span><span class=\"member_info_value\">".$this->city."</span><br class=\"left\" />\n";
            }
            if (!empty($this->province)) {
                $z .= $i." <span class=\"member_info_heading\">Province:</span><span class=\"member_info_value\">".$this->province."</span><br class=\"left\" />\n";
            }
            if (!empty($this->postal_code)) {
                $z .= $i." <span class=\"member_info_heading\">Postal Code :</span><span class=\"member_info_value\">".$this->postal_code."</span><br class=\"left\" />\n";
            }
            if (!empty($this->neighborhood)) {
                $z .= $i." <span class=\"member_info_heading\">Neighborhood:</span><span class=\"member_info_value\">".$this->neighborhood."</span><br class=\"left\" />\n";
            }
            if (!empty($this->mailing_address)) {
                $z .= $i." <span class=\"member_info_heading\">Mailing address:</span><span class=\"member_info_value\">".$this->mailing_address."</span><br class=\"left\" />\n";
            }
            if (!empty($this->mailing_city)) {
                $z .= $i." <span class=\"member_info_heading\">Mailing City:</span><span class=\"member_info_value\">".$this->mailing_city."</span><br class=\"left\" />\n";
            }
            if (!empty($this->mailing_province)) {
                $z .= $i." <span class=\"member_info_heading\">Mailing Province:</span><span class=\"member_info_value\">".$this->mailing_province."</span><br class=\"left\" />\n";
            }
            if (!empty($this->mailing_postal_code)) {
                $z .= $i." <span class=\"member_info_heading\">Mailing Postal Code:</span><span class=\"member_info_value\">".$this->mailing_postal_code."</span><br class=\"left\" />\n";
            }
            if (!empty($this->home_phone_number)) {
                $z .= $i." <span class=\"member_info_heading\">Home phone:</span><span class=\"member_info_value\">".$this->home_phone_number."</span><br class=\"left\" />\n";
            }
            if (!empty($this->work_phone_number)) {
                $z .= $i." <span class=\"member_info_heading\">Work phone:</span><span class=\"member_info_value\">".$this->work_phone_number."</span><br class=\"left\" />\n";
            }
            if (!empty($this->mobile_phone_number)) {
                $z .= $i." <span class=\"member_info_heading\">Mobile phone:</span><span class=\"member_info_value\">".$this->mobile_phone_number."</span><br class=\"left\" />\n";
            }
            if (!empty($this->email_address)) {
                $z .= $i." <span class=\"member_info_heading\">email:</span><span class=\"member_info_value\"><a href=\"mailto:".$this->email_address."\">".$this->email_address."</a></span><br class=\"left\" />\n";
            }
            if (!empty($this->url)) {
                $z .= $i." <span class=\"member_info_heading\">Webpage:</span><span class=\"member_info_value\"><a href=\"".$this->url."\" target=\"_blank\">".$this->url."</a></span><br class=\"left\" />\n";
            }


            if (!empty($this->date_created)) {
                $z .= $i." <span class=\"member_info_heading\">".ucwords(MEMBERS_NAME_SINGULAR)." since:</span><span class=\"member_info_value\">".format_date($this->date_created)."</span><br class=\"left\" />\n";
            }
            $z .= $i." <br />\n";


        } elseif ($type == "public") {
            if (!empty($this->neighborhood)) {
                $z .= $i." <span class=\"member_info_heading\">Neighborhood:</span><span class=\"member_info_value\">".$this->neighborhood."</span><br class=\"left\" />\n";
            }
            if (!empty($this->city)) {
                $z .= $i." <span class=\"member_info_heading\">City:</span><span class=\"member_info_value\">".$this->city."</span><br class=\"left\" />\n";
            }
        }
        if (!empty($additional_html)) {
            $z .= $i.' <br />'."\n";
            $z .= $additional_html;
        }
        $z .= $i."</div>\n";
        $z .= $i."<!-- /member_display_info -->\n";
        return $z;
    }

    function register_html($type,$error,$i) {
        $this->set_required_variables();
        global $links;
        $mysql = new mysql;
        // $type should be "add" or "edit"

        /* If this function is called a second time because of errors
            the phone numbers should be brought back from POST data
            otherwise they are built from the database and need to be
            seperated from their (555) 555-555 format */

        if (ENABLE_IMAGES) global $image;

        if (!$error) {
            if (!empty($this->home_phone_number)) {
                $this->home_area_code = substr($this->home_phone_number,1,3);
                $this->home_phone_number_prefix = substr($this->home_phone_number,6,3);
                $this->home_phone_number = substr($this->home_phone_number,10,4);
            }
            if (!empty($this->work_phone_number)) {
                $this->work_area_code = substr($this->work_phone_number,1,3);
                $this->work_phone_number_prefix = substr($this->work_phone_number,6,3);
                $this->work_phone_number = substr($this->work_phone_number,10,4);
            }
            if (!empty($this->mobile_phone_number)) {
                $this->mobile_area_code = substr($this->mobile_phone_number,1,3);
                $this->mobile_phone_number_prefix = substr($this->mobile_phone_number,6,3);
                $this->mobile_phone_number = substr($this->mobile_phone_number,10,4);
            }
        }
        $z = '';
        $z .= $i."<!-- member_form -->\n";
        $z .= $i."<div id=\"member_form\">\n";
        $links->build_url(1,2);
        if (user_type() == 2) {
            $z .= $i."<fieldset>\n";
            $z .= $i.'<form enctype="multipart/form-data" id="register" action="'.URL.$links->complete_url.append_url(0)."\" method=\"post\">\n";
            $z .= $i." Enter ".ucwords(MEMBERS_NAME_SINGULAR)." ID to edit <input type=\"text\" id=\"member_id_to_edit\" name=\"member_id_to_edit\" maxlength=\"32\" value=\"".$this->id."\" />\n";
            $z .= $i." <input id=\"find_member_button\" type=\"submit\" name=\"submit\" value=\"Find ".ucwords(MEMBERS_NAME_SINGULAR)."\" />\n";
            $z .= $i."</form>\n";
            $z .= $i."</fieldset><br />\n";
            if ($this->id != $_SESSION['member_id']) {
                $z .= $i."This is <strong>NOT</strong> your account.<br />Click <a href=\"".URL.$links->complete_url.append_url(0)."\">here</a> to edit your own account<br /><br />\n";
            }
        }
        $z .= $i."<fieldset>\n";
        $z .= $i."<legend>\n";
        $z .= $i.ucwords(MEMBERS_NAME_SINGULAR)." Information\n";
        $z .= $i."</legend>\n";
        $z .= $i."Required fields are <span class=\"required_field\">".REQUIRED_DISPLAY."</span>.<br /><br />\n";
        $z .= $i.'<form enctype="multipart/form-data" id="register" action="'.URL;
        $image_action = '';
        if ($type == "add") {
            if ($links->build_url(1,1)) {
                $z .= $links->complete_url.append_url()."\" method=\"post\">\n";
                $image_action = $links->complete_url;
            }
        } elseif ($type == "edit") {
            if ($links->build_url(1,2)) {
                if ($this->id != $_SESSION['member_id'] and user_type() == 2) {
                    $z .= $links->complete_url.$this->id.'/'.append_url()."\" method=\"post\">\n";
                    $image_action = $links->complete_url.$this->id.'/';
                } else {
                    $z .= $links->complete_url.append_url()."\" method=\"post\">\n";
                    $image_action = $links->complete_url;
                }
            }
        }
        $z .= "$i <label for=\"member_first_name\" id=\"member_first_name_label\">".$this->check_required($this->first_name_required,1)."First Name:".$this->check_required($this->first_name_required,2)."</label>\n";
        $z .= "$i <input type=\"text\" id=\"member_first_name\" name=\"member_first_name\" maxlength=\"32\" value=\"".htmlspecialchars($this->first_name)."\" /><br class=\"left\" />\n";
        $z .= "$i <label for=\"member_last_name\">".$this->check_required($this->last_name_required,1)."Last Name:".$this->check_required($this->last_name_required,2)."</label>\n";
        $z .= "$i <input type=\"text\" id=\"member_last_name\" name=\"member_last_name\" maxlength=\"32\" value=\"".htmlspecialchars($this->last_name)."\" /><br class=\"left\" />\n";
        $z .= "$i <label for=\"member_password\">".$this->check_required($this->password_required,1)."Password:".$this->check_required($this->password_required,2);
        if ($type == "edit") {
            $z .= "<br class=\"left\" />(Leave blank to keep the same)";
        }
        $z .= "</label>\n";
        $z .= "$i <input type=\"password\" id=\"member_password\" name=\"member_password\" maxlength=\"32\" /><br class=\"left\" />\n";
        $z .= "$i <label for=\"member_confirmed_password\">".$this->check_required($this->confirmed_password_required,1)."Re-type Password:".$this->check_required($this->confirmed_password_required,2)."</label>\n";
        $z .= "$i <input type=\"password\" id=\"member_confirmed_password\" name=\"member_confirmed_password\" maxlength=\"32\" /><br class=\"left\" />\n";
        $z .= "$i <label for=\"member_address\">".$this->check_required($this->address_required,1)."Street Address:".$this->check_required($this->address_required,2)."</label>\n";
        $z .= "$i <input type=\"text\" id=\"member_address\" name=\"member_address\" maxlength=\"32\" value=\"".htmlspecialchars($this->address)."\" /><br class=\"left\" />\n";
        $z .= "$i <label for=\"member_city\">".$this->check_required($this->city_required,1)."City:</label>".$this->check_required($this->city_required,2)."\n";
        $z .= "$i <input type=\"text\" id=\"member_city\" name=\"member_city\" maxlength=\"32\" value=\"".htmlspecialchars($this->city)."\" /><br class=\"left\" />\n";
        if (CANADIAN) {
            $z .= "$i <label for=\"member_province\">".$this->check_required($this->province_required,1)."Province:</label>".$this->check_required($this->province_required,2)."\n";
            $z .= "$i <select id=\"member_province\" name=\"member_province\">\n";
            // Select default with $this->add_selected($this->province,"<province>",1)
            $z .= "$i  <option value=\"AB\"".$this->add_selected($this->province,"AB",0).">Alberta</option>\n";
            $z .= "$i  <option value=\"BC\"".$this->add_selected($this->province,"BC",1).">British Columbia</option>\n";
            $z .= "$i  <option value=\"MB\"".$this->add_selected($this->province,"MB",0).">Manitoba</option>\n";
            $z .= "$i  <option value=\"NB\"".$this->add_selected($this->province,"NB",0).">New Brunswick</option>\n";
            $z .= "$i  <option value=\"NL\"".$this->add_selected($this->province,"NL",0).">Newfoundland and Labrador</option>\n";
            $z .= "$i  <option value=\"NT\"".$this->add_selected($this->province,"NT",0).">Northwest Territories</option>\n";
            $z .= "$i  <option value=\"NS\"".$this->add_selected($this->province,"NS",0).">Nova Scotia</option>\n";
            $z .= "$i  <option value=\"NU\"".$this->add_selected($this->province,"NU",0).">Nunavut</option>\n";
            $z .= "$i  <option value=\"ON\"".$this->add_selected($this->province,"ON",0).">Ontario</option>\n";
            $z .= "$i  <option value=\"PE\"".$this->add_selected($this->province,"PE",0).">Prince Edward Island</option>\n";
            $z .= "$i  <option value=\"QC\"".$this->add_selected($this->province,"QC",0).">Quebec</option>\n";
            $z .= "$i  <option value=\"SK\"".$this->add_selected($this->province,"SK",0).">Saskatchewan</option>\n";
            $z .= "$i  <option value=\"YT\"".$this->add_selected($this->province,"YT",0).">Yukon</option>\n";
            $z .= "$i </select><br class=\"left\" />\n";
            $z .= "$i <label for=\"member_postal_code\">".$this->check_required($this->postal_code_required,1)."Postal Code:".$this->check_required($this->postal_code_required,2)."</label>\n";
            $z .= "$i <input type=\"text\" id=\"member_postal_code\" name=\"member_postal_code\" maxlength=\"6\" value=\"".htmlspecialchars($this->postal_code)."\" /><br class=\"left\" />\n";
        } else {
            $z .= "$i <label for=\"member_province\">".$this->check_required($this->province_required,1)."Province or State:</label>".$this->check_required($this->province_required,2)."\n";
            $z .= "$i <input type=\"text\" id=\"member_province\" name=\"member_province\" maxlength=\"50\" value=\"".htmlspecialchars($this->province)."\" /><br class=\"left\" />\n";
            $z .= "$i <label for=\"member_postal_code\">".$this->check_required($this->postal_code_required,1)."Postal/Zip Code:".$this->check_required($this->postal_code_required,2)."</label>\n";
            $z .= "$i <input type=\"text\" id=\"member_postal_code\" name=\"member_postal_code\" maxlength=\"50\" value=\"".htmlspecialchars($this->postal_code)."\" /><br class=\"left\" />\n";
        }
        $z .= "$i <label for=\"member_mailing_address\">Mailing Address <br class=\"left\" />(Leave blank to use street address):</label>\n";
        $z .= "$i <input type=\"text\" id=\"member_mailing_address\" name=\"member_mailing_address\" maxlength=\"32\" value=\"".htmlspecialchars($this->mailing_address)."\" /><br class=\"left\" />\n";
        $z .= "$i <label for=\"member_mailing_city\">City:</label>\n";
        $z .= "$i <input type=\"text\" id=\"member_mailing_city\" name=\"member_mailing_city\" maxlength=\"32\" value=\"".htmlspecialchars($this->mailing_city)."\" /><br class=\"left\" />\n";
        if (CANADIAN) {
            $z .= "$i <label for=\"member_mailing_province\">Province:</label>\n";
            $z .= "$i <select id=\"member_mailing_province\" name=\"member_mailing_province\">\n";
            $z .= "$i  <option value=\"AB\"".$this->add_selected($this->mailing_province,"AB",0).">Alberta</option>\n";
            $z .= "$i  <option value=\"BC\"".$this->add_selected($this->mailing_province,"BC",1).">British Columbia</option>\n";
            $z .= "$i  <option value=\"MB\"".$this->add_selected($this->mailing_province,"MB",0).">Manitoba</option>\n";
            $z .= "$i  <option value=\"NB\"".$this->add_selected($this->mailing_province,"NB",0).">New Brunswick</option>\n";
            $z .= "$i  <option value=\"NL\"".$this->add_selected($this->mailing_province,"NL",0).">Newfoundland and Labrador</option>\n";
            $z .= "$i  <option value=\"NT\"".$this->add_selected($this->mailing_province,"NT",0).">Northwest Territories</option>\n";
            $z .= "$i  <option value=\"NS\"".$this->add_selected($this->mailing_province,"NS",0).">Nova Scotia</option>\n";
            $z .= "$i  <option value=\"NU\"".$this->add_selected($this->mailing_province,"NU",0).">Nunavut</option>\n";
            $z .= "$i  <option value=\"ON\"".$this->add_selected($this->mailing_province,"ON",0).">Ontario</option>\n";
            $z .= "$i  <option value=\"PE\"".$this->add_selected($this->mailing_province,"PE",0).">Prince Edward Island</option>\n";
            $z .= "$i  <option value=\"QC\"".$this->add_selected($this->mailing_province,"QC",0).">Quebec</option>\n";
            $z .= "$i  <option value=\"SK\"".$this->add_selected($this->mailing_province,"SK",0).">Saskatchewan</option>\n";
            $z .= "$i  <option value=\"YT\"".$this->add_selected($this->mailing_province,"YT",0).">Yukon</option>\n";
            $z .= "$i </select><br class=\"left\" />\n";
            $z .= "$i <label for=\"member_mailing_postal_code\">Postal Code:</label>\n";
            $z .= "$i <input type=\"text\" id=\"member_mailing_postal_code\" name=\"member_mailing_postal_code\" maxlength=\"6\" value=\"".htmlspecialchars($this->mailing_postal_code)."\" /><br class=\"left\" />\n";
        } else {
            $z .= "$i <label for=\"member_mailing_province\">Province or State:</label>\n";
            $z .= "$i <input type=\"text\" id=\"mailing_member_province\" name=\"mailing_member_province\" maxlength=\"50\" value=\"".htmlspecialchars($this->mailing_province)."\" /><br class=\"left\" />\n";
            $z .= "$i <label for=\"member_mailing_postal_code\">Postal/Zip Code:</label>\n";
            $z .= "$i <input type=\"text\" id=\"member_mailing_postal_code\" name=\"member_mailing_postal_code\" maxlength=\"50\" value=\"".htmlspecialchars($this->mailing_postal_code)."\" /><br class=\"left\" />\n";
        }
        $z .= "$i <label for=\"member_neighborhood\">".$this->check_required($this->neighborhood_required,1)."Neighborhood:".$this->check_required($this->neighborhood_required,2)."</label>\n";
        $z .= "$i <input type=\"text\" id=\"member_neighborhood\" name=\"member_neighborhood\" maxlength=\"32\" value=\"".htmlspecialchars($this->neighborhood)."\" /><br class=\"left\" />\n";
        $z .= "$i <label for=\"member_home_area_code\">".$this->check_required($this->home_area_code_required,1)."Home phone number:".$this->check_required($this->home_area_code_required,2)."</label>\n";
        $z .= "$i <input type=\"text\" id=\"member_home_phone_number\" name=\"member_home_phone_number\" maxlength=\"14\" value=\"".$this->home_phone_number."\" /><br class=\"left\" />\n";
        $z .= "$i <label for=\"member_work_area_code\">".$this->check_required($this->work_area_code_required,1)."Work phone number:".$this->check_required($this->work_area_code_required,2)."</label>\n";
        $z .= "$i <input type=\"text\" id=\"member_work_phone_number\" name=\"member_work_phone_number\" maxlength=\"4\" value=\"".$this->work_phone_number."\" /><br class=\"left\" />\n";
        $z .= "$i <label for=\"member_mobile_area_code\">".$this->check_required($this->mobile_area_code_required,1)."Mobile phone number:".$this->check_required($this->mobile_area_code_required,2)."</label>\n";
        $z .= "$i <input type=\"text\" id=\"member_mobile_phone_number\" name=\"member_mobile_phone_number\" maxlength=\"4\" value=\"".$this->mobile_phone_number."\" /><br class=\"left\" />\n";
        $z .= "$i <label for=\"member_email_address\">".$this->check_required($this->email_address_required,1)."Email Address:".$this->check_required($this->email_address_required,2)."</label>\n";
        $z .= "$i <input type=\"text\" id=\"member_email_address\" name=\"member_email_address\" maxlength=\"100\" value=\"".$this->email_address."\" /><br class=\"left\" />\n";
        $z .= "$i <label for=\"member_member_profile\">".$this->check_required($this->member_profile_required,1).ucwords(MEMBERS_NAME_SINGULAR)." profile:".$this->check_required($this->member_profile_required,2)."<br class=\"left\" />(Not seen by public)</label>\n";
        $z .= "$i <textarea id=\"member_member_profile\" name=\"member_member_profile\">".htmlspecialchars($this->member_profile)."</textarea><br class=\"left\" />\n";
        $z .= "$i <label for=\"member_public_profile_enabled\">Enable public profile (seen by guests to the website)?</label>\n";
        $z .= "$i <input type=\"checkbox\" id=\"member_public_profile_enabled\" name=\"member_public_profile_enabled\" value=\"1\"".$this->add_checkbox($type,$error,$this->public_profile_enabled,0)." /><br class=\"left\" />\n";
        $z .= "$i <label for=\"member_public_profile\">Public Profile:</label>\n";
        $z .= "$i <textarea id=\"member_public_profile\" name=\"member_public_profile\">".htmlspecialchars($this->public_profile)."</textarea><br class=\"left\" />\n";
        $z .= "$i <label for=\"member_url\">".$this->check_required($this->url_required,1)."Webpage:".$this->check_required($this->url_required,2)."</label>\n";
        $z .= "$i <input type=\"text\" id=\"member_url\" name=\"member_url\" maxlength=\"100\" value=\"".$this->url."\" /><br class=\"left\" />\n";
        $z .= "$i <br class=\"left\" /><strong>Email Options.</strong><br class=\"left\" />Check the types of emails you would like to receive.<br class=\"left\" /><br class=\"left\" />\n";
        $z .= "$i <label for=\"member_receive_email_newletter\">".ucwords(ARTICLES_NAME_SINGULAR)." updates:</label>\n";
        $z .= "$i <input type=\"checkbox\" id=\"member_receive_email_newletter\" name=\"member_receive_email_newletter\" value=\"1\"".$this->add_checkbox($type,$error,$this->receive_email_newletter,1)." /><br class=\"left\" />\n";
        $z .= "$i <label for=\"member_receive_email_events\">".ucwords(EVENTS_NAME_SINGULAR)." updates:</label>\n";
        $z .= "$i <input type=\"checkbox\" id=\"member_receive_email_events\" name=\"member_receive_email_events\" value=\"1\"".$this->add_checkbox($type,$error,$this->receive_email_events,1)." /><br class=\"left\" />\n";
        $z .= "$i <label for=\"member_receive_email_url\">".ucwords(LINKS_NAME_SINGULAR)." updates:</label>\n";
        $z .= "$i <input type=\"checkbox\" id=\"member_receive_email_url\" name=\"member_receive_email_url\" value=\"1\"".$this->add_checkbox($type,$error,$this->receive_email_url,1)." /><br class=\"left\" />\n";
        $z .= "$i <label for=\"member_receive_email_faq\">".ucwords(FAQ_NAME_SINGULAR)." updates:</label>\n";
        $z .= "$i <input type=\"checkbox\" id=\"member_receive_email_faq\" name=\"member_receive_email_faq\" value=\"1\"".$this->add_checkbox($type,$error,$this->receive_email_faq,1)." /><br class=\"left\" />\n";
        $z .= "$i <label for=\"member_receive_email_buy\">Buy ".ucwords(TRANSACTION_NAME_PLURAL).":</label>\n";
        $z .= "$i <input type=\"checkbox\" id=\"member_receive_email_buy\" name=\"member_receive_email_buy\" value=\"1\"".$this->add_checkbox($type,$error,$this->receive_email_buy,1)." /><br class=\"left\" />\n";
        $z .= "$i <label for=\"member_receive_email_sell\">Sell ".ucwords(TRANSACTION_NAME_PLURAL).":</label>\n";
        $z .= "$i <input type=\"checkbox\" id=\"member_receive_email_sell\" name=\"member_receive_email_sell\" value=\"1\"".$this->add_checkbox($type,$error,$this->receive_email_sell,1)." /><br class=\"left\" />\n";
        $z .= "$i <label for=\"member_receive_email_noticeboard\">".ucwords(NOTICEBOARD_NAME_SINGULAR)." updates:</label>\n";
        $z .= "$i <input type=\"checkbox\" id=\"member_receive_email_noticeboard\" name=\"member_receive_email_noticeboard\" value=\"1\"".$this->add_checkbox($type,$error,$this->receive_email_noticeboard,1)." /><br class=\"left\" />\n";
        if (ENABLE_AUCTIONS) {
            $z .= "$i <label for=\"member_receive_email_outbid\">Being out-bid:</label>\n";
            $z .= "$i <input type=\"checkbox\" id=\"member_receive_email_outbid\" name=\"member_receive_email_outbid\" value=\"1\"".$this->add_checkbox($type,$error,$this->receive_email_outbid,1)." /><br class=\"left\" />\n";
        }
        $z .= "$i <br class=\"left\" /><strong>Mail Options.</strong><br class=\"left\" />\n";
        $z .= "$i <label for=\"member_receive_newsletter\">Receive ".strtolower(ARTICLES_NAME_PLURAL)." via mail?</label>\n";
        $z .= "$i <input type=\"checkbox\" id=\"member_receive_newsletter\" name=\"member_receive_newsletter\" value=\"1\"".$this->add_checkbox($type,$error,$this->receive_newsletter,1)." /><br class=\"left\" />\n";
        $z .= "$i <label for=\"member_receive_statement\">Receive ".strtolower(TRANSACTION_NAME_SINGULAR)." history via mail?</label>\n";
        $z .= "$i <input type=\"checkbox\" id=\"member_receive_statement\" name=\"member_receive_statement\" value=\"1\"".$this->add_checkbox($type,$error,$this->receive_statement,1)." /><br class=\"left\" />\n";
        $z .= "$i <input type=\"hidden\" name=\"accepted_tos\" value=\"true\" /><br />\n";

        if ($type == 'edit') {
            if (ENABLE_IMAGES) {
                $z .= $i." <input class=\"button\" type=\"submit\" name=\"submit\" value=\"Edit\" /><br class=\"left\" /><input class=\"button\" type=\"submit\" name=\"submit\" value=\"Delete\" />\n";
                $z .= $i."</form>\n";
                $z .= $i."</fieldset>\n";
                $z .= $i."<br />\n";
                $z .= $i."<fieldset>\n";
                $z .= $i."<legend>\n";
                $z .= $i."Add or Edit images\n";
                $z .= $i."</legend>\n";
                $z .= $image->edit_form($i,$this->id,'accountID','accounts',URL.$image_action.append_url(0),'noticeboardID = 0 AND articleID = 0');
                $z .= $i."</fieldset>\n";
            } else {
                $z .= $i." <input class=\"button\" type=\"submit\" name=\"submit\" value=\"Submit\" /><br class=\"left\" /><input class=\"button\" type=\"submit\" name=\"submit\" value=\"Delete\" />\n";
                $z .= $i."</form>\n";
                $z .= $i."</fieldset>\n";
            }
        } else {
            if (ENABLE_IMAGES) {
                if (!isset($_POST['submit']) or $_POST['submit'] == 'Delete') $image->clear();
                $z .= $i." <strong>Add an image to your profile.</strong><br /><br />\n";
                $z .= $image->form_html($i.' ',$type,0);
            }
            $z .= $i." <input class=\"button\" type=\"submit\" name=\"submit\" value=\"Submit\" />\n";
            $z .= $i."</form>\n";
            $z .= $i."</fieldset>\n";
        }
        $z .= $i."</div>\n";
        $z .= $i."<!-- /member_form -->\n";
        return $z;
    }
    function search_form($i,$search_term = '',$member_search_type = 0) {
        $z = $i."<!-- member_search_form -->\n";
        $z .= $i."<div id=\"member_search_form\">\n";
        $z .= $i." <fieldset>\n";
        $z .= $i." <form id=\"member_search\" name=\"member_search\" action=\"".URL.MEMBER_LIST_URL.'/'.append_url()."\" method=\"post\">\n";
        $z .= $i."  <div class=\"member_search\">\n";
        $z .= $i."   Search for ".a(MEMBERS_NAME_SINGULAR)." ".strtolower(MEMBERS_NAME_SINGULAR)."'s name or ID number:<br /><input type=\"text\" name=\"member_search_term\" id=\"member_search_term\" value=\"".$search_term."\" />\n";
        if (user_type() == 2) {
            $z .= $i."  <div class=\"member_search\">\n";
            $z .= "$i   <select id=\"member_search_type\" name=\"member_search_type\">\n";
            $z .= "$i    <option value=\"1\"".$this->add_selected($member_search_type,"1",1).">Normal and Expired</option>\n";
            $z .= "$i    <option value=\"2\"".$this->add_selected($member_search_type,"2",0).">Unvalidated</option>\n";
            $z .= "$i    <option value=\"3\"".$this->add_selected($member_search_type,"3",0).">Suspended</option>\n";
            $z .= "$i    <option value=\"4\"".$this->add_selected($member_search_type,"4",0).">Expiring within one month</option>\n";
            $z .= "$i    <option value=\"5\"".$this->add_selected($member_search_type,"5",0).">Expired</option>\n";
            $z .= "$i    <option value=\"6\"".$this->add_selected($member_search_type,"6",0).">Deleted</option>\n";
            $z .= "$i   </select>\n";
        }
        $z .= $i."  </div>\n";
        $z .= $i."  <div class=\"member_search\">\n";
        $z .= $i."   <input id=\"member_button\" type=\"submit\" name=\"submit\" value=\"Search\" />\n";
        $z .= $i."  </div>\n";
        $z .= $i." </form>\n";
        $z .= $i." </fieldset><br />\n";
        $z .= $i."</div>\n";
        $z .= $i."<!-- /member_search_form -->\n";
        return $z;
    }
    function set_last_login() {
        $mysql = new mysql;
        if ($mysql->query('UPDATE accounts SET ll_day = '.$GLOBALS['date']['day'].', ll_month = '.$GLOBALS['date']['month'].', ll_year = '.$GLOBALS['date']['year'].', ll_hour = '.$GLOBALS['date']['hour'].' WHERE accountID = '.$_SESSION['member_id'].' LIMIT 1')) {
            return true;
        }
    }
    function login() {

        $login_result = mysql_query("SELECT * FROM accounts WHERE accountID = ".mysql_real_escape_string($_POST["login_id"]));
        if (!$login_result) {
            // Bad login
            $this->error_message = '<ul><li class="error">Wrong user ID or password !!!</il></ul>';
            return false;
        } else {
            $pass = mysql_result($login_result,0,"password");
            if (bad_user()) {
                $this->error_message = "<strong>Too many Login attempts!</strong><br /><br />Please try again later.\n";
                if (ENABLE_LOG) log_action('Login Failure: Account ID:'.$_POST["login_id"].' ('.$this->full_name($_POST["login_id"]).') Entered an invalid password.');
                return false;
            } elseif ($pass == crypt(md5($_POST['login_password']),md5(SITE_KEY))) {
                $this->build_dataset($_POST['login_id']);
                $lets_session_name = session_name('SID');
                session_start();
                header("Cache-Control: private");
                $_SESSION['member_id']                  =       $this->id;
                $_SESSION['member_name']                =       $this->first_name;
                $_SESSION['member_full_name']           =       $this->first_name.' '.$this->last_name;
                $_SESSION['member_validated']           =       $this->validated;
                $_SESSION['member_suspended']           =       $this->suspended;
                $_SESSION['member_suspended_message']   =       $this->suspended_message;
                $_SESSION['member_type']                =       $this->type;
                if (ENABLE_LOG) log_action('Account ID:'.$_POST["login_id"].' ('.$this->full_name($_POST["login_id"]).') Signed in.');
                return true;
            } else {
                // Login ok but not the pass
                wrong_password();
                $this->error_message = '<ul><li class="error">Wrong user ID or password !!!</li></ul>';
                if (ENABLE_LOG) log_action('Login Failure: Account ID:'.$_POST["login_id"].' ('.$this->full_name($_POST["login_id"]).') Entered an invalid password.');
                return false;
            }
        }
    }
}


?>
