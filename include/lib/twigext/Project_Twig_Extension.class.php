<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/13
 * Time: 18:33
 */

class Project_Twig_Extension extends Twig_Extension
{
    public function getFunctions()
    {
        return array(
                new Twig_Function('make_url', 'make_url'),
        );
    }
}