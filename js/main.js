
/* сохраняем кнопку в переменную btn */
if (document.getElementById("copyBtn")) {
    var btn = document.getElementById("copyBtn");
    var text = document.getElementById("shortLink");
}

/* вызываем функцию при нажатии на кнопку */
btn.onclick = function () {
    var aux = document.createElement("input");
    aux.setAttribute("value", text);
    document.body.appendChild(aux);
    aux.select();
    document.execCommand("copy");
    document.body.removeChild(aux);
    document.getElementById('copyBtn').textContent = "Скопировано!";
}