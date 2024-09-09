<?php

namespace WeblaborMx\Collaboration;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use WeblaborMx\Collaboration\Commands\CollaborationCommand;

class CollaborationServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('collaboration')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_collaboration_table')
            ->hasCommand(CollaborationCommand::class);
    }
}
