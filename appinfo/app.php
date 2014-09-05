<?php
require_once(dirname(__FILE__).'/../user_groupoffice.php');
require_once(dirname(__FILE__).'/../group_groupoffice.php');

$userBackend  = new \OCA\groupoffice\User();
OC_User::useBackend($userBackend);

$groupBackend  = new \OCA\groupoffice\Group();
OC_Group::useBackend($groupBackend);

OC::$CLASSPATH['OC\Files\Storage\Groupoffice'] = 'groupoffice/lib/groupofficestorage.php';
OCP\Util::connectHook('OC_Filesystem', 'setup', '\OC\Files\Storage\Groupoffice', 'setup');
OCP\Util::connectHook('OC_Filesystem', 'post_initMountPoints', '\OC\Files\Storage\Groupoffice', 'setup');
