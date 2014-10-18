<?php
/*
This is the default template 

*/
?>

<div id="container">
 <div id="header">
  <div id="header_container">
   <div id="login_html">
<?php $login_html; ?>
   </div>   <div id="site_title">    
   <strong>&lt;Insert Logo here&gt;</strong>
   </div>
   <br class="right" />
  </div>
 </div>
 <div id="tab">
  <div id="navigation">
<?php $search_sidebar; ?>
   <br />
<?php $nav_html; ?>
   <br />
<?php $articles_sidebar; ?>
<?php $events_sidebar; ?>
<?php $noticeboard_sidebar; ?>
<?php $faq_sidebar; ?>
<?php $links_sidebar; ?>
  </div>
  <div id="main">
<?php $message; ?>
<?php $main_html; ?>
<?php $print_button; ?>
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
