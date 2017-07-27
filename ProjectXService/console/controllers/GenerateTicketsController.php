<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;
use common\models\bean\FieldBean;
use common\models\mongo\TicketCollection;
use common\models\mysql\Collaborators;
use common\models\mysql\StoryFields;
use common\models\mysql\WorkFlowFields;
use common\models\mysql\TicketType;
use common\models\mysql\Bucket;
use common\models\mysql\PlanLevel;
use common\models\mysql\Priority;

//use common\models\mongo\ProjectTicketSequence;

class GenerateTicketsController extends Controller
{
    public function actionIndex(){
        try{
            $i = 1;
            $numberOfRecords = 100000;
            
            $title = "This is a Test ticket for Demo, Ticket no: ";
            $description = "<b>This is a Test Description</b><p>This Do not have any relation with any of the other tickets in the project</p>"
                    . "<h2>A new Independent Ticket</h2>"
                    . "<span>Content of this description is completely fictious and any similarities with other tickets is just a conincidence.</span>"
                    . "<p>This description contains the overview of the task that has to be performed under this ticket</p>"
                    . "<b>Please read all the instruction carefully and start working on the ticket</b>"
                    . "<i>Kindly approach your leads. if you need any further details on the ticket</i>";
            $crudeDescription = "<b>This is a Test Description</b><p>This Do not have any relation with any of the other tickets in the project</p>"
                    . "<h2>A new Independent Ticket</h2>"
                    . "<span>Content of this description is completely fictious and any similarities with other tickets is just a conincidence.</span>"
                    . "<p>This description contains the overview of the task that has to be performed under this ticket</p>"
                    . "<b>Please read all the instruction carefully and start working on the ticket</b>"
                    . "<i>Kindly approach your leads. if you need any further details on the ticket</i>";
            $storyField = new StoryFields();
            $standardFields = $storyField->getStoryFieldList();
            
            $collaboratorsData = Collaborators::getCollabrators();
            $numberOfCollabs = count($collaboratorsData);
            $excess = (int)($numberOfRecords % $numberOfCollabs);
            $divsion = (int)($numberOfRecords / $numberOfCollabs);
            $countEachCollab = [];
            for($j = 0;$j<$numberOfCollabs;$j++){
                $countEachCollab[$collaboratorsData[$j]["Id"]] = 0;
            }
            
            $workflows = new WorkFlowFields();
            $workflowsData = $workflows->getStoryWorkFlowList();
            
            $ticketType = new TicketType();
            $ticketTypeData = $ticketType->getTicketTypeList();
            
            $planLevel = new PlanLevel();
            $planLevelData = $planLevel->getPlanLevelList();
            
            $priority = new Priority();
            $priorityData = $priority->getPriorityList();
            
            $bucketData = Bucket::getBucketsList(1);
            $dataArray = [];
//            $Team = Collaborators::getProjectTeam(1);
            echo "---------------Started Generating-----------------\n";
        for($i = 1;$i<=$numberOfRecords;$i++){    
         foreach ($standardFields as $field) {
                     $fieldBean = new FieldBean();
                     $fieldId =  $field["Id"];
                     $fieldType =  $field["Type"];
                     $fieldTitle =  $field["Title"];
                      $fieldName =  $field["Field_Name"];
                     $fieldBean->Id = (int)$field["Id"];
                     $fieldBean->title = $fieldTitle;
                     
                     if($fieldType == 6 && ($fieldName == "reportedby"||$fieldName == "stakeholder")){
                         $randNum = rand(1,2);
                         $reportedby = Collaborators::getCollboratorByFieldType("Id",$randNum);
                         $fieldBean->value= (int)$reportedby["Id"]; 
                         $fieldBean->value_name= $reportedby["UserName"]; 
                     }
                     else if($fieldName == "tickettype"){
                         $idx = rand(0,count($ticketTypeData)-1);
                         $fieldBean->value= (int)$ticketTypeData[$idx]["Id"]; 
                         $fieldBean->value_name= $ticketTypeData[$idx]["Name"];
                     }
                     else if($fieldName == "workflow"){
                         $idx = rand(0,count($workflowsData)-1);
                         $fieldBean->value= (int)$workflowsData[$idx]["Id"]; 
                         $fieldBean->value_name= $workflowsData[$idx]["Name"]; 
                     }
                     else if($fieldName == "planlevel"){
                         $idx = rand(0,count($planLevelData)-1);
                         $fieldBean->value= (int)$planLevelData[$idx]["Id"]; 
                         $fieldBean->value_name= $planLevelData[$idx]["Name"]; 
                     }
                     else if($fieldName == "priority"){
                         $idx = rand(0,count($priorityData)-1);
                         $fieldBean->value= (int)$priorityData[$idx]["Id"]; 
                         $fieldBean->value_name= $priorityData[$idx]["Name"]; 
                     }
                     else if($fieldName == "estimatedpoints"){
                         $fieldBean->value= (int)rand(0,5); 
                     }
                     else if($fieldType == 4 || $fieldType == 5){
                         if($fieldName == "duedate"){
                              $fieldBean->value= "";
                         }else{
                            $fieldBean->value= new \MongoDB\BSON\UTCDateTime(time() * 1000);   
                         }
                            
                         }
                     else if($fieldType == 8){
                        $idx = rand(0,count($bucketData)-1);
                        $bucketId = $bucketData[$idx]["Id"];
                        $fieldBean->value = (int)$bucketId;
                        $fieldBean->value_name= $bucketData[$idx]["Name"];
                     }
                     else if($fieldType == 6 && $fieldName == "assignedto"){
                         
                         regenrate:
                        $idx = rand(0,count($collaboratorsData)-1);
                         $cid = (int)$collaboratorsData[$idx]["Id"];
                         if($countEachCollab[$cid] < $divsion || $excess > 0){
                            $fieldBean->value = $cid;
                            $fieldBean->value_name= $collaboratorsData[$idx]["UserName"];
                            if($countEachCollab[$cid] >= $divsion){
                                $excess--;
                            }
                            $countEachCollab[$cid]++;
                         }else{
                             goto regenrate;
                         }
                     }
                     else{
                          $fieldBean->value=""; 
                     }
                    
                     $dataArray[$fieldName]= $fieldBean;
                      //array_push($dataArray, $fieldBean);
                  }    
            
        $ticketModel = new TicketCollection();
        $ticketModel->Title = $title.$i;
           $ticketModel->Description = "<p><b><u>Ticket Reference: $i</u></b></p>".$description;
           $ticketModel->CrudeDescription = "<p><b><u>Ticket Reference: $i</u></b></p>".$crudeDescription;
           $ticketModel->Fields = $dataArray;
           $ticketModel->ArtifactsRef = "";
           $ticketModel->CommentsRef = "";
           $ticketModel->FollowersRef = "";
           $ticketModel->ProjectId = 1;
           $ticketModel->RelatedStories= [];
           $ticketModel->Tasks= [];
           $ticketNumber = $i;
           $ticketModel->TicketId = (int)$ticketNumber;
           $ticketModel->TotalEstimate = 0;
           $ticketModel->TotalTimeLog = 0;
           $collection = Yii::$app->mongodb->getCollection('TicketCollection');
            $ticketModel->insert();
        }
//         $ticketDetails = $ticketModel->getTicketDetails(8,1);
//         echo "\n";
//         echo print_r($ticketModel,1);
//         echo "\n";
//         echo print_r($Team,1);
         echo "---------------Completed-----------------\n";
        } catch (\Throwable $ex) {
            echo "Exception in GeneratesTicketController::actionIndex ".$ex->getMessage();
            Yii::error("GeneratesTicketController:actionIndex::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
    }
    
   
}

?>
