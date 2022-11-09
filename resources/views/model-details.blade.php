
<style>
	.search-result {
	display: flex;
	flex-wrap: wrap;
	
	margin: 15px 0px;
}
.search-result a {
    flex: 0 31%;
        margin: 0px 5px;
}
</style>
@include('sidebar')
<div class="main_searchwrap border-success pt-3 w-50 m-auto">
	<div class="search_wrap text-center mt-5">
	  <span>Model Details</span>
	</div>
	<div class="">
       	<div class="tab-content">
       		<div class="tab-pane active main-section-cls" id="pag1" role="tabpanel">
	           <div class="">
		           	@if(!$variations)
			           	@foreach ($modelParts->parts as $part) 
			           	@php 
			           		$part = json_decode(json_encode($part), true);

			           	@endphp
		                    <a href="{{url('part-details', [$part['basePN']])}}">
						        <div class="card mb-3">
					                <div class="custom-row g-0">
					                    <div class="card_img">
					                      <img src="https://encompass-11307.kxcdn.com/imageDisplay?{{$part['picturePath']}}" class="img-fluid rounded-start" alt="..." height="100px" width="100px">
					                    </div>
					                    <div class="card_details">
					                      <div class="card-body">
					                        <p class="card-text">Part Number : {{$part['partNumber']}}</p>
					                      </div>
					                    </div>
					                </div>
					            </div>
					         </a>
		                @endforeach
		            @else
			            <div class="row">
	                        <div class="col-lg-12">
	                        	@php 
					           		$modelParts = json_decode(json_encode($modelParts), true);
					           	@endphp
	                        	<a href="{{url('part-details', [$basePN])}}">
							        <div class="card mb-3">
						                <div class="custom-row g-0">
						                    <div class="card_img">
						                      <img src="https://encompass.com/{{$modelParts['variationImage']}}" class="img-fluid rounded-start" alt="..." width="100px" height="100px">
						                    </div>
						                    <div class="card_details">
						                      <div class="card-body">
						                        <p class="card-text">Model Number : {{$modelParts['modelNumber']}}</p>
						                      </div>
						                    </div>
						                </div>
						            </div>
						         </a>
	                            <div class="horizontal-timeline">
	                              <span>variationsModel Models - </span>
	                                <ul class="bottom-tags list-unstyle">	                           
		                                  @foreach($modelParts['variations']  as $variationsModel)
		                                    <li><span class="model-variations" id="{{$variationsModel}}">{{$variationsModel}}</span></li>
		                                  @endforeach
	                                </ul> 
	                           </div>
	                        </div>
	                     </div>
	                     <div class="variations-parts">
	                     	
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
   $('.model-variations').on('click', function(){
            let modelVariations = $(this).attr("id");
            $.ajax({
                type : 'post',
                url : '{{URL::to('model-variations')}}',
                data:{ "_token": "{{ csrf_token() }}",
                            'variation': modelVariations,
                            'modelID' : `{{$modelId}}`
                    },
                success:function(data){
                	let modelHtml = "";
                	data.forEach((models)=> {
                		let basePN = models['basePN'];
                		var url = `{{url('/part-details/${basePN}')}}`;
                		 modelHtml += `<a href="${url}">
							<div class="card mb-3"><div class="custom-row g-0"><div class="card_img">
				                      <img src="https://encompass-11307.kxcdn.com/imageDisplay?${models['picturePath']}" class="img-fluid rounded-start" alt="..." width="100px" height="100px"></div><div class="card_details"><div class="card-body"><p class="card-text">Part Number : ${models['partNumber']}</p></div></div></div></div></a>`;
                	});
                	$('.variations-parts').html(modelHtml);
                }
            });
        });
</script>
 <style type="text/css">
 	.variations-parts {
    display: flex;
    width: 100%;
    flex-wrap: wrap;
    justify-content: space-between;
}

.variations-parts a {
    flex: 0 48%;
    max-width: 48%;
}
 </style>
