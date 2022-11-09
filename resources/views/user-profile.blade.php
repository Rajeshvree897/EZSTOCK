@include('sidebar')
<div class="col-md-10 offset-md-2">
  <section class="h-100 h-custom user_profile_fm" style="background-color: #eee;">
    <div class="row">
        <div class="col-md-2">
            <div class="profile-img text-center">
              @php 
              $url = str_replace('public', '', URL::to('/')).'storage/app/'.$user_details[0]->profile_image;
              @endphp
                <img src="{{$url}}" alt=""/>
            </div>
            <div class="profile-work">
                <p class="add_title"><span>Address</span></p>
                <p>{{isset($user_details[0]->address) ? $user_details[0]->address : ''}}</p>
            </div>
        </div>
        <div class="col-md-10">
            <div class="profile-card">
            <div class="profile-head">
                        <h5>
                            {{isset($user_details[0]->name) ? $user_details[0]->name : ''}}
                        </h5>
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">About</a>
                    </li>
                </ul>
            </div>
             <div class="tab-content profile-tab" id="myTabContent">
                <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Name</label>
                                </div>
                                <div class="col-md-6">
                                    <p>{{isset($user_details[0]->name) ? $user_details[0]->name : ''}}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Email</label>
                                </div>
                                <div class="col-md-6">
                                    <p>{{isset($user_details[0]->email) ? $user_details[0]->email : ''}}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Phone</label>
                                </div>
                                <div class="col-md-6">
                                    <p>{{isset($user_details[0]->phone) ? $user_details[0]->phone : ''}}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Address</label>
                                </div>
                                <div class="col-md-6">
                                    <p>{{isset($user_details[0]->address) ? $user_details[0]->address : ''}}</p>
                                </div>
                            </div>
                </div>
            </div>
        </div>
        </div>
    </div>
    <hr>
      <div class="row" style="padding: 0px 15px;">
        <div class="col-md-4">
            <div class="">
            <a class="items_name" href="{{route('trucks', ['id' => $user_details[0]->id])}}"><span><h5>No of trucks : </h5></span><span class="itemnumber">{{$trucks}}</span></a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="d-flex align-items-baseline">
              <span><h5>No of bins : </h5></span>
              <span style="margin-left: 10px;">{{$total_bins}}</span>
            </div>
        </div>

         <div class="col-md-4">
            <div class="">
              <a class="items_name" href="{{route('inventory', ['id' => $user_details[0]->id])}}"><span><h5>No of inventories : </h5></span><span class="itemnumber">{{$total_inventries_sum}}</span></a>
            </div>
        </div>
      </div>
      
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h2>Recent Orders</h2>
                    <a href="{{route('orders', ['id' => $user_details[0]->id])}}">View all</a> 
                </div>
               @if(isset($orders))   
                <table class="table table-striped" id="order_table" style="width:100%">
                  <thead>
                    <tr>
                      <th>S. no</th>
                      <th>Order id</th>
                      <th>Shippind method</th>
                      <th>Part name</th>
                      <th>Part code</th>
                      <th>Quantity</th>
                      <th>Total</th>
                      <th>Status</th>
                      <th>Created at</th>
                      <th>Updated at</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php $count = 0;?>
                        @foreach ($orders as $order)

                         <?php
                          $item_details = json_decode($order->item_details, true);
                          $part_code = $item_details['parts'][0]['partNumber'];
                          $part_name = $item_details['parts'][0]['mfgCode'];
                          $quality   = count($item_details['parts']);
                          $count++;
                        ?>
                        <tr>
                          <td>{{$count}}</td>
                          <td><a href="{{route('order-details', ['id' => $order->order_id])}}">{{$order->order_id}}</a></td>
                          <td>@if($order->shipping_method == 1)Pick Up
                              @else 
                              Delivery
                              @endif
                          </td>
                          <td>{{$part_code}}</td>
                          <td>{{$part_name}}</td>
                          <td>{{$quality}}</td>
                          <td>{{$order->total}}</td>
                          <td>{{$order->status}}</td>
                          <td>{{$order->created_at}}</td>
                          <td>{{$order->updated_at}}</td>
                        </tr>
                        @endforeach
                  </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>
  </section>

</div>
<!-- main section closed -->
</section>
