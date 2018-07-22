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
        print_r($this->response);
        return $this->response;
    }
    
    
    
    /**
     * re-form result from service format
     */
    private function formatResponse()
    {
        $formatedResponse = ['error' => ''];
        $this->response = json_decode($this->response, true);
        if(!array_key_exists('def', $this->response))
            $formatedResponse['error'] = 'empty response';
        elseif(empty($this->response['def']))
            $formatedResponse['error'] = 'word not found';
        else
        {
            $isNoun = true;
            $formatedResponse['pos'] = $this->response['def'][0]['pos'];
            if($this->response['def'][0]['pos'] != 'noun')
            {
                $formatedResponse['error'] = 'is not noun';
                $isNoun = false;
            }
            foreach($this->response['def'][0]['tr'] as $variant)
            {
                if(($isNoun && $variant['pos'] == 'noun') || !$isNoun)
                    $formatedResponse['translate'][] = $variant['text'];
                
                if($formatedResponse['translate'] && count($formatedResponse['translate']) > 3)
                    break;
                    
            }
            $formatedResponse['translate'] = implode(', ', $formatedResponse['translate']);
            $this->response = $formatedResponse;
        }
    }
    
    
    public function getWordInfo($word)
    {
        if(!$word)
            throw new \Exception('empty word');
        
        $this->request(static::METHOD_FIND, ['text' => $word]);
    }
    
    
}
