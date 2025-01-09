<?php
try {
    $strConnection = 'mysql:host=localhost;dbname=bepc2022';
    $pdo = new PDO($strConnection, 'adminmeps', 'exploitation');
} catch (PDOException $e) {
    $msg = 'ERREUR PDO dans' . $e->getMessage();
    die($msg);
}
$candidats = array(
    102840, 105876, 105866, 105828, 116497, 116465, 116508, 119103, 116485, 116455, 123578, 123902, 124353, 149348, 149318, 149680, 154605,
    160040, 160049, 160207, 160206, 160133, 160078, 160026, 160007, 160020, 160006, 106958, 138867, 130467
);

foreach ($candidats as $cand) {

    $num_table = $cand;

    var_dump($num_table);
    $id_matiere = 42;
    $id_type_note = 4;
    $note = 12;

    $req1 = "SELECT * FROM bg_repartition WHERE num_table =$num_table";
    $result = $pdo->prepare($req1);
    $result->execute();

    while ($et = $result->fetch()) {
        $num_table = $et['num_table'];
        $id_anonyme = $et['id_anonyme'];
        $ano = $et['ano'];
        $id_type_session = $et['id_type_session'];
        $annee = $et['annee'];
        $id_serie = 2;
        $coeff = 0;
        $jury = $et['jury'];


        $req = "INSERT INTO bg_notes(num_table, id_anonyme, ano, id_type_session, annee, id_matiere, id_type_note, id_serie, note, coeff, jury) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
        $ps = $pdo->prepare($req);
        $params = array($num_table, $id_anonyme, $ano, $id_type_session, $annee, $id_matiere, $id_type_note, $id_serie, NULL, $coeff, $jury);
        $ps->execute($params);
    }
}
