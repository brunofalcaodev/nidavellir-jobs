<?php

namespace Nidavellir\Jobs\Jobs\Alerts;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Nidavellir\Workflows\Pipeline;
use Nidavellir\Workflows\Pipelines\ProcessAlert\ProcessAlert as ProcessAlertPipeline;

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
    public function __construct($headers, $body)
    {
        $this->headers = $headers;
        $this->body = $body;

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
                ->onPipeline(ProcessAlertPipeline::class)
                ->execute();
    }
}
