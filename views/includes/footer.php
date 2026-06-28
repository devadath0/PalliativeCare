    </div><!-- /.container -->

    <footer class="footer bg-light mt-5 py-3">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">
                        <i class="fas fa-heartbeat text-primary"></i> Palliative Care System &copy; <?php echo date('Y'); ?>
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">
                        <a href="index.php" class="text-decoration-none">
                            <i class="fas fa-home"></i> Home
                        </a>
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <span class="mx-2">|</span>
                        <a href="logout.php" class="text-decoration-none">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-hide alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                alert.classList.add('fade');
                setTimeout(function() {
                    alert.remove();
                }, 500);
            }, 5000);
        });
        
        // Enable dropdowns
        const dropdownElementList = document.querySelectorAll('.dropdown-toggle');
        dropdownElementList.forEach(function(dropdownToggleEl) {
            new bootstrap.Dropdown(dropdownToggleEl);
        });
    });
    </script>
</body>
</html>
