<?php
// This should be the first page to appear on the screen during installation to allow user to select his language
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Select Language</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="/templates/default/styles/install.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div class="basic-grey"><h1>Select you Language:</h1><br />
<form  action="/index.php" method="post">
   <select id="lang" name="lang">
      <option value="en_US">English</option>
      <option value="fr_FR">Français</option>
    </select><br />
    <input type="submit" value="Submit"><br />
</form></div>
</body>
</html>
