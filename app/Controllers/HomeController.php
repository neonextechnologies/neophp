<?php

namespace App\Controllers;

use NeoPhp\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        return response(view('home', [
            'title' => 'Home - NeoPhp Framework'
        ]));
    }

    public function about(Request $request)
    {
        return $this->json([
            'framework' => 'NeoPhp',
            'version' => '1.0.0',
            'description' => 'A Modern PHP Core Framework',
            'inspired_by' => 'Neonex Core'
        ]);
    }
}
