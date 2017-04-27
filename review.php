<?php

function setUTF8($conn) {
	if($stmt = $conn->prepare("SET NAMES utf8")) {
		$stmt->execute();
		unset($stmt);
	}
}

function getReviewsFor($conn, $course, $id, $reviewid) {
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

function getNewReviewId($conn) {
	if($stmt    = $conn->prepare("SELECT MAX(id) + 1 AS `new_id` FROM review_schemes")) {
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows > 0) {
			if ($row = $result->fetch_assoc()) {
				if(isset($row['new_id'])) {
					return $row['new_id'];
				} else {
					return 0;
				}
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

function setTargets($conn, $course, $limit, $reviewId) {
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
			setReviewTarget($conn, $us[$i], $us[$x], $course, $reviewId);
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

function setCode($conn, $id, $code, $course, $reviewId) {
	if(!startsWith(strtolower($code), "http://")) {
		$code = "http://" . $code;
	}
	if($stmt = $conn->prepare("INSERT INTO links (user, course, review_id, link) VALUES(?, ?, ?, ?) ON DUPLICATE KEY UPDATE link=?")){
		$stmt->bind_param("iiiss", $id, $course, $reviewId, $code, $code);
		$stmt->execute();
		unset($stmt);
	}
}

function setSubmissionType($conn, $id, $type, $course, $reviewId) {
	if($stmt = $conn->prepare("UPDATE reviews SET submission_type = ? WHERE id = ? AND course = ? AND review_id = ?")){
		$stmt->bind_param("iiii", $type, $id, $course, $reviewId);
		$stmt->execute();
		unset($stmt);
	}
}

function setScript($conn, $id, $scriptId, $course, $reviewId) {
	if($stmt = $conn->prepare("UPDATE reviews SET submission_id = ? WHERE id = ? AND course = ? AND review_id = ?")){
		$stmt->bind_param("iiii", $scriptId, $id, $course, $reviewId);
		$stmt->execute();
		unset($stmt);
	}
}

function createScript($conn, $id, $script, $name) {
	if($stmt = $conn->prepare('INSERT INTO scripts (user, script, name, last_modified) VALUES ( ?, ?, ?, NOW() )')){
		$stmt->bind_param("iss", $id, $script, $name);
		$stmt->execute();

		$stmt = $conn->prepare("SELECT LAST_INSERT_ID() AS `id`");
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows > 0) {
			if ($row = $result->fetch_assoc()) {
				return $row['id'];
			}
		}
		unset($stmt);
	} else {
		die($conn->error);
	}
	return -1;
}

function getScript($conn, $id, $course, $reviewid) {
	if($stmt    = $conn->prepare("SELECT * FROM reviews LEFT JOIN scripts ON scripts.script_id = reviews.submission_id WHERE submission_type = 1 AND id = ? AND course = ? AND review_id = ? ")) {
		$stmt->bind_param("iii", $id, $course, $reviewid);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows > 0) {
			if ($row = $result->fetch_assoc()) {
				return $row;
			}
		}
		$stmt->free_result();
	} else {
		die($conn->error);
	}
	return null;
}

function getCode($conn, $id, $course, $reviewid) {
	if($stmt    = $conn->prepare("SELECT link FROM links WHERE user = ? AND course = ? AND review_id = ?")) {
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

function getAllReviewIDsOfUser($conn, $id, $course) {
	$ret = array();
	if($stmt    = $conn->prepare("SELECT review_id FROM reviews WHERE id = ? AND course = ? GROUP BY review_id")) {
		$stmt->bind_param("ii", $id, $course);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				$ret[] = $row['review_id'];
			}
		}
		$stmt->free_result();
	} else {
		die($conn->error);
	}
	return $ret;
}

function getAllReviewIDsOfCourse($conn, $course) {
	$ret = array();
	if($stmt    = $conn->prepare("SELECT id FROM review_schemes WHERE course = ?")) {
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
		die($conn->error);
	}
	return $ret;
}

/**
 * Get the name of a course by its ID
 * @param  mysqli $conn The MySQL connection
 * @param  int $id   The ID of the course
 * @return string       The name of the course
 */
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

/**
 * Get the percentage of overall finished reviews in one cours
 * @param  mysql $conn   The MySQL connection
 * @param  int $course The ID of the course
 * @return float         The percentage
 */
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

/**
 * Get the percentage of finished reviews of one id in one specific course
 * @param  mysqli $conn     The MySQL connection
 * @param  int $course   The ID of the course
 * @param  int $reviewId The ID of the review
 * @return float           The percentage of finished reviews / total reviews
 */
function getFinishedReviewsOfCourseAndId($conn, $course, $reviewId) {
	if($stmt = $conn->prepare('SELECT avg( review IS NOT NULL ) * 100 AS "avg" FROM reviews WHERE course = ? AND review_id = ?')) {
		$stmt->bind_param("ii", $course, $reviewId);
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

/**
 * Check if an user with the given ID exists
 * @param  mysqli $conn The MySQL connection
 * @param  int $id   The ID of the user to check for
 * @return boolean       True if the user exists, false otherwise
 */
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

/**
 * Get the ID of a course by its key
 * @param  mysqli $conn The MySQL connection
 * @param  string $key  The key
 * @return int       The ID of the course
 */
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

/**
 * Get the Key of a course by its id
 * @param  mysqli $conn The MySQL connection
 * @param  int $id  The ID
 * @return string       The key of the course
 */
function getKeyOfCourse($conn, $id) {
	if($stmt = $conn->prepare("SELECT signin_key FROM course_data WHERE course = ?")) {
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows > 0) {
			if ($row = $result->fetch_assoc()) {
				return $row['signin_key'];
			}
		}
		$stmt->free_result();
	} else {
		echo $conn->error;
	}
	return 0;
}


/**
 * Get the name of a course by its course_id
 * @param  mysqli $conn   The mysql connection
 * @param  int $course The id of the course
 * @return string    The name of the course
 */
function getCourseName($conn, $course) {
	if($stmt = $conn->prepare("SELECT name FROM course_data WHERE course = ?")) {
		$stmt->bind_param("i", $course);
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

function getReviewSchemeForID($conn, $course, $reviewId) {
	setUTF8($conn);
	if($stmt = $conn->prepare("SELECT * FROM review_schemes WHERE course = ? AND id = ?")) {
		$stmt->bind_param("ii", $course, $reviewId);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows > 0) {
			if ($row = $result->fetch_assoc()) {
				return $row['review_scheme'];
			}
		}
		$stmt->free_result();
	} else {
		echo $conn->error;
	}
	return "";
}

function getReviewNameForID($conn, $reviewId) {
	setUTF8($conn);
	if($stmt = $conn->prepare("SELECT * FROM review_schemes WHERE id = ?")) {
		$stmt->bind_param("i", $reviewId);
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

function addReviewScheme($conn, $course, $review) {
	setUTF8($conn);
	if($stmt = $conn->prepare("INSERT INTO review_schemes (course, review_scheme, name, id) VALUES (?,?,?,?)")) {
		$encoded = json_encode($review);
		$name = $review->name;
		$id = getNewReviewId($conn);
		$stmt->bind_param("issi", $course, $encoded, $name, $id);
		$stmt->execute();
		unset($stmt);
	}
}

function createCourse($conn, $courseName, $key) {
	setUTF8($conn);
	if($stmt = $conn->prepare("INSERT INTO course_data (signin_key, name) VALUES(?,?)")) {
		$stmt->bind_param("ss", $courseName, $key);
		$stmt->execute();
		unset($stmt);
	}
}

function setReview($conn, $id, $autor, $course, $review, $reviewid) {
	setUTF8($conn);
	if($stmt = $conn->prepare("UPDATE reviews SET review = ?, modified = NOW() WHERE id = ? AND course = ? AND code_reviewer = ? AND review_id = ?")) {
		$stmt->bind_param("siiii", $review, $id, $course, $autor, $reviewid);
		$stmt->execute();
		unset($stmt);
	}
}

function setCourseName($conn, $course, $name) {
	setUTF8($conn);
	if($stmt = $conn->prepare("UPDATE course_data SET name = ? WHERE course = ?")) {
		$stmt->bind_param("si", $name, $course);
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

/**
 * Get the total amount of logins since a given interval
 * @param  mysqli $conn     The MySQL connection
 * @param  mysqlinterval $interval The interval to check
 * @return int           The number of logins
 */
function getTotalLoginsOfTimeInterval($conn, $interval) {
	if($stmt = $conn->prepare("SELECT COUNT(*) as 'count' from login_history WHERE for_date BETWEEN DATE_SUB(NOW(), INTERVAL ".$interval.") AND NOW()")) {
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

/**
 * Generate a random password of given length
 * @param  int $length The required length of the password
 * @return string         A new random passwort containg letters [a-z], [A-Z] and [0-9]
 */
function randomPassword($length){
	$alphabet    = 'abcdefghjkmnopqrstuvwxyzABCDEFGHJKMNOPQRSTUVWXYZ1234567890';
	$pass        = array();
	$alphaLength = strlen($alphabet) - 1;
	for ($i = 0; $i < $length; $i++) {
		$n      = rand(0, $alphaLength);
		$pass[] = $alphabet[$n];
	}
	return implode($pass);
}

/**
 * Get the number of updated reviews for a user since his last login
 * @param  mysqli $conn   The MySQL connection
 * @param  int $id     The id of the user
 * @param  int $course The ID of the course to check for
 * @return int         The amount of upated reviews
 */
function getReviewsSinceLastLoginForUser($conn, $id, $course) {
	if($stmt = $conn->prepare("SELECT COUNT(*) as `numbers` FROM `info`.`reviews` WHERE `modified` > (SELECT `for_date` FROM `login_history` WHERE `user` = ? AND course = ? ORDER BY `for_date` DESC LIMIT 1,1) AND id = ?")) {
		$stmt->bind_param("iii", $id, $course, $id);
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

/**
 * Get the total amount of courses in one course
 * @param  mysqli $conn The MySQL connection
 * @param  int 	$course The ID of the course
 * @return int       The amount
 */
function getNumberOfUsersInCourse($conn, $course) {
	if($stmt = $conn->prepare("select count(*) as 'count' from users where exists (select 1 from courses where id = users.id and course = ?)")) {
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

/**
 * Get the total amount of users
 * @param  mysqli $conn The MySQL connection
 * @return int       The amount
 */
function getNumberOfUsersTotal($conn) {
	if($stmt = $conn->prepare("select count(*) as 'count' from users")) {
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

/**
 * Get the total amount of courses
 * @param  mysqli $conn The MySQL connection
 * @return int       The amount
 */
function getNumberOfCoursesTotal($conn) {
	if($stmt = $conn->prepare("select count(*) as 'count' from course_data")) {
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

/**
 * Get all courses of a specific user
 * @param  mysqli $conn The MySQL connection
 * @param  int $id   The ID of the user
 * @return array       An array of all IDs of courses
 */
function getCoursesOfUser($conn, $id) {
	$ret = array();
	if($stmt = $conn->prepare("select * from courses where id = ?")) {
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				$ret[] = $row['course'];
			}
		}
		$stmt->free_result();
	} else {
		echo $conn->error;
	}
	return $ret;
}

/**
 * Get the overall total number of logins
 * @param  mysqli $conn The MySQL connection
 * @return int       The number of logings
 */
function getTotalLogins($conn) {
	if($stmt = $conn->prepare("select count(*) as 'count' from login_history")) {
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

/**
 * Get the logins of the past to weeks.
 * This will be used for chart purposes only.
 * @param  mysqli $conn The MySQL connection
 * @return array       An array of dates => number of logins
 */
function getLoginsOfLastTwoWeeks($conn) {
	$sql = "SELECT DATE(`login_history`.`for_date`) AS `date`, COUNT(`login_history`.`user`) AS `count` FROM `login_history` WHERE `login_history`.`for_date` BETWEEN DATE_SUB(NOW(), INTERVAL 2 WEEK) AND NOW() GROUP BY `date` ORDER BY `date`";
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


////////////////////////////////////////////////////////////
///						SCRIPTS							 ///
////////////////////////////////////////////////////////////

function getScriptForUserAndId($conn, $script) {
	if($stmt    = $conn->prepare("SELECT * FROM scripts WHERE script_id = ?")) {
		$stmt->bind_param("i", $script);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows > 0) {
			if ($row = $result->fetch_assoc()) {
				return $row;
			}
		}
		$stmt->free_result();
	} else {
		die($conn->error);
	}
	return null;
}

function getScriptsForUser($conn, $user) {
	$ret = array();
	if($stmt    = $conn->prepare("SELECT * FROM scripts WHERE user = ?")) {
		$stmt->bind_param("i", $user);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				$ret[] = $row;
			}
		}
		$stmt->free_result();
	} else {
		die($conn->error);
	}
	return $ret;
}

?>