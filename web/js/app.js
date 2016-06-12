/**
 * Created by daemon on 13.06.16.
 */
$(function(){
    var sideBar = $('.sidebar');

    sideBar.on('click', 'li.active a', function(e){
        e.preventDefault();
    });
});