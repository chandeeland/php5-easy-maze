<?php

class point {
	public $x;
	public $y;

	public function __construct($x, $y) {
		$this->x = $x;
		$this->y = $y;
	}
}

class maze {
	//const WALL  = 'X';
	const WALL  = 'X';
	const EMPTY_PATH  = ' ';
	const START  = 's';
	const GOAL  = 'e';

	protected $cells = array();
	private $visited = array();

	public function __construct($size = 50) {
		if ($size % 2 == 0) $size++;

		for ($i = 0; $i < $size; $i++) {
			$this->cells[$i] = array_fill(0, $size, self::WALL);
			$this->visited[$i] = array_fill(0, $size, false);
		}

		$this->gen_depth_first(1, 1);
		$this->cells[1][1] = self::START;
		$this->cells[$size-1][$size-2] = self::GOAL;
	}

	private function gen_depth_first($x, $y) {
		$this->visited[$x][$y] = true;
		$this->cells[$x][$y] = self::EMPTY_PATH;

		$neighbors = $this->getNeighbors($x, $y, 2);
		shuffle($neighbors);
		foreach ($neighbors as $direction => $n) {
			if ($this->cells[$n->x][$n->y] === self::WALL) {
				$this->cells[ ($x + $n->x)/2 ][ ($y + $n->y)/2 ] = self::EMPTY_PATH;
				$this->gen_depth_first($n->x, $n->y);
			}
		}
	}

	protected function getNeighbors($x,$y,$step = 1) {
		$neighbors = array();

		if (array_key_exists($x - $step, $this->cells)) {
			$neighbors['n'] = new Point($x - $step, $y);
		}
		if (array_key_exists($x + $step, $this->cells)) {
			$neighbors['s'] = new Point($x + $step, $y);
		}
		if (array_key_exists($y + $step, $this->cells[$x])) {
			$neighbors['e'] = new Point($x, $y + $step);
		}
		if (array_key_exists($y - $step, $this->cells[$x])) {
			$neighbors['w'] = new Point($x, $y - $step);
		}
		return $neighbors;
	}



	public function display() {
		echo "\n";
		foreach ($this->cells as $row) {
			foreach ($row as $cell) {
				echo $cell;
			}
			echo "\n";
		}
	}
}

class maze_solver extends maze {

	const GOOD_TRAIL = 'o';
	const BAD_TRAIL = '.';

	public function solve($x, $y) {
		if ($this->cells[$x][$y] == self::GOAL) {
			return true;
		}

		if ($this->cells[$x][$y] == self::EMPTY_PATH || $this->cells[$x][$y] == self::START) {
			$this->cells[$x][$y] = self::GOOD_TRAIL;
		
			// uncomment this to show incremental progress
			// $this->display();

			$neighbors = $this->getNeighbors($x,$y);
			foreach ($neighbors as $n) {
				if ($this->solve($n->x, $n->y)) {
					return true;
				}
			}
			$this->cells[$x][$y] = self::BAD_TRAIL;

			// uncomment this to show incremental back steps
			// $this->display();
		}
		return false;
	}
}

class HTMLmaze extends maze_solver {
	protected $CSS_CLASS = array (
		self::WALL => 'wall-cell',
		self::EMPTY_PATH => 'empty-cell',
		self::BAD_TRAIL => 'bad-cell',
		self::GOOD_TRAIL => 'good-cell',
		self::START => 'start-cell',
		self::GOAL => 'goal-cell',
	);

	protected $HTML_COLORS = array (
		self::WALL => '#000',
		self::EMPTY_PATH => '#fff',
		self::BAD_TRAIL => '#f0f',
		self::GOOD_TRAIL => '#0f0',
		self::START => 'f5f',
		self::GOAL => '5f5',
	);

	protected function css() {
		static $need_setup = true;
		if ($need_setup) {
			echo "\n<style>";
			foreach ($this->HTML_COLORS as $class => $color) {
				echo "\n.{$this->CSS_CLASS[$class]} {background-color: {$color};width: 10px;height: 10px;}";
			}
			echo "\n</style>";
			$need_setup = false;
		}
	}

	public function display() {
		$this->css();

		echo "\n<table>";
		foreach ($this->cells as $row) {
			echo "\n\t<tr>";
			foreach ($row as $cell) {
				echo "\n\t\t<td class=\"{$this->CSS_CLASS[$cell]}\"></td>";
			}
			echo "\n\t</tr>";
		}
		echo "\n</table>";
	}
}

$m = new HTMLmaze(45);
$m->solve(1,1);
$m->display();

