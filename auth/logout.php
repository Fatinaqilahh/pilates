<?php
session_start();
session_unset();
session_destroy();

header("Location: /pilates/public/index.php");
exit;
