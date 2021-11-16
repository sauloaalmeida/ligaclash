<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="liga.css">
        <title>Resultado da Liga</title>
    </head>
    <body>
        <?php
        include './ligahttp.php';
        
        $clanTagReq = $_REQUEST["tagClan"];
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
        
        function getOpponentThLevel($jsArray, $oppnt, $opponentTag){
            foreach ($jsArray[$oppnt]["members"] as $opponentMember) {
                
                //echo "oppTag -> ".$opponentMember["tag"] ." thLevel -> ". $opponentMember["townhallLevel"];
                
                if($opponentMember["tag"] == $opponentTag){
                    return $opponentMember["townhallLevel"];
                }
            }
        }
        
        $httpLigaInfo = new LigaHttp($url);
        $result = $httpLigaInfo->get();
                
        $jsonArray = json_decode($result,true);
        
        $resultado = Array();
       
        
        foreach ($jsonArray["rounds"] as $round){
            
            $warTag = getClanWarTag($round["warTags"], $clanTagReq);
            
            if($warTag == "#0"){
                echo "Não disponível <br/>";
            }else{
                
                $urlWtag = 'https://api.clashofclans.com/v1/clanwarleagues/wars/%23'.substr($warTag, 1);
                        
                $httpWarTagInfo = new LigaHttp($urlWtag);
                $resultWar = $httpWarTagInfo->get();
                $jsonArray = json_decode($resultWar,true); 

                //defindo em que parte da arvore vai buscar os dados
                if($jsonArray["clan"]["tag"] == "#".$clanTagReq){
                    $clan = "clan";
                    $opponent = "opponent";
                }else{
                    $clan = "opponent";
                    $opponent = "clan";
                }

                $clanNome = $jsonArray[$clan]["name"];
                $oponenteNome = $jsonArray[$opponent]["name"];
                $oponenteTag = $jsonArray[$opponent]["tag"]; 
                $state = $jsonArray["state"];
                
                if($state == "warEnded"){
                
                ?>
                <table id="tabela-liga" >
                    <tr><td colspan="7" align='center' ><?php echo $clanNome ." X ". $oponenteNome ." / Status guerra: ". $state ; ?></td></tr>
            <?php
                    if ($state != "preparation"){
                        
                        $ganhou = false;
                
                        if($jsonArray[$clan]["stars"] > $jsonArray[$opponent]["stars"] || ($jsonArray[$clan]["stars"] = $jsonArray[$opponent]["stars"] && $jsonArray[$clan]["destructionPercentage"] > $jsonArray[$opponent]["destructionPercentage"])){
                           $ganhou = true; 
                        }

                    echo "<tr>";
                    echo "<td colspan='7' align='center'> ";
                    echo "<span style='color:".(($ganhou)?"green":"red")."'> ".$clanNome." / Ataques:".$jsonArray[$clan]["attacks"];
                    echo " / Estrelas:".$jsonArray[$clan]["stars"];
                    echo " / Destruicao:".$jsonArray[$clan]["destructionPercentage"];
                    echo " / Ataques restantes:". ($jsonArray["teamSize"] - $jsonArray[$clan]["attacks"]); 
                    echo "</span><br/>X<br/><span style='color:".(($ganhou)?"red":"green")."'> ".$oponenteNome." / Ataques:".$jsonArray[$opponent]["attacks"];
                    echo " / Estrelas:".$jsonArray[$opponent]["stars"];
                    echo " / Destruicao:".$jsonArray[$opponent]["destructionPercentage"];
                    echo " / Ataques restantes:". ($jsonArray["teamSize"] - $jsonArray[$opponent]["attacks"]); 
                    echo "</span></td>";
                    echo "</tr>";

                    echo "<tr>";
                    echo "<th>Nome</th>";
                    echo "<th>Nvl CV atacante</th>";
                    echo "<th>Nvl CV atacado</th>";
                    echo "<th>Dip</th>";
                    echo "<th>Estrelas</th>";
                    echo "<th>% Destruição</th>";
                    echo "<th>Calc Pontos</th>";
                    echo "</tr>";

                    foreach ($jsonArray[$clan]["members"] as $membro) {

                        $atacou = false;
                        
                        if(!isset($resultado[$membro["tag"]])){
                            $resultado[$membro["tag"]] = ["jogador" => $membro["name"], "WO" => 0, "Estrelas" =>0, "DIPs" => 0, "UPs" => 0, "Calc" =>0, "QtdWars" =>0, "Destruicao" => 0];
                        }

                        //echo "opponente: ". $opponent;

                        if(isset($membro["attacks"])){
                            $opponentThLevel = getOpponentThLevel($jsonArray, $opponent, $membro["attacks"][0]["defenderTag"]);
                            //echo  " - DefenderTag". $membro["attacks"][0]["defenderTag"];
                            $atacou = true;
                            $resultado[$membro["tag"]]["QtdWars"]++;
                        }
                        
                        

                        
                        echo "<tr>";
                        echo "<td>".$membro["name"]."</td>";
                        echo "<td align='center'>".$membro["townhallLevel"]."</td>";
                        if($atacou){
                            echo "<td align='center'>".$opponentThLevel."</td>";
                            $cellColor = "";
                            if($membro["townhallLevel"] > $opponentThLevel){
                               $cellColor = "red"; 
                            }
                            if($membro["townhallLevel"] < $opponentThLevel){
                                $cellColor = "green";
                            }
                            echo "<td align='center' ".($cellColor !== "" ?" style='background-color:".$cellColor."' ":"")."  >".(($membro["townhallLevel"] > $opponentThLevel)? "Sim" : "Não")."</td>";
                            echo "<td align='center'>".$membro["attacks"][0]["stars"]."</td>"; $resultado[$membro["tag"]]["Estrelas"] += $membro["attacks"][0]["stars"]; 
                            echo "<td align='center'>".$membro["attacks"][0]["destructionPercentage"]."</td>"; $resultado[$membro["tag"]]["Destruicao"] += $membro["attacks"][0]["destructionPercentage"];
                            echo "<td align='center'>";
                            if($membro["townhallLevel"] == $opponentThLevel){
                                echo $membro["attacks"][0]["stars"]; 
                                $resultado[$membro["tag"]]["Calc"] += $membro["attacks"][0]["stars"];
                            }else if($membro["townhallLevel"] > $opponentThLevel){
                                echo ($membro["attacks"][0]["stars"] - 1);
                                $resultado[$membro["tag"]]["Calc"] += $membro["attacks"][0]["stars"]-1;
                                $resultado[$membro["tag"]]["DIPs"]++;
                            }else{
                                echo ($membro["attacks"][0]["stars"] + 1);
                                $resultado[$membro["tag"]]["Calc"] += $membro["attacks"][0]["stars"]+1;
                                $resultado[$membro["tag"]]["UPs"]++;
                            }
                            echo "</td>";
                        }else{
                            $resultado[$membro["tag"]]["WO"]++;
                            echo "<td colspan='5' style='background-color:".(($state  == "inWar")? "white" : "red")."' >".($state  == "inWar"? "Falta Atacar": "Não Atacou")."</td>";
                        }
                        echo "</tr>";
                    }

                }
                
                echo "</table><br/><br/>";
            }
          }  
        }
        
//echo print_r($resultado, true);
function cmp($a, $b){   
    if ($a["WO"] < $b["WO"]){
        return -1;
    }else if($a["WO"] > $b["WO"]){
        return 1;
    }else {
        
        if($a["Calc"] < $b["Calc"]){
            return 1;
        }else if($a["Calc"] > $b["Calc"]){
            return -1;
        }else {
            if ($a["DIPs"] < $b["DIPs"]) {
                return -1;
            } else if ($a["DIPs"] > $b["DIPs"]){
                return 1;
            }else{
                if ($a["Destruicao"] < $b["Destruicao"]) {
                return 1;
                } else if ($a["DIPs"] > $b["DIPs"]){
                    return -1;
                }else {                    
                    return 0;
                }
            }
            
        }
            
    }
    
}


usort($resultado, "cmp");
        ?>
                    <table id="tabela-liga">
                    <tr><td colspan="9" align='center' >Resultado Geral</td></tr>
                               <tr>
                               <th>Nome</th>
                               <th>Classificacao</th>
                               <th>QtdWars</th>
                               <th>WOs</th>
                               <th>Estrelas</th>
                               <th>%Destruicao</th>
                               <th>DIPs</th>
                               <th>UPs</th>
                               <th>Calc Final</th>
                               </tr>
                   <?php 
                           foreach ($resultado as $key => $value) {
                               echo "<tr>";
                               echo "<td>".$value["jogador"]."</td>";
                               echo "<td>".($key + 1)."</td>";
                               echo "<td>".$value["QtdWars"]."</td>";
                               echo "<td>".$value["WO"]."</td>";
                               echo "<td>".$value["Estrelas"]."</td>";
                               echo "<td>".$value["Destruicao"]."</td>";
                               echo "<td>".$value["DIPs"]."</td>";
                               echo "<td>".$value["UPs"]."</td>";
                               echo "<td>".$value["Calc"]."</td>";
                               echo "</tr>";
                           }                        
                   ?>
                    </table>
                    
        <a href="javascript:history.back()">Voltar</a>            
    </body>
</html>
