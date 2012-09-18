<?php

abstract class Zipmark_EditableResource extends Zipmark_Resource {
  /**
   * Create a new Zipmark_Object at Zipmark
   *
   * @param Zipmark_Client $client Client object to connect to service
   */
  public function create($client = null) {
    if (!is_null($client))
      $this->setClient($client);

    $this->_save(Zipmark_Client::POST, $this->pathFor(''));
  }

  /**
   * Update an existing Zipmark_Object at Zipmark
   *
   * @param Zipmark_Client $client Client object to connect to service
   */
  public function update($client = null) {
    if (!is_null($client))
      $this->setClient($client);

    $this->_save(Zipmark_Client::PUT, $this->path());
  }

  protected function _save($method, $path) {
    $response = $this->_client->request($method, $path, $this->toJson());
    Zipmark_Base::_parseJsonToUpdateObject($response->body);
    $response->checkResponse($this);
  }
}

?>
