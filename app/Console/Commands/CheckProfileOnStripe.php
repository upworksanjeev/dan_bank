<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckProfileOnStripe extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:profile';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check profile on Stripe whether the profile is completed on stripe or not.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // \Log::info("Cron is working fine!");
        return app(\App\Http\Controllers\UserController::class)->check_user_profile_status();
    }
}
