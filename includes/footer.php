<?php
/**
 * Closes whatever includes/header.php opened, based on $variant,
 * then prints the site footer and closing scripts/tags.
 */
?>
    <?php if ($variant === 'guest'): ?>
            </div>
        </main>
        <div class="lg:fixed inset-0 hidden h-screen overflow-hidden bg-eco-dark lg: lg:block -z-10">
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-b from-black via-transparent to-transparent"></div>
            <img src="assets/images/auth-banner.jpg" alt="EcoVolts Energia Solar"
                 class="h-full w-full object-cover object-center">
        </div>  
    </div>
    <?php else: ?>
    </main>
    <?php endif; ?>
</div>

<?php if ($variant !== 'guest'): ?>
    <?php include __DIR__ . '/site-footer.php'; ?>
<?php endif; ?>

<script src="assets/js/main.js"></script>
<script>if (window.lucide) { lucide.createIcons(); }</script>
</body>
</html>
