<?php

	function getPath($user) {
		return __DIR__ . "/html/assets/users/".$user."/avatar/";
	}

	function getPicName($user) {
		$path = getPath($user);
		if(is_dir( $path )) {
			return "/assets/users/" . $user . "/avatar/" . scandir($path)[2];
		}
		return "/assets/avatar.png";
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
