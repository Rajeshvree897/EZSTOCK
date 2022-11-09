@include('sidebar')
<div class="col-md-10 offset-md-2">
  <div class="tab-pane pag3" id="pag3" role="tabpanel">
    <div class="sv-tab-panel">
      <div>
        <div class=" mt-3">
          <h2>Truck Bins</h2> 
          @if(isset($bins))      
            <table class="table table-striped" id="bin_table" style="width:100%">
              <thead>
                <tr>
                  <th>S.No</th>
                  <th>User name</i></th>
                  <th>User email</th>
                  <th>Truck name</th>
                  <th>Bins</th>
                  <th>Category</th>
                  <th>created at</th>
                  <th>created at</th>
                </tr>
              </thead>
              <tbody>
                <?php $count = 0;?>
                @foreach ($bins as $bin)
                   <?php
                    $binname_arr = $bin->binName;
                    $arr = json_decode($binname_arr,true);
                    foreach($arr as $key){
                     $count =  $count +1;
                    ?>
                    <tr>
                      <td>{{$count}}</td>
                      <td><a href="{{route('user-profile', ['id' => isset($bin->users->id)])}}">{{isset($bin->users->name) ? $bin->users->name : ''}}</a></td>
                       <td>{{isset($bin->users->email) ? $bin->users->email : ''}}</td>
                      <td>{{$bin->truckName}}</td>
                      <td>{{$key['bin_name']}}</td>
                      <td>{{$key['bin_category']}}</td>
                      <td>{{$bin->created_at}}</td>
                      <td>{{$bin->updated_at}}</td>
                    </tr>
                    <?php }?>
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
      $('#bin_table').DataTable();
  } );
</script>