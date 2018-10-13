$(function(){
  'use strict';


  /////////////////// START: TEMPLATE SETTINGS /////////////////////
  var loc    = window.location.pathname;
  var path   = loc.split('/');
  var isRtl  = (path[2].indexOf('rtl') >= 0)? true : false;
  var newloc = '';

  // inject additional link tag for header skin
  $('head').append('<link id="headerSkin" rel="stylesheet" href="">');

  // inject template options content
  $.get('../settings.html', function(data){
      $('body').append(data);

      // set direction value in settings
      if(isRtl) {
        $('.slim-direction[value="rtl"]').prop('checked', true);
      } else {
        $('.slim-direction[value="ltr"]').prop('checked', true);
      }

      //check if header set to sticky
      if($.cookie('sticky-header')) {
        $('body').addClass('slim-sticky-header');
        $('.sticky-header[value="yes"]').prop('checked', true);
      } else {
        $('.sticky-header[value="no"]').prop('checked', true);
      }

      //check if header have skin
      if($.cookie('header-skin')) {
        var sk = $.cookie('header-skin');
        $('body').addClass(sk);
        $('#headerSkin').attr('href',  '../css/slim.'+sk+'.css');
        $('.header-skin[value="'+sk+'"]').prop('checked', true);
      } else {
        $('.header-skin[value="default"]').prop('checked', true);
      }

      //check if page set to wide
      if($.cookie('full-width')) {
        $('body').addClass('slim-full-width');
        $('.full-width[value="yes"]').prop('checked', true);
      } else {
        $('.full-width[value="no"]').prop('checked', true);
      }
  });

  // show/hide template options panel
  $('body').on('click', '.template-options-btn', function(e){
    e.preventDefault();
    $('.template-options-wrapper').toggleClass('show');
  });

  // set current page to light mode
  $('body').on('click', '.skin-light-mode', function(e){
    e.preventDefault();
    newloc = loc.replace('template-dark', 'template');
    $(location).attr('href', newloc);
  });

  // set current page to dark mode
  $('body').on('click', '.skin-dark-mode', function(e){
    e.preventDefault();
    if(loc.indexOf('template-dark') >= 0) {
      newloc = loc;
    } else {
      newloc = loc.replace('template', 'template-dark');
    }
    $(location).attr('href', newloc);
  });

  // set current page to rtl/ltr direction
  $('body').on('click', '.slim-direction', function(){
    var val = $(this).val();
    
    if(val === 'rtl') {
      if(!isRtl) {
        if(path[3]) {
          newloc = '/slim/'+path[2]+'-rtl/'+path[3];
        } else {
          newloc = '/slim/'+path[2]+'-rtl/';
        }
        $(location).attr('href', newloc);
      }
    } else {
      if(isRtl) {
        if(path[3]) {
          newloc = '/slim/'+path[2].replace('-rtl','')+'/'+path[3];
        } else {
          newloc = '/slim/'+path[2].replace('-rtl','')+'/';
        }
        $(location).attr('href', newloc);
      }
    }
  });

  // toggles header to sticky
  $('body').on('click', '.sticky-header', function(){
    var val = $(this).val();
    if(val === 'yes') {
      $.cookie('sticky-header', 'true');
      $('body').addClass('slim-sticky-header');
    } else {
      $.removeCookie('sticky-header');
      $('body').removeClass('slim-sticky-header');
    }
  });

  // set skin to header
  $('body').on('click', '.header-skin', function(){
    var val = $(this).val();
    if(val !== 'default') {
      $.cookie('header-skin', val);
      $('#headerSkin').attr('href','../css/slim.'+val+'.css');
    } else {
      $.removeCookie('header-skin');
      $('#headerSkin').attr('href', '');
    }
  });

  // set page to wide
  $('body').on('click', '.full-width', function(){
    var val = $(this).val();
    if(val === 'yes') {
      $.cookie('full-width', 'true');
      $('body').addClass('slim-full-width');
    } else {
      $.removeCookie('full-width');
      $('body').removeClass('slim-full-width');
    }
  });
  /////////////////// END: TEMPLATE SETTINGS /////////////////////

});
