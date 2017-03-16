<?php
function getReviews($conn, $id) {
	$ret = array();
	if($stmt    = $conn->prepare("SELECT * FROM reviews WHERE id = ?")) {
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				$ret[] = $row;
			}
		}
		$stmt->free_result();
	} else {
		echo $conn->error;
	}
	
	return $ret;
}

function getReviewsBy($conn, $id) {
	$ret = array();
	if($stmt    = $conn->prepare("SELECT * FROM reviews WHERE code_reviewer = ?")) {
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				$ret[] = $row;
			}
		}
		$stmt->free_result();
	} else {
		echo $conn->error;
	}
	
	return $ret;
}


function getReview($conn, $id, $autor) {
	if($stmt    = $conn->prepare("SELECT * FROM reviews WHERE id = ? and code_reviewer = ?")) {
		$stmt->bind_param("ii", $id, $autor);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows > 0) {
			if ($row = $result->fetch_assoc()) {
				return $row;
			}
		}
		$stmt->free_result();
	} else {
		echo $conn->error;
	}
	
	return array();
}

function getUsersLike($conn, $name) {
	$ret = array();
	$param = strtolower("%".$name."%");
	if($stmt = $conn->prepare("SELECT id,name FROM users WHERE LOWER(users.name) LIKE ?")) {
		$stmt->bind_param("s", $param);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				$ret[] = $row;
			}
		}
		$stmt->free_result();
	} else {
		echo $conn->error;
	}
	return $ret;
}


function getUsers($conn) {
	$ret = array();
	if($stmt    = $conn->prepare("SELECT id FROM users")) {
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				$ret[] = $row['id'];
			}
		}
		$stmt->free_result();
	} else {
		echo $conn->error;
	}
	return $ret;
}


function getUsersForReviews($conn) {
	$ret = array();
	if($stmt    = $conn->prepare("SELECT id FROM users WHERE level != 1 ORDER BY RAND()")) {
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				$ret[] = $row['id'];
			}
		}
		$stmt->free_result();
	} else {
		echo $conn->error;
	}
	return $ret;
}

function setTargets($conn, $limit) {
	$us = getUsersForReviews($conn);
	shuffle($us);
	$l = count($us);
	if($l < 4) {
		return false;
	}
	for ($i=0; $i < count($us); $i++) {
		for ($j=$i + 1; $j < $i + $limit + 1; $j++) { 
			$x = $j;
			if ($x > $l - 1) {
				$x = $x - $l;
			}
			setReviewTarget($conn, $us[$i], $us[$x]);
		}
	}
	return true;
}

function getReviewTargets($conn, $id) {
	$ret = array();
	if($stmt   = $conn->prepare("SELECT * FROM reviews WHERE code_reviewer = ?")) {
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				$ret[] = array('name' => getName($conn, $row['id']), 'id' => $row['id']);
			}
		}
		$stmt->free_result();
	} else {
		die($conn->error);
	}
	return $ret;
}

function setReviewTarget($conn, $id, $tar) {
	if($stmt = $conn->prepare("INSERT INTO reviews (id, code_reviewer) VALUES (?,?)")) {
		$stmt->bind_param("ii", $id, $tar);
		$stmt->execute();
		unset($stmt);
	} else {
		echo $conn->error;
		return false;
	}
	return true;
}

function startsWith($haystack, $needle) {
     return (substr($haystack, 0, strlen($needle)) === $needle);
}

function endsWith($haystack, $needle) {
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }
    return (substr($haystack, -$length) === $needle);
}

function setCode($conn, $id, $code) {
	if(!startsWith(strtolower($code), "http://")) {
		$code = "http://" . $code;
	}
	if($stmt = $conn->prepare("UPDATE users SET code_link = ? WHERE id = ?")) {
		$stmt->bind_param("si", $code, $id);
		$stmt->execute();
		unset($stmt);
	}
}


function getCode($conn, $id) {
	if($stmt    = $conn->prepare("SELECT * FROM users WHERE id = ?")) {
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows > 0) {
			if ($row = $result->fetch_assoc()) {
				return $row['code_link'];
			}
		}
		$stmt->free_result();
	} else {
		die($conn->error);
	}
	return "";
}


function getName($conn, $id) {
	if($stmt    = $conn->prepare("SELECT * FROM users WHERE id = ?")) {
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows > 0) {
			if ($row = $result->fetch_assoc()) {
				return $row['name'];
			}
		}
		$stmt->free_result();
	} else {
		echo $conn->error;
	}
	return "";
}

function getPoints($conn, $id, $autor) {
	if($stmt    = $conn->prepare("SELECT * FROM reviews WHERE id = ? AND code_reviewer = ?")) {
		$stmt->bind_param("ii", $id, $autor);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows > 0) {
			if ($row = $result->fetch_assoc()) {
				$ret = 0;
				foreach ($row as $r => $v) {
					if(is_int($v) && !endsWith($r,"_c") && $r != 'id' && $r != 'isset' && $r != 'code_reviewer') {
						$ret = $ret + $v;
					}
				}
				return $ret;
			}
		}
		$stmt->free_result();
	} else {
		echo $conn->error;
	}
	return 0;
}

function userExists($conn, $id) {
	if($stmt    = $conn->prepare("SELECT id FROM users WHERE id = ?")) {
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows > 0) {
			if ($row = $result->fetch_assoc()) {
				return true;
			}
		}
		$stmt->free_result();
	} else {
		echo $conn->error;
	}
	return false;
}

function setReviewParam($conn, $id, $autor, $param, $val) {
	$param = strtolower($param);
	if($stmt = $conn->prepare("UPDATE reviews SET ".$param." = ? WHERE id = ? AND code_reviewer = ?")) {
		if(is_int($val)) {
			$stmt->bind_param("iii", $val, $id, $autor);
		} else {
			$stmt->bind_param("sii", $val, $id, $autor);
		}
		$stmt->execute();
		unset($stmt);
	}
	if($stmt = $conn->prepare("UPDATE reviews SET isset = 1 WHERE id = ? AND code_reviewer = ?")) {
		$stmt->bind_param("ii", $id, $autor);
		$stmt->execute();
		unset($stmt);
	}
}

?>