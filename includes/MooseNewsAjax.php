<?php

/**
 * Ajax handler
 */
class MooseNewsAjax {

    /**
     * Content
     *
     * @var string
     */
    protected $content;

    /**
     * Errors
     *
     * @var string
     */
    protected $errors;

    /**
     * Current user
     *
     * @var Object
     */
    protected $user;

    /**
     * Create new theme handler
     */
    public function createTheme() {
        global $wpdb;
        if ($this->checkUser() && $this->validateForm()) {
            $wpdb->insert($wpdb->prefix . MooseNews::NEWS_TABLE, array(
                'user_id' => $this->user->id,
                'content' => $this->content,
                'postdate' => date('Y-m-d H:i:s'),
                'rating' => 0,
            ));
            $this->ok();
        } else {
            $this->error();
        }
    }

    /**
     * Update theme handler
     */
    public function updateTheme() {
        global $wpdb;

        if (!$this->checkUser()) {
            $this->error();
        }

        $newsId = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $newsItem = $this->getNewsById($newsId);
        if (!$this->validateForm() || !$newsItem
            || (!current_user_can('moderate_comments') && $newsItem->user_id != $this->user->id)
        ) {
            $this->error();
        }

        $wpdb->update(
            $wpdb->prefix . MooseNews::NEWS_TABLE,
            array('content' => $this->content),
            array('id' => $newsId)
        );
        $this->ok();
    }

    /**
     * Preview theme handler
     */
    public function previewTheme() {
        if ($this->validateForm()) {
            $this->ok();
        } else {
            $this->error();
        }
    }

    /**
     * Delete theme handler
     */
    public function deleteTheme() {
        global $wpdb;

        if (!$this->checkUser()) {
            $this->error();
        }

        $newsId = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $newsItem = $this->getNewsById($newsId);
        if (!$newsItem
            || (!current_user_can('moderate_comments') && $newsItem->user_id != $this->user->id)
        ) {
            $this->error();
        }

        $wpdb->delete($wpdb->prefix . MooseNews::NEWS_TABLE, array('id' => $newsId));
        $wpdb->delete($wpdb->prefix . MooseNews::VOTES_TABLE, array('news_id' => $newsId));
        $this->ok();
    }

    /**
     * Vote handler
     */
    public function vote() {
        global $wpdb;

        if (!$this->checkUser()) {
            $this->error();
        }

        $newsId = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $voteType = isset($_POST['type']) ? strval($_POST['type']) : '';
        if ($newsId < 1 || !in_array($voteType, array('up', 'down'), true)) {
            $this->error();
        }

        if (!current_user_can('delete_users')) {
            $checkVote = $wpdb->get_var("SELECT vote_type FROM " . $wpdb->prefix . MooseNews::VOTES_TABLE
                . " WHERE news_id=$newsId AND user_id=" . $this->user->id . " LIMIT 1");
            if (!empty($checkVote)) {
                $this->error();
            }
        }

        $wpdb->insert($wpdb->prefix . MooseNews::VOTES_TABLE, array(
            'user_id'   => $this->user->id,
            'news_id'   => $newsId,
            'vote_type' => $voteType,
        ));
        if ($voteType == 'up') {
            $sql = "UPDATE " . $wpdb->prefix . MooseNews::NEWS_TABLE
                . " SET rating = rating + 1 WHERE id = $newsId";
        } else {
            $sql = "UPDATE " . $wpdb->prefix . MooseNews::NEWS_TABLE
                . " SET rating = rating - 1 WHERE id = $newsId";
        }
        $wpdb->query($sql);

        $this->content = intval($wpdb->get_var("SELECT rating FROM "
            . $wpdb->prefix . MooseNews::NEWS_TABLE . " WHERE id=$newsId LIMIT 1"));
        $this->ok();
    }

    /**
     * Get news theme from database by id
     */
    protected function getNewsById($id) {
        global $wpdb;

        $id = intval($id);
        if ($id < 1) return false;

        $item = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . MooseNews::NEWS_TABLE . " WHERE id=$id LIMIT 1");
        if ( !is_object($item) || count($item) < 1) return false;

        return $item;
    }

    /**
     * Check if current user is logged in
     *
     * @return bool
     */
    protected function checkUser() {
        $this->user = wp_get_current_user();
        if (!$this->user->exists()) {
            $this->errors = sprintf(__('Please <a href="%s">login</a> for vote or post new theme.', 'moosenews'), '/wp-login.php');
            return false;
        }
        return true;
    }

    /**
     * Get content from request
     *
     * @return string
     */
    protected function getContent() {
        $content = isset($_POST['content']) ? stripslashes($_POST['content']) : '';
        return trim($content);
    }

    /**
     * Validate form
     *
     * @return bool
     */
    protected function validateForm() {
        $this->content = $this->getContent();

        if ($this->content === '') {
            $this->errors = __('Please fill form');
        }

        if (mb_strlen($this->content, 'UTF-8') < intval(get_option('moosenews_message_minlength', 10))) {
            $this->errors = sprintf(__('Please enter longer description'), MooseNews::MESSAGE_DEFAULT_MAXLENGTH);
        }

        if (mb_strlen($this->content, 'UTF-8') > MooseNews::MESSAGE_DEFAULT_MAXLENGTH) {
            $this->errors = sprintf(__('The description can be up to %s characters long'), MooseNews::MESSAGE_DEFAULT_MAXLENGTH);
        }

        return empty($this->errors);
    }

    /**
     * Return success response
     */
    protected function ok() {
        $this->response(array(
            'status' => 'ok',
            'content' => MooseNews::htmlize($this->content),
            'errors' => $this->errors,
        ));
    }

    /**
     * Return error response
     */
    protected function error() {
        $this->response(array(
            'status' => 'error',
            'content' => MooseNews::htmlize($this->content),
            'errors' => $this->errors,
        ));
    }

    /**
     * Return response based on data
     *
     * @param array $data
     */
    protected function response($data=array()) {
        if (!is_array($data)) $data = array();
        if (!isset($data['status'])) {
            $data['status'] = 'error';
            $data['errors'] = __('Something went wrong :(');
        }
        if (!isset($data['errors'])) $data['errors'] = '';
        if (!isset($data['content'])) $data['content'] = '';
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($data);
        wp_die();
    }

} 