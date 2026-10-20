<?php
/** Logo + nome "EcoVolts" com link para a home. Usada na barra do topo e no menu lateral. */
?>
<a href="<?= route('home') ?>" class="flex shrink-0 items-center gap-2.5">
    <img src="assets/images/brand-small.png" alt="" class="h-[48px] w-[55px] shrink-0">
    <span class="font-serif text-2xl font-semibold tracking-[0.2px]">
        <span class="text-eco-green">Eco</span><span class="text-eco-amber">Volts</span>
    </span>
</a>
