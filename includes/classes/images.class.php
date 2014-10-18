<?php
class images {

	var	$id,
		$member_id,
		$noticeboard_id,
		$article_id,
		$name,
		$title,
		$blurb,
		$description,
		
		$thumbnail,
		$page,
		
		$t_w,
		$t_h,
		$p_w,
		$p_h,
		$w,
		$h,
		
		$title_required,
		$blurb_required,
		$description_required,
		
		$table,
		$field,
		$field_value,
		$img,
		$error;

	function images() {
		$this->set_required_variables();
	}
	
	function set_required_variables() {
		$mysql = new mysql;
		$mysql->result('SELECT image_title_required, image_blurb_required, image_description_required FROM config');
		$this->title_required = $mysql->result['image_title_required'];
		$this->blurb_required = $mysql->result['image_blurb_required'];
		$this->description_required = $mysql->result['image_description_required'];	
	}
	function clear() {
		unset($this->id);
		unset($this->member_id);
		unset($this->noticeboard_id);
		unset($this->name);
		unset($this->title);
		unset($this->blurb);
		unset($this->description);
		unset($this->thumbnail);
		unset($this->page);
		unset($this->t_w);
		unset($this->t_h);
		unset($this->p_w);
		unset($this->p_h);
		unset($this->w);
		unset($this->h);
		unset($this->field);
		unset($this->field_value);
		unset($this->img);
		unset($this->error);
	}
	function info($id) {
		$mysql = new mysql;
		
		if (!$mysql->result('SELECT * FROM images WHERE imageID = '.$id.' LIMIT 1')) {
			$this->error = $mysql->error;
			return false;
		}
		
		$this->id = 			$mysql->result['imageID'];
		$this->member_id = 		$mysql->result['accountID'];
		$this->title = 			$mysql->result['title'];
		$this->blurb = 			$mysql->result['blurb'];
		$this->description = 	$mysql->result['description'];
		$this->name = 			$mysql->result['name'];
		$this->thumbnail = 		$mysql->result['thumbnail'];
		$this->page = 			$mysql->result['page'];
		$this->w = 				$mysql->result['w'];
		$this->h = 				$mysql->result['h'];
		$this->t_w = 			$mysql->result['t_w'];
		$this->t_h = 			$mysql->result['t_h'];
		$this->p_w = 			$mysql->result['p_w'];
		$this->p_h = 			$mysql->result['p_h'];
		
		return true;
		
	}
	
	function img($id,$type = '',$alt = 0,$class = 0) {
		if (!$this->info($id)) return false;
		
		if ($type == 't' and $this->thumbnail) {
			$w = $this->t_w;
			$h = $this->t_h;
		} elseif ($type == 'p' and $this->page) {
			$w = $this->p_w;
			$h = $this->p_h;
		} else {
			$w = $this->w;
			$h = $this->h;
			$type = '';
		}
		
		$prefix = '';
		if (!empty($type)) $prefix = '-'.$type;
		
		if ($alt) {
			if ($class) {
				$this->img = '<img class="'.$class.'" src="'.URL.'images/'.$this->name.$prefix.'.png" alt="'.$alt.'" width="'.$w.'" height="'.$h.'" />';
			} else {
				$this->img = '<img src="'.URL.'images/'.$this->name.$prefix.'.png" alt="'.$alt.'" width="'.$w.'" height="'.$h.'" />';
			}
		} else {
			if ($class) {
				$this->img = '<img class="'.$class.'" src="'.URL.'images/'.$this->name.$prefix.'.png" alt="'.htmlspecialchars($this->title).'" width="'.$w.'" height="'.$h.'" />';
			} else {
				$this->img = '<img src="'.URL.'images/'.$this->name.$prefix.'.png" alt="'.htmlspecialchars($this->title).'" width="'.$w.'" height="'.$h.'" />';
			}
		}
		if ($type == '-p') {
			$this->img = '<a href="'.URL.'images/'.$this->name.'.png'.append_url(0).'">'.$this->img.'</a>';
		}
		return true;
	}
	function thumbs($i,$data,$url = '') {
		$z = '';
		$z .= $i."<!-- thumbs -->\n";
		foreach ($data as $image) {
			if ($this->img($image['imageID'],'t',0,0)) {
				
				$z .= $i."<div class=\"thumbs\">\n";
				if ($this->title) $z .= $i." <span class=\"thumbs_title\">".$this->title."</span><br />\n";
				$z .= $i.' <a href="'.$url.'?image='.$image['imageID'].append_url(' ?').'">'.$this->img."</a><br />\n";
				if ($this->blurb) $z .= $i." <span class=\"thumbs_blurb\">".$this->blurb."</span><br />\n";
				$z .= $i."</div>\n";
				
			}
		}
		$z .= $i."<!-- /thumbs -->\n";
		return $z;
	}
	function make_default() {
		if (empty($this->table) or empty($this->field) or empty($this->field_value) or empty($this->id)) {
			$this->error = 'Input error';
			return false;
		}
		$mysql = new mysql;
		if (!$mysql->query('UPDATE '.$this->table.' SET imageID = '.$this->id.' WHERE '.$this->field.' = '.$this->field_value.' LIMIT 1')) {
			$this->error = $mysql->error;
			return false;
		}
		return true;
	
	}
	
