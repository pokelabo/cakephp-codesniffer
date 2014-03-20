<?php

class VariablenamePass {

    private $_us_snake_case = 'applications';
    private $snake_case = 'applications';
    static private $static_snake_case = 'applications';
    static private $_us_static_snake_case = 'applications';

    public function setVariables() {
        $this->_us_snake_case = 'changed';
        $this->snake_case = 'changed';
    }

    public static function setStatics() {
        self::$static_snake_case = 'changed';
        self::$_us_static_snake_case = 'changed';
    }
}