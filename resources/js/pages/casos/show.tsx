import { Head, Link } from '@inertiajs/react';
import { AlertCircle, ArrowLeft, Loader2, Scale } from 'lucide-react';
import { useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ESTADO_LABELS, ESTADO_VARIANTS } from '@/lib/estado-caso';
import { formatFecha } from '@/lib/utils';
import type { CasoDetalle } from '@/types/casos';

type ErrorType = 'unauthorized' | 'not_found' | 'unknown' | null;

export default function CasoShow({ id }: { id: number }) {
    const [token, setToken] = useState('');
    const [loading, setLoading] = useState(false);
    const [caso, setCaso] = useState<CasoDetalle | null>(null);
    const [error, setError] = useState<ErrorType>(null);

    async function consultar() {
        setLoading(true);
        setError(null);
        setCaso(null);

        try {
            const response = await fetch(`/api/casos/${id}`, {
                headers: {
                    Authorization: `Bearer ${token}`,
                    Accept: 'application/json',
                },
            });

            if (response.status === 401) {
                setError('unauthorized');

                return;
            }

            if (response.status === 404) {
                setError('not_found');

                return;
            }

            if (!response.ok) {
                setError('unknown');

                return;
            }

            const body = (await response.json()) as { data: CasoDetalle };
            setCaso(body.data);
        } catch {
            setError('unknown');
        } finally {
            setLoading(false);
        }
    }

    return (
        <>
            <Head title={`Caso #${id}`} />
            <div className="mx-auto flex min-h-screen w-full max-w-3xl flex-col gap-6 p-6 lg:p-8">
                <header className="flex items-center justify-between gap-4 border-b pb-4">
                    <div className="flex items-center gap-3">
                        <div className="flex size-10 items-center justify-center rounded-md bg-primary text-primary-foreground">
                            <Scale className="size-5" />
                        </div>
                        <div>
                            <h1 className="text-lg font-semibold">
                                Caso #{id}
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                Detalle del caso
                            </p>
                        </div>
                    </div>
                    <Button asChild variant="outline" size="sm">
                        <Link href="/">
                            <ArrowLeft />
                            Volver al listado
                        </Link>
                    </Button>
                </header>

                <Card>
                    <CardHeader>
                        <CardTitle>Autenticación</CardTitle>
                        <CardDescription>
                            Pega un Bearer Token generado con{' '}
                            <code className="rounded bg-muted px-1 py-0.5 text-xs">
                                php artisan token:generate
                            </code>{' '}
                            para consultar este caso.
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="flex flex-col gap-3 sm:flex-row sm:items-end">
                        <div className="flex-1 space-y-1.5">
                            <Label htmlFor="token">Bearer Token</Label>
                            <Input
                                id="token"
                                type="password"
                                autoComplete="off"
                                placeholder="1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                                value={token}
                                onChange={(e) => setToken(e.target.value)}
                            />
                        </div>
                        <Button
                            onClick={consultar}
                            disabled={loading || token.trim() === ''}
                        >
                            {loading && <Loader2 className="animate-spin" />}
                            Consultar caso
                        </Button>
                    </CardContent>
                </Card>

                {error === 'unauthorized' && (
                    <div className="flex items-center gap-2 rounded-lg border border-destructive/50 bg-destructive/10 p-4 text-sm text-destructive">
                        <AlertCircle className="size-4 shrink-0" />
                        Token inválido o no proporcionado.
                    </div>
                )}

                {error === 'not_found' && (
                    <div className="flex items-center gap-2 rounded-lg border border-destructive/50 bg-destructive/10 p-4 text-sm text-destructive">
                        <AlertCircle className="size-4 shrink-0" />
                        Caso no encontrado.
                    </div>
                )}

                {error === 'unknown' && (
                    <div className="flex items-center gap-2 rounded-lg border border-destructive/50 bg-destructive/10 p-4 text-sm text-destructive">
                        <AlertCircle className="size-4 shrink-0" />
                        Ocurrió un error al consultar el caso.
                    </div>
                )}

                {caso && (
                    <div className="flex flex-col gap-6">
                        <Card>
                            <CardHeader>
                                <div className="flex items-center justify-between">
                                    <CardTitle>
                                        {caso.numero_expediente}
                                    </CardTitle>
                                    <Badge
                                        variant={ESTADO_VARIANTS[caso.estado]}
                                    >
                                        {ESTADO_LABELS[caso.estado]}
                                    </Badge>
                                </div>
                            </CardHeader>
                            <CardContent className="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p className="text-muted-foreground">
                                        Fecha de inicio
                                    </p>
                                    <p className="font-medium">
                                        {formatFecha(caso.fecha_inicio)}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-muted-foreground">
                                        Fecha de archivo
                                    </p>
                                    <p className="font-medium">
                                        {caso.fecha_archivo
                                            ? formatFecha(caso.fecha_archivo)
                                            : '—'}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Cliente</CardTitle>
                            </CardHeader>
                            <CardContent className="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p className="text-muted-foreground">
                                        Nombre
                                    </p>
                                    <p className="font-medium">
                                        {caso.cliente.nombre}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-muted-foreground">
                                        Cédula
                                    </p>
                                    <p className="font-medium">
                                        {caso.cliente.cedula}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-muted-foreground">
                                        Teléfono
                                    </p>
                                    <p className="font-medium">
                                        {caso.cliente.telefono ?? '—'}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-muted-foreground">
                                        Email
                                    </p>
                                    <p className="font-medium">
                                        {caso.cliente.email ?? '—'}
                                    </p>
                                </div>
                                <div className="col-span-2">
                                    <p className="text-muted-foreground">
                                        Dirección
                                    </p>
                                    <p className="font-medium">
                                        {caso.cliente.direccion ?? '—'}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>
                                    Abogados asignados ({caso.abogados.length})
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="flex flex-col gap-4">
                                {caso.abogados.length === 0 ? (
                                    <p className="text-sm text-muted-foreground">
                                        Este caso no tiene abogados asignados.
                                    </p>
                                ) : (
                                    caso.abogados.map((abogado) => (
                                        <div
                                            key={abogado.id}
                                            className="grid grid-cols-2 gap-4 border-b pb-4 text-sm last:border-0 last:pb-0"
                                        >
                                            <div>
                                                <p className="text-muted-foreground">
                                                    Nombre
                                                </p>
                                                <p className="font-medium">
                                                    {abogado.nombre}
                                                </p>
                                            </div>
                                            <div>
                                                <p className="text-muted-foreground">
                                                    Tarjeta profesional
                                                </p>
                                                <p className="font-medium">
                                                    {abogado.tarjeta_profesional ??
                                                        '—'}
                                                </p>
                                            </div>
                                            <div>
                                                <p className="text-muted-foreground">
                                                    Teléfono
                                                </p>
                                                <p className="font-medium">
                                                    {abogado.telefono ?? '—'}
                                                </p>
                                            </div>
                                            <div>
                                                <p className="text-muted-foreground">
                                                    Email
                                                </p>
                                                <p className="font-medium">
                                                    {abogado.email ?? '—'}
                                                </p>
                                            </div>
                                        </div>
                                    ))
                                )}
                            </CardContent>
                        </Card>
                    </div>
                )}
            </div>
        </>
    );
}
