<?php
namespace kak\clickhouse;
use Yii;
use yii\db\ActiveRecord as BaseActiveRecord;
use yii\db\Exception;

class ActiveRecord extends BaseActiveRecord
{
    /**
     * Returns the connection used by this AR class.
     * @return mixed|Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('clickhouse');
    }

    /**
     * @inheritdoc
     * @return \kak\clickhouse\ActiveQuery the newly created [[\kak\clickhouse\ActiveQuery]] instance.
     */
    public static function find()
    {
        return Yii::createObject(ActiveQuery::class, [get_called_class()]);
    }

    /**
     * Returns the primary key **name(s)** for this AR class.
     *
     * Note that an array should be returned even when the record only has a single primary key.
     *
     * For the primary key **value** see [[getPrimaryKey()]] instead.
     *
     * @return string[] the primary key name(s) for this AR class.
     */
    public static function primaryKey()
    {
        // TODO: Implement primaryKey() method.
        return null;
    }

    /**
     * Returns a value indicating whether the specified operation is transactional in the current [[$scenario]].
     * @param int $operation the operation to check. Possible values are [[OP_INSERT]], [[OP_UPDATE]] and [[OP_DELETE]].
     * @return bool whether the specified operation is transactional in the current [[scenario]].
     */
    public function isTransactional($operation)
    {
        return false;
    }



}