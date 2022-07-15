<?php

namespace Utils;
class Utils
{
    const apiKey = 'fde4fa02-d773-4413-952e-7a4c6bcadc80';
    const apiSecret = '9A8A783E00F3DC9955D40688CA59BD48';
    const passphrase = '';

    const FUTURE_API_URL = 'https://www.okx.com';
    const SERVER_TIMESTAMP_URL = '/api/v5/public/time';

    public  static  function request($requestPath, $params, $method, $cursor = false)
    {
        if (strtoupper($method) == 'GET') {
            $requestPath .= $params ? '?'.http_build_query($params) : '';
            $params = [];
        }

        $url = self::FUTURE_API_URL.$requestPath;
        $body = $params ? json_encode($params, JSON_UNESCAPED_SLASHES) : '';
        $timestamp = self::getServerTimestamp();

        $sign = self::signature($timestamp, $method, $requestPath, $body, self::apiSecret);
        $headers = self::getHeader(self::apiKey, $sign, $timestamp, self::passphrase);

        $ch= curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        if($method == "POST") {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $return = curl_exec($ch);

        $return = json_decode($return,true);

        return $return;
    }

    public static function getHeader($apiKey, $sign, $timestamp, $passphrase)
    {
        $headers = array();

        $headers[] = "Content-Type: application/json";
        $headers[] = "OK-ACCESS-KEY: $apiKey";
        $headers[] = "OK-ACCESS-SIGN: $sign";
        $headers[] = "OK-ACCESS-TIMESTAMP: $timestamp";
        $headers[] = "OK-ACCESS-PASSPHRASE: $passphrase";

        return $headers;
    }

    public static function getTimestamp()
    {
        return date("Y-m-d\TH:i:s"). substr((string)microtime(), 1, 4) . 'Z';
    }

    public static function getServerTimestamp(){
        try{
            $response = file_get_contents(self::FUTURE_API_URL.self::SERVER_TIMESTAMP_URL);
            $response = json_decode($response,true);
            return $response['data'][0]['ts'];
        }catch (Exception $e){
            return $e;
        }
    }

    public static function signature($timestamp, $method, $requestPath, $body, $secretKey)
    {
        $message = (string) $timestamp . strtoupper($method) . $requestPath . (string) $body;

        return base64_encode(hash_hmac('sha256', $message, $secretKey, true));
    }

}