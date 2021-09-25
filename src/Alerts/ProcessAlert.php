<?php

namespace Nidavellir\Jobs\Alerts;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Nidavellir\Pipelines\Pipeline;
use Nidavellir\Pipelines\ProcessAlert\ParseAlert;

class ProcessAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $headers;
    public $body;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Request $request = null, array $headers = null, string $body = null)
    {
        if ($request) {
            $this->headers = $request->header();
            $this->body = $request->getContent();
        } else {
            $this->headers = $headers;
            $this->body = $body;
        }

        $this->onQueue('alerts');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Pipeline::set('headers', $this->headers)
                ->set('body', $this->body)
                ->onPipeline(ParseAlert::class)
                ->execute();
    }
}
