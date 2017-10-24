<?php
namespace common\models\bean;

/**
 * @Description This bean is used to prepare event bean.
 * @author Anand
 */
class EventBean {
    
    
    public $ProjectId = "";
    public $OccuredIn = "";
    public $ReferringId = "";
    public $DisplayAction="";
    public $ActionType = "";
    public $ActionBy = "";
    public $Miscellaneous = array();
    public $ChangeSummary=array();
    public $ShowInActivity = true;
    public $Status = 1;
    
     static function getConstant($constant)
    {
        return constant('self::'. $constant);
    }
    
}
?>