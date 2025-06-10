<?php

class DatabaseObjectFi {

  static protected $database;
  static protected $table_name = "";
  static protected $columns = [];
  public $errors = [];

  static public function set_database($database) {
    self::$database = $database;
  }

  static public function find_by_sql($sql) {
    $result = self::$database->query($sql);
    if(!$result) {
      exit("Database query failed.");
    }
    $object_array = [];
    $record = $result->fetch(PDO::FETCH_ASSOC);
    if(!$record)
    {
      return false;
    }
    else {
      do{
        $object_array[] = static::instantiate($record);
      }while($record = $result->fetch(PDO::FETCH_ASSOC));
    }

    $result->closeCursor();
    return $object_array;
  }


  static protected function instantiate($record) {
    $object = new static;
    // Could manually assign values to properties
    // but automatically assignment is easier and re-usable

    foreach($record as $property => $value) {
      if(property_exists($object, $property)) {
        $object->$property = $value;
      }

    }
    return $object;
  }

  static public function count_by_sql($sql) {
    $result_set = self::$database->query($sql);
    $row = $result_set->fetch(PDO::FETCH_ASSOC);
    return array_shift($row);
  }

}

?>
