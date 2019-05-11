<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
//        DB::listen(function($query) {
//            $tmp = str_replace('?', '"'.'%s'.'"', $query->sql);
//            $tmp = vsprintf($tmp, $query->bindings);
//            $tmp = str_replace("\\","",$tmp);
//            Log::info($tmp."\n\n\t");
//        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
