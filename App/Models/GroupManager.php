<?php

namespace App\Models;
use PDO;
use App\Controllers\ControllerDataObjenious;
use \App\Token;
use \App\Mail;
use \Core\View;


class GroupManager extends \Core\Model
{

    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }

    }

    public function listAllGroupsFromAPI()
    {
        $url = "https://api.objenious.com/v1/groups";
        $results_api = ControllerDataObjenious::CallAPI("GET", $url);

        return $results_api;
    }

    public function displayGroupInfoFromAPI($id_group)
    {
        $url = "https://api.objenious.com/v1/groups/".$id_group;
        $results_api = ControllerDataObjenious::CallAPI("GET", $url);

        return $results_api;
    }

    public function deleteGroupFromAPI($id_group)
    {
        $url = "https://api.objenious.com/v1/groups/" . $id_group;
        $results_api = ControllerDataObjenious::CallAPI("DELETE", $url);

        return $results_api;
    }

    public function createGroupUsingAPI($name, $parent_group_id)
    {
        $url = "https://api.objenious.com/v1/groups?name=".$name."&parent_group_id=".$parent_group_id;
        $results_api = ControllerDataObjenious::CallAPI("POST", $url);

    }



}