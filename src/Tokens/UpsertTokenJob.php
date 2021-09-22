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
            $quote = null;
            if (array_key_exists('quote', $this->token)) {
                $quote = $this->token['quote'];
                unset($this->token['quote']);
            }

            $token = Token::updateOrCreate(
                ['symbol' => $this->token['symbol']],
                $this->token
            );

            if ($quote) {
                if (!Pair::firstWhere('token_id')) {
                    Pair::create(
                        ['token_id' => $token->id],
                        ['quote'    => $quote]
                    );
                }
            }
        });
    }
}
