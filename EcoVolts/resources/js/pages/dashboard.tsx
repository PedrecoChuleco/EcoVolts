import EcoVoltsLayout, { PanelHeading } from '@/layouts/ecovolts-layout';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';

export default function Dashboard() {
    const { auth } = usePage<SharedData>().props;
    const firstName = auth.user.name.split(' ')[0] ?? auth.user.name;

    return (
        <EcoVoltsLayout variant="account">
            <Head title="Menu" />
            <PanelHeading eyebrow={`Bem-vindo(a), ${firstName}`} title="O que você quer fazer?" subtitle="Escolha uma opção para continuar." />

            <div className="grid gap-3.5">
                <Link
                    href={route('perfil')}
                    className="flex items-center justify-between gap-4 rounded-[10px] border border-eco-line bg-eco-surface px-6 py-[22px]"
                >
                    <div>
                        <h3 className="mb-1 font-serif text-[19px] font-medium">Ver informações do usuário</h3>
                        <p className="m-0 text-sm text-eco-muted">Confira seus dados de cadastro e endereço salvos.</p>
                    </div>
                    <span aria-hidden>→</span>
                </Link>

                <Link
                    href={route('orcamento')}
                    className="flex items-center justify-between gap-4 rounded-[10px] border border-eco-line bg-eco-surface px-6 py-[22px]"
                >
                    <div>
                        <h3 className="mb-1 font-serif text-[19px] font-medium">Calcular orçamento</h3>
                        <p className="m-0 text-sm text-eco-muted">Informe sua conta de energia e receba uma estimativa de placas solares.</p>
                    </div>
                    <span aria-hidden>→</span>
                </Link>

                <Link
                    href={route('logout')}
                    method="post"
                    as="button"
                    className="flex items-center justify-between gap-4 rounded-[10px] border border-dashed border-eco-line bg-eco-surface px-6 py-[22px] text-left"
                >
                    <div>
                        <h3 className="mb-1 font-serif text-[19px] font-medium text-eco-danger">Sair</h3>
                        <p className="m-0 text-sm text-eco-muted">Encerrar a sessão e voltar para o início.</p>
                    </div>
                    <span aria-hidden>×</span>
                </Link>
            </div>
        </EcoVoltsLayout>
    );
}
