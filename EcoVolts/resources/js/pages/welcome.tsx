import { RoofTick } from '@/components/ecovolts/brand-mark';
import EcoVoltsLayout from '@/layouts/ecovolts-layout';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';

export default function Welcome() {
    const { auth } = usePage<SharedData>().props;

    return (
        <EcoVoltsLayout variant="landing">
            <Head title="EcoVolts" />

            <section>
                <div className="flex flex-col items-center justify-between gap-12 border-b border-eco-line bg-gradient-to-b from-eco-surface to-eco-bg px-[22px] py-14 md:flex-row md:px-14 md:py-[88px] md:pb-[76px]">
                    <div className="max-w-[480px]">
                        <div className="mb-2.5 flex items-center gap-2.5 text-[13.5px] text-eco-muted">
                            <RoofTick /> Energia solar sob medida
                        </div>
                        <h1 className="mb-1.5 font-serif text-4xl leading-[1.08] font-medium md:text-[44px]">
                            Descubra quantas placas cabem no seu telhado.
                        </h1>
                        <p className="max-w-[52ch] text-[15px] text-eco-muted">
                            Informe sua conta de energia e alguns dados do telhado — o EcoVolts calcula a quantidade de placas, o investimento e o
                            tempo de retorno para você.
                        </p>
                        <div className="mt-[30px] flex items-center gap-3.5">
                            {auth.user ? (
                                <Link
                                    href={route('dashboard')}
                                    className="inline-flex items-center rounded-md bg-eco-ink px-[22px] py-3 text-[15px] font-semibold text-white hover:bg-[#0B1D20]"
                                >
                                    Ir para o menu
                                </Link>
                            ) : (
                                <>
                                    <Link
                                        href={route('register')}
                                        className="inline-flex items-center rounded-md bg-eco-ink px-[22px] py-3 text-[15px] font-semibold text-white hover:bg-[#0B1D20]"
                                    >
                                        Criar conta
                                    </Link>
                                    <Link
                                        href={route('login')}
                                        className="inline-flex items-center rounded-md border border-eco-line bg-transparent px-[22px] py-3 text-[15px] font-semibold text-eco-ink hover:border-eco-ink"
                                    >
                                        Já tenho conta
                                    </Link>
                                </>
                            )}
                        </div>
                    </div>

                    <div
                        aria-hidden
                        className="size-[140px] shrink-0 rounded-[14px] bg-eco-ink md:size-[220px]"
                        style={{
                            backgroundImage:
                                'repeating-linear-gradient(0deg, transparent 0 34px, rgba(232,163,61,0.5) 34px 36px), repeating-linear-gradient(90deg, transparent 0 34px, rgba(232,163,61,0.5) 34px 36px)',
                        }}
                    />
                </div>

                <div className="grid grid-cols-1 gap-px bg-eco-line md:grid-cols-3">
                    {[
                        {
                            title: 'Orçamento em minutos',
                            body: 'Informe o valor da sua conta de energia e receba a quantidade de placas e o investimento estimado.',
                        },
                        {
                            title: 'Telhado sob medida',
                            body: 'Sabendo o tamanho e a direção do telhado, o cálculo considera a eficiência real de geração.',
                        },
                        {
                            title: 'Payback claro',
                            body: 'Veja em quantos meses o investimento se paga com a economia na conta de energia.',
                        },
                    ].map((feature) => (
                        <div key={feature.title} className="bg-eco-surface px-8 py-[34px]">
                            <RoofTick />
                            <h3 className="mt-3.5 mb-2 font-serif text-lg font-medium">{feature.title}</h3>
                            <p className="m-0 text-sm text-eco-muted">{feature.body}</p>
                        </div>
                    ))}
                </div>

                <p className="px-[22px] py-10 pb-[60px] text-[13.5px] text-eco-muted md:px-14">
                    Crie uma conta ou entre para simular um orçamento de energia solar.
                </p>
            </section>
        </EcoVoltsLayout>
    );
}