	function add() {
		$mysql = new mysql;
		$insert = array();
		$insert[0]['name'] = 'accountID';
		$insert[0]['value'] = $this->member_id;
		$insert[1]['name'] = 'title';
		$insert[1]['value'] = mysql_real_escape_string($this->title);
		$insert[2]['name'] = 'blurb';
		$insert[2]['value'] = mysql_real_escape_string($this->blurb);
		$insert[3]['name'] = 'description';
		$insert[3]['value'] = mysql_real_escape_string($this->description);
		$insert[4]['name'] = 'name';
		$insert[4]['value'] = $this->name;
		$insert[5]['name'] = 'thumbnail';
		$insert[5]['value'] = $this->thumbnail;
		$insert[6]['name'] = 'page';
		$insert[6]['value'] = $this->page;
		$insert[7]['name'] = 'w';
		$insert[7]['value'] = $this->w;
		$insert[8]['name'] = 'h';
		$insert[8]['value'] = $this->h;
		$insert[9]['name'] = 't_w';
		$insert[9]['value'] = $this->t_w;
		$insert[10]['name'] = 't_h';
		$insert[10]['value'] = $this->t_h;
		$insert[11]['name'] = 'p_w';
		$insert[11]['value'] = $this->p_w;
		$insert[12]['name'] = 'p_h';
		$insert[12]['value'] = $this->p_h;
		$insert[13]['name'] = 'articleID';
		$insert[13]['value'] = $this->article_id;
		
		$insert[14]['name'] = 'noticeboardID';
		$insert[14]['value'] = $this->noticeboard_id;
		
		if (!$mysql->insert_values('images',$insert)) {
			$this->error = $mysql->error;
			return false;
		} else {
			$this->id = $mysql->inserted_id;
			return true;
		}
		
	}
	
