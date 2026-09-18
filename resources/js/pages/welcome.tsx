import { BrandMark, RoofTick } from '@/components/ecovolts/brand-mark';
import EcoVoltsLayout, { EcoButton } from '@/layouts/ecovolts-layout';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { Phone, Mail, MapPin, Instagram, ArrowRight } from 'lucide-react';
import bgImg01 from '@/assets/bg-01.jpg';
import LandingBanner01 from '@/assets/landing-banner1.jpg'
import LandingBanner02 from '@/assets/landing-banner2.jpg'
import LandingBanner03 from '@/assets/landing-banner3.jpg'
import Footer from '@/components/ecovolts/footer';

export default function Welcome() {
    const { auth } = usePage<SharedData>().props;

    return (
        <EcoVoltsLayout variant="landing">
            <Head title="EcoVolts" />

            <section>
                <div
                    className="relative flex flex-col items-center justify-between gap-12 bg-eco-surface px-[22px] py-24 md:flex-row md:px-14 md:py-[88px] md:pb-[76px] h-screen bg-cover bg-bottom"
                    style={{ backgroundImage: `url(${bgImg01})` }}>
                    <div className="pointer-events-none absolute inset-0 bg-gradient-to-b from-black via-transparent to-transparent" />
                    <div className="pointer-events-none absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-b from-transparent to-eco-ink" />
                    <div className="relative z-10 max-w-[600px] mt-4">
                        <div className="mb-2.5 flex items-center gap-2.5 text-[13.5px] text-eco-muted">
                            <RoofTick /> Energia solar sob medida
                        </div>
                        <h1 className="mb-1.5 font-outfit text-4xl leading-[1.08] md:text-[60px] text-gray-200">
                            Descubra quantas placas cabem no seu telhado.
                        </h1>
                        <p className="max-w-[52ch] text-[15px] text-gray-300">
                            Informe sua conta de energia e alguns dados do telhado — o EcoVolts calcula a quantidade
                            de placas, o investimento e o tempo de retorno para você.
                        </p>
                        <div className="mt-[30px] flex items-center gap-3.5">
                            {auth.user ? (
                                <Link
                                    href={route('dashboard')}
                                    className="inline-flex items-center rounded-md bg-eco-ink px-[22px] py-3 text-[15px] font-semibold text-white hover:bg-[#0B1D20] hover:scale-105 transition-transform duration-200"
                                >
                                    Ir para o menu
                                </Link>
                            ) : (
                                <>
                                    <Link
                                        href={route('register')}
                                        className="inline-flex items-center rounded-md bg-eco-amber px-[22px] py-3 text-[15px] font-semibold text-eco-ink hover:bg-eco-amber/75 hover:scale-105 transition-transform duration-200"
                                    >
                                        Criar conta
                                    </Link>
                                    <Link
                                        href={route('login')}
                                        className="inline-flex items-center rounded-md bg-eco-line px-[22px] py-3 text-[15px] font-semibold text-eco-ink hover:bg-eco-line/75 hover:scale-105 transition-transform duration-200"
                                    >
                                        Já tenho conta
                                    </Link>
                                </>
                            )}
                        </div>
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 bg-eco-ink px-32 pt-13">
                    {[
                        {
                            title: '20.000+',
                            body: 'Placas instaladas',
                        },
                        {
                            title: '20MW+',
                            body: 'Potência instalada',
                        },
                        {
                            title: 'R$10M+',
                            body: 'Economia gerada',
                        },
                    ].map((feature) => (
                        <a
                            className="flex items-center justify-center gap-6 px-auto py-5 my-4 mx-2 rounded-md bg-eco-dark border border-eco-amber/10 hover:scale-105 transition-transform duration-200">
                            <div className="text-center">
                                <h3 className="m-0 text-6xl font-black font-outfit text-eco-amber uppercase">
                                    {feature.title}
                                </h3>
                                <p className="m-0 mt-1 text-md text-white">{feature.body}</p>
                            </div>
                        </a>
                    ))}
                </div>

                <div className="grid grid-cols-1 h-[33vh] lg:grid-cols-2 lg:h-screen bg-eco-ink">
                    <div className="m-auto p-16 lg:p-40">
                        <RoofTick />
                        <h2 className="text-eco-surface font-medium text-6xl font-serif mb-2">Orçamento em minutos</h2>
                        <p className="text-eco-muted text-2xl">Informe o valor da sua conta de energia e receba a quantidade de placas e o investimento estimado.</p>
                    </div>
                    <div className="hidden overflow-hidden bg-eco-ink lg:block lg:p-20">
                        <img
                            src={LandingBanner01} 
                            alt="EcoVolts Energia Solar"
                            className="h-full w-full object-cover object-center border-6 border-eco-amber/75 rounded-4xl  hover:scale-105 transition-transform duration-400"
                        />
                    </div>
                </div>

                <div className="grid grid-cols-1 h-[33vh] lg:grid-cols-2 lg:h-screen bg-eco-ink lg:-my-72">
                    <div className="m-auto p-16 lg:p-40 order-last">
                        <RoofTick />
                        <h2 className="text-eco-surface text-6xl font-serif mb-2">Telhado sob medida</h2>
                        <p className="text-eco-muted text-2xl">Sabendo o tamanho e a direção do telhado, o cálculo considera a eficiência real de geração.</p>
                    </div>
                    <div className="hidden overflow-hidden bg-eco-bg lg:block lg:p-20 rounded-r-4xl">
                        <img
                            src={LandingBanner02} 
                            alt="EcoVolts Energia Solar"
                            className="h-full w-full object-cover object-center border-6 border-eco-ink rounded-4xl hover:scale-105 transition-transform duration-400"
                        />
                    </div>
                </div>

                <div className="grid grid-cols-1 h-[33vh] lg:grid-cols-2 lg:h-screen bg-eco-ink">
                    <div className="m-auto p-16 lg:p-40">
                        <RoofTick />
                        <h2 className="text-eco-surface font-medium text-6xl font-serif mb-2">Payback claro</h2>
                        <p className="text-eco-muted text-2xl">Veja em quantos meses o investimento se paga com a economia na conta de energia.</p>
                    </div>
                    <div className="hidden overflow-hidden lg:block lg:p-20">
                        <img
                            src={LandingBanner03} 
                            alt="EcoVolts Energia Solar"
                            className="h-full w-full object-cover object-center border-6 border-eco-amber/75 rounded-4xl  hover:scale-105 transition-transform duration-400"
                        />
                    </div>
                </div>

                <div className="grid grid-cols-1 h-screen bg-eco-dark items-center px-32 lg:grid-cols-2 lg:gap-32" id="contato">
                    <div className="">
                        <h1 className="text-6xl text-eco-surface font-outfit mb-2 font-bold">Pronto para começar?</h1>
                        <p className="text-2xl text-eco-muted mb-2">Crie uma conta ou entre para simular um orçamento de energia solar.</p>
                        <Link href={route(auth.user ? 'dashboard' : 'register')} className="flex flex-row gap-2 center items-center w-fit my-4 p-3 rounded-md bg-eco-amber hover:bg-eco-amber/75 hover:scale-105 transition-transform duration-200">
                            <span className="text-eco-ink text-xl">Começar</span>
                            <ArrowRight />
                        </Link>
                    </div>
                    <div className="order-first lg:order-last">
                        {[
                            {
                                icon: Phone,
                                title: 'Entre em contato',
                                body: '(19) 99999-9999',
                                href: 'tel:+5519999999999',
                            },
                            {
                                icon: Mail,
                                title: 'Mande um E-Mail',
                                body: 'ecovolts@ecovolts.com.br',
                                href: 'mailto:ecovolts@ecovolts.com.br',
                            },
                            {
                                icon: MapPin,
                                title: 'Sede',
                                body: 'Av. Dr. Maximiliano Baruto, 500 - Jardim Universitario, Araras - SP, 13607-339',
                                href: 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(
                                    'Av. Dr. Maximiliano Baruto, 500 - Jardim Universitario, Araras - SP, 13607-339'
                                ),
                            },
                        ].map((feature) => (
                            <a
                                key={feature.title}
                                href={feature.href}
                                target={feature.href.startsWith('http') ? '_blank' : undefined}
                                rel={feature.href.startsWith('http') ? 'noopener noreferrer' : undefined}
                                className="flex items-center gap-4 px-6 py-5 my-4 rounded-md bg-eco-ink border border-eco-amber/10 hover:scale-105 transition-transform duration-200">
                                <div className="flex size-14 shrink-0 items-center justify-center bg-eco-amber rounded-md">
                                    <feature.icon className="size-6 text-eco-ink" strokeWidth={2} />
                                </div>
                                <div>
                                    <p className="m-0 text-xs font-bold tracking-widest text-eco-amber uppercase">
                                        {feature.title}
                                    </p>
                                    <p className="m-0 mt-1 text-lg text-white">{feature.body}</p>
                                </div>
                            </a>
                        ))}
                    </div>
                </div>

                <Footer/>
            </section>
        </EcoVoltsLayout>
    );
}
