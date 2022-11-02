<?php

namespace RssImporter\Core\Feed;

use DateTime;
use SimpleXMLElement;

class Post
{
    public SimpleXMLElement $xmlPost;
    public int $authorID;
    private int|\WP_Error $postID;

    public function __construct(SimpleXMLElement $xmlPost, int $authorID)
    {
        $this->xmlPost = $xmlPost;
        $this->authorID = $authorID;
    }

    public function save(): void
    {
        $post = [
            'post_author' => $this->authorID,
            'post_title' => $this->getPostTitle(),
            'post_content' => $this->getPostContent(),
            'post_date' => $this->getPostDate(),
            'post_status' => $this->getPostStatus(),
            'meta_input' => [
                'blog_url' => $this->getBlogUrl(),
            ]
        ];

        $this->postID = wp_insert_post($post);

        if (is_wp_error($this->postID)) {
            echo $this->postID->get_error_message();
            return;
        }
        $this->saveCategories();
        $this->savePostThumbnail();
    }

    private function getPostDate(): string
    {
        $date = $this->xmlPost->pubDate->__toString();
        try {
            $date = new DateTime($date);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        return $date->format('Y-m-d H:i:s');
    }

    private function getPostStatus(): string
    {
        return 'publish';
    }

    public function getBlogUrl(): string
    {
        return $this->xmlPost->link->__toString();
    }

    private function getPostContent(): string
    {
        return $this->xmlPost->description->__toString();
    }

    private function getPostTitle(): string
    {
        return wp_strip_all_tags($this->xmlPost->title->__toString());
    }

    private function savePostThumbnail(): void
    {
        $imageManager = new ImageManager($this->xmlPost);
        if ($imageManager->find()) {
            $imageManager->savePostImage($this->postID);
        }
    }

    private function saveCategories(): void
    {
        if (!$this->xmlPost->category) return;

        $categoryList = [];
        foreach ($this->xmlPost->category as $category) {
            $categoryName = $category->__toString();
            $category = new Category($categoryName);
            $categoryList[] = $category->exist() ? $category->exist() : $category->create();
        }

        foreach ($categoryList as $category) {
            wp_set_post_categories($this->postID, $category, true);
        }

        wp_remove_object_terms($this->postID, $this->getDefaultPostCategorySlug(), 'category');
    }

    private function getDefaultPostCategorySlug(): string
    {
        $defaultCategoryID = get_option('default_category');
        return get_term_by('id', $defaultCategoryID, 'category')->slug;
    }
}