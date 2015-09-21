<!DOCTYPE html>
<html>
<head>
  <title>GUID Statistics</title>
  
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
  <script>
    $(function () {
      $.getJSON('/json/callModule?module_name=BaseStats&admin&method=getFunctionCalls', function (data) {
        var stats = {
          ips: {},
          scopes: {
            global: {
              count: 0,
              services: {}
            }
          }
        };
        
        for (var ip in data) {
          stats.ips[ip] = 0;
          
          for (var scope in data[ip]) {
            if (stats.scopes[scope] === undefined) {
              stats.scopes[scope] = {
                count: 0,
                services: {}
              };
            }
            
            for (var func in data[ip][scope]) {
              var sm = func.split('__');
              
              if (stats.scopes[scope].services[sm[0]] === undefined) {
                stats.scopes[scope].services[sm[0]] = {};
              }
              if (stats.scopes[scope].services[sm[0]][sm[1]] === undefined) {
                stats.scopes[scope].services[sm[0]][sm[1]] = 0;
              }
              
              stats.scopes[scope].services[sm[0]][sm[1]] += data[ip][scope][func];
              stats.scopes[scope].count += data[ip][scope][func];
              
              if (scope === 'global') {
                stats.ips[ip] += data[ip][scope][func];
              }
            }
          }
        }
        
        for (var ip in stats.ips) {
          var $ip = $('#ip').clone()
            .removeAttr('id')
            .removeClass('template')
            .appendTo('#ips');
          $('.name', $ip).text(ip);
          $('.count', $ip).text(stats.ips[ip]);
        }
        
        createScope('Global', stats.scopes.global);
        
        for (var scope in stats.scopes) {
          if (scope === 'global') {
            continue;
          }
          createScope(scope, stats.scopes[scope]);
        }
        
        function createScope(scope_name, scope) {
          var $scope = $('#scope').clone()
            .removeAttr('id')
            .removeClass('template')
            .appendTo('#scopes' + (scope_name == 'Global' ? '-global' : '-rest'));
            
          if (scope_name != 'Global') {
            scope_name = scope_name.split('_');
            $('> .citem .name', $scope).text(scope_name[0] + '/' + scope_name[1] + ' - ' + scope_name[2] + ':00');
          } else {
          $('> .citem .name', $scope).text(scope_name);
          }
            
          
          $('> .citem .count', $scope).text(scope.count);
          
          for (var service in scope.services) {
            var
              count = 0,
              $service = $('#service').clone()
                .removeAttr('id')
                .removeClass('template')
                .appendTo($('.services', $scope));
                
            $('> .citem .name', $service).text(service);
            for (var method in scope.services[service]) {
              var $method = $('#method').clone()
                .removeAttr('id')
                .removeClass('template')
                .appendTo($('.methods', $service));
                
              $('> .citem .name', $method).text(method);
              $('> .citem .count', $method).text(scope.services[service][method]);
              count += scope.services[service][method];
              
              $('> .citem', $method).attr('href', location.protocol + '//' + location.host + '/' + service + '/' + method + '?format-options=PRETTY_PRINT');
            }
            
            
            $('> .citem .count', $service).text(count);
          }
        }
        
        $('.citem').not('.linkout').bind('click', function (evt) {
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
    
    .count:after {
      clear: both;
      content: '';
      display: block;
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
  </style>
</head>
<body>

<div id="ip" class="template ip">
  <div class="citem">
    <div class="name"></div>
    <div class="count"></div>
    <div style="clear: both"></div>
  </div>
</div>

<div id="scope" class="template scope">
  <a class="citem" href="#">
    <div class="name"></div>
    <div class="count"></div>
    <div style="clear: both"></div>
  </a>
  <div class="services"></div>
  <hr />
</div>
<div id="service" class="template service">
  <a class="citem" href="#">
    <div class="name"></div>
    <div class="count"></div>
    <div style="clear: both"></div>
  </a>
  <div class="methods"></div>
</div>
<div id="method" class="template method">
  <a class="citem linkout" href="#">
    <div class="name"></div>
    <div class="count"></div>
    <div style="clear: both"></div>
  </a>
</div>

<h1>DDB GUID Service V1.x.1.0</h1>
<div class="wrap">
  <h2>Requests per IP</h2>
  <div id="ips"></div>
  <hr />
  <h2>Total requests per function</h2>
  <div id="scopes-global"></div>
  <h2>Requests per function per hour</h2>
  <div id="scopes-rest"></div>
</div>

</body>
</html>

