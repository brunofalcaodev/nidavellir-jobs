<?php

namespace Nidavellir\Jobs\Kucoin;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Nidavellir\Apis\Kucoin;
use Nidavellir\Trading\Upserters\TokenUpserter;

/**
 * === REFRESHES ALL TOKENS FROM KUCOIN DATABASE
 * This job imports or updates tokens into the database. It will call another
 * job called UpdateTokenJob via the TokenUpserter class.
 *
 * Tables involved:
 * - tokens
 * - pairs.
 */
class UpsertTokensJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        $this->onQueue('tokens');
    }

    public function handle()
    {
        $tokens = Kucoin::asSystem()
                      ->allTokens();

        foreach ($tokens->response()['ticker'] as $token) {
            $pair = explode('-', $token['symbol']);
            TokenUpserter::import([
                'symbol' => $pair[0],
                'quote' => $pair[1], ]);
        }
    }
}
