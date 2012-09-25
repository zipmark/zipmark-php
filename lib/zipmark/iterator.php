<?php

/**
 * Iterate through paginated Zipmark_Collection objects.  Used for lists that may require
 * multiple API calls to retrieve all results such as index calls.
 *
 * Iterating past the last item in either direction returns a 
 * null and leaves the pointer unchanged.
 */
class Zipmark_Iterator implements Iterator
{
  private $_position = 0; // Position within the range of resources
  private $_collection;   // The Zipmark_Collection for which this Iterator is used

  function __construct(Zipmark_Collection $collection)
  {
    $this->_collection = $collection;
  }

  /**
   * Rewind to the beginning
   */  
  public function rewind()
  {
    $this->_collection->loadLink("first");
    $this->_position = 0;
  }

  /**
   * The current object
   *
   * @return Zipmark_Resource The current object
   */
  public function current()
  {
    // Calculate "effective position" within the current page
    $effectivePosition = $this->_position % $this->_collection->perPage();

    return $this->_collection->getResource($effectivePosition);
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
   * Decrements the position to the previous element
   *
   * @return Zipmark_Resource The previous element in the collection
   */
  public function prev()
  {
    $this->_position--;
    if ($this->_position < 0) {
      // Hit the beginning of the list
      return null;
    } elseif ($this->_position < (($this->_collection->page() - 1) * $this->_collection->perPage())) {
      // Reversing to the previous page
      $this->_collection->loadLink("prev");
    }

    // Calculate "effective position" within the current page
    $effectivePosition = $this->_position % $this->_collection->perPage();

    return $this->valid() ? $this->_collection->getResource($effectivePosition) : null;
  }

  /**
   * Increments the position to the next element
   *
   * @return Zipmark_Resource The next element in the collection
   */
  public function next()
  {
    $this->_position++;
    if ($this->_position >= $this->_collection->count()) {
      // Hit the end of the list
      return null;
    }
    elseif ($this->_position >= ($this->_collection->page() * $this->_collection->perPage())) {
      // Advancing to the next page
      $this->_collection->loadLink("next");
    }

    // Calculate "effective position" within the current page
    $effectivePosition = $this->_position % $this->_collection->perPage();

    return $this->valid() ? $this->_collection->getResource($effectivePosition) : null;

  }

  /**
   * @return boolean True if the current position is valid.
   */
  public function valid()
  {
    return ($this->_position >= 0 && $this->_position < $this->_collection->count());
  }

}

?>