	function edit() {
		$mysql = new mysql;
		
		$q = '';
		
		$q .= 'title = \''.mysql_real_escape_string($this->title).'\', ';
		$q .= 'blurb = \''.mysql_real_escape_string($this->blurb).'\', ';
		$q .= 'description = \''.mysql_real_escape_string($this->description).'\', ';
		
		if (empty($q)) return false;
		
		$q = 'UPDATE images SET '.$q;
		$q = rtrim($q,", ");
		$q .= ' WHERE imageID = '.$this->id.' LIMIT 1';
		if ($mysql->query($q)) {
			return true;
		} else {
			$this->error = $mysql->error;
			return false;
		}
	}
	function delete() {
		if (!$this->info($this->id)) return false;
		
		ob_start();
		error_reporting(2047);
		$lm = '';

		if (file_exists(PATH.'images/'.$this->name.'.png')) {
			if (!unlink(PATH.'images/'.$this->name.'.png')){
				$lm .= 'Could not delete '.$this->name.'.png<br />';
			}
		} else {
			$lm .= 'Could not find '.$this->name.'.png<br />';
		}
		
		if ($this->thumbnail) {
			if (file_exists(PATH.'images/'.$this->name.'-t.png')) {
				if (!unlink(PATH.'images/'.$this->name.'-t.png')){
					$lm .= 'Could not delete '.$this->name.'-t.png<br />';
				}
			} else {
				$lm .= 'Could not find '.$this->name.'-t.png<br />';
			}
		}
		if ($this->page) {
			if (file_exists(PATH.'images/'.$this->name.'-p.png')) {
				if (!unlink(PATH.'images/'.$this->name.'-p.png')){
					$lm .= 'Could not delete '.$this->name.'-p.png<br />';
				}
			} else {
				$lm .= 'Could not find '.$this->name.'-p.png<br />';
			}
		}
		error_reporting(0);
		$buffer = ob_get_contents();
		ob_end_clean();
		if (!empty($buffer) or !empty($lm)) {
			$this->error = 'Could not delete Image';
			if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: Image '.$lm.' deleted from file system');
			if (ENABLE_ERROR_LOG) log_error('FAILED: Image '.$lm.' deleted from file system<br />Error: '.$buffer);
		}
		
		$mysql = new mysql;
		
		if (!$mysql->query('DELETE FROM images WHERE imageID = '.$this->id.' LIMIT 1')) {
			$this->error = 'Database Error';
			if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: Image '.$lm.' deleted from database');
			if (ENABLE_ERROR_LOG) log_error('FAILED: Image '.$lm.' deleted from database<br />Error: '.$mysql->error);
			return false;
		}
		return true;
	
	}
	
	function delete_group($field,$id) {
		$mysql = new mysql;
		
		if (!$mysql->build_array('SELECT * FROM images WHERE '.$field.' = '.$id)) {
			$this->error = $mysql->error;
			return false;
		}
		if (!$mysql->num_rows) return true;
		
		ob_start();
		error_reporting(2047);
		$lm = '';
		foreach ($mysql->result as $image) {
			if (file_exists(PATH.'images/'.$image['name'].'.png')) {
				if (!unlink(PATH.'images/'.$image['name'].'.png')){
					$lm .= 'Could not delete '.$image['name'].'.png<br />';
				}
			} else {
				$lm .= 'Could not find '.$image['name'].'.png<br />';
			}
			
			if ($image['thumbnail']) {
				if (file_exists(PATH.'images/'.$image['name'].'-t.png')) {
					if (!unlink(PATH.'images/'.$image['name'].'-t.png')){
						$lm .= 'Could not delete '.$image['name'].'-t.png<br />';
					}
				} else {
					$lm .= 'Could not find '.$image['name'].'-t.png<br />';
				}
			}
			if ($image['page']) {
				if (file_exists(PATH.'images/'.$image['name'].'-p.png')) {
					if (!unlink(PATH.'images/'.$image['name'].'-p.png')){
						$lm .= 'Could not delete '.$image['name'].'-p.png<br />';
					}
				} else {
					$lm .= 'Could not find '.$image['name'].'-p.png<br />';
				}
			}
			
		}
		error_reporting(0);
		$buffer = ob_get_contents();
		ob_end_clean();
		if (!empty($buffer) or !empty($lm)) {
			$this->error = 'There were errors when trying to delete images:<br />'.$lm.'BUFFER:<br />'.$buffer;
			if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: Images deleted from file system:<br />'.$lm);
			if (ENABLE_ERROR_LOG) log_error('FAILED: Image '.$lm.' deleted from file system:<br />'.$lm.'<br />Error: '.$buffer);
		}
		if (!$mysql->query('DELETE FROM images WHERE '.$field.' = '.$id)) {
			$this->error = 'Database Error';
			if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: Image '.$lm.' deleted from database');
			if (ENABLE_ERROR_LOG) log_error('FAILED: Image '.$lm.' deleted from database<br />Error: '.$mysql->error);
			return false;
		}
		return true;
	}
	
