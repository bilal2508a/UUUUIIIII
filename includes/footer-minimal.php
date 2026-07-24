<?php
// Mehmaan Hub - Minimal Footer for Auth Pages (no footer)
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo url('/assets/js/main.js'); ?>"></script>
<script>
if (document.getElementById('flashAlert')) {
    setTimeout(function() {
        var el = document.getElementById('flashAlert');
        el.style.transition = 'opacity 0.3s';
        el.style.opacity = '0';
        setTimeout(function() { el.remove(); }, 300);
    }, 4000);
}
</script>
</body>
</html>
