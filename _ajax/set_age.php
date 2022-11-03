<?php 

// setcookie("age", $_POST['age'], 2147483647);
setcookie( "age", $_POST['age'], strtotime( '+30 days' ) ); // MAJ : un cookie de 30 jours

?>