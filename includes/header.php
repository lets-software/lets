<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
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
