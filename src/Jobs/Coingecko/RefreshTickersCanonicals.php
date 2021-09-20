<?php

namespace Nidavellir\Jobs\Jobs\Coingecko;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Nidavellir\Apis\Coingecko;
use Nidavellir\Cube\Models\Ticker;

class RefreshTickersCanonicals implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->onQueue('tickers');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = Coingecko::allTickers();

        foreach ($data->response() as $ticker) {
            Ticker::updateOrCreate(
                ['coingecko_id' => $ticker['id']],
                ['name'         => $ticker['name'],
                    'canonical'    => $ticker['symbol'],
                    'coingecko_id' => $ticker['id'],
                ]
            );
        }
    }
}
