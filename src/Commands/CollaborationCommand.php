<?php

namespace WeblaborMx\Collaboration\Commands;

use Illuminate\Console\Command;

class CollaborationCommand extends Command
{
    public $signature = 'collaboration';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
