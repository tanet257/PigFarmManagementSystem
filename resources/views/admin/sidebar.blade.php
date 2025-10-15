 <div class="d-flex align-items-stretch">
     {{-- SnackBar --}}
     <div id="snackbar" class="snackbar">
         <span id="snackbarMessage"></span>
         <button onclick="copySnackbar()" id="copyBtn"><i class="bi bi-copy"></i></button>
         <button onclick="closeSnackbar()">✕</button>
     </div>

     <!-- Sidebar Navigation-->
     <nav id="sidebar">
         <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
         <ul class="list-unstyled">

             {{-- แสดง Validation Errors --}}
             @if ($errors->any())
                 <div class="alert alert-danger">
                     <ul>
                         @foreach ($errors->all() as $error)
                             <li>{{ $error }}</li>
                         @endforeach
                     </ul>
                 </div>
             @endif

             <li class="active"><a href="{{ url('admin_index') }}"><i class="icon-home"></i>Home</a></li>
             <!--<li><a href="tables.html"> <i class="icon-grid"></i>Tables </a></li>-->
             <!--<li><a href="charts.html"> <i class="fa fa-bar-chart"></i>Charts </a></li>-->
             <!--<li><a href="forms.html"> <i class="icon-padnote"></i>Forms </a></li>-->

             <li><a href="#batchMenuDropdown" aria-expanded="false" data-toggle="collapse"> <i
                         class="icon-windows"></i>Add Batch </a>
                 <ul id="batchMenuDropdown" class="collapse list-unstyled ">
                     @if (Route::has('batches.index'))
                         <li><a href="{{ route('batches.index') }}">Index Batch</a></li>
                     @else
                         <li><a href="#" onclick="alert('Route batches.index ยังไม่ได้ถูกกำหนด!')">Index Batch</a>
                         </li>
                     @endif
                     <li><a href="{{ route('pig_entry_records.index') }}">Index Pig Entry Record</a></li>
                     <li><a href="{{ route('batch_pen_allocations.index') }}">Index batch_pen_allocations</a></li>
                 </ul>
             </li>

             <li><a href="#dairyDropdown" aria-expanded="false" data-toggle="collapse"> <i
                         class="icon-windows"></i>dairy record </a>
                 <ul id="dairyDropdown" class="collapse list-unstyled ">
                     <li><a href="{{ route('dairy_records.record') }}">dairy record</a></li>
                     <li><a href="{{ route('dairy_records.index') }}">View dairy </a></li>
                 </ul>
             </li>

             <li><a href="#storehouseDropdown" aria-expanded="false" data-toggle="collapse"> <i
                         class="icon-windows"></i>store house record </a>
                 <ul id="storehouseDropdown" class="collapse list-unstyled ">
                     <li><a href="{{ route('store_house_record.recordview') }}">store house record</a></li>
                     <li><a href="{{ route('storehouses.index') }}">View store house </a></li>
                     <li><a href="{{ route('inventory_movements.index') }}">View inventory movement</a></li>
                 </ul>
             </li>

             <li><a href="#pigsaleMenuDropdown" aria-expanded="false" data-toggle="collapse"> <i
                         class="icon-windows"></i>Add Pig Sale </a>
                 <ul id="pigsaleMenuDropdown" class="collapse list-unstyled ">
                     <li><a href="{{ route('pig_sales.index') }}">View Pig Sale</a></li>
                 </ul>
             </li>

             <li><a href="#farmMenuDropdown" aria-expanded="false" data-toggle="collapse"> <i
                         class="icon-windows"></i>Farm / Barn / Pen </a>
                 <ul id="farmMenuDropdown" class="collapse list-unstyled ">
                     <li><a href="#farmSubMenu" aria-expanded="false" data-toggle="collapse">Farm</a>
                         <ul id="farmSubMenu" class="collapse list-unstyled">
                             <li><a href="{{ url('add_farm') }}">Add Farm</a></li>
                             <li><a href="{{ url('view_farm') }}">View Farm</a></li>
                         </ul>
                     </li>
                     <li><a href="#barnSubMenu" aria-expanded="false" data-toggle="collapse">Barn</a>
                         <ul id="barnSubMenu" class="collapse list-unstyled">
                             <li><a href="{{ url('add_barn') }}">Add Barn</a></li>
                             <li><a href="{{ url('view_barn') }}">View Barn</a></li>
                         </ul>
                     </li>
                     <li><a href="#penSubMenu" aria-expanded="false" data-toggle="collapse">Pen</a>
                         <ul id="penSubMenu" class="collapse list-unstyled">
                             <li><a href="{{ url('add_pen') }}">Add Pen</a></li>
                             <li><a href="{{ url('view_pen') }}">View Pen</a></li>
                         </ul>
                     </li>
                 </ul>
             </li>

         </ul>
     </nav>
     <!-- Sidebar Navigation end-->
