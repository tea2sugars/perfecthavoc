=== Minimalist Twitter Widget ===
Contributors: impression11 
Tags: Twitter, Widget, Minimalist, Tweets,
Requires at least: 3.5
Tested up to: 3.9
Stable tag: 1.5

A quick and efficient Twitter widget to display tweets.

== Description ==

Minimalists Twitter Widget displays user tweets using the REST API v1.1. With minimal styling it picks up your theme’s styling to blend in seamlessly.

With efficiency in mind this widget can also cache your Tweets reduce the amount of API calls your website has to make and to load quicker.

= Features =
* Custom Widget Title
* Load User Tweets or Hashtags
* The option to not display Replies and Retweets in the widget
* Smart caching retrieves the newer Tweets only meaning less API calls and (after a little time) been able to show more than 15 Tweets at one time
* Fits in perfectly with (just about) all themes!

Something not working? [Contact Me](http://impression11.co.uk/contact/)

[View our other plugins](http://profiles.wordpress.org/impression11/)

== Installation ==

1. Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your WordPress installation and then activate the Plugin from Plugins page.

2. Go to "Tweet Options" under Appearance and input your Consumer Key, Consumer Secret, Access Token & Access Token Secret. If you haven't got these details, register and application at https://apps.twitter.com/ to attain them.

4. Go to Widgets, drag the "Minimalist Twitter Widget" to the your sidebar and define the widgets Title, your twitter handle and how many tweets it should display. To insert into a post use the shortcode [mintweet username="impression11" count="5" type="user" retweets=“1” replies=“1”], replacing the shortcode options with your desired parameters. Insert the widget as many times as required, though baring in mind the API limits.

5. If you run into API limits use the caching feature to speed up loading and to limit the amount of requests sent to Twitter.

== Changelog ==

= 1.4 =
* Tweets displayed by shortcode should appear where the shortcode is placed, not before any other content.
* Fixed an issue with displaying Retweets within a hashtag search.
* General code improvements.
* Added a "Debug Mode", if enabled it will display the raw data received from Twitter to allow easier diagnosis of issues.

= 1.3 =
* The option to decide if Replies and Retweets are shown.
* Linkified Twitter handles in Replies & Mentions.
* Smarter caching. Tweets are stored between API calls; subsequent calls will just retrieve the new Tweets. This means less API calls, and that over time more than 15 Tweets can be shown at 1 time.

= 1.2 =
* Added the option to not include Re-tweets from user tweets on a per widget/shortcode basis. Use the new option on the widget configuration or add retweets=“0” (to disable Re-tweets) to your shortcode. Due to the way Twitter feeds user Tweets through their API it will reduce the total number of Tweets shown if there are Re-tweets there if they are disabled.

= 1.1 =
* Better handling of cached Tweets, differentiating between different counts and search types.
* Partial re-write to support shortcode input.
* Better handling of hashtag search.
* Converts URL in Tweets to hyperlinks.
* Fixed various notices while debugging.
* Added Plugin Description.


= 1.0 =
* Initial release.