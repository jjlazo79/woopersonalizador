**v1.3.2** (12 Sep 2018)  
[update] Add tool to reinstall db tables  
[update] Update POT file  
[fix] Ensure check for active Iconic plugins works  
[fix] Ensure WooThumbs zoom is triggered after layer change  

**v1.3.1** (10 Sep 2018)  
[update] Implement new Iconic core classes  
[update] Hash image names to prevent them being too long  
[fix] Ensure WooThumbs is at the latest version before attempting to use  
[fix] 0 value attribute names were not displayed in the configurator tab  
[fix] Add "no media icon" param for WooThumbs  
[fix] Don't float configurator in WooThumbs  
[fix] Ensure dummy zoom image is removed when fetching variation via AJAX  
[fix] Fix Compatibility issue with WooCommerce Variations Swatches and Photos Plugin  
[fix] Ensure inventory DB is created even after activation  

**v1.3.0** (6 Jun 2018)  
[update] Inventory: decrease even when stock isn't managed on the product.  
[update] Inventory: increase if order is cancelled/failed/refunded.  
[update] Inventory: check stock again before checking out.  
[update] Update POT file  
[update] Update Freemius  
[update] WooThumbs compatibility  
[update] Update classes and IDs  
[update] Cache images as they load for quicker switching  
[update] Add retina sizes to image layers  
[update] Return to default layer image when removing a selected option  
[update] Add plugin suggestions  
[update] Enable zoom and lightbox  
[fix] Fix thumbnail fullscreen issue  
[fix] Fix image size issue in Woo 3+  
[fix] Fix issue with some characters in attribute values (&|.|@|etc)  
[fix] Enhance layer loading so it can't be "tricked"  
[fix] Fix issue with Russian/foreign characters in layers  

**v1.2.2** (19/12/2017)  
[update] Allow loader to be disabled  
[update] Add \[iconic-wpc-gallery\] shortcode  
[update] Update Freemius  
[update] Compatibility with \[product_page\] shortcode  
[update] Improve configurator layer collapsing and sorting in admin  
[update] Improve image upload/remove in admin  
[update] update POT file  
[fix] Sync custom fields when using WPML  
[fix] Set language in AJAX requests  
[fix] Get correct taxonomy terms when taxonomy is translated  
[fix] Fix issue when using forward slashes in attribute value name  
[fix] Only validate BG image when configurator is enabled  
[fix] Issue with png validation for some hosts  
[fix] Strip query string from image URLs when generating

**v1.2.1** (10/08/2017)  
[update] Add WPML compatibility  
[update] New licence system  
[update] Renamed the plugin folder to match Iconic branding  
[fix] Missing galleries  
[fix] Invalid image URL in emails

**v1.2.0** (02/04/2017)  
[update] Compatibility with WooCommerce 3.0.0  
[update] Add static layer functionality  
[update] Remove redux and add settings framework  
[update] Tidy code and comments  
[fix] Fix issue with sorting layers  
[fix] Issue with uploading media to attribute  
[fix] Use WC ajax URL  
[fix] Compiled image in order emails  
[fix] Issue with product specific atts not loading layer on load  
[fix] Issue loading query selected layer with spaces

**v1.1.5** (22/12/2016)  
[fix] Some updates regarding image generation were missing  
[update] Envato market updater  
[update] Add filter to order email thumbnail

**v1.1.4** (07/09/16)  
[update] Author tags  
[fix] Check to see if WooCommerce is active - fix issue on multisites  
[fix] Some hosts don't allow remote images in getimagesize, changed to path so images are generated  
[fix] Error when no default image is set, but it was before  
[fix] Make sure correct image shows in email if it's enabled  
[update] Add validation to check for PNG images  
[fix] Attribute add default image button

**v1.1.3** (16/01/16)  
[fix] admin_url SSL issue

**v1.1.2** (09/09/15)  
[update] change watch to .variations select

**v1.1.1** (08/09/15)  
[fix] Errors when $post is not set  
[fix] Undefined param issue on add_to_cart_inventory_check  
[fix] Missing terms when product is draft

**v1.1.0** (13/07/15)  
[fix] Image layers not loading  
[fix] Image layer loading broken image when no image assigned  
[fix] Default image layer functionality  
[update] Added inventory functionality * Make sure to deactivate/reactivate to install the new DB table  
[fix] Removed images from order success and order email

**v1.0.8** (27/06/15)  
[fix] Moved check for woocommerce to try and fix header issues  
[fix] Remove invalid header error  
[fix] is_array errors  
[update] better pot file

**v1.0.7** (27/04/15)  
[fix] Moved check for woocommerce to try and fix header issues

**v1.0.6** (19/02/15)  
[Update] Add Cyr to Lat enhanced compatibility

**v1.0.5** (14/01/15)  
[Update] Allow for Russian (and other lang) attributes - caution: may affect previous configurations  
[Fix] Load admin scripts only on edit product page  
[Update] Check if WooCommerce is enabled  
[Fix] Fix compatibility with WooCommerce Variation Swatches and Photos v1.5.3

**v1.0.4** (06/08/14)  
[Fix] Fixed bug where TGM didn't notify that Redux was required

**v1.0.3** (27/07/14)  
[Update] Added "Default" image for attributes  
[Fix] Fixed bug where configurator was displayed on frontend even though it wasn't enabled  
[Update] Now works with WooCommerce Variation Swatches and Photos by Lucas Stark  
[Update] Added ability to order configurator options independantly, via drag/drop

**v1.0.2**  
[Fix] Removed tgmpa_load_bulk_installer error

**v1.0.1**  
[Fix] Configurator Enabled returned yes, not true. Added check for this.

**v1.0.0**  
Initial Release