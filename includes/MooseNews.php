<?php

class MooseNews {

    const NEWS_TABLE = 'moosenews_news';
    const VOTES_TABLE = 'moosenews_votes';
    const MESSAGE_DEFAULT_MAXLENGTH = 5000;

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @var MooseNewsLoader
     */
    protected $loader;

    /**
     * Template engine
     *
     * @var MooseNewsTemplate
     */
    protected $template;

    /**
     * The unique identifier of this plugin.
     *
     * @var string
     */
    protected $pluginName;

    /**
     * The current version of the plugin.
     *
     * @var string
     */
    protected $version;

    protected static $htmlPurifer = null;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the Dashboard and
     * the public-facing side of the site.
     */
    public function __construct() {
        $this->pluginName = 'moosenews';
        $this->version = '1.0.0';
        $this->loadDependencies();
        $this->loadTranslations();
        $this->definePublicHooks();
        $this->defineShortcodes();
    }

    /**
     * Make content sexy
     *
     * @param string $content
     * @return string
     */
    public static function htmlize($content) {
        if (self::$htmlPurifer == null) {
            $config = HTMLPurifier_Config::createDefault();
            $config->set('HTML.Allowed', 'a,b,strong,i,em,u,s');
            $config->set('HTML.AllowedAttributes', 'a.href,a.target');
            $config->set('Core.EscapeInvalidTags', true);
            $config->set('AutoFormat.Linkify', true);
            $config->set('HTML.TargetBlank', true);
            $config->set('HTML.Nofollow', true);
            $config->set('AutoFormat.RemoveEmpty', 'true');
            self::$htmlPurifer = new HTMLPurifier($config);
        }

        try{
            $content = self::$htmlPurifer->purify($content);
            $content = nl2br($content);
            $content = preg_replace("/(<br\s*\/?>\s*){3,}/", "<br><br>", $content);
            return $content;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - MooseNewsLoader. Orchestrates the hooks of the plugin.
     * - MooseNewsI18n. Defines internationalization functionality.
     * - MooseNewsPublic. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     */
    protected function loadDependencies() {
        require_once plugin_dir_path(dirname( __FILE__ )) . 'includes/MooseNewsI18n.php';
        require_once plugin_dir_path(dirname( __FILE__ )) . 'includes/MooseNewsAjax.php';
        require_once plugin_dir_path(dirname( __FILE__ )) . 'public/MooseNewsPublic.php';

        require_once plugin_dir_path(dirname( __FILE__ )) . 'includes/MooseNewsLoader.php';
        $this->loader = new MooseNewsLoader();

        require_once plugin_dir_path(dirname( __FILE__ )) . 'includes/MooseNewsTemplate.php';
        $this->template = new MooseNewsTemplate();

        // third-party libs
        require_once plugin_dir_path(dirname( __FILE__ )) . 'libs/htmlpurifier-4.6.0/HTMLPurifier.auto.php';
    }

    /**
     * Load string translations.
     *
     * Uses the MooseNewsI18n class in order to set the domain and to register the hook
     * with WordPress.
     */
    protected function loadTranslations() {
        $i18n = new MooseNewsI18n();
        $i18n->setDomain($this->getPluginName());
        $this->loader->addAction('plugins_loaded', $i18n, 'loadPluginTextdomain');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     */
    protected function definePublicHooks() {
        $public = new MooseNewsPublic($this->getPluginName(), $this->getVersion());
        $this->loader->addAction('wp_enqueue_scripts', $public, 'enqueueStyles');
        $this->loader->addAction('wp_enqueue_scripts', $public, 'enqueueScripts');

        // ajax handler of form submit
        $ajaxHandler = new MooseNewsAjax();
        $this->loader->addAction('wp_ajax_moosenews_createtheme', $ajaxHandler, 'createTheme');
        $this->loader->addAction('wp_ajax_moosenews_updatetheme', $ajaxHandler, 'updateTheme');
        $this->loader->addAction('wp_ajax_moosenews_previewtheme', $ajaxHandler, 'previewTheme');
        $this->loader->addAction('wp_ajax_moosenews_deletetheme', $ajaxHandler, 'deleteTheme');
        $this->loader->addAction('wp_ajax_moosenews_vote', $ajaxHandler, 'vote');
    }

    /**
     * Register shortcodes
     */
    protected function defineShortcodes() {
        add_shortcode('moosenews', array($this, 'displayContent'));
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @return string
     */
    public function getPluginName() {
        return $this->pluginName;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @return string
     */
    public function getVersion() {
        return $this->version;
    }

    /**
     * Display plugin content
     */
    public function displayContent() {
        $sort = isset($_GET['sort']) ? $_GET['sort'] : '';
        if ($sort != 'rating') $sort = 'postdate';

        $this->template->display('list', array(
            'news' => $this->getNewsFromDb($sort),
            'maxlength' => $this->getMaxlength(),
            'sort' => $sort,
        ));
    }

    /**
     * Get news from database
     *
     * @param string $sort
     * @return array
     */
    protected function getNewsFromDb($sort) {
        global $wpdb;

        $votesUserSql = 'news.user_id = votes.user_id';
        $user = wp_get_current_user();
        if ($user->exists()) {
            $votesUserSql = 'votes.user_id = ' . $user->id;
        }

        $news = $wpdb->get_results("
               SELECT news.id as id, news.content as content_raw, news.postdate as postdate,
                      news.rating as rating, news.user_id as user_id, users.display_name as nickname,
                      votes.vote_type as vote
                 FROM ".$wpdb->prefix.self::NEWS_TABLE." news
            LEFT JOIN ".$wpdb->prefix."users users ON (users.id = news.user_id)
            LEFT JOIN ".$wpdb->prefix.self::VOTES_TABLE." votes ON ($votesUserSql AND news.id = votes.news_id)
           ORDER BY $sort DESC");

        foreach($news as $k=>$v) {
            $news[$k]->content = self::htmlize($v->content_raw);
            $news[$k]->canEdit = is_object($user) && intval($v->user_id) == intval($user->id);
        }

        return $news;
    }

    /**
     * Get maxlength for content message
     *
     * @return int
     */
    protected function getMaxlength() {
        $maxlength = intval(get_option('moosenews_message_maxlength'));
        if ($maxlength < 1 ) $maxlength = self::MESSAGE_DEFAULT_MAXLENGTH;
        return $maxlength;
    }

} 