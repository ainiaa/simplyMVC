<?php

class Persister
{

    /**
     * @var Persister
     */
    private static $instance;


    /**
     * Singleton insurance.
     *
     * @return Persister
     */
    private function __construct()
    {
    }

    /**
     *
     * @return Persister
     */
    public static function getInstance()
    {
        if (null == self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * objects to be persisted:
     *
     * <pre>
     * array(objectName => object)
     * objectName: "{$voName}{$shardId}" | "{$voListName}{$shardId}"
     * </pre>
     *
     * @var array
     */
    private $persistenceList = array();


    /**
     * Start to persist all the objects changed & cached in this class.
     *
     * @see Persister::$responseList
     * @see SzResponseManager::mergeResponse
     *
     * @return void
     * <pre>
     * responses are all stored in:
     * $this->responseList
     * see more Persister::$responseList to get the detail of the structure
     * </pre>
     */
    public function persist()
    {
        if ($this->persistenceList) {
            foreach ($this->persistenceList as $object) {
                /* @var SzAbstractVo|SzAbstractVoList $object */
                $response = $object->persist();
                if ($response && $object instanceof SzAbstractVoList) {//todo
                    foreach ($response as $res) {
                        $this->addResponse($res);
                    }
                } else if ($response && $object instanceof SzAbstractVo) {//todo
                    $this->addResponse($response);
                }
            }
        }
    }

    /**
     * Manually inserted into response when we manually insert something into mysql in logical
     * @see Persister::addResponse
     *
     * @param SzAbstractVo $element
     * @param int          $shardId
     * @param int          $pkValue
     *
     * @throws SzException 10518
     * @return void
     */
    public function addManuallyInsertedResponse($element, $shardId, $pkValue)
    {
        if (!($element instanceof SzAbstractVo)) {
            throw new SzException(10518);
        }

        $newResponse = array(
                $element->getOrmName(), // ormName
                $shardId, // shardId
                $pkValue, // pkValue
                $element->toArray(), // pkValue
        );
        $this->addResponse($newResponse);
        $this->addManuallyPersistedData($element);
    }

    /**
     * Add manually persisted data. The purpose of these data is to be removed if some exception encountered in the logic process.
     * @see ManuallyInsertedExceptionHandler::removeManuallyInsertedData
     *
     * @param SzAbstractVo $element
     *
     * @return void
     */
    private function addManuallyPersistedData($element)
    {
        $persistData = LocalCache::getData(SzSystemCache::CTRL_PERSIST_DATA, SzSystemCache::CTRL_KEY_MANUAL);
        if (!$persistData) {
            $persistData = array($element);
        } else {
            $persistData[] = $element;
        }
        SzSystemCache::cache(SzSystemCache::CTRL_PERSIST_DATA, SzSystemCache::CTRL_KEY_MANUAL, $persistData);
    }

    /**
     * Get manual persist data
     *
     * @return array $manualPersistData
     */
    public function getManuallyPersistedData()
    {
        return LocalCache::getData(SzSystemCache::CTRL_PERSIST_DATA, SzSystemCache::CTRL_KEY_MANUAL);
    }


    /**
     * Get model instance.
     *
     * @param string $ormName
     *
     * @throws Exception 10513
     * @return SzAbstractModel
     */
    public function getModel($ormName)
    {
        $className = $ormName . SzAbstractModel::MODEL_SUFFIX;
        if (!class_exists($className)) {
            throw new Exception(10513, $className);
        }
        $model = LocalCache::getData($className);
        if (!$model) {
            $model = new $className();
            LocalCache::setData($className, $model);
        }
        return $model;
    }

    /**
     * Get cached vo instance.
     *
     * @param string $shardVal
     * @param string $ormName
     * @param string $cacheVal default null
     *                         <pre>
     *                         Default null, means use the same value as shardVal.
     *                         In most cases, these two values shall be the same.
     *                         </pre>
     *
     * @throws Exception 10521
     * @return SzAbstractVo|null
     */
    public function getVo($shardVal, $ormName, $cacheVal = null)
    {
        $model = $this->getModel($ormName);
        if ($model->hasListMode()) {
            throw new Exception(10521, get_class($model));
        }

        if (is_null($cacheVal)) {
            $cacheVal = $shardVal;
        }

        $result = LocalCache::getData("{$model->getVoClassName()}{$shardVal}{$cacheVal}");
        if ($result === false) {
            $result = $model->retrieve($shardVal, $cacheVal);
        }

        return $result;
    }

    /**
     * Cache vo instance.
     *
     * @param SzAbstractVo $vo
     *
     * @throws Exception 10519
     * @return boolean
     */
    public function setVo($vo)
    {
        if (!($vo instanceof SzAbstractVo)) {
            throw new Exception(10519);
        }

        $model    = $this->getModel($vo->getOrmName());
        $shardVal = $model->getColumnValue($vo, $model->getShardColumn());
        $cacheVal = $model->getColumnValue($vo, $model->getCacheColumn());

        $cacheKey = "{$vo->getVoClassName()}{$shardVal}{$cacheVal}";
        if ($vo->isChanged() || $vo->isInsert()) {
            $this->persistenceList[$cacheKey] = $vo;
        }

        return LocalCache::setData($cacheKey, $vo);
    }

    /**
     * Get cached vo list instance.
     *
     * @param string $shardVal
     * @param string $ormName
     * @param string $cacheVal
     * <pre>
     * Default null, means use the same value as shardVal.
     * In most cases, these two values shall be the same.
     * </pre>
     *
     * @throws Exception 10514
     * @return SzAbstractVoList
     */
    public function getVoList($shardVal, $ormName, $cacheVal = null)
    {
        $model = $this->getModel($ormName);
        if (!$model->hasListMode()) {
            throw new Exception(10514, get_class($model));
        }

        if (is_null($cacheVal)) {
            $cacheVal = $shardVal;
        }

        $result = LocalCache::getData("{$model->getVoListClassName()}{$shardVal}{$cacheVal}");
        if ($result === false) {
            $result = $model->retrieve($shardVal, $cacheVal);
            LocalCache::setData("{$model->getVoListClassName()}{$shardVal}{$cacheVal}", $result);
        }

        return $result;
    }

    /**
     * Cache vo list instance.
     *
     * @param SzAbstractVoList $list
     *
     * @throws Exception 10517, 10520
     * @return boolean
     */
    public function setVoList($list)
    {
        if (!($list instanceof SzAbstractVoList)) {
            throw new Exception(10520);
        }

        $targetList = $list->getList();
        if (!$targetList) { // list has no data, check insert list & delete list
            if ($list->getInsertList()) {
                $targetList = $list->getInsertList();
            } else if ($list->getDeleteList()) {
                $targetList = $list->getDeleteList();
            }
            if (!$targetList) {
                /**
                 * no content in $this->list & $this->insertList & $this->deleteList
                 * and also shall has no content in $this->updateList
                 * it's not necessary to go through the next logic
                 */
                return false;
            }
        }

        $values = array_values($targetList);
        $vo     = array_shift($values);
        if (!($vo instanceof SzAbstractVo)) {
            $loopLimit = 2;
            $flagFound = false;
            for ($loop = 0; $loop < $loopLimit; ++$loop) {
                $values = array_values($vo);
                $vo     = array_shift($values);
                if ($vo instanceof SzAbstractVo) {
                    $flagFound = true;
                    break;
                }
            }
            if (!$flagFound) {
                throw new Exception(10517);
            }
        }

        /* @var SzAbstractVo $vo */
        $model    = $this->getModel($vo->getOrmName());
        $shardVal = $model->getColumnValue($vo, $model->getShardColumn());
        $cacheVal = $model->getColumnValue($vo, $model->getCacheColumn());

        $cacheKey = "{$list->getListClassName()}{$shardVal}{$cacheVal}";
        if ($list->isChanged()) {
            $this->persistenceList[$cacheKey] = $list;
        }

        return LocalCache::setData($cacheKey, $list);
    }

}