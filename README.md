#todo list
##todo
1、router 的具体实现   `pending`  -- optional

2、autoload 的具体实现   `doing` <font color="red">implement this first -- 比如 i18n 不能正常自动加载的问题</font>

   * include path 设置 (model的加载方式)  再Importer::autoLoad 方法中实现  <font color="green">`done`</font>
   * 第三方类库自动加载 加载第三方类的时候需要调用Importer::setIncludePath() 设置路径 如果类不是以 CLASSNAME.php的形式
     还需要调用 Importer::setFileExt() 方法 注册后缀 <font color="green">`done`</font>
   * HMVC 自动加载 `pending`  -- optional

3、Conf配置的具体实现 (使用第三方 + 修改)   `doing`  -- required

   * core/SmvcConf.class.php  <font color="green">`done`</font>
   * based on Configula `pending`  -- optional

4、template的具体实现 (使用第三方 smarty )   `pending`  -- optional

   * 使用 原生态的PHP 作为模板语言  `pending`
   * 使用 smarty (太庞大了)  `pending`
   * 使用 twing  `pending` -- optional
   * 使用 bootstrap  `pending` -- optional

5、session 的具体实现   `pending` -- optional

   * cookie   `doing` 依赖于 cookie实例获取需要处理
   * db   `doing`  依赖于 db实例获取需要处理
   * file   `doing`
   * memcache   `doing`  依赖于 memcache实例获取需要处理
   * redis   `doing`  依赖于redis实例获取需要处理

6、db的 具体实现   `pending` -- optional

   * db 分库 --  直接搜索 "分表分库 原理"   `pending`
       * [分库分表的解决方案](http://www.cnblogs.com/littlehb/archive/2012/04/22/2465453.html "分库分表的解决方案")
       * [数据库水平切分的实现原理解析－－－分库，分表，主从，集群，负载均衡器](http://zhengdl126.iteye.com/blog/419850 "数据库水平切分的实现原理解析－－－分库，分表，主从，集群，负载均衡器")
       * [数据库分库技巧](http://wenku.baidu.com/link?url=DvH7E3jZE72Id7jESFNbm5QVS4wWO_YFK54rqsQhIrXa-TMmZPOzXD707DHj7JUTVT20jIY8DTrtzyKR-jdsDsQAikpuH8u4J_10oec3g_i "数据库分库技巧")
       * [MYSQL分库分表总结](http://wentao365.iteye.com/blog/1740874 "MYSQL分库分表总结")
       * [Mysql分表策略及实现](http://gubaojian.blog.163.com/blog/static/16617990820133183334047/ "Mysql分表策略及实现")
   * db 分表   `pending`

7、ORM的具体实现   `pending`   -- optional

8、hook的具体实现   <font color="green">`done`</font>  -- optional

9、debug的具体实现  `doing`   -- required

  * fileDebug  <font color="green">`done`</font>
  * firephp Debug   <font color="green">`done`</font>
  * 类似 thinkphp 的 debugbar  `pending`

10、cache(redis memcache)处理   `pending`   -- required

   * memcache `pending`
   * redis   `pending`
   * localCache  `pending`


11、log 的具体实现   fileLog  `doing`  -- required

   * fileLog  <font color="green">`done`</font>
   * socketLog <font color="green">`done`</font>
   * dbLog  `pending` -- optional
   * consoleLog `pending` -- optional
      * 可以参考 panada 框架的实现方式 -- 正式我想要的实现方式。。

12、I18N L10N   `doing`  -- required

   * I18N  `doing`
      * ini <font color="green">`done`</font>
      * php <font color="green">`done`</font>
      * json <font color="green">`done`</font>
      * xml <font color="green">`done`</font>
      * db `pending` -- optional I18N多为file db不是很必要
   * L10N  `pending` -- optional

13、RBAC ACL  `pending`   -- optional

   * RBAC   `pending`
   * ACL   `pending`

14、 UnitTest 集成  `pending`  -- required UT必须要弄 不能想这么就怎么。。

   * PHPUnit `pending`
   * simpletest `pending`
   * Behat `pending`  -- 基于 BDD

15、未完待续...

---

 windframework 也不错 应该可以学习啊 ioc di 之类的东西
----