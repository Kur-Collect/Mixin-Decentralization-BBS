<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class StoreCommentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tailTraceId;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($tailTraceId)
    {
        $this->tailTraceId = $tailTraceId;

        $this->onQueue('comment');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //TODO

    }
}
