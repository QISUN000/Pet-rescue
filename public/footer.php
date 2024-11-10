<footer class="py-3">
        <div class="container">
            <div class="row align-items-center">
                <div class="col">
                    <p class="mb-0">Copyright 2024 Animal Shelte</p>
                </div>
                <div class="col text-end">
                    <a href="#" class="text-dark me-3"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="text-dark me-3"><i class="bi bi-twitter"></i></a>
                    <a href="#" class="text-dark me-3"><i class="bi bi-pinterest"></i></a>
                    <a href="#" class="text-dark"><i class="bi bi-instagram"></i></a>
                </div>
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Close menu when clicking nav-link on mobile
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                const navbarCollapse = document.querySelector('.navbar-collapse');
                if (navbarCollapse.classList.contains('show')) {
                    navbarCollapse.classList.remove('show');
                }
            });
        });
    </script>
</body>
</html>