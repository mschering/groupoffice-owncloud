# groupoffice <-> owncloud connector

ownCloud plugin to connect with Group-Office

This is a fork, which additionally shows and syncs Shared Folders (for which you have at least read permissions in Group-Office) like this:

* OwnCloud
* NotInGroupOfficeFolder1
* NotInGroupOfficeFolder1
* Groupoffice
* Groupoffice – SharedFolder1
* Groupoffice – SharedFolder2
* Groupoffice – ownFolder – myPrivateFolder1
* Groupoffice – ownFolder – myPrivateFolder2


## Installation

Put this line into a ssh shell to clone this repository.

    git clone https://github.com/horfic/groupoffice-owncloud.git groupoffice
  
Put the now cloned "groupoffice" folder into ownCloud "apps" folder.

#### Make sure ownCloud and Group-Office are installed on the same server.

  If Group-Office is **not** installed in **/usr/share/groupoffice** add this variable to the config array of
  "/path/to/OwnCloud/config/config.php" configuration file.

    'groupoffice_root'=>'/path/to/groupoffice'

Tested with Group-Office 5.0.75 and ownCloud 7.0.1

  If you need to specify a Group-Office config.php location you can add:

    'groupoffice_config'=>'/path/to/groupoffice/config.php'

**Now you can install the Group-Office app in the app manager of ownCloud.**
Enjoy!

### Keep in mind

* the user- and accessmanagement stays in groupoffice
* no users are created within owncloud, only access is granted
* sharing folders in owncloud to groupmembers of groupoffice does not work. This only works in groupoffice and the folders are then accessible in owncloud.

---

Intermesh Group-Office Team   &   ALLMENDA Informatik
http://www.group-office.com       http://ALLMENDA.com/informatik

-
