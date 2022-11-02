<?php

use RssImporter\Core\Feed\RSSImporter;

class Plugin
{
    public string $rssImporterDataDirectory;
    public string $rssImporterDataPluginFile;

    public function __construct()
    {
        $this->rssImporterDataPluginFile = __FILE__;
        $this->rssImporterDataDirectory = plugin_dir_path($this->rssImporterDataPluginFile);
    }

    public function init(): void
    {
        $this->initAutoload();
        $this->registerHooks();
        $this->initCLI();
    }

    private function initAutoload(): void
    {
        spl_autoload_register(function (string $class) {
            $path = $this->rssImporterDataDirectory . str_replace(['RssImporter', '\\'], ['App', '/'], $class) . '.php';
            if (file_exists($path)) {
                require_once $path;
            }
        });
    }

    private function registerHooks(): void
    {
        /**
         * Register Core Hooks
         */
        add_action('init', function () {
            //RSSImporter::import();
        });
    }

    private function initCLI(): void
    {
        if (defined('WP_CLI') && WP_CLI) {
            \WP_CLI::add_command('rss', 'RssImporter\Core\CLI\RSS_CLI');
        }
    }
}

