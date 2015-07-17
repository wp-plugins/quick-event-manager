function pseudo_popup(content) {
    var popup = document.createElement("div");
    popup.innerHTML = content;
    var viewport_width = window.innerWidth;
    var viewport_height = window.innerHeight;
    function add_underlay() {
        var underlay = document.createElement("div");
        underlay.style.position = "fixed";
        popup.style.zIndex = "9997";
        underlay.style.top = "0px";
        underlay.style.left = "0px";
        underlay.style.width = viewport_width + "px";
        underlay.style.height = viewport_height + "px";
        underlay.style.background = "#7f7f7f";
        if( navigator.userAgent.match(/msie/i) ) {
            underlay.style.background = "#7f7f7f";
            underlay.style.filter = "progid:DXImageTransform.Microsoft.Alpha(opacity=50)";
        } else {
            underlay.style.background = "rgba(127, 127, 127, 0.5)";
        }
        underlay.onclick = function() {
            underlay.parentNode.removeChild(underlay);
            popup.parentNode.removeChild(popup);
        };
        document.body.appendChild(underlay);
    }
    add_underlay();
    var x = viewport_width / 2;
    var y = viewport_height / 2;
    popup.style.position = "fixed";
    document.body.appendChild(popup);
    x -= popup.clientWidth / 2;
    y -= popup.clientHeight / 2;
    popup.style.zIndex = "9998";
    popup.style.top = y + "px";
    popup.style.left = x + "px";
    return false;
}