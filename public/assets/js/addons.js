define([], function () {
    require.config({
    paths: {
        'voicenotice': '../addons/voicenotice/js/voicenotice',
        'socket.io':['../addons/voicenotice/js/socket.io.min','https://cdn.bootcdn.net/ajax/libs/socket.io/2.4.0/socket.io.min']
    }
});


if (window.Config.actionname == "index" && window.Config.controllername == "index") {
    require(['voicenotice'], function (voicenotice) {
       voicenotice.start();
    })
}
});