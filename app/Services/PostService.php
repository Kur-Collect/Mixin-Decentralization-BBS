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
    // headTraceId 是头部写Mark标示那个区块的 TraceId
    // tailTranceId 是尾部区块写Mark标示的那个 ID
    // 头部区块中会写入尾部区块的TraceID和下一个区块的TraceID
    /**
     * @param $text
     *
     * @return array
     * @throws \Exception
     */
    public function formatText($text)
    {
        $traceIds = [Uuid::uuid4()->toString()];

        $compressionContent = base64_encode(gzcompress($text));
        $contentArray       = str_split($compressionContent, 140 - 24);

        foreach ($contentArray as $k => $text) {
            $uuidObj = Uuid::uuid4();

            if ($k == count($contentArray) - 1) {
                $traceIds[] = $uuidObj->toString();
                $memo       = $text . base64_encode($uuidObj->getBytes());
            } else {
                $traceIds[] = str_repeat('Z', 24);
                $memo       = $text . str_repeat('Z', 24);
            }

            fetchMixinSDk()->wallet()->transfer(config('data.assetId.NXC'), config('runtime.opponentId'), null, 0.00001, $memo, $traceIds[$k]);
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

        return preg_match('/' . config('runtime.headMark') . '.+/', $res['memo'])
            ? $res
            : false;
    }

    /**
     * @param             $traceId
     * @param PostService $postService
     *
     * @return string
     */
    public function getTailTraceIdFromHeadTraceId($traceId, PostService $postService)
    {
        $res = $postService->headNodeVerify($traceId);

        return $postService->getInterceptUuidSegment(str_replace(config('runtime.headMark'), $res['memo'], ''), 0);
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

    /**
     * @param     $traceId
     * @param int $time
     *
     * @return string
     */
    public function getAfterTimeUuid($traceId, $time = 1)
    {
        $nextUuid = $traceId;
        for ($i = 0; $i < $time; ++$i) {
            $res      = fetchMixinSDk()->wallet()->readTransfer($nextUuid);
            $nextUuid = Uuid::fromBytes(base64_decode(substr($res['memo'], strlen($res['memo']) - 24, strlen($res['memo']) - 1)))->toString();
        }

        return $nextUuid;
    }

    public function readTitleWithHeadTraceId(string $traceId)
    {


    }

    public function readContentWithHeadTraceId(string $traceId)
    {


    }

    public function readCommentWithHeadTraceId(string $traceId)
    {


    }
}
