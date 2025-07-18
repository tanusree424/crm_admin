@extends('layouts.adminmaster')

@section('styles')
    <!-- INTERNAL Data table css -->
    <link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}?v={{ time() }}"
        rel="stylesheet" />
    <link href="{{ asset('assets/plugins/datatable/responsive.bootstrap5.css') }}?v={{ time() }}" rel="stylesheet" />

    <!-- INTERNAL Sweet-Alert css -->
    <link href="{{ asset('assets/plugins/sweet-alert/sweetalert.css') }}?v={{ time() }}" rel="stylesheet" />

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('content')
    <!--Page header-->
    <div class="page-header d-xl-flex d-block">
        <div class="page-leftheader">
            <h4 class="page-title"><span class="font-weight-normal text-muted ms-2">{{ lang('Mapping', 'menu') }}</span>
            </h4>
        </div>
    </div>
    <!--End Page header-->

    <div class="col-xl-12 col-lg-12 col-md-12">
        <div class="card">
            <div class="card-header border-0 d-sm-max-flex">
                <h4 class="card-title">{{ lang('Mapping', 'menu') }}</h4>
                <div class="card-options mt-sm-max-2">
                    <a href="{{ route('admin.mapping.index') }}" class="btn btn-secondary me-3"
                        id="create-new-department">{{ lang('Back') }}</a>
                </div>
            </div>

            <div class="card-body">
                <form action="{{ route('admin.mapping.store') }}" method="POST">
    @csrf

    <div class="mb-3">
        <label for="empid" class="form-label">Employee</label>
        <select name="empid" id="empid" class="form-control select2" required>
            <option value="">Select Employee</option>
            @foreach ($user as $u)
                <option value="{{ $u->id }}">{{ $u->name }} (ID: {{ $u->empid }})</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label for="modules" class="form-label">Modules (Departments)</label>
        <select name="modules" id="modules" class="form-control select2" required>
            <option value="">Select Module</option>
            @foreach ($departments as $department)
                <option value="{{ $department->id }}">{{ $department->departmentname }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label for="customer" class="form-label">Customers</label>
        <select name="customer" id="customer" class="form-control select2" required>
            <option value="">Select Customer</option>
            @foreach ($customers as $customer)
                <option value="{{ $customer->id }}">{{ $customer->firstname }} {{ $customer->lastname }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" id="status" name="status" checked>
        <label class="form-check-label" for="status">Active</label>
    </div>

    <button type="submit" class="btn btn-primary">Save Mapping</button>
</form>

            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "Search and select",
                allowClear: true
            });
        });
    </script>
@endsection
