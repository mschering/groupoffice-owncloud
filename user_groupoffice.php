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

class User extends \OC_User_Backend {

	private $_user=array();
		
	private $_groupoffice_mount;
	

	public function __construct() {
		
		$groupoffice_root = rtrim(\OC_Config::getValue("groupoffice_root", "/usr/share/groupoffice"),'/');
		
		$groupoffice_config = \OC_Config::getValue("groupoffice_config");
		if(!empty($groupoffice_config))
			define('GO_CONFIG_FILE', $groupoffice_config);
		
		$this->_groupoffice_mount = '/'.trim(\OC_Config::getValue("groupoffice_mount", "ownCloud"),' /');
		
		require_once($groupoffice_root.'/GO.php');
		
		//create group-office mount.json file
		$datadir = \OC_Config::getValue("datadirectory", \OC::$SERVERROOT . "/data");
		$mountFile = $datadir.'/mount.json';
		
		if(!file_exists($mountFile)){
			$mountConfig =  array(
				'user'=>array(
					'all'=>array(
						'/$user/files/Group-Office'=>array(
							'class'=>"\\OC\\Files\\Storage\\Local",
							'options'=>array(
								'datadir'=>\GO::config()->file_storage_path.'users/$user/'.$this->_groupoffice_mount
							),
							'priority'=>150
						)
					)
				)
			);

			file_put_contents($mountFile, json_encode($mountConfig));
		}
	}
	
	

	public function deleteUser($uid) {
		// Can't delete user
		return false;
	}

	public function setPassword($uid, $password) {
		// We can't change user password
		return false;
	}

	public function checkPassword($uid, $password) {

		$this->_user[$uid] = \GO::session()->login($uid, $password, false);

		if (!$this->_user[$uid]) {
			return false;
		} else {
			
			//workaround bug in ownCloud			
			$cache = \OC_User::getHome($uid).'/cache';			
			if(!is_dir($cache))
				mkdir($cache,0755,true);
			
			//make sure ownCloud folder exists in Group-Office
			$folder = new \GO\Base\Fs\Folder(\GO::config()->file_storage_path.'users/'.$uid.$this->_groupoffice_mount);
			$folder->create();

			
			return $uid;
		}
	}

	/**
	 * 
	 * @param string $username
	 * @return GO\Base\Model\User
	 */
	private function _getUser($username){
		if(!isset($this->_user[$username])){
				$this->_user[$username] = \GO\Base\Model\User::model()->findSingleByAttribute('username', $username);
		}
		
			
		return $this->_user[$username];
	}
	/*
	 * we don´t know if a user exists without the password. so we have to return true all the time
	 */

	public function userExists($uid) {

		
		return $this->_getUser($uid) != false;
	}

	/**
	 * @return bool
	 */
	public function hasUserListings() {
		return true;
	}

	/*
	 * we don´t know the users so all we can do it return an empty array here
	 */

	public function getUsers($search = '', $limit = 10, $offset = 0) {
		$returnArray = array();
		
		$fp = \GO\Base\Db\FindParams::newInstance()
						->limit($limit)
						->start($offset)
						->searchQuery($search);
		
		$stmt = \GO\Base\Model\User::model()->find($fp);
		foreach($stmt as $user){
			$returnArray[]=$user->username;
		}

		return $returnArray;
	}
	
	public function getHome($uid) {
		
		$home = new \GO\Base\Fs\Folder(\GO::config()->file_storage_path.'owncloud/'.$this->_getUser($uid)->username);
		$home->create();	
		
		return $home->path();
	}
	
	public function getDisplayName($uid) {
		return $this->_getUser($uid)->name;
	}

}
