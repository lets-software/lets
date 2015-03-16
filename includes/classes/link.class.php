<?php

class lets_links {

    var $id,
        $member_id,
        $category,
        $parent,
        $title,
        $url,
        $description,
        $day,
        $month,
        $year,
        $validated,
        $position,

        $categories,
        $category_javascript,
        $link_array,
        $next_position,
        $parents,
        $dependents,
        $submit_attempt,
        $num_sub_categories,
        $num_links,
        $error;

    function lets_links() {
        $this->error = "No errors";
        $this->submit_attempt = false;
        $this->set_required_variables();
    }
    function set_required_variables() {
        $mysql = new mysql;
        $mysql->result('SELECT require_link_description, require_link_title, require_link_url FROM config');
        $this->require_link_description = $mysql->result['require_link_description'];
        $this->require_link_title = $mysql->result['require_link_title'];
        $this->require_link_url = $mysql->result['require_link_url'];
    }
    function clear() {
        $this->description = '';
        $this->title = '';
        $this->url = '';
        $this->parent = '';
        $this->category = '';
        $this->submit_attempt = false;
    }
    function validate($id) {
        $mysql = new mysql;
        if ($mysql->query('UPDATE links SET validated = 1 WHERE linkID = '.$id.' LIMIT 1')) {
            return true;
        } else {
            return false;
        }
    }
    function next_position() {
        $mysql = new mysql;
        if ($mysql->result('SELECT max(position) as last_position FROM links')) {
            $this->next_position = $mysql->result['last_position'] + 1;
        }
    }
    function confirm_deletion($i,$id,$url) {
        global $links;
        $mysql = new mysql;
        $z = $i.'<!-- confirm_deletion -->'."\n";
        $z .= $i.'<div class="confirm_deletion">'."\n";
        $z .= $i.' Are you sure you want to delete '.strtolower(LINKS_NAME_SINGULAR).' #'.$id."?<br />\n";
        $z .= $i.' This category contains '.$this->num_links.' '.strtolower(LINKS_NAME_PLURAL).' and '.$this->num_sub_categories." sub-categories<br /><br />\n";
        $z .= $i.' <form name="article" method="post" action="'.URL.$url.append_url().'">'."\n";
        $z .= $i.'  <input type="hidden" name="link_id" value="'.$id.'" />'."\n";
        $z .= $i.'  <input type="hidden" name="deletion_confirmed" value="1" />'."\n";
        $z .= $i.'  <input class="article_button" type="submit" name="submit" value="Delete '.ucwords(LINKS_NAME_SINGULAR).'" /><br /><br />'."\n";
        $z .= $i.'  <input class="article_button" type="submit" name="submit" value="Cancel" />'."\n";
        $z .= $i.' </form>'."\n";
        $z .= $i.'</div>'."\n";
        $z .= $i.'<!-- /confirm_deletion -->'."\n";
        return $z;
    }
    function get_dependents($id) {
        $mysql = new mysql;
        $y = 0;
        if ($this->build_link($id)) {
            if (!$this->parent) {
                return false;
            }
        }
        $this->dependents = array();
        $this->num_sub_categories = 0;
        $this->num_links = 0;
        if ($mysql->build_array('SELECT * FROM links WHERE category = '.$id)) {
            if ($mysql->num_rows) {
                $level_one_dependents = $mysql->result;
                foreach($level_one_dependents as $level_one_dependent) {
                    if ($level_one_dependent['parent']) {
                    if ($mysql->build_array('SELECT * FROM links WHERE category = '.$level_one_dependent['linkID'])) {
                        if ($mysql->num_rows) {
                            $level_two_dependents = $mysql->result;
                            foreach($level_two_dependents as $level_two_dependent) {
                                if ($level_two_dependent['parent']) {
                                if ($mysql->build_array('SELECT * FROM links WHERE category = '.$level_two_dependent['linkID'])) {
                                    if ($mysql->num_rows) {
                                        $level_three_dependents = $mysql->result;
                                        foreach($level_three_dependents as $level_three_dependent) {
                                            if ($level_three_dependent['parent']) {
                                            if ($mysql->build_array('SELECT * FROM links WHERE category = '.$level_three_dependent['linkID'])) {
                                                if ($mysql->num_rows) {
                                                    $level_four_dependents = $mysql->result;
                                                    foreach($level_four_dependents as $level_four_dependent) {
                                                        if ($level_four_dependent['parent']) {
                                                        if ($mysql->build_array('SELECT * FROM links WHERE category = '.$level_four_dependent['linkID'])) {
                                                            if ($mysql->num_rows) {
                                                                $level_five_dependents = $mysql->result;
                                                                foreach($level_five_dependents as $level_five_dependent) {
                                                                    $this->dependents[$y]['linkID'] = $level_five_dependent['linkID'];
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
                                                        $this->dependents[$y]['linkID'] = $level_four_dependent['linkID'];
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
                                            $this->dependents[$y]['linkID'] = $level_three_dependent['linkID'];
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
                                $this->dependents[$y]['linkID'] = $level_two_dependent['linkID'];
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
                    $this->dependents[$y]['linkID'] = $level_one_dependent['linkID'];
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
        if (!$mysql->query('DELETE FROM links WHERE linkID = '.$id.' LIMIT 1')) {
            $this->error .= $mysql->error;
        }
        if (is_array($this->dependents)) {
            foreach ($this->dependents as $dependent) {
                if (!$mysql->query('DELETE FROM links WHERE linkID = '.$dependent['linkID'].' LIMIT 1')) {
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
        if ($mysql->build_array('SELECT * FROM links WHERE parent = 1 ORDER BY title')) $this->parents = $mysql->result;

    }
    function move_link($id_to_move,$id_to_replace) {
        $mysql = new mysql;
        $current_pos = 0;
        $new_pos = 0;
        if ($mysql->result('SELECT position FROM links WHERE linkID = '.$id_to_move.' LIMIT 1')) {
            $current_pos = $mysql->result['position'];
        }
        if ($mysql->result('SELECT position FROM links WHERE linkID = '.$id_to_replace.' LIMIT 1')) {
            $new_pos = $mysql->result['position'];
        }
        if (!$current_pos or !$new_pos) {
            $this->error = $mysql->error;
            return false;
        }
        if (!$mysql->query('UPDATE links SET position = '.$new_pos.' WHERE linkID = '.$id_to_move.' LIMIT 1')) {
            return false;
        }
        if (!$mysql->query('UPDATE links SET position = '.$current_pos.' WHERE linkID = '.$id_to_replace.' LIMIT 1')) {
            return false;
        }
        return true;
    }
    function build_link($id) {
        $mysql = new mysql;
        if ($mysql->result('SELECT * FROM links WHERE linkID = '.$id.' LIMIT 1')) {
            $this->id =             $id;
            $this->member_id =      $mysql->result['accountID'];
            $this->category =       $mysql->result['category'];
            $this->title =          $mysql->result['title'];
            $this->url =            $mysql->result['url'];
            $this->description =    $mysql->result['description'];
            $this->parent =         $mysql->result['parent'];
            $this->day =            $mysql->result['day'];
            $this->month =          $mysql->result['month'];
            $this->year =           $mysql->result['year'];
            $this->validated =      $mysql->result['validated'];
            // don't want zeros appearing in form
            if (!$this->title) $this->title = '';
            if (!$this->url) $this->url = '';
            if (!$this->description) $this->description = '';
            return true;
        }
        return false;
    }

    function validate_form() {
        $this->submit_attempt = true;
        $this->error = '';
        $post_post = remove_slashes($_POST);
        if (isset($post_post['link_parent'])) {
            $this->parent = 1;
        } else {
            $this->parent = 0;
        }
        if (isset($post_post['link_url'])) $url = $post_post['link_url']; else $url = '';
        if (isset($post_post['link_title'])) $title = $post_post['link_title']; else $title = '';
        if (isset($post_post['link_description'])) $description = $post_post['link_description']; else $description = '';
        if (!$url and !$this->parent and $this->require_link_url) {
            $this->error .= 'URL required<br />';
        } else {
            $url = strtolower($url);
            if (!strpos(' '.$url,'http://')) {
                $this->url = 'http://'.$url;
            } else {
                $this->url = $url;
            }
            if ((addslashes($this->url) != $this->url)) {
                $this->error = 'URL contains disallowed characters';
                return false;
            }
        }
        if (!$title and $this->require_link_title) {
            $this->error .= 'Title required<br />';
        } else {
            if (VALIDATE_XHTML) {
                $xhtml_report = valid_XHTML($title);
                if ($xhtml_report) {
                    $this->error .= $xhtml_report;
                }
            }
            $this->title = remove_bad_tags($title);
        }
        if (!$description and !$this->parent and $this->require_link_description) {
            $this->error .= 'Description required<br />';
        } else {
            if (VALIDATE_XHTML) {
                $xhtml_report = valid_XHTML($description);
                if ($xhtml_report) {
                    $this->error .= $xhtml_report;
                }
            }
            $this->description = remove_bad_tags($description);
        }

        if (isset($post_post['link_category'])) {
            $this->category = $post_post['link_category'];
        } else {
            $this->category = 0;
        }

        if ($this->error) {
            return false;
        } else {
            return true;
        }
    }
    function link_level($id,$category,$parent) {
        if (!$category) return 0;
        $mysql = new mysql;
        if ($parent) {
            $s = 1;
        } else {
            $s = 0;
        }
        for($x=$s;$x<5;$x++) {
            if($mysql->result('SELECT linkID,category FROM links WHERE linkID = '.$category.' AND parent = 1 LIMIT 1')) {
                $category = $mysql->result['category'];
                if (!$category) {
                    return $x;
                }
            }
        }
        return $x - 1;

    }

    function description_javascript() {
        $z = 'function disable_description() {'."\n";
        $z .= "\t".'if (document.link.link_parent.checked == true) {'."\n";
        $z .= "\t\t".'document.link.link_description.disabled = true;'."\n";
        $z .= "\t\t".'document.link.link_url.disabled = true;'."\n";
        $z .= "\t"."} else {\n";
        $z .= "\t\t".'document.link.link_description.disabled = false;'."\n";
        $z .= "\t\t".'document.link.link_url.disabled = false;'."\n";
        $z .= "\t"."}\n";
        $z .= "}\n";
        $z .= "disable_description();\n";
        return $z;
    }

    function form_html($i,$type,$action_page) {
        $this->get_parents();
        $z = $i.'<!-- link_form -->'."\n";
        $z .= $i.'<div class="link_form">'."\n";
        $level = 0;
        if ($type == 'add') {
            $z .= $i."<span class=\"add_link_title\">Add ".a(LINKS_NAME_SINGULAR)." ".ucwords(LINKS_NAME_SINGULAR)."</span><br /><br />\n";
        } else {
            $level = $this->link_level($this->id,$this->category,$this->parent);
            $z .= $i."<span class=\"add_link_title\">Edit ".ucwords(LINKS_NAME_SINGULAR)." #".$this->id." or <a href=\"".URL.$action_page.append_url(0)."\">Add a New ".ucwords(LINKS_NAME_SINGULAR)."</a></span><br /><br />\n";
        }
        $z .= $i."Required fields are <span class=\"required_field\">".REQUIRED_DISPLAY."</span>.<br /><br />\n";
        $z .= $i."<fieldset><br />\n";
        $z .= $i.' <form name="link" method="post" action="'.URL.$action_page;
        if ($this->id and $type == 'edit') {
            $z .= $this->id.'/';
        }
        $z .= append_url().'">'."\n";

        $z .= $i.'  <label for="link_title">'.$this->check_required($this->require_link_title,1).'Title:'.$this->check_required($this->require_link_title,2)."</label>\n";
        $z .= $i." <input type=\"text\" id=\"link_title\" name=\"link_title\" maxlength=\"255\" value=\"".htmlspecialchars($this->title)."\" /><br class=\"left\" />\n";
        $z .= $i.'  <label for="link_description">'.$this->check_required($this->require_link_description,1).'Description:'.$this->check_required($this->require_link_description,2)."</label>\n";
        $z .= $i.'  <textarea id="link_description" name="link_description">'.htmlspecialchars($this->description)."</textarea><br /><br />\n";


        $z .= $i.' <label for="link_url">'.$this->check_required($this->require_link_url,1).'URL:'.$this->check_required($this->require_link_url,2).'</span></label>'."\n";
        $z .= $i." <input type=\"text\" id=\"link_url\" name=\"link_url\" maxlength=\"255\" value=\"".$this->url."\" /><br class=\"left\" />\n";

        if (is_array($this->parents)) {
            $z .= $i.'  <label for="link_category"><span class="required_field">'.ucwords(LINKS_NAME_SINGULAR).' Category:</span></label>'."\n";
            $z .= $i."  <select id=\"link_category\" name=\"link_category\" onClick=\"check_category();\">\n";
            if ($this->category) {
                $z .= $i."   <option value=\"0\">None</option>\n";
            } else {
                $z .= $i."   <option value=\"0\" selected=\"selected\">None</option>\n";
            }
            foreach ($this->categories as $parent) {
                if ($this->id != $parent['linkID']) {
                    $z .= $i."   <option value=\"".$parent['linkID']."\"".selected('','',$parent['linkID'],$this->category).">";
                    for ($j=0;$j<$parent['level'];$j++) {
                        $z .= '&nbsp;';
                    }
                    $z .= ucwords($parent['title'])."</option>\n";
                }
            }
            $z .= $i."  </select><br class=\"left\" />\n";
        } else {
            $z .= $i.' There are no categories to this section. Add one by checking "Make this a category".'."<br /><br />\n";
        }
        $z .= "$i <label for=\"link_parent\">Make this a category</label>\n";
        $z .= "$i <input type=\"checkbox\" id=\"link_parent\" name=\"link_parent\" value=\"1\"".$this->add_checkbox($type,0,$this->parent,0)." onClick=\"disable_description();\"/><br class=\"left\" /><br class=\"left\" />\n";

        if ($type == 'add') {
            $z .= $i.'  <input class="link_button" type="submit" name="submit" value="Add '.ucwords(LINKS_NAME_SINGULAR).'" />'."\n";
        } else {
            $z .= $i.'  <input class="link_button" type="submit" name="submit" value="Edit '.ucwords(LINKS_NAME_SINGULAR).'" /><br class=\"left\" />'."\n";
            if (user_type() == 2 and !$this->validated) {
                $z .= $i.'  <input class="link_button" type="submit" name="submit" value="Validate '.ucwords(LINKS_NAME_SINGULAR).'" />'."\n";
            }
            $z .= $i.'  <input class="link_button" type="submit" name="submit" value="Delete '.ucwords(LINKS_NAME_SINGULAR).'" />'."\n";
        }
        $z .= $i.' </form>'."\n";
        $z .= $i."</fieldset>\n";
        $z .= $i.'</div>'."\n";
        $z .= $i.'<!-- /link_form -->'."\n";
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

        $insert[11]['name'] = 'title';
        $insert[11]['value'] = mysql_real_escape_string($this->title);
        $insert[12]['name'] = 'description';
        $insert[12]['value'] = mysql_real_escape_string($this->description);

        $insert[13]['name'] = 'parent';
        $insert[13]['value'] = $this->parent;

        $insert[14]['name'] = 'category';
        $insert[14]['value'] = mysql_real_escape_string($this->category);

        if (VALIDATE_LINKS and user_type() !=2) {
            $insert[15]['name'] = 'validated';
            $insert[15]['value'] = '0';
        } else {
            $insert[15]['name'] = 'validated';
            $insert[15]['value'] = '1';
        }

        $insert[16]['name'] = 'position';
        $insert[16]['value'] = $this->next_position;
        $insert[17]['name'] = 'url';
        $insert[17]['value'] = mysql_real_escape_string($this->url);

        if (!$mysql->insert_values('links',$insert)) {
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
            $query = "UPDATE links SET parent = ".mysql_real_escape_string($this->parent).", url = '".mysql_real_escape_string($this->url)."', title = '".mysql_real_escape_string($this->title)."', description = '".mysql_real_escape_string($this->description)."', category = '".mysql_real_escape_string($this->category)."' WHERE linkID = ".$this->id." LIMIT 1";
            if ($mysql->query($query)) {
                return true;
            }
        }
        return false;
    }
    function delete_event($id) {
        $mysql = new mysql;
        if (!$id) return false;
        $query = "DELETE FROM links WHERE linkID = ".$id." LIMIT 1";
        if ($mysql->query($query)) {
            return true;
        }
        return false;
    }

    function up_down_link($id,$position,$category,$parent,$url) {
        $mysql = new mysql;

        $down_id = 0;
        if ($mysql->result('SELECT linkID FROM links WHERE position > '.$position.' AND category = '.$category.' AND parent = '.$parent.' ORDER BY position ASC LIMIT 1')) {
            $down_id = $mysql->result['linkID'];
        }
        $up_id = 0;
        if ($mysql->result('SELECT linkID FROM links WHERE position < '.$position.' AND category = '.$category.' AND parent = '.$parent.' ORDER BY position DESC LIMIT 1')) {
            $up_id = $mysql->result['linkID'];
        }
        if (!$down_id and !$up_id) {
            return '';
        }

        if ($down_id and $up_id) {
            return ' (<span class="up_down">Move <a href="'.URL.$url.'?link_id='.$id.'&replace_id='.$up_id.append_url(' ?').'">Up</a> or <a href="'.URL.$url.'?link_id='.$id.'&replace_id='.$down_id.append_url(' ?').'">Down</a>)</span>';
        }
        if ($down_id) {
            return ' (<span class="up_down">Move <a href="'.URL.$url.'?link_id='.$id.'&replace_id='.$down_id.append_url(' ?').'">Down</a>)</span>';
        }
        if ($up_id) {
            return ' (<span class="up_down">Move <a href="'.URL.$url.'?link_id='.$id.'&replace_id='.$up_id.append_url(' ?').'">Up</a>)</span>';
        }
    }

    function build_link_array($i,$url,$index_only,$unvalidated = 0) {
        $mysql = new mysql;
        global $user;
        global $links;
        // ***************************************************
        // build query
        // first have to find number of rows in table
        // this will determine theoretical maximun iterations
        // and we exit the function if there are none
        // ***************************************************
        if ($mysql->num_rows('SELECT * FROM links')) {
            if (!$mysql->num_rows) {
                return false;
            }
        } else {
            // document database error
            $this->error = $mysql->error;
            return false;
        }

        $validation_query = '';
        $query = 'SELECT * FROM links WHERE category = 0';
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
            $root_links = $mysql->result;


            $javascript = 'function check_category() {'."\n";

            $categories = array();
            $this->link_array = array();
            $f = 0;
            $g = 1;
            foreach ($root_links as $root_link) {
                // if an entry is a regular link we just print it otherwise we have to cycle through
                if ($root_link['parent']) {
                    $categories[$g]['linkID'] = $root_link['linkID'];
                    $categories[$g]['title'] = $root_link['title'];
                    $categories[$g]['level'] = 0;
                    $javascript .= "\t".'if (document.link.link_category.selectedIndex == '.$g.')'."\n";
                    $javascript .= "\t\t".'document.link.link_parent.disabled = false;'."\n";
                    $this->link_array[$f]['linkID'] = $root_link['linkID'];
                    $this->link_array[$f]['parent'] = $root_link['parent'];
                    $this->link_array[$f]['accountID'] = $root_link['accountID'];
                    $this->link_array[$f]['title'] = $root_link['title'];
                    $this->link_array[$f]['description'] = $root_link['description'];
                    $this->link_array[$f]['url'] = $root_link['url'];
                    $this->link_array[$f]['day'] = $root_link['day'];
                    $this->link_array[$f]['month'] = $root_link['month'];
                    $this->link_array[$f]['year'] = $root_link['year'];
                    $this->link_array[$f]['linkID'] = $root_link['linkID'];
                    $this->link_array[$f]['position'] = $root_link['position'];
                    $this->link_array[$f]['category'] = $root_link['category'];
                    $this->link_array[$f]['validated'] = $root_link['validated'];
                    $this->link_array[$f]['level'] = 0;
                    $f++;
                    $g++;
                    if ($mysql->build_array('SELECT * FROM links WHERE category = '.$root_link['linkID'].$validation_query.' ORDER BY parent ASC, position ASC')) {
                        if ($mysql->num_rows) {
                            $level_one_links = $mysql->result;
                            foreach ($level_one_links as $level_one_link) {
                                if ($level_one_link['parent']) {
                                    $categories[$g]['linkID'] = $level_one_link['linkID'];
                                    $categories[$g]['title'] = $level_one_link['title'];
                                    $categories[$g]['level'] = 1;
                                    $javascript .= "\t".'if (document.link.link_category.selectedIndex == '.$g.')'."\n";
                                    $javascript .= "\t\t".'document.link.link_parent.disabled = false;'."\n";
                                    $this->link_array[$f]['linkID'] = $level_one_link['linkID'];
                                    $this->link_array[$f]['parent'] = $level_one_link['parent'];
                                    $this->link_array[$f]['accountID'] = $level_one_link['accountID'];
                                    $this->link_array[$f]['title'] = $level_one_link['title'];
                                    $this->link_array[$f]['description'] = $level_one_link['description'];
                                    $this->link_array[$f]['url'] = $level_one_link['url'];
                                    $this->link_array[$f]['day'] = $level_one_link['day'];
                                    $this->link_array[$f]['month'] = $level_one_link['month'];
                                    $this->link_array[$f]['year'] = $level_one_link['year'];
                                    $this->link_array[$f]['linkID'] = $level_one_link['linkID'];
                                    $this->link_array[$f]['position'] = $level_one_link['position'];
                                    $this->link_array[$f]['category'] = $level_one_link['category'];
                                    $this->link_array[$f]['validated'] = $level_one_link['validated'];
                                    $this->link_array[$f]['level'] = 1;
                                    $f++;
                                    $g++;
                                    if ($mysql->build_array('SELECT * FROM links WHERE category = '.$level_one_link['linkID'].$validation_query.' ORDER BY parent ASC, position ASC')) {
                                        if ($mysql->num_rows) {
                                            $level_two_links = $mysql->result;
                                            foreach ($level_two_links as $level_two_link) {
                                                if ($level_two_link['parent']) {
                                                    $categories[$g]['linkID'] = $level_two_link['linkID'];
                                                    $categories[$g]['title'] = $level_two_link['title'];
                                                    $categories[$g]['level'] = 2;
                                                    $javascript .= "\t".'if (document.link.link_category.selectedIndex == '.$g.')'."\n";
                                                    $javascript .= "\t\t".'document.link.link_parent.disabled = false;'."\n";
                                                    $this->link_array[$f]['linkID'] = $level_two_link['linkID'];
                                                    $this->link_array[$f]['parent'] = $level_two_link['parent'];
                                                    $this->link_array[$f]['accountID'] = $level_two_link['accountID'];
                                                    $this->link_array[$f]['title'] = $level_two_link['title'];
                                                    $this->link_array[$f]['description'] = $level_two_link['description'];
                                                    $this->link_array[$f]['url'] = $level_two_link['url'];
                                                    $this->link_array[$f]['day'] = $level_two_link['day'];
                                                    $this->link_array[$f]['month'] = $level_two_link['month'];
                                                    $this->link_array[$f]['year'] = $level_two_link['year'];
                                                    $this->link_array[$f]['linkID'] = $level_two_link['linkID'];
                                                    $this->link_array[$f]['position'] = $level_two_link['position'];
                                                    $this->link_array[$f]['category'] = $level_two_link['category'];
                                                    $this->link_array[$f]['validated'] = $level_two_link['validated'];
                                                    $this->link_array[$f]['level'] = 2;
                                                    $f++;
                                                    $g++;
                                                    if ($mysql->build_array('SELECT * FROM links WHERE category = '.$level_two_link['linkID'].$validation_query.' ORDER BY parent ASC, position ASC')) {
                                                        if ($mysql->num_rows) {
                                                            $level_three_links = $mysql->result;
                                                            foreach ($level_three_links as $level_three_link) {
                                                                if ($level_three_link['parent']) {
                                                                    $categories[$g]['linkID'] = $level_three_link['linkID'];
                                                                    $categories[$g]['title'] = $level_three_link['title'];
                                                                    $categories[$g]['level'] = 3;
                                                                    $javascript .= "\t".'if (document.link.link_category.selectedIndex == '.$g.')'."\n";
                                                                    $javascript .= "\t\t".'document.link.link_parent.disabled = false;'."\n";
                                                                    $this->link_array[$f]['linkID'] = $level_three_link['linkID'];
                                                                    $this->link_array[$f]['parent'] = $level_three_link['parent'];
                                                                    $this->link_array[$f]['accountID'] = $level_three_link['accountID'];
                                                                    $this->link_array[$f]['title'] = $level_three_link['title'];
                                                                    $this->link_array[$f]['description'] = $level_three_link['description'];
                                                                    $this->link_array[$f]['url'] = $level_three_link['url'];
                                                                    $this->link_array[$f]['day'] = $level_three_link['day'];
                                                                    $this->link_array[$f]['month'] = $level_three_link['month'];
                                                                    $this->link_array[$f]['year'] = $level_three_link['year'];
                                                                    $this->link_array[$f]['linkID'] = $level_three_link['linkID'];
                                                                    $this->link_array[$f]['position'] = $level_three_link['position'];
                                                                    $this->link_array[$f]['category'] = $level_three_link['category'];
                                                                    $this->link_array[$f]['validated'] = $level_three_link['validated'];
                                                                    $this->link_array[$f]['level'] = 3;
                                                                    $f++;
                                                                    $g++;
                                                                    if ($mysql->build_array('SELECT * FROM links WHERE category = '.$level_three_link['linkID'].$validation_query.' ORDER BY parent ASC, position ASC')) {
                                                                        if ($mysql->num_rows) {
                                                                            $level_four_links = $mysql->result;
                                                                            foreach ($level_four_links as $level_four_link) {
                                                                                if ($level_four_link['parent']) {
                                                                                    $categories[$g]['linkID'] = $level_four_link['linkID'];
                                                                                    $categories[$g]['title'] = $level_four_link['title'];
                                                                                    $categories[$g]['level'] = 4;
                                                                                    $javascript .= "\t".'if (document.link.link_category.selectedIndex == '.$g.') {'."\n";
                                                                                    $javascript .= "\t\t".'document.link.link_parent.disabled = true;'."\n";
                                                                                    $javascript .= "\t\t".'document.link.link_description.disabled = false;'."\n";
                                                                                    $javascript .= "\t".'}'."\n";
                                                                                    $this->link_array[$f]['linkID'] = $level_four_link['linkID'];
                                                                                    $this->link_array[$f]['parent'] = $level_four_link['parent'];
                                                                                    $this->link_array[$f]['accountID'] = $level_four_link['accountID'];
                                                                                    $this->link_array[$f]['title'] = $level_four_link['title'];
                                                                                    $this->link_array[$f]['description'] = $level_four_link['description'];
                                                                                    $this->link_array[$f]['url'] = $level_four_link['url'];
                                                                                    $this->link_array[$f]['day'] = $level_four_link['day'];
                                                                                    $this->link_array[$f]['month'] = $level_four_link['month'];
                                                                                    $this->link_array[$f]['year'] = $level_four_link['year'];
                                                                                    $this->link_array[$f]['linkID'] = $level_four_link['linkID'];
                                                                                    $this->link_array[$f]['position'] = $level_four_link['position'];
                                                                                    $this->link_array[$f]['category'] = $level_four_link['category'];
                                                                                    $this->link_array[$f]['validated'] = $level_four_link['validated'];
                                                                                    $this->link_array[$f]['level'] = 4;
                                                                                    $f++;
                                                                                    $g++;
                                                                                    if ($mysql->build_array('SELECT * FROM links WHERE category = '.$level_four_link['linkID'].$validation_query.' AND parent = 0 ORDER BY  position ASC')) {
                                                                                        if ($mysql->num_rows) {
                                                                                            $level_five_links = $mysql->result;
                                                                                            foreach ($level_five_links as $level_four_link) {
                                                                                                $this->link_array[$f]['linkID'] = $level_four_link['linkID'];
                                                                                                $this->link_array[$f]['parent'] = 0;
                                                                                                $this->link_array[$f]['accountID'] = $level_four_link['accountID'];
                                                                                                $this->link_array[$f]['title'] = $level_four_link['title'];
                                                                                                $this->link_array[$f]['description'] = $level_four_link['description'];
                                                                                                $this->link_array[$f]['url'] = $level_four_link['url'];
                                                                                                $this->link_array[$f]['day'] = $level_four_link['day'];
                                                                                                $this->link_array[$f]['month'] = $level_four_link['month'];
                                                                                                $this->link_array[$f]['year'] = $level_four_link['year'];
                                                                                                $this->link_array[$f]['linkID'] = $level_four_link['linkID'];
                                                                                                $this->link_array[$f]['position'] = $level_four_link['position'];
                                                                                                $this->link_array[$f]['category'] = $level_four_link['category'];
                                                                                                $this->link_array[$f]['validated'] = $level_four_link['validated'];
                                                                                                $this->link_array[$f]['level'] = 4;
                                                                                                $f++;
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                } else {
                                                                                    $this->link_array[$f]['linkID'] = $level_four_link['linkID'];
                                                                                    $this->link_array[$f]['parent'] = $level_four_link['parent'];
                                                                                    $this->link_array[$f]['accountID'] = $level_four_link['accountID'];
                                                                                    $this->link_array[$f]['title'] = $level_four_link['title'];
                                                                                    $this->link_array[$f]['description'] = $level_four_link['description'];
                                                                                    $this->link_array[$f]['url'] = $level_four_link['url'];
                                                                                    $this->link_array[$f]['day'] = $level_four_link['day'];
                                                                                    $this->link_array[$f]['month'] = $level_four_link['month'];
                                                                                    $this->link_array[$f]['year'] = $level_four_link['year'];
                                                                                    $this->link_array[$f]['linkID'] = $level_four_link['linkID'];
                                                                                    $this->link_array[$f]['position'] = $level_four_link['position'];
                                                                                    $this->link_array[$f]['category'] = $level_four_link['category'];
                                                                                    $this->link_array[$f]['validated'] = $level_four_link['validated'];
                                                                                    $this->link_array[$f]['level'] = 4;
                                                                                    $f++;
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                } else {
                                                                    $this->link_array[$f]['linkID'] = $level_three_link['linkID'];
                                                                    $this->link_array[$f]['parent'] = $level_three_link['parent'];
                                                                    $this->link_array[$f]['accountID'] = $level_three_link['accountID'];
                                                                    $this->link_array[$f]['title'] = $level_three_link['title'];
                                                                    $this->link_array[$f]['description'] = $level_three_link['description'];
                                                                    $this->link_array[$f]['url'] = $level_three_link['url'];
                                                                    $this->link_array[$f]['day'] = $level_three_link['day'];
                                                                    $this->link_array[$f]['month'] = $level_three_link['month'];
                                                                    $this->link_array[$f]['year'] = $level_three_link['year'];
                                                                    $this->link_array[$f]['linkID'] = $level_three_link['linkID'];
                                                                    $this->link_array[$f]['position'] = $level_three_link['position'];
                                                                    $this->link_array[$f]['category'] = $level_three_link['category'];
                                                                    $this->link_array[$f]['validated'] = $level_three_link['validated'];
                                                                    $this->link_array[$f]['level'] = 3;
                                                                    $f++;
                                                                }
                                                            }
                                                        }
                                                    }
                                                } else {
                                                    $this->link_array[$f]['linkID'] = $level_two_link['linkID'];
                                                    $this->link_array[$f]['parent'] = $level_two_link['parent'];
                                                    $this->link_array[$f]['accountID'] = $level_two_link['accountID'];
                                                    $this->link_array[$f]['title'] = $level_two_link['title'];
                                                    $this->link_array[$f]['description'] = $level_two_link['description'];
                                                    $this->link_array[$f]['url'] = $level_two_link['url'];
                                                    $this->link_array[$f]['day'] = $level_two_link['day'];
                                                    $this->link_array[$f]['month'] = $level_two_link['month'];
                                                    $this->link_array[$f]['year'] = $level_two_link['year'];
                                                    $this->link_array[$f]['linkID'] = $level_two_link['linkID'];
                                                    $this->link_array[$f]['position'] = $level_two_link['position'];
                                                    $this->link_array[$f]['category'] = $level_two_link['category'];
                                                    $this->link_array[$f]['validated'] = $level_two_link['validated'];
                                                    $this->link_array[$f]['level'] = 2;
                                                    $f++;
                                                }
                                            }
                                        }
                                    }
                                } else {
                                    $this->link_array[$f]['linkID'] = $level_one_link['linkID'];
                                    $this->link_array[$f]['parent'] = $level_one_link['parent'];
                                    $this->link_array[$f]['accountID'] = $level_one_link['accountID'];
                                    $this->link_array[$f]['title'] = $level_one_link['title'];
                                    $this->link_array[$f]['description'] = $level_one_link['description'];
                                    $this->link_array[$f]['url'] = $level_one_link['url'];
                                    $this->link_array[$f]['day'] = $level_one_link['day'];
                                    $this->link_array[$f]['month'] = $level_one_link['month'];
                                    $this->link_array[$f]['year'] = $level_one_link['year'];
                                    $this->link_array[$f]['linkID'] = $level_one_link['linkID'];
                                    $this->link_array[$f]['position'] = $level_one_link['position'];
                                    $this->link_array[$f]['category'] = $level_one_link['category'];
                                    $this->link_array[$f]['validated'] = $level_one_link['validated'];
                                    $this->link_array[$f]['level'] = 1;
                                    $f++;
                                }
                            }
                        }
                    }
                } else {
                    $this->link_array[$f]['linkID'] = $root_link['linkID'];
                    $this->link_array[$f]['parent'] = $root_link['parent'];
                    $this->link_array[$f]['accountID'] = $root_link['accountID'];
                    $this->link_array[$f]['title'] = $root_link['title'];
                    $this->link_array[$f]['description'] = $root_link['description'];
                    $this->link_array[$f]['url'] = $root_link['url'];
                    $this->link_array[$f]['day'] = $root_link['day'];
                    $this->link_array[$f]['month'] = $root_link['month'];
                    $this->link_array[$f]['year'] = $root_link['year'];
                    $this->link_array[$f]['linkID'] = $root_link['linkID'];
                    $this->link_array[$f]['position'] = $root_link['position'];
                    $this->link_array[$f]['category'] = $root_link['category'];
                    $this->link_array[$f]['validated'] = $root_link['validated'];
                    $this->link_array[$f]['level'] = 0;
                    $f++;
                }
            }
            $javascript .= "}\n";
            $this->categories = $categories;
            $this->category_javascript = $javascript;
        }
        return true;

    }

    function link_list($i,$url,$index_only,$unvalidated = 0) {
        $mysql = new mysql;
        global $user;
        global $links;
        global $style;
        if (!$this->build_link_array($i,$url,$index_only,$unvalidated)) {
            return '';
        }
        $num_to_validate = 0;
        if (VALIDATE_LINKS) {
            if ($mysql->num_rows('SELECT * FROM links WHERE validated = 0')) {
                $num_to_validate = $mysql->num_rows;
            }
        }
        $z = $i.'<!-- link_index -->'."\n";
        $z .= $i."<div id=\"link_index\">\n";
        if ($url and user_type() != 2) {
            $z .= $i." Your ".strtolower(LINKS_NAME_PLURAL)." appear with a hyperlink. Click their link to edit them.<br /><br />\n";
        } elseif ($url and user_type() == 2) {
            $z .= $i." Add or edit ".strtolower(LINKS_NAME_PLURAL)."<br /><br />\n";
            if ($num_to_validate) {
                $z .= $i.' '.$num_to_validate." ".strtolower(LINKS_NAME_PLURAL)." need to be validated. They are marked with an <span class=\"unvalidated_link\">*</span><br /><br />\n";
            }
        }
        $num_links = count($this->link_array);
        for($f=0;$f<$num_links;$f++) {
            $indent = '';
            if ($this->link_array[$f]['level']) {
                for ($q=0;$q<$this->link_array[$f]['level'];$q++) {
                    $indent .= '<span class="indent">&nbsp;</span>&nbsp;';
                }
            }

            if ($this->link_array[$f]['parent']) {
                if (!$url) {
                    $z .= $i.' '.$indent.'<span class="link_index_parent_title"><a href="#'.$this->link_array[$f]['linkID'].'">'.$this->link_array[$f]['title']."</a></span><br />\n";
                } else {
                    if ($_SESSION['member_id'] == $this->link_array[$f]['accountID'] or user_type() == 2) {
                        $z .= $i.' '.$indent.'<span class="link_index_parent_title"><a href="'.URL.$url.$this->link_array[$f]['linkID'].'/'.append_url(0).'">'.$this->link_array[$f]['title'].'</a></span>';
                    } else {
                        $z .= $i.' '.$indent.'<span class="link_index_parent_title">'.$this->link_array[$f]['title'].'</span>';
                    }
                    if (!RESTRICT_UPDOWN_LINKS or user_type() == 2) {
                        $z .= $this->up_down_link($this->link_array[$f]['linkID'],$this->link_array[$f]['position'],$this->link_array[$f]['category'],$this->link_array[$f]['parent'],$url);
                    }
                    if ($url and user_type() == 2 and !$this->link_array[$f]['validated']) {
                        $z .= '<span class="unvalidated_link">*</span>';
                    }
                    $z .= "<br />\n";
                }
            } else {
                $indent .= '&nbsp;';
                if (!$url) {
                    $z .= $i.' '.$indent.'<span class="link_index_link"><a href="#'.$this->link_array[$f]['linkID'].'">'.$this->link_array[$f]['title']."</a></span><br />\n";
                } else {
                    if ($_SESSION['member_id'] == $this->link_array[$f]['accountID'] or user_type() == 2) {
                        $z .= $i.' '.$indent.'<span class="link_index_link"><a href="'.URL.$url.$this->link_array[$f]['linkID'].'/'.append_url(0).'">'.$this->link_array[$f]['title'].'</a></span>';
                    } else {
                        $z .= $i.' '.$indent.'<span class="link_index_link">'.$this->link_array[$f]['title'].'</span>';
                    }
                    if (!RESTRICT_UPDOWN_LINKS or user_type() == 2) {
                        $z .= $this->up_down_link($this->link_array[$f]['linkID'],$this->link_array[$f]['position'],$this->link_array[$f]['category'],$this->link_array[$f]['parent'],$url);
                    }
                    if ($url and user_type() == 2 and !$this->link_array[$f]['validated']) {
                        $z .= '<span class="unvalidated_link">*</span>';
                    }
                    $z .= "<br />\n";
                }
            }
        }
        $z .= $i."</div><br /><br />\n";
        $z .= $i.'<!-- /link_index -->'."\n";
        if ($index_only) {
            return $z;
        }
        $z .= $i.'<!-- link_list -->'."\n";
        $z .= $i."<div id=\"link_list\">\n";
        for($f=0;$f<$num_links;$f++) {
            $indent = '';
            if ($this->link_array[$f]['level']) {
                for ($q=0;$q<$this->link_array[$f]['level'];$q++) {
                    $indent .= '&nbsp;&nbsp;';
                }
            }
            if ($this->link_array[$f]['parent']) {
                $z .= $i.' '.$indent.'<span class="link_category"><a name="'.$this->link_array[$f]['linkID'].'">'.$this->link_array[$f]['title']."</a></span><br />\n";
            } else {
                $indent .= '&nbsp;';
                $z .= $i.' '.$indent.'<span class="link_title"><a name="'.$this->link_array[$f]['linkID'].'">'.$this->link_array[$f]['title']."</a></span><br />\n";
                if (SHOW_LINK_DETAILS) {
                    $z .= $i.' '.$indent."<span class=\"link_details\">Posted ".return_month($this->link_array[$f]['month']).' '.$this->link_array[$f]['day'].', '.$this->link_array[$f]['year'].' by ';
                    if (user_type() == 0 or $_SESSION["member_validated"] == 0 or $_SESSION["member_suspended"] == 1) {
                        $z .= '<a href="'.URL.MEMBER_LIST_URL.'/'.$this->link_array[$f]['accountID'].'/">'.ucwords(MEMBERS_NAME_SINGULAR).' '.$this->link_array[$f]['accountID']."</a>";
                    } elseif (user_type() == 1 or user_type() == 2) {
                        $z .= '<a href="'.URL.MEMBER_LIST_URL.'/'.$this->link_array[$f]['accountID'].'/'.append_url(0).'">'.$user->full_name($this->link_array[$f]['accountID'])."</a>";
                    }
                    if (isset($_SESSION['member_id'])) {
                        if (($this->member_id == $_SESSION['member_id'] or user_type() == 2) and $links->build_url(1,10)) {
                            $z .= ' (<a href="'.URL.$links->complete_url.$this->link_array[$f]['linkID'].'/'.append_url(0).'">edit</a>)';
                        }
                    }
                    $z .= "</span><br /><br />\n";
                }
                if ($this->link_array[$f]['description']) {
                    $z .= $i.' <div class="link_description_'.$this->link_array[$f]['linkID'].'">'."\n".indent_variable($i.'  ',$this->link_array[$f]['description'])."\n".$i." </div>\n";
                }
                if ($this->link_array[$f]['url']) {
                    $z .= $i.' '.$indent.'<span class="link_url"><a href="'.$this->link_array[$f]['url'].'" target="_blank">'.$this->link_array[$f]['url']."</a></span><br />\n";
                }
                $z .=  $i." <br />\n";
                $style->dynamic_elements .= ' div.link_description_'.$this->link_array[$f]['linkID'].' {padding-left:'.(15 * $this->link_array[$f]['level'])."px; }\n";
            }
        }
        $z .= $i."</div>\n";
        $z .= $i.'<!-- /link_list -->'."\n";
        return $z;
    }
    function sidebar($i,$heading) {
        $mysql = new mysql;
        $mysql->build_array('SELECT * FROM links WHERE parent = 0 AND validated = 1 ORDER BY year DESC, month DESC, day DESC LIMIT 5');
        if (is_array($mysql->result)) {
            $z .= $i.'<div id="links_sidebar">'."\n";
            $z .= $i.'<span class="sidebar_heading">'.$heading."</span><br />\n";
            foreach ($mysql->result as $link) {
                $z .= $i.'<span class="sidebar_title"><a href="'.URL.LINKS_URL.'/#'.$link['linkID'].append_url(0).'">'.$link['title'].'</a> by '.first_name($link['accountID'])."</span><br />\n";
                $date_difference = date_difference(array('year' => $link['year'], 'month' => $link['month'], 'day' => $link['day']),array('year' => $GLOBALS['date']['year'], 'month' =>  $GLOBALS['date']['month'], 'day' =>  $GLOBALS['date']['day'], 'hour' => $GLOBALS['date']['hour']));
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
        $z .= $i."<!-- /links_sidebar -->\n";
        return $z;
    }
}
?>