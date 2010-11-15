=== Drafts Scheduler ===
Contributors: talus
Donate link: http://www.installedforyou.com/wordpress/drafts-scheduler-plugin/
Tags: drafts, scheduling, posts, schedule
Requires at least: 2.8
Tested up to: 3.1
Stable tag: 1.3

Sequentially or randomly schedule ALL draft posts for posting.

== Description ==
Ever imported or written a whole bunch of posts in draft status, then realized you needed to post them all over time?

If you've tried to do this manually, you know it takes a LONG time even using the Quick Edit.

Draft Scheduler solves this problem, allowing you to schedule ALL drafts to be posted based on your settings.

Usage:

* Choose from the 3 options
* Sequentially based on your selected interval (hours & minutes)
* Random draft posts at the interval you choose
* Fully random inside a daily time frame with a random or exact number of posts per day within a daily maximum you choose.
* All options also allow you to choose the start date. See Screenshots.
* Click Schedule Drafts and ALL posts withe status DRAFT will be scheduled.
* Now allows "UNDO" of most recent schedule for any non-published posts.

NB: I've said it before - ALL drafts will be scheduled, and there is now an undo option.

== Installation ==

1. Upload draft-scheduler folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Visit the Draft Schedule menu option under POSTS and setup your options.

== Frequently Asked Questions ==

= I upgraded to 1.3 and can't find the Drafts Scheduler in Tools. What happened? =

It was suggested I move this to the POSTS sub-menu as it makes sense in there. Not sure WordPress gurus would agree, but that's ok.

= Can I schedule posts in another status besides draft?  =

Sorry, not in this version. In the future, it would be easy to add.

= Ok, can I set drafts to a status other than Future? =

Again, sorry, not in this version. In the future, it would be easy to add.

== Screenshots ==


== Changelog ==

= 1.3 =
New feature:
        - Allow posting an exact number of posts in "Surprise me" mode.
        - Moved menu to the POSTS menu group.
        - Display an error message if there are no drafts and stop.

= 1.2 =
New feature:
        - Undo posts from the most recent schedule. This will reset them for Scheduled (Future) to draft status
        - Doesn't undo any posts that are already published.

= 1.1 =
Bug fixes:
	- Proper calculation of time for intervals
	- Proper scheduling of future posts (big oops)
	- Update options page to remember settings after updating
	- Remove debugging code on update
	- Display message if there are no drafts to update

= 1.0 =
* First version released.
