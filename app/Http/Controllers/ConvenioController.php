<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

class ConvenioController extends Controller
{
    public function all()
    {
        $json = File::get(storage_path("app/public/simulador/convenios.json"));
        return response(json_decode($json));
    }
}
