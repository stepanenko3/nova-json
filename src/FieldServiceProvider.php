<?php

namespace Stepanenko3\NovaJson;

use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class FieldServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Nova::serving(function (ServingNova $event) {
            $path = __DIR__.'/../dist/js/field.js';

            // Nova serves scripts from a fixed URL with only a Last-Modified header, so
            // browsers heuristically cache them for weeks. Versioning the name by content
            // changes the URL on every rebuild and forces the new bundle to load.
            Nova::script('nova-json-array-field-'.substr(md5_file($path), 0, 8), $path);
        });
    }
}
