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

5、ORM的具体实现

6、debug的具体实现

  * 类似 thinkphp 的 debugbar

7、cache(redis memcache)处理

   * memcache
   * redis

8、log 的具体实现

   * dbLog
   * consoleLog
      * 可以参考 panada 框架的实现方式 -- 正式我想要的实现方式。。

9、RBAC ACL

   * RBAC
   * ACL
   * oAuth

10、 UnitTest 集成

   * PHPUnit
   * simpletest
   * Behat