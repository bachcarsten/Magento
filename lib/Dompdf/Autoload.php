<?php

class Dompdf_Autoload {
    static protected $_instance;

    public function autoload($class) {
        $filename = mb_strtolower($class) . ".cls.php";
        if (file_exists(DOMPDF_INC_DIR . "/$filename")) {
            return include (DOMPDF_INC_DIR . "/$filename");
        } else
            return false;
    }

    static public function instance()
    {
        if (!self::$_instance) {
            self::$_instance = new Dompdf_Autoload();
        }
        return self::$_instance;
    }

    static public function register()
    {
        spl_autoload_register(array(self::instance(), 'autoload'), false);
    }
}
