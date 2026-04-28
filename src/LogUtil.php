<?php

declare(strict_types=1);
/**
 * This file is part of l1n6yun/aliyun-sls.
 *
 * @link     https://github.com/l1n6yun/aliyun-sls
 * @contact  l1n6yun@gmail.com
 * @license  https://github.com/l1n6yun/aliyun-sls/blob/master/LICENSE
 */

namespace L1n6yun\AliyunSls;

class LogUtil
{
    /**
     * Get the local machine ip address.
     *
     * @return string
     */
    public static function getLocalIp()
    {
        $local_ip = gethostbyname(php_uname('n'));
        if (strlen($local_ip) == 0) {
            $local_ip = gethostbyname(gethostname());
        }
        return $local_ip;
    }

    /**
     * If $gonten is raw IP address, return true.
     *
     * @param mixed $gonten
     * @return bool
     */
    public static function isIp($gonten)
    {
        $ip = explode('.', $gonten);
        for ($i = 0; $i < count($ip); ++$i) {
            if ($ip[$i] > 255) {
                return 0;
            }
        }
        return preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $gonten);
    }

    /**
     * Calculate string $value MD5.
     *
     * @param mixed $value
     * @return string
     */
    public static function calMD5($value)
    {
        return strtoupper(md5($value));
    }

    /**
     * Calculate string $content hmacSHA1 with secret key $key.
     *
     * @param mixed $content
     * @param mixed $key
     * @return string
     */
    public static function hmacSHA1($content, $key)
    {
        $signature = hash_hmac('sha1', $content, $key, true);
        return base64_encode($signature);
    }

    /**
     * Change $logGroup to bytes.
     *
     * @param mixed $logGroup
     * @return string
     */
    public static function toBytes($logGroup)
    {
        $mem = fopen('php://memory', 'rwb');
        $logGroup->write($mem);
        rewind($mem);
        $bytes = '';

        if (feof($mem) === false) {
            $bytes = fread($mem, 10 * 1024 * 1024);
        }
        fclose($mem);

        return $bytes;
        // $mem = fopen("php://memory", "wb");
        /*   $fiveMBs = 5*1024*1024;
           $mem = fopen("php://temp/maxmemory:$fiveMBs", 'rwb');
           $logGroup->write($mem);
          // rewind($mem);

          // fclose($mem);
           //d://logGroup.pdoc
          // $mem = fopen("php://memory", "rb");
          // $mem = fopen("php://temp/maxmemory:$fiveMBs", 'r+');
           $bytes;
           while(!feof($mem))
               $bytes = fread($mem, 10*1024*1024);
           fclose($mem);
           //test
           if($bytes===false)echo "fread fail";
           return $bytes;*/
    }

    /**
     * Get url encode.
     *
     * @param mixed $value
     * @return string
     */
    public static function urlEncodeValue($value)
    {
        return urlencode($value);
    }

    /**
     * Get url encode.
     *
     * @param mixed $params
     * @return string
     */
    public static function urlEncode($params)
    {
        ksort($params);
        $url = '';
        $first = true;
        foreach ($params as $key => $value) {
            $val = LogUtil::urlEncodeValue($value);
            if ($first) {
                $first = false;
                $url = "{$key}={$val}";
            } else {
                $url .= "&{$key}={$val}";
            }
        }
        return $url;
    }

    /**
     * Get canonicalizedLOGHeaders string as defined.
     *
     * @param mixed $header
     * @return string
     */
    public static function canonicalizedLOGHeaders($header)
    {
        ksort($header);
        $content = '';
        $first = true;
        foreach ($header as $key => $value) {
            if (strpos($key, 'x-log-') === 0 || strpos($key, 'x-acs-') === 0) { // x-log- header
                if ($first) {
                    $content .= $key . ':' . $value;
                    $first = false;
                } else {
                    $content .= "\n" . $key . ':' . $value;
                }
            }
        }
        return $content;
    }

    /**
     * Get canonicalizedResource string as defined.
     *
     * @param mixed $resource
     * @param mixed $params
     * @return string
     */
    public static function canonicalizedResource($resource, $params)
    {
        if ($params) {
            ksort($params);
            $urlString = '';
            $first = true;
            foreach ($params as $key => $value) {
                if ($first) {
                    $first = false;
                    $urlString = "{$key}={$value}";
                } else {
                    $urlString .= "&{$key}={$value}";
                }
            }
            return $resource . '?' . $urlString;
        }
        return $resource;
    }

    /**
     * Get request authorization string as defined.
     *
     * @param mixed $method
     * @param mixed $resource
     * @param mixed $key
     * @param mixed $params
     * @param mixed $headers
     * @return string
     */
    public static function getRequestAuthorization($method, $resource, $key, $params, $headers)
    {
        if (! $key) {
            return '';
        }
        $content = $method . "\n";
        if (isset($headers['Content-MD5'])) {
            $content .= $headers['Content-MD5'];
        }
        $content .= "\n";
        if (isset($headers['Content-Type'])) {
            $content .= $headers['Content-Type'];
        }
        $content .= "\n";
        $content .= $headers['Date'] . "\n";
        $content .= LogUtil::canonicalizedLOGHeaders($headers) . "\n";
        $content .= LogUtil::canonicalizedResource($resource, $params);
        return LogUtil::hmacSHA1($content, $key);
    }
}
