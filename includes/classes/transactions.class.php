<?php


class transactions {
    /*


    type:
    1 - standard transaction
    2 - "Buy Now"
    3 - Auction
    4 - service fee
    5 - account creation
    6 - when an account is terminated and the balance is dumped into another account
    7 - when that account is reactivated
    8 - distribute No 1 account

    */
    var $id,
        $buyer_id,
        $seller_id,
        $member_id,
        $amount,
        $description,
        $day,
        $month,
        $year,
        $bulk_post,
        $num_bulk_transactions,
        $num_bulk_transactions_processed,
        $bulk_summary,
        $bulk_confirm_form,
        $error,
        $bulk_submission_errors,
        $balance;

    function start($member_id) {
        global $user;

        // call with setup fee of 0.00 if not applicable
        $start_transaction_result = mysql_query('SELECT * FROM transactions WHERE buyerID = '.$member_id);
        if (!$start_transaction_result) {
            $this->error = 'Database error: '.mysql_error();
            return false;
        }
        if (SETUP_FEE != "0.00" and $member_id != 1) {
            if (SETUP_FEE > 0) {
                $description = 'Account Creation with membership fee of '.SETUP_FEE.' '.ucwords(CURRENCY_NAME);
                $query = "INSERT INTO transactions VALUES ( '','".$member_id."','1','".SETUP_FEE."','".mysql_real_escape_string($description)."','".$GLOBALS['date']['day']."','".$GLOBALS['date']['month']."','".$GLOBALS['date']['year']."','".$GLOBALS['date']['hour']."','".$GLOBALS['date']['minutes']."','".$GLOBALS['date']['seconds']."','5','0' )";
                $user->update_balance(SETUP_FEE,$member_id);
                if ($this->balance(1)) {
                    $user->update_balance($this->balance,1);
                }
            } else {
                $setup_fee = SETUP_FEE * -1;
                $description = 'Account Creation with bonus of '.number_format($setup_fee,2).' '.ucwords(CURRENCY_NAME);
                $query = "INSERT INTO transactions VALUES ( '','1','".$member_id."','".$setup_fee."','".mysql_real_escape_string($description)."','".$GLOBALS['date']['day']."','".$GLOBALS['date']['month']."','".$GLOBALS['date']['year']."','".$GLOBALS['date']['hour']."','".$GLOBALS['date']['minutes']."','".$GLOBALS['date']['seconds']."','5','0' )";
                $user->update_balance($setup_fee,$member_id);
                if ($this->balance(1)) {
                    $user->update_balance($this->balance,1);
                }
            }
        } else {
            $description = 'Account Creation';
            $query = "INSERT INTO transactions VALUES ( '','".$member_id."','1','','".mysql_real_escape_string($description)."','".$GLOBALS['date']['day']."','".$GLOBALS['date']['month']."','".$GLOBALS['date']['year']."','".$GLOBALS['date']['hour']."','".$GLOBALS['date']['minutes']."','".$GLOBALS['date']['seconds']."','5','0' )";
        }
        $start_transaction_insert = mysql_query($query);
        if (!$start_transaction_insert) {
            $this->error = 'Database Error';
            if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('There was a database error when trying to enter the starting '.strtolower(TRANSACTION_NAME_SINGULAR).' for '.strtolower(MEMBERS_NAME_SINGULAR).' #'.$member_id);
            if (ENABLE_ERROR_LOG) log_error('There was a database error when trying to enter the starting '.strtolower(TRANSACTION_NAME_SINGULAR).' for '.strtolower(MEMBERS_NAME_SINGULAR).' #'.$member_id.'<br />Error:'.mysql_error());
            return false;
        } else {
            return true;
        }
    }
    function pre_confirm_trade($buyer_id,$seller_id,$amount) {
        if ($buyer_id == $seller_id) {
            $this->error = 'Buyer and Seller are the same';
            return false;
        }
        if (!$this->balance($buyer_id)) {
            $this->error = ucfirst(MEMBERS_NAME_SINGULAR).' #'.$buyer_id.' does not exist';
            return false;
        }
        if (NEGATIVE_BALANCE_LIMIT != '0.00') {
            if (($this->balance - ($amount + TRANSACTION_SERVICE_FEE_BUYER)) < (NEGATIVE_BALANCE_LIMIT * -1)) {
                $this->error = 'A limit of '.NEGATIVE_BALANCE_LIMIT.' '.CURRENCY_NAME.' has been established. This '.TRANSACTION_NAME_SINGULAR.' cannot be completed because it would exceed that limit';
                return false;
            }
        }
        if (($this->member_suspended($buyer_id) or $this->member_suspended($seller_id)) and user_type() != 2) {
            return false;
        }
        return true;
    }
    function member_suspended($id) {
        $mysql = new mysql;
        if (!$mysql->result('SELECT suspended FROM accounts WHERE accountID = '.$id.' LIMIT 1')) {
            $this->error = ucfirst(MEMBERS_NAME_SINGULAR).' #'.$id.' does not exist';
            return true;
        } else {
            if ($mysql->result['suspended']) {
                $this->error = 'This '.strtolower(MEMBERS_NAME_SINGULAR).' has been suspended and cannot take part in '.a(TRANSACTION_NAME_SINGULAR).' '.strtolower(TRANSACTION_NAME_SINGULAR);
                return true;
            } else {
                return false;
            }
        }
    }
    function type($type) {
        switch ($type) {
            case 1: return 'Normal';
            case 2: return 'Buy Now';
            case 3: return 'Auction Win';
            case 4: return 'Service Fee';
            case 5: return 'Account Created';
            case 6: return 'Account Deleted';
            case 7: return 'Account Reactivated';
            case 8: return 'Global '.ucwords(TRANSACTION_NAME_SINGULAR);
        }
    }
    function buy($i,$buyer_id,$seller_id,$amount,$description,$type = 0,$noticeboard_id = 0) {
        global $user;
        global $links;
        $mysql = new mysql;
        if (($this->member_suspended($buyer_id) or $this->member_suspended($seller_id)) and user_type() != 2) {
            return false;
        }
        if (!$this->balance($buyer_id)) {
            return false;
        }
        if (NEGATIVE_BALANCE_LIMIT != '0.00') {
            if (($this->balance - ($amount + TRANSACTION_SERVICE_FEE_BUYER)) < (NEGATIVE_BALANCE_LIMIT * -1)) {
                $this->error = $i.'A limit of '.number_format(NEGATIVE_BALANCE_LIMIT,2).' '.ucwords(CURRENCY_NAME).' has been established. This '.strtolower(TRANSACTION_NAME_SINGULAR).' cannot be completed because it would exceed that limit';
                return false;
            }
        }
        if ($buyer_id == $seller_id) {
            $this->error = $i.'You cannot trade with yourself';
            return false;
        }
        if (empty($description)) {
            $this->error = $i.'A description of each '.strtolower(TRANSACTION_NAME_SINGULAR).' is required';
            return false;
        }
        if (empty($amount) or !isset($amount) or $amount == "0.00" or !is_numeric($amount)) {
            $this->error = $i.'An amount was not given';
            return false;
        }
        $query = "INSERT INTO transactions VALUES ( '','".$buyer_id."','".$seller_id."','".$amount."','".mysql_real_escape_string($description)."','".$GLOBALS['date']['day']."','".$GLOBALS['date']['month']."','".$GLOBALS['date']['year']."','".$GLOBALS['date']['hour']."','".$GLOBALS['date']['minutes']."','".$GLOBALS['date']['seconds']."','".$type."','".$noticeboard_id."' )";
        $transaction_query = mysql_query($query);
        if (!$transaction_query) {
            $this->error = $i.'Database Error';
            if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('There was a database error when trying to enter '.a(TRANSACTION_NAME_SINGULAR).' '.strtolower(TRANSACTION_NAME_SINGULAR).'. Buyer ID: '.$buyer_id,', Seller ID: '.$seller_id.' Amount: '.$amount);
            if (ENABLE_ERROR_LOG) log_error('There was a database error when trying to enter '.a(TRANSACTION_NAME_SINGULAR).' '.strtolower(TRANSACTION_NAME_SINGULAR).'. Buyer ID: '.$buyer_id,', Seller ID: '.$seller_id.' Amount: '.$amount.'<br />Error:'.mysql_error());
            return false;
        } else {
            $new_transaction_id = mysql_insert_id();
            if (!$this->transaction_fee($buyer_id,$seller_id,$new_transaction_id)) {
                return false;
            }
            if (ENABLE_EMAIL) {
                $links->build_url(1,2);
                $buyer_balance = 0;
                $seller_balance = 0;
                if ($this->balance($buyer_id)) {
                    $buyer_balance = $this->balance;
                }
                if ($this->balance($seller_id)) {
                    $seller_balance = $this->balance;
                }

                $transaction_html = '<strong>Amount:</strong> '.number_format($amount,2).'<br />';
                $transaction_html .= '<strong>'.ucwords(TRANSACTION_NAME_SINGULAR).' Type:</strong> '.$this->type($type).'<br />';
                $transaction_html .= '<strong>Description:</strong> '.indent_variable('',$description).'<br />';
                $transaction_html .= '<strong>Buyer\'s new balance:</strong> '.number_format($buyer_balance,2).'<br />';
                $transaction_html .= '<strong>Seller\'s new balance:</strong> '.number_format($seller_balance,2).'<br />';
                if ($noticeboard_id) {
                    $transaction_html .= '<strong>'.ucwords(NOTICEBOARD_NAME_SINGULAR).':</strong> <a href="'.URL.NOTICEBOARD_URL.'/'.$noticeboard_id.'/">'.$noticeboard_id.'</a><br />';
                }
                $transaction_html .= '<strong>Time:</strong> '.return_month($GLOBALS['date']['month']).' '.$GLOBALS['date']['day'].', '.$GLOBALS['date']['year'].' - '.return_time($GLOBALS['date']['hour'],$GLOBALS['date']['minutes']).'<br /><br />';


                $mysql->result('SELECT receive_email_buy, first_name, last_name, email_address FROM accounts WHERE accountID = '.$buyer_id.' AND email_address != \'\'');
                if ($mysql->result['receive_email_buy']) {
                    $email_html = 'Dear '.$mysql->result['first_name'].',<br /><br />';
                    $email_html .= 'A '.strtolower(TRANSACTION_NAME_SINGULAR).' has been recorded with you as buyer and '.$user->full_name($seller_id).' as seller.<br /><br />';
                    $email_html .= $transaction_html;
                    send_single_email(UPDATE_EMAIL,EMAIL_FROM_NAME,$mysql->result['email_address'],$mysql->result['first_name'].' '.$mysql->result['last_name'],ucfirst(a(TRANSACTION_NAME_SINGULAR)).' '.strtolower(TRANSACTION_NAME_SINGULAR).' has been recorded',strip_tags(str_replace('<br />',"\r\n\r\n",$email_html)).'Change your email delivery options here: '.URL.$links->complete_url,$email_html.'Change your email delivery options <a href="'.URL.$links->complete_url.'">here</a>');
                }
                $mysql->result('SELECT receive_email_sell, first_name, last_name, email_address FROM accounts WHERE accountID = '.$seller_id.' AND email_address != \'\'');
                if ($mysql->result['receive_email_sell']) {
                    $email_html = 'Dear '.$mysql->result['first_name'].',<br /><br />';
                    $email_html .= 'A '.strtolower(TRANSACTION_NAME_SINGULAR).' has been recorded with you as seller and '.$user->full_name($buyer_id).' as buyer.<br /><br />';
                    $email_html .= $transaction_html;
                    send_single_email(UPDATE_EMAIL,EMAIL_FROM_NAME,$mysql->result['email_address'],$mysql->result['first_name'].' '.$mysql->result['last_name'],ucfirst(a(TRANSACTION_NAME_SINGULAR)).' '.strtolower(TRANSACTION_NAME_SINGULAR).' has been recorded',strip_tags(str_replace('<br />',"\r\n\r\n",$email_html)).'Change your email delivery options here: '.URL.$links->complete_url,$email_html.'Change your email delivery options <a href="'.URL.$links->complete_url.'">here</a>');
                }
            }

            $this->id = $new_transaction_id;
            $this->amount = $amount;
            $this->description = $description;
            $this->day = $GLOBALS['date']['day'];
            $this->month = $GLOBALS['date']['month'];
            $this->year = $GLOBALS['date']['year'];
            if (ENABLE_LOG and LOG_TRANSACTIONS) log_action(ucfirst(a(TRANSACTION_NAME_SINGULAR)).' '.strtolower(TRANSACTION_NAME_SINGULAR).' was recorded. '.ucwords(TRANSACTION_NAME_SINGULAR).' ID: '.$this->id.', Buyer ID: '.$buyer_id.', Seller ID: '.$seller_id.', Amount: '.number_format($amount,2));
            return true;
        }
    }

