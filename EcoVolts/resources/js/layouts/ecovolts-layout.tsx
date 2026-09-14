import { BrandMark, RoofTick } from '@/components/ecovolts/brand-mark';
import { type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { PropsWithChildren } from 'react';
import { useScrollOpacity } from '@/hooks/use-scroll-opacity';
import authBanner from '@/assets/auth-banner.jpg';
import Footer from '@/components/ecovolts/footer';

type EcoVoltsLayoutProps = PropsWithChildren<{
    variant: 'landing' | 'guest' | 'account';
}>;

function navClass(active: boolean) {
    return active
        ? 'flex items-baseline gap-2 whitespace-nowrap rounded-t-md border-b-2 border-eco-amber px-3.5 py-2 text-sm font-semibold'
        : 'flex items-baseline gap-2 whitespace-nowrap rounded-t-md border-b-2 border-transparent px-3.5 py-2 text-sm text-eco-muted hover:text-eco-hint';
}

function sidebarNavClass(active: boolean) {
    return active
        ? 'flex items-baseline gap-2.5 rounded-md border-l-2 border-eco-amber bg-eco-amber/14 px-2.5 py-2.5 text-[14.5px] text-white'
        : 'flex items-baseline gap-2.5 rounded-md border-l-2 border-transparent px-2.5 py-2.5 text-[14.5px] text-[#C7D8D1] hover:bg-white/5 hover:text-white';
}

export default function EcoVoltsLayout({ children, variant }: EcoVoltsLayoutProps) {
    const { auth } = usePage<SharedData>().props;
    const path = usePage().url;
    const showSidebar = variant === 'account';
    const showImgGuest = variant ==='guest';
    const headerOpacity = useScrollOpacity();

    return (
        <div className="min-h-screen bg-eco-bg font-sans text-eco-ink">
            <header className={`fixed top-0 left-0 z-50 ${showImgGuest ? 'w-full lg:w-1/2' : 'w-full'}`}>
                <div
                    className="absolute inset-0 bg-eco-dark/25 backdrop-blur-sm transition-opacity duration-150 ease-out"
                    style={{ opacity: headerOpacity }}
                    aria-hidden="true"
                />

                <div className="relative flex flex-wrap items-center justify-between gap-6 px-5 py-4 md:px-10">
                    <Link href={route('home')} className="flex items-center gap-2.5">
                        <BrandMark />
                        <span className="font-serif text-2xl font-semibold tracking-[0.2px]">
                            <span className="text-eco-green">Eco</span><span className="text-eco-amber">Volts</span>
                        </span>
                    </Link>

                    {!auth.user && (
                        <nav className="flex gap-1 overflow-x-auto">
                            <Link href={route('register')} className={navClass(path.startsWith('/register'))}>
                                Cadastro
                            </Link>
                            <Link href={route('login')} className={navClass(path.startsWith('/login'))}>
                                Login
                            </Link>
                        </nav>
                    )}
                </div>
            </header>

            <div className={showSidebar ? 'grid min-h-screen grid-cols-1 md:grid-cols-[248px_1fr]' : 'grid min-h-[calc(100vh-65px)] grid-cols-1'}>
                {showSidebar && (
                    <aside className="flex flex-row items-center gap-6 overflow-x-auto bg-eco-ink px-5 text-eco-line md:flex-col md:items-stretch md:gap-8 md:px-5 md:py-6 md:pt-20">
                        <nav className="flex flex-row gap-0.5 md:flex-col">
                            <p className="mb-2.5 ml-0.5 hidden text-xs text-[#9FB4AE] md:block">Área da conta</p>
                            <Link href={route('dashboard')} className={sidebarNavClass(path.startsWith('/dashboard'))}>
                                Menu
                            </Link>
                            <Link href={route('perfil')} className={sidebarNavClass(path.startsWith('/perfil'))}>
                                Perfil
                            </Link>
                            <Link href={route('orcamento')} className={sidebarNavClass(path.startsWith('/orcamento'))}>
                                Orçamento
                            </Link>
                            <Link href={route('relatorio')} className={sidebarNavClass(path.startsWith('/relatorio'))}>
                                Relatório
                            </Link>
                        </nav>
                    </aside>
                )}

                {showImgGuest ? (
                    <div className="grid min-h-screen grid-cols-1 lg:grid-cols-2">
                        <main className="flex min-h-screen flex-col justify-center px-6 pt-24 pb-12 md:px-14 lg:px-16">
                            <div className="mx-auto w-full max-w-[600px]">
                                {children}
                            </div>
                        </main>
                        
                        <div className="sticky top-0 hidden h-screen overflow-hidden bg-eco-dark lg:block">
                            <img
                                src={authBanner} 
                                alt="EcoVolts Energia Solar"
                                className="h-full w-full object-cover object-center"
                            />
                        </div>
                    </div>
                ) : (
                    <main className={variant === 'landing' ? 'max-w-none p-0' : 'max-w-[760px] px-[22px] py-[34px] pb-[60px] md:px-14 md:py-12 md:pb-20'}>
                        {children}
                    </main>
                )}
            </div>
        </div>
    );
}

export function PanelHeading({ eyebrow, title }: { eyebrow: string; title: string;}) {
    return (
        <>
            <div className="mb-2.5 flex items-center gap-2.5 text-[13.5px] text-eco-muted mt-10">
                <RoofTick /> {eyebrow}
            </div>
            <h1 className="mb-1.5 font-serif text-[34px] font-medium">{title}</h1>
        </>
    );
}

export function EcoCard({ children, className = '' }: PropsWithChildren<{ className?: string }>) {
    return <div className={`rounded-[10px] border border-eco-line bg-eco-surface p-[30px_32px] ${className}`}>{children}</div>;
}

export function EcoButton({
    children,
    variant = 'primary',
    type = 'button',
    disabled,
    href,
    method,
}: PropsWithChildren<{
    variant?: 'primary' | 'ghost';
    type?: 'button' | 'submit';
    disabled?: boolean;
    href?: string;
    method?: 'get' | 'post';
}>) {
    const classes =
        variant === 'primary'
            ? 'inline-flex items-center gap-2 rounded-md bg-eco-ink px-[22px] py-3 text-center text-[15px] font-semibold text-white hover:bg-[#0B1D20] disabled:opacity-60'
            : 'inline-flex items-center gap-2 rounded-md border border-eco-line bg-transparent px-[22px] py-3 text-center text-[15px] font-semibold text-eco-ink hover:border-eco-ink';

    if (href) {
        return (
            <Link href={href} method={method === 'post' ? 'post' : undefined} as={method === 'post' ? 'button' : undefined} className={classes}>
                {children}
            </Link>
        );
    }

    return (
        <button type={type} disabled={disabled} className={classes}>
            {children}
        </button>
    );
}

export function EcoField({
    id,
    label,
    children,
    full,
}: PropsWithChildren<{
    id?: string;
    label: string;
    full?: boolean;
}>) {
    return (
        <div className={`flex flex-col gap-1.5 ${full ? 'col-span-full' : ''}`}>
            <label htmlFor={id} className="text-[13px] font-medium text-eco-muted">
                {label}
            </label>
            {children}
        </div>
    );
}

export const ecoInputClass =
    'rounded-md border border-eco-line bg-[#FBFDFC] px-3 py-2.5 font-sans text-[15px] text-eco-ink outline-none focus-visible:border-eco-green focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-eco-green';
