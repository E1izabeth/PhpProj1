<?php

require_once("shapes.php");
	
class Point extends Shape
{
	public $x;
	public $y;

	function __construct($x_val, $y_val)
	{
		$this->x = $x_val;
		$this->y = $y_val;
	}

	public function mul($k)
	{
		return new Point((double)$this->x * (double)$k, (double)$this->y * (double)$k);
	}

	public function apply($visitor)
	{
		return $visitor->visitPoint($this);
	}
}
?>