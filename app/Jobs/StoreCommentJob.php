<?php

namespace App\Jobs;

use App\Services\PostService;
use ExinOne\MixinSDK\Exceptions\MixinNetworkRequestException;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Ramsey\Uuid\Uuid;

class StoreCommentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $headTraceId;
    protected $postService;
    protected $commentContent;
    protected $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($headTraceId, $commentContent, $user = null)
    {
        $this->headTraceId    = $headTraceId;
        $this->postService    = new PostService();
        $this->commentContent = $commentContent;
        $this->user           = $user;

        $this->onQueue('comment');
    }

    /**
     * @return bool
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        $res = fetchMixinSDk()->wallet()->readTransfer($this->headTraceId);
        $memoUuid = str_replace(config('runtime.headMark'), '', $res['memo']);

        $firstCommentTraceId = Uuid::fromBytes(base64_decode($this->postService->getInterceptUuidSegment($memoUuid, 2)))->toString();

        try {
            $nextCommentTraceId = $firstCommentTraceId;
            for (; ;) {
                $res                = fetchMixinSDk()->wallet()->readTransfer($nextCommentTraceId);
                $nextCommentTraceId = Uuid::fromBytes(base64_decode(substr($res['memo'], strlen($res['memo']) - 24, strlen($res['memo']) - 1)))->toString();
            }
        } catch (MixinNetworkRequestException $e) {
            $memo = base64_encode(gzcompress($this->commentContent)) . Uuid::uuid4()->toString();

            fetchMixinSDk()->wallet()->transfer(config('data.assetId.NXC'), '17d1c125-aada-46b0-897d-3cb2a29eb011', null, 0.01, $memo, $nextCommentTraceId);

            return true;
        }
    }
}
