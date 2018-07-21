<?php


class DictonaryHelper {
    

    protected static function formClassNameByName($dict)
    {
        return ucfirst($dict) . 'Dictonary';
    }
    
    protected static function existsDictonary($dict)
    {
        if(file_get_contents(static::formClassNameByName($dict) . '.php'))
            return true;
        return false;
    }
    
    
    public static function getDictonaryInfo()
    {
        return '';
    }
    
    
    public static function getListDictonary()
    {
        return ['yandex'];
    }
    
    
    public static function loadDictonary($dict)
    {
        if(!static::existsDictonary($dict))
            throw new \Exception('service not found');
        $className = static::formClassNameByName($dict);
            return new $className;
    }
}
