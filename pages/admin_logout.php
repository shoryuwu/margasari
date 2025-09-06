<?php
// Logout admin
session_unset();
session_destroy();
session_start();

set_flash_message('info', 'Anda telah logout dari admin panel');
redirect('?page=admin-login');
?>
