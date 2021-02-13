
/* Global variables. */
var loops = 0;
var start = 0;

function slogDriver() {
    //alert( "Wow");
    loops = 0;

    HTTP = new XMLHttpRequest();
    //HTTP.addEventListener( 'load', reqListener );
    HTTP.addEventListener("progress", updateProgress);
    HTTP.addEventListener("load", transferComplete);
    HTTP.addEventListener("error", transferFailed);
    HTTP.addEventListener("abort", transferCanceled);

    goAgain();

    return false;
}


function reqListener( evt) {
    console.log( 'Event');
    console.log(evt );

}

// progress on transfers from the server to the client (downloads)
function updateProgress (oEvent) {
    if (oEvent.lengthComputable) {
        var percentComplete = oEvent.loaded / oEvent.total * 100;
        // ...
    } else {
        // Unable to compute progress information since the total size is unknown
    }
}

function transferComplete(evt) {
    console.log("The transfer is complete.");
    //console.log( HTTP.response );
    //console.log( HTTP.response.length);
    console.log( HTTP );
    reportResults();
    repeatRequest();

}


function getElapsed() {
    var elapsed = new Date().getTime() - start;
    elapsed /= 1000;
    elapsed = elapsed.toFixed( 3 );
    return elapsed;
}

function getResults() {
    var results = getElapsed();
    results += ' ';
    results += HTTP.responseURL;
    results += ' ';
    results += HTTP.status;
    results += ' ';
    results += HTTP.response.length;
    return results;
}

function reportResults() {
    var listitem = document.createElement("LI");
    var t = document.createTextNode( getResults() );      // Create a text node
    listitem.appendChild(t);
    document.getElementById("results").appendChild(listitem);
}

function transferFailed(evt) {
    console.log("An error occurred while transferring the file.");

}

function transferCanceled(evt) {
    console.log("The transfer has been canceled by the user.");
}

function goAgain() {
    start = new Date().getTime();
    var url = document.getElementById("url").value;
    HTTP.open("GET", url);
    HTTP.send();
}

function incLoops() {
    loops++;
    return loops;
}

function repeatRequest() {
    console.log( 'Shall we go again');
    var done = incLoops();
    if ( done <  document.getElementById("limit").value ) {
        console.log( loops );
        goAgain();
    }
}
