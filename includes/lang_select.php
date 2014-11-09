<?php
// This should be the first page to appear on the screen during installationb to allow user to select his language
var_dump($_SESSION);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<style type="text/css">
 body { font-family: Verdana,Helvetica; font-size: 11px; }
 .center {
	margin-left: auto;
    margin-right: auto;
	width: 50%;
	text-align: center;
    background-color: #C1DAD6;	}
</style>
</html>
<body>
<div class="center">Select you Language:<br>
<form  action="/index.php" method="post">
   <select id="lang" name="lang">
      <option value="ENG">English</option>
	  <option value="FRA">Francais</option>
	</select><br>
	<input type="submit" value="Submit"><br>
</form></div>
</body>
</html>
