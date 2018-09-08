<!DOCTYPE html>
<html>
	<head>
		<title>Lab 1</title>
		<meta charset="utf-8" />
		<style>
			<?php
				include_once("style.css");
			?>
		</style>
	</head>
<body>

<?php
ini_set('error_reporting', E_ALL); 
ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1);

require_once('timer.php');
require_once('point.php');
require_once('requestInfo.php');
require_once('flatZone.php');

Timer::start();

$now = new DateTime();
$request = new RequestInfo();
$xml = simplexml_load_file("FlatZone.xml");
$zone = loadZoneFromXml($xml, $request->r);
$result = $zone->isInFigure($request->point);

 echo '<img src=https://se.ifmo.ru/~s243891/imageZoneTest.php?x=' . $request->point->x . '&y=' . $request->point->y . '&r=' . $request->r . '&page=2 align="center" />' ?>
  <table class="table">
   <thead>
    <td>X</td>
    <td>Y</td>
    <td>R</td>
    <td>Is in a figure</td>
    <td>Now</td>
    <td>Working time</td>
   </thead>
   <tbody>
    <td><?php echo $request->point->x ?></td>
    <td><?php echo $request->point->y ?></td>
    <td><?php echo $request->r ?></td>
    <td><?php 
	    if ($result) {echo "true";} 
	    else {echo "false";}
     	?>
     </td>
     <td> <?php echo $now->format('Y-m-d H:i:s'); ?> </td>
     <td> <?php echo Timer::finish() . ' сек.'; ?> </td>
   </tbody>

  </table>
	
</body>
</html>