	function substantiate($member_id,$field,$field_value,$t_w,$t_h,$p_w,$p_h,$name = '') {
		if (isset($_FILES['upload']['name'])) {
			if (!$_FILES['upload']['name']) {
				$this->error = '';
				return false;
			}
		} else {
			return false;
		}
		ob_start();
		error_reporting(2047);
		$mtime = array_sum(explode(' ',microtime()));
		$mtime = ceil($mtime);
		
		if (!empty($name)) {
			$name = strtolower($name);
			$name = str_replace(' ','_',$name);
			$name = preg_replace("/[^0-9a-z\" ]/",'',$name);
			$name = $name.'_'.$mtime;
		} else {
			$name = $mtime;
		}
		$this->member_id = $member_id;
		$this->name = $name;
		$this->field = $field;
		$this->field_value = $field_value;
		
		if ($this->field == 'noticeboardID') $this->noticeboard_id = $this->field_value;
		if ($this->field == 'articleID') $this->article_id = $this->field_value;
		
		$pathname = PATH.'images/'.$name;
		
		if (!move_uploaded_file($_FILES['upload']['tmp_name'],$pathname.'.png')) {
			$this->error = 'Could not move image file';
			error_reporting(0);
			$buffer = ob_get_contents();
			ob_end_clean();
			if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: Uploaded Image Moved');
			if (ENABLE_ERROR_LOG) log_error('FAILED: Uploaded Image Moved<br />Error: '.$buffer);
			return false;
		}
		
		// first build page-sized image
		$size = getimagesize($pathname.'.png');
		$this->w = $size[0];
		$this->h = $size[1];
		
		$page_resized = 0;
		if ($this->w > $p_w) {
			$main_image = imagecreatefromjpeg($pathname.'.png');
			$new_p_h = (($p_w * $this->h) / $this->w);
			if ($new_p_h <= $p_h) {
				$page_resized = 1;
				$page_image = imagecreatetruecolor($p_w,$new_p_h);
				imagecopyresampled($page_image,$main_image,0,0,0,0,$p_w,$new_p_h,$this->w,$this->h);
				imagejpeg($page_image,$pathname.'-p.png',IMAGE_QUALITY);
				
				$this->p_h = $new_p_h;
				$this->p_w = $p_w;
			}
		}
		
		if ($this->h > $p_h and !$page_resized) {
			$page_resized = 1;
			$main_image = imagecreatefromjpeg($pathname.'.png');
			$new_p_w = (($p_h * $this->w) / $this->h);
			$page_image = imagecreatetruecolor($new_p_w,$p_h);
			imagecopyresampled($page_image,$main_image,0,0,0,0,$new_p_w,$p_h,$this->w,$this->h);
			imagejpeg($page_image,$pathname.'-p.png',IMAGE_QUALITY);
			
			$this->p_h = $p_h;
			$this->p_w = $new_p_w;
		}
		
		
		// now build thumb-sized image
		$thumb_resized = 0;
		if ($this->w > $t_w) {
			$main_image = imagecreatefromjpeg($pathname.'.png');
			$new_t_h = (($t_w * $this->h) / $this->w);
			if ($new_t_h <= $t_h) {
				$thumb_resized = 1;
				$thumb_image = imagecreatetruecolor($t_w,$new_t_h);
				imagecopyresampled($thumb_image,$main_image,0,0,0,0,$t_w,$new_t_h,$this->w,$this->h);
				imagejpeg($thumb_image,$pathname.'-t.png',IMAGE_QUALITY);
				
				$this->t_w = $t_w;
				$this->t_h = $new_t_h;
			}
		}
		
		if ($this->h > $t_h and !$thumb_resized) {
			$thumb_resized = 1;
			$main_image = imagecreatefromjpeg($pathname.'.png');
			$new_t_w = (($t_h * $this->w) / $this->h);
			$thumb_image = imagecreatetruecolor($new_t_w,$t_h);
			imagecopyresampled($thumb_image,$main_image,0,0,0,0,$new_t_w,$t_h,$this->w,$this->h);
			imagejpeg($thumb_image,$pathname.'-t.png',IMAGE_QUALITY);
			
			$this->t_w = $new_t_w;
			$this->t_h = $t_h;
		}
		error_reporting(0);
		if ($page_resized) $this->page = 1;
		if ($thumb_resized) {
			$this->thumbnail = 1;
			$buffer = ob_get_contents();
			ob_end_clean();
			return true;
		}
		$buffer = ob_get_contents();
		ob_end_clean();
		if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: Substantiate Image');
		if (ENABLE_ERROR_LOG) log_error('FAILED: Substantiate Image<br />Error: '.$buffer);
		return false;	
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
	function validate_form($i) {
		if (isset($_FILES['upload']['name'])) {
			if ($_FILES['upload']['name']) {
				if (strpos(strtolower($_FILES['upload']['name']),'.gif')) {
					return 'Sorry this script does not support GIF files.';
				}
			}
		}
		$post_post = remove_slashes($_POST);
		$message = '';
		if (empty($post_post['image_title']) and $this->title_required == 1) {
			$message .= $i."An image title is required<br />\n";
		} else {
			if (VALIDATE_XHTML) {
				$xhtml_report = valid_XHTML($post_post['image_title']);
				if ($xhtml_report) {
					$message .= $xhtml_report;
				}
			}
			$this->title = remove_bad_tags($post_post['image_title']);
		}
		if (empty($post_post['image_blurb']) and $this->blurb_required == 1) {
			$message .= $i."An image blurb is required<br />\n";
		} else {
			if (VALIDATE_XHTML) {
				$xhtml_report = valid_XHTML($post_post['image_blurb']);
				if ($xhtml_report) {
					$message .= $xhtml_report;
				}
			}
			$this->blurb = remove_bad_tags($post_post['image_blurb']);
		}
		if (empty($post_post['image_description']) and $this->description_required == 1) {
			$message .= $i."An image description is required<br />\n";
		} else {
			if (VALIDATE_XHTML) {
				$xhtml_report = valid_XHTML($post_post['image_description']);
				if ($xhtml_report) {
					$message .= $xhtml_report;
				}
			}
			$this->description = remove_bad_tags($post_post['image_description']);
		}
		$this->id = $post_post['image_id'];
		$this->field = $post_post['field'];
		$this->field_value = $post_post['field_value'];
		$this->table = $post_post['table'];
		
		return $message;
		
	}
	function edit_form($i,$id,$field,$table,$action,$additional_sql = '') {
		$this->field_value = $id;
		$this->field = $field;
		$this->table = $table;
		
		$z = '';
		$z .= $i."<!-- edit_image -->\n";
		$mysql = new mysql;
		
		
		if ($mysql->result('SELECT imageID FROM '.$this->table.' WHERE '.$this->field.' = '.$this->field_value.' LIMIT 1')) {
			$current_default = $mysql->result['imageID'];
			
			if ($additional_sql) {
				$q = 'SELECT * FROM images WHERE '.$field.' = '.$id.' AND '.$additional_sql;
			} else {
				$q = 'SELECT * FROM images WHERE '.$field.' = '.$id;
			}
			if ($mysql->build_array($q)) {
				if ($mysql->num_rows) {
					foreach ($mysql->result as $image) {
						if (!empty($image['title'])) {
							$name = $image['title'];
						} else {
							$name = 'image #'.$image['imageID'];
						}
						if ($this->img($image['imageID'],'t',0,'edit_image_form')) {
							$z .= $i."<div class=\"edit_image_box\">\n";
							$z .= $i." <form name=\"edit_image\" method=\"post\" enctype=\"multipart/form-data\" action=\"".$action."\">\n";
							if ($image['imageID'] != $current_default) {
								$z .= $i."  <input class=\"default_image_button\" type=\"submit\" name=\"submit\" value=\"Set Default\">\n";
								$z .= $i."  Edit <strong>".$name."</strong>\n";
							} else {
								$z .= $i."  Edit <strong>".$name."</strong>\n";
								$z .= $i."  <div class=\"image_spacer\"></div>\n";
							}
							$z .= $i."  <br class=\"right\" />".$this->img."\n";
							$z .= $this->form_html($i.'  ','edit');
							$z .= $i."  <input class=\"edit_image_button\" type=\"submit\" name=\"submit\" value=\"Edit Image\">\n";
							$z .= $i."  <input class=\"delete_image_button\" type=\"submit\" name=\"submit\" value=\"Delete Image\">\n";
							$z .= $i." </form>\n";
							$z .= $i."</div>\n";
						}
					}
					$z .= $i."<br class=\"left\" />\n";
				}
			}
		}
		$z .= $i."<br /><strong>Add an image:</strong><br /><br />\n";
		$z .= $i."<form name=\"edit_image\" method=\"post\" enctype=\"multipart/form-data\" action=\"".$action."\">\n";
		$this->clear();
		$z .= $this->form_html($i.' ','add');
		$z .= $i." <input class=\"button\" type=\"submit\" name=\"submit\" value=\"Add Image\">\n";
		$z .= $i."</form>\n";
		$z .= $i."<!-- /edit_image -->\n";
		return $z;
	}
	
	function form_html($i,$type,$image_required = 0, $class = '') {
		
		if (!isset($this->title)) $this->title = '';
		if (!isset($this->blurb)) $this->blurb = '';
		if (!isset($this->description)) $this->description = '';
		$z = $i."<!-- image_form -->\n";
		$z .= $i.'<input type="hidden" name="MAX_FILE_SIZE" value="6000000" />'."\n";
		if ($type == 'add') {
			$max_upload_size = min(let_to_num(ini_get('post_max_size')), let_to_num(ini_get('upload_max_filesize')));
			$z .= $i."Maximum upload file size is ".($max_upload_size/(1024*1024))."MB.<br /><br />"."\n";
			$z .= $i."<label for=\"upload\">".$this->check_required($image_required,1)."Upload Image:".$this->check_required($image_required,2)."</label>\n";
			$z .= $i."<input type=\"file\" name=\"upload\" id=\"upload\" /><br class=\"left\" />\n";
			$z .= $i."<label for=\"image_title\">".$this->check_required($this->title_required,1)."Image title:".$this->check_required($this->title_required,2)."</label>\n";
			$z .= $i."<input type=\"text\" name=\"image_title\" id=\"image_title\" value=\"".htmlspecialchars($this->title)."\" /><br class=\"left\" />\n";
			$z .= $i."<label for=\"image_blurb\">".$this->check_required($this->blurb_required,1)."Image blurb:".$this->check_required($this->blurb_required,2)."</label>\n";
			$z .= $i."<textarea id=\"image_blurb\" name=\"image_blurb\" cols=\"30\" rows=\"5\">".htmlspecialchars($this->blurb)."</textarea><br class=\"left\" />\n";
			$z .= $i."<label for=\"image_description\">".$this->check_required($this->description_required,1)."Image Description:".$this->check_required($this->description_required,2)."</label>\n";
			$z .= $i."<textarea id=\"image_description\" name=\"image_description\" cols=\"30\" rows=\"5\">".htmlspecialchars($this->description)."</textarea><br class=\"left\" />\n";
		} else {
			$z .= $i."<input type=\"hidden\" name=\"image_id\" value=\"".$this->id."\" />\n";
			$z .= $i."<input type=\"hidden\" name=\"field\" value=\"".$this->field."\" />\n";
			$z .= $i."<input type=\"hidden\" name=\"field_value\" value=\"".$this->field_value."\" />\n";
			$z .= $i."<input type=\"hidden\" name=\"table\" value=\"".$this->table."\" />\n";
			$z .= $i.$this->check_required($this->title_required,1)."Image title:".$this->check_required($this->title_required,2)."<br />\n";
			$z .= $i."<input class=\"edit_image_title\" type=\"text\" name=\"image_title\" value=\"".htmlspecialchars($this->title)."\" /><br class=\"left\" />\n";
			$z .= $i.$this->check_required($this->blurb_required,1)."Image blurb:".$this->check_required($this->blurb_required,2)."<br />\n";
			$z .= $i."<textarea class=\"edit_image_blurb\" name=\"image_blurb\" cols=\"30\" rows=\"5\">".htmlspecialchars($this->blurb)."</textarea><br class=\"left\" />\n";
			$z .= $i.$this->check_required($this->description_required,1)."Image Description:".$this->check_required($this->description_required,2)."<br />\n";
			$z .= $i."<textarea class=\"edit_image_description\" name=\"image_description\" cols=\"30\" rows=\"5\">".htmlspecialchars($this->description)."</textarea><br class=\"left\" />\n";
		}
		$z .= $i."<!-- /image_form -->\n";
		return $z;
	}
	
	
	
	
}
?>