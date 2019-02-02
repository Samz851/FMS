=== Firstlook Listing Retriever ===

Contributors: andyfms
Donate link: http://myfirstlook.ca/retriever-plugin
Tags: ddf, crea, crea ddf, data distribution facility, mls, idx, real estate, realtor, listings, automation, property, sale, lease, buy, sell, residential, commercial, real estate, realestate, realtor, RETS, Vertical Market, wordpress real estate, wp-property, firstlook, myfirstlook.ca
Requires at least: 4.0
Tested up to: 4.8
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

CREA DDF®, Firstlook Magic. Canadian MLS® listing automation made easy.

== Description ==

The Firstlook Listings Retriever plugin is a CREA DDF® listing automation solution for Canadian REALTORS®. Don't change your existing WordPress site just because you need to show off your listings. Developed in Canada, we are the best solution for getting DDF® member and National Shared Pool listings on your website without any technical overhead.

**For web developers & site builders:**

Spend more time focusing on your client’s needs and less time trying to get the tech to work properly. We host all the data for you so there’s no setup overhead, and our friendly API makes building custom templates easy. We’ll do the heavy lifting for you so you can compete with the big dogs.

**For real estate agents & site admins:**

Your member feed listings are synced daily and integrate perfectly into your site. This means you don’t need to learn anything new to make the whole system work. Reduce the amount of time you spend babysitting your website and get back to doing what you do best. Want to profile neighbourhoods or cities and capture more leads from the National Shared Pool? The Firstlook Retriever makes it a snap.

**How it works:**

Listings from the Canadian Real Estate Association (CREA) are generally split into several feed categories, the most prominent of which are 'member' feeds and 'National Shared Pool' feeds. Member feeds are collections of data that represent an agent's own listings, while National Shared Pool feeds represent every listing currently available in the Canadian market. *Please note that only the provinces of Alberta, British Columbia, New Brunswick, Newfoundland and Labrador, Nova Scotia, Ontario, and Prince Edward Island are supported at this time.*

**Enter The Firstlook Retriever plugin:**

For a low monthly fee, we give you access to member and National Shared Pool feed data, delivered directly to your site. We cache member feed data in a WordPress custom post type so it's easy to work with, and we make National Shared Pool data available for user submitted or automatic searches. Either use our built-in content templates (will display using the default post single and archive), or create your own to match the look and feel of your theme!

**Try for free:**

The plugin is available to download and use without an active data connection free of charge. Add your own exclusive listing content and play with the API at no cost. When you're ready for some live data goodness, all of our data plans come with a free trial so you can be confident that you're getting what you need.

