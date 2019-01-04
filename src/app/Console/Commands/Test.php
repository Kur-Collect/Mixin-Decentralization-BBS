<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;
use function Zend\Diactoros\parseCookieHeader;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:c';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'r';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {
        dd($this->show());
        dd($this->store());
        dd(fetchMixinSDk()->wallet()->readAssets());

        $context = '';
        for ($i = 0; $i <= 5; ++$i) {
            $context .= Uuid::uuid4()->getBytes();
            dump(strlen(Uuid::uuid4()->getBytes()));
        }
//        dd(strlen(base64_encode($context)));
        dd(strlen(base64_encode(zlib_encode($context, ZLIB_ENCODING_RAW))));


        dd(-1);
        $context = str_repeat('asjdfowieujrisadfvniox/cc', 500);
        dump(base64_encode(gzcompress($context)), '---', strlen(base64_encode(gzcompress($context))), '---');
        dump(base64_encode(gzencode($context)), '---', strlen(base64_encode(gzencode($context))), '---');
        dump(base64_encode(gzdeflate($context)), '---', strlen(base64_encode(gzdeflate($context))), '---');

    }

    public function store()
    {
        $title   = 'titlexxxxccc';
        $content = str_repeat('@@vfavavavacsaxc98vxz98c7v89u-dfxasu890-avsd890u-avsd890-asvduy890-vasd08-yhsdvdwqre235w4a97f-*w44-tg8345-*tg7-gh7b-3*57yhg-*354rh74-3*h57gy4-tfasder23r5r2wxdf', 112);

        $traceIds           = [Uuid::uuid4()->toString()];
        $compressionContent = base64_encode(gzcompress($content));
        dump($compressionContent);
        $contentArray       = str_split($compressionContent, 140 - 24);
        foreach ($contentArray as $k => $v) {
            $uuid = Uuid::uuid4();
            if (count($contentArray) != ($k + 1)) {
                $traceIds[] = $uuid->toString();
                $memo       = $v . base64_encode($uuid->getBytes());
            } else {
                $memo = $v . str_repeat('z', 24);
            }

            $res = fetchMixinSDk()->wallet()->transfer(config('data.assetId.NXC'), '17d1c125-aada-46b0-897d-3cb2a29eb011', null, 0.01, $memo, $traceIds[$k]);
            dump($res);
        }
        dd($traceIds);

        // 创造第一个块
//        fetchMixinSDk()->wallet()->transfer(config('data.assetId.NXC'), '17d1c125-aada-46b0-897d-3cb2a29eb011', null, 0.01, $headerTraceId);

    }

    public function show()
    {
        $traceId = 'edc4b0d0-f2b9-45fb-9131-14bd3ae9e9f2';
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
}
