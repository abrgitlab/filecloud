/**
 * Created by daemon on 02.05.16.
 */

$(function(){
    $('#input-id').fileinput();
    $('#input-id').on('change', function(e){
        alert('Change');
    });
});