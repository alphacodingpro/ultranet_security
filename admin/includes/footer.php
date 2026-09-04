  </div><!-- /.admin-content -->
</div><!-- /.admin-main -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Sidebar toggle
document.getElementById('sidebarToggle').addEventListener('click', function () {
  document.getElementById('adminSidebar').classList.toggle('open');
  document.getElementById('adminMain').classList.toggle('sidebar-open');
});

// Auto-hide flash alerts
setTimeout(function () {
  document.querySelectorAll('.alert-dismissible').forEach(function (el) {
    el.classList.remove('show');
  });
}, 4000);
</script>
</body>
</html>
