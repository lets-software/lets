<?php
/*

	Welcome to LETS-Software
	
	System Requirement:
	- A Web server supporting PHP5
	- PHP 5.2 or above	
	- MySQL 5.0.7 or above
	
	The scripts are organized as follows:
	
	index.php		This file. The only file that encompasses the entire timeline of a request.
					All the html holders that appear in the template files can have different
					indentations which can be set here.
					
					Other variables appear here to be used in other scripts and can be ignored.
	
	function begin	This function is in the file /includes/processing_functions.php
					Many class files are opened and objects created here.
					This function establishes the session and restricts too many login attempts.
					
					The object "site" is created which sets many of the CONSTANTS used.
					The object "links" is created which creates the navigational links,
					sets more CONSTANTS and determines which page has been called.
	
	main_xhtml.php	Once our visitor is logged in (or not) and we know what page they want
					main_xhtml.php opens the relevant scripts in the /includes/pages folder.
					
					Visitors not logged in trying to reach a member only page or pages not
					found are given a message.
					
					This file also checks whether the user wants to print the page.
					
					There is the option to persistently display some html. This includes
					summaries of articles, advertisements (noticeboard), login box, search box, etc.
					These html holder are set if enabled.

	header and template
					Once our html has been set in the appropriate variables we need only
					call the header and template files.
					
					The header inserts javascript and styles if they exist.
					
					The template file has no code other than the variables themselves.
					
	Notes:			Styles are set dynamically from the styles table.
					A member can have a row in this table with their memberID as styleID. (forms yet to be done for this)
					
					To create a new template make/copy a new folder in the template folder
						ex: copy /templates/default/ to /templates/your_template
						You can them totally change around the .css and .php files.
						
	INSTALLATION:	You may wish to enable error_reporting while installing the scripts.
					To do this change error_reporting(0); to //error_reporting(0);
	
					Copy the files to your webhost making sure the index.php file is in the url root directory.
						For example /home/your_account/public_html/ or /home/your_friends_account/public_html/your_account/
					
					Create a table in MySQL and copy the relevant data to /includes/config.php
					
					IMPORTANT: 	The scripts will immediately be in an installation-ready state
								so run the installation routine right after uploading the files.
					
					Open your browser to the url you placed index.php in and follow the prompts.
						For example http://www.your-domain.com/ or http://www.your-friends-domain.com/your_lets/
					
					Simply enter your FTP information in and the script will set the proper permissions.
					
					If this doesn't work for some reason use a different method.					
					This is the required configuration:
						/images		777
						/logs		777
						.htaccess	777
						/logs/<Your Site Name>.log			666
						/logs/<Your Site Name>_Errors.log	666
					
					Once permissions are set refresh your browser and the script should set everything else up.
					
					You can them login with username:1 and password whatever you entered previously.
				
	
*/
session_start();

//error_reporting(0);
//error_reporting(0);

$login_html_indent 				= 		'   ';
$nav_links_indent 				= 		'   ';
$main_indent 					= 		'   ';
$blurb_indent 					= 		'   ';
$print_button_indent 			= 		'   ';
$message_indent					= 		'   ';
$messages_indent				= 		'   ';

// Persistent HTML
$search_sidebar_indent 			= 		'   ';
$noticeboard_sidebar_indent		= 		'   ';
$articles_sidebar_indent		= 		'   ';
$events_sidebar_indent			= 		'   ';
$faq_sidebar_indent				= 		'   ';
$links_sidebar_indent			= 		'   ';
$search_sidebar_indent			= 		'   ';
$login_sidebar_indent			= 		'   ';

$doc_root = $_SERVER['DOCUMENT_ROOT']."/"; //.$_SERVER['PHP_SELF'];



require_once($doc_root.'includes/config.php');
require_once($doc_root.'includes/main_file.php');


if (!isset($_SESSION['lang'])){
	if (isset($_POST['lang'])) {
		$_SESSION['lang']=setLanguage($_POST['lang']);
	}else{
		require_once($doc_root.'includes/lang_select.php');
		die();
	}
}


require_once($doc_root.'includes/processing_functions.php');
require_once($doc_root.'includes/html_functions.php');


// clear HTMl holders:
$main_html 						= 		'';
$message 						= 		'';
$messages 						= 		'';
$search_sidebar					= 		'';
$noticeboard_sidebar			= 		'';
$articles_sidebar				= 		'';
$events_sidebar					= 		'';
$faq_sidebar					= 		'';
$links_sidebar					= 		'';
$login_sidebar					= 		'';

$errors = '';
$javascript = '';
$javascript_in_body = '';
$restricted_page = false;

$default_min_width = 650;
$min_width = $default_min_width;


// *********  Main Part Here! ***********

// Establish database connection and check if member
// If member: session is started and begin returns 0 or error message
$errors = begin($main_indent,$database_host,$database_name,$database_user,$database_password);
//var_dump($errors);

// Check if the database exist, if not, we create it
// TODO: Replace this code with mySQLi class
$mysql = new mysql;
if (!mysql_select_db("$database_name")) {
    echo("<li>Creating database $database_name! Please refresh the page... (F5)</li>");
    mysql_query("CREATE DATABASE $database_name");
    mysql_select_db("$database_name");
}


// Returns $main_html and $title and appends $styles 
require_once($doc_root.'includes/main_xhtml.php');
require_once($doc_root.'includes/header.php');

if (!isset($template_filename)) {
	$template_filename = 'default';
}


require_once($doc_root.'templates/'.TEMPLATE.'/'.$template_filename.'.php');

?>