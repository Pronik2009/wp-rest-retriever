<?php

/**
 * Plugin Name: WordPress REST Retriever
 * Plugin URI: https://github.com/zgoniaiko/wp-rest-retriever
 * Description: 
 * Version: 1.0.0
 * Author: Ivan Zgoniaiko
 * Author URI: https://github.com/zgoniaiko/
 * Text Domain: wp-rest-retriever
 */
// Global variables
define('WP_REST_RETRIEVER_VER', '1.0.0');

include( plugin_dir_path(__FILE__) . 'inc/settings-screen.php');

add_action('wp_enqueue_scripts', 'wp_rest_retriever_css');

function wp_rest_retriever_css()
{
    wp_enqueue_style('rest-retriever', plugin_dir_url(__FILE__) . 'inc/css/rest-retriever.css', $deps = false, $ver = wp_rest_retriever_VER);
}

add_shortcode('wp_rest_retriever', 'wp_rest_retriever_func');

function wp_rest_retriever_func($atts, $content = null)
{
    extract(shortcode_atts([
        'url' => '/quotes/random',
    ], $atts));


    $url = get_option('rr_domain', null) . $url;
    $user = get_option('rr_user', null);
    $password = get_option('rr_pass', null);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, $user . ':' . $password);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    //Получаем ответ
    $response = curl_exec($ch);
    curl_close($ch);

    $item = (json_decode($response, true));

    $output = '<div class="wp_rest_retriever">';

    $output .= $item['quote'];
    $output .= ' - ';
    $output .= '<a href="?allquotes=' . $item['author']['id'] . '">' . $item['author']['name'] . '</a>';

    $output .= '</div>';

    return $output;
}

register_activation_hook(__FILE__, 'wp_rest_retriever_activate');

function wp_rest_retriever_activate()
{
    set_transient('_wp_rest_retriever_activation_redirect', true, 30);
}

// add action link under plugins list
function wp_rest_retriever_add_action_links($links)
{
    $mylinks = [
        '<a href="' . admin_url('index.php?page=wp-rest-retriever-settings') . '">Settings</a>',
        '<a href="' . admin_url('index.php?page=wp-rest-retriever-examples') . '">Edit quotes</a>',
        '<a href="' . '/?allquotes' . '">All quotes</a>'
    ];
    return array_merge($links, $mylinks);
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wp_rest_retriever_add_action_links');
