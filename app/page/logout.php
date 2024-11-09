<?php
session_destroy();
cookie_destroy();
session_start();
temp('success', 'Logout Successful');
return redirect('/login');
?>
