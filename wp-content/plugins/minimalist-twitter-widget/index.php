<?php
/* Plugin Name: Minimalist Twitter Widget
Plugin URI: http://impression11.co.uk/
Version: 1.4
Description: A minimalist Twitter widget to display tweets.
Author: Impression11 Ltd
Author URI: http://impression11.co.uk/
*/
require_once (dirname(__FILE__) . '/options.php');

add_shortcode('mintweet', 'mintweet');

function printtweets($tweetarray, $type, $count, $retweets, $replies)
{
	$countcheck = 0;
	echo '<ul id="tweets">';
	if ($type == 'user') {
		foreach($tweetarray as $tweet) {
			if ($count > $countcheck) {
				if ($retweets == 0 && isset($tweet['Retweet']) || $replies == 0 && isset($tweet['Reply'])) {
				}
				else {
					echo '<li>' . $tweet['Tweet'] . '</li>';
					$countcheck = $countcheck + 1;
				}
			}
		}
	}
	else {
		foreach($tweetarray as $tweet) {
					if ($count > $countcheck) {
			echo '<li><a href="https://twitter.com/#!/' . $tweet['User'] . '" target="_blank"/>' . $tweet['User'] . '</a>: ' . $tweet['Tweet'] . '</li>';					$countcheck = $countcheck + 1;
}
		}
	}

	echo '</ul>';
}

function mintweet($atts)
{
	extract(shortcode_atts(array(
		'username' => 'ethanjim',
		'count' => 5,
		'type' => 'user',
		'retweets' => 1,
		'replies' => 1
	) , $atts));

	// Get the variables all parts need

	$options = get_option('tweet_plugin_options');
	$file = plugin_dir_path(__FILE__) . $username . '_tweets.php';
	if ($username == '' || $count == '' || $options['ck'] == '' || $options['cs'] == '' || $options['at'] == '' || $options['ats'] == '') {
		echo 'Please ensure this plugin is correctly configured under "Tweet Options" & "Widgets"';
	}
	else {
		if ($options['caching'] == 1 && file_exists($file) && time() - filemtime($file) < $options['cache_exp'] * 3600) {
			include (plugin_dir_path(__FILE__) . $username . '_tweets.php');

			printtweets($tweetarray, $type, $count, $retweets, $replies);
			echo '</ul>';
			echo '<!-- Cached File -->';
		}
		else {
			require_once 'lib/twitteroauth.php';

			if (!defined('CONSUMER_KEY')) define('CONSUMER_KEY', $options['ck']);
			if (!defined('CONSUMER_SECRET')) define('CONSUMER_SECRET', $options['cs']);
			if (!defined('ACCESS_TOKEN')) define('ACCESS_TOKEN', $options['at']);
			if (!defined('ACCESS_TOKEN_SECRET')) define('ACCESS_TOKEN_SECRET', $options['ats']);
			$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);
			if (file_exists($file) && $options['caching'] == 1) {
				include (plugin_dir_path(__FILE__) . $username . '_tweets.php');

			}

			if (!isset($tweetarray)) {
				$tweetarray = array();
				$since_id = 0;
				$countlimit = $count;
				$searcharray = array(
					'screen_name' => $username,
					'count' => $count
				);
			}
			else {
				$since_id = max(array_keys($tweetarray));
				$searcharray = array(
					'screen_name' => $username,
					'count' => 15,
					'since_id' => $since_id
				);
			}

			// Check if we're getting user tweets or hashtag tweets

			if ($type == 'user') {

				// get user tweets

				$statuses = $connection->get('statuses/user_timeline', $searcharray);
			}
			else {

				// get hastag tweets

				$statuses = $connection->get('search/tweets', array(
					"q" => '#' . $username,
					'count' => 15
				));

				// bring the array up one level so it's compatible with the loop for getting user tweets

				$statuses = $statuses->statuses;
			}
			if (count($statuses) == 0 && !$options['caching'] == 1 || isset($statuses->error)) {
				echo 'Please check your Twitter Application details, that you have specified the number of tweets to load, if you have ran out of API requests, or if your account is set to private';
			}
			else {
				foreach($statuses as $status) {
					if (isset($status->retweeted_status)) {
						$tweetarray[$status->id_str]['Retweet'] = 1;
						$status->retweeted_status->text = 'RT @'.$status->retweeted_status->user->screen_name.' '.$status->retweeted_status->text;
						$status->retweeted_status->text = preg_replace('|([\w\d]*)\s?(https?://([\d\w\.-]+\.[\w\.]{2,6})[^\s\]\[\<\>]*/?)|i', '$1 <a href="$2" target="_blank">$2</a>', $status->retweeted_status->text);
					$status->retweeted_status->text = preg_replace('/\B\@([a-zA-Z0-9_]{1,20})/', '<a href="https://twitter.com/#!/$1" target="_blank">$0</a>', $status->retweeted_status->text);
					$status->retweeted_status->text = preg_replace('/(^|\s)#(\w*[a-zA-Z_]+\w*)/', '\1#<a href="http://twitter.com/search?q=%23\2">\2</a>', $status->retweeted_status->text);
					$tweetarray[$status->id_str]['Tweet'] = $status->retweeted_status->text;

					}
					else{
					$status->text = preg_replace('|([\w\d]*)\s?(https?://([\d\w\.-]+\.[\w\.]{2,6})[^\s\]\[\<\>]*/?)|i', '$1 <a href="$2" target="_blank">$2</a>', $status->text);
					$status->text = preg_replace('/\B\@([a-zA-Z0-9_]{1,20})/', '<a href="https://twitter.com/#!/$1" target="_blank">$0</a>', $status->text);
					$status->text = preg_replace('/(^|\s)#(\w*[a-zA-Z_]+\w*)/', '\1<a href="http://twitter.com/search?q=%23\2" target="_blank">#\2</a>', $status->text);

					$tweetarray[$status->id_str]['Tweet'] = $status->text;
					}

					if (isset($status->in_reply_to_user_id)) {
						$tweetarray[$status->id_str]['Reply'] = 1;
					}

					krsort($tweetarray);
				}

				printtweets($tweetarray, $type, $count, $retweets, $replies);
				if ($options['caching'] == 1) {
					$var_str = var_export($tweetarray, true);
					$var = "<?php\n\n\$tweetarray = $var_str;\n\n?>";
					file_put_contents(plugin_dir_path(__FILE__) . $username . '_tweets.php', $var);
				}
			}
		}
	}
}

