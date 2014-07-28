<?php

interface DatabaseWrapper
{
    public function getRow();

    public function getCol();

    public function getOne();

    public function getAll();

    public function exec();

    public function lastInsertId();

    public function getDriver();

    public function query();

    public function fetch($query);
}
