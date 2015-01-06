<?php
/**
* 
*/

function file_perms($file, $octal = false)
{
    if(!file_exists($file)) return false;

    $perms = fileperms($file);

    $cut = $octal ? 2 : 3;

    return substr(decoct($perms), $cut);
}


if (!function_exists('ftp_chmod')) {
	function ftp_chmod($ftp_stream, $mode, $filename) {
		return ftp_site($ftp_stream, sprintf('CHMOD %o %s', $mode, $filename));
	}
}


function permission($filename) {
	$perms = fileperms($filename);
	if     (($perms & 0xC000) == 0xC000) { $info = 's'; }
	elseif (($perms & 0xA000) == 0xA000) { $info = 'l'; }
	elseif (($perms & 0x8000) == 0x8000) { $info = '-'; }
	elseif (($perms & 0x6000) == 0x6000) { $info = 'b'; }
	elseif (($perms & 0x4000) == 0x4000) { $info = 'd'; }
	elseif (($perms & 0x2000) == 0x2000) { $info = 'c'; }
	elseif (($perms & 0x1000) == 0x1000) { $info = 'p'; }
	else { $info = 'u'; }

	$info .= (($perms & 0x0100) ? 'r' : '-');
	$info .= (($perms & 0x0080) ? 'w' : '-');
	$info .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x' ) : (($perms & 0x0800) ? 'S' : '-'));

	$info .= (($perms & 0x0020) ? 'r' : '-');
	$info .= (($perms & 0x0010) ? 'w' : '-');
	$info .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x' ) : (($perms & 0x0400) ? 'S' : '-'));

	$info .= (($perms & 0x0004) ? 'r' : '-');
	$info .= (($perms & 0x0002) ? 'w' : '-');
	$info .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x' ) : (($perms & 0x0200) ? 'T' : '-'));

	return $info;
}


function dir_contents($dir) {
	if ($dir[strlen($dir)-1] != '/') $dir .= '/';

	if (!is_dir($dir)) return array();

	$dir_handle  = opendir($dir);
	$dir_objects = array();
	while ($object = readdir($dir_handle))
	if (!in_array($object, array('.','..'))) {
		$filename    = $dir . $object;
		$file_object = array(
			'name' => $object,
			'size' => filesize($filename),
			'perms' => permission($filename),
			'type' => filetype($filename),
			'time' => date("d F Y H:i:s", filemtime($filename))
		);
		$dir_objects[] = $file_object;
	}

	return $dir_objects;
}