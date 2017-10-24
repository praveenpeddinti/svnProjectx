<?php

namespace common\components;

use common\service\StoryService;
use common\service\CollaboratorService;
use common\service\TimeReportService;
use common\service\BucketService;
use common\service\ProjectService;
use yii\base\ErrorException;

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
    private static $inst_project_service = null;
    
    private function __construct() {
        
    }
    /**
     * 
     * @return type
     * @throws ErrorException
     * @Description Checks if the instance of StoryService Class exists or not returns the instance. If no instance exists, returns a new one
     */
    public static function getStoryServiceInstance() {
        try {
            if (!self::$inst_story_service) {
                self::$inst_story_service = new StoryService();
            }
            return self::$inst_story_service;
        }catch (\Throwable $ex) {
            Yii::error("ServiceFactory:getStoryServiceInstance::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    /**
     * 
     * @return type
     * @throws ErrorException
     * @Description Checks if the instance of CollaboratorService Class exists or not returns the instance. If no instance exists, returns a new one
     */
    public static function getCollaboratorServiceInstance() {
        try {
            if (!self::$inst_collaborator_service) {
                self::$inst_collaborator_service = new CollaboratorService();
            }
            return self::$inst_collaborator_service;
        } catch (\Throwable $ex) {
            Yii::error("ServiceFactory:getCollaboratorServiceInstance::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    /**
     * 
     * @return type
     * @throws ErrorException
     * @Description Checks if the instance of TimeReportService Class exists or not returns the instance. If no instance exists, returns a new one
     */
    public static function getTimeReportServiceInstance() {
        try {
            if (!self::$inst_timereport_service) {
                self::$inst_timereport_service = new TimeReportService();
            }
            return self::$inst_timereport_service;
        } catch (\Throwable $ex) {
            Yii::error("ServiceFactory:getTimeReportServiceInstance::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    /**
     * 
     * @return type
     * @throws ErrorException
     * @Description Checks if the instance of BucketService Class exists or not returns the instance. If no instance exists, returns a new one
     */
    public static function getBucketServiceInstance() {
        try {
            if (!self::$inst_bucket_service) {
                self::$inst_bucket_service = new BucketService();
            }
            return self::$inst_bucket_service;
        } catch (\Throwable $ex) {
            Yii::error("ServiceFactory:getBucketServiceInstance::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
     /**
      * 
      * @return type
      * @throws ErrorException
      * @Description Checks if the instance of ProjectService Class exists or not returns the instance. If no instance exists, returns a new one
      */
    public static function getProjectServiceInstance() {
        try {
            if (!self::$inst_project_service) {
                self::$inst_project_service = new ProjectService();
            }
            return self::$inst_project_service;
        } catch (\Throwable $ex) {
            Yii::error("ServiceFactory:getProjectServiceInstance::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

}
