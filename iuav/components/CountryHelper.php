<?php

namespace app\components;

use GeoIp2\Database\Reader;

class CountryHelper
{

    public static function getByIp()
    {
        $ip = self::getClientIp();

        $country = '';

        if (!self::isLocalIp($ip)) {
            try {
                $reader = new Reader(__DIR__ . '/../config/GeoIP2-Country.mmdb');
                $record = $reader->country($ip);
                if ($record == false) {
                    //use MaxMind\Db\Reader 修改文件，不会报错导致程序无法进行
                } else {
                    $country = $record->country->isoCode;
                }
            } catch (ErrorException $e) {
                // log
            }
        }
        return $country;
    }

    private static function isLocalIp($ip)
    {
        return preg_match('#^(10|172\.16|127\.0|192\.168)\.#', $ip);
    }

    private static function getClientIp()
    {
        $ip = $_SERVER['REMOTE_ADDR'];

        if (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
            foreach ($matches[0] AS $xip) {
                if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
                    $ip = $xip;
                    break;
                }
            }
        }
        return $ip;
    }
}