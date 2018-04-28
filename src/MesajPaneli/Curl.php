<?php

namespace Bskl\MpSms\MesajPaneli;

class Curl
{
	static $handle; // Handle
	static $body = ''; // Response body
	static $head = ''; // Response head
	static $info = [];

	static function head( $ch, $data ) {
		Curl::$head = $data;
		return strlen( $data );
	}

	static function body( $ch, $data ) {
		Curl::$body .= $data;
		return strlen( $data );
	}

	static function fetch( $url, $opts = [] ) {
		Curl::$head = Curl::$body = '';

		Curl::$info = [];
		Curl::$handle = curl_init( $url );
        curl_setopt_array( Curl::$handle, $opts );
		curl_exec( Curl::$handle );
		Curl::$info = curl_getinfo( Curl::$handle );
		curl_close( Curl::$handle );
	}
}
