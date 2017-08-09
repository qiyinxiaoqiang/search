<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Model\Search;
use App\Model\Common;
use App\Model\Follow;
use Entere\Utils\Sign;
use \Firebase\JWT\JWT;

class SearchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct()
    {
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Origin: *");
    }



    public function index(Request $request)
    {
        if(empty($request->input())){
            $type = 'article';
            return view('default.search.search', compact('type'));
        }else{
            $validator = \Validator::make($request->all(), [
                'key' => 'required',
            ]);
            if ($validator->fails()) {
                return $this->returnCode(400,'',$validator->errors()->all());
            }
            $key = $request->input('key');
            $user_id = $request->input('user_id');
            $id = $request->input('id');
            $userinfo = array();
            if(!empty($user_id)){
                $user_id = intval($user_id);
                $author = $this->setvar($user_id);
                $nickname = $author['user_info']['data']['nickname'];
                $userinfo['name'] =  $author['user_info']['data']['name'];
                $userinfo['uid'] = $user_id;
                $userinfo['nickname'] = $nickname;
            }else{
                $user_id = 0;
                $nickname = '';
            }
            $type = $request->input('type');
            $type = isset($type)? $type: 'article';
            $server=$request->server();
            $params = [
                'key' => $key,
                'user_id' => $user_id,
                'id' => isset($id)? $id: 0,
                'type' => $type,
                'page' => $request->input('page',1),
                'limit' => $request->input('limit',10),
            ];
            $data = Search::search($params);
            // if($type == 'article'){
            //     if(!empty($data['data']['res'])){
            //         foreach ($data['data']['res'] as $k => $v) {
            //             if(empty($user_id)){
            //                 $author = $this->setvar($v['user_id']);
            //                 $nickname = $author['user_info']['data']['nickname'];
            //             }
            //             $data['data']['res'][$k]['nickname'] = $nickname;
            //         }
            //     }
            // }
            if($request->ajax()){
                if (array_key_exists('HTTP_X_PJAX', $server) && $server['HTTP_X_PJAX'] == 'true') {
                    switch ($type) {
                        case 'article':
                            return view('default.search.article', compact('data', 'type', 'key', 'userinfo'));
                            break;
                        case 'author':
                            return view('default.search.author', compact('data', 'type', 'key'));
                            break;
                        default:
                            return view('default.search.search', compact('data', 'key', 'userinfo', 'type'));
                            break;
                    }
                }
            }
            return view('default.search.search',compact('data', 'key', 'userinfo', 'type'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'key' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->returnCode(400,'',$validator->errors()->all());
        }
        $user_id = $request->input('user_id');
        $params = [
            'key' => $request->input('key'),
            'type' => $request->input('type'),
            'user_id' => isset($user_id)? $user_id: 0,
            'limit' => 5
        ];
        $data = Search::thinkWord($params);
        return response()->json($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $key)
    {
        $validator = \Validator::make($request->all(), [
            'type' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->returnCode(400,'',$validator->errors()->all());
        }   
        if(!empty($request->input('author_id'))){
            $author_id = $request->input('author_id');
            $author = $this->setvar(intval($author_id));
            $nickname = $author['user_info']['data']['nickname'];
        }else{
            $author_id = 0;
            $nickname = '';
        }
        $params = [
            'key' => $key,
            'user_id' => intval($author_id),
            'type' => $request->input('type'),
            'page' => $request->input('page',1),
            'limit' => $request->input('limit',10),
        ];
        $data = Search::search($params);
        // if($params['type'] == 'article'){
        //     foreach ($data['data']['res'] as $k => $v) {
        //         if($nickname == ''){
        //             $author = $this->setvar($v['user_id']);
        //             $nickname = $author['user_info']['data']['nickname'];
        //         }
        //         $v['nickname'] = $nickname;
        //         $data['data']['res'][$k] = $v;
        //     }
        // }
        return response()->json($data)->setCallback($request->input('callback'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
