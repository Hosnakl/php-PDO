<?php
    include("./Common/Header.php");
    session_destroy();
    header("Location:index.php");
    include("./Common/Footer.php");
?>
