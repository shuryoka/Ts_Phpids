PHPIDS inside Magento
=====================
see http://phpids.org

Increase the security of your shop
----------------------------------
PHPIDS is a large and steady growing collection of known attacks like XSS, SQL-Injection and so on. Based on it's configuration it calculates an impact level on which you can react.
This Magento integration brings some backend settings to let you easily learn and define the best settings for you.
While you can define an impact level which is still okay (but should be logged), you can also stop further Magento processing with a second impact level which will work as a red line!

Configure it
------------
Please find the configuration settings at: System > Configuration > Services > PHPIDS

How it works
------------
Ts_Phpids hooks in via the 'controller_action_presdispatch' event (currently only frontend). If the impact level is to high, the Magento 503 error page will be shown, some stuff will be written to an own log file and then Magento will be stopped.

TODOs
-----
Currently not every feature of PHPIDS is included in this module. Some of the missing possibilities are notices via mail and caching via Memcached, session or database.

Alternatives
------------
While this is a very cool way to have dynamic settings it also means to always process additional stuff for every page hit, always initialising Magento.
You can avoid that by having a look at the file 'example.php' inside the PHPIDS submodule. Just adapt the usage in an own file and include it at the beginning of the index.php. Alternatively you could set this file via .htaccess or php.ini and the auto_prepend_file command to always load it first. In that case you won't have the Magento overhead.