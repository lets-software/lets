<?php
// NOTE: This is 2 pages in one. Either it is an noticeboard entry or a list of them.

// unset noticeboard ID if it doesn't exist
if (!isset($_GET['page_id'])) $_GET['page_id'] = 0;
if ($_GET['page_id']) {
    if ($mysql->result('SELECT accounts.suspended FROM noticeboard,accounts WHERE noticeboard.noticeboardID = '.$_GET['page_id'].' AND noticeboard.accountID = accounts.accountID LIMIT 1')) {
        $noticeboard_owner_suspended = $mysql->result['suspended'];
    } else {
        $_GET['page_id'] = 0;
    }
}


if (!$_GET['page_id']) {
/***********************************************************************************************************************************
*        Page:            noticeboard.php
*        Access:            Public
*        Purpose:        Displays a list of entries or a single
*        HTML Holders:    $heading                    :    Page Heading
*                        $noticeboard_search_form    :    The form used to search the listings
*                        $noticeboard_list            :    The Noticeboard List
*                        $blurb                        :    Additional dynamic text
*        Template File:                                                                                                            */
            $template_filename                         =         'noticeboard_list';
/*        Classes:                                                                                                                */
            require_once('includes/classes/noticeboard.class.php');
            $noticeboard                             =         new noticeboard;

/*        Indentation:                                                                                                            */
            $heading_indent                         =         '   ';
            $noticeboard_search_form_indent            =         '   ';
            $noticeboard_list_indent                =         '   ';
            $blurb_indent                             =         '   ';
/*        Disable "Print Page" Link on this page:                                                                                    */
            $disable_print_page                        =        false;
/*        CSS Files Called by script:                                                                                                */
        if (!$print) {
            if(!isset($styles_link)) { $styles_link = ''; }
            $styles_link                      .=         '<link rel="stylesheet" href="'.URL.'templates/'.TEMPLATE."/styles/noticeboard_search_form.css\" />\n";
            $styles_link                      .=         '<link rel="stylesheet" href="'.URL.'templates/'.TEMPLATE."/styles/results_table.css\" />\n";
/*        Dynamic Styling:                                                                                                        */
            /* if (FONT_SIZE > 14) {
                $local_font_size         =         14;
            } else {
                $local_font_size         =         FONT_SIZE;
            }
            $style->dynamic_elements     .=         " table {font-size: ".$local_font_size."px;}\n"; */
            $style->dynamic_elements                 .=         " th.h {background-color:".TAB_COLOUR."; color:".LINK_COLOUR.";}\n";
            // $style->dynamic_elements                 .=         " td {border-bottom:1px solid ".TAB_BORDER_COLOUR."; border-right: 1px solid ".TAB_COLOUR."; }\n";
        }
/*        Set Universal Page elements:                                                                                            */
            $links->page_info(2,0);
            $page_name                      =         $links->name;
            $url                                    =         $links->url;
            $blurb                                =         $links->body;
/*        Page Title:                                                                                                                */
            $title                                  =         SITE_NAME.' '.$page_name;
/*        Page Heading                                                                                                            */
            $heading                           =         $heading_indent."<h1>".SITE_NAME.' '.$page_name."</h1>\n";
/*
************************************************************************************************************************************/

    // check to see if there are any variables in the URL
    // ** because of using mod-rewrite we have to do this manually **
    $start_default = 0;
    $limit_default = 0;
    $category_id = return_link_variable("category_id",'');
    $start = return_link_variable("start",$start_default);
    $limit = return_link_variable("limit",$limit_default);
    $request = return_link_variable('request','');
    $offer = return_link_variable('offer','');
    $noticeboard_member_id = return_link_variable('noticeboard_member',0);
    $amount = return_link_variable('amount','');
    $amount_above = return_link_variable('amount_above','');
    $item = return_link_variable('item','');
    $auction = return_link_variable('auction','');
    $service = return_link_variable('service','');
    $orderby = return_link_variable('orderby','');
    $orderdir = return_link_variable('orderdir','');
    $request_or_offer = return_link_variable('request_or_offer','');
    $noticeboard_type = return_link_variable('noticeboard_type','');
    if (isset($_POST['category_id'])) $category_id = $_POST['category_id'];
    if (isset($_POST['start'])) $start = $_POST['start'];
    if (isset($_POST['limit'])) $limit = $_POST['limit'];
    if (isset($_POST['request'])) $request = $_POST['request'];
    if (isset($_POST['offer'])) $offer = $_POST['offer'];
    if (isset($_POST['noticeboard_member'])) $noticeboard_member_id = $_POST['noticeboard_member'];
    if (isset($_POST['amount'])) $amount = $_POST['amount'];
    if (isset($_POST['amount_above'])) $amount_above = $_POST['amount_above'];
    if (isset($_POST['item'])) $category_id = $_POST['item'];
    if (isset($_POST['auction'])) $auction = $_POST['auction'];
    if (isset($_POST['service'])) $service = $_POST['service'];
    if (isset($_POST['orderby'])) $orderby = $_POST['orderby'];
    if (isset($_POST['orderdir'])) $orderdir = $_POST['orderdir'];
    if (isset($_POST['request_or_offer'])) {
        if ($_POST['request_or_offer'] == 1) $offer = 1;
        if ($_POST['request_or_offer'] == 2) $request = 1;
    }
    if (isset($_POST['noticeboard_type'])) {
        if ($_POST['noticeboard_type'] == 1) $item = 1;
        if ($_POST['noticeboard_type'] == 2) $service = 1;
        if ($_POST['noticeboard_type'] == 3) $auction = 1;
    }
    $keyword = return_link_variable("keyword",'');
    if ($keyword) {
        $keyword = trim(str_replace('_',' ',$keyword));
        $keyword = str_replace('%22','"',$keyword);
        $keyword = preg_replace("/[^0-9a-z\" ]/",'',$keyword);
        $keyword = eregi_replace(" +", ' ', $keyword);
    }
    if (isset($_POST['keyword'])) {
        $keyword = trim($_POST['keyword']);
        $keyword = html_entity_decode($keyword);
        $keyword = preg_replace("/[^0-9a-z\" ]/",'',$keyword);
        $keyword = eregi_replace(" +", ' ', $keyword);
    }

    // *******************************************
    //             Main Page

    // now we have all our variables to pass to the form
    $noticeboard_search_form = $noticeboard->search_form($noticeboard_search_form_indent,$keyword,$limit,$start,$request,$offer,$noticeboard_member_id,$amount,$amount_above,$item,$service,$category_id,$auction,$orderby,$orderdir);
    // and the results-generator
    $noticeboard_list = $noticeboard->noticeboard_list($noticeboard_list_indent,$keyword,$limit,$start,$request,$offer,$noticeboard_member_id,$amount,$amount_above,$item,$service,$category_id,$auction,$orderby,$orderdir);

} elseif (!$noticeboard_owner_suspended or user_type() == 2) {
//************* an item-specific page *****************************
/***********************************************************************************************************************************
*        Page:                  noticeboard.php
*        Access:               Public
*        Purpose:              Displays noticeboard entry
*        HTML Holders:    $heading                     :        Page Heading
*                        $messages                             :        Many messages can be generated on this page
*                        $noticeboard_info               :        The form used to search the listings
*                        $noticeboard_body               :        The Noticeboard List
*                        $noticeboard_tools              :        Transaction Options
*                        $comment_html                   :        Comment List
*                        $comment_form                   :        Add a comment
*        Template File:                                                                                                            */
            $template_filename                         =         'noticeboard_page';
/*        Classes:        faq - contains the html                                                                                    */
            require_once('includes/classes/noticeboard.class.php');
            $noticeboard                             =         new noticeboard;
            require_once('includes/classes/transactions.class.php');
            $transactions                             =         new transactions;
            if (ENABLE_COMMENTS) {
                require_once('includes/classes/comments.class.php');
                $comments                             =         new comments;
            }

/*        Indentation:                                                                                                            */
            $heading_indent                         =         '   ';
            $messages_indent                        =         '   ';
            $noticeboard_info_indent                =         '   ';
            $noticeboard_body_indent                =         '   ';
            $noticeboard_tools_indent                =         '   ';
            $comment_html_indent                     =         '   ';
            $comment_form_indent                     =         '   ';
/*        Disable "Print Page" Link on this page:                                                                                    */
            $disable_print_page                        =        false;
/*        CSS Files Called by script:                                                                                                */
        if (!$print) {
            if(!isset($styles_link)) { $styles_link = ''; }
            $styles_link                         .=         '<link rel="stylesheet" href="'.URL.'templates/'.TEMPLATE."/styles/noticeboard_page.css\" />\n";
            $styles_link                         .=         '<link rel="stylesheet" href="'.URL.'templates/'.TEMPLATE."/styles/transaction_form.css\" />\n";
            $styles_link                         .=         '<link rel="stylesheet" href="'.URL.'templates/'.TEMPLATE."/styles/results_table.css\" />\n";
            if (ENABLE_COMMENTS) {
                $styles_link                         .=         '<link rel="stylesheet" href="'.URL.'templates/'.TEMPLATE."/styles/comment_form.css\" />\n";
                $styles_link                         .=         '<link rel="stylesheet" href="'.URL.'templates/'.TEMPLATE."/styles/comment_list.css\" />\n";
            }
/*        Dynamic Styling:                                                                                                        */
            $style->dynamic_elements                 .=         " div#nb_page_divider {border-top: 1px solid ".TAB_BORDER_COLOUR."; border-bottom: 1px solid ".TAB_BORDER_COLOUR."; }\n";
            $style->dynamic_elements                 .=         " th.h {background-color:".TAB_COLOUR."; color:".LINK_COLOUR."; text-align:left; padding-left:5px;}\n";
        }
/*        Set Universal Page elements:                                                                                            */
            $links->page_info(2,0);
            $page_name                                 =         $links->name;
            $url                                     =         $links->url;
//            $blurb                                     =         $links->body;
/*        Page Title:
            Called below to include noticeboard details and location
/*        Page Heading                                                                                                            */
            $heading                                 =         $heading_indent."<h1>".SITE_NAME.' '.$page_name."</h1>\n";
/*        Clear HTML                                                                                                                */

            $messages                                =         '';
            $noticeboard_info                        =         '';
            $noticeboard_body                        =         '';
            $noticeboard_tools                        =         '';
            $comment_html                             =         '';
            $comment_form                             =         '';
/*
************************************************************************************************************************************/


$comment_edited = false;
$min_bid = 0;

if (!isset($_POST['submit'])) $_POST['submit'] = '';
    // check for transactinn or validation (included forms on this page):
    // build query first ensuring members can't see the entries of other suspended members
    if ($mysql->result('SELECT * FROM noticeboard WHERE noticeboardID = '.$_GET['page_id'].' LIMIT 1')) {
        $transaction_form_displayed = false;
        $notice = $mysql->result;

        $title = SITE_NAME.' '.NOTICEBOARD_NAME.' - '.$notice['title'].' in '.LOCATION;
        if (ENABLE_INSTANT_BUY and $notice['quick_delete'] and isset($_POST['buy_now_amount']) and user_type() and !$_SESSION['member_suspended'] and $_SESSION['member_validated']) {
            $transaction_halted = false;
            if (LOCK_BUY_NOW_PRICE and $_POST['buy_now_amount'] != $notice['amount']) {
                $messages .= $messages_indent.T_('<span class="message">You must pay '.$notice['amount'].' for this item</span>')."<br /><br />\n";
            } elseif (!is_numeric($_POST['buy_now_amount']) or $_POST['buy_now_amount'] < 1) {
                $messages .= $messages_indent.T_('<span class="message">You must enter a positive number</span>')."<br /><br />\n";
            } else {
                if (NEGATIVE_BALANCE_LIMIT != '0.00') {
                    if ($transactions->balance($_SESSION['member_id'])) {
                        if (($transactions->balance - $amount) < (NEGATIVE_BALANCE_LIMIT * -1)) {
                            $transaction_halted = true;
                            $messages .= $messages_indent . T_('<span class="message">A limit of ') . NEGATIVE_BALANCE_LIMIT . ' '.CURRENCY_NAME . T_(' has been established. This trade cannot be completed because it would exceed that limit.</span>')."<br /><br />\n";
                        }
                    }
                }
                if (!$transaction_halted) {
                    if ($transactions->make_transaction(2,$_SESSION['member_id'],$notice['accountID'],$_POST['buy_now_amount'],T_('Automated transaction for a "Buy Now" entry') . ' (<a href="'.URL.NOTICEBOARD_URL.'/'.$notice['noticeboardID'].'/">'.ucwords(NOTICEBOARD_NAME_SINGULAR).' ID #'.$notice['noticeboardID'].'</a>): '.$notice['title'],$date['day'],$date['month'],$date['year'],$date['hour'],$date['minutes'],$date['seconds'],$_GET['page_id'])) {
                        if (!$noticeboard->bid($messages_indent,$notice['noticeboardID'],$notice['reserve'],$_POST['buy_now_amount'])) {
                            if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: Bid representing a "buy now" added to '.strtolower(NOTICEBOARD_NAME_SINGULAR).' ID:'.$_GET['event_id'].' ('.$notice['title'].')');
                            if (ENABLE_ERROR_LOG) log_error('FAILED: Bid representing a "buy now" added to '.strtolower(NOTICEBOARD_NAME_SINGULAR).' ID:'.$_GET['event_id'].' ('.$notice['title'].')'.$noticeboard->error);
                        }
                        if (!$noticeboard->bought($notice['noticeboardID'])) {
                            if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: Set '.strtolower(NOTICEBOARD_NAME_SINGULAR).' ID:'.$_GET['event_id'].' ('.$notice['title'].') to "bought"');
                            if (ENABLE_ERROR_LOG) log_error('FAILED: Set '.strtolower(NOTICEBOARD_NAME_SINGULAR).' ID:'.$_GET['event_id'].' ('.$notice['title'].') to "bought"<br />Error:'.$noticeboard->error);
                        }
                    }
                }
            }
        } else {
            $member_message = process_transaction($_GET['page_id']);
        }

        // *******************
        //  comment actions
        // *******************
        if ((ENABLE_COMMENTS and user_type() and !$_SESSION['member_suspended'] and $_SESSION['member_validated']) or (ENABLE_COMMENTS and ENABLE_GUEST_COMMENTS)) {
            $comments = new comments;
            if ($_POST['submit'] == 'Make '.ucwords(COMMENT_NAME_SINGULAR)) {
                if ($comments->validate_form('Re: '.$notice['title'])) {
                    if ($comments->add_comment()) {
                        $messages .= $messages_indent.'<span class="message">'.ucwords(COMMENT_NAME_SINGULAR).' Added</span><br /><br />'."\n";
                        if (ENABLE_LOG and LOG_ADDITIONS and LOG_COMMENTS) log_action(ucwords(COMMENT_NAME_SINGULAR).' added to '.ucwords(NOTICEBOARD_NAME_SINGULAR).' ID:'.$_GET['event_id'].' ('.$notice['title'].')');
                    } else {
                        $messages .= $messages_indent.'<span class="message">'.$comments->error.'</span><br /><br />'."\n";
                        if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: '.ucwords(COMMENT_NAME_SINGULAR).' added to '.ucwords(NOTICEBOARD_NAME_SINGULAR).' ID:'.$_GET['event_id'].' ('.$notice['title'].')');
                        if (ENABLE_ERROR_LOG) log_error('FAILED: '.ucwords(COMMENT_NAME_SINGULAR).' added to '.ucwords(NOTICEBOARD_NAME_SINGULAR).' ID:'.$_GET['event_id'].' ('.$notice['title'].')<br />Error: '.$comments->error);
                    }
                } else {
                    $messages .= $messages_indent.'<span class="message">'.$comments->error.'</span><br /><br />'."\n";
                }
            } elseif ($_POST['submit'] == 'Edit '.ucwords(COMMENT_NAME_SINGULAR)) {
                if ($comments->validate_form('Re: '.$notice['title'])) {
                    if ($comments->edit_comment()) {
                        $messages .= $messages_indent.'<span class="message">'.ucwords(COMMENT_NAME_SINGULAR).' Edited</span><br /><br />'."\n";
                        if (ENABLE_LOG and LOG_EDITS and LOG_COMMENTS) log_action(ucwords(COMMENT_NAME_SINGULAR).' edited in '.ucwords(NOTICEBOARD_NAME_SINGULAR).' ID:'.$_GET['event_id'].' ('.$notice['title'].')');
                        $comment_edited = true;
                    } else {
                        $messages .= $messages_indent.'<span class="message">'.$comments->error.' <br /><strong>' . T_('GO Back in your browser to make the changes') . '</strong></span><br /><br />'."\n";
                        if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: '.ucwords(COMMENT_NAME_SINGULAR).' edited in '.ucwords(NOTICEBOARD_NAME_SINGULAR).' ID:'.$_GET['event_id'].' ('.$notice['title'].')');
                        if (ENABLE_ERROR_LOG) log_error('FAILED: '.ucwords(COMMENT_NAME_SINGULAR).' edited in '.ucwords(NOTICEBOARD_NAME_SINGULAR).' ID:'.$_GET['event_id'].' ('.$notice['title'].')<br />Error: '.$comments->error);
                    }
                } else {
                    $messages .= $messages_indent.'<span class="message">'.$comments->error.'</span><br /><br />'."\n";
                }
            } elseif (ALLOW_COMMENT_DELETION and $_POST['submit'] == 'Delete '.ucwords(COMMENT_NAME_SINGULAR) and $_POST['comment_id']) {
                if ($comments->delete_comment($_POST['comment_id'])) {
                    $messages .= $messages_indent.'<span class="message">'.ucwords(COMMENT_NAME_SINGULAR).' Deleted</span><br /><br />'."\n";
                    if (ENABLE_LOG and LOG_DELETIONS and LOG_COMMENTS) log_action(ucwords(COMMENT_NAME_SINGULAR).' deleted in '.ucwords(NOTICEBOARD_NAME_SINGULAR).' ID:'.$_GET['event_id'].' ('.$notice['title'].')');
                } else {
                    // document error
                    $messages .= $messages_indent.'<span class="message">'.ucwords(COMMENT_NAME_SINGULAR) . T_(' could not be deleted') . '</span><br /><br />'."\n";
                    if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: '.ucwords(COMMENT_NAME_SINGULAR).' deleted in '.ucwords(NOTICEBOARD_NAME_SINGULAR).' ID:'.$_GET['event_id'].' ('.$notice['title'].')');
                    if (ENABLE_ERROR_LOG) log_error('FAILED: '.ucwords(COMMENT_NAME_SINGULAR).' edited in '.ucwords(NOTICEBOARD_NAME_SINGULAR).' ID:'.$_GET['event_id'].' ('.$notice['title'].')<br />Error: '.$comments->error);
                }
            }
        }
        // *******************
        // rebuild
        if ($mysql->result('SELECT * FROM noticeboard WHERE noticeboardID = '.mysql_real_escape_string($_GET['page_id']).' LIMIT 1')) {
            $notice = $mysql->result;
        }

        if ($member_message) {
            $messages .= $messages_indent.'<span class="message">'.$member_message.'</span><br /><br />'."\n";
        }
        // *******************
        // determine if entry already bought or expired
        if ($notice['expired']) {
            $expired = true;
        } else {
            if ($notice['expiry_year'] > $GLOBALS['date']['year'] or ($notice['expiry_year'] == $GLOBALS['date']['year'] and $notice['expiry_month'] > $GLOBALS['date']['month']) or ($notice['expiry_year'] == $GLOBALS['date']['year'] and $notice['expiry_month'] == $GLOBALS['date']['month'] and $notice['expiry_day'] > $GLOBALS['date']['day']) or ($notice['expiry_year'] == $GLOBALS['date']['year'] and $notice['expiry_month'] == $GLOBALS['date']['month'] and $notice['expiry_day'] == $GLOBALS['date']['day'] and $notice['expiry_hour'] > $GLOBALS['date']['hour']) or (!$notice['expiry_year'] and !$notice['expiry_month'] and !$notice['expiry_day'] and !$notice['expiry_hour'])) {
                $expired = false;
            } else {
                $expired = true;
            }
        }

        $bought = false;
        if ($notice['bought']) $bought = true;

        $member_info = new member;
        $member_info->build_dataset($notice['accountID']);

        //***********************************************
        // start of page
        $noticeboard_info = $noticeboard_info_indent."<!-- noticeboard_info -->\n";
        $noticeboard_info .= $noticeboard_info_indent."<div id=\"noticeboard_info\">\n";
        $noticeboard_info .= $noticeboard_info_indent.'<span class="nb_page_stats">'.T_(ucwords(NOTICEBOARD_NAME_SINGULAR).' ID: '.$notice['noticeboardID'])."</span><br />\n";
        $noticeboard_info .= $noticeboard_info_indent.'<span class="nb_page_stats">'.T_('Created '.return_month($notice['created_month']).' '.$notice['created_day'].', '.$notice['created_year'].' by ');
        if (user_type() == 0 or $_SESSION["member_validated"] == 0 or $_SESSION["member_suspended"] == 1) {
            $noticeboard_info .= ' <a href="'.URL.MEMBER_LIST_URL.'/'.$notice['accountID'].'/">'.ucwords(MEMBERS_NAME_SINGULAR).' '.$notice['accountID']."</a></span><br />\n";
        } elseif (user_type() == 1 or user_type() == 2) {
            $noticeboard_info .= ' <a href="'.URL.MEMBER_LIST_URL.'/'.$notice['accountID'].'/'.append_url(0).'">'.$member_info->full_name($notice['accountID'])."</a>";
            if ((user_type() == 2 or $notice['accountID'] == $_SESSION["member_id"]) and $links->build_url(1,5)) {
                $noticeboard_info .= ' (<a href="'.URL.$links->complete_url.$notice['noticeboardID'].'/'.append_url(0).'">edit</a>)';
            }
            $noticeboard_info .= "</span><br />\n";
        }
        if ($notice['expiry_year']) {
            if (!$expired) {
                $noticeboard_info .= $noticeboard_info_indent.' <span class="nb_page_stats">'.T_('Expires '.return_month($notice['expiry_month']).' '.$notice['expiry_day'].', '.$notice['expiry_year'])."</span><br />\n";
            } else {
                $noticeboard_info .= $noticeboard_info_indent.' <span class="nb_page_stats">'.T_('Expired '.return_month($notice['expiry_month']).' '.$notice['expiry_day'].', '.$notice['expiry_year'])."</span><br />\n";
            }
        }
        $noticeboard_info .= $noticeboard_info_indent.' <br /><span class="nb_page_type">';
        if ($notice['type'] == 2) {
            $noticeboard_info .= T_('Auction');
        }
        if ($notice['item'] == 1 and $notice['type'] != 2) {
            $noticeboard_info .= T_(' Item');
            if ($notice['request']) {
                $noticeboard_info .= T_(' Requested');
            } else {
                $noticeboard_info .= T_(' Offered');
            }
        }
        if ($notice['item'] == 0 and $notice['type'] != 2) {
            $noticeboard_info .= T_(' Service');
            if ($notice['request']) {
                $noticeboard_info .= T_(' Requested');
            } else {
                $noticeboard_info .= T_(' Offered');
            }
        }
        $noticeboard_info .= "</span><br /><br />\n";
        if ($noticeboard_owner_suspended) {
            $noticeboard_info .= $noticeboard_info_indent.T_(' <span class="nb_page_expired">The owner of this '.strtolower(NOTICEBOARD_NAME_SINGULAR).' has been suspended<br />'.ucfirst(MEMBERS_NAME_PLURAL).' will not be able to access it until '.$user->full_name($notice['accountID'])."'s account is re-activated</span>")."<br /><br />\n";
        }
        if ($expired and $bought) {
            $noticeboard_info .= $noticeboard_info_indent.T_(' <span class="nb_page_expired">This auction has ended</span>')."<br /><br />\n";
        } elseif ($expired) {
            $noticeboard_info .= $noticeboard_info_indent.T_(' <span class="nb_page_expired">This '.strtolower(NOTICEBOARD_NAME_SINGULAR).' has expired</span>')."<br /><br />\n";
        } elseif ($bought) {
            if (user_type() == 0 or $_SESSION["member_validated"] == 0 or $_SESSION["member_suspended"] == 1) {
                $noticeboard_info .= $noticeboard_info_indent.T_(' <span class="nb_page_expired">This item has been purchased</span>')."<br /><br />\n";
            } elseif (user_type() == 1 or user_type() == 2) {
                if ($mysql->result('SELECT buyerID FROM transactions WHERE noticeboardID = '.$notice['noticeboardID'].' LIMIT 1')) {
                    if ($mysql->result['buyerID'] == $_SESSION["member_id"]) {
                        $noticeboard_info .= $noticeboard_info_indent.T_(' <span class="nb_page_expired">You have purchased this item</span>')."<br /><br />\n";
                    } else {
                        $noticeboard_info .= $noticeboard_info_indent.T_(' <span class="nb_page_expired">'.$user->full_name($mysql->result['buyerID']).' purchased this item</span>')."<br /><br />\n";
                    }
                } else {
                    $noticeboard_info .= $noticeboard_info_indent.T_(' <span class="nb_page_expired">This item has been purchased</span>')."<br /><br />\n";
                }
            }
        }
        $noticeboard_info .= $noticeboard_info_indent."</div>\n";
        $noticeboard_info .= $noticeboard_info_indent."<!-- /noticeboard_info -->\n";
        //*************************************************************
        //        finished printing the status of the entry
        //        this is the user-entered body of the entry:
        //*************************************************************
        $noticeboard_body = $noticeboard_body_indent."<!-- noticeboard_body -->\n";
        $noticeboard_body .= $noticeboard_body_indent."<div id=\"nb_page_divider\">\n";
        $noticeboard_body .= $noticeboard_body_indent.' <span class="nb_page_title">'.$notice['title']."</span><br /><br />\n";

        //*******************
        // image html
        //*******************
        $img_html = '';
        $thumb_html = '';
        if (ENABLE_IMAGES and $notice['imageID']) {
            $image_id = '';
            if (strpos(' '.$_SERVER['REQUEST_URI'],'image=')) {
                $y = explode('image=',$_SERVER['REQUEST_URI']);
                $image_id = $y[1];
            }

            if ($image_id) {
                if ($image->img($image_id,'p',$notice['title'],'noticeboard_image')) $img_html = $image->img;
            } else {
                if ($image->img($notice['imageID'],'p',$notice['title'],'noticeboard_image')) $img_html = $image->img;
            }
            if ($img_html) {
                $noticeboard_body .= $noticeboard_body_indent.' '.$img_html."<br /><br />\n";
            }

            if ($image_id and $mysql->build_array('SELECT * FROM images WHERE noticeboardID = '.$notice['noticeboardID'])) {
                if ($mysql->num_rows > 1) {
                    $thumb_html = $image->thumbs($noticeboard_body_indent.' ',remove_slashes($mysql->result),URL.NOTICEBOARD_URL.'/'.$notice['noticeboardID'].'/');
                    if(!isset($styles_link)) { $styles_link = ''; }
                    $styles_link                         .= '<link rel="stylesheet" href="'.URL.'templates/'.TEMPLATE."/styles/thumbs_list.css\" />\n";
                    $style->dynamic_elements    .= " div.thumbs {float:left; width:".IMAGE_WIDTH_THUMB_NOTICEBOARD."; margin-right:5px; }\n";
                }
            }
        }

        $noticeboard_body .= $noticeboard_body_indent." <span class=\"nb_page_description\">\n".indent_variable($noticeboard_body_indent.'  ',$notice['description'])."\n".$noticeboard_body_indent." </span><br />";
        if ($notice['amount']) {
            $noticeboard_body .= "\n".$noticeboard_body_indent.T_(' <span class="nb_page_title">Amount: '.$notice['amount'].'</span>')."<br /><br />\n";
        } else {
            $noticeboard_body .= "<br />\n";
        }
        if ($thumb_html) {
            $noticeboard_body .= $thumb_html.$noticeboard_body_indent." <br /><br />\n";
        }

        $noticeboard_body .= $noticeboard_body_indent."</div>\n";
        $noticeboard_body .= $noticeboard_body_indent."<!-- /noticeboard_body -->\n";
        //*****************************************************************************
        //        finished printing user-entered body of the entry
        //        this is a list of tools: regular transaction, buy it now, bid
        //*****************************************************************************
        $noticeboard_tools = $noticeboard_tools_indent."<!-- noticeboard_tools -->\n";
        if (user_type()) {
            if ($notice['type'] != 2  and !$bought and !$expired and $notice['accountID'] != $_SESSION["member_id"]) {
                if (ENABLE_INSTANT_BUY and $notice['quick_delete'] and $notice['item']) {
                    $transaction_form_displayed = true;
                    $noticeboard_tools .= $noticeboard->buy_now_html($noticeboard_tools_indent,$notice['amount'],$notice['noticeboardID']);
                } elseif (!$notice['request']) {
                    $transaction_form_displayed = true;
                    $noticeboard_tools .= $transactions->html($noticeboard_tools_indent,'member',$notice['accountID'],NOTICEBOARD_URL.'/'.$notice['noticeboardID'].'/'.append_url(),$notice['amount'],$notice['title']);
                }
            }
            if ($notice['type'] == 2 and ENABLE_AUCTIONS) {
                // kind of deep in the script but we'll run any bids now
                if (isset($_POST['bid_amount']) and !$expired and !$bought) {
                    $transaction_halted = false;
                    if (NEGATIVE_BALANCE_LIMIT != '0.00') {
                        if ($transactions->balance($_SESSION['member_id'])) {
                            if (($transactions->balance - $amount) < (NEGATIVE_BALANCE_LIMIT * -1)) {
                                $transaction_halted = true;
                                $noticeboard_tools .= $noticeboard_tools_indent.'<span class="message">A limit of '.NEGATIVE_BALANCE_LIMIT.' '.CURRENCY_NAME.' has been established. This bid cannot be completed because it would exceed that limit.</span><br /><br />'."\n";
                            }
                        }
                    }
                    if (!$transaction_halted) {
                        if ($noticeboard->bid($noticeboard_tools_indent,$notice['noticeboardID'],$notice['reserve'],$_POST['bid_amount'])) {
                            $noticeboard_tools .= $noticeboard_tools_indent.'<span class="message">Bid made successfully</span><br /><br />'."\n";
                        } else {
                            $noticeboard_tools .= $noticeboard_tools_indent.'<span class="message">'.$noticeboard->error.'</span><br /><br />'."\n";
                        }
                    }
                }

                $expiry = array('year' => $notice['expiry_year'], 'month' => $notice['expiry_month'], 'day' => $notice['expiry_day'], 'hour' => $notice['expiry_hour']);
                $time_left = $noticeboard_tools_indent.'<span class="time_left">('.date_difference($GLOBALS['date'],$expiry)." left!)</span><br /><br />\n";

                if ($noticeboard->bid_info($notice['noticeboardID'])) {
                    if (!$noticeboard->num_bids and !$expired and !$bought) {
                        if ($notice['accountID'] != $_SESSION['member_id']) {
                            $noticeboard_tools .= $noticeboard_tools_indent."<span class=\"nb_page_auction_title\">Be the first to bid on this auction.</span><br /><br />\n";
                            $min_bid = 1;
                            if ($notice['reserve'] != '0.00') {
                                $min_bid = $notice['reserve'];
                                $noticeboard_tools .= $noticeboard_tools_indent."<span class=\"nb_page_auction_title\">A reserve of ".$min_bid.' '.CURRENCY_NAME." must be met.</span><br /><br />\n";
                            } else {
                                $noticeboard_tools .= $noticeboard_tools_indent."<span class=\"nb_page_auction_title\">No reserve. Minimun bid: 1 ".rtrim(CURRENCY_NAME,'s').".</span><br /><br />\n";
                            }
                            $noticeboard_tools .= $time_left;
                            $noticeboard_tools .= $noticeboard->auction_html($noticeboard_tools_indent,$notice['noticeboardID'],$min_bid);
                            if (ENABLE_INSTANT_BUY and $notice['quick_delete']) {
                                $noticeboard_tools .= $noticeboard_tools_indent." <br />\n";
                                $noticeboard_tools .= $noticeboard->buy_now_html($noticeboard_tools_indent,$notice['amount'],$notice['noticeboardID']);
                            }
                            $transaction_form_displayed = true;
                        } else {
                            $noticeboard_tools .= $noticeboard_tools_indent."<span class=\"nb_page_auction_title\">No bids have been made yet.</span><br /><br />\n";
                            $noticeboard_tools .= $time_left;
                        }
                    } else {
                        $min_bid = $noticeboard->auction_winning_amount + 1;
                        if (!$expired and !$bought) {
                            if ($noticeboard->auction_winning_member_id != $_SESSION['member_id']) {
                                $noticeboard_tools .= $noticeboard_tools_indent."<span class=\"nb_page_auction_title\"><strong>".$member_info->full_name($noticeboard->auction_winning_member_id)."</strong> is winning this auction with a bid of <strong>".$noticeboard->auction_winning_amount."</strong> ".CURRENCY_NAME."</span><br /><br />\n";
                            } else {
                                $noticeboard_tools .= $noticeboard_tools_indent."<span class=\"nb_page_auction_title\"><strong>You</strong> are winning this auction with a bid of <strong>".$noticeboard->auction_winning_amount."</strong> ".CURRENCY_NAME."</span><br /><br />\n";
                            }
                        } else {
                            if ($expired and !$bought) {
                                $noticeboard_tools .= $noticeboard_tools_indent."<span class=\"nb_page_auction_title\">This auction ended with no bids</span><br /><br />\n";
                            } elseif ($bought and !$expired) {
                                $noticeboard_tools .= $noticeboard_tools_indent."<span class=\"nb_page_auction_title\"><strong>" . $member_info->full_name($noticeboard->auction_winning_member_id) . "</strong> purchased this item with the \"Buy Now\" feature for <strong>".$noticeboard->auction_winning_amount."</strong> ".CURRENCY_NAME."</span><br /><br />\n";
                            } else {
                                if ($noticeboard->auction_winning_member_id != $_SESSION['member_id']) {
                                    $noticeboard_tools .= $noticeboard_tools_indent . '<span class="nb_page_auction_title"><strong>' . $member_info->full_name($noticeboard->auction_winning_member_id) . T_('</strong> won this auction with a bid of <strong>') . $noticeboard->auction_winning_amount . '</strong> ' . CURRENCY_NAME."</span><br /><br />\n";
                                } else {
                                    $noticeboard_tools .= $noticeboard_tools_indent . T_('<span class="nb_page_auction_title"><strong>You</strong> won this auction with a bid of <strong>') . $noticeboard->auction_winning_amount . '</strong> ' . CURRENCY_NAME . "</span><br /><br />\n";
                                }
                            }
                        }
                        if (is_array($noticeboard->bids)) {
                            if (!$expired and !$bought) $noticeboard_tools .= $time_left;
                            $noticeboard_tools .= $noticeboard->bid_list($i);
                            $noticeboard_tools .= $noticeboard_tools_indent." <br />\n";
                        } else {
                            if (!$expired and !$bought) {
                                $noticeboard_tools .= $time_left;
                            }
                        }

                        if ($notice['accountID'] != $_SESSION['member_id'] and !$expired and !$bought) {
                            if ($noticeboard->auction_winning_member_id != $_SESSION['member_id']) {
                                $noticeboard_tools .= $noticeboard->auction_html($noticeboard_tools_indent,$notice['noticeboardID'],($min_bid + 9));
                                $transaction_form_displayed = true;
                            }
                            if (ENABLE_INSTANT_BUY and $notice['quick_delete']) {
                                $noticeboard_tools .= $noticeboard_tools_indent." <br />\n";
                                $noticeboard_tools .= $noticeboard->buy_now_html($noticeboard_tools_indent,$notice['amount'],$notice['noticeboardID']);
                                $transaction_form_displayed = true;
                            }
                        }
                    }
                }
                if ($min_bid) {
                    $javascript .= $noticeboard->auction_javascript($min_bid);
                }
            }
        }
        $noticeboard_tools .= $noticeboard_tools_indent."<!-- /noticeboard_tools -->\n";
        if (ENABLE_COMMENTS) {
            $comment_html .= $comments->comments_list($comment_html_indent,$notice['noticeboardID'],0,0);
            if ($comment_edited) $comments->clear();
            if (user_type() or ENABLE_GUEST_COMMENTS) {
                $comment_form .= $comments->form_html($comment_form_indent,'add',NOTICEBOARD_URL.'/'.$notice['noticeboardID'].'/','Re: '.$notice['title'],$notice['noticeboardID'],0,0);
            }
            if ($transaction_form_displayed and !$print) {
                $style->dynamic_elements .= " div#comment_list {border-top: 1px solid ".TAB_BORDER_COLOUR."; margin-top:10px; padding-top:5px; }\n";
            }
        }

        if (user_type() == 0 or $_SESSION["member_validated"] == 0 or $_SESSION["member_suspended"] == 1) {
            $noticeboard_tools .= $noticeboard_tools_indent . '<br /><strong>' . T_('Please Login or ') . '<a href="' . URL . MEMBERS_URL . '"register/">' . T_('register</a> to gain access to all details of this entry.') . "</strong><br /><br /><br />\n";
        }

    }
} else {
    $messages .= $messages_indent . '<span class="message">' . T_('The owner of this ') . strtolower(NOTICEBOARD_NAME_SINGULAR) . T(' has been suspended.') . '<br />' . ucfirst(MEMBERS_NAME_PLURAL) . T_(' will not be able to access it until this account is re-activated') . "</span><br /><br />\n";
}

?>