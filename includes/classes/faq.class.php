<?php

class faq {

    var $id,
        $member_id,
        $category,
        $parent,
        $question,
        $answer,
        $day,
        $month,
        $year,
        $validated,
        $position,

        $categories,
        $category_javascript,
        $faq_array,
        $next_position,
        $parents,
        $dependents,
        $submit_attempt,
        $num_sub_categories,
        $num_links,
        $error;

    function events() {
        $this->error = "No errors";
        $this->submit_attempt = false;
    }
    function clear() {
        $this->answer = '';
        $this->question = '';
        $this->parent = '';
        $this->category = '';
        $this->submit_attempt = false;
    }
    function validate($id) {
        $mysql = new mysql;
        if ($mysql->query('UPDATE faq SET validated = 1 WHERE faqID = '.$id.' LIMIT 1')) {
            return true;
        } else {
            return false;
        }
    }
    function next_position() {
        $mysql = new mysql;
        if ($mysql->result('SELECT max(position) as last_position FROM faq')) $this->next_position = $mysql->result['last_position'] + 1;
    }
    function confirm_deletion($i,$id,$url) {
        global $links;
        $mysql = new mysql;

        $z = $i.'<div class="confirm_deletion">'."\n";
        $z .= $i.' Are you sure you want to delete '.strtolower(FAQ_NAME_SINGULAR).' #'.$id."?<br />\n";
        $z .= $i.' This category contains '.$this->num_links.' links and '.$this->num_sub_categories." sub-categories<br /><br />\n";
        $z .= $i.' <form name="article" method="post" action="'.URL.$url.append_url().'">'."\n";
        $z .= $i.'  <input type="hidden" name="faq_id" value="'.$id.'" />'."\n";
        $z .= $i.'  <input type="hidden" name="deletion_confirmed" value="1" />'."\n";
        $z .= $i.'  <input class="article_button" type="submit" name="submit" value="Delete '.ucwords(FAQ_NAME_SINGULAR).'" /><br /><br />'."\n";
        $z .= $i.'  <input class="article_button" type="submit" name="submit" value="Cancel" />'."\n";
        $z .= $i.' </form>'."\n";
        $z .= $i.'</div>'."\n";

        return $z;
    }
    function get_dependents($id) {
        $mysql = new mysql;
        $y = 0;
        if ($this->build_faq($id)) {
            if (!$this->parent) {
                return false;
            }
        }
        $this->dependents = array();
        $this->num_sub_categories = 0;
        $this->num_links = 0;
        if ($mysql->build_array('SELECT * FROM faq WHERE category = '.$id)) {
            if ($mysql->num_rows) {
                $level_one_dependents = $mysql->result;
                foreach($level_one_dependents as $level_one_dependent) {
                    if ($level_one_dependent['parent']) {
                    if ($mysql->build_array('SELECT * FROM faq WHERE category = '.$level_one_dependent['faqID'])) {
                        if ($mysql->num_rows) {
                            $level_two_dependents = $mysql->result;
                            foreach($level_two_dependents as $level_two_dependent) {
                                if ($level_two_dependent['parent']) {
                                if ($mysql->build_array('SELECT * FROM faq WHERE category = '.$level_two_dependent['faqID'])) {
                                    if ($mysql->num_rows) {
                                        $level_three_dependents = $mysql->result;
                                        foreach($level_three_dependents as $level_three_dependent) {
                                            if ($level_three_dependent['parent']) {
                                            if ($mysql->build_array('SELECT * FROM faq WHERE category = '.$level_three_dependent['faqID'])) {
                                                if ($mysql->num_rows) {
                                                    $level_four_dependents = $mysql->result;
                                                    foreach($level_four_dependents as $level_four_dependent) {
                                                        if ($level_four_dependent['parent']) {
                                                        if ($mysql->build_array('SELECT * FROM faq WHERE category = '.$level_four_dependent['faqID'])) {
                                                            if ($mysql->num_rows) {
                                                                $level_five_dependents = $mysql->result;
                                                                foreach($level_five_dependents as $level_five_dependent) {
                                                                    $this->dependents[$y]['faqID'] = $level_five_dependent['faqID'];
                                                                    $y++;
                                                                    if ($level_five_dependent['parent']) {
                                                                        $this->num_sub_categories++;
                                                                    } else {
                                                                        $this->num_links++;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                        }
                                                        $this->dependents[$y]['faqID'] = $level_four_dependent['faqID'];
                                                        $y++;
                                                        if ($level_four_dependent['parent']) {
                                                            $this->num_sub_categories++;
                                                        } else {
                                                            $this->num_links++;
                                                        }
                                                    }

                                                }
                                            }
                                            }
                                            $this->dependents[$y]['faqID'] = $level_three_dependent['faqID'];
                                            $y++;
                                            if ($level_three_dependent['parent']) {
                                                $this->num_sub_categories++;
                                            } else {
                                                $this->num_links++;
                                            }
                                        }
                                    }
                                }
                                }
                                $this->dependents[$y]['faqID'] = $level_two_dependent['faqID'];
                                $y++;
                                if ($level_two_dependent['parent']) {
                                    $this->num_sub_categories++;
                                } else {
                                    $this->num_links++;
                                }
                            }
                        }
                    }
                    }
                    $this->dependents[$y]['faqID'] = $level_one_dependent['faqID'];
                    $y++;
                    if ($level_one_dependent['parent']) {
                        $this->num_sub_categories++;
                    } else {
                        $this->num_links++;
                    }
                }
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    function delete($id) {
        $mysql = new mysql;
        $this->error = '';
        if (!$mysql->query('DELETE FROM faq WHERE faqID = '.$id.' LIMIT 1')) {
            $this->error .= $mysql->error;
        }
        if (is_array($this->dependents)) {
            foreach ($this->dependents as $dependent) {
                if (!$mysql->query('DELETE FROM faq WHERE faqID = '.$dependent['faqID'].' LIMIT 1')) {
                    $this->error .= $mysql->error;
                }
            }
        }
        if ($this->error) {
            return false;
        } else {
            return true;
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
    function get_parents() {
        $mysql = new mysql;
        if ($mysql->build_array('SELECT * FROM faq WHERE parent = 1 ORDER BY question')) $this->parents = $mysql->result;

    }
    function move_faq($id_to_move,$id_to_replace) {
        $mysql = new mysql;
        $current_pos = 0;
        $new_pos = 0;
        if ($mysql->result('SELECT position FROM faq WHERE faqID = '.$id_to_move.' LIMIT 1')) {
            $current_pos = $mysql->result['position'];
        }
        if ($mysql->result('SELECT position FROM faq WHERE faqID = '.$id_to_replace.' LIMIT 1')) {
            $new_pos = $mysql->result['position'];
        }
        if (!$current_pos or !$new_pos) {
            $this->error = $mysql->error;
            return false;
        }
        if (!$mysql->query('UPDATE faq SET position = '.$new_pos.' WHERE faqID = '.$id_to_move.' LIMIT 1')) {
            return false;
        }
        if (!$mysql->query('UPDATE faq SET position = '.$current_pos.' WHERE faqID = '.$id_to_replace.' LIMIT 1')) {
            return false;
        }
        return true;
    }
    function build_faq($id) {
        $mysql = new mysql;
        if ($mysql->result('SELECT * FROM faq WHERE faqID = '.$id.' LIMIT 1')) {
            $this->id = $id;
            $this->member_id =      $mysql->result['accountID'];
            $this->category =       $mysql->result['category'];
            $this->question =       $mysql->result['question'];
            $this->answer =         $mysql->result['answer'];
            $this->parent =         $mysql->result['parent'];
            $this->day =            $mysql->result['day'];
            $this->month =          $mysql->result['month'];
            $this->year =           $mysql->result['year'];
            $this->validated =      $mysql->result['validated'];
            // don't want zeros appearing in form
            if (!$this->question) $this->question = '';
            if (!$this->answer) $this->answer = '';
            return true;
        }
        return false;
    }

    function validate_form() {
        $this->submit_attempt = true;
        $this->error = '';
        $post_post = remove_slashes($_POST);
        if (!isset($post_post['faq_parent'])) {
            $this->parent = 0;
        } else {
            $this->parent = 1;
        }
        if (!isset($post_post['faq_question'])) {
            $this->error .= 'Question required<br />';
        } else {
            if (!$post_post['faq_question']) {
                $this->error .= 'Question required<br />';
            }
            if (VALIDATE_XHTML) {
                $xhtml_report = valid_XHTML($post_post['faq_question']);
                if ($xhtml_report) {
                    $this->error .= $xhtml_report;
                }
            }
            $this->question = remove_bad_tags($post_post['faq_question']);

        }
        if (!$post_post['faq_answer'] and !$this->parent) {
            $this->error .= 'Answer required<br />';
        }
        if (VALIDATE_XHTML) {
            $xhtml_report = valid_XHTML($post_post['faq_answer']);
            if ($xhtml_report) {
                $this->error .= $xhtml_report;
            }
        }
        $this->answer = remove_bad_tags($post_post['faq_answer']);

        if (!isset($post_post['faq_category'])) $post_post['faq_category'] = 0;
        if (is_numeric($post_post['faq_category'])) {
            $this->category = $post_post['faq_category'];
        } elseif (!$post_post['faq_category']) {
            $this->category = 0;
        } else {
            $this->error .= 'Input Error<br />';
        }

        if ($this->error) {
            return false;
        } else {
            return true;
        }
    }
    function faq_level($id,$category,$parent) {
        if (!$category) return 0;
        $mysql = new mysql;
        if ($parent) {
            $s = 1;
        } else {
            $s = 0;
        }
        for($x=$s;$x<5;$x++) {
            if($mysql->result('SELECT faqID,category FROM faq WHERE faqID = '.$category.' AND parent = 1 LIMIT 1')) {
                $category = $mysql->result['category'];
                if (!$category) {
                    return $x;
                }
            }
        }
        return $x - 1;

    }

    function answer_javascript() {
        $z = 'function disable_answer() {'."\n";
        $z .= "\t".'if (document.faq.faq_parent.checked == true) {'."\n";
        $z .= "\t\t".'document.faq.faq_answer.disabled = true;'."\n";
        $z .= "\t"."} else {\n";
        $z .= "\t\t".'document.faq.faq_answer.disabled = false;'."\n";
        $z .= "\t"."}\n";
        $z .= "}\n";
        $z .= "disable_answer();\n";
        return $z;
    }

    function form_html($i,$type,$action_page) {
        $this->get_parents();
        $z = $i.'<!-- faq_form -->'."\n";
        $z .= $i.'<div class="faq_form">'."\n";
        $level = 0;
        if ($type == 'add') {
            $z .= $i."<span class=\"add_faq_title\">Add ".a(FAQ_NAME_SINGULAR).' '.ucwords(FAQ_NAME_SINGULAR)."</span><br /><br />\n";
        } else {
            $level = $this->faq_level($this->id,$this->category,$this->parent);
            $z .= $i."<span class=\"add_faq_title\">Edit ".ucwords(FAQ_NAME_SINGULAR)." #".$this->id." or <a href=\"".URL.$action_page.append_url(0)."\">Add a New ".ucwords(FAQ_NAME_SINGULAR)."</a></span><br /><br />\n";
        }
        $z .= $i."Required fields are <span class=\"required_field\">".REQUIRED_DISPLAY."</span>.<br /><br />\n";
        $z .= $i."<fieldset><br />\n";
        $z .= $i.' <form name="faq" method="post" action="'.URL.$action_page;
        if ($this->id and $type == 'edit') {
            $z .= $this->id.'/';
        }
        $z .= append_url().'">'."\n";

        $z .= $i.'  <label for="faq_question"><span class="required_field">Question:</span></label>'."\n";
        $z .= $i.'  <textarea id="faq_question" name="faq_question">'.htmlspecialchars($this->question)."</textarea><br /><br />\n";
        $z .= $i.'  <label for="faq_answer"><span class="required_field">Answer:</span></label>'."\n";
        $z .= $i.'  <textarea id="faq_answer" name="faq_answer">'.htmlspecialchars($this->answer)."</textarea><br /><br />\n";

        if (is_array($this->parents)) {
            $z .= $i.'  <label for="faq_category"><span class="required_field">'.ucwords(FAQ_NAME_SINGULAR).' Category:</span></label>'."\n";
            $z .= $i."  <select id=\"faq_category\" name=\"faq_category\" onClick=\"check_category();\">\n";
            if ($this->category) {
                $z .= $i."   <option value=\"0\">None</option>\n";
            } else {
                $z .= $i."   <option value=\"0\" selected=\"selected\">None</option>\n";
            }
            foreach ($this->categories as $parent) {
                if ($this->id != $parent['faqID']) {
                    $z .= $i."   <option value=\"".$parent['faqID']."\"".selected('','',$parent['faqID'],$this->category).">";
                    for ($j=0;$j<$parent['level'];$j++) {
                        $z .= '&nbsp;';
                    }
                    $z .= ucwords($parent['question'])."</option>\n";
                }
            }
            $z .= $i."  </select><br class=\"left\" />\n";
        } else {
            $z .= $i.' There are no categories to this faq. Add one by checking "Make this a category".'."\n";
        }
        $z .= "$i <label for=\"faq_parent\">Make this a category</label>\n";
        $z .= "$i <input type=\"checkbox\" id=\"faq_parent\" name=\"faq_parent\" value=\"1\"".$this->add_checkbox($type,0,$this->parent,0)." onClick=\"disable_answer();\"/><br class=\"left\" /><br class=\"left\" />\n";

        if ($type == 'add') {
            $z .= $i.'  <input class="faq_button" type="submit" name="submit" value="Add '.ucwords(FAQ_NAME_SINGULAR).'" />'."\n";
        } else {
            $z .= $i.'  <input class="faq_button" type="submit" name="submit" value="Edit '.ucwords(FAQ_NAME_SINGULAR).'" /><br class=\"left\" />'."\n";
            if (user_type() == 2 and !$this->validated) {
                $z .= $i.'  <input class="faq_button" type="submit" name="submit" value="Validate '.ucwords(FAQ_NAME_SINGULAR).'" />'."\n";
            }
            $z .= $i.'  <input class="faq_button" type="submit" name="submit" value="Delete '.ucwords(FAQ_NAME_SINGULAR).'" />'."\n";
        }
        $z .= $i.' </form>'."\n";
        $z .= $i."</fieldset>\n";
        $z .= $i.'</div>'."\n";
        $z .= $i.'<!-- /faq_form -->'."\n";
        return $z;

    }

    function add() {
        $mysql = new mysql;
        global $date;
        $this->next_position();
        $insert = array();

        $insert[0]['name'] = 'accountID';
        $insert[0]['value'] = $_SESSION["member_id"];

        $insert[1]['name'] = 'day';
        $insert[1]['value'] = $GLOBALS['date']['day'];
        $insert[2]['name'] = 'month';
        $insert[2]['value'] = $GLOBALS['date']['month'];
        $insert[3]['name'] = 'year';
        $insert[3]['value'] = $GLOBALS['date']['year'];

        $insert[11]['name'] = 'question';
        $insert[11]['value'] = mysql_real_escape_string($this->question);
        $insert[12]['name'] = 'answer';
        $insert[12]['value'] = mysql_real_escape_string($this->answer);

        $insert[13]['name'] = 'parent';
        $insert[13]['value'] = $this->parent;

        $insert[14]['name'] = 'category';
        $insert[14]['value'] = $this->category;

        if (VALIDATE_FAQ and user_type() !=2) {
            $insert[15]['name'] = 'validated';
            $insert[15]['value'] = '0';
        } else {
            $insert[15]['name'] = 'validated';
            $insert[15]['value'] = '1';
        }

        $insert[16]['name'] = 'position';
        $insert[16]['value'] = $this->next_position;

        if (!$mysql->insert_values('faq',$insert)) {
            $this->error = $mysql->error;
            return false;
        } else {
            $this->id = $mysql->inserted_id;
            $this->added = true;
            return true;
        }
    }

    function edit() {
        $mysql = new mysql;
        if ($this->id) {
            $query = "UPDATE faq SET parent =  ".$this->parent.", question = '".mysql_real_escape_string($this->question)."', answer = '".mysql_real_escape_string($this->answer)."', category = '".mysql_real_escape_string($this->category)."' WHERE faqID = ".$this->id." LIMIT 1";
            if ($mysql->query($query)) {
                return true;
            }
        }
        return false;
    }
    function delete_event($id) {
        $mysql = new mysql;
        if (!$id) return false;
        $query = "DELETE FROM faq WHERE faqID = ".$id." LIMIT 1";
        if ($mysql->query($query)) {
            return true;
        }
        return false;
    }
    function validation_html($i,$id,$url) {
        $z = $i.'<!-- faq_validation_form -->'."\n";
        $z .= $i."<fieldset>\n";
        $z .= $i."<form name=\"faq_validation\" action=\"".$url.append_url()."\" method=\"post\">\n";
        $z .= $i." <input type=\"hidden\" name=\"faq_id\" value=\"".$id."\" />\n";
        $z .= $i." <input id=\"faq_validation_button\" type=\"submit\" name=\"submit\" value=\"Validate ".ucwords(FAQ_NAME_SINGULAR)."\" /><br class=\"left\"/>\n";
        $z .= $i." <input id=\"faq_delete_button\" type=\"submit\" name=\"submit\" value=\"Delete ".ucwords(FAQ_NAME_SINGULAR)."\" />\n";
        $z .= $i."</form>\n";
        $z .= $i."</fieldset><br />\n";
        $z .= $i.'<!-- /faq_validation_form -->'."\n";
        return $z;
    }

    function up_down_link($id,$position,$category,$parent,$url) {
        $mysql = new mysql;

        $down_id = 0;
        if ($mysql->result('SELECT faqID FROM faq WHERE position > '.$position.' AND category = '.$category.' AND parent = '.$parent.' ORDER BY position ASC LIMIT 1')) {
            $down_id = $mysql->result['faqID'];
        }
        $up_id = 0;
        if ($mysql->result('SELECT faqID FROM faq WHERE position < '.$position.' AND category = '.$category.' AND parent = '.$parent.' ORDER BY position DESC LIMIT 1')) {
            $up_id = $mysql->result['faqID'];
        }
        if (!$down_id and !$up_id) {
            return '';
        }

        if ($down_id and $up_id) {
            return ' (<span class="up_down">Move <a href="'.URL.$url.'?faq_id='.$id.'&replace_id='.$up_id.append_url(' ?').'">Up</a> or <a href="'.URL.$url.'?faq_id='.$id.'&replace_id='.$down_id.append_url(' ?').'">Down</a>)</span>';
        }
        if ($down_id) {
            return ' (<span class="up_down">Move <a href="'.URL.$url.'?faq_id='.$id.'&replace_id='.$down_id.append_url(' ?').'">Down</a>)</span>';
        }
        if ($up_id) {
            return ' (<span class="up_down">Move <a href="'.URL.$url.'?faq_id='.$id.'&replace_id='.$up_id.append_url(' ?').'">Up</a>)</span>';
        }
    }

    function build_faq_array($i,$url,$index_only,$unvalidated = 0) {
        $mysql = new mysql;
        global $user;
        global $links;
        // ***************************************************
        // build query
        // first have to find number of rows in table
        // this will determine theoretical maximun iterations
        // and we exit the function if there are none
        // ***************************************************
        if ($mysql->num_rows('SELECT * FROM faq')) {
            if (!$mysql->num_rows) {
                return false;
            }
        } else {
            // document database error
            $this->error = $mysql->error;
            return false;
        }

        $validation_query = '';
        $query = 'SELECT * FROM faq WHERE category = 0';
        if ($unvalidated) {
            if ($unvalidated == 1) {
                $validation_query .= ' AND validated = 0';
            }
        } else {
            $validation_query .= ' AND validated = 1';
        }
        $query .= $validation_query.' ORDER BY parent ASC, position ASC';
        if ($mysql->build_array($query)) {
            if (!$mysql->num_rows) {
                return false;
            }
            $root_faqs = $mysql->result;


            $javascript = 'function check_category() {'."\n";

            $categories = array();
            $this->faq_array = array();
            $f = 0;
            $g = 1;
            foreach ($root_faqs as $root_faq) {
                // if an entry is a regular faq we just print it otherwise we have to cycle through
                if ($root_faq['parent']) {
                    $categories[$g]['faqID'] = $root_faq['faqID'];
                    $categories[$g]['question'] = $root_faq['question'];
                    $categories[$g]['level'] = 0;
                    $javascript .= "\t".'if (document.faq.faq_category.selectedIndex == '.$g.')'."\n";
                    $javascript .= "\t\t".'document.faq.faq_parent.disabled = false;'."\n";
                    $this->faq_array[$f]['faqID'] = $root_faq['faqID'];
                    $this->faq_array[$f]['parent'] = $root_faq['parent'];
                    $this->faq_array[$f]['accountID'] = $root_faq['accountID'];
                    $this->faq_array[$f]['question'] = $root_faq['question'];
                    $this->faq_array[$f]['answer'] = $root_faq['answer'];
                    $this->faq_array[$f]['day'] = $root_faq['day'];
                    $this->faq_array[$f]['month'] = $root_faq['month'];
                    $this->faq_array[$f]['year'] = $root_faq['year'];
                    $this->faq_array[$f]['faqID'] = $root_faq['faqID'];
                    $this->faq_array[$f]['position'] = $root_faq['position'];
                    $this->faq_array[$f]['category'] = $root_faq['category'];
                    $this->faq_array[$f]['validated'] = $root_faq['validated'];
                    $this->faq_array[$f]['level'] = 0;
                    $f++;
                    $g++;
                    if ($mysql->build_array('SELECT * FROM faq WHERE category = '.$root_faq['faqID'].$validation_query.' ORDER BY parent ASC, position ASC')) {
                        if ($mysql->num_rows) {
                            $level_one_faqs = $mysql->result;
                            foreach ($level_one_faqs as $level_one_faq) {
                                if ($level_one_faq['parent']) {
                                    $categories[$g]['faqID'] = $level_one_faq['faqID'];
                                    $categories[$g]['question'] = $level_one_faq['question'];
                                    $categories[$g]['level'] = 1;
                                    $javascript .= "\t".'if (document.faq.faq_category.selectedIndex == '.$g.')'."\n";
                                    $javascript .= "\t\t".'document.faq.faq_parent.disabled = false;'."\n";
                                    $this->faq_array[$f]['faqID'] = $level_one_faq['faqID'];
                                    $this->faq_array[$f]['parent'] = $level_one_faq['parent'];
                                    $this->faq_array[$f]['accountID'] = $level_one_faq['accountID'];
                                    $this->faq_array[$f]['question'] = $level_one_faq['question'];
                                    $this->faq_array[$f]['answer'] = $level_one_faq['answer'];
                                    $this->faq_array[$f]['day'] = $level_one_faq['day'];
                                    $this->faq_array[$f]['month'] = $level_one_faq['month'];
                                    $this->faq_array[$f]['year'] = $level_one_faq['year'];
                                    $this->faq_array[$f]['faqID'] = $level_one_faq['faqID'];
                                    $this->faq_array[$f]['position'] = $level_one_faq['position'];
                                    $this->faq_array[$f]['category'] = $level_one_faq['category'];
                                    $this->faq_array[$f]['validated'] = $level_one_faq['validated'];
                                    $this->faq_array[$f]['level'] = 1;
                                    $f++;
                                    $g++;
                                    if ($mysql->build_array('SELECT * FROM faq WHERE category = '.$level_one_faq['faqID'].$validation_query.' ORDER BY parent ASC, position ASC')) {
                                        if ($mysql->num_rows) {
                                            $level_two_faqs = $mysql->result;
                                            foreach ($level_two_faqs as $level_two_faq) {
                                                if ($level_two_faq['parent']) {
                                                    $categories[$g]['faqID'] = $level_two_faq['faqID'];
                                                    $categories[$g]['question'] = $level_two_faq['question'];
                                                    $categories[$g]['level'] = 2;
                                                    $javascript .= "\t".'if (document.faq.faq_category.selectedIndex == '.$g.')'."\n";
                                                    $javascript .= "\t\t".'document.faq.faq_parent.disabled = false;'."\n";
                                                    $this->faq_array[$f]['faqID'] = $level_two_faq['faqID'];
                                                    $this->faq_array[$f]['parent'] = $level_two_faq['parent'];
                                                    $this->faq_array[$f]['accountID'] = $level_two_faq['accountID'];
                                                    $this->faq_array[$f]['question'] = $level_two_faq['question'];
                                                    $this->faq_array[$f]['answer'] = $level_two_faq['answer'];
                                                    $this->faq_array[$f]['day'] = $level_two_faq['day'];
                                                    $this->faq_array[$f]['month'] = $level_two_faq['month'];
                                                    $this->faq_array[$f]['year'] = $level_two_faq['year'];
                                                    $this->faq_array[$f]['faqID'] = $level_two_faq['faqID'];
                                                    $this->faq_array[$f]['position'] = $level_two_faq['position'];
                                                    $this->faq_array[$f]['category'] = $level_two_faq['category'];
                                                    $this->faq_array[$f]['validated'] = $level_two_faq['validated'];
                                                    $this->faq_array[$f]['level'] = 2;
                                                    $f++;
                                                    $g++;
                                                    if ($mysql->build_array('SELECT * FROM faq WHERE category = '.$level_two_faq['faqID'].$validation_query.' ORDER BY parent ASC, position ASC')) {
                                                        if ($mysql->num_rows) {
                                                            $level_three_faqs = $mysql->result;
                                                            foreach ($level_three_faqs as $level_three_faq) {
                                                                if ($level_three_faq['parent']) {
                                                                    $categories[$g]['faqID'] = $level_three_faq['faqID'];
                                                                    $categories[$g]['question'] = $level_three_faq['question'];
                                                                    $categories[$g]['level'] = 3;
                                                                    $javascript .= "\t".'if (document.faq.faq_category.selectedIndex == '.$g.')'."\n";
                                                                    $javascript .= "\t\t".'document.faq.faq_parent.disabled = false;'."\n";
                                                                    $this->faq_array[$f]['faqID'] = $level_three_faq['faqID'];
                                                                    $this->faq_array[$f]['parent'] = $level_three_faq['parent'];
                                                                    $this->faq_array[$f]['accountID'] = $level_three_faq['accountID'];
                                                                    $this->faq_array[$f]['question'] = $level_three_faq['question'];
                                                                    $this->faq_array[$f]['answer'] = $level_three_faq['answer'];
                                                                    $this->faq_array[$f]['day'] = $level_three_faq['day'];
                                                                    $this->faq_array[$f]['month'] = $level_three_faq['month'];
                                                                    $this->faq_array[$f]['year'] = $level_three_faq['year'];
                                                                    $this->faq_array[$f]['faqID'] = $level_three_faq['faqID'];
                                                                    $this->faq_array[$f]['position'] = $level_three_faq['position'];
                                                                    $this->faq_array[$f]['category'] = $level_three_faq['category'];
                                                                    $this->faq_array[$f]['validated'] = $level_three_faq['validated'];
                                                                    $this->faq_array[$f]['level'] = 3;
                                                                    $f++;
                                                                    $g++;
                                                                    if ($mysql->build_array('SELECT * FROM faq WHERE category = '.$level_three_faq['faqID'].$validation_query.' ORDER BY parent ASC, position ASC')) {
                                                                        if ($mysql->num_rows) {
                                                                            $level_four_faqs = $mysql->result;
                                                                            foreach ($level_four_faqs as $level_four_faq) {
                                                                                if ($level_four_faq['parent']) {
                                                                                    $categories[$g]['faqID'] = $level_four_faq['faqID'];
                                                                                    $categories[$g]['question'] = $level_four_faq['question'];
                                                                                    $categories[$g]['level'] = 4;
                                                                                    $javascript .= "\t".'if (document.faq.faq_category.selectedIndex == '.$g.') {'."\n";
                                                                                    $javascript .= "\t\t".'document.faq.faq_parent.disabled = true;'."\n";
                                                                                    $javascript .= "\t\t".'document.faq.faq_answer.disabled = false;'."\n";
                                                                                    $javascript .= "\t".'}'."\n";
                                                                                    $this->faq_array[$f]['faqID'] = $level_four_faq['faqID'];
                                                                                    $this->faq_array[$f]['parent'] = $level_four_faq['parent'];
                                                                                    $this->faq_array[$f]['accountID'] = $level_four_faq['accountID'];
                                                                                    $this->faq_array[$f]['question'] = $level_four_faq['question'];
                                                                                    $this->faq_array[$f]['answer'] = $level_four_faq['answer'];
                                                                                    $this->faq_array[$f]['day'] = $level_four_faq['day'];
                                                                                    $this->faq_array[$f]['month'] = $level_four_faq['month'];
                                                                                    $this->faq_array[$f]['year'] = $level_four_faq['year'];
                                                                                    $this->faq_array[$f]['faqID'] = $level_four_faq['faqID'];
                                                                                    $this->faq_array[$f]['position'] = $level_four_faq['position'];
                                                                                    $this->faq_array[$f]['category'] = $level_four_faq['category'];
                                                                                    $this->faq_array[$f]['validated'] = $level_four_faq['validated'];
                                                                                    $this->faq_array[$f]['level'] = 4;
                                                                                    $f++;
                                                                                    $g++;
                                                                                    if ($mysql->build_array('SELECT * FROM faq WHERE category = '.$level_four_faq['faqID'].$validation_query.' AND parent = 0 ORDER BY  position ASC')) {
                                                                                        if ($mysql->num_rows) {
                                                                                            $level_five_faqs = $mysql->result;
                                                                                            foreach ($level_five_faqs as $level_four_faq) {
                                                                                                $this->faq_array[$f]['faqID'] = $level_four_faq['faqID'];
                                                                                                $this->faq_array[$f]['parent'] = 0;
                                                                                                $this->faq_array[$f]['accountID'] = $level_four_faq['accountID'];
                                                                                                $this->faq_array[$f]['question'] = $level_four_faq['question'];
                                                                                                $this->faq_array[$f]['answer'] = $level_four_faq['answer'];
                                                                                                $this->faq_array[$f]['day'] = $level_four_faq['day'];
                                                                                                $this->faq_array[$f]['month'] = $level_four_faq['month'];
                                                                                                $this->faq_array[$f]['year'] = $level_four_faq['year'];
                                                                                                $this->faq_array[$f]['faqID'] = $level_four_faq['faqID'];
                                                                                                $this->faq_array[$f]['position'] = $level_four_faq['position'];
                                                                                                $this->faq_array[$f]['category'] = $level_four_faq['category'];
                                                                                                $this->faq_array[$f]['validated'] = $level_four_faq['validated'];
                                                                                                $this->faq_array[$f]['level'] = 4;
                                                                                                $f++;
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                } else {
                                                                                    $this->faq_array[$f]['faqID'] = $level_four_faq['faqID'];
                                                                                    $this->faq_array[$f]['parent'] = $level_four_faq['parent'];
                                                                                    $this->faq_array[$f]['accountID'] = $level_four_faq['accountID'];
                                                                                    $this->faq_array[$f]['question'] = $level_four_faq['question'];
                                                                                    $this->faq_array[$f]['answer'] = $level_four_faq['answer'];
                                                                                    $this->faq_array[$f]['day'] = $level_four_faq['day'];
                                                                                    $this->faq_array[$f]['month'] = $level_four_faq['month'];
                                                                                    $this->faq_array[$f]['year'] = $level_four_faq['year'];
                                                                                    $this->faq_array[$f]['faqID'] = $level_four_faq['faqID'];
                                                                                    $this->faq_array[$f]['position'] = $level_four_faq['position'];
                                                                                    $this->faq_array[$f]['category'] = $level_four_faq['category'];
                                                                                    $this->faq_array[$f]['validated'] = $level_four_faq['validated'];
                                                                                    $this->faq_array[$f]['level'] = 4;
                                                                                    $f++;
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                } else {
                                                                    $this->faq_array[$f]['faqID'] = $level_three_faq['faqID'];
                                                                    $this->faq_array[$f]['parent'] = $level_three_faq['parent'];
                                                                    $this->faq_array[$f]['accountID'] = $level_three_faq['accountID'];
                                                                    $this->faq_array[$f]['question'] = $level_three_faq['question'];
                                                                    $this->faq_array[$f]['answer'] = $level_three_faq['answer'];
                                                                    $this->faq_array[$f]['day'] = $level_three_faq['day'];
                                                                    $this->faq_array[$f]['month'] = $level_three_faq['month'];
                                                                    $this->faq_array[$f]['year'] = $level_three_faq['year'];
                                                                    $this->faq_array[$f]['faqID'] = $level_three_faq['faqID'];
                                                                    $this->faq_array[$f]['position'] = $level_three_faq['position'];
                                                                    $this->faq_array[$f]['category'] = $level_three_faq['category'];
                                                                    $this->faq_array[$f]['validated'] = $level_three_faq['validated'];
                                                                    $this->faq_array[$f]['level'] = 3;
                                                                    $f++;
                                                                }
                                                            }
                                                        }
                                                    }
                                                } else {
                                                    $this->faq_array[$f]['faqID'] = $level_two_faq['faqID'];
                                                    $this->faq_array[$f]['parent'] = $level_two_faq['parent'];
                                                    $this->faq_array[$f]['accountID'] = $level_two_faq['accountID'];
                                                    $this->faq_array[$f]['question'] = $level_two_faq['question'];
                                                    $this->faq_array[$f]['answer'] = $level_two_faq['answer'];
                                                    $this->faq_array[$f]['day'] = $level_two_faq['day'];
                                                    $this->faq_array[$f]['month'] = $level_two_faq['month'];
                                                    $this->faq_array[$f]['year'] = $level_two_faq['year'];
                                                    $this->faq_array[$f]['faqID'] = $level_two_faq['faqID'];
                                                    $this->faq_array[$f]['position'] = $level_two_faq['position'];
                                                    $this->faq_array[$f]['category'] = $level_two_faq['category'];
                                                    $this->faq_array[$f]['validated'] = $level_two_faq['validated'];
                                                    $this->faq_array[$f]['level'] = 2;
                                                    $f++;
                                                }
                                            }
                                        }
                                    }
                                } else {
                                    $this->faq_array[$f]['faqID'] = $level_one_faq['faqID'];
                                    $this->faq_array[$f]['parent'] = $level_one_faq['parent'];
                                    $this->faq_array[$f]['accountID'] = $level_one_faq['accountID'];
                                    $this->faq_array[$f]['question'] = $level_one_faq['question'];
                                    $this->faq_array[$f]['answer'] = $level_one_faq['answer'];
                                    $this->faq_array[$f]['day'] = $level_one_faq['day'];
                                    $this->faq_array[$f]['month'] = $level_one_faq['month'];
                                    $this->faq_array[$f]['year'] = $level_one_faq['year'];
                                    $this->faq_array[$f]['faqID'] = $level_one_faq['faqID'];
                                    $this->faq_array[$f]['position'] = $level_one_faq['position'];
                                    $this->faq_array[$f]['category'] = $level_one_faq['category'];
                                    $this->faq_array[$f]['validated'] = $level_one_faq['validated'];
                                    $this->faq_array[$f]['level'] = 1;
                                    $f++;
                                }
                            }
                        }
                    }
                } else {
                    $this->faq_array[$f]['faqID'] = $root_faq['faqID'];
                    $this->faq_array[$f]['parent'] = $root_faq['parent'];
                    $this->faq_array[$f]['accountID'] = $root_faq['accountID'];
                    $this->faq_array[$f]['question'] = $root_faq['question'];
                    $this->faq_array[$f]['answer'] = $root_faq['answer'];
                    $this->faq_array[$f]['day'] = $root_faq['day'];
                    $this->faq_array[$f]['month'] = $root_faq['month'];
                    $this->faq_array[$f]['year'] = $root_faq['year'];
                    $this->faq_array[$f]['faqID'] = $root_faq['faqID'];
                    $this->faq_array[$f]['position'] = $root_faq['position'];
                    $this->faq_array[$f]['category'] = $root_faq['category'];
                    $this->faq_array[$f]['validated'] = $root_faq['validated'];
                    $this->faq_array[$f]['level'] = 0;
                    $f++;
                }
            }
            $javascript .= "}\n";
            $this->categories = $categories;
            $this->category_javascript = $javascript;
        }
        return true;

    }

    function faq_list($i,$url,$index_only,$unvalidated = 0) {
        $mysql = new mysql;
        global $user;
        global $links;
        global $style;
        if (!$this->build_faq_array($i,$url,$index_only,$unvalidated)) {
            return '';
        }
        $num_to_validate = 0;
        if (VALIDATE_FAQ) {
            if ($mysql->num_rows('SELECT * FROM faq WHERE validated = 0')) {
                $num_to_validate = $mysql->num_rows;
            }
        }
        $z = $i.'<!-- faq_index -->'."\n";
        $z .= $i."<div id=\"faq_index\">\n";
        if ($url and user_type() != 2) {
            $z .= $i." Your ".strtolower(FAQ_NAME_PLURAL)." appear with a hyperlink. Click their link to edit them.<br /><br />\n";
        } elseif ($url and user_type() == 2) {
            $z .= $i." Add or edit ".strtolower(FAQ_NAME_PLURAL)."<br /><br />\n";
            if ($num_to_validate) {
                $z .= $i.' '.$num_to_validate." ".strtolower(FAQ_NAME_PLURAL)." need to be validated. They are marked with an <span class=\"unvalidated_faq\">*</span><br /><br />\n";
            }
        }
        $num_faqs = count($this->faq_array);
        for($f=0;$f<$num_faqs;$f++) {
            $indent = '';
            if ($this->faq_array[$f]['level']) {
                for ($q=0;$q<$this->faq_array[$f]['level'];$q++) {
                    $indent .= '<span class="indent">&nbsp;</span>&nbsp;';
                }
            }

            if ($this->faq_array[$f]['parent']) {
                if (!$url) {
                    $z .= $i.' '.$indent.'<span class="faq_index_parent_title"><a href="#'.$this->faq_array[$f]['faqID'].'">'.$this->faq_array[$f]['question']."</a></span><br />\n";
                } else {
                    if ($_SESSION['member_id'] == $this->faq_array[$f]['accountID'] or user_type() == 2) {
                        $z .= $i.' '.$indent.'<span class="faq_index_parent_title"><a href="'.URL.$url.$this->faq_array[$f]['faqID'].'/'.append_url(0).'">'.$this->faq_array[$f]['question'].'</a></span>';
                    } else {
                        $z .= $i.' '.$indent.'<span class="faq_index_parent_title">'.$this->faq_array[$f]['question'].'</span>';
                    }
                    if (!RESTRICT_UPDOWN_LINKS or user_type() == 2) {
                        $z .= $this->up_down_link($this->faq_array[$f]['faqID'],$this->faq_array[$f]['position'],$this->faq_array[$f]['category'],$this->faq_array[$f]['parent'],$url);
                    }
                    if ($url and user_type() == 2 and !$this->faq_array[$f]['validated']) {
                        $z .= '<span class="unvalidated_faq">*</span>';
                    }
                    $z .= "<br />\n";
                }
            } else {
                $indent .= '&nbsp;';
                if (!$url) {
                    $z .= $i.' '.$indent.'<span class="faq_index_link"><a href="#'.$this->faq_array[$f]['faqID'].'">'.$this->faq_array[$f]['question']."</a></span><br />\n";
                } else {
                    if ($_SESSION['member_id'] == $this->faq_array[$f]['accountID'] or user_type() == 2) {
                        $z .= $i.' '.$indent.'<span class="faq_index_link"><a href="'.URL.$url.$this->faq_array[$f]['faqID'].'/'.append_url(0).'">'.$this->faq_array[$f]['question'].'</a></span>';
                    } else {
                        $z .= $i.' '.$indent.'<span class="faq_index_link">'.$this->faq_array[$f]['question'].'</span>';
                    }
                    if (!RESTRICT_UPDOWN_LINKS or user_type() == 2) {
                        $z .= $this->up_down_link($this->faq_array[$f]['faqID'],$this->faq_array[$f]['position'],$this->faq_array[$f]['category'],$this->faq_array[$f]['parent'],$url);
                    }
                    if ($url and user_type() == 2 and !$this->faq_array[$f]['validated']) {
                        $z .= '<span class="unvalidated_faq">*</span>';
                    }
                    $z .= "<br />\n";
                }
            }
        }
        $z .= $i."</div><br /><br />\n";
        $z .= $i.'<!-- /faq_index -->'."\n";
        if ($index_only) {
            return $z;
        }
        $z .= $i.'<!-- faq_listing -->'."\n";
        $z .= $i."<div id=\"faq_list\">\n";
        for($f=0;$f<$num_faqs;$f++) {
            $indent = '';
            if ($this->faq_array[$f]['level']) {
                for ($q=0;$q<$this->faq_array[$f]['level'];$q++) {
                    $indent .= '&nbsp;&nbsp;';
                }
            }
            if ($this->faq_array[$f]['parent']) {
                $z .= $i.' '.$indent.'<span class="faq_category"><a name="'.$this->faq_array[$f]['faqID'].'">'.$this->faq_array[$f]['question']."</a></span><br />\n";
            } else {
                $indent .= '&nbsp;';
                $z .= $i.' '.$indent.'<span class="faq_question"><a name="'.$this->faq_array[$f]['faqID'].'">'.$this->faq_array[$f]['question']."</a></span><br />\n";
                if (SHOW_FAQ_DETAILS) {
                    $z .= $i.' '.$indent."<span class=\"faq_details\">Posted ".return_month($this->faq_array[$f]['month']).' '.$this->faq_array[$f]['day'].', '.$this->faq_array[$f]['year'].' by ';
                    if (user_type() == 0 or $_SESSION["member_validated"] == 0 or $_SESSION["member_suspended"] == 1) {
                        $z .= '<a href="'.URL.MEMBER_LIST_URL.'/'.$this->faq_array[$f]['accountID'].'/">'.ucwords(MEMBERS_NAME_SINGULAR).' '.$this->faq_array[$f]['accountID']."</a>";
                    } elseif (user_type() == 1 or user_type() == 2) {
                        $z .= '<a href="'.URL.MEMBER_LIST_URL.'/'.$this->faq_array[$f]['accountID'].'/'.append_url(0).'">'.$user->full_name($this->faq_array[$f]['accountID'])."</a>";
                    }

                    if (($this->member_id == $_SESSION['member_id'] or user_type() == 2) and $links->build_url(1,9)) {
                        $z .= ' (<a href="'.URL.$links->complete_url.$this->faq_array[$f]['faqID'].'/'.append_url(0).'">edit</a>)';
                    }
                    $z .= "</span><br /><br />\n";
                }
                $z .= $i.' <div class="faq_answer_'.$this->faq_array[$f]['faqID'].'">'."\n".indent_variable($i.'  ',$this->faq_array[$f]['answer'])."\n".$i." </div><br /><br />\n";
                $style->dynamic_elements .= ' div.faq_answer_'.$this->faq_array[$f]['faqID'].' {padding-left:'.(15 * $this->faq_array[$f]['level'])."px; }\n";
            }
        }
        $z .= $i."</div>\n";
        $z .= $i.'<!-- /faq_listing -->'."\n";
        return $z;
    }
    function sidebar($i,$heading) {
        $mysql = new mysql;
        $mysql->build_array('SELECT * FROM faq WHERE parent = 0 AND validated = 1 ORDER BY year DESC, month DESC, day DESC LIMIT 5');
        if (is_array($mysql->result)) {
            $z .= $i.'<div id="faq_sidebar">'."\n";
            $z .= $i.'<span class="sidebar_heading">'.$heading."</span><br />\n";
            foreach ($mysql->result as $faq) {
                $z .= $i.'<span class="sidebar_title"><a href="'.URL.FAQ_URL.'/#'.$faq['faqID'].append_url(0).'">'.$faq['question'].'</a> by '.first_name($faq['accountID'])."</span><br />\n";
                $date_difference = date_difference(array('year' => $faq['year'], 'month' => $faq['month'], 'day' => $faq['day']),array('year' => $GLOBALS['date']['year'], 'month' =>  $GLOBALS['date']['month'], 'day' =>  $GLOBALS['date']['day'], 'hour' => $GLOBALS['date']['hour']));
                if (strpos(' '.$date_difference,'days,')) {
                    $arr = explode('days,',$date_difference);
                    $date_difference = $arr[0].' days ago';
                } else {
                    if (strpos(' '.$date_difference,'month') and strpos(' '.$date_difference,'day')) {
                        $arr = explode('day,',$date_difference);
                        $date_difference = $arr[0].' day ago';
                    } else {
                        $date_difference = 'today';
                    }
                }
                $z .= $i.'<span class="sidebar_details">Posted '.$date_difference.".</span><br /><br />\n";
            }
        } else {
            return '';
        }
        $z .= $i.'</div>'."\n";
        $z .= $i."<!-- /faq_sidebar -->\n";
        return $z;
    }
}
?>