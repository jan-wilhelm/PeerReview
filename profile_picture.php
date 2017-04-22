<?php

	function getPath($user, $local) {

		if($local) {
			return __DIR__ . "/html/assets/users/".$user."/avatar/";
		} else {
			$p = explode("/", __DIR__);
			array_pop($p);
			$p = implode("/", $p);
			return $p . "/html/info/assets/users/".$user."/avatar/";
		}
	}

	function getPicName($user, $local, $root) {
		$path = getPath($user, $local);
		if(is_dir( $path )) {
			return $root . "assets/users/" . $user . "/avatar/" . scandir($path)[2];
		}
		return $root . "assets/avatar.png";
	}

	function getImageTagForHTML($user, $local, $root) {
		$path = getPicName($user, $local, $root);
		return '<img class="profile-pic" src="' . $path . '" alt="Avatar">';
	}

	function imagecreatefromfile( $filename ) {
	    if (!file_exists($filename)) {
	        throw new InvalidArgumentException('File "'.$filename.'" not found.');
	    }
	    switch ( strtolower( pathinfo( $filename, PATHINFO_EXTENSION ))) {
	        case 'jpeg':
	        case 'jpg':
	            return imagecreatefromjpeg($filename);
	        break;

	        case 'png':
	            return imagecreatefrompng($filename);
	        break;

	        case 'gif':
	            return imagecreatefromgif($filename);
	        break;

	        default:
	            throw new InvalidArgumentException('File "'.$filename.'" is not valid jpg, png or gif image.');
	        break;
	    }
	}

?>
