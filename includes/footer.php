</main>
    
    <footer class="footer">
        <div class="footer-content">
            <p>&copy; <?php echo date('Y'); ?> Judging System - LAMP Stack Implementation</p>
            <p>Built with PHP, MySQL, Apache, and JavaScript</p>
        </div>
    </footer>
    
    <?php if (isset($include_scoreboard_js) && $include_scoreboard_js): ?>
        <script src="<?php echo $base_url ?? ''; ?>js/scoreboard.js"></script>
    <?php endif; ?>
</body>
</html>