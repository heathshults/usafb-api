<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->group(
    ['prefix' => 'rest'],
    function () use ($app) {
        $app->group(
            ['prefix' => 'auth'],
            function () use ($app) {
                $app->post(
                    '/login', [
                        'uses' => 'AuthenticationController@login'
                    ]
                );
                $app->get(
                    '/user', [
                        'uses' => 'AuthenticationController@getUser'
                    ]
                );
            }
        );
    }
);

$app->get(
    '/', function () use ($app) {
        return $app->version();
    }
);


