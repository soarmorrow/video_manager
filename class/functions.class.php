<?php

$server = filter_input_array(INPUT_SERVER);

function getBaseUrl(){
	global $server;
	$relpath = explode('/', $server['REQUEST_URI'])[1];
	return "/".$relpath;
}

function getPath(){
	global $server;
	$domain = $server['REQUEST_SCHEME']."://".$server['SERVER_NAME'].getBaseUrl();
	return $domain;
}

function dump($data) {
	echo '<pre>';
	print_r($data);
	echo '</pre>';
}
?>