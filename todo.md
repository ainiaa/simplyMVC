#todo list
1、router 的具体实现  -- low

2、template的具体实现

   * 使用 原生态的PHP 作为模板语言  `pending`
   * 使用 smarty (太庞大了)  `pending`
   * 使用 twing  `pending` -- optional
   * 使用 bootstrap  `pending` -- optional

3、session 的具体实现   `pending` -- optional

   * cookie   `doing` 依赖于 cookie实例获取需要处理
   * file   `doing`
   * memcache   `doing`  依赖于 memcache实例获取需要处理
   * redis   `doing`  依赖于redis实例获取需要处理

4、ORM的具体实现 -- low

5、debug的具体实现 -- low

  * 类似 thinkphp 的 debugbar


6、log 的具体实现  -- low

   * dbLog
   * consoleLog
      * 可以参考 panada 框架的实现方式 -- 正式我想要的实现方式。。

7、RBAC ACL  -- low

   * RBAC
   * ACL
   * oAuth

8、 UnitTest 集成  -- low

   * PHPUnit
   * simpletest
   * Behat