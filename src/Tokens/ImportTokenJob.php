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

class ImportTokenJob implements ShouldQueue
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
            $token = Token::updateOrCreate(
                ['symbol' => $this->token['symbol']],
                ['name' => $this->token['name'] ?? null,
                    'image_url' => $this->token['image_url'] ?? null, ]
            );

            if (array_key_exists('quote', $this->token)) {
                Pair::updateOrCreate(
                    ['token_id' => $token->id],
                    ['quote' => $this->token['quote']]
                );
            }
        });
    }
}
