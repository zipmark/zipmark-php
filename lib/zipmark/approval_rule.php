<?php

class Zipmark_ApprovalRule extends Zipmark_Resource {
  /**
   * Find all approval rules
   *
   * @param  string                   $params Parameters for find
   * @param  Zipmark_Client           $client Client object to connect to service
   *
   * @return Zipmark_ApprovalRules            A list of Approval Rules
   */
  public static function all($params = null, $client = null) {
    $list = new Zipmark_ApprovalRules(Zipmark_Client::PATH_APPROVAL_RULES, $client);
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