class wp_tweets extends WP_Widget

{
	public

	function __construct()
	{
		parent::__construct('wordpress-tweets', 'Minimalist Twitter Widget', array(
			'description' => 'Displays user Tweets in sidebar.'
		));
	}

	public

	function widget($args, $instance)
	{
		echo $args['before_widget'];
		echo $args['before_title'] . $instance['title'] . $args['after_title'];
		echo do_shortcode('[mintweet type=' . $instance['search'] . ' username=' . $instance['username'] . ' count=' . $instance['count'] . ' retweets="' . $instance['retweet'] . '" replies="' . $instance['replies'] . '"]');
		echo $args['after_widget'];
	}

	public

	function form($instance)
	{

		// removed the for loop, you can create new instances of the widget instead

?>

<p>
  <label for="<?php
		echo $this->get_field_id('title'); ?>">Widget Title</label>
  <br />
  <input type="text" class="img" name="<?php
		echo $this->get_field_name('title'); ?>" id="<?php
		echo $this->get_field_id('title'); ?>" value="<?php
		echo $instance['title']; ?>" />
</p>
<p>
  <label for="<?php
		echo $this->get_field_id('search'); ?>">
    <?php
		_e('User Tweets / Hashtag'); ?>
  </label>
  <select name="<?php
		echo $this->get_field_name('search'); ?>" id="<?php
		echo $this->get_field_id('pager'); ?>" class="widefat">
    <option value="user"<?php
		selected($instance['search'], 'user'); ?>>
    <?php
		_e('User'); ?>
    </option>
    <option value="hash"<?php
		selected($instance['search'], 'hash'); ?>>
    <?php
		_e('Hashtag'); ?>
    </option>
  </select>
</p>
<p>
  <label for="<?php
		echo $this->get_field_id('username'); ?>">Username / Hashtag</label>
  <br />
  <input type="text" class="img" name="<?php
		echo $this->get_field_name('username'); ?>" id="<?php
		echo $this->get_field_id('username'); ?>" value="<?php
		echo $instance['username']; ?>" />
</p>
<label for="<?php
		echo $this->get_field_id('count'); ?>"># of Tweets</label>
<br />
<input type="text" class="img" name="<?php
		echo $this->get_field_name('count'); ?>" id="<?php
		echo $this->get_field_id('count'); ?>" value="<?php
		echo $instance['count']; ?>" />
</p>
<p>
  <label for="<?php
		echo $this->get_field_id('retweet'); ?>">
    <?php
		_e('Display Retweets (User Tweets Only)'); ?>
  </label>
  <select name="<?php
		echo $this->get_field_name('retweet'); ?>" id="<?php
		echo $this->get_field_id('retweet'); ?>" class="widefat">
    <option value="1"<?php
		selected($instance['retweet'], '1'); ?>>
    <?php
		_e('True'); ?>
    </option>
    <option value="0"<?php
		selected($instance['retweet'], '0'); ?>>
    <?php
		_e('False'); ?>
    </option>
  </select>
</p>
<p>
  <label for="<?php
		echo $this->get_field_id('replies'); ?>">
    <?php
		_e('Display Replies (User Tweets Only)'); ?>
  </label>
  <select name="<?php
		echo $this->get_field_name('replies'); ?>" id="<?php
		echo $this->get_field_id('replies'); ?>" class="widefat">
    <option value="1"<?php
		selected($instance['replies'], '1'); ?>>
    <?php
		_e('True'); ?>
    </option>
    <option value="0"<?php
		selected($instance['replies'], '0'); ?>>
    <?php
		_e('False'); ?>
    </option>
  </select>
</p>
<?php
	}
}

add_action('widgets_init', create_function('', 'return register_widget("wp_tweets");'));

function wp_tweets_css()
{
	wp_enqueue_style('minimal-tweet', plugins_url('wp-tweet.css', __FILE__) , null, null);
}

add_action('init', 'wp_tweets_css'); ?>