<?php

namespace Bskl\MpSms\MesajPaneli;

class Curl
{
    public static $handle; // Handle
    public static $body = ''; // Response body
    public static $head = ''; // Response head
    public static $info = [];

    public static function head($ch, $data)
    {
        self::$head = $data;

        return strlen($data);
    }

    public static function body($ch, $data)
    {
        self::$body .= $data;

        return strlen($data);
    }

    public static function fetch($url, $opts = [])
    {
        self::$head = self::$body = '';

        self::$info = [];
        self::$handle = curl_init($url);
        curl_setopt_array(self::$handle, $opts);
        curl_exec(self::$handle);
        self::$info = curl_getinfo(self::$handle);
        curl_close(self::$handle);
    }
}
