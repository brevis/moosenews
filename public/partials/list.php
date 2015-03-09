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
                <a href="#" class="up<?php if ($n->vote=='up') echo ' voted'; ?><?php if ($n->vote!='') echo ' disabled'; ?>" data-id="<?php echo $n->id; ?>" data-type="up">▲</a>
                <span class="counter<?php if ($n->rating > 0) echo ' good'; elseif ($n->rating < 0) echo ' bad'; ?>"><?php echo $n->rating; ?></span>
                <a href="#" class="down<?php if ($n->vote=='down') echo ' voted'; ?><?php if ($n->vote!='') echo ' disabled'; ?>" data-id="<?php echo $n->id; ?>" data-type="down">▼</a>
            </div>
            <div class="moosenews-meta">
                <?php printf(__('%s suggested a theme:', 'moosenews'), $n->nickname); ?>
                <span class="date"><?php echo date("d F Y, H:i", strtotime($n->postdate)); ?></span>
            </div>
            <div class="moosenews-content">
                <?php echo $n->content; ?>
            </div>
            <?php if (current_user_can('moderate_comments')) : ?>
                <div class="admin">
                    <a href="#" class="deletetheme" data-id="<?php echo $n->id; ?>">delete</a>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php else : ?>
    <p class="moosenews-nothemes"><?php echo __('No themes yet'); ?></p>
<?php endif; ?>

<?php if (get_option('moosenews_form_position') == 'down') include('form.php'); ?>

<script>
    var moosenewsvars = {
        "ajaxurl_createtheme": "<?php echo admin_url('admin-ajax.php?action=moosenews_createtheme'); ?>",
        "ajaxurl_previewtheme": "<?php echo admin_url('admin-ajax.php?action=moosenews_previewtheme'); ?>",
        "ajaxurl_deletetheme": "<?php echo admin_url('admin-ajax.php?action=moosenews_deletetheme'); ?>",
        "ajaxurl_vote": "<?php echo admin_url('admin-ajax.php?action=moosenews_vote'); ?>"
    };
</script>
