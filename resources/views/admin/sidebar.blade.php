 <div class="d-flex align-items-stretch">
      <!-- Sidebar Navigation-->
      <nav id="sidebar">
        <!-- Sidebar Header-->
        <div class="sidebar-header d-flex align-items-center">
          <div class="avatar"><img src="admin/img/avatar-6.jpg" alt="..." class="img-fluid rounded-circle"></div>
          <div class="title">
            <h1 class="h5">Mark Stephen</h1>
            <p>Web Designer</p>
          </div>
        </div>
        <link rel="stylesheet"
                    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
        <!-- Sidebar Navidation Menus--><span class="heading">Main</span>
        <ul class="list-unstyled">

            {{-- แสดง Validation Errors --}}
        @if($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

                <li class="active"><a href="{{url('admin_index')}}"><i class="icon-home"></i>Home</a></li>
                <!--<li><a href="tables.html"> <i class="icon-grid"></i>Tables </a></li>-->
                <!--<li><a href="charts.html"> <i class="fa fa-bar-chart"></i>Charts </a></li>-->
                <!--<li><a href="forms.html"> <i class="icon-padnote"></i>Forms </a></li>-->

                <li><a href="#batchMenuDropdown" aria-expanded="false" data-toggle="collapse"> <i class="icon-windows"></i>Add Batch </a>
                  <ul id="batchMenuDropdown" class="collapse list-unstyled ">
                    @if (Route::has('batches.index'))
                        <li><a href="{{route('batches.index')}}">Index Batch</a></li>
                    @else
                        <li><a href="#" onclick="alert('Route batches.index ยังไม่ได้ถูกกำหนด!')">Index Batch</a></li>
                    @endif
                  </ul>
                </li>

                <li><a href="#pigEntryRecordMenuDropdown" aria-expanded="false" data-toggle="collapse"> <i class="icon-windows"></i>Add Pig Entry Record </a>
                  <ul id="pigEntryRecordMenuDropdown" class="collapse list-unstyled ">
                    <li><a href="{{route('pig_entry_records.record')}}">Add Pig Entry Record</a></li>
                    <li><a href="{{route('pig_entry_records.index')}}">Index Pig Entry Record</a></li>
                  </ul>
                </li>

                <li><a href="#storehouseDropdown" aria-expanded="false" data-toggle="collapse"> <i class="icon-windows"></i>store house record </a>
                  <ul id="storehouseDropdown" class="collapse list-unstyled ">
                    <li><a href="{{route('store_house_record.record')}}">store house record</a></li>
                    <li><a href="{{route('storehouses.index')}}">View store house record</a></li>
                  </ul>
                </li>

                <li><a href="#farmMenuDropdown" aria-expanded="false" data-toggle="collapse"> <i class="icon-windows"></i>Add Farm </a>
                  <ul id="farmMenuDropdown" class="collapse list-unstyled ">
                    <li><a href="{{url('add_farm')}}">Add Farm</a></li>
                    <li><a href="{{url('view_farm')}}">View Farm</a></li>
                  </ul>
                </li>

                <li><a href="#barnMenuDropdown" aria-expanded="false" data-toggle="collapse"> <i class="icon-windows"></i>Add Barn </a>
                  <ul id="barnMenuDropdown" class="collapse list-unstyled ">
                    <li><a href="{{url('add_barn')}}">Add Barn</a></li>
                    <li><a href="{{url('view_barn')}}">View Barn</a></li>
                  </ul>
                </li>

                <li><a href="#penMenuDropdown" aria-expanded="false" data-toggle="collapse"> <i class="icon-windows"></i>Add Pen </a>
                  <ul id="penMenuDropdown" class="collapse list-unstyled ">
                    <li><a href="{{url('add_pen')}}">Add Pen</a></li>
                    <li><a href="{{url('view_pen')}}">View Pen</a></li>
                  </ul>
                </li>

                <li><a href="#batchtreatmentMenuDropdown" aria-expanded="false" data-toggle="collapse"> <i class="icon-windows"></i>Add Batch Treatment </a>
                  <ul id="batchtreatmentMenuDropdown" class="collapse list-unstyled ">
                    <li><a href="{{url('add_batch_treatment')}}">Add Batch Treatment</a></li>
                    <li><a href="{{url('view_batch_treatment')}}">View Batch Treatment</a></li>
                  </ul>
                </li>

                <li><a href="#costMenuDropdown" aria-expanded="false" data-toggle="collapse"> <i class="icon-windows"></i>Add Cost </a>
                  <ul id="costMenuDropdown" class="collapse list-unstyled ">
                    <li><a href="{{url('add_cost')}}">Add Cost</a></li>
                    <li><a href="{{url('view_cost')}}">View Cost</a></li>
                  </ul>
                </li>

                <li><a href="#pigsellMenuDropdown" aria-expanded="false" data-toggle="collapse"> <i class="icon-windows"></i>Add Pig Sell </a>
                  <ul id="pigsellMenuDropdown" class="collapse list-unstyled ">
                    <li><a href="{{url('add_pig_sell')}}">Add Pig Sell</a></li>
                    <li><a href="{{url('view_pig_sell')}}">View Pig Sell</a></li>
                  </ul>
                </li>

                <li><a href="#feedingMenuDropdown" aria-expanded="false" data-toggle="collapse"> <i class="icon-windows"></i>Add Feeding </a>
                  <ul id="feedingMenuDropdown" class="collapse list-unstyled ">
                    <li><a href="{{url('add_feeding')}}">Add Feeding</a></li>
                    <li><a href="{{url('view_feeding')}}">View Feeding</a></li>
                  </ul>
                </li>

                <li><a href="#pigdeathDropdown" aria-expanded="false" data-toggle="collapse"> <i class="icon-windows"></i>Add Pig Death </a>
                  <ul id="pigdeathDropdown" class="collapse list-unstyled ">
                    <li><a href="{{url('add_pig_death')}}">Add Pig Death</a></li>
                    <li><a href="{{url('view_pig_death')}}">View Pig Death</a></li>
                  </ul>
                </li>

                <li><a href="login.html"> <i class="icon-logout"></i>Login page </a></li>
        </ul><span class="heading">Extras</span>
        <ul class="list-unstyled">
          <li> <a href="#"> <i class="icon-settings"></i>Demo </a></li>
          <li> <a href="#"> <i class="icon-writing-whiteboard"></i>Demo </a></li>
          <li> <a href="#"> <i class="icon-chart"></i>Demo </a></li>
        </ul>
      </nav>
      <!-- Sidebar Navigation end-->
