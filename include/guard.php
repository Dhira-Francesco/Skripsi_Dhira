<?php
// guard.php
if (!isset($_SESSION['stLogin']) || $_SESSION['stLogin'] !== true) {
  header('Location: login.php');
  exit;
}
