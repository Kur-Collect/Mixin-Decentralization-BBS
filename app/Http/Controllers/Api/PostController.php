<?php

namespace App\Http\Controllers\Api\V1;

use App\Post;
use App\Transformers\PostTransformer;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class PostController extends Controller
{
    /**
     * @return \Dingo\Api\Http\Response
     */
    public function index()
    {
        return $this->response->paginator(Post::all()->paginate(15), new PostTransformer());
    }

    /**
     * @param Request $request
     *
     * @return string
     * TODO 网络请求需要推入队列
     */
    public function show(Request $request)
    {
        $traceId = $request->input('trace_id');
        $content = '';
        for (; ;) {
            $res = fetchMixinSDk()->wallet()->readTransfer($traceId);
            dump($res);
            $encodeTraceId = substr($res['memo'], strlen($res['memo']) - 24, strlen($res['memo']) - 1);
            $content       .= substr($res['memo'], 0, strlen($res['memo']) - 24);
            if ($encodeTraceId == str_repeat('z', 24)) {
                break;
            }

            $traceId = Uuid::fromBytes(base64_decode($encodeTraceId))->toString();
        }
//        dump($content);
//        dd(gzuncompress(base64_decode($content)));

        return gzuncompress(base64_decode($content));
    }

    /**
     * @param Request $request
     * TODO Request类 限制 title 长度 140-24, (mb_strlen)
     * TODO 需要考虑全语言通用的压缩方式，目前仅为尝试中的测试方式
     * TODO 需要考虑切块存储的方法进行存储
     *
     */
    public function store(Request $request)
    {
        $title   = $request->input('title');
        $content = $request->input('content');

        $traceIds = [Uuid::uuid4()->toString()];

        // 生成了第一个 trace_id
        $compressionContent = base64_encode(gzcompress($content));
        $contentArray       = str_split($compressionContent, 140 - 24);
        array_unshift($contentArray, $title);

        foreach ($contentArray as $k => $v) {
            $uuid       = Uuid::uuid4();
            $traceIds[] = $uuid->toString();
            // TODO 此处最后一个 trace_id 是评论的起始id
            $memo = $v . base64_encode($uuid->getBytes());

            $res = fetchMixinSDk()->wallet()->transfer(config('data.assetId.NXC'), '17d1c125-aada-46b0-897d-3cb2a29eb011', null, 0.01, $memo, $traceIds[$k]);
        }

        $post = Post::create([
            'trace_id'        => $traceIds[0],
            'comment_trace_id' => $traceIds[count($traceIds) - 1],
        ]);

        return $this->response->item($post, new PostTransformer());
    }

    /**
     * @param Post $post
     */
    public function edit(Post $post)
    {
        // TODO 重新生成链条的时候注意最后的一个 trace_id 仍然需要使用之前的 最后一个的 trace_id ， 因为指向 comment 链条

    }

    /**
     * @param Post $post
     *
     * @return bool|null
     * @throws \Exception
     */
    public function delete(Post $post)
    {
        return $post->delete();
    }
}
