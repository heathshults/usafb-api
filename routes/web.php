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

$app->get('healthz', 'StatusController@health');
$app->get('statusz', 'StatusController@status');

$app->post(
    '/login',
    [
        'uses' => 'AuthenticationController@login'
    ]
);
$app->post(
    '/forgot-password',
    [
        'uses' => 'AuthenticationController@forgotPassword'
    ]
);
$app->group(
    ['middleware' => 'authenticate'],
    function () use ($app) {
        $app->group(
            ['middleware' =>
                [
                    'authorize:'.Role::label(Role::SUPER_USER)
                ]
            ],
            function () use ($app) {
                $app->get(
                    '/users/{id}', [
                        'uses' => 'UsersController@getById'
                    ]
                );
                $app->get(
                    '/users/{id}/logs', [
                        'uses' => 'UsersController@getLogs'
                    ]
                );
                $app->get(
                    '/users', [
                        'uses' => 'UsersController@getAll'
                    ]
                );
                $app->post(
                    '/users', [
                        'uses' => 'UsersController@create'
                    ]
                );
                $app->put(
                    '/users/{id}', [
                        'uses' => 'UsersController@update'
                    ]
                );
                $app->post(
                    '/reset-password', [
                        'uses' => 'AuthenticationController@resetPassword'
                    ]
                );
            }
        );
        $app->group(
            ['middleware' =>
                [
                    'authorize:'.Role::label(Role::SUPER_USER).','.Role::label(Role::ADMIN_USER)
                ]
            ],
            function () use ($app) {
                $app->get('/registrants/export', [
                        'uses' => 'DownloadController@downloadFile'
                    ]
                );
                $app->post('/registrants/import', [
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
                    'authorize:'.Role::label(Role::SUPER_USER).','.Role::label(Role::TEST)
                ]
            ],
            function () use ($app) {
                $app->delete(
                    '/users/{id}', [
                        'uses' => 'UsersController@delete'
                    ]
                );
            }
        );
    }
);

$app->get(
    '/', function () use ($app) {
        return ;
    }
);
