<?php

namespace App\Http\Controllers;

use App\Services\PostService;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $response = $this->api->get('/posts/', ['page' => $request->input('page')]);

        return view('post.index', compact('response'));

    }

    /**
     * @param             $traceId
     * @param PostService $postService
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function show($traceId, PostService $postService)
    {
        $response = $this->api->get('/posts/' . $traceId);
        $title    = $postService->getContentToTail($response['data']['title']);
        $content  = $postService->getContentToTail($response['data']['content']);

        return view('post.show', compact('title', 'content'));

    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $response = $this->api->post('/posts/', [
            'title'   => $request->input('title'),
            'content' => $request->input('content')
        ]);

        return redirect()->route('post.show', ['traceId' => $response->head_trace_id]);
    }

    /**
     * @param $traceId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function edit($traceId)
    {
        $response = $this->api->patch('/posts/' . $traceId);
        return redirect()->route('post.show', ['traceId' => $response->head_trace_id]);
    }

    /**
     * @param $traceId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($traceId)
    {
        $this->api->delete('/posts/' . $traceId);
        return redirect()->route('post.index');
    }
}
