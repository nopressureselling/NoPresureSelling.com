function sidebarAjaxCallback($el){
    console.log('sidebarAjaxCallback');
    var chevronHtml = $el.closest('ul').find('span').clone();
    $el.closest('ul').find('li').each(function(){
       $(this).find('span').remove();
    });
    $el.prepend(chevronHtml);
}