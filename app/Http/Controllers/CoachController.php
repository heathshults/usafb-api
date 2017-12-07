<?php

namespace App\Http\Controllers;

use App\Models\Coach;

class CoachController extends Controller
{
    public function index()
    {
        $players = Coach::paginate(10);

        return $players;
    }
}
