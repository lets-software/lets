<?php
/*
This is the default template 
*/
?>
<div id="container">
 <div id="header">
  <div id="header_container">
   <div id="login_html">
<?php echo $login_html; ?>
   </div>
   <div id="site_title">    
    <strong>&lt;Insert Logo here&gt;</strong>
   </div>
   <br class="right" />
  </div>
 </div>
 <div id="tab">
  <div id="navigation">
<?php echo $search_sidebar; ?>
   <br />
<?php echo $nav_html; ?>
   <br />
<?php echo $articles_sidebar; ?>
<?php echo $events_sidebar; ?>
<?php echo $noticeboard_sidebar; ?>
<?php echo $faq_sidebar; ?>
<?php echo $links_sidebar; ?>
  </div>
  <div id="main">
<?php echo $message; ?>
<?php echo $articles_html; ?>
<?php echo $noticeboard_html; ?>
<?php echo $events_html; ?>
<?php echo $faq_html; ?>
<?php echo $links_html; ?>
<?php echo $print_button; ?>
   <div id="holder">
    &nbsp;
   </div>
  </div>
 </div>
</div>
</body>
</html>