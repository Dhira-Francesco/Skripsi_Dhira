<?php
    include "include/config.php";
    if(isset($_GET['id']))
    {
        $iduser = $_GET["id"];

        mysqli_query($connection, "delete from user 
        where user_id ='$iduser'");
        header("location:latihan2.php?msg=hapus");
    exit;
        
    }
    ?>