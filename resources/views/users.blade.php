@include('sidebar')
<style>
  .dashboard-filter .form.d-flex.align-self-end.float-right {
    align-items: flex-end;
}
</style>
<div class="col-md-10 offset-md-2">
  <div class="tab-pane pag2" id="pag2" role="tabpanel">
    <div class="sv-tab-panel">
      <div>
        <div class=" mt-3">
          <h2>Users</h2> 
          <div class="dashboard-filter mt-5 row ">
            <div class="col-12">
            
                  <form method="GET" action ="{{route('user')}}" class="d-flex align-self-end float-right" style="align-items: flex-end;">
                      <div class="search_inputs">
                        <label>List of users who didn't ordered since</label>
                           <input class="form-control" name="orderdate" type="date" id="" value="@if(isset($start)){{$start}}@endif" max="@if(isset($start)){{$start}}@endif"> 
                      </div>
                      <div class="submit_btn">
                          <input type="submit" name="submit" value="Search" class="ml-3 btn btn-primary">
                      </div>
                  </form>
              </div>
              
          </div>
           @if(isset($users))   
            <table class="table table-striped" id="user_table" style="width:100%">
              <thead>
                <tr>
                  <th>Id </th>
                  <th>Name</th>
                  <th>Email </th>
                  <th>Address</th>
                  <th>Last order</th>
                  <th>Created at </th>
                  <th>Updated at</th>
                </tr>
              </thead>
              <tbody>
                 <?php $count = 0;?>
                @foreach ($users as $user)
                <?php
                 $count++;
                ?>
                <tr>
                  <td>{{$count}}</td>
                  <td><a href="{{route('user-profile', ['id' => $user->id])}}">{{$user->name}}</a></td>
                  <td>{{$user->email}}</td>
                  <td>{{$user->address}}</td>
                  <td>{{$user->order ?  $user->order->created_at : ""}}</td>
                  <td>{{$user->created_at}}</td>
                  <td>{{$user->updated_at}}</td>
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
        $('#user_table').DataTable({
          responsive: true,
          pageLength: 25,
          // lengthMenu: [0, 5, 10, 20, 50, 100, 200, 500],
          dom: 'Bfrtip',
          buttons: [
              'csv'
          ]
        });
  } );
    $('input[name="daterange"]').daterangepicker({
        locale: {
          format: 'YYYY-MM-DD'
        },
        startDate: '2022-05-22',
        endDate: `2022-05-22`
    });
</script>