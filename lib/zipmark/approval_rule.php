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
    return Zipmark_ApprovalRules::get($params, $client);
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
    $approval_rule = new Zipmark_ApprovalRule();
    return $approval_rule->_get($approval_rule->pathFor($approvalRuleId), $client);
  }

  protected function path() {
    if (!empty($this->_href))
      return $this->getHref();
    else
      return $this->pathFor($this->approvalRuleId);
  }
}

?>
