<?php

/**
 * Internationalization support
 */
class MooseNewsI18n {

    /**
     * The domain specified for this plugin.
     *
     * @var string
     */
    private $domain;

    /**
     * Load the plugin text domain for translation.
     */
    public function loadPluginTextdomain() {
        load_plugin_textdomain(
            $this->domain,
            false,
            dirname( plugin_basename( __FILE__ ) ) . '/../languages'
        );
    }

    /**
     * Set the domain equal to that of the specified domain.
     *
     * @param string $domain
     */
    public function setDomain($domain) {
        $this->domain = $domain;
    }

} 