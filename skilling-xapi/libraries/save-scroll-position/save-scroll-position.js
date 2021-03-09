(function (Drupal, $) {
  "use strict";
  Drupal.skilling = Drupal.skilling || {};
  Drupal.skilling.saveScrollPositionSetup = false;
  // Make some variables.
  Drupal.skilling.saveScrollPosition = {};
  // Identifies the elements we want to count, all of them on the page.
  Drupal.skilling.saveScrollPosition.containerSelector = ".field--name-body";
  Drupal.skilling.saveScrollPosition.allElementsSelector = "p";
  // The elements list.
  Drupal.skilling.saveScrollPosition.allElements = null;
  // Number of elements.
  Drupal.skilling.saveScrollPosition.countAllElements = 0;
  // In seconds.
  Drupal.skilling.saveScrollPosition.samplingFrequency = 5;
  Drupal.skilling.saveScrollPosition.debug = false;
  Drupal.behaviors.saveScrollPosition = {
    attach: function (context, settings) {
      if (! Drupal.skilling.saveScrollPositionSetup) {
        Drupal.skilling.saveScrollPositionSetup = true;
        $(document).ready(function(){
          window.addEventListener("beforeunload", function(event) {
            Drupal.behaviors.saveScrollPosition.lessonUnload();
          });
          // Find containers. Maybe more than one. Use the first one.
          let container = $(Drupal.skilling.saveScrollPosition.containerSelector);
          if (container.length === 0) {
            return;
          }
          container = $(container).get(0);
          // Get all target elements in the container.
          Drupal.skilling.saveScrollPosition.allElements = [];
          let allElements = $(container).find(Drupal.skilling.saveScrollPosition.allElementsSelector);
          // Drop the elements that are collapsed.
          for (let i = 0; i < allElements.length; i++) {
            let elementToCheck = allElements[i];
            let rememberElement = true;
            // Skip things that can collapse.
            if ($(elementToCheck).closest(".collapse").length > 0) {
              if (Drupal.skilling.saveScrollPosition.debug) {
                console.log("Skipping item with collapsed parent");
              }
              rememberElement = false;
            }
            // Skip MCQ explanations.
            if ($(elementToCheck).parent().hasClass("skilling-mcq-response-explanation")) {
              rememberElement = false;
            }
            // Skip FiB explanations.
            if ($(elementToCheck).parent().hasClass("skilling-fib-response-message")) {
              rememberElement = false;
            }
            if (rememberElement) {
              Drupal.skilling.saveScrollPosition.allElements.push(elementToCheck);
            }

          }
          Drupal.skilling.saveScrollPosition.countAllElements
              = Drupal.skilling.saveScrollPosition.allElements.length;
          if (Drupal.skilling.saveScrollPosition.countAllElements > 0) {
            //Start sampling.
            window.setInterval(
                Drupal.behaviors.saveScrollPosition.findPosition,
                Drupal.skilling.saveScrollPosition.samplingFrequency * 1000
            );
            if (Drupal.skilling.saveScrollPosition.debug) {
              console.log("Ready to sample...");
            }
          }
        });
      }
    }, //End attach.
    /**
     * Find the position of what the user is seeing in the viewport.
     * @return {boolean}
     */
    findPosition() {
      if (Drupal.skilling.saveScrollPosition.debug) {
        $(".bg-primary").removeClass("bg-primary");
        $(".bg-success").removeClass("bg-success");
      }
      let firstVisibleElement = null;
      let firstVisibleElementIndex = 0;
      for (let i = 0; i < Drupal.skilling.saveScrollPosition.countAllElements; i++) {
        let childToTest = Drupal.skilling.saveScrollPosition.allElements[i];
        if (Drupal.behaviors.saveScrollPosition.isInViewport(childToTest)) {
          firstVisibleElement = childToTest;
          firstVisibleElementIndex = i;
          break;
        }
      }
      if (Drupal.skilling.saveScrollPosition.debug) {
        console.log("First vis: ", firstVisibleElement);
      }
      // Exit if nothing visible.
      if (firstVisibleElement == null) {
        return false;
      }
      // Find last visible element.
      // let lastVisibleElement = null;
      // let lastVisibleElementIndex = 0;
      // for (let i = firstVisibleElementIndex; i < Drupal.skilling.saveScrollPosition.countAllElements; i++) {
      //   let previousElement = Drupal.skilling.saveScrollPosition.allElements[i - 1];
      //   let childToTest = Drupal.skilling.saveScrollPosition.allElements[i];
      //   if (! Drupal.behaviors.saveScrollPosition.isInViewport(childToTest)) {
      //     lastVisibleElement = previousElement;
      //     lastVisibleElementIndex = i - 1;
      //     break;
      //   }
      // }
      // if (Drupal.skilling.saveScrollPosition.debug) {
      //   console.log("Last vis: ", lastVisibleElement);
      // }
      // if (lastVisibleElement === null) {
      //   // All elements after the first one are visible.
      //   // The last visible one is the last node.
      //   lastVisibleElementIndex = Drupal.skilling.saveScrollPosition.countAllElements - 1;
      //   lastVisibleElement = Drupal.skilling.saveScrollPosition.allElements[lastVisibleElementIndex];
      // }
      // Save the data.
      Drupal.behaviors.saveScrollPosition.xapiSaveScrollPosition(
          firstVisibleElementIndex,
          // lastVisibleElementIndex,
          Drupal.skilling.saveScrollPosition.countAllElements
      );
      if (Drupal.skilling.saveScrollPosition.debug) {
        console.log("First index:", firstVisibleElementIndex,
            // "Last ", lastVisibleElementIndex,
            "Total", Drupal.skilling.saveScrollPosition.countAllElements);
        $(firstVisibleElement).addClass("bg-primary");
        // $(lastVisibleElement).addClass("bg-success");
      }
      return true;
    },
    /**
     * Is an element in the browser's viewport?
     *
     * @param element
     * @return {boolean}
     */
    isInViewport(element) {
      const rect = element.getBoundingClientRect();
      return (
          rect.top >= 0 &&
          rect.left >= 0 &&
          rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
          rect.right <= (window.innerWidth || document.documentElement.clientWidth)
      );
    },
    xapiSaveScrollPosition(
        // Position of the first visible element in the container.
        firstVisibleElementIndex,
        // Position of the last visible element in the container.
        // lastVisibleElementIndex,
        // Number of element in the container.
        countAllElements
    ) {
      $.ajax({
        url: Drupal.url("skilling-xapi/save-scroll-position"),
        type: 'POST',
        dataType: 'json',
        data: {
          "csrfToken": drupalSettings.csrfToken,
          "sessionId": drupalSettings.sessionId,
          "countAllElements": countAllElements,
          "firstVisibleElementIndex": firstVisibleElementIndex,
          // "lastVisibleElementIndex": lastVisibleElementIndex,
          "nid": drupalSettings.skillingXapi.nid
        },
        success: function(result) {
          if (Drupal.skilling.saveScrollPosition.debug) {
            console.log("Save success");
          }
        },
        fail: (function (jqXHR, textStatus) {
          if (Drupal.skilling.saveScrollPosition.debug) {
            console.log("Save fail");
          }
        })
      });
    },
    lessonUnload() {
      $.ajax({
        url: Drupal.url("skilling-xapi/save-lesson-unload"),
        type: 'POST',
        dataType: 'json',
        data: {
          "csrfToken": drupalSettings.csrfToken,
          "sessionId": drupalSettings.sessionId,
          "nid": drupalSettings.skillingXapi.nid
        },
        success: function(result) {
          if (Drupal.skilling.saveScrollPosition.debug) {
            console.log("Save success");
          }
        },
        fail: (function (jqXHR, textStatus) {
          if (Drupal.skilling.saveScrollPosition.debug) {
            console.log("Save fail");
          }
        })
      });

    }

  };
})(Drupal, jQuery);

