											<!--Vertical Menu-->
											<div class="col-xl-3">
												<div class="card">
													<div class="card-body text-center item-user">
														<div class="profile-pic">
															<div class="profile-pic-img">
																<span class="bg-success dots" data-bs-toggle="tooltip" data-placement="top" title=""
																	data-bs-original-title="{{lang('Online')}}"></span>
																@if (Auth::guard('customer')->user()->image == null)

																<img src="{{asset('uploads/profile/user-profile.png')}}" class="brround avatar-xxl" alt="default">
																@else

																<img src="{{asset('uploads/profile/'.Auth::guard('customer')->user()->image)}}" class="brround avatar-xxl"
																	alt="{{Auth::guard('customer')->user()->image}}">
																@endif

															</div>
															<a href="#" class="text-dark">
																<h5 class="mt-3 mb-1 font-weight-semibold2">{{Auth::guard('customer')->user()->username}}</h5>

															</a>
															<small class="text-muted ">{{Auth::guard('customer')->user()->email}}</small>
														</div>
													</div>
													<div class="support-sidebar">
														<ul class="side-menu custom-ul">
															<li>
																<a class="side-menu__item" href="{{route('client.dashboard')}}">
																		<svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" enable-background="new 0 0 24 24" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><g><rect fill="none" height="24" width="24"/></g><g><g><g><path d="M3,3v8h8V3H3z M9,9H5V5h4V9z M3,13v8h8v-8H3z M9,19H5v-4h4V19z M13,3v8h8V3H13z M19,9h-4V5h4V9z M13,13v8h8v-8H13z M19,19h-4v-4h4V19z"/></g></g></g></svg><span class="side-menu__label">{{lang('Dashboard', 'Menu')}}</span></a>
															</li>
															<li>
																<a class="side-menu__item" href="{{route('client.profile')}}">
																		<svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" enable-background="new 0 0 24 24" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><g><path d="M0,0h24v24H0V0z" fill="none"/></g><g><g><path d="M4,18v-0.65c0-0.34,0.16-0.66,0.41-0.81C6.1,15.53,8.03,15,10,15c0.03,0,0.05,0,0.08,0.01c0.1-0.7,0.3-1.37,0.59-1.98 C10.45,13.01,10.23,13,10,13c-2.42,0-4.68,0.67-6.61,1.82C2.51,15.34,2,16.32,2,17.35V20h9.26c-0.42-0.6-0.75-1.28-0.97-2H4z"/><path d="M10,12c2.21,0,4-1.79,4-4s-1.79-4-4-4C7.79,4,6,5.79,6,8S7.79,12,10,12z M10,6c1.1,0,2,0.9,2,2s-0.9,2-2,2 c-1.1,0-2-0.9-2-2S8.9,6,10,6z"/><path d="M20.75,16c0-0.22-0.03-0.42-0.06-0.63l1.14-1.01l-1-1.73l-1.45,0.49c-0.32-0.27-0.68-0.48-1.08-0.63L18,11h-2l-0.3,1.49 c-0.4,0.15-0.76,0.36-1.08,0.63l-1.45-0.49l-1,1.73l1.14,1.01c-0.03,0.21-0.06,0.41-0.06,0.63s0.03,0.42,0.06,0.63l-1.14,1.01 l1,1.73l1.45-0.49c0.32,0.27,0.68,0.48,1.08,0.63L16,21h2l0.3-1.49c0.4-0.15,0.76-0.36,1.08-0.63l1.45,0.49l1-1.73l-1.14-1.01 C20.72,16.42,20.75,16.22,20.75,16z M17,18c-1.1,0-2-0.9-2-2s0.9-2,2-2s2,0.9,2,2S18.1,18,17,18z"/></g></g></svg><span class="side-menu__label">{{lang('Edit Profile', 'Menu')}}</span></a>
															</li>
															@if(setting('CUSTOMER_TICKET') == 'no')
															<li>
																<a class="side-menu__item" href="{{route('client.ticket')}}">
																	<svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" enable-background="new 0 0 24 24" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><g><rect fill="none" height="24" width="24"/></g><g><g/><g><path d="M17,19.22H5V7h7V5H5C3.9,5,3,5.9,3,7v12c0,1.1,0.9,2,2,2h12c1.1,0,2-0.9,2-2v-7h-2V19.22z"/><path d="M19,2h-2v3h-3c0.01,0.01,0,2,0,2h3v2.99c0.01,0.01,2,0,2,0V7h3V5h-3V2z"/><rect height="2" width="8" x="7" y="9"/><polygon points="7,12 7,14 15,14 15,12 12,12"/><rect height="2" width="8" x="7" y="15"/></g></g></svg>
																	<span class="side-menu__label">{{lang('Create Ticket', 'Menu')}}</span>
																</a>
															</li>
															@endif
															<li>
																<a class="side-menu__item" href="{{route('activeticket')}}">
																		<svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M22 10V6c0-1.1-.9-2-2-2H4c-1.1 0-1.99.9-1.99 2v4c1.1 0 1.99.9 1.99 2s-.89 2-2 2v4c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2v-4c-1.1 0-2-.9-2-2s.9-2 2-2zm-2-1.46c-1.19.69-2 1.99-2 3.46s.81 2.77 2 3.46V18H4v-2.54c1.19-.69 2-1.99 2-3.46 0-1.48-.8-2.77-1.99-3.46L4 6h16v2.54zM9.07 16L12 14.12 14.93 16l-.89-3.36 2.69-2.2-3.47-.21L12 7l-1.27 3.22-3.47.21 2.69 2.2z"/></svg><span class="side-menu__label">{{lang('Active Tickets', 'Menu')}}
																		</span></a>
															</li>
															<li>
																<a class="side-menu__item" href="{{route('closedticket')}}">
																		<svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" enable-background="new 0 0 24 24" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><rect fill="none" height="24" width="24"/><path d="M13,10c0-0.55,0.45-1,1-1h3c0.55,0,1,0.45,1,1v1h-1.5v-0.5h-2v1L13,10z M16.5,13.5l1.21,1.21C17.89,14.52,18,14.27,18,14v-1 h-1.5V13.5z M8.83,6H19v10.17l1.98,1.98c0-0.05,0.02-0.1,0.02-0.16V6c0-1.1-0.9-2-2-2H6.83L8.83,6z M19.78,22.61L17.17,20H5 c-1.11,0-2-0.9-2-2V6c0-0.05,0.02-0.1,0.02-0.15L1.39,4.22l1.41-1.41l18.38,18.38L19.78,22.61z M7.5,13.5h2V13h0.67l-2.5-2.5H7.5 V13.5z M15.17,18L11,13.83V14c0,0.55-0.45,1-1,1H7c-0.55,0-1-0.45-1-1v-4c0-0.32,0.16-0.59,0.4-0.78L5,7.83V18H15.17z"/></svg><span class="side-menu__label">{{lang('Closed Tickets', 'Menu')}}
																		</span></a>
															</li>
															<li>
																<a class="side-menu__item" href="{{route('onholdticket')}}">
																	<svg xmlns="http://www.w3.org/2000/svg"  class="side-menu__icon" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0z" fill="none"/><path d="M22 10V6c0-1.11-.9-2-2-2H4c-1.1 0-1.99.89-1.99 2v4c1.1 0 1.99.9 1.99 2s-.89 2-2 2v4c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2v-4c-1.1 0-2-.9-2-2s.9-2 2-2zm-2-1.46c-1.19.69-2 1.99-2 3.46s.81 2.77 2 3.46V18H4v-2.54c1.19-.69 2-1.99 2-3.46 0-1.48-.8-2.77-1.99-3.46L4 6h16v2.54zM11 15h2v2h-2zm0-4h2v2h-2zm0-4h2v2h-2z"/></svg><span class="side-menu__label">{{lang('On-Hold Tickets', 'Menu')}}
																		</span></a>
															</li>
                                                            <li>
																<a class="side-menu__item" href="{{route('ticket.report')}}">
																	<svg xmlns="http://www.w3.org/2000/svg"  class="side-menu__icon" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0z" fill="none"/><path d="M22 10V6c0-1.11-.9-2-2-2H4c-1.1 0-1.99.89-1.99 2v4c1.1 0 1.99.9 1.99 2s-.89 2-2 2v4c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2v-4c-1.1 0-2-.9-2-2s.9-2 2-2zm-2-1.46c-1.19.69-2 1.99-2 3.46s.81 2.77 2 3.46V18H4v-2.54c1.19-.69 2-1.99 2-3.46 0-1.48-.8-2.77-1.99-3.46L4 6h16v2.54zM11 15h2v2h-2zm0-4h2v2h-2zm0-4h2v2h-2z"/></svg><span class="side-menu__label">{{lang('Ticket Report', 'Menu')}}
																		</span></a>
															</li>
														</ul>
													</div>
												</div>
												<!-- Bussiness Hour -->
												@if(setting('businesshoursswitch') == 'on')
												<div class="card p-3 pricing-card border d-flex notify-days-toggle">
													<div class="d-md-flex d-block">
														<div  class="support-img1">
															@if(setting('supporticonimage') != null)

															<img src="{{asset('uploads/support/'. setting('supporticonimage'))}}" class="rounded-circle" alt="img" width="50" height="50">
															@else

															<img src="{{asset('assets/images/support/support.png')}}" alt="img" width="50" height="50">
															@endif

														</div>
														<div class="card-header text-justified flex-1 pt-0 ps-md-3 ps-0 pb-0 ">
															<p class="fs-18 font-weight-semibold mb-1">{{setting('businesshourstitle')}}
																@foreach(bussinesshour() as $bussiness)


                                                                    @if(now()->timezone(setting('default_timezone'))->format('D') == $bussiness->weeks)


                                                                        @if(strtotime($bussiness->starttime) <= strtotime(now()->timezone(setting('default_timezone'))->format('h:i A')) && strtotime($bussiness->endtime) >= strtotime(now()->timezone(setting('default_timezone'))->format('h:i A'))|| $bussiness->starttime == "24H")

                                                                            @if($bussiness->starttime != "24H")
                                                                                <span class="ms-3 badge bg-success text-white  mt-1 fs-12 font-weight-normal">{{lang('online')}}</span>
                                                                            @else
                                                                                <span class="ms-3 badge bg-success text-white  mt-1 fs-12 font-weight-normal">{{lang('online')}}</span>
                                                                            @endif
                                                                        @else
                                                                            @if($bussiness->starttime != "24H")

                                                                                <span class="ms-3 badge bg-danger text-white  mt-1 fs-12 font-weight-normal">{{lang('offline')}}</span>
                                                                            @else
                                                                                <span class="ms-3 badge bg-danger text-white  mt-1 fs-12 font-weight-normal">{{lang('offline')}}</span>
                                                                            @endif
                                                                        @endif

                                                                    @endif
																@endforeach

															</p>

															<p class="fs-13 mb-0 text-muted">{{setting('businesshourssubtitle')}}</p>
														</div>
														<div class="my-4 ms-auto">
															<span class="fe fe-chevron-down float-end notify-arrow"></span>
														</div>

													</div>
													<div class="card-body  pt-0 pb-0 px-4 notify-days-container">
														<ul class="custom-ul text-justify pricing-body text-muted ps-0 mb-4">
															@foreach(bussinesshour() as $bussiness)
															@if($bussiness->weeks != null)
															<li class="mb-2">
																<div class="row br-5 notify-days-cal align-items-center p-2 br-5 border text-center {{now()->timezone(setting('default_timezone'))->format('D') == $bussiness->weeks ? 'bg-success-transparent' : '' }}">
																	<div class="col-xxl-3 col-xl-3 col-sm-12 ps-0">

																		<span class="badge {{now()->timezone(setting('default_timezone'))->format('D') == $bussiness->weeks ? 'bg-success' : 'bg-info' }}   fs-13 font-weight-normal  w-100 ">{{lang($bussiness->weeks)}}</span>

																	</div>
																	<div class="col-xxl-3 col-xl-4 col-sm-12">
																		@if(now()->timezone(setting('default_timezone'))->format('D') == $bussiness->weeks)

																		<span class="{{$bussiness->status != 'Closed' ? 'text-success' : 'text-success' }} fs-12 ms-2">{{lang('Today')}}</span>
																		@endif
																	</div>
																	<div class="col-xxl-6 col-xl-5 col-sm-12 px-0">
																		@if($bussiness->status == "Closed")
																		<span class="text-danger fs-12 ms-2">{{lang($bussiness->status)}}</span>
																		@else
																		<span class="ms-0 fs-13">{{$bussiness->starttime}}
																		@if($bussiness->starttime !== null && $bussiness->endtime != null )
																		<span class="fs-10 mx-1">- </span>
																		@endif
																		</span>
																		@if($bussiness->starttime !== null && $bussiness->endtime )
																		<span class="ms-0">{{$bussiness->endtime}}</span>
																		@endif
																		@endif
																	</div>
																</div>
															</li>
															@endif
															@endforeach
														</ul>
													</div>
												</div>

												@endif
												<!-- End Bussiness Hour -->
											</div>
											<!--Vertical Menu-->


											<script type="text/javascript">
												'use strict';
												let notifyToggl = document.querySelector('.notify-days-toggle');
												let notifyCont = document.querySelector('.notify-days-container');
												if(notifyToggl){
													notifyToggl.addEventListener('click', ()=>{
														notifyCont.classList.toggle('show-days');
														notifyToggl.querySelector('.notify-arrow').classList.toggle('hide-container')
													})
												}
											</script>

