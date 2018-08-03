<?php

class IOHelper {
    
    const ACTION_FORCE_START = '/force_start';
    const ACTION_FORCE_START_SOLO = '/force_start_solo';
    const ACTION_SET_RESULT = '/set_result';
    const ACTION_GET_INFO = '/get_info';
    
    
    
    public static function sendResult($result) 
    {
        return json_encode($result, JSON_FORCE_OBJECT);
        //die(json_encode($result, JSON_FORCE_OBJECT));
    }
    
    
    public static function extractInput($inputData = [])
    {
        if(!$inputData)
            $inputData = $_REQUEST;
        $vars = [];
        
        if(@$inputData['user_id'])
        {
            $vars['userId'] = intval($inputData['user_id']);
            CurrentUserList::put($vars['userId']);
            $vars['queue'] = new UserQueue();
            //$vars['user'] = 
        }
        if(@$inputData['game_id'])
        {
            $vars['gameId'] = intval($inputData['game_id']);
            $vars['game'] = new Game($vars['gameId']);
        }
        if(@$inputData['action'])
            $vars['action'] = $inputData['action'];
        
        if(@$inputData['words'])
            $vars['words'] = (json_decode($inputData['words']) ?: []);
                    
        return $vars;
    }
    
    
    public static function doAction($requestParams, $actionExternal = '')
    {
        extract(static::extractInput($requestParams));
        if($actionExternal)
            $action = $actionExternal;

        
        switch($action)
        {
            case '/shutdown':
                return 'shutdown';
                break;
            case '/test':
                return '----------------<br>----------------<br>----------------<br>----------------<br>----------------<br>counter = ' . intval(++$GLOBALS['counter']);
                break;
            case static::ACTION_FORCE_START_SOLO:
                if($game = Game::findGameByUser($userId))
                    return static::sendResult(Game::getFormatedDataForResponse($game->getId()));
                $newGame = Game::startGame([$userId]);
                for($i = 1; $i < Game::USER_PER_GAME; $i++)
                    $newGame->addBot();
                return static::sendResult(Game::getFormatedDataForResponse($newGame->getId()));
                break;
            case static::ACTION_FORCE_START:
                if($game = Game::findGameByUser($userId))
                    return static::sendResult(Game::getFormatedDataForResponse($game->getId()));
                   

                if(!$queue->exists($userId))
                	$queue->push($userId);
                $users = $queue->getUsersForGame();
                $newGame = Game::startGame($users);
                for($i = count($users); $i < Game::USER_PER_GAME; $i++)
                    $newGame->addBot();
                return static::sendResult(Game::getFormatedDataForResponse($newGame->getId()));
                break;
            case static::ACTION_SET_RESULT:
                if(!$game->hasUser($userId))
                    throw new \Exception('user_id must be defined');
                if(!$game->isActive())
                    throw new \Exception('game already closed');
                $game->setWords($userId, $words);
                return static::sendResult(Game::getFormatedDataForResponse($game->getId()));
                break;  
            case static::ACTION_GET_INFO:
                if(!$game->hasUser($userId))
                    throw new \Exception('user_id must be defined');
                return static::sendResult(Game::getFormatedDataForResponse($game->getId()));
                break;      

        }
    }
}
