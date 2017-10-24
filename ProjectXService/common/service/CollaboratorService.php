<?php

namespace common\service;

use common\models\mongo\{TicketCollection,TinyUserCollection};
use common\components\{CommonUtility,CommonUtilityTwo,ServiceFactory,NotificationTrait};
use common\models\mysql\WorkFlowFields;
use common\models\mysql\Collaborators;
use common\models\mongo\AccessTokenCollection;
use common\models\mysql\ProjectTeam;
use common\models\mysql\Projects;
use common\models\mysql\ProjectInvitation;
use common\models\mysql\Settings;
use Yii;
use yii\base\ErrorException;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class CollaboratorService {
    use NotificationTrait;

    /**
     * @author Moin Hussain
     * @Description This method to get the collaborators of a project.
     * @param type $ticketId
     * @param type $projectId
     * @return type
     */
    public function getProjectTeam($projectId) {
        try {
            $collaboratorModel = new Collaborators();
             $data = $collaboratorModel->getProjectTeam($projectId);
             error_log("*******project Team***********".print_r($data,1));
            foreach($data as $key=>$user){
                $userProfile = TinyUserCollection::getMiniUserDetails($user["Id"]);
                error_log("++++++keyyyyyy++++++++".$key);
                $data[$key]["ProfilePic"] = $userProfile["ProfilePicture"];
            }
            return $data;
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:getProjectTeam::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
      public function getProjectTeamImages($projectId,$id) {
        try {
            $collaboratorModel = new Collaborators();
            return $collaboratorModel->getProjectTeamImages($projectId,$id);
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:getProjectTeam::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * @author Padmaja
     * @return type 
     * @param type $collabaratorId
     * @Description Gets Access Token for given collaborator Id
     */
    public function getCollabaratorAccesstoken($collabaratorId) {
        try {
            $model = new AccessTokenCollection();
            $remembermeStatus = $model->checkCollabaratorStatus($collabaratorId);
            return $remembermeStatus;
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:getCollabaratorAccesstoken::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * @author Padmaja
     * @Description This is to save the Collabarator accesstoken details 
     * @return type 
     * @param type $accesstoken,$collabaratorId,$browserType,$remembermeStatus
     */
    public function saveCollabaratortokenData($accesstoken = "", $collabaratorId = 0, $browserType, $remembermeStatus = "") {
        try {
            $model = new AccessTokenCollection();
            return $tokenData = $model->saveAccesstokenData($accesstoken, $collabaratorId, $browserType, $remembermeStatus);
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:saveCollabaratortokenData::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * @author Padmaja
     * @Description This is to updateStatusCollabarator 
     * @return type 
     * @param type $collabaratortoken
     */
    public function updateStatusCollabarator($collabaratortoken) {
        try {
            $model = new AccessTokenCollection();
            return $tokenData = $model->updateStatusByToken($collabaratortoken);
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:updateStatusCollabarator::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * @author Ryan
     * @Description This is to get filtered team list in @mention 
     * @return type 
     * @param type $projectId, $search_query
     */
    public function getFilteredProjectTeam($projectId, $search_query) {
        try {
            $collaboratorModel = new Collaborators();
            return $collaboratorModel->getFilteredProjectTeam($projectId, $search_query);
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:getFilteredProjectTeam::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * @author Ryan
     * @Description This is to get matched user in @mention 
     * @return type 
     * @param type $user
     */
    public function getMatchedCollaborator($user) {
        try {
            $collaboratorModel = new Collaborators();
            return $collaboratorModel->checkMatchedUsers($user);
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:getMatchedCollaborator::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * @author Praveen P
     * @Description This is to get filtered team list in Followerlist 
     * @return type 
     * @param type $projectId, $search_query,$defaultusers
     */
    public function getCollaboratorsForFollow($ticketId, $searchValue, $projectId) {
        try {
            $collaboratorModel = new Collaborators();
            $matchArray = array("TicketId" => (int) $ticketId, "ProjectId" => (int) $projectId);
            $query = Yii::$app->mongodb->getCollection('TicketCollection');
            $pipeline = array(
                array('$match' => $matchArray),
                array(
                    '$group' => array(
                        '_id' => '$TicketId',
                        "followerData" => array('$push' => '$Followers.FollowerId'),
                    ),
                ),
            );
            $Arraytimelog = $query->aggregate($pipeline);
            ;
            $dafaultUserList = $Arraytimelog[0]["followerData"][0];
            return $collaboratorModel->getCollaboratorsForFollow($dafaultUserList, $searchValue, $projectId);
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:getCollaboratorsForFollow::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * @author Praveen P
     * @Description This method is to used to show the selected user (Stake Holder, Assigned to and Reproted by) in Follower list.
     * @param type $ticketId
     * @param type $projectId
     * @return type
     */
    public function getTicketFollowersList($ticketId, $projectId) {
        try {
            $ticketDetails = TicketCollection::getTicketDetails($ticketId, $projectId);
            if (!empty($ticketDetails)) {
                $details = CommonUtility::prepareFollowerDetails($ticketDetails);
            }
            return $details;
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:getTicketFollowersList::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * @author Padmaja
     * @Description This method to get the collaborators of a project.
     * @param type $ticketId
     * @param type $projectId
     * @return type
     */
    public function getProjectTeamDetailsByRole($projectId) {
        try {
            $ProjectTeamModel = new ProjectTeam();
            return $ProjectTeamModel->getProjectTeamDetailsByRole($projectId);
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:getProjectTeamDetailsByRole::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * @author Praveen
     * @Description This is to get filtered team list in creating buckets 
     * @return type 
     * @param type $projectId, $role
     */
    public function getResponsibleProjectTeam($projectId, $role) {
        try {
            $collaboratorModel = new Collaborators();
            return $collaboratorModel->getResponsibleProjectTeam($projectId, $role);
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:getResponsibleProjectTeam::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * @author Padmaja
     * @Description This method to verify  the  project.
     * @param type $projectId
     * @return type
     */
    public function verifyProjectName($projectName) {
        try {
            $ProjectModel = new Projects();
            return $ProjectModel->verifyingProjectName($projectName);
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:verifyProjectName::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * @author Padmaja
     * @Description This method to verify  the  project.
     * @param type $projectId
     * @return type
     */
    public function savingProjectDetails($projectName, $description, $userId, $projectLogo) {
        try {
            $ProjectModel = new Projects();
            $projectId = $ProjectModel->savingProjectDetails($projectName, $description, $userId, $projectLogo);
            return $projectId;
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:savingProjectDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * @author Padmaja
     * @Description This method to updateProjectlogo
     * @param type $projectId
     * @return type
     */
    public function updateProjectlogo($projectId, $logo) {
        try {
            $projectLogo = Yii::$app->params['projectLogo'] . '/' . $logo;
            $ProjectModel = new Projects();
            $projectId = $ProjectModel->updatingProjectLog($projectId, $projectLogo);
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:updateProjectlogo::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * @author Padmaja
     * @Description This method to verify  the  project.
     * @param type $projectId
     * @return type
     */
    public function savingProjectTeamDetails($projectId, $userId) {
        try {
            error_log($projectId . "=======" . $userId);
            $ProjectModel = new ProjectTeam();
            return $ProjectModel->saveProjectTeamDetails($projectId, $userId);
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:savingProjectTeamDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * @author Padmaja
     * @Description This method to   project count.
     * @param type $userId
     * @return type
     */
    public function getTotalProjectCount($userId) {
        try {
            $ProjectModel = new ProjectTeam();
            $total = $ProjectModel->getProjectsCountByUserId($userId);
            return count($total);
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:getTotalProjectCount::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * @author Padmaja
     * @Description This method to   project Names by UserId
     * @param type $userId
     * @return type
     */
    public function getProjectNameByUserId($userId) {
        try {
            $ProjectModel = new Projects();
            return $ProjectModel->getProjectNameByUserId($userId);
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:getProjectNameByUserId::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * @author Padmaja
     * @Description This method to   save projectlog
     * @param type $userId
     * @return type
     */
    public function saveProjectLogo($logoName) {
        try {
            $firstArray = explode("/", $logoName);
            $secondArray = explode("|", $firstArray[1]);
            $tempFileName = $secondArray[0];
            $originalFileName = $secondArray[1];
            $originalFileName = str_replace("]]", "", $originalFileName);
            error_log("log--------" . $originalFileName);
            $projectLogoPath = Yii::$app->params['ProjectRoot'] . Yii::$app->params['projectLogo'];
            if (!is_dir($projectLogoPath)) {
                if (!mkdir($projectLogoPath, 0775, true)) {
                    Yii::log("CollaboratorService:saveProjectLog::Unable to create folder--" . $ex->getTraceAsString(), 'error', 'application');
                }
            }
            $newPath = Yii::$app->params['ServerURL'] . Yii::$app->params['projectLogo'] . "/" . $tempFileName . "-" . $originalFileName;
            if (file_exists("/usr/share/nginx/www/ProjectXService/node/uploads/$tempFileName")) {
                rename("/usr/share/nginx/www/ProjectXService/node/uploads/$tempFileName", Yii::$app->params['ProjectRoot'] . Yii::$app->params['projectLogo'] . "/" . $tempFileName . "-" . $originalFileName);
            }

            $description = Yii::$app->params['ServerURL'] . Yii::$app->params['projectLogo'] . "/" . $tempFileName . "-" . $originalFileName;
            error_log("log---333333333-----" . $description);
            return $description;
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:saveProjectLog::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * 
     * @param type $params
     * @return type
     * @throws ErrorException
     * @author  Anand Singh
     * @Description Get user dashboard details.
     */
    public function getUserDashboardDetails($params) {
        try {
            $preparedDahboard = array();
            $userId = $params->userInfo->Id;
            $pageLength = $params->projectLimit;
            $pageNo = $params->projectOffset;
            $activityOffset = $params->activityOffset;
            $activityLimit = $params->activityLimit;
            $timeZone = $params->timeZone;
            $projectDetails = ProjectTeam::getAllProjects($userId, $pageLength, $pageNo);
            $projectCount = self::getTotalProjectCount($userId);
            $preparedDahboard['projectCount'] = $projectCount;
            $preparedDahboard['weeklyTimeLog'] = ServiceFactory::getTimeReportServiceInstance()->getCurrentWeekTimeLog($userId);
            $preparedDahboard['projects'] = CommonUtilityTwo::prepareProjectsForUserDashboard($projectDetails, $userId);
            $activities = ServiceFactory::getStoryServiceInstance()->getNotifications($userId, 0, $activityOffset, $activityLimit, 1, true, $timeZone);
            $preparedDahboard['activities'] = CommonUtilityTwo::prepareUserDashboardActivities($activities);
            return $preparedDahboard;
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:getUserDashboardDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage()."____".$ex->getTraceAsString());
        }
    }

    /**
     * @author Ryan
     * @param type $projectId
     * @param type $user
     * @param type $profilepic
     * @return type int
     * @Description Creates a new user
     */
    public function saveNewUser($projectId, $user,$code) {
        try { 
            $userId='';
            $isUserExist = Collaborators::getCollaboratorByEmail($user->email);
            if($isUserExist==''){
            $userId = Collaborators::createUser($user);
            $extension = CommonUtility::getExtension($user->originalImageName);
            $extension = strtolower($extension);
            $userName =  $user->firstName."".$user->lastName;
            $newImagePath = $userName."_".$userId.".".$extension;
            error_log("________Image__path_________".$user->userProfileImage);
            $userProfileTempPath = Yii::$app->params['ProjectRoot'].$user->userProfileImage;
            error_log("userProfileTempPath----3333333333333333333333".$userProfileTempPath);
            $newFilePath = Yii::$app->params['UserProfilePath']."".$newImagePath;
            $userNewProfilePath = Yii::$app->params['ProjectRoot']. $newFilePath ;
            error_log("userNewProfilePath----4444444444444444444444444".$userNewProfilePath);  
            copy($userProfileTempPath,$userNewProfilePath);
            $user->userProfileImage = $newFilePath;
            $usermail=$user->email;
            $invite_code=$code;
            error_log("saven ew user------1111111111----");
            TinyUserCollection::createNewUser($user,$userId);
            if ($userId > 0) {
               
                Collaborators::saveUserProfile($userId, $user->userProfileImage);
                Settings::saveNotificationsSettingsForUser($userId);
                $status = $this->addUserToTeam($projectId, $userId);
                $this->invalidateInvite($usermail, $invite_code);
            }
            }else{
                return "User Exist";
            }
            return $userId;
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:saveNewUser::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    /**
     * 
     * @param type $projectId
     * @param type $userid
     * @return boolean
     * @throws ErrorException
     * @Description Adds a user to project team.
     */
    public function addUserToTeam($projectId, $userid) {
        try { error_log("in team==".$projectId."==".$userid);
            Collaborators::addToTeam($projectId, $userid);
            return true;
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:addUserToTeam::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    /**
     * 
     * @param type $userid
     * @return type
     * @throws ErrorException
     * @Description returns data of a particular user.
     */
    public function getUserDetails($userid) {
        try {
            $user = Collaborators::getCollaboratorById($userid);
            $userDetails = Collaborators::getCollaboratorWithProfile($user['Email']);
            return $userDetails;
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:getUserDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * @author Ryan
     * @param type $search
     * @param type $projectId
     * @return type
     * @Description gets users to invite in a Project
     */
    public function getUsersToInvite($search, $projectId) {
        try {
            $userData = ProjectTeam::getActiveUsersForAllProjects($search, $projectId);
            return $userData;
        } catch (\Throwable $ex) {
            Yii::error("StoryService:getUsersToInvite::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * @author Ryan
     * @param type $invited_users
     * @param type $projectName
     * @param type $invited_by
     * @return type
     * @Description Sends a mail for with User Invitation
     */
    public function sendMailInvitation($invited_users, $projectName, $invited_by) {
        try {
            $invite_list = array();
            $status = false;
            $isInviteSent = array();
            $project = Projects::getProjectDetails($projectName);
            foreach ($invited_users as $recipient_email) {
                $recipient_id = Collaborators::getCollaboratorByEmail($recipient_email); // check if user exist in system
                if (isset($recipient_id)) { //if user exist in system
                    $isInviteSent = ProjectInvitation::checkInviteSent($recipient_email, $project['PId']); //check whether invite already sent for a project
                }
                $invite_code = $this->generateInvitation();
                if (empty($isInviteSent)) {
                    error_log("in insert invite code");
                    $new_invite_code = ProjectInvitation::insertInviteCode($recipient_id, $recipient_email, $invite_code, $project['PId'], $invited_by);
                } else { //if invite already sent
                    $new_invite_code = ProjectInvitation::updateInviteCode($recipient_id, $invite_code, $recipient_email, $project['PId']);
                }
                $text_message = "You have been Invited to " . $projectName . "<br/> <a href=" . Yii::$app->params['InviteUrl']  . '/Invitation?code=' . '' . $new_invite_code . ">Click to Accept</a>";
                $subject = "ProjectX | " . $projectName;
                $mailingName="ProjectX";
                NotificationTrait::processSingleEmail($mailingName,$recipient_email,$text_message,$subject);
            }
            return 'success';
        } catch (\Throwable $ex) {
            Yii::error("StoryService:sendMailInvitation::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
            return 'failure';
        }
    }

    /**
     * @author Ryan
     * @return type string
     * @Description Generates an Invitation code to send user invitation
     */
    public function generateInvitation() {
        try {
            $length = 10;
            $inviteCode = "";
            $characters = "0123456789abcdefghijklmnopqrstuvwxyz";
            for ($p = 0; $p < $length; $p++) {
                $inviteCode .= $characters[mt_rand(0, strlen($characters) - 1)];
            }
            return $inviteCode;
        } catch (\Throwable $ex) {
            Yii::error("StoryService:generateInvitation::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * @author Ryan
     * @Description Used for Verifying Invitation Code
     * @param type $invite_code
     * @return type array
     */
    public function verifyCode($invite_code) {
        try {
            $usertype="";
            $userData=array();
            $invite_data = ProjectInvitation::verifyCode($invite_code);
            error_log("==Invite Userrrrrrrr==".$invite_data['UserId']);
            if ($invite_data['IsValid']==0) { //either empty or invalid code
               $usertype=null;
            }else{ 
                if($invite_data['UserId']>0){
                    $usertype="Existing";
                    $add_status=$this->addUserToTeam($invite_data['ProjectId'],$invite_data['UserId'] );
                    $this->invalidateInvite($invite_data['Email'], $invite_code);
                    $userData=Collaborators::getCollaboratorWithProfile($invite_data['Email']);
                }else{
                    $usertype="New";
                }
            }
            return array('UserType'=>$usertype,'User'=>$userData);
        } catch (\Throwable $ex) {
            Yii::error("StoryService:verifyCode::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    /**
     * 
     * @param type $invite_email
     * @param type $invite_code
     * @return boolean
     * @throws ErrorException
     * @Description Invalidates or diables an Invite code
     */
    public function invalidateInvite($invite_email, $invite_code) {
        try {
            ProjectInvitation::disableInvite($invite_email, $invite_code);
            return true;
        } catch (\Throwable $ex) {
            Yii::error("StoryService:invalidateInvite::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * @author Padmaja
     * @param type $projectId
     * @param type $userId
     * @Description Update Project details on edit
     */
    public function updatingProjectDetails($projectName, $description, $fileExt, $projectLogo, $projectId,$userId='') {
        try {
            if (strpos($projectLogo, 'assets') !== false) {
                $logo = $projectLogo;
            } else {
                $extractUrl = explode('projectlogo/', $projectLogo);
                $projectLogoPath = Yii::$app->params['ProjectRoot'] . Yii::$app->params['projectLogo'];
                error_log("eee-------" . $extractUrl[1]);
                if (file_exists($projectLogoPath . "/" . $extractUrl[1])) {
                    error_log("eee-----aaaaaa--" . $fileExt);
                    if (empty($fileExt) || $fileExt == '') {
                        error_log("aaaaaa-------------" . $extractUrl[1]);
                        rename($projectLogoPath . "/" . $extractUrl[1], $projectLogoPath . "/" . $extractUrl[1]);
                        $logo = Yii::$app->params['projectLogo'] . '/' . $extractUrl[1];
                    } else {
                        error_log("aaaa6666666666aa-----------" . $extractUrl[1]);
                        rename($projectLogoPath . "/" . $extractUrl[1], $projectLogoPath . "/" . $projectName . "_" . $projectId . ".$fileExt");
                        $logo = Yii::$app->params['projectLogo'] . '/' . $projectName . "_" . $projectId . ".$fileExt";
                    }
                } else {
                    error_log("not existeddddddddd----");
                }
            }
            error_log("not existeddddddddd@@@@@@@@@@@@@--------" . $logo);
            $ProjectModel = new Projects();
            $updateStatus = $ProjectModel->updateProjectDetails($projectName, $description, $fileExt, $logo, $projectId);
            return $updateStatus;
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:updatingProjectDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * @author Padmaja
     * @param type $projectId
     * @param type $userId
     * @Description gets Project dashboard details.
     */
    public function getProjectDashboardDetails($projectName, $projectId, $userId,$page) {
        try {
            error_log("not existeddddddddd@@@@@@@@@@@@@--------".$page);
            return $projectDetails = CommonUtilityTwo::getProjectDetailsForProjectDashboard($projectId, $userId,$page);
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:getProjectDashboardDetails::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
     /**
     * @author Lakshmi
     * @param type $userId
      * @Description returns all notification types for a user.
     */
    public function getAllNotificationTypes($userId) {
        try {
            $notification_types = array();
            $notification_types = Settings::getAllNotificationTypes($userId);
            if(count($notification_types) == 0){
                Settings::saveNotificationsSettingsForUser($userId);
                $notification_types = Settings::getAllNotificationTypes($userId);
            }
            return $notification_types;
        } catch (Exception $ex) {
            Yii::error("CollaboratorService:getAllNotificationTypes::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    /**
     * @author Lakshmi
     * @param type $userId
     * @Description Gets status of all notifications
     */
    public function getAllNotificationsStatus($userId) {
        try {
            $notification_status = array();
            $notification_status = Settings::getAllNotificationsStatus($userId);
            return $notification_status;
        } catch (Exception $ex) {
            Yii::error("CollaboratorService:getAllNotificationsStatus::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    /**
     * @author Ryan
     * @param type $mailingName
     * @param type $invite_list
     * @param type $text_message
     * @param type$subject
     * @Description Sends invite to a user
     */
    public function sendSingleMailToInvite($mailingName,$invite_list,$text_message,$subject){
        try{
            $recipient=array();
            array_push($recipient,$invite_list);
            CommonUtility::sendEmail($mailingName,$recipient,$text_message,$subject);
        } catch (\Throwable $ex) {
            Yii::error("CollaboratorService:sendSingleMailToInvite::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }   
    }

    /**
     * @author Lakshmi
     * @param type $userId
     * @param type $status
     * @param type $type
     * @param type $activityId
     * @param type $isChecked
     * @Description Updates a notification's status
     */
    
    public function notificationsSetttingsStatusUpdate($userId, $type, $activityId,$isChecked) {
        try {
            $notification_status = array();
            $notification_status = Settings::NotificationsSetttingsStatusUpdate($userId,$type,$activityId,$isChecked);
            return $notification_status;
        } catch (Exception $ex) {
            Yii::error("CollaboratorService:notificationsSetttingsStatusUpdate::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

      /**
     * @author Lakshmi
     * @param type $userId
     * @param type $type
     * @param type $isChecked
       * @Description Updates the all notifications status
     */
    public function notificationsSetttingsStatusUpdateAll($userId, $type, $isChecked) {
        try {
            $notification_status = array();
            $notification_status = Settings::NotificationsSetttingsStatusUpdateAll($userId, $type, $isChecked);
            return $notification_status;
        } catch (Exception $ex) {
            Yii::error("CollaboratorService:notificationsSetttingsStatusUpdateAll::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }

    
    public function getEmailFromCode($code){
        try{
            $invitationDetails = ProjectInvitation::getMiniInvitationDetails($code);
            if($invitationDetails["IsValid"] == 1){
             return $invitationDetails;
            }else{
                 return "invalidCode";
            }
           
        } catch (Exception $ex) {
            Yii::error("CollaboratorService:getEmailFromCode::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
     /**
     * @autor Padmaja
     *  @param type $page
      * @Description Gets and processes Activities data based on the criteria
     */
    public function getAllActivities($postData){
        try{
            return $activities=CommonUtilityTwo::getAllProjectActivities($postData);
        } catch (Exception $ex) {
            Yii::error("CollaboratorService:getAllActivities::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
     /**
     * @autor Ryan
     * @param type $userId,$projectId
     * @Description Used for getting the role of logged in user
     */
    public function checkRole($projectId,$userId){ 
        try{
            $role=Collaborators::getUserRole($projectId,$userId);
            return $role;
        } catch (Exception $ex) {
            Yii::error("CollaboratorService:checkRole::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }
    
     /**
     * @autor Ryan
     * @param type $projectId,$email
     * @Description Used for checking user exists in team
     */
    public function checkUserInTeam($projectId,$email){
        try{
            $user=Collaborators::findUserInTeam($projectId,$email);
            if($user!=''){
                return 'exist';
            }else{
                return 'not exist';
            }
           
        } catch (Exception $ex) {
            Yii::error("CollaboratorService:checkUserInTeam::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'application');
            throw new ErrorException($ex->getMessage());
        }
    }


}
