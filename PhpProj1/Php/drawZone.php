<?php
  $i = imageCreate(300, 300);
  $color = imageColorAllocate($i, 223, 223, 223);

  $black = imageColorAllocate($i, 0, 0, 0);
  imageLine($i, 0, 150, 300, 150, $black);
  imageLine($i, 150, 0, 150, 300, $black);


  $blue = imageColorAllocate($i, 33, 118, 217);
  imageFilledRectangle($i, 150, 150, 200, 200, $blue);

  Header("Content-type: image/jpeg");
  imageJpeg($i);
  imageDestroy($i);
?>