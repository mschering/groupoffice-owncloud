<?php
require_once(dirname(__FILE__).'/../user_groupoffice.php');
require_once(dirname(__FILE__).'/../group_groupoffice.php');
$userBackend  = new \OCA\groupoffice\User();
$groupBackend  = new \OCA\groupoffice\Group();
OC_User::useBackend($userBackend);
OC_Group::useBackend($groupBackend);
