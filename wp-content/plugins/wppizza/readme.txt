=== WPPizza ===
Contributors: ollybach
Donate link: http://www.wp-pizza.com/
Author URI: http://www.wp-pizza.com
Plugin URI: http://wordpress.org/extend/plugins/wppizza/
Tags: pizza, restaurant, pizzaria, pizzeria, restaurant menu, ecommerce, e-commerce, commerce, wordpress ecommerce, store, shop, sales, shopping, cart, order online, cash on delivery, multilingual, checkout, configurable, variable, widgets, shipping, tax, wpml
Requires at least: PHP 5.3+, MySql 5.5+, WP 3.3+ 
Tested up to: 5.0
Stable tag: 3.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A Restaurant Plugin (not only for Pizza). Maintain your Menu (sizes, prices, categories). Accept COD orders. Multisite, Multilingual, WPML compatible.



== Description ==

- **Conceived for Pizza Delivery Businesses, but flexible enough to serve any restaurant type.**

- Maintain your restaurant menu online and accept cash on delivery orders.

- Set categories, multiple prices per item and descriptions.

- Multilingual Frontend (just update labels in admin settings page and/or widget as required). WPML compatible.

- Multisite enabled

- Keeps track of your online orders.

- Shortcode enabled. (see <a href='http://docs.wp-pizza.com/shortcodes/'>complete shortcode list</a>)


**To see the plugin in action with different themes try it at <a href="http://demo.wp-pizza.com/">demo.wp-pizza.com</a>**

**if you wish to allow your customers to add additional ingredients to any given menu item, have a look at the premium <a href='https://www.wp-pizza.com/'>"WPPizza Add Ingredients"</a> extension**

= Current premium extensions available: =

* <a href='https://www.wp-pizza.com/'>Add Ingredients</a> - <a href='http://demo.wp-pizza.com/wppizza-add-ingredients/'>(Demo)</a>   
* <a href='https://www.wp-pizza.com/'>Delivery By Post/ZipCode</a> - <a href='http://demo.wp-pizza.com/wppizza-delivery-by-postcode/'>(Demo)</a>    
* <a href='https://www.wp-pizza.com/'>Pickup Prices</a> - <a href='http://demo.wp-pizza.com/wppizza-pickup-prices/'>(Demo)</a>    
* <a href='https://www.wp-pizza.com/'>Coupons and Discounts</a> - <a href='http://demo.wp-pizza.com/wppizza-coupons-and-discounts/'>(Demo)</a>    
* <a href='https://www.wp-pizza.com/'>Preorder</a> - <a href='http://demo.wp-pizza.com/wppizza-preorder/'>(Demo)</a>    
* <a href='https://www.wp-pizza.com/'>Timed Menu</a> - <a href='http://demo.wp-pizza.com/wppizza-timed-menu/our-menu/special-offers/'>(Demo)</a>    
* <a href='https://www.wp-pizza.com/'>Google Cloudprint</a> - (No demo available as it requires printer setup)     


**gateways available to process credit card payments instead of just cash on delivery at <a href='https://www.wp-pizza.com/gateways/'>www.wp-pizza.com</a>** 


== Installation ==

**Install**

1. Download the plugin and upload the entire `wppizza` folder to the `/wp-content/plugins/` directory.  
Alternatively you can download and install WPPizza using the built in WordPress plugin installer.  
2. Activate the plugin through the 'Plugins' menu in WordPress.  
3. You will find all configuration and menu options in your administration sidebar  


**Things to do on first install**

for consistency this document has now moved to the following location :   
<a href='http://docs.wp-pizza.com/getting-started/?section=setup'>http://docs.wp-pizza.com/getting-started/?section=setup</a>  
** I strongly encourage you to read it **  


**Uninstall**

Please note:  
although all options, menu items and menu categories get deleted from the database along with the table that holds any orders you may have received, you will manually have to delete any additional pages (such as the order page for example) that have been created as i have no way of knowing if you are using this page elsewhere or have changed the content/name of it.  
the same goes for the 3 example icons that come with this plugin as you might have used them elsewhere.


