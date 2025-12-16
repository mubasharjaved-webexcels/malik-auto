<footer class="footer">
  <div class="d-sm-flex justify-content-center justify-content-sm-between">
    <span class="float-none float-sm-end d-block mt-1 mt-sm-0 text-center">
      Copyright © 2025. All rights reserved.
    </span>
  </div>
</footer>

<!-- Scripts -->
<!-- jQuery (required by DataTables) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('admin-assets/vendors/js/vendor.bundle.base.js') }}"></script>
<script src="{{ asset('admin-assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('admin-assets/vendors/chart.js/chart.umd.js') }}"></script>
<script src="{{ asset('admin-assets/vendors/progressbar.js/progressbar.min.js') }}"></script>
<script src="{{ asset('admin-assets/js/off-canvas.js') }}"></script>
<script src="{{ asset('admin-assets/js/template.js') }}"></script>
<script src="{{ asset('admin-assets/js/settings.js') }}"></script>
<script src="{{ asset('admin-assets/js/hoverable-collapse.js') }}"></script>
<script src="{{ asset('admin-assets/js/todolist.js') }}"></script>
<script src="{{ asset('admin-assets/js/jquery.cookie.js') }}"></script>
<script src="{{ asset('admin-assets/js/dashboard.js') }}"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
@stack('scripts')
</body>
</html>
