<?php
add_action( 'admin_init', 'tweets_options_init1' );
add_action( 'admin_menu', 'tweets_options_add_page1' );
function tweets_options_init1(){
	register_setting( 'tweet_options', 'tweet_plugin_options', 'tweet_options_validate' );
}
function tweets_options_add_page1() {
add_menu_page( __( 'Twitter Widget', 'minimalisttwitterwidget' ), __( 'Twitter Options', 'minimalisttwitterwidget' ), 'edit_theme_options', 'tweet_options', 'tweet_options_do_page1', plugins_url('img/logo.png',__FILE__ ) );}
function tweet_options_do_page1() {
	global $select_options, $radio_options;
	if ( ! isset( $_REQUEST['settings-updated'] ) )
		$_REQUEST['settings-updated'] = false;
	?>

<div class="wrap">
  <h2>Minimalist Twitter Options</h2>
  <?php if ( false !== $_REQUEST['settings-updated'] ) : ?>
  <div class="updated fade">
    <p><strong>
      <?php _e( 'Options saved', 'minimalisttwitterwidget' ); ?>
      </strong></p>
  </div>
  <?php endif; ?>
  <form method="post" action="options.php">
    <?php settings_fields( 'tweet_options' ); ?>
    <?php $options = get_option( 'tweet_plugin_options' ); ?>
    <h3>Options</h3>
    <p>In order to load Tweets you will need to generate a set of keys provided through Twitter, you can get these by signing into your Twitter account here: <a href="https://apps.twitter.com/">https://apps.twitter.com/</a></p>
    <table class="form-table">
     <tr valign="top">
        <th scope="row"><?php _e( 'Consumer Key', 'minimalisttwitterwidget' ); ?></th>
        <td><input  id="ck" class="regular-text" type="text" name="tweet_plugin_options[ck]" value="<?php esc_attr_e( $options['ck'] ); ?>" /></td>
      </tr>
      <tr valign="top">
        <th scope="row"><?php _e( 'Consumer Secret', 'minimalisttwitterwidget' ); ?></th>
        <td><input id="cs" class="regular-text" type="text" name="tweet_plugin_options[cs]" value="<?php esc_attr_e( $options['cs'] ); ?>" /></td>
      </tr>  
       <tr valign="top">
        <th scope="row"><?php _e( 'Access Token', 'minimalisttwitterwidget' ); ?></th>
        <td><input id="at" class="regular-text" type="text" name="tweet_plugin_options[at]" value="<?php esc_attr_e( $options['at'] ); ?>" /></td>
      </tr>  
       <tr valign="top">
        <th scope="row"><?php _e( 'Access Token Secret', 'minimalisttwitterwidget' ); ?></th>
        <td><input id="ats" class="regular-text" type="text" name="tweet_plugin_options[ats]" value="<?php esc_attr_e( $options['ats'] ); ?>" /></td>
      </tr>  
    </table>
    <h3>Caching Options</h3>
        <p>To avoid reaching API limits on websites with large volumes of traffic this app can cache the Tweets and load them locally. This will also allow the widget to be loaded quicker if you are having issues with slow loading.</p>
    <table class="form-table">
 <tr valign="top">
        <th scope="row"><?php _e( 'Enable Caching', 'minimalisttwitterwidget' ); ?></th>
        <td><input id="caching" name="tweet_plugin_options[caching]" type="checkbox" value="1" <?php checked( '1', $options['caching'] ); ?> /></td>
      </tr>
      <tr valign="top">
        <th scope="row"><?php _e( 'Cache Expiry (Hrs)', 'minimalisttwitterwidget' ); ?></th>
        <td><input  id="cache_exp" class="regular-text" type="text" name="tweet_plugin_options[cache_exp]" value="<?php esc_attr_e( $options['cache_exp'] ); ?>" /></td>
      </tr> 
    </table>
     <h3>Debug Mode</h3>
        <p>If Debug mode is active it will give a read out of technical infomation along with the friendly error message, use this to figure out exactly what is wrong</p>
    <table class="form-table">
 <tr valign="top">
        <th scope="row"><?php _e( 'Enable Debug Mode', 'minimalisttwitterwidget' ); ?></th>
        <td><input id="debug" name="tweet_plugin_options[debug]" type="checkbox" value="1" <?php checked( '1', $options['debug'] ); ?> /></td>
      </tr>
         </table>

        <p class="submit">
      <input type="submit" class="button-primary" value="<?php _e( 'Save Options', 'minimalisttwitterwidget' ); ?>" />
    </p>
  </form>
</div>
<?php
}
function tweet_options_validate( $input ) {
	$input['ck'] = wp_filter_nohtml_kses( $input['ck'] );
	$input['cs'] = wp_filter_nohtml_kses( $input['cs'] );
	$input['at'] = wp_filter_nohtml_kses( $input['at'] );
	$input['ats'] = wp_filter_nohtml_kses( $input['ats'] );
	$input['cache_exp'] = wp_filter_nohtml_kses( $input['cache_exp'] );

	if ( ! isset( $input['caching'] ) )
		$input['caching'] = null;
		$input['caching'] = ( $input['caching'] == 1 ? 1 : 0 );
		if ( ! isset( $input['debug'] ) )
		$input['debug'] = null;
		$input['debug'] = ( $input['debug'] == 1 ? 1 : 0 );
	return $input;
}