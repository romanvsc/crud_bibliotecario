    </main>
    <footer class="footer">
        <div class="footer-container">
            <p>&copy; <?php echo date('Y'); ?> Sistema de Gestión de Biblioteca. Todos los derechos reservados.</p>
            <div class="footer-links">
                <a href="#"><i class="fas fa-info-circle"></i> Ayuda</a>
                <a href="#"><i class="fas fa-envelope"></i> Contacto</a>
            </div>
        </div>
    </footer>
    <script src="<?php echo isset($relative_path) ? $relative_path : ''; ?>assets/js/main.js"></script>
    <script src="<?php echo isset($relative_path) ? $relative_path : ''; ?>assets/js/validaciones.js"></script>
    <script src="<?php echo isset($relative_path) ? $relative_path : ''; ?>assets/js/busquedas.js"></script>
</body>
</html>