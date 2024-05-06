<?
class TeamTable {
    public $lid;
    public $name;
    public $el;
    public $teams_table;
    public $wpoints;
    public $groups;
    public $end_basic;
    public $games_total;
    public $playoff_line;
    public $playoff_wins;
    public $conference;

    public function __construct($lid) {
        $q = mysql_query("SELECT * FROM 2004leagues WHERE id='".$lid."'");
        $f = mysql_fetch_array($q);
        if($_SESSION[logged]) {
          $w = mysql_query("SELECT user_favteam FROM e_xoops_users WHERE uid='$_SESSION[logged]'");
          $e = mysql_fetch_array($w);
          $this->favteam = $e[user_favteam];
        }
        $this->lid = $lid;
        $this->name = $f[longname];
        $this->el = $f[el];
        $this->wpoints = $f[points];
        $this->groups = explode("|", $f[groups]);
        $this->end_basic = $f[endbasic];
    }
    
    public function add_conference($conference_name) {
        $this->conference[]["name"] = $conference_name;
    }
    
    public function add_division($division_name, $to_conference) {
        $this->conference[$to_conference]["division"][]["name"] = $division_name;
    }

    public function simulation() {
        $this->teams_table = "el_simulation";
    }

    public function add_teams($teams, $orderby, $to_conference, $to_division) {
        $teams = implode("','", $teams);
        $i=0;
        if($this->teams_table=="") {
            if($this->el==1) $this->teams_table = "el_teams";
            else $this->teams_table = "2004teams";
        }
        if(count($this->groups)>1) {
            $group = $this->groups[$to_division];
            $q = mysql_query("SELECT *, goals-ga as diff FROM ".$this->teams_table." WHERE skupina='".$group."' && league='".$this->lid."' ORDER BY ".$orderby);
        }
        elseif($teams=="") $q = mysql_query("SELECT *, goals-ga as diff FROM ".$this->teams_table." WHERE league='".$this->lid."' ORDER BY ".$orderby); 
        else $q = mysql_query("SELECT *, goals-ga as diff FROM ".$this->teams_table." WHERE shortname IN ('".$teams."') && league='".$this->lid."' ORDER BY ".$orderby);
        while($f = mysql_fetch_array($q)) {
            $this->conference[$to_conference]["division"][$to_division]["teams"][$i]["tid"] = $f["id"];
            $this->conference[$to_conference]["division"][$to_division]["teams"][$i]["tshort"] = $f["shortname"];
            $this->conference[$to_conference]["division"][$to_division]["teams"][$i]["tmedium"] = ($this->el==1 ? $f["mediumname"]:$f["longname"]);
            $this->conference[$to_conference]["division"][$to_division]["teams"][$i]["tlong"] = $f["longname"];
            $this->conference[$to_conference]["division"][$to_division]["teams"][$i]["games"] = ($this->end_basic==1 ? $f["w_basic"]+$f["l_basic"]:$f["zapasov"]);
            $this->conference[$to_conference]["division"][$to_division]["teams"][$i]["wins"] = ($this->end_basic==1 ? $f["w_basic"]:$f["wins"]);
            $this->conference[$to_conference]["division"][$to_division]["teams"][$i]["losts"] = ($this->end_basic==1 ? $f["l_basic"]:$f["losts"]);
            $this->conference[$to_conference]["division"][$to_division]["teams"][$i]["gf"] = ($this->end_basic==1 ? $f["gf_basic"]:$f["goals"]);
            $this->conference[$to_conference]["division"][$to_division]["teams"][$i]["ga"] = ($this->end_basic==1 ? $f["ga_basic"]:$f["ga"]);
            $this->conference[$to_conference]["division"][$to_division]["teams"][$i]["gdiff"] = ($this->end_basic==1 ? $f["gf_basic"]-$f["ga_basic"]:$f["goals"]-$f["ga"]);
            $this->conference[$to_conference]["division"][$to_division]["teams"][$i]["points"] = ($this->end_basic==1 ? $f["p_basic"]:$f["body"]);
            $this->conference[$to_conference]["division"][$to_division]["teams"][$i]["cws"] = $f["cws"];
            $this->conference[$to_conference]["division"][$to_division]["teams"][$i]["cls"] = $f["cls"];
            $this->conference[$to_conference]["division"][$to_division]["teams"][$i]["ppgf"] = $f["ppgf"];
            $this->conference[$to_conference]["division"][$to_division]["teams"][$i]["ppga"] = $f["ppga"];
            $this->conference[$to_conference]["division"][$to_division]["teams"][$i]["shgf"] = $f["shgf"];
            $this->conference[$to_conference]["division"][$to_division]["teams"][$i]["shga"] = $f["shga"];
            $this->conference[$to_conference]["division"][$to_division]["teams"][$i]["so"] = $f["so"];
            $this->conference[$to_conference]["division"][$to_division]["teams"][$i]["can_earn"] = $this->conference[$to_conference]["division"][$to_division]["teams"][$i]["points"]+(($this->games_total-$this->conference[$to_conference]["division"][$to_division]["teams"][$i]["games"])*$this->wpoints);
            $this->conference[$to_conference]["division"][$to_division]["teams"][$i]["last5games"] = $this->last5games($f["shortname"]);
            $i++;
        }
    }
    
    public function last5games($tshort) {
      if($this->el==1) {
        $q = mysql_query("SELECT * FROM el_matches WHERE (team1short='".$tshort."' || team2short='".$tshort."') && league='".$this->lid."' && kolo!=0 && kedy='konečný stav' ORDER BY datetime DESC LIMIT 5");
      }
      else {
        $q = mysql_query("SELECT * FROM 2004matches WHERE (team1short='".$tshort."' || team2short='".$tshort."') && league='".$this->lid."' && po_type IS NULL && kedy='konečný stav' ORDER BY datetime DESC LIMIT 5");
      }
      $i=0;
      while($f = mysql_fetch_array($q)) {
        if($f["team1short"]==$tshort) {
          if($f["goals1"]>$f["goals2"]) $l5g[$i]["result"] = "W";
          else $l5g[$i]["result"] = "L";
        }
        else {
          if($f["goals1"]>$f["goals2"]) $l5g[$i]["result"] = "L";
          else $l5g[$i]["result"] = "W";
        }
        $l5g[$i]["gid"] = $f["id"].$this->el;
        $l5g[$i]["seo"] = SEOtitle($f["team1long"]." vs ".$f["team2long"]);
        $i++;
      }
      $l5g = array_reverse($l5g);
      return $l5g;
    }
    
    public function render_table($type, $condensed=FALSE, $json=FALSE) {
        Global $clinchwas, $cannotwas, $relegwas, $leaderwas;
        $clinchwas=$cannotwas=$relegwas=$leaderwas=0;
        switch ($type) {
          case "league":
            foreach($this->conference as $conf) {
              foreach($conf["division"] as $div) {
                foreach($div["teams"] as $team) {
                  $league_teams[] = $team;
                  }
              }
            }
            usort($league_teams, function($a,$b){ $c = $b["points"] - $a["points"]; $c .= $b["gdiff"] - $a["gdiff"]; $c .= $a["games"] - $b["games"]; $c .= $b["gf"] - $a["gf"]; $c .= $b["wins"] - $a["wins"]; $c .= $a["losts"] - $b["losts"]; return $c; });
            $table = $this->output_table($type, $this->name, $league_teams, $condensed, $json);
          break;
          case "conference":
            foreach($this->conference as $key => $conf) {
              if($conf["name"]=="") $conf["name"]=$this->name;
              $conf_teams[$key]["name"] = $conf["name"];
              foreach($conf["division"] as $div) {
                foreach($div["teams"] as $team) {
                  $conf_teams[$key]["teams"][] = $team;
                  }
              }
              if(strstr($this->name, 'NHL')) usort($conf_teams[$key]["teams"], function($a,$b){ $c = $b["points"] - $a["points"]; $c .= $a["games"] - $b["games"]; $c .= $b["wins"] - $a["wins"]; $c .= $b["gdiff"] - $a["gdiff"]; $c .= $b["gf"] - $a["gf"]; return $c; });
              else usort($conf_teams[$key]["teams"], function($a,$b){ $c = $b["points"] - $a["points"]; $c .= $b["gdiff"] - $a["gdiff"]; $c .= $a["games"] - $b["games"]; $c .= $b["gf"] - $a["gf"]; $c .= $b["wins"] - $a["wins"]; $c .= $a["losts"] - $b["losts"]; return $c; });
            if($condensed) break;
            }
            foreach($conf_teams as $conf) {
              $table .= $this->output_table($type, $conf["name"], $conf["teams"], $condensed, $json);
            }
          break;
          case "division":
            $i=0;
            foreach($this->conference as $conf) {
              foreach($conf["division"] as $div) {
                $div_teams[$i]["name"] = $div["name"];
                foreach($div["teams"] as $team) {
                  $div_teams[$i]["teams"][] = $team;
                  }
                if(strstr($this->name, 'NHL')) usort($div_teams[$i]["teams"], function($a,$b){ $c = $b["points"] - $a["points"]; $c .= $a["games"] - $b["games"]; $c .= $b["wins"] - $a["wins"]; $c .= $b["gdiff"] - $a["gdiff"]; $c .= $b["gf"] - $a["gf"]; return $c; });
                else usort($div_teams[$i]["teams"], function($a,$b){ $c = $b["points"] - $a["points"]; $c .= $a["games"] - $b["games"]; $c .= $b["gdiff"] - $a["gdiff"]; $c .= $b["gf"] - $a["gf"]; $c .= $b["wins"] - $a["wins"]; $c .= $a["losts"] - $b["losts"]; return $c; });
                if($this->el==0) {
                  $same_points = array();
                  foreach($div_teams[$i]["teams"] as $team) {
                    $points = $team["points"];
                    $same_points[$points][] = $team;
                  }
                $div_teams = $this->tie_break($same_points, $div_teams);
                }
                $i++;
              }
            }
            foreach($div_teams as $div) {
              $table .= $this->output_table($type, $div["name"], $div["teams"], $condensed, $json);
            }
          break;
        }
        if(!$json) {
            if($condensed) $table .= '
            <div class="small pt-1">
                '.($clinchwas==1 ? "<div class='pl-2'><span class='text-success'>*</span> - ".LANG_TEAMTABLE_CLINCHEDSHORT."</div>":"").'
                '.($cannotwas==1 ? "<div class='pl-2'><span class='text-danger'>x</span> - ".LANG_TEAMTABLE_CANNOTMAKEPOSHORT."</div>":"").'
                '.($this->el==1 && (strstr($this->name, 'KHL') || strstr($this->name, 'NHL')) ? "<div class='text-right pr-2'><a href='/table/".$this->lid."-".SEOtitle($this->name)."'>".LANG_TEAMTABLE_FULLTABLE." &raquo;</a></div>":"").'
            </div>';
            else {
                if($leaderwas==1 || $clinchwas==1 || $cannotwas==1 || $relegwas==1) {
                    $table .= '
                    <div class="bg-white border my-3 p-2 rounded small">
                        '.($leaderwas==1 ? "<span class='font-weight-bold'>*</span> - ".LANG_TEAMTABLE_DIVLEADERS."<br>":"").'
                        '.($clinchwas==1 ? "<span class='font-weight-bold text-success'>x</span> - ".($type=="division" && $this->el==0 ? LANG_TEAMTABLE_CLINCHEDQF : LANG_TEAMTABLE_CLINCHED)."<br>":"").'
                        '.($cannotwas==1 ? "<span class='font-weight-bold text-danger'>y</span> - ".($type=="division" && $this->el==0 ? LANG_TEAMTABLE_CANNOTMAKEQF : LANG_TEAMTABLE_CANNOTMAKEPO)."<br>":"").'
                        '.($relegwas==1 ? "<span class='font-weight-bold text-info'>z</span> - ".LANG_TEAMTABLE_RELEGATED:"").'
                    </div>';
                }
            }
        }
        return $table;
    }
    
    private function output_table($type, $name, $teams, $condensed=FALSE, $json=FALSE) {
      Global $leaguecolor, $clinchwas, $cannotwas, $relegwas, $leaderwas;
      if(!$json) {
        if($condensed) $table = "
                <table class='w-100 table-striped table-hover'>
                    <thead>
                        ".($type=="division" && $this->el==0 ? "
                        <tr>
                            <td colspan='3' class='pl-2'><b>".$name."</b></td>
                        </tr>
                        ":"")."
                        <tr> 
                            <td scope='col' class='text-center'>#</td>
                            <td scope='col'><b>".LANG_PLAYERSTATS_TEAM."</b></td>
                            <td scope='col' class='text-center'><b>".LANG_TEAMSTATS_POINTS."</b></td>
                        </tr>
                    </thead>
                    <tbody>";
        else $table = '<div class="card my-4 shadow animated--grow-in">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-'.$leaguecolor.'">
                '.$name.'
                <span class="swipe d-none float-right text-gray-800"><i class="fas fa-hand-point-up"></i> <i class="fas fa-exchange-alt align-text-top text-xs"></i></span>
                </h6>
            </div>
            <div class="card-body">
            <table class="table-hover table-light table-striped table-responsive-lg w-100 p-fluid teamTable">
                <thead>
                <tr>
                    <th>#</th>
                    <th style="width: 40%;">'.LANG_PLAYERSTATS_TEAM.'</th>
                    <th class="text-center">'.LANG_TEAMSTATS_MATCHES.'</th>
                    <th class="text-center">'.LANG_TEAMSTATS_WINS.'</th>
                    <th class="text-center">'.LANG_TEAMSTATS_LOSTS.'</th>
                    <th class="text-center">'.LANG_TEAMSTATS_SCORE.'</th>
                    <th class="text-center">'.LANG_TEAMSTATS_POINTS.'</th>
                    <th class="text-center">'.LANG_TEAMTABLE_FORM.'</th>
                </tr>
                </thead>
                <tbody>';
      } 
      $p=1;
      foreach($teams as $key => $team) {
        $bs=$be=$clinchout=$fav="";
        if($type=="conference" || $type=="division" && $this->el==0 && $this->playoff_line) {
          $clinch = $this->check_clinch($key, $teams);
          if($clinch=="x") { $clinchwas=1; $bs = "<b>"; $be="</b>"; $clinchout = "<sup><span class='text-success font-weight-bold'>x</span></sup>"; }
          if($clinch=="y") { $cannotwas=1; $bs = "<span class='font-italic'>"; $be="</span>"; $clinchout = "<sup><span class='text-danger font-weight-bold'>y</span></sup>"; }
          if($clinch=="z") { $relegwas=1; $bs = "<span class='font-italic'>"; $be="</span>"; $clinchout = "<sup><span class='text-primary font-weight-bold'>z</span></sup>"; }
          if($clinch=="*") { $leaderwas=1; $clinchout = "*"; }
          if(in_array($p, $this->playoff_line) || $p==$this->playoff_line) $line=" style='border-bottom:1px dashed black !important;'";
          else $line="";
        }
        if($this->favteam!="0" && $this->favteam==$team[tshort]) $fav=" class='bg-gray-400'";
        if(!$json) {
            if($condensed) $table .= "<tr><td class='text-center'$line>$p.</td><td$line><a href='/team/".$team[tid].$this->el."-".SEOtitle($team[tlong])."'><img class='flag-".($this->el==0 ? 'iihf':'el')." ".$team[tshort]."-small mr-1' src='/images/blank.png' alt='".$team[tlong]."'>".($this->el==0 ? $team[tlong]:$team[tmedium])."</a> $clinchout</td><td class='text-center'$line><b>".$team[points]."</b></td></tr>";
            else $table .= "<tr$fav><td class='text-center'$line>$p.</td><td class='text-nowrap'$line><img class='flag-".($this->el==0 ? 'iihf':'el')." ".$team[tshort]."-small' src='/img/blank.png' alt='".$team[tlong]."'> $leader<a href='/team/".$team[tid].$this->el."-".SEOtitle($team[tlong])."'>$bs<span class='d-none d-md-inline'>$team[tlong]</span><span class='d-inline d-md-none'>$team[tmedium]</span>$be</a> $clinchout</td><td class='text-center'$line>$team[games]</td><td class='text-center'$line>$team[wins]</td><td class='text-center'$line>$team[losts]</td><td class='text-center'$line>$team[gf]:$team[ga]</td><td class='text-center'$line><span class='font-weight-bold'>$team[points]</span></td>
            <td class='text-center text-nowrap'$line>";
            foreach($team["last5games"] as $game) {
              $table .= "<a href='/report/".$game["gid"]."-".$game["seo"]."' class='badge badge-pill badge-".($game["result"]=="W" ? 'success':'danger')."'>".($game["result"]=="W" ? LANG_W:LANG_L)."</a>";
            }
            $table .= "</td></tr>";
        }
        else {
            $table["conference"][$name][$p]["shortname"] = $team[tshort];
            $table["conference"][$name][$p]["longname"] = $team[tlong];
            $table["conference"][$name][$p]["gp"] = $team[games];
            $table["conference"][$name][$p]["wins"] = $team[wins];
            $table["conference"][$name][$p]["losts"] = $team[losts];
            $table["conference"][$name][$p]["score"] = $team[gf].":".$team[ga];
            $table["conference"][$name][$p]["points"] = $team[points];
            $table["conference"][$name][$p]["clinch"] = $clinch;
        }
        $p++;
      }
      if(!$json) {
        $table = str_replace(">Slovensko<", "><span class='font-weight-bold'>Slovensko</span><", $table);
        $table = str_replace(">Slovensko U20<", "><span class='font-weight-bold'>Slovensko U20</span><", $table);
        $table = str_replace(">Európa<", "><span class='font-weight-bold'>Európa</span><", $table);
        if($condensed) {
            $table .= "</tbody></table>";
        }
        else $table .= "</tbody></table></div></div>";
      }
      else $table = json_encode($table, JSON_UNESCAPED_UNICODE);
      return $table;
    }
    
    private function check_clinch($team_pos, $teams) {
      // clinched playoff
      $clinch = "";
      if(is_array($this->playoff_line)) $tpos_under_line = $this->playoff_line[0];
      else $tpos_under_line = $this->playoff_line;
      $playoff_line = $tpos_under_line;
      $can_earn = 0;
      while($tpos_under_line < count($teams)) {
        if($teams[$tpos_under_line][can_earn]>$can_earn) $can_earn = $teams[$tpos_under_line][can_earn];
        $tpos_under_line++;
      }
      //if($teams[$team_pos][points]>$can_earn || $teams[$team_pos][games]==$this->games_total && $team_pos < $playoff_line) $clinch = "x";
      if($teams[$team_pos][points]>$can_earn) $clinch = "x";
      // cannot make playoffs
      if(is_array($this->playoff_line)) {
          if(strstr($this->name, "Tipos")) $tpos_over_line = $this->playoff_line[1]-1;
          else $tpos_over_line = $this->playoff_line[0]-1;
      }
      else $tpos_over_line = $this->playoff_line-1;
      if($teams[$team_pos][can_earn]<$teams[$tpos_over_line][points] || $teams[$team_pos][games]==$this->games_total && $team_pos >= $playoff_line) $clinch = "y";
      // relegated to I.DIV
      if(is_array($this->playoff_line) && strstr($this->name, "MS")) {
        $tpos_over_line = end($this->playoff_line)-1;
        if($teams[$team_pos][can_earn]<$teams[$tpos_over_line][points] || $teams[$team_pos][games]==$this->games_total && $team_pos > $tpos_over_line) $clinch = "z";
      }
      return $clinch;
    }
    
    private function tie_break($point_values, $teams) {
      foreach($point_values as $point_value) {
        // tie-breaking procedure for 2 teams tied on points
        if(count($point_value)==2) {
          $t1 = $point_value[0]["tshort"];
          $t2 = $point_value[1]["tshort"];
          $vzaj = mysql_query("SELECT IF(team1short='$t2',IF(goals1>goals2,1,0),IF(goals1>goals2,0,1)) as posun FROM 2004matches WHERE (team1short='$t2' && team2short='$t1' || team1short='$t1' && team2short='$t2') && league='".$this->lid."' && kedy='konečný stav'");
          $vzajom = mysql_fetch_array($vzaj);
          if($vzajom[posun]==1) {
            // change teams between them
            foreach($teams as $key => $team) {
              $t1_key = array_search($t1, array_column($team["teams"], 'tshort'));
              $t2_key = array_search($t2, array_column($team["teams"], 'tshort'));
              $val1 = $team["teams"][$t1_key];
              $val2 = $team["teams"][$t2_key];              
              $teams[$key]["teams"][$t2_key] = $val1;
              $teams[$key]["teams"][$t1_key] = $val2;
            }
          }
        else return $teams;
        }
        // tie-breaking procedure for 3 or more teams tied on points
        elseif(count($point_value)>2) {
          $tied_teams=[];
          foreach($point_value as $tied_team) {
            $tied_teams[] = $tied_team["tshort"];
          }
          $tied_teams = implode("','", $tied_teams);
          $vzaj = mysql_query("SELECT m.*, MAX(g.time) as time FROM 2004matches m LEFT JOIN 2004goals g ON g.matchno=m.id WHERE m.team1short IN ('".$tied_teams."') && m.team2short IN ('".$tied_teams."') && m.league='".$this->lid."' && m.kedy='konečný stav' GROUP BY m.id");
          while($vzajom = mysql_fetch_array($vzaj)) {
            if($vzajom["time"]>60) { $pts = 2; $lpts = 1; }
            else { $pts = 3; $lpts = 0; }
            $t1 = $vzajom["team1short"];
            $t2 = $vzajom["team2short"];
            if($vzajom["goals1"]>$vzajom["goals2"]) { $winner = $t1; $loser = $t2; }
            else { $winner = $t2; $loser = $t1; }
            $sub_points[$winner]=$sub_points[$winner]+$pts;
            $sub_points[$loser]=$sub_points[$loser]+$lpts;
            $sub_goalsdiff[$t1]=$sub_goalsdiff[$t1]+$vzajom["goals1"]-$vzajom["goals2"];
            $sub_goalsdiff[$t2]=$sub_goalsdiff[$t2]+$vzajom["goals2"]-$vzajom["goals1"];
            $sub_goalsfor[$t1]=$sub_goalsfor[$t1]+$vzajom["goals1"];
            $sub_goalsfor[$t2]=$sub_goalsfor[$t2]+$vzajom["goals2"];
          }
          arsort($sub_points);
          arsort($sub_goalsdiff);
          arsort($sub_goalsfor);
          $num_val = array_count_values($sub_points);
          $num_val_gms = array_count_values($num_val);
          if($num_val_gms[1]==count($point_value)) {
            // teams succesfully sorted by h2h games, no need to apply other tie-breaking steps, change teams according to new keys
            foreach($teams as $key => $team) {
              foreach($sub_points as $tshort => $value) {
                $t_key = array_search($tshort, array_column($team["teams"], 'tshort'));
                $old_keys[$tshort] = $t_key;
                ${"val".$t_key} = $team["teams"][$t_key];
              }
              $min = min($old_keys);
              $max = max($old_keys);
              $avg = ($min+$max)/2;
              $i = $min;
              $j = 0;
              foreach($old_keys as $tshort => $old_key) {
                if($old_key!=$i) {
                  $new_key = ceil($old_key-(($old_key-$avg)*2));
                  $teams[$key]["teams"][$old_key] = ${"val".$new_key};
                }
                $i++;
                $j++;
              }
            }
          }

        }
      }
      return $teams;
    }
    
    public function check_position($tshort) {
      $i=0;
      foreach($this->conference as $conf) {
        foreach($conf["division"] as $div) {
          $div_teams[$i]["name"] = $div["name"];
          foreach($div["teams"] as $team) {
            $div_teams[$i]["teams"][] = $team;
            }
          usort($div_teams[$i]["teams"], function($a,$b){ $c = $b["points"] - $a["points"]; $c .= $a["games"] - $b["games"]; $c .= $b["gdiff"] - $a["gdiff"]; $c .= $b["gf"] - $a["gf"]; $c .= $b["wins"] - $a["wins"]; $c .= $a["losts"] - $b["losts"]; return $c; });
          if($this->el==0) {
            $same_points = array();
            foreach($div_teams[$i]["teams"] as $team) {
              $points = $team["points"];
              $same_points[$points][] = $team;
            }
          $div_teams = $this->tie_break($same_points, $div_teams);
          }
          $i++;
          foreach($div_teams as $div) {
            foreach($div["teams"] as $key => $team) {
              if($team["tshort"]==$tshort) { $key++; return $key; }
            }
          }
        }
      }
    }
    
    public function check_canearn($tshort) {
      foreach($this->conference as $conf) {
        foreach($conf["division"] as $div) {
          foreach($div["teams"] as $team) {
            if($team["tshort"]==$tshort) return $team["can_earn"];
          }
        }
      }
    }
}
?>