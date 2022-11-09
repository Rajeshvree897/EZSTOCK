@if(\Auth::user())
<h1>nmvm</h1>
@include('sidebar')
<div class="col-md-10 offset-md-2">
   <div class="tab-content">
        <div class="tab-pane active" id="pag1" role="tabpanel">
            <div class="sv-tab-panel">
                <h2>Dashboard</h2>
                <div>
                    <ul class="list-inline same_list">
                        <li><img src="{{url('assets/images/user.png')}}" data-id = "pag2" alt="Image"/ class="tab-open">
                            <span>Users</span></li>
                        <li><img src="{{url('assets/images/truck.png')}}" data-id = "pag3" alt="Image"/ class="tab-open"><span>Trucks</span></li>
                        <li><img src="{{url('assets/images/inventory.png')}}" data-id = "pag4" alt="Image"/ class="tab-open">
                        <span>Inventory</span>
                        </li>
                        <li><img src="{{url('assets/images/checkout.png')}}" data-id = "pag5" alt="Image"/ class="tab-open">
                        <span>Orders</span>
                        </li>
                        <li><img src="{{url('assets/images/switch.png')}}" alt="Image"/ class="tab-open">
                            <span>Logout</span>
                        </li>
                     </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- main section closed -->
</section>
@endif