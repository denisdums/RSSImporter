<?php

namespace RssImporter\Core\Feed;


use SimpleXMLElement;

class RSSExtractor
{

    public string $feedUrl;
    public string $feedAuthor;

    private ?string $data;
    private SimpleXMLElement $xml;

    private array $importTable;

    public function __construct()
    {
        $this->importTable = $this->getImportTable();
    }

    private function getImportTable(): array
    {
        global $wpdb;
        $queryString = "SELECT pm.meta_value FROM {$wpdb->postmeta} pm LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE pm.meta_key = %s  AND p.post_status = %s AND p.post_type = %s";
        $query = $wpdb->prepare($queryString, 'blog_url', 'publish', 'post');
        return $wpdb->get_col($query);
    }

    public function setFeed(string $author, string $url): void
    {
        $this->feedUrl = $url;
        $this->feedAuthor = $author;
    }

    public function getFeedAuthor(): string
    {
        return $this->feedAuthor;
    }

    public function getFeedUrl(): string
    {
        return $this->feedUrl;
    }

    public function extract(): void
    {
        $this->processData();
    }

    private function processData(): void
    {
        if (defined('WP_CLI')) {
            \WP_CLI::line('Curling ' . $this->getFeedUrl());
        }

        try {
            $this->xml = new SimpleXMLElement($this->getFeedUrl(), LIBXML_NOCDATA, true);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        if (!$this->xml->channel) return;

        $channel = new Channel($this->getFeedAuthor(), $this->xml->channel);

        foreach ($channel->getXMLChannel()->item as $xmlPost) {
            $post = new Post($xmlPost, $channel->getAuthorID());
            if (!$this->postAlreadyImported($post->getBlogUrl())) {
                $post->save();
                $this->importTable[] = $post->getBlogUrl();
            }
        }
    }

    private function postAlreadyImported(string $url): bool
    {
        return in_array($url, $this->importTable);
    }
}