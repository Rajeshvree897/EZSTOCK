
@include('sidebar')
<div class="col-md-10 offset-md-2">
  <section class="h-100 h-custom" style="background-color: #eee;">
    <div class="container py-5 h-100">
      <div class="row d-flex justify-content-center align-items-center h-100">
        <div class="col-lg-8 col-xl-6">
          <div class="card border-top border-bottom border-3" style="border-color: #000 !important;">
            <div class="card-body p-5">
              <?php $address =  json_decode($order_details[0]->address, true);
                $item_details = json_decode($order_details[0]->item_details, true);
                
             ?>
              <p class="lead fw-bold mb-5">Order Reciept</p>

              <div class="row">
                <div class="col mb-3">
                  <p class="small text-muted mb-1">Date</p>
                  <p>{{$order_details[0]->created_at}}</p>
                </div>
                <div class="col mb-3">
                  <p class="small text-muted mb-1">Order No.</p>
                  <p>{{$order_details[0]->order_id}}</p>
                </div>
              </div>
              <?php 
            foreach($item_details['parts'] as $detail){?>
                <div class="mx-n5 px-5 py-4" style="background-color: #f2f2f2;">
                  <div class="row">
                    <div class="col-md-6 col-lg-6">
                      <p>Part Number</p>
                    </div>
                    <div class="col-md-6 col-lg-6">
                      <p><?php if(!empty($detail['partNumber'])){echo $detail['partNumber']; }?></p>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-6 col-lg-6">
                      <p>BasePN</p>
                    </div>
                    <div class="col-md-6 col-lg-6">
                      <p><?php if(!empty($detail['basePN'])){ echo $detail['basePN']; }?></p>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-6 col-lg-6">
                      <p>price</p>
                    </div>
                    <div class="col-md-6 col-lg-6">
                      <p><?php if(!empty($detail['totalPrice'])){echo $detail['totalPrice'];} ?></p>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-6 col-lg-6">
                      <p>Shipping</p>
                    </div>
                    <div class="col-md-6 col-lg-6">
                      <p>
                        @if($order_details[0]->shipping_method == '4')
                          Delivery
                        @else
                          @if($order_details[0]->shipping_method == '1') 
                            Pick Up
                          @endif
                        @endif
                      </p>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-6 col-lg-6">
                      <p >Description</p>
                    </div>
                    <div class="col-md-6 col-lg-6">
                      <p ><?php if(!empty($detail['partDescription'])){ echo $detail['partDescription']; }?></p>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-6 col-lg-6">
                      <p >Quantity</p>
                    </div>
                    <div class="col-md-6 col-lg-6">
                      <p ><?php echo $detail['orderQuantity']; ?></p>
                    </div>
                  </div>
                </div>
                <hr>
            <?php }
            ?>
              <div class="row my-4">
                <div class="col-md-4 offset-md-8 col-lg-3 offset-lg-9">
                  <p class="lead fw-bold mb-0" style="color: #f37a27;">${{$order_details[0]->total}}</p>
                </div>
              </div>
              <div class="row">
                <div class="col-lg-12">

                  <div class="horizontal-timeline">

                    <ul class="list-inline items d-flex justify-content-between">
                      <li class="list-inline-item items-list">
                        <p class="py-1 px-2 rounded text-white" style="background-color: #71706f;">Order status {{$order_details[0]->status}}</p class="py-1 px-2 rounded text-white">
                      </li>
                    </ul>

                  </div>

                </div>
              </div>

              <p class="mt-4 pt-2 mb-0">Want any help? <a href="#!" style="color: #f37a27;">Please contact us</a></p>

            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

</div>
<!-- main section closed -->
</section>