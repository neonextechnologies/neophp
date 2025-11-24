<?php

namespace App\Controllers;

use NeoPhp\Http\Request;
use NeoPhp\Http\Response;

abstract class Controller
{
    protected function json($data, int $status = 200): Response
    {
        return response()->json($data, $status);
    }

    protected function response($content = '', int $status = 200): Response
    {
        return new Response($content, $status);
    }

    protected function redirect(string $url, int $status = 302): Response
    {
        return response()->redirect($url, $status);
    }
}
