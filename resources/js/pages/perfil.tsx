import EcoVoltsLayout, { EcoButton, EcoCard, PanelHeading } from '@/layouts/ecovolts-layout';
import { type SharedData } from '@/types';
import { Head, usePage } from '@inertiajs/react';

export default function Perfil() {
    const { auth } = usePage<SharedData>().props;
    const user = auth.user;
    const address = [user.street, [user.neighborhood, user.city].filter(Boolean).join(', ')].filter(Boolean).join(' — ');

    return (
        <EcoVoltsLayout variant="account">
            <Head title="Perfil" />
            <PanelHeading 
                eyebrow="Meus dados" 
                title="Informações do usuário"
            />

            <EcoCard>
                <dl className="grid grid-cols-1 gap-x-6 gap-y-[22px] md:grid-cols-2">
                    <div>
                        <dt className="mb-1 text-[12.5px] text-eco-muted">Nome</dt>
                        <dd className="m-0 font-serif text-lg">{user.name}</dd>
                    </div>
                    <div>
                        <dt className="mb-1 text-[12.5px] text-eco-muted">CPF</dt>
                        <dd className="m-0 font-serif text-lg">{user.cpf || '—'}</dd>
                    </div>
                    <div>
                        <dt className="mb-1 text-[12.5px] text-eco-muted">Telefone</dt>
                        <dd className="m-0 font-serif text-lg">{user.phone || '—'}</dd>
                    </div>
                    <div>
                        <dt className="mb-1 text-[12.5px] text-eco-muted">CEP</dt>
                        <dd className="m-0 font-serif text-lg">{user.cep || '—'}</dd>
                    </div>
                    <div className="col-span-full">
                        <dt className="mb-1 text-[12.5px] text-eco-muted">Endereço</dt>
                        <dd className="m-0 font-serif text-lg">{address || '—'}</dd>
                    </div>
                </dl>

                <div className="mt-[26px] flex items-center gap-3.5">
                    <EcoButton href={route('dashboard')} variant="ghost">
                        Voltar ao menu
                    </EcoButton>
                </div>
            </EcoCard>
        </EcoVoltsLayout>
    );
}
