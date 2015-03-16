<?php
class comments {

    var $id,
        $comment,
        $title,
        $alternative_title,
        $noticeboard_id,
        $article_id,
        $event_id,
        $member_id,
        $guest_name,
        $added,
        $error;

    function comments() {
        $this->error    = "No errors";
        $this->added  = false;
        $this->set_required_variables();
    }

    function clear() {
        $this->id = '';
        $this->title = '';
        $this->comment = '';
    }
    function set_required_variables() {
        $mysql = new mysql;
        $mysql->result('SELECT require_comment_title , require_comment_body FROM config LIMIT 1');
        $this->require_comment_title   = $mysql->result['require_comment_title'];
        $this->require_comment_body = $mysql->result['require_comment_body'];
    }
    function build_comment($id) {
        $mysql = new mysql;
        if ($mysql->result('SELECT * FROM comments WHERE commentID = '.$id.' LIMIT 1')) {
            $this->id                      =       $id;
            $this->title                   =       $mysql->result['title'];
            $this->member_id        =       $mysql->result['accountID'];
            $this->guest_name      =       $mysql->result['guest_name'];
            $this->comment          =       $mysql->result['comment'];
            $this->article_id           =       $mysql->result['articleID'];
            $this->noticeboard_id   =       $mysql->result['noticeboardID'];
            $this->event_id            =       $mysql->result['eventID'];
            return true;
        }
        return false;
    }

    function check_required($bool,$position) {
        if ($bool == 1) {
            if ($position == 1) {
                return '<span class="required_field">';
            } else {
                return '</span>';
            }
        }
    }

    function validate_form($alternative_title) {
        $this->error = '';
        $post_post = remove_slashes($_POST);
        if (!isset($post_post['comment_title'])) {
            if ($this->require_comment_title) {
                $this->error .= 'Title required<br />';
            }
            $this->title = $alternative_title;
        } else {
            $this->title = remove_bad_tags($post_post['comment_title']);
        }
        if (!isset($post_post['comment_body'])) {
            if ($this->require_comment_body) {
                $this->error .= ucfirst(COMMENT_NAME_SINGULAR) . ' body required<br />';
            }
        } else {
            if (!$post_post['comment_body']) {
                $this->error .= ucfirst(COMMENT_NAME_SINGULAR) . ' body required<br />';
            }
            if (VALIDATE_XHTML) {
                $xhtml_report = valid_XHTML($post_post['comment_body']);
                if ($xhtml_report) {
                    $this->error .= $xhtml_report;
                }
            }
            $this->comment = remove_bad_tags($post_post['comment_body']);
        }
        if (isset($post_post['noticeboard_id'])) {
            if ($post_post['noticeboard_id'] and is_numeric($post_post['noticeboard_id'])) {
                $this->noticeboard_id = $post_post['noticeboard_id'];
            } else {
                $this->noticeboard_id = 0;
            }
        } else {
            $this->noticeboard_id = 0;
        }
        if (isset($post_post['article_id'])) {
            if (is_numeric($post_post['article_id']) and $post_post['article_id']) {
                $this->article_id = $post_post['article_id'];
            } else {
                $this->article_id = 0;
            }
        } else {
            $this->article_id = 0;
        }
        if (isset($post_post['event_id'])) {
            if ($post_post['event_id'] and is_numeric($post_post['event_id'])) {
                $this->event_id = $post_post['event_id'];
            } else {
                $this->event_id = 0;
            }
        } else {
            $this->event_id = 0;
        }
        if (isset($post_post['comment_id'])) {
            $this->id = $post_post['comment_id'];
        }
        if (!$_SESSION['member_id'] AND ENABLE_GUEST_COMMENTS) {
            if (isset($post_post['guest_name'])) {
                $this->guest_name = remove_bad_tags($post_post['guest_name']);
            } else {
                $this->guest_name = 'Guest';
            }
        } elseif (isset($post_post['guest_name'])) {
            $this->guest_name = $post_post['guest_name'];
        }
        if ($this->error) {
            return false;
        } else {
            return true;
        }
    }

