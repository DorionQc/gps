<?php

namespace App\APIs;

class CurlHelper 
{
    private static $default_opts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_ENCODING => "",
        CURLOPT_USERAGENT => "",
        CURLOPT_AUTOREFERER => true,
        CURLOPT_CONNECTTIMEOUT => 120,
        CURLOPT_TIMEOUT => 120,
        CURLOPT_MAXREDIRS => 16,
        CURLOPT_SSL_VERIFYPEER => true,
    ];
    
    
    /**
     * Reads the content of a web page
     * @param string $url
     * @param array $options
     * @return array
     */
    public static function getPageContent(string $url, array $options = []) 
    {
        $opt = array_merge(static::$default_opts, $options);
        
        $ch = curl_init($url);
        curl_setopt_array($ch, $opt);
        $content = curl_exec($ch);
        $err = curl_errno($ch);
        $errmsg = curl_errno($ch);
        $header = curl_getinfo($ch);
        curl_close($ch);
        
        $header['errno'] = $err;
        $header['errmsg'] = $errmsg;
        $header['content'] = $content;
        return $header;
    }
    
}
