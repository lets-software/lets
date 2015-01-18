<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<?php if(isset($styles_link)){ echo $styles_link; } ?>
<?php echo $styles;?>
<title><?php echo $title; ?></title>
<script type="text/javascript">
<!--
<?php echo $javascript; ?>
-->
</script>
</head>

<?php
if ($javascript_in_body) {
    print '<body '.$javascript_in_body.'>'."\n";
} else {
    print '<body>'."\n";
}
print HEADER_HTML;
?>
