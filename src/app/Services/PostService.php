<?php
/**
 * Created by PhpStorm.
 * User: kurisu
 * Date: 18-12-28
 * Time: 下午5:15
 */

namespace App\Services;

use function GuzzleHttp\Psr7\str;
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

            if ($k == (count($contentArray) - 1)) {
                $traceIds[] = str_repeat('Z', 24);
                $memo       = $text . str_repeat('Z', 24);
            } else {
                $traceIds[] = $uuidObj->toString();
                $memo       = $text . base64_encode($uuidObj->getBytes());
            }

            fetchMixinSDk()->wallet()->transfer(config('data.assetId.NXC'), config('runtime.opponentId'), null, 0.00001, $memo, $traceIds[$k]);
        }

        return $traceIds;
    }

    /**
     * @param $trade_id
     *
     * @return array|bool
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function headNodeVerify($trade_id)
    {
        $res = fetchMixinSDk()->wallet()->readTransfer($trade_id);

        return preg_match('/' . config('runtime.headMark') . '.+/', $res['memo'])
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
        return Uuid::fromBytes(base64_decode(substr($str, 24 * $segment, 24)))->toString();
    }

    /**
     * @param string $traceId
     *
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getContentToTail(string $traceId)
    {
        $content = '';
        for (; ;) {
            $memo        = fetchMixinSDk()->wallet()->readTransfer($traceId)['memo'];
            $contentPart = substr($memo, 0, strlen($memo) - 24);
            $content     .= $contentPart;
            dump($memo, $content);

            $base64Uuid = substr($memo, strlen($memo) - 24, 24);

            if ($base64Uuid == str_repeat('Z', 24)) {
                break;
            }

            $traceId = Uuid::fromBytes(base64_decode($base64Uuid))->toString();
        }

        return gzuncompress(base64_decode($content));
    }

}
