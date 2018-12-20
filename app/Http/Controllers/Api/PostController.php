<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class PostController extends Controller
{
    public function index()
    {


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
            $content .= substr($res['memo'], 0, strlen($res['memo']) - 24 );
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
     * TODO Request类 限制 title 长度 140, (mb_strlen)
     * TODO 需要考虑全语言通用的压缩方式，目前仅为尝试中的测试方式
     * TODO 需要考虑切块存储的方法进行存储
     *
     */
    public function store(Request $request)
    {
        $title   = $request->input('title');
        $content = $request->input('content');

        // 生成了第一个 trace_id
        $traceIds           = [Uuid::uuid4()->toString()];
        $compressionContent = base64_encode(gzcompress($content));
        $contentArray       = str_split($compressionContent, 140 - 24);
        // TODO 把Title也存进去
        foreach ($contentArray as $k => $v) {
            $uuid = Uuid::uuid4();
            if (count($contentArray) != ($k + 1)) {
                $traceIds[] = $uuid->toString();
                $memo       = $v . base64_encode($uuid->getBytes());
            } else {
                $memo = $v . str_repeat('z', 24);
            }

            $res = fetchMixinSDk()->wallet()->transfer(config('data.assetId.NXC'), '17d1c125-aada-46b0-897d-3cb2a29eb011', null, 0.01, $memo, $traceIds[$k]);
        }

        // TODO 保存 traceId 到数据库


        // TDOO 返回第一个trace_id
        
    }

    public function edit()
    {

    }

    public function delete()
    {

    }
}