    function transaction_fee($buyer_id,$seller_id,$transaction_id,$noticeboard_id = 0,$disable_buyer_service_fee = false,$disable_seller_service_fee = false) {
        global $user;
        if (ENABLE_TRANSACTION_SERVICE_FEE and $buyer_id != 1) {
            if (TRANSACTION_SERVICE_FEE_BUYER != "0.00" and !$disable_buyer_service_fee) {
                $buyer_service_fee_query = mysql_query("INSERT INTO transactions VALUES ( '','".$buyer_id."','1','".TRANSACTION_SERVICE_FEE_BUYER."','Buyer\'s service fee for ".TRANSACTION_NAME_SINGULAR." ".$transaction_id."','".$GLOBALS['date']['day']."','".$GLOBALS['date']['month']."','".$GLOBALS['date']['year']."','".$GLOBALS['date']['hour']."','".$GLOBALS['date']['minutes']."','".$GLOBALS['date']['seconds']."','4','".$noticeboard_id."' )");
                if (!$buyer_service_fee_query) {
                    $this->error .= 'Database Error while inserting '.strtolower(TRANSACTION_NAME_SINGULAR).' fee';
                    if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('There was a database error when trying to enter a buyer\'s '.strtolower(TRANSACTION_NAME_SINGULAR).' fee. Buyer ID: '.$buyer_id,', Seller ID: '.$seller_id.' Amount: '.$amount);
                    if (ENABLE_ERROR_LOG) log_error('There was a database error when trying to enter a buyer\'s '.strtolower(TRANSACTION_NAME_SINGULAR).' fee. Buyer ID: '.$buyer_id,', Seller ID: '.$seller_id.' Amount: '.$amount.'<br />Error:'.mysql_error());
                    return false;
                }
            }
            if (TRANSACTION_SERVICE_FEE_SELLER != "0.00" and !$disable_seller_service_fee) {
                $seller_service_fee_query = mysql_query("INSERT INTO transactions VALUES ( '','".$seller_id."','1','".TRANSACTION_SERVICE_FEE_SELLER."','Seller\'s service fee for ".TRANSACTION_NAME_SINGULAR." ".$transaction_id."''".$GLOBALS['date']['day']."','".$GLOBALS['date']['month']."','".$GLOBALS['date']['year']."','".$GLOBALS['date']['hour']."','".$GLOBALS['date']['minutes']."','".$GLOBALS['date']['seconds']."','4','".$noticeboard_id."' )");
                if (!$seller_service_fee_query) {
                    $this->error .= 'Database Error while inserting '.strtolower(TRANSACTION_NAME_SINGULAR).' fee';
                    if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('There was a database error when trying to enter a seller\'s '.strtolower(TRANSACTION_NAME_SINGULAR).' fee. Buyer ID: '.$buyer_id,', Seller ID: '.$seller_id.' Amount: '.$amount);
                    if (ENABLE_ERROR_LOG) log_error('There was a database error when trying to enter a seller\'s '.strtolower(TRANSACTION_NAME_SINGULAR).' fee. Buyer ID: '.$buyer_id,', Seller ID: '.$seller_id.' Amount: '.$amount.'<br />Error:'.mysql_error());
                    return false;
                }
            }
        }
        $local_errors = '';
        if ($this->balance($buyer_id)) {
            $user->update_balance($this->balance,$buyer_id);
        } else {
            $local_errors .= ucwords(TRANSACTION_NAME_SINGULAR).' successfull but balance not updated.';
        }
        if ($this->balance($seller_id)) {
            $user->update_balance($this->balance,$seller_id);
        } else {
            $local_errors .= ucwords(TRANSACTION_NAME_SINGULAR).' successfull but balance not updated.';
        }

        if ($local_errors) {
            $this->error .= $local_errors;
            return false;
        } else {
            return true;
        }
    }
    function reverse($id) {
        if (ENABLE_LOG and LOG_TRANSACTIONS) log_action(ucwords(TRANSACTION_NAME_SINGULAR).' ID: '.$id.' REVERSAL initiated');
        $mysql = new mysql;
        //restore auction or bin
        if ($mysql->result('SELECT * FROM transactions WHERE transactionID = '.$id.' LIMIT 1')) {
            $buyer = $mysql->result['buyerID'];
            $seller = $mysql->result['sellerID'];
            if (($mysql->result['type'] == 2 or $mysql->result['type'] == 3) and $mysql->result['noticeboardID']) {
                if (!$mysql->query('UPDATE noticeboard SET bought = 0 WHERE noticeboardID = '.$mysql->result['noticeboardID'].' LIMIT 1')) {
                    if (ENABLE_LOG and LOG_TRANSACTIONS) log_action(ucwords(NOTICEBOARD_NAME_SINGULAR).' ID: '.$id.' was not reactivated');
                } else {
                    if (ENABLE_LOG and LOG_TRANSACTIONS) log_action(ucwords(NOTICEBOARD_NAME_SINGULAR).' ID: '.$id.' was reactivated');
                }
                if (!$mysql->query('DELETE FROM bids WHERE noticeboardID = '.$mysql->result['noticeboardID'])) {
                    if (ENABLE_LOG and LOG_TRANSACTIONS) log_action(ucwords(NOTICEBOARD_NAME_SINGULAR).' ID: '.$id.' bids not deleted');
                } else {
                    if (ENABLE_LOG and LOG_TRANSACTIONS) log_action(ucwords(NOTICEBOARD_NAME_SINGULAR).' ID: '.$id.' bids deleted');
                }
            }
        } else {
            $this->error = $mysql->error;
            return false;
        }
        if ($mysql->query('DELETE FROM transactions WHERE transactionID = '.$id.' LIMIT 1')) {
            if (ENABLE_LOG and LOG_TRANSACTIONS) log_action(ucwords(TRANSACTION_NAME_SINGULAR).' ID: '.$id.' was deleted');
            if ($this->balance($buyer)) {
                if (!$mysql->query('UPDATE accounts SET balance = '.$this->balance.' WHERE accountID = '.$buyer.' LIMIT 1')) {
                    if (ENABLE_LOG and LOG_TRANSACTIONS) log_action(ucwords(MEMBER_NAME_SINGULAR).' ID: '.$buyer.' (buyer): failed to update balance');
                }
            }
            if ($this->balance($seller)) {
                if (!$mysql->query('UPDATE accounts SET balance = '.$this->balance.' WHERE accountID = '.$seller.' LIMIT 1')) {
                    if (ENABLE_LOG and LOG_TRANSACTIONS) log_action(ucwords(MEMBER_NAME_SINGULAR).' ID: '.$seller.' (seller): failed to update balance');
                }
            }
            if (ENABLE_LOG and LOG_TRANSACTIONS) log_action(ucwords(TRANSACTION_NAME_SINGULAR).' ID: '.$id.' REVERSAL completed successfully');
            return true;
        } else {
            $this->error = $mysql->error;
            return false;
        }
    }

