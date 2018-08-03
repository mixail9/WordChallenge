<?php


class Game
{
	protected static $allGames;
	protected $game;
    protected $gameId;
    
    const USER_PER_GAME = 4;
	

    
    public static function getFormatedDataForResponse($gameId)
    {
    
    print 'allGames = ';
    print_r(static::$allGames);
    print "\n";
        $data = static::$allGames[$gameId];
        foreach($data['user_list'] as &$user)
        {
            $user = CurrentUserList::get($user);
        }
        return $data;
    }
    
    

	public function __construct($gameId = null)
	{		
        $this->gameId = $gameId;
        if($gameId && intval($gameId) > 0)
            $this->reloadGameData();
        else 
        {
            $this->gameId = rand(1000, 9999);
            $this->game = [
                'id' => $this->gameId, 
                'begin_time' => time(),
                'end_time' => time() + 60,
                'active' => true,
                'user_list' => [],
                'word_by_user' => []
            ];
            $this->saveData();
            
        }
	}
    
    public function getId()
    {   
        return $this->gameId;
    }
        
    public function reloadGameData()
    {
        static::loadAllGames();
        if(array_key_exists($this->gameId, static::$allGames))
            $this->game = &static::$allGames[$this->gameId];  // '&' for getting actual (changed) info in static methods
        else 
            throw new \Exception ("game not found");
    }
    
    public function saveData()
    {
        // data from other games may be changed, dont rewrite
        static::loadAllGames();
        static::$allGames[$this->gameId] = $this->game;
        $this->game = &static::$allGames[$this->gameId];
        //file_put_contents('games.txt', json_encode(static::$allGames, JSON_FORCE_OBJECT));
    }
    
	
	protected static function loadAllGames()
	{
		if(!@$GLOBALS['game_list'])
			$GLOBALS['game_list'] = [];
		static::$allGames = &$GLOBALS['game_list'];
		//static::$allGames = json_decode(file_get_contents('games.txt'), true);
	}
        
    
    public static function findGameByUser($userId, $active = true)
    {
        if(!$userId)
            throw new \Exception("empty userId");
        
        static::loadAllGames();
        foreach(static::$allGames as $gid => $game)
        {
            if(in_array($userId, $game['user_list']))
            {
                // skip unactive games with activity flas
                if($active && !$game['active'])
                    continue;
                return new Game($gid);
            }
        }
        
        return null;
    }
    
    
    public static function startGame($users)
    {      
        $game = new Game();
        if($users && is_array($users))
        {
		foreach($users as $userId => $userData)
		{
			if(array_key_exists('id', $userData))
		    		$game->addUser($userData['id']);
		    	else
		    		$game->addUser($userData['id']);
		}
	}
	else
		throw new \Exception('try start game without users');
       
        return $game;
    }
    
    
    public function addUser($userId)
    {
        if(!CurrentUserList::exists($userId))
            throw new \Exception ('user not found');
        $this->game['user_list'][$userId] = $userId;
    }    
    
    public function addBot()
    {
        $id = rand(100, 999);
        CurrentUserList::put($id, ['id' => $id, 'name' => 'bot_' . $id, 'priority' => 0, 'time' => time()]); 
        $this->addUser($id); 
    }
    
    
    public function hasUser($userId)
    {
        if(!intval($userId))
            throw new \Exception('empty $userId');
        
        if(array_key_exists($userId, $this->game['user_list']))
            return true;
        
        return false;
    }
    
    
    public function isActive()
    {
        if(!$this->game['active'])
            return false;
        
        // network and system time delay beetwen users
        if(time() - $this->game['end_time'] > 5)
        {
            $this->game['active'] = false;
            $this->saveData();
            return false;
        }
        
        return true;
    }
    
    
    public function setWords($userId, $words = [])
    {
        if(!is_array($words))
            throw new \Exception('$words must be array');
        if(!intval($userId))
            throw new \Exception('empty $userId');
        
        
        if(!$this->game['active'] || abs($this->game['end_time'] - time()) > 5)
            return false;
        
        $this->game['word_by_user'][$userId] = $words;
    }
        
}
