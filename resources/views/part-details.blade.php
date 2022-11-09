@include('sidebar')
<style>
      th span {
            font-weight: 500;
      }
      button.full-btn {
    width: 100%;
    border: 0px;
    padding: 15px;
    color: #fff;
    font-weight: 600;
    border-radius: 5px;
}
.w-100 textarea {
    width: 100%;
    margin: 10px 0px;
}
.row.mt-3.mb-3.btn-flex {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
}

button#addToCart {
    flex: 0 50%;
}

button.full-btn {
    max-width: 48%;
}
button#checkoutBtn {
    max-width: 100%;
}
ul.locations-ava li {
    opacity: 0.5;
}

</style>
<div class="col-md-10 offset-md-2">
   <section class="h-100 h-custom">
      <div class="container py-5 h-100">
        <div class="alert alert-success" id="notify-sucess" style="display:none"></div>
        <div class="alert alert-danger" id="notify-error" style="display:none"></div>
         <div class="row d-flex justify-content-center align-items-center h-100">
            <div class="col-lg-8 col-xl-12">
               <div class="card border-top border-bottom border-3" style="border-color: #000 !important;">
                  <div class="card-body p-5">
                     <div class="row">
                        <div class="col-md-4">
                            @php 
                            if(empty($partDetails->images)){
                                $image = "https://encompass-11307.kxcdn.com/imageDisplay?";
                            }else{
                                $imagesPart = json_decode(json_encode($partDetails->images[0]), true);
                                $image  = $imagesPart['thumbPath'];
                            }
                            @endphp
                          <img src="{{$image}}" width="100px" height="100px">
                           <div class="card-details">
                              <span class="small text-muted">{{$partDetails->searchTerms[1]}}</span>
                              <span class="small text-muted " style="color: #f37a27 !important;">Original Part ?</span>
                             <span class="small text-muted">{{$partDetails->manufacturer->name}} {{$partDetails->partNumber}}</span>
                           </div>
                        </div> 
                        <div class="col-md-4 offset-md-4">
                           <h5>{{$partDetails->partNumber}}</h5>
                           <table>
                                <tr>
                                  <th><span>Name</span></th>
                                  <td>{{$partDetails->searchTerms[1]}}</span></td>
                                </tr>
                                 <tr>
                                  <th><span>In stock</span></th>
                                  <td>{{$partDetails->manufacturer->name}}</span></td>
                                </tr>
                                 <tr>
                                  <th><span>price</span></th>
                                  <td>{{$partDetails->totalPrice}}</span></td>
                                </tr>         
                              </table>
                        </div>
                     </div>
                     
                     <div class="row">
                        <div class="col-md-6">
                              <h5>Total Price</h5>
                              <span id="itemPrice" value="{{$partDetails->totalPrice}}">${{$partDetails->totalPrice}}</span>
                        </div>
                        <div class="col-md-4 offset-md-2">
                              <h6>Add To Cart </h6>
                           <div class="input-group">
                              <input type="button" value="-" class="button-minus border rounded-circle  icon-shape icon-sm mx-1 " data-field="quantity">
                              <input type="number" step="1" max="10" value="1" name="quantity" class="quantity-field border-0 text-center w-25">
                              <input type="button" value="+" class="button-plus border rounded-circle icon-shape icon-sm " data-field="quantity">
                           </div>
                           <span class="d-none currentQuantity" id="1"></span>
                        </div>
                     </div>
                     <div class="row mt-3 mb-3 btn-flex">
                        @php 
                        (\Storage::disk('local')->exists('cartItem')) ? \Storage::disk('local')->get('cartItem') : \Storage::disk('local')->put('cartItem', json_encode([]));
                        @endphp
                        <button class="full-btn" id="addToCart" value="{{$partDetails->basePN}}" style="background: #f37a27 !important;">Add To Cart({{count(json_decode(\Storage::disk('local')->get('cartItem'), true))}})</button><br> 
                        <button type="button" class="full-btn" style="background: #f37a27 !important;" data-toggle="modal" data-target="#ViewCartModel">View cart</button>
                     </div>
                    <div class="row">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input locationCheck" type="radio" name="shippingMethod" id="shippingMethod1" value="4" checked>
                            <label class="form-check-label locationCheck" for="shippingMethod1">PickUp</label>
                        </div>
                        <div class="form-check form-check-inline">
                          <input class="form-check-input locationCheck" type="radio" name="shippingMethod" id="shippingMethod2" value="2">
                          <label class="form-check-label locationCheck" for="shippingMethod2">Delivery</label>
                        </div>
                    </div>
                    <div class="DeliverySection" style="display:none">
                        <h5>Delivery </h5><br>
                        <div class="w-100">
                              <textarea>{{\Auth::user()->email}}</textarea>
                        </div>
                    </div>
                    <div class="row pickupSection">
                        <div class="col-lg-12">
                            <div class="horizontal-timeline">
                                <ul class="bottom-tags locations-ava list-unstyle">
                                    @php
                                        $count = 0;
                                    @endphp
                                  @foreach($partDetails->availableByLocations  as $location)
                                    @php
                                        $count++ ;
                                    @endphp
                                    <li for="location{{$count}}" location="{{$location->name}}"><label for="location{{$count}}"><input class="d-none form-check-input" type="radio" name="location" id="location{{$count}}" value="">{{$location->name}}</label></li>
                                  @endforeach
                                </ul>
                           </div>
                        </div>
                    </div>
                     <div class="row mt-3 mb-3">
                        <button class="full-btn" id="checkoutBtn" style="background: #f37a27 !important;" value="{{$partDetails->basePN}}">Checkout</button> 
                     </div>
                     <hr>
                     <div class="row my-4">
                        <div class="col-md-4 offset-md-8 col-lg-3 offset-lg-9">
                           <p class="lead fw-bold mb-0" style="color: #f37a27;"></p>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-lg-12">
                            <div class="horizontal-timeline">
                              <span>Compatible Models - </span>
                              <ul class="bottom-tags list-unstyle">
                           
                                  @foreach($partDetails->compatibleModels  as $compatiblemodels)
                                    <li><span><a href="{{url('/part-model', [$compatiblemodels->id, $partDetails->basePN])}}">{{$compatiblemodels->model}}</a></span></li>
                                  @endforeach
                                    
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
<div id="ViewCartModel" class="modal fade" role='dialog'>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
            </div>
             @if(\Auth::user()->address)
            <div class="modal-body">
                @php 
                    if(count(json_decode(\Storage::disk('local')->get('cartItem'), true)) > 0){
                     $cartItems = json_decode(\Storage::disk('local')->get('cartItem'), true);
                @endphp 
                    @foreach($cartItems as $item)
                            @php $cart = json_decode(json_encode($item), true);
                            @endphp
                    <div class="card mb-3 {{$cart['basePN']}}">
                      <div class="custom-row g-0">
                        <div class="card_img">
                          <img src="{{$cart['itemImage']}}" class="img-fluid rounded-start" alt="...">
                        </div>
                        <div class="card_details">
                          <div class="card-body">
                            <p class="card-text">Part Number : {{$cart['partNumber']}}</p>
                             <p class="card-text">Quantity : {{$cart['orderQuantity']}}</p>
                             <p class="card-text">Price : {{$cart['requestedPrice']}}</p>
                            <p class="card-text">Mfg Code : {{$cart['mfgCode']}}</p>
                          </div>
                        </div>
                        <div><span class="removeItem" id="{{$cart['basePN']}}"><i class="fa fa-remove"></i></span></div>
                      </div>
                    </div>
                   @endforeach
                @php }else{ @endphp
                <div class="card mb-3">
                      <div class="custom-row g-0">
                        <div class="card_details">
                          <div class="card-body">
                            <p class="card-text">Empty cart</p>
                          </div>
                        </div>
                      </div>
                    </div>
                @php } @endphp
            </div>
            @endif
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- main section closed -->
</section>
<style type="text/css">
   icon-shape {
   display: inline-flex;
   align-items: center;
   justify-content: center;
   text-align: center;
   vertical-align: middle;
   }
   .icon-sm {
   width: 2rem;
   height: 2rem;
   background: #f37a27;
   }
   .alert-success {
    color: #155724;
    background-color: #d4edda;
    border-color: #c3e6cb;
    position: fixed;
    width: 50%;
    top: 50%;
    right: 43%;
    transform: translate(50%, -50%);
    z-index: 1030;
}
ul.locations-ava.list-unstyle li label {
    border: 0px solid #000;
    padding: 10px 15px;
    /* margin: 28px 0px !important; */
    font-size: 14px;
    border-radius: 10px;
    cursor: pointer;
}
</style>
<script type="text/javascript">
    $( document ).ready(function() {
        $('#checkoutBtn').on('click', function(){
            let basePN = $(this).val();
            let currentQuantityval = $('.currentQuantity').attr("id");
            let shippingMethod     =  $("input[name='shippingMethod']:checked").val();
            let pickupLocation = $('.locations-ava li.active').attr('location');
            console.log('{{\Auth::user()->address}}');
            if(!'{{\Auth::user()->address}}'){
                alert('Please Fill Address First')
            }else{
                $.ajax({
                    type : 'post',
                    url : '{{URL::to('create-order')}}',
                    data:{ "_token": "{{ csrf_token() }}",
                                'basePN': basePN,
                                'quantity' : currentQuantityval,
                                'shippingMethod' : shippingMethod,
                                'pickupLocation' : pickupLocation
                        },
                    success:function(data){ 
                        if(data['errorMessage'] == "SUCCESS"){
                            let message = "Create order Successfully.";
                            notify('notify-sucess', message);
                            window.location.href = "{{ route('orders-parts')}}";
                        }else{
                            notify('notify-error', data['errorMessage'])
                        }
                       
                    }
                });
            }
        });
        function notify(notifyClass, message){
            $('#'+notifyClass).text(message);
            $("#"+notifyClass).show();
                setTimeout(function() {
                $("#"+notifyClass).hide();
            }, 5000);
        }
        $('#addToCart').on('click', function(){
            let basePN = $(this).val();
            let currentQuantityval = $('.currentQuantity').attr("id");
             if(!'{{\Auth::user()->address}}'){
                alert('Please Fill adress first')
            }else{
                    let cartdata = {
                        "basePN": `{{$partDetails->basePN}}`,
                        "mfgCode": `{{$partDetails->manufacturer->code}}`,
                        "partNumber":`{{$partDetails->partNumber}}`,
                        "orderQuantity": currentQuantityval,
                        "authorizationOrReferenceNumber": null,
                        "claimsProcessorCode": null,
                        "claimNumber": null,
                        "requestedPrice":`{{$partDetails->totalPrice}}`* currentQuantityval,
                        'itemImage'  : `{{$image}}`
                    } ;
                $.ajax({
                    type : 'post',
                    url : '{{URL::to('add-to-cart')}}',
                    data:{ "_token": "{{ csrf_token() }}",
                                'basePN': basePN,
                                "cart" : cartdata
                        },
                    success:function(data){ 
                        notify('notify-sucess', "Part Added on cart");
                        window.location.reload();
                    }
                });
            }
        });
        $('.removeItem').on('click', function(){
            let basePN = $(this).attr("id");
            $.ajax({
                type : 'post',
                url : '{{URL::to('remove-cart')}}',
                data:{ "_token": "{{ csrf_token() }}",
                            'basePN': basePN
                    },
                success:function(data){
                    if(data['basePN']){ 
                       $('.'+data['basePN']).fadeOut(300, function() {
                            $('.'+data['basePN']).remove();
                        });
                        notify('notify-sucess', "Part Removed From cart");
                        window.location.reload();
                    }else{
                        notify('notify-error', "Empty cart");
                        window.location.reload();
                    }
                }
            });
        });
        $('.locations-ava li').on('click', function(){
            $('.locations-ava li').css('opacity', 0.5);
            $(this).css('opacity', 1);
            let id = $(this).attr('class');
            $('.locations-ava li.active').removeClass('active');
            $(this).addClass('active');
        });
        $('.locationCheck').on('click', function(){
            let shippingMethod     =  $("input[name='shippingMethod']:checked").val();
            if(shippingMethod == 2){

                $('.pickupSection').css('display', "none");
                $('.DeliverySection').css('display', "block");
            }else{
                $('.DeliverySection').css('display', "none");
                $('.pickupSection').css('display', "block");
            }

        });
    })
   function incrementValue(e) {
     e.preventDefault();
     var fieldName = $(e.target).data('field');
     var parent = $(e.target).closest('div');
     var currentVal = parseInt(parent.find('input[name=' + fieldName + ']').val(), 10);
     let incrementPrice = 0;
     if (!isNaN(currentVal)) { 
        let currentNo = currentVal + 1;  
        incrementPrice = $('#itemPrice').attr('value') * currentNo;
        $('.currentQuantity').attr("id", currentNo);
         parent.find('input[name=' + fieldName + ']').val(currentVal + 1);
     } else {
         $('.currentQuantity').attr("id", 0)
         incrementPrice = $('#itemPrice').attr('value') * 0;
         parent.find('input[name=' + fieldName + ']').val(0);
     }
      $('#itemPrice').text("$"+incrementPrice);
   }
   
   function decrementValue(e) {
     e.preventDefault();
     var fieldName = $(e.target).data('field');
     var parent = $(e.target).closest('div');
     var currentVal = parseInt(parent.find('input[name=' + fieldName + ']').val(), 10);
    let decrementPrice = 0;
     if (!isNaN(currentVal) && currentVal > 0) {
        let currentNo = currentVal - 1; 
        decrementPrice = $('#itemPrice').attr('value') * currentNo;
        $('.currentQuantity').attr("id", currentNo);
         parent.find('input[name=' + fieldName + ']').val(currentVal - 1);
     } else {
        decrementPrice = $('#itemPrice').attr('value') * 0;
        $('.currentQuantity').attr("id", 0);
         parent.find('input[name=' + fieldName + ']').val(0);
     }
      $('#itemPrice').text("$"+decrementPrice);
   }
   
   $('.input-group').on('click', '.button-plus', function(e) {
     incrementValue(e);
   });
   
   $('.input-group').on('click', '.button-minus', function(e) {
     decrementValue(e);
   });
   
</script>