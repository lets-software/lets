<?php
/*
This is the default template 

*/
?>

<div id="container">
 <div id="header">
  <div id="header_container">
   <div id="login_html">
<?php  print($login_html); ?>
   </div>   <div id="site_title">    
   <strong>&lt;Insert Logo here&gt;</strong>
   </div>
   <br class="right" />
  </div>
 </div>
 <div id="tab">
  <div id="navigation">
<?php  print($search_sidebar); ?>
   <br />
<?php  print($nav_html); ?>
   <br />
<?php  print($articles_sidebar); ?>
<?php  print($events_sidebar); ?>
<?php  print($noticeboard_sidebar); ?>
<?php  print($faq_sidebar); ?>
<?php  print($links_sidebar); ?>
  </div>
  <div id="main">
<?php  print($heading); ?>
<?php  print($message); ?>
<?php  print($blurb); ?>
<?php  print($articles_search_form); ?>
<?php  print($articles_xhtml); ?>
<?php  print($print_button); ?>
   <div id="holder">
    &nbsp;
   </div>
  </div>
 </div>
</div>
<br />
<br />
<br />
<br />
<br />
<br />
</body>
</html>
