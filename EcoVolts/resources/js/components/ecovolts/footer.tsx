import { Link } from "@inertiajs/react";
import { BrandMark } from "./brand-mark";
import { Phone, Mail, MapPin } from 'lucide-react';
import { SiX, SiYoutube, SiFacebook, SiInstagram } from '@icons-pack/react-simple-icons';

export default function Footer() {
    return (
        <footer className="flex flex-col bg-eco-ink py-12">
            <div className="grid grid-cols-1 text-left pb-12 border-b-2 border-eco-hint lg:grid-cols-4">
                <div className="mx-auto p-4 rounded-xl">
                    <Link href={route('home')} className="flex gap-2.5">
                        <BrandMark />
                        <span className="font-serif text-2xl font-semibold tracking-[0.2px]">
                            <span className="text-eco-green">Eco</span><span className="text-eco-amber">Volts</span>
                        </span>
                    </Link>
                </div>
                <div className="">
                    <h3 className="text-eco-amber font-outfit font-bold tracking-widest">Navegação</h3>
                    <div className="pt-2">
                        {[
                            {
                                title: 'Home',
                                href: '',
                            },
                            {
                                title: 'Sobre',
                                href: 'sobre',
                            },
                            {
                                title: 'Trabalhe conosco',
                                href: 'vagas',
                            },
                            {
                                title: 'Contato',
                                href: '#contato',
                            },
                        ].map((feature) => (
                            <a
                                className="flex items-center py-1 hover:scale-105 transition-transform duration-200 w-fit"
                                href={feature.href}>
                                <div className="text-center">
                                    <h3 className="m-0 text-md text-eco-muted hover:text-eco-amber">
                                        {feature.title}
                                    </h3>
                                </div>
                            </a>
                        ))}
                    </div>
                </div>
                <div className="">
                    <h3 className="text-eco-amber font-outfit font-bold tracking-widest">Contato</h3>
                    <div>
                        {[
                            {
                                icon: Phone,
                                body: '(19) 99999-9999',
                                href: 'tel:+5519999999999',
                            },
                            {
                                icon: Mail,
                                body: 'ecovolts@ecovolts.com.br',
                                href: 'mailto:ecovolts@ecovolts.com.br',
                            },
                            {
                                icon: MapPin,
                                body: 'Av. Dr. Maximiliano Baruto, 500 - Jardim Universitario, Araras - SP, 13607-339',
                            },
                        ].map((feature) => (
                            <a
                                href={feature.href}
                                className="flex items-center text-left hover:text-eco-amber w-fit">
                                <div className="flex size-14 shrink-0 items-center -mr-4 rounded-md">
                                    <feature.icon className="size-6 text-eco-amber" strokeWidth={2} />
                                </div>
                                <div>
                                    <p className="m-0 mt-1 text-lg text-eco-muted">{feature.body}</p>
                                </div>
                            </a>
                        ))}
                    </div>
                </div>
                <div className="">
                    <h3 className="text-eco-amber font-outfit font-bold tracking-widest">Redes Sociais</h3>
                    <div className="flex">
                        {[
                            {
                                icon: SiInstagram,
                                href: 'https://www.instagram.com',
                            },
                            {
                                icon: SiFacebook,
                                href: 'https://www.facebook.com',
                            },
                            {
                                icon: SiYoutube,
                                href: 'https://www.youtube.com'
                            },
                            {
                                icon: SiX,
                                href: 'https://www.twitter.com'
                            },
                        ].map((feature) => (
                            <a
                                href={feature.href}
                                target={feature.href.startsWith('http') ? '_blank' : undefined}
                                rel={feature.href.startsWith('http') ? 'noopener noreferrer' : undefined}
                                className="flex items-center text-left hover:text-eco-amber w-fit">
                                <div className="flex size-20 shrink-0 items-center rounded-md">
                                    <feature.icon className="size-10 text-eco-amber" strokeWidth={2} />
                                </div>
                            </a>
                        ))}
                    </div>
                </div>
            </div>
            <div className="px-12 pt-12 flex justify-between">
                <p className="text-eco-muted">© 2026 EcoVolts Energia Solar. Todos os direitos reservados</p>
                <Link href="" className="text-eco-muted">Politica de Privacidade</Link>
            </div>
        </footer>
    )
}