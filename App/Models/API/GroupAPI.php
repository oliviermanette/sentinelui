<?php

namespace App\Models\API;

use App\Utilities;


class GroupAPI 
{


    public static function listAllGroupsFromAPI()
    {
        $url = "https://api.objenious.com/v1/groups";
        $results_api = API::CallAPI("GET", $url);
        return $results_api;
    }

    public static function displayGroupInfoFromAPI($id_group)
    {
        $url = "https://api.objenious.com/v1/groups/" . $id_group;
        $results_api = API::CallAPI("GET", $url);

        return $results_api;
    }

    public static function deleteGroupFromAPI($id_group)
    {
        $url = "https://api.objenious.com/v1/groups/" . $id_group;
        $results_api = API::CallAPI("DELETE", $url);

        return $results_api;
    }

    public static function createGroupUsingAPI($name, $parent_group_id)
    {
        $url = "https://api.objenious.com/v1/groups?name=" . $name . "&parent_group_id=" . $parent_group_id;
        $results_api = API::CallAPI("POST", $url);
    }
}
