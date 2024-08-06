<?php

use Controllers\Compta;

$f3 = \Base::instance();

$f3->route('GET /', Compta::class.'->home');
$f3->route('GET /banque', Compta::class.'->banque');
$f3->route('GET /pieces', Compta::class.'->pieces');
$f3->route('GET /piece/@piece_id', Compta::class.'->piece');
$f3->route('GET /files', Compta::class.'->files');
$f3->route('GET /associate_banque', Compta::class.'->associate_banque');
$f3->route('GET /associate_piece', Compta::class.'->associate_piece');
