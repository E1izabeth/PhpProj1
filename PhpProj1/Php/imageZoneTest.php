<?php

require_once("imageLib.php");
require_once("flatZone.php");
require_once("point.php");
require_once("letters.php");


class ZoneRenderer implements IZoneVisitor
{
	public $image, $k;

	function __construct($image, $k)
	{
		$this->image = $image;
		$this->k = $k;
	}

	private function zoom($x, $y)
	{
		return new Point($x * $this->k + $this->image->w / 2, $y * $this->k + $this->image->h / 2);
	}

	private function scale($x, $y)
	{
		return new Point($x * $this->k, $y * $this->k);
	}

	public function visitCircle($shape)
	{
		$p = $this->zoom($shape->cx, $shape->cy);
		$this->image->fillCircle($p->x, $p->y, (double)$shape->r * $this->k, $shape->quarter, pixel(0, 0, 255));
	}

	public function visitRectangle($shape)
	{
		$p = $this->zoom($shape->x, $shape->y);
		$s = $this->scale($shape->w, $shape->h);
		$this->image->fillRectangle((int)$p->x, (int)$p->y, (int)$s->x, (int)$s->y, pixel(0, 0, 255));
	}

	public function visitTriangle($shape)
	{
		$p1 = $this->zoom($shape->x1, $shape->y1);
		$p2 = $this->zoom($shape->x2, $shape->y2);
		$p3 = $this->zoom($shape->x3, $shape->y3);
		$this->image->fillTriangle($p1->x, $p1->y, $p2->x, $p2->y, $p3->x, $p3->y, pixel(0, 0, 255));
	}

	public function visitPoint($shape)
	{
		$p = $this->zoom($shape->x, $shape->y);
		$this->image->drawPoint($p->x, $p->y, pixel(255, 0, 0));
	}

	public function visitX($shape)
	{
		$this->image->drawX($shape->x, $shape->y, pixel(0, 0, 0));
	}

	public function visitY($shape)
	{
		$this->image->drawY($shape->x, $shape->y, pixel(0, 0, 0));
	}
	
	public function visitR($shape)
	{
		$p = $this->zoom($shape->x, $shape->y);
		$this->image->drawR($p->x, $p->y, pixel(0, 0, 0));
	}

	public function visitMinusR($shape)
	{
		$p = $this->zoom($shape->x, $shape->y);
		$this->image->drawMinusR($p->x, $p->y, pixel(0, 0, 0));
	}

	public function visitRdiv2($shape)
	{
		$p = $this->zoom($shape->x, $shape->y);
		$this->image->drawRdiv2($p->x, $p->y, pixel(0, 0, 0));
	}

	public function visitMinusRdiv2($shape)
	{
		$p = $this->zoom($shape->x, $shape->y);
		$this->image->drawMinusRdiv2($p->x, $p->y, pixel(0, 0, 0));
	}
}


$bb = new Bitmap($w = 800, $h = 600,  pixel(223, 223, 223));
$black = pixel(0, 0, 0);

$xml = simplexml_load_file("FlatZone.xml");
$zone = loadZoneFromXml($xml, 1);

$zoneRenderer = new ZoneRenderer($bb, 100);
foreach ($zone->elements as $item)
	$item->apply($zoneRenderer);

if (isset($_GET["page"]) && $_GET["page"] == 2) // use isset(..)
{
	$r = $_GET["r"];
	$point = new Point((double)$_GET["x"] / $r, (double)$_GET["y"] / $r);
	$zoneRenderer->visitPoint($point);
}
		
// vertical axe
$bb->drawLineDDA($w / 2, 0, $w / 2, $h, $black);
$bb->drawLineDDA($w / 2, $h, $w / 2 - 25, $h - 50,  $black);
$bb->drawLineDDA($w / 2, $h, $w / 2 + 25, $h - 50,  $black);

// horizontal axe
$bb->drawLineDDA(0, $h / 2, $w, $h / 2, $black);
$bb->drawLineDDA($w, $h / 2, $w - 50, $h / 2 - 25, $black);
$bb->drawLineDDA($w, $h / 2, $w - 50, $h / 2 + 25, $black);

$xp = new letX($w - 30, $h / 2 - 80);
$zoneRenderer->visitX($xp);
$yp = new letY($w / 2 - 50, $h- 50);
$zoneRenderer->visitY($yp);
$rp = new letR(1, 0);
$zoneRenderer->visitR($rp);
$rp = new letR(0, 1);
$zoneRenderer->visitR($rp);
$rp = new letMinusR(-1, 0);
$zoneRenderer->visitMinusR($rp);
$rp = new letMinusR(0, -1);
$zoneRenderer->visitMinusR($rp);
$rp = new letMinusRdiv2(0, -0.5);
$zoneRenderer->visitMinusRdiv2($rp);
$rp = new letMinusRdiv2(-0.5, 0);
$zoneRenderer->visitMinusRdiv2($rp);
$rp = new letRdiv2(0, 0.5);
$zoneRenderer->visitRdiv2($rp);
$rp = new letRdiv2(0.5, 0);
$zoneRenderer->visitRdiv2($rp);

// header('Content-Disposition: inline; filename="imageTest.bmp"');
// header('Content-Type: image/bmp');
// header('Content-Length: ' . $bb->size);
// $bb->write();

header('Content-Disposition: inline; filename="imageTest.png"');
header('Content-Type: image/png');
// header('Content-Length: ' . $bb->size);
$bb->writeCompressed();


?>
