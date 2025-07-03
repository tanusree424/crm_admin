@extends('layouts.adminmaster')

@section('styles')
    <!-- INTERNAL Tag css -->
    <link href="{{ asset('assets/plugins/taginput/bootstrap-tagsinput.css') }}?v={{ time() }}" rel="stylesheet" />
@endsection

@section('content')
    <!-- Page header -->
    <div class="page-header d-xl-flex d-block">
        <div class="page-leftheader">
            <h4 class="page-title"><span class="font-weight-normal text-muted ms-2">{{ lang('Material') }}</span></h4>
        </div>
    </div>
    <!-- End Page header -->

    <!-- Create Material -->
    <div class="col-xl-12 col-lg-12 col-md-12">
        <div class="card">
            <div class="card-header border-0">
                <h4 class="card-title">{{ lang('Create Material') }}</h4>
            </div>
            <form method="POST" action="{{ url('/admin/material/create') }}" enctype="multipart/form-data">
                <div class="card-body">
                    @csrf

                    @honeypot
                    <div class="row">
                        <div class="col-sm-6 col-md-3">
                            <div class="form-group">
                                <label class="form-label">{{ lang('Material Code') }} <span
                                        class="text-red">*</span></label>
                                <input type="text" class="form-control @error('material_code') is-invalid @enderror"
                                    name="material_code" value="{{ old('material_code') }}">
                                @error('material_code')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ lang($message) }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="form-group">
                                <label class="form-label">{{ lang('Material Name') }} <span
                                        class="text-red">*</span></label>
                                <input type="text" class="form-control @error('material_name') is-invalid @enderror"
                                    name="material_name" value="{{ old('material_name') }}">
                                @error('material_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ lang($message) }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="form-group">
                                <label class="form-label">{{ lang('Description') }}</label>
                                <textarea class="form-control @error('material_description') is-invalid @enderror" name="material_description">{{ old('material_description') }}</textarea>
                                @error('material_description')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ lang($message) }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="form-group">
                                <label class="form-label">{{ lang('Product Brand') }}</label>
                                <select class="form-control select2 @error('material_group_code1') is-invalid @enderror"
                                    name="material_group_code1">
                                    <option value="" disabled selected>{{ lang('Select Material Group Code 1') }}
                                    </option>
                                    @foreach ($materialGroups1 as $mat )
                                    <option value="{{ $mat->id }}"  {{ old('material_group_code1') == 'code1' ? 'selected' : '' }}>{{ $mat->name }}-{{ $mat->description}} </option>
                                    @endforeach
                                  
                                </select>
                                @error('material_group_code1')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ lang($message) }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="form-group">
                                <label class="form-label">{{ lang('Product Type') }}</label>
                                <select class="form-control select2 @error('material_group_code2') is-invalid @enderror"
                                    name="material_group_code2">
                                    <option value="" disabled selected>{{ lang('Select Material Group Code 2') }}
                                    </option>
                                    @foreach ($materialGroups2 as $mat )
                                    <option value="{{ $mat->id }}"  {{ old('material_group_code2') == 'code1' ? 'selected' : '' }}>{{ $mat->name }}-{{ $mat->description}} </option>
                                    @endforeach
                                  
                                </select>
                                @error('material_group_code2')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ lang($message) }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="form-group">
                                <label class="form-label">{{ lang('Material Group Code 3') }}</label>
                                <select class="form-control select2 @error('material_group_code3') is-invalid @enderror"
                                    name="material_group_code3">
                                    <option value="" disabled selected>{{ lang('Select Material Group Code 3') }}
                                    </option>
                                 @foreach ($materialGroups3 as $mat )
                                 <option value="{{ $mat->id }}"  {{ old('material_group_code3') == 'code1' ? 'selected' : '' }}>{{ $mat->name }}-{{ $mat->description}} </option>
                                 @endforeach
                                   
                                    <!-- Add more options as needed -->
                                </select>
                                @error('material_group_code3')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ lang($message) }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="form-group">
                                <label class="form-label">{{ lang('MRP') }}</label>
                                <input type="text" class="form-control @error('mrp') is-invalid @enderror" name="mrp"
                                    value="{{ old('mrp') }}">
                                @error('mrp')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ lang($message) }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="form-group">
                                <label class="form-label">{{ lang('Division Code') }}</label>
                                <input type="text" class="form-control @error('division_code') is-invalid @enderror"
                                    name="division_code" value="{{ old('division_code') }}">
                                @error('division_code')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ lang($message) }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="form-group">
                                <label class="form-label">{{ lang('Serialized') }}</label>
                                <select class="form-select @error('isserialized') is-invalid @enderror" name="isserialized">
                                    <option value="1" {{ old('isserialized') == '1' ? 'selected' : '' }}>
                                        {{ lang('Yes') }}</option>
                                    <option value="0" {{ old('isserialized') == '0' ? 'selected' : '' }}>
                                        {{ lang('No') }}</option>
                                </select>
                                @error('isserialized')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ lang($message) }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="form-group">
                                <label class="form-label">{{ lang('Repairable') }}</label>
                                <select class="form-select @error('isrepairable') is-invalid @enderror"
                                    name="isrepairable">
                                    <option value="1" {{ old('isrepairable') == '1' ? 'selected' : '' }}>
                                        {{ lang('Yes') }}</option>
                                    <option value="0" {{ old('isrepairable') == '0' ? 'selected' : '' }}>
                                        {{ lang('No') }}</option>
                                </select>
                                @error('isrepairable')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ lang($message) }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="form-group">
                                <label class="form-label">{{ lang('On-Site Allowed') }}</label>
                                <select class="form-select @error('isonsiteallowed') is-invalid @enderror"
                                    name="isonsiteallowed">
                                    <option value="1" {{ old('isonsiteallowed') == '1' ? 'selected' : '' }}>
                                        {{ lang('Yes') }}</option>
                                    <option value="0" {{ old('isonsiteallowed') == '0' ? 'selected' : '' }}>
                                        {{ lang('No') }}</option>
                                </select>
                                @error('isonsiteallowed')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ lang($message) }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="form-group">
                                <label class="form-label">{{ lang('is Active') }}</label>
                                <select class="form-select @error('is_active') is-invalid @enderror" name="is_active">
                                    <option value="1" {{ old('is_active') == '1' ? 'selected' : '' }}>
                                        {{ lang('Yes') }}</option>
                                    <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>
                                        {{ lang('No') }}</option>
                                </select>
                                @error('is_active')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ lang($message) }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-sm-6 col-md-3">
                            <div class="form-group">
                                <label class="form-label">{{ lang('Warranty Years') }}</label>
                                <input type="number" class="form-control @error('warranty_years') is-invalid @enderror"
                                    id="warranty_years" name="warranty_years" onblur="convertYearsToDays()" value="{{ old('warranty_years') }}">
                                @error('warranty_years')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ lang($message) }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-sm-6 col-md-3">
                            <div class="form-group">
                                <label class="form-label">{{ lang('Warranty Days') }}</label>
                                <input type="number" class="form-control @error('warrant_days') is-invalid @enderror"
                                    name="warrant_days"id="warranty_days" value="{{ old('warrant_days') }}">
                                @error('warrant_days')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ lang($message) }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-sm-6 col-md-3">
                            <div class="form-group">
                                <label class="form-label">{{ lang('Number of Repairs') }}</label>
                                <input type="number" class="form-control @error('numberofrepair') is-invalid @enderror"
                                    name="numberofrepair" value="{{ old('numberofrepair') }}">
                                @error('numberofrepair')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ lang($message) }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-sm-6 col-md-3">
                            <div class="form-group">
                                <label class="form-label">{{ lang('Is Service Charge Applicable') }}</label>
                                <select class="form-select @error('is_servicecharge_applicable') is-invalid @enderror"
                                    name="is_servicecharge_applicable">
                                    <option value="1"
                                        {{ old('is_servicecharge_applicable') == '1' ? 'selected' : '' }}>
                                        {{ lang('Yes') }}</option>
                                    <option value="0"
                                        {{ old('is_servicecharge_applicable') == '0' ? 'selected' : '' }}>
                                        {{ lang('No') }}</option>
                                </select>
                                @error('is_servicecharge_applicable')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ lang($message) }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                    </div>
                </div>
                <div class="col-md-12 card-footer">
                    <div class="form-group float-end">
                        <input type="submit" class="btn btn-secondary" value="{{ lang('Create Material') }}"
                            onclick="this.disabled=true;this.form.submit();">
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- Create Material -->
@endsection

<script>
    function convertYearsToDays() {
        const yearsInput = document.getElementById('warranty_years');
        const daysInput = document.getElementById('warranty_days');

        // Ensure the value is a positive number
        const years = parseFloat(yearsInput.value);
        if (!isNaN(years) && years > 0) {
            const days = Math.round(years * 365.25); // accounting for leap years
            daysInput.value = days;
        } else {
            daysInput.value = '';
        }
    }
</script>
@section('scripts')
    <!-- File Browser -->
    <script src="{{ asset('assets/js/form-browser.js') }}?v={{ time() }}"></script>
    <!-- INTERNAL Vertical-scroll js -->
    <script src="{{ asset('assets/plugins/vertical-scroll/jquery.bootstrap.newsbox.js') }}?v={{ time() }}">
    </script>
    <script src="{{ asset('assets/js/select2.js') }}?v={{ time() }}"></script>
    <!-- INTERNAL Index js -->
    <script src="{{ asset('assets/js/support/support-sidemenu.js') }}?v={{ time() }}"></script>
    <!-- INTERNAL TAG js -->
    <script src="{{ asset('assets/plugins/taginput/bootstrap-tagsinput.js') }}?v={{ time() }}"></script>
@endsection
