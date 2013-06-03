<?php
require_once(dirname(__FILE__).'/../user_groupoffice.php');
require_once(dirname(__FILE__).'/../group_groupoffice.php');
$userBackend  = new OC_USER_GROUPOFFICE();
$groupBackend  = new OC_GROUP_GROUPOFFICE();
OC_User::useBackend($userBackend);
OC_Group::useBackend($groupBackend);