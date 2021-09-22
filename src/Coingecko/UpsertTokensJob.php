<?php

namespace Nidavellir\Jobs\Coingecko;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Nidavellir\Apis\Coingecko;
use Nidavellir\Cube\Models\Token;
use Nidavellir\Trading\Upserters\TokenUpserter;

/**
 * === REFRESHES ALL TOKENS USING COINGECKO DATABASE
 * This job inserts or updates tokens into the database from the coingecko
 * database.
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
        $tokens = collect(Coingecko::allTokens()->response());

        /**
         * Updates the tokens names from the "tokens" table.
         * Doesn't add new lines. Just updates given the symbol value being
         * the same value as the one from coigecko.
         */
        Token::whereNull('name')
             ->get()
             ->each(function ($model) use ($tokens) {
                 $match = $tokens->where('symbol', strtolower($model['symbol']))->first();
                 if ($match) {
                     TokenUpserter::import([
                         'symbol' => $model->symbol,
                         'name' => $match['name'],
                     ]);
                 }
             });
    }
}
