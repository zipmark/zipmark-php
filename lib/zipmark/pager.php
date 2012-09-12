<?php

/**
 * Iterate through paginated resources.  Used for lists that may require
 * multiple API calls to retrieve all results such as index calls.
 *
 * The list of resources is treated as a loop.  Iterating past the last item
 * returns to the beginning of the list and vice versa, meaning that the list
 * can be traversed in both directions.
 */
abstract class Zipmark_Pager extends Zipmark_Base implements Iterator {
  private $_position = 0; // Position within the current page
  protected $_page;       // Current page number
  protected $_totalPages; // Total number of pages
  protected $_perPage;    // Resources per page
  protected $_count;      // Total number of resources
  protected $_objects;    // Current page of resources

  abstract public function getObjectName();

  /**
   * Number of records in the list.
   *
   * @return integer number of records in the list
   */
  public function count() {
    if (empty($this->_count)) {
      if (is_null($this->_client))
        $this->_client = new Zipmark_Client();
      $response = $this->_client->request(Zipmark_Client::GET, $this->_href);
      $response->checkResponse();
      $this->_loadPageMetadata($response);
    }
    return $this->_count;
  }

  /**
   * Rewind to the beginning
   */
  public function rewind() {
    if (isset($this->_links['first'])) {
      $this->_loadFrom($this->_links['first']);
    }
    $this->_position = 0;
  }

  /**
   * The current object
   *
   * @return Zipmark_Resource The current object
   */
  public function current()
  {
    if (empty($this->_count)) {
      return null;
    }

    // Calculate "effective position" within the current page
    $effectivePosition = $this->_position % $this->_perPage;

    return $this->_objects[$effectivePosition];
  }


  /**
   * Get the current position
   *
   * @return integer Current position
   */
  public function key() {
    return $this->_position;
  }

  /**
   * Increments the position to the next element (wrapping)
   */
  public function next() {
    if (empty($this->_count)) {
      return null;
    }

    $this->_position++;
    if ($this->_position >= $this->_count) {
      // Hit the end of the list
      $this->_position--;
      return null;
    }
    elseif ($this->_position >= ($this->_page * $this->_perPage)) {
      // Advancing to the next page
      if (isset($this->_links['next']))
        $this->_loadFrom($this->_links['next']);
    }

    // Calculate "effective position" within the current page
    $effectivePosition = $this->_position % $this->_perPage;

    return $this->valid() ? $this->_objects[$effectivePosition] : null;
  }

  /**
   * Decrements the position to the previous element (wrapping)
   */
  public function prev() {
    if (empty($this->_count)) {
      return null;
    }

    $this->_position--;
    if ($this->_position < 0) {
      // Hit the beginning of the list
      $this->_position++;
      return null;
    }
    elseif ($this->_position < (($this->_page - 1) * $this->_perPage)) {
      // Reversing to the previous page
      if (isset($this->_links['prev']))
        $this->_loadFrom($this->_links['prev']);
    }

    // Calculate "effective position" within the current page
    $effectivePosition = $this->_position % $this->_perPage;

    return $this->valid() ? $this->_objects[$effectivePosition] : null;
  }

  /**
   * @return boolean True if the current position is valid.
   */
  public function valid() {
    return ($this->_position >= 0 && $this->_position < $this->_count);
  }

  /**
   * Load a page of results into this pager.
   */
  public function _loadFrom($path, $params = null) {
    if (is_null($this->_client))
      $this->_client = new Zipmark_Client();

    if (!is_null($params) && is_array($params)) {
      $vals = array();
      foreach ($params as $k => $v) {
        $vals[] = $k . '=' . urlencode($v);
      }
      $path .= '?' . implode($vals, '&');
    }

    $response = $this->_client->request(Zipmark_Client::GET, $path);
    $response->checkResponse();

    $this->_loadPageMetadata($response);
    $this->_loadLinks($response);
    $this->_loadObjects($response);
  }
  
  /**
   * Load Pagination meta-data from response (current page, number of pages, number of items, items per page)
   */
  private function _loadPageMetadata($response)
  {
    $parsedBody = json_decode($response->body, true);
    $pagination = $parsedBody["meta"]["pagination"];

    $this->_page       = $pagination["page"];
    $this->_totalPages = $pagination["total_pages"];
    $this->_perPage    = $pagination["per_page"];
    $this->_count      = $pagination["total"];
  }

  /**
   * Any paginated response will contain a "links" section which contains links to any or all of
   * the next, previous, first and last pages. This parses the links section into an array of 
   * links if the they're present.
   */
  private function _loadLinks($response) {
    $this->_links = array();

    $parsedBody = json_decode($response->body, true);
    $link_ary = $parsedBody["links"];
    foreach ($link_ary as $link) {
      $rel = $link["rel"];
      $href = $link["href"];
      $this->_links[$rel] = $href;
    }
  }

  /**
   * Refresh the current object list with the list in the current page of results
   */
  private function _loadObjects($response)
  {
    $this->_objects = array();
    $objName = $this->getObjectName();
    $parsedBody = json_decode($response->body, true);

    $objects = $parsedBody[$objName];
    $classType = Zipmark_Pager::$collectionToObjectMap[$objName];

    foreach ($objects as $object) {
      $newObj = new $classType();

      $href = Zipmark_Pager::_findObjectHref($object);
      if (!empty($href))
        $newObj->setHref($href);
      else if ($newObj instanceof Zipmark_Pager) {
        $newObj->_count = Zipmark_Base::_numRecords($object);
      }

      self::_buildObject($objName, $object, $newObj);

      $this->_objects[] = $newObj;
    }
  }

  private function _findObjectHref($object) {
    $links = $object["links"];
    if (!is_null($links)) {
      foreach ($links as $link) {
        if ($link["rel"] == "self")
          return $link["href"];
      }
    }
  }

  /**
   * Mapping to determine the type of objects in
   * a collection based on the Zipmark class type
   */
  static $collectionToObjectMap = array(
    'approval_rules'                 => 'Zipmark_ApprovalRule',
    'bills'                          => 'Zipmark_Bill',
    'callbacks'                      => 'Zipmark_Callback',
    'disbursements'                  => 'Zipmark_Disbursement',
    'vendor_relationships'           => 'Zipmark_VendorRelationship',
  );
}

?>
