<?php

class Storage
{
    private static $keyConfig;

    /**
     * getData($param       =   array('key' => $key, 'fields' => NULL, 'where' => NULL, 'orderBy => NULL));
     * getManyData($param   =   array('key' => $key, 'fields' => NULL, 'where' => NULL, 'orderBy => NULL, 'start' => NULL, 'len' => NULL));
     * countData($param     =   array('key' => $key, 'where'  => NULL));
     * addData($param       =   array('key' => $key, 'value'  => $value));
     * addManyData($param   =   array('key' => $key, 'value'  => $value));
     * setData($param       =   array('key' => $key, 'value'  => $value, 'where' => NULL));
     * delData($param       =   array('key' => $key, 'where'  => NULL));
     * rankData($param      =   array('key' => $key, 'where'  => NULL));
     * limitUpdate($param   =   array('key' => $key, 'value'  => array('field1' => n, 'field2' => m ), 'where' => NULL);
     * complexUpdate($param =   array('key' => $key, 'value'  => $value, 'limit' => array('field1' => n, 'field2' => m), 'where' => NULL);
     * #incr($param         =   array('key' => $key, array('field1' => n, 'field2' => m ), 'where' => NULL);
     * #decr($param         =   array('key' => $key, array('field1' => n, 'field2' => m ), 'where' => NULL);
     * incrDecr($param      =   array('key' => $key, 'order'  => incr/decr, 'i' => m(默认1), 'fields' (Hash Redis 使用)));
     * setManyData($param   =   array('key' => $key, 'value'  => array(array(value, where), array(value, where), )));
     * complexManyUpdate($param =   array('key' => $key, array(array('value'  => $value, 'limit' => array('field1' => n, 'field2' => m), 'where' => NULL), ...)));
     * rebuildMc($param     =   array('key' => $key, 'where'  => NULL));
     * onlyUpdateMc($param  =   array('key' => $key, 'update' => $date));
     */
    public static function __callStatic($method, $params)
    {
        $param = $params[0];
        if (empty($param['key'])) {
            return Logger::getInstance()->error(
                    array(
                            'msg'   => 'SL001',
                            'no'    => 'SL001',
                            'param' => array('paramString' => $method . '- :' . SmvcUtilHelper::encodeData($param))
                    )
            );
        }

        $methodMap = self::getKeyMethod($method);
        if (empty($methodMap)) {
            return Logger::getInstance()->error(
                    array(
                            'msg'   => 'SL002',
                            'no'    => 'SL002',
                            'param' => array('paramString' => $method . '- :' . SmvcUtilHelper::encodeData($param))
                    )
            );
        }

        $splitKey = SC::splitMapKey($param['key']);
        if (empty($splitKey[0]) || !isset($splitKey[1])) {
            return Logger::getInstance()->error(
                    array(
                            'msg'   => 'SL003',
                            'no'    => 'SL003',
                            'param' => array('paramString' => $method . '- :' . SmvcUtilHelper::encodeData($param))
                    )
            );
        }
        $keyConfig = SC::getKeyMapStorage($splitKey[0]);
        if (empty($keyConfig) || empty($methodMap[$keyConfig])) {
            return Logger::getInstance()->error(
                    array(
                            'msg'   => 'SL004',
                            'no'    => 'SL004',
                            'param' => array('paramString' => $method . '- :' . SmvcUtilHelper::encodeData($param))
                    )
            );
        }

        $param['keyConfig'] = $keyConfig;
        self::$keyConfig    = $keyConfig;
        if ('MC-M-DB' == $keyConfig) {
            return self::doStorageForMcManyDb($methodMap[$keyConfig], $param);
        }

        return self::doStorage($methodMap[$keyConfig], $param);
    }

    # Db operation
    private static function getDbData($param)
    {
        return MapDbStorage::getOneResult($param);
    }

