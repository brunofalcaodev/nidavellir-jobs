<?php

namespace Nidavellir\Jobs\Tokens;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Nidavellir\Cube\Models\Pair;
use Nidavellir\Cube\Models\Token;

/**
 * === IMPORTS / UPDATES A TOKEN INTO THE DATABASE
 * This job imports or updates tokens into the database.
 * Tables involved:
 * - tokens
 * - pairs.
 *
 * Upsert PK:
 * - tokens.symbol
 *
 * In case the token doesn't exist, it is created.
 * In case the token exists, it will be updated.
 * In case a 'quote' key is passed, the quote table is also created/updated.
 */
class UpsertTokenJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $token;

    public function __construct(array $token)
    {
        $this->token = $token;

        $this->onQueue('tokens');
    }

    public function handle()
    {
        // Insert token into the database and update pairs if needed.
        DB::transaction(function () {
            /**
             * Remove the 'quote' column from the array so it doesn't blow up
             * the Token updateOrCreate().
             */
            $quote = null;
            if (array_key_exists('quote', $this->token)) {
                $quote = $this->token['quote'];
                unset($this->token['quote']);
            }

            /**
             * The token is now added or updated. What matters also is that
             * only the keys that were passed are the ones used, so the other
             * columns are not affected with NULL values.
             */
            $token = Token::updateOrCreate(
                ['symbol' => $this->token['symbol']],
                $this->token
            );

            /**
             * If we have a quote, then lets update/insert it into the
             * pairs table. This is useful to then cross-match with the
             * orders and alerts. Also, each token will create as much pairs
             * as the stablecoins that they are connected to, no matter
             * what exchange is being used.
             */
            if ($quote) {
                if (! Pair::where('token_id', $token->id)
                         ->where('quote', $quote)
                         ->first()) {
                    Pair::create(
                        ['token_id' => $token->id,
                            'quote'    => $quote, ]
                    );
                }
            }
        });
    }
}
