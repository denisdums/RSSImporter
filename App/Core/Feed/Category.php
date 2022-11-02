<?php

namespace RssImporter\Core\Feed;

class Category
{
    private string $name;

    public function __construct(string $name)
    {
        $this->setName($name);
    }

    private function setName(string $name): void
    {
        $this->name = strtolower($name);
    }

    public function exist()
    {
        return term_exists($this->name, 'category');
    }


    public function create(): int
    {
        $category = wp_insert_term($this->name, 'category');
        return $category['term_id'];
    }
}