import InputError from '@/components/input-error';
import EcoVoltsLayout, { EcoButton, EcoCard, EcoField, ecoInputClass, PanelHeading } from '@/layouts/ecovolts-layout';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';
import { InputMask } from '@react-input/mask';

interface RegisterForm {
    name: string;
    cpf: string;
    phone: string;
    cep: string;
    city: string;
    neighborhood: string;
    street: string;
    email: string;
    password: string;
    password_confirmation: string;
    [key: string]: string;
}

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm<RegisterForm>({
        name: '',
        cpf: '',
        phone: '',
        cep: '',
        city: '',
        neighborhood: '',
        street: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const [cepLoading, setCepLoading] = useState(false);
    const [cepError, setCepError] = useState<string | null>(null);

    async function handleCep(value: string) {
        setData('cep', value);
        setCepError(null);

        const digits = value.replace(/\D/g, '');
        if (digits.length !== 8) return;

        setCepLoading(true);
        try {
            const response = await fetch(`https://viacep.com.br/ws/${digits}/json/`);
            const address = await response.json();

            if (address.erro) {
                setCepError('CEP não encontrado');
            } else {
                setData((prevData) => ({
                    ...prevData,
                    cep: value,
                    city: address.localidade || prevData.city,
                    neighborhood: address.bairro || prevData.neighborhood,
                    street: address.logradouro || prevData.street,
                }));
            }
        } catch {
            setCepError('Erro ao buscar CEP');
        } finally {
            setCepLoading(false);
        }
    }

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <EcoVoltsLayout variant="guest">
            <Head title="Cadastro" />
            <PanelHeading eyebrow="Passo 1 de 2" title="Cadastro do usuário" />

            <EcoCard>
                <form onSubmit={submit}>
                    <div className="grid grid-cols-1 gap-x-5 gap-y-[18px] md:grid-cols-2">
                        <EcoField id="name" label="Nome de usuário" full>
                            <input
                                id="name"
                                className={ecoInputClass}
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                autoComplete="name"
                                autoFocus
                                placeholder="Ex: Maria Souza"
                                required
                            />
                            <InputError message={errors.name} />
                        </EcoField>
                        <EcoField id="cpf" label="CPF">
                            <InputMask
                                id="cpf"
                                className={ecoInputClass}
                                value={data.cpf}
                                onChange={(e) => setData('cpf', e.target.value)}
                                placeholder="000.000.000-00"
                                mask="___.___.___-__"
                                replacement={{ _: /\d/ }}
                                required
                            />
                            <InputError message={errors.cpf} />
                        </EcoField>
                        <EcoField id="phone" label="Telefone">
                            <InputMask
                                id="phone"
                                className={ecoInputClass}
                                value={data.phone}
                                onChange={(e) => setData('phone', e.target.value)}
                                autoComplete="tel"
                                placeholder="(00) 00000-0000"
                                mask="(__) _____-____"
                                replacement={{ _: /\d/ }}
                                required
                            />
                            <InputError message={errors.phone} />
                        </EcoField>
                        <EcoField id="cep" label="CEP">
                            <InputMask
                                id="cep"
                                className={ecoInputClass}
                                value={data.cep}
                                onChange={(e) => handleCep(e.target.value)}
                                autoComplete="postal-code"
                                placeholder="00000-000"
                                mask="_____-___"
                                replacement={{ _: /\d/ }}
                                required
                            />
                            {cepLoading && <span className="text-xs text-eco-muted">Buscando endereço...</span>}
                            {cepError && <span className="text-xs text-red-500">{cepError}</span>}
                            <InputError message={errors.cep} />
                        </EcoField>
                        <EcoField id="city" label="Cidade">
                            <input
                                id="city"
                                className={ecoInputClass}
                                value={data.city}
                                onChange={(e) => setData('city', e.target.value)}
                                autoComplete="address-level2"
                                placeholder="Ex: Sorocaba"
                                required
                            />
                            <InputError message={errors.city} />
                        </EcoField>
                        <EcoField id="neighborhood" label="Bairro" full>
                            <input
                                id="neighborhood"
                                className={ecoInputClass}
                                value={data.neighborhood}
                                onChange={(e) => setData('neighborhood', e.target.value)}
                                placeholder="Ex: Jardim das Flores"
                                required
                            />
                            <InputError message={errors.neighborhood} />
                        </EcoField>
                        <EcoField id="street" label="Rua" full>
                            <input
                                id="street"
                                className={ecoInputClass}
                                value={data.street}
                                onChange={(e) => setData('street', e.target.value)}
                                autoComplete="street-address"
                                placeholder="Ex: Rua das Acácias, 120"
                                required
                            />
                            <InputError message={errors.street} />
                        </EcoField>
                        <EcoField id="email" label="E-mail" full>
                            <input
                                id="email"
                                type="email"
                                className={ecoInputClass}
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                autoComplete="email"
                                placeholder="voce@email.com"
                                required
                            />
                            <InputError message={errors.email} />
                        </EcoField>
                        <EcoField id="password" label="Senha">
                            <input
                                id="password"
                                type="password"
                                className={ecoInputClass}
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                autoComplete="new-password"
                                placeholder="••••••••"
                                required
                            />
                            <InputError message={errors.password} />
                        </EcoField>
                        <EcoField id="password_confirmation" label="Confirmar senha">
                            <input
                                id="password_confirmation"
                                type="password"
                                className={ecoInputClass}
                                value={data.password_confirmation}
                                onChange={(e) => setData('password_confirmation', e.target.value)}
                                autoComplete="new-password"
                                placeholder="••••••••"
                                required
                            />
                            <InputError message={errors.password_confirmation} />
                        </EcoField>
                    </div>

                    <div className="mt-[26px] flex items-center gap-3.5">
                        <EcoButton type="submit" disabled={processing}>
                            Criar conta
                        </EcoButton>
                        <span className="text-[13px] text-eco-muted">Depois de cadastrar, você fará login com este e-mail e senha.</span>
                    </div>
                </form>
            </EcoCard>
        </EcoVoltsLayout>
    );
}