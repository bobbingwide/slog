/**
 * @package slog
 * @copyright (C) Copyright Bobbing Wide 2021
 * @returns {boolean}
 */


function slogCopyToClipboard() {
    //alert( "Copy");
    var copyText = document.getElementById("slog-csv");
    console.log( copyText.value );
    /* Select the text field */
    //copyText.select();
    //copyText.setSelectionRange(0, 99999); /* For mobile devices */

    /* Copy the text inside the text/textarea field */
    //document.execCommand("copy");
    updateClipboard( copyText.value );
    return false;
}

function updateClipboard(newClip) {
    navigator.clipboard.writeText(newClip).then(function() {
        /* clipboard successfully set */
        alert( "Copied to clipboard. Bytes:" + newClip.length);
    }, function() {
        /* clipboard write failed */
        alert( "Copy failed");
    });
}