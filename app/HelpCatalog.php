<?php
declare(strict_types=1);
namespace Spirit;
/** Keep these guides aligned with the working admin screens. */
final class HelpCatalog {
 /** Link every admin screen to the most relevant help topic. */
 public static function topicForAdmin(string $active): string {
  return match($active){
   'page'=>'pages',
   'member'=>'members',
   'library','gallery','video','press'=>'media',
   'release','track','event'=>'music',
   'analytics','analytics-visitors','analytics-geography'=>'analytics',
   'bookings','subscribers','mail','communication'=>'audience',
   'store','store-categories','customers','orders','store-settings','delivery-policy','payment-settings','payment-notifications'=>'commerce',
   'staff','account','settings','discovery'=>'account',
   'launch','installation','backups','upgrades','updates','maintenance','activity','roadmap'=>'maintenance',
   default=>'getting-started'
  };
 }
 public static function categories(): array {
  $topics = [
   'getting-started'=>['Getting started','Launch checks, your dashboard and where to begin',[
    ['Your administration dashboard','Open Overview to review site health, published music and shows, audience summary and recent activity.','admin/'],
    ['Before launching','Open System & maintenance → Launch checks. Review HTTPS, settings, installation state and any warnings before publishing.','admin/launch/']]],
   'pages'=>['Pages & imagery','Edit website introductions, backgrounds and photographs',[
    ['Edit a page','Open Website content → Pages & homepage. Choose the page, update Subtitle, Page text or Feature image, then Save private draft. Preview the draft and choose Publish approved content to make it live.','admin/content/?kind=page'],
    ['Edit headings inside pages','Open Pages & homepage and select the relevant designed page. Fields labeled for that page, such as Home · Music section heading or Band · Member profiles heading, control individual section titles. Leave them blank to keep the approved original typography; save, preview and publish to go live.','admin/content/?kind=page'],
    ['Change a page headline','Open Pages & homepage, edit the Page headline or Small heading above headline, and publish. Leaving these optional fields empty keeps the approved default text.','admin/content/?kind=page'],
    ['Edit the Band page button','Open the Band page record in Pages & homepage. Set Primary button label and an optional HTTPS destination; leaving the destination blank retains the internal Press link. Preview before publishing.','admin/content/?kind=page'],
    ['Edit the Booking page','Open Pages & homepage and select Booking. Edit its small heading, headline, introduction, artwork and primary button label or HTTPS destination. An optional Booking details heading appears above the inquiry form when supplied. Save and preview a private draft; the inquiry form is disabled in preview. Publish approved content to change the live Booking presentation without changing inquiry handling.','admin/content/?kind=page'],
    ['Replace an image','Open Media library to upload an approved photograph or artwork. Return to the page editor, select its Feature image, preview, then publish. Uploaded files do not automatically replace a public image.','admin/media/']]],
   'members'=>['Band members','Portraits, biographies and lineup order',[
    ['Import the established lineup','On a site with no saved member records, open Band members and expand Bring the existing lineup into the editor. Confirm import to create editable published names and instrument roles from the former public layout; then add approved portraits and biographies. Previously deleted members are never automatically restored.','admin/content/?kind=member'],
    ['Add a band member','Open Website content → Band members and select Add band member. Enter the name, musical role, biography and an approved image from Media library. Save, preview and publish.','admin/content/?kind=member'],
    ['Edit a biography or photograph','Select an existing member in Band members. Update Approved biography, Musical role or Approved portrait, then publish. The public Band page shows the portrait on the left and the biography on the right on desktop.','admin/content/?kind=member'],
    ['Change lineup order','Open a member and change Sort order. Lower numbers appear first. Publish each edited member to update public ordering.','admin/content/?kind=member'],
    ['Remove a member','Open an existing member and use Remove band member with confirmation. Removal hides the profile; uploaded photographs are retained for safety.','admin/content/?kind=member']]],
   'music'=>['Music & performances','Albums, tracks, shows and venue ticket links',[
    ['Publish a release and tracks','Create an approved Music release with cover artwork. Then add Audio tracks and assign them to the release. Publish records when complete.','admin/content/?kind=release'],
    ['Publish an event','Add a show under Music & shows, enter the event date, timezone, location and admission status. For ticketed events use the venue’s HTTPS seller link, then publish.','admin/content/?kind=event']]],
   'media'=>['Media & press','Photo gallery, videos and press downloads',[
    ['Upload media','Use the Media library for approved images, audio and PDFs. Select uploaded files in the relevant content editor.','admin/media/'],
    ['Manage photos and video','Use Photo gallery for published photos and captions, Videos for approved YouTube/Vimeo links and posters, and Press downloads for PDF material.','admin/content/?kind=gallery']]],
   'audience'=>['Bookings & audience','Inquiries, newsletter and delivery monitoring',[
    ['Review booking inquiries','Open Booking inquiries to review submitted requests. A request is not a confirmed booking.','admin/bookings/'],
    ['Manage email delivery','Configure Email settings, test SMTP as available, then review the Delivery queue and Subscribers.','admin/communication/']]],
   'commerce'=>['Store & orders','Optional merchandise, checkout and fulfillment',[
    ['Prepare a store','Use Products & inventory, categories, shipping/tax policies and payment settings. Do not activate checkout until the merchant and policy checks are complete.','admin/shop/']]],
   'maintenance'=>['Settings & maintenance','Staff, backups, recovery and updates',[
    ['Manage access','Open Staff access to review permissions. Give each staff member only the access required for their work.','admin/staff/'],
    ['Backup before upgrading','Create and verify a backup before using Browser upgrades. Follow the version-specific upgrade notes; do not rerun the fresh installer on an existing site.','admin/backups/'],
    ['Check site settings','Use Website settings to maintain brand and site details. Use Installation health or Database updates only when needed.','admin/settings.php']]],
   'analytics'=>['Analytics & reporting','Understand audience reports without confusing visitors and visits',[
    ['Review audience trends','Open Analytics → Audience dashboard, choose a date range, and review traffic trends. Traffic counts depend on enabled collection and observed data.','admin/analytics/'],
    ['Explore visitor records','Use Visitor explorer to review the measured visits. Respect privacy and do not treat bot traffic as confirmed people.','admin/analytics/visitors.php'],
    ['Review geographic data','Open Geography data to inspect collected location summaries. Geo results are approximate, not exact visitor addresses.','admin/analytics/geography.php']]],
   'account'=>['Accounts & permissions','Maintain sign-in security and manage collaborators',[
    ['Update your account','Open Account & security to review your own account and sign-in options.','admin/account.php'],
    ['Manage staff permissions','Use Staff access to manage roles. Review access after role changes and keep owner privileges limited.','admin/staff/']]],
   'troubleshooting'=>['Troubleshooting','Resolve common publishing and operation problems',[
    ['Changes are not showing','Confirm you selected Publish approved content rather than Save private draft; reload the public page. Only published records appear in the public site.','admin/content/?kind=page'],
    ['Portrait or artwork is missing','Open Media library and verify the approved file exists. Then confirm the content record selects that image and was published. Do not delete shared images to fix a preview.','admin/media/'],
    ['Cannot publish a show','Check the required event fields including venue, date, timezone and city. Add the venue ticket link only when confirmed.','admin/content/?kind=event'],
    ['Email is not being delivered','Review SMTP configuration in Email settings and the Delivery queue. Check host certificate, encryption and credentials rather than switching to insecure delivery.','admin/communication/'],
    ['Site needs an update','Review Installation health, Database updates and Browser upgrades before taking action. Back up first and never run the fresh installer on an existing live site.','admin/installation.php'],
    ['Store unavailable','Commerce is optional and may be intentionally disabled. Review store activation and payment readiness without collecting transactions until provider acceptance is complete.','admin/shop/settings.php']]],
  ];
  foreach([['Find the right instructions','Open Help & guidance → Help Center, search by a task or select a topic. Guides link directly to the related admin screen; staff only see topics their permissions allow.','admin/help/']] as $guide){$topics['getting-started'][2][]=$guide;}
  foreach([['Publish a video','Go to Website content → Videos, add the approved YouTube or Vimeo URL, choose an optional poster image, save privately, preview and publish.','admin/content/?kind=video'],
           ['Manage press downloads','Open Press downloads and create an entry with a previously uploaded, approved PDF. Preview it, then publish it for visitors to access.','admin/content/?kind=press']] as $guide){$topics['media'][2][]=$guide;}
  foreach([['Add an audio track','Upload the authorized recording to Media library. Open Audio tracks, associate it with the correct release, add its title and duration, then save and publish.','admin/content/?kind=track']] as $guide){$topics['music'][2][]=$guide;}
  foreach([['Review newsletter subscribers','Open Subscribers and review subscription state and consent before sending. Never add addresses without permission.','admin/subscribers/'],
           ['Investigate delivery errors','Open Delivery queue to review pending or failed messages. Correct verified SMTP settings, then use the available retry actions only after checking duplicates.','admin/mail/']] as $guide){$topics['audience'][2][]=$guide;}
  foreach([['Organize products into categories','Open Product categories to create or maintain the organization of approved merchandise. Confirm that product category assignments display correctly.','admin/shop/categories.php'],
           ['Review customer accounts','Open Customers to review existing customer records according to your staff permissions. Avoid exporting private details unless business operations require them.','admin/shop/customers.php'],
           ['Manage fulfillment','Open Orders & fulfillment, review order status and verified payment before dispatching goods. Keep fulfillment records accurate.','admin/orders/'],
           ['Configure shipping and tax policies','Open Shipping, tax & terms; review required business policies and validate the results before activating checkout.','admin/orders/policy.php'],
           ['Set up payment processing','Open Payment settings and follow the gateway configuration and acceptance process. Keep credentials private; do not process live transactions before testing.','admin/orders/payments.php'],
           ['Inspect payment notifications','Review Payment notifications to understand webhook delivery and processing state. Never manually mark a payment received without provider confirmation.','admin/orders/webhooks.php'],
           ['Review store settings','Open Store settings to review optional commerce activation. Checkout stays disabled until merchant, inventory and policy acceptance is complete.','admin/shop/settings.php']] as $guide){$topics['commerce'][2][]=$guide;}
  foreach([['Control search and sharing','Open Search & sharing to review public discovery metadata and social previews. Make sure descriptions and artwork are approved before publication.','admin/discovery.php']] as $guide){$topics['account'][2][]=$guide;}
  foreach([['Apply browser upgrades safely','Take and verify a full backup first. Open Browser upgrades, inspect the package version and use the guarded upgrade/recovery flow for your current installation layout.','admin/upgrades/'],
           ['Apply database updates','Open Database updates only when the application reports pending migrations. Preserve the database and installation lock; do not reinstall to resolve pending updates.','admin/updates.php'],
           ['Review temporary data maintenance','Open Temporary data & retention and review the documented cleanup boundaries. Do not delete actual media, orders, consent, audit or recovery data as a shortcut.','admin/maintenance/'],
           ['Review administrative activity','Open Activity history to examine changes made by authorized staff. Follow up on unexpected actions before editing or restoring content.','admin/activity/'],
           ['Read the release roadmap','Open Release roadmap to review available features and planned changes. Do not assume a feature is live merely because it appears on the roadmap.','admin/roadmap.php']] as $guide){$topics['maintenance'][2][]=$guide;}
  return $topics;
 }
}
