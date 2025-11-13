    </div>

    <footer class="bg-light text-center text-lg-start mt-5">
        <div class="text-center p-3">
            <small class="text-muted">
                © <?php echo date('Y'); ?> TestItems Module 
                - Enhanced upMVC v2.0
                <?php if ($debug_mode ?? false): ?>
                - <span class="badge bg-warning text-dark">Debug Mode</span>
                <?php endif; ?>
            </small>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Enhanced Module JS -->
    <script src="<?php echo BASE_URL ?? ''; ?>/modules/TestItems/assets/js/script.js"></script>
    
    <?php if ($debug_mode ?? false): ?>
    <script>
        console.log('Enhanced TestItems Module Debug Mode Active');
        console.log('Environment:', '<?php echo $app_env; ?>');
        console.log('Auto-discovery: Enabled via InitModsImproved.php');
    </script>
    <?php endif; ?>
</body>
</html>










