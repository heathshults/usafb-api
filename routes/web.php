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

// authentication/session
$app->post('/auth/login', 'AuthenticationController@login');
$app->post('/auth/activate-user', 'AuthenticationController@activateUser');
$app->post('/auth/refresh-token', 'AuthenticationController@refreshToken');
$app->post('/auth/forgot-password', 'AuthenticationController@forgotPassword');
$app->post('/auth/forgot-password-confirm', 'AuthenticationController@confirmForgotPassword');

// public/status
$app->get('/healthz', 'StatusController@health');
$app->get('/statusz', 'StatusController@status');

$app->group(
    ['middleware' => 'authenticate'],
    function () use ($app) {
        
        // manage users permission
        $app->group(
            ['middleware' => 'authorize:manage_users'],
            function() use ($app){
                // users
                $app->get('/users', 'UsersController@index');        
                $app->get('/users/{id}', 'UsersController@show');
                $app->put('/users/{id}', 'UsersController@update');
                $app->put('/users/{id}/activate', 'UsersController@activate');
                $app->put('/users/{id}/deactivate', 'UsersController@deactivate');
                $app->post('/users', 'UsersController@create');
                $app->delete('/users/{id}', 'UsersController@destroy');

                // roles
                $app->get('/roles', 'RolesController@index');
                $app->post('/roles', 'RolesController@create');
                $app->get('/roles/permissions', 'RolesController@permissions');
                $app->get('/roles/{id}', 'RolesController@show');
                $app->put('/roles/{id}', 'RolesController@update');
                $app->delete('/roles/{id}', 'RolesController@destroy');
            }
        );

        // view coaches and coach registrations permission
        $app->group(
            ['middleware' => 'authorize:view_coaches'],
            function() use ($app){
                // coaches
                $app->get('/coaches/search', 'CoachesController@search');
                $app->get('/coaches/{id}', 'CoachesController@show');
                
                // coach registrations
                $app->get('/coaches/{coach_id}/registrations', 'CoachRegistrationsController@index');
                $app->get('/coaches/{coach_id}/registrations/{id}', 'CoachRegistrationsController@show');
            }
        );

        // view players and player registrations permission
        $app->group(
            ['middleware' => 'authorize:view_players'],
            function() use ($app){
                // players
                $app->get('/players/search', [ 'middleware' => 'authorize', 'uses' => 'PlayersController@search' ]);
                $app->get('/players/{id}', 'PlayersController@show');
                
                // player registrations
                $app->get('/players/{player_id}/registrations', 'PlayerRegistrationsController@index');
                $app->get('/players/{player_id}/registrations/{id}', 'PlayerRegistrationsController@show');                
            }
        );
            
        // coaches / coach registrations modification endpoints (will be protected by third-party tokens)
        $app->post('/coaches', 'CoachesController@create');
        $app->put('/coaches/{id}', 'CoachesController@update');
        $app->delete('/coaches/{id}', 'CoachesController@destroy');
        $app->post('/coaches/{coach_id}/registrations', 'CoachRegistrationsController@create');
        $app->put('/coaches/{coach_id}/registrations/{id}', 'CoachRegistrationsController@update');                   
        
        // players / player registration modification endpoints (will be protected by third-party tokens)
        $app->put('/players/{id}', 'PlayersController@update');
        $app->delete('/players/{id}', 'PlayersController@destroy');
        $app->post('/players', 'PlayersController@create');
        $app->post('/players/{player_id}/registrations', 'PlayerRegistrationsController@create');
        $app->put('/players/{player_id}/registrations/{id}', 'PlayerRegistrationsController@update');        
    }
);

// root route
$app->get(
    '/',
    function () use ($app) {
        return $app->version();
    }
);