<?php

namespace Nidavellir\Jobs;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class NidavellirJobsServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function boot()
    {
        // Only process jobs when all DB transactions are finished.
        config(['queue.connections.redis.after_commit' => true]);
    }

    public function register()
    {
        //
    }
}
