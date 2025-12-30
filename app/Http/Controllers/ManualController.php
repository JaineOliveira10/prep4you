<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ManualController extends Controller
{
    public function showAdmin($pagina)
    {
        $path = resource_path("manual/admin/{$pagina}.md");

        abort_if(!File::exists($path), 404);

        $content = File::get($path);

        return view('pages.manual.show-admin', [
            'content' => Str::markdown($content)
        ]);
    }

    public function showClient($pagina)
    {
        $path = resource_path("manual/clientes/{$pagina}.md");

        abort_if(!File::exists($path), 404);

        $content = File::get($path);

        return view('pages.manual.show-client', [
            'content' => Str::markdown($content)
        ]);
    }
}