    function form_html($i,$type,$action_page,$title,$noticeboard_id,$article_id,$event_id) {
        if ($this->added) {
            $this->title = '';
            $this->comment = '';
        }
        if ($this->title) {
            $title = $this->title;
        }

        if (strpos(' '.$action_page,URL)) {
            $action_page = str_replace(URL,'',$action_page);
        }
        $z = '<!-- comment_form -->'."\n";
        $z .= '<div id="comment_form">'."\n";
        if ($type == 'add') {
            $z .= '<span class="add_comment_title">Add ' . a(COMMENT_NAME_SINGULAR) . ' ' . ucwords(COMMENT_NAME_SINGULAR) . '</span><br /><br />';
        }
        $z .= 'Required fields are <span class="required_field">' . REQUIRED_DISPLAY . '</span>.<br /><br />';
        $z .= '<fieldset><br />';
        $z .= '<form name="comment" method="post" action="'.URL.$action_page.append_url().'">';
        if(!user_type() and ENABLE_GUEST_COMMENTS) {
            $z .= '  <label for="guest_name">Guest Name:</label>';
            $z .= '  <input type="text" id="guest_name" name="guest_name" maxlength="50" value="Guest" /><br class="left" />';
        } elseif ($type == 'edit' and $this->guest_name) {
            $z .= '  <label for="guest_name">Guest Name:</label>';
            $z .= '  <input type="text" id="guest_name" name="guest_name" maxlength="50" value="' . $this->guest_name . '" /><br class="left" />';
        }
        $z .= '  <label for="comment_title">' . $this->check_required($this->require_comment_title,1) . 'Title:' . $this->check_required($this->require_comment_title,2) . '</label>';
        $z .= '  <input type="text" id="comment_title" name="comment_title" maxlength="255" value="' . htmlspecialchars($title) . '" /><br class="left" />';
        $z .= '  <label for="comment_body">' . $this->check_required($this->require_comment_body,1) . ucwords(COMMENT_NAME_SINGULAR) . ':' . $this->check_required($this->require_comment_body,2) . '</label>';
        $z .= '  <textarea id="comment_body" name="comment_body">' . htmlspecialchars($this->comment) . '</textarea>';

        if ($type == 'edit' and $this->id) {
            $z .= '  <input type="hidden" name="comment_id" value="' . $this->id . '" />';
        }
        if ($noticeboard_id) {
            $z .= '  <input type="hidden" name="noticeboard_id" value="' . $noticeboard_id . '" />';
        }
        if ($article_id) {
            $z .= '  <input type="hidden" name="article_id" value="' . $article_id . '" />';
        }
        if ($event_id) {
            $z .= '  <input type="hidden" name="event_id" value="' . $event_id . '" />';
        }
        if ($type == 'add') {
            $z .= '  <input class="comment_button" type="submit" name="submit" value="Make ' . ucwords(COMMENT_NAME_SINGULAR) . '" />';
        } else {
            $z .= '  <input class="comment_button" type="submit" name="submit" value="Edit ' . ucwords(COMMENT_NAME_SINGULAR) . '" />';
            if (ALLOW_COMMENT_DELETION) {
                $z .= '  <input class="comment_button" type="submit" name="submit" value="Delete ' . ucwords(COMMENT_NAME_SINGULAR) . '" />';
            }
        }
        $z .= ' </form>';
        $z .= '</fieldset>';
        $z .= '</div>';
        $z .= '<!-- /comment_form -->';
        return $z;

    }

    function add_comment() {
        $mysql = new mysql;
        global $date;

        $insert = array();

        if ($_SESSION["member_id"]) {
            $insert[0]['name'] = 'accountID';
            $insert[0]['value'] = $_SESSION["member_id"];
        } elseif (ENABLE_GUEST_COMMENTS) {
            $insert[0]['name'] = 'guest_name';
            $insert[0]['value'] = addslashes($this->guest_name);
        } else {
            return false;
        }

        $insert[1]['name'] = 'created_day';
        $insert[1]['value'] = $date['day'];
        $insert[2]['name'] = 'created_month';
        $insert[2]['value'] = $date['month'];
        $insert[3]['name'] = 'created_year';
        $insert[3]['value'] = $date['year'];

        $insert[4]['name'] = 'title';
        $insert[4]['value'] = mysql_real_escape_string($this->title);
        $insert[5]['name'] = 'comment';
        $insert[5]['value'] = mysql_real_escape_string($this->comment);

        $insert[6]['name'] = 'noticeboardID';
        $insert[6]['value'] = $this->noticeboard_id;
        $insert[7]['name'] = 'articleID';
        $insert[7]['value'] = $this->article_id;
        $insert[8]['name'] = 'created_hour';
        $insert[8]['value'] = $date['hour'];
        $insert[9]['name'] = 'created_minute';
        $insert[9]['value'] = $date['minutes'];
        $insert[10]['name'] = 'eventID';
        $insert[10]['value'] = $this->event_id;

        if (!$mysql->insert_values('comments',$insert)) {
            $this->error = $mysql->error;
            return false;
        } else {
            $this->id = $mysql->inserted_id;
            $this->added = true;
            return true;
        }
    }

    function edit_comment() {
        $mysql = new mysql;
        if ($this->id) {
            $query = "UPDATE comments SET guest_name = '" . mysql_real_escape_string($this->guest_name) . "', title = '" . mysql_real_escape_string($this->title) . "', comment = '" . mysql_real_escape_string($this->comment) . "', edited_by = " . $_SESSION['member_id'] . ", edited_day = " . $GLOBALS['date']['day'] . ", edited_month = " . $GLOBALS['date']['month'] . ", edited_year = " . $GLOBALS['date']['year'] . " WHERE commentID = " . $this->id . " LIMIT 1";
            if ($mysql->query($query)) {
                return true;
            }
        }
        return false;
    }
    function delete_comment($id) {
        $mysql = new mysql;
        if (!$id) return false;
        $query = "DELETE FROM comments WHERE commentID = " . $id . " LIMIT 1";
        if ($mysql->query($query)) {
            return true;
        }
        return false;
    }

