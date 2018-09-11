<?php

function pixel($r, $g, $b)
{
	return ($r << 16) | ($g << 8) | ($b << 0);
}

function createBitmap($w, $h, $c)
{
	$bb = array();

	for ($x = 0; $x < $w; $x++)
	{
		$bb[$x] = array();

		for ($y = 0; $y < $h; $y++)
			$bb[$x][$y] = $c;
	}
	
	return $bb;
}

function computeBmpSize(&$pp)
{	
	$w = count($pp);
	$h = count($pp[0]);

	$dataSize = $h * $w * 3;
	return $dataSize + 0x36;
}

function writeBmp(&$data)
{
	$w = count($data);
	$h = count($data[0]);

	$dataSize = $h * $w * 3;

	// BITMAPFILEHEADER
	echo pack("C*", 0x42, 0x4D); // signature
	echo pack("V", $dataSize +  36);  // size of file
	echo pack("V", 0);			 // reserved
	echo pack("V", 0x36);		 // pixels position from start

	// BITMAPINFOHEADER
	echo pack("V", 40); // size of structure
	echo pack("V", $w); // width
	echo pack("V", $h); // height
	echo pack("v", 1); // planes
	echo pack("v", 24); // bits per pixel
	echo pack("V", 0); // compression
	echo pack("V", $dataSize); // image size 
	echo pack("V*", 0, 0, 0, 0);
	
	for ($y = 0; $y < $h; $y++)
	{
		for ($x = 0; $x < $w; $x++)
		{
			$p = $data[$x][$y];
			echo pack("C*", ($p >> 0) & 0xff, ($p >> 8) & 0xff, ($p >> 16) & 0xff);
		}
	}
}

class PngChunkBuff
{
	private $chunkType;
	private $data = array();
	
	function __construct($a, $b, $c, $d)
	{
		$t = array();
		$t[0] = $a;
		$t[1] = $b;
		$t[2] = $c;
		$t[3] = $d;

		$this->chunkType = $t;
	}

	public function add($bytes)
	{
		$this->data = array_merge($this->data, unpack("C*", $bytes));
	}

	private function computePngCrc($bytes)
	{
		$crc_table = array();
		for ($n = 0; $n < 256; $n++) {
			$c = (int)$n;
			for ($k = 0; $k < 8; $k++) {
				if ($c & 1)
					$c = 0xedb88320 ^ ($c >> 1);
				else
					$c = $c >> 1;
			}
			$crc_table[$n] = $c;
		}
  
		$c = 0xffffffff;
		for ($n = 0; $n < count($bytes); $n++) {
			$c = $crc_table[($c ^ $bytes[$n]) & 0xff] ^ ($c >> 8);
		}

		return $c ^ 0xffffffff;
	}

	public function getChunkBytes()
	{
		$crc = $this->computePngCrc(array_merge($this->chunkType, $this->data));

		return call_user_func_array('pack', array_merge(
			array("C*"),
			unpack("C*", pack("N", count($this->data))),
			$this->chunkType,
			$this->data,
			unpack("C*", pack("N", $crc))
		));			
	}
	
	public function packData()
	{
		return call_user_func_array('pack', array_merge(
			array("C*"),
			$this->data
		));
	}
	
	public function getDataSize()
	{
		return count($this->data);
	}
}

