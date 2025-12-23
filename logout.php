<?php
include "include/config.php";
session_unset();
session_destroy();
header('Location: login.php');
