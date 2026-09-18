import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import EcoVoltsLayout, { EcoButton, EcoCard, EcoField, ecoInputClass, PanelHeading } from '@/layouts/ecovolts-layout';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import { type FormDataConvertible } from '@inertiajs/core';

interface LoginForm {
    email: string;
    password: string;
    remember: boolean;
    [key: string]: FormDataConvertible;
}

interface LoginProps {
    status?: string;
    canResetPassword: boolean;
}

export default function Login({ status, canResetPassword }: LoginProps) {
    const { data, setData, post, processing, errors, reset } = useForm<LoginForm>({
        email: '',
        password: '',
        remember: false,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <EcoVoltsLayout variant="guest">
            <Head title="Login" />
            <PanelHeading 
                eyebrow="Passo 2 de 2" 
                title="Entrar na conta"
            />

            <EcoCard>
                {status && <div className="mb-4 text-sm font-medium text-eco-green">{status}</div>}
                <form onSubmit={submit}>
                    <div className="grid grid-cols-1 gap-[18px]">
                        <EcoField id="email" label="E-mail">
                            <input
                                id="email"
                                type="email"
                                className={ecoInputClass}
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                autoComplete="email"
                                autoFocus
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
                                autoComplete="current-password"
                                placeholder="••••••••"
                                required
                            />
                            <InputError message={errors.password} />
                        </EcoField>
                    </div>

                    <div className="mt-[26px] flex items-center gap-3.5">
                        <EcoButton type="submit" disabled={processing}>
                            Entrar
                        </EcoButton>
                        {canResetPassword ? (
                            <TextLink href={route('password.request')} className="text-[13px] text-eco-muted">
                                Esqueceu a senha?
                            </TextLink>
                        ) : (
                            <span className="text-[13px] text-eco-muted">Esqueceu a senha?</span>
                        )}
                    </div>
                </form>
            </EcoCard>
        </EcoVoltsLayout>
    );
}
