<?php
include_once('../generales.php');
$lien=lien();

//$tBddConf=getBddConf();
//mysql_connect($tBddConf['host'], $tBddConf['user'], $tBddConf['pass']) or die('Connection impossible<br/>'.mysql_error());
//mysql_select_db($tBddConf['bdd']) or die('Base inaccessible<br/>'.mysql_error());
foreach($_REQUEST as $key=>$val) $$key=mysqli_real_escape_string ($lien, trim(addslashes($val))); 
if($annee=='') $annee=date('Y');

$DateConvocPied="IMPRIME LE ".date('d').'/'.date('m').'/'.date('Y');
$sql1 = "SELECT res.num_table,res.id_anonyme,nom,prenoms,res.annee,ser.serie,sexe,can.ddn,
                    sum(IF(nte.id_matiere=48,note,NULL)) AS LV1,
                    sum(IF(nte.id_matiere=51,note,NULL)) AS LV2,
                    sum(IF(nte.id_matiere=60,note,NULL)) AS ECM, 
                    sum(IF(nte.id_matiere=46,note,NULL)) AS Fr, 
                    sum(IF(nte.id_matiere=47,note,NULL)) AS HG,
                    sum(IF(nte.id_matiere=29,note,NULL)) AS Maths, 
                    sum(IF(nte.id_matiere=49,note,NULL)) AS SVT, 
                    sum(IF(nte.id_matiere=50,note,NULL)) AS Physique,
                    sum(IF(nte.id_matiere=8,note,NULL)) AS Cout,
                    sum(IF(nte.id_matiere=61,note,NULL)) AS Agri,
                    sum(IF(nte.id_matiere=42,note,NULL)) AS EPS,
                    sum(IF(nte.id_matiere=3,note,NULL)) AS Ewe_Kabye_Latin,
                    sum(IF(nte.id_matiere=4,note,NULL)) AS Russe,
                    sum(IF(nte.id_matiere=5,note,NULL)) AS Arabe,
                    sum(IF(nte.id_matiere=6,note,NULL)) AS Dessin,
                    sum(IF(nte.id_matiere=7,note,NULL)) AS Musique,
                    nte.jury,total,res.coeff,moyenne,delib1
                    FROM bg_notes as nte 
                    join bg_resultats as res 
                    on nte.num_table=res.num_table AND nte.id_type_note<>3
                    JOIN bg_candidats can
                    on can.num_table=nte.num_table
                    JOIN  bg_ref_serie ser
                    on ser.id=can.serie
                    GROUP BY num_table";
                    $result1 = bg_query($lien, $sql1, __FILE__, __LINE__);
                    $tab = array(
                        'num_table',
                        'id_anonyme',
                        'nom',
                        'prenoms',
                        'annee',
                        'serie',
                        'sexe',
                        'ddn',
                        'LV1',
                        'LV2','ECM','Fr','HG','Maths','SVT','Physique',
                        'Cout','Agri','EPS','Ewe_Kabye_Latin','Russe','Arabe','Dessin','Musique',
                        'jury','total','coeff','moyenne','delib1');
 if(mysqli_num_rows($result1) > 0)
 {
  $output .= '
  <table class="spip liste">
    <thead>
        <th>N° table</th><th>Anonyme</th><th>Nom & prenoms</th><th>Serie</th><th>Sexe</th><th>Date naiss</th>
        <th>LV1</th><th>LV2</th><th>ECM</th><th>Fr</th><th>HG</th><th>Maths</th><th>SVT</th><th>PC</th>
        <th>Cout</th><th>Agri</th><th>EPS</th><th>Ewe/Kabye/Latin</th><th>Rus.</th><th>Ara.</th><th>Des.</th><th>Mus.</th>
        <th>Jury</th><th>Total</th><th>Coeff</th><th>Moy.</th><th>delib1</th>
    </thead>
    <tbody>';
  while($row = mysqli_fetch_array($result1))
  {
    if ($row['sexe']=='1') {
        $sexe='F';
    }else {
        $sexe='M';
    }
   $output .= '
   <tr>
        <td><b>' . $row["num_table"] . '</b></td>
        <td>' . $row["id_anonyme"] . '</td>
        <td>' . $row["nom"] .' '. $row["prenoms"]. '</td>
        <td>' . $row["serie"] . '</td>
        <td>' . $sexe . '</td>
        <td>' . $row["ddn"] . '</td>
        <td>' . $row["LV1"] . '</td>
        <td>' . $row["LV2"] . '</td>
        <td>' . $row["ECM"] . '</td>
        <td>' . $row["Fr"] . '</td>
        <td>' . $row["HG"] . '</td>
        <td>' . $row["Maths"] . '</td>
        <td>' . $row["SVT"] . '</td>
        <td>' . $row["Physique"] . '</td>
        <td>' . $row["Cout"] . '</td>
        <td>' . $row["Agri"] . '</td>
        <td>' . $row["EPS"] . '</td>
        <td>' . $row["Ewe_Kabye_Latin"] . '</td>
        <td>' . $row["Russe"] . '</td>
        <td>' . $row["Arabe"] . '</td>
        <td>' . $row["Dessin"] . '</td>
        <td>' . $row["Musique"] . '</td>
        <td>' . $row["jury"] . '</td>
        <td>' . $row["total"] . '</td>
        <td>' . $row["coeff"] . '</td>
        <td>' . $row["moyenne"] . '</td>
        <td>' . $row["delib1"] . '</td>';
  }
  $output .= '</table>';
  $filename="stats-notes-globales_".$annee.".xls";
  header('Content-Type: application/xls');
  header('Content-Disposition: attachment; filename='.$filename);
  echo $output;
 }
?>