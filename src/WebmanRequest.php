<?php

namespace JieAnthony\LaravelOctaneWorkerman;

class WebmanRequest
{
    use WorkerTrait {
        __call as workerCall;
    }

    public function getRealIp($safe_mode = true)
    {
        $remote_ip = $this->getRemoteIp();

        if ($safe_mode && !static::isIntranetIp($remote_ip)) {
            return $remote_ip;
        }
        
        return $this->header('client-ip', $this->header('x-forwarded-for',
                   $this->header('x-real-ip', $this->header('x-client-ip',
                   $this->header('via', $remote_ip)))));
    }

    public static function isIntranetIp(string $ip)
    {
        $reserved_ips = [
            '167772160'  => 184549375,  /*    10.0.0.0 -  10.255.255.255 */
            '3232235520' => 3232301055, /* 192.168.0.0 - 192.168.255.255 */
            '2130706432' => 2147483647, /*   127.0.0.0 - 127.255.255.255 */
            '2886729728' => 2887778303, /*  172.16.0.0 -  172.31.255.255 */
        ];

        $ip_long = ip2long($ip);

        foreach ($reserved_ips as $ip_start => $ip_end) {
            if (($ip_long >= $ip_start) && ($ip_long <= $ip_end)) {
                return true;
            }
        }

        return false;
    }

    public function __call($method, $args)
    {
        if (method_exists(\request(), $method)) {
            return \request()->$method(...$args);
        }

        return static::workerCall($method, $args);
    }
}