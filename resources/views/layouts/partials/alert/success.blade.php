@if (Session::has('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{Session::get('success')}}
<button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
@endif

<!-- <script>
    // Hide the success alert after 5 seconds
    $(document).ready(function() {
        setTimeout(function() {
            $("#success-alert").alert('close');
        }, 10000); // 5000 milliseconds = 5 seconds
    });
</script> -->
