<?
class events {

	var	$id,
		$member_id,
		$event_category,
		$title,
		$description,
		$location,
		$start_day,
		$start_month,
		$start_year,
		$start_hour,
		$start_minute,
		$end_day,
		$end_month,
		$end_year,
		$end_hour,
		$end_minute,
		$event_categories,
		$validated,
		$submit_attempt,
		$category_javascript,
		$error;

	function events() {
		$this->error = "No errors";
		$this->get_event_categories();
		$this->set_required_variables();
		$this->submit_attempt = false;
	}
	function set_required_variables() {
		$mysql = new mysql;
		$mysql->result('SELECT event_description_required, event_location_required FROM config');
		$this->event_description_required = $mysql->result['event_description_required'];
		$this->event_location_required = $mysql->result['event_location_required'];
	}
	function clear() {
		$this->title = '';
		$this->description = '';
		$this->location = '';
		$this->submit_attempt = false;
	}
	function validate($id) {
		$mysql = new mysql;
		if ($mysql->query('UPDATE events SET validated = 1 WHERE eventID = '.$id.' LIMIT 1')) {
			return true;
		} else {
			return false;
		}
	}
	function num_comments($id) {
		$mysql = new mysql;
		if ($mysql->num_rows('SELECT commentID FROM comments WHERE eventID = '.$id)) {
			return $mysql->num_rows;
		} else {
			return 0;
		}
	}
	function delete($id) {
		$mysql = new mysql;
		$this->error = '';
		if (!$mysql->query('DELETE FROM events WHERE eventID = '.$id.' LIMIT 1')) {
			$this->error = $mysql->error;
			return false;
		}
		if (!$mysql->query('DELETE FROM comments WHERE eventID = '.$id)) {
			$this->error = $mysql->error;
			return false;
		}
		return true;
		
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
	function get_event_categories() {
		$mysql = new mysql;
		if ($mysql->build_array('SELECT * FROM event_categories ORDER BY name ASC')) $this->event_categories = $mysql->result;
	}
	function event_category_name($id) {
		$mysql = new mysql;
		if ($mysql->result('SELECT name FROM event_categories WHERE event_categoryID = '.$id.' LIMIT 1')) return $mysql->result['name'];
	}
	function build_event($id) {
		$mysql = new mysql;
		if ($mysql->result('SELECT * FROM events WHERE eventID = '.$id.' LIMIT 1')) {
			$this->id = $id;
			$this->member_id 			= $mysql->result['accountID'];
			$this->event_category 		= $mysql->result['event_categoryID'];
			$this->title 				= $mysql->result['title'];
			$this->description 			= $mysql->result['description'];
			$this->location 			= $mysql->result['location'];
			$this->start_day 			= $mysql->result['start_day'];
			$this->start_month 			= $mysql->result['start_month'];
			$this->start_year 			= $mysql->result['start_year'];
			$this->start_hour 			= $mysql->result['start_hour'];
			$this->start_minute 		= $mysql->result['start_minute'];
			$this->end_day 				= $mysql->result['end_day'];
			$this->end_month 			= $mysql->result['end_month'];
			$this->end_year 			= $mysql->result['end_year'];
			$this->end_hour 			= $mysql->result['end_hour'];
			$this->end_minute 			= $mysql->result['end_minute'];
			$this->validated 			= $mysql->result['validated'];
			// don't want zeros appearing in form
			if (!$this->title) $this->title = '';
			if (!$this->description) $this->description = '';
			if (!$this->location) $this->location = '';
			return true;
		}
		return false;	
	}
	
	function validate_form() {
		$this->submit_attempt = true;
		$this->error = '';
		$post_post = remove_slashes($_POST);
		if (!isset($post_post['event_title'])) {
			$this->error .= 'Title required<br />';
		} else {
			if (VALIDATE_XHTML) {
				$xhtml_report = valid_XHTML($post_post['event_title']);
				if ($xhtml_report) {
					$this->error .= $xhtml_report;
				}
			}
			$this->title = remove_bad_tags($post_post['event_title']);
		}
		if (!isset($post_post['event_description']) and $this->event_description_required) {
			$this->error .= ucfirst(EVENTS_NAME_SINGULAR).' description required<br />';
		} else {
			if (!$post_post['event_description'] and $this->event_description_required) {
				$this->error .= ucfirst(EVENTS_NAME_SINGULAR).' description required<br />';
			}
			if (VALIDATE_XHTML) {
				$xhtml_report = valid_XHTML($post_post['event_description']);
				if ($xhtml_report) {
					$this->error .= $xhtml_report;
				}
			}
			$this->description = remove_bad_tags($post_post['event_description']);

		}
		if (!isset($post_post['event_location']) and $this->event_location_required) {
			$this->error .= ucfirst(EVENTS_NAME_SINGULAR).' location required<br />';
		} else {
			if (!$post_post['event_location'] and $this->event_location_required) {
				$this->error .= ucfirst(EVENTS_NAME_SINGULAR).' location required<br />';
			}
			if (VALIDATE_XHTML) {
				$xhtml_report = valid_XHTML($post_post['event_location']);
				if ($xhtml_report) {
					$this->error .= $xhtml_report;
				}
			}
			$this->location = remove_bad_tags($post_post['event_location']);
		}
		if (!isset($post_post['start_day']) or !isset($post_post['start_month']) or !isset($post_post['start_year']) or !isset($post_post['end_day']) or !isset($post_post['end_month']) or !isset($post_post['end_year'])) {
			$this->error .= 'Date invalid<br />';
		}
		if (!is_numeric($post_post['start_day']) or !is_numeric($post_post['start_month']) or !is_numeric($post_post['start_year']) or !is_numeric($post_post['end_day']) or !is_numeric($post_post['end_month']) or !is_numeric($post_post['end_year'])) {
			$this->error .= 'Date invalid<br />';
		} else {
			$this->start_day = $post_post['start_day'];
			$this->start_month = $post_post['start_month'];
			$this->start_year = $post_post['start_year'];
			$this->start_hour = $post_post['start_hour'];
			$this->start_minute = $post_post['start_minute'];
			$this->end_day = $post_post['end_day'];
			$this->end_month = $post_post['end_month'];
			$this->end_year = $post_post['end_year'];
			$this->end_hour = $post_post['end_hour'];
			$this->end_minute = $post_post['end_minute'];
		}
		
		if (!isset($post_post['event_category'])) {
			$this->error .= 'No '.strtolower(EVENTS_NAME_SINGULAR).' category<br />';
		} else {
			if (!is_numeric($post_post['event_category'])) {
				$this->error .= 'No '.strtolower(EVENTS_NAME_SINGULAR).' category<br />';
			}
			$this->event_category = $post_post['event_category'];
		}
		if ($this->error) {
			return false;
		} else {
			return true;
		}
	}

	function form_html($i,$type,$action_page) {
		if (!$this->start_hour) $this->start_hour = 15;
		if (!$this->end_hour) $this->end_hour = 18;
		
		$z = $i.'<!-- event_form -->'."\n";
		$z .= $i.'<div class="event_form">'."\n";
		if ($type == 'add') {
			$z .= $i."<span class=\"add_event_title\">Add ".a(EVENTS_NAME_SINGULAR)." ".ucwords(EVENTS_NAME_SINGULAR)."</span><br /><br />\n";
		} else {
			$z .= $i."<span class=\"add_event_title\">Edit ".ucwords(EVENTS_NAME_SINGULAR)." #".$this->id." or <a href=\"".URL.$action_page.append_url(0)."\">Add a new ".ucwords(EVENTS_NAME_SINGULAR)."</a></span><br /><br />\n";
		}
		$z .= $i."Required fields are <span class=\"required_field\">".REQUIRED_DISPLAY."</span>.<br /><br />\n";
		$z .= $i."<fieldset><br />\n";
		$z .= $i.' <form name="event" method="post" action="'.URL.$action_page;
		if ($this->id) {
			$z .= $this->id.'/';
		}
		$z .= append_url().'">'."\n";
		$z .= $i.'  <label for="event_title"><span class="required_field">Title:</span></label>'."\n";
		$z .= $i.'  <input type="text" id="event_title" name="event_title" maxlength="255" value="'.htmlspecialchars($this->title).'" /><br class="left" />'."\n";
		$z .= $i.'  <label for="event_description">'.$this->check_required($this->event_description_required,1).'Description:'.$this->check_required($this->event_description_required,2).'</label>'."\n";
		$z .= $i.'  <textarea id="event_description" name="event_description" cols=\"60\" rows=\"4\">'.htmlspecialchars($this->description)."</textarea><br /><br />\n";
		$z .= $i.'  <label for="start_month"><span class="required_field">Start Time:</span></label>'."\n";
		if ($this->submit_attempt) {
			$z .= date_form($i.'  ',array('year' => $this->start_year, 'month' => $this->start_month, 'day' => $this->start_day),'start_',false,'false',0);
		} else {
			if ($type == 'edit') {
				$z .= date_form($i.'  ',array('year' => $this->start_year, 'month' => $this->start_month, 'day' => $this->start_day),'start_',false,'false',0);
			} else {
				$z .= date_form($i.'  ',change_date('day','+',7),'start_',false,'false',0);
			}
		}
		$z .= $i."  <select id=\"start_hour\" name=\"start_hour\">\n";
		$z .= $i."   <option value=\"0\"".selected(' ','',0,$this->start_hour).">AM - 12</option>\n";
		$z .= $i."   <option value=\"1\"".selected(' ','',1,$this->start_hour).">AM - 1</option>\n";
		$z .= $i."   <option value=\"2\"".selected(' ','',2,$this->start_hour).">AM - 2</option>\n";
		$z .= $i."   <option value=\"3\"".selected(' ','',3,$this->start_hour).">AM - 3</option>\n";
		$z .= $i."   <option value=\"4\"".selected(' ','',4,$this->start_hour).">AM - 4</option>\n";
		$z .= $i."   <option value=\"5\"".selected(' ','',5,$this->start_hour).">AM - 5</option>\n";
		$z .= $i."   <option value=\"6\"".selected(' ','',6,$this->start_hour).">AM - 6</option>\n";
		$z .= $i."   <option value=\"7\"".selected(' ','',7,$this->start_hour).">AM - 7</option>\n";
		$z .= $i."   <option value=\"8\"".selected(' ','',8,$this->start_hour).">AM - 8</option>\n";
		$z .= $i."   <option value=\"9\"".selected(' ','',9,$this->start_hour).">AM - 9</option>\n";
		$z .= $i."   <option value=\"10\"".selected(' ','',10,$this->start_hour).">AM - 10</option>\n";
		$z .= $i."   <option value=\"11\"".selected(' ','',11,$this->start_hour).">AM - 11</option>\n";
		$z .= $i."   <option value=\"12\"".selected(' ','',12,$this->start_hour).">PM - 12</option>\n";
		$z .= $i."   <option value=\"13\"".selected(' ','',13,$this->start_hour).">PM - 1</option>\n";
		$z .= $i."   <option value=\"14\"".selected(' ','',14,$this->start_hour).">PM - 2</option>\n";
		$z .= $i."   <option value=\"15\"".selected(' ','',15,$this->start_hour).">PM - 3</option>\n";
		$z .= $i."   <option value=\"16\"".selected(' ','',16,$this->start_hour).">PM - 4</option>\n";
		$z .= $i."   <option value=\"17\"".selected(' ','',17,$this->start_hour).">PM - 5</option>\n";
		$z .= $i."   <option value=\"18\"".selected(' ','',18,$this->start_hour).">PM - 6</option>\n";
		$z .= $i."   <option value=\"19\"".selected(' ','',19,$this->start_hour).">PM - 7</option>\n";
		$z .= $i."   <option value=\"20\"".selected(' ','',20,$this->start_hour).">PM - 8</option>\n";
		$z .= $i."   <option value=\"21\"".selected(' ','',21,$this->start_hour).">PM - 9</option>\n";
		$z .= $i."   <option value=\"22\"".selected(' ','',22,$this->start_hour).">PM - 10</option>\n";
		$z .= $i."   <option value=\"23\"".selected(' ','',23,$this->start_hour).">PM - 11</option>\n";
		$z .= $i."  </select>:\n";
		$z .= $i."  <select id=\"start_minute\" name=\"start_minute\">\n";
		for($v=0;$v<61;$v++) {
			$z .= $i."   <option value=\"".$v."\"".selected(' ','',$v,$this->start_minute).">".return_minute($v)."</option>\n";
		}
		$z .= $i."  </select><br class=\"left\" />\n";
		
		$z .= $i.'  <label for="end_month"><span class="required_field">End Time:</span></label>'."\n";
		if ($this->submit_attempt) {
			$z .= date_form($i.'  ',array('year' => $this->end_year, 'month' => $this->end_month, 'day' => $this->end_day),'end_',false,'false',0);
		} else {
			if ($type == 'edit') {
				$z .= date_form($i.'  ',array('year' => $this->end_year, 'month' => $this->end_month, 'day' => $this->end_day),'end_',false,'false',0);
			} else {
				$z .= date_form($i.'  ',change_date('day','+',7),'end_',false,'false',0);
			}
		}
		$z .= $i."  <select id=\"end_hour\" name=\"end_hour\">\n";
		$z .= $i."   <option value=\"0\"".selected(' ','',0,$this->end_hour).">AM - 12</option>\n";
		$z .= $i."   <option value=\"1\"".selected(' ','',1,$this->end_hour).">AM - 1</option>\n";
		$z .= $i."   <option value=\"2\"".selected(' ','',2,$this->end_hour).">AM - 2</option>\n";
		$z .= $i."   <option value=\"3\"".selected(' ','',3,$this->end_hour).">AM - 3</option>\n";
		$z .= $i."   <option value=\"4\"".selected(' ','',4,$this->end_hour).">AM - 4</option>\n";
		$z .= $i."   <option value=\"5\"".selected(' ','',5,$this->end_hour).">AM - 5</option>\n";
		$z .= $i."   <option value=\"6\"".selected(' ','',6,$this->end_hour).">AM - 6</option>\n";
		$z .= $i."   <option value=\"7\"".selected(' ','',7,$this->end_hour).">AM - 7</option>\n";
		$z .= $i."   <option value=\"8\"".selected(' ','',8,$this->end_hour).">AM - 8</option>\n";
		$z .= $i."   <option value=\"9\"".selected(' ','',9,$this->end_hour).">AM - 9</option>\n";
		$z .= $i."   <option value=\"10\"".selected(' ','',10,$this->end_hour).">AM - 10</option>\n";
		$z .= $i."   <option value=\"11\"".selected(' ','',11,$this->end_hour).">AM - 11</option>\n";
		$z .= $i."   <option value=\"12\"".selected(' ','',12,$this->end_hour).">PM - 12</option>\n";
		$z .= $i."   <option value=\"13\"".selected(' ','',13,$this->end_hour).">PM - 1</option>\n";
		$z .= $i."   <option value=\"14\"".selected(' ','',14,$this->end_hour).">PM - 2</option>\n";
		$z .= $i."   <option value=\"15\"".selected(' ','',15,$this->end_hour).">PM - 3</option>\n";
		$z .= $i."   <option value=\"16\"".selected(' ','',16,$this->end_hour).">PM - 4</option>\n";
		$z .= $i."   <option value=\"17\"".selected(' ','',17,$this->end_hour).">PM - 5</option>\n";
		$z .= $i."   <option value=\"18\"".selected(' ','',18,$this->end_hour).">PM - 6</option>\n";
		$z .= $i."   <option value=\"19\"".selected(' ','',19,$this->end_hour).">PM - 7</option>\n";
		$z .= $i."   <option value=\"20\"".selected(' ','',20,$this->end_hour).">PM - 8</option>\n";
		$z .= $i."   <option value=\"21\"".selected(' ','',21,$this->end_hour).">PM - 9</option>\n";
		$z .= $i."   <option value=\"22\"".selected(' ','',22,$this->end_hour).">PM - 10</option>\n";
		$z .= $i."   <option value=\"23\"".selected(' ','',23,$this->end_hour).">PM - 11</option>\n";
		$z .= $i."  </select>:\n";
		$z .= $i."  <select id=\"end_minute\" name=\"end_minute\">\n";
		for($v=0;$v<61;$v++) {
			$z .= $i."   <option value=\"".$v."\"".selected(' ','',$v,$this->end_minute).">".return_minute($v)."</option>\n";
		}
		$z .= $i."  </select><br class=\"left\" />\n";
		$z .= $i.'  <label for="event_location">'.$this->check_required($this->event_location_required,1).'Location:'.$this->check_required($this->event_location_required,2).'</label>'."\n";
		$z .= $i.'  <input type="text" id="event_location" name="event_location" maxlength="255" value="'.htmlspecialchars($this->location).'" /><br class="left" />'."\n";
		$z .= $i.'  <label for="event_category"><span class="required_field">'.ucwords(EVENTS_NAME_SINGULAR).' Category:</span></label>'."\n";
		$z .= $i."  <select id=\"event_category\" name=\"event_category\">\n";
		foreach ($this->event_categories as $category) {
			$z .= $i."   <option value=\"".$category['event_categoryID']."\"".selected('','',$category['event_categoryID'],$this->event_category).">".ucwords($category['name'])."</option>\n";
		}
		$z .= $i."  </select><br class=\"left\" />\n";
		
		if ($type == 'add') {
			$z .= $i.'  <input class="event_button" type="submit" name="submit" value="Add '.ucwords(EVENTS_NAME_SINGULAR).'" />'."\n";
		} else {
			$z .= $i.'  <input class="event_button" type="submit" name="submit" value="Edit '.ucwords(EVENTS_NAME_SINGULAR).'" /><br class=\"left\" />'."\n";
			$z .= $i.'  <input class="event_button" type="submit" name="submit" value="Delete '.ucwords(EVENTS_NAME_SINGULAR).'" />'."\n";
		}
		$z .= $i.' </form>'."\n";
		$z .= $i."</fieldset>\n";
		$z .= $i.'</div>'."\n";
		$z .= $i.'<!-- /event_form -->'."\n";
		return $z;
	
	}
	
	function add() {
		$mysql = new mysql;
		global $date;
		$insert = array();
		
		$insert[0]['name'] = 'accountID';
		$insert[0]['value'] = $_SESSION["member_id"];
		
		$insert[1]['name'] = 'start_day';
		$insert[1]['value'] = $this->start_day;
		$insert[2]['name'] = 'start_month';
		$insert[2]['value'] = $this->start_month;
		$insert[3]['name'] = 'start_year';
		$insert[3]['value'] = $this->start_year;
		$insert[4]['name'] = 'start_hour';
		$insert[4]['value'] = $this->start_hour;
		$insert[5]['name'] = 'start_minute';
		$insert[5]['value'] = $this->start_minute;
		$insert[6]['name'] = 'end_day';
		$insert[6]['value'] = $this->end_day;
		$insert[7]['name'] = 'end_month';
		$insert[7]['value'] = $this->end_month;
		$insert[8]['name'] = 'end_year';
		$insert[8]['value'] = $this->end_year;
		$insert[9]['name'] = 'end_hour';
		$insert[9]['value'] = $this->end_hour;
		$insert[10]['name'] = 'end_minute';
		$insert[10]['value'] = $this->end_minute;
		
		$insert[11]['name'] = 'title';
		$insert[11]['value'] = mysql_real_escape_string($this->title);
		$insert[12]['name'] = 'description';
		$insert[12]['value'] = mysql_real_escape_string($this->description);
		
		$insert[13]['name'] = 'event_categoryID';
		$insert[13]['value'] = $this->event_category;
		
		$insert[14]['name'] = 'location';
		$insert[14]['value'] = mysql_real_escape_string($this->location);
		
		if (VALIDATE_EVENTS and user_type() !=2) {
			$insert[15]['name'] = 'validated';
			$insert[15]['value'] = '0';
		} else {
			$insert[15]['name'] = 'validated';
			$insert[15]['value'] = '1';		
		}
		
		if (!$mysql->insert_values('events',$insert)) {
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
			$query = "UPDATE events SET title = '".mysql_real_escape_string($this->title)."', description = '".mysql_real_escape_string($this->description)."', location = '".mysql_real_escape_string($this->location)."', start_day = '".$this->start_day."', start_month = '".$this->start_month."', start_year = '".$this->start_year."', start_hour = '".$this->start_hour."', start_minute = '".$this->start_minute."', end_day = '".$this->end_day."', end_month = '".$this->end_month."', end_year = '".$this->end_year."', end_hour = '".$this->end_hour."', end_minute = '".$this->end_minute."', event_categoryID = ".$this->event_category." WHERE eventID = ".$this->id." LIMIT 1";
			if ($mysql->query($query)) {
				return true;
			}
		}
		return false;
	}
	function delete_event($id) {
		$mysql = new mysql;
		if (!$id) return false;
		$query = "DELETE FROM events WHERE eventID = ".$id." LIMIT 1";
		if ($mysql->query($query)) {
			return true;
		}
		return false;
	}
	function validation_html($i,$id,$url) {
		$z = $i.'<!-- validate_event_form -->'."\n";
		$z .= $i."<fieldset>\n";
		$z .= $i."<form name=\"event_validation\" action=\"".$url.append_url($url)."\" method=\"post\">\n";
		$z .= $i." <input type=\"hidden\" name=\"event_id\" value=\"".$id."\" />\n";
		$z .= $i." <input id=\"event_validation_button\" type=\"submit\" name=\"submit\" value=\"Validate ".ucwords(EVENTS_NAME_SINGULAR)."\" /><br class=\"left\"/>\n";
		$z .= $i." <input id=\"event_delete_button\" type=\"submit\" name=\"submit\" value=\"Delete ".ucwords(EVENTS_NAME_SINGULAR)."\" />\n";
		$z .= $i."</form>\n";
		$z .= $i."</fieldset><br />\n";
		$z .= $i.'<!-- /validate_event_form -->'."\n";
		return $z;
	}
	function categories_html($i,$url) {
		$mysql = new mysql;
		// form html
		$z = $i."<!-- categories_form -->\n";
		$z .= $i."<h3>".ucwords(EVENTS_NAME_SINGULAR)." Categories</h3>\n";
		$z .= $i."<fieldset>\n";
		$z .= $i."<form name=\"event_categories\" action=\"".URL.$url.append_url($url)."\" method=\"post\">\n";
		$z .= $i." <input type=\"hidden\" name=\"event\" value=\"1\" />\n";
		$z .= $i." <select id=\"category\" name=\"category\" onclick=\"set_event_buttons();\">\n";
		
		// javascript
		$j = 'function set_event_buttons() {'."\n";
		$b=0;
		foreach ($this->event_categories as $category) {
			$z .= $i."  <option value=\"".$category['event_categoryID']."\"".selected('','',$category['event_categoryID'],0).">".ucwords($category['name'])."</option>\n";
			$mysql->num_rows('SELECT eventID FROM events WHERE event_categoryID = '.$category['event_categoryID'].' LIMIT 1');
			$j .= "\t".'if (document.event_categories.category.selectedIndex == '.($b).') {'."\n";
			if ($mysql->num_rows) {
				$j .= "\t\t".'document.event_categories.event_delete_button.disabled = true;'."\n";
			} else {
				$j .= "\t\t".'document.event_categories.event_delete_button.disabled = false;'."\n";
			}
			$j .= "\t\t".'document.event_categories.name.value = "'.ucwords($category['name']).'";'."\n";
			$j .= "\t"."}\n";
			$b++;
		}
		$j .= "}\n";
		
		$z .= $i." </select>\n";
		$z .= $i." <input type=\"text\" name=\"name\" id=\"name\" value=\"\" /><br class=\"left\" />\n";
		$z .= $i." <input id=\"event_add_button\" type=\"submit\" name=\"submit\" value=\"Add Category\" />\n";
		$z .= $i." <input id=\"event_delete_button\" type=\"submit\" name=\"submit\" value=\"Delete Category\" />\n";
		$z .= $i." <input id=\"event_edit_button\" type=\"submit\" name=\"submit\" value=\"Edit Category\" />\n";
		$z .= $i."</form>\n";
		$z .= $i."</fieldset><br />\n";
		$z .= $i."<!-- /categories_form -->\n";
		$this->category_javascript = $j;
		return $z;
	}
	function search_form($i,$member_id = 0,$past_events = false,$event_category = 0,$keyword = '') {
		if (!$member_id) {
			$member_id = '';
		}
		$z = $i.'<!-- events_search_form -->'."\n";
		$z .= $i."<div id=\"events_search_form\">\n";
		$z .= $i." <fieldset>\n";
		$z .= $i." <legend>\n";
		$z .= $i." Search Parameters\n";
		$z .= $i." </legend>\n";
		$z .= $i." <form id=\"event_search\" name=\"event_search\" action=\"".URL.EVENTS_URL.'/'.append_url()."\" method=\"post\">\n";
		$z .= $i."  <div class=\"event_search\">\n";
		$keyword = htmlentities($keyword);
		$z .= $i."   Search Term:<br /><input type=\"text\" name=\"keyword\" id=\"keyword\" value=\"".$keyword."\" />\n";
		$z .= $i."  </div>\n";
		$z .= $i."  <div class=\"event_search\">\n";
		$z .= $i."   Category:<br />\n";
		$z .= $i."   <select id=\"category\" name=\"category\">\n";
		if ($event_category) {
			$z .= $i."    <option value=\"0\">All</option>\n";
		} else {
			$z .= $i."    <option value=\"0\" selected=\"selected\">All</option>\n";
		}
		foreach ($this->event_categories as $category) {
			$z .= $i."    <option value=\"".$category['event_categoryID']."\"".selected('','',$category['event_categoryID'],$event_category).">".ucwords($category['name'])."</option>\n";
		}
		$z .= $i."   </select>\n";
		$z .= $i."  </div>\n";
		
		$z .= $i."  <div class=\"event_search\">\n";
		$z .= $i."   ".ucwords(MEMBERS_NAME_SINGULAR)." ID:<br /><input type=\"text\" name=\"member\" id=\"member\" value=\"".$member_id."\" />\n";
		$z .= $i."  </div>\n";
		$z .= $i."  <div class=\"event_search\">\n";
		$z .= $i."   Span:<br />\n";
		$z .= $i."   <select id=\"past_events\" name=\"past_events\">\n";
		$z .= $i."    <option value=\"0\"".selected(' ','',0,$past_events).">Upcoming ".ucwords(EVENTS_NAME_PLURAL)."</option>\n";
		$z .= $i."    <option value=\"1\"".selected(' ','',1,$past_events).">Past ".ucwords(EVENTS_NAME_PLURAL)."</option>\n";
		$z .= $i."   </select>\n";
		$z .= $i."  </div>\n";
		$z .= $i."  <div class=\"event_search\">\n";
		$z .= $i."   <br /><input id=\"event_button\" type=\"submit\" name=\"submit\" value=\"Search\" />\n";
		$z .= $i."  </div>\n";
		$z .= $i." </form>\n";
		$z .= $i." </fieldset><br />\n";
		$z .= $i."</div>\n";
		$z .= $i.'<!-- /events_search_form -->'."\n";
		return $z;
	}
	function event_page($i) {
		global $user;
		global $links;
		$z = $i.'<!-- event_page -->'."\n";
		$z .= $i."<div id=\"event_page\">\n";
		$z .= $i.' <span class="event_title">'.$this->title."</span><br />\n";
		$z .= $i.' <span class="event_details">Posted by ';
		if (user_type() == 0 or $_SESSION["member_validated"] == 0 or $_SESSION["member_suspended"] == 1) {
			$z .= '<a href="'.URL.MEMBER_LIST_URL.'/'.$this->member_id.'/">'.ucwords(MEMBERS_NAME_SINGULAR).' '.$this->member_id."</a>";
		} elseif (user_type() == 1 or user_type() == 2) {
			$z .= '<a href="'.URL.MEMBER_LIST_URL.'/'.$this->member_id.'/'.append_url(0).'">'.$user->full_name($this->member_id)."</a>";
		}
		$z .= ' in '.$this->event_category_name($this->event_category);
		if (isset($_SESSION["member_id"])) {
			if ((($this->member_id == $_SESSION["member_id"]) or user_type() == 2) and $links->build_url(1,8)) {
				$z .= ' (<a href="'.URL.$links->complete_url.$this->id.'/'.append_url(0).'">edit</a>)';
			}
		}
		$z .= "</span><br /><br />\n";
		$z .= $i.' <span class="start_date_title">Time:</span> <span class="start_date">'.return_month($this->start_month).' '.$this->start_day;
		if (($this->start_year == $this->end_year) and ($this->start_month == $this->end_month) and ($this->start_day == $this->end_day)) {
			// clunky procedure to output nice format, ex: 4:30 to 8:00 PM
			$start_time = return_time($this->start_hour,$this->start_minute);
			$end_time = return_time($this->end_hour,$this->end_minute);
			$am_pm = '';
			$am_pm_same = false;
			if (strpos($start_time,'AM') and strpos($end_time,'AM')) {
				$am_pm = 'AM';
				$am_pm_same = true;
			}
			if (strpos($start_time,'PM') and strpos($end_time,'PM')) {
				$am_pm = 'PM';
				$am_pm_same = true;
			}
			if ($am_pm_same) {
				if ($am_pm == 'AM') {
					$z .= ' from '.rtrim($start_time,' AM').' to '.$end_time.' ';
				} else {
					$z .= ' from '.rtrim($start_time,' PM').' to '.$end_time.' ';
				}
			} else {
				$z .= ' from '.$start_time.' to '.$end_time.' ';
			}
		} else {
			if ($this->start_year == $this->end_year) {
				$z .= ' to '.return_month($this->end_month).' '.$this->end_day.' ';
			} else {
				$z .= ', '.$this->start_year.' to '.return_month($this->end_month).' '.$this->end_day.', '.$this->end_year;
			}
		}
		$z .= "</span><br />\n";
		
		$z .= $i.' <span class="event_duration_title">Duration: </span><span class="event_duration">'.date_difference(array('year' => $this->start_year, 'month' => $this->start_month, 'day' => $this->start_day, 'hour' => $this->start_hour, 'minutes' => $this->start_minute),array('year' => $this->end_year, 'month' => $this->end_month, 'day' => $this->end_day, 'hour' => $this->end_hour),$this->end_minute)."</span><br />\n";
		if (user_type() and $_SESSION["member_validated"] and !$_SESSION["member_suspended"]) {
			$z .= $i.' <span class="event_location_title">Location: </span><span class="event_location">'.$this->location."</span><br /><br />\n";
		} else {
			$z .= $i."<br />\n";
		}
		
		$started = false;
		$starting_in = date_difference($GLOBALS['date'],array('year' => $this->start_year, 'month' => $this->start_month, 'day' => $this->start_day, 'hour' => $this->start_hour),$this->start_minute);
		if (strpos(' '.$starting_in,'-')) {
			$started = true;
		}
		if ($started) {
			$started_ago = date_difference(array('year' => $this->start_year, 'month' => $this->start_month, 'day' => $this->start_day, 'hour' => $this->start_hour, 'minutes' => $this->start_minute),$GLOBALS['date'],$GLOBALS['date']['minutes']);
			$ending_in = date_difference($GLOBALS['date'],array('year' => $this->end_year, 'month' => $this->end_month, 'day' => $this->end_day, 'hour' => $this->end_hour),$this->end_minute);
			if (strpos(' '.$ending_in,'-')) {
				$z .= $i.' <span class="start_date">This '.strtolower(EVENTS_NAME_SINGULAR).' ended '.return_month($this->end_month).' '.$this->end_day.', '.$this->end_year."</span><br /><br />\n";
			} else {
				$z .= $i.' <span class="event_timing">This '.strtolower(EVENTS_NAME_SINGULAR).' started '.$started_ago.' ago and will end in '.$ending_in."</span><br /><br />\n";
			}
		} else {
			$z .= $i.' <span class="event_timing">This '.strtolower(EVENTS_NAME_SINGULAR).' will start in '.$starting_in."</span><br /><br />\n";
		}
		
		
		$z .= $i.' <span class="event_description">'."\n".indent_variable($i.'  ',$this->description,false)."\n".$i." </span><br />\n";
		$z .= $i."</div>\n";
		$z .= $i.'<!-- /event_page -->'."\n";
		return $z;
	
	}
	function event_list($i,$url,$member_id = 0,$past_events = false,$event_category = 0,$keyword = '',$view = true,$unvalidated = false,$show_results = true) {
		$mysql = new mysql;
		global $user;
		global $links;
		$this->get_event_categories();
		$query = 'SELECT * FROM events WHERE ';
		if ($past_events) {
			$query .= '((end_year < '.$GLOBALS['date']['year'].') OR (end_year = '.$GLOBALS['date']['year'].' AND end_month < '.$GLOBALS['date']['month'].') OR (end_year = '.$GLOBALS['date']['year'].' AND end_month = '.$GLOBALS['date']['month'].' AND end_day < '.$GLOBALS['date']['day'].') OR (end_year = '.$GLOBALS['date']['year'].' AND end_month = '.$GLOBALS['date']['month'].' AND end_day = '.$GLOBALS['date']['day'].' AND end_hour < '.$GLOBALS['date']['hour'].') OR (end_year = '.$GLOBALS['date']['year'].' AND end_month = '.$GLOBALS['date']['month'].' AND end_day = '.$GLOBALS['date']['day'].' AND end_hour = '.$GLOBALS['date']['hour'].' AND end_minute < '.$GLOBALS['date']['minutes'].'))';
		} else {
			$query .= '((end_year > '.$GLOBALS['date']['year'].') OR (end_year = '.$GLOBALS['date']['year'].' AND end_month > '.$GLOBALS['date']['month'].') OR (end_year = '.$GLOBALS['date']['year'].' AND end_month = '.$GLOBALS['date']['month'].' AND end_day > '.$GLOBALS['date']['day'].') OR (end_year = '.$GLOBALS['date']['year'].' AND end_month = '.$GLOBALS['date']['month'].' AND end_day = '.$GLOBALS['date']['day'].' AND end_hour > '.$GLOBALS['date']['hour'].') OR (end_year = '.$GLOBALS['date']['year'].' AND end_month = '.$GLOBALS['date']['month'].' AND end_day = '.$GLOBALS['date']['day'].' AND end_hour = '.$GLOBALS['date']['hour'].' AND end_minute > '.$GLOBALS['date']['minutes'].'))';
		}
		if ($member_id) {
			$query .= ' AND accountID = '.$member_id;
		}
		if ($event_category) {
			$query .= ' AND event_categoryID = '.$event_category;
		}
		$conditions = '';
		if ($keyword) {
			$keywords = search_terms($keyword);
			$num_searches = count($keywords);
			if ($num_searches > 1) {
				$ii = 1;
				$conditions .= ' AND (';
				foreach($keywords as $search_term) {
					$conditions .= "(title LIKE '%".$search_term."%' OR description LIKE '%".$search_term."%')";
					if ($ii <  $num_searches) {
						$conditions .= ' OR ';
					}
					$ii++;
				}
				$conditions .= ')';
			} else {
				$conditions .= " AND (title LIKE '%".$keywords[0]."%' OR description LIKE '%".$keywords[0]."%')";
			}
		}
		if (!$unvalidated) {
			$query .= ' AND validated = 1';
		}
		$query .= $conditions.' ORDER BY start_year, start_month, start_day, start_hour, start_minute';
		
		$z = '';
		if ($mysql->build_array($query)) {
			if (!$mysql->num_rows) {
				if ($show_results) {
					return $i." No ".strtolower(EVENTS_NAME_PLURAL)." found<br /><br />\n";
				} else {
					return '';
				}
			}
			$z .= $i."<!-- event_list -->\n";
			$z .= $i."<div id=\"event_list\">\n";
			if ($show_results) {
				$z .= $i.'Found '.$mysql->num_rows.' '.strtolower(EVENTS_NAME_PLURAL)."<br /><br />\n";
			}
			foreach ($mysql->result as $event) {
				$z .= $i.' <span class="event_title"><a href="'.$url.$event['eventID'].'/'.append_url(0).'">'.$event['title']."</a></span>";
				if (!$event['validated']) {
					$z .= $i.' (<em>Not Yet Validated</em>)';
				}
				$z .= "<br />\n";
				$z .= $i.' <span class="event_details">Posted by ';
				if (user_type() == 0 or $_SESSION["member_validated"] == 0 or $_SESSION["member_suspended"] == 1) {
					$z .= '<a href="'.URL.MEMBER_LIST_URL.'/'.$event['accountID'].'/">'.ucwords(MEMBERS_NAME_SINGULAR).' '.$event['accountID']."</a>";
				} elseif (user_type() == 1 or user_type() == 2) {
					$z .= '<a href="'.URL.MEMBER_LIST_URL.'/'.$event['accountID'].'/'.append_url(0).'">'.$user->full_name($event['accountID'])."</a>";
				}
				$z .= ' in '.$this->event_category_name($event['event_categoryID']);
				if (ENABLE_COMMENTS) {
					$num_comments = $this->num_comments($event['eventID']);
					if ($num_comments) {
						if ($num_comments == 1) {
							$z .= ' (1 comment)';
						} else {
							$z .= ' ('.$num_comments.' comments)';
						}
					}
				}
				if ($view) {
					$z .= ' (<a href="'.URL.EVENTS_URL.'/'.$event['eventID'].'/'.append_url(0).'">view</a>';
				}
				if (isset($_SESSION["member_id"])) {
					if ((($event['accountID'] == $_SESSION["member_id"]) or user_type() == 2) and $links->build_url(1,8)) {
						if ($view) {
							$z .= ' or <a href="'.URL.$links->complete_url.$event['eventID'].'/'.append_url(0).'">edit</a>)';
						} else {
							$z .= ' (<a href="'.URL.$links->complete_url.$event['eventID'].'/'.append_url(0).'">edit</a>)';
						}
					} else {
						if ($view) {
							$z .= ')';
						}
					}
				} else {
					if ($view) {
						$z .= ')';
					}
				}
				$z .= "</span><br />\n";
				
				
				$started = false;
				$starting_in = date_difference($GLOBALS['date'],array('year' => $event['start_year'], 'month' => $event['start_month'], 'day' => $event['start_day'], 'hour' => $event['start_hour']),$event['start_minute']);
				if (strpos(' '.$starting_in,'-')) {
					$started = true;
				}
				if ($started) {
					if ($past_events) {
						$z .= $i.' <span class="start_date">This '.strtolower(EVENTS_NAME_SINGULAR).' ended '.return_month($event['end_month']).' '.$event['end_day'].', '.$event['end_year']."</span><br /><br />\n";
					} else {
						$ending_in = date_difference($GLOBALS['date'],array('year' => $event['end_year'], 'month' => $event['end_month'], 'day' => $event['end_day'], 'hour' => $event['end_hour']),$event['end_minute']);
						$z .= $i.' <span class="start_date">This '.strtolower(EVENTS_NAME_SINGULAR).' has started and will end in '.$ending_in."</span><br /><br />\n";
					}
				} else {
					$z .= $i.' <span class="start_date">This '.strtolower(EVENTS_NAME_SINGULAR).' will start in '.$starting_in."</span><br /><br />\n";
				}
				if (!$event['validated'] and user_type() == 2 and $links->build_url(1,107)) {
					$z .= $i.' <span class="event_description">'."\n".indent_variable($i.'  ',$event['description'])."\n".$i." </span><br />\n";
					$z .= $this->validation_html($i,$event['eventID'],URL.$links->complete_url);
				}
			}
			
			$z .= $i."</div>\n";
			$z .= $i."<!-- /event_list -->\n";
			return $z;
		}
	}
	function sidebar($i,$heading) {
		$mysql = new mysql;
		$mysql->build_array('SELECT * FROM events WHERE ((end_year > '.$GLOBALS['date']['year'].') OR (end_year = '.$GLOBALS['date']['year'].' AND end_month > '.$GLOBALS['date']['month'].') OR (end_year = '.$GLOBALS['date']['year'].' AND end_month = '.$GLOBALS['date']['month'].' AND end_day > '.$GLOBALS['date']['day'].') OR (end_year = '.$GLOBALS['date']['year'].' AND end_month = '.$GLOBALS['date']['month'].' AND end_day = '.$GLOBALS['date']['day'].' AND end_hour > '.$GLOBALS['date']['hour'].') OR (end_year = '.$GLOBALS['date']['year'].' AND end_month = '.$GLOBALS['date']['month'].' AND end_day = '.$GLOBALS['date']['day'].' AND end_hour = '.$GLOBALS['date']['hour'].' AND end_minute > '.$GLOBALS['date']['minutes'].')) AND validated = 1 ORDER BY end_year ASC, end_month ASC, end_day ASC LIMIT 5');
		if (is_array($mysql->result)) {
			
			$z = $i."<!-- events_sidebar -->\n";
			$z .= $i.'<div id="events_sidebar">'."\n";
			$z .= $i.'<span class="sidebar_heading">'.$heading."</span><br />\n";
			foreach ($mysql->result as $event) {
				$z .= $i.'<span class="sidebar_title"><a href="'.URL.EVENTS_URL.'/'.$event['eventID'].'/'.append_url(0).'">'.$event['title'].'</a> by '.first_name($event['accountID'])."</span><br />\n";
				$date_difference = date_difference(array('year' => $GLOBALS['date']['year'], 'month' =>  $GLOBALS['date']['month'], 'day' =>  $GLOBALS['date']['day'], 'hour' => $GLOBALS['date']['hour']),array('year' => $event['start_year'], 'month' => $event['start_month'], 'day' => $event['start_day']));
				if (strpos(' '.$date_difference,'-')) {
					$ending_starting = 'Ending in ';
					$date_difference = date_difference(array('year' => $GLOBALS['date']['year'], 'month' =>  $GLOBALS['date']['month'], 'day' =>  $GLOBALS['date']['day'], 'hour' => $GLOBALS['date']['hour']),array('year' => $event['end_year'], 'month' => $event['end_month'], 'day' => $event['end_day']));
				} else {
					$ending_starting = 'Starting';
				}
				if (strpos(' '.$date_difference,'days,')) {
					$arr = explode('days,',$date_difference);
					$date_difference = 'in '.$arr[0].' days';
				} else {
					if (strpos(' '.$date_difference,'month') and strpos(' '.$date_difference,'day')) {
						$arr = explode('day,',$date_difference);
						$date_difference = $arr[0].' day ago';
					} else {
						$date_difference = 'today';
					}
				}
				$z .= $i.'<span class="sidebar_details">'.$ending_starting.' '.$date_difference.'. Event Type: <a href="'.URL.ARTICLES_URL.'/?category='.$event['event_categoryID'].append_url(' ?').'">'.$this->event_category_name($event['event_categoryID'])."</a></span><br /><br />\n";
			}
			
		} else {
			echo $mysql->error;
			return '';
		}
		$z .= $i.'</div>'."\n";
		$z .= $i."<!-- /events_sidebar -->\n";
		return $z;
	}
}

?>