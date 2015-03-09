<?php
/**
 * Suggest news form
 */
?>

<?php
$user = wp_get_current_user();
if ($user->exists()) : ?>
    <form action="" method="post" class="moosenews-form">
        <h2><?php echo __('Suggest a theme:', 'moosenews'); ?></h2>
        <div class="moosenews-form-preview"></div>
        <textarea data-maxlength="<?php echo $maxlength; ?>"></textarea>
        <div class="chars-counter"><span></span></div>
        <div class="controls">
            <button type="button" class="preview"><?php echo __('Preview', 'moosenews'); ?></button>
            <button><?php echo __('Submit', 'moosenews'); ?></button>
        </div>
    </form>
<?php else : ?>
    <div class="moosenews-alert"><?php printf(__('Please <a href="%s">login</a> for vote or post new theme.', 'moosenews'), '/wp-login.php'); ?></div>
<?php endif; ?>