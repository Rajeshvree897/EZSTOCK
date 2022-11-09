
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
  <span>Find a part you want from Encompass dealer.<br>
  	Simply search by the model, part or brand
  	and get the desired listed item right before you.
  </span>
</div>
<div class="border border-success pt-3">
	<div class="input-group mb-3">
	  	<input type="text" class="form-control" id="searchItem" placeholder="Search by model , brand or part number" aria-label="Recipient's username">
		  <div class="input-group-append">
		    <span class="input-group-text"><i class="fa fa-search"></i></span>
		  </div>
	  </div>
</div>
	<div class="">
       	<div class="tab-content">
       		<div class="tab-pane active main-section-cls" id="pag1" role="tabpanel">
	           <div class="search-result">
	           </div>
	        </div>
         </div>
    </div>

     </div>
             
           <!--  </div>
        </div> -->
   </section>


<script type="text/javascript">
    $( document ).ready(function() {
		$('#searchItem').on('keyup', function(){
	    	let searchTxt = $(this).val();
			$.ajax({
				type : 'get',
				url : '{{URL::to('search-parts')}}',
				data:{'search': searchTxt},
				success:function(data){
					$('.search-result').html(data);
				}
			});
    	});
    })
</script>

