<?php


class Game
{
	protected static $allGames;
	protected $game;
    protected $gameId;
    
    const USER_PER_GAME = 4;
	

    
    public static function getFormatedDataForResponse($gameId)
    {
        $data = static::$allGames[$gameId];
        foreach($data['user_list'] as &$user)
        {
            $user = CurrentUserList::get($user);
        }
        return $data;
    }
    
    

	public function __construct($gameId)
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
                'user_list' => []
            ];
            
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
        file_put_contents('games.txt', json_encode(static::$allGames, JSON_FORCE_OBJECT));
    }
    
	
	protected static function loadAllGames()
	{
		static::$allGames = json_decode(file_get_contents('games.txt'), true);
	}
        
    
    public static function findGameByUser($userId)
    {
        if($userId)
            throw new \Exception("empty userId");
        
        static::loadAllGames();
        foreach(static::$allGames as $gid => $game)
        {
            if(in_array($userId, $game['user_list']))
            {
                return new Game($gid);
            }
        }
        
        return null;
    }
    
    
    public static function startGame($users)
    {      
        $game = new Game();
        foreach($users as $userId)
            $game->addUser($userId);
       
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
        
}
