<?php

require_once('meta.php');

abstract class Shape
{
	abstract public function apply($visitor);
}
	
class Circle extends Shape
{
	public $cx, $cy, $r, $quarter;

	// q in [1 .. 4] and r > 0
	function __construct($p, $r, $q)
	{
		$this->cx = $p->x;
		$this->cy = $p->y;
		$this->r = $r;
		$this->quarter = $q;
	}

	public function apply($visitor)
	{
		return $visitor->visitCircle($this);
	}
}

class Rectangle extends Shape
{
	public $x, $y, $w, $h;
	
	function __construct($p, $s)
	{
		$this->x = $p->x;
		$this->y = $p->y;
		$this->w = $s->x;
		$this->h = $s->y;
	}

	public function apply($visitor)
	{
		return $visitor->visitRectangle($this);
	}

}

class Triangle extends Shape
{
	public $x1, $y1, $x2, $y2, $x3, $y3;
	
	function __construct($p1, $p2, $p3)
	{
		$this->x1 = $p1->x;
		$this->y1 = $p1->y;
		$this->x2 = $p2->x;
		$this->y2 = $p2->y;
		$this->x3 = $p3->x;
		$this->y3 = $p3->y;
	}

	public function apply($visitor)
	{
		return $visitor->visitTriangle($this);
	}
}

?>