    private static function getManyDbData($param)
    {
        return MapDbStorage::getManyResult($param);
    }

    private static function getMcManyDbData($param)
    {
        return MapDbStorage::getManyResult($param);
    }

    private static function getManyDbDataToManyMc($param)
    {
        $dbConfig = SC::getDbClient($param['key']);
        if (!empty(AC::$errorNo)) {
            return Logger::getInstance()->error(
                    array('msg' => 'SL006', 'no' => 'SL006', 'param' => array('paramString' => 'SL006'))
            );
        }

        $result = MapDbStorage::getManyResult($param);
        if (empty($result)) {
            return Logger::getInstance()->error(
                    array('msg' => 'SL007', 'no' => 'SL007', 'param' => array('paramString' => 'SL007'))
            );
        }

        $mapPrefix  = $dbConfig['mapPrefix'];
        $primaryKey = $dbConfig['uuidName'];

        $tmp = array();
        foreach ($result as $data) {
            $key       = $mapPrefix . '/' . $data[$primaryKey];
            $tmp[$key] = $data;
        }
        $result = $tmp;
        return $result;
    }

    private static function getManyDbDataToMc($param)
    {
        $dbConfig = SC::getDbClient($param['key']);
        if (!empty(AC::$errorNo)) {
            return Logger::getInstance()->error(
                    array('msg' => 'SL010', 'no' => 'SL010', 'param' => array('paramString' => 'SL010'))
            );
        }

        $dbResult   = self::getManyDbData($param);
        $formatData = self::formatMcData($param['key'], $dbResult);
        if ($formatData === false) {
            return false;
        }

        self::setMcData(array('key' => $param['key'], 'value' => $formatData));
        return $formatData;
    }

    private static function addDbData($param)
    {
        return MapDbStorage::insert($param);
    }

    private static function addManyDbData($param)
    {
        return MapDbStorage::insertMany($param);
    }

    private static function setDbData($param)
    {
        return MapDbStorage::update($param);
    }

    private static function setManyDbData($param)
    {
        if (empty($param['key']) || !is_array($param['value'])) {
            return Logger::getInstance()->error(
                    array('msg' => 'SL008', 'no' => 'SL008', 'param' => array('paramString' => 'SL008'))
            );
        }

        $result = $tmpResult = null;
        foreach ($param['value'] as $k => $p) {
            $result[$k] = $p;
            $p['key']   = $param['key'];
            $tmpResult  = MapDbStorage::save($param);
            if (!empty($tmpResult)) {
                unset($result[$k]);
            } else {
                return false;
            }
        }
        if (empty($result)) {
            return true;
        }

        return $result;
    }

    private static function complexManyDbUpdate($param)
    {
        if (empty($param['key']) || !is_array($param['value'])) {
            return Logger::getInstance()->error(
                    array('msg' => 'SL009', 'no' => 'SL009', 'param' => array('paramString' => 'SL009'))
            );
        }

        $result = $tmpResult = null;
        foreach ($param['value'] as $k => $p) {
            $p['key']  = $param['key'];
            $tmpResult = self::complexDbUpdate($p);
            if (empty($tmpResult)) {
                $tmp = null;
                if (isset($p['limit']) && is_array($p['limit'])) {
                    foreach ($p['limit'] as $field => $value) {
                        $tmp[$field] = $value;
                    }
                }
                if (isset($p['value']) && is_array($p['value'])) {
                    foreach ($p['value'] as $field => $value) {
                        $tmp[$field] = $value;
                    }
                }
                if (isset($p['where']) && is_array($p['where'])) {
                    foreach ($p['where'] as $field => $value) {
                        $tmp[$field] = $value;
                    }
                }
                if (empty($tmp)) {
                    continue;
                }
                $result[$k] = $tmp;
            }
        }
        if (empty($result)) {
            return true;
        }

        return $result;
    }

