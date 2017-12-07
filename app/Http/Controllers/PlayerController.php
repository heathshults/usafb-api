<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Guardian;
use App\Models\Player;

/*
 * PlayerController
 *
 * RESTful API to manage the Player Model in db
 * Basic CRUD with ability to refine search results
 * There may be some dependencies with Mongo embeded objects
 */

class PlayerController extends Controller
{
    /*
     * Index
     *
     * Used to get a list of players based on the param/value pair.
     * Param values will be used to refine the paginated search results.
     * If no param value pairs are provided this will return all results in db.
     *
     */
    public function index()
    {
        $players = Player::paginate(10);

        return $players;
    }
}
