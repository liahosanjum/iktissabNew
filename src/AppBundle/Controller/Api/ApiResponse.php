<?php
/**
 * Created by PhpStorm.
 * User: s.aman
 * Date: 3/2/17
 * Time: 3:19 PM
 */

namespace AppBundle\Controller\Api;


use Symfony\Component\HttpFoundation\Request;

class ApiResponse
{
    public static function IsTemplateRequested(Request $request){
        if($request->headers->get("Rest-Template", "false") == "true"){
            return true;
        }

        return false;

    }

    /**
     * @param $status
     * @param $data
     * @return array
     */
    public static function Template($status, $data){
        return array("Success"=>"bool", "Status"=>$status, "Data"=>$data);
    }

    /**
     * @param $success
     * @param $status
     * @param $data
     * @return array
     */
    public static function Response($success, $status, $data){
        return array("Success"=>$success, "Status"=>$status, "Data"=>$data);
    }
}