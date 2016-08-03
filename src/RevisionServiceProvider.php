<?php

namespace Stevebauman\Revision;

use Illuminate\Support\ServiceProvider;

class RevisionServiceProvider extends ServiceProvider
{
    /**
     * Boot the revision service provider.
     *
     * Assigns the migrations to be publishable.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/Migrations' => database_path('/migrations'),
        ], 'revision');
    }

    /**
     * Register the revision service provider.
     */
    public function register()
    {
        //
    }
}
