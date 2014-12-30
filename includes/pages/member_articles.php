<?php
/***********************************************************************************************************************************
*		Page:			member_articles.php
*		Access:			Member
*		Purpose:		Member admin of articles
*		HTML Holders:	$main_html		:		Entire Contents
*		Template File:																											*/
			$template_filename 			= 		'default';
/*		Classes:																												*/
			require_once('includes/classes/articles.class.php');
			$articles 					= 		new articles;
/*		Indentation:																											
			Set $main_indent on index.php
/*		Disable "Print Page" Link on this page:																					*/
			$disable_print_page			=		true;
/*		CSS Files Called by script:																								*/
		if (!$print) {
			$styles 					.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/article_form.css);\n";
			$styles 					.= 		" @import url(".URL.'templates/'.TEMPLATE."/styles/edit_images.css);\n";
/*		Dynamic Styling:																										*/
			if ((IMAGE_WIDTH_THUMB_NOTICEBOARD + 90) < 210) $image_box_width = 210; else $image_box_width = IMAGE_WIDTH_THUMB_NOTICEBOARD + 90;
			$style->dynamic_elements 	.= 		" div.edit_image_box { float:left; width:".$image_box_width."px; margin-left:5px; margin-bottom:5px; padding:2px 2px 2px 2px; border:1px solid ".TAB_COLOUR."; }\n";
			$style->dynamic_elements 	.= 		" img.edit_image_form {float:right; width:".IMAGE_WIDTH_THUMB_NOTICEBOARD."px; }\n";
		}
/*		Javascript:																										*/

/*		Set Universal Page elements:																							*/
			$links->page_info(1,7);
			$page_name 					= 		$links->name;
			$url 						= 		$links->url;
			$blurb 						= 		$links->body;
/*		Page Title:																												*/
			$title 						= 		SITE_NAME.' '.$page_name;
/*		Page Heading																											*/
			$main_html 					= 		$i."<h1>".SITE_NAME.' '.$page_name."</h1>\n";
/*
************************************************************************************************************************************/

$current_article_id = 0;
$owner = false;
if (isset($_GET['article_id'])) {
	$current_article_id = $_GET['article_id'];
	$existing_article = new articles;
	if ($existing_article->build_article($current_article_id)) {
		if (($_SESSION['member_id'] == $existing_article->member_id) or (user_type() == 2)) {
			$owner = true;
		}
	}
}


