  __  __  ____    _             _             
 |  \/  |/ ___|  / \__   ____ _| |_ __ _ _ __ 
 | |\/| | |     / _ \ \ / / _` | __/ _` | '__|
 | |  | | |___ / ___ \ V / (_| | || (_| | |   
 |_|  |_|\____/_/   \_\_/ \__,_|\__\__,_|_| by Robin Kesseler

/* Developer Notice ***********************************************************************************/
MCAvatar is free for use to all, though I call on your courtesy not to redistribute MCAvatar under a
different name.

The MCAvatar class has PHPDOC descriptions for advanced IDE's

Any problems with running the code?
send an Email to github@juraji.nl and I'll try to sort it out.

Bug reports are always welcome via the bugtracker at https://github.com/Juraji/MCAvatar/issues.

/* Overview *******************************************************************************************/
MCAvatar is an independent php-class capable of creating and showing MineCraft User Avatars.
Basicly the script cuts out the "face" from the skin it gets from Minecraft.net, enlarges it and saves
it to the defined folder.


/* Prerequisites **************************************************************************************/
Minimal PHP version: 5.2
Minimal memorylimit: 256MB

PHP modules:
  - GD for php
  - DOMXML

Folders (with read/write permissions for Apache and PHP):
  - A temporary folder for image conversions.
  - An avatar folder in witch the script will save the finished avatars.


/* Defining Members ***********************************************************************************/
First of all edit the "members.xml" file and add users.

"members.xml" contains a few examples on usergroup and user definitions, but I'll explain them here.

Notice: ALWAYS use lower-case characters and singular grammar, since casing and pluralism is
handled within the MCAvatar script itself! EXCEPT for the usernames themselves, since these are case-sensitive!

Create usergroups using the tag "<ugroup>", This can be an unlimited number of groups, like so:
  <ugroup>admin</ugroup>
  <ugroup>moderator</ugroup>
  <ugroup>member</ugroup>
  <ugroup>ANYTHING</ugroup>
  <ugroup>ANYTHING2</ugroup>

Users are divided into the usergroups by their tags in the xml-file.
A "admin" is to be placed within the <admin>-tags, like so:
  <admin>USERNAME</admin>

The same goes for "moderators" and "members" etc.:
  <moderator>USERNAME</moderator>
  <member>USERNAME</member>

All groups can contain an unlimited number of users, for example when there are two admins,
define them like so:
  <admin>ADMIN1</admin>
  <admin>ADMIN2</admin>

Empty usergroups will be ommited when building the avatar page.


/* Using the Class in your scripts ********************************************************************/
The MCAvatar class is coded objectivly, this means you have to derive an object from it then use the
object to achieve goals.

Basicly this involves including "mcavatar.class.php" in your own PHP script then create and object from
the class like so:
  $MCA = new MCAvatar(...

The ClassConstructor expects four variables passed:
  - $membersxmlpath: The relative path to your "members.xml".
  - $tmppath: The relative path to your temporary folder.
  - $avatarimagepath: The relative path to your avatar folder.
  - $notice: A string of text added to the avatar page after building (optional)

The complete line follows:
  $MCA = new MCAvatar('path/to/members.xml',
                      'path/to/temporaryfolder',
                      'path/to/avatarfolder',
                      'Just a string of text telling your users about stuff');


/* Using the Classmethods *****************************************************************************/
The class has a few methods you can call to make the class manipulate the avatars.

The following methods are public and you can call them whenever you want.
(call the methods by pointing to the MCAvatar object like so: $MCA->METHODNAME())

mca_update_avatars():
  Get userskins from Minecraft.net, using "members.xml", and process these into avatars.
  It is not recommended to use this method on every pagebuild, but maybe put it into a cronjob, for
  timely updates, because this method uses quite a lot of systemresources and takes a while to complete.

mca_build_page():
  This method builds and returns a HTML formatted list of members you've defined in "members.xml" along
  with their respective avatars and groups.
  Empty usergroups will be ommited when building the avatar page.

mca_messages():
  returns an array with scriptgenerated messages for debugging.

mca_set_image_settings():
  Change output image settings.

mca_update_user_avatar():
  Update the avatar for a single user and returns the relative filepath to the new file.

/* Styling your MCAvatar page *************************************************************************/
MCAvatar is supplied with a CSS file to get you started on styling your MCAvatar page.
Though everything is covered, the only thing I cannot cover is the custum usergroups.

All usergroup titles are supplied with two classes, one always being "member-list-title" and the other the
respective usergroup name. the colors and styling for every custom usergroup can be added to the CSS by
refering to these classes. So, for instance, the "donator" usergroup, wich isn't default, can be styled
by refering to ".member-list-title.donators". Notice the plural usage.

So if we want the "donator" usergroup title to be orange, put the following into the CSS file:

.member-list-title.donators {
  color: rgb(255, 128, 128);
}