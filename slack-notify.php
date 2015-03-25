#!/usr/local/bin/php
<?php

$ip_address_now = json_decode( file_get_contents( 'http://httpbin.org/ip') )->origin;

//hidden IP Address file in home.
$filename = getenv('HOME') . '/.ip_address';
$info = new SplFileInfo( $filename );

if ($info->isFile()) {
	$file = $info->openFile('r+');
	$old_ip_address = $file->fgets();
} else {
	$file = new SplFileObject( $filename, 'w+' );
	$old_ip_address = '';
}

assert( $file->ftruncate( 0 ) );
assert( $file->fwrite($ip_address_now) );

if( trim( $old_ip_address ) != trim( $ip_address_now ) ){
	$payload['text'] = 'IP address has changed from ' . $old_ip_address . ' to ' . $ip_address_now;
	$payload['channel'] = '@' . ( ! empty(getenv('SLACK_USER') ) ? getenv('SLACK_USER') : getenv('USER'));
	$payload['username'] = 'computer-bot';
	$payload['icon_emoji'] = ':computer:';

	curl_setopt_array($ch = curl_init(), array(
		CURLOPT_URL => "https://hooks.slack.com/services/" . getenv('SLACK_WEBHOOK'),
		CURLOPT_POSTFIELDS => json_encode( $payload ),
		CURLOPT_SAFE_UPLOAD => true,
	));
	curl_exec($ch);
	curl_close($ch);
}

?>
