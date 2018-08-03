<?php


class Logger {
    
    private static $instanceList = null; 
    
    private function __construct($type = 'system')
    {
        
    }
    
    public static function getInstance($type = 'system')
    {
        if(!$type)
            throw new \Exception('empty instance type');
        if($type != 'system')
            throw new \Exception('instance not found');
        if(!stitic::$instanceList[$type])
            stitic::$instanceList[$type] = new Logger($type);
        
        return stitic::$instanceList[$type];
    }
    
    public function saveData()
    {
        
    }
    
}
