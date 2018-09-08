<?php
require_once('point.php');

class RequestInfo
{
	public $point;
	public $r;

	function __construct()
	{
		if( isset( $_POST['y'] ) )
		{
			$x = $_POST["x"];
		}
		else
		{
			$x = 0;
		}

		if( isset( $_POST['y'] ))
		{
			$y = $_POST['y'];
		}
		else
		{
			$y = 0;
		}

		if( isset( $_POST['r'] ) )
		{
			$r = $_POST['r'];
		}
		else
		{
			$r = 0;
		}
		$this->point = new Point($x, $y);
		$this->r = $r;
	}
}

?>