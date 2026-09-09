import EcoVoltsLayout, { PanelHeading } from '@/layouts/ecovolts-layout';
import { Head, Link } from '@inertiajs/react';

interface Report {
    name: string;
    address: string;
    issued_at: string;
    panel_count: number;
    investment: number;
    payback_months: number;
    current_bill: number;
    bill_after: number;
    roof_fits: boolean;
}

function brl(value: number): string {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
}

export default function Relatorio({ report }: { report: Report }) {
    return (
        <EcoVoltsLayout variant="account">
            <Head title="Relatório" />
            <PanelHeading eyebrow="Resultado" title="Orçamento final" subtitle="Estimativa gerada a partir dos dados informados." />

            <div className="overflow-hidden rounded-[10px] bg-eco-ink text-[#EAF1EC]">
                <div className="flex items-start justify-between gap-5 border-b border-white/12 px-8 pt-[26px] pb-[22px]">
                    <div>
                        <h3 className="mb-1 font-serif text-[22px] font-medium">{report.name}</h3>
                        <p className="m-0 text-[13.5px] text-[#9FB4AE]">{report.address}</p>
                    </div>
                    <div
                        aria-hidden
                        className="size-[30px] shrink-0 rounded-[4px]"
                        style={{
                            backgroundColor: '#17393F',
                            backgroundImage:
                                'repeating-linear-gradient(0deg, transparent 0 5px, rgba(232,163,61,0.6) 5px 6px), repeating-linear-gradient(90deg, transparent 0 5px, rgba(232,163,61,0.6) 5px 6px)',
                        }}
                    />
                </div>

                <dl className="grid grid-cols-1 gap-x-7 gap-y-5 px-8 py-[26px] md:grid-cols-2">
                    <div>
                        <dt className="mb-1 text-[12.5px] text-[#9FB4AE]">Quantidade de placas</dt>
                        <dd className="m-0 font-serif text-xl">{report.panel_count}</dd>
                    </div>
                    <div>
                        <dt className="mb-1 text-[12.5px] text-[#9FB4AE]">Investimento</dt>
                        <dd className="m-0 font-serif text-xl">{brl(report.investment)}</dd>
                    </div>
                    <div>
                        <dt className="mb-1 text-[12.5px] text-[#9FB4AE]">Payback</dt>
                        <dd className="m-0 font-serif text-xl">{report.payback_months} meses</dd>
                    </div>
                    <div>
                        <dt className="mb-1 text-[12.5px] text-[#9FB4AE]">Data de emissão</dt>
                        <dd className="m-0 font-serif text-xl">{report.issued_at}</dd>
                    </div>
                    <div>
                        <dt className="mb-1 text-[12.5px] text-[#9FB4AE]">Conta de energia atual</dt>
                        <dd className="m-0 font-serif text-xl">{brl(report.current_bill)}</dd>
                    </div>
                    <div>
                        <dt className="mb-1 text-[12.5px] text-[#9FB4AE]">Conta após instalação</dt>
                        <dd className="m-0 font-serif text-xl">{brl(report.bill_after)}</dd>
                    </div>
                </dl>

                <div className="flex flex-wrap items-center justify-between gap-4 px-8 pt-[18px] pb-[26px]">
                    <span
                        className={`inline-flex items-center gap-2 rounded-full border px-4 py-2 text-[13.5px] ${
                            report.roof_fits
                                ? 'border-[rgba(154,214,183,0.4)] bg-[rgba(47,107,79,0.35)] text-[#C9EFD9]'
                                : 'border-[rgba(232,163,61,0.4)] bg-[rgba(181,80,47,0.35)] text-[#F6D7C8]'
                        }`}
                    >
                        <span className={`size-[7px] rounded-full ${report.roof_fits ? 'bg-[#5FCE93]' : 'bg-eco-amber'}`} />
                        {report.roof_fits ? 'O telhado comporta as placas' : 'O telhado pode não comportar as placas'}
                    </span>
                    <Link
                        href={route('dashboard')}
                        className="inline-flex items-center rounded-md border border-white/30 px-[22px] py-3 text-[15px] font-semibold text-[#EAF1EC]"
                    >
                        Voltar ao menu
                    </Link>
                </div>
            </div>
        </EcoVoltsLayout>
    );
}
