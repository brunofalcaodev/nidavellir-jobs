<?php

namespace Nidavellir\Jobs\Coingecko;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Nidavellir\Apis\Coingecko;
use Nidavellir\Cube\Models\Token;

class RefreshTokensSymbols implements ShouldQueue
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
        $data = Coingecko::allTokens();

        foreach ($data->response() as $token) {
            Token::updateOrCreate(
                ['coingecko_id' => $token['id']],
                ['name'         => $token['name'],
                    'canonical'    => $token['symbol'],
                    'coingecko_id' => $token['id'],
                ]
            );
        }
    }
}
