<?php
/*
 * This file is part of the UniversalClassLoader package.
 *
 * (c) Yo-An Lin <cornelius.howl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 *
 *      $loader = new \UniversalClassLoader\SplClassLoader( array(  
 *               'Vendor\Onion' => 'path/to/Onion',
 *               'Vendor\CLIFramework' => 'path/to/CLIFramework',
 *      ));
 *
 *      $loader->addNamespace(array( 
 *          'NS' => 
 *      ));
 *
 *      $loader->useIncludePath();
 *      $loader->register();
 *
 */
namespace UniversalClassLoader;
use Exception;

class SplClassLoader
{
    public $namespaces = array();
    public $prefixes = array();
    public $useIncludePath;

    public function __construct($namespaces = null)
    {
        if( $namespaces )
            $this->addNamespace( $namespaces );
    }

    public function addNamespace($ns = array())
    {
        if( is_array($ns) ) {
            foreach( $ns as $n => $dirs )
                $this->namespaces[ $n ] = (array) $dirs;
        } 
        elseif( $args = func_get_args() && count($args) == 2 ) {
            $this->namespaces[ $args[0] ] = $args[1];
        }
        else {
            throw new Exception;
        }
    }

    public function addPrefix($ps = array())
    {
        foreach ($ps as $prefix => $dirs) {
            $this->prefixes[$prefix] = (array) $dirs;
        }
    }

    public function useIncludePath($bool)
    {
        $this->useIncludePaths = $bool;
    }

    public function register($prepend = false)
    {
        spl_autoload_register(array($this, 'loadClass'), true, $prepend);
    }

    public function unregister()
    {
        spl_autoload_unregister(array($this, 'loadClass'));
    }

    public function findClassFile($fullclass)
    {
        $fullclass = ltrim($fullclass,'\\');
        if( ($r = strrpos($fullclass,'\\')) !== false ) {
            $namespace = substr($fullclass,0,($r-1));
            $class = substr($fullclass,$r);
            foreach( $this->namespaces as $ns => $dirs ) {
                if( strpos($ns,$namespace) !== 0 )
                    continue;

                $subpath = str_replace('\\', DIRECTORY_SEPARATOR, $namespace )
                    . DIRECTORY_SEPARATOR . str_replace( '_' , DIRECTORY_SEPARATOR , $class ) 
                    . '.php';
                foreach( $dirs as $d ) {
                    $path = $d . DIRECTORY_SEPARATOR . $subpath;
                    if( file_exists($path) )
                        return $path;
                }
            }
        }
        else {
            // use prefix to load class (pear style), convert _ to DIRECTORY_SEPARATOR.
            $subpath = str_replace('_', DIRECTORY_SEPARATOR, $class).'.php';
            foreach ($this->prefixes as $p => $dirs) {
                if (strpos($class, $p) !== 0)
                    continue;
                foreach ($dirs as $dir) {
                    $file = $dir.DIRECTORY_SEPARATOR.$subpath;
                    if (file_exists($file))
                        return $file;
                }
            }
        }

        if ($this->useIncludePaths && $file = stream_resolve_include_path($class))
            return $file;
    }

    public function loadClass($class)
    {
        if ($file = $this->findFile($class))
            require $file;
    }

}