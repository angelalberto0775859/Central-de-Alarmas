function criarCookieAviso() {
    var date = new Date();
    date.setTime(date.getTime() + 31104000000);
    var expires = "; expires=" + date.toGMTString();

    document.cookie = "esconder-aviso-cookies=true" + expires + "; path=/";
}

function fecharAviso() {
    var aviso = document.querySelector("#aviso-cookies");

    aviso.style.display = "none";

    criarCookieAviso();

    return false;
}

(function() {
    function readCookie() {
        var nameEQ = "esconder-aviso-cookies=";
        var ca = document.cookie.split(";");
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) === " ") {
                c = c.substring(1, c.length);
            }
            if (c.indexOf(nameEQ) === 0) {
                return c.substring(nameEQ.length, c.length);
            }
        }
        return null;
    }

    if (!readCookie()) {
        var aviso = document.querySelector("#aviso-cookies");
        aviso.style.display = "flex";
    } else {
        criarCookieAviso();
    }
})();