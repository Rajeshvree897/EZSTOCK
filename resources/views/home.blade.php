@include('sidebar')

        <div class="col-md-10 offset-md-2">
           <div class="tab-content">
                <div class="tab-pane active main-section-cls" id="pag1" role="tabpanel">
                    <div class="sv-tab-panel">
                        <h2>Dashboard</h2>
                        <div class="main-section-child">
                            <ul class="list-inline same_list">
                                @if(\Auth::user()->role =='admin')
                                <li><a href="{{route('user')}}"><img src="{{url('assets/images/user.png')}}" data-id = "pag2" alt="Image"/ class="tab-open"></a>
                                    <span>Users</span></li>
                                @endif
                                <li><a href="{{route('trucks')}}"><img src="{{url('assets/images/truck.png')}}" data-id = "pag3" alt="Image"/ class="tab-open"></a>
                                    <span>Trucks</span>
                                </li>
                                <li><a href="{{route('inventory')}}"><img src="{{url('assets/images/inventory.png')}}" data-id = "pag4" alt="Image"/ class="tab-open"></a>
                                    <span>Inventory</span></li>
                                <li><a href="{{route('orders')}}"><img src="{{url('assets/images/checkout.png')}}" data-id = "pag5" alt="Image"/ class="tab-open"></a>
                                    <span>Orders</span></li>
                                <li>
                                    <a class="" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <img src="{{url('assets/images/switch.png')}}" alt="Image"/ class="tab-open">
                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                            @csrf
                                        </form>
                                    </a>
                                    <span>Logout</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <hr>
                    <div class="dashboard-content">
                        <div class="row">
                             <div class="col-md-6">
                                    <div class="card">
                                       <div class="card-header">
                                        <img class="img-fluid" src="{{url('assets/images/truck.png')}}" data-id = "pag5" alt="Image"/> Total Trucks
                                        <span class="pull-right">Statistics
                                        </span>
                                      </div>
                                      <div class="card-body">
                                        <h5 class="card-title" id="total_trucks">{{$trucks}}</h5>

                    
                                      </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                       <div class="card-header">
                                        <img class="img-fluid" src="{{url('assets/images/inventory.png')}}" data-id = "pag5" alt="Image"/> Total Inventory
                                        <span class="pull-right">Statistics
                                        </span>
                                      </div>
                                      <div class="card-body">
                                        <h5 class="card-title no-of-total" id="total_inventory">{{$inventries}}</h5>
                                       <!--  <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
                                        <a href="#" class="btn btn-primary">Go somewhere</a> -->
                                      </div>
                                    </div>
                                </div>
                        </div>
                    </div>
                    <div class="dashboard-main my-5">
                        <div class="row">
                            <div class="col-md-12">
                                <button class="change_all_dates btn btn-primary float-right mb-3"> Select date
                                <input type="text" name="daterange" id="daterange"  class="change_all_dates filter_content"  data-id='changes_all'>
                                </button>
                            </div>
                            
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                   <div class="card-header">
                                    <img class="img-fluid" src="{{url('assets/images/checkout.png')}}" data-id = "pag5" alt="Image"/> Orders
                                    <span class="pull-right">Statistics
                                    </span>
                                  </div>
                                  <div class="card-body">
                                    <h5 class="card-title" id="total_orders">{{$orders}}</h5>
                                   <!--  <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
                                    <a href="#" class="btn btn-primary">Go somewhere</a> -->
                                  </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                   <div class="card-header">
                                    <img class="img-fluid" src="{{url('assets/images/checkout.png')}}" data-id = "pag5" alt="Image"/> Sales
                                    <span class="pull-right">Statistics
                                    </span>
                                  </div>
                                  <div class="card-body">
                                    <h5 class="card-title" id="total_sales">{{$sales}}</h5>
                                   <!--  <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
                                    <a href="#" class="btn btn-primary">Go somewhere</a> -->
                                  </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="dashboard-content">
                        <div class="row">
                           @if(\Auth::user()->role =='admin')
                                <div class="col-md-4">
                                    <div class="card">
                                   <div class="card-header">
                                    <img class="img-fluid" src="{{url('assets/images/user.png')}}" data-id = "pag5" alt="Image"/> Total users
                                    <span class="pull-right">Statistics
                                    </span>
                                  </div>
                                  <div class="card-body">
                                    <h5 class="card-title changes_all" id="total_users">{{$users}}</h5>
                                   <!--  <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
                                    <a href="#" class="btn btn-primary">Go somewhere</a> -->
                                  </div>
                                    </div>
                                </div>
                            @endif
                             
                        </div>
                    </div>
                    @if(\Auth::user()->role =='admin')
                    <div class="dashboard-filter mt-5 row">
                       <div class="col-lg-4">
                            <div class="card">
                               <div class="card-header">
                                <label>List of users who didn't ordered since </label>
                               </div>
                                <div class="card-body">
                                    <form method="GET" action ="{{route('user')}}" class="d-flex">
                                        <div class="search_inputs">
                                             <input class="form-control user-order-cal" name="orderdate" type="date" id="" value="@if(isset($current_date)){{$current_date}}@endif" max="@if(isset($current_date)){{$current_date}}@endif"> 
                                        </div>
                                        <div class="submit_btn">
                                            <input type="submit" name="submit" value="Search" class="ml-3 btn btn-primary">
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>  
     
        </div>
    </div>
             
           <!--  </div>
        </div> -->
   </section>



<script type="text/javascript">
    $( document ).ready(function() {
        $('.tab-open').on('click', function(){
            let tabId = $(this).attr("data-id");
            $(".nav-link").removeClass("active");
            $(".tab-pane").removeClass("active");
  
            $("."+tabId).addClass("active show");
            $("."+tabId).addClass("active show");
        });
        
    });
    $('input[name="daterange"]').daterangepicker({
        locale: {
          format: 'YYYY-MM-DD'
        },
        startDate: `{{$start}}`,
        endDate: `{{$end}}`
    }, function(start, end, label) {
    getTodayTotalOrders(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
  });

    function getTodayTotalOrders(start, end ) {
        console.log('hh');
            $.ajax({
                url: '{{route('filter.content')}}',
                type: 'POST',
                data: {
                    "start": start, "end":end, "_token": "{{ csrf_token() }}",
                },
                success: function (data) {
                         $('#total_orders').text(data.total_orders);
                         $('#total_users').text(data.total_users);
                         $('#total_sales').text(data.total_sales);

                    
                }
            });
    }



</script>


<style type="text/css">
    .tab-open{
        height: 80px;
        width: 80px;
    }
</style>