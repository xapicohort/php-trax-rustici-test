// Identifies the elements we want to count, all of them on the page.
let allElementsSelector = ".field--name-body p";
// The elements list.
let allElements = null;
// Number of elements.
let countAllElements = 0;
// In seconds.
let samplingFrequency = 3;
let debug = true;

$(document).ready(function(){
    allElements = $(allElementsSelector);
    countAllElements = allElements.length;
    if (countAllElements > 0) {
        //Start sampling.
        window.setInterval(findPosition, samplingFrequency * 1000);
        if (debug) {
            console.log("Ready to sample...");
        }
    }
});

function findPosition() {
    if (debug) {
        $(".bg-primary").removeClass("bg-primary");
        $(".bg-success").removeClass("bg-success");
    }
    let firstVisibleElement = null;
    let firstVisibleElementIndex = 0;
    for (let i = 0; i < countAllElements; i++) {
        let childToTest = allElements[i];
        if (isInViewport(childToTest)) {
            firstVisibleElement = childToTest;
            firstVisibleElementIndex = i;
            break;
        }
    }
    if (debug) {
        console.log("First vis: ", firstVisibleElement);
    }
    // Exit if nothing visible.
    if (firstVisibleElement == null) {
        return false;
    }
    // Find last visible element.
    let lastVisibleElement = null;
    let lastVisibleElementIndex = 0;
    for (let i = firstVisibleElementIndex; i < countAllElements; i++) {
        let previousElement = allElements[i - 1];
        let childToTest = allElements[i];
        if (! isInViewport(childToTest)) {
            lastVisibleElement = previousElement;
            lastVisibleElementIndex = i - 1;
            break;
        }
    }
    if (debug) {
        console.log("Last vis: ", lastVisibleElement);
    }
    if (lastVisibleElement === null) {
        // All elements after the first one are visible.
        // The last visible one is the last node.
        lastVisibleElementIndex = countAllElements - 1;
        lastVisibleElement = allElements[lastVisibleElementIndex];
    }
    if (debug) {
        console.log("First index:", firstVisibleElementIndex,
            "Last ", lastVisibleElementIndex, "Total", countAllElements);
        $(firstVisibleElement).addClass("bg-primary");
        $(lastVisibleElement).addClass("bg-success");
    }
}

function isInViewport(element) {
    const rect = element.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}