<?php

namespace Nidavellir\Jobs\Jobs\Coingecko;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Nidavellir\Apis\Coingecko;
use Nidavellir\Cube\Models\Token;

class RefreshTokensUrls implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->onQueue('tokens');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $tokens = Token::whereNull('image_url')
                         ->take(250)
                         ->get();

        $ids = $tokens->pluck('coingecko_id')->join(',');

        $data = Coingecko::allMarkets(['ids' => $ids]);

        foreach ($data->response() as $token) {
            Token::updateOrCreate(
                ['coingecko_id' => $token['id']],
                ['image_url' => $token['image']]
            );
        }
    }
}
