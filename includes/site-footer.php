<?php
/**
 * Converted from the React Footer.jsx component.
 *
 * Notes on the conversion:
 * - `Link`/`route()` from Inertia -> plain <a> tags + the route() helper
 *   in includes/config.php (edit the $routes map there if a page's
 *   filename differs from what's below).
 * - lucide-react (Phone/Mail/MapPin) -> the same lucide CDN + <i data-lucide>
 *   already used elsewhere on the site.
 * - @icons-pack/react-simple-icons (SiX/SiYoutube/SiFacebook/SiInstagram)
 *   -> Font Awesome brand icons, since Lucide doesn't ship logo marks.
 *   Loaded once in includes/header.php.
 */
?>
<footer class="flex flex-col bg-eco-ink py-12">
    <div class="inset grid grid-cols-1 border-b-2 border-eco-hint pb-12 text-left lg:grid-cols-4">
        <div class="mx-auto rounded-xl p-4">
            <a href="<?= route('home') ?>" class="flex gap-2.5">
                <i data-lucide="sun-medium" class="text-eco-amber" width="26" height="26"></i>
                <span class="font-serif text-2xl font-semibold tracking-[0.2px]">
                    <span class="text-eco-green">Eco</span><span class="text-eco-amber">Volts</span>
                </span>
            </a>
        </div>

        <div>
            <h3 class="font-outfit font-bold tracking-widest text-eco-amber">Navegação</h3>
            <div class="pt-2">
                <?php
                $footerNav = [
                    ['title' => 'Home',              'href' => route('home')],
                    ['title' => 'Sobre',              'href' => route('sobre')],
                    ['title' => 'Trabalhe conosco',   'href' => route('vagas')],
                    ['title' => 'Contato',            'href' => '#contato'],
                ];
                foreach ($footerNav as $link): ?>
                    <a href="<?= htmlspecialchars($link['href']) ?>"
                       class="flex w-fit items-center py-1 transition-transform duration-200 hover:scale-105">
                        <h3 class="m-0 text-md text-eco-muted hover:text-eco-amber">
                            <?= htmlspecialchars($link['title']) ?>
                        </h3>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div>
            <h3 class="font-outfit font-bold tracking-widest text-eco-amber">Contato</h3>
            <div>
                <?php
                $footerAddress = 'Av. Dr. Maximiliano Baruto, 500 - Jardim Universitario, Araras - SP, 13607-339';
                $footerContacts = [
                    ['icon' => 'phone',   'body' => '(19) 99999-9999',            'href' => 'tel:+5519999999999'],
                    ['icon' => 'mail',    'body' => 'ecovolts@ecovolts.com.br',   'href' => 'mailto:ecovolts@ecovolts.com.br'],
                    ['icon' => 'map-pin', 'body' => $footerAddress,               'href' => null],
                ];
                foreach ($footerContacts as $contact): ?>
                    <a<?= $contact['href'] ? ' href="' . htmlspecialchars($contact['href']) . '"' : '' ?>
                       class="flex w-fit items-center text-left hover:text-eco-amber">
                        <div class="-mr-4 flex size-14 shrink-0 items-center rounded-md">
                            <i data-lucide="<?= htmlspecialchars($contact['icon']) ?>" class="size-6 text-eco-amber"></i>
                        </div>
                        <p class="m-0 mt-1 text-lg text-eco-muted"><?= htmlspecialchars($contact['body']) ?></p>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div>
            <h3 class="font-outfit font-bold tracking-widest text-eco-amber">Redes Sociais</h3>
            <div class="flex">
                <?php
                $socials = [
                    ['icon' => 'fa-instagram', 'href' => 'https://www.instagram.com'],
                    ['icon' => 'fa-facebook',  'href' => 'https://www.facebook.com'],
                    ['icon' => 'fa-youtube',   'href' => 'https://www.youtube.com'],
                    ['icon' => 'fa-x-twitter', 'href' => 'https://www.twitter.com'],
                ];
                foreach ($socials as $social):
                    $isExternal = str_starts_with($social['href'], 'http');
                ?>
                    <a href="<?= htmlspecialchars($social['href']) ?>"
                       <?= $isExternal ? 'target="_blank" rel="noopener noreferrer"' : '' ?>
                       class="flex w-fit items-center justify-center text-left hover:text-eco-amber">
                        <div class="flex size-20 shrink-0 items-center justify-center rounded-md">
                            <i class="fa-brands <?= htmlspecialchars($social['icon']) ?> text-eco-amber text-4xl"></i>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="inset flex justify-between pt-12">
        <p class="text-eco-muted">&copy; <?= date('Y') ?> EcoVolts Energia Solar. Todos os direitos reservados</p>
        <a href="<?= route('privacidade') ?>" class="text-eco-muted hover:text-eco-amber">Política de Privacidade</a>
    </div>
</footer>
