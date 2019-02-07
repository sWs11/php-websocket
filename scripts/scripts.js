$(function () {
    var socket = new WebSocket("ws://localhost:8090/chat/server.php");

    socket.onopen = function () {
        writeMessage('Open', 'success');
    };

    socket.onerror = function (error) {
        writeMessage('Error' + (error.message ? ": " + error.message : ""), 'danger');
    };

    socket.onclose = function (event) {

        console.log(event);

        writeMessage('Connection closed', 'warning');
    };

    socket.onmessage = function (event) {
        var data = JSON.parse(event.data);

        console.log(data);

        writeMessage(data.message, 'light');
    };
});

function writeMessage(message, status) {
    var notification_element = $("#alert-" + status);
    notification_element.find(".alert-" + status).text(message);
    var alert_html = $(notification_element).html();
    $(notification_element).find(".alert-" + status).text("");

    $("#notifications").append(alert_html);

    console.log(message);
}