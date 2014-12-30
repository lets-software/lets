<?php
/**
* This file create the file include/Configdb.php
* @category Installer
* @author Gael Langlais
* @license http://opensource.org/licenses/gpl-2.0.php GNU Public License V2
*/

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="'.URL.'/templates/'.TEMPLATE.'/styles/install.css" rel="stylesheet" type="text/css">
</head><body>'
;

echo "<form action=\"{$_SERVER['REQUEST_URI']}\" method=\"post\" class=\"basic-grey\">\n";        
echo '<h1>Setup Form';
echo '        <span>Please fill all the texts in the fields.</span>';
echo '</h1><br /><br />';