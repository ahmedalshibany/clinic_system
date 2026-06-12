<script>
document.addEventListener('DOMContentLoaded', function() {
    // Immediate Synchronous Purge: Vaporize any historical or stale toast HTML nodes before creating new ones
    const activeContainer = document.querySelector('.toast-container');
    if (activeContainer) {
        activeContainer.innerHTML = '';
    }

    // 50ms micro-timeout to guarantee app.js toast object binding parity
    setTimeout(function() {
        if (!window.toast) return;

        @if(session('success'))
            window.toast.success({!! json_encode(session('success')) !!});
        @endif

        @if(session('warnings') && is_array(session('warnings')))
            @foreach(session('warnings') as $warning)
                window.toast.warning({!! json_encode($warning) !!});
            @endforeach
        @endif

        @if(session('error'))
            window.toast.error({!! json_encode(session('error')) !!});
        @endif

        @if(session('info'))
            window.toast.info({!! json_encode(session('info')) !!});
        @endif

        @if($errors->any())
            @foreach($errors->all() as $error)
                window.toast.error({!! json_encode($error) !!});
            @endforeach
        @endif
    }, 50);
});
</script>
