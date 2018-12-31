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
     */
    public function show($traceId, PostService $postService)
    {
        $res = $postService->headNodeVerify($traceId);

        if (!$res) {
            return $this->failed(400, 0, 'isn\'t a head node');
        }

        $tailTraceId = $postService->getInterceptUuidSegment(str_replace(config('runtime.headMark'), $res['memo'], ''), 0);

        // 然后跳过两个块取得第一个 Comment 的traceId
        $commentUuid = $postService->getAfterTimeUuid($tailTraceId, 2);

        $response = [];

        try {
            for (; ;) {
                $res                  = fetchMixinSDk()->wallet()->readTransfer($commentUuid);
                $encodeTraceId        = substr($res['memo'], strlen($res['memo']) - 24, strlen($res['memo']) - 1);
                $response ['content'] .= substr($res['memo'], 0, strlen($res['memo']) - 24);
                $commentUuid          = Uuid::fromBytes(base64_decode($encodeTraceId))->toString();
            }
        } catch (MixinNetworkRequestException $e) {
            return $this->success($response);
        }
    }

    /**
     * @param                $traceId
     * @param CommentRequest $request
     * @param PostService    $postService
     *
     * @return \Illuminate\Http\JsonResponse
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