function makePng(&$data)
{
	$r = new PngChunkBuff(0, 0, 0, 0);
	$w = count($data);
	$h = count($data[0]);

	$colors = array_values(array_unique(call_user_func_array(
		'array_merge', 
		array_map(function ($col) {
			return array_values(array_unique($col, SORT_NUMERIC));
		}, $data)
	), SORT_NUMERIC));

	$indexedData = array_map(function ($col) use ($colors) {
		return array_map(function ($p) use ($colors) { 
			return array_search($p, $colors); 
		}, $col); 
	}, $data);

	$scanlines = array();

	for ($y = 0; $y < $h; $y++)
	{
		$scanlines[$y] = array();
		$scanlines[$y][0] = 0;

		for ($x = 0; $x < $w; $x++)
			$scanlines[$y][$x + 1] = $indexedData[$x][$y];
	}

	$dataBytes = call_user_func_array('array_merge', array_reverse($scanlines));
	$rawData = call_user_func_array('pack', array_merge(array("C*"), $dataBytes));
	$compressedData = gzcompress($rawData);
	
	$r->add(pack("C*", 137, 80, 78, 71, 13, 10, 26, 10)); // PNG header
	
	$hdr = new PngChunkBuff(73, 72, 68, 82);
	$hdr->add(pack("N", $w));
	$hdr->add(pack("N", $h));
	$hdr->add(pack("C", 8)); // scanline entry size in bits 
	$hdr->add(pack("C", 3)); // colour type is indexed-colour
	$hdr->add(pack("C", 0)); // compression method deflate
	$hdr->add(pack("C", 0)); // compression method deflate
	$hdr->add(pack("C", 0)); // compression method deflate
	$r->add($hdr->getChunkBytes());

	$plte= new PngChunkBuff(80, 76, 84, 69);
	for ($i = 0; $i < count($colors); $i++)
	{
		$p = $colors[$i];
		$plte->add(pack("C*", ($p >> 16) & 0xff, ($p >> 8) & 0xff, ($p >> 0) & 0xff));
	}
	$r->add($plte->getChunkBytes());

	$idat = new PngChunkBuff(73, 68, 65, 84);
	$idat->add($compressedData);
	$r->add($idat->getChunkBytes());

	$end = new PngChunkBuff(73, 69, 78, 68);
	$r->add($end->getChunkBytes());
	
	return $r;
}

function setPixel(&$pp, $x, $y, $c)
{
	$w = count($pp);
	$h = count($pp[0]);

	$x = (int)$x;
	$y = (int)$y;

	if ($x >= 0 && $x < $w && $y >= 0 && $y < $h)
	{
		$pp[$x][$y] = $c;
	}
}

function drawLine(&$pp, $x0, $y0, $x1, $y1, $c)
{
	if ($x1 < $x0)
	{
		$t = $x1; $x1 = $x0; $x0 = $t;
		$t = $y1; $y1 = $y0; $y0 = $t;
	}

	$deltax = abs($x1 - $x0);
	$deltay = abs($y1 - $y0);
	$error = 0;
	$deltaerr = $deltay;
	$y = $y0;
	$diry = $y1 - $y0;

	if ($diry > 0)
		$diry = 1;

	if ($diry < 0)
		$diry = -1;

	for ($x = $x0; $x < $x1; $x++)
	{
		setPixel($pp, $x, $y, $c);
		$error = $error + $deltaerr;

		if (2 * $error >= $deltax)	
		{
			$y = $y + $diry;
			$error = $error - $deltax;
		}
	}
}

function drawLineDDA(&$pp, $x1, $y1, $x2, $y2, $c)
{
      $iX1 = (int)$x1;
      $iY1 = (int)$y1;
      $iX2 = (int)$x2;
      $iY2 = (int)$y2;
 
      // (2) line projection lengths per axis
      $deltaX = abs($iX1 - $iX2);
      $deltaY = abs($iY1 - $iY2);
 
      // (3) computing number of steps required
      $length = max($deltaX, $deltaY);
      // case for one-pixel-sized circle
      if ($length == 0)
      {
            setPixel($pp, $iX1, $iY1, $c);
            return;
      }
 
      // (4) computing delta per axis
      $dX = ($x2 - $x1) / $length;
      $dY = ($y2 - $y1) / $length;
 
      // (5) initial values
      $x = $x1;
      $y = $y1;
 
      // main loop
      $length++;
      while ($length--)
      {
            $x += $dX;
            $y += $dY;
            setPixel($pp, (int)$x, (int)$y, $c);
      }
}

function drawPoint(&$pp, $X1, $Y1, $R, $c)
{
	$x = 0;
	$y = $R;
	$delta = 1 - 2 * $R;
	$error = 0;

	while ($y >= 0)
	{
		$yy = $Y1 + $y;
		for ($xx = $X1 - $x; $xx < $X1 + $x; $xx++)
			setPixel($pp, $xx, $yy, $c);
			
		$yy = $Y1 - $y;
		for ($xx = $X1 - $x; $xx < $X1 + $x; $xx++)
			setPixel($pp, $xx, $yy, $c);

		$error = 2 * ($delta + $y) - 1;
		if (($delta < 0) && ($error <= 0))
		{
			$delta += 2 * ++$x + 1;
			continue;
		}
		if (($delta > 0) && ($error > 0))
		{
			$delta -= 2 * --$y + 1;
			continue;
		}
		$delta += 2 * (++$x - $y--);
	}
}
 
