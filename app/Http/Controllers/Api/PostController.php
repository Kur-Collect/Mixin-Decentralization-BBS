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
        return $this->response->paginator(Post::all()->paginate(100), new PostTransformer());
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

        $tailTraceId = $postService->getInterceptUuidSegment(str_replace(config('runtime.headMark'), $res['memo'], ''), 0);

        $response = [
            'title'   => '',
            'content' => '',
            'comment' => [],
        ];

        // 读取 Title
        $titleMemo         = fetchMixinSDk()->wallet()->readTransfer(substr($res['memo'], strlen($res['memo']) - 24, strlen($res['memo']) - 1))['memo'];
        $response['title'] = substr($titleMemo['memo'], 0, strlen($titleMemo['memo']) - 24);

        // 读取 content
        $contentStartId = substr($titleMemo['memo'], strlen($titleMemo['memo']) - 24, strlen($titleMemo['memo']) - 1);
        for (; ;) {
            $res = fetchMixinSDk()->wallet()->readTransfer($contentStartId);
            dump($res);
            $encodeTraceId        = substr($res['memo'], strlen($res['memo']) - 24, strlen($res['memo']) - 1);
            $response ['content'] .= substr($res['memo'], 0, strlen($res['memo']) - 24);
            $traceId              = Uuid::fromBytes(base64_decode($encodeTraceId))->toString();

            if ($traceId == $tailTraceId) {
                break;
            }
        }

        // 读取 comment
        $commentUuid = $postService->getAfterTimeUuid($tailTraceId, 2);

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
        [$title, $content] = [$request->input('title'), $request->input('content')];
        [$titleIds, $contentIds] = [$postService->formatText($title), $postService->formatText($content)];

        // 随机生成一个 Comment Chain
        $commentId = Uuid::uuid4()->toString();

        $memo = config('runtime.headMark') . uuid2Bytes2Base64($titleIds[0]) . uuid2Bytes2Base64($contentIds[0]) . uuid2Bytes2Base64($commentId);

        // 生成 HEAD Chain
        $headInfo = fetchMixinSDk()->wallet()->transfer(config('data.assetId.NXC'), config('runtime.opponentId'), null, 0.00001, $memo);

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

        $tailTraceId = $postService->getInterceptUuidSegment(str_replace(config('runtime.headMark'), $res['memo'], ''), 0);

        $title   = $request->input('title');
        $content = $request->input('content');

        $traceIds = $postService->formatContent($content, $title, null, $tailTraceId, false);

        $post = Post::where('trace_id', $traceId)->first();

        $post = $post->update([
            'trace_id'         => $traceIds[0],
            'comment_trace_id' => $traceIds[count($traceIds) - 1],
        ]);

        return $this->response->item($post, new PostTransformer());
    }

    /**
     * @param                           $traceId
     * @param \App\Services\PostService $postService
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($traceId, PostService $postService)
    {
        if ($postService->headNodeVerify($traceId)) {
            return $this->failed(400, 0, 'isn\'t a head node');
        }
        $post = Post::where('posts', $traceId)->firstOrFail();

        return $post->delete();
    }
}
