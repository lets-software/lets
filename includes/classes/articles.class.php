<?
class articles {

	var	$id,
		$title,
		$blurb,
		$body,
		$article_category,
		$member_id,
		$day,
		$month,
		$year,
		$validated,
		$art_cat_name,
		
		$image_id,
		$art_cats,
		$error;

	function articles() {
		$this->error = "No errors";
		$this->set_required_variables();
		$this->get_art_cats();
	}
	function set_required_variables() {
		$mysql = new mysql;
		$mysql->result('SELECT article_title_required, article_blurb_required, article_body_required FROM config');
		$this->article_title_required = $mysql->result['article_title_required'];
		$this->article_blurb_required = $mysql->result['article_blurb_required'];
		$this->article_body_required = $mysql->result['article_body_required'];
	}
	function set_default_image($id,$image_id) {
		$mysql = new mysql;
		if (!$mysql->query('UPDATE articles SET imageID = '.$image_id.' WHERE articleID = '.$id.' LIMIT 1')) {
			$this->error = $mysql->error;
			return false;
		}
		return true;
	}
	
	function rebuild_default_image($id) {
		$mysql = new mysql;
		if ($mysql->result('SELECT imageID FROM images WHERE articleID = '.$id.' LIMIT 1')) {
			$this->set_default_image($id,$mysql->result['imageID']);
		}		
	}
	function validate($id) {
		$mysql = new mysql;
		if ($mysql->query('UPDATE articles SET validated = 1 WHERE articleID = '.$id.' LIMIT 1')) {
			return true;
		}
		return false;
	
	}
	function clear() {
		$this->id = '';
		$this->title = '';
		$this->blurb = '';
		$this->body = '';
		$this->article_category = '';
		$this->member_id = '';
		$this->error = '';
		$this->validated =  '';
		$this->image_id =  '';
	}
	
	function edit() {
		$mysql = new mysql;
		$q = 'UPDATE articles SET ';
		if ($this->article_category) $q .= 'article_categoryID = \''.$this->article_category.'\', ';
		if ($this->title) $q .= 'title = \''.mysql_real_escape_string($this->title).'\', ';
		if ($this->blurb) $q .= 'blurb = \''.mysql_real_escape_string($this->blurb).'\', ';
		if ($this->body) $q .= 'body = \''.mysql_real_escape_string($this->body).'\', ';
		if ($this->image_id) $q .= 'imageID = \''.$this->image_id.'\', ';
		$q .= ' accountID = '.$this->member_id.' WHERE articleID = '.$this->id.' LIMIT 1';
		
		if ($mysql->query($q)) {
			return true;
		} else {
			$this->error = $mysql->error;
			return false;
		}
	
	}
	
	function delete($id) {
		global $image;
		$mysql = new mysql;
		$this->error = '';
		if (!$mysql->query('DELETE FROM articles WHERE articleID = '.$id.' LIMIT 1')) {
			$this->error = $mysql->error;
			return false;
		}		
		if (!$mysql->query('DELETE FROM comments WHERE articleID = '.$id)) {
			$this->error = $mysql->error;
		}
		if (!$image->delete_group('articleID',$id)) {
			$this->error .= $image->error;
			return false;
		}
		if ($this->error) {
			return false;
		} else {
			return true;
		}
	
	}
	
	function build_article($id) {
		$mysql = new mysql;
		if ($mysql->result('SELECT * FROM articles WHERE articleID = '.$id.' LIMIT 1')) {
			$this->art_cat_name($mysql->result['article_categoryID']);
			$this->id 				= 	$id;
			$this->title 			= 	$mysql->result['title'];
			$this->blurb 			=  	$mysql->result['blurb'];
			$this->body 			=  	$mysql->result['body'];
			$this->article_category =  	$mysql->result['article_categoryID'];
			$this->member_id 		=  	$mysql->result['accountID'];
			$this->validated 		=  	$mysql->result['validated'];
			$this->image_id 		=  	$mysql->result['imageID'];
			$this->art_cat_name 	= 	$this->art_cat_name;
			$this->day 				= 	$mysql->result['day'];
			$this->month 			=  	$mysql->result['month'];
			$this->year 			=  	$mysql->result['year'];
			return true;
		}
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
	
	function validate_form() {
		global $links;
		$this->error = '';
		if (isset($_POST['deletion_confirmed'])) if ($_POST['deletion_confirmed']) return true;
		$post_post = remove_slashes($_POST);
		if (!isset($post_post['article_title']) and $this->article_title_required) {
			$this->error .= ucwords(ARTICLES_NAME_SINGULAR).' title required<br />';
		} else {
			if (!$post_post['article_title'] and $this->article_title_required) {
				$this->error .= ucwords(ARTICLES_NAME_SINGULAR).' title required<br />';
			}
			if (VALIDATE_XHTML) {
				$xhtml_report = valid_XHTML($post_post['article_title']);
				if ($xhtml_report) {
					$this->error .= $xhtml_report;
				}
			}
			$this->title = remove_bad_tags($post_post['article_title']);
		}
		if (!isset($post_post['article_blurb']) and $this->article_blurb_required) {
			$this->error .= ucwords(ARTICLES_NAME_SINGULAR).' blurb required<br />';
		} else {
			if (!$post_post['article_blurb'] and $this->article_blurb_required) {
				$this->error .= ucwords(ARTICLES_NAME_SINGULAR).' blurb required<br />';
			}
			if (VALIDATE_XHTML) {
				$xhtml_report = valid_XHTML($post_post['article_blurb']);
				if ($xhtml_report) {
					$this->error .= $xhtml_report;
				}
			}
			$this->blurb = remove_bad_tags($post_post['article_blurb']);
		}
		if (!isset($post_post['article_body']) and $this->article_body_required) {
			$this->error .= ucwords(ARTICLES_NAME_SINGULAR).' body required<br />';
		} else {
			if (!$post_post['article_body'] and $this->article_body_required) {
				$this->error .= ucwords(ARTICLES_NAME_SINGULAR).' body required<br />';
			}
			if (VALIDATE_XHTML) {
				$xhtml_report = valid_XHTML($post_post['article_body']);
				if ($xhtml_report) {
					$this->error .= $xhtml_report;
				}
			}
			$this->body = remove_bad_tags($post_post['article_body']);
		}

		if (isset($post_post['article_id'])) {
			if (is_numeric($post_post['article_id'])) {
				$this->id = $post_post['article_id'];
			}
		}
		if (isset($post_post['art_cat'])) {
			$this->article_category = $post_post['art_cat'];
		} else {
			$this->error .= 'No Category<br />';;
		}
		if ($this->error) {
			return false;
		} else {
			return true;
		}
	}

	function form_html($i,$type,$action_page) {
		global $links;
		if (ENABLE_IMAGES) global $image;
		$z = $i.'<!-- article_form -->'."\n";
		$z .= $i.'<div class="article_form">'."\n";
		if ($type == 'add') {
			$z .= $i."<span class=\"add_article_title\">Add a new ".ucwords(ARTICLES_NAME_SINGULAR)."</span><br /><br />\n";
		} elseif ($type == 'edit' and $this->id) {
			$z .= $i."<span class=\"add_article_title\">Edit ".ucwords(ARTICLES_NAME_SINGULAR)." #".$this->id." or <a href=\"".URL.$action_page.append_url(URL.$action_page).'">Add a new '.ucwords(ARTICLES_NAME_SINGULAR)."</a></span><br /><br />\n";
		}
		$z .= $i."Required fields are <span class=\"required_field\">".REQUIRED_DISPLAY."</span>.<br /><br />\n";
		$z .= $i."<fieldset><br />\n";
		$z .= $i.' <form name="article" method="post" enctype="multipart/form-data" action="'.URL.$action_page;
		if ($type == 'edit' and $this->id) $z .= $this->id.'/';
		$z .= append_url().'">'."\n";
		$z .= $i.'  <label for="article_title">'.$this->check_required($this->article_title_required,1).'Title:'.$this->check_required($this->article_title_required,2).'</label>'."\n";
		$z .= $i.'  <input type="text" id="article_title" name="article_title" maxlength="255" value="'.htmlspecialchars($this->title).'" /><br class="left" />'."\n";
		$z .= $i.'  <label for="article_blurb">'.$this->check_required($this->article_blurb_required,1).'Blurb:'.$this->check_required($this->article_blurb_required,2).'</label>'."\n";
		$z .= $i.'  <textarea id="article_blurb" name="article_blurb" cols=\"60\" rows=\"4\">'.htmlspecialchars($this->blurb)."</textarea><br class=\"left\" />\n";
		$z .= $i.'  <label for="article_body">'.$this->check_required($this->article_body_required,1).'Body:'.$this->check_required($this->article_body_required,2).'</label>'."\n";
		$z .= $i.'  <textarea id="article_body" name="article_body" cols=\"80\" rows=\"6\">'.htmlspecialchars($this->body)."</textarea><br class=\"left\" />\n";
		
		$z .= $i."  <label for=\"art_cat\">".ucwords(ARTICLES_NAME_SINGULAR)." type:</label>\n";
		$z .= $i."  <select id=\"art_cat\" name=\"art_cat\">\n";
		foreach ($this->art_cats as $category) {
			$z .= $i."   <option value=\"".$category['art_catID']."\"".selected('',' ',$category['art_catID'],$this->article_category).">".ucwords($category['art_cat'])."</option>\n";
		}
		$z .= $i."  </select><br class=\"left\" />\n";
		
		if ($type == 'edit' and $this->id) {
			$z .= $i.'  <input type="hidden" name="article_id" value="'.$this->id.'" />'."\n";
		}
		
		if ($type == 'edit') {
			if (ENABLE_IMAGES) {
				$z .= $i.'  <input class="article_button" type="submit" name="submit" value="Edit '.ucwords(ARTICLES_NAME_SINGULAR).'" /><br /><br /><input class="article_button" type="submit" name="submit" value="Delete '.ucwords(ARTICLES_NAME_SINGULAR).'" />'."\n";
				$z .= $i."</form>\n";
				$z .= $i."</fieldset>\n";
				$z .= $i."<br />\n";
				$z .= $i."<fieldset>\n";
				$z .= $i."<legend>\n";
				$z .= $i."Add or Edit images\n";
				$z .= $i."</legend>\n";
				$z .= $image->edit_form($i.'  ',$this->id,'articleID','articles',URL.$action_page.$this->id.'/'.append_url());
				$z .= $i."</fieldset>\n";
			} else {
				$z .= $i.'  <input class="article_button" type="submit" name="submit" value="Edit '.ucwords(ARTICLES_NAME_SINGULAR).'" /><br /><br /><input class="article_button" type="submit" name="submit" value="Delete '.ucwords(ARTICLES_NAME_SINGULAR).'" />'."\n";
				$z .= $i."</form>\n";
				$z .= $i."</fieldset>\n";
			}
		} else {
			if (ENABLE_IMAGES) {
				if (!isset($_POST['submit']) or $_POST['submit'] == 'Delete') $image->clear();
				$z .= $i."  Add an image to your offer or request:<br /><br />\n";
				$z .= $image->form_html($i.'  ',$type,0);
			}
			$z .= $i.'  <input class="article_button" type="submit" name="submit" value="Add '.ucwords(ARTICLES_NAME_SINGULAR).'" />'."\n";
			$z .= $i."</form>\n";
			$z .= $i."</fieldset>\n";
		}
		$z .= $i.'<!-- /article_form -->'."\n";
		return $z;
	
	}
	
	function get_art_cats() {
		$mysql = new mysql;
		if ($mysql->build_array('SELECT * FROM article_categories ORDER BY art_cat ASC')) $this->art_cats = $mysql->result;
	}
	
	function art_cat_name($id) {
		$mysql = new mysql;
		if ($mysql->result('SELECT art_cat FROM article_categories WHERE art_catID = '.$id.' LIMIT 1')) $this->art_cat_name = $mysql->result['art_cat'];
	}
	
	function add() {
		$mysql = new mysql;
		global $date;
		$insert = array();
		
		$insert[0]['name'] = 'accountID';
		$insert[0]['value'] = $_SESSION["member_id"];
		
		$insert[1]['name'] = 'day';
		$insert[1]['value'] = $date['day'];
		$insert[2]['name'] = 'month';
		$insert[2]['value'] = $date['month'];
		$insert[3]['name'] = 'year';
		$insert[3]['value'] = $date['year'];
				
		$insert[4]['name'] = 'title';
		$insert[4]['value'] = mysql_real_escape_string($this->title);
		$insert[5]['name'] = 'blurb';
		$insert[5]['value'] = mysql_real_escape_string($this->blurb);
		$insert[6]['name'] = 'body';
		$insert[6]['value'] = mysql_real_escape_string($this->body);
		$insert[7]['name'] = 'article_categoryID';
		$insert[7]['value'] = $this->article_category;
		
		if (VALIDATE_ARTICLES and user_type() !=2) {
			$insert[8]['name'] = 'validated';
			$insert[8]['value'] = '0';
		} else {
			$insert[8]['name'] = 'validated';
			$insert[8]['value'] = '1';		
		}
		
		if (ENABLE_IMAGES and $this->image_id) {
			$insert[9]['name'] = 'imageID';
			$insert[9]['value'] = $this->image_id;
		} 
		
		if (!$mysql->insert_values('articles',$insert)) {
			$this->error = $mysql->error;
			return false;
		} else {
			$this->id = $mysql->inserted_id;
			$this->added = true;
			return true;
		}
	}
	function categories_html($i,$url) {
		$mysql = new mysql;
		// form html
		$z = $i."<!-- article_categories_form -->\n";
		$z .= $i."<h3>".ucwords(ARTICLES_NAME_SINGULAR)." Categories</h3>\n";
		$z .= $i."<fieldset>\n";
		$z .= $i."<form name=\"article_categories\" action=\"".URL.$url.append_url($url)."\" method=\"post\">\n";
		$z .= $i." <input type=\"hidden\" name=\"article\" value=\"1\" />\n";
		$z .= $i." <select id=\"category\" name=\"category\" onclick=\"set_article_buttons();\">\n";
		
		// javascript
		$j = 'function set_article_buttons() {'."\n";
		$b=0;
		foreach ($this->art_cats as $category) {
			$z .= $i."  <option value=\"".$category['art_catID']."\"".selected('','',$category['art_catID'],0).">".ucwords($category['art_cat'])."</option>\n";
			$mysql->num_rows('SELECT articleID FROM articles WHERE article_categoryID = '.$category['art_catID'].' LIMIT 1');
			$j .= "\t".'if (document.article_categories.category.selectedIndex == '.($b).') {'."\n";
			if ($mysql->num_rows) {
				$j .= "\t\t".'document.article_categories.article_delete_button.disabled = true;'."\n";
			} else {
				$j .= "\t\t".'document.article_categories.article_delete_button.disabled = false;'."\n";
			}
			$j .= "\t\t".'document.article_categories.name.value = "'.ucwords($category['art_cat']).'";'."\n";
			$j .= "\t"."}\n";
			$b++;
		}
		$j .= "}\n";
		
		$z .= $i." </select>\n";
		$z .= $i." <input type=\"text\" name=\"name\" id=\"name\" value=\"\" /><br class=\"left\" />\n";
		$z .= $i." <input id=\"article_add_button\" type=\"submit\" name=\"submit\" value=\"Add Category\" />\n";
		$z .= $i." <input id=\"article_delete_button\" type=\"submit\" name=\"submit\" value=\"Delete Category\" />\n";
		$z .= $i." <input id=\"article_edit_button\" type=\"submit\" name=\"submit\" value=\"Edit Category\" />\n";
		$z .= $i."</form>\n";
		$z .= $i."</fieldset><br />\n";
		$z .= $i."<!-- /article_categories_form -->\n";
		$this->category_javascript = $j;
		return $z;
	}
	function article_list ($i,$id,$url) {
		if (!isset($_GET['article_id'])) $_GET['article_id'] = 0;
		$mysql = new mysql;
		global $links;
		if ($mysql->build_array('SELECT * FROM articles WHERE accountID = '.$id)) {
			if (!$mysql->num_rows) return '';
			$z = $i."<!-- member_article_list -->\n";
			$z .= $i."<div id=\"member_article_list\">\n";
			$z .= $i."<span class=\"article_list_title\">List of ".ucwords(ARTICLES_NAME_PLURAL)."</span><br /><br />\n";
			foreach ($mysql->result as $article) {
				$this->art_cat_name($article['article_categoryID']);
				$z .= $i.' <span class="member_article_details">';
				if ($article['articleID'] == $_GET['article_id']) $z .= '<strong>';
				$z .= '<a class="member_article_details_link" href="'.URL.$url.$article['articleID'].'/'.append_url(0).'">'.$article['title'].'</a>';
				if ($article['articleID'] == $_GET['article_id']) $z .= '</strong>';
				if (!$article['validated']) {
					$z .= ' (<em>Not yet validated</em>) ';
				}
				
				$z .= ' Submitted '.return_month($article['month']).' '.$article['day'].', '.$article['year'].' in <span class="article_category_detail">'.$this->art_cat_name.'</span> (<a href="'.URL.ARTICLES_URL.'/'.$article['articleID'].'/'.append_url(0).'">view</a>)'."</span><br />\n";
				
			}
			$z .= $i." <br />\n";
			$z .= $i."</div>\n";
			$z .= $i."<!-- /member_article_list -->\n";
			return $z;
		} else {
			// document		
		}	
	}
	function confirm_deletion($i,$id,$url) {
		global $links;
		$mysql = new mysql;
		
		$message = '';
		if ($mysql->num_rows('SELECT imageID FROM images WHERE articleID = '.$id)) {
			if ($mysql->num_rows) $message .= $mysql->num_rows.' images will be deleted!<br />';
		}
		if ($mysql->num_rows('SELECT commentID FROM comments WHERE articleID = '.$id)) {
			if ($mysql->num_rows) $message .= $mysql->num_rows.' comments will be deleted!<br />';
		}
		
		$z = $i.'<div class="article_form">'."\n";
		$z .= $i.' Are you sure you want to delete '.ucwords(ARTICLES_NAME_SINGULAR).' #'.$id."?<br />\n";
		if ($message) {
			$z .= $i.' '.indent_variable($i.'  ',$message)."<br />\n";
		} else {
			$z .= $i." <br />\n";
		}
		$z .= $i.' <form name="article" method="post" enctype=\"multipart/form-data\" action="'.URL.$url.$id.'/'.append_url().'">'."\n";
		$z .= $i.'  <input type="hidden" name="article_id" value="'.$id.'" />'."\n";
		$z .= $i.'  <input type="hidden" name="deletion_confirmed" value="1" />'."\n";
		$z .= $i.'  <input class="article_button" type="submit" name="submit" value="Delete '.ucwords(ARTICLES_NAME_SINGULAR).'" /><br /><br />'."\n";
		$z .= $i.'  <input class="article_button" type="submit" name="submit" value="Cancel" />'."\n";
		$z .= $i.' </form>'."\n";
		$z .= $i.'</div>'."\n";
		
		return $z;
	}
	
	function search_form($i,$format,$keyword,$limit,$start = 0,$member_id = 0,$category_id = 0,$month = 0,$year = 0,$day = 0,$orderby,$orderdir) {
		if (ENABLE_IMAGES) global $image;
		$z = $i."<!-- articles_search_form -->\n";
		$z = $i."<div id=\"articles_search_form\">\n";
		$z .= $i." <fieldset>\n";
		$z .= $i." <legend>\n";
		$z .= $i." Search Parameters\n";
		$z .= $i." </legend>\n";
		$z .= $i." <form id=\"article_search\" name=\"article_search\" action=\"".URL.ARTICLES_URL.'/'.append_url()."\" method=\"post\">\n";
		
		$z .= $i."  <div class=\"article_search\">\n";
		$keyword = htmlentities($keyword);
		$z .= $i."   Search Term:<br /><input type=\"text\" name=\"keyword\" id=\"keyword\" value=\"".$keyword."\" />\n";
		$z .= $i."  </div>\n";
		
		$z .= $i."  <div class=\"article_search\">\n";
		$z .= $i."   Category:<br />\n";
		$z .= $i."   <select id=\"category\" name=\"category\">\n";
		if ($category_id) {
			$z .= $i."    <option value=\"0\">All</option>\n";
		} else {
			$z .= $i."    <option value=\"0\" selected=\"selected\">All</option>\n";
		}
		foreach ($this->art_cats as $category) {
			$z .= $i."    <option value=\"".$category['art_catID']."\"".selected('',' ',$category['art_catID'],$category_id).">".ucwords($category['art_cat'])."</option>\n";
		}
		$z .= $i."  </select>\n";
		$z .= $i."  </div>\n";
		
		$z .= $i."  <div class=\"article_search\">\n";
		$z .= $i."   ".ucwords(MEMBERS_NAME_SINGULAR)." ID:<br /><input type=\"text\" name=\"member\" id=\"member\" value=\"".$member_id."\" />\n";
		$z .= $i."  </div>\n";
		
		$z .= $i."  <div class=\"article_search\">\n";
		$z .= $i."   <br /><select id=\"format\" name=\"format\">\n";
		$z .= $i."    <option value=\"1\"".check_selected(1,$format).">Title and Summary</option>\n";
		$z .= $i."    <option value=\"2\"".check_selected(2,$format).">Title Only</option>\n";
		$z .= $i."    <option value=\"3\"".check_selected(3,$format).">Full ".ucwords(ARTICLES_NAME_PLURAL)."</option>\n";
		$z .= $i."   </select><br class=\"left\" />\n";
		$z .= $i."  </div><br class=\"left\" />\n";
		
		$z .= $i."  <div class=\"article_search\">\n";
		$z .= date_form($i.'   ',array('day' => $day, 'month' => $month, 'year' => $year),'',true,'false','article_search');
		$z .= $i."  </div>\n";
		
		$z .= $i."  <div class=\"article_search\">\n";
		$z .= $i."   Show:<br />\n";
		$z .= $i."  <select id=\"limit\" name=\"limit\">\n";
		$z .= $i."   <option value=\"10\"".check_selected(10,$limit).">10</option>\n";
		$z .= $i."   <option value=\"25\"".check_selected(25,$limit).">25</option>\n";
		$z .= $i."   <option value=\"50\"".check_selected(50,$limit).">50</option>\n";
		$z .= $i."   <option value=\"100\"".check_selected(100,$limit).">100</option>\n";
		$z .= $i."  </select>\n";
		$z .= $i."  </div>\n";
		
		$z .= $i."  <div class=\"article_search\">\n";
		$z .= $i."   Order By:<br />\n";
		$z .= $i."  <select id=\"orderby\" name=\"orderby\">\n";
		$z .= $i."   <option value=\"category\"".check_selected('category',$orderby).">Category</option>\n";
		$z .= $i."   <option value=\"title\"".check_selected('title',$orderby).">Title</option>\n";
		$z .= $i."   <option value=\"posted\"".check_selected('posted',$orderby).">Date Posted</option>\n";
		$z .= $i."   <option value=\"member\"".check_selected('member',$orderby).">".ucwords(MEMBERS_NAME_SINGULAR)."</option>\n";
		$z .= $i."  </select>\n";
		$z .= $i."  <select id=\"orderdir\" name=\"orderdir\">\n";
		$z .= $i."   <option value=\"ASC\"".check_selected('ASC',$orderdir).">Ascending</option>\n";
		$z .= $i."   <option value=\"DESC\"".check_selected('DESC',$orderdir).">Descending</option>\n";
		$z .= $i."  </select>\n";
		$z .= $i."  </div>\n";
		
		
		$z .= $i."  <div class=\"article_search\">\n";
		$z .= $i."   <br /><input id=\"article_button\" type=\"submit\" name=\"submit\" value=\"Search\" />\n";
		$z .= $i."  </div>\n";
		
		$z .= $i." </form>\n";
		$z .= $i." </fieldset><br />\n";
		$z .= $i."</div>\n";
		$z .= $i."<!-- /articles_search_form -->\n";
		return $z;
	}
	
	function append_search_url($keyword,$limit,$format,$member_id,$category_id,$orderby,$orderdir,$day,$month,$year) {
		$z = '';
		if ($keyword) {
			$keyword = htmlentities($keyword);
			$z .= '&keyword='.str_replace(' ','_',$keyword);
		}
		if ($limit) $z .= '&limit='.$limit;
		if ($format) $z .= '&format='.format;
		if ($member_id) $z .= 'member='.$member_id;
		if ($category_id) $z .= '&category='.$category_id;
		if ($orderby) $z .= '&orderby='.$orderby;
		if ($orderdir) $z .= '&orderdir='.$orderdir;
		if ($day) $z .= '&day='.$day;
		if ($month) $z .= '&month='.$month;
		if ($year) $z .= '&year='.$year;
		return $z;
	}
	function num_comments($id) {
		$mysql = new mysql;
		if ($mysql->num_rows('SELECT commentID FROM comments WHERE articleID = '.$id)) {
			return $mysql->num_rows;
		} else {
			return 0;
		}
	}
	function xhtml($i,$format,$keyword,$limit,$start = 0,$member_id = 0,$category_id = 0,$month = 0,$year = 0,$day = 0,$orderby,$orderdir,$show_results = false,$unvalidated = false) {
		if (ENABLE_IMAGES) global $image;
		global $user;
		global $links;
		$mysql = new mysql;
		
		// ******************************************
		// develop query from function call
		// ******************************************
		if (!$start) $start = 0;
		$query = "SELECT * FROM articles,article_categories WHERE ";
		$conditions = '';
		
		if ($member_id) {
			$conditions .= 'articles.accountID = '.$member_id.' AND ';
		}
		if ($category_id) {
			$conditions .= 'articles.article_categoryID = '.$category_id.' AND ';
		}
		if ($month) {
			$conditions .= 'articles.month = '.$month.' AND ';
		}
		if ($year) {
			$conditions .= 'articles.year = '.$year.' AND ';
		}
		if ($day) {
			$conditions .= 'articles.day = '.$day.' AND ';
		}
		if ($keyword) {
			$keywords = search_terms($keyword);
			$num_searches = count($keywords);
			if ($num_searches > 1) {
				$ii = 1;
				$conditions .= '(';
				foreach($keywords as $search_term) {
					$conditions .= "(articles.title LIKE '%".$search_term."%' OR articles.blurb LIKE '%".$search_term."%' OR articles.body LIKE '%".$search_term."%')";
					if ($ii <  $num_searches) {
						$conditions .= ' OR ';
					}
					$ii++;
				}
				$conditions .= ') AND ';
			} else {
				$conditions .= "(articles.title LIKE '%".$keywords[0]."%' OR articles.blurb LIKE '%".$keywords[0]."%' OR articles.body LIKE '%".$keywords[0]."%') AND ";
			}
		}
		$query .= $conditions.' articles.article_categoryID = article_categories.art_catID ';
		
		// ************************************************************************
		// function can be used to display un-validated articles for review by admin
		// ************************************************************************
		if (VALIDATE_ARTICLES and $unvalidated) {
			$query .= ' AND articles.validated = 0 ';
		} else {
			$query .= ' AND articles.validated = 1 ';
		}
		
		if (!$orderby or ($orderby == 'category')) {
			if (!$orderdir or $orderdir == 'ASC') {
				$query .= 'ORDER BY article_categories.art_cat';
			}
			if ($orderdir == 'DESC') {
				$query .= 'ORDER BY article_categories.art_cat DESC';
			}
		} elseif ($orderby == 'title') {
			if (!$orderdir or $orderdir == 'ASC') {
				$query .= 'ORDER BY articles.title';
			}
			if ($orderdir == 'DESC') {
				$query .= 'ORDER BY articles.title DESC';
			}
		} elseif ($orderby == 'member') {
			if (!$orderdir or $orderdir == 'ASC') {
				$query .= 'ORDER BY articles.accountID';
			}
			if ($orderdir == 'DESC') {
				$query .= 'ORDER BY articles.accountID DESC';
			}
		} else {
			if (!$orderdir or $orderdir == 'ASC') {
				$query .= 'ORDER BY articles.year, articles.month, articles.day';
			}
			if ($orderdir == 'DESC') {
				$query .= 'ORDER BY articles.year DESC, articles.month DESC, articles.day DESC';
			}
		}
		
		// ******************************************
		// got query, test and build array
		// ******************************************
		$z = '';
		if (!$mysql->build_array($query)) {
			log_error('Failed a query in class articles, function xhtml:<br />'.$mysql->error);
		} else {
			if (!$mysql->num_rows) {
				if ($show_results) {
					return $i." No ".strtolower(ARTICLES_NAME_PLURAL)." found<br /><br />\n";
				} else {
					return '';
				}
			}
			$z .= $i."<!-- articles_xhtml -->\n";
			$num_result = $mysql->num_rows;
			if ($num_result > $limit) {
				$query .= ' LIMIT '.$start.','.$limit;
				if (!$mysql->build_array($query)) {
					return $mysql->error; 
				} else {
					$notices = $mysql->result;
					if ($show_results) {
						if (($limit + $start) > $num_result) {
							$z .= $i.'Showing Results '.($start + 1).' to '.$num_result.' of '.$num_result."<br /><br />\n";
						} else {
							$z .= $i.'Showing Results '.($start + 1).' to '.($limit + $start).' of '.$num_result."<br /><br />\n";
						}
						
						$num_pages = ceil($num_result/$limit);
						
						if ($num_pages > 1) {
							if ($start > $limit) {
								$page = ceil($start/$limit) + 1;
							} elseif ($start == $limit) {
								$page = 2;
							} else {
								$page = 1;
							}
							
							for($p=1;$p<($num_pages + 1);$p++) {
								if ($p != $page) {
									$z .= '<a href="'.URL.ARTICLES_URL.'/?start='.(($p * $limit) - $limit).$this->append_search_url($keyword,$limit,$request,$offer,$member_id,$amount,$amount_above,$item,$service,$category_id,$auction,$orderby,$orderdir).append_url(' ?').'">'.$p.'</a> ';
								} else {
									$z .= $p.' ';
								}
							}
							$z .= $i."<br /><br />\n";
						}
					}
				}
			} else {
				$articles = $mysql->result;
				if ($show_results) $z .= $i.'Found '.$num_result." ".strtolower(ARTICLES_NAME_PLURAL)."<br /><br />\n";
			}
		}
		
		// ******************************************
		// array of articles built. display according to format
		// ******************************************
		$z .= $i."<div id=\"articles\">\n";
		foreach($articles as $article) {
			$this->art_cat_name($article['article_categoryID']);
			// highlight search hits
			if ($keyword) {
				if ($num_searches > 1) {
					foreach($keywords as $search_term) {
						$title = highlight($article['title'],$search_term);
						$article['blurb'] = highlight($article['blurb'],$search_term);
						$article['body'] = highlight($article['body'],$search_term);
					}
				} else {
					$title = highlight($article['title'],$keyword);
					$article['blurb'] = highlight($article['blurb'],$keyword);
					$article['body'] = highlight($article['body'],$keyword);
				}
			} else {
				$title = $article['title'];
			}
			// *************************************
			// all formats get the same title and details
			// they have seperate css files for customizing
			// *************************************
			$z .= $i.' <span class="article_title"><a href="'.URL.ARTICLES_URL.'/'.$article['articleID'].'/'.append_url(0).'">'.$title."</a></span><br />\n";
			$z .= $i." <span class=\"article_details\">Posted ".return_month($article['month']).' '.$article['day'].', '.$article['year'].' by ';
			if (user_type() == 0 or $_SESSION["member_validated"] == 0 or $_SESSION["member_suspended"] == 1) {
				$z .= '<a href="'.URL.MEMBER_LIST_URL.'/'.$article['accountID'].'/">'.ucwords(MEMBERS_NAME_SINGULAR).' '.$article['accountID']."</a>";
			} elseif (user_type() == 1 or user_type() == 2) {
				$z .= '<a href="'.URL.MEMBER_LIST_URL.'/'.$article['accountID'].'/'.append_url(0).'">'.$user->full_name($article['accountID'])."</a>";
			}
			$z .= " in <a href=\"".URL.ARTICLES_URL.'/?category='.$article['article_categoryID'].append_url(' ?')."\">".$this->art_cat_name."</a>";
			if (ENABLE_COMMENTS) {
				$num_comments = $this->num_comments($article['articleID']);
				if ($num_comments) {
					if ($num_comments == 1) {
						$z .= ' (1 '.strtolower(COMMENT_NAME_SINGULAR).')';
					} else {
						$z .= ' ('.$num_comments.' '.strtolower(COMMENT_NAME_PLURAL).')';
					}
				}
			}
			if (isset($_SESSION['member_id'])) {
				if (($article['accountID'] == $_SESSION['member_id'] or user_type() == 2) and $links->build_url(1,7)) {
					$z .= ' (<a href="'.URL.$links->complete_url.$article['articleID'].'/'.append_url(0).'">edit</a>)';			
				}
			}
			$z .= "</span><br /><br />\n";
		
			if ($format == 1) { // *********** Titles and summary *****************
				// get thumbnail if possible
				$image_check = false;
				if (ENABLE_IMAGES) {
					if($article['imageID']) {
						if ($image->img($article['imageID'],'t',$article['title'],'article_thumb')) {
							$z .= $i.'<div class="article_image_holder">'."\n";
							$z .= $i.' '.$image->img."<br />\n";
							if ($image->title) {
								$z .= $i.' '.$image->title."<br />\n";
							}
							$z .= $i."</div>\n";
							$image_check = true;
						}
					}
				}
				// blurb
				$z .= $i." <span class=\"article_blurb\">\n".indent_variable($i.' ',$article['blurb'])."\n".$i." </span><br /><br class=\"left\" />\n";
			} elseif ($format == 3) { // *********** Full Articles *****************
				if (ENABLE_IMAGES) {
					if($article['imageID']) {
						if ($image->img($article['imageID'],'p',0,'article_thumb')) {
							$z .= $i." <div id=\"article_page_image_holder\" >\n";
							$z .= $i.'  <a href="/images/'.$image->name.'.png" target="_blank">'.$image->img."</a><br />\n";
							if ($image->blurb) {
								$z .= $i.' '.$image->blurb."<br />\n";
							}
							$z .= $i." </div>\n";
							if ($image->description) {
								$z .= $i.' <span class="image_description">'.$image->description."</span><br />\n";
							}
							
							$z .= $i." <br class=\"left\" /><br />\n";
						}
						

						if ($mysql->build_array('SELECT * FROM images WHERE articleID = '.$article['articleID'])) {
							if ($mysql->num_rows > 1) {
								$z .= $image->thumbs($i.' ',$mysql->result,URL.ARTICLES_URL.'/'.$article['articleID'].'/').$i." <br /><br class=\"left\"/>\n";
							}
						}
					}
				}
				$z .= $i." <span class=\"article_body\">\n".indent_variable($i.' ',$article['body'])."\n".$i." </span><br /><br />\n";
			}
			// ************************************************************************
			// function can be used to display un-validated articles for review by admin
			// ************************************************************************
			if (VALIDATE_ARTICLES and $unvalidated and $links->build_url(1,105)) {
				$z .= $i."<form id=\"validate_article\" name=\"validate_article\" action=\"".URL.$links->complete_url.append_url()."\" method=\"post\">\n";
				$z .= $i."  <input id=\"validate_article_button\" type=\"submit\" name=\"submit\" value=\"Validate\" /><br />\n";
				$z .= $i."  <input id=\"validate_article_button\" type=\"submit\" name=\"submit\" value=\"Delete\" /><br /><br />\n";
				$z .= $i."  <input type=\"hidden\" name=\"article_id\" value=\"".$article['articleID']."\" /><br />\n";
				$z .= $i."</form>\n";
			}		
		}
		$z .= $i."</div>\n";
		$z .= $i."<!-- /articles_xhtml -->\n";
		return $z;
	}
	
	function page_html($i,$id) {
		if (!$this->build_article($id)) {
			return 'Could not display '.ucwords(ARTICLES_NAME_SINGULAR);
		}
		
		if (ENABLE_IMAGES) global $image;
		global $user;
		$mysql = new mysql;
		global $style;
		global $links;
		
		$z = $i."<!-- article_page -->\n";
		$z .= $i."<div id=\"article_page\">\n";
		$z .= $i."<h2>".$this->title."</h2>\n";
		$z .= $i." <span class=\"article_details\">Posted ".return_month($this->month).' '.$this->day.', '.$this->year.' by ';
		if (user_type() == 0 or $_SESSION["member_validated"] == 0 or $_SESSION["member_suspended"] == 1) {
			$z .= '<a href="'.URL.MEMBER_LIST_URL.'/'.$this->member_id.'/">'.ucwords(MEMBERS_NAME_SINGULAR).' '.$this->member_id."</a>";
		} elseif (user_type() == 1 or user_type() == 2) {
			$z .= '<a href="'.URL.MEMBER_LIST_URL.'/'.$this->member_id.'/'.append_url(0).'">'.$user->full_name($this->member_id)."</a>";
		}
		$z .= " in <a href=\"".URL.ARTICLES_URL.'/?category='.$this->article_category.append_url(' ?')."\">".$this->art_cat_name."</a>";
		if (isset($_SESSION["member_id"])) {
			if (($this->member_id == $_SESSION['member_id'] or user_type() == 2) and $links->build_url(1,7)) {
				$z .= ' (<a href="'.URL.$links->complete_url.$this->id.'/'.append_url(0).'">edit</a>)';			
			}
		}
		$z .= "</span><br /><br />\n";
		if (ENABLE_IMAGES) {
			if($this->image_id) {
				$image_id = '';
				if (strpos(' '.$_SERVER['REQUEST_URI'],'image=')) {
					$y = explode('image=',$_SERVER['REQUEST_URI']);
					$image_id = $y[1];
				} else {
					$image_id = $this->image_id;
				}
				if ($image->img($image_id,'p',0,0)) {
					$z .= $i." <div id=\"article_page_image_holder\" >\n";
					$z .= $i.'  <a href="/images/'.$image->name.'.png" target="_blank">'.$image->img."</a><br />\n";
					if ($image->blurb) {
						$z .= $i.' '.$image->blurb."<br />\n";
					}
					$z .= $i." </div>\n";
					if ($image->description) {
						$z .= $i.' <span class="image_description">'.$image->description."</span><br />\n";
					}
					
					$z .= $i." <br class=\"left\" /><br />\n";
				}
				if ($mysql->build_array('SELECT * FROM images WHERE articleID = '.$this->id)) {
					if ($mysql->num_rows > 1) {
						$z .= $image->thumbs($i.' ',$mysql->result,URL.ARTICLES_URL.'/'.$this->id.'/').$i." <br /><br class=\"left\"/>\n";
					}
				}
			}
		}
		$z .= $i." <span class=\"article_body\">\n".indent_variable($i.' ',$this->body)."\n".$i." </span><br /><br />\n";
		$z .= $i."</div>\n";
		$z .= $i."<!-- /article_page -->\n";
		return $z;
	}
	function sidebar($i,$heading) {
		$mysql = new mysql;
		$mysql->build_array('SELECT * FROM articles WHERE validated = 1 ORDER BY year DESC, month DESC, day DESC');
		if (is_array($mysql->result)) {
			$z = $i."<!-- articles_sidebar -->\n";
			$z .= $i.'<div id="articles_sidebar">'."\n";
			$z .= $i.'<span class="sidebar_heading">'.$heading."</span><br />\n";
			foreach ($mysql->result as $article) {
				$z .= $i.'<span class="sidebar_title"><a href="'.URL.ARTICLES_URL.'/'.$article['articleID'].'/'.append_url(0).'">'.$article['title'].'</a> by '.first_name($article['accountID'])."</span><br />\n";
				$date_difference = date_difference(array('year' => $article['year'], 'month' => $article['month'], 'day' => $article['day']),array('year' => $GLOBALS['date']['year'], 'month' =>  $GLOBALS['date']['month'], 'day' =>  $GLOBALS['date']['day'], 'hour' => $GLOBALS['date']['hour']));
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
				$this->art_cat_name($article['article_categoryID']);
				$z .= $i.'<span class="sidebar_details">Posted '.$date_difference.' in <a href="'.URL.ARTICLES_URL.'/?category='.$article['article_categoryID'].append_url(' ?').'">'.$this->art_cat_name."</a></span><br /><br />\n";
			}
		} else {
			return '';
		}
		$z .= $i.'</div>'."\n";
		$z .= $i."<!-- /articles_sidebar -->\n";
		return $z;
	}
}











?>