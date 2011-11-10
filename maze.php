<?php

class Point {
	public $x;
	public $y;

	public function __construct($x, $y) {
		$this->x = $x;
		$this->y = $y;
	}
}

class Maze {
	const WALL  = 'X';
	const EMPTY_PATH  = ' ';
	const START  = 's';
	const GOAL  = 'e';

	private $cells = array();
	private $visited = array();
	private $start = null;
	private $goal = null;

	public function __construct($sizex = 51, $sizey = null) {
		if ($sizex % 2 == 0) $sizex++;
		if ($sizey === null) {
			$sizey = $sizex;
		} else if ($sizey % 2 == 0) {
			 $sizey++;
		}

		for ($i = 0; $i < $sizex; $i++) {
			$this->cells[$i] = array_fill(0, $sizey, self::WALL);
			$this->visited[$i] = array_fill(0, $sizey, false);
		}

		$this->gen_depth_first(1, 1);

		$this->setStart(new Point(1,0));
		$this->setGoal(new Point(3,$sizey-2));
	}

	private function gen_depth_first($x, $y) {
		$this->visited[$x][$y] = true;
		$this->cells[$x][$y] = self::EMPTY_PATH;

		$neighbors = $this->getNeighbors(new Point($x, $y), 2);
		shuffle($neighbors);
		foreach ($neighbors as $direction => $n) {
			if ($this->cells[$n->x][$n->y] === self::WALL) {
				$this->cells[ ($x + $n->x)/2 ][ ($y + $n->y)/2 ] = self::EMPTY_PATH;
				$this->gen_depth_first($n->x, $n->y);
			}
		}
	}

	protected function getNeighbors(Point $p, $step = 1) {
		$neighbors = array();

		if (array_key_exists($p->x - $step, $this->cells)) {
			$neighbors['n'] = new Point($p->x - $step, $p->y);
		}
		if (array_key_exists($p->x + $step, $this->cells)) {
			$neighbors['s'] = new Point($p->x + $step, $p->y);
		}
		if (array_key_exists($p->y + $step, $this->cells[$p->x])) {
			$neighbors['e'] = new Point($p->x, $p->y + $step);
		}
		if (array_key_exists($p->y - $step, $this->cells[$p->x])) {
			$neighbors['w'] = new Point($p->x, $p->y - $step);
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

	public function setGoal(Point $goal) {
		if ($this->goal instanceof Point) {
			$this->cells[$this->goal->x][$this->goal->y] = self::EMPTY_PATH;
		}
		$this->goal = $goal;
		$this->cells[$this->goal->x][$this->goal->y] = self::GOAL;
	}

	public function setStart(Point $start) {
		if ($this->start instanceof Point) {
			$this->cells[$this->start->x][$this->start->y] = self::EMPTY_PATH;
		}
		$this->start = $start;
		$this->cells[$this->start->x][$this->start->y] = self::START;
	}

	public function getGoal() {
		return $this->goal;
	}

	public function getStart() {
		return $this->start;
	}

	public function setCell(Point $p, $value) {
		if ($this->cells[$p->x][$p->y] == self::WALL) {
			return self::WALL;
		}
		return $this->cells[$p->x][$p->y] = $value;
	}

	public function getCell(Point $p) {
		if (array_key_exists($p->x, $this->cells) && array_key_exists($p->y, $this->cells[$p->x])) {
			return $this->cells[$p->x][$p->y];
		}
		return false;
	}

	public function getCells() {
		return (array)$this->cells;
	}
}		


/**
 * Add the solution finding function to the maze class
 * 
 * Kept seperate to facilitate using the parent class as a teaching tool
 */
class Maze_solver extends maze {

	const GOOD_TRAIL = 'o';
	const BAD_TRAIL = '.';

	public function solve(Point $p = null) {
		if ($p === null) $p = $this->getStart();

		$curr = $this->getCell($p);

		if ($curr == self::GOAL) {
			return true;
		}

		if ($curr == self::EMPTY_PATH || $curr == self::START) {
			$this->setCell($p, self::GOOD_TRAIL);
		
			// uncomment this to show incremental progress
			// $this->display();

			$neighbors = $this->getNeighbors($p);
			foreach ($neighbors as $n) {
				if ($this->solve($n)) {
					return true;
				}
			}
			$this->setCell($p, self::BAD_TRAIL);

			// uncomment this to show incremental back steps
			// $this->display();
		}
		return false;
	}
}


class HTML_maze extends maze_solver {
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
		foreach ($this->getCells() as $row) {
			echo "\n\t<tr>";
			foreach ($row as $cell) {
				echo "\n\t\t<td class=\"{$this->CSS_CLASS[$cell]}\"></td>";
			}
			echo "\n\t</tr>";
		}
		echo "\n</table>";
	}
}

// text output
//$m = new maze_solver(40);

$m = new HTML_maze(100);
$m->solve();
$m->display();

