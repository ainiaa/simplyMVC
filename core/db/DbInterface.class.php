<?php

interface DbInterface
{

    public function insert();

    public function update();

    public function delete();

    public function replace();

    public function count();

    public function getRow();

    public function getAll();

    public function getCol();


}