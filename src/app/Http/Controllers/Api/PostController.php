<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\PostRequest;
use App\Http\Requests\TitleRequest;
use App\Post;
use App\Services\PostService;
use App\Transformers\PostTransformer;
use ExinOne\MixinSDK\Exceptions\MixinNetworkRequestException;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class PostController extends Controller
{
    /**
     * @return \Dingo\Api\Http\Response
     */
    public function index()
    {
        return $this->response->paginator(Post::paginate(5), new PostTransformer());
    }

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

        $data = [
            'title'   => $postService->getInterceptUuidSegment($memoUuid, 0),
            'content' => $postService->getInterceptUuidSegment($memoUuid, 1),
            'comment' => $postService->getInterceptUuidSegment($memoUuid, 2),
        ];

        return $this->success($data);
    }

    /**
     * TODO 需要考虑全语言通用的压缩方式，目前仅为尝试中的测试方式
     * TODO 需要考虑切块存储的方法进行存储
     *
     * @param \App\Http\Requests\TitleRequest $request
     * @param \App\Services\PostService       $postService
     *
     * @return \Dingo\Api\Http\Response
     * @throws \Exception
     */
    public function store(PostRequest $request, PostService $postService)
    {
        $titleIds   = $postService->formatText($request->input('title'));
        $contentIds = $postService->formatText($request->input('content'));

        // 随机生成一个 Comment Chain
        $commentId = Uuid::uuid4()->toString();

        $headMemo = config('runtime.headMark') . uuid2Bytes2Base64($titleIds[0]) . uuid2Bytes2Base64($contentIds[0]) . uuid2Bytes2Base64($commentId);

        // 生成 HEAD Chain
        $headInfo = fetchMixinSDk()->wallet()->transfer(config('data.assetId.NXC'), config('runtime.opponentId'), null, 0.00001, $headMemo);

        $post = Post::create([
            'head_trace_id' => $headInfo['trace_id'],
        ]);

        return $this->response->item($post, new PostTransformer());
    }

    /**
     * @param                                $traceId
     * @param \App\Http\Requests\PostRequest $request
     * @param \App\Services\PostService      $postService
     *
     * @return \Dingo\Api\Http\Response|\Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function edit($traceId, PostRequest $request, PostService $postService)
    {
        $res = $postService->headNodeVerify($traceId);
        if (!$res) {
            return $this->failed(400, 0, 'isn\'t a head node');
        }

        // 取得 Head Node
        $memoUuid = str_replace(config('runtime.headMark'), '', $res['memo']);

        $commentId = Uuid::fromBytes(base64_decode($postService->getInterceptUuidSegment($memoUuid, 2)))->toString();

        [$titleIds, $contentIds] = [
            $postService->formatText($request->input('title')),
            $postService->formatText($request->input('content'))
        ];

        $headMemo = config('runtime.headMark') . uuid2Bytes2Base64($titleIds[0]) . uuid2Bytes2Base64($contentIds[0]) . uuid2Bytes2Base64($commentId);

        // 生成 HEAD Chain
        $headInfo = fetchMixinSDk()->wallet()->transfer(config('data.assetId.NXC'), config('runtime.opponentId'), null, 0.00001, $headMemo);

        $post = Post::create([
            'head_trace_id' => $headInfo['trace_id'],
        ]);

        return $this->response->item($post, new PostTransformer());
    }

    /**
     * @param             $traceId
     * @param PostService $postService
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function delete($traceId, PostService $postService)
    {
        if ($postService->headNodeVerify($traceId)) {
            return $this->failed(400, 0, 'isn\'t a head node');
        }
        $post = Post::where('head_trace_id', $traceId)->firstOrFail();

        return $post->delete();
    }
}
