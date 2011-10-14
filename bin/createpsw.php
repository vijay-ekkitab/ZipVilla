<?php

// Create a hashed password for a clear text password and salt.
// Usage: php createpsw.php <password> <salt>

    
if ($argc < 3) {
   echo "Missing arguments.\n";
   echo "Usage: createpsw <password> <salt>\n";
   exit(1);
}

$password = $argv[1];
$salt     = $argv[2];
echo sha1($password . $salt) . "\n";

?>
