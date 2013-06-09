=== Quick Event Manager ===

Contributors: 
Tags: contact form
Requires at least: 2.7
Tested up to: 3.4.1
Stable tag: trunk

Simple event manager. No messing about, just add events and a shortcode and the plugin does the rest for you. 

== Description ==

A really, really simple event creator. Just add new events and publish. The shortcode lists all the events. The settings page lets you select how you want the event displayed.

= Features =

*	Fill in the form to create an event
*	Use the settings page to change how it displays
*	Google maps options

= Developers plugin page =

[quick event list plugin](http://quick-plugins.com/quick-event-manager/).

== Screenshots ==
1. This an events list.
2. This an event post.
3. This is the admin (settings) page.
4. This the event editor.

== Installation ==

1.	Login to your wordpress dashboard.
2.	Go to 'Plugins', 'Add New' then search for 'quick event manager'.
4.	Select the plugin then 'Install Now'.
5.	Activate the plugin.
6.	Go to the plugin 'Settings' page to change how the events display.
7.	Go to your permalinks page and re-save to activate the custom posts.
8.	Add new events using the event editor on your dashboard
9.	To use the form in your posts and pages add the shortcode `[qem]`.

== Frequently Asked Questions ==

= How do I add a new event? =
In the main dashboard, click on 'event' then 'add new'.

= What's the shortcode? =
[qem]
If you just want old events use the shortcode [qem id="archive"]

= How can I change the date format? = 
Use the plugin settings page. You have the option of US format (MM/DD/YYYY) or the one the rest of the world uses (DD/MM/YYYY). You can also change the size and colour of the icon.

= How do I change the colours and things? =
Use the plugin settings page. You can't style individual events, they all look the same.

= Can I add more fields? =
No.

= Why not? =
Well OK yes you can add more fields if you want but you are going to have to fiddle about with the php file which needs a bit of care and attention. Everything you need to know is in the [wordpress codex](http://codex.wordpress.org/Writing_a_Plugin).

== Upgrade Notice ==
= 2.3 =
The sidebar widget now has an options to how many events are displayed.

== Changelog ==

= 2.3 =
*	Improved widget and shortcode options allows you to set the number of events to display
*	Fixed code to display all events (overrides reading settings)

=2.2 = 
*	Added option to display old events
*	Added option to display events in descending order (new to old)

= 2.1 =
*	Minor tweaks to the CSS
*	Fixed a float problem with the event title
*	Solved the problem with displaying today's events.
*	New events now show today's date

= 2.0 =
*	Added shortcode to list old events
*	Fixed CSS bug in the calendar icon
*	Fixed bug in the map marker
*	Optimised the code so it loads much faster

= 1.6 =
*	Allows captions on all options
*	Editable 'read more' label

= 1.5 =
*	Added comment support for events

= 1.4 =
*	Added sidebar widget

= 1.3 =
*	Added styling option for the month display

= 1.2 =
*	Added locale for the date format

= 1.1 =
*	Reomved a div that shouldn't be there and was causing alignemnt problems

= 1.0 =
*	Initial Issue