"use strict";

// @link https://stackoverflow.com/questions/14573223/set-cookie-and-get-cookie-with-javascript
function setCookie(name,value,days) {
    let expires = "";
    if (days) {
        let date = new Date();
        date.setTime(date.getTime() + (days*24*60*60*1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "")  + expires + "; path=/";
}
function getCookie(name) {
    const nameEQ = name + "=";
    const ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}

let current_cookie = getCookie('1815_last-viewed');
const post_id = document.querySelector('[name=post_id]').content;

if (current_cookie) {
    if (current_cookie.indexOf(post_id) === -1) {
        current_cookie = post_id + ',' + current_cookie;

        setCookie('1815_last-viewed', current_cookie, 30);
    }
} else {
    setCookie('1815_last-viewed', post_id, 30);
}