    private static function delDbData(&$param)
    {
        if (isset($param['where']) && empty($param['value'])) {
            $param['value'] = $param['where'];
        }
        return MapDbStorage::remove($param);
    }

    private static function limitDbUpdate($param)
    {
        return MapDbStorage::limitUpdate($param);
    }

    private static function complexDbUpdate($param)
    {
        return MapDbStorage::complexUpdate($param);
    }

    private static function incrDbData($param)
    {
        return MapDbStorage::limitUpdate($param);
    }

    private static function decrDbData($param)
    {
        return MapDbStorage::limitUpdate($param);
    }

    private static function countDbData($param)
    {
        return MapDbStorage::fetchCountRow($param);
    }

    private static function rankDbData($param)
    {
        return MapDbStorage::top($param);
    }
    # Db end

    # Mc operation
    public static function unityWriteMc()
    {
        return MapMemcacheStorage::unityWriteMc();
    }

    private static function getMcData($param)
    {
        return MapMemcacheStorage::fetch($param);
    }

    private static function getMcKeyData($param)
    {
        $result = MapMemcacheStorage::fetch($param);
        if (empty($result)) {
            return null;
        }

        $mcConfig = SC::getMcClient($param['key']);
        if (empty($mcConfig['primary']) || empty($param['where'][$mcConfig['primary']])) {
            return $result;
        }

        $dataKey = $param['where'][$mcConfig['primary']];
        if (empty($result[$dataKey])) {
            return null;
        }

        return $result[$dataKey];
    }

    private static function getManyMcData($param)
    {
        return MapMemcacheStorage::fetchMany($param);
    }

    private static function setManyMcData($param)
    {
        # NO setMany case!
        return MapMemcacheStorage::setMany($param);
    }

    private static function addMcData($param)
    {
        return MapMemcacheStorage::add($param);
    }

    private static function setMcData($param)
    {
        $result = MapMemcacheStorage::save($param);
        # WHEN 'MC' !=  self::$keyConfig的时候
        # 增加 MC 保存失败的时候，为了防止其数据和DB 不一致，
        # 多做一次删除MC动作，下次获取数据的时候，再次执行MC save!
        if (empty($result) && 'MC' != self::$keyConfig) {
            self::delMcData($param);
        }

        return $result;
    }

    private static function delMcData($param)
    {
        return MapMemcacheStorage::remove($param);
    }

    private static function getMcDelData($param)
    {
        $result = MapMemcacheStorage::fetch($param);
        if (empty($result)) {
            return null;
        }

        $mcConfig = SC::getMcClient($param['key']);
        if (empty($mcConfig['primary']) || empty($param['where'][$mcConfig['primary']])) {
            MapMemcacheStorage::remove($param);
            return $result;
        }
        if (empty($result[$param['where'][$mcConfig['primary']]])) {
            return true;
        }

        unset($result[$param['where'][$mcConfig['primary']]]);
        $param['value'] = $result;
        MapMemcacheStorage::save($param);

        return true;
    }

    private static function updateMcOnly($param)
    {
        #   empty($param['doType']) &&  $param['doType']    =   'update';
        if ('get' == $param['doType']) {
            return MapMemcacheStorage::fetch($param);
        }
        if ('set' == $param['doType']) {
            if (empty($param['value'])) {
                return null;
            }
            return MapMemcacheStorage::save($param);
        }
        if ('del' == $param['doType']) {
            return MapMemcacheStorage::remove($param);
        }
        if ('getDb' == $param['doType']) {
            return MapDbStorage::getOneResult($param);
        }

        return null;
    }

