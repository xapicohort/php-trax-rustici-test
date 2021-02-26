<h1>Logout statement store test</h1>

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

// Actor
$actor = new TinCan\Agent();
$account = new TinCan\AgentAccount();
$account->setName('123');
$account->setHomePage('https://webapps.skilling.us/user/123');
$actor->setAccount($account);

// Verb
$verb = new TinCan\Verb();
$verb->setId('https://brindlewaye.com/xAPITerms/verbs/loggedout');
$verb->setDisplay([
    'en-US' => 'logged out of',
]);

// Object
$activityDefinition = new TinCan\ActivityDefinition();
$activityDefinition->setType('http://adlnet.gov/expapi/activities/course');
$activityDefinition->setName([
    'en-US' => 'Course',
]);
$object = new TinCan\Activity();
$object->setId('http://webapps.skilling.us');
$object->setDefinition($activityDefinition);

// Context
$context = new TinCan\Context();
$context->setPlatform('https://skilling.us');
$contextExtensions = new TinCan\Extensions();
$contextExtensions->set('http://skilling.us/extension/has-student-role', true);
$contextExtensions->set('http://skilling.us/extension/course-site', 'http://webapps.skilling.us');
$contextExtensions->set('http://skilling.us/extension/course-name', 'Business web apps');
$contextExtensions->set('http://skilling.us/extension/semester', 'fall, 2021');
$contextExtensions->set('http://skilling.us/extension/section', '938903');

//Timestamp
$timeStamp = TinCan\Util::getTimestamp();

$statement = new TinCan\Statement();
$statement->setActor($actor);
$statement->setVerb($verb);
$statement->setObject($object);
$statement->setContext($context);
$statement->setTimestamp($timeStamp);
$response = $lrs->saveStatement($statement);
if ($response->success) {
    print "<p>Log out saved successfully</p>\n";
}
else {
    print "<p>Error saving logout: $response->content </p>\n";
}