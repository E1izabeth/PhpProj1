<?php
require_once('shapes.php');
require_once('meta.php');
require_once('point.php');

interface IZoneVisitor
{
	public function visitCircle($shape);
	public function visitRectangle($shape);
	public function visitTriangle($shape);
}

class ZoneChecker implements IZoneVisitor
{
	private $kx = array(1, -1, -1, 1);
	private $ky = array(1, 1, -1, -1);

	public $point;

	function __construct($point)
	{
		$this->point = $point;
	}

	public function visitCircle($shape)
	{
		$x = $this->point->x - $shape->cx;
		$y = $this->point->y - $shape->cy;
		
		return ($x * $x + $y * $y <= $shape->r * $shape->r) && ($x * $this->kx[$shape->quarter - 1] > 0 && $y * $this->ky[$shape->quarter - 1] > 0);
	}
	
	public function visitRectangle($shape)
	{
		$x_p = $this->point->x;
		$y_p = $this->point->y;

		$x_r = $shape->x + $shape->w;
		$y_r = $shape->y + $shape->h;
		
		return ($x_p >= $shape->x && $y_p >= $shape->y && $x_p <= $x_r && $y_p <= $y_r);
	}
	
	public function visitTriangle($shape)
	{
		$a = ($shape->x1 - $this->point->x) * ($shape->y2 - $shape->y1) - ($shape->x2 - $shape->x1) * ($shape->y1 - $this->point->y);
        $b = ($shape->x2 - $this->point->x) * ($shape->y3 - $shape->y2) - ($shape->x3 - $shape->x2) * ($shape->y2 - $this->point->y);
        $c = ($shape->x3 - $this->point->x) * ($shape->y1 - $shape->y3) - ($shape->x1 - $shape->x2) * ($shape->y3 - $this->point->y);
 
        if (($a >= 0 && $b >= 0 && $c >= 0) || ($a <= 0 && $b <= 0 && $c <= 0))
        {
            return true;
        }
		return false;
	}
}

class Zone
{
	public $elements;

	function __construct($elements)
	{
		$this->elements = $elements;
	}

	function isInFigure($point)
	{
		$checker = new ZoneChecker($point);

		foreach ($this->elements as $item)
		{
			if ($item->apply($checker))
			{
				return true;
			}	
		}

		return false;
	}
}

class PointReader
{
	public $element;
	public $xName, $yName;
	
	function __construct($element, $xName, $yName)
	{
		fillMe();
	}

	public function getFromXml($suffix = '', $prefix = '')
	{
		$attrs = $this->element->attributes();
		$x = $attrs[$prefix . $this->xName . $suffix];
		$y = $attrs[$prefix . $this->yName . $suffix];

		return new Point((double)$x, (double)$y);
	}	
}

function loadZoneFromXml($xml, $r)
{
	if (get_class($xml) == "SimpleXMLElement")
	{
		$elements = array();

		foreach ($xml->children() as $node) 
		{
			$pr = new PointReader($node, 'X', 'Y');
			$sr = new PointReader($node, 'H', 'W');

			$arr = $node->attributes();
			
			switch($node->getName())
			{
				case "Circle": $elements[] = new Circle($pr->getFromXml('', 'O')->mul($r), (double)$arr["R"] * $r, (double)$arr["Quarter"]); break;
				case "Rectangle": $elements[] = new Rectangle($pr->getFromXml()->mul($r), $sr->getFromXml()->mul($r)); break;  
				case "Triangle":$elements[] = new Triangle($pr->getFromXml('1')->mul($r), $pr->getFromXml('2')->mul($r), $pr->getFromXml('3')->mul($r)); break;
				default: // error
			}
		}
		return new Zone($elements);
	}
	else
	{
		throw new Exception('Invalid xml model');
	}
}

?>