For more information, head over to [myfirstlook.ca](http://myfirstlook.ca)

== Installation ==

**Before you begin:**

In order to use the Firstlook Retriever with live DDF® data you must create a DDF® feed through CREA (member and/or National Shared Pool feeds). This authorizes our servers to collect listing information on your behalf, to display on your website. For REALTORS®, visit [realtorlink.ca](http://realtorlink.ca) to create a data feed, selecting 'Firstlook Media Solutions' as your technical provider. No pre-filtering is necessary for these data feeds. Once a data feed is registered, head over to the [Firstlook Store](https://store.myfirstlook.ca) to purchase a license key. *Please note that you do not need this information or a paid subscription in order to use the plugin without live data access!*

**Basic setup:**

* Upload the plugin folder to the '/wp-content/plugins/' directory, or install the plugin through the WordPress plugins screen directly.
* Activate the plugin through the 'Plugins' screen in WordPress.
* From the WordPress sidebar, navigate to 'Listings > Settings' to configure basic Retriever settings.
* If you're using the plugin with live DDF® data, enter your license key information and CREA transport ID(s) under the 'Data Feeds' tab. If you do not have either of these yet, see the notes above. Under the 'Listing Fetch' tab, use the controls to start/stop the hourly sync schedule or to manually fetch and test the member and national shared pool feeds respectively.

**For web developers & site builders:**

Please visit [myfirstlook.ca/documentation](http://myfirstlook.ca/documentation) for more information on how to get started customizing theme templates and working with listing data.

== Frequently Asked Questions ==

= Are there any other installation requirements? =

No! We strive to make using the Firstlook Retriever plugin as easy as possible. We host all the listing data for you and deliver it to your server only when it's needed. You'll never have to do any extra database configuration or server-side setup or absorb any extra hosting fees to make this work. 

= What is CREA DDF® anyways? =

While not totally new, the Canadian Real Estate Association's Data Distribution Facility® (CREA DDF®) is gaining popularity because of the fast-paced and highly competitive nature of the modern Canadian real estate market. Combined with our Retriever technology, the DDF® allows members to quickly disseminate MLS® listing content to multiple websites, and to ensure that MLS® listing content is displayed accurately. The Retriever plugin is able to synchronize and integrate DDF® 'member feed' listings (an agent's own listings) into any WordPress site (4.0+) so they can be displayed an manipulated accordingly. We also provide a mechanism to access 'National Shared Pool feeds' (all available listings in Canada) so that agents can benefit from lead generation on listings that aren't their own. Visit [myfirstlook.ca](http://myfirstlook.ca) for more information.

= How do I get a CREA DDF® Transport ID? =

REALTORS® can visit [realtorlink.ca](http://realtorlink.ca) to create Member or National Shared Pool feeds, making sure to select Firstlook Media Solutions Inc. as the technical provider. You do not need to apply any pre-filtering when creating a feed. Just authorize us to pull the data on your behalf and you're good to go. Contact [CREA Member Support](http://www.crea.ca/cafe_category/member-support/) for more information.

= How do I purchase a license key? =

Simple, visit the [Firstlook Store](https://store.myfirstlook.ca), sign up for an account, and purchase a license key for the domain that you will be using this plugin on. Once you have your license key, account number and transport IDs from CREA, you have everything you need start pulling listing data to your site with the Listing Retriever plugin. We offer free trials on all of our license plans, so you can be sure you're getting what you need before you commit to a monthly subscription (valid payment method is required).

= Do I have to pay to install the plugin? =

No! The Firstlook retriever plugin is free to try and use for manual listing creation. You only need to purchase a license key to access to our data servers when you're ready to start pulling live DDF® data. Visit [myfirstlook.ca/retriever-plugin](http://myfirstlook.ca/retriever-plugin) for more information.

= Why are there listings missing? =

We are able to collect all Canadian listings that CREA releases to the National Shared Pool (for member and National Shared Pool data feeds). There are a couple of factors that might contribute to listings not being available: First, your brokerage (or client's brokerage) must have explicitly opted in to contributing their listings to the National Shared Pool. Once this is done and it's time to upload or update a listing on MLS, there is usually a checkbox or some other option that specifies that "this listing should be contributed to the National Shared Pool" (varies by region / brokerage) that must be selected in order for our system to pick it up. Finally, while we make every effort to have as complete of coverage as possible, there are some regions that we cannot access for now. Listings from the provinces / territories of Saskatchewan, Manitoba, Québec, Yukon, Northwest Territories, and Nunavut are not available, but we will continue to work with CREA and brokerages in these regions to try and provide the best service possible. 

= Why are there no maps displaying on the single listing pages? =

To enable mapping support, you need to get your own Google Maps license key. It's quick, easy, and free! Click [here](https://developers.google.com/maps/documentation/javascript/get-api-key) for instructions on how to obtain a maps key. Once you have your key, from the WordPress admin sidebar, navigate to 'Listings > Settings > Retriever Options (tab)' and enter your key in the 'Google Maps Key' field.

= How do I change the listing archive slug? =

By default, the listing archive will be available under the '/listings' slug, and the listing category taxonomy under the '/listing-category' slug. If you'd like to change these slugs, navigate to 'Listings > Settings > Retriever Options (tab)' and put a new archive or taxonomy slug in the 'Listing Archive Slug' or the 'Listing Category Slug' fields respectively. Please note that you'll most likely have to flush your permalinks before these new slugs will take effect. Navigate to 'Settings > Permalinks' and click 'Save' to accomplish this.

= How do I stop listings from appearing on the home page? =

By default, listings are included in the home page query, mixed in chronologically with new post content. This can be annoying if you're building page templates with custom loops to handle listings and blog posts separately. To disable this behaviour, simply navigate to 'Listings > Settings > Retriever Options (tab)' and check the 'Hide listings on the homepage...' checkbox.

== Screenshots ==

1. Cached member feed listings as a custom post type.
2. Editing a cached member feed listing. Exclusive listings can also be created manually.
3. Creating categories for the custom listing posts.
4. Settings page, settings for DDF feed automation.
5. Settings page, fetch and sync controls.
6. Settings page, general plugin options.

== Changelog ==

= 1.1.0 =
* Changed endpoint where the license manager connects to. THIS IS A MANDATORY UPDATE!
* Added ability to tag default shared pool searches to taxonomies.
* Modified the built-in listing pre_get_posts action to sort by listing status hierarchy (For Sale > For Lease > Sold > Leased) and post date at the same time.
* Changed the listing statuses to numeric values in the DB instead of strings.
* Fixed issue with mapping listings that have lat / lng available in the WP database.

= 1.0.6 =
* Added Open Graph and Twitter summary card support for listing archives and singles.
* Slightly modified default CREA clickwrap text and content.
* Began laying foundation for listing shortcode feature. This should be fully implemented in a future release.

= 1.0.5 =
* Fixed bug that would cause results to not display properly if "posts_per_page = -1" was used in a theme's "pre_get_posts" action.
* Fixed bug that prevented sorting filters from applying to member feed listings when mixed with default searches and the separate option is checked
* Added new function "fms_is_ddf_national_search_available" which checks to see if a DDF national IDs are set.
* Altered the National search widget to use the new "is available" function.
* Fixed bug that prevented alias domains from working with a registered license key (same server, different domain).
* Added code to remove duplicate listings from mixed member / national result sets.
* Fixed permission for log file creation. System now properly implements WP Filesystem to create the log file if it doesn't already exist.
* Added simple auto retry mechanism to member feed fetch.
* Added system to protect listings from being altered in the event of a server communication failure during member feed fetch. 

= 1.0.4 =
* New feature addition: Added default search capability so that searches can be run in the background on the listings archive page and on the home page.
* Fixed display bug related to Sold and Leased properties showing more data than they should.
* Several small tweaks and bug fixes.
* Tested for compatibility with WordPress 4.8 (RC1).

= 1.0.3 =
* Changed sync dashboard to be more user friendly. Sync controls have been moved to the feed setup screen, and the number of tabs on the options screen has been reduced to two.
* Fixed conflict with Root Relative URLs plugin that was causing CSS and JS files to not load properly.
* Added new function "fms_format_query_string" to the API. The definition of "fms_format_search_url" was confusing, so this new function has been introduced to return just a query string from a parameters array, while the old function will still return a full URL to the search results.

= 1.0.2 =
* Changed function name for temporary post creation to fms_create_virtual_listing_post($listingdata). See our [documentation.](http://www.myfirstlook.ca/documentation)
* Updated default clickwrap text to include new CREA disclaimer about out of province real estate licensing.
* Added mechanism for collecting analytics for listing pages, as per new CREA regulations.
* Changed endpoint for Listing access requests.
* Re-wrote call stack for listing retrieval to use the wp_remote_get function instead of cURL calls.

= 1.0.1 =
* Changed behaviour that was adding an extra question mark to AJAX requests.
* Fixed API function (fms_format_search_url) that was adding the wrong pool prefix to the parameter string.
* Updated the "assume" behaviour in fms_get_lease_rate. You can now pass a default value or an empty string (instead of a boolean).
* Modified fms_get_status_slug function to strip non-alphanumeric characters out of user generated statuses.

= 1.0.0 =
This is the first release of the Firstlook Listing Retriever!

== Upgrade Notice ==

= 1.1.0 =
MANDATORY UPDATE. Our license management system has been upgraded and the data listing feed access will no longer work without this update. Please plan to change over immediately.

= 1.0.6 =
Latest update includes improved SEO and social media integration for listings and some minor bug fixes.

= 1.0.5 =
Latest update includes several bug fixes, some new additions to the developer API, and some features to make member feed syncing more robust.

= 1.0.4 =
Latest update tested with WordPress 4.8! Includes the ability to add default National Shared Pool searches to the listings archive and home page to supplement (or subtly replace) member feed listings. Also includes several bug fixes and stability tweaks.

= 1.0.3 =
Latest updates includes fix for Root Relative URLs plugin conflict, an improved sync dashboard UI, and a new function to format search queries without a full search archive link.

= 1.0.2 =
Latest update includes measures to ensure that your site remains compliant with CREA regulations. Please update now!

= 1.0.1 =
Minor API tweaks, please upgrade to the latest version.

= 1.0.0 =
This is the first release of the Firstlook Listing Retriever!
