<?php
namespace common\components;

class SVNUtility {
/**
 * 
 * @param type $projectName
 * @Description Unused Class - Can be removed
 */
   
    public static function createRepository($projectName){
        try{
            $ch = curl_init();
            $postfields = array();
            
            $projectName = preg_replace('/[^A-Za-z0-9\. -]/', '', $projectName);

            // Replace sequences of spaces with hyphen
            $projectName = preg_replace('/  */', '-', $projectName);
            
            
            $postfields['projectName'] = $projectName;
            error_log("--createRepository----".$projectName);
            //$postfields['field2'] = urlencode('value2');
            curl_setopt($ch, CURLOPT_URL, "http://10.10.73.16/test.php");
            curl_setopt($ch, CURLOPT_USERPWD, "guest:guest");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
            
            $result = curl_exec($ch);
            //$info=curl_exec($ch);
            //$info = curl_getinfo($ch);
            error_log("-createRepository response-".print_r($result,1));
          
          //  return $response;
       } catch (\Throwable $th) {
            Yii::error("SVNUtility:createRepository::" . $th->getMessage() . "--" . $th->getTraceAsString(), 'application');
           
        }
    }
    
    public static function createRepositoryUser($user){
        try{
           
            $userName =  $user->firstName."".$user->lastName;
            error_log("createRepositoryUser------".$userName);
            $ch = curl_init();
            $post_data = array(
                'username' => $userName,
                'password' => $user->password
            );
            $postfields = array();
            curl_setopt($ch, CURLOPT_URL, "http://10.10.73.16/user.php");
            curl_setopt($ch, CURLOPT_USERPWD, "guest:guest");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            
            $result = curl_exec($ch);
            //$info=curl_exec($ch);
            //$info = curl_getinfo($ch);
            error_log("-curl user response-".print_r($result,1));
            
        
            return $response;
       } catch (\Throwable $th) {
            Yii::error("SVNUtility:createUser::" . $th->getMessage() . "--" . $th->getTraceAsString(), 'application');
        }
    }
    
    public static function updateUser(){
        
    }
 
    public static function saveUserPermissions($postData){
        try{
             $returnValue = 'failure';
            $projects = new Projects();
            $projects->ProjectName = $projectName;
            $projects->Description = $description;
            $projects->CreatedBy = $userId;
            if($projects->save()){
               error_log("-------Id--------".$projects->PId);
               $returnValue = $projects->PId;
          }
           return $returnValue ;
        } catch (Exception $ex) {

        }
    }
    
}