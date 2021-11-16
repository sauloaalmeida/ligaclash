
<?php

class LigaHttp{
    
    // property declaration
    private $url = null;

    function __construct($url) {
        $this->url = $url;
    }
    
    private function executeCurl($action, $contentType){
        
        $ch = curl_init($this->url);                                                                      
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $action);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
         
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(                         
            'Content-Type: '.$contentType,                                   
            'authorization: Bearer ToKeNdeAcesso')                          
        );    
        
        $result = curl_exec($ch);
        
        curl_close($ch);
        
        return $result;
    }
    
    public function get(){ 
        $result = $this->executeCurl('GET', "application/json");
        return $result;
    }
    
}

?>
