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

		public function __construct($objects) {
			$this->objects = $objects;
		}

		public static function fromJSON($json) {
			if(is_string($json) || !is_array($json)) {
				$json = json_decode($json, JSON_UNESCAPED_UNICODE);
			}
			if(is_null($json)) {
				return;
			}
			$objects = array();
			foreach ($json as $object) {
				$name = $object['name'];
				$categories = array();
				foreach ($object['categories'] as $cat) {
					$categories[] = new ReviewCategory($cat['description'],((int)$cat['max_points']));
				}
				$objects[] = new ReviewObject($categories, $name);
			}
			return new self($objects);
		}

		public function jsonSerialize() {
			return $this->objects;
		}
	}

?>