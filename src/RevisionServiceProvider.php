<?php

namespace Stevebauman\Revision;

use Illuminate\Support\ServiceProvider;

/**
 * Class RevisionServiceProvider
 *
 * @package Stevebauman\Revision
 * @version 1.3.0
 * @author Stevebauman
 * @author Pauljbergmann
 */
class RevisionServiceProvider extends ServiceProvider
{
    /**
     * Revision package version.
     *
     * @var string
     */
    const VERSION = '1.3.0';

    /**
     * Boot the revision service provider.
     *
     * Assigns the migrations to be publishable.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/Migrations' => database_path('/migrations'),
        ], 'revision');
    }

    /**
     * Register the revision service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
