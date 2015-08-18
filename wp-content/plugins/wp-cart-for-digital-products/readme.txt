=== WordPress eStore - Sell digital products through Paypal ===
Contributors: Ruhul Amin
Donate link: http://www.tipsandtricks-hq.com/development-center
Tags: Paypal shopping cart, online shop, shopping cart, online checkout system, sell digital products
Requires at least: 3.0
Tested up to: 3.6
Stable tag: 6.9.0

Simple Shopping Cart Plugin to sell digital products (ebook, mp3, photos) from your wordpress blog through PayPal! The product is automatically delivered to the buyer after purchase.

== Description ==

WP eStore allows you to add an 'Add to Cart' button on any post or page of your wordpress blog easily. It allows you to add the shopping cart to any post or page or sidebar easily. The shopping cart shows the user what they currently have in the cart and allows them to remove the items.

For detailed documentation please visit the documentation site:

http://www.tipsandtricks-hq.com/ecommerce/wp-estore-documentation

== Usage ==

1. First add products to the database through the 'Add/Edit Products' interface. Products can be modified through the 'Manage Products' interface.
2. To add the 'Add to Cart' button simply add the shortcode [wp_eStore_add_to_cart id=PRODUCT-ID] to a post or page next to the product. The add to cart button can also be added using the following 'PHP' function call
get_button_code_for_product(PRODUCT-ID);

Replace PRODUCT-ID with the actual product id. Product IDs for all your products can be found in the 'Manage Products' section.

3. To add the shopping cart to a post, checkout page and/or sidebar simply add the shortcode [wp_eStore_cart] to a post or page or use the sidebar widget. 

== Additional Usage Guide ==
Please refer to the documentation site for additional advanced usage options

== Installation ==
1. Unzip and Upload the folder 'wp-cart-for-digital-products' to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings menu under 'WP eStore' and configure the options eg. your email, Shopping Cart name, Return URL etc.
4. Go to the Add/Edit Products page under the 'WP eStore' menu and add your product details to the database.
5. Use the shortcode to add a product to a post or page where you want it to appear.

== Frequently Asked Questions ==
1. Can this plugin be used to accept paypal payment for a service or a product? Yes
2. Does this plugin have shopping cart functionality? Yes.
3. Can the shopping cart be added to any posts or pages or sidebar? Yes.
4. Does this plugin has multiple currency support? Yes.
5. Is the 'Add to Cart' button customizable? Yes.
6. Does this plugin use a return URL to redirect customers to a specified page after Paypal has processed the payment? Yes.
7. Can this plugin be used to automatically email the digital product to the buyer after purchase? Yes.
8. How does this plugin deliver the digital product to the buyer? The plugin will send an email to the buyer which will contain an encrypted download link that is valid for a configarable time.
9. Can this plugin be configured to send the product as an email attachment? Yes.

== Screenshots ==
Visit the plugin site at http://www.tipsandtricks-hq.com/?p=1059 for screenshots more info.

