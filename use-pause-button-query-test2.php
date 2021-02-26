<h1>Use pause button statement query test</h1>

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
$verb->setId('http://activitystrea.ms/schema/1.0/use');



$response = $lrs->queryStatements([
    'verb' => $verb,
    'limit' => 25,
]);


if ($response->success) {
    print "<p>Statement sent successfully!</p>\n";
    print '<pre>' . print_r($response, TRUE) . '</pre>';
}
else {
    print "Error statement not sent: " . $response->content . "\n";
}