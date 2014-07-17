<?php

class HomeCtrl extends FrontendControl
{

    function __contruct()
    {
        parent::__construct();
    }

    function prefixIndex()
    {
        echo '在index之前执行 ... <br />';
    }

    function index()
    {
        echo 'Hello World!<br />';
    }

    function postIndex()
    {
        echo '在index之后执行.... <br />';
    }
}
