<?php

// Do not delete these lines
        if (!empty($_SERVER['SCRIPT_FILENAME']) && 'comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
        die ('Please do not load this page directly.');

        if ( post_password_required() ) { ?>
        <p class="nocomments"><?php _e('This post is password protected. Enter the password to view comments.', 'gxg_textdomain') ?></p>
        <?php
        return;
        }
?>

<h3 id="comments-number"> <?php comments_number(__('No Comments', 'gxg_textdomain'), __('One Comment', 'gxg_textdomain'), __('% Comments', 'gxg_textdomain')); ?></h3>

<!-- Display the comments -->
<?php if ( have_comments() ) { ?>
<?php $counter = 0; ?>
        <ol class="commentlist">
                <?php wp_list_comments('type=comment&callback=gg_comment'); ?>                
        </ol>
<?php }  ?>

<div class="nav_pagination_bottom">
        <?php paginate_comments_links(); ?>
</div>

<div class="clear"> </div>

<!-- Display the comment form -->

<?php if(comments_open()) : ?>

        <div id="respond"><!-- Do not rename, as you need the div id response for threaded comments! -->

                <h3><?php comment_form_title(); ?></h3>

                <!-- Registration required? -->
                <?php if ( get_option('comment_registration') && !$user_ID ) : ?>
                        <p><?php printf(__('You must be %1$slogged in%2$s to post a comment.', 'gxg_textdomain'), '<a href="'.get_option('siteurl').'/wp-login.php?redirect_to='.urlencode(get_permalink()).'">', '</a>') ?></p>
 
                <?php else : ?>

                <!-- form -->
                <form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform">
                <fieldset>
                        <ul>
                                <?php if ( is_user_logged_in() ) : ?>
                                 
                                <p><?php printf(__('Logged in as %1$s. %2$sLog out &raquo;%3$s', 'gxg_textdomain'), '<a href="'.get_option('siteurl').'/wp-admin/profile.php">'.$user_identity.'</a>', '<a href="'.(function_exists('wp_logout_url') ? wp_logout_url(get_permalink()) : get_option('siteurl').'/wp-login.php?action=logout" title="').'" title="'.__('Log out of this account', 'gxg_textdomain').'">', '</a>') ?></p>
                
                                <?php else : ?>
                
                                        <li>
                                                <input type="text" name="author" class="text_input" id="author" value="<?php echo $comment_author; ?>" size="22" tabindex="1"  />
                                                <label for="author"> <?php _e('Name', 'gxg_textdomain') ?> <?php if ($req) echo ""; ?></label>
                                        </li>
                
                                        <li>
                                                <input type="text" name="email" class="text_input" id="email" value="<?php echo $comment_author_email; ?>" size="22" tabindex="2" />
                                                <label for="email"> <?php _e('Email', 'gxg_textdomain') ?>  <small> <?php _e('(will not be published)', 'gxg_textdomain') ?> </small>  <?php if ($req) echo ""; ?></label>
                                        </li>
                
                                 <?php endif; ?>
                
                                        <li>
                                                <textarea name="comment" id="comment" cols="40" rows="8" class='text_area' tabindex="4"></textarea>
                                        </li>
                                        
                                        <li>
                                                <input name="submit" class="button1" type="submit" id="submit" tabindex="5" value="<?php _e('Submit', 'gxg_textdomain') ?>" />
                                                <?php cancel_comment_reply_link(__("Cancel Reply", "gxg_textdomain")); ?>                                                
                                                <?php comment_id_fields(); ?>
                
                                                <?php do_action('comment_form', $post->ID); ?><!-- hook for plugins-->
                                        </li>                                        
                        </ul>                
                </fieldset>
                </form>

                <?php endif; ?>

        </div> <!-- respond -->

<?php else : ?>

<p> <?php _e('Comments are closed.', 'gxg_textdomain') ?> </p>

<?php endif; ?>