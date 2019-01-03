<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\CommentRequest;
use App\Jobs\StoreCommentJob;
use App\Services\PostService;
use ExinOne\MixinSDK\Exceptions\MixinNetworkRequestException;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * @param             $traceId
     * @param PostService $postService
     *
     * @return \Illuminate\Http\JsonResponse|mixed
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function show($traceId, PostService $postService)
    {
        $res = $postService->headNodeVerify($traceId);
        if (!$res) {
            return $this->failed(400, 0, 'isn\'t a head node');
        }

        $memoUuid = str_replace(config('runtime.headMark'), '', $res['memo']);

        $commentTraceId = Uuid::fromBytes(base64_decode($postService->getInterceptUuidSegment($memoUuid, 2)))->toString();
        return $this->success(compact('commentTraceId'));
    }

    /**
     * @param                $traceId
     * @param CommentRequest $request
     * @param PostService    $postService
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function store($traceId, CommentRequest $request, PostService $postService)
    {
        $res = $postService->headNodeVerify($traceId);
        if (!$res) {
            return $this->failed(400, 0, 'isn\'t a head node');
        }

        StoreCommentJob::dispatch($traceId, $request->input('comment'));
    }
}
