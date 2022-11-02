<?php
namespace RssImporter\Core\CLI;

use RssImporter\Core\Feed\RSSImporter;

class RSS_CLI
{
    public function import(): void
    {
        RSSImporter::import();
    }
}