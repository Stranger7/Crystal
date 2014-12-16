<?php
/**
 * This file is part of the Crystal package.
 *
 * (c) Sergey Novikov (novikov.stranger@gmail.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 05.12.2014
 * Time: 13:14
 */

namespace core\generic;

use core\DatabaseManager;
use core\Utils;

/**
 * Class Model
 * @package core\generic
 */
abstract class Model
{
    /**
     * @var DbDriver
     */
    protected $db;

    /**
     * @var string
     */
    private $table_name;

    /**
     * @var Property
     */
    public $id = Property::NOT_INITIALIZED;

    /**
     * @var array of Property
     */
    protected $properties = [];

    /**
     * @var array
     */
    private $errors = [];

    /*===============================================================*/
    /*                  I N I T I A L I Z A T I O N                  */
    /*===============================================================*/

    /**
     * Typical use case
     *
     * Specify the identifier
     *
     * $this->setTableName('customers');
     * $this->id = new Integer('id')->title('Identifier')->validator(new isRequired());
     * $this->property('name', 'String')->title('Customer')->validator(new isRequired());
     * $this->property('email', 'String')->title('E-mail')
     *      ->validator(new isRequired())
     *      ->validator(new isEmail());
     *
     * @param string $dsn
     */
    public function __construct($dsn = '')
    {
        $this->db = DatabaseManager::getInstance()->getConn($dsn);
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->table_name;
    }

    /**
     * Specifies table name
     * @param string $table_name
     */
    public function setTableName($table_name)
    {
        $this->table_name = $table_name;
    }

    /**
     * Create a property and add into the internal array
     * @param string $name
     * @param string $type
     * @return Property
     */
    protected function property($name, $type)
    {
        $class_name = $this->getPropertyClass($type);
        $this->$name = new $class_name($name);
        $this->properties[$name] = &$this->$name;
        return $this->$name;
    }

    /**
     * Create identifier property
     * @param string $name
     * @param string $type
     * @return Property
     */
    protected function identifier($name, $type)
    {
        $class_name = $this->getPropertyClass($type);
        $this->id = new $class_name($name);
        return $this->id;
    }

    /**
     * Checks and prepare class name for property
     * @param string $type
     * @return string
     */
    private function getPropertyClass($type)
    {
        $class_name = '\\core\\property_types\\' . Utils::toCamelCase($type);
        if (!class_exists($class_name)) {
            throw new \RuntimeException("Invalid property type: {$type}");
        }
        return $class_name;
    }

    /**
     * Typical use case in controller: $model->setValues($this->request->post());
     * @param $values
     */
    public function setValues($values)
    {
        foreach ($values as $field => $value)
        {
            if (isset($this->properties[$field])) {
                /** @var Property $this->$field */
                $this->$field->set($value);
            }
        }
    }

    /*===============================================================*/
    /*                        C R E A T E                            */
    /*===============================================================*/
    protected function beforeCreate()
    {
        $this->idInitializationCheck();
        $this->setDefaults();
        return $this->validation('create');
    }

    /**
     * @return bool
     */
    public function create()
    {
        if ($this->beforeCreate())
        {
            $this->id->set(
                $this->db->createEntry(
                    $this->getTableName(),
                    $this->createParamArray()
                )
            );
            return $this->afterCreate();
        }
        return false;
    }

    protected function afterCreate()
    {
        return true;
    }

    /*===============================================================*/
    /*                        U P D A T E                            */
    /*===============================================================*/
    protected function beforeUpdate()
    {
        $this->idInitializationCheck(true);
        return $this->validation('update');
    }

    public function update()
    {
        if ($this->beforeUpdate()) {
            $this->db->updateEntry(
                $this->getTableName(),
                $this->createParamArray(),
                [$this->id->name() => $this->id->get()]
            );
            return $this->afterUpdate();
        }
        return false;
    }

    protected function afterUpdate()
    {
        return true;
    }

    /*===============================================================*/
    /*                        D E L E T E                            */
    /*===============================================================*/
    protected function beforeDelete()
    {
        $this->idInitializationCheck(true);
        return true;
    }

    public function delete()
    {
        if ($this->beforeDelete()) {
            $this->db->deleteEntry(
                $this->getTableName(),
                [$this->id->name() => $this->id->get()]
            );
            return $this->afterDelete();
        }
        return false;
    }

    protected function afterDelete()
    {
        return true;
    }

    /*===============================================================*/
    /*                         S E L E C T                           */
    /*===============================================================*/

    /**
     * @param string $select
     * @return bool|\core\db_drivers\query_results\QueryResult
     */
    public function getEntry($select = '*')
    {
        return $this->db->getEntry(
            $this->getTableName(),
            [$this->id->name() => $this->id->get()],
            $select
        );
    }

    /*===============================================================*/
    /*                     V A L I D A T I O N                       */
    /*===============================================================*/
    /**
     * Do validation before create and update.
     * May be overridden in child class
     * @param string $operation : 'create' or 'update'
     * @return bool
     */
    protected function validation($operation = 'create')
    {
        foreach($this->properties as $name => $property)
        {
            /** @var Property $property */
            $do_validation = ($operation == 'create') ? true : $property->initialized();
            if (!$property->isReadOnly() && $do_validation)
            {
                if (!$property->isValid())
                {
                    $this->addError($property->name(), $property->getErrors());
                }
            }
        }
        return empty($this->errors);
    }

    /**
     * Add error to internal array
     * @param string $name
     * @param string|array $errors
     */
    protected function addError($name, $errors)
    {
        $this->errors[$name] = $errors;
    }

    /**
     * @return array of errors
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Clears error array
     */
    public function clearErrors()
    {
        $this->errors = [];
    }

    /*===============================================================*/
    /*                   M I S C E L L A N E O U S                   */
    /*===============================================================*/

    /**
     * Assign default values for properties if they are not initialized
     */
    private function setDefaults()
    {
        /** @var Property $property */
        foreach($this->properties as $name => $property)
        {
            $property->applyDefault();
        }
    }

    /**
     * Create parameters array for create and update operations.
     * @return array
     */
    private function createParamArray()
    {
        $data = [];
        /** @var Property $property */
        foreach($this->properties as $name => $property)
        {
            if ($property->initialized() && !$property->isReadOnly())
            {
                $data[$property->name()] = $property->preparedForDb();
            }
        }
        return $data;
    }

    private function idInitializationCheck($idHasValueCheck = false)
    {
        if (is_null($this->id)) {
            throw new \LogicException('Id field not defined', 501);
        }
        if ($idHasValueCheck && !$this->id->initialized()) {
            throw new \LogicException('Id is not set', 501);
        }
    }
}