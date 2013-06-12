iG:Twitter Cards
================

**License:** [GNU GPL v2](http://www.gnu.org/licenses/gpl-2.0.html)

A plugin to enable Twitter Cards for your WordPress website.


## Description ##

**iG:Twitter Cards** enables [Twitter Cards](https://dev.twitter.com/docs/cards) on your WordPress website to present your website in an extra useful and stylish way when anyone shares your website URLs on Twitter, be it Twitter website or Twitter mobile app.

At present only summary and player cards are supported. Support for other cards will be added in future. You can validate your website's Twitter Cards using official [Twitter Cards Validator](https://dev.twitter.com/docs/cards/validation/validator).

This plugin allows complete customization of Twitter Cards meta tags before output via filters. If you have custom post types and you wish to have Twitter Cards enabled for them too then this plugin allows you to do that using filters to add/remove post types on which Twitter Cards are enabled.

This plugin has been written with WordPress.com VIP compliance in mind, so you can use it as is even if you host your website with WordPress.com VIP.

**Requirements**: This plugin requires PHP 5.3 or better and is supported on WordPress 3.4 or better. It might work on a lower version of PHP or WordPress but no support would be provided for those platforms.

Pull requests are welcome.

WordPress.org plugin page: http://wordpress.org/extend/plugins/ig-twitter-cards/
WordPress.org plugin repo: http://plugins.svn.wordpress.org/ig-twitter-cards/

## Installation ##

###Installing The Plugin###

Extract all files from the zip file and then upload it to `/wp-content/plugins/`. **Make sure to keep the file/folder structure intact.**

Go to WordPress admin section, click on _Plugins_ in the menu bar and then click _Activate_ link under _iG:Twitter Cards_.

**See Also:** ["Installing Plugins" article on the WP Codex](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins)

## Other Notes ##

###Plugin Usage###

Using this plugin is fairly easy. Set the options by going to `Settings > iG:Twitter Cards` in your wp-admin. If you select to customize cards when creating/editing a post or page then a metabox would be available when you create/edit a post or page. There you can tweak the card by selecting card type, adding/changing Twitter username of author, etc.

## Frequently Asked Questions ##

##### I selected Player card for a post/page and entered the player URL but video does not play. What is wrong? #####

Twitter needs your player URL to be served over SSL, so your URL must start with `https://` and not `http://`.

##### I see some code that I can improve. Do you accept pull requests? #####

By all means, feel free to submit a pull request.

##### I want XYZ feature. Can you implement it? #####

Please feel free to suggest a new feature. Its inclusion might be speedier if you can provide the code to make it work.

## ChangeLog ##

##### v1.3 #####

* removed video aspect ratio calculation in card options UI on post screen
* added aspect ratio calculation helper text for video height in card options UI on post screen

##### v1.2 #####

* added data escaping to admin UI elements

##### v1.1 #####

* Updated labels for admin UI elements
* Added `ig_twitter_cards_post_mb_ui` & `ig_twitter_cards_post_mb_data` hooks to extend card options UI on post screen in wp-admin
* Added aspect ratio calculation for video in card options UI on post screen in wp-admin

##### v1.0 #####

* Initial release with support for _Summary_ and _Player_ cards


