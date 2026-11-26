
    </main>

    <footer class="container">
        <?php $dt = new DateTime('now', new DateTimeZone('America/Sao_Paulo')); ?>
        <p>&copy;2026 à <?= $dt->format('Y') ?> 🎀 Julia Coelho e Mirella Costa.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <script src="<?php echo BASEURL; ?>js/functions.js?v=20261125-2"></script>

</body>
</html>
