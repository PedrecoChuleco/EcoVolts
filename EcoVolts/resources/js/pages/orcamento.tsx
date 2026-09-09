import InputError from '@/components/input-error';
import EcoVoltsLayout, { EcoButton, EcoCard, EcoField, ecoInputClass, PanelHeading } from '@/layouts/ecovolts-layout';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

interface BudgetForm {
    bill_amount: string;
    knows_roof_size: boolean;
    roof_size_m2: string;
    knows_direction: boolean;
    roof_direction: string;
}

export default function Orcamento({ directions }: { directions: string[] }) {
    const { data, setData, post, processing, errors } = useForm<BudgetForm>({
        bill_amount: '',
        knows_roof_size: true,
        roof_size_m2: '',
        knows_direction: true,
        roof_direction: directions[0] ?? 'Norte',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('orcamento.store'));
    };

    return (
        <EcoVoltsLayout variant="account">
            <Head title="Orçamento" />
            <PanelHeading
                eyebrow="Simulação"
                title="Calcular orçamento"
                subtitle="Quanto mais informações sobre o telhado, mais preciso é o resultado."
            />

            <EcoCard>
                <form onSubmit={submit}>
                    <div className="grid grid-cols-1 gap-x-5 gap-y-[18px] md:grid-cols-2">
                        <EcoField id="bill_amount" label="Conta de energia (R$)" full>
                            <input
                                id="bill_amount"
                                className={ecoInputClass}
                                value={data.bill_amount}
                                onChange={(e) => setData('bill_amount', e.target.value)}
                                placeholder="Ex: 380,00"
                                required
                            />
                            <InputError message={errors.bill_amount} />
                        </EcoField>

                        <div className="col-span-full mt-1 border-t border-eco-line pt-5">
                            <span className="mb-2.5 block text-[14.5px] font-medium">Você sabe o tamanho do telhado?</span>
                            <button
                                type="button"
                                className={`mr-2 inline-block rounded-full border px-4 py-[7px] text-[13.5px] ${
                                    data.knows_roof_size
                                        ? 'border-eco-green bg-eco-green text-white'
                                        : 'border-eco-line text-eco-muted'
                                }`}
                                onClick={() => setData('knows_roof_size', true)}
                            >
                                Sim
                            </button>
                            <button
                                type="button"
                                className={`mr-2 inline-block rounded-full border px-4 py-[7px] text-[13.5px] ${
                                    !data.knows_roof_size
                                        ? 'border-eco-green bg-eco-green text-white'
                                        : 'border-eco-line text-eco-muted'
                                }`}
                                onClick={() => setData('knows_roof_size', false)}
                            >
                                Não
                            </button>
                            <span className="mt-1.5 block text-[12.5px] text-eco-hint">Se não souber, usamos uma estimativa média.</span>
                            {data.knows_roof_size && (
                                <div className="mt-4 max-w-[420px]">
                                    <EcoField id="roof_size_m2" label="Tamanho do telhado (m²)" full>
                                        <input
                                            id="roof_size_m2"
                                            className={ecoInputClass}
                                            value={data.roof_size_m2}
                                            onChange={(e) => setData('roof_size_m2', e.target.value)}
                                            placeholder="Ex: 45"
                                        />
                                        <InputError message={errors.roof_size_m2} />
                                    </EcoField>
                                </div>
                            )}
                        </div>

                        <div className="col-span-full mt-1 border-t border-eco-line pt-5">
                            <span className="mb-2.5 block text-[14.5px] font-medium">Você sabe a direção do telhado?</span>
                            <button
                                type="button"
                                className={`mr-2 inline-block rounded-full border px-4 py-[7px] text-[13.5px] ${
                                    data.knows_direction
                                        ? 'border-eco-green bg-eco-green text-white'
                                        : 'border-eco-line text-eco-muted'
                                }`}
                                onClick={() => setData('knows_direction', true)}
                            >
                                Sim
                            </button>
                            <button
                                type="button"
                                className={`mr-2 inline-block rounded-full border px-4 py-[7px] text-[13.5px] ${
                                    !data.knows_direction
                                        ? 'border-eco-green bg-eco-green text-white'
                                        : 'border-eco-line text-eco-muted'
                                }`}
                                onClick={() => setData('knows_direction', false)}
                            >
                                Não
                            </button>
                            <span className="mt-1.5 block text-[12.5px] text-eco-hint">A direção muda a eficiência de geração das placas.</span>
                            {data.knows_direction && (
                                <div className="mt-4 max-w-[420px]">
                                    <EcoField id="roof_direction" label="Direção do telhado" full>
                                        <select
                                            id="roof_direction"
                                            className={ecoInputClass}
                                            value={data.roof_direction}
                                            onChange={(e) => setData('roof_direction', e.target.value)}
                                        >
                                            {directions.map((direction) => (
                                                <option key={direction} value={direction}>
                                                    {direction}
                                                </option>
                                            ))}
                                        </select>
                                        <InputError message={errors.roof_direction} />
                                    </EcoField>
                                </div>
                            )}
                        </div>
                    </div>

                    <div className="mt-[26px] flex items-center gap-3.5">
                        <EcoButton type="submit" disabled={processing}>
                            Calcular orçamento
                        </EcoButton>
                        <EcoButton href={route('dashboard')} variant="ghost">
                            Cancelar
                        </EcoButton>
                    </div>
                </form>
            </EcoCard>
        </EcoVoltsLayout>
    );
}
