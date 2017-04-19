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

?>
