Let's pick a few xAPI statements to emit. Thanks to Izzy Lara and Matt Kliewer for their help in Slack.

The statements capture learners' actions in Skilling, a tool for making and running skill courses, like programming. Most of the statement types are easy to understand, but some need some explanation. 

### Pause

Typing "pause" in the authoring tool adds a pause to a lesson, like this:

![image](https://user-images.githubusercontent.com/19878838/109021088-8db4d380-7688-11eb-8813-7ca0c34d25d9.png)

This is segmentation, from Richard Mayer's multimedia research, though applied to a text/image page. It invites learners to pause for a moment, to think about what they've read.

I'd like to record what learners do when they see a More button. This image summarizes the available actions.

![image](https://user-images.githubusercontent.com/19878838/109021456-e6846c00-7688-11eb-923c-2d55075c35a4.png)

### Scroll position

I'd like to see how learners read the lessons. Most lessons have a programming exercise at the bottom. Do learners start at the top of a lesson and read down, or jump to the exercise, read that first, and work backwards through the lesson to find the info they need for the exercise? Does a difference in behavior correlate with, say, difficulty rating of the exercise?

So, I'd like to sample learners' positions in lessons each 5 seconds or so. There's a xAPI statement type for that, below.

### The standard

--- CUT SCREEN HERE ---

Events to record:

* Log in - student logs in to class website.
* Log out - student logs out of class website.
* View lesson - student accesses a lesson.
* Use pause button - student uses a pause button.
* Page position - record student position in a lesson every 5 seconds.
* Leave lesson - student leaves a lesson.

All statements have actor, verb, object, timestamp, and context.

See JISC spec at https://github.com/jiscdev/xapi was helpful. 

Actor: (same for all):

    "actor": {
        "objectType": "Agent",
        "account": {
            "name": "323",
            "homePage": "https://webapps.skilling.us/user/323"
        }
    },

name stores user id (not a name). homePage will refer to different sites, e.g.:

* https://webapps.skilling.us
* https://vba.skilling.us

Note that the homepage URLs are not accessible, except for Skilling instructors and admins.

Context: (same for all statement types)

"context": {
    "platform": "https://skilling.us",
    "extensions": {
        "http://skilling.us/extension/has-student-role": true,
        "http://skilling.us/extension/course-site": "http://webapps.skilling.us",
        "http://skilling.us/extension/class-name": "Business web apps"
        "http://skilling.us/extension/class-id": "34"
    }
}

has-student-role takes boolean, not string.

Timestamp is ISO8601 for all.

Login
-----

See https://github.com/jiscdev/xapi/blob/master/generic/login.md

Verb:

"verb": {
  "id": "https://brindlewaye.com/xAPITerms/verbs/loggedin",
  "display": {
    "en-US" : "logged in to"
  }
}

"object": {
  "objectType": "Activity",
  "id": "http://webapps.skilling.us",
  "definition": {
    "type": "http://adlnet.gov/expapi/activities/course",
    "name": {
      "en-US": "Course"
    }
}



Logout
-----

See https://github.com/jiscdev/xapi/blob/master/generic/logout.md

Verb:

"verb": {
  "id": "https://brindlewaye.com/xAPITerms/verbs/loggedout",
  "display": {
    "en" : "logged out of"
  }
}

"object": {
  "objectType": "Activity",
  "id": "http://webapps.skilling.us",
  "definition": {
    "type": "http://adlnet.gov/expapi/activities/course",
    "name": {
      "en-US": "Course"
    }
}

View lesson
-----------

Stored when lesson shown, triggered in skilling.module (?).

"verb": {
        "id": "http://activitystrea.ms/schema/1.0/open",
        "display": {
            "en-US": "opened"
        }
    },

"object": {
	"objectType": "Activity",
	"id": "https://webapps.skilling.us/lesson/validation-functions",
	"definition": {
		"type": "http://id.tincanapi.com/activitytype/resource",
		"name": {
		    "en-US": "Validation functions"
		 },
		"description": {
		    "en-US": "Something here."
		 },
	  }
    }

id is the URL of the lesson. Is that right? Or is it the id of the activity, with the
lesson being in extensions, or something?

name is the lesson title. Escape double quotes.

description is the lesson summary. Escape double quotes.


Use pause button
----------------

"verb": {
        "id": "http://activitystrea.ms/schema/1.0/use",
        "display": {
            "en-US": "used"
        }
    },

"object": {
	"objectType": "Activity",
	"id": "https://webapps.skilling.us/lesson/validation-functions/pause",
	"definition": {
		"type": "http://adlnet.gov/expapi/activities/interaction",
		    "name": {
                "en-us": "Interaction"
            }
		}
    }

id - the lesson URL.

"result": {
  "extensions": {
     "https://xapi.skillling.us/pause-button-number": "3",
     "https://xapi.skillling.us/pause-buttons-on-page": "5",
     "https://xapi.skillling.us/pause-button-use": "next" || "all"
  }
}

The button number is the position of the button in the lesson. There can be many buttons in a lesson.

Scroll position
---------------

Triggered by JS every so often.

"verb": {
        "id": "http://activitystrea.ms/schema/1.0/at",
        "display": {
            "en-US": "was at"
        }
    },

"object": {
	"objectType": "Activity",
	"id": "https://webapps.skilling.us/lesson/validation-functions",
	"definition": {
		"type": "https://xapi.skillling.us/location-in-page",
		    "name": {
                "en-US": "Location in page"
            }
		}
    }

"result": {
  "extensions": {
     "https://xapi.skillling.us/location-in-page": "3333"
  }
}


Leave lesson
-------------

Triggered by page leave - JS calls a controller on the app server.


"verb": {
        "id": "http://activitystrea.ms/schema/1.0/close",
        "display": {
            "en-US": "closed"
        }
    },

"object": {
	"objectType": "Activity",
	"id": "https://webapps.skilling.us/lesson/validation-functions",
	"definition": {
		"type": "http://id.tincanapi.com/activitytype/resource"
		}
    }

--- CUT SCREEN HERE ---

Feedback appreciated. 

