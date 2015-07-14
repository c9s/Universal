<?php
namespace Universal\ClassLoader;

interface ClassLoader { 

    public function register($prepend = false);

    public function resolveClass($fullclass);
}

