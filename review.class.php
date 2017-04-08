<?php
	
	class ReviewCategory implements JsonSerializable {
		public $description = "";
		public $max_points = 0;

		public function __construct($desc, $max_points) {
			$this->description = $desc;
			$this->max_points = $max_points;
		}

		public function jsonSerialize() {
	        return [
		        "description" => $this->description,
		        "max_points" => $this->max_points
        	];
	    }
	}

	class ReviewObject implements JsonSerializable {
		public $categories = array();
		public $name = "";

		public function __construct($categories, $name) {
			$this->categories = $categories;
			$this->name = $name;
		}

		public function jsonSerialize() {
			return [
				"name" => $this->name,
				"categories" => $this->categories
			];
		}
	}

	class Review implements JsonSerializable {
		public $objects = array();
		public $name = "";

		public function __construct($name, $objects) {
			$this->objects = $objects;
			$this->name = $name;
		}

		public static function fromJSON($name, $json) {

			if(strlen($json) < 5) {
				return new self(null);
			}

			if(is_string($json) || !is_array($json)) {
				$json = json_decode($json, JSON_UNESCAPED_UNICODE);
			}

			$objects = array();
			foreach ($json as $object) {
				$sectionName = $object['name'];
				$categories = array();
				foreach ($object['categories'] as $cat) {
					$categories[] = new ReviewCategory($cat['description'],((int)$cat['max_points']));
				}
				$objects[] = new ReviewObject($categories, $sectionName);
			}
			return new self($name, $objects);
		}

		public function jsonSerialize() {
			return $this->objects;
		}
	}

	/*class ReviewScheme implements JsonSerializable {
		public $reviews = array();

		public function __construct($reviews) {
			$this->reviews = $reviews;
		}

		public static function fromJSON($json) {

			if(strlen($json) < 5) {
				return new self(null);
			}

			if(is_string($json) || !is_array($json)) {
				$json = json_decode($json, JSON_UNESCAPED_UNICODE);
			}

			$reviews = array();
			foreach ($json as $review) {
				$objects = array();
				foreach ($review['sections'] as $object) {
					$name = $object['name'];
					$categories = array();
					foreach ($object['categories'] as $cat) {
						$categories[] = new ReviewCategory($cat['description'],((int)$cat['max_points']));
					}
					$objects[] = new ReviewObject($categories, $name);
				}
				$reviews[] = new Review($objects);
			}
			return new self($reviews);
		}

		public function addReview($obj) {
			$this->reviews[] = $obj;
		}

		public function jsonSerialize() {
			return $this->reviews;
		}
	}*/

?>