// *******  actions ***********
$article_added = false;
$article_edited = false;
if (!isset($_POST['submit'])) $_POST['submit'] = '';
if (!isset($_POST['deletion_confirmed'])) $_POST['deletion_confirmed'] = 0;
if ($_POST['submit']) {
		// *******************************************
		//      article actions
		// *******************************************
		if ($_POST['submit'] == 'Add '.ucwords(ARTICLES_NAME_SINGULAR) or $_POST['submit'] == 'Edit '.ucwords(ARTICLES_NAME_SINGULAR) or $_POST['deletion_confirmed']) {
			// validate form data
			if ($articles->validate_form()) {
				if ($_POST['submit'] == 'Add '.ucwords(ARTICLES_NAME_SINGULAR)) {
					if ($articles->add()) {
						$article_added = true;
						$owner = true;
						$main_html .= $i.'<span class="message">'.ucwords(ARTICLES_NAME_SINGULAR).' Added';
						if (VALIDATE_ARTICLES and user_type() != 2) {
							$main_html .= ' - Your '.strtolower(EVENTS_NAME_SINGULAR).' will not show up until it has been validated';
							if (EMAIL_VALIDATION_SUBMISSIONS and $links->build_url(1,105)) {
								send_single_email(UPDATE_EMAIL,EMAIL_FROM_NAME,VALIDATION_EMAIL,ucwords(ARTICLES_NAME_SINGULAR).' Reviewer','A new '.strtolower(ARTICLES_NAME_SINGULAR).' has been submitted','A new '.strtolower(ARTICLES_NAME_SINGULAR).' entitled "'.$articles->title.'" has been submitted'."\r\n\r\n You can review and validate this ".strtolower(ARTICLES_NAME_SINGULAR).' here: '.URL.$links->complete_url,'A new '.strtolower(ARTICLES_NAME_SINGULAR).' entitled "'.$articles->title.'" has been submitted<br /><br />You can review and validate this '.strtolower(ARTICLES_NAME_SINGULAR).' <a href="'.URL.$links->complete_url.'">here</a>');
							}
						} else {
							if (ENABLE_EMAIL) {
								bulk_membership_email('receive_email_newletter',$_SESSION['member_name'].' has added '.a(ARTICLES_NAME_SINGULAR).' '.strtolower(ARTICLES_NAME_SINGULAR).' entitled "'.$articles->title."\"\r\n\r\n\r\nClick the following link to view this ".strtolower(ARTICLES_NAME_SINGULAR).": ".URL.ARTICLES_URL.'/'.$articles->id.'/',$_SESSION['member_name'].' has added '.a(ARTICLES_NAME_SINGULAR).' '.strtolower(ARTICLES_NAME_SINGULAR).' entitled <strong>'.$articles->title.'</strong><br /><br />Click <a href="'.URL.ARTICLES_URL.'/'.$articles->id.'/">here</a> to view this '.strtolower(ARTICLES_NAME_SINGULAR).'.',ucfirst(a(ARTICLES_NAME_SINGULAR)).' new '.strtolower(ARTICLES_NAME_SINGULAR).' has been posted',EMAIL_FROM_NAME);
							}
						}
						$main_html .= '</span><br /><br />'."\n";
						if (ENABLE_LOG and LOG_ADDITIONS and LOG_ARTICLES) log_action(ucwords(ARTICLES_NAME_SINGULAR).' ID:'.$articles->id.' ('.$articles->title.') added.');
					} else {
						$main_html .= $i.'<span class="message">'.ucwords(ARTICLES_NAME_SINGULAR).' could not be added</span><br /><br />'."\n";
						if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: '.ucwords(ARTICLES_NAME_SINGULAR).' ID:'.$articles->id.' ('.$articles->title.') added.');
						if (ENABLE_ERROR_LOG) log_error('FAILED: '.ucwords(ARTICLES_NAME_SINGULAR).' ID:'.$articles->id.' ('.$articles->title.') added.<br />Error:'.$articles->error);
					}
				} // /add article
				if ($_POST['submit'] == 'Edit '.ucwords(ARTICLES_NAME_SINGULAR) and $current_article_id and $owner) {
					$articles->member_id = $existing_article->member_id;
					if ($articles->edit()) {
						$main_html .= $i.'<span class="message">'.ucwords(ARTICLES_NAME_SINGULAR).' #'.$current_article_id.' edited</span><br /><br />'."\n";
						if (ENABLE_LOG and LOG_EDITS and LOG_ARTICLES) log_action(ucwords(ARTICLES_NAME_SINGULAR).' ID:'.$articles->id.' ('.$articles->title.') edited.');
						$article_edited = true;
					} else {
						$main_html .= $i.'<span class="message">'.ucwords(ARTICLES_NAME_SINGULAR).' #'.$current_article_id.' could not be edited</span><br /><br />'."\n";
						if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: '.ucwords(ARTICLES_NAME_SINGULAR).' ID:'.$articles->id.' ('.$articles->title.') edited.');
						if (ENABLE_ERROR_LOG) log_error('FAILED: '.ucwords(ARTICLES_NAME_SINGULAR).' ID:'.$articles->id.' ('.$articles->title.') edited.<br />Error:'.$articles->error);
					}				
				} // /edit article
				if ($_POST['submit'] == 'Delete '.ucwords(ARTICLES_NAME_SINGULAR) and $current_article_id and $owner) {
					if ($articles->delete($current_article_id)) {
						$main_html .= $i.'<span class="message">'.ucwords(ARTICLES_NAME_SINGULAR).' #'.$current_article_id.' deleted</span><br /><br />'."\n";
						if (ENABLE_LOG and LOG_DELETIONS and LOG_ARTICLES) log_action(ucwords(ARTICLES_NAME_SINGULAR).' ID:'.$current_article_id.' deleted.');
						$current_article_id = 0;
					} else {
						$main_html .= $i.'<span class="message">Could not delete '.strtolower(ARTICLES_NAME_SINGULAR).'</span><br /><br />'."\n";
						if (ENABLE_LOG and LOG_TRIMMED_ERROR) log_action('FAILED: '.ucwords(ARTICLES_NAME_SINGULAR).' ID:'.$current_article_id.' deleted.');
						if (ENABLE_ERROR_LOG) log_error('FAILED: '.ucwords(ARTICLES_NAME_SINGULAR).' ID:'.$current_article_id.' deleted.<br />Error:'.$articles->error);
					}
				} // /delete article
			} else { // /form errors
				$main_html .= $i.'<span class="message">'.$articles->error.'</span><br /><br />'."\n";
			}// /validate form data
		} // /add/edit article
		// *******************************************
		//      image actions
		// *******************************************
		if (ENABLE_IMAGES and $owner) {
			$image_message = '';
			if (($_POST['submit'] == 'Add '.ucwords(ARTICLES_NAME_SINGULAR) or $_POST['submit'] == 'Add Image') and ($article_added or $current_article_id)) {
				if ($article_added) {
					$field_value = $articles->id; 
				} else {
					$field_value = $current_article_id;
				}
				if ($image->substantiate($_SESSION["member_id"],'articleID',$field_value,IMAGE_WIDTH_THUMB_ARTICLE,IMAGE_HEIGHT_THUMB_ARTICLE,IMAGE_WIDTH_PAGE_ARTICLE,IMAGE_HEIGHT_PAGE_ARTICLE)) {
					$image_message .= $image->validate_form($i);
					if (!$image_message) {
						if (!$image->add()) {
							$image_message .= $image->error;
						} else {
							if ($image->id) {
								if (!$articles->set_default_image($field_value,$image->id)) {
									$image_message .= $noticeboard->error;
								} else {
								// We did it !!!!!!
									$main_html .= $i.'<span class="message">Image #'.$image->id.' Added</span><br /><br />'."\n";
								}
							}
						}
					} else {
						$image_message .= $image->error;
					}
				}
			}
			if ($_POST['submit'] == 'Edit Image') {
				$image_message .= $image->validate_form($i);
				if (!$image_message) {
					if (!$image->edit()) {
						$image_message .= $image->error;
					}
				}
			}
			if ($_POST['submit'] == 'Set Default') {
				$image_message .= $image->validate_form($i);
				if (!$image_message) {
					if (!$image->make_default() or !$image->edit()) {
						$image_message .= $image->error;
					}
				}
			}
			if ($_POST['submit'] == 'Delete Image') {
				$image_message .= $image->validate_form($i);
				if (!$image_message) {
					if (!$image->delete()) {
						$image_message .= $image->error;
					}
				}
				if (!$image_message and $current_article_id) {
					$articles->rebuild_default_image($current_article_id);
				}
			}
			
			if ($image_message) {
				$main_html .= $i.'<span class="message">'.$image_message.'</span><br /><br />'."\n";
			}
		}

} // /something submitted
//********************************************************************
//			Main Page

// one unusual screen in this section is deletion confirmation
// deleting an article could delete comments and images
if ($_POST['submit'] == 'Delete '.ucwords(ARTICLES_NAME_SINGULAR) and !$_POST['deletion_confirmed'] and $current_article_id) {
	$main_html .= $articles->confirm_deletion($i,$current_article_id,$url);
} else {
	$main_html .= $articles->article_list($i,$_SESSION['member_id'],$url);
	if ($current_article_id) {
		if ($articles->build_article($current_article_id)) {
			if (($_SESSION['member_id'] == $articles->member_id) or (user_type() == 2)) {
				$main_html .= $articles->form_html($i,'edit',$url);
			} else {
				$main_html .= $i.'<span class="message">You are not authorized to edit this '.strtolower(ARTICLES_NAME_SINGULAR).'</span><br /><br />'."\n";
			}
		} else {
			$main_html .= $articles->form_html($i,'add',$url);
		}	
	} else {
		if ($article_added or $_POST['submit'] == 'Delete '.ucwords(ARTICLES_NAME_SINGULAR)) $articles->clear();
		$main_html .= $articles->form_html($i,'add',$url);
	}
}


?>