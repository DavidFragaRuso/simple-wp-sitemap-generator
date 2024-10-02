<?php
/*
Plugin Name: Custom Sitemap Generator
Plugin URI: https://dfraga.es/
Description: __( 'Genera un sitemap XML automáticamente, incluyendo publicaciones, páginas, categorías y etiquetas.', 'dfr-custom-sitemap' )
Version: 1.0
Author: David Fraga
Author URI: https://dfraga.es/
Text Domain: dfr-custom-sitemap
Domain Path: /languages
License: GPLv3
*/

add_action('plugins_loaded', 'custom_sitemap_load_textdomain');

function custom_sitemap_load_textdomain() {
    load_plugin_textdomain('dfr-custom-sitemap', false, dirname(plugin_basename(__FILE__)) . '/languages');
}

add_action('init', 'register_sitemap_rewrite_rule');
function register_sitemap_rewrite_rule() {
    // Add a new rewrite rule for /sitemap.xml
    add_rewrite_rule('^sitemap\.xml$', 'index.php?sitemap=1', 'top');
}

add_action('init', 'generate_dynamic_sitemap_plugin');
function generate_dynamic_sitemap_plugin() {
    // Check if the sitemap=1 parameter is in the query
    if (get_query_var('sitemap')) {
        header('Content-Type: application/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';   

        // Include posts and publications
        $posts = get_posts(array('numberposts' => -1, 'post_type' => 'post', 'post_status' => 'publish'));
        foreach ($posts as $post) {
            setup_postdata($post);
            echo '<url>';
            echo '<loc>' . get_permalink($post) . '</loc>';
            echo '<lastmod>' . get_the_modified_time('c', $post) . '</lastmod>';
            echo '<changefreq>weekly</changefreq>';
            echo '<priority>0.5</priority>';
            echo '</url>';
        }
        wp_reset_postdata();

        // Include pages
        $pages = get_posts(array('numberposts' => -1, 'post_type' => 'page', 'post_status' => 'publish'));
        foreach ($pages as $page) {
            setup_postdata($page);
            echo '<url>';
            echo '<loc>' . get_permalink($page) . '</loc>';
            echo '<lastmod>' . get_the_modified_time('c', $page) . '</lastmod>';
            echo '<changefreq>monthly</changefreq>';
            echo '<priority>0.7</priority>';
            echo '</url>';
        }
        wp_reset_postdata();

        // Incluide categories
        $categories = get_categories();
        foreach ($categories as $category) {
            echo '<url>';
            echo '<loc>' . get_category_link($category->term_id) . '</loc>';
            echo '<lastmod>' . date('c') . '</lastmod>';
            echo '<changefreq>weekly</changefreq>';
            echo '<priority>0.3</priority>';
            echo '</url>';
        }

        // Include tags
        $tags = get_tags();
        foreach ($tags as $tag) {
            echo '<url>';
            echo '<loc>' . get_tag_link($tag->term_id) . '</loc>';
            echo '<lastmod>' . date('c') . '</lastmod>';
            echo '<changefreq>weekly</changefreq>';
            echo '<priority>0.3</priority>';
            echo '</url>';
        }

        echo '</urlset>';
        exit;

    }  
}

// Register the 'sitemap' query variable
add_filter('query_vars', 'add_sitemap_query_var');
function add_sitemap_query_var($vars) {
    $vars[] = 'sitemap';
    return $vars;
}