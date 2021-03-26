<?php

if (!function_exists('str_starts_with')) { // PHP <8
	function str_starts_with($haystack, $needle) {
		return (string)$needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
	}
}

if (!function_exists('str_contains')) { // PHP <8
	function str_contains($haystack , $needle) {
		return strpos($haystack, $needle) !== false;
	}
}


function http_request($url) {
	$ch = curl_init();
	$agent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.90 Safari/537.36";
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_USERAGENT, $agent);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$output = curl_exec($ch);
	curl_close($ch);
	return $output;
}

function arr_get($arr, $need, $default="") {
	if (array_key_exists($need, $arr)) return $arr[$need];
	return $default;
}

function disable_ob() {
	ini_set('output_buffering', 'off');
	ini_set('zlib.output_compression', false);
	ini_set('implicit_flush', true);
	ob_implicit_flush(true);
	while (ob_get_level() > 0) {
		$level = ob_get_level();
		ob_end_clean();
		if (ob_get_level() == $level) break;
	}
	if (function_exists('apache_setenv')) {
		apache_setenv('no-gzip', '1');
		apache_setenv('dont-vary', '1');
	}
}
