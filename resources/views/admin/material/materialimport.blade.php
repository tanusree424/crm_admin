@extends('layouts.adminmaster')

@section('styles')
    <!-- INTERNAL Tag css -->
    <link href="{{ asset('assets/plugins/taginput/bootstrap-tagsinput.css') }}?v={{ time() }}" rel="stylesheet" />
@endsection

@section('content')
    <!-- Page header -->
    <div class="page-header d-xl-flex d-block">
        <div class="page-leftheader">
            <h4 class="page-title"><span class="font-weight-normal text-muted ms-2">{{ lang('Import Materials') }}</span></h4>
        </div>
    </div>
    <!-- End Page header -->

    <!-- Import Materials -->
    <div class="col-xl-12 col-lg-12 col-md-12">
        <div class="card">
            <div class="card-header border-0">
                <h4 class="card-title">{{ lang('Import Materials from CSV') }}</h4>
            </div>
            <form method="POST" action="{{ route('material.import') }}" enctype="multipart/form-data">
                @csrf

                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">{{ lang('Upload CSV File') }} <span class="text-red">*</span></label>
                        <input type="file" class="form-control @error('csv') is-invalid @enderror" name="csv" accept=".csv">
                        @error('csv')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ lang($message) }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-12 card-footer">
                    <div class="form-group float-end">
                        <input type="submit" class="btn btn-secondary" value="{{ lang('Import') }}">
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- Import Materials -->
@endsection

@section('scripts')
    <!-- File Browser -->
    <script src="{{ asset('assets/js/form-browser.js') }}?v={{ time() }}"></script>
@endsection
