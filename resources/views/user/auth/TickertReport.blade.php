@extends('layouts.usermaster')

@section('styles')
<!-- INTERNAl Summernote css -->
<link rel="stylesheet" href="{{asset('assets/plugins/summernote/summernote.css')}}?v=<?php echo time(); ?>">

<!-- INTERNAl DropZone css -->
<link href="{{asset('assets/plugins/dropzone/dropzone.css')}}?v=<?php echo time(); ?>" rel="stylesheet" />

<link href="{{asset('assets/plugins/wowmaster/css/animate.css')}}?v=<?php echo time(); ?>" rel="stylesheet" />
@endsection

@section('content')

<!-- Section -->
<section>
    <div class="bannerimg cover-image" data-bs-image-src="{{asset('assets/images/photos/banner1.jpg')}}">
        <div class="header-text mb-0">
            <div class="container ">
                <div class="row text-white">
                    <div class="col">
                        <h1 class="mb-0">{{lang('Report Ticket', 'menu')}}</h1>
                    </div>
                    <div class="col col-auto">
                        <ol class="breadcrumb text-center">
                            <li class="breadcrumb-item">
                                <a href="{{url('/')}}" class="text-white-50">{{lang('Home', 'menu')}}</a>
                            </li>
                            <li class="breadcrumb-item active">
                                <a href="#" class="text-white">{{lang('Create Ticket', 'menu')}}</a>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Section -->

<!--Section-->
<section>
    <div class="cover-image sptb">
        <div class="container ">
            <div class="row">
                @include('includes.user.verticalmenu')

                <div class="col-xl-9">
                    <div class="row">
                        <!-- Ticket Status Chart -->
                        <div class="col-md-6">
                            <div class="card shadow-sm mb-4">
                                <div class="card-header">
                                    <h4 class="card-title">Your Ticket Status Report</h4>
                                </div>
                                <div class="mb-4">
    @foreach($ticketCategories as $category)
    <a class="btn btn-light btn-sm" href="{{ route('ticket.export.category', ['download' => 1, 'category_id' => $category->id]) }}">
        <i class="fe fe-download pe-lg-2" style="font-size:10px;"></i>{{ $category->name }}
    </a>
@endforeach

</div>

                                <div class="card-body">
                                    <div id="ticketStatusChart"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Ticket Priority Chart -->
                        <div class="col-md-6">
                            <div class="card shadow-sm mb-4">
                                <div class="card-header">
                                    <h4 class="card-title">Your Ticket Priority Report</h4>
                                </div>
                                <div class="card-body">
                                    <div id="ticketPriorityChart"></div>
                                </div>
                            </div>
                        </div>
                    </div> <!-- end row -->
                </div> <!-- end col-xl-9 -->

            </div> <!-- end row -->
        </div>
    </div>
</section>
<!--Section-->

@endsection

@section('scripts')
<!-- INTERNAL Vertical-scroll js-->
<script src="{{asset('assets/plugins/vertical-scroll/jquery.bootstrap.newsbox.js')}}?v=<?php echo time(); ?>"></script>

<!-- INTERNAL Summernote js  -->
<script src="{{asset('assets/plugins/summernote/summernote.js')}}?v=<?php echo time(); ?>"></script>

<!-- INTERNAL Index js-->
<script src="{{asset('assets/js/support/support-sidemenu.js')}}?v=<?php echo time(); ?>"></script>
<script src="{{asset('assets/js/select2.js')}}?v=<?php echo time(); ?>"></script>

<!-- INTERNAL Dropzone js-->
<script src="{{asset('assets/plugins/dropzone/dropzone.js')}}?v=<?php echo time(); ?>"></script>

<!-- wowmaster js-->
<script src="{{asset('assets/plugins/wowmaster/js/wow.min.js')}}?v=<?php echo time(); ?>"></script>

<!-- INTERNAL Bootstrap-MaxLength js-->
<script src="{{asset('assets/plugins/bootstrapmaxlength/bootstrap-maxlength.min.js')}}?v=<?php echo time(); ?>"></script>

<!-- ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Ticket Status Donut Chart
        new ApexCharts(document.querySelector("#ticketStatusChart"), {
            chart: {
                type: 'donut',
                height: 360
            },
            labels: ['New', 'Inprogress', 'On-Hold', 'Re-Open', 'Closed'],
            series: [
                {{ $statusData['New'] ?? 0 }},
                {{ $statusData['Inprogress'] ?? 0 }},
                {{ $statusData['On-Hold'] ?? 0 }},
                {{ $statusData['Re-Open'] ?? 0 }},
                {{ $statusData['Closed'] ?? 0 }}
            ],
            colors: ['#fb8c00', '#1e88e5', '#fbc02d', '#00acc1', '#e53935'],
            legend: {
                position: 'bottom',
                fontSize: '14px'
            },
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    return val.toFixed(1) + "%";
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val + " Tickets";
                    }
                }
            }
        }).render();

        // Ticket Priority Donut Chart
        new ApexCharts(document.querySelector("#ticketPriorityChart"), {
            chart: {
                type: 'donut',
                height: 360
            },
            labels: ['Low', 'Medium', 'High', 'Critical'],
            series: [
                {{ $priorityData['Low'] ?? 0 }},
                {{ $priorityData['Medium'] ?? 0 }},
                {{ $priorityData['High'] ?? 0 }},
                {{ $priorityData['Critical'] ?? 0 }}
            ],
            colors: ['#42a5f5', '#66bb6a', '#ef5350', '#8e24aa'],
            legend: {
                position: 'bottom',
                fontSize: '14px'
            },
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    return val.toFixed(1) + "%";
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val + " Tickets";
                    }
                }
            }
        }).render();
    });
</script>
@endsection
