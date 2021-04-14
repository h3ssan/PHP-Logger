<?php

require_once 'log.class.php';

$mainLogger = new Log(3, './mainLogger.log');
$mainLogger->log(2, 'Logger started');

echo 'Ops, error';
$mainLogger->log(5, 'test error logged');