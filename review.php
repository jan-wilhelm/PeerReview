<?php

function setUTF8($conn) {
	if($stmt = $conn->prepare("SET NAMES utf8")) {
		$stmt->execute();
		unset($stmt);
	}
}

function getReviews($conn, $course, $id) {
	$ret = array();
	setUTF8($conn);
	if($stmt    = $conn->prepare("SELECT * FROM reviews WHERE id = ? AND course = ?")) {
		$stmt->bind_param("ii", $id, $course);
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

function getReviewsBy($conn, $course, $id) {
	$ret = array();
	setUTF8($conn);
	if($stmt    = $conn->prepare("SELECT * FROM reviews WHERE code_reviewer = ? AND course = ?")) {
		$stmt->bind_param("ii", $id, $course);
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

function getReview($conn, $id, $autor, $course) {
	setUTF8($conn);
	if($stmt = $conn->prepare("SELECT * FROM reviews WHERE id = ? and code_reviewer = ? AND course = ?")) {
		$stmt->bind_param("iii", $id, $autor, $course);
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

function getUsersLike($conn, $name, $course) {
	$ret = array();
	$param = strtolower("%".$name."%");
	setUTF8($conn);
	if($stmt = $conn->prepare("SELECT id,name FROM users WHERE LOWER(users.name) LIKE ? AND EXISTS (select 1 from courses where id = users.id and course = ?)")) {
		$stmt->bind_param("si", $param, $course);
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

function getUsersOfCourse($conn, $course) {
	$ret = array();
	if($stmt    = $conn->prepare("SELECT id FROM users WHERE EXISTS (select 1 from courses where id = users.id and course = ?)")) {
		$stmt->bind_param("i", $course);
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

function getUsersForReviews($conn, $course) {
	$ret = array();
	if($stmt    = $conn->prepare("SELECT id FROM users WHERE level != 1 AND EXISTS (select 1 from courses where id = users.id and course = ?) ORDER BY RAND()")) {
		$stmt->bind_param("i", $course);
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

function setTargets($conn, $course, $limit) {
	$us = getUsersForReviews($conn, $course);
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
			setReviewTarget($conn, $us[$i], $us[$x], $course);
		}
	}
	return true;
}

function getReviewTargets($conn, $id, $course) {
	$ret = array();
	if($stmt   = $conn->prepare("SELECT * FROM reviews WHERE code_reviewer = ? AND course = ?")) {
		$stmt->bind_param("ii", $id, $course);
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

function setReviewTarget($conn, $id, $tar, $course) {
	if($stmt = $conn->prepare("INSERT INTO reviews (id, code_reviewer, course) VALUES (?,?,?)")) {
		$stmt->bind_param("iii", $id, $tar, $course);
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

function setCode($conn, $id, $code, $course) {
	if(!startsWith(strtolower($code), "http://")) {
		$code = "http://" . $code;
	}
	if($stmt = $conn->prepare("UPDATE courses SET link = ? WHERE id = ? AND course = ?")) {
		$stmt->bind_param("sii", $code, $id, $course);
		$stmt->execute();
		unset($stmt);
	}
}


function getCode($conn, $id, $course) {
	if($stmt    = $conn->prepare("SELECT * FROM courses WHERE id = ? AND course = ?")) {
		$stmt->bind_param("ii", $id, $course);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows > 0) {
			if ($row = $result->fetch_assoc()) {
				return $row['link'];
			}
		}
		$stmt->free_result();
	} else {
		die($conn->error);
	}
	return "";
}


function getName($conn, $id) {
	setUTF8($conn);
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

function getPoints($conn, $id, $autor, $course) {
	if($stmt    = $conn->prepare("SELECT * FROM reviews WHERE id = ? AND code_reviewer = ? AND course = ?")) {
		$stmt->bind_param("iii", $id, $autor, $course);
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

function getReviewScheme($conn, $course) {
	setUTF8($conn);
	if($stmt = $conn->prepare("SELECT * FROM course_data WHERE course = ?")) {
		$stmt->bind_param("i", $course);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows > 0) {
			if ($row = $result->fetch_assoc()) {
				return $row['review'];
			}
		}
		$stmt->free_result();
	} else {
		echo $conn->error;
	}
	return "";
}

function setReview($conn, $id, $autor, $course, $review) {
	setUTF8($conn);
	if($stmt = $conn->prepare("UPDATE reviews SET review = ? WHERE id = ? AND course = ? AND code_reviewer = ?")) {
		$stmt->bind_param("siii", $review, $id, $course, $autor);
		$stmt->execute();
		unset($stmt);
	}
}

?>