function fillCircle(&$pp, $X1, $Y1, $R, $q, $c)
{
	$x = 0;
	$y = $R;
	$delta = 1 - 2 * $R;
	$error = 0;

	while ($y >= 0)
	{
		if($q == 1 || $q == 2)
		{
			$yy = $Y1 + $y;
		}
		else if ($q == 3 || $q == 4)
		{
			$yy = $Y1 - $y;
		}
		if ($q == 3 || $q == 2)
		{
			for ($xx = $X1 - $x; $xx < $X1; $xx++)
				setPixel($pp, $xx, $yy, $c);
		}
		else if( $q == 4 || $q == 1)
		{
			for ($xx = $X1; $xx < $X1 + $x; $xx++)
				setPixel($pp, $xx, $yy, $c);
		}

		$error = 2 * ($delta + $y) - 1;
		if (($delta < 0) && ($error <= 0))
		{
			$delta += 2 * ++$x + 1;
			continue;
		}
		if (($delta > 0) && ($error > 0))
		{
			$delta -= 2 * --$y + 1;
			continue;
		}
		$delta += 2 * (++$x - $y--);
	}
}

function fillRectangle(&$pp, $x0, $y0, $w, $h, $c)
{
	if ($w < 0)
		$sx = -1;
	else
		$sx = 1;

	for ($x = $x0; $x < $x0 + $w; $x += $sx)
	{
		drawLineDDA($pp, $x, $y0, $x, $y0 + $h, $c);
	}
}

function swap($a, $b)
{
	$tmp = $a;
	$a = $b;
	$b = $tmp;
}

function fillTriangle(&$pp, $x1, $y1, $x2, $y2, $x3, $y3, $c)
{
	if ($y2 < $y1) 
	{
		swap($y1, $y2);
		swap($x1, $x2);
	}
	if ($y3 < $y1)
	{
		swap($y1, $y3);
		swap($x1, $x3);
	}
	if ($y2 > $y3) 
	{
		swap($y2, $y3);
		swap($x2, $x3); 
	}

	$dx13 = 0; $dx12 = 0; $dx23 = 0;

	if ($y3 != $y1) 
	{
		$dx13 = $x3 - $x1;
		$dx13 /= $y3 - $y1;
	}
	else
	{
		$dx13 = 0;
	}
 
	if ($y2 != $y1) 
	{
		$dx12 = $x2 - $x1;
		$dx12 /= ($y2 - $y1);
	}
	else
	{
		$dx12 = 0;
	}
 
	if ($y3 != $y2) 
	{
		$dx23 = $x3 - $x2;
		$dx23 /= ($y3 - $y2);
	}
	else
	{
		$dx23 = 0;
	}
     

	$wx1 = $x1;
	$wx2 = $wx1;

	$_dx13 = $dx13;
 
	if ($dx13 > $dx12)
	{
		swap($dx13, $dx12);
	}
	for ($i = $y1; $i < $y2; $i++)
	{
		for ($j = $wx1; $j <= $wx2; $j++)
		{ 
			setPixel($pp, $j, $i, $c);
		}
		$wx1 += $dx13;
		$wx2 += $dx12;
	}
 
	if ($y1 == $y2)
	{
		$wx1 = $x1;
		$wx2 = $x2;
	}
	if ($_dx13 < $dx23)
	{
		swap($_dx13, $dx23);
	}

	for ($i = $y2; $i <= $y3; $i++)
	{
		for ($j = $x1; $j <= $wx2; $j++)
		{
			setPixel($pp, $j, $i, $c);
		}
		$wx1 += $_dx13;
		$wx2 += $dx23;
	}
}

function drawX(&$pp, $x, $y, $c)
{
	drawLineDDA($pp, $x, $y, $x + 20, $y + 40, $c);
	drawLineDDA($pp, $x, $y + 40, $x + 20, $y, $c);
}

function drawY(&$pp, $x, $y, $c)
{
	drawLineDDA($pp, $x, $y, $x + 20, $y + 40, $c);
	drawLineDDA($pp, $x, $y + 40, $x + 10, $y + 20, $c);
}

