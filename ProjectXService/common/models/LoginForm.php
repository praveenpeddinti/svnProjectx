<?php
namespace common\models;

use Yii;
use yii\base\Model;
use common\models\mysql\Collaborators;

/**
 * Login form
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return bool whether the user is logged in successfully
     */

    public function loginAjax($username){
        $collaboratorModel = new Collaborators();
        $userData = $collaboratorModel->findByUsername($username);
        return $userData;
    }
    
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        } else {
            return false;
        }
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = Collaborators::findByUsername($this->username);
        }

        return $this->_user;
    }
      /**
     * @author Padmaja
     * @description This is update active Collabarator status to inActive when  logout
     * @return type json object
     * 
     */ 
    public function checkLoginData($userData){
        try{
            $returnValue='failure';
            $userData = Collaborators::getCollaboratorDetails($userData);
            if(sizeof($userData)>0){
                $returnValue=$userData;
            }
            return $returnValue;
          
        } catch (Exception $ex) {
             Yii::log("Login:checkLoginData::" . $ex->getMessage() . "--" . $ex->getTraceAsString(), 'error', 'application');
        }
      
    }
    

    
     protected function getCollaborators()
    {
        $collaborators= array();
        $collaborators = User::getCollabrators();
        return $collaborators;
    }
}
