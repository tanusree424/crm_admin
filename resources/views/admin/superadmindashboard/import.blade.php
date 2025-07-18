<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" >
	<head>
    	@include('includes.admin.styles')

		@if(setting('GOOGLE_ANALYTICS_ENABLE') == 'yes')
		<!-- Global site tag (gtag.js) - Google Analytics -->
		<script async src="https://www.googletagmanager.com/gtag/js?id={{setting('GOOGLE_ANALYTICS')}}"></script>
		<script>
			window.dataLayer = window.dataLayer || [];
			function gtag(){dataLayer.push(arguments);}
			gtag('js', new Date());

			gtag('config', '{{setting('GOOGLE_ANALYTICS')}}');
		</script>
		@endif

	</head>

</html>
