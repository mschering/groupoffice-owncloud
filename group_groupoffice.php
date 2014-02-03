<?php

class OC_GROUP_GROUPOFFICE extends OC_Group_Backend
{

    public function getGroups($search = '', $limit = -1, $offset = 0)
    {
        $returnArray = array();

        $fp = GO_Base_Db_FindParams::newInstance()
            ->start($offset)
            ->searchQuery($search);

        if ($limit > 0)
            $fp->limit($limit);

        $stmt = GO_Base_Model_Group::model()->find($fp);

        foreach ($stmt as $group) {
            $returnArray[] = $group->name;
        }

        return $returnArray;
    }

    public function getUserGroups($uid)
    {
        $groups = array();

        $user = GO_Base_Model_User::model()->findSingleByAttribute('username', $uid);

        if ($user) {
            $stmt = $user->groups();

            foreach ($stmt as $group) {
                $groups[] = $group->name;
            }
        }

        return $groups;
    }

    public function groupExists($gid)
    {
        $group = GO_Base_Model_Group::model()->findSingleByAttribute('name', $gid);

        return $group != false;
    }

    public function inGroup($uid, $gid)
    {
        $user = GO_Base_Model_User::model()->findSingleByAttribute('username', $uid);
        if (!$user)
            return false;

        $group = GO_Base_Model_Group::model()->findSingleByAttribute('name', $gid);
        if (!$group)
            return false;

        $ug = GO_Base_Model_UserGroup::model()->findByPk(array('user_id' => $user->id, 'group_id' => $group->id));

        return $ug != false;
    }

    public function usersInGroup($gid, $search = '', $limit = -1, $offset = 0)
    {

        $users = array();

        $group = GO_Base_Model_Group::model()->findSingleByAttribute('name', $gid);

        $findParams = GO_Base_Db_FindParams::newInstance()
            ->start($offset)
            ->limit($limit)
            ->searchQuery($search);

        $stmt = $group->users($findParams);
        foreach ($stmt as $user) {
            $users[] = $user->username;
        }

        return $users;
    }
}
