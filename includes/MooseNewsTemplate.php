<?php

/**
 * Very simple template engine
 */
class MooseNewsTemplate {

    /**
     * Display templat
     *
     * @param string $template
     * @param array $vars
     */
    public function display($template, $vars=array()) {
        if (!file_exists(__DIR__ . '/../public/partials/'.$template.'.php')) {
            wp_die('Template ['.$template.'] not found');
        }
        extract($vars);
        include(__DIR__ . '/../public/partials/'.$template.'.php');
    }

} 