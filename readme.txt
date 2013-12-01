=== Quick Event Manager ===

Contributors: 
Tags: event manager, calendar
Requires at least: 2.7
Tested up to: 3.6
Stable tag: trunk

Simple event manager. No messing about, just add events and a shortcode and the plugin does the rest for you. 

== Description ==

A really, really simple event creator. Just add new events and publish. The shortcode lists all the events. The settings pages let you select how you want the event displayed.

= Features =

*	Event posts created from your dashboard
*	Loads of layout and styling options
*	Show events as a list or a calendar
*	Built in event registration form
*	Event maps
*	Widgets and Shortcode options
*	Archive post options

= Developers plugin page =

[quick event list plugin](http://quick-plugins.com/quick-event-manager/).

= Translations = 

French: [Dan (chouf1](http://bp-fr.net)

= Demo Pages =

[Event list](http://quick-plugins.com/the-event-list/).
[Calendar](http://quick-plugins.com/event-calendar/).

== Screenshots ==

1. This is an example of an events post.
2. This is the list of events.
3. This the event editor. 
4. The styling editor.
5. Setting up the calendar.

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
If you just want a calendar use the shortcode [qemcalendar"]

= How do I change the colours and things? =
Use the plugin settings page. You can't style individual events, they all look the same.

= Can I add more fields? =
No.

= Why not? =
Well OK yes you can add more fields if you want but you are going to have to fiddle about with the php file which needs a bit of care and attention. Everything you need to know is in the [wordpress codex](http://codex.wordpress.org/Writing_a_Plugin).

== Changelog ==

= 5.0 =
*	Extracted scrpts and styles to external files
*	Added mini form for event registraton
*	New field to add end date to events
*	Option to display end date on event list
*	Cleaned out a whole bunch of code

= 4.2 =
*	Added option to display anchor text on website links
*	Awsome French translation
*	Calender can start the week on a Sunday or Monday
*	Duplicate weekly or monthly events

= 4.1 =
*	Fixed an issue with categories

= 4.0 =
*	Calendars! You can now display events in a calendar

= 3.0 =
*	New Settings interface
*	Loads of styling options
*	Improved calendar icon options
*	Date picker

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