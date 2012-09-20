<?php

/**
 * Iterate through paginated resources.  Used for lists that may require
 * multiple API calls to retrieve all results such as index calls.
 *
 * Iterating past the last item in either direction returns a 
 * null and leaves the pointer unchanged.
 */
class Zipmark_Collection extends Zipmark_Base implements Iterator
{
  private $_position = 0; // Position within the current page
  private $_links;        // The collection's links
  private $_page;         // Current page number
  private $_totalPages;   // Total number of pages
  private $_perPage;      // Resources per page
  private $_count;        // Total number of resources
  private $_objects;      // Current page of resources

  /**
   * Find all objects
   *
   * @param  string             $params Optional parameters for find
   *
   * @return Zipmark_Collection         A list of objects
   */
  public function getAll($params = null)
  {
    $this->_loadFrom($this->getHref(), $params);
    return $this;
  }

  /**
   * Find a specific object
   *
   * @param  string $objectId The Object Identifier
   *
   * @return Zipmark_Resource The Object
   */
  public function get($objectId)
  {
    $objName = $this->getObjectName();
    $obj = new Zipmark_Resource($objName, $this->getHref(), $this->getClient());
    return $obj->get($objectId);
  }

  /**
   * Number of records in the list.
   *
   * @return integer number of records in the list
   */
  public function count()
  {
    if (empty($this->_count)) {
      $response = $this->getClient()->request(Zipmark_Client::GET, $this->pathFor());
      $this->_loadPageMetadata($response);
    }
    return $this->_count;
  }

  /**
   * Rewind to the beginning
   */
  public function rewind()
  {
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
  public function key()
  {
    return $this->_position;
  }

  /**
   * Get the current page
   *
   * @return integer Current page
   */
  public function page()
  {
    return $this->_page;
  }

  /**
   * Get the number of pages
   *
   * @return integer Number of pages
   */
  public function numPages()
  {
    return $this->_totalPages;
  }

  /**
   * Get the number of objects per page
   *
   * @return integer Number of objects per page
   */
  public function perPage()
  {
    return $this->_perPage;
  }

  /**
   * Increments the position to the next element
   *
   * @return Zipmark_Resource The next element in the collection
   */
  public function next()
  {
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
   * Decrements the position to the previous element
   *
   * @return Zipmark_Resource The previous element in the collection
   */
  public function prev()
  {
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
  public function valid()
  {
    return ($this->_position >= 0 && $this->_position < $this->_count);
  }

  /**
   * Load a page of results into this collection.
   *
   * @param string $path   HTTP path from which to load the object
   * @param array  $params Optional parameters to add to the query
   */
  private function _loadFrom($path, $params = null)
  {
    if (!is_null($params) && is_array($params)) {
      $vals = array();
      foreach ($params as $k => $v) {
        $vals[] = $k . '=' . urlencode($v);
      }
      $path .= '?' . implode($vals, '&');
    }

    $response = $this->getClient()->request('GET', $path);

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
  private function _loadLinks($response)
  {
    $this->_links = array();

    $parsedBody = json_decode($response->body, true);
    $link_ary = $parsedBody["links"];
    foreach ($link_ary as $link) {
      $this->_links[$link["rel"]] = $link["href"];
    }
  }

  /**
   * Refresh the current object list with the list in the current page of results
   */
  private function _loadObjects($response)
  {
    $this->_objects = array();
    $collectionName = $this->getObjectName();
    $parsedBody = json_decode($response->body, true);

    $objects = $parsedBody[$collectionName];
    $objectName = rtrim($collectionName, 's');

    foreach ($objects as $object) {    
      $href = Zipmark_Collection::_findObjectHref($object);
      $newObj = new Zipmark_Resource($objectName, $href, $this->getClient());
      
      if ($newObj instanceof Zipmark_Collection) {
        $newObj->_count = Zipmark_Base::_numRecords($object);
      }

      self::buildObject($objectName, $object, $newObj);

      $this->_objects[] = $newObj;
    }
  }

  private function _findObjectHref($object)
  {
    $links = $object["links"];
    if (!is_null($links)) {
      foreach ($links as $link) {
        if ($link["rel"] == "self")
          return $link["href"];
      }
    }
  }
}

?>
