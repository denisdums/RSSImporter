<?php

namespace RssImporter\Core\Feed;

use SimpleXMLElement;

class ImageManager
{
    private SimpleXMLElement $xmlPost;
    private string $foundImageUrl;

    public function __construct($xmlPost)
    {
        $this->xmlPost = $xmlPost;
    }

    public function find(): ?string
    {
        if (isset($this->xmlPost->enclosure)) {
            $this->foundImageUrl = $this->xmlPost->enclosure->attributes()['url']->__toString();
            return $this->foundImageUrl;
        } elseif ($this->findImageInContent()) {
            return $this->foundImageUrl;
        }
        return null;
    }

    public function savePostImage($postID): void
    {
        $image = \media_sideload_image($this->foundImageUrl, $postID, '', 'id');
        set_post_thumbnail($postID, $image);
    }

    private function findImageInContent(): bool
    {
        preg_match('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $this->xmlPost->description->__toString(), $matches);
        if (count($matches) > 0) {
            $this->foundImageUrl = $matches['src'];
            return true;
        }
        return false;
    }
}