    function member_history_array($i,$member_id,$start_day,$start_month,$start_year,$end_day,$end_month,$end_year,$transaction_type = 1,$newest = false) {
        $mysql = new mysql;
        $user = new member;
        $query = 'SELECT * FROM transactions WHERE (year > '.$start_year;
        $query .= ' OR (year = '.$start_year.' AND month > '.$start_month.')';
        $query .= ' OR (year = '.$start_year.' AND month = '.$start_month.' AND day >= '.$start_day.'))';
        $query .= ' AND (year < '.$end_year;
        $query .= ' OR (year = '.$end_year.' AND month < '.$end_month.')';
        $query .= ' OR (year = '.$end_year.' AND month = '.$end_month.' AND day <= '.$end_day.'))';
        $query .= ' AND (buyerID = '.$member_id.' OR sellerID = '.$member_id.' )';
        $query .= ' ORDER BY year ASC, month ASC, day ASC, hour ASC, minute ASC, second ASC';
        if (!$mysql->build_array($query)) {
            if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('There was a database error when trying to build an array of '.strtolower(TRANSACTION_NAME_PLURAL).' in transactions.class.php');
            if (ENABLE_ERROR_LOG) log_error('There was a database error when trying to build an array of '.strtolower(TRANSACTION_NAME_PLURAL).' in transactions.class.php<br />Error:'.$mysql->error);
            return false;
        }
        $arr = $mysql->result;
        if (!is_array($arr)) {
            $z = $i."<!-- transaction_history -->\n";
            $z .= $i."<table>\n";
            $z .= $i." <tr>\n";
            $z .= $i."  <td>\n";
            $z .= $i."  There were no ".strtolower(TRANSACTION_NAME_PLURAL)." in this time period</td>\n";
            $z .= $i." </tr>\n";
            $z .= $i."</table>\n";
            $z .= $i."<!-- /transaction_history -->\n";
            return $z;
        }
        $initial_debit = 0;
        if (!$mysql->num_rows('SELECT * FROM transactions WHERE buyerID = '.$member_id.' AND (year < '.$start_year.' OR (year = '.$start_year.' AND month < '.$start_month.') OR (year = '.$start_year.' AND month = '.$start_month.' AND day < '.$start_day.'))')) {
            if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('There was a database error when trying to build an array of '.strtolower(TRANSACTION_NAME_PLURAL).' in transactions.class.php');
            if (ENABLE_ERROR_LOG) log_error('There was a database error when trying to build an array of '.strtolower(TRANSACTION_NAME_PLURAL).' in transactions.class.php<br />Error:'.$mysql->error);
            return false;
        }
        if ($mysql->num_rows > 0) {
            if (!$mysql->result('SELECT SUM(amount) AS debit FROM transactions WHERE buyerID = '.$member_id.' AND (year < '.$start_year.' OR (year = '.$start_year.' AND month < '.$start_month.') OR (year = '.$start_year.' AND month = '.$start_month.' AND day < '.$start_day.'))')) {
                if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('There was a database error when trying to build an array of '.strtolower(TRANSACTION_NAME_PLURAL).' in transactions.class.php');
                if (ENABLE_ERROR_LOG) log_error('There was a database error when trying to build an array of '.strtolower(TRANSACTION_NAME_PLURAL).' in transactions.class.php<br />Error:'.$mysql->error);
                return false;
            }
            $initial_debit = $mysql->result['debit'];
        } else {
            $initial_debit = 0;
        }
        $initial_credit = 0;
        if (!$mysql->num_rows('SELECT * FROM transactions WHERE sellerID = '.$member_id.' AND (year < '.$start_year.' OR (year = '.$start_year.' AND month < '.$start_month.') OR (year = '.$start_year.' AND month = '.$start_month.' AND day < '.$start_day.'))')) {
            if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('There was a database error when trying to build an array of '.strtolower(TRANSACTION_NAME_PLURAL).' in transactions.class.php');
            if (ENABLE_ERROR_LOG) log_error('There was a database error when trying to build an array of '.strtolower(TRANSACTION_NAME_PLURAL).' in transactions.class.php<br />Error:'.$mysql->error);
            return false;
        }
        if ($mysql->num_rows > 0) {
            if (!$mysql->result('SELECT SUM(amount) AS credit FROM transactions WHERE sellerID = '.$member_id.' AND (year < '.$start_year.' OR (year = '.$start_year.' AND month < '.$start_month.') OR (year = '.$start_year.' AND month = '.$start_month.' AND day < '.$start_day.'))')) {
                if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('There was a database error when trying to build an array of '.strtolower(TRANSACTION_NAME_PLURAL).' in transactions.class.php');
                if (ENABLE_ERROR_LOG) log_error('There was a database error when trying to build an array of '.strtolower(TRANSACTION_NAME_PLURAL).' in transactions.class.php<br />Error:'.$mysql->error);
                return false;
            }
            $initial_credit = $mysql->result['credit'];
        } else {
            $initial_credit = 0;
        }

        $beginning_amount = $initial_credit - $initial_debit;
        $z = '';
        $current_balance = $beginning_amount;
        foreach ($arr as $a) {
            $action = '';
            $y = '';
            $y .= $i." <tr>\n";
            $y .= $i."  <td>".return_month($a['month']).' '.$a['day'].', '.$a['year']."</td>\n";
            $y .= $i."  <td>";
            if ($a['sellerID'] == 1) {
                if ($member_id != 1) {
                    $action = 'buy';
                } else {
                    $action = 'sell';
                }
                if ($a['type'] == 4) {
                    $y .= ucwords(TRANSACTION_NAME_SINGULAR).' Fee'."</td>\n";
                } elseif ($a['type'] == 5) {
                    $y .= 'Account Creation'."</td>\n";
                } elseif ($a['type'] == 6) {
                    $y .= 'Account Deletion'."</td>\n";
                } elseif ($a['type'] == 7) {
                    $y .= 'Account Reactivated'."</td>\n";
                } else {
                    $y .= 'System Entry'."</td>\n";
                }
            } elseif ($a['buyerID'] == $member_id) {
                $action = 'buy';
                if ($a['type'] == 4) {
                    $y .= ucwords(TRANSACTION_NAME_SINGULAR).' Fee'."</td>\n";
                } elseif ($a['type'] == 5) {
                    $y .= 'Account Creation'."</td>\n";
                } elseif ($a['type'] == 6) {
                    $y .= 'Account Deletion'."</td>\n";
                } elseif ($a['type'] == 7) {
                    $y .= 'Account Reactivated'."</td>\n";
                } else {
                    $y .= '<span class="purchase">Purchase</span>'."</td>\n";
                }
            } elseif ($a['sellerID'] == $member_id) {
                $action = 'sell';
                if ($a['type'] == 4) {
                    $y .= ucwords(TRANSACTION_NAME_SINGULAR).' Fee'."</td>\n";
                } elseif ($a['type'] == 5) {
                    $y .= 'Account Creation'."</td>\n";
                } elseif ($a['type'] == 6) {
                    $y .= 'Account Deletion'."</td>\n";
                } elseif ($a['type'] == 7) {
                    $y .= 'Account Reactivated'."</td>\n";
                } else {
                    $y .= '<span class="sale">Sale</span>'."</td>\n";
                }
            }

            $y .= $i."  <td>".$a['transactionID']."</td>\n";
            if ($action == 'buy') {
                $y .= $i."  <td><a href=\"".URL.MEMBER_LIST_URL.'/'.$a['sellerID'].'/'.append_url(0)."\">".$user->full_name($a['sellerID'])."</a></td>\n";
            } else {
                $y .= $i."  <td><a href=\"".URL.MEMBER_LIST_URL.'/'.$a['buyerID'].'/'.append_url(0)."\">".$user->full_name($a['buyerID'])."</a></td>\n";
            }
            $y .= $i."  <td>".$a['amount']."</td>\n";
            $y .= $i."  <td>".trim(indent_variable($i,$a['description']))."</td>\n";
            if ($action == 'buy') {
                $current_balance = $current_balance - $a['amount'];
            } else {
                $current_balance = $current_balance + $a['amount'];
            }
            $y .= $i."  <td>".number_format($current_balance,2,'.',',')."</td>\n";
            $y .= $i." </tr>\n";

            if ($transaction_type == 2) {
                if (($a['type'] == 1 or $a['type'] == 2 or $a['type'] == 3) and $action == 'buy') {
                    $z .= $y;
                }
            } elseif ($transaction_type == 3) {
                if (($a['type'] == 1 or $a['type'] == 2 or $a['type'] == 3) and $action == 'sell') {
                    $z .= $y;
                }
            } elseif ($transaction_type == 4) {
                if ($a['type'] == 2) {
                    $z .= $y;
                }
            } elseif ($transaction_type == 5) {
                if ($a['type'] == 3) {
                    $z .= $y;
                }
            } elseif ($transaction_type == 6) {
                if ($a['type'] == 4) {
                    $z .= $y;
                }
            } elseif ($transaction_type == 7) {
                if ($a['type'] == 5 or $a['type'] == 6 or $a['type'] == 7) {
                    $z .= $y;
                }
            } else {
                $z .= $y;
            }

        }

        $b = $i."<!-- transaction_history -->\n";
        $b .= $i."<div id=\"transaction_history\">\n";
        $b .= $i."<table>\n";
        $b .= $i." <tr>\n";
        $b .= $i."  <th class=\"h\">Date</th>\n";
        $b .= $i."  <th class=\"h\">Action</th>\n";
        $b .= $i."  <th class=\"h\">".ucwords(TRANSACTION_NAME_SINGULAR)." ID</th>\n";
        $b .= $i."  <th class=\"h\">".ucwords(MEMBERS_NAME_SINGULAR)."</th>\n";
        $b .= $i."  <th class=\"h\">Amount</th>\n";
        $b .= $i."  <th class=\"h\">Description</th>\n";
        $b .= $i."  <th class=\"h\">Balance on date</th>\n";
        $b .= $i." </tr>\n";

        if ($z) {


            if ($newest == 2) {
                $y = '';
                $tmp_arr = explode('<tr>',$z);
                $num_entries = (count($tmp_arr) - 1);
                for ($iii=$num_entries;$iii>-1;$iii--) {
                    if ($tmp_arr[$iii]) {
                        $y .= '<tr>'.$i.' '.$tmp_arr[$iii];
                    }
                }
                $z = $y;
            }


            $z = $b.$z;
            $z .= $i."</table>\n";
            $b .= $i."</div>\n";
            $z .= $i."<!-- /transaction_history -->\n";

        } else {
            $z = $b.
            $z .= $i."</table>\n";
            $z .= $i."There were no ".strtolower(TRANSACTION_NAME_PLURAL)." found\n";
            $b .= $i."</div>\n";
            $z .= $i."<!-- /transaction_history -->\n";

        }
        return $z;
    }

    function search_html($i,$url,$start_day,$start_month,$start_year,$end_day,$end_month,$end_year,$type,$member_id,$order) {
        $z = $i."<!-- transaction_search -->\n";
        $z .= $i."<div id=\"transaction_search\">\n";
        $z .= $i." <form name=\"transaction_dates\" id=\"transaction_dates\" method=\"post\" action=\"".URL.$url;
        if ($member_id != $_SESSION['member_id']) {
            $z .= $member_id.'/'.append_url()."\">\n";
        } else {
            $z .= append_url()."\">\n";
        }
        $z .= $i." Start: ";
        $z .= date_form($i,array('day' => $start_day, 'month' => $start_month, 'year' => $start_year),'start_');
        $z .= $i."  End: ";
        $z .= date_form($i,array('day' => $end_day, 'month' => $end_month, 'year' => $end_year),'end_');
        $z .= "<br class=\"left\">\n";

        if (user_type() == 2 or ALLOW_VIEW_OTHER_TRANSACTION_HISTORY) {
            $z .= $i.'  '.ucwords(MEMBERS_NAME_SINGULAR).': <input type="text" id="member_id_to_view" name="member_id_to_view" maxlength="9" value="'.$member_id.'" />'."\n";
        }
        $z .= $i."  ".ucwords(TRANSACTION_NAME_SINGULAR)." Type: ";
        $z .= $i."  <select id=\"transaction_type\" name=\"transaction_type\">\n";
        $z .= $i."   <option value=\"1\"".$this->add_selected($type,"1",1).">All</option>\n";
        $z .= $i."   <option value=\"2\"".$this->add_selected($type,"2",0).">Purchases</option>\n";
        $z .= $i."   <option value=\"3\"".$this->add_selected($type,"3",0).">Sales</option>\n";
        $z .= $i."   <option value=\"4\"".$this->add_selected($type,"4",0).">Buy Nows</option>\n";
        $z .= $i."   <option value=\"5\"".$this->add_selected($type,"5",0).">Auction Wins</option>\n";
        $z .= $i."   <option value=\"6\"".$this->add_selected($type,"6",0).">".ucwords(TRANSACTION_NAME_SINGULAR)." Fees</option>\n";
        $z .= $i."   <option value=\"7\"".$this->add_selected($type,"7",0).">Other</option>\n";
        $z .= $i."  </select>\n";
        $z .= $i."  <select id=\"order\" name=\"order\">\n";
        $z .= $i."   <option value=\"2\"".$this->add_selected($order,"2",1).">Newest to Oldest</option>\n";
        $z .= $i."   <option value=\"1\"".$this->add_selected($order,"1",0).">Oldest to Newest</option>\n";
        $z .= $i."  </select>\n";
        $z .= $i."  <input id=\"tranaction_search_button\" type=\"submit\" name=\"submit\" value=\"Go\" />\n";
        $z .= $i." </form>\n";
        $z .= $i."</div>\n";
        $z .= $i."<!-- /transaction_search -->\n";
        return $z;
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
    function balance($member_id) {
        $this->balance = '';
        $transaction_balance_query = mysql_query('SELECT * FROM transactions WHERE buyerID = '.$member_id.' OR sellerID = '.$member_id);
        if (!$transaction_balance_query) {
            $this->error = 'Database Error';
            if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('There was a database error when trying to find '.a(MEMBERS_NAME_SINGULAR).' '.strtolower(MEMBERS_NAME_SINGULAR).'\'s balance in transactions.class.php');
            if (ENABLE_ERROR_LOG) log_error('There was a database error when trying to find '.a(MEMBERS_NAME_SINGULAR).' '.strtolower(MEMBERS_NAME_SINGULAR).'\'s balance in transactions.class.php<br />Error:'.mysql_error());
            return false;
        }
        $sales = false;
        $purchases = false;

        $member_balance_query = mysql_query('SELECT SUM(amount) as purchases FROM transactions WHERE buyerID = '.$member_id);
        if (!$member_balance_query) {
            $this->error = 'Database Error';
            if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('There was a database error when trying to find '.a(MEMBERS_NAME_SINGULAR).' '.strtolower(MEMBERS_NAME_SINGULAR).'\'s balance in transactions.class.php');
            if (ENABLE_ERROR_LOG) log_error('There was a database error when trying to find '.a(MEMBERS_NAME_SINGULAR).' '.strtolower(MEMBERS_NAME_SINGULAR).'\'s balance in transactions.class.php<br />Error:'.mysql_error());
            return false;
        }
        $purchases = mysql_result($member_balance_query,0,'purchases');
        $member_sales_query = mysql_query('SELECT SUM(amount) as sales FROM transactions WHERE sellerID = '.$member_id);
        if (!$member_balance_query) {
            $this->error = 'Database Error';
            if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('There was a database error when trying to find '.a(MEMBERS_NAME_SINGULAR).' '.strtolower(MEMBERS_NAME_SINGULAR).'\'s balance in transactions.class.php');
            if (ENABLE_ERROR_LOG) log_error('There was a database error when trying to find '.a(MEMBERS_NAME_SINGULAR).' '.strtolower(MEMBERS_NAME_SINGULAR).'\'s balance in transactions.class.php<br />Error:'.mysql_error());
            return false;
        }
        $sales = mysql_result($member_sales_query,0,'sales');
        $this->balance = $sales - $purchases;
        return true;
    }
    function check_all_balances($i) {
        $mysql = new mysql;
        $update_mysql = new mysql;
        global $user;

        if (!$mysql->build_array('SELECT balance, accountID, validated, suspended, deleted, first_name, last_name FROM accounts')) {
            return $mysql->error;
        }

        if (count($mysql->result)) {
            $old_counter = 0;
            $new_counter = 0;

            $z = $i.'<!-- checking balances -->'."\n";
            $z .= $i.'<h2>Balance Analysis</h2>'."\n";
            $z .= $i.'<div id="balance_analysis">'."\n";
            $z .= $i.' <table>'."\n";
            $z .= $i.'  <tr>'."\n";
            $z .= $i.'   <th class="h">'.ucwords(MEMBERS_NAME_SINGULAR).' ID'."\n";
            $z .= $i.'   </th>'."\n";
            $z .= $i.'   <th class="h">Status'."\n";
            $z .= $i.'   </th>'."\n";
            $z .= $i.'   <th class="h">Name'."\n";
            $z .= $i.'   </th>'."\n";
            $z .= $i.'   <th class="h">Last Balance'."\n";
            $z .= $i.'   </th>'."\n";
            $z .= $i.'   <th class="h">Real Balance'."\n";
            $z .= $i.'   </th>'."\n";
            $z .= $i.'  </tr>'."\n";

            foreach ($mysql->result as $member) {
                $z .= $i.'  <tr>'."\n";
                $z .= $i.'   <td>'.$member['accountID']."\n";
                $z .= $i.'   </td>'."\n";
                $member_status = '';
                if ($member['validated']) {
                    $member_status .= 'Validated';
                }
                if ($member['suspended']) {
                    if ($member_status) {
                        $member_status .= ', Suspended';
                    } else {
                        $member_status .= 'Suspended';
                    }
                }
                if ($member['deleted']) {
                    if ($member_status) {
                        $member_status .= ', Deleted';
                    } else {
                        $member_status .= 'Deleted';
                    }
                }
                $z .= $i.'   <td>'.$member_status."\n";
                $z .= $i.'   </td>'."\n";
                $z .= $i.'   <td>'.$member['first_name'].' '.$member['last_name']."\n";
                $z .= $i.'   </td>'."\n";
                $z .= $i.'   <td>'.$member['balance']."\n";
                $z .= $i.'   </td>'."\n";
                if ($this->balance($member['accountID'])) {
                    $new_balance = $this->balance;
                } else {
                    $new_balance = 'EEEEERRRRROOOOOORRRRRRRRRRRR';
                }

                if ($member['balance'] != $new_balance) {
                    $update_mysql->query('UPDATE accounts SET balance = '.$new_balance.' WHERE accountID = '.$member['accountID'].' LIMIT 1');
                    $z .= $i.'   <td><strong>'.number_format($new_balance,2,'.','')."<strong>\n";
                    $z .= $i.'   </td>'."\n";
                } else {
                    $z .= $i.'   <td>'.number_format($new_balance,2,'.','')."\n";
                    $z .= $i.'   </td>'."\n";
                }


                $z .= $i.'  </tr>'."\n";

                $old_counter = $old_counter + $member['balance'];
                $new_counter = $new_counter + $new_balance;


            }
            $z .= $i.' </table>'."\n";
            $z .= $i.'</div><br />'."\n";
            $z .= $i.'Old balances added up to <strong>'.number_format($old_counter,2,'.','')."</strong><br />\n";
            $z .= $i.'New balances added up to <strong>'.number_format($new_counter,2,'.','')."</strong><br /><br />\n";
            $z .= $i.'<!-- end of checking balances -->'."\n";
        } else {
            return $i.'No rows returned';
        }
        return $z;
    }
    function make_transaction($type,$buyer_id,$seller_id,$amount,$description,$day,$month,$year,$hour = 0,$minute = 0,$second = 0,$noticeboard_id = 0,$disable_buyer_service_fee = false,$disable_seller_service_fee = false) {
        $mysql = new mysql;
        global $user;
        global $links;
        if ($buyer_id == $seller_id) {
            $this->error = 'Buyer and Seller are the same';
            return false;
        }
        if (($this->member_suspended($buyer_id) or $this->member_suspended($buyer_id)) and user_type() != 2) {
            return false;
        }
        $this->error = '';
        if ($mysql->query("INSERT INTO transactions VALUES ('','".$buyer_id."','".$seller_id."','".$amount."','".mysql_real_escape_string($description)."','".$day."','".$month."','".$year."','".$hour."','".$minute."','".$second."','".$type."','".$noticeboard_id."')")) {
            $new_transaction_id = mysql_insert_id();
            if ($type != 6 and $type != 7) {
                if (!$this->transaction_fee($buyer_id,$seller_id,$new_transaction_id,$noticeboard_id,$disable_buyer_service_fee,$disable_seller_service_fee)) {
                    // error documented in transaction_fee
                }
            }
            if ($this->balance($buyer_id)) {
                $user->update_balance($this->balance,$buyer_id);
                $buyer_balance = $this->balance;
            } else {
                $this->error .= ucfirst(TRANSACTION_NAME_SINGULAR).' successfull but balance not updated.';
            }
            if ($this->balance($seller_id)) {
                $user->update_balance($this->balance,$seller_id);
                $seller_balance = $this->balance;
            } else {
                $this->error .= ucfirst(TRANSACTION_NAME_SINGULAR).' successfull but balance not updated.';
            }
            if (!$this->error) {
                $this->id = $new_transaction_id;
                if (ENABLE_EMAIL) {
                    $links->build_url(1,2);
                    $transaction_html = '<strong>Amount:</strong> '.number_format($amount,2).'<br />';
                    $transaction_html .= '<strong>'.ucwords(TRANSACTION_NAME_SINGULAR).' Type:</strong> '.$this->type($type).'<br />';
                    $transaction_html .= '<strong>Description:</strong> '.indent_variable('',$description).'<br />';
                    $transaction_html .= '<strong>Buyer\'s new balance:</strong> '.number_format($buyer_balance,2).'<br />';
                    $transaction_html .= '<strong>Seller\'s new balance:</strong> '.number_format($seller_balance,2).'<br />';
                    if ($noticeboard_id) {
                        $transaction_html .= '<strong>'.ucwords(NOTICEBOARD_NAME_SINGULAR).':</strong> <a href="'.URL.NOTICEBOARD_URL.'/'.$noticeboard_id.'/">'.$noticeboard_id.'</a><br />';
                    }
                    $transaction_html .= '<strong>Time:</strong> '.return_month($GLOBALS['date']['month']).' '.$GLOBALS['date']['day'].', '.$GLOBALS['date']['year'].' - '.return_time($GLOBALS['date']['hour'],$GLOBALS['date']['minutes']).'<br /><br /><br />';


                    if ($mysql->result('SELECT receive_email_buy, first_name, last_name, email_address FROM accounts WHERE accountID = '.$buyer_id.' AND email_address != \'\'')) {
                        $email_html = 'Dear '.$mysql->result['first_name'].',<br /><br />';
                        $email_html .= 'A '.strtolower(TRANSACTION_NAME_SINGULAR).' has been recorded with you as buyer and '.$user->full_name($seller_id).' as seller.<br /><br />';
                        $email_html .= $transaction_html;
                        send_single_email(UPDATE_EMAIL,EMAIL_FROM_NAME,$mysql->result['email_address'],$mysql->result['first_name'].' '.$mysql->result['last_name'],ucfirst(a(TRANSACTION_NAME_SINGULAR)).' '.strtolower(TRANSACTION_NAME_SINGULAR).' has been recorded',strip_tags(str_replace('<br />',"\r\n\r\n",$email_html)).'Change your email delivery options here: '.URL.$links->complete_url,$email_html.'Change your email delivery options <a href="'.URL.$links->complete_url.'">here</a>');
                    }
                    if ($mysql->result('SELECT receive_email_sell, first_name, last_name, email_address FROM accounts WHERE accountID = '.$seller_id.' AND email_address != \'\'')) {
                        $email_html = 'Dear '.$mysql->result['first_name'].',<br /><br />';
                        $email_html .= 'A '.strtolower(TRANSACTION_NAME_SINGULAR).' has been recorded with you as seller and '.$user->full_name($buyer_id).' as buyer.<br /><br />';
                        $email_html .= $transaction_html;
                        send_single_email(UPDATE_EMAIL,EMAIL_FROM_NAME,$mysql->result['email_address'],$mysql->result['first_name'].' '.$mysql->result['last_name'],ucfirst(a(TRANSACTION_NAME_SINGULAR)).' '.strtolower(TRANSACTION_NAME_SINGULAR).' has been recorded',strip_tags(str_replace('<br />',"\r\n\r\n",$email_html)).'Change your email delivery options here: '.URL.$links->complete_url,$email_html.'Change your email delivery options <a href="'.URL.$links->complete_url.'">here</a>');
                    }
                }
                return true;
            } else {
                return false;
            }
        } else {
            $this->error = 'Database Error';
            if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('There was a database error when trying to insert '.a(TRANSACTION_NAME_SINGULAR).' '.strtolower(TRANSACTION_NAME_SINGULAR).' in transactions.class.php');
            if (ENABLE_ERROR_LOG) log_error('There was a database error when trying to insert '.a(TRANSACTION_NAME_SINGULAR).' '.strtolower(TRANSACTION_NAME_SINGULAR).' in transactions.class.php<br />Error:'.$mysql->error);
        }
        return false;
    }
    function html($i,$type,$seller_id,$action_page,$amount = '',$description = '') {
    //  either type "member" or "admin"
    //  admin must show buyer as well as seller fields
        if ($amount == 0) $amount = '';
        $z = $i.'<!-- transaction_form -->'."\n";
        $z .= $i.'<div id="transaction_form">'."\n";
        $z .= $i." <fieldset>\n";
        $z .= $i." <legend>\n";
        $z .= $i.' '.ucwords(TRANSACTION_NAME_SINGULAR)." Details\n";
        $z .= $i." </legend>\n";
        $z .= $i.' <form name="transaction" id="transaction" method="post" action="'.URL.$action_page.append_url().'">'."\n";

        if ($type == 'admin') {
            $z .= $i.'  <label for="buyer_id">Buyer ID:</label>'."\n";
            $z .= $i.'  <input type="text" id="buyer_id" name="buyer_id" maxlength="6" value="'.$_SESSION['member_id'].'" /><br class="left" />'."\n";
        }
        $z .= $i.'  <label for="seller_id">Seller ID:</label>'."\n";
        $z .= $i.'  <input type="text" id="seller_id" name="seller_id" maxlength="6" value="'.$seller_id.'" /><br class="left" />'."\n";
        $z .= $i.'  <label for="transaction_amount">Amount:</label>'."\n";
        $z .= $i.'  <input type="text" id="transaction_amount" name="transaction_amount" maxlength="10" value="'.$amount.'" /><br class="left" />'."\n";
        $z .= $i.'  <label for="transaction_description">Description:</label>'."\n";
        $z .= $i.'  <textarea id="transaction_description" name="transaction_description" cols=\"30\" rows=\"3\">'.$description."</textarea>\n";
        $z .= $i.'  <input class="transaction_button" type="submit" name="submit" value="Submit '.ucwords(TRANSACTION_NAME_SINGULAR).'" />'."\n";
        $z .= $i.' </form>'."\n";
        $z .= $i." </fieldset>\n";
        $z .= $i.'</div>'."\n";
        $z .= $i.'<!-- /transaction_form -->'."\n";
        return $z;
    }
    function bulk_trading_form($i,$url,$num = 20) {
        $z = $i."<!-- bulk_trading_form -->\n";
        $z .= $i."<div id=\"bulk_trading\">\n";
        $z .= $i." <strong>Notes:</strong><br /><br />\n";
        $z .= $i." Enter as many ".strtolower(TRANSACTION_NAME_PLURAL)." in this form as you like.<br /><br />Buyer ID, Seller ID, Amount and Description are required.<br />\n";
        $z .= $i." ".ucwords(NOTICEBOARD_NAME_SINGULAR)." IDs are optional. This data could allow ".strtolower(MEMBERS_NAME_PLURAL)." to know which of their ".strtolower(NOTICEBOARD_NAME_PLURAL)." are generating interest<br /><br />\n";
        $z .= $i." <fieldset>\n";
        $z .= $i." <legend>\n";
        $z .= $i." Bulk ".ucwords(TRANSACTION_NAME_PLURAL)."\n";
        $z .= $i." </legend>\n";
        $z .= $i.' <form name="transaction" method="post" action="'.URL.$url.append_url($url).'">'."\n";
        $z .= $i."  <table>\n";
        $z .= $i."   <tr>\n";
        $z .= $i."    <th class=\"h\">Buyer ID</th>\n";
        $z .= $i."    <th class=\"h\">Seller ID</th>\n";
        $z .= $i."    <th class=\"h\">Amount</th>\n";
        $z .= $i."    <th class=\"h\">Description</th>\n";
        $z .= $i."    <th class=\"h\">".ucwords(NOTICEBOARD_NAME_SINGULAR)." ID</th>\n";
        $z .= $i."    <th class=\"h\">".ucwords(TRANSACTION_NAME_SINGULAR)." Type</th>\n";
        if (TRANSACTION_SERVICE_FEE_BUYER != '0.00') {
            $z .= $i."    <th class=\"h\">Disable Buyer's Fee?</th>\n";
        }
        if (TRANSACTION_SERVICE_FEE_SELLER != '0.00') {
            $z .= $i."    <th class=\"h\">Disable Seller's Fee?</th>\n";
        }
        $z .= $i."   </tr>\n";

        for($t=0;$t<$num;$t++) {
            if (!isset($this->bulk_post['buyer_id'][$t]))           $this->bulk_post['buyer_id'][$t]            =   '';
            if (!isset($this->bulk_post['seller_id'][$t]))          $this->bulk_post['seller_id'][$t]           =   '';
            if (!isset($this->bulk_post['amount'][$t]))             $this->bulk_post['amount'][$t]              =   '';
            if (!isset($this->bulk_post['description'][$t]))        $this->bulk_post['description'][$t]         =   '';
            if (!isset($this->bulk_post['noticeboard_id'][$t]))     $this->bulk_post['noticeboard_id'][$t]      =   '';
            if (!isset($this->bulk_post['transaction_type'][$t]))   $this->bulk_post['transaction_type'][$t]    =   '';
            if (!isset($this->bulk_post['disable_buyer_fee'][$t]))  $this->bulk_post['disable_buyer_fee'][$t]   =   '';
            if (!isset($this->bulk_post['disable_seller_fee'][$t])) $this->bulk_post['disable_seller_fee'][$t]  =   '';
            $z .= $i."   <tr>\n";
            $z .= $i."    <td>\n";
            $z .= $i.'     <input type="text" name="buyer_id['.$t.']" value="'.$this->bulk_post['buyer_id'][$t].'" /></td>'."\n";
            $z .= $i."    <td>\n";
            $z .= $i.'     <input type="text" name="seller_id['.$t.']" value="'.$this->bulk_post['seller_id'][$t].'" /></td>'."\n";
            $z .= $i."    <td>\n";
            $z .= $i.'     <input type="text" name="amount['.$t.']" value="'.$this->bulk_post['amount'][$t].'" /></td>'."\n";
            $z .= $i."    <td>\n";
            $z .= $i.'     <textarea class="description" name="description['.$t.']" />'.$this->bulk_post['description'][$t].'</textarea></td>'."\n";
            $z .= $i."    <td>\n";
            $z .= $i.'     <input type="text" name="noticeboard_id['.$t.']" value="'.$this->bulk_post['noticeboard_id'][$t].'" /></td>'."\n";
            $z .= $i."    <td>\n";
            $z .= $i."     <select name=\"transaction_type[".$t."]\">\n";
            $z .= $i."      <option value=\"1\"".$this->add_selected($this->bulk_post['transaction_type'][$t],"1",1).">Normal</option>\n";
            $z .= $i."      <option value=\"2\"".$this->add_selected($this->bulk_post['transaction_type'][$t],"2",0).">Buy Now</option>\n";
            $z .= $i."      <option value=\"3\"".$this->add_selected($this->bulk_post['transaction_type'][$t],"3",0).">Auction</option>\n";
            $z .= $i."      <option value=\"4\"".$this->add_selected($this->bulk_post['transaction_type'][$t],"4",0).">Fee</option>\n";
            $z .= $i."     </select>\n";
            $z .= $i."    </td>\n";
            if (TRANSACTION_SERVICE_FEE_BUYER != '0.00') {
                $z .= $i."    <td>\n";
                $z .= $i.'     <input type="checkbox" name="disable_buyer_fee['.$t.']" value="1"'.set_checked($this->bulk_post['disable_buyer_fee'][$t])." /></td>\n";
            }
            if (TRANSACTION_SERVICE_FEE_SELLER != '0.00') {
                $z .= $i."    <td>\n";
                $z .= $i.'     <input type="checkbox" name="disable_seller_fee['.$t.']" value="1"'.set_checked($this->bulk_post['disable_seller_fee'][$t])." /></td>\n";
            }
            $z .= $i."   </tr>\n";
        }
        $z .= $i."  </table>\n";
        $z .= $i.'  <br /><input class="transaction_button" type="submit" name="submit" value="Submit '.ucwords(TRANSACTION_NAME_PLURAL).'" />'."\n";
        $z .= $i.' </form>'."\n";
        $z .= $i." </fieldset>\n";
        $z .= $i.'</div>'."\n";
        $z .= $i."<!-- /bulk_trading_form -->\n";
        return $z;
    }
    function process_bulk_post($i,$url,$process = false) {
        $mysql = new mysql;
        global $user;
        $this->bulk_submission_errors = '';
        if (!is_array($_POST) or !$_POST['buyer_id'][0]) return false;
        $this->bulk_post = array();
        $t=0;
        $c=0;
        $this->num_bulk_transactions_processed = 0;
        foreach ($_POST['buyer_id'] as $transaction) {
            if ($_POST['buyer_id'][$t] and $_POST['seller_id'][$t] and $_POST['amount'][$t] and $_POST['description'][$t]) {
                $buyer_name = $user->full_name($_POST['buyer_id'][$t]);
                $seller_name = $user->full_name($_POST['seller_id'][$t]);
                if (!$this->pre_confirm_trade($_POST['buyer_id'][$t],$_POST['seller_id'][$t],$_POST['amount'][$t])) {
                    if (!$buyer_name) {
                        $buyer_name = ucwords(MEMBERS_NAME_SINGULAR).' #'.$_POST['buyer_id'][$t];
                    }
                    if (!$seller_name) {
                        $seller_name = ucwords(MEMBERS_NAME_SINGULAR).' #'.$_POST['seller_id'][$t];
                    }
                    $this->bulk_submission_errors .= 'Error found in '.strtolower(TRANSACTION_NAME_SINGULAR).' #'.$c.' (between '.ucwords($buyer_name).' and '.ucwords($seller_name).'): '.$this->error.'<br /><br />';
                }
                $this->bulk_post['buyer_id'][$c] = remove_slashes($_POST['buyer_id'][$t]);
                $this->bulk_post['seller_id'][$c] = remove_slashes($_POST['seller_id'][$t]);
                $this->bulk_post['amount'][$c] = remove_slashes($_POST['amount'][$t]);
                if (!is_numeric($this->bulk_post['amount'][$c]) or $this->bulk_post['amount'][$c] < 0) {
                    $this->bulk_submission_errors .= 'Error found in '.strtolower(TRANSACTION_NAME_SINGULAR).' #'.$c.' (between '.ucwords($buyer_name).' and '.ucwords($seller_name).'): Amount given is not a positive number<br /><br />';
                }
                $this->bulk_post['description'][$c] = remove_slashes($_POST['description'][$t]);
                if (VALIDATE_XHTML) {
                    $xhtml_report = valid_XHTML($this->bulk_post['description'][$c]);
                    if ($xhtml_report) {
                        $this->bulk_submission_errors .= 'Error found in '.strtolower(TRANSACTION_NAME_SINGULAR).' #'.$c.' (between '.ucwords($buyer_name).' and '.ucwords($seller_name).'): '.$xhtml_report.'<br /><br />';
                    }
                }
                $this->bulk_post['noticeboard_id'][$c] = remove_slashes($_POST['noticeboard_id'][$t]);
                if ($this->bulk_post['noticeboard_id'][$c]) {
                    if ($mysql->num_rows('SELECT noticeboardID FROM noticeboard WHERE noticeboardID = '.$this->bulk_post['noticeboard_id'][$c].' LIMIT 1')) {
                        if (!$mysql->num_rows) {
                            $this->bulk_submission_errors .= 'Error found in '.strtolower(TRANSACTION_NAME_SINGULAR).' #'.$c.' (between '.ucwords($buyer_name).' and '.ucwords($seller_name).'): '.ucfirst(NOTICEBOARD_NAME_SINGULAR).' #'.$this->bulk_post['noticeboard_id'][$c].' not found<br /><br />';
                        }
                    }
                } else {
                    $this->bulk_post['noticeboard_id'][$c] = '';
                }
                if (isset($_POST['transaction_type'][$t]))      $this->bulk_post['transaction_type'][$c] = remove_slashes($_POST['transaction_type'][$t]);      else    $this->bulk_post['transaction_type'][$c] = 1;
                if (isset($_POST['disable_buyer_fee'][$t]))     $this->bulk_post['disable_buyer_fee'][$c] = remove_slashes($_POST['disable_buyer_fee'][$t]);    else    $this->bulk_post['disable_buyer_fee'][$c] = 0;
                if (isset($_POST['disable_seller_fee'][$t]))    $this->bulk_post['disable_seller_fee'][$c] = remove_slashes($_POST['disable_seller_fee'][$t]);  else    $this->bulk_post['disable_seller_fee'][$c] = 0;
                if ($process) {
                    if ($this->make_transaction($this->bulk_post['transaction_type'][$c],$this->bulk_post['buyer_id'][$c],$this->bulk_post['seller_id'][$c],$this->bulk_post['amount'][$c],$this->bulk_post['description'][$c],$GLOBALS['date']['day'],$GLOBALS['date']['month'],$GLOBALS['date']['year'],$GLOBALS['date']['hour'],$GLOBALS['date']['minutes'],$GLOBALS['date']['seconds'],$this->bulk_post['noticeboard_id'][$c],$this->bulk_post['disable_buyer_fee'][$c],$this->bulk_post['disable_seller_fee'][$c])) {
                        $this->bulk_post['transaction_id'][$c] = $this->id;
                        $this->num_bulk_transactions_processed++;
                    } else {
                        $this->bulk_post['transaction_id'][$c] = 0;
                    }
                }
                $c++;
            }
            $t++;
        }
        if ($this->bulk_submission_errors) {
            return false;
        }

        $this->num_bulk_transactions = count($this->bulk_post['buyer_id']);

        $z = $i."<!-- bulk_trading_result -->\n";
        $z .= $i."<div id=\"bulk_trading_result\">\n";
        $z .= $i." <table>\n";
        $z .= $i."  <tr>\n";
        if ($process) {
            $z .= $i."   <th class=\"h\">".ucwords(TRANSACTION_NAME_SINGULAR)." ID</th>\n";
        }
        $z .= $i."   <th class=\"h\">Buyer ID</th>\n";
        $z .= $i."   <th class=\"h\">Seller ID</th>\n";
        $z .= $i."   <th class=\"h\">Amount</th>\n";
        $z .= $i."   <th class=\"h\">Description</th>\n";
        $z .= $i."   <th class=\"h\">".ucwords(NOTICEBOARD_NAME_SINGULAR)." ID</th>\n";
        $z .= $i."   <th class=\"h\">".ucwords(TRANSACTION_NAME_SINGULAR)." Type</th>\n";
        if (TRANSACTION_SERVICE_FEE_BUYER != '0.00') {
            $z .= $i."   <th class=\"h\">Disable Buyer's Fee?</th>\n";
        }
        if (TRANSACTION_SERVICE_FEE_SELLER != '0.00') {
            $z .= $i."   <th class=\"h\">Disable Seller's Fee?</th>\n";
        }
        $z .= $i."  </tr>\n";
        if (BULK_TRADING_CONFIRM) {
            $f = $i."<!-- bulk_trading_confirmation_form -->\n";
            $f .= $i."<div id=\"bulk_trading_confirmation_form\">\n";
            $f .= $i.' <form name="transaction" method="post" action="'.URL.$url.append_url($url).'">'."\n";
        }
        for($t=0;$t<$this->num_bulk_transactions;$t++) {
            // going to build a table with all the data as well as a form for confirmation
            $z .= $i."  <tr>\n";
            if ($process) {
                if ($this->bulk_post['transaction_id'][$t]) {
                    $z .= $i.'   <td>'.$this->bulk_post['transaction_id'][$t]."</td>\n";
                } else {
                    $z .= $i.'   <td>'.ucwords(TRANSACTION_NAME_SINGULAR)." Failed</td>\n";
                }
            }
            $z .= $i.'   <td>'.$this->bulk_post['buyer_id'][$t]." (".$user->full_name($this->bulk_post['buyer_id'][$t]).")</td>\n";
            $z .= $i.'   <td>'.$this->bulk_post['seller_id'][$t]." (".$user->full_name($this->bulk_post['seller_id'][$t]).")</td>\n";
            $z .= $i.'   <td>'.number_format($this->bulk_post['amount'][$t],2)."</td>\n";
            $z .= $i.'   <td>'.$this->bulk_post['description'][$t]."</td>\n";
            $z .= $i.'   <td>'.$this->bulk_post['noticeboard_id'][$t]."</td>\n";
            switch ($this->bulk_post['transaction_type'][$t]) {
                case 1: $z .= $i."   <td>Normal</td>\n"; break;
                case 2: $z .= $i."   <td>Buy Now</td>\n"; break;
                case 3: $z .= $i."   <td>Auction</td>\n"; break;
                case 4: $z .= $i."   <td>Fee</td>\n"; break;
            }
            if (TRANSACTION_SERVICE_FEE_BUYER != '0.00') {
                if ($this->bulk_post['disable_buyer_fee'][$t]) {
                    $z .= $i."   <td>YES</td>\n";
                } else {
                    $z .= $i."   <td>NO</td>\n";
                }
            }
            if (TRANSACTION_SERVICE_FEE_SELLER != '0.00') {
                if ($this->bulk_post['disable_seller_fee'][$t]) {
                    $z .= $i."   <td>YES</td>\n";
                } else {
                    $z .= $i."   <td>NO</td>\n";
                }
            }
            $z .= $i."  </tr>\n";
            if (BULK_TRADING_CONFIRM) {
                $f .= $i.'  <input type="hidden" name="buyer_id['.$t.']" value="'.$this->bulk_post['buyer_id'][$t].'" />'."\n";
                $f .= $i.'  <input type="hidden" name="seller_id['.$t.']" value="'.$this->bulk_post['seller_id'][$t].'" />'."\n";
                $f .= $i.'  <input type="hidden" name="amount['.$t.']" value="'.$this->bulk_post['amount'][$t].'" />'."\n";
                $f .= $i.'  <input type="hidden" name="description['.$t.']" value="'.$this->bulk_post['description'][$t].'" />'."\n";
                $f .= $i.'  <input type="hidden" name="noticeboard_id['.$t.']" value="'.$this->bulk_post['noticeboard_id'][$t].'" />'."\n";
                $f .= $i.'  <input type="hidden" name="transaction_type['.$t.']" value="'.$this->bulk_post['transaction_type'][$t].'" />'."\n";
                if (TRANSACTION_SERVICE_FEE_BUYER != '0.00') {
                    $f .= $i.'     <input type="hidden" name="disable_buyer_fee['.$t.']" value="'.$this->bulk_post['disable_buyer_fee'][$t].'" />'."\n";
                }
                if (TRANSACTION_SERVICE_FEE_SELLER != '0.00') {
                    $f .= $i.'     <input type="hidden" name="disable_seller_fee['.$t.']" value="'.$this->bulk_post['disable_seller_fee'][$t].'" />'."\n";
                }
            }
        }
        $z .= $i." </table>\n";
        $z .= $i."</div>\n";
        $z .= $i."<!-- /bulk_trading_result -->\n";
        $f .= $i.'  <br /><input class="transaction_button" type="submit" name="submit" value="Confirm '.ucwords(TRANSACTION_NAME_PLURAL).'" />'."\n";
        $f .= $i.'  <input class="transaction_button" type="submit" name="submit" value="Edit '.ucwords(TRANSACTION_NAME_PLURAL).'" />'."\n";
        $f .= $i." </form>\n";
        $f .= $i."</div>\n";
        $f .= $i."<!-- /bulk_trading_confirmation_form -->\n";
        $this->bulk_summary = $z;
        $this->bulk_confirm_form = $f;
        return true;
    }
    function confirm_distribution_html($i,$url) {
        $mysql = new mysql;
        global $user;
        $total_accounts = 0;
        if (!isset($_POST['amount']))                       $_POST['amount']                    =   0;
        if (!isset($_POST['member_id']))                    $_POST['member_id']                 =   0;
        if (!isset($_POST['description']))                  $_POST['description']               =   '';
        if (!isset($_POST['include_suspended_members']))    $_POST['include_suspended_members'] =   0;
        if ($_POST['include_suspended_members']) {
            $total_accounts = ($user->num_active_members() + $user->num_suspended_members()) - 1;
        } else {
            $total_accounts = $user->num_active_members() - 1;
        }
        $amount_per_member = round(($_POST['amount'] / $total_accounts),2);
        $z = $i."<!-- confirm_distributione_html -->\n";
        $z .= $i."<div id=\"confirm_distribution_form\">\n";
        $z .= $i." <fieldset>\n";
        if ($_POST['include_suspended_members']) {
            $z .= $i." <strong>Confirm:</strong> All ".strtolower(MEMBERS_NAME_PLURAL)." will pay ".$amount_per_member.' '.ucwords(CURRENCY_NAME)." to ".ucwords($user->full_name($_POST['member_id'])).".<br /><br />\n";
        } else {
            $z .= $i." <strong>Confirm:</strong> All non-suspended ".strtolower(MEMBERS_NAME_PLURAL)." will pay ".$amount_per_member.' '.ucwords(CURRENCY_NAME)." to ".ucwords($user->full_name($_POST['member_id'])).".<br /><br />\n";
        }
        $z .= $i." <strong>Note:</strong> You will be able to undo this operation until midnight tonight.<br /><br />\n";
        $z .= $i.' <form name="member_distribute" method="post" action="'.URL.$url.append_url($url).'">'."\n";
        $z .= $i.'  <input type="hidden" name="member_id" value="'.$_POST['member_id'].'" />'."\n";
        $z .= $i.'  <input type="hidden" name="amount" value="'.$_POST['amount'].'" />'."\n";
        $z .= $i.'  <input type="hidden" name="description" value="'.mysql_real_escape_string($_POST['description']).'" />'."\n";
        $z .= $i.'  <input type="hidden" name="include_suspended_members" value="'.$_POST['include_suspended_members'].'" />'."\n";
        $z .= $i.'  <input id="confirm_transaction_button" class="confirm_transaction_button" type="submit" name="submit" value="Confirm '.ucwords(TRANSACTION_NAME_SINGULAR).'" /><br class="left" />'."\n";
        $z .= $i.'  <input id="cancel_transaction_button" class="cancel_transaction_button" type="submit" name="submit" value="Cancel" />'."\n";
        $z .= $i." </form>\n";
        $z .= $i."</div>\n";
        $z .= $i."<!-- /confirm_distributione_html -->\n";
        return $z;
    }
    function distribute_account() {
        $mysql = new mysql;
        $secondary_mysql = new mysql;
        global $date;
        global $user;
        global $links;
        $total_accounts = 0;
        if (!isset($_POST['include_suspended_members']))    $_POST['include_suspended_members'] =   1;
        if (!isset($_POST['amount']))                       $_POST['amount']                    =   0;
        if (!isset($_POST['member_id']))                    $_POST['member_id']                 =   0;
        if (!isset($_POST['description']))                  $_POST['description']               =   '';
        if ($_POST['include_suspended_members']) {
            $total_accounts = ($user->num_active_members() + $user->num_suspended_members()) - 1;
        } else {
            $total_accounts = $user->num_active_members() - 1;
        }
        $amount_per_member = round(($_POST['amount'] / $total_accounts),2);

        if ($_POST['include_suspended_members']) {
            $query = 'SELECT accountID FROM accounts WHERE validated = 1 AND deleted = 0 AND accountID != '.$_POST['member_id'];
        } else {
            $query = 'SELECT accountID FROM accounts WHERE validated = 1 AND suspended = 0 AND accountID != '.$_POST['member_id'];
        }
        if (!$mysql->build_array($query)) {
            return $mysql->error;
        } else {
            $another_mysql = new mysql;
            $members = $mysql->result;
            $errors = '';
            $successful_transactions = 0;
            foreach($members as $member) {
                $insert = array();
                $insert[0]['name'] = 'buyerID';
                $insert[0]['value'] = $member["accountID"];
                $insert[1]['name'] = 'sellerID';
                $insert[1]['value'] = $_POST['member_id'];
                $insert[2]['name'] = 'amount';
                $insert[2]['value'] = $amount_per_member;
                $insert[3]['name'] = 'description';
                $insert[3]['value'] = mysql_real_escape_string($_POST['description']);
                $insert[4]['name'] = 'day';
                $insert[4]['value'] = $date['day'];
                $insert[5]['name'] = 'month';
                $insert[5]['value'] = $date['month'];
                $insert[6]['name'] = 'year';
                $insert[6]['value'] = $date['year'];
                $insert[7]['name'] = 'hour';
                $insert[7]['value'] = $date['hour'];
                $insert[8]['name'] = 'minute';
                $insert[8]['value'] = $date['minutes'];
                $insert[9]['name'] = 'second';
                $insert[9]['value'] = $date['seconds'];
                $insert[10]['name'] = 'type';
                $insert[10]['value'] = 8;

                if (!$another_mysql->insert_values('transactions',$insert)) {
                    if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('There was a database error when trying to insert '.a(TRANSACTION_NAME_SINGULAR).' '.strtolower(TRANSACTION_NAME_SINGULAR).' in transactions.class.php');
                    if (ENABLE_ERROR_LOG) log_error('There was a database error when trying to insert '.a(TRANSACTION_NAME_SINGULAR).' '.strtolower(TRANSACTION_NAME_SINGULAR).' in transactions.class.php<br />Error:'.$mysql->error);
                    $errors .= $another_mysql->error;
                } else {

                    if (ENABLE_EMAIL) {
                        $links->build_url(1,2);
                        $buyer_balance = 0;
                        $seller_balance = 0;
                        if ($this->balance($member["accountID"])) {
                            $buyer_balance = $this->balance;
                        }
                        if ($this->balance($_POST['member_id'])) {
                            $seller_balance = $this->balance;
                        }
                        $transaction_html = '<strong>Amount:</strong> '.number_format($amount_per_member,2).'<br />';
                        $transaction_html .= '<strong>'.ucwords(TRANSACTION_NAME_SINGULAR).' Type:</strong> 8<br />';
                        $transaction_html .= '<strong>Description:</strong> '.indent_variable('',$_POST['description']).'<br />';
                        $transaction_html .= '<strong>Buyer\'s new balance:</strong> '.number_format($buyer_balance,2).'<br />';
                        $transaction_html .= '<strong>Seller\'s new balance:</strong> '.number_format($seller_balance,2).'<br />';
                        $transaction_html .= '<strong>Time:</strong> '.return_month($GLOBALS['date']['month']).' '.$GLOBALS['date']['day'].', '.$GLOBALS['date']['year'].' - '.return_time($GLOBALS['date']['hour'],$GLOBALS['date']['minutes']).'<br /><br /><br />';
                        if ($secondary_mysql->result('SELECT receive_email_buy, first_name, last_name, email_address FROM accounts WHERE accountID = '.$member["accountID"].' AND email_address != \'\'')) {
                            $email_html = 'Dear '.$secondary_mysql->result['first_name'].',<br /><br />';
                            $email_html .= 'A '.strtolower(TRANSACTION_NAME_SINGULAR).' has been recorded with you as buyer and '.$user->full_name($_POST['member_id']).' as seller.<br /><br />';
                            $email_html .= $transaction_html;
                            send_single_email(UPDATE_EMAIL,EMAIL_FROM_NAME,$secondary_mysql->result['email_address'],$secondary_mysql->result['first_name'].' '.$secondary_mysql->result['last_name'],ucfirst(a(TRANSACTION_NAME_SINGULAR)).' '.strtolower(TRANSACTION_NAME_SINGULAR).' has been recorded',strip_tags(str_replace('<br />',"\r\n\r\n",$email_html)).'Change your email delivery options here: '.URL.$links->complete_url,$email_html.'Change your email delivery options <a href="'.URL.$links->complete_url.'">here</a>');
                        }
                        if ($secondary_mysql->result('SELECT receive_email_sell, first_name, last_name, email_address FROM accounts WHERE accountID = '.$_POST['member_id'].' AND email_address != \'\'')) {
                            $email_html = 'Dear '.$secondary_mysql->result['first_name'].',<br /><br />';
                            $email_html .= 'A '.strtolower(TRANSACTION_NAME_SINGULAR).' has been recorded with you as seller and '.$user->full_name($member["accountID"]).' as buyer.<br /><br />';
                            $email_html .= $transaction_html;
                            send_single_email(UPDATE_EMAIL,EMAIL_FROM_NAME,$secondary_mysql->result['email_address'],$secondary_mysql->result['first_name'].' '.$secondary_mysql->result['last_name'],ucfirst(a(TRANSACTION_NAME_SINGULAR)).' '.strtolower(TRANSACTION_NAME_SINGULAR).' has been recorded',strip_tags(str_replace('<br />',"\r\n\r\n",$email_html)).'Change your email delivery options here: '.URL.$links->complete_url,$email_html.'Change your email delivery options <a href="'.URL.$links->complete_url.'">here</a>');
                        }
                    }
                    if (ENABLE_LOG and LOG_TRANSACTIONS) log_action(ucfirst(a(TRANSACTION_NAME_SINGULAR)).' '.strtolower(TRANSACTION_NAME_SINGULAR).' was recorded as part of a global '.strtolower(TRANSACTION_NAME_SINGULAR).'. Buyer ID: '.$member["accountID"].', Seller ID: '.$_POST['member_id'].', Amount: '.number_format($amount_per_member,2));
                    $successful_transactions++;
                }
                $this->balance($member["accountID"]);
                $user->update_balance($this->balance,$member["accountID"]);
            }
        }
        $this->balance($_POST['member_id']);
        $user->update_balance($this->balance,$_POST['member_id']);
        if ($errors) {
            if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('There was a database error when trying to perform a global '.strtolower(TRANSACTION_NAME_SINGULAR));
            if (ENABLE_ERROR_LOG) log_error('There was a database error when trying to perform a global '.a(TRANSACTION_NAME_SINGULAR).' '.strtolower(TRANSACTION_NAME_SINGULAR).'<br />Error:'.$errors);
            return $errors;
        }
        return $successful_transactions.' '.ucwords(TRANSACTION_NAME_PLURAL).' Made Successfully';
    }
    function undo_distributions() {
        $mysql = new mysql;
        if (!$mysql->query('DELETE FROM transactions WHERE type = 8 AND day = '.$GLOBALS['date']['day'].' AND month = '.$GLOBALS['date']['month'].' AND year = '.$GLOBALS['date']['year'])) {
            if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('There was a database error when trying to undo global '.strtolower(TRANSACTION_NAME_PLURAL));
            if (ENABLE_ERROR_LOG) log_error('There was a database error when trying to undo global '.strtolower(TRANSACTION_NAME_PLURAL).'<br />Error:'.$errors);
        }
    }
    function reverse_transaction_html($i,$url) {
        $links = new links;
        $links->build_url(1,3);
        $z = $i."<!-- reverse_transaction_html -->\n";
        $z .= $i."<div id=\"reverse_transaction_form\">\n";
        $z .= $i." Please enter ".a(TRANSACTION_NAME_SINGULAR)." ".strtolower(TRANSACTION_NAME_SINGULAR)." ID to reverse. Review ".strtolower(TRANSACTION_NAME_PLURAL).' <a href="'.URL.$links->complete_url.append_url(0).'">here</a>'." <br /><br />\n";
        $z .= $i." <fieldset>\n";
        $z .= $i.' <form name="reverse_transaction" method="post" action="'.URL.$url.append_url($url).'">'."\n";
        $z .= $i.'  <label for="member_id">'.ucwords(TRANSACTION_NAME_SINGULAR).' ID:</label>'."\n";
        $z .= $i.'  <input type="text" id="transaction_id" name="transaction_id" /><br class="left" />'."\n";
        $z .= $i.'  <input class="reverse_transaction_button" type="submit" name="submit" value="Reverse" /><br class="left" />'."\n";
        $z .= $i." </form>\n";
        $z .= $i." </fieldset>\n";
        $z .= $i."</div>\n";
        $z .= $i."<!-- reverse_transaction_html -->\n";
        return $z;
    }
    function confirm_reverse_transaction($i,$url,$id) {
        $mysql = new mysql;
        global $user;
        if (!$mysql->result('SELECT * FROM transactions WHERE transactionID = '.$id.' LIMIT 1')) {
            $this->error = $mysql->error;
            return false;
        }
        $z = $i."<!-- confirm_reverse_transaction_html -->\n";
        $z .= $i."<div id=\"confirm_reverse_transaction_form\">\n";
        $z .= $i." <strong>Warning:</strong> You cannot undo this operation.<br /><br />Are you sure you want to delete to following ".ucwords(TRANSACTION_NAME_SINGULAR)."?<br /><br />\n";
        if (ENABLE_TRANSACTION_SERVICE_FEE) {
            $z .= $i." <strong>Note:</strong> Any corresponding fees will not be deleted.<br /><br />\n";
        }
        $z .= $i." <table>\n";
        $z .= $i."  <tr>\n";
        $z .= $i."   <th class=\"h\">".ucwords(TRANSACTION_NAME_SINGULAR)." ID</th>\n";
        $z .= $i."   <th class=\"h\">Buyer</th>\n";
        $z .= $i."   <th class=\"h\">Seller</th>\n";
        $z .= $i."   <th class=\"h\">Amount</th>\n";
        $z .= $i."   <th class=\"h\">Description</th>\n";
        $z .= $i."   <th class=\"h\">".ucwords(TRANSACTION_NAME_SINGULAR)." Type</th>\n";
        if ($mysql->result['noticeboardID']) {
            $z .= $i."   <th class=\"h\">".ucwords(NOTICEBOARD_NAME_SINGULAR)." ID</th>\n";
        }
        $z .= $i."   <th class=\"h\">Time</th>\n";
        $z .= $i."  </tr>\n";
        $z .= $i."  <tr>\n";
        $z .= $i."   <td>\n";
        $z .= $i."    ".$id."</td>\n";
        $z .= $i."   <td>\n";
        $z .= $i."    ".$user->full_name($mysql->result['buyerID'])."</td>\n";
        $z .= $i."   <td>\n";
        $z .= $i."    ".$user->full_name($mysql->result['sellerID'])."</td>\n";
        $z .= $i."   <td>\n";
        $z .= $i."    ".number_format($mysql->result['amount'],2)."</td>\n";
        $z .= $i."   <td>\n";
        $z .= $i."    ".indent_variable($i.'    ',$mysql->result['description'])."</td>\n";
        switch ($mysql->result['type']) {
            case 1: $z .= $i."   <td>\n".$i."    Normal</td>\n"; break;
            case 2: $z .= $i."   <td>\n".$i."    Buy Now</td>\n"; break;
            case 3: $z .= $i."   <td>\n".$i."    Auction Win</td>\n"; break;
            case 4: $z .= $i."   <td>\n".$i."    Service Fee</td>\n"; break;
            case 5: $z .= $i."   <td>\n".$i."    Account Created</td>\n"; break;
            case 6: $z .= $i."   <td>\n".$i."    Account Deleted</td>\n"; break;
            case 7: $z .= $i."   <td>\n".$i."    Account Reactivated</td>\n"; break;
            case 8: $z .= $i."   <td>\n".$i."    Global ".ucwords(TRANSACTION_NAME_SINGULAR)."</td>\n"; break;
        }
        if ($mysql->result['noticeboardID']) {
            $z .= $i."   <td>\n";
            $z .= $i."    ".$mysql->result['noticeboardID']."</td>\n";
        }
        $z .= $i."   <td>\n";
        $z .= $i."    ".return_month($mysql->result['month']).' '.$mysql->result['day'].', '.$mysql->result['year'].' - '.$mysql->result['hour'].':'.return_minute($mysql->result['minute']).':'.return_minute($mysql->result['second'])."</td>\n";
        $z .= $i."  </tr>\n";
        $z .= $i." </table>\n";
        $z .= $i.' <form name="confirm_reverse_transaction" method="post" action="'.URL.$url.append_url($url).'">'."\n";
        $z .= $i.'  <input type="hidden" name="transaction_id" value="'.$id.'" />'."\n";
        $z .= $i.'  <input class="confim_reverse_transaction_button" type="submit" name="submit" value="Confirm Reversal" /><br class="left" />'."\n";
        $z .= $i." </form>\n";
        $z .= $i."</div>\n";
        $z .= $i."<!-- reverse_transaction_html -->\n";
        return $z;

    }
    function member_distribute_html($i,$url) {
        $mysql = new mysql;
        global $user;
        $z = $i."<!-- member_distribute_html -->\n";
        $z .= $i."<div id=\"member_distribute_form\">\n";
        $z .= $i." <strong>Note:</strong> There are ".($user->num_active_members() - 1)." active and ".$user->num_suspended_members()." suspended ".strtolower(MEMBERS_NAME_PLURAL)." (not counting #1 account).<br />The default settings will return the #1 account to zero from a negative balance although this tool can be used with any ".strtolower(MEMBERS_NAME_SINGULAR)." as the seller.<br /><br />\n";
        $z .= $i." <fieldset>\n";
        $z .= $i.' <form name="member_distribute" method="post" action="'.URL.$url.append_url($url).'">'."\n";
        $z .= $i.'  <label for="member_id">'.ucwords(MEMBERS_NAME_SINGULAR).' ID:</label>'."\n";
        $z .= $i.'  <input type="text" id="member_id" name="member_id" value="1" /><br class="left" />'."\n";
        $z .= $i.'  <label for="amount">Total Amount:</label>'."\n";
        $this->balance(1);
        if ($this->balance < 0) {
            $this->balance = $this->balance * -1;
        } else {
            $this->balance = '';
        }
        $z .= $i.'  <input type="text" id="amount" name="amount" value="'.$this->balance.'" /><br class="left" />'."\n";
        $z .= $i.'  <label for="description">Description:</label>'."\n";
        $z .= $i.'  <input type="text" id="description" name="description" /><br class="left" />'."\n";
        $z .= $i.'  <label for="include_suspended_members">Include Suspended '.ucwords(MEMBERS_NAME_PLURAL).':</label>'."\n";
        $z .= $i.'  <input type="checkbox" id="include_suspended_members" name="include_suspended_members" value="1" /><br class="left" /><br class="left" />'."\n";
        $z .= $i.'  <input id="member_distribute_button" class="member_distribute_button" type="submit" name="submit" value="Submit '.ucwords(TRANSACTION_NAME_SINGULAR).'" /><br class="left" />'."\n";
        $mysql->num_rows('SELECT * FROM transactions WHERE type = 8 AND day = '.$GLOBALS['date']['day'].' AND month = '.$GLOBALS['date']['month'].' AND year = '.$GLOBALS['date']['year'].' LIMIT 1');
        if ($mysql->num_rows) {
            $z .= $i.'  <input id="undo_distribute_button" class="undo_distribute_button" type="submit" name="submit" value="Reverse All Global '.ucwords(TRANSACTION_NAME_PLURAL).' Made Today" />'."\n";
        }
        $z .= $i." </form>\n";
        $z .= $i." </fieldset>\n";
        $z .= $i."</div>\n";
        $z .= $i."<!-- member_distribute_html -->\n";
        return $z;
    }
}


?>
