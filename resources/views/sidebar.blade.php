<link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<!------ Include the above in your HEAD tag ---------->

<!doctype html>
<html lang="pt-br">

<head>
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, user-scalable=no">
  <meta http-eqiv="X-UA-Compatible" content="IE=edge" />

  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
 <!-- datatable -->
 <script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
   <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/jszip-2.5.0/dt-1.10.20/b-1.6.1/b-colvis-1.6.1/b-html5-1.6.1/b-print-1.6.1/r-2.2.3/datatables.min.css" />
      <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/v/bs4/jszip-2.5.0/dt-1.10.20/b-1.6.1/b-colvis-1.6.1/b-html5-1.6.1/b-print-1.6.1/r-2.2.3/datatables.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
      <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>

  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
   <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
<!--    date picker
 -->    
 <!-- Include Required Prerequisites -->
<script type="text/javascript" src="//cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script> 
<!-- Include Date Range Picker -->
<script type="text/javascript" src="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js"></script>
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css" />

    
  <title>Encompass dashboard</title>
</head>

<body>
    <section class="main-sec">        
        <div class="custom-container p-0">
            <div class="vertical-tabs">
                <div class="custom-row">
                    <div class="p-0 slidebar_toggle show">
                        <i class="fa fa-bars float-right" id="toggle-sidebar" aria-hidden="true"></i>
                         <div class="sidebar_section sidebar-content">
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link @if(route('dashboard') == Request::url() || route('home') == Request::url())  active @endif" data-toggle="" href="{{route('dashboard')}}" role="tab" aria-controls="home"><i class="fa fa-tachometer" aria-hidden="true"></i><span class="ml-2">Dashboard</span></a>
                                </li>
                                <li class="nav-item">
                                  <a class="nav-link pag2 @if(route('my-account') == Request::url()) active @endif" data-toggle="" href="{{route('my-account')}}" role="tab" aria-controls="profile"><i class="fa fa-user" aria-hidden="true"></i><span class="ml-2">My Account</span></a>
                                </li>
                                @if(\Auth::user()->role =='admin')
                                <li class="nav-item">
                                  <a class="nav-link pag2 @if(route('user') == Request::url()) active @endif" data-toggle="" href="{{route('user')}}" role="tab" aria-controls="profile"><i class="fa fa-users" aria-hidden="true"></i><span class="ml-2">Users</span></a>
                                </li>
                                @endif
                                <li class="nav-item">
                                  <a class="nav-link pag3 @if(route('trucks') == Request::url()) active @endif" data-toggle="" href="{{route('trucks')}}" role="tab" aria-controls="messages"><i class="fa fa-truck" aria-hidden="true"></i><span class="ml-2">Trucks</span></a>
                                </li>
                                <li class="nav-item">
                                  <a class="nav-link pag4  @if(route('inventory') == Request::url()) active @endif" data-toggle="" href="{{route('inventory')}}" role="tab" aria-controls="settings"><i class="fa fa-indent" aria-hidden="true"></i><span class="ml-2">Inventory</span></a>
                                </li>
                                @if(\Auth::user()->role =='admin')
                                <li class="nav-item">
                                  <a class="nav-link pag5  @if(route('orders-parts') == Request::url()   || Request::route('id')) active @endif" data-toggle="" href="{{route('orders-parts')}}" role="tab" aria-controls="orders"><i class="fa fa-cart-plus" aria-hidden="true"></i><span class="ml-2">Order Parts</span></a>
                                </li>
                                @endif
                                <li class="nav-item">
                                  <a class="nav-link pag5  @if(route('orders') == Request::url()   || Request::route('id')) active @endif" data-toggle="" href="{{route('orders')}}" role="tab" aria-controls="orders"><i class="fa fa-cart-plus" aria-hidden="true"></i><span class="ml-2">Orders</span></a>
                                </li>
                                <li class="nav-item">
                                     
                                    <a class="nav-link" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"> <i class="fa fa-sign-out" aria-hidden="true"></i><span class="ml-2">{{ __('Logout') }}</span>  

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                    </a>
                               
                                </li>
                            </ul>
                        </div>
                </div>
            
            </div>
        </div>


<script type="text/javascript">
    $( document ).ready(function() {
        $('#toggle-sidebar').on('click', function(){
                $(".slidebar_toggle").toggleClass('show');
        });
    });

</script>
      