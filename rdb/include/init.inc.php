<?php 
define('RDB_PATH', dirname(dirname(__FILE__)) . '/');
gmod::inc('rdb', 'include/config.inc.php');
gmod::inc('rdb', 'include/factory.func.php');
gmod::inc('rdb', 'include/help_drive.class.php');
gmod::inc('rdb', 'drive/drive.class.php');
gmod::api('rdb', 'rdb.cls');
