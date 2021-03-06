"use strict";

var pjax = require('jquery-pjax');
var swal = require('sweetalert');
var Base = require('./mods/base.js');
var Editor = require('./mods/editor.js');
var Admin = require('./mods/admin/admin.js');


$(function() {
    addPjax();
    pageInit();
});


$(document).off('pjax:end');
$(document).on('pjax:end', function(e, xhr, options) {
    $('#pjax-container .container-content').addClass('we-animation-fade');
    setTimeout(function() {
        $('#pjax-container .container-content').removeClass('we-animation-fade');
    }, 1000);
    addPjax();
    pageInit();

});

// 添加pjax支持
function addPjax() {

    var a = $(document).find('a.pjax');

    a.unbind('click');
    a.bind('click', function(event) {
        var url = $(this).attr('href');
        var container = '#pjax-container';
        var fragment = '#pjax-content';

        $.pjax({
            url: url,
            container: container,
            fragment: fragment,
            timeout:0
        });

        return false;
    });

}

var pageInit = function() {

  Base.init();
  Editor.init();
  Admin.init();

}
