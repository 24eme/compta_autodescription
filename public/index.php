<?php

$f3 = require __DIR__.'/../vendor/fatfree-core/base.php';
require __DIR__.'/../app/routes.php';

$f3->config(__DIR__.'/../app/default.ini');
if (file_exists(__DIR__.'/../config/config.ini')) {
    $f3->config(__DIR__.'/../config/config.ini');
}

$f3->set('DB', DBManager::init($f3->get('db.dsn')));
$f3->set('PDF_METADATA', array('url' => $f3->get('pdf.metadata_url'), 'prefix' => $f3->get('pdf.metadata_prefix')));


$f3->run();
