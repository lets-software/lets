<?php
// This should be the first page to appear on the screen during installation to allow user to select his language
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="stylesheet" type="text/css" href="/templates/default/styles/install.css">
</html>
<body>
<form  action="/index.php" method="post" class="basic-grey">
	<h1>Select you Language:</h1>
   <select id="lang" name="lang">
      <option value="ENG">English</option>
	  <option value="FRA">Francais</option>
	</select><br>
	<input type="submit" value="Submit"><br>
</form>
</body>
</html>
