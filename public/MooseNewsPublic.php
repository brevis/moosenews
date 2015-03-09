<?php

/**
 * The public-facing functionality of the plugin.
 */
class MooseNewsPublic {
    /**
     * The ID of this plugin.
     * @var string
     */
    private $pluginName;

    /**
     * The version of this plugin.
     *
     * @var string
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $pluginName
     * @param string $version
     */
    public function __construct($pluginName, $version) {
        $this->pluginName = $pluginName;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     */
    public function enqueueStyles() {
        wp_enqueue_style(
            $this->pluginName,
            plugin_dir_url(__FILE__) . 'css/moosenews.css',
            array(),
            $this->version,
            'all'
        );
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     */
    public function enqueueScripts() {
        wp_enqueue_script(
            $this->pluginName,
            plugin_dir_url(__FILE__) . 'js/moosenews.js',
            array('jquery'),
            $this->version,
            false
        );
    }
} 