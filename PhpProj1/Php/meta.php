<?php

function fillMe()
{
	$caller = debug_backtrace();
	// var_dump($caller);

	$caller = $caller[1];

	if (!isset($caller['class'])) 
		throw new Exception('Invalid context!');

	$method =  new ReflectionMethod($caller['class'], $caller['function']);

	$params = $method->getParameters();
	$paramNames = array_map(function($item) { return $item->getName(); }, $params);
	
	for ($i = 0; $i < count($params); $i++)
	{
		$caller["object"]->{$paramNames[$i]} = $caller["args"][$i];
	}
}

?>