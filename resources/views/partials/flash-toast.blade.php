<script>
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        if (!window.toast) {
            console.error('Da Vinci System Error: window.toast object is missing or not initialized in app.js.');
            return;
        }

        @if(session('success'))
            window.toast.success({!! json_encode(session('success')) !!});
        @endif

        @if(session('error'))
            window.toast.error({!! json_encode(session('error')) !!});
        @endif

        @if(session('warning'))
            window.toast.warning({!! json_encode(session('warning')) !!});
        @endif

        @if(session('info'))
            window.toast.info({!! json_encode(session('info')) !!});
        @endif

        @if($errors->any())
            window.toast.error({!! json_encode($errors->first()) !!});
        @endif
    }, 50);
});
</script>
