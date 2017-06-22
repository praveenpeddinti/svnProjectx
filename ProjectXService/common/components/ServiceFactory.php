<?php

namespace common\components;

use common\service\StoryService;
use common\service\CollaboratorService;
use common\service\TimeReportService;
use common\service\BucketService;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ServiceFactory {

    private static $inst_story_service = null;
    private static $inst_collaborator_service = null;
    private static $inst_timereport_service = null;
    private static $inst_bucket_service = null;
    private function __construct() {
        
    }

    public static function getStoryServiceInstance() {
        try {
            if (!self::$inst_story_service) {
                self::$inst_story_service = new StoryService();
            }
            return self::$inst_story_service;
        } catch (Exception $ex) {
            Yii::log("ServiceFactory:getStoryServiceInstance::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

    public static function getCollaboratorServiceInstance() {
        try {
            if (!self::$inst_collaborator_service) {
                self::$inst_collaborator_service = new CollaboratorService();
            }
            return self::$inst_collaborator_service;
        } catch (Exception $ex) {
            Yii::log("ServiceFactory:getCollaboratorServiceInstance::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

    public static function getTimeReportServiceInstance() {
        try {
            if (!self::$inst_timereport_service) {
                self::$inst_timereport_service = new TimeReportService();
            }
            return self::$inst_timereport_service;
        } catch (Exception $ex) {
            Yii::log("ServiceFactory:getTimeReportServiceInstance::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
    public static function getBucketServiceInstance() {
        try {
            if (!self::$inst_bucket_service) {
                self::$inst_bucket_service = new BucketService();
            }
            return self::$inst_bucket_service;
        } catch (Exception $ex) {
            Yii::log("ServiceFactory:getBucketServiceInstance::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

}