    function comments_list($i,$noticeboard_id,$article_id,$event_id) {
        $mysql = new mysql;
        global $user;
        global $links;
        global $image;
        global $style;
        $z = '';
        $image_shown = false;
        if ($mysql->build_array('SELECT * FROM comments WHERE noticeboardID = ' . $noticeboard_id . ' AND articleID = ' . $article_id . ' AND eventID = ' . $event_id . ' ORDER BY created_year, created_month, created_day, created_hour, created_minute')) {
            if (!$mysql->num_rows) return '';
            $z .= '<!-- comment_list -->';
            $z .= '<div id="comment_list">';
            $z .= '<span class="comment_list_title">' . COMMENT_NAME . '</span><br /><br />';
            foreach ($mysql->result as $comment) {
                if (ENABLE_IMAGES and COMMENT_MEMBER_IMAGES and user_type() and !$_SESSION["member_suspended"] and $_SESSION["member_validated"]) {
                    if ($mysql->result('SELECT imageID FROM accounts WHERE accountID = '.$comment['accountID'].' LIMIT 1')) {
                        if ($mysql->result['imageID']) {
                            if ($image->img($mysql->result['imageID'],'t',$user->first_name($comment['accountID']),'comment_image')) {
                                $z .= $i.' <a href="' . URL.MEMBER_LIST_URL . '/' . $comment['accountID'] . '/' . append_url(0) . '">' . $image->img . '</a>';
                                $image_shown = true;
                            }
                        }
                    }
                }
                $z .= '<span class="comment_title">' . $comment['title'] . '</span><br />';
                $z .= '<span class="comment_details">Posted by ';
                if ((user_type() == 0 or $_SESSION["member_validated"] == 0 or $_SESSION["member_suspended"] == 1) and $comment['accountID']) {
                    $z .= '<a href="' . URL.MEMBER_LIST_URL . '/' . $comment['accountID'] . '/">' . ucwords(MEMBERS_NAME_SINGULAR) . ' ' . $comment['accountID'] . "</a>";
                } elseif ((user_type() == 1 or user_type() == 2) and $comment['accountID']) {
                    $name = $user->full_name($comment['accountID']);
                    $z .= '<a href="' . URL.MEMBER_LIST_URL . '/' . $comment['accountID'] . '/' . append_url(0) . '">' . $name;
                    $z .= '</a>';
                } else {
                    if ($comment['guest_name']) {
                        $z .= $comment['guest_name'];
                    } else {
                        $z .= 'Guest';
                    }
                }
                $z .= ' ' . return_month($comment['created_month']) . ' ' . $comment['created_day'] . ', ' . $comment['created_year'] . ' - ' . return_time($comment['created_hour'],$comment['created_minute']);
                if (isset($_SESSION["member_id"])) {
                    if ((($comment['accountID'] == $_SESSION["member_id"]) or user_type() == 2) and $links->build_url(1,11)) {
                        $z .= ' (<a href="' . URL . $links->complete_url . $comment['commentID'] . '/' . append_url(0) . '">edit</a>)';
                    }
                }
                $z .= '</span><br /><br />';
                $z .= '<span class="comment_body">' . indent_variable(' ',$comment['comment']);
                $z .= $i." </span><br />\n";
                if (SHOW_COMMENT_EDITED and $comment['edited_by']) {
                    $z .= '<span class="comment_details">Last edited by ';
                    if (user_type() == 0 or $_SESSION["member_validated"] == 0 or $_SESSION["member_suspended"] == 1) {
                        $z .= '<a href="' . URL . MEMBER_LIST_URL . '/' . $comment['edited_by'] . '/">' . ucwords(MEMBERS_NAME_SINGULAR) . ' ' . $comment['edited_by'] . '</a>';
                    } elseif (user_type() == 1 or user_type() == 2) {
                        $z .= '<a href="' . URL . MEMBER_LIST_URL . '/' . $comment['edited_by'] . '/' . append_url(0) . '">' . $user->first_name($comment['edited_by']) . '</a>';
                    }
                    $z .= ' ' . return_month($comment['edited_month']) . ' ' . $comment['edited_day'] . ', ' . $comment['edited_year'] . '</span><br />';
                }
                $z .= ' <br />';

            }
            $z .= '</div>';
            $z .= '<!-- /comment_list -->';
            if ($image_shown) {
                $style->dynamic_elements .= ' img.comment_image {float:left; width:' . IMAGE_WIDTH_THUMB_MEMBER . '; margin-right:5px; }';
            }
            return $z;
        }
    }
}

?>