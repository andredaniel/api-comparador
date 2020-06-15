<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

class InstituicaoController extends Controller
{
    public function all()
    {
        $json = File::get(storage_path("app/public/simulador/instituicoes.json"));
        return response(json_decode($json));
    }
}
