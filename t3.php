<?php
require 'library/RusticiSoftware-TinCanPHP-9758b3e/autoload.php';
require '../trax-lrs-test1-params.php';

/** @var string $lrsEndPoint */
/** @var string $lrsUserName */
/** @var string $lrsPassword */
$lrs = new TinCan\RemoteLRS(
    $lrsEndPoint,
    '1.0.3',
    $lrsUserName,
    $lrsPassword
);
$verb = new TinCan\Verb();
$verb->setId("http://adlnet.gov/expapi/verbs/alert");
$verb->setDisplay(['en-US' => 'alert',]);

$response = $lrs->queryStatements([
    'verb' => $verb,
    'limit' => 25,
]);


if ($response->success) {
    print "Statement sent successfully!\n";
}
else {
    print "Error statement not sent: " . $response->content . "\n";
}

print "<h1>THINGS!</h1>";