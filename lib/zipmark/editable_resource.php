<?php

abstract class Zipmark_EditableResource extends Zipmark_Resource {
  /**
   * Save a Zipmark_EditableResource to the Zipmark Service
   *
   * @return Zipmark_EditableResource The Zipmark Object
   */
  public function save() {
    if ($this->id)
      $this->_save(Zipmark_Client::PUT, $this->path());
    else
      $this->_save(Zipmark_Client::POST, $this->pathFor(''));

    return $this;
  }

  protected function _save($method, $path) {
    $response = $this->_client->request($method, $path, $this->toJson());
    Zipmark_Base::_parseJsonToUpdateObject($response->body);
    $response->checkResponse($this);
  }
}

?>
