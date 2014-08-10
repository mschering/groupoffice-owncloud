<?php

class OC_USER_GROUPOFFICE extends OC_User_Backend
{

    private $_user = array();

    public function __construct()
    {


        $groupoffice_root = rtrim(\OC_Config::getValue("groupoffice_root", "/usr/share/groupoffice"), '/');

        $groupoffice_config = \OC_Config::getValue("groupoffice_config");
        if (!empty($groupoffice_config))
            define('GO_CONFIG_FILE', $groupoffice_config);

        require_once($groupoffice_root . '/GO.php');

        //create group-office mount.json file
        $datadir = \OC_Config::getValue("datadirectory", \OC::$SERVERROOT . "/data");
        $mountFile = $datadir . '/mount.json';

        if (!file_exists($mountFile)) {
            $mountConfig = array(
                'user' => array(
                    'all' => array(
                        '/$user/files/Groupoffice' =>
                            array(
                                'class' => '\OC\Files\Storage\Groupoffice',
                                'options' => array(
                                    'user' => '$user'
                                )
                            ),

                    )
                )
            );

            file_put_contents($mountFile, json_encode($mountConfig));
        }
    }

    public function deleteUser($uid)
    {
        // Can't delete user
        return false;
    }

    public function setPassword($uid, $password)
    {
        // We can't change user password
        return false;
    }

    public function checkPassword($uid, $password)
    {

        $this->_user[$uid] = GO::session()->login($uid, $password, false);

        if (!$this->_user[$uid]) {
            return false;
        } else {

            //workaround bug in ownCloud
            $cache = OC_User::getHome($uid) . '/cache';
            if (!is_dir($cache))
                mkdir($cache, 0755, true);

            return $uid;
        }
    }

    private function _getUser($username)
    {
        if (!isset($this->_user[$username])) {
            $this->_user[$username] = GO_Base_Model_User::model()->findSingleByAttribute('username', $username);
        }


        return $this->_user[$username];
    }

    public function userExists($uid)
    {
        return $this->_getUser($uid) != false;
    }

    public function hasUserListings()
    {
        return true;
    }

    public function getUsers($search = '', $limit = 10, $offset = 0)
    {
        $returnArray = array();

        $fp = GO_Base_Db_FindParams::newInstance()
            ->limit($limit)
            ->start($offset)
            ->searchQuery($search);

        $stmt = GO_Base_Model_User::model()->find($fp);
        foreach ($stmt as $user) {
            $returnArray[] = $user->username;
        }

        return $returnArray;
    }

    public function getHome($uid)
    {

        $home = new GO_Base_Fs_Folder(GO::config()->file_storage_path . 'owncloud/' . $this->_getUser($uid)->username);
        $home->create();

        return $home->path();
    }

    public function getDisplayName($uid)
    {
        return $this->_getUser($uid)->name;
    }

}
