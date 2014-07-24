#todo list
1、router 的具体实现

2、template的具体实现

   * 使用 原生态的PHP 作为模板语言  `pending`
   * 使用 smarty (太庞大了)  `pending`
   * 使用 twing  `pending` -- optional
   * 使用 bootstrap  `pending` -- optional

3、session 的具体实现   `pending` -- optional

   * cookie   `doing` 依赖于 cookie实例获取需要处理
   * db   `doing`  依赖于 db实例获取需要处理
   * file   `doing`
   * memcache   `doing`  依赖于 memcache实例获取需要处理
   * redis   `doing`  依赖于redis实例获取需要处理

4、db的 具体实现

   * PS https://github.com/a1phanumeric/PHP-MySQL-Class
        https://github.com/salebab/database
        https://github.com/adriengibrat/Simple-Database-PHP-Class
        https://github.com/robmorgan/phinx tools
        https://github.com/khoaofgod/phpfastcache
        https://github.com/stefangabos/Zebra_Database
        https://github.com/joshcam/PHP-MySQLi-Database-Class
        https://github.com/mikehenrty/thin-pdo-wrapper
        https://github.com/mikecao/sparrow
        https://github.com/ttsuruoka/php-simple-dbi/blob/master/src/SimpleDBI.php
        https://github.com/MyXoToD/PHP-Database-Class
        https://github.com/vrana/notorm
        https://github.com/jv2222/ezSQL
        https://github.com/Xeoncross/1kb-PHP-MVC-Framework
        https://github.com/sensiolabs/security-advisories ??
        https://github.com/fluxbb/database
        https://github.com/catfan/Medoo
        https://github.com/jaredtking/php-database
        https://github.com/adrinavarro/Database.php
        https://github.com/Molajo/Database  based on joomla
        https://github.com/xuanyan/Database
        https://github.com/spadefoot/kohana-orm-leap orm
        https://github.com/smasty/Neevo
        https://github.com/ADOdb/ADOdb
        https://github.com/Bananity/Wrapper-for-Master-Slaves-Databases-in-PHP-
        https://github.com/dmolsen/Detector orm
        https://github.com/Tharos/LeanMapper orm
        https://github.com/nbari/DALMP
        https://github.com/Xeoncross/DByte
        https://github.com/m4rw3r/RapidDataMapper
        https://github.com/oscarotero/simplecrud -- study php 5.4
        https://github.com/fridge-project/dbal
        https://github.com/maranemil/databasescomparer  -- dbtools db compare
        https://github.com/CDSGlobal/database-compare  -- dbtools db compare
        https://github.com/rulin/databaseDriver
        https://github.com/uzi88/PHP_MySQL_wrapper
        https://github.com/JonathanFrias/PHP-Database-Helper  -- orm
        https://github.com/search?p=32&q=php+database+&ref=searchresults&type=Repositories  再公司继续

5、ORM的具体实现

6、debug的具体实现

  * 类似 thinkphp 的 debugbar


7、log 的具体实现

   * dbLog
   * consoleLog
      * 可以参考 panada 框架的实现方式 -- 正式我想要的实现方式。。

8、RBAC ACL

   * RBAC
   * ACL
   * oAuth

9、 UnitTest 集成

   * PHPUnit
   * simpletest
   * Behat