<?php

require_once("shapes.php");
	
class letX extends Shape
{
	public $x;
	public $y;

	function __construct($x_val, $y_val)
	{
		$this->x = $x_val;
		$this->y = $y_val;
	}

	public function apply($visitor)
	{
		return $visitor->visitX($this);
	}
}

class letY extends Shape
{
	public $x;
	public $y;

	function __construct($x_val, $y_val)
	{
		$this->x = $x_val;
		$this->y = $y_val;
	}

	public function apply($visitor)
	{
		return $visitor->visitY($this);
	}
}

class letR extends Shape
{
	public $x;
	public $y;

	function __construct($x_val, $y_val)
	{
		$this->x = $x_val;
		$this->y = $y_val;
	}

	public function apply($visitor)
	{
		return $visitor->visitR($this);
	}
}

class letRdiv2 extends Shape
{
	public $x;
	public $y;

	function __construct($x_val, $y_val)
	{
		$this->x = $x_val;
		$this->y = $y_val;
	}

	public function apply($visitor)
	{
		return $visitor->visitRdiv2($this);
	}
}

class letMinusR extends Shape
{
	public $x;
	public $y;

	function __construct($x_val, $y_val)
	{
		$this->x = $x_val;
		$this->y = $y_val;
	}

	public function apply($visitor)
	{
		return $visitor->visitMinusR($this);
	}
}

class letMinusRdiv2 extends Shape
{
	public $x;
	public $y;

	function __construct($x_val, $y_val)
	{
		$this->x = $x_val;
		$this->y = $y_val;
	}

	public function apply($visitor)
	{
		return $visitor->visitMinusRdiv2($this);
	}
}
?>