@extends('layouts.adminmaster')

@section('styles')
    <link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}?v={{ time() }}"
        rel="stylesheet" />
    <link href="{{ asset('assets/plugins/datatable/responsive.bootstrap5.css') }}?v={{ time() }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/sweet-alert/sweetalert.css') }}?v={{ time() }}" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection
@section('content')
  <div class="page-header d-xl-flex d-block">
                                <div class="page-leftheader">
                                    <h4 class="page-title"><span class="font-weight-normal text-muted ms-2">{{lang('User Import')}}</span></h4>
                                </div>
                            </div>
                            <!--End Page header-->

                            <!-- Employee Import-->
                            <div class="col-xl-12 col-lg-12 col-md-12">
                                <div class="card ">

                                    @if (isset($errors) & $errors->any())

                                        <div class="alert alert-danger">
                                            @foreach ($errors->all() as $item)

                                                {{$item}}
                                            @endforeach

                                        </div>
                                    @endif

                                    <div class="card-header border-0">
                                        <h4 class="card-title">{{lang('Import file')}}</h4>
                                    </div>
                                    <form method="POST" action="{{route('mapping.import.excel')}}" enctype="multipart/form-data">
                                        @csrf

                                        @honeypot
                                        <div class="card-body" >
                                            <div class="row">
                                                <div class="form-group">
                                                    <label class="form-label">{{lang('Upload File', 'filesetting')}}</label>
                                                    <div class="input-group file-browser">
                                                        <input class="form-control" name="file" type="file">
                                                    </div>
                                                    <small class="text-muted"><i>{{lang('File Format: .xlsx & .csv', 'filesetting')}}</i></small>
                                                    <p>{{lang('Download', 'filesetting')}} <a href="{{asset('download/mapping.csv')}}" class="text-primary" download>{{lang('Sample File')}}</a></p>
                                                    <span id="nameError" class="text-danger alert-message"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <button type="submit" class="btn btn-secondary float-end mb-2" >{{lang('Import Data', 'filesetting')}}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <!-- End Employee Import-->
                            @endsection

