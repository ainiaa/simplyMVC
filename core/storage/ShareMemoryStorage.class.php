<?php

class ShareMemoryStorage
{
    static private $shmHandle;

    /**
     * @param $memoryKey
     * @param $memorySize
     */
    public function createMemory($memoryKey, $memorySize)
    {
        self::$shmHandle = shm_attach($memoryKey, $memorySize);
    }

    /**
     * @param $memoryKey
     */
    public function linkMemory($memoryKey)
    {
        self::$shmHandle = shm_attach($memoryKey);
    }

    /**
     * @param $key
     *
     * @return array|mixed
     */
    public function getOneResult($key)
    {
        $result = shm_get_var(self::$shmHandle, $key);

        if (empty($result)) {
            $result = array();
        }

        return $result;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return bool
     */
    public function setData($key, $value)
    {
        $result = shm_put_var(self::$shmHandle, $key, $value);

        if (empty($result)) {
            return false;
        }

        return true;
    }


    /**
     * @param $keys
     *
     * @return array
     */
    public function getManyResult($keys)
    {
        $result = array();
        foreach ($keys as $key) {
            $value = shm_get_var(self::$shmHandle, $key);
            !empty($value) && $result[$key] = $value;
        }

        return $result;
    }


    /**
     * @return bool
     */
    public function disconnect()
    {
        return shm_detach(self::$shmHandle);
    }


    /**
     * @return bool
     */
    public function removeAll()
    {
        return shm_remove(self::$shmHandle);
    }


    /**
     * @param $key
     *
     * @return bool
     */
    public function removeData($key)
    {
        return shm_remove_var(self::$shmHandle, $key);
    }
}
