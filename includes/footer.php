<?php
/**
 * Fecha o que includes/header.php abriu (conforme $variant), imprime o rodapé
 * do site SOMENTE na landing e encerra o documento.
 */
$variant = $variant ?? 'landing';
?>
<?php if ($variant === 'guest'): ?>
        </main>
    </div>
</div>
<?php else: ?>
    </main>
</div>
<?php endif; ?>

<?php if ($variant === 'landing'): ?>
    <?php include __DIR__ . '/site-footer.php'; ?>
<?php endif; ?>

<script src="assets/js/main.js"></script>
<script>if (window.lucide) { lucide.createIcons(); }</script>
</body>
</html>