function drawR(&$pp, $x, $y, $c)
{
	drawLineDDA($pp, $x, $y, $x, $y + 20, $c);
	drawLineDDA($pp, $x, $y + 20, $x + 7, $y + 20, $c);
	drawLineDDA($pp, $x + 7, $y + 20, $x + 10, $y + 15, $c);
	drawLineDDA($pp, $x + 10, $y + 15, $x + 10, $y + 12, $c);
	drawLineDDA($pp, $x + 10, $y + 12, $x + 7, $y + 8, $c);
	drawLineDDA($pp, $x + 7, $y + 8, $x + 1, $y + 8, $c);
	drawLineDDA($pp, $x + 1, $y + 8, $x + 9, $y, $c);
}

function drawDiv2(&$pp, $x, $y, $c)
{
	drawLineDDA($pp, $x, $y, $x + 5, $y + 20, $c);
	drawLineDDA($pp, $x + 8, $y + 13, $x + 10, $y + 20, $c);
	drawLineDDA($pp, $x + 11, $y + 20, $x + 15, $y + 15, $c);
	drawLineDDA($pp, $x + 15, $y + 15, $x + 15, $y + 8, $c);
	drawLineDDA($pp, $x + 15, $y + 8, $x + 8, $y, $c);
	drawLineDDA($pp, $x + 8, $y, $x + 15, $y, $c);
}

function drawRdiv2(&$pp, $x, $y, $c)
{
	drawR($pp, $x, $y, $c);
	drawDiv2($pp, $x + 15, $y, $c);
}

function drawMinus(&$pp, $x, $y, $c)
{
	drawLineDDA($pp, $x, $y + 10, $x + 3, $y + 10, $c);
}

function drawMinusR(&$pp, $x, $y, $c)
{
	drawMinus($pp, $x, $y, $c);
	drawR($pp, $x + 7, $y, $c);
}

function drawMinusRdiv2(&$pp, $x, $y, $c)
{
	drawMinus($pp, $x, $y, $c);
	drawRdiv2($pp, $x + 7, $y, $c);
}



class Bitmap
{
	public $data, $w, $h, $size;

	function __construct($w, $h, $c)
	{
		$this->w = $w;
		$this->h = $h;
		$this->data = createBitmap($w, $h, $c);
		$this->size = computeBmpSize($this->data);
	}

    private function zoom($x, $y)
	{
		return new Point($x * $this->k + $this->image->w / 2, $y * $this->k + $this->image->h / 2);
	}

	public function drawLineDDA($x1, $y1, $x2, $y2, $c)
	{
		drawLineDDA($this->data, $x1, $y1, $x2, $y2, $c);
	}

	public function fillRectangle($x0, $y0, $w, $h, $c)
	{
		fillRectangle($this->data, $x0, $y0, $w, $h, $c);
	}

	public function fillCircle($x, $y, $r, $q, $c)
	{
		fillCircle($this->data, $x, $y, $r, $q, $c);
	}

	public function fillTriangle($x1, $y1, $x2, $y2, $x3, $y3, $c)
	{
		fillTriangle($this->data, $x1, $y1, $x2, $y2, $x3, $y3, $c);
	}

	public function drawPoint($x, $y, $c)
	{
		drawPoint($this->data, $x, $y, 5, $c);
	}

	public function drawX($x, $y, $c)
	{
		drawX($this->data, $x, $y, $c);
	}

	public function drawY($x, $y, $c)
	{
		drawY($this->data, $x, $y, $c);
	}

	public function drawR($x, $y, $c)
	{
		drawR($this->data, $x + 1, $y + 1, $c);
	}

	public function drawRdiv2($x, $y, $c)
	{
		drawRdiv2($this->data, $x + 1, $y + 1, $c);
	}

	public function drawMinusR($x, $y, $c)
	{
		drawMinusR($this->data, $x + 1, $y + 1, $c);
	}

	public function drawMinusRdiv2($x, $y, $c)
	{
		drawMinusRdiv2($this->data, $x + 1, $y + 1, $c);
	}

	public function write()
	{
		writeBmp($this->data);
	}

	public function makeCompressed()
	{
		return makePng($this->data);
	}
}

?>
