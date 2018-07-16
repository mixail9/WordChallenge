<?php
if(!intval($_GET['user_id']))
    die('ok');

$games = [];
$userId = intval($_GET['user_id']);
$games = json_decode(file_get_contents('games.txt'), true);


function sendResult($result) 
{
    die(json_encode($result, JSON_FORCE_OBJECT));
}


function clearQueue($queue) 
{
    foreach($queue as $userId => $queueParams)
    {
        if(time() - $queueParams['time'] > 60)
            unset($queue[$userId]);
    }
}


function createBot(&$queue)
{
    $id = rand(100, 999);
    $queue[$id] = ['name' => 'bot_' . $id, 'priority' => 0, 'time' => time()];
}

function createBotWords(&$gameData) 
{
    //  search bots
    foreach($gameData['user_list'] as $uid => $user)
    {
    	// is bot
    	if(strpos($user['name'], 'bot_') !== false)
    	    $gameData['word_by_user'][$uid] = [$user['name']];
    }
}


function setWordsId(&$game)
{
    $uniqueWordList = [];
    foreach($game['word_by_user'] as $uid => $word)
    {
        $wordOneUser = [];
        foreach($word as $oneWord)
        {            
            if(!$uniqueWordList[$oneWord])
                $uniqueWordList[$oneWord] = count($uniqueWordList) + 5;
            $wordOneUser[$uniqueWordList[$oneWord]] = $oneWord;
        }
        $game['word_by_user'][$uid] = $wordOneUser;
                
    }
    $game['word_by_user'][1] = array_flip($uniqueWordList);
}


function setVoted(&$game, $votes)
{
    /*
    foreach($votes as $wordId => $voteQuantity)
    {
        foreach($game['word_by_user'] as $uid => $word)
        {
            foreach($word as $wid => $oneWord)
            {
                if($wordId == $wid)
                    $game['votes'][]
            }
        }  
    }
     * 
     */
}


function tryStartGame(&$queue, &$games) 
{
    if(count($queue) > 6)
    {
        $newGameUsers = [];
        // select 4 user with max priority
        for($i = 0; $i < 4; $i++)
        {
            $priorityUserId = 0;
            foreach($queue as $userId => $queuwInfo)
            {
                if(!$priorityUserId || $queuwInfo['priority'] > $queue[$priorityUserId]['priority'])
                {
                    $priorityUserId = $userId;
                }
            }
            $newGameUsers[$priorityUserId] = ($queue[$priorityUserId]['name'] ?: $priorityUserId);
            unset($queue[$priorityUserId]);         
        }
        
        $newGame = ['id' => rand(1000, 9999), 'user_list' => $newGameUsers];
        $games[$newGame['id']] = $newGame;
        return $newGame;
    }
    return ['queue' => count($queue)];
}

        
if($_GET['search_new_game']) 
{
    //  user already in game
    foreach($games as $game)
    {
        if(in_array($userId, $game['user_list']))
            sendResult($game);

    }
    $waitingList = json_decode(file_get_contents('search_new_game_list.txt'), true);
    clearQueue($waitingList);
    $waitingList[$userId] = ['priority' => 10, 'time' => time()];
    $result = tryStartGame($waitingList, $games); 
    
    if($_GET['force_start'])
    {
        for($i = 0; $i < 10; $i++)
            createBot($waitingList);
    }
    
    file_put_contents('games.txt', json_encode($games, JSON_FORCE_OBJECT));
    file_put_contents('search_new_game_list.txt', json_encode($waitingList, JSON_FORCE_OBJECT));
    sendResult($result);
}       

if($_GET['get_game_info']) 
{
    $gameId = intval($_GET['game_id']);
    if($games[$gameId] && in_array($userId, $games[$gameId]['user_list']))
        sendResult($games[$gameId]);
    sendResult(['id' => 0]);
}   


if($_GET['set_words_and_wait_result']) 
{
    $gameId = intval($_GET['game_id']);
    if($games[$gameId] && in_array($userId, $games[$gameId]['user_list']))
    {
    	$games[$gameId]['word_by_user'][$userId] = json_decode($_GET['set_words_and_wait_result'], true)['w'];
    	
    	/*print '<pre>';
    	print_r($_GET['set_words_and_wait_result']);
   	var_dump(json_decode($_GET['set_words_and_wait_result'], true));
    	print_r($games[$gameId]);*/
    	
    	createBotWords($games[$gameId]);
        setWordsId($games[$gameId]);
    	file_put_contents('games.txt', json_encode($games, JSON_FORCE_OBJECT));
    	
    	// wait words from all players
    	$startTime = time();
    	while(count($games[$gameId]['word_by_user']) < count($games[$gameId]['user_list']) && time() - $startTime < 20)
    	{
    	    sleep(1);
    	    $games = json_decode(file_get_contents('games.txt'), true);
    	}
        sendResult($games[$gameId]);
    	
    }
    sendResult(['error' => 9]);
}

print 'ok';











extract(IOHelper::extractInput());

switch($action)
{
    case IOHelper::ACTION_FORCE_START_SOLO:
        $newGame = Game::startGame([$userId]);
        for($i = 1; $i < Game::USER_PER_GAME; $i++)
            $newGame->addBot();
        IOHelper::sendResult(Game::getFormatedDataForResponse($newGame->getId()));
        break;
    case IOHelper::ACTION_FORCE_START:
        $users = $queue->getUsersForGame();
        $newGame = Game::startGame($users);
        for($i = count($users); $i < Game::USER_PER_GAME; $i++)
            $newGame->addBot();
        IOHelper::sendResult(Game::getFormatedDataForResponse($newGame->getId()));
        break;
    case IOHelper::ACTION_SET_RESULT:
        if(!$game->hasUser($userId))
            throw new \Exception('user_id must be defined');
        if(!$game->isActive())
            throw new \Exception('game already closed');
        $game->setWords($userId, $words);
        IOHelper::sendResult(Game::getFormatedDataForResponse($game->getId()));
        break;      
    
}










?>
