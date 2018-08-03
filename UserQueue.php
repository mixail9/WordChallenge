<?php

class UserQueue {
    
    protected $queue = [];  // see comment for "public function push"
    const DEFAULT_USER_PRIORITY = 5;
    
    
    public function __construct() {
        $this->reloadData();
    }
    
    
    
    protected function reloadData()
    {
        //$this->queue = json_decode(file_get_contents('search_new_game_list.txt'), true);
        if(!@$GLOBALS['user_quiue'])
        	$GLOBALS['user_quiue'] = [];
        $this->queue = &$GLOBALS['user_quiue'];
    }
    
    protected function saveData()
    {
        //file_put_contents('search_new_game_list.txt', json_encode($this->queue, JSON_FORCE_OBJECT));
    }
    
    
    public function clearOld() 
    {
        foreach($this->queue as $userId => $queueParams)
        {
            if(time() - $queueParams['time'] > 60)
                unset($this->queue[$userId]);
        }
    }
    
    public function getLength()
    {
        return count($this->queue);
    }
    
    
    
    public function exists($userId)
    {
        if(!$userId || !intval($userId))
            throw new \Exception ('empty data');
        
        if(array_key_exists($userId, $this->queue))
            return true;
        else 
            return false;
    }
    
    
    public function pop()
    {
        $priorityUserId = 0;
        foreach($this->queue as $userId => $queueInfo)
        {
            if(!$priorityUserId || $queueInfo['priority'] > $this->queue[$priorityUserId]['priority'])
            {
                $priorityUserId = $userId;
            }
        }
        $user = $this->queue[$priorityUserId];
        unset($this->queue[$priorityUserId]);
        return $user;
    }
    
    
    /**
     * $userData = [id => user_id, priority => user_priority]
     * $userData = user_id
     * 
     * priority 
     *  0 bot
     *  5 user
     * 
     * @param type $userData
     */
    public function push($userData)
    {
        if(!$userData)
            throw new \Exception ('empty data');    
        
        if(is_array($userData) && $userData['id']) 
            $this->queue[$userData['id']] = ['id' => $userData['id'], 'priority' => ($userData['priority'] ?: static::DEFAULT_USER_PRIORITY)];
        elseif(!is_array($userData))    
            $this->queue[$userData] = ['id' => $userData, 'priority' => static::DEFAULT_USER_PRIORITY];
        else 
            throw new \Exception ('incorrect data');

    }
    
    
    public function getUsersForGame($count = Game::USER_PER_GAME)
    {
        //if($this->getLength() < Game::USER_PER_GAME + 2)
        //    return null;
                    
        $users = [];
        for($i = 0; $i < min($count, $this->getLength()); $i++)
        {
            $users[] = $this->pop();
        }
        return $users;
    }
}
