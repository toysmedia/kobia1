<?php
echo "<h1>PHP is working!</h1>";
echo "<p>User: " . exec('whoami') . "</p>";
echo "<p>Current directory: " . __DIR__ . "</p>";
phpinfo();
