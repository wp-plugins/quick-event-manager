=== Quick Event Manager ===

Contributors: 
Tags: event manager, calendar
Requires at least: 4.0
Tested up to: 4.2
Stable tag: trunk

Simple event manager. No messing about, just add events and a shortcode and the plugin does the rest for you. 

== Description ==

A really, really simple event creator. Just add new events and publish. The shortcode lists all the events. The settings pages let you select how you want the event displayed.

= Features =

*   Event posts created from your dashboard
*   Loads of layout and styling options
*   Show events as a list or a calendar
*   Built in event registration form
*   Accepts payments and IPN
*   Download events to your calendar
*   Download attendee report to email/CSV
*   Event maps
*   Widgets and lots of shortcode options

= Developers plugin page =

[Quick Event Manager](http://quick-plugins.com/quick-event-manager/).

= Translations = 

French: [Bernard](http://sorties-en-creuse.fr/)
Czech: [Augustin](http://zidek.eu/)
Russian: [Alexey](http://hakuna-matata.spb.ru/)

If you want the plugin in your own language you can use a plugin like [loco translate](https://wordpress.org/plugins/loco-translate/) to create the necessary translation files. If you do create a translation please snd me the files so I can add them to the plugin repository.

= Demo Pages =

[Event list](http://quick-plugins.com/the-event-list/).
[Calendar](http://quick-plugins.com/event-calendar/).
[Guest Events](http://quick-plugins.com/quick-event-manager/guest-event-plugin-test/).

== Screenshots ==

1. This is an example of an event post.
2. This is the list of events.
3. This the event editor. 
4. The styling editor.
5. Setting up the calendar.

== Installation ==

1.  Login to your wordpress dashboard.
2.  Go to 'Plugins', 'Add New' then search for 'quick event manager'.
4.  Select the plugin then 'Install Now'.
5.  Activate the plugin.
6.  Go to the plugin 'Settings' page to change how the events display.
7.  Go to your permalinks page and re-save to activate the custom posts.
8.  Add new events using the event editor on your dashboard
9.  To use the form in your posts and pages add the shortcode `[qem]`.

== Frequently Asked Questions ==

= How do I add a new event? =
In the main dashboard, click on 'event' then 'add new'.

= What's the shortcode? =
[qem]
If you just want a calendar use the shortcode [qemcalendar]

= How do I change the colours and things? =
Use the plugin settings page. You can't style individual events, they all look the same.
But you can change lots of colours on the calendar

= Can I add more fields? =
No.

= Why not? =
Well OK yes you can add more fields if you want but you are going to have to fiddle about with the php file which needs a bit of care and attention. Everything you need to know is in the [wordpress codex](http://codex.wordpress.org/Writing_a_Plugin).

== Changelog ==

= 6.3 =
*   Improved confirmation email
*   Bug fixes for calendar download
*   More categories
*   Updated linking options
*   Shortcode to only show today's events.
*   Shortcode for single events
*   Posting date can now be the same as the event date
*   Extra info field on the registration form
*   Multiple categories in shortcode
*   Option to float the registration form on the right
*   Full editing autoresponder

= 6.2 =
*   Options to remove links from event list
*   Month formatting on the event list
*   Add category name options to event
*   Build email confirmation message
*   Updated Payment options
*   Instant Payment Notification
*   Add calendar download to event list
*   Fixed Missing widget bug
*   Various styling bugs sorted

= 6.1 =
*   Added option to show numbers attending
*   Option to remove duplicate dates
*   Option to add captions to date icons
*   Widget settings for the calender event key
*   Styling options for cell spacing and borders on the calender
*   Updates to payment options
*   Display number attending on the dashboard event list
*   Allow multiple registrations
*   Allow attendees to deregister
*   Fixed fullevent shortcode bug
*   Bug fix in Author selection
*   Stripslashes bug fix
*   Fix to event field ordering

= 6.0 =
*   Akismet spam filtering
*   Sortable registration form fields
*   User defined fields
*   Open events in a lightbox (a bit iffy this one)
*   Option to display maps and images
*   Integrate paypal into the registration form
*   Option to limit registration to registered users.
*   Category lists and links
*   Category selection in Widget
*   Fixed the date language problem in the event editor
*   Other minor bug fixes and coding cockups
*   Probably more options that I've fogotten about

= 5.12 =
*   Fixed ics bug
*   Added 'hide event' option
*   Fixed bug in month/year seperators
*   Option to combine start and end dates if they are in the same month
*   You can now open external links in new window
*   Improved registration reporting
*   Added navigation icon options
*   Counter now resets properly when you delete registrants

= 5.11 =
*   Added new 'organiser' field
*   More shortcodes options
*   Option to display timezones
*   Event templates for your theme
*   New 'not attending' field on the registration form
*   Option to clear calendar icon styles (just display a plain date)
*   More options on date ordering in the icon

= 5.10 =
*   Bug fixes to the sidebar widget
*   Added ICS download option.
*   Places available counter now displays on payment forms
*   Delete registered people
*   Copy event details to registrant
*   Add titles to both sidebar widgets
*   Fixed 'pm' bug when calculating times

= 5.9.1 =
*   Bug fixes to the Event registration reporting
*   Added feature to display maps on event list.

= 5.9 =
*   Event registration reporting and downloads
*   Added widget feature to link to full event list

= 5.8 =
*   Option to edit 'prev' and 'next' anchor text in calendar
*   Added shortcode to display images in event list
*   Option to display month and year as separators in the event list
*   Events now list by date and start time
*   Lots of shortcode options to display list by month and year
*   Fixed 'number of places bug'
*   Improved registration form validation
*   Option to add 'go back to event list' link on events.
*   Calendar widget (might need a bit more work)

= 5.7.1 =
*   Bug fix for the Registration form and Counter
*   Option to link to an external website rather than the event post

= 5.7 =
*   Event key on the calendar
*   Fixed calendar bug if permalinks aren't used
*   Styling options to fix padding/margins
*   Attendee Counter

= 5.6 =
*   Repeat Events
*   More styling options on the Calendar
*   Option to show continuing events on the calendar
*   Set image size on event list and event
*   Registration form auto completes is user is logged in

= 5.5 =
*   Optional payment form
*   Whole bunch of ways to display dates
*   Show calendar categories
*   Fixed number of bugs with event images
*   More fields on the registration form
*   Cleaned up a lot of the code

= 5.4 =
*   Display atttendees on events
*   Fixed issue with clearind event image
*   Option to display old events on the calendar

= 5.3 =
*	Introduction of a color picker
*   Add images to events
*   Display start and end dates side by side
*   Colour coding for event
*   Better event styling
*   Cleaned out a load of duplicate code
*	Better Wordpress 3.8 support

= 5.2 =
*	Removed the need to resave permalinks

= 5.1 =
*	Updated for Wordpress 3.8 compatibility
*	Added colour options for calendar events
*	Improved event title display on calendar
*	Code reduction

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

= 2.2 = 
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
*	Removed a div that shouldn't be there and was causing alignemnt problems

= 1.0 =
*	Initial Issue