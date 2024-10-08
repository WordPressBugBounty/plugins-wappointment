<?php

namespace Wappointment\Helpers;

use Wappointment\Plugins\Helper;
use Wappointment\System\Container;
// @codingStandardsIgnoreFile
class Site
{
    public static function locale()
    {
        return Helper::getPlugin('MultiLang')->locale();
    }
    public static function lang()
    {
        return Helper::getPlugin('MultiLang')->lang();
    }
    public static function languages()
    {
        return Helper::getPlugin('MultiLang')->languages();
    }
    public static function container()
    {
        static $container = \false;
        if ($container === \false) {
            $container = new Container();
        }
        return $container;
    }
    public static function singleton($className)
    {
        $instance = \Wappointment\Helpers\Site::container()->resolve($className);
        if (!empty($instance)) {
            return $instance;
        }
        if (!\class_exists($className)) {
            throw new \WappointmentException("Class cannot be binded " . $className, 1);
        }
        return \Wappointment\Helpers\Site::container()->bind($className, new $className());
    }
}
