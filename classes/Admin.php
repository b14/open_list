<?php

class Admin
{
  /**
   * Set the weights of the elements in a list.
   *
   * @param integer $list_id
   *   The id of the list.
   */
  public static function normalizeElements($list_id, $weight_span = 32) {
    // We use a multi_query here, to first set a variable, which we increment
    // with the WEIGHT_SPAN every update.
    // used to be SELECT instead of SET.
    $result = DB::q('
SET @start := -%weight_span;
UPDATE elements
SET weight = (@start := @start + %weight_span)
WHERE list_id = %list_id
ORDER BY weight;
    ',
    array(
      '%weight_span' => $weight_span,
      '%list_id' => $list_id
    ),
    TRUE); // This true tells it to use multi_query

    // Clear the result list after our multi_query
    DB::clearResults();
  }
  
  public static function check($list_id) {
    // Make sure previous is correect.
  }
}