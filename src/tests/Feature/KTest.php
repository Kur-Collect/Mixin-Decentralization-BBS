<?php

namespace Tests\Feature;

use Ramsey\Uuid\Uuid;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function test_i_can_test()
    {
        dd(fetchMixinSDk()->wallet()->transfer(config('data.assetId.NXC'), config('runtime.opponentId'), null, 0.00001, 'ccc'));
        dd(fetchMixinSDk()->network()->readUser('a789b1af-1586-4efa-86e1-c6528183b6cb'));
        dd(strlen(base64_encode(Uuid::uuid4()->getBytes())));
        $a = fetchMixinSDk()->wallet()->readUserSnapshots();
        dd($a);
    }
}
