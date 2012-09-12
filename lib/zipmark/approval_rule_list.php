<?php

class Zipmark_ApprovalRuleList extends Zipmark_Pager {
  /**
   * Find all approval rules
   *
   * @param  string                   $params Parameters for find
   * @param  Zipmark_Client           $client Client object to connect to service
   *
   * @return Zipmark_ApprovalRuleList         A list of Approval Rules
   */
  public static function get($params = null, $client = null) {
    $list = new Zipmark_ApprovalRuleList(Zipmark_Client::PATH_APPROVAL_RULES, $client);
    $list->_loadFrom(Zipmark_Client::PATH_APPROVAL_RULES, $params);
    return $list;
  }

  /**
   * Provide the object's name
   *
   * @return string The Object Name
   */
  public function getObjectName() {
    return 'approval_rules';
  }
}

?>
