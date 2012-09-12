<?php

class Zipmark_ApprovalRule extends Zipmark_Resource {
  /**
   * Find all approval rules
   *
   * @param  string                   $params Parameters for find
   * @param  Zipmark_Client           $client Client object to connect to service
   *
   * @return Zipmark_ApprovalRuleList         A list of Approval Rules
   */
  public static function all($params = null, $client = null) {
    $list = new Zipmark_ApprovalRuleList(Zipmark_Client::PATH_APPROVAL_RULES, $client);
    $list->_loadFrom(Zipmark_Client::PATH_APPROVAL_RULES, $params);
    return $list;
  }

  /**
   * Find an approval rule by its ID
   *
   * @param  string               $approvalRuleId Approval Rule ID
   * @param  Zipmark_Client       $client         Client object to connect to service
   *
   * @return Zipmark_ApprovalRule                 An Approval Rule
   */
  public static function get($approvalRuleId, $client = null) {
    return self::_get(self::pathForApprovalRule($approvalRuleId), $client);
  }

  /**
   * Provide the object's name
   *
   * @return string The Object Name
   */
  public function getObjectName() {
    return 'approval_rule';
  }

  protected static function pathForApprovalRule($approvalRuleId) {
    return Zipmark_Client::PATH_APPROVAL_RULES . '/' . rawurlencode($approvalRuleId);
  }

  protected function path() {
    if (!empty($this->_href))
      return $this->getHref();
    else
      return self::pathForApprovalRule($this->approvalRuleId);
  }
}

?>
