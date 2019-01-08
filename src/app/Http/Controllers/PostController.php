<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {

    }

    public function show($traceId)
    {
        $response = $this->api->get('/posts/'.$traceId);
        dd($response);
    }

    public function store()
    {

    }

    public function edit()
    {

    }

    public function delete()
    {

    }
}
