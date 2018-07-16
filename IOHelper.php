<?php

class IOHelper {
    
    const ACTION_FORCE_START = 'force_start';
    const ACTION_FORCE_START_SOLO = 'force_start_solo';
    const ACTION_SET_RESULT = 'set_result';
    const ACTION_GET_INFO = 'get_info';
    
    
    
    public static function sendResult($result) 
    {
        die(json_encode($result, JSON_FORCE_OBJECT));
    }
    
    
    public static function extractInput()
    {
        $vars = [];
        
        if($_REQUEST['user_id'])
        {
            $vars['userId'] = intval($_REQUEST['user_id']);
            CurrentUserList::put($vars['userId']);
            $vars['queue'] = new UserQueue();
            //$vars['user'] = 
        }
        if($_REQUEST['game_id'])
        {
            $vars['gameId'] = intval($_REQUEST['game_id']);
            $vars['game'] = new Game($vars['gameId']);
        }
        if($_REQUEST['action'])
            $vars['action'] = $_REQUEST['action'];
        
        if($_REQUEST['words'])
            $vars['words'] = (json_decode($_REQUEST['words']) ?: []);
        
            
        return $vars;
    }
}
