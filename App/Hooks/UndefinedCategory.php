<?php

namespace RssImporter\Hooks;

use RssImporter\Core\Hookable;

class UndefinedCategory extends Hookable
{
    public function register(): void
    {
        add_action('publish_post', [$this, 'remove_default_category'], 10, 2);
    }

    public function remove_default_category($ID, $post): void
    {
        $categories = wp_get_object_terms($ID, 'category');
        if (count($categories) > 1) {
            foreach ($categories as $key => $category) {
                if ($category->name == "Uncategorized") {
                    wp_remove_object_terms($ID, 'uncategorized', 'category');
                }
            }
        }
    }
}