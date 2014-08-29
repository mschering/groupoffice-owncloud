# groupoffice-owncloud

ownCloud plugin to connect with Group-Office


Make sure ownCloud and Group-Office are installed on the same server.
Put the "groupoffice" ownCloud app folder in the "apps" folder of ownCloud.

If Group-Office is not installed in /usr/share/groupoffice add this variable to
"config/config.php":

'groupoffice_root'=>'/path/to/groupoffice'


If you need to specify a Group-Office config.php location you can add:

'groupoffice_config'=>'/path/to/groupoffice/config.php'

Now you can install the Group-Office app in the app manager of ownCloud.
after this activate to "External Storage Support" App as well

Enjoy!

Intermesh Group-Office Team

http://www.group-office.com

## Troubleshooting

If the Group-Office folder is not displayed in ownCloud remove the mount.json file
it will recreate after the first login


