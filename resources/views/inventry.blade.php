
@include('sidebar')
<div class="col-md-10 offset-md-2">
  <div class="tab-pane pag4" id="pag4" role="tabpanel">
      <div class="sv-tab-panel">
        <div>
        	<div class=" mt-3">
            @if(session()->has('success'))
                <div class="alert alert-success">
                    {{ session()->get('success') }}
                </div>
            @endif
             
            <h2>Inventories</h2> 
              <div class="all-button-box row d-flex justify-content-end">
                <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6 col-6">
                    <form method="post" action="{{ route('bulk.inventory')}}" enctype="multipart/form-data"> 
                      {{ csrf_field() }}
                         <label class="btn btn-xs btn-primary btn-icon-only width-auto" for="file-upload">
                            <i class="fa fa-plus"></i> {{__('Upload CSV')}}
                        </label>
                        <input id="file-upload" name='bulk_inventory' type="file" class="d-none">
                        <label class="btn-inner--icon bulk-inventory-btn"  for="submit-bulk-data"><i class="fa fa-upload" >
                        </i>
                      </label>
                       <input type="submit" name="bulk_inventory" id="submit-bulk-data" class="d-none" >
                   </form>
                   @php 
                   $file= url('/'). "/uploads/sampleInventory.csv";
                   @endphp
                   <a href="{{$file}}" download><button class="btn btn-primary">Download Sample CSV</button></a>
                </div>
              </div> 
              @if(isset($inventries))      
              <table class="table table-striped" id="inventry_table" style="width:100%">
                <thead>
                  <tr>
                    <th>S.no</th>
                    <th>User name</th>
                    <th>User email</th>
                    <th>Truck name</th>
                    <th>Bin name</th>
                    <th>Part name</th>
                    <th>Part code</th>
                    <th>Quantity</th>
                    <th>Created at</th>
                    <th>Updated at</th>
                  </tr>
                </thead>
                <tbody>
                  <?php $count = 0;?>
                	@foreach ($inventries as $inventry)
                  <?php
                      $binname_arr = $inventry->trucks->binName;
                      $bins = json_decode($binname_arr,true);
                      $binId = $inventry->bin_id; 
                      $index = array_search($binId, array_column($bins, 'id'));
                      $binName = $bins[$index]['bin_name'];

                      ?>
                  <?php $count++;
                  ?>
                  <tr>
                  	<td>{{$count}}</td>
                  	<td><a href="{{route('user-profile', ['id' => $inventry->user->id])}}">{{$inventry->user->name}}</td>
                    <td>{{$inventry->user->email}}<a></td>
                  	<td>{{$inventry->trucks->truckName}}</td>
                    <td>{{$binName}}</td>
                    <td>{{$inventry->item_name}}</td>
                    <td>{{$inventry->item_code}}</td>
                  	<td>{{$inventry->quantity}}</td>
                  	<td>{{$inventry->created_at}}</td>
                  	<td>{{$inventry->updated_at}}</td>
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

<!-- Modal -->
<div class="modal fade " id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <ul>
          <li>Invalid User : @if(session()->has('invalidUser'))               
                    {{ session()->get('invalidUser') }}
            @endif
          </li>
          <li>Invalid Data : @if(session()->has('invalidData'))               
                    {{ session()->get('invalidData') }}
            @endif
          </li>
          <li>Existing Data : @if(session()->has('existingData'))               
                    {{ session()->get('existingData') }}
            @endif
          </li>
          <li>New Data : @if(session()->has('newData'))               
                    {{ session()->get('newData') }}
            @endif
          </li>
        </ul>
      </div>
      <div class="modal-footer">
         <a href="{{ route('bulk.inventory.close')}}" class="btn btn-xs btn-secondary btn-icon-only width-auto">
            </i> {{__('Close')}}
          </a>
         <a href="{{ route('bulk.inventory.submit')}}" class="btn btn-xs btn-primary btn-icon-only width-auto">
            </i> {{__('OK')}}
          </a>
      </div>
    </div>
  </div>
</div>

<!-- main section closed -->
</section>
<script type="text/javascript">
      $(document).ready(function() {
          $('#inventry_table').DataTable(
            {
                dom: 'lBfrtip',
                buttons: [
                    {
                        extend: 'csv',
                        title: 'CSV'
                    }
                ]
            });
      } );
      @if(session()->has('getDataCount'))
        $('#exampleModal').modal('show');
      @endif 
</script>

<style type="text/css">
  .bulk-inventory-btn{
    font-size: 0.875rem;
    line-height: 2.5;
    background: #0f5ef7;
    border-radius: 50% !important;
    color: white;
    margin-right: 5px;
    padding: 10px 12px;
  }
</style>