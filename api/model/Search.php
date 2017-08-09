<?php
namespace App\Model;

use Moloquent as Eloquent;
use Input, Request, Cache, Redis2;
use App\Model\Publicm;

use Illuminate\Support\Facades\DB;
use Elasticsearch\ClientBuilder as Elasticsearch;

class Search extends Eloquent
{
    protected $connection = 'mongodb';
    protected $collection = 'articles';
    protected $primaryKey = 'aid';//定义主键
    private static $host = ['172.16.88.32:9200'];

    public static function search($params)
    {
        if(!empty($params['id'])){
            if($params['type'] == 'article'){
                $json = '{
                    "query": {
                        "bool": {
                            "must": [
                                { "match_phrase": { "aid":  '.$params['id'].' }}
                            ]
                        }
                    },
                    "highlight" : {
                        "pre_tags" : ["<span class=\"search_word\">"],
                        "post_tags" : ["</span>"],
                        "fields" : {
                            "title" : {}
                        }
                    }
                }';
                $arr = [
                    'index' => 'v5_uc',
                    'type' => '_search',
                    'body' => $json
                ];
                $client = Elasticsearch::create()->setHosts(self::$host)->build();
                $response = $client->index($arr);
                $count = $response['hits']['total'];
                $resource = $response['hits']['hits'];
            //     $data = Search::select('aid', 'user_id', 'notebook_id', 'title', 'summary', 'add_time')
            //         ->where('aid', intval($params['id']))
            //         ->get();
            $data = array();
            $data[0] = $resource[0]['_source'];
                $arr = \DB::table('users')
                    ->select('nickname','name')
                    ->where('uid', $data[0]['user_id'])
                    ->first();
                $data[0]['nickname'] = $arr['nickname'];
                $data[0]['name'] = $arr['name'];
                $data[0]['user_url'] = 'http://'.$data[0]['name'].\Config::get('app.blog_domain');
                $data[0]['article_url'] = $data[0]['user_url'].'/'.$data[0]['aid'].'.html';
                $data[0]['highlight_title'] = isset($resource['highlight']['title'])? $resource['highlight']['title'][0]: '';
                
                
                $nums = Publicm::getArticleNums($params['id']);
                $data[0]['click'] = isset($nums['click']) ? $nums['click'] : 0;
                $data[0]['support'] = isset($nums['support']) ? $nums['support'] : 0;
                $data[0]['oppose'] = isset($nums['oppose']) ? $nums['oppose'] : 0;
                $data[0]['comment'] = isset($nums['comment']) ? $nums['comment'] : 0;
            }elseif($params['type'] == 'author'){
                $data = \DB::table('users')
                    ->select('uid', 'name', 'nickname', 'avatar', 'avatar_by_editor')
                    ->where('uid', intval($params['id']))
                    ->get();
                $data[0]['user_url'] = 'http://'.$data[0]['name'].\Config::get('app.blog_domain');
                $data[0]['article_url'] = '';    
                    
                $nums = Publicm::getUserNums($params['id']);
                $data[0]['avatar_color'] = getAvatarColor($data[0]['uid']);
                $data[0]['articles'] = isset($nums['article'])? $nums['article']: 0;
                $data[0]['click'] = isset($nums['click'])? $nums['click']: 0;
                $data[0]['support'] = isset($nums['support'])? $nums['support']: 0;
            }
            $count = 1;
        }else{
            if($params['type'] == 'article'){
                if(empty($params['user_id'])){
                    $json = '{
                        "from": '.($params['page'] - 1)*$params['limit'].', "size": '.$params['limit'].',
                        "query": {
                            "bool": {
                                "must": [
                                    {"multi_match" : {
                                        "query":      "'.$params['key'].'",
                                        "type":       "best_fields",
                                        "fields":     ["body^3", "title"],
                                        "operator":   "and" 
                                    }},
                                    { "match_phrase": { "is_publish":  "y" }},
                                    { "match_phrase": { "is_recyle":  "n" }},
                                    { "match_phrase": { "is_del":  "n" }},
                                    { "match_phrase": { "monitor.is_audit":  "y" }},
                                    { "match_phrase": { "monitor.hidden.is":  "n" }},
                                    { "match_phrase": { "monitor.del.is":  "n" }}
                                ]
                            }
                        },
                        "highlight" : {
                            "pre_tags" : ["<em class=\"search_word\">"],
                            "post_tags" : ["</em>"],
                            "fields" : {
                                "title" : {},
                                "body" : {}
                            }
                        }
                    }';
                    $arr = [
                        'index' => 'v5_uc',
                        'type' => '_search',
                        'body' => $json
                    ];
                    $client = Elasticsearch::create()->setHosts(self::$host)->build();
                    $response = $client->index($arr);
                    $count = $response['hits']['total'];
                    $data = $response['hits']['hits'];
                }else{
                    $json = '{
                        "from": '.($params['page'] - 1)*$params['limit'].', "size": '.$params['limit'].',
                        "query": {
                            "bool": {
                                "must": [
                                    { "match_phrase": { "user_id":   '.$params['user_id'].' }},
                                    {"multi_match" : {
                                        "query":      "'.$params['key'].'",
                                        "type":       "best_fields",
                                        "fields":     ["body^3", "title"],
                                        "operator":   "and" 
                                    }},
                                    { "match_phrase": { "is_publish":  "y" }},
                                    { "match_phrase": { "is_recyle":  "n" }},
                                    { "match_phrase": { "is_del":  "n" }},
                                    { "match_phrase": { "monitor.is_audit":  "y" }},
                                    { "match_phrase": { "monitor.hidden.is":  "n" }},
                                    { "match_phrase": { "monitor.del.is":  "n" }}
                                ]
                            }
                        },
                        "highlight" : {
                            "pre_tags" : ["<em class=\"search_word\">"],
                            "post_tags" : ["</em>"],
                            "fields" : {
                                "title" : {},
                                "body": {}
                            }
                        }
                    }';
                    $arr = [
                        'index' => 'v5_uc',
                        'type' => '_search',
                        'body' => $json
                    ];
                    $client = Elasticsearch::create()->setHosts(self::$host)->build();
                    $response = $client->index($arr);
                    $count = $response['hits']['total'];
                    $data = $response['hits']['hits'];
                }
                foreach($data as $k => $v){
                    $arr = \DB::table('users')
                        ->select('nickname','name')
                        ->where('uid', $v['_source']['user_id'])
                        ->first();
                    $data[$k] = $v['_source'];
                    $data[$k]['nickname'] = $arr['nickname'];
                    $data[$k]['name'] = $arr['name']; 
	                $data[$k]['user_url'] = 'http://'.$data[$k]['name'].\Config::get('app.blog_domain');
	                $data[$k]['article_url'] = $data[$k]['user_url'].'/'.$data[$k]['aid'].'.html'; 
                    $nums = Publicm::getArticleNums($data[$k]['aid']);
                    $data[$k]['click'] = isset($nums['click']) ? $nums['click'] : 0;
                    $data[$k]['support'] = isset($nums['support']) ? $nums['support'] : 0;
                    $data[$k]['oppose'] = isset($nums['oppose']) ? $nums['oppose'] : 0;
                    $data[$k]['comment'] = isset($nums['comment']) ? $nums['comment'] : 0;
                    $data[$k]['highlight_title'] = isset($v['highlight']['title'])? $v['highlight']['title'][0]: '';
                    $data[$k]['highlight_summary'] = isset($v['highlight']['body'])? strip_tags($v['highlight']['body'][0], '<em>'): '';
                }
            }elseif($params['type'] == 'author'){
                $data = \DB::table('users')
                    ->select('uid', 'name', 'nickname', 'avatar', 'avatar_by_editor')
                    ->where('group_id', 200)
                    ->where(function($query) use ($params)
                    {
                        $query->orWhere('name', 'like', '%'.$params['key'].'%')
                                  ->orWhere('nickname', 'like', '%'.$params['key'].'%');
                    })
                    ->where(function($q) use ($params){
                        $q->orWhere('monitor.is_audit', 'y')
                            ->orWhere('monitor.is_pending', 'y');
                    })
                    ->where('monitor.is_lock', 'n')
                    ->where('monitor.is_del', 'n')
                    ->where('reg_from', 'blogchina')
                    ->orderBy('add_time', 'desc')
                    ->skip(($params['page'] - 1) * intval($params['limit']))
                    ->take(intval($params['limit']))
                    ->get();
                $count = \DB::table('users')
                        ->where('group_id', 200)
                        ->where(function($query) use ($params)
                        {
                            $query->orWhere('name', 'like', '%'.$params['key'].'%')
                                      ->orWhere('nickname', 'like', '%'.$params['key'].'%');
                        })
                        ->where(function($q) use ($params){
                            $q->orWhere('monitor.is_audit', 'y')
                                ->orWhere('monitor.is_pending', 'y');
                        })
                        ->where('monitor.is_lock', 'n')
                        ->where('monitor.is_del', 'n')
                        ->where('reg_from', 'blogchina')
                        ->count();
                foreach ($data as $key => $value) { 
                	$data[$key]['name'] = $value['name'];
                	$data[$key]['user_url'] = 'http://'.$data[$key]['name'].\Config::get('app.blog_domain');
                	$data[$key]['article_url'] = '';    
                    $nums = Publicm::getUserNums($value['uid']);
                    $data[$key]['avatar_color'] = getAvatarColor($value['uid']);
                    $data[$key]['articles'] = isset($nums['article'])? $nums['article']: 0;
                    $data[$key]['click'] = isset($nums['click'])? $nums['click']: 0;
                    $data[$key]['support'] = isset($nums['support'])? $nums['support']: 0;
                }
            }
        }
        // $count = count($data);
        return ['res' => $data, 'count' => $count];
    }

    public static function thinkWord($params)
    {
        if($params['type'] == 'article'){
            if(empty($params['user_id'])){
                $json = '{
                    "from": 0, "size": 5,
                    "query": {
                        "bool": {
                            "must": [
                                {"multi_match" : {
                                    "query":      "'.$params['key'].'",
                                    "type":       "best_fields",
                                    "fields":     ["title"],
                                    "operator":   "and" 
                                }},
                                { "match_phrase": { "is_publish":  "y" }},
                                { "match_phrase": { "is_recyle":  "n" }},
                                { "match_phrase": { "is_del":  "n" }},
                                { "match_phrase": { "monitor.is_audit":  "y" }},
                                { "match_phrase": { "monitor.hidden.is":  "n" }},
                                { "match_phrase": { "monitor.del.is":  "n" }}
                            ]
                        }
                    },
                    "highlight" : {
                        "pre_tags" : ["<em class=\"search_word\">"],
                        "post_tags" : ["</em>"],
                        "fields" : {
                            "title" : {}
                        }
                    }
                }';
                $arr = [
                    'index' => 'v5_uc',
                    'type' => '_search',
                    'body' => $json
                ];
                $client = Elasticsearch::create()->setHosts(self::$host)->build();
                $response = $client->index($arr);
                $data = $response['hits']['hits'];
            }else{
                $json = '{
                    "from": 0, "size": 5,
                    "query": {
                        "bool": {
                            "must": [
                                { "match_phrase": { "user_id":   '.$params['user_id'].' }},
                                {"multi_match" : {
                                    "query": "'.$params['key'].'",
                                    "type": "best_fields",
                                    "fields": ["title"],
                                    "operator":   "and" 
                                }},
                                { "match_phrase": { "is_publish":  "y" }},
                                { "match_phrase": { "is_recyle":  "n" }},
                                { "match_phrase": { "is_del":  "n" }},
                                { "match_phrase": { "monitor.is_audit":  "y" }},
                                { "match_phrase": { "monitor.hidden.is":  "n" }},
                                { "match_phrase": { "monitor.del.is":  "n" }}
                            ]
                        }
                    },
                    "highlight" : {
                        "pre_tags" : ["<em class=\"search_word\">"],
                        "post_tags" : ["</em>"],
                        "fields" : {
                            "title" : {}
                        }
                    }
                }';
                $arr = [
                    'index' => 'v5_uc',
                    'type' => '_search',
                    'body' => $json
                ];
                $client = Elasticsearch::create()->setHosts(self::$host)->build();
                $response = $client->index($arr);
                $count = $response['hits']['total'];
                $data = $response['hits']['hits'];
            }
        }elseif($params['type'] == 'author'){
            $data = \DB::table('users')
                    ->select('uid', 'name', 'nickname')
                    ->where(function($query) use ($params)
                    {
                        $query->orWhere('name', 'like', '%'.$params['key'].'%')
                                ->orWhere('nickname', 'like', '%'.$params['key'].'%');
                    })
                    ->where(function($q) use ($params){
                        $q->orWhere('monitor.is_audit', 'y')
                            ->orWhere('monitor.is_pending', 'y');
                    })
                    ->where('monitor.is_lock', 'n')
                    ->where('monitor.is_del', 'n')
                    ->where('reg_from', 'blogchina')
                    ->orderBy('add_time', 'desc')
                    ->take($params['limit'])
                    ->get();
        }
        return $data;
    }
}



