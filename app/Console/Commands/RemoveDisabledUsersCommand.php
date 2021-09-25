<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RemoveDisabledUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remove:disabled-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command remove all users which disabled more than 24 hours ago';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        User::where('disabled_at', '<=', now()->subDay()->toDateTimeString())->delete();
    }
}
