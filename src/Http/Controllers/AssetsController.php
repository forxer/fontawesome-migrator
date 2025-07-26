<?php

namespace FontAwesome\Migrator\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class AssetsController extends Controller
{
    /**
     * Servir les fichiers CSS du package
     */
    public function css(Request $request, string $filename): Response
    {
        $allowedFiles = [
            'migration-reports.css',
        ];

        if (! \in_array($filename, $allowedFiles)) {
            abort(404);
        }

        $path = __DIR__.'/../../../resources/css/'.$filename;

        if (! file_exists($path)) {
            abort(404);
        }

        $content = file_get_contents($path);

        return response($content, 200, [
            'Content-Type' => 'text/css',
            'Cache-Control' => 'public, max-age=31536000', // Cache 1 an
            'Expires' => gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000),
        ]);
    }

    /**
     * Servir les fichiers JavaScript du package
     */
    public function js(Request $request, string $filename): Response
    {
        $allowedFiles = [
            'migration-reports.js',
        ];

        if (! \in_array($filename, $allowedFiles)) {
            abort(404);
        }

        $path = __DIR__.'/../../../resources/js/'.$filename;

        if (! file_exists($path)) {
            abort(404);
        }

        $content = file_get_contents($path);

        return response($content, 200, [
            'Content-Type' => 'application/javascript',
            'Cache-Control' => 'public, max-age=31536000', // Cache 1 an
            'Expires' => gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000),
        ]);
    }
}
