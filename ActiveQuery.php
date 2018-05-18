<?php
namespace kak\clickhouse;
use yii\db\ActiveQueryInterface;
use yii\db\ActiveQueryTrait;
use yii\db\ActiveRelationTrait;

/**
 * Class ActiveQuery
 * @package kak\clickhouse
 */
class ActiveQuery extends Query implements ActiveQueryInterface
{
    use ActiveQueryTrait;
    use ActiveRelationTrait;
    /**
     * @event Event an event that is triggered when the query is initialized via [[init()]].
     */
    const EVENT_INIT = 'init';

    /**
     * Constructor.
     * @param array $modelClass the model class associated with this query
     * @param array $config configurations to be applied to the newly created query object
     */
    public function __construct($modelClass, $config = [])
    {
        $this->modelClass = $modelClass;
        parent::__construct($config);
    }

    /**
     * Initializes the object.
     * This method is invoked at the end of the constructor after the object is initialized with the
     * given configuration.
     */
    public function init()
    {
        parent::init();
        $this->trigger(self::EVENT_INIT);
    }

    /**
     * Executes query and returns all results as an array.
     * @param Connection $db the ClickHosue connection used to execute the query.
     * If null, the ClickHouse connection returned by [[modelClass]] will be used.
     * @return array|ActiveRecord the query results. If the query results in nothing, an empty array will be returned.
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * Executes query and returns a single row of result.
     * @param Connection $db the ClickHouse connection used to execute the query.
     * If null, the ClickHouse connection returned by [[modelClass]] will be used.
     * @return ActiveRecord|array|null a single row of query result. Depending on the setting of [[asArray]],
     * the query result may be either an array or an ActiveRecord object. Null will be returned
     * if the query results in nothing.
     */
    public function one($db = null)
    {
        $row = parent::one($db);
        if ($row !== false) {
            $models = $this->populate([$row]);
            return reset($models) ?: null;
        }
        return null;
    }


    /**
    * Creates a DB command that can be used to execute this query.
    * @param Connection|null $db the DB connection used to create the DB command.
    * If `null`, the DB connection returned by [[modelClass]] will be used.
    * @return Command the created DB command instance.
    */
    public function createCommand($db = null)
    {
        $modelClass = $this->modelClass;
        return parent::createCommand($db ? $db : $modelClass::getDb());
    }

    /**
     * Returns the number of records.
     * @param string $q the COUNT expression. Defaults to ''. clickhouse not support
     * Make sure you properly [quote](guide:db-dao#quoting-table-and-column-names) column names in the expression.
     * @param Connection $db the database connection used to generate the SQL statement.
     * If this parameter is not given (or null), the `db` application component will be used.
     * @return integer|string number of records. The result may be a string depending on the
     * underlying database engine and to support integer values higher than a 32bit PHP integer can handle.
     */
    public function count($q = '', $db = null)
    {
        return parent::count($q, $db);
    }


    /**
     * Converts the raw query results into the format as specified by this query.
     * This method is internally used to convert the data fetched from ClickHouse
     * into the format as required by this query.
     * @param array $rows the raw query result from ClickHouse
     * @return array the converted query result
     */
    public function populate($rows)
    {
        if (empty($rows)) {
            return [];
        }

        $models = $this->createModels($rows);

        if (!empty($this->with)) {
            $this->findWith($this->with, $models);
        }
        if (!$this->asArray) {
            foreach ($models as $model) {
                $model->afterFind();
            }
        }

        return parent::populate($models);
    }


}
