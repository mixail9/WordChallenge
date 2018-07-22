<?php

spl_autoload_register(function($className){
    include __DIR__ . '/' . $className . '.php';
});


print '<pre>';
$dict = DictonaryHelper::loadDictonary('yandex');
$dict->getWordInfo($_GET['w']);
die();


extract(IOHelper::extractInput());

switch($action)
{
    case IOHelper::ACTION_FORCE_START_SOLO:
        if($game = Game::findGameByUser($userId))
            IOHelper::sendResult(Game::getFormatedDataForResponse($game->getId()));
        $newGame = Game::startGame([$userId]);
        for($i = 1; $i < Game::USER_PER_GAME; $i++)
            $newGame->addBot();
        IOHelper::sendResult(Game::getFormatedDataForResponse($newGame->getId()));
        break;
    case IOHelper::ACTION_FORCE_START:
        if($game = Game::findGameByUser($userId))
            IOHelper::sendResult(Game::getFormatedDataForResponse($game->getId()));
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
    case IOHelper::ACTION_GET_INFO:
        if(!$game->hasUser($userId))
            throw new \Exception('user_id must be defined');
        IOHelper::sendResult(Game::getFormatedDataForResponse($game->getId()));
        break;      
    
}
