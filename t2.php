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

$actr = new TinCan\Agent();
$actr->setName('Sally');
$actr->setMbox('mailto:sally@example.com');

$verb = new TinCan\Verb();
$verb->setId("http://adlnet.gov/expapi/verbs/alert");
$verb->setDisplay(['en-US' => 'alert',]);

$activity = new TinCan\Activity();
$activity->setId('http://example.com/activities/solo-hang-gliding');
$activity->setDefinition([
    'name' => ['en-US' => 'doggo',]
]);

$statement = new \TinCan\Statement();
$statement->setActor($actr);
$statement->setVerb($verb);
$statement->setObject($activity);

$response = $lrs->saveStatement($statement);
if ($response->success) {
    print "Statement sent successfully!\n";
}
else {
    print "Error statement not sent: " . $response->content . "\n";
}

print "<h1>Parrots!</h1>";