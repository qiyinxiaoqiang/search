<?php

namespace App\Model;
//use Moloquent as Eloquent;
use Input, Request, Cache, Redis2;
use App\Model\Publicm;

use Illuminate\Support\Facades\DB;

class Search
{
    public static function search($params)
    {
        $headers = array("Accept" => "application/json");
        $data1 = \Unirest\Request::get(\Config::get('app.site_api')."/search", $headers, $params);
        $data = $data1->raw_body;
        $response = json_decode($data,true);
        return $response;
    }

    public static function thinkWord($params)
    {
        $headers = array("Accept" => "application/json");
        $data1 = \Unirest\Request::post(\Config::get('app.site_api')."/search", $headers, $params);
        $data = $data1->raw_body;
        $response = json_decode($data,true);
        return $response;
    }
}