<?
function generateGamesTable($lid) {
    Global $link;
    $q = mysqli_query($link, "SELECT * FROM 2004leagues WHERE id='".$lid."'");
    $f = mysqli_fetch_assoc($q);
    $f["el"]=$f["el"] ?? 0;
    if($f["el"]==1) $w = mysqli_query($link, "SELECT * FROM el_matches WHERE league='".$lid."' ORDER BY datetime");
    else $w = mysqli_query($link, "SELECT m.*, t.skupina FROM 2004matches m LEFT JOIN 2004teams t ON t.shortname=m.team1short && t.league='".$lid."' WHERE m.league='".$lid."' ORDER BY m.datetime;");
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
    while($game = mysqli_fetch_assoc($w)) {
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
    Global $link;
    $q = mysqli_query($link, "SELECT * FROM 2004leagues WHERE id='".$lid."'");
    $f = mysqli_fetch_assoc($q);
    $f["el"]=$f["el"] ?? 0;
    if($f["el"]==1) $w = mysqli_query($link, "SELECT id, name, pos FROM `el_players` WHERE teamshort='".$tshort."' && league='".$lid."' UNION SELECT id, name, 'GK' as pos FROM `el_goalies` WHERE teamshort='".$tshort."' && league='".$lid."' ORDER BY pos DESC, name");
    else $w = mysqli_query($link, "SELECT id, name, pos FROM `2004players` WHERE teamshort='".$tshort."' && league='".$lid."' UNION SELECT id, name, 'GK' as pos FROM `2004goalies` WHERE teamshort='".$tshort."' && league='".$lid."' ORDER BY pos DESC, name");

    $table = '';
    if(mysqli_num_rows($w)>0) {
        $table = '
        <table class="table table-sm table-striped w-50">
            <tr>
                <th>Pozícia</th>
                <th>Meno</th>
            </tr>
        ';
        while($player = mysqli_fetch_assoc($w)) {
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

function generateTripForm($lid) {
    Global $link;
    $q = mysqli_query($link, "SELECT * FROM 2004leagues WHERE id='".$lid."'");
    $f = mysqli_fetch_assoc($q);
    
    $form = '
    <form class="needs-validation" novalidate>
        <input type="hidden" name="tournament" value="'.$f["longname"].'">
        <div class="form-group row mb-3">
            <label for="numPeople" class="col-sm-3 col-form-label">Počet osôb</label>
            <div class="col-sm-9">
                <input type="number" class="form-control" id="numPeople" name="numPeople" min="1" required>
                <div class="invalid-feedback">
                    Prosím zadajte počet osôb
                </div>
            </div>
        </div>

        <div class="form-group row mb-3">
            <label for="numDays" class="col-sm-3 col-form-label">Počet dní</label>
            <div class="col-sm-9">
                <input type="number" class="form-control" id="numDays" name="numDays" min="1" required>
                <div class="invalid-feedback">
                    Prosím zadajte počet dní
                </div>
            </div>
        </div>

        <div class="form-group row mb-3">
            <label for="travelMode" class="col-sm-3 col-form-label">Spôsob dopravy</label>
            <div class="col-sm-9">
                <select class="form-control" id="travelMode" name="travelMode" required>
                    <option value="">Vyberte spôsob dopravy...</option>
                    <option value="car">Autom</option>
                    <option value="bus">Autobusom</option>
                    <option value="train">Vlakom</option>
                    <option value="plane">Lietadlom</option>
                </select>
                <div class="invalid-feedback">
                    Prosím vyberte spôsob dopravy
                </div>
            </div>
        </div>

        <div class="form-group row mb-3">
            <label for="budget" class="col-sm-3 col-form-label">Rozpočet</label>
            <div class="col-sm-9">
                <select class="form-control" id="budget" name="budget" required>
                    <option value="">Vyberte rozpočet...</option>
                    <option value="low">low-cost</option>
                    <option value="medium">stredný</option>
                    <option value="high">vysoký</option>
                </select>
                <div class="invalid-feedback">
                    Prosím vyberte rozpočet
                </div>
            </div>
        </div>

        <div class="form-group row mb-3">
            <label for="cityOrigin" class="col-sm-3 col-form-label">Mesto odchodu</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="cityOrigin" name="cityOrigin" required>
                <div class="invalid-feedback">
                    Prosím zadajte mesto odchodu
                </div>
            </div>
        </div>

        <div class="form-group row mb-3">
            <label for="email" class="col-sm-3 col-form-label">Kontaktný e-mail</label>
            <div class="col-sm-9">
                <input type="email" class="form-control" id="email" name="email" required>
                <div class="invalid-feedback">
                    Prosím zadajte platný email
                </div>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-sm-9 offset-sm-3">
                <button type="submit" class="btn btn-primary">Odoslať</button>
            </div>
        </div>
    </form>

    <script>
    (function() {
        "use strict";
        window.addEventListener("load", function() {
            var forms = document.getElementsByClassName("needs-validation");
            var validation = Array.prototype.filter.call(forms, function(form) {
                form.addEventListener("submit", function(event) {
                    event.preventDefault();
                    event.stopPropagation();
                    
                    if (form.checkValidity() === true) {
                        var formData = new FormData(form);
                        fetch("/includes/trip_handler.php", {
                            method: "POST",
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert("Formulár bol úspešne odoslaný!");
                                form.reset();
                                form.classList.remove("was-validated");
                            } else {
                                alert("Chyba pri odosielaní: " + data.message);
                            }
                        })
                        .catch(error => {
                            alert("Chyba pri odosielaní formulára.");
                        });
                    }
                    
                    form.classList.add("was-validated");
                }, false);
            });
        }, false);
    })();
    </script>
    ';
    
    return $form;
}