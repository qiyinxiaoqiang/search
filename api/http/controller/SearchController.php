<?php

namespace App\Http\Controllers;

use Redis2;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use \Firebase\JWT\JWT;
use App\Model\Publicm;
use App\Model\Search;
use App\Model\UserLog;
use Entere\Utils\SequenceNumber;
use Event;
use App\Events\ArticleNumEvent;
use App\Model\Notebook;
use App\Model\Archive;
use Cache;

class SearchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'key' => 'required',
            'type' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->returnCode(400,'',$validator->errors()->all());
        }
        $params = [
            'key' => $request->input('key'),
            'user_id' => intval($request->input('user_id')),
            'id' => $request->input('id'),
            'type' => $request->input('type'),
            'page' => $request->input('page',1),
            'limit' => $request->input('limit',10),
        ];
        $data = Search::search($params);
        return $this->returnCode(200,'',$data);
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
            'type' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->returnCode(400,'',$validator->errors()->all());
        }
        $params = [
            'key' => $request->input('key'),
            'type' => $request->input('type'),
            'user_id' => intval($request->input('user_id')),
            'limit' => $request->input('limit')
        ];
        $data = Search::thinkWord($params);
        return $this->returnCode(200, '', $data);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $key)
    {
        // 
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
