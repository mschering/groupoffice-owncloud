groupoffice-owncloud
====================

ownCloud plugin to connect with Group-Office

This is a fork, which additionally shows and syncs Shared Folders (for which you have at least read permissions in Group-Office) like this:

OwnCloud
– NotInGroupOfficeFolder1
– NotInGroupOfficeFolder1
– Groupoffice
– Groupoffice – SharedFolder1
– Groupoffice – SharedFolder2
– Groupoffice – ownFolder – myPrivateFolder1
– Groupoffice – ownFolder – myPrivateFolder2


Installation:

Make sure ownCloud and Group-Office are installed on the same server.
Put the "groupoffice" ownCloud app folder in the "apps" folder of ownCloud.

If Group-Office is not installed in /usr/share/groupoffice add this variable to
"config/config.php":

'groupoffice_root'=>'/path/to/groupoffice'

Tested with Group-Office 5.0.29 and ownClou 6.0.1

If you need to specify a Group-Office config.php location you can add:

'groupoffice_config'=>'/path/to/groupoffice/config.php'

Now you can install the Group-Office app in the app manager of ownCloud.
Enjoy!

Intermesh Group-Office Team   &   ALLMENDA Informatik
http://www.group-office.com       http://ALLMENDA.com/informatik

