
@include('sidebar')
<div class="main_searchwrap border-success pt-3 w-50 m-auto">
	<div class="search_wrap text-center mt-5">
	  <span>My Account</span>
	</div>
	<div class="">
       	<div class="tab-content">
       		<div class="tab-pane active main-section-cls" id="pag1" role="tabpanel">
       			<div class="container rounded bg-white mt-5 mb-5">
				    <div class="row">
				        <div class="col-md-3 border-right">
				            <div class="d-flex flex-column align-items-center text-center p-3 py-5"><img class="rounded-circle mt-5" width="150px" src="https://st3.depositphotos.com/15648834/17930/v/600/depositphotos_179308454-stock-illustration-unknown-person-silhouette-glasses-profile.jpg"><span class="font-weight-bold">{{\Auth::user()->name}}</span><span class="text-black-50">{{\Auth::user()->email}}</span><span> </span></div>
				        </div>
				        @php
				        	$address = \Auth::user()->address;
				        	if($address){
				        		$addressArray = explode(",", $address);
				        	}
				        @endphp
				        <div class="col-md-9 border-right">
				            <div class="p-3 py-5">
				                <div class="d-flex justify-content-between align-items-center mb-3">
				                    <h4 class="text-right">Profile Settings</h4>
				                </div>
				                <form method="POST" action="{{route('user-profile-update')}}">
				                	@csrf
				                	<div class="row mt-2">
					                    <div class="col-md-6"><label class="labels">Full Address</label><input type="text" class="form-control" placeholder="Full Address" value="@if(isset($addressArray)) {{$addressArray[0]}} @endif" name="full_address"></div>
					                    <div class="col-md-6"><label class="labels">City/Town</label><input type="text" class="form-control" value="@if(isset($addressArray)) {{$addressArray[1]}} @endif" placeholder="City/Town" name="city"></div>
					                </div>
					                <div class="row mt-3">
					                    <div class="col-md-12"><label class="labels">State</label><input type="text" class="form-control" placeholder="State" value="@if(isset($addressArray)) {{$addressArray[2]}} @endif" name ="state"></div>
					                    <div class="col-md-12"><label class="labels">Postal Code</label><input type="text" class="form-control" placeholder="postal code" value="@if(isset($addressArray)) {{$addressArray[3]}} @endif" name="postal_code"></div>
					                    <div class="col-md-12"><label class="labels">Country</label><input type="text" class="form-control" placeholder="us" value="@if(isset($addressArray)) {{$addressArray[4]}} @endif" name="country"></div>
					                </div>
					                <div class="mt-5 text-center"><button class="btn btn-primary profile-button" type="submit">Save</button></div>
				                </form>
				            </div>
				        </div>
				    </div>
				</div>
			</div>
		</div>
    </div>
</div>
</section>


<style type="text/css">
.form-control:focus {
    box-shadow: none;
    border-color: #BA68C8
}

.profile-button {
    background: #f37a27 !important;
    box-shadow: none;
    border: none
}

.profile-button:hover {
    bbackground: #f37a27 !important;
}

.profile-button:focus {
   background: #f37a27 !important;
    box-shadow: none
}

.profile-button:active {
    background: #f37a27 !important;
    box-shadow: none
}

.back:hover {
    color: #682773;
    cursor: pointer
}

.labels {
    font-size: 11px
}

.add-experience:hover {
    background: #BA68C8;
    color: #fff;
    cursor: pointer;
    border: solid 1px #BA68C8
}
</style>