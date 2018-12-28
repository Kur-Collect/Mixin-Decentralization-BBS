<?php
/**
 * Created by PhpStorm.
 * User: kurisu
 * Date: 18-12-28
 * Time: 下午5:15
 */

namespace App\Services;

use Ramsey\Uuid\Uuid;

class PostService
{
    // 塞入内容和标题后,生成每个下一段的UUID,将内容全部返回
    // headTraceId 是头部写Mark标示那个区块的TraceId
    // tailTranceId 是尾部区块写Mark标示的那个ID
    // 头部区块中会写入尾部区块的TraceID和下一个区块的TraceID
    /**
     * @param        $content
     * @param string $title
     * @param null   $headTraceId
     * @param null   $tailTraceId
     * @param bool   $addTailNode
     *
     * @return array
     * @throws \Exception
     */
    public function formatContent($content, $title = 'default', $headTraceId = null, $tailTraceId = null, $addTailNode = true)
    {
        $traceIds    = [empty($headTraceId) ? Uuid::uuid4()->toString() : $headTraceId];
        $tailTraceId = empty($tailTraceId) ? Uuid::uuid4()->toString() : $tailTraceId;

        $compressionContent = base64_encode(gzcompress($content));
        $contentArray       = str_split($compressionContent, 140 - 24);

        // 填充 头部标示
        array_unshift($contentArray,
            config('runtime.headMark').base64_encode(Uuid::fromString($tailTraceId)->getBytes()),
            $title
        );

        // 是否添加评论节点
        if ($addTailNode) {
            // 填充尾部标示和评论标示
            array_push($contentArray,
                config('runtime.tailMark'),
                config('runtime.commentMark')
            );
        }

        foreach ($contentArray as $k => $content) {
            // 读取尾部 TraceID
            $uuid = ($k == count($contentArray - 3))
                ? $uuid = Uuid::fromString($tailTraceId)
                : Uuid::uuid4();

            $traceIds[] = $uuid->toString();
            $memo       = $content.base64_encode($uuid->getBytes());

            fetchMixinSDk()->wallet()->transfer(config('data.assetId.NXC'), '17d1c125-aada-46b0-897d-3cb2a29eb011', null, 0.01, $memo, $traceIds[$k]);
        }

        return $traceIds;
    }

    /**
     * @param $trade_id
     *
     * @return false|int
     */
    public function headNodeVerify($trade_id)
    {
        $res = fetchMixinSDk()->wallet()->readTransfer($trade_id);

        return preg_match('/'.config('runtime.headMark').'.+/', $res['memo'])
            ? $res
            : false;
    }

    /**
     * @param string $str
     * @param        $segment
     *
     * @return string
     */
    public function getInterceptUuidSegment(string $str, $segment)
    {
        return Uuid::fromBytes(substr($str, 24 * $segment, 24))->toString();
    }

    public function getAfterTimeUuid($traceId, $time = 1)
    {
        $nextUuid = $traceId;
        for ($i = 0; $i < $time; ++$i) {
            $res = fetchMixinSDk()->wallet()->readTransfer($nextUuid);
            $nextUuid=Uuid::fromBytes(base64_decode(substr($res['memo'], strlen($res['memo']) - 24, strlen($res['memo']) - 1)))->toString();
        }

        return $nextUuid;
    }
}
