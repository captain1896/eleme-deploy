<?php
/**
 * Created by PhpStorm.
 * User: heimonsy
 * Date: 14-10-29
 * Time: 上午11:08
 */


class WebSite
{
    private $redis;

    private $key = 'deploy.L.web.sites';

    public function __construct()
    {
        $this->redis = app('redis')->connection();
    }

    public function getList()
    {
        $jsonArray = $this->redis->zrange($this->key, 0, -1);
        $res = array();
        foreach ($jsonArray as $value) {
            $res[] = json_decode($value, true);
        }
        return $res;
    }

    public function add($site)
    {
        $site = $this->jsonValue($site);
        return $this->redis->zadd($this->key, time(), $site);
    }

    public function remove($site)
    {
        $site = $this->jsonValue($site);
        return $this->redis->zrem($this->key, 0, $site);
    }

    private function jsonValue($site) {
        if (!is_array($site)) {
            $site = json_decode($site);
        }
        ksort($site);
        return json_encode($site);
    }
}