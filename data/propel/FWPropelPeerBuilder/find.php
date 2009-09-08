<?php

/**
 * Retrieve objects
 *
 * @param    array $where
 * @param    array $order_by
 * @param    integer $limit
 * @param    integer $offset
 * @param    Criteria $criteria additional criteria to use
 * @param    PropelPDO $con the connection to use
 * @return   array
 */
public static function find($where = array(), $order_by = array(), $limit = null, $offset = null, $criteria = null, $con = null)
{
  $exception = __CLASS__ . '::find(): %s';

  if($where === null) $where = array();
  if($order_by === null) $order_by = array();
  
  if($criteria === null)
  {
    $criteria = new Criteria;
  }
  
  if($con === null)
  {
    $con = Propel::getConnection({{peerclass}}::DATABASE_NAME);
  }

  // process $where
  if($where)
  {
    $re_field = '@^(?<f>[a-z0-9_]+)(?<o>!?=|<>| like)?$@';

    $criteria_constants = array(
      '='       => Criteria::EQUAL,
      '!='      => Criteria::NOT_EQUAL,
      '<>'      => Criteria::NOT_EQUAL,
      'like'    => Criteria::LIKE,
    );

    if(sizeof($where) % 2 != 0)
    {
      throw new Exception(sprintf($exception, 'the amount of elements in $where should be a multiple of two'));
    }

    $where = array_chunk(array_values($where), 2);

    foreach($where as $rule)
    {
      list($field, $compare) = $rule;
      
      if(!preg_match($re_field, $field, $matches))
      {
        throw new Exception(sprintf($exception, 'invalid field: ' . $field));
      }

      $field = $matches['f'];
      $operator = !empty($matches['o']) ? trim($matches['o']) : '=';

      if(!in_array($field, self::$fieldNames[BasePeer::TYPE_FIELDNAME]))
      {
        throw new Exception(sprintf($exception, 'non-existent field: ' . $field));
      }

      $field_col = {{peerclass}}::translateFieldName($field, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_COLNAME);

      $criteria->add($field_col, $compare, $criteria_constants[$operator]);
    }
  }

  // process $order_by
  if($order_by)
  {
    $re_order = '|^(?<o>[+-]?)(?<f>[a-z0-9_]+)$|';

    foreach($order_by as $rule)
    {
      if(!preg_match($re_order, $rule, $matches))
      {
        throw new Exception(sprintf($exception, 'invalid order rule: ' . $rule));
      }

      $order = $matches['o'] ? $matches['o'] : '+';
      $field = $matches['f'];

      if(!in_array($field, self::$fieldNames[BasePeer::TYPE_FIELDNAME]))
      {
        throw new Exception(sprintf($exception, 'non-existent field: ' . $field));
      }

      $field_col = {{peerclass}}::translateFieldName($field, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_COLNAME);

      if($order == '+')
        $criteria->addAscendingOrderByColumn($field_col);
      else
        $criteria->addDescendingOrderByColumn($field_col);
    }
  }

  // process $limit and $offset
  if($limit)
  {
    $criteria->setLimit($limit);
  }
  if($offset)
  {
    $criteria->setOffset($offset);
  }

  // retrieve objects
  return {{peerclass}}::doSelect($criteria);
}

