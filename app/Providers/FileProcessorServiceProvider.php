<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Files\FileProcessorDispatcher;
use App\Services\Files\Processors\JpgFileProcessor;
use App\Services\Files\Processors\XmlFileProcessor;
use App\Services\WordService;

class FileProcessorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FileProcessorDispatcher::class, function ($app) {
            $dispatcher = new FileProcessorDispatcher();
            
            $dispatcher
                ->addProcessor(new JpgFileProcessor())
                ->addProcessor(new XmlFileProcessor(new WordService()));

            return $dispatcher;
        });
    }
}