<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        <?php
        include './ligahttp.php';
        
        $clanTagReq = trim($_REQUEST["tagClan"]);
        $clanTagReq = (substr( $clanTagReq, 0, 1 ) === "#")? substr( $clanTagReq, 1) : $clanTagReq;
        $url = "https://api.clashofclans.com/v1/clans/%23".$clanTagReq."/currentwar/leaguegroup";
        
        function getClanWar($wtag){
            
            $urlWtag = 'https://api.clashofclans.com/v1/clanwarleagues/wars/%23'.substr($wtag, 1);
                        
            $httpWarTagInfo = new LigaHttp($urlWtag);
            $result = $httpWarTagInfo->get();
            return json_decode($result,true);        
        }
        
        function getClanWarTag($wtags, $cTag){
           
            $cTagCompleta = "#".$cTag;
            
            foreach ($wtags as $wtag) {
                
                if($wtag == "#0"){
                    return "#0";
                }
                
                $jsonArray = getClanWar($wtag);
                
                if($jsonArray["clan"]["tag"] == $cTagCompleta || $jsonArray["opponent"]["tag"] == $cTagCompleta){
                    return $wtag; 
                }              
            }         
            
            return "#0";
        }
        
        $httpLigaInfo = new LigaHttp($url);
        $result = $httpLigaInfo->get();
                
        $jsonArray = json_decode($result,true);
        
        $primeiroLoop = true;
        $nomeCla = "";
        
        echo "<b>Clas participantes: </b><br/><br/>";
        
        if (isset($jsonArray["clans"])){
        
            foreach ($jsonArray["clans"] as $clan){
            
                if($primeiroLoop){
                    $primeiroLoop = false;
                }else{
                    echo ", ";
                } 
                
                echo "<a href='ligaDadosClan.php?tagClan=".substr($clan["tag"], 1)."'>".$clan["name"]."</a> ";
                
                if(substr($clan["tag"], 1) == $clanTagReq){
                    $nomeCla = $clan["name"];
                }
            }
       
            echo "<br/><br/>";
        
        
            echo "<b>Guerras do clã ".$nomeCla.": </b><br/><br/>";
            foreach ($jsonArray["rounds"] as $round){
                
                $warTag = getClanWarTag($round["warTags"], $clanTagReq);
                
                if($warTag == "#0"){
                    echo "Não disponível <br/>";
                }else{
                    echo "<a href='ligaGuerraDetalhe.php?clanTag=".$clanTagReq."&warTag=".substr($warTag, 1)."'>Detalhes guerra dia ". ++$dia. "</a><br/>";
                }
            }
        }
        
        ?>
        
        <br/><a href="ligaResultadoClan.php?tagClan=<?php echo $clanTagReq; ?>">Resultado Liga</a>
        <br/><br/><a href="index.php">Consultar Outro Clã</a>
    </body>
</html>
