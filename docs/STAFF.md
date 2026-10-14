# Staff access and owner audit history

Development target 0.9.0-dev.1. Check TEST_RESULTS.md for execution evidence.

## Roles and boundaries

Owner has the administrative capabilities. Content editor (`content_editor`) can manage public content/drafts/previews and the shared media library. Store manager (`store_manager`) can manage catalog, variants, inventory, shared media and order fulfillment. Both have their own password/security screen and a restricted dashboard/sidebar.

Store managers see addresses/email needed to fulfill orders but not provider keys, gateway reporting/reversals, subscriber/customer exports, customer suspension, booking inquiries or system maintenance. Owner staff, mail settings/queue, subscribers, site configuration, migration and installation controls remain owner-only. Unknown legacy roles such as `editor` are intentionally not mapped to a privileged role; an owner must assign an explicitly supported role. Customer accounts use a separate identity system and never grant staff access.

All entry points check server-side permissions. Hiding navigation is not authorization. Shared-media capability permits viewing otherwise-private uploaded files in that media library; it does not grant private config, backups, mail bodies or customer-account access. Plan staff assignments with that boundary in mind.

## Invite, verify and revoke

Configure and actually verify SMTP first. Admin > Staff access requires the current owner password for inviting, changing access or cancelling an invitation. Enter name, email and the intended role. The recipient receives a 48-hour link and chooses their own password after email verification. GET requests do not activate accounts. Completion is CSRF-protected; the token is single-use, stored hashed, and does not permit submitted role/email fields to change the assigned invitation.

There is no public staff registration, temporary shared password, or invitation-based overwrite of an existing staff email. Outstanding invitations from an owner cease to apply if that owner is demoted or suspended. An owner cannot remove/demote their own account; at least one active owner is preserved under a serialized database operation. Staff changes use optimistic version checks and immediately revoke prior sessions and applicable recovery tokens.

The recent active-invitation list is capped at 25; staff accounts are paginated. Cancel an unused link explicitly. Queued invitation/reset messages are cancelled before sending when the token is used, expired or no longer applicable. An already in-flight email cannot be recalled.

## Password recovery

Staff sign-in has a separate recovery link. It gives the same response for unknown, suspended and active addresses. An active staff address can receive a 30-minute single-use link. GET leaves credentials unchanged; explicit password submission resets them and revokes earlier sessions. Configure provider delivery before relying on email recovery. No provider inbox success is implied by a stored configuration.

Password changes require the current password and are rate-limited. A locked database recheck prevents a simultaneous role change, password reset or suspension being overwritten by stale verification/rehashing. Successful changes keep only the current session valid. Forgotten passwords without working email require the existing owner's assistance or documented host-level recovery; no unauthenticated emergency bypass is added.

## Activity and deployment

Admin > Activity history is owner-only, searchable by action and paginated, showing recorded actor, action and UTC time. It does not display mail bodies, setup codes, payment secrets or password hashes. Detailed per-object audit diffs are not part of this initial view.

Migration 006 adds the staff-token table without rewriting 001–005 or changing existing owners/content/orders. Full backup/restore/browser ZIP upgrades are separate .9 work, not implied by this staff checkpoint. Existing supported installs should preserve all private config/secrets/media/data and use the owner database update; never reinstall to bypass an error.
