<?php
require_once OC_App::getAppPath('groupoffice').'/user_groupoffice.php';
require_once OC_App::getAppPath('groupoffice').'/group_groupoffice.php';

OC_User::registerBackend("GROUPOFFICE");
OC_User::useBackend("GROUPOFFICE");

$groupBackend  = new OC_GROUP_GROUPOFFICE();
OC_Group::useBackend($groupBackend);

OC::$CLASSPATH['OC\Files\Storage\Groupoffice'] = 'groupoffice/lib/groupofficestorage.php';
OCP\Util::connectHook('OC_Filesystem', 'setup', '\OC\Files\Storage\Groupoffice', 'setup');