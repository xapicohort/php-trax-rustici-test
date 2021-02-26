<h1>Use pause button statement store test</h1>

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
$verb->setId('http://activitystrea.ms/schema/1.0/use');
$verb->setDisplay([
    'en-US' => 'use',
]);

// Object
$object = new TinCan\Activity();
$object->setId('https://webapps.skilling.us/lesson/validation-functions/pause/3');
$activityDefinition = new TinCan\ActivityDefinition();
$activityDefinition->setType('http://adlnet.gov/expapi/activities/interaction');
$activityDefinition->setName([
    'en-US' => 'Interaction',
]);
$activityDefinition->setDescription([
    'en-US' => 'Pause button - next',
]);
$object->setDefinition($activityDefinition);

$result = new TinCan\Result();
$result->setExtensions([
    'https://xapi.skillling.us/pause-button-number' => '3',
    'https://xapi.skillling.us/pause-button-use' => 'next',
]);

// Context
$context = new TinCan\Context();
$context->setPlatform('https://skilling.us');
// Context activity definition. Reference lesson access as parent.
$parentActivity = new TinCan\Activity();
$parentActivity->setId('https://webapps.skilling.us/lesson/validation-functions');
$parentActivityDefinition = new TinCan\ActivityDefinition();
$parentActivityDefinition->setType('http://id.tincanapi.com/activitytype/resource');
$parentActivityDefinition->setName([
    'en-US' => 'Validation functions',
]);
$parentActivityDefinition->setDescription([
    'en-US' => 'Lesson summary here',
]);
$parentActivity->setDefinition($parentActivityDefinition);
$contextActivities = new TinCan\ContextActivities();
$contextActivities->setParent($parentActivity);
$context->setContextActivities($contextActivities);
// Context extensions.
$contextExtensions = new TinCan\Extensions();
$contextExtensions->set('http://skilling.us/extension/has-student-role', true);
$contextExtensions->set('http://skilling.us/extension/course-site', 'http://webapps.skilling.us');
$contextExtensions->set('http://skilling.us/extension/course-name', 'Business web apps');
$contextExtensions->set('http://skilling.us/extension/semester', 'fall, 2021');
$contextExtensions->set('http://skilling.us/extension/section', '938903');
$context->setExtensions($contextExtensions);


//Timestamp
$timeStamp = TinCan\Util::getTimestamp();

$statement = new TinCan\Statement();
$statement->setActor($actor);
$statement->setVerb($verb);
$statement->setObject($object);
$statement->setResult($result);
$statement->setContext($context);
$statement->setTimestamp($timeStamp);
$response = $lrs->saveStatement($statement);
if ($response->success) {
    print "<p>Use pause button saved successfully</p>\n";
}
else {
    print "<p>Error saving use pause button: $response->content </p>\n";
}