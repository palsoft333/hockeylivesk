<?
function generateGamesTable($lid) {
    $q = mysql_query("SELECT * FROM 2004leagues WHERE id='".$lid."'");
    $f = mysql_fetch_assoc($q);
    if($f["el"]==1) $w = mysql_query("SELECT * FROM el_matches WHERE league='".$lid."' ORDER BY datetime");
    else $w = mysql_query("SELECT m.*, t.skupina FROM 2004matches m LEFT JOIN 2004teams t ON t.shortname=m.team1short && t.league='".$lid."' WHERE m.league='".$lid."' ORDER BY m.datetime;");
    $table = '
    <table class="table table-sm table-striped table-responsive-sm">
        <tr>
            <th>Fáza</th>
            <th>Dátum</th>
            <th>Čas (SEČ)</th>
            '.($f["el"]==0 ? '<th>Skupina</th>':'').'
            <th>Zápas</th>
            <th>Výsledok</th>
        </tr>
    ';
    while($game = mysql_fetch_assoc($w)) {
        $table .= '
        <tr>
            <td>'.($game["po_type"]==null ? 'základná časť':($game["po_type"]=="QF" ? 'štvrťfinále':($game["po_type"]=="SF" ? 'semifinále':($game["po_type"]=="F" ? 'finále':($game["po_type"]=="B" ? 'súboj o bronz':'kvalifikácia'))))).'</td>
            <td>'.date("j.n.Y", strtotime($game["datetime"])).'</td>
            <td>'.date("G:i", strtotime($game["datetime"])).'</td>
            '.($f["el"]==0 ? '<td>'.($game["po_type"]==null ? $game["skupina"]:'-').'</td>':'').'
            <td><a href="'.sprintf("/game/%d%d-%s",$game["id"],$f["el"],SEOtitle($game["team1long"].' vs. '.$game["team2long"])).'">'.$game["team1long"].' vs. '.$game["team2long"].'</a></td>
            <td>'.($game["kedy"]=="konečný stav" ? '<a href="'.sprintf("/report/%d%d-%s",$game["id"],$f["el"],SEOtitle($game["team1long"].' vs. '.$game["team2long"])).'">'.$game["goals1"].':'.$game["goals2"].'</a>':'-').'</td>
        </tr>
        ';
    }
    $table = str_replace("Slovensko", "<span class='font-weight-bold'>Slovensko</span>", $table);
    $table = str_replace("Európa", "><span class='font-weight-bold'>Európa</span><", $table);
    $table .= '</table>';
    return $table;
}

function generateRoster($tshort, $lid) {
    $q = mysql_query("SELECT * FROM 2004leagues WHERE id='".$lid."'");
    $f = mysql_fetch_assoc($q);
    if($f["el"]==1) $w = mysql_query("SELECT id, name, pos FROM `el_players` WHERE teamshort='".$tshort."' && league='".$lid."' UNION SELECT id, name, 'GK' as pos FROM `el_goalies` WHERE teamshort='".$tshort."' && league='".$lid."' ORDER BY pos DESC, name");
    else $w = mysql_query("SELECT id, name, pos FROM `2004players` WHERE teamshort='".$tshort."' && league='".$lid."' UNION SELECT id, name, 'GK' as pos FROM `2004goalies` WHERE teamshort='".$tshort."' && league='".$lid."' ORDER BY pos DESC, name");

    if(mysql_num_rows($w)>0) {
        $table = '
        <table class="table table-sm table-striped w-50">
            <tr>
                <th>Pozícia</th>
                <th>Meno</th>
            </tr>
        ';
        while($player = mysql_fetch_assoc($w)) {
            $table .= '
            <tr>
                <td>'.$player["pos"].'</td>
                <td><a href="'.sprintf("/%s/%d%d-%s",($player["pos"]=="GK" ? 'goalie':'player'),$player["id"],$f["el"],SEOtitle($player["name"])).'">'.$player["name"].'</a></td>
            </tr>
            ';
        }
        $table .= '</table>';
    }
    return $table;
}