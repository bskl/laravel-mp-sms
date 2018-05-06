<?php

namespace Bskl\MpSms\Jobs;

use Bskl\MpSms\MpSms;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class WriteLog implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var MpSms
     */
    private $client;

    /**
     * @var void
     */
    protected $sms;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(MpSms $client, $sms)
    {
        $this->client = $client;
        $this->sms = $sms;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->client->writeLog($this->sms);
    }
}
