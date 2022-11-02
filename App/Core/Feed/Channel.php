<?php

namespace RssImporter\Core\Feed;

use SimpleXMLElement;

class Channel
{
    public string $authorName;
    public int $authorID;
    public SimpleXMLElement $xmlChannel;

    public function __construct(string $authorName, $xmlChannel)
    {
        $this->authorName = $authorName;
        $this->xmlChannel = $xmlChannel;
        $this->authorID = $this->initAuthor();
    }

    public function getXMLChannel(): SimpleXMLElement
    {
        return $this->xmlChannel;
    }

    private function initAuthor(): int
    {
        if (is_int($this->findAuthorID($this->authorName))) {
            return $this->findAuthorID($this->authorName);
        }

        return $this->createAuthor($this->authorName);
    }

    private function createAuthor(string $authorName): int
    {
        $authorID = wp_create_user($authorName, wp_generate_password(), $authorName . '+' . get_bloginfo('admin_email'));
        wp_update_user(['ID' => $authorID, 'role' => 'author']);
        return $authorID;
    }

    private function findAuthorID(string $authorName): bool|int
    {
        return username_exists($authorName);
    }

    public function getAuthorID(): int
    {
        return $this->authorID;
    }
}