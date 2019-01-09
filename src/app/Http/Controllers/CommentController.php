<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function show($traceId)
    {
        $response = $this->api->get('/posts/'.$traceId.'/comment');
    }

    public function store($traceId, Request $request)
    {
        $response = $this->api->patch('/posts/'.$traceId.'/comment');
    }
}
