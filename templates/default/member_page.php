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
   </div>   <div id="site_title">    
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
   <div id="member_info">
<?php echo $message; ?>
<?php echo $messages; ?>
<?php echo $admin_html; ?>
<?php echo $img_html; ?>
     <br />
<?php echo $member_details; ?>
<?php echo $member_quicklink_html; ?>
     <br class="right" />
<?php echo $thumb_html; ?>
<?php echo $member_profile_html; ?>
     <br class="left">
<?php echo $transaction_html; ?>
<?php echo $print_button; ?>
   </div>
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