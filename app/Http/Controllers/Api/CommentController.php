<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\CommentRequest;
use App\Jobs\StoreCommentJob;
use App\Services\PostService;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store($traceId, CommentRequest $request,PostService $postService)
    {
        $res = $postService->headNodeVerify($traceId);

        if (!$res) {
            return $this->failed(400, 0, 'isn\'t a head node');
        }
        //TODO 投入队列


        StoreCommentJob::dispatch($traceId,$request->input('comment'));

    }
}
