<?php
/**
 * List all news
 */

function pageUrl($sort='') {
    if ($sort == '' || $sort == 'postdate') {
        return remove_query_arg('sort');
    } else {
        return add_query_arg('sort', 'rating');
    }
}

?>

<?php if (get_option('moosenews_form_position') == 'up') include('form.php'); ?>

<div class="moosenews-order">
    <?php echo __('Sort by:', 'moosenews'); ?>
    <a href="<?php echo pageUrl(); ?>"<?php echo $sort == 'postdate' ? ' class="current"' : '' ?>><?php echo __('Post date', 'moosenews'); ?></a>
    <a href="<?php echo pageUrl('rating'); ?>"<?php echo $sort == 'rating' ? ' class="current"' : '' ?>><?php echo __('Rating', 'moosenews'); ?></a>
</div>

<?php if (count($news) > 0) : ?>
    <?php foreach($news as $n) : ?>
        <div id="moosenewstheme<?php echo $n->id; ?>" class="moosenews-theme">
            <div class="moosenews-rating">
                <?php $user = wp_get_current_user();
                if ($user->exists()) : ?>
                    <a href="#" class="up<?php if ($n->vote=='up') echo ' voted'; ?><?php if (!current_user_can('delete_users') && $n->vote!='') echo ' disabled'; ?>" data-id="<?php echo $n->id; ?>" data-type="up">▲</a>
                <?php endif; ?>
                <span class="counter<?php if ($n->rating > 0) echo ' good'; elseif ($n->rating < 0) echo ' bad'; ?>"><?php echo $n->rating; ?></span>
                <?php $user = wp_get_current_user();
                if ($user->exists()) : ?>
                    <a href="#" class="down<?php if ($n->vote=='down') echo ' voted'; ?><?php if (!current_user_can('delete_users') && $n->vote!='') echo ' disabled'; ?>" data-id="<?php echo $n->id; ?>" data-type="down">▼</a>
                <?php endif; ?>
            </div>
            <div class="moosenews-meta">
                <?php printf(__('%s suggested a theme:', 'moosenews'), esc_html($n->nickname)); ?>
                <span class="date"><?php echo date("d F Y, H:i", strtotime($n->postdate)); ?></span>
            </div>
            <div class="moosenews-content">
                <div class="content"><?php echo $n->content; ?></div>
                <div class="admin">
                    <?php if ($n->canEdit || current_user_can('moderate_comments')) : ?>
                        <a href="#" class="edittheme" data-id="<?php echo $n->id; ?>">edit</a>
                        <a href="#" class="deletetheme" data-id="<?php echo $n->id; ?>">delete</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="moosenews-form" style="display: none;">
                <div class="error"></div>
                <textarea data-maxlength="<?php echo $maxlength; ?>"><?php echo esc_html($n->content_raw); ?></textarea>
                <div class="chars-counter"><span></span></div>
                <div class="controls">
                    <button type="button" class="close" data-id="<?php echo $n->id; ?>"><?php echo __('Close', 'moosenews'); ?></button>
                    <button type="button" class="update" data-id="<?php echo $n->id; ?>"><?php echo __('Save', 'moosenews'); ?></button>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php else : ?>
    <p class="moosenews-nothemes"><?php echo __('No themes yet'); ?></p>
<?php endif; ?>

<?php if (get_option('moosenews_form_position') == 'down') include('form.php'); ?>

<script>
    var moosenewsvars = {
        "ajaxurl_createtheme": "<?php echo admin_url('admin-ajax.php?action=moosenews_createtheme'); ?>",
        "ajaxurl_updatetheme": "<?php echo admin_url('admin-ajax.php?action=moosenews_updatetheme'); ?>",
        "ajaxurl_previewtheme": "<?php echo admin_url('admin-ajax.php?action=moosenews_previewtheme'); ?>",
        "ajaxurl_deletetheme": "<?php echo admin_url('admin-ajax.php?action=moosenews_deletetheme'); ?>",
        "ajaxurl_vote": "<?php echo admin_url('admin-ajax.php?action=moosenews_vote'); ?>",
        "id_admin_logged": <?php echo current_user_can('delete_users') ? 'true' : 'false'; ?>
    };
</script>
