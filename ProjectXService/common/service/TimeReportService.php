<?php
namespace common\service;
use common\models\mongo\{TicketTimeLog,TicketCollection,TinyUserCollection};
use common\components\CommonUtility;
use common\models\mysql\{WorkFlowFields,StoryFields,Priority,PlanLevel,TicketType,Bucket,Collaborators,TaskTypes,Filters};
use common\models\bean\FieldBean;
use Yii;


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class TimeReportService {

        
    /**
     * @author Praveen P
     * @param type $CollaboratorId
     * @param type $projectId
     * @return type
     */
  
    public function getTimeReportCount($StoryData, $projectId) {
        try {
            $totalCount = TicketTimeLog::getTimeReportCount($StoryData, $projectId);

            return $totalCount;
        } catch (Exception $ex) {
            Yii::log("TimeReportService:getTimeReportCount::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }

    public function getAllTimeReportDetails($StoryData,$projectId) {
        try {
            $finalData = array();
            $timeReportDetails = TicketTimeLog::getAllTimeReportDetails($StoryData,$projectId);
            return $timeReportDetails;
        } catch (Exception $ex) {
            Yii::log("TimeReportService:getAllTimeReportDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
    /**
     * @author Praveen P
     * @param type $CollaboratorId
     * @param type $projectId
     * @return type 7days work hours for collaborator
     */
    
    public function getTimeLogRecordsForLast7Days($StoryData, $projectId) {
        try {
            $workLogHours = TicketTimeLog::getTimeLogRecordsForLast7Days($StoryData, $projectId);

            return $workLogHours;
        } catch (Exception $ex) {
            Yii::log("TimeReportService:getTimeReportCount::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
            
    
}

  