== Screenshots ==

1. frontend example
2. administration - widget
3. administration - categories
4. administration - menu item
5. administration - order settings (one of many option screens)
  

== Other Notes ==

= Translations provided by: =

* Italien:  Silvia Palandri  
* Hebrew:  Yair10 [&#1492;&#1500;&#1489;&#32;&#1489;&#1504;&#1497;&#1497;&#1514;&#32;&#1488;&#1514;&#1512;&#1497;&#1501;&#32;]  
* Dutch:  Jelmer  
* Spanish:  Andrew Kurtis at <a href="http://www.webhostinghub.com/">WebHostingHub</a>  
* German:  Franz Rufnak, Witali Opfer 

Many, many thanks guys and girls.  

Note: As the plugin gets updated over time and has some other strings and features added, the translations above (and future ones) will probably have a few strings not yet translated. If you wish, feel free to provide any of those missing and I will update the translations accordingly.  

If you want to contribute your own translation, feel free to send me your files and I will be more than happy to include them.  


= Demo Icons: =
please note that the icons used in the demo installation are <a href="http://www.iconarchive.com/show/desktop-buffet-icons-by-aha-soft.html">iconarchive.com</a> icons and not for commercial use.  
if you do wish to use any icon from this set commercially, please follow <a href="http://www.desktop-icon.com/stock-icons/desktop-buffet-icons.htm">this link</a> to purchase it.  


== Changelog ==

3.7  
* Fix: wppizza smtp settings - if enabled - were not always applied  
* Fix: added missing "personal information" and "order details" localization strings in use on "thank you" page that erroneously used strings reserved for confirmation page  
* Added: class "wppizza-totals-no-items" |"wppizza-totals-has-items" to minicart parent div if (no) item in cart  
* Added: "count_only" attribute for [wppizza type='totals'] shortcode    
* Added: set of filters to enable customisation of default install sizes/categories/items/prices/additives (wppizza_filter_install_default_sizes, wppizza_filter_install_default_additives, wppizza_filter_install_default_categories, wppizza_filter_install_default_items)  
* Tweak: on new installations default pickup toggle to be enabled on order page too (as opposed to just under cart)  
* Tweak: added _SERVER vars to some potential error messages to aid debugging  
* Tweak: added formatted order  parameter to 'wppizza_filter_email_subject' filter  
* Tweak: display full path of all customised wppizza templates in wppizza->tools->system info (if any)  
* Tweak: added wppizza custom header to emails send by wppizza for identificationa and debug purposes and to more reliably use smtp for wppizza mails only  
* Tweak: allow gateway classnames to have underscores after 'WPPIZZA_GATEWAY_' Prefix  
* Dev/Tweak: added generic - empty - wppizza-submit-error div right before pay buttons to allow custom js to write errors into if required  
* Dev/Tweak: added 'PAYMENT_PENDING' db payment_status column for payment methods that perhaps only pay hours or even days later  
* Dev: removed superflous argument from static wppizza_selected_gateway function  
* Dev: several action, filter hooks and helper functions added to allow for inline payment gateway development  
28th August 2018  


3.6.6  
* fix: Admin -> Customer "Avg. / Order" was wrongly calculated using number of items instead of number of orders as divider    
* tweak: added postid parameter to 'wppizza_filter_post_content_markup' filter  
* tweak: added 'wppizza_filter_post_content' filter  
* tweak: removed some wppizza constants from being displayed in wppizza-> tools -> system info  
25th July 2018  


3.6.5  
* fix: set categories in "WPPizza->Order Settings" Discount/Delivery exclusions (if any) were still lost on plugin updates  
4th July 2018  


3.6.4  
* fix: making sure custom_css (wppizzza->layout) file gets (re)created if it does not (yet) exist - especially on plugin update.  
4th July 2018  


3.6.3  
* fix: any set menu items and categories in "WPPizza->Order Settings" Discount/Delivery exclusions were lost on plugin updates  
* fix: any custom css entered in WPPizza -> layout-> custom css got lost on plugin updates  
* fix: privacy checkbox was always enabled on checkout even if privacy settings were not enabled  
3rd July 2018  


3.6.2  
* Tweak: also use order of categories displayed in cart according to the category hierarchy set if category display is enabled in layout settings   
* Tweak: INEXPLICABLY (as far as I am concerned) - WPML (v4+ but might apply to older WPML versions too) requires "Language filtering for AJAX operations" to be explicitly enabled/set for *ajax* calls to know what the current language is.  3.6.2 Adds a workaround to not to have to rely on this setting/option (appplicable to WPPizza only). 
25th June 2018  


3.6.1  
* Fix: [wppizza single=x] shortcode did not allow for items to be added to cart 
* Tweak: force an "Uncategorised" category if categories are supposed to be displayed (cart/orders) but an item was not assigned to any category  
* DEV - Added: wppizza_gateways_inline_payment_details filter to allow gateways to add payment information directly into the order page  
* DEV - Added: in conjunction with that above filter, added payment_details.php template and parameters into page.order.php and page.confirm-order.php 
20th June 2018  


3.6  
* Fix: additional recipients set in email templates might have got lost on any plugin update  
* Fix: possible error when WPML installed but without string translation activated  
* Fix: not all shortcode attributes were used when re-polling orders using [wppizza_admin type=admin_orderhistory] shortcode  
* Fix: not able to print orders that had been rejected (blank page)  
* Fix: removed some - inconsequential - deprecated warnings when getting system info (wppizza->tools)  
* Added (Internal/Dev):  "anonymised" column (date) to orders table  
* Added: allow to also remove item in minicart order details (if enabled)  
* Added: privacy erase options (anonymise/delete/no_action) (requires wordpress 4.9.6)  
* Added: privacy suggestion text (requires wordpress 4.9.6)  
* Tweak: moved privacy functions to its own tab in WPPizza->Tools (requires wordpress 4.9.6 to do anything)  
* Tweak: removed (and updated) old and unused payment_status ENUM values COD, OTHER, NOTAPPLICABLE, PENDING in favour of their applicable equivalents  
* Tweak: bypass polling if not logged in or not having access when using [wppizza_admin type=admin_orderhistory] shortcode  
* Tweak: added new order audio notifications (if enabled) when using [wppizza_admin type=admin_orderhistory] shortcode  
* Tweak: made html template markup available in wppizza_on_orderstatus_change filter  
* DEV - update/note: constant WPPIZZA_ENCRYPTION_KEY (introduced as a temporary solution for privacy integrations in 3.5.0.1/3.5.0.2 ) can be removed again (if it was added to wp-config.php at all) as it has been superseeded by a more sensible way of doing things (keeping it won't break things though)  
15th June 2018  

3.5.0.2  
* fix: possible fatal error with php <=5.4 
21st May 2018 

3.5.0.1  
* Dev - Tweak: Added uoKey (unique order key) made up of blog id and order id to output of wppizza_get_orders()  
* Dev - Tweak: Removed under development and not yet for production license tools tab    
17th May 2018 


3.5  
* Added - Privacy: options added to WPPizza->Settings->Privacy/GDPR. Please refer to the "help" screen on that page for more details (requires WP 4.9.6+)  
* Added - Privacy: 'email' column added to orders table to aid exporting data  
* Added - Shortcode: [wppizza_admin type="admin_orderhistory"] added to display compact order history in frontend (see https://docs.wp-pizza.com/shortcodes/?section=admin-orderhistory for attributes/details)  
* Fix: some more possible phpnotices eliminated  
* Fix: stop throwing/mailing false errors when the same ipn notifications of gateways get sent multiple times and an order cannot be found as it was already completed  
* Fix: some custom statuses (if set) might not have been saved when updated for an order  
* Fix: globally available function wppizza_is_current_businesday() was not working  
* Fix: trying to include jquery ui spinner css stylesheet even if not set  
* Fix: forcefully closing of shop (in WPPizza -> Opening Times) did not get applied in ajax requests   
* Fix: (Multisite) if querying for all orders from all blogs in parent site's "WPPizza -> Order History" might have resulted in error if different number of columns existed in order tables of the different blogs    
* Tweak: added "ellipsis" css for massively long gaateway transaction ids with mouseover title in admin history  
* Tweak: made the prettyphoto url a bit more generic and adding post tile  
* Tweak: more seamless jquery toggle between transaction and order details when using user purchase history shortcode ( [wppizza type='orderhistory'] ) 
* Tweak: added  more orders info (no of orders . orders per page ) under pagination links when using user purchase history shortcode  
* Tweak: make actual ID/Key for each order form input in "WPPizza -> Order Form" visible when hovering over "enabled" button  
* Tweak: made admin labels filterable  
* Tweak: removed "wppizza" post type from wordpress export option (as imports would only partially work) but could be reenabled with "wppizza_filter_cpt_args" filter (though cloning the entire site is currently the only sensible option.)  
* Tweak: additional sanity check for admin subpage links to avoid namespace clashes with other plugins that do not prefix their admin page(s) variables  
* Tweak: added some more filters per section to email / print templates that could be used if necessary    
* Tweak: added 'wppizza_filter_force_pickup_toggle_display filter' to alwasy show pickup toggle, even if shop is closed (also bypasses js isOpen check alerts)  
* Tweak: increased varchar in orders table that captures IP addresses to 50 for IPv4-mapped IPv6 (45 should be enough but let's be conservative. it's not used anywhere anyway)  
* Dev - IMPORTANT: internal function 'get_orders_orderhistory()' HAS BEEN REMOVED due to too many inconsistencies. Use 'wppizza_get_orders()' instead. See https://docs.wp-pizza.com/developers/?section=function-wppizza_get_orders (you might need to tweak a few things)  
* Dev - consistency: all dimensions of returned order result queries will now also always be arrays themselves (no more mix and match between array and objects)  
* Dev - DEPRACATION NOTICE: fetch_order_details(), results_formatted() as well as simplyfy_orders() will also be removed soon in favour of using wppizza_get_orders() above  
* Dev - IMPORTANT: do not use internal functions of the plugin unless they are documented as they may change or get removed at any time without notice.  
* Under Development (no ETA yet): Allow any License Keys that might exist for different premium plugins to be entered in one dedicated page/tab instead a different tab per plugin    
16th May 2018 


3.4  
* intentionally skipped  

3.3.5  
* fix: any newly entered or amended customer data on order page was not kept when changing quantities (if enabled) 
* fix: ajax request to update cart ran 2 times when updating item quantity via input fields in cart/orderpage  
13th March 2018 

3.3.4  
* tweak: minor admin js tweaks/simplifications  
* tweak: update to plugin development helper functions  
* tweak/update: making sure any GET variables send to gateways are trimmed of leading/trailing whitespaces  
12th March 2018 

3.3.3  
* fix: wppizza_on_order_execute action hook only had customer details available with some gateways  
* development: tweaked some of the (future use) plugin development functions  
08th March 2018 


3.3.2  
* tweak: allow label of order formfield ("WPPizza->Order Form Settings") to include html if type is set to "checkbox"  
* added: "viewonly" shortcode attribute for menu item display, removing cart icon and add to cart functionality  
* added: option to exclude delivery charges (if any) to see if minimum order value was reached  
06th March 2018 

3.3.1  
* fix for php versions <=5.4: fatal error thrown Cant use function return value in write context  
04th March 2018 

3.3  
* added: bulk delete of orders in admin history (provided user has that capabiity)  
* tweak: some minor code tidy ups and improvements in plugin development helper functions  
03rd March 2018  

3.2.10  
* added: action hook wppizza_on_email_sent to perhaps do something more when an email was sent  
21st February 2018  

3.2.9  
* tweak: moved capture of any transaction details in db before sending any emails (to make those details available there when using mail filters)
* tweak: added formatted and raw order data as parameters to wppizza_filter_mail_headers and wppizza_filter_mail_attachments hooks
21st February 2018  

3.2.8 
* added: wppizza_order_reject function and payment_status "rejected" to orders table (might be useful in some cases/gateways)  
* tweak: wrapped wppizza_on_order_execute hook into has_action call to truly restrict to completed orders only  and to eliminate some overheads if not used  
* tweak: minor tweak to admin css applied to contextual help screen  
* tweak: allow min/max arguments to be passed on to wppizza_validate_int_only helper validation function  
* added: a somewhat more versatile "wppizza_get_order_by_columns" helper function that can be used elsewhere (not used in plugin itself)  
* fix: make sure gateway/transaction responses/details also get passed to wppizza_on_order_execute hook  
* changed: convenience function "wppizza_telephone_country_codes" (added in 3.2.4) removed in favour of more versatile "wppizza_country_info"  
20th February 2018  


3.2.7    
* added: globally available convenience functions "added wppizza_get_cart()"  "wppizza_cart_summary()"
* added: a couple of css classes to order history screen elements
* added/fix: added missing filter wppizza_filter_db_column_data when submitting order
16th February 2018  

3.2.6  
* tweak: allow for shortcodes (and any other the_content filters for that matter) in menu item descriptions  (enable in wppizza->layout->general)  
6th February 2018  

3.2.5  
* fix: any *custom* opening times set in wppizza->openingtimes were duplicated on plugin update (simply delete the duplicates if you have any, though leaving them will not cause any problems either)   
* fix: "Wppizza->Order Settings->Delivery->Exclude menu items from calculation" was broken in previous v3.x version. If you have any item added there, please simply save this page ("WPPizza->Order Settings")once.  
* fix: the wrong menu items were potentially displayed when using install option 2 (using templates to display mnu items) and menu items were in more than one category  
* tweak: using more sensical "vertical-align: super" instead of -12px positioning for additives display to aid more consistency throughout different themes  
* tweak: removed some legacy(v2.x) and now unused in v3.x localization strings  
* tweak: added some more available info to error/admin email if email to shop fails  
* added: wppizza_filter_gateways_payment_options introduced (see https://docs.wp-pizza.com/developers/?section=gateway-filter-frontend )  
* in progress: several plugin development functions added - more to come. A skeleton plugin will be added to add-ons directory in due course.
31th January 2018  


3.2.4  
* added: convenience function "wppizza_telephone_country_codes" to get country phonenumbers prefixes
* tweak/fix: made guest/regsiter checkout radio inputs have distinct ids  
* tweak: made sure fieldsets on order page are position:relative (as some themes might to globaly funny things)  
* fix: delivery type (pickup/delivery) was not shown consistently in order history  
16th January 2018  

3.2.3  
* fix: some more possible php notices eliminated   
* removed: some old - and now wrong - v2 help documentation regarding print/email templates   
* added: option to make javascript alerts stylable modal windows instead of browser native popups (wppizza->layout->miscellaneous)  
3rd January 2018  

3.2.2  
* fix: typo fixed in 3.2.1 appeared 3 times actually so fixed the other 2 as well   
19th December 2017  

3.2.1  
* fix: typo in wppizza_breadcrumbs() function introduced in 3.2 causing syntax error (with older versions of php)  
19th December 2017  

3.2  
* fix: any closing times or custom opening times set in wppizza->opening times got lost on plugin update  
* fix: "wppizza_filter_email_items_markup" filter was applied 2x so removed one of them  
* fix: some more possible php notices eliminated  
* tweak: orderhistory split action buttons array for more granulaity when using wppizza_filter_orderhistory_order_actions filters  
* tweak: to account for display limitations of small screen devices, quantities of items in cart/orderpage were only shown for screensizes >420, this has now changed to be >300 as there still should be enough space available. Note: Instead of removing quantities below 300, the remove button is now being hidden instead however, as quantities can still be set to 0 - i.e removed -  if required (always provided the theme accounts for responsiveness in the first place)  
* tweak: made admin localization input fields a little bit bigger  
* tweak: added js/ajax error identifiers to aid debugging in admin order history js
* tweak: access rights - allow admins to also delete menu items of other users  
* tweak: access rights - only allow "admins" and "editors" to edit menu items created by other user roles - in line with standard wordpress behaviour. (Re-save "Wppizza-> Access rights -> Menu Items" for this to be applied for non-admin roles [1 time: off->save, on->save])  
* tweak: more meaningful ajax error console.log entries (if any)  
* tweak: stop and restart polling during changing order status in order history to decrease server load   
* added: some more gateway helper functions  
* added: make gateway objects filterable using wppizza_filter_gateway_object_{gw_ident} (to - for example - enable discounts instead of surcharges for a prepay gateway )
* added: dashboard widget, breakdown by gateways (if more than one)  
* added: make admin dashboard widget filterable to only display selective parts of data if necessary  
* added: wppizza_breadcrumbs() template function added to display breadcrumbs somewhere if required (for example in theme-wrapper.php if used - see there for arguments/usage)  
17th December 2017  


3.0 - 3.1.7  
* changelogs for versions 3.0 to 3.1.7 moved to /wppizza/changelogs/

1.0 - 2.16.11.28  
* changelogs for versions up to 3.0 can be found in /wppizza/changelogs/


== Frequently Asked Questions ==

= General Faq's =

for consistency and manageability the faq's have been moved to <a href='http://docs.wp-pizza.com/faqs/'>http://docs.wp-pizza.com/faqs/</a>

= Shortcodes = 

please refer to <a href='http://docs.wp-pizza.com/shortcodes/'>http://docs.wp-pizza.com/shortcodes/</a>


= How can I submit a bug, ask for help or request a new feature? =

- leave a message on the <a href="http://wordpress.org/support/plugin/wppizza">wordpress forum</a> and I'll respond asap.  
- send an email to dev[at]wp-pizza.com with as much info as you can give me or 
- use the <a href="https://www.wp-pizza.com/contact/">"contact" form</a>, <a href="https://www.wp-pizza.com/forum/feature-requests/">"feature request" page </a> or <a href="https://www.wp-pizza.com/support/">support forum</a> on <a href="https://www.wp-pizza.com/">www.wp-pizza.com</a>


	
	>**additional premium add-ons can be found at <a href="https://www.wp-pizza.com/">www.wp-pizza.com</a>**  
	>


== Upgrade Notice ==

= 3.0 =
<b>WARNING : DO NOT SIMPLY CLICK ON "UPDATE". YOU *MUST* READ THE DOCUMENTATION OF <a href="http://docs.wp-pizza.com/getting-started/?section=upgrade-v2-x-v3-x" target="_blank" style="color:#6ce414;text-decoration:underline">HOW TO UPGRADE</a> FROM WPPIZZA VERSION 2.x to VERSION 3.X </b>
&nbsp;
You are <b>STRONGLY</b> advised to also make a <b>FULL BACKUP OF YOUR SITE</b> first. Please refer to the <a href="http://docs.wp-pizza.com/getting-started/?section=upgrade-v2-x-v3-x" target="_blank" style="color:#6ce414;text-decoration:underline">upgrade documentation</a>.
&nbsp;
<b>Developers/Sites that have customised the WPPizza v2.x plugin:</b> 
If you have edited templates, are using some actions/filters, have amended the css etc, you should read the <a href="http://docs.wp-pizza.com/getting-started/?section=migrating-2-x-to-3-x-customisations" style="color:#6ce414;text-decoration:underline">Migrating Customisations</a> documents. Many things have changed !!
Do NOT just update and hope your customisation(s) will work. They probably will not and things will break. Again, whatever you do, make a complete backup of your site first !!!
&nbsp;
<b>IF YOU ARE NOT SURE ABOUT SOMETHING PLEASE ASK <a href="https://www.wp-pizza.com/support/" target="_blank" style="color:#6ce414;text-decoration:underline">IN THE FORUM</a> OR USE THE <a href="https://www.wp-pizza.com/contact/" target="_blank" style="color:#6ce414;text-decoration:underline">CONTACT FORM</a></b>
&nbsp;