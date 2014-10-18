<?
/*
This is the default template 

*/
?>

<div id="container">
 <div id="header">
  <div id="header_container">
   <div id="login_html">
<?= $login_html; ?>
   </div>   <div id="site_title">    
   <strong>&lt;Insert Logo here&gt;</strong>
   </div>
   <br class="right" />
  </div>
 </div>
 <div id="tab">
  <div id="navigation">
<?= $search_sidebar; ?>
   <br />
<?= $nav_html; ?>
   <br />
<?= $articles_sidebar; ?>
<?= $events_sidebar; ?>
<?= $noticeboard_sidebar; ?>
<?= $faq_sidebar; ?>
<?= $links_sidebar; ?>
  </div>
  <div id="main">
   <div id="member_info">
<?= $message; ?>
<?= $messages; ?>
<?= $admin_html; ?>
<?= $img_html; ?>
     <br />
<?= $member_details; ?>
<?= $member_quicklink_html; ?>
     <br class="right" />
<?= $thumb_html; ?>
<?= $member_profile_html; ?>
     <br class="left">
<?= $transaction_html; ?>
<?= $print_button; ?>
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