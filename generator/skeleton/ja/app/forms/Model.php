<?php

abstract class Forms_Model extends Forms_Object
{
  protected $model = null;
  
  public function __construct($id = null)
  {
    if (is_empty($this->modelName)) {
      $exp = explode("_", get_class($this));
      $this->modelName = array_pop($exp);
    }
    
    if (is_empty($id)) {
      $this->setModel(MODEL($this->modelName));
    } else {
      $this->setModel(MODEL($this->modelName, $id));
    }
  }
  
  public function setModel($model)
  {
    if (is_string($model)) {
      $this->model = MODEL($model);
    } elseif ($model instanceof Sabel_Db_Model) {
      $this->model = $model;
    } else {
      $message = __METHOD__ . "() argument must be a string or an instance of model.";
      throw new Sabel_Exception_Runtime();
    }
    
    $this->values = $this->model->toArray();
  }
  
  public function getModel()
  {
    return $this->model;
  }
  
  /**
   * @return boolean
   */
  public function validate(Sabel_Validator $validator = null)
  {
    if ($validator === null) {
      $validator = $this->buildValidator();
    }
    
    $validator->validate($this->values);
    $errors = $validator->getErrors();
    
    if ($uniques = $this->model->getMetadata()->getUniques()) {
      $this->uniqueCheck($uniques, $errors);
    }
    
    $this->errors = $errors;
    
    return empty($this->errors);
  }
  
  public function buildValidator()
  {
    $validator = $this->createValidator();
    
    $this->setupModelValidator($validator);
    $this->setupValidator($validator);
    
    return $validator;
  }
  
  public function save()
  {
    $this->model->setValues($this->values);
    
    return $this->model->save();
  }
  
  protected function uniqueCheck(array $uniques, array &$errors)
  {
    $model = $this->model;
    
    $pkey = $model->getMetadata()->getPrimaryKey();
    if (!is_array($pkey)) $pkey = array($pkey);
    
    $inputValues = $this->values;
    foreach ($uniques as $_uniques) {
      $fetch  = true;
      $values = array();
      $finder = new Sabel_Db_Finder($model->getName(), $pkey);
      
      foreach ($_uniques as $unique) {
        if (isset($inputValues[$unique])) {
          $values[] = $inputValues[$unique];
          $finder->eq($unique, $inputValues[$unique]);
        } else {
          $fetch = false;
          break;
        }
      }
      
      if (!$fetch) continue;
      
      $_model = $finder->fetch();
      if ($_model->isSelected()) {
        $isValid = true;
        if ($model->isSelected()) {  // update
          foreach ($pkey as $column) {
            if ($_model->$column !== $model->$column) {
              $isValid = false;
              break;
            }
          }
        } else {  // insert
          $isValid = false;
        }
        
        if (!$isValid) {
          $names = array();
          foreach ($_uniques as $unique) {
            $names[] = $this->getDisplayName($unique);
          }
          
          $errors[] = implode("、", $names) . ' "' . implode(", ", $values) . '" は既に登録されています';
        }
      }
    }
  }
  
  protected function setupModelValidator(Sabel_Validator $validator)
  {
    $metadata = $this->model->getMetadata();
    $columns = $metadata->getColumns();
    
    $validators = $this->validators;
    foreach ($this->inputNames as $inputName) {
      if (!isset($columns[$inputName])) continue;
      
      $column = $columns[$inputName];
      if ($column->increment) continue;
      
      if (!$column->nullable) {
        $validator->add($column->name, "required");
      }
      
      if ($column->isString()) {
        $validator->add($column->name, "strwidth({$column->max})");
      } elseif ($column->isNumeric()) {
        $validator->add($column->name, "max({$column->max})");
        $validator->add($column->name, "min({$column->min})");
        
        if ($column->isInt()) {
          $validator->add($column->name, "integer");
        } else {  // float, double
          $validator->add($column->name, "numeric");
        }
      } elseif ($column->isBoolean()) {
        $validator->add($column->name, "boolean");
      } elseif ($column->isDate()) {
        $validator->add($column->name, "date");
      } elseif ($column->isDatetime()) {
        $validator->add($column->name, "datetime");
      }
    }
  }
}
