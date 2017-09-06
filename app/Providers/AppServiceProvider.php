<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment(['staging', 'production'])) {
            if ($this->app->environment(['staging'])) {
                $infoHandler = new \Monolog\Handler\StreamHandler('php://stdout', \Monolog\Logger::INFO);
                $infoHandler->setFormatter(
                    new \Monolog\Formatter\FluentdFormatter
                );

                $monolog->pushHandler($infoHandler);
            }

            $warningHandler = new \Monolog\Handler\StreamHandler('php://stderr', \Monolog\Logger::WARNING, false);
            $warningHandler->setFormatter(
                new \Monolog\Formatter\FluentdFormatter
            );

            $monolog->pushHandler($warningHandler);
        }
    }
}
