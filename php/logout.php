<?php
session_start();
session_unset();
session_destroy();
header("Location: ../Main.html");
exit();
?>