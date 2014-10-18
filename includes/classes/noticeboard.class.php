<?
 class noticeboard {
 
 	var	$id,
		$member_id,
		
		$created_day,
		$created_month,
		$created_year,
		$expiry_day,
		$expiry_month,
		$expiry_year,
 
 		$image_id,
		$image_thumb,
		$image_main,
		$image_big,
		
		$url,
		$title,
		$blurb,
		$description,
		$amount,
		$reserve,
		$fast_delete,
		
		$request,
		$item,
		$bought,
		
		$categories,
		$category_id,
		
		$title_required,
		$blurb_required,
		$image_required,
		$description_required,
		$amount_required,
		
		$auction_winning_member_id,
		$auction_winning_amount,
		$num_bids,
		$bids,
		
		$error,
		$title_failed,
		$example,
		
		$set_expiry = 0,
		$category_javascript,
		$type,
		$expired;
		
	function noticeboard() {
		$this->set_required_variables();
		$this->get_categories();
		$this->finish_auctions();
	}
	function clear() {
		$this->image_id = '';
		$this->title = '';
		$this->amount = '';
		$this->blurb = '';
		$this->description = '';
		$this->type = '';
		$this->item = '';
		$this->category_id = '';
		$this->reserve = '';
		$this->fast_delete = '';
	}
 	function set_required_variables() {
		$mysql = new mysql;
		$mysql->result('SELECT noticeboard_title_required, noticeboard_blurb_required, noticeboard_description_required, noticeboard_amount_required FROM config');
		$this->title_required = $mysql->result['noticeboard_title_required'];
		$this->blurb_required = $mysql->result['noticeboard_blurb_required'];
		$this->description_required = $mysql->result['noticeboard_description_required'];
		$this->amount_required = $mysql->result['noticeboard_amount_required'];
	
	}
	
	function info($id) {
		$mysql = new mysql;
		
		if (!$mysql->result('SELECT * FROM noticeboard WHERE noticeboardID = '.$id.' LIMIT 1')) {
			$this->error = $mysql->error;
			return false;
		}
		
		$this->id = 						$mysql->result['noticeboardID'];
		$this->member_id = 					$mysql->result['accountID'];
		$this->request = 					$mysql->result['request'];
		$this->created_day = 				$mysql->result['created_day'];
		$this->created_month = 				$mysql->result['created_month'];
		$this->created_year = 				$mysql->result['created_year'];
		$this->expiry_day = 				$mysql->result['expiry_day'];
		$this->expiry_month = 				$mysql->result['expiry_month'];
		$this->expiry_year = 				$mysql->result['expiry_year'];
		$this->expiry_hour = 				$mysql->result['expiry_hour'];
		$this->image_id = 					$mysql->result['imageID'];
		$this->title = 						$mysql->result['title'];
		$this->amount = 					$mysql->result['amount'];
		$this->blurb = 						$mysql->result['blurb'];
		$this->description = 				$mysql->result['description'];
		$this->type = 						$mysql->result['type'];
		$this->item = 						$mysql->result['item'];
		$this->category_id = 				$mysql->result['categoryID'];
		$this->bought = 					$mysql->result['bought'];
		$this->reserve = 					$mysql->result['reserve'];
		$this->expired = 					$mysql->result['expired'];
		$this->fast_delete = 				$mysql->result['quick_delete'];
		
		if ($this->type == 2 or ($this->expiry_day and $this->expiry_month and $this->expiry_year)) $this->set_expiry = 1;
		
		return true;
	}
	
	function add($member_id,$image_id = 0) {
		$mysql = new mysql;
		global $date;
		
		$insert = array();
		$insert[0]['name'] = 'accountID';
		$insert[0]['value'] = $member_id;
		
		$insert[1]['name'] = 'created_day';
		$insert[1]['value'] = $date['day'];
		$insert[2]['name'] = 'created_month';
		$insert[2]['value'] = $date['month'];
		$insert[3]['name'] = 'created_year';
		$insert[3]['value'] = $date['year'];
				
		$insert[4]['name'] = 'expiry_day';
		$insert[4]['value'] = $this->expiry_day;
		$insert[5]['name'] = 'expiry_month';
		$insert[5]['value'] = $this->expiry_month;
		$insert[6]['name'] = 'expiry_year';
		$insert[6]['value'] = $this->expiry_year;
		$insert[7]['name'] = 'expiry_hour';
		$insert[7]['value'] = $this->expiry_hour;
		
		
		$insert[8]['name'] = 'title';
		$insert[8]['value'] = mysql_escape_string($this->title);
		$insert[9]['name'] = 'blurb';
		$insert[9]['value'] = mysql_escape_string($this->blurb);
		$insert[10]['name'] = 'description';
		$insert[10]['value'] = mysql_escape_string($this->description);
		
		$insert[11]['name'] = 'type';
		$insert[11]['value'] = $this->type;
		$insert[12]['name'] = 'item';
		$insert[12]['value'] = $this->item;
		$insert[13]['name'] = 'categoryID';
		$insert[13]['value'] = $this->category_id;
		
		
		$insert[14]['name'] = 'reserve';
		$insert[14]['value'] = $this->reserve;
		$insert[15]['name'] = 'amount';
		$insert[15]['value'] = mysql_escape_string($this->amount);
		
		$insert[16]['name'] = 'request';
		$insert[16]['value'] = $this->request;
		
		$insert[17]['name'] = 'quick_delete';
		$insert[17]['value'] = $this->fast_delete;
		
		if (ENABLE_IMAGES) {
			if (!empty($image_id)) {
				$insert[18]['name'] = 'imageID';
				$insert[18]['value'] = $image_id;
			}
		}
		
		if (!$mysql->insert_values('noticeboard',$insert)) {
			$this->error = $mysql->error;
			return false;
		} else {
			$this->id = $mysql->inserted_id;
			return true;
		}
		
	}
	
	function expire($id) {
		$mysql = new mysql;
		if (!$mysql->query('UPDATE noticeboard SET expired = 1 WHERE noticeboardID = '.$id.' LIMIT 1')) {
			$this->error = $mysql->error;
			return false;
		}
		return true;
	}
	
	function bought($id) {
		$mysql = new mysql;
		if (!$mysql->query('UPDATE noticeboard SET bought = 1 WHERE noticeboardID = '.$id.' LIMIT 1')) {
			$this->error = $mysql->error;
			return false;
		}
		return true;
	}
	
	function set_default_image($id,$image_id) {
		$mysql = new mysql;
		if (!$mysql->query('UPDATE noticeboard SET imageID = '.$image_id.' WHERE noticeboardID = '.$id.' LIMIT 1')) {
			$this->error = $mysql->error;
			return false;
		}
		return true;
	}
	
	function rebuild_default_image($id) {
		$mysql = new mysql;
		if ($mysql->result('SELECT imageID FROM images WHERE noticeboardID = '.$id.' LIMIT 1')) {
			$this->set_default_image($id,$mysql->result['imageID']);
		}		
	}
	
	function edit() {
		if (user_type() == 1 and ($this->member_id != $_SESSION["member_id"])) {
			echo $this->member_id.'-';
			$this->error = 'You cannot edit another '.strtolower(MEMBERS_NAME_SINGULAR).'\'s entry';
			return false;		
		}
		
		$mysql = new mysql;
		
		$q = 'UPDATE noticeboard SET ';
		if (!isset($this->expiry_day)) $q .= 'expiry_day = 0, '; else $q .= 'expiry_day = '.$this->expiry_day.', ';
		if (!isset($this->expiry_month)) $q .= 'expiry_month = 0, '; else $q .= 'expiry_month = '.$this->expiry_month.', ';
		if (!isset($this->expiry_year)) $q .= 'expiry_year = 0, '; else $q .= 'expiry_year = '.$this->expiry_year.', ';
		if (!isset($this->expiry_hour)) $q .= 'expiry_hour = 0, '; else $q .= 'expiry_hour = '.$this->expiry_hour.', ';
		if (!empty($this->amount)) $q .= 'amount = \''.mysql_real_escape_string($this->amount).'\', ';
		if (!empty($this->reserve)) $q .= 'reserve = '.mysql_real_escape_string($this->reserve).', ';
		if (!empty($this->title)) $q .= 'title = \''.mysql_real_escape_string($this->title).'\', ';
		if (!empty($this->blurb)) $q .= 'blurb = \''.mysql_real_escape_string($this->blurb).'\', ';
		if (!empty($this->description)) $q .= 'description = \''.mysql_real_escape_string($this->description).'\', ';
		if ($this->fast_delete) {
			$q .= 'quick_delete = 1, ';
		} else {
			$q .= 'quick_delete = 0, ';
		}
		$q .= 'type = '.$this->type.', ';
		$q .= 'item = '.$this->item.', ';
		$q .= 'categoryID = '.$this->category_id.' WHERE noticeboardID = '.$this->id.' LIMIT 1';
		

		if ($mysql->query($q)) {
			return true;
		} else {
			$this->error = $mysql->error;
			return false;
		}
	}
	
	function bid_on($id) {
		$mysql = new mysql;
		if ($mysql->num_rows('SELECT bidID FROM bids WHERE noticeboardID = '.$id.' LIMIT 1')) {
			if ($mysql->num_rows) {
				return true;
			}
		}
		return false;
	}
	
	function delete($id) {
		global $image;
		$mysql = new mysql;
		$this->error = '';
		if ($this->info($id)) {
			if (user_type() == 1 and ($this->member_id != $_SESSION["member_id"])) {
				$this->error = 'You cannot delete another '.strtolower(MEMBERS_NAME_SINGULAR).'\'s entry';
				return false;		
			}
		
		}
		
		if (FREEZE_AUCTION_AFTER_BID) {
			if ($this->bid_on($id)) {
				$this->error = 'Cannot delete auctions once a bid has been made';
				return false;
			}
		}
		if ($mysql->result('SELECT bought, expired FROM noticeboard WHERE noticeboardID = '.$id.' LIMIT 1')) {
			if ($mysql->result['bought'] or $mysql->result['expired']) {
				$this->error = 'Cannot delete expired or bought entries';
				return false;
			}
		}
		
		
		if (!$mysql->query('DELETE FROM noticeboard WHERE noticeboardID = '.$id.' LIMIT 1')) {
			$this->error = $mysql->error;
			return false;
		}
		if (!$image->delete_group('noticeboardID',$id)) {
			$this->error .= $image->error;
		}
		if (!$mysql->query('DELETE FROM comments WHERE noticeboardID = '.$id)) {
			$this->error .= $mysql->error;
		}
		if (!$mysql->query('DELETE FROM bids WHERE noticeboardID = '.$id)) {
			$this->error .= $mysql->error;
		}
		if ($this->error) {
			return false;
		} else {
			return true;
		}
	}
	function get_categories() {
		$mysql = new mysql;
		if ($mysql->build_array('SELECT * FROM categories ORDER BY name ASC')) $this->categories = $mysql->result;
	}
	function category_name($id) {
		$mysql = new mysql;
		if ($mysql->result('SELECT name FROM categories WHERE categoryID = '.$id.' LIMIT 1')) return $mysql->result['name'];	
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
	
	function member_list($i,$data,$url) {
		$z = '';
		$z .= $i."<!-- member_noticeboard_entries -->\n";
		if (ENABLE_IMAGES) global $image;
		global $user;
		
		foreach ($data as $entry) {
			$z .= $i."<div class=\"member_noticeboard_entries\">\n";
			if (ENABLE_IMAGES and $entry['imageID']) {
				if ($image->img($entry['imageID'],'t',$entry['title'],'image_noticeboard_thumb')) {
					$z .= $i." ".$image->img.'<br /><strong>'.$entry['title']."</strong><br \>\n";
				}
			} else {
				$z .= $i." <strong>".$entry['title']."</strong><br \>\n";
			}
			$info = '';
			
			$expired = false;			
			if (($entry['expiry_year'] < $GLOBALS['date']['year'] or ($entry['expiry_year'] == $GLOBALS['date']['year'] and $entry['expiry_month'] < $GLOBALS['date']['month']) or ($entry['expiry_year'] == $GLOBALS['date']['year'] and $entry['expiry_month'] == $GLOBALS['date']['month'] and $entry['expiry_day'] < $GLOBALS['date']['day']) or ($entry['expiry_year'] == $GLOBALS['date']['year'] and $entry['expiry_month'] == $GLOBALS['date']['month'] and $entry['expiry_day'] == $GLOBALS['date']['day'] and $entry['expiry_hour'] < $GLOBALS['date']['hour'])) and ($entry['expiry_year'] and $entry['expiry_month'] and $entry['expiry_day'])) {
				$expired = true;
			}
			
			if ($entry['type'] == 2) {
				if ($expired) {
					$info .= 'Auction expired '.return_month($entry['expiry_month']).' '.$entry['expiry_day'].', '.$entry['expiry_year'].' - '.$entry['expiry_hour'].':00';
				} else {
					$info .= 'Auction expiring in '.date_difference($GLOBALS['date'],array('year' => $entry['expiry_year'], 'month' => $entry['expiry_month'], 'day' => $entry['expiry_day'], 'hour' => $entry['expiry_hour']));
				}
				if ($this->bid_info($entry['noticeboardID'])) {
					if (!$this->num_bids) {
						$info .= ' with no bids in ';
					} else {
						$info .= ' with '.$this->num_bids.' bids ('.$user->full_name($this->auction_winning_member_id).' winning with $'.$this->auction_winning_amount.' '.CURRENCY_NAME.') in ';
					}
				}
			} else {
				if ($entry['request']) $info .= 'Request for '; else $info .= 'Offer of ';
				if ($entry['item']) $info .= ' an item in '; else $info .= ' a service in ';
			}
			$info .= $this->category_name($entry['categoryID']).' created '.return_month($entry['created_month']).' '.$entry['created_day'].', '.$entry['created_year'].'.';
			
			$z .= $i.' '.$info."\n";
			if (!empty($entry['blurb'])) $z .= $i.' <br /><br /><em>'.$entry['blurb']."</em><br \>\n";
			
			$froze = false;
			if (FREEZE_AUCTION_AFTER_BID) {
				if ($entry['type'] == 2) {
					if ($this->bid_on($entry['noticeboardID'])) {
						$froze = true;
					}
				}
			}
			
			if ($froze) {
				$z .= $i.'<a href="'.URL.NOTICEBOARD_URL.'/'.$entry['noticeboardID'].'/'.append_url(0)."\">View</a>\n";
			} else {
				$z .= $i." <a href=\"".URL.$url.$entry['noticeboardID'].'/'.append_url(0).'">Edit</a> or <a href="'.URL.NOTICEBOARD_URL.'/'.$entry['noticeboardID'].'/'.append_url(0)."\">View</a>\n";
			}
			
			if (($entry['expiry_year'] < $GLOBALS['date']['year'] or ($entry['expiry_year'] == $GLOBALS['date']['year'] and $entry['expiry_month'] < $GLOBALS['date']['month']) or ($entry['expiry_year'] == $GLOBALS['date']['year'] and $entry['expiry_month'] == $GLOBALS['date']['month'] and $entry['expiry_day'] < $GLOBALS['date']['day']) or ($entry['expiry_year'] == $GLOBALS['date']['year'] and $entry['expiry_month'] == $GLOBALS['date']['month'] and $entry['expiry_day'] == $GLOBALS['date']['day'] and $entry['expiry_hour'] < $GLOBALS['date']['hour'])) and ($entry['expiry_year'] and $entry['expiry_month'] and $entry['expiry_day'])) {
				$z .= $i."<br /><br /><span class=\"nb_entry_expired\">Expired</span>\n";
			}
			
			$z .= $i."</div>\n";
		}
		
		$z .= $i."<br /><br /><br /><br />\n";
		$z .= $i."<!-- /member_noticeboard_entries -->\n";
		return $z;
	}

	function validate_form ($i) {
		$mysql = new mysql;
		$this->title_failed = 0;
		$post_post = remove_slashes($_POST);
		$message = '';
		
		if (isset($_POST['noticeboard_id'])) $this->id = $_POST['noticeboard_id']; else $this->id = 0;
		
		if ($this->id) {
			if ($mysql->result('SELECT accountID FROM noticeboard WHERE noticeboardID = '.$this->id.' LIMIT 1')) {
				$this->member_id = $mysql->result['accountID'];
			}
		}
		
		if (FREEZE_AUCTION_AFTER_BID) {
			if ($this->bid_on($this->id)) {
				return $i."Cannot edit an auction once a bid has been made<br /><br />\n";
			}
		}
		
		if (isset($post_post['request'])) {
			$this->request = $post_post['request'];
		} else {
			$this->request = 0;
		}
		
		if (isset($post_post['category'])) $this->category_id = $post_post['category']; else return $i."No Category<br /><br />\n";
		
		if (!isset($post_post['title']) and $this->title_required == 1) {
			$message .= $i."A title is required<br />\n";
		} else {
			if (!$post_post['title'] and $this->title_required == 1) {
				$message .= $i."A title is required<br />\n";
			}
			if (VALIDATE_XHTML) {
				$xhtml_report = valid_XHTML($post_post['title']);
				if ($xhtml_report) {
					$message .= $i.$xhtml_report;
				}
			}
			$this->title = remove_bad_tags($post_post['title']);
		}
		if (!isset($post_post['blurb']) and $this->blurb_required == 1) {
			$message .= $i."A short description is required<br />\n";
		} else {
			if (!$post_post['blurb'] and $this->blurb_required == 1) {
				$message .= $i."A short description is required<br />\n";
			}
			if (VALIDATE_XHTML) {
				$xhtml_report = valid_XHTML($post_post['blurb']);
				if ($xhtml_report) {
					$message .= $i.$xhtml_report;
				}
			}
			$this->blurb = remove_bad_tags($post_post['blurb']);
		}
		if (!isset($post_post['description']) and $this->description_required == 1) {
			$message .= $i."A description is required<br />\n";
		} else {
			if (empty($post_post['description']) and $this->description_required == 1) {
				$message .= $i."A description is required<br />\n";
			}
			if (VALIDATE_XHTML) {
				$xhtml_report = valid_XHTML($post_post['description']);
				if ($xhtml_report) {
					$message .= $i.$xhtml_report;
				}
			}
			$this->description = remove_bad_tags($post_post['description']);
		}
		if (!$this->blurb and $this->description) {
			$this->blurb = $this->description;
		}
		if (!$this->description and $this->blurb) {
			$this->description = $this->blurb;
		}
		if (isset($post_post['fast_delete']) and $this->item) {
			$this->fast_delete = $post_post['fast_delete'];
		}
		if (!isset($post_post['amount']) and (($this->amount_required == 1 and !$this->request) or ($this->fast_delete))) {
			$message .= $i."An amount is required<br />\n";
		} else {
			if (empty($post_post['amount']) and $this->amount_required == 1 and !$this->request) {
				$message .= $i."An amount is required<br />\n";
			}
			$this->amount = remove_bad_tags($post_post['amount']);
			$this->amount = str_replace('$','',$this->amount);
			if ($this->fast_delete and !is_numeric($this->amount)) {
				$message .= $i."The amount must be a number<br />\n";
			}
		}
		if (isset($post_post['item'])) {
			$this->item = $post_post['item'];
		} else {
			$this->item = 0;
		}
		if (isset($post_post['type'])) {
			if ($post_post['type'] == 2) {
				$this->type = 2;
				$this->item = 1;
				if (!isset($post_post['set_expiry'])) {
					$message .= $i."Auctions need an expiry date, please double check<br />\n";
				} else {
					$this->set_expiry = 1;
				}
			} else {
				$this->type = 1;
			}
		} else {
			$this->type = 1;
		}		
		if (isset($post_post['reserve']) and $this->type == 2) {
			if (!is_numeric($post_post['reserve'])) $message .= $i."The reserve price must be a number<br />\n";
			$this->reserve = $post_post['reserve'];
		} else {
			$this->reserve = 0;
		}
		
		if (isset($post_post['set_expiry'])) {
			$this->set_expiry = $post_post['set_expiry'];
		} else {
			$this->set_expiry = 0;
		}
		
		if ($this->set_expiry) {
			if (!isset($post_post['expiry_day']) or !isset($post_post['expiry_month']) or !isset($post_post['expiry_year'])) {
				$message .= $i."Expiry date not set<br />\n";
			} elseif ($post_post['expiry_day'] < $GLOBALS['date']['day'] and $post_post['expiry_month'] <= $GLOBALS['date']['month'] and $post_post['expiry_year'] <= $GLOBALS['date']['year']) {
				$message .= $i."Expiry date appears to be in the past<br />\n";
			} elseif (($post_post['expiry_hour'] + 3) < $GLOBALS['date']['hour'] and ($post_post['expiry_day'] == $GLOBALS['date']['day'] and $post_post['expiry_month'] == $GLOBALS['date']['month'] and $post_post['expiry_year'] == $GLOBALS['date']['year'])) {
				$message .= $i."Expiry hour need to be at least a few hours in the future<br />\n";
			} else {
				$this->expiry_day = $post_post['expiry_day'];
				$this->expiry_month = $post_post['expiry_month'];
				$this->expiry_year = $post_post['expiry_year'];
				$this->expiry_hour = $post_post['expiry_hour'];
			}
		} else {
			$this->expiry_day = 0;
			$this->expiry_month = 0;
			$this->expiry_year = 0;
			$this->expiry_hour = 0;
		}
		return $message;
	}
	
	function buy_now_html($i,$amount,$noticeboard_id) {
		$z = $i."<!-- buy_now_html -->\n";
		$z .= $i."<div class=\"buy_now_form\">\n";
		$z .= $i." <form id=\"buy_now\" name=\"buy_now\" action=\"".URL.NOTICEBOARD_URL.'/'.$noticeboard_id.'/'.append_url()."\" method=\"post\" >\n";
		$z .= $i."  <input type=\"text\" name=\"buy_now_amount\" id=\"buy_now_amount\" value=\"".$amount."\" ";
		if (LOCK_BUY_NOW_PRICE) {
			$z .= 'disabled="disabled" ';
		}
		$z .= "/>\n";
		if (LOCK_BUY_NOW_PRICE) {
			$z .= $i."  <input type=\"hidden\" id=\"buy_now_amount\" name=\"buy_now_amount\" value=\"".$amount."\" />\n";
		}
		$z .= $i."  <input id=\"buy_now_button\" type=\"submit\" name=\"submit\" value=\"Buy Now\" />\n";
		$z .= $i." </form>\n";
		$z .= $i."</div>\n";
		$z .= $i."<!-- /buy_now_html -->\n";
		return $z;
		
	
	
	}
	
	function bid_list($i) {
		$member = new member;
		$z = $i."<!-- bid_list -->\n";
		$z .= $i."<div id=\"bid_list\">\n";
		$z .= $i." <table>\n";
		$z .= $i."  <tr>\n";
		$z .= $i."   <th class=\"h\">Amount</th>\n";
		$z .= $i."   <th class=\"h\">".ucwords(MEMBERS_NAME_SINGULAR)."</th>\n";
		$z .= $i."   <th class=\"h\">Date</th>\n";
		$z .= $i."  </tr>\n";
		
		foreach($this->bids as $bid) {
			$z .= $i."  <tr>\n";
			$z .= $i."   <td>".$bid['amount']."</td>\n";
			$z .= $i."   <td>".$member->full_name($bid['accountID'])."</td>\n";
			
			if (!$bid['minute']) {
				$bid['minute'] = '00';
			} elseif ($bid['minute'] >= 1 and $bid['minute'] <= 9) {
				$bid['minute'] = '0'.$bid['minute'];
			}
			
			$z .= $i."   <td>".return_month($bid['month']).' '.$bid['day'].', '.$bid['year'].' - '.return_time($bid['hour'],$bid['minute'])."</td>\n";
			$z .= $i."  </tr>\n";
		}
		$z .= $i." </table>\n";
		$z .= $i."</div>\n";
		$z .= $i."<!-- /bid_list -->\n";
		return $z;
	}
	
	function bid_info($id) {
		$mysql = new mysql;
		if (!$mysql->build_array('SELECT * FROM bids WHERE noticeboardID = '.$id.' ORDER BY amount ASC')) {
			$this->error = $mysql->error;
			return false;		
		}
		$this->num_bids = $mysql->num_rows;
		if (!$this->num_bids) {
			$this->auction_winning_member_id = '';
			$this->auction_winning_amount = 0;
		} else {
			$this->bids = $mysql->result;
			$last_row = $this->num_bids - 1;
			$this->auction_winning_member_id = $mysql->result[$last_row]['accountID'];
			$this->auction_winning_amount = $mysql->result[$last_row]['amount'];
		}
		return true;
	}
	
	function winning_auctions($i,$id) {
		$mysql= new mysql;
		$query = 'SELECT noticeboard.noticeboardID,noticeboard.title, MAX(bids.amount) as amount
					FROM accounts,noticeboard,bids 
					WHERE accounts.accountID = bids.accountID AND
						noticeboard.noticeboardID = bids.noticeboardID AND
						(noticeboard.expiry_year > '.$GLOBALS['date']['year'].' OR 
							(noticeboard.expiry_year = '.$GLOBALS['date']['year'].' AND noticeboard.expiry_month > '.$GLOBALS['date']['month'].') OR 
							(noticeboard.expiry_year = '.$GLOBALS['date']['year'].' AND noticeboard.expiry_month = '.$GLOBALS['date']['month'].' AND noticeboard.expiry_day > '.$GLOBALS['date']['day'].') OR 
							(noticeboard.expiry_year = '.$GLOBALS['date']['year'].' AND noticeboard.expiry_month = '.$GLOBALS['date']['month'].' AND noticeboard.expiry_day = '.$GLOBALS['date']['day'].' AND noticeboard.expiry_hour > '.$GLOBALS['date']['hour'].') OR 
							(noticeboard.expiry_year = 0 AND noticeboard.expiry_month = 0 AND noticeboard.expiry_day = 0 AND noticeboard.expiry_hour = 0)
						) AND 
						noticeboard.bought = 0 AND 
						noticeboard.expired = 0 AND 
						accounts.suspended = 0 AND
						accounts.accountID = '.$id.'
					GROUP BY accounts.accountID';
		if (!$mysql->build_array($query)) {
			return '';
		}
		if (is_array($mysql->result)) {
			$winning = array();
			$losing = array();
			$w = 0;
			$l = 0;
			foreach($mysql->result as $notice) {
				if ($this->bid_info($notice['noticeboardID'])) {
					if ($this->auction_winning_member_id == $id) {
						$winning[$w]['noticeboardID'] = $notice['noticeboardID'];
						$winning[$w]['amount'] = $notice['amount'];
						$winning[$w]['title'] = $notice['title'];
						$w++;
					} else {
						$losing[$l]['noticeboardID'] = $notice['noticeboardID'];
						$losing[$l]['losing_amount'] = $notice['amount'];
						$losing[$l]['title'] = $notice['title'];
						$losing[$l]['winning_amount'] = $this->auction_winning_amount;
						$l++;
					}
				}
			}
			if (count($winning) > 0 or count($losing) > 0) {
				$z = $i."<!-- member_auction_status -->\n";
				if (count($winning) > 0) {
					$z .= $i."<strong>Winning Auctions:</strong><br />\n";
					foreach ($winning as $notice) {
						$z .= $i.'<a href="'.URL.NOTICEBOARD_URL.'/'.$notice['noticeboardID'].'/'.append_url(0).'">'.$notice['title'].'</a> with a bid of '.number_format($notice['amount'],2).' '.ucwords(CURRENCY_NAME)."<br />\n";
					}
					$z .= $i."<br />\n";
				}
				if (count($losing) > 0) {
					$z .= $i."<strong>Losing Auctions:</strong><br />\n";
					foreach ($losing as $notice) {
						$z .= $i.'<a href="'.URL.NOTICEBOARD_URL.'/'.$notice['noticeboardID'].'/'.append_url(0).'">'.$notice['title'].'</a> with a bid of '.number_format($notice['losing_amount'],2).' '.ucwords(CURRENCY_NAME).' (Top bid is '.$notice['winning_amount'].' '.ucwords(CURRENCY_NAME).")<br />\n";
					}
					$z .= $i."<br />\n";
				}
				$z .= $i."<!-- /member_auction_status -->\n";
				return $z;
			}
		} else {
			return '';
		}
	}
	
	function bid($i,$noticeboard_id,$reserve,$amount) {
		$mysql = new mysql;
		global $user;
		if ($this->bid_info($noticeboard_id)) {
			if (($amount < $reserve) or ($amount <= $this->auction_winning_amount)) {
				$this->error = 'Bid too low';
				return false;
			}
			if ($this->auction_winning_member_id == $_SESSION['member_id'] and !isset($_POST['buy_now_amount'])) {
				$this->error = ucwords(MEMBERS_NAME_SINGULAR).' already winning auction';
				return false;
			}
			
			if (!$mysql->query("INSERT INTO bids VALUES ( '','".$noticeboard_id."','".$_SESSION['member_id']."','".$amount."','".$GLOBALS['date']['day']."','".$GLOBALS['date']['month']."','".$GLOBALS['date']['year']."','".$GLOBALS['date']['hour']."','".$GLOBALS['date']['minutes']."' )")) {
				if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: Bid added to '.strtolower(NOTICEBOARD_NAME_SINGULAR).' ID:'.$noticeboard_id);
				if (ENABLE_ERROR_LOG) log_error('FAILED: Bid added to '.strtolower(NOTICEBOARD_NAME_SINGULAR).' ID:'.$noticeboard_id.'<br />Error: '.$mysql->error);
				$this->error = 'Error entering bid';
				return false;
			}
			if ($mysql->result('SELECT receive_email_outbid,email_address,first_name,last_name FROM accounts WHERE accountID = '.$this->auction_winning_member_id.' AND email_address != \'\' LIMIT 1')) {
				if ($mysql->result['receive_email_outbid']) {
					send_single_email(UPDATE_EMAIL,EMAIL_FROM_NAME,$mysql->result['email_address'],$mysql->result['first_name'].' '.$mysql->result['last_name'],'Out-Bid Notice','Dear '.$mysql->result['first_name'].",\r\n\r\n\r\n".'You have been out-bid on '.strtolower(NOTICEBOARD_NAME_SINGULAR).' #'.$noticeboard_id.".\r\n\r\n\r\n".$user->full_name($_SESSION['member_id']).' just bid '.number_format($amount,2).' '.ucwords(CURRENCY_NAME)."\r\n\r\n\r\nUse the following link to enter a new bid: ".URL.NOTICEBOARD_URL.'/'.$noticeboard_id.'/','Dear '.$mysql->result['first_name'].',<br /><br />You have been out-bid on <a href="'.URL.NOTICEBOARD_URL.'/'.$noticeboard_id.'/">'.strtolower(NOTICEBOARD_NAME_SINGULAR).' #'.$noticeboard_id.'</a>.<br /><br />'.$user->full_name($_SESSION['member_id']).' just bid '.number_format($amount,2).' '.ucwords(CURRENCY_NAME));
				}
			}
			if (ENABLE_LOG and LOG_TRANSACTIONS) log_action('Bid added to '.ucwords(NOTICEBOARD_NAME_SINGULAR).' ID:'.$noticeboard_id);
			return true;
		}
	}
	
	function auction_html($i,$noticeboard_id,$min_bid) {
		$z = $i."<!-- auction_html -->\n";
		$z .= $i."<div id=\"auction_html\">\n";
		$z .= $i." <form enctype=\"multipart/form-data\" id=\"bid\" name=\"bid\" action=\"".URL.NOTICEBOARD_URL.'/'.$noticeboard_id.'/'.append_url()."\" method=\"post\" onSubmit=\"return check_bid();\" >\n";
		$z .= $i."  <input type=\"text\" name=\"bid_amount\" id=\"bid_amount\" value=\"".$min_bid."\" />\n";
		$z .= $i."  <input id=\"bid_button\" type=\"submit\" name=\"submit\" value=\"Bid Now\" />\n";
		$z .= $i." </form>\n";
		$z .= $i."</div>\n";
		$z .= $i."<!-- /auction_html -->\n";
		return $z;
	}
	
	function auction_javascript($min_bid) {
		$z = 'function check_bid() {'."\n";
		$z .= "\t".'if (document.bid.bid_amount.value < '.$min_bid.') {'."\n";
		$z .= "\t\t".'alert("Minimun bid is '.$min_bid.' '.CURRENCY_NAME.'");'."\n";
		$z .= "\t\t".'return false;'."\n";
		$z .= "\t"."} else {\n";
		$z .= "\t\t".'return true;'."\n";
		$z .= "\t"."}\n";
		$z .= "}\n";
		return $z;
	}
	
	function finish_auctions() {
		$mysql = new mysql;
		require_once('includes/classes/transactions.class.php');
		$transactions = new transactions;
		$query = 'SELECT * FROM bids,noticeboard WHERE (noticeboard.expiry_year < '.$GLOBALS['date']['year'].' OR (noticeboard.expiry_year = '.$GLOBALS['date']['year'].' AND noticeboard.expiry_month < '.$GLOBALS['date']['month'].') OR (noticeboard.expiry_year = '.$GLOBALS['date']['year'].' AND noticeboard.expiry_month = '.$GLOBALS['date']['month'].' AND noticeboard.expiry_day < '.$GLOBALS['date']['day'].') OR (noticeboard.expiry_year = '.$GLOBALS['date']['year'].' AND noticeboard.expiry_month = '.$GLOBALS['date']['month'].' AND noticeboard.expiry_day = '.$GLOBALS['date']['day'].' AND noticeboard.expiry_hour <= '.$GLOBALS['date']['hour'].')) AND noticeboard.noticeboardID = bids.noticeboardID AND noticeboard.bought != 1 AND noticeboard.expired != 1 AND noticeboard.type = 2';
		if ($mysql->build_array($query)) {
			if ($mysql->num_rows) {
				$old_auctions = $mysql->result;
				foreach ($old_auctions as $auction) {
					if ($this->bid_info($auction['noticeboardID'])) {
						if ($mysql->num_rows('SELECT * FROM noticeboard WHERE expired = 0 AND bought = 0 AND noticeboardID = '.$auction['noticeboardID'].' LIMIT 1')) {
							if ($mysql->num_rows) {
								$transactions->make_transaction(3,$this->auction_winning_member_id,$auction['accountID'],$this->auction_winning_amount,'Automated transaction for auction win (<a href="'.URL.NOTICEBOARD_URL.'/'.$auction['noticeboardID'].'/">'.ucwords(NOTICEBOARD_NAME_SINGULAR).' ID #'.$auction['noticeboardID'].'</a>):'.$auction['title'],$auction['expiry_day'],$auction['expiry_month'],$auction['expiry_year'],$auction['expiry_hour'],0,0,$auction['noticeboardID']);
								$mysql->query('UPDATE noticeboard SET expired = 1, bought = 1 WHERE noticeboardID = '.$auction['noticeboardID'].' LIMIT 1');
							}
						}
					}
				}
			}
		}
	}
	function categories_html($i,$url) {
		$mysql = new mysql;
		// form html
		$z = $i."<!-- categories_form -->\n";
		$z .= $i."<h3>".ucwords(NOTICEBOARD_NAME_SINGULAR)." Categories</h3>\n";
		$z .= $i."<fieldset>\n";
		$z .= $i."<form name=\"noticeboard_categories\" action=\"".URL.$url.append_url($url)."\" method=\"post\">\n";
		$z .= $i." <input type=\"hidden\" name=\"noticeboard\" value=\"1\" />\n";
		$z .= $i." <select id=\"category\" name=\"category\" onclick=\"set_noticeboard_buttons();\">\n";
		
		// javascript
		$j = 'function set_noticeboard_buttons() {'."\n";
		$b=0;
		foreach ($this->categories as $category) {
			$z .= $i."  <option value=\"".$category['categoryID']."\"".selected('','',$category['categoryID'],0).">".ucwords($category['name'])."</option>\n";
			$mysql->num_rows('SELECT noticeboardID FROM noticeboard WHERE categoryID = '.$category['categoryID'].' LIMIT 1');
			$j .= "\t".'if (document.noticeboard_categories.category.selectedIndex == '.($b).') {'."\n";
			if ($mysql->num_rows) {
				$j .= "\t\t".'document.noticeboard_categories.noticeboard_delete_button.disabled = true;'."\n";
			} else {
				$j .= "\t\t".'document.noticeboard_categories.noticeboard_delete_button.disabled = false;'."\n";
			}
			$j .= "\t\t".'document.noticeboard_categories.name.value = "'.ucwords($category['name']).'";'."\n";
			$j .= "\t"."}\n";
			$b++;
		}
		$j .= "}\n";
		
		$z .= $i." </select>\n";
		$z .= $i." <input type=\"text\" name=\"name\" id=\"name\" value=\"\" /><br class=\"left\" />\n";
		$z .= $i." <input id=\"noticeboard_add_button\" type=\"submit\" name=\"submit\" value=\"Add Category\" />\n";
		$z .= $i." <input id=\"noticeboard_delete_button\" type=\"submit\" name=\"submit\" value=\"Delete Category\" />\n";
		$z .= $i." <input id=\"noticeboard_edit_button\" type=\"submit\" name=\"submit\" value=\"Edit Category\" />\n";
		$z .= $i."</form>\n";
		$z .= $i."</fieldset><br />\n";
		$z .= $i."<!-- /categories_form -->\n";
		$this->category_javascript = $j;
		return $z;
	}
	function form_javascript() {
		$z = 'function set_as_auction() {'."\n";
		$z .= "\t".'if (document.noticeboard.set_expiry.checked != true && document.noticeboard.type.checked == true) {'."\n";
		$z .= "\t\t".'document.noticeboard.set_expiry.checked = true;'."\n";
		$z .= "\t\t".'document.noticeboard.expiry_day.disabled = false;'."\n";
		$z .= "\t\t".'document.noticeboard.expiry_month.disabled = false;'."\n";
		$z .= "\t\t".'document.noticeboard.expiry_year.disabled = false;'."\n";
		$z .= "\t\t".'document.noticeboard.expiry_hour.disabled = false;'."\n";
		$z .= "\t"."}\n";
		$z .= "\t".'if (document.noticeboard.reserve.disabled == true && document.noticeboard.type.checked == true)'."\n";
		$z .= "\t\t".'document.noticeboard.reserve.disabled = false;'."\n";
		$z .= "\t".'if (document.noticeboard.reserve.disabled == false && document.noticeboard.type.checked == false)'."\n";
		$z .= "\t\t".'document.noticeboard.reserve.disabled = true;'."\n";
		$z .= "}\n";
		
		$z .= 'function set_expiry_date() {'."\n";
		$z .= "\t".'if (document.noticeboard.set_expiry.checked != true && document.noticeboard.type.checked == true) {'."\n";
		$z .= "\t\t".'document.noticeboard.set_expiry.checked = true;'."\n";
		$z .= "\t\t".'alert("Auctions must expire.\nDisable the auction before disabling the expiry date");'."\n";
		$z .= "\t"."}\n";
		$z .= "\t".'if (document.noticeboard.set_expiry.checked == true) {'."\n";
		$z .= "\t\t".'document.noticeboard.expiry_day.disabled = false;'."\n";
		$z .= "\t\t".'document.noticeboard.expiry_month.disabled = false;'."\n";
		$z .= "\t\t".'document.noticeboard.expiry_year.disabled = false;'."\n";
		$z .= "\t\t".'document.noticeboard.expiry_hour.disabled = false;'."\n";
		$z .= "\t"."} else {\n";
		$z .= "\t\t".'document.noticeboard.expiry_day.disabled = true;'."\n";
		$z .= "\t\t".'document.noticeboard.expiry_month.disabled = true;'."\n";
		$z .= "\t\t".'document.noticeboard.expiry_year.disabled = true;'."\n";
		$z .= "\t\t".'document.noticeboard.expiry_hour.disabled = true;'."\n";
		$z .= "\t"."}\n";
		$z .= "}\n";
		
		$z .= 'function form_type() {'."\n";
		$z .= "\t".'if (document.noticeboard.request.selectedIndex == 1) {'."\n";
		$z .= "\t\t".'document.noticeboard.type.disabled = true;'."\n";
		$z .= "\t\t".'document.noticeboard.reserve.disabled = true;'."\n";
		$z .= "\t\t".'document.noticeboard.fast_delete.disabled = true;'."\n";
		$z .= "\t"."} else {\n";
		$z .= "\t\t".'document.noticeboard.set_expiry.disabled = false;'."\n";
		$z .= "\t\t".'if (document.noticeboard.item.selectedIndex == 1) {'."\n";
		$z .= "\t\t\t".'document.noticeboard.fast_delete.checked = true;'."\n";
		$z .= "\t\t\t".'document.noticeboard.fast_delete.disabled = false;'."\n";
		$z .= "\t\t"."} else {\n";
		$z .= "\t\t\t".'document.noticeboard.fast_delete.disabled = true;'."\n";
		
		$z .= "\t\t"."}\n";
		$z .= "\t\t".'document.noticeboard.type.disabled = false;'."\n";
		$z .= "\t\t".'if (document.noticeboard.set_expiry.checked == true) {'."\n";
		$z .= "\t\t\t".'document.noticeboard.expiry_day.disabled = false;'."\n";
		$z .= "\t\t\t".'document.noticeboard.expiry_month.disabled = false;'."\n";
		$z .= "\t\t\t".'document.noticeboard.expiry_year.disabled = false;'."\n";
		$z .= "\t\t\t".'document.noticeboard.expiry_hour.disabled = false;'."\n";
		$z .= "\t\t"."}\n";
		$z .= "\t\t".'if (document.noticeboard.type.checked == true) {'."\n";
		$z .= "\t\t\t".'document.noticeboard.reserve.disabled = false;'."\n";
		$z .= "\t\t"."}\n";
		$z .= "\t"."}\n";
		$z .= "}\n";
		return $z;
	
	}
	
	function form_html($i,$type,$url) {
		$mysql = new mysql;
		$z = $i."<!-- noticeboard_form -->\n";
		if ($type == 'edit') {
			
			if (user_type() == 1 and ($this->member_id != $_SESSION["member_id"])) {
				return 'You cannot edit another '.strtolower(MEMBERS_NAME_SINGULAR).'\'s entry';
			} elseif (user_type() == 1) {
				if ($mysql->result('SELECT expired, bought FROM noticeboard WHERE noticeboardID = '.$this->id.' LIMIT 1')) {
					if (($mysql->result['expired'] or $mysql->result['bought']) and PREVENT_EDIT_AFTER_TRANSACTION) {
						
						if ($mysql->result['expired']) {
							$z .= $i."<strong>This auction has ended</strong><br /><br />\n";
						}
						if ($mysql->result['bought'] and !$mysql->result['expired']) {
							$z .= $i."<strong>This item has been purchased</strong><br /><br />\n";
						}
						$z .= $i."It is a record of a transaction and should not be edited.";
						if (PREVENT_DELETION_AFTER_TRANSACTION) {
							$z .= "<br />\n";
						} else {
						
							$z .= $i." It may be deleted however.<br />\n";
							$z .= $i."<form enctype=\"multipart/form-data\" id=\"noticeboard\" name=\"noticeboard\" action=\"".URL.$url.$this->id.'/'.append_url(0)."\" method=\"post\">\n";
							$z .= $i." <br class=\"left\" /><input class=\"button\" type=\"submit\" name=\"submit\" value=\"Delete\" />\n";
							$z .= $i."</form>\n";
						}
						return $z;
					}
				}
				if (FREEZE_AUCTION_AFTER_BID and $this->type != 2) {
					if ($this->bid_on($this->id)) {
						return $i."Cannot edit an auction once a bid has been made<br /><br />\n";
					}
				}
			}
		}
		
		
		
		if (ENABLE_IMAGES) global $image;
		
		if ($this->request) {
			$disable_auction = ' disabled="true"';
			$this->amount_required = 0;
		} else {
			$disable_auction = '';
		}
		
		
		$z .= $i."<fieldset>\n";
		$z .= $i."<legend>\n";
		$z .= $i."Information\n";
		$z .= $i."</legend>\n";
		
		
		$z .= $i."Required fields are <span class=\"required_field\">".REQUIRED_DISPLAY."</span>.<br /><br />\n";
		$z .= $i."<form enctype=\"multipart/form-data\" id=\"noticeboard\" name=\"noticeboard\" action=\"".URL.$url.append_url(0)."\" method=\"post\">\n";
		if ($type == 'edit') {
			$z .= $i." <input type=\"hidden\" name=\"noticeboard_id\" value=\"".$this->id."\" />\n";
			// can't remember why this is here:
			// $request_field_disabled = ' disabled="true"';
			$request_field_disabled = '';
		} else {
			$request_field_disabled = '';
		}
		
		$z .= $i." <label for=\"request\">Offer or Request?</label>\n";
		$z .= $i." <select id=\"request\" name=\"request\"".$request_field_disabled." onclick=\"form_type();\">\n";
		$z .= $i."  <option value=\"0\"".selected(' ','',0,$this->request).">Offer</option>\n";
		$z .= $i."  <option value=\"1\"".selected(' ','',1,$this->request).">Request</option>\n";
		$z .= $i." </select><br class=\"left\" />\n";
		
		$z .= $i." <label for=\"title\">".$this->check_required($this->title_required,1)."Title:".$this->check_required($this->title_required,2)."</label>\n";
		$z .= $i." <input type=\"text\" name=\"title\" id=\"title\" value=\"".htmlspecialchars($this->title)."\" /><br class=\"left\" />\n";
		$z .= $i." <label for=\"item\">Item or service?</label>\n";
		$z .= $i." <select id=\"item\" name=\"item\" onclick=\"form_type();\">\n";
		$z .= $i."  <option value=\"0\"".selected(' ','',0,$this->item).">Service</option>\n";
		$z .= $i."  <option value=\"1\"".selected(' ','',1,$this->item).">Item</option>\n";
		$z .= $i." </select><br class=\"left\" />\n";
		$z .= $i." <label for=\"amount\">".$this->check_required($this->amount_required,1)."Amount:".$this->check_required($this->amount_required,2)."</label>\n";
		$z .= $i." <input type=\"text\" name=\"amount\" id=\"amount\" value=\"".$this->amount."\" /><br class=\"left\" />\n";
		if (ENABLE_INSTANT_BUY) {
			$z .= $i." The \"Buy it Now\" option will automatically delete the listing after it is purchased - Make sure you include an amount.<label for=\"fast_delete\">Buy It Now?</label>\n";
			$z .= $i." <input type=\"checkbox\" name=\"fast_delete\" id=\"fast_delete\" value=\"1\" ";
			if ($this->fast_delete) $z .= 'checked="checked" ';
			$z .= "/><br class=\"left\" />\n";
		}
		$z .= $i." <label for=\"amount\">Category:</label>\n";
		$z .= $i." <select id=\"category\" name=\"category\">\n";
		foreach ($this->categories as $category) {
			$z .= $i."  <option value=\"".$category['categoryID']."\"".selected('',' ',$category['categoryID'],$this->category_id).">".ucwords($category['name'])."</option>\n";
		}
		$z .= $i." </select><br class=\"left\" />\n";
		$z .= $i." <label for=\"blurb\">".$this->check_required($this->blurb_required,1)."Short Description:<br />(Used on list page)".$this->check_required($this->blurb_required,2)."</label>\n";
		$z .= $i." <textarea id=\"blurb\" name=\"blurb\" cols=\"10\" rows=\"3\">".htmlspecialchars($this->blurb)."</textarea><br class=\"left\" />\n";
		$z .= $i." <label for=\"description\">".$this->check_required($this->description_required,1)."Long Description:<br />(Used on item page)".$this->check_required($this->description_required,2)."</label>\n";
		$z .= $i." <textarea id=\"description\" name=\"description\" cols=\"30\" rows=\"5\">".htmlspecialchars($this->description)."</textarea><br class=\"left\" />\n";
				
		
		
		if (ENABLE_AUCTIONS) {
			$z .= $i." <label for=\"type\">Make this an auction?</label>\n";
			$z .= $i." <input type=\"checkbox\" name=\"type\" id=\"type\" value=\"2\" ";
			if ($this->type == 2) {
				$z .= 'checked="checked" ';
				$disabled_reserve = ' ';				
			} else {
				$disabled_reserve = ' disabled=\"true\" ';
			}
			$z .= "onClick=\"set_as_auction();\"".$disable_auction."\" /><br class=\"left\" />\n";
			
			$z .= $i." <label for=\"reserve\">Reserve:</label>\n";
			$z .= $i." <input type=\"text\" name=\"reserve\" id=\"reserve\"".$disabled_reserve."value=\"".$this->reserve."\" /><br class=\"left\" />\n";
			
		}
		
		$z .= $i." <br class=\"left\" /><label for=\"set_expiry\">Set an expiry date?</label>\n";
		$z .= $i." <input type=\"checkbox\" name=\"set_expiry\" id=\"set_expiry\" value=\"1\" onClick=\"set_expiry_date();\" ";
		if ($this->set_expiry) $z .= 'checked="checked" ';
		$z .= "/><br class=\"left\" />\n";
		$z .= $i." <label for=\"expiry_day\">Expiry date:</label>\n";
		if (isset($this->expiry_day) and isset($this->expiry_month) and isset($this->expiry_year)) {
			if ($this->set_expiry) {
				$z .= date_form($i.' ',array('day' => $this->expiry_day, 'month' => $this->expiry_month, 'year' => $this->expiry_year),'expiry_',false,'false');
			} else {
				$z .= date_form($i.' ',array('day' => $this->expiry_day, 'month' => $this->expiry_month, 'year' => $this->expiry_year),'expiry_',false,'true');
			}
		} else {
			if ($this->set_expiry) {
				$z .= date_form($i.' ',change_date('day','+',7),'expiry_',false,'false');
			} else {
				$z .= date_form($i.' ',change_date('day','+',7),'expiry_',false,'true');
			}
		}
		if (empty($this->expiry_hour)) $this->expiry_hour = 15;
		$z .= $i." <select id=\"expiry_hour\" name=\"expiry_hour\"";
		if ($this->set_expiry) {
			$z .= " ";
		} else {
			$z .= " disabled=\"true\"";
		}
		$z .= ">\n";
		$z .= $i."  <option value=\"0\"".selected(' ','',0,$this->expiry_hour).">12:00 AM</option>\n";
		$z .= $i."  <option value=\"1\"".selected(' ','',1,$this->expiry_hour).">1:00 AM</option>\n";
		$z .= $i."  <option value=\"2\"".selected(' ','',2,$this->expiry_hour).">2:00 AM</option>\n";
		$z .= $i."  <option value=\"3\"".selected(' ','',3,$this->expiry_hour).">3:00 AM</option>\n";
		$z .= $i."  <option value=\"4\"".selected(' ','',4,$this->expiry_hour).">4:00 AM</option>\n";
		$z .= $i."  <option value=\"5\"".selected(' ','',5,$this->expiry_hour).">5:00 AM</option>\n";
		$z .= $i."  <option value=\"6\"".selected(' ','',6,$this->expiry_hour).">6:00 AM</option>\n";
		$z .= $i."  <option value=\"7\"".selected(' ','',7,$this->expiry_hour).">7:00 AM</option>\n";
		$z .= $i."  <option value=\"8\"".selected(' ','',8,$this->expiry_hour).">8:00 AM</option>\n";
		$z .= $i."  <option value=\"9\"".selected(' ','',9,$this->expiry_hour).">9:00 AM</option>\n";
		$z .= $i."  <option value=\"10\"".selected(' ','',10,$this->expiry_hour).">10:00 AM</option>\n";
		$z .= $i."  <option value=\"11\"".selected(' ','',11,$this->expiry_hour).">11:00 AM</option>\n";
		$z .= $i."  <option value=\"12\"".selected(' ','',12,$this->expiry_hour).">12:00 PM</option>\n";
		$z .= $i."  <option value=\"13\"".selected(' ','',13,$this->expiry_hour).">1:00 PM</option>\n";
		$z .= $i."  <option value=\"14\"".selected(' ','',14,$this->expiry_hour).">2:00 PM</option>\n";
		$z .= $i."  <option value=\"15\"".selected(' ','',15,$this->expiry_hour).">3:00 PM</option>\n";
		$z .= $i."  <option value=\"16\"".selected(' ','',16,$this->expiry_hour).">4:00 PM</option>\n";
		$z .= $i."  <option value=\"17\"".selected(' ','',17,$this->expiry_hour).">5:00 PM</option>\n";
		$z .= $i."  <option value=\"18\"".selected(' ','',18,$this->expiry_hour).">6:00 PM</option>\n";
		$z .= $i."  <option value=\"19\"".selected(' ','',19,$this->expiry_hour).">7:00 PM</option>\n";
		$z .= $i."  <option value=\"20\"".selected(' ','',20,$this->expiry_hour).">8:00 PM</option>\n";
		$z .= $i."  <option value=\"21\"".selected(' ','',21,$this->expiry_hour).">9:00 PM</option>\n";
		$z .= $i."  <option value=\"22\"".selected(' ','',22,$this->expiry_hour).">10:00 PM</option>\n";
		$z .= $i."  <option value=\"23\"".selected(' ','',23,$this->expiry_hour).">11:00 PM</option>\n";
		$z .= $i." </select><br class=\"left\" />\n";
		
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
				$z .= $image->edit_form($i,$this->id,'noticeboardID','noticeboard',URL.$url.append_url(0));
				$z .= $i."</fieldset>\n";
			} else {
				$z .= $i." <input class=\"button\" type=\"submit\" name=\"submit\" value=\"Edit\" /><br class=\"left\" /><input class=\"button\" type=\"submit\" name=\"submit\" value=\"Delete\" />\n";
				$z .= $i."</form>\n";
				$z .= $i."</fieldset>\n";
			}
		} else {
			if (ENABLE_IMAGES) {
				if (!isset($_POST['submit']) or $_POST['submit'] == 'Delete') $image->clear();
				$z .= $i." Add an image to your offer or request:<br /><br />\n";
				if ($this->image_required) $z .= $image->form_html($i,$type,1); else $z .= $image->form_html($i.' ',$type,0);
			}
			$z .= $i." <input class=\"button\" type=\"submit\" name=\"submit\" value=\"Add\" />\n";
			$z .= $i."</form>\n";
			$z .= $i."</fieldset>\n";
		}
		$z .= $i."<!-- /noticeboard_form -->\n";
		return $z;
	}
	function append_search_url($keyword,$limit,$request,$offer,$noticeboard_member_id,$amount,$amount_above,$item,$service,$category_id,$auction,$orderby,$orderdir) {
		$z = '';
		if ($keyword) {
			$keyword = htmlentities($keyword);
			$z .= '&keyword='.str_replace(' ','_',$keyword);
		}
		if ($limit) $z .= '&limit='.$limit;
		if ($request) $z .= '&request=1';
		if ($offer) $z .= '&offer=1';
		if ($noticeboard_member_id) $z .= '&noticeboard_member='.$noticeboard_member_id;
		if ($amount) $z .= '&amount='.$amount;
		if ($amount_above) $z .= '&amount_above='.$amount_above;
		if ($item) $z .= '&item=1';
		if ($service) $z .= '&service=1';
		if ($category_id) $z .= '&category_id='.$category_id;
		if ($auction) $z .= '&auction=1';
		if ($orderby) $z .= '&orderby='.$orderby;
		if ($orderdir) $z .= '&orderdir='.$orderdir;
		return $z;
	}
	function noticeboard_list($i,$keyword = '',$limit,$start = 0,$request = 0,$offer = 0,$member_id = 0,$amount = 0,$amount_above = 0,$item = 0,$service = 0,$category_id = 0,$auction = 0,$orderby = 0,$orderdir = 0,$show_results = true) {
		if (ENABLE_IMAGES) global $image;
		global $user;
		$mysql = new mysql;

		if (!$start) $start = 0;

		// develop query from function call
		if (user_type() == 2) {
			$query = "SELECT * FROM noticeboard,categories WHERE ";
		} else {
			$query = "SELECT noticeboard.noticeboardID,noticeboard.request,noticeboard.accountID,noticeboard.created_month,noticeboard.created_day,noticeboard.created_year,noticeboard.expiry_day,noticeboard.expiry_month,noticeboard.expiry_year,noticeboard.expiry_hour,noticeboard.imageID,noticeboard.title,noticeboard.amount,noticeboard.blurb,noticeboard.description,noticeboard.type,noticeboard.item,noticeboard.categoryID,noticeboard.bought,noticeboard.reserve,noticeboard.expired,noticeboard.quick_delete FROM noticeboard,categories,accounts WHERE ";
		}
		$conditions = '';
		
		if ($request == 1 and $offer != 1) {
			$conditions .= 'noticeboard.request = 1 AND ';
		}
		if ($offer == 1 and $request != 1) {
			$conditions .= 'noticeboard.request = 0 AND ';
		}
		
		if ($member_id) {
			$conditions .= 'noticeboard.accountID = '.$member_id.' AND ';
		}
		
		if ($amount) {
			if ($amount_above == 1) {
				$conditions .= 'noticeboard.amount > '.$amount.' AND ';
			} else {
				$conditions .= 'noticeboard.amount < '.$amount.' AND ';
			}
		}

		if ($item == 1 and $service != 1) {
			$conditions .= 'noticeboard.item = 1 AND ';
		}
		if ($service == 1 and $item != 1) {
			$conditions .= 'noticeboard.item = 0 AND ';
		}
		if ($auction and !$request) {
			$conditions .= 'noticeboard.type = 2 AND ';
		}
		
		if ($category_id) {
			$conditions .= 'noticeboard.categoryID = '.$category_id.' AND ';
		}
		
		if ($keyword) {
			$keywords = search_terms($keyword);
			$num_searches = count($keywords);
			if ($num_searches > 1) {
				$ii = 1;
				$conditions .= '(';
				foreach($keywords as $search_term) {
					$conditions .= "(noticeboard.title LIKE '%".$search_term."%' OR noticeboard.blurb LIKE '%".$search_term."%' OR noticeboard.description LIKE '%".$search_term."%')";
					if ($ii <  $num_searches) {
						$conditions .= ' OR ';
					}
					$ii++;
				}
				$conditions .= ') AND ';
			} else {
				$conditions .= "(noticeboard.title LIKE '%".$keywords[0]."%' OR noticeboard.blurb LIKE '%".$keywords[0]."%' OR noticeboard.description LIKE '%".$keywords[0]."%') AND ";
			}
		}
		if (user_type() == 2) {
			$query .= $conditions.'(noticeboard.expiry_year > '.$GLOBALS['date']['year'].' OR (noticeboard.expiry_year = '.$GLOBALS['date']['year'].' AND noticeboard.expiry_month > '.$GLOBALS['date']['month'].') OR (noticeboard.expiry_year = '.$GLOBALS['date']['year'].' AND noticeboard.expiry_month = '.$GLOBALS['date']['month'].' AND noticeboard.expiry_day > '.$GLOBALS['date']['day'].') OR (noticeboard.expiry_year = '.$GLOBALS['date']['year'].' AND noticeboard.expiry_month = '.$GLOBALS['date']['month'].' AND noticeboard.expiry_day = '.$GLOBALS['date']['day'].' AND noticeboard.expiry_hour > '.$GLOBALS['date']['hour'].') OR (noticeboard.expiry_year = 0 AND noticeboard.expiry_month = 0 AND noticeboard.expiry_day = 0 AND noticeboard.expiry_hour = 0)) AND noticeboard.bought = 0 AND noticeboard.expired = 0 AND noticeboard.categoryID = categories.categoryID ';
		} else {
			$query .= $conditions.'(noticeboard.expiry_year > '.$GLOBALS['date']['year'].' OR (noticeboard.expiry_year = '.$GLOBALS['date']['year'].' AND noticeboard.expiry_month > '.$GLOBALS['date']['month'].') OR (noticeboard.expiry_year = '.$GLOBALS['date']['year'].' AND noticeboard.expiry_month = '.$GLOBALS['date']['month'].' AND noticeboard.expiry_day > '.$GLOBALS['date']['day'].') OR (noticeboard.expiry_year = '.$GLOBALS['date']['year'].' AND noticeboard.expiry_month = '.$GLOBALS['date']['month'].' AND noticeboard.expiry_day = '.$GLOBALS['date']['day'].' AND noticeboard.expiry_hour > '.$GLOBALS['date']['hour'].') OR (noticeboard.expiry_year = 0 AND noticeboard.expiry_month = 0 AND noticeboard.expiry_day = 0 AND noticeboard.expiry_hour = 0)) AND noticeboard.bought = 0 AND noticeboard.expired = 0 AND noticeboard.categoryID = categories.categoryID AND noticeboard.accountID = accounts.accountID AND accounts.suspended = 0 ';
		}
		
		if (!$orderby or ($orderby == 'category')) {
			if (!$orderdir or $orderdir == 'ASC') {
				$query .= 'ORDER BY categories.name';
			}
			if ($orderdir == 'DESC') {
				$query .= 'ORDER BY categories.name DESC';
			}
		}
		if ($orderby == 'title') {
			if (!$orderdir or $orderdir == 'ASC') {
				$query .= 'ORDER BY noticeboard.title';
			}
			if ($orderdir == 'DESC') {
				$query .= 'ORDER BY noticeboard.title DESC';
			}
		}
		if ($orderby == 'amount') {
			if (!$orderdir or $orderdir == 'ASC') {
				$query .= 'ORDER BY noticeboard.amount';
			}
			if ($orderdir == 'DESC') {
				$query .= 'ORDER BY noticeboard.amount DESC';
			}
		}
		if ($orderby == 'member') {
			if (!$orderdir or $orderdir == 'ASC') {
				$query .= 'ORDER BY noticeboard.accountID';
			}
			if ($orderdir == 'DESC') {
				$query .= 'ORDER BY noticeboard.accountID DESC';
			}
		}
		if ($orderby == 'expiry') {
			if (!$orderdir or $orderdir == 'ASC') {
				$query .= 'ORDER BY noticeboard.expiry_year, noticeboard.expiry_month, noticeboard.expiry_day, noticeboard.expiry_hour';
			}
			if ($orderdir == 'DESC') {
				$query .= 'ORDER BY noticeboard.expiry_year DESC, noticeboard.expiry_month DESC, noticeboard.expiry_day DESC, noticeboard.expiry_hour DESC';
			}
		}
		if ($orderby == 'posted') {
			if (!$orderdir or $orderdir == 'ASC') {
				$query .= 'ORDER BY noticeboard.created_year, noticeboard.created_month, noticeboard.created_day';
			}
			if ($orderdir == 'DESC') {
				$query .= 'ORDER BY noticeboard.created_year DESC, noticeboard.created_month DESC, noticeboard.created_day DESC';
			}
		}
		
		$z = $i."<!-- noticeboard_listing -->\n";
		$z .= $i."<div id=\"noticeboard_listing\">\n";
		// try out the query
		if (!$mysql->build_array($query)) {
			echo $mysql->error; 
		} else {
			if (!$mysql->num_rows) {
				if ($show_results) {
					return 'No '.strtolower(NOTICEBOARD_NAME_PLURAL)." found<br /><br />\n";
				} else {
					return '';
				}
			}
			$num_result = $mysql->num_rows;
			if ($num_result > $limit) {
				if ($limit) {
					$query .= ' LIMIT '.$start.','.$limit;
				}
				if (!$mysql->build_array($query)) {
					return $mysql->error; 
				} else {
					$notices = $mysql->result;
					if (($limit + $start) > $num_result) {
						$z .= 'Showing Results '.($start + 1).' to '.$num_result.' of '.$num_result."<br /><br />\n";
					} else {
						$z .= 'Showing Results '.($start + 1).' to '.($limit + $start).' of '.$num_result."<br /><br />\n";
					}
					if ($limit) {
						$num_pages = ceil($num_result/$limit);
					} else {
						$num_pages = 1;
					}					
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
								$z .= '<a href="'.URL.NOTICEBOARD_URL.'/?start='.(($p * $limit) - $limit).$this->append_search_url($keyword,$limit,$request,$offer,$member_id,$amount,$amount_above,$item,$service,$category_id,$auction,$orderby,$orderdir).append_url(' ?').'">'.$p.'</a> ';
							} else {
								$z .= $p.' ';
							}
						}
						$z .= $i."<br /><br />\n";
					}
				}
			} else {
				$notices = $mysql->result;
				$z .= $i.' Found '.$num_result." results<br /><br />\n";
			}
		}

		if ($orderdir == 'DESC') {
			$orderdir = 'ASC';
		} elseif ($orderdir == 'ASC') {
			$orderdir = 'DESC';
		}
		if (!$orderdir) $orderdir = 'ASC';
		
		$z .= $i." <table width=\"100%\">\n";
		$z .= $i."  <tr>\n";
		$z .= $i.'   <th class="h"><a href="'.URL.NOTICEBOARD_URL.'/?'.$this->append_search_url($keyword,$limit,$request,$offer,$member_id,$amount,$amount_above,$item,$service,$category_id,$auction,'title',$orderdir).append_url(' ?')."\">Title</a></th>\n";
		$z .= $i."   <th class=\"h\">Description</th>\n";
		$z .= $i.'   <th class="h"><a href="'.URL.NOTICEBOARD_URL.'/?'.$this->append_search_url($keyword,$limit,$request,$offer,$member_id,$amount,$amount_above,$item,$service,$category_id,$auction,'category',$orderdir).append_url(' ?')."\">Category</a></th>\n";
		$z .= $i.'   <th class="h"><a href="'.URL.NOTICEBOARD_URL.'/?'.$this->append_search_url($keyword,$limit,$request,$offer,$member_id,$amount,$amount_above,$item,$service,$category_id,$auction,'amount',$orderdir).append_url(' ?')."\">Amount</a></th>\n";
		$z .= $i."   <th class=\"h\">Type</th>\n";
		$z .= $i.'   <th class="h"><a href="'.URL.NOTICEBOARD_URL.'/?'.$this->append_search_url($keyword,$limit,$request,$offer,$member_id,$amount,$amount_above,$item,$service,$category_id,$auction,'posted',$orderdir).append_url(' ?')."\">Posted</a></th>\n";
		$z .= $i.'   <th class="h"><a href="'.URL.NOTICEBOARD_URL.'/?'.$this->append_search_url($keyword,$limit,$request,$offer,$member_id,$amount,$amount_above,$item,$service,$category_id,$auction,'expiry',$orderdir).append_url(' ?')."\">Expires</a></th>\n";
		$z .= $i.'   <th class="h"><a href="'.URL.NOTICEBOARD_URL.'/?'.$this->append_search_url($keyword,$limit,$request,$offer,$member_id,$amount,$amount_above,$item,$service,$category_id,$auction,'member',$orderdir).append_url(' ?')."\">".ucwords(MEMBERS_NAME_SINGULAR)."</a></th>\n";
		$z .= $i."  </tr>\n";
		
		foreach($notices as $notice) {
			$z .= $i."  <tr>\n";
			
			// title
			// check img code!!!
			$z .= $i."   <td>\n";
			$image_html = '';
			if (ENABLE_IMAGES) {
				if ($notice['imageID']) {
					if ($image->img($notice['imageID'],'t','')) {
						$image_html = $image->img.'<br />';
					}
				}
			}
			
			$title = $notice['title'];
			if ($keyword) {
				if ($num_searches > 1) {
					foreach($keywords as $search_term) {
						$title = highlight($title,$search_term);
					}
				} else {
					$title = highlight($title,$keyword);
				}
			}
			if ($image_html) $title = $image_html.$title;
			$z .= $i.'    <a href="'.URL.NOTICEBOARD_URL.'/'.$notice['noticeboardID'].'/'.append_url(0).'">'.$title."</a></td>\n";
			
			// description
			$z .= $i."   <td>\n";
			$description = $notice['blurb'];
			if ($keyword) {
				if ($num_searches > 1) {
					foreach($keywords as $search_term) {
						$description = highlight($description,$search_term);
					}
				} else {
					$description = highlight($description,$keyword);
				}
			}
			$z .= indent_variable($i.'    ',$description)."</td>\n";
			
			// category
			$z .= $i."   <td>\n";
			$z .= $i.'    <a href="'.URL.NOTICEBOARD_URL.'/?category_id='.$notice['categoryID'].append_url(' ?').'">'.ucwords($this->category_name($notice['categoryID']))."</a></td>\n";
			
			// amount
			$z .= $i."   <td>\n";
			if ($notice['amount'] and $notice['amount'] != '0.00') {
				$z .= $i.'    '.$notice['amount']."</td>\n";
			} else {
				$z .= $i."   </td>\n";
			}
			
			// type
			$z .= $i."   <td>\n";
			if ($notice['type'] == 2) {
				$z .= $i."    Auction</td>\n";
			}
			if ($notice['item'] == 1 and $notice['type'] != 2) {
				$z .= $i."    Item";
				if ($notice['request']) {
					$z .= " requested</td>\n";
				} else {
					$z .= " offered</td>\n";
				}
			}
			if ($notice['item'] == 0 and $notice['type'] != 2) {
				$z .= $i."    Service";
				if ($notice['request']) {
					$z .= " requested</td>\n";
				} else {
					$z .= " offered</td>\n";
				}					
			}
			
			// posted
			$z .= $i."   <td>\n";
			if ($notice['created_year']) {
				$z .= $i."    ".return_month($notice['created_month']).' '.$notice['created_day'].', '.$notice['created_year']."</td>\n";
			} else {
				$z .= $i."   </td>\n";
			}
			
			// expiry
			$z .= $i."   <td>\n";
			if ($notice['expiry_year']) {
				$expiry = array('year' => $notice['expiry_year'], 'month' => $notice['expiry_month'], 'day' => $notice['expiry_day'], 'hour' => $notice['expiry_hour']);
				$z .= $i."    ".date_difference($GLOBALS['date'],$expiry)."</td>\n";
			} else {
				$z .= $i."   </td>\n";
			}
			
			// creator
			$z .= $i."   <td>\n";
			if (user_type() == 0 or $_SESSION["member_validated"] == 0 or $_SESSION["member_suspended"] == 1) {
				$z .= $i.'    <a href="'.URL.MEMBER_LIST_URL.'/'.$notice['accountID'].'/">'.ucwords(MEMBERS_NAME_SINGULAR).' '.$notice['accountID']."</td>\n";
			} elseif (user_type() == 1 or user_type() == 2) {
				$z .= $i.'    <a href="'.URL.MEMBER_LIST_URL.'/'.$notice['accountID'].'/'.append_url(0).'">'.$user->full_name($notice['accountID'])."</td>\n";
			}
			$z .= $i."  </tr>\n";
		}
		$z .= $i." </table>\n";
		$z .= $i."</div>\n";
		$z .= $i."<!-- /noticeboard_listing -->\n";
		return $z;		
	}

	function search_form($i,$keyword,$limit,$start = 0,$request = 0,$offer = 0,$member_id = 0,$amount = 0,$amount_above = 0,$item = 0,$service = 0,$category_id = 0,$auction = 0,$orderby = 0,$orderdir = 0) {
		if (ENABLE_IMAGES) global $image;
		
		$z = $i."<!-- noticeboard_search_form -->\n";
		$z .= $i."<div id=\"noticeboard_search_form\">\n";
		$z .= $i." <fieldset>\n";
		$z .= $i." <legend>\n";
		$z .= $i." Search Parameters\n";
		$z .= $i." </legend>\n";
		$z .= $i." <form enctype=\"multipart/form-data\" id=\"noticeboard\" name=\"noticeboard\" action=\"".URL.NOTICEBOARD_URL.'/'.append_url()."\" method=\"post\">\n";
		
		$z .= $i."  <div class=\"noticeboard_search\">\n";
		$keyword = htmlentities($keyword);
		$z .= $i."   Search Term:<br /><input type=\"text\" name=\"keyword\" id=\"keyword\" value=\"".$keyword."\" />\n";
		$z .= $i."  </div>\n";
		
		$z .= $i."  <div class=\"noticeboard_search\">\n";
		$z .= $i."   Category:<br />\n";
		$z .= $i."   <select id=\"category_id\" name=\"category_id\">\n";
		if ($category_id) {
			$z .= $i."    <option value=\"0\">All</option>\n";
		} else {
			$z .= $i."    <option value=\"0\" selected=\"selected\">All</option>\n";
		}
		foreach ($this->categories as $category) {
			$z .= $i."    <option value=\"".$category['categoryID']."\"".selected('',' ',$category['categoryID'],$category_id).">".ucwords($category['name'])."</option>\n";
		}
		$z .= $i."   </select>\n";
		$z .= $i."  </div><br class=\"left\" >\n";
		
		$z .= $i."  <div class=\"noticeboard_search\">\n";
		$z .= $i."   Asking Price:<br /><input type=\"text\" name=\"amount\" id=\"amount\" value=\"".$amount."\" />\n";
		$z .= $i."   <select id=\"amount_above\" name=\"amount_above\">\n";
		$z .= $i."    <option value=\"0\"".check_selected(0,$amount_above).">And Lower</option>\n";
		$z .= $i."    <option value=\"1\"".check_selected(1,$amount_above).">And Higher</option>\n";
		$z .= $i."   </select>\n";
		$z .= $i."  </div>\n";
		
		$z .= $i."  <div class=\"noticeboard_search\">\n";
		$z .= $i."   ".ucwords(MEMBERS_NAME_SINGULAR)." ID:<br /><input type=\"text\" name=\"noticeboard_member\" id=\"noticeboard_member\" value=\"".$member_id."\" />\n";
		$z .= $i."  </div>\n";
		
		
		$z .= $i."  <div class=\"noticeboard_search\">\n";
		$z .= $i."   <br /><select id=\"request_or_offer\" name=\"request_or_offer\">\n";
		if (!$offer and !$request) {
			$z .= $i."    <option value=\"3\" selected=\"selected\">Offers and Requests</option>\n";
		} else {
			$z .= $i."    <option value=\"3\">Offers and Requests</option>\n";
		}
		$z .= $i."    <option value=\"1\"".check_selected(1,$offer).">Offers Only</option>\n";
		$z .= $i."    <option value=\"2\"".check_selected(1,$request).">Requests Only</option>\n";
		$z .= $i."   </select><br class=\"left\" />\n";
		$z .= $i."  </div>\n";

		$z .= $i."  <div class=\"noticeboard_search\">\n";
		$z .= $i."   ".ucwords(NOTICEBOARD_NAME_SINGULAR)." Type:<br /><select id=\"noticeboard_type\" name=\"noticeboard_type\">\n";
		if (!$item and !$service and !$auction) {
			$z .= $i."    <option value=\"4\" selected=\"selected\">All Types</option>\n";
		} else {
			$z .= $i."    <option value=\"4\">All Types</option>\n";
		}
		$z .= $i."    <option value=\"1\"".check_selected(1,$item).">Items Only</option>\n";
		$z .= $i."    <option value=\"2\"".check_selected(1,$service).">Services Only</option>\n";
		if (ENABLE_AUCTIONS) $z .= $i."   <option value=\"3\"".check_selected(1,$auction).">Auctions Only</option>\n";
		$z .= $i."   </select><br class=\"left\" />\n";
		$z .= $i."  </div>\n";
		
		$z .= $i."  <div class=\"noticeboard_search\">\n";
		$z .= $i."   Show:<br />\n";
		$z .= $i."  <select id=\"limit\" name=\"limit\">\n";
		$z .= $i."   <option value=\"10\"".check_selected(10,$limit).">10</option>\n";
		$z .= $i."   <option value=\"25\"".check_selected(25,$limit).">25</option>\n";
		$z .= $i."   <option value=\"50\"".check_selected(50,$limit).">50</option>\n";
		$z .= $i."   <option value=\"100\"".check_selected(100,$limit).">100</option>\n";
		$z .= $i."   <option value=\"0\"".check_selected(0,$limit).">All</option>\n";
		$z .= $i."  </select>\n";
		$z .= $i."  </div>\n";
		// shouldn't be here!!!
		$z .= $i."  <br class=\"left\" />\n";
		//***
		$z .= $i."  <div class=\"noticeboard_search\">\n";
		$z .= $i."   Order By:<br />\n";
		$z .= $i."   <select id=\"orderby\" name=\"orderby\">\n";
		$z .= $i."    <option value=\"category\"".check_selected('category',$orderby).">Category</option>\n";
		$z .= $i."    <option value=\"title\"".check_selected('title',$orderby).">Title</option>\n";
		$z .= $i."    <option value=\"amount\"".check_selected('amount',$orderby).">Amount</option>\n";
		$z .= $i."    <option value=\"posted\"".check_selected('posted',$orderby).">Date Posted</option>\n";
		$z .= $i."    <option value=\"expiry\"".check_selected('expiry',$orderby).">Expiry Date</option>\n";
		$z .= $i."    <option value=\"member\"".check_selected('member',$orderby).">".ucwords(MEMBERS_NAME_SINGULAR)."</option>\n";
		$z .= $i."   </select>\n";
		$z .= $i."   <select id=\"orderdir\" name=\"orderdir\">\n";
		$z .= $i."    <option value=\"ASC\"".check_selected('ASC',$orderdir).">Ascending</option>\n";
		$z .= $i."    <option value=\"DESC\"".check_selected('DESC',$orderdir).">Descending</option>\n";
		$z .= $i."   </select>\n";
		$z .= $i."  </div>\n";
		
		
		$z .= $i."  <div class=\"noticeboard_search\">\n";
		$z .= $i."   <br /><input id=\"noticeboard_button\" type=\"submit\" name=\"submit\" value=\"Search\" />\n";
		$z .= $i."  </div>\n";
		
		$z .= $i." </form>\n";
		$z .= $i." </fieldset>\n";
		$z .= $i."</div>\n";
		$z .= $i."<!-- /noticeboard_search_form -->\n";
		return $z;
	}
	function sidebar($i,$heading) {
		$mysql = new mysql;
		$mysql->build_array('SELECT noticeboard.noticeboardID,
								noticeboard.request,
								noticeboard.accountID,
								noticeboard.created_month,
								noticeboard.created_day,
								noticeboard.created_year,
								noticeboard.expiry_day,
								noticeboard.expiry_month,
								noticeboard.expiry_year,
								noticeboard.expiry_hour,
								noticeboard.title,
								noticeboard.amount,
								noticeboard.type,
								noticeboard.item,
								noticeboard.categoryID,
								noticeboard.reserve,
								noticeboard.quick_delete 
							FROM noticeboard,
								categories,
								accounts 
							WHERE 
								(noticeboard.expiry_year > '.$GLOBALS['date']['year'].' OR 
									(noticeboard.expiry_year = '.$GLOBALS['date']['year'].' AND noticeboard.expiry_month > '.$GLOBALS['date']['month'].') OR 
									(noticeboard.expiry_year = '.$GLOBALS['date']['year'].' AND noticeboard.expiry_month = '.$GLOBALS['date']['month'].' AND noticeboard.expiry_day > '.$GLOBALS['date']['day'].') OR 
									(noticeboard.expiry_year = '.$GLOBALS['date']['year'].' AND noticeboard.expiry_month = '.$GLOBALS['date']['month'].' AND noticeboard.expiry_day = '.$GLOBALS['date']['day'].' AND noticeboard.expiry_hour > '.$GLOBALS['date']['hour'].') OR 
									(noticeboard.expiry_year = 0 AND noticeboard.expiry_month = 0 AND noticeboard.expiry_day = 0 AND noticeboard.expiry_hour = 0)
								) AND 
								noticeboard.bought = 0 AND 
								noticeboard.expired = 0 AND 
								noticeboard.categoryID = categories.categoryID AND
								noticeboard.accountID = accounts.accountID AND 
								accounts.suspended = 0
							ORDER BY noticeboard.created_year DESC, 
								noticeboard.created_month DESC, 
								noticeboard.created_day DESC
							LIMIT 5');
		if (is_array($mysql->result)) {
			$z = $i."<!-- noticeboard_sidebar -->\n";
			$z .= $i.'<div id="noticeboard_sidebar">'."\n";
			$z .= $i.'<span class="sidebar_heading">'.$heading."</span><br />\n";
			foreach ($mysql->result as $notice) {
				if ($notice['request']) {
					$z .= $i.'<span class="sidebar_title">Request for ';
				} else {
					$z .= $i.'<span class="sidebar_title">Offer of ';
				}
				$z .= '<a href="'.URL.NOTICEBOARD_URL.'/'.$notice['noticeboardID'].'/'.append_url(0).'">'.$notice['title'].'</a> by '.first_name($notice['accountID'])."</span><br />\n";
				$date_difference = date_difference(array('year' => $notice['created_year'], 'month' => $notice['created_month'], 'day' => $notice['created_day']),array('year' => $GLOBALS['date']['year'], 'month' =>  $GLOBALS['date']['month'], 'day' =>  $GLOBALS['date']['day'], 'hour' => $GLOBALS['date']['hour']));
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
				$z .= $i.'<span class="sidebar_details">Posted '.$date_difference.' in <a href="'.URL.NOTICEBOARD_URL.'/?category_id='.$notice['categoryID'].append_url(' ?').'">'.$this->category_name($notice['categoryID'])."</a></span><br /><br />\n";
			}
		} else {
			return '';
		}
		
		$z .= $i.'</div>'."\n";
		$z .= $i."<!-- /noticeboard_sidebar -->\n";
		return $z;
	}
}

?>