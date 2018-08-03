<?php


class CurrentUserList {
    
    protected static $users = [];
    
    
    public static function exists($userId)
    {
        if(!$userId || !intval($userId))
            throw new \Exception ('empty userId');
        
        return array_key_exists($userId, static::$users);
    }
    
    
    public static function put($userId, $userData = null)
    {
        if(!$userId)
            throw new \Exception ('empty userId');
        
        if($userData)
        {
            static::$users[$userId] = $userData;
            static::$users[$userId]['time'] = time();
        }
        else
            static::$users[$userId] = ['name' => $userId, 'time' => time()];       
    }
    
    
    public static function get($userId)
    {
        if(!$userId)
            throw new \Exception ('empty userId');
        
        return static::$users[$userId];
    }
}
