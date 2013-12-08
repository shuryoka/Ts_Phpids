<?php
class Ts_Phpids_Model_Autoloader
{
    public static function addAutoloader()
    {
        spl_autoload_register(array(new self(), 'autoload'), true, true);
    }

    public static function removeAutoloader()
    {
        foreach (spl_autoload_functions() AS $autoloader) {
            if ($autoloader[0] instanceof Ts_Phpids_Model_Autoloader) {
                spl_autoload_unregister($autoloader);
                break;
            }
        }
    }

    public function autoload($class)
    {
        $libPath = Mage::getBaseDir('lib') . DS . 'PHPIDS' . DS . 'lib' . DS;
        require_once $libPath . str_replace('\\', DS, $class) . '.php';
    }
}