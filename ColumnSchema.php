<?php
/**
 * @author    Dmytro Karpovych
 * @copyright 2016 NRE
 */

namespace kak\clickhouse;

use Box\Spout\Reader\CSV\Sheet;
use yii\base\BaseObject;
use yii\db\ColumnSchema as BaseColumnSchema;
use yii\db\Expression;
use yii\db\ExpressionInterface;

/**
 * Class ColumnSchema
 * @package kak\clickhouse
 */
class ColumnSchema extends BaseObject
{
    /**
     * @var string name of this column (without quotes).
     */
    public $name;
    /**
     * @var bool whether this column can be null.
     */
    public $allowNull;
    /**
     * @var string abstract type of this column. Possible abstract types include:
     * char, string, text, boolean, smallint, integer, bigint, float, decimal, datetime,
     * timestamp, time, date, binary, and money.
     */
    public $type;
    /**
     * @var string the PHP type of this column. Possible PHP types include:
     * `string`, `boolean`, `integer`, `double`, `array`.
     */
    public $phpType;
    /**
     * @var string the DB type of this column. Possible DB types vary according to the type of DBMS.
     */
    public $dbType;
    /**
     * @var mixed default value of this column
     */
    public $defaultValue;
    /**
     * @var array enumerable values. This is set only if the column is declared to be an enumerable type.
     */
    public $enumValues;
    /**
     * @var int display size of the column.
     */
    public $size;
    /**
     * @var int precision of the column data, if it is numeric.
     */
    public $precision;
    /**
     * @var int scale of the column data, if it is numeric.
     */
    public $scale;
    /**
     * @var bool whether this column is a primary key
     */
    public $isPrimaryKey;
    /**
     * @var bool whether this column is auto-incremental
     */
    public $autoIncrement = false;
    /**
     * @var bool whether this column is unsigned. This is only meaningful
     * when [[type]] is `smallint`, `integer` or `bigint`.
     */
    public $unsigned;
    /**
     * @var string comment of this column. Not all DBMS support this.
     */
    public $comment;

    /**
     * Converts the input value according to [[phpType]] after retrieval from the database.
     * If the value is null or an [[Expression]], it will not be converted.
     * @param mixed $value input value
     * @return mixed converted value
     */
    public function phpTypecast($value)
    {
        return $this->typecast($value);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function dbTypecast($value)
    {
        if ($value !== null ) {
            if($this->phpType === Schema::TYPE_STRING && in_array($this->type, [Schema::TYPE_BIGINT, Schema::TYPE_BIGFLOAT])){
                return new Expression($value);
            }
        }
        return $this->typecast($value);
    }


    /**
     * Converts the input value according to [[phpType]] after retrieval from the database.
     * If the value is null or an [[Expression]], it will not be converted.
     * @param mixed $value input value
     * @return mixed converted value
     */
    protected function typecast($value)
    {
        if ($value === ''
            && !in_array(
                $this->type,
                [
                    Schema::TYPE_TEXT,
                    Schema::TYPE_STRING,
                    Schema::TYPE_BINARY,
                    Schema::TYPE_CHAR
                ],
                true)
        ) {
            return null;
        }

        if ($value === null
            || $value instanceof ExpressionInterface
            || $value instanceof Query
        ) {
            return $value;
        }

        switch ($this->phpType) {
            case Schema::TYPE_RESOURCE:
            case Schema::TYPE_STRING:
                if (is_resource($value)) {
                    return $value;
                }
                if (is_float($value)) {
                    // ensure type cast always has . as decimal separator in all locales
                    return StringHelper::floatToString($value);
                }
                return (string)$value;
            case Schema::TYPE_INTEGER:
                return (int)$value;
            case  Schema::TYPE_BOOLEAN:
                // treating a 0 bit value as false too
                // https://github.com/yiisoft/yii2/issues/9006
                return (bool)$value && $value !== "\0";
            case Schema::TYPE_DOUBLE:
                return (float)$value;
        }
        return $value;
    }
}
