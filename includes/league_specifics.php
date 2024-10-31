<?
function LeagueSpecifics($lid, $longname, $condensed=FALSE) {
    Global $sim, $link;
    $tt = new TeamTable($lid, $link);
    if($sim==1 && !$condensed) $tt->simulation();

    if(strstr($longname, "liga")) {
        // extraliga
        $tt->games_total = 50;
        $tt->playoff_line = array(6, 10);
        $tt->playoff_wins = 4;
        $tt->add_conference("");
        $tt->add_division("", 0);
        $tt->add_teams(array("SBA", "ZVO", "NIT", "MII", "KOS", "POP", "SNV", "BBS", "TRE", "ZIL", "NZO", "LMI"), "body desc, wins desc, diff desc, zapasov asc, goals desc, losts asc", 0, 0);
    }
    if(strstr($longname, "NHL")) {
        // NHL
        $tt->games_total = 82;
        $tt->playoff_line = 8;
        $tt->playoff_wins = 4;
        $tt->add_conference(LANG_TEAMTABLE_WESTCONF1);
        $tt->add_conference(LANG_TEAMTABLE_EASTCONF1);
        $tt->add_division(LANG_TEAMTABLE_CENTRAL, 0);
        $tt->add_division(LANG_TEAMTABLE_PACIFIC, 0);
        $tt->add_division(LANG_TEAMTABLE_ATLANTIC, 1);
        $tt->add_division(LANG_TEAMTABLE_METROPOLITAN, 1);
        $tt->add_teams(array("STL", "COL", "CHI", "MIN", "DAL", "WPG", "NSH", "UTA"), "body desc, zapasov asc, wins desc, diff desc", 0, 0);
        $tt->add_teams(array("ANA", "SJS", "LAK", "SEA", "VAN", "CGY", "EDM", "VGK"), "body desc, zapasov asc, wins desc, diff desc", 0, 1);
        $tt->add_teams(array("BOS", "MTL", "TBL", "DET", "TOR", "OTT", "FLA", "BUF"), "body desc, zapasov asc, wins desc, diff desc", 1, 0);
        $tt->add_teams(array("PIT", "NYR", "PHI", "CBJ", "WSH", "NJD", "CAR", "NYI"), "body desc, zapasov asc, wins desc, diff desc", 1, 1);
    }
    if(strstr($longname, "KHL")) {
        // KHL
        $tt->games_total = 68;
        $tt->playoff_line = 8;
        $tt->playoff_wins = 4;
        $tt->add_conference(LANG_TEAMTABLE_WESTCONF1);
        $tt->add_conference(LANG_TEAMTABLE_EASTCONF1);
        $tt->add_division(LANG_TEAMTABLE_BOBROV, 0);
        $tt->add_division(LANG_TEAMTABLE_TARASOV, 0);
        $tt->add_division(LANG_TEAMTABLE_KHARLAMOV, 1);
        $tt->add_division(LANG_TEAMTABLE_CHERNYSHEV, 1);
        $tt->add_teams(array("SPA", "VIT", "SOC", "PET", "TNN"), "body desc, wins desc, diff desc, zapasov asc, id asc", 0, 0);
        $tt->add_teams(array("DYN", "DMN", "KUN", "LOK", "CSK", "SEV"), "body desc, wins desc, diff desc, zapasov asc, id asc", 0, 1);
        $tt->add_teams(array("AVT", "AKB", "MMG", "NKH", "TRA", "LAD"), "body desc, wins desc, diff desc, zapasov asc, id asc", 1, 0);
        $tt->add_teams(array("AVA", "SIB", "AMU", "BAR", "SAL", "VLA"), "body desc, wins desc, diff desc, zapasov asc, id asc", 1, 1);
    }
    if(strstr($longname, "MS 20")) {
        if($lid<36) {
          // MS pred 2012
          $tt->games_total = 3;
          $tt->playoff_line = 3;
          $tt->playoff_wins = 1;
          $tt->add_conference("");
          $tt->add_division("Skupina A", 0);
          $tt->add_division("Skupina B", 0);
          $tt->add_division("Skupina C", 0);
          $tt->add_division("Skupina D", 0);
          $tt->add_teams("", "body desc, diff desc, goals desc, wins desc, losts asc", 0, 0);
          $tt->add_teams("", "body desc, diff desc, goals desc, wins desc, losts asc", 0, 1);
          $tt->add_teams("", "body desc, diff desc, goals desc, wins desc, losts asc", 0, 2);
          $tt->add_teams("", "body desc, diff desc, goals desc, wins desc, losts asc", 0, 3);
        }
        else {
          // MS po 2012
          $tt->games_total = 7;
          $tt->playoff_line = array(4,7);
          $tt->playoff_wins = 1;
          $tt->add_conference("");
          $tt->add_division("Skupina A", 0);
          $tt->add_division("Skupina B", 0);
          $tt->add_teams("", "body desc, zapasov asc, diff desc, goals desc, wins desc, losts asc", 0, 0);
          $tt->add_teams("", "body desc, zapasov asc, diff desc, goals desc, wins desc, losts asc", 0, 1);

        }
    }
    if(strstr($longname, "MS U20 20")) {
        // MS U20
        $tt->games_total = 4;
        $tt->playoff_line = 4;
        $tt->playoff_wins = 1;
        $tt->add_conference("");
        $tt->add_division("Skupina A", 0);
        $tt->add_division("Skupina B", 0);
        $tt->add_teams("", "body desc, diff desc, goals desc, wins desc, losts asc", 0, 0);
        $tt->add_teams("", "body desc, diff desc, goals desc, wins desc, losts asc", 0, 1);
    }
    if(strstr($longname, "ZOH ")) {
        // ZOH
        if($lid==15) {
          $tt->games_total = 5;
          $tt->playoff_line = 4;
          $tt->playoff_wins = 1;
          $tt->add_conference("");
          $tt->add_division("Skupina A", 0);
          $tt->add_division("Skupina B", 0);
          $tt->add_teams("", "body desc, diff desc, goals desc, wins desc, losts asc", 0, 0);
          $tt->add_teams("", "body desc, diff desc, goals desc, wins desc, losts asc", 0, 1);
        }
        else {
          $tt->games_total = 3;
          $tt->playoff_line = 1;
          $tt->playoff_wins = 1;
          $tt->add_conference("");
          $tt->add_division("Skupina A", 0);
          $tt->add_division("Skupina B", 0);
          $tt->add_division("Skupina C", 0);
          $tt->add_teams("", "body desc, diff desc, goals desc, wins desc, losts asc", 0, 0);
          $tt->add_teams("", "body desc, diff desc, goals desc, wins desc, losts asc", 0, 1);
          $tt->add_teams("", "body desc, diff desc, goals desc, wins desc, losts asc", 0, 2);

        }
    }
    if(strstr($longname, "Svetový pohár ")) {
        // World Cup
        $tt->games_total = 3;
        $tt->playoff_line = 2;
        $tt->playoff_wins = 1;
        $tt->add_conference("");
        $tt->add_division("Skupina A", 0);
        $tt->add_division("Skupina B", 0);
        $tt->add_teams("", "body desc, diff desc, goals desc, wins desc, losts asc", 0, 0);
        $tt->add_teams("", "body desc, diff desc, goals desc, wins desc, losts asc", 0, 1);
    }
    if(strstr($longname, "Loto Cup ") || strstr($longname, "Škoda Cup ") || strstr($longname, "Nemecký pohár ") || strstr($longname, "Arosa Challenge ") || strstr($longname, "Slovakia Cup ") || strstr($longname, "Swiss Ice Hockey Challenge ") || strstr($longname, "Kaufland Cup ") || strstr($longname, "NaturEnergie Challenge ")) {
        // malé turnaje
        $tt->add_conference("");
        $tt->add_division("", 0);
        $tt->add_teams("", "body desc, diff desc, goals desc, wins desc, losts asc", 0, 0);
    }
    return $tt;
}
?>