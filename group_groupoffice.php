<?php
/**
 * Copyright (c) 2014 Intermesh BV
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later
 *
 * If you have questions write an e-mail to info@intermesh.nl
 */
namespace OCA\groupoffice;

class Group extends \OC_Group_Backend {

	public function getGroups($search = '', $limit = -1, $offset = 0) {
		$returnArray = array();
		
		$fp = \GO\Base\Db\FindParams::newInstance()						
						->start($offset)
						->searchQuery($search);
		
		if($limit>0)
			$fp->limit($limit);
		
		$stmt = \GO\Base\Model\Group::model()->find($fp);
		
		foreach($stmt as $group){
			$returnArray[]=$group->name;
		}

		return $returnArray;
	}

	public function getUserGroups($uid) {
		$groups = array();
			
		$user = \GO\Base\Model\User::model()->findSingleByAttribute('username', $uid);
		
		if($user){
			$stmt = $user->groups();

			foreach($stmt as $group){
				$groups[]=$group->name;
			}
		}
		
		return $groups;
	}

	public function groupExists($gid) {
		$group = \GO\Base\Model\Group::model()->findSingleByAttribute('name', $gid);
		
		return $group!=false;
	}

	public function inGroup($uid, $gid) {
		$user = \GO\Base\Model\User::model()->findSingleByAttribute('username', $uid);
		if(!$user)
			return false;
		
		$group = \GO\Base\Model\Group::model()->findSingleByAttribute('name', $gid);
		if(!$group)
			return false;
		
		$ug = \GO\Base\Model\UserGroup::model()->findByPk(array('user_id'=>$user->id, 'group_id'=>$group->id));
		
		return $ug!=false;
	}

	public function usersInGroup($gid, $search = '', $limit = -1, $offset = 0) {
		
		$users = array();
		
		$group = \GO\Base\Model\Group::model()->findSingleByAttribute('name', $gid);
		
		$findParams = \GO\Base\Db\FindParams::newInstance()
						->start($offset)
						->limit($limit)
						->searchQuery($search);
		
		$stmt = $group->users($findParams);
		foreach($stmt as $user){
			$users[]=$user->username;
		}
		
		return $users;
	}
}
