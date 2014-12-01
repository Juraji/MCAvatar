MCAvatar
===
By Robin Kesseler @ GreenDog Solutions

Developer Notice
---
MCAvatar is free for use to all, though I call on your courtesy not to redistribute MCAvatar under a
different name.

The MCAvatar class has PHPDOC descriptions for advanced IDE's

Any problems with running the code?
send an Email to github@juraji.nl and I'll try to sort it out.

Bug reports are always welcome via the bug tracker at https://github.com/Juraji/MCAvatar/issues.

Overview
---
MCAvatar is an independent php-class capable of creating and showing MineCraft User Avatars.<br />
Basicly the script cuts out the "face" from the skin it gets from Minecraft.net, enlarges it and saves it to the defined folder.


Prerequisites
---
Minimal PHP version: `5.2`<br />
Minimal memory limit: `256MB`<br />

PHP modules:<br />
  - `GD for php`<br />
  - `DOMXML`<br />

Folders (with read/write permissions for Apache and PHP):<br />
  - A temporary folder for image conversions.<br />
  - An avatar folder in witch the script will save the finished avatars.<br />


Defining Members
---
First of all edit the "members.xml" file and add users.

"members.xml" contains a few examples on user group and user definitions, but I'll explain them here.

Notice: ALWAYS use lower-case characters and singular grammar, since casing and pluralism is handled within the MCAvatar script itself! EXCEPT for the usernames themselves, since these are case-sensitive!

Create user groups using the tag "<uGroup>", This can be an unlimited number of groups, like so:<br />
  `<uGroup>admin</uGroup>
  <uGroup>moderator</uGroup>
  <uGroup>member</uGroup>
  <uGroup>ANYTHING</uGroup>
  <uGroup>ANYTHING2</uGroup>`

Users are divided into the user groups by their tags in the xml-file.
A "admin" is to be placed within the <admin>-tags, like so:<br />
  `<admin>USERNAME</admin>`

The same goes for "moderators" and "members" etc.:<br />
  `<moderator>USERNAME</moderator>
  <member>USERNAME</member>`

All groups can contain an unlimited number of users, for example when there are two admins, define them like so:<br />
  `<admin>ADMIN1</admin>
  <admin>ADMIN2</admin>`

Empty user groups will be omitted when building the avatar page.


Using the Class in your scripts
---
The MCAvatar class is coded objectively, this means you have to derive an object from it then use the object to achieve goals.

Basicly this involves including "mcavatar.class.php" in your own PHP script then create and object from the class like so:<br />
  `$MCA = new MCAvatar(...`

The ClassConstructor expects four variables passed:<br />
  - `$membersXMLPath`: The relative path to your "members.xml".<br />
  - `$tmpPath`: The relative path to your temporary folder.<br />
  - `$avatarImagePath`: The relative path to your avatar folder.<br />
  - `$notice`: A string of text added to the avatar page after building (optional).<br />

The complete line follows:<br />
  `$MCA = new MCAvatar('path/to/members.xml',
                       'path/to/temporary folder',
                       'path/to/avatar folder',
                       'Just a string of text telling your users about stuff');`


Using the Class methods
---
The class has a few methods you can call to make the class manipulate the avatars.

The following methods are public and you can call them whenever you want.<br />
(call the methods by pointing to the MCAvatar object like so: $MCA->METHOD-NAME())

`mca_update_avatars()`:<br />
  Get user skins from Minecraft.net, using "members.xml", and process these into avatars.
  It is not recommended to use this method on every page build, but maybe put it into a cron job, for timely updates, because this method uses quite a lot of system resources and takes a while to complete.

`mca_build_page()`:<br />
  This method builds and returns a HTML formatted list of members you've defined in "members.xml" along with their respective avatars and groups.<br />
  Empty user groups will be omitted when building the avatar page.

`mca_messages()`:<br />
  returns an array with script generated messages for debugging.

`mca_set_image_settings()`:<br />
  Change output image settings.

`mca_update_user_avatar()`:<br />
  Update the avatar for a single user and returns the relative file path to the new file.

Styling your MCAvatar page
---
MCAvatar is supplied with a CSS file to get you started on styling your MCAvatar page.<br />
Though everything is covered, the only thing I cannot cover is the custom user groups.<br />

All user group titles are supplied with two classes, one always being "member-list-title" and the other the respective usergroup name.<br />
The colors and styling for every custom user group can be added to the CSS by referring to these classes. So, for instance, the "donator" usergroup, wich isn't default, can be styled by refering to ".member-list-title.donators". Notice the plural usage.

So if we want the "donator" user group title to be orange, put the following into the CSS file:

`.member-list-title.donators {
  color: rgb(255, 128, 128);
}`