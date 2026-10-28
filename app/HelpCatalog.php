<?php
declare(strict_types=1);
namespace Spirit;
/** Keep these guides aligned with the working admin screens. */
final class HelpCatalog {
 public static function categories(): array {
  return [
   'getting-started'=>['Getting started','Launch checks, your dashboard and where to begin',[
    ['Your administration dashboard','Open Overview to review site health, published music and shows, audience summary and recent activity.','admin/'],
    ['Before launching','Open System & maintenance → Launch checks. Review HTTPS, settings, installation state and any warnings before publishing.','admin/launch/']]],
   'pages'=>['Pages & imagery','Edit website introductions, backgrounds and photographs',[
    ['Edit a page','Open Website content → Pages & homepage. Choose the page, update Subtitle, Page text or Feature image, then Save private draft. Preview the draft and choose Publish approved content to make it live.','admin/content/?kind=page'],
    ['Replace an image','Open Media library to upload an approved photograph or artwork. Return to the page editor, select its Feature image, preview, then publish. Uploaded files do not automatically replace a public image.','admin/media/']]],
   'members'=>['Band members','Portraits, biographies and lineup order',[
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
  ];
 }
}
