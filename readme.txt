=== WP Integration ===
Contributors: inveo
Tags: ecommerce, e-commerce, commerce, wordpress ecommerce, prestashop, integration, connector, store, sales, sell, shop, shopping, cart, checkout,
Requires at least: 2.7
Tested up to: 4.3
Stable tag: 1.4.11
License: LGPLv2.1
License URI: http://www.gnu.org/licenses/lgpl-2.1.html

== Description ==

This plugin will **fully integrate** your WordPress **for free** with no compromises into any web application supported by the Theme Provider module.

Currently supported web apps: **PrestaShop 1.3/1.4/1.5/1.6** (excellent eCommerce store)

You need to install a free or paid **Theme Provider** module on your ecommerce website to use this plugin: [http://www.inveostore.com/wp-theme-providers](http://www.inveostore.com/wp-theme-providers "PrestaShop Theme Provider")

Features:

* Integrates WordPress to PrestaShop with a simple click
* Automatically adjusts WordPress CSS selectors (restricts WordPress styles only to the WordPress)
* 100% compatibility with other WordPress plugins
* Editing of the WordPress or PrestaShop template files not required
* Produces a valid (X)HTML source code

Support thread (PrestaShop): https://www.prestashop.com/forums/topic/432988-free-module-wordpress-blog-integration-to-prestashop-13141516/?view=getlastpost

== Installation ==

1. Install the plugin as you always install them, either by uploading it via FTP or by using the "Add Plugin" function of the WordPress.
2. Activate the plugin at the plugin administration page.
3. Open the plugin configuration page, which is located under Settings -> WP Integration and setup the API key.

== Frequently Asked Questions ==

= What do I have to install to the PrestaShop? =

You have to download the [PrestaShop Theme Provider](http://www.inveostore.com/wp-theme-providers "PrestaShop Theme Provider module") module created by the same author of this plugin. This module is available in both, free and paid versions.

= Where do I have to install WordPress in order to integrate it into the host app (PrestaShop)? =

In a sub directory of the host web application (the application you want to integrate your WordPress with) such as **/blog/** or **/news/**.

== Screenshots ==

1. The configuration screen of the plugin

== Changelog ==

= 1.4.11 =
* WebAppsDetector class was updated
* Theme Provider module was updated to 1.4.11
* WP 4.3 "Tested up to" tag

= 1.4.10 =
* Warnings in certain situations were fixed
* WebAppsDetector class was updated
* Theme Provider module was updated to 1.4.10

= 1.4.09 =
* The requirement to enter the API security key has been removed (this was the most common step where users get stuck)
* WebAppsDetector class was updated
* Theme Provider module was updated to 1.4.09

= 1.4.08 =
* WebAppsDetector class was updated
* Theme Provider module was updated to 1.4.08

= 1.4.07 =
* WebAppsDetector class was updated
* Theme Provider module was updated to 1.4.07

= 1.4.06 =
* WebAppsDetector class was updated
* Theme Provider module was updated to 1.4.06

= 1.4.05 =
* PHP notice was fixed
* WebAppsDetector class was updated
* Theme Provider module was updated to 1.4.03

= 1.4.04 =
* Support for update notices of the Theme Provider module was added
* WebAppsDetector class was updated
* Theme Provider module was updated to 1.4.01

= 1.4.03 =
* UI improvements
* Fixed the Theme Provider download link
* Prepared for localization

= 1.4.02 =
* PHP 5.0-5.2 compatibility fixed
* Theme Provider module was updated to 1.4.00

= 1.4.01 =
* Theme Provider module was updated to 1.3.01
* WP 4.2 "Tested up to" tag

= 1.4.00 =
* Theme Provider Free module support finally added!

= 1.3.01 =
* A wizard that guides users through the setup process was improved

= 1.3.00 =
* Option to enable and disable the adjustment of internal & external CSS files
* Option to enable experimental features

= 1.2.00 =
* Shared runtime mode added

= 1.1.00 =
* Direct cache access mode added

= 1.0.00 =
* Initial release with Isolated runtime mode

== License ==

This plugin is free for everyone! It is released under the LGPL, which means you can use it free of charge on your personal or commercial blog. But in order to enjoy the full functionality of this plugin, you need to download the Theme Provider module for the web app you want to integrate your WordPress with.
