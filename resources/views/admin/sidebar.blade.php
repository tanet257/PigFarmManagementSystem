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

             {{-- <li class="active"><a href="{{ url('admin_index') }}"><i class="icon-home"></i>Home</a></li> --}}

             <li class="active"><a href="{{ route('dashboard.index') }}"><i class="bi bi-speedometer2"></i>Dashboard</a></li>

             <li><a href="#batchMenuDropdown" aria-expanded="false" data-toggle="collapse"> <i
                         class="bi bi-piggy-bank"></i>รุ่นหมู </a>
                 <ul id="batchMenuDropdown" class="collapse list-unstyled ">

                     <li ><a href="{{ route('batch.index') }}">
                         <i class="bi bi-list-ul"></i> ดูรุ่นทั้งหมด
                     </a></li>
                     <li ></li>
                     <li><a href="{{ route('batch_pen_allocations.index') }}">
                         <i class="bi bi-houses"></i> จัดการคอก
                     </a></li>
                 </ul>
             </li>

             <li><a href="#dairyDropdown" aria-expanded="false" data-toggle="collapse"> <i
                         class="bi bi-droplet-half"></i>บันทึกประจำวัน </a>
                 <ul id="dairyDropdown" class="collapse list-unstyled ">
                     <li><a href="{{ route('dairy_records.record') }}">บันทึกประจำวัน</a></li>
                     <li><a href="{{ route('dairy_records.index') }}">ดูบันทึกประจำวัน</a></li>
                     <li><a href="{{ route('treatments.index') }}">จัดการการรักษา</a></li>
                 </ul>
             </li>

             <li><a href="#storehouseDropdown" aria-expanded="false" data-toggle="collapse"> <i
                         class="bi bi-box-seam"></i>store house </a>
                 <ul id="storehouseDropdown" class="collapse list-unstyled ">
                     <li><a href="{{ route('storehouse_records.record') }}">store house record</a></li>
                     <li><a href="{{ route('storehouse_records.index') }}">View store house </a></li>
                     <li><a href="{{ route('inventory_movements.index') }}">View inventory movement</a></li>
                 </ul>
             </li>

             <li><a href="#pigsaleMenuDropdown" aria-expanded="false" data-toggle="collapse"> <i
                         class="bi bi-cash-stack"></i>Add Pig Sale </a>
                 <ul id="pigsaleMenuDropdown" class="collapse list-unstyled ">
                     <li><a href="{{ route('pig_sales.index') }}">View Pig Sale</a></li>
                 </ul>
             </li>
             <li><a href="#RequestsApprovalDropdown" aria-expanded="false" data-toggle="collapse"> <i
                         class="bi-check2-square"></i> การอนุมัติคำขอ </a>
                 <ul id="RequestsApprovalDropdown" class="collapse list-unstyled">
                     <li><a href="{{ route('payment_approvals.index') }}"><i class="bi bi-clipboard-check"></i>
                         อนุมัติการชำระเงิน</a></li>

                     <li><a href="{{ route('user_management.index') }}"><i class="bi bi-clipboard-check"></i>
                         จัดการผู้ใช้</a></li>

                     <li><a href="{{ route('cost_payment_approvals.index') }}"><i
                         class="bi bi-clipboard-check"></i>Cost Payment Approvals</a></li>
                 </ul>
             </li>
             <li><a href="#farmMenuDropdown" aria-expanded="false" data-toggle="collapse"> <i
                         class="bi bi-tree"></i>Farm / Barn / Pen </a>
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
