<?php

use App\Models\Enums\Role;
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

// general
$app->get('healthz', 'StatusController@health');
$app->get('statusz', 'StatusController@status');

// users
// forgot-password, login
$app->get('/users', 'UsersController@index'); 
$app->get('/users/{id}', 'UsersController@show');
$app->put('/users/{id}', 'UsersController@update');
$app->put('/users/{id}/activate', 'UsersController@activate');
$app->put('/users/{id}/deactivate', 'UsersController@deactivate');
$app->post('/users', 'UsersController@create');
$app->delete('/users/{id}', 'UsersController@destroy');

// players
$app->get('/players/search', 'PlayersController@search');
$app->get('/players/{id}', 'PlayersController@show');
$app->put('/players/{id}', 'PlayersController@update');
$app->delete('/players/{id}', 'PlayersController@destroy');

// player registrations
$app->get('/players/{player_id}/registrations', 'PlayerRegistrationsController@index');
$app->get('/players/{player_id}/registrations/{id}', 'PlayerRegistrationsController@show');

// coaches
$app->get('/coaches/search', 'CoachesController@search');
$app->get('/coaches/{id}', 'CoachesController@show');
$app->put('/coaches/{id}', 'CoachesController@update');
$app->delete('/coaches/{id}', 'CoachesController@destroy');
    
// coach registrations
$app->get('/coaches/{coach_id}/registrations/{id}', 'CoachRegistrationsController@show');
$app->get('/coaches/{coach_id}/registrations', 'CoachRegistrationsController@index');

/*
$app->group(
    ['middleware' => 'authenticate'],
    function () use ($app) {
        $app->group(
            ['middleware' =>
                [
                    'authorize:'.Role::SUPER_USER
                ]
            ],
            function () use ($app) {
                $app->get(
                    '/users/{id}',
                    [
                        'uses' => 'UsersController@getById'
                    ]
                );
                $app->get(
                    '/users/{id}/logs',
                    [
                        'uses' => 'UsersController@getLogs'
                    ]
                );
                $app->get(
                    '/users',
                    [
                        'uses' => 'UsersController@getAll'
                    ]
                );
                $app->post(
                    '/users',
                    [
                        'uses' => 'UsersController@create'
                    ]
                );
                $app->put(
                    '/users/{id}',
                    [
                        'uses' => 'UsersController@update'
                    ]
                );
                $app->post(
                    '/reset-password',
                    [
                        'uses' => 'AuthenticationController@resetPassword'
                    ]
                );
            }
        );
        $app->group(
            ['middleware' =>
                [
                    'authorize:'.Role::SUPER_USER.','.Role::ADMIN_USER
                ]
            ],
            function () use ($app) {
                $app->get(
                    '/registrants/export',
                    [
                        'uses' => 'DownloadController@downloadFile'
                    ]
                );
                $app->post(
                    '/registrants/import',
                    [
                        'uses' => 'UploadController@processFileUpload'
                    ]
                );
            }
        );
        $app->get(
            '/me',
            [
                'uses' => 'AuthenticationController@getAuthenticatedUser'
            ]
        );
        $app->group(
            ['middleware' =>
                [
                    'authorize:'.Role::SUPER_USER.','.Role::TEST
                ]
            ],
            function () use ($app) {
                $app->delete(
                    '/users/{id}',
                    [
                        'uses' => 'UsersController@delete'
                    ]
                );
            }
        );
    }
);
*/

$app->get(
    '/',
    function () use ($app) {
        return $app->version();
    }
);
