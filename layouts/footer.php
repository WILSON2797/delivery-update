<?php
if (!isset($version)) {
    $version = time();
}
?>
                <footer class="footer-admin mt-auto footer-light">
                    <div class="container-xl px-4">
                        <div class="row">
                            <div class="col-md-6 small">Copyright &copy; FIS-APPS <?php echo date('Y'); ?></div>
                            <div class="col-md-6 text-md-end small">
                                <a href="#!">Privacy Policy</a>
                                &middot;
                                <a href="#!">Terms &amp; Conditions</a>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>

    <!-- ============================================ -->
    <!-- JS LIBRARIES -->
    <!-- ============================================ -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js?v=<?php echo $version; ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js?v=<?php echo $version; ?>"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js?v=<?php echo $version; ?>"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js?v=<?php echo $version; ?>"></script>
    <script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js?v=<?php echo $version; ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11?v=<?php echo $version; ?>"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js?v=<?php echo $version; ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js?v=<?php echo $version; ?>"></script>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    
    <script src="js/scripts.js?v=<?php echo $version; ?>"></script>

    <!-- Custom Scripts -->
    <script src="assets/js/script.js?v=<?php echo $version; ?>"></script>
    <script src="assets/js/spinner.js?v=<?php echo $version; ?>"></script>
    <script src="assets/js/toastr-init.js?v=<?php echo $version; ?>"></script>


    <!-- LOADING OVERLAY -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="container-spinner">
            <div class="spinner">
                <div class="grok-spinner">
                    <div class="grok-dot"></div>
                    <div class="grok-dot"></div>
                    <div class="grok-dot"></div>
                    <div class="grok-dot"></div>
                </div>
            </div>
            <div class="text" id="loading-text">Loading...</div>
            <p class="sub-text">Please wait while we fetch your data.</p>
        </div>
    </div>


</body>
</html>