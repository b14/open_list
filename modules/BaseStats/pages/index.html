<!DOCTYPE html>
<html>
<head>
  <title>Openlist version 2.x.1.0</title>
  
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
  <script>
    $(function () {
      $.getJSON('/json/callModule?module_name=BaseStats&admin&method=getTotalUsage', function (data) {
        var lists = elements = 0;
        
        for (var i in data) {
          lists += parseInt(data[i].lists, 10);
          elements += parseInt(data[i].elements, 10);
          
          
          $('#list-type').clone()
            .removeAttr('id')
            .removeClass('template')
            .find('> .citem .name').text(i).end()
            .find('> .citem .count').text(data[i].lists).end()
            .appendTo($('.list-types', $('.tl')));
            
          $('#list-type').clone()
            .removeAttr('id')
            .removeClass('template')
            .find('> .citem .name').text(i).end()
            .find('> .citem .count').text(data[i].elements).end()
            .appendTo($('.list-types', $('.te')));

          
        }
        
        $('#total-lists .count').text(lists);
        $('#total-elements .count').text(elements);
      });
      
      $.getJSON('/json/callModule?module_name=BaseStats&admin&method=getMonthlyUsage', function (data) {
        for (var year in data) {
          var elements = lists = 0,
            $year = $('#year').clone()
            .removeAttr('id')
            .removeClass('template')
            .find('> .citem .name').text(year)
            .end();
            
          for (var month in data[year]) {
            var $month = $('#month').clone()
              .removeAttr('id')
              .removeClass('template')
              .prependTo($('.months', $year));
              
            var ml = me = 0;
            
            for (var type in data[year][month]) {
              if (data[year][month][type].lists) {
                lists += parseInt(data[year][month][type].lists, 10);
                ml += parseInt(data[year][month][type].lists, 10);
              }
              
              if (data[year][month][type].elements) {
                elements += parseInt(data[year][month][type].elements, 10);
                me += parseInt(data[year][month][type].elements, 10);
              }
              
              $('#list-type-both').clone()
                .removeAttr('id')
                .removeClass('template')
                .find('> .citem .name').text(type).end()
                .find('> .citem .count .lists').text(data[year][month][type].lists).end()
                .find('> .citem .count .elements').text(data[year][month][type].elements).end()
                .appendTo($('.list-types', $month));
            }
              
            $month.find('> .citem .name').text(month).end()
              .find('> .citem .count .lists').text(ml).end()
              .find('> .citem .count .elements').text(me).end()
          }
          
          $year
            .find('> .citem .count .lists').text(lists).end()
            .find('> .citem .count .elements').text(elements).end()
            .prependTo('#years');
        }
        
        $('a.citem').not('.linkout').bind('click', function (evt) {
          evt.preventDefault();
          $(this).next().slideToggle();
        }).next().slideToggle();
      });
    });
  </script>
  
  <style>
    .wrap {
      width: 50%;
    }
    .citem {
      display: block;
    }
    .citem:hover {
      background-color: #eee;
      display: block;
    }
    a {
      color: #000;
      border-bottom: 1px solid #fff;
    }
    a:hover {
      color: #999;
      border-bottom: 1px solid #ccc;
    }
    a.linkout:hover {
      border-bottom: 1px solid #fff;
    }
    .name, .count {
      float: left;
    }
    .name {
      width: 50%;
    }
    .count {
      width: 50%;
      text-align: right;
    }
    .count .lists,
    .count .elements {
      float: left;
      width: 50%;
    }
    
    .service .name {
      padding-left: 20px;
      margin-right: -20px;
    }
    .method .name {
      padding-left: 40px;
      margin-right: -40px;
    }
    
    hr {
      margin: 20px 0;
    }
    
    .template {
      display: none;
    }
    
    .citem + div .citem .name {
      padding-left: 10px;
      box-sizing: border-box;
    }
    
    .citem + div .citem + div .name {
      padding-left: 20px;
      box-sizing: border-box;
    }
  </style>
</head>
<body>


<div id="year" class="template year">
  <a class="citem" href="#">
    <div class="name"></div>
    <div class="count">
      <div class="lists"></div>
      <div class="elements"></div>
    </div>
    <div style="clear: both"></div>
  </a>
  <div class="months"></div>
  <hr />
</div>
<div id="month" class="template month">
  <a class="citem" href="#">
    <div class="name"></div>
    <div class="count">
      <div class="lists"></div>
      <div class="elements"></div>
    </div>
    <div style="clear: both"></div>
  </a>
  <div class="list-types"></div>
</div>

<div id="list-type" class="template list-type">
  <div class="citem">
    <div class="name"></div>
    <div class="count"></div>
    <div style="clear: both"></div>
  </div>
</div>

<div id="list-type-both" class="template list-type-both">
  <div class="citem">
    <div class="name"></div>
    <div class="count">
      <div class="lists"></div>
      <div class="elements"></div>
    </div>
    <div style="clear: both"></div>
  </div>
</div>

<h1>Openlist version 2.x.1.0</h1>
<div class="wrap">
  <h2>Total usage</h2>
  <div class="total">
    <div class="tl">
      <a id="total-lists" class="citem">
        <div class="name">Lists</div>
        <div class="count"></div>
        <div style="clear: both"></div>
      </a>
      <div class="list-types"></div>
    </div>
    
    <div class="te">
      <a id="total-elements" class="citem">
        <div class="name">Elements</div>
        <div class="count"></div>
        <div style="clear: both"></div>
      </a>
      <div class="list-types"></div>
    </div>
  </div>
  
  <hr />
  <h2>Montly</h2>
  <div id="years"></div>
</div>

</body>
</html>

