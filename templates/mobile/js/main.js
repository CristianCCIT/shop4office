$(function(){
    if($('#carousel').length){
        setInterval(function(){
            var current = $('#carousel').find('a').not('.hidden');
            current.addClass('hidden');
            current.next().first().removeClass('hidden');
            current.insertAfter($('.hidden').last());
        }, 10000);
    }
});