<?php

namespace App\Models;

use PDO;
use \App\Token;
use \App\Mail;
use \Core\View;


class GroupManager extends \Core\Model
{
    public function getAllPermissions($groupId)
    {
        $sql = "SELECT group_permission.id, group_permission.name FROM `group_permission`
                LEFT JOIN group_role_permission ON group_role_permission.group_permission_id = group_permission.id
                LEFT JOIN group_roles ON group_roles.id = group_role_permission.group_role_id
                LEFT JOIN group_name ON group_name.group_role=group_roles.id
                WHERE group_name.group_id = :group_id";

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':group_id', $groupId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $permissionsArr = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $permissionsArr;
        }
    }

    public function getRole($groupId)
    {
        $sql = "SELECT group_roles.id, group_roles.name FROM group_roles
                LEFT JOIN group_name ON group_name.group_role = group_roles.id
                WHERE group_name.group_id = :group_id";

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':group_id', $groupId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $role = $stmt->fetch(PDO::FETCH_ASSOC);

            return $role;
        }
    }
}
