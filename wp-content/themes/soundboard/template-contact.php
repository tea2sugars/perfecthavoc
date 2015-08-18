<?php
/*
Template Name: Contact
*/
?>
<?php

$nameError = '';
$emailError = '';
$commentError = '';

if(isset($_POST['submitted'])) {
                if(trim($_POST['contactName']) === '') {
                        $nameError = true;
                        $hasError = true;
                } else {
                        $name = trim($_POST['contactName']);
                }

                if(trim($_POST['email']) === '')  {
                        $emailError = true;
                        $hasError = true;
                } else if (!eregi("^[A-Z0-9._%-]+@[A-Z0-9._%-]+\.[A-Z]{2,4}$", trim($_POST['email']))) {
                        $emailError = 'You entered an invalid email address.';
                        $hasError = true;
                } else {
                        $email = trim($_POST['email']);
                }

                if(trim($_POST['comments']) === '') {
                        $commentError = true;
                        $hasError = true;
                } else {
                        if(function_exists('stripslashes')) {
                                $comments = stripslashes(trim($_POST['comments']));
                        } else {
                                $comments = trim($_POST['comments']);
                        }
                }

                if(!isset($hasError)) {
                        $emailTo = of_get_option('email_adress');
                        if (!isset($emailTo) || ($emailTo == '') ){
                                $emailTo = of_get_option('gg_email_adress');
                        }
                        $subject = of_get_option('gg_email_subject');
                        $body = "Name: $name \n\nEmail: $email \n\nComments: $comments";
                        $headers = 'From: '.$name.' <'.$emailTo.'>' . "\r\n" . 'Reply-To: ' . $email;

                        mail($emailTo, $subject, $body, $headers);
                        $emailSent = true;
                }

} ?>

<?php get_header(); ?>

                 <div id="content" class="grid_12">

                                 <?php if (have_posts()) : while (have_posts()) : the_post(); ?>                                    

                                      <h1 class="pagetitle"> <?php the_title(); ?> </h1>
                                      
                                      <div class="contact">

                                                <?php if(isset($emailSent) && $emailSent == true) { ?>
                                                        <div class="thanks">
                                                                <p><?php _e('Thanks, your email was sent successfully.', 'gxg_textdomain') ?></p>
                                                        </div>
                                                <?php } else { ?>
                                                        <?php the_content(); ?>
                                                        <?php if(isset($hasError) || isset($captchaError)) { ?>
                                                                <p class="error"><?php _e('Uh oh... an error occured.', 'gxg_textdomain') ?><p>
                                                        <?php } ?>

                                             <div class="guy-con blue-guy-con"><img class="colorguy blueguy" src="http://www.perfecthavoc.com/wp-content/uploads/2014/08/blue2.jpg" alt="" />
<div class="abso-flute">
 <form action="<?php the_permalink(); ?>" id="contactform" method="post">
                                                <fieldset>
                                                        <ul>
                                                                <li>
                                                                        <input type="text" name="contactName" id="contactName" value="<?php if(isset($_POST['contactName'])) echo $_POST['contactName'];?>" size="30"  class="required requiredField" />
                                                                        <label for="contactName"><?php _e('Name', 'gxg_textdomain') ?></label>
                                                                        <?php if($nameError != '') { ?>
                                                                                <div class="error"><?php _e('&larr; Please enter your name.', 'gxg_textdomain') ?></div>
                                                                        <?php } ?>
                                                                </li>
        
                                                                <li>
                                                                        <input type="text" name="email" id="email"  value="<?php if(isset($_POST['email']))  echo $_POST['email'];?>" size="30" class="required requiredField email" />
                                                                        <label for="email"><?php _e('Email', 'gxg_textdomain') ?></label>
                                                                        <?php if($emailError != '') { ?>
                                                                                <div class="error"><?php _e('&larr; Please enter a valid email address.', 'gxg_textdomain') ?></div>
                                                                        <?php } ?>
                                                                </li>
        
                                                                <li>
                                                                        <textarea name="comments" id="commentsText" rows="12" cols="70" class="required requiredField"><?php if(isset($_POST['comments'])) { if(function_exists('stripslashes')) { echo stripslashes($_POST['comments']); } else { echo $_POST['comments']; } } ?></textarea>
                                                                        <label for="commentsText"><?php _e('Message', 'gxg_textdomain') ?></label>
                                                                        <?php if($commentError != '') { ?>
                                                                                <div class="error"><?php _e('&larr; Please enter a message.', 'gxg_textdomain') ?></div>
                                                                        <?php } ?>
                                                                </li>
        
                                                                <li>
                                                                        <input id="submitmail" class="button1" type="submit" value="<?php _e('Send', 'gxg_textdomain') ?>" />
                                                                </li>
                                                        </ul>
                                                        <input type="hidden" name="submitted" id="submitted" value="true" />
                                                 </fieldset>
                                                 </form>
                                                 <?php } ?>

</div>
</div>  
                                         </div><!-- .contact -->

                                <?php endwhile; endif; ?>

                 </div><!-- #content-->

                 <div id="sidebar" class="grid_3">                                
                                
                                <?php  if ( is_active_sidebar( 'contact_sidebar' ) ) :  ?>
                                <div id="contact_sidebar" class="widget-area">
                                     <?php dynamic_sidebar( 'contact_sidebar' ); ?>
                                </div><!-- #contact_sidebar .widget-area -->
                                <?php endif; ?>                         
                         
                 </div><!-- #sidebar-->

                 <div class="clear">
                 </div><!-- .clear-->

<?php get_footer(); ?>