<?php

	function getPath($user) {
		if($IS_LOCAL) {
			return __DIR__ . "/html/assets/users/".$user."/avatar/";
		} else {
			$p = explode("/", __DIR__);
			array_pop($p);
			$p = implode("/", $p);
			return $p . "/html/info/assets/users/".$user."/avatar/";
		}
	}

	function getPicName($user) {
		$path = getPath($user);
		if(is_dir( $path )) {
			return $ROOT_SITE . "assets/users/" . $user . "/avatar/" . scandir($path)[2];
		}
		return $ROOT_SITE . "assets/avatar.png";
	}

	function getImageTagForHTML($user) {
		$path = getPicName($user);
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
