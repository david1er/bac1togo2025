<?php
include_once '../generales.php';
$lien = lien();

//$tBddConf=getBddConf();
//mysql_connect($tBddConf['host'], $tBddConf['user'], $tBddConf['pass']) or die('Connection impossible<br/>'.mysql_error());
//mysql_select_db($tBddConf['bdd']) or die('Base inaccessible<br/>'.mysql_error());
foreach ($_REQUEST as $key => $val) {
    $$key = mysqli_real_escape_string($lien, trim(addslashes($val)));
}

if ($annee == '') {
    $annee = date('Y');
}

if (($_GET['centre']) == 'oui') {
    $tous = 'oui';
    $andWhere = " AND eta.si_centre='oui' AND ins.id=$id_inspection";
}
if (($_GET['centre']) == 'non') {
    $tous = 'non';
    $andWhere = " AND eta.si_centre='non' AND ins.id=$id_inspection";
}
if (($_GET['centre']) == 'tous') {
    $tous = 'tous';
    $andWhere = " AND ins.id=$id_inspection";
}

$DateConvocPied = "IMPRIME LE " . date('d') . '/' . date('m') . '/' . date('Y');
$sql1 = "SELECT eta.*,id_prefecture FROM bg_ref_etablissement eta, bg_ref_ville vil, bg_ref_prefecture pre,bg_ref_inspection ins
        WHERE pre.id=vil.id_prefecture AND vil.id=eta.id_ville AND eta.id_inspection=ins.id $andWhere ORDER BY eta.etablissement ";
// $sql1 = "SELECT eta.*,id_prefecture
//         FROM bg_ref_etablissement eta, bg_ref_ville vil, bg_ref_prefecture pre,bg_ref_inspection ins
//         WHERE pre.id=vil.id_prefecture AND vil.id=eta.id_ville AND eta.id_inspection=ins.id $andWhere
//         GROUP BY eta.etablissement
//         ORDER BY eta.etablissement ";

$result1 = bg_query($lien, $sql1, __FILE__, __LINE__);
$tab = array('id', 'etablissement', 'code', 'id_inspection', 'id_prefecture', 'id_ville', 'id_type_etablissement', 'id_etat', 'code', 'nom_responsable', 'telephones_eta', 'login_eta', 'mdp_eta', 'id_centre');
if (mysqli_num_rows($result1) > 0) {
    $output .= '
    <table class="spip liste"><thead>
    <th>Etbalissement</th><th>Code</th><th>Login</th><th>Ville</th><th>Prefecture</th><th>Centre</th>
    </thead><tbody>
';
    while ($row = mysqli_fetch_array($result1)) {
        foreach ($tab as $var) {
            $$var = $row[$var];
        }
        $output .= '

   <tr>
    <td>' . $etablissement . '</td>
    <td>' . $code . '</td>
    <td>' . $login_eta . '</td>
    <td>' . utf8_decode(selectReferentiel($lien, "ville", $id_ville)) . '</td>
    <td>' . utf8_decode(selectReferentiel($lien, "prefecture", $id_prefecture)) . '</td>
    <td>' . utf8_decode(selectReferentiel($lien, "etablissement", $id_centre)) . '</td></tr>';

    }
    $output .= '</tbody><tfoot><tr></tr></tfoot></table>';
    $filename = "etablissement_liste_" . $annee . ".xls";
    header('Content-Type: application/xls');
    header('Content-Disposition: attachment; filename=' . $filename);
    echo $output;
}
