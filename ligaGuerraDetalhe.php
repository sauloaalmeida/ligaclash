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
        <title>Resultado da guerra</title>
    </head>
    <body>
        <?php
        include './ligahttp.php';
        
        
        function getOpponentThLevel($jsArray, $oppnt, $opponentTag){
            foreach ($jsArray[$oppnt]["members"] as $opponentMember) {
                
                //echo "oppTag -> ".$opponentMember["tag"] ." thLevel -> ". $opponentMember["townhallLevel"];
                
                if($opponentMember["tag"] == $opponentTag){
                    return $opponentMember["townhallLevel"];
                }
            }
        }
        
        $clanTag = trim($_REQUEST["clanTag"]);
        $clanTag = (substr( $clanTag, 0, 1 ) === "#")? substr( $clanTag, 1) : $clanTag;
        $warTag = $_REQUEST["warTag"];
        $urlWtag = 'https://api.clashofclans.com/v1/clanwarleagues/wars/%23'.$warTag;
                        
        $httpWarTagInfo = new LigaHttp($urlWtag);
        $result = $httpWarTagInfo->get();
        $jsonArray = json_decode($result,true); 
        
        //defindo em que parte da arvore vai buscar os dados
        if($jsonArray["clan"]["tag"] == "#".$clanTag){
            $clan = "clan";
            $opponent = "opponent";
        }else{
            $clan = "opponent";
            $opponent = "clan";
        }
        
        $clanNome = $jsonArray[$clan]["name"];
        $oponenteNome = $jsonArray[$opponent]["name"];
        $oponenteTag = substr(trim($jsonArray[$opponent]["tag"]),1); 
        $state = $jsonArray["state"];
        ?>
        <table id="tabela-liga">
            <tr><td colspan="7" align='center' ><?php echo $clanNome ." X <a href='ligaDadosClan.php?tagClan=".$oponenteTag."' >". $oponenteNome ."</a> / Status guerra: ". $state ; ?></td></tr>
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
                    
                    //echo "opponente: ". $opponent;
                    
                    if(isset($membro["attacks"])){
                        $opponentThLevel = getOpponentThLevel($jsonArray, $opponent, $membro["attacks"][0]["defenderTag"]);
                        //echo  " - DefenderTag". $membro["attacks"][0]["defenderTag"];
                        $atacou = true;
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
                        echo "<td align='center' ".($cellColor !== "" ?" style='background-color:".$cellColor."' ":"")." >".(($membro["townhallLevel"] > $opponentThLevel)? "Sim" : "Nao")."</td>";
                        echo "<td align='center'>".$membro["attacks"][0]["stars"]."</td>";
                        echo "<td align='center'>".$membro["attacks"][0]["destructionPercentage"]."</td>";
                        echo "<td align='center'>";
                        if($membro["townhallLevel"] == $opponentThLevel){
                            echo $membro["attacks"][0]["stars"];
                        }else if($membro["townhallLevel"] > $opponentThLevel){
                            echo ($membro["attacks"][0]["stars"] - 1);
                        }else{
                            echo ($membro["attacks"][0]["stars"] + 1);
                        }
                        echo "</td>";
                    }else{
                        echo "<td colspan='5' style='background-color:".(($state  == "inWar")? "white" : "red")."' >".($state  == "inWar"? "Falta Atacar": "Não Atacou")."</td>";
                    }
                    echo "</tr>";
                }
            
            }
            
            ?>
            
        </table>
        <a href="ligaDadosClan.php?tagClan=<?php echo $clanTag;?>">Voltar</a>
    </body>
</html>
