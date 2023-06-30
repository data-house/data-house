<?php

namespace App\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use League\Flysystem\WebDAV\WebDAVAdapter;
use Sabre\DAV\Client;

class ImportFilesystemProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Storage::extend('webdav', function (Application $app, array $config) {
            $client = new Client([
                'baseUri' => $config['url'],
                'userName' => $config['user'],
                'password' => $config['password'],
            ]);

            $adapter = new WebDAVAdapter($client);
 
            return new FilesystemAdapter(
                new Filesystem($adapter, $config),
                $adapter,
                $config
            );
        });
    }
}
