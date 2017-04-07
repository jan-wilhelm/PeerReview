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
	if($stmt    = $conn->prepare("SELECT * FROM reviews WHERE id = ? AND course = ? AND review_id = ? ORDER BY review DESC")) {
		$stmt->bind_param("iii", $id, $course, $reviewid);
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

function getReviewsBy($conn, $course, $id, $reviewid) {
	$ret = array();
	setUTF8($conn);
	if($stmt    = $conn->prepare("SELECT * FROM reviews WHERE code_reviewer = ? AND course = ? AND review_id = ?")) {
		$stmt->bind_param("iii", $id, $course, $reviewid);
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

function getReview($conn, $id, $autor, $course, $reviewid) {
	setUTF8($conn);
	if($stmt = $conn->prepare("SELECT * FROM reviews WHERE id = ? and code_reviewer = ? AND course = ? AND review_id = ?")) {
		$stmt->bind_param("iiii", $id, $autor, $course, $reviewid);
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

function getNewReviewId($conn, $course) {
	if($stmt    = $conn->prepare("SELECT MAX(review_id) + 1 AS `new_id` FROM reviews WHERE course = ?")) {
		$stmt->bind_param("i", $course);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows > 0) {
			if ($row = $result->fetch_assoc()) {
				return $row['new_id'];
			}
		}
		$stmt->free_result();
	} else {
		echo $conn->error;
	}
	return random_int(0, 100);
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

function getUsersOfCourseApartFromAdmin($conn, $course) {
	$ret = array();
	if($stmt    = $conn->prepare("SELECT id FROM users WHERE EXISTS (select 1 from courses where id = users.id and course = ?) AND level != 1")) {
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
	$id = getNewReviewId($conn, $course);
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
			setReviewTarget($conn, $us[$i], $us[$x], $course, $id);
		}
	}
	return true;
}

function getReviewTargets($conn, $id, $course, $reviewid) {
	$ret = array();
	if($stmt   = $conn->prepare("SELECT * FROM reviews WHERE code_reviewer = ? AND course = ? AND review_id = ?")) {
		$stmt->bind_param("iii", $id, $course, $reviewid);
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

function setReviewTarget($conn, $id, $tar, $course, $reviewid) {
	if($stmt = $conn->prepare("INSERT INTO reviews (id, code_reviewer, course, review_id) VALUES (?,?,?,?)")) {
		$stmt->bind_param("iiii", $id, $tar, $course, $reviewid);
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

function setCode($conn, $id, $code, $course, $reviewid) {
	if(!startsWith(strtolower($code), "http://")) {
		$code = "http://" . $code;
	}
	if($stmt = $conn->prepare("UPDATE courses SET link = ? WHERE id = ? AND course = ? AND review_id = ?")) {
		$stmt->bind_param("siii", $code, $id, $course, $reviewid);
		$stmt->execute();
		unset($stmt);
	}
}


function getCode($conn, $id, $course, $reviewid) {
	if($stmt    = $conn->prepare("SELECT link FROM reviews WHERE id = ? AND course = ? AND review_id = ?")) {
		$stmt->bind_param("iii", $id, $course, $reviewid);
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

function getNewestCode($conn, $id, $course) {
	if($stmt    = $conn->prepare("SELECT link FROM reviews WHERE id = ? AND course = ? ORDER BY review_id DESC LIMIT 1")) {
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

function getFinishedReviewsOfCourse($conn, $course) {
	if($stmt = $conn->prepare('SELECT avg( review IS NOT NULL ) * 100 AS "avg" FROM reviews WHERE course = ?')) {
		$stmt->bind_param("i", $course);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows > 0) {
			if ($row = $result->fetch_assoc()) {
				return $row['avg'];
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
			return true;
		}
		$stmt->free_result();
	} else {
		echo $conn->error;
	}
	return false;
}

function getCourseByKey($conn, $key) {
	if($stmt = $conn->prepare("SELECT course FROM course_data WHERE signin_key = ?")) {
		$stmt->bind_param("s", $key);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows > 0) {
			if ($row = $result->fetch_assoc()) {
				return $row['course'];
			}
		}
		$stmt->free_result();
	} else {
		echo $conn->error;
	}
	return 0;
}

function getCourseName($conn, $id) {
	if($stmt = $conn->prepare("SELECT name FROM course_data WHERE course = ?")) {
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


function isUserInCourse($conn, $id, $course) {
	if($stmt = $conn->prepare("SELECT 1 from courses WHERE id = ? AND course = ?")) {
		$stmt->bind_param("ii", $id, $course);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows > 0) {
			return true;
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

function setReviewScheme($conn, $course, $scheme) {
	setUTF8($conn);
	if($stmt = $conn->prepare("UPDATE course_data SET review = ? WHERE course = ?")) {
		$stmt->bind_param("si", $scheme, $course);
		$stmt->execute();
		unset($stmt);
	}
}

function setReview($conn, $id, $autor, $course, $review, $reviewid) {
	setUTF8($courseonn);
	if($stmt = $conn->prepare("UPDATE reviews SET review = ?, modified = NOW() WHERE id = ? AND course = ? AND code_reviewer = ?")) {
		$stmt->bind_param("siii", $review, $id, $course, $autor);
		$stmt->execute();
		unset($stmt);
	}
}

function addUserToCourse($conn, $id, $course) {
	if($stmt = $conn->prepare("INSERT INTO courses (id, course) VALUES (?,?)")) {
		$stmt->bind_param("ii", $id, $course);
		$stmt->execute();
		unset($stmt);
	} else {
		echo $conn->error;
		return false;
	}
	return true;
}

function handleKeyTyped($conn, $id, $key) {
	$course = getCourseByKey($conn, $key);
	if($course == "") {
		return -3;
	} elseif (isUserInCourse($conn, $id, $course)) {
		return -2;
	} else {
		if (addUserToCourse($conn, $id, $course) != true){
			return -1;
		} else {
			return 0;
		}
	}
}

function getReviewsOfToday($conn, $course) {
	if($stmt = $conn->prepare("SELECT DATE(`reviews`.`modified`) AS `date`, COUNT(*) AS `count` FROM `reviews` WHERE course = ? AND `reviews`.`modified` BETWEEN DATE_SUB(NOW(), INTERVAL 1 MONTH) AND NOW() GROUP BY `date` ORDER BY `date`")) {
		$stmt->bind_param("i", $course);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows > 0) {
			if ($row = $result->fetch_assoc()) {
				return $row['count'];
			}
		}
		$stmt->free_result();
	} else {
		echo $conn->error;
	}
	return 0;
}

function getReviewsSinceLastLoginForUser($conn, $id, $course) {
	if($stmt = $conn->prepare("SELECT COUNT(*) as `numbers` FROM `info`.`reviews` WHERE `modified` > (SELECT `for_date` FROM `login_history` WHERE `user` = ? AND course = ? ORDER BY `for_date` DESC LIMIT 1,1)")) {
		$stmt->bind_param("ii", $id, $course);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows > 0) {
			if ($row = $result->fetch_assoc()) {
				return $row['numbers'];
			}
		}
		$stmt->free_result();
	} else {
		echo $conn->error;
	}
	return 0;
}

function getLoginsOfLastMonth($conn) {
	$sql = "SELECT DATE(`login_history`.`for_date`) AS `date`, COUNT(`login_history`.`user`) AS `count` FROM `login_history` WHERE `login_history`.`for_date` BETWEEN DATE_SUB(NOW(), INTERVAL 1 MONTH) AND NOW() GROUP BY `date` ORDER BY `date`";
	$ret = array();
	if($stmt = $conn->prepare($sql)) {
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				$ret[$row['date']] = $row['count'];
			}
		}
		$stmt->free_result();
	} else {
		echo $conn->error;
	}
	return $ret;
}



?>
