<?php


final class YandexDictonary implements InterfaceDictonary
{
    
    const NAME = 'yandex';
    
    const METHOD_FIND = 'lookup';    
    const BASE_URL = 'https://dictionary.yandex.net/api/v1/dicservice.json/';
    
    private $key;
    
    private $lang = 'en';
    private $responjse;
    
    public function __construct($lang = 'en') {
        $this->setLang($lang);
        $this->loadKey();
    }
    
    private function loadKey()
    {
        $this->key = file_get_contents('./settings/yandex_key');
    }
    

    public function setLang($lang)
    {
        if($lang)
            $this->lang = $lang . '-' . $lang;  // format 'en-en'  
        
    }
    
    private function request($method, $params)
    {
        if(!$method)
            throw new \Exception('$method is empty');       
        
        $params = array_merge(['key' => $this->key, 'lang' => $this->lang], $params);
        $url = static::BASE_URL . $method . '?' . http_build_query($params);
        print $url . '<br>';
        $this->response = file_get_contents($url);
        $this->formatResponse();
        return $this->response;
    }
    
    
    
    /**
     * re-form result from service format
     */
    private function formatResponse()
    {
        $this->response = json_decode($this->response, true);
    }
    
    
    public function getWordInfo($word)
    {
        if(!$word)
            throw new \Exception('empty word');
        
        $this->request(static::METHOD_FIND, ['text' => $word]);
    }
    
    
}
