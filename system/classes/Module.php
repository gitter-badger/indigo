<?php defined('SYSTEM') or exit('No direct script access allowed');

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of module
 *
 * @author parvas
 */
class Module {
    
    private static $_class;
    private static $_method;
    private static $_modules = array();
    private static $_module;
    private static $_directory;
    private static $_params = array();

    /**
     * 
     * 
     * @param type $module
     * @return class 
     */
    public static function factory($module)
    {
        self::$_class = '';
        self::$_directory = APP . 'modules/';
        self::$_method = 'index';
        
        if (strpos($module, '/') !== FALSE)
        {
            $segments = explode('/', $module);

            // trying to determine path in case of nested module
            foreach ($segments as $segment)
            {
                // first check if segment maps to an existing sub-directory
                if (is_dir(self::$_directory . $segment))
                {
                    // digging deeper into the filesystem
                    self::$_directory .= $segment . '/';
                    self::$_class = $segment;
                    
                    // include parent class
                    require_once self::$_directory . self::$_class . '.php';
                    // path segment not fully parsed yet, so loop again
                    continue;
                }
                
                // url class segment parsed, but no directory found 
                if (self::$_class == '')
                {
                    echo "404!!!";
                    return;
                }
                // directory (and class) is set, include class file.
                //require_once self::$_directory . self::$_class . '.php';
                
                // time to check if this segment maps to a valid controller method
                if (self::$_method == 'index' && method_exists(ucfirst(self::$_class), $segment))
                {
                    self::$_method = $segment;
                }
                else 
                {
                    self::$_params[] = $segment;
                }
            }
        }
        else
        {
            // no nesting, direct call to <module>/index
            if (!is_dir(self::$_directory . $module))
            {
                echo '404!!!!';
                return;
            }
            
            self::$_directory .= $module . '/';
            self::$_class = $module;
        }
        
        require_once self::$_directory . self::$_class . '.php';
        
        $class = ucfirst(self::$_class);
        $instance = new $class;
        self::_add_to_stack();
        call_user_func_array(array($instance, self::$_method), self::$_params);
        self::_remove_from_stack();
        return $instance;
    }
    
    private function __construct() {
        ;
    }
    
    public static function find($item)
    {
        if (strpos($item, '/') !== FALSE)
        {
            self::$_directory = APP . 'modules/';
            
            $segments = explode('/', $item);
            
            foreach ($segments as $segment)
            {
                if (is_dir(self::$_directory . $segment))
                {
                    self::$_directory .= $segment . '/';
                    continue;
                }

                if (file_exists(self::$_directory . 'views/' . $segment . '.php'))
                {
                    return self::$_directory . 'views/' . $segment . '.php';
                }
            }
        }
    }
    
    private static function _add_to_stack()
    {
        self::$_modules[] = self::$_class;
    }
    
    private static function _remove_from_stack()
    {
        array_pop(self::$_modules);
    }
    
    public static function is_master()
    {
        return count(self::$_modules) === 1;
    }
}

?>