    private static function updateMMcOnly($param)
    {
        #   empty($param['doType']) &&  $param['doType']    =   'update';
        if ('get' == $param['doType']) {
            return MapMemcacheStorage::fetch($param);
        }
        if ('set' == $param['doType']) {
            if (empty($param['value'])) {
                return null;
            }
            return MapMemcacheStorage::save($param);
        }
        if ('del' == $param['doType']) {
            return MapMemcacheStorage::remove($param);
        }
        if ('getDb' == $param['doType']) {
            return MapDbStorage::getManyResult($param);
        }

        #   doType is other or NULL  default = update
        if (empty($param['update'])) {
            return null;
        }

        $mcData = MapMemcacheStorage::fetch($param);
        if (empty($mcData) || !is_array($mcData)) {
            return true;
        }

        foreach ($mcData as &$value) {
            $value = array_merge($value, $param['update']);
        }
        if (empty($param['update'])) {
            return null;
        }
        $param['value'] = $mcData;
        MapMemcacheStorage::save($param);

        return $param['value'];
    }

    private static function updateMcForAddManyDb($param)
    {
        if (empty($param['key']) || empty($param['value']) || !is_array($param['value'])) {
            return self::delMcData($param);
        }

        $mcData = MapMemcacheStorage::fetch($param);
        if (empty($mcData) || !is_array($mcData)) {
            return true;
        }

        $mcConfig = SC::getMcClient($param['key']);
        if (empty($mcConfig['primary'])) {
            return self::delMcData($param);
        }

        foreach ($param['value'] as $value) {
            if (empty($value[$mcConfig['primary']])) {
                return self::delMcData($param);
            }

            $mcData[$value[$mcConfig['primary']]] = $value;
        }
        $param['value'] = $mcData;

        return MapMemcacheStorage::save($param);
    }

    private static function updateMcForAddDb($param)
    {
        if (empty($param['key']) || empty($param['value']) || !is_array($param['value'])) {
            return self::delMcData($param);
        }

        $mcData = MapMemcacheStorage::fetch($param);
        if (empty($mcData) || !is_array($mcData)) {
            return true;
        }

        $mcConfig = SC::getMcClient($param['key']);
        if (empty($mcConfig['primary'])) {
            return self::delMcData($param);
        }
        if (!empty($param['resultId']) && !empty($param['resultType'])) {
            empty($param['value'][$param['resultType']]) && $param['value'][$param['resultType']] = $param['resultId'];
        }

        if (empty($param['value'][$mcConfig['primary']])) {
            return self::delMcData($param);
        }

        $mcData[$param['value'][$mcConfig['primary']]] = $param['value'];

        $param['value'] = $mcData;

        return MapMemcacheStorage::save($param);
    }

    private static function updateMcForSetDb($param)
    {
        if (empty($param['key']) || empty($param['value']) || !is_array($param['value'])) {
            return self::delMcData($param);
        }

        $mcData = MapMemcacheStorage::fetch($param);
        if (empty($mcData) || !is_array($mcData)) {
            return true;
        }

        $mcConfig = SC::getMcClient($param['key']);
        if (empty($mcConfig['primary'])) {
            return self::delMcData($param);
        }

        if (empty($param['setWhere']) || empty($param['setUpdate'])) {
            if (empty($param['value'][$mcConfig['primary']])) {
                return self::delMcData($param);
            }
            $mcData[$param['value'][$mcConfig['primary']]] = array_merge(
                    $mcData[$param['value'][$mcConfig['primary']]],
                    $param['value']
            );
        } else {
            if ($param['setWhere'] != $mcConfig['primary']) {
                return self::delMcData($param);
            }

            foreach ($param['setUpdate'] as $k) {
                $mcData[$k] = array_merge($mcData[$k], $param['value']);
            }
        }

        $param['value'] = $mcData;

        return MapMemcacheStorage::save($param);
    }

    /**
     * MC-M-DB: 涉及到跨MC存储块的操作,比如: 图腾从室内移动到花园，从室内MC 删除对应坐标物品、并在花园MC中增加对应物品信息...
     * $param   =   array('key', 'value', 'primaryKey', 'delKey')
     *              primaryKey: MC缓存块对应的MC配置项 primary的内容
     *              delKey: 原数据MC的KEY
     */
    private static function setMcTransRegional($param)
    {
        $oldParam = array('key' => $param['delKey']);
        $oldData  = MapMemcacheStorage::fetch($oldParam);
        if (is_array($oldData) && isset($oldData[$param['primaryKey']])) {
            unset($oldData[$param['primaryKey']]);
            $oldParam['value'] = $oldData;
            MapMemcacheStorage::save($oldParam);
        }

        $newData = MapMemcacheStorage::fetch($param);
        if (empty($newData) || !is_array($newData)) {
            return null;
        }
        $newData[$param['primaryKey']] = $param['value'];

        $param['value'] = $newData;
        return MapMemcacheStorage::save($param);
    }
    # Mc end

    # Redis operation
    private static function getRedisData($param)
    {
        return MapRedisStorage::fetch($param);
    }

    private static function setRedisData($param)
    {
        return MapRedisStorage::save($param);
    }

    private static function delRedisData($param)
    {
        return MapRedisStorage::remove($param);
    }

    private static function rankRedisData($param)
    {
        return MapRedisStorage::rank($param);
    }

    private static function getManyRedisData($param)
    {
        return MapRedisStorage::fetchMany($param);
    }

    private static function setManyRedisData($param)
    {
        return MapRedisStorage::setMany($param);
    }

    private static function incrDecrRedisData($param)
    {
        return MapRedisStorage::incrDecr($param);
    }

    private static function countRedisData($param)
    {
        return MapRedisStorage::countLen($param);
    }
    # Redis end

    # Key Config
    private static function doStorage($methodArray, $param)
    {

        if (empty($methodArray[0])) {
            return Logger::getInstance()->error(
                    array('msg' => 'SL005', 'no' => 'SL005', 'param' => array('paramString' => 'SL005'))
            );
        }

        # primary first method!
        #   $doList =   explode(',', $methodArray[0]);
        $doList  = SmvcArrayHelper::trimExplodeArray($methodArray[0], ',');
        $doFrist = trim($doList[0]);
        $result  = self::$doFrist($param);
        # primary second method!
        if (isset($doList[1])) {
            $doSecond = trim($doList[1]);
            self::$doSecond($param);
        }
        if ($result || empty($methodArray[1])) {
            return $result;
        }

        # rebuild method; When primary method result is FALSE & config have rebuild DATA method
        #   $doList =   explode(',', $methodArray[1]);
        $doList  = SmvcArrayHelper::trimExplodeArray($methodArray[1], ',');
        $doFrist = trim($doList[0]);
        $result  = self::$doFrist($param);
        if (empty($result)) {
            return $result;
        }

        if (isset($doList[1])) {
            $rebuildParam = array('key' => $param['key'], 'value' => $result);
            $doSecond     = trim($doList[1]);
            self::$doSecond($rebuildParam);
        }
        return $result;
    }

    private static function doStorageForMcManyDb($methodArray, $param)
    {

        if (empty($methodArray[0])) {
            return Logger::getInstance()->error(
                    array('msg' => 'SL005', 'no' => 'SL005', 'param' => array('paramString' => 'SL005'))
            );
        }
        $loopArray = $methodArray[0];
        if (!is_array($methodArray[0])) {
            $loopArray = SmvcArrayHelper::trimExplodeArray($methodArray[0], ',');
        }     #   explode(',', $methodArray[0]);
        $result = self::loopStorage($loopArray, $param);
        if ($result || empty($methodArray[1])) {
            return $result;
        }

        # Last  loopStorage is FALSE!
        # Next  loopStorage is rebuild Storage!
        #   So, return is formatMcData result!
        $loopArray = $methodArray[1];
        if (!is_array($methodArray[1])) {
            $loopArray = SmvcArrayHelper::trimExplodeArray($methodArray[1], ',');
        }     #   explode(',', $methodArray[1]);
        $result = self::rebuildStorageCache($loopArray, $param);
        return $result;
    }

    private static function loopStorage($loopArray, $param)
    {
        $result = self::$loopArray[0]($param);
        if (is_array($result) && isset($param['where'])) {
            $mcConfig = SC::getMcClient($param['key']);
            if (isset($mcConfig['primary']) && isset($param['where'][$mcConfig['primary']])) {
                $key = $param['where'][$mcConfig['primary']];
                isset($result[$key]) && $result = $result[$key];
            }
        }
        if (empty($result) || empty($loopArray[1])) {
            return $result;
        }

        #   if addData result return autoincrementID
        $param['resultId'] = $result;

        $loop = null;
        if (is_array($loopArray[1])) {
            if (isset($loopArray[1]['format'])) {
                self::rebuildStorageCache($loopArray[1], $param);
            } else {
                $loop = $loopArray[1];
            }
        } else {
            $loop = array($loopArray[1]);
        }

        if (!empty($loop)) {
            foreach ($loop as $do) {
                self::$do($param);
            }
        }

        return $result;
    }

    private static function rebuildStorageCache($loopArray, $param)
    {
        # MC Trans Regional Data operation
        if (isset($loopArray['mctr']) && isset($param['primaryKey'])) {
            return self::$loopArray['mctr']($param);
        }

        if (empty($loopArray['format'])) {
            return null;
        }
        $mcConfig = SC::getMcClient($param['key']);

        $resultData = null;
        if (isset($loopArray['mc']) && isset($param['value'])) {
            $newParam   = array('key' => $param['key']);
            $resultData = self::$loopArray['mc']($newParam);
            $resultData = self::$loopArray['format']($param['key'], $resultData, $param['value']);
        }
        if (empty($resultData) && isset($loopArray['db'])) {
            $newParam = $param;
            if (isset($mcConfig['primary']) && isset($param['where'][$mcConfig['primary']])) {
                unset($newParam['where']);
            }

            $resultData = self::$loopArray['db']($newParam);
            #   $resultData =   self::$loopArray['format']($param['key'], $resultData);
        }

        # Here, may be the storage have "?"! Future: must add monitor!
        # if (empty($resultData)) return  NULL;

        $new['key']   = $param['key'];
        $new['value'] = $resultData;
        self::$loopArray['set']($new);
        if (is_array($resultData) && isset($param['where'])) {
            if (empty($mcConfig['primary']) || empty($param['where'][$mcConfig['primary']])) {
                return $resultData;
            }

            $dataKey = $param['where'][$mcConfig['primary']];
            isset($resultData[$dataKey]) && $resultData = $resultData[$dataKey];
        }
        return $resultData;
    }

    private static function rebuildMcData($param)
    {
        $mcOldData = self::getMcData($param);
        if (!empty($mcOldData) && is_array($mcOldData)) {
            $rebuildData = array_merge($mcOldData, $param['value']);
        } else {
            $rebuildData = self::getDbData($param);
        }
        $param['value'] = $rebuildData;
        return self::setMcData($param);
    }

    private static function rebuildMMcLimit($param)
    {
        $mcOldData = self::getMcData($param);
        if (empty($mcOldData) || !is_array($mcOldData)) {
            return null;
        }
        $fetchDb = false;
        if (!empty($mcOldData) && is_array($mcOldData)) {

            foreach ($param['value'] as $field => $v) {
                if (!isset($mcOldData[$field])) {
                    $fetchDb = true;
                    break;
                }
                $mcOldData[$field] = $v + $mcOldData[$field];
            }
            $param['value'] = $mcOldData;
        }

        if ($fetchDb) {
            $param['value'] = self::getDbData($param);
        }

        self::setMcData($param);
        return $param['value'];
    }

    private static function rebuildMcLimit($param)
    {
        $fetchDb   = true;
        $mcOldData = self::getMcData($param);
        if (!empty($mcOldData) && is_array($mcOldData)) {
            $fetchDb = false;
            foreach ($param['value'] as $field => $v) {
                if (!isset($mcOldData[$field])) {
                    $fetchDb = true;
                    break;
                }
                $mcOldData[$field] = $v + $mcOldData[$field];
            }
            $param['value'] = $mcOldData;
        }

        if ($fetchDb) {
            $param['value'] = self::getDbData($param);
        }

        self::setMcData($param);
        return $param['value'];
    }

    private static function rebuildMcComplex($param)
    {
        if (empty($param['limit']) || !is_array($param['limit'])) {
            return self::rebuildMcData($param);
        }

        $fetchDb   = true;
        $mcOldData = self::getMcData($param);
        if (!empty($mcOldData) && is_array($mcOldData)) {
            $fetchDb   = false;
            $mcOldData = array_merge($mcOldData, $param['value']);
            foreach ($param['limit'] as $field => $v) {
                if (!isset($mcOldData[$field])) {
                    $fetchDb = true;
                    break;
                }
                $mcOldData[$field] = $v + $mcOldData[$field];
            }
            $param['value'] = $mcOldData;
        }

        if ($fetchDb) {
            $param['value'] = self::getDbData($param);
        }

        return self::setMcData($param);
    }

    private static function formatMcData($key, $value, $newValue = null)
    {
        # $newValue =   NULL; The $value just from DB!
        # $newValue =   array;  The $value from MC!
        if (empty($value) || !is_array($value)) {
            return $value;
        }
        $mcConfig = SC::getMcClient($key);
        if (empty($mcConfig['primary'])) {
            return false;
        }

        $primary     = $mcConfig['primary'];
        $resultValue = array();
        if (empty($newValue)) {
            foreach ($value as $v) {
                if (empty($v[$primary])) {
                    continue;
                }
                $resultValue[$v[$primary]] = $v;
            }

            return $resultValue;
        }

        if (!isset($newValue[$primary])) {
            return $value;
        }
        if (empty($value[$newValue[$primary]])) {
            $value[$newValue[$primary]] = $newValue;
        } else {
            $value[$newValue[$primary]] = array_merge($value[$newValue[$primary]], $newValue);
        }

        return $value;
    }

    private static function formatMcDelete($key, $value, $newValue = null)
    {
        # $newValue =   NULL; The $value just from DB!
        # $newValue =   array;  The $value from MC!
        if (empty($value) || !is_array($value)) {
            return $value;
        }
        $mcConfig = SC::getMcClient($key);
        if (empty($mcConfig['primary'])) {
            return false;
        }

        $primary     = $mcConfig['primary'];
        $resultValue = array();
        if (empty($newValue) || !is_array($newValue)) {
            foreach ($value as $v) {
                if (empty($v[$primary])) {
                    continue;
                }
                $resultValue[$v[$primary]] = $v;
            }

            return $resultValue;
        }
        $delSign = false;
        foreach ($newValue as $v) {
            if (!isset($v[$primary])) {
                continue;
            }
            if (!isset($value[$v[$primary]])) {
                continue;
            }
            unset($value[$v[$primary]]);
            $delSign = true;
        }
        if ($delSign) {
            return $value;
        }

        return null;
    }

    private static function getKeyMethod($method)
    {
        #   Key Map Method
        $keyMapMethod = array(
                'getData'           => array(
                        'DB'      => array('getDbData'),
                        'MC'      => array('getMcData'),
                        'REDIS'   => array('getRedisData'),
                        'DB-MC'   => array('getMcData', 'getDbData, setMcData'),
                        'MC-M-DB' => array(
                                'getMcKeyData',
                                array('db' => 'getMcManyDbData', 'format' => 'formatMcData', 'set' => 'setMcData')
                        ),   # DB 多条，MC存一个KEY的情况
                ),
                'setData'           => array(
                        'DB'      => array('setDbData'),
                        'MC'      => array('setMcData'),
                        'REDIS'   => array('setRedisData'),
                        'DB-MC'   => array('setDbData, rebuildMcData'),
                        'MC-M-DB' => array('setDbData, updateMcForSetDb'),
                    # 'MC-M-DB'   =>  array(array('setDbData', array('mctr' => 'setMcTransRegional', 'mc' => 'getMcData', 'db' => 'getMcManyDbData', 'format' => 'formatMcData', 'set' => 'setMcData'))),
                ),
                'countData'         => array(
                        'DB'    => array('countDbData'),
                        'REDIS' => array('countRedisData'),
                ),
                'addData'           => array(
                        'DB'      => array('addDbData'),
                        'MC'      => array('setMcData'),
                        'REDIS'   => array('setRedisData'),
                        'DB-MC'   => array('addDbData, setMcData'),
                        'MC-M-DB' => array('addDbData, updateMcForAddDb'),
                    #   'MC-M-DB'   =>  array(array('addDbData', array('db' => 'getMcManyDbData', 'format' => 'formatMcData', 'set' => 'setMcData'))),
                ),
                'delData'           => array(
                        'DB'      => array('delDbData'),
                        'MC'      => array('delMcData'),
                        'REDIS'   => array('delRedisData'),
                        'DB-MC'   => array('delDbData, delMcData'),
                        'MC-M-DB' => array(array('delDbData', array('getMcDelData'))),
                ),
                'rankData'          => array(
                        'REDIS' => array('rankRedisData'),
                ),
                'limitUpdate'       => array(
                        'DB'      => array('limitDbUpdate'),
                        'DB-MC'   => array('limitDbUpdate, rebuildMcLimit'),
                        'MC-M-DB' => array('limitDbUpdate'),
                    #   model 自行使用onlyUpdateMc 更新缓存 目前只有PackageModel 2个方法
                    # 'MC-M-DB'   =>  array(array('limitDbUpdate', array('db' => 'getMcManyDbData', 'format' => 'formatMcData', 'set' => 'setMcData'))),
                ),
                'incrDecr'          => array(
                        'REDIS' => array('incrDecrRedisData'),
                ),
                'complexUpdate'     => array(
                        'DB'    => array('complexDbUpdate'),
                        'DB-MC' => array('complexDbUpdate, rebuildMcComplex'),
                ),
                'addManyData'       => array(
                        'DB'      => array('addManyDbData'),
                        'MC-M-DB' => array('addManyDbData, updateMcForAddManyDb'),
                        'REDIS'   => array('setManyRedisData'),
                ),
                'getManyData'       => array(
                        'DB'      => array('getManyDbData'),
                        'REDIS'   => array('getManyRedisData'),
                    # 'DB-MC'     =>  array('getManyMcData', 'getManyDbData, setManyMcData'),
                        'MC-M-DB' => array(
                                'getMcData',
                                array('db' => 'getMcManyDbData', 'format' => 'formatMcData', 'set' => 'setMcData')
                        ),
                ),
                'rebuildMc'         => array(
                        'MC-M-DB' => array('getManyDbDataToMc'),
                ),
                'onlyUpdateMc'      => array(
                        'DB-MC'   => array('updateMcOnly'),
                        'MC-M-DB' => array('updateMMcOnly'),
                ),
                'setManyData'       => array(
                        'MC-M-DB' => array('setManyDbData'),
                        'REDIS'   => array('setManyRedisData'),
                ),
                'complexManyUpdate' => array(
                        'DB'      => array('complexManyDbUpdate'),
                        'MC-M-DB' => array('complexManyDbUpdate'),
                ),
        );
        #   End

        if (empty($keyMapMethod[$method])) {
            return null;
        }

        return $keyMapMethod[$method];
    }

    private static function nullMethod($param)
    {
        return null;
    }
}

