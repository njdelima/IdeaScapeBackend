<?php>

<?php

file_put_contents('logger.txt', date('l, F jS Y - g:i:s A')."\n", FILE_APPEND);
file_put_contents('logger.txt', 'POST: '.print_r($_POST, true).'GET: '.print_r($_GET, true), FILE_APPEND);
file_put_contents('logger.txt', 'BODY: '.print_r(json_decode(file_get_contents('php://input')), true), FILE_APPEND);
file_put_contents('logger.txt', "\n", FILE_APPEND);


?>
