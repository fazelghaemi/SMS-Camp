(function($){
  $(document).on('click', '#rsms-fetch-tpl', function(e){
    e.preventDefault();
    var tpl = $('input[name=template]').val();
    if(!tpl) return;
    var $btn=$(this), $out=$('#rsms-tpl-preview');
    $btn.prop('disabled', true).text('در حال دریافت...');
    $.post(ajaxurl, { action:'rsms_fetch_template', _ajax_nonce: rsms.nonce, template: tpl }, function(res){
      $btn.prop('disabled', false).text('دریافت متن الگو');
      if(res && res.status==='ok'){ $out.text(res.preview || '').show(); }
      else { $out.text('خطا در دریافت الگو').show(); }
    });
  });

  // Enforce max params 10 and maxlength 38
  function enforceLimits(){
    $('.rsms-param').attr('maxlength',40);
    var $wrap = $('#rsms-params-wrap');
    var count = $wrap.find('input.rsms-param').length;
    if(count<10){
      for(var i=count+1;i<=10;i++){
        $wrap.append('<input type="text" class="rsms-input small rsms-param" name="p'+i+'" placeholder="param'+i+'" maxlength="40" /> ');
      }
    }
  }
  enforceLimits();

  // Audience source toggles: site_all (none), excel (show file), manual (show textarea), registered/buyers (none)
  function toggleAudience(){
    var src = $('input[name=aud_source]:checked').val();
    $('.aud-block').hide();
    if(src==='excel') $('#aud-excel').show();
    if(src==='manual') $('#aud-manual').show();
  }
  $(document).on('change','input[name=aud_source]', toggleAudience);
  $(toggleAudience);
})(jQuery);