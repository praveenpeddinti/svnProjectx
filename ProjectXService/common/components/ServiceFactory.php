<?php
namespace  common\components;
use common\service\StoryService;
use common\service\CollaboratorService;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class ServiceFactory 
{
 private static $inst_story_service=null;  
 private static $inst_collaborator_service=null; 

 private function __construct() {
 }

public static function getStoryServiceInstance() {
    try{
    if(!self::$inst_story_service) {
        self::$inst_story_service = new StoryService();
    }
    return self::$inst_story_service;
    } catch (Exception $ex) {
        Yii::log("ServiceFactory:getSkiptaUserServiceInstance::".$ex->getMessage()."--".$ex->getTraceAsString(), 'error', 'application');
    }
}
public static function getCollaboratorServiceInstance() {
    try{
    if(!self::$inst_collaborator_service) {
        self::$inst_collaborator_service = new CollaboratorService();
    }
    return self::$inst_collaborator_service;
    } catch (Exception $ex) {
        Yii::log("ServiceFactory:getSkiptaUserServiceInstance::".$ex->getMessage()."--".$ex->getTraceAsString(), 'error', 'application');
    }
}
 

    
 }
