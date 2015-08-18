<?php
// Include WordPress
require('../../../wp-load.php');

$args = array(
        'echo' => true,
        'redirect' => site_url( $_SERVER['REQUEST_URI'] ), 
        'form_id' => 'loginform',
        'label_username' => __( 'Username', 'gxg_textdomain' ),
        'label_password' => __( 'Password', 'gxg_textdomain' ),
        'label_remember' => __( 'Remember Me', 'gxg_textdomain' ),
        'label_log_in' => __( 'Log In', 'gxg_textdomain' ),
        'id_username' => 'user_login',
        'id_password' => 'user_pass',
        'id_remember' => 'rememberme',
        'id_submit' => 'wp-submit',
        'remember' => true,
        'value_username' => NULL,
        'value_remember' => false );
?>

<div id="mylogin">
        
        <div id="login-logo">
                <a href="<?php echo home_url(); ?>" > <img src="<?php echo of_get_option('gg_login_image'); ?>" /> </a>
        </div> <!-- #login-logo-->
        
        <?php wp_login_form( $args );?>      
        
        <ul id="login-bottom">
                <?php wp_register(); ?> | 
        
        <li><a href="<?php echo wp_lostpassword_url( get_permalink() ); ?>" title="Lost Password">Lost Password</a></li>

</div>