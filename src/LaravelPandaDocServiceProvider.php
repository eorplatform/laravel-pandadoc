<?php

namespace EorPlatform\LaravelPandaDoc;

use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelPandaDocServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-pandadoc')
            ->hasMigration('create_panda_doc_documents_table')
            ->hasConfigFile()
            ->hasRoute('pandadoc-webhooks')
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->startWith(function (InstallCommand $command) {
                        $command->info('Hello, and welcome to Reployer Laravel PandaDoc package!');
                    })
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('eorplatform/laravel-pandadoc')
                    ->endWith(function (InstallCommand $command) {
                        $command->info('Thank you and have a great day!');
                    });
            });
    }


    public function packageRegistered(): void
    {
        $this->app->singleton(PandaDoc::class);
        $this->app->alias(PandaDoc::class, 'pandadoc');
    }
}
