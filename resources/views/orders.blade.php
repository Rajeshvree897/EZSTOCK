@include('sidebar')
<div class="col-md-10  offset-md-2">
  <div class="tab-pane pag5" id="pag5" role="tabpanel">
    <div class="sv-tab-panel">
      <div>
        <div class=" mt-3">
          <h2>Orders</h2> 
           @if(isset($orders))   
            <table class="table table-striped" id="order_table" style="width:100%">
              <thead>
                <tr>
                  <th>S. no</th>
                  <th>Order id</th>
                  <th>User name</th>
                  <th>Address</th>
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
                  $part_code = isset($item_details['parts'][0]['partNumber']) ? $item_details['parts'][0]['partNumber'] : "";
                  $part_name = isset($item_details['parts'][0]['mfgCode']) ? $item_details['parts'][0]['mfgCode'] : "";
                  $quality   = count($item_details['parts']);
                  $count++;
                ?>
                <tr>
                  <td>{{$count}}</td>
                  <td><a href="{{route('order-details', ['id' => $order->order_id])}}">{{$order->order_id}}</a></td>
                  <td><a href="{{route('user-profile', ['id' => $order->order_user->id])}}">{{$order->order_user->name}}</a></td>
                  <td>{{$order->order_user->address}}</td>
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
  </div>
</div>
<!-- main section closed -->
</section>

<script type="text/javascript">
      $(document).ready(function() {
      $('#order_table').DataTable({
        "pageLength": 50,
      });
  } );
</script>