import { Head, Link, usePage } from '@inertiajs/react';
import { Scale } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Pagination,
    PaginationContent,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { ESTADO_LABELS, ESTADO_VARIANTS } from '@/lib/estado-caso';
import { formatFecha } from '@/lib/utils';
import { show as showCaso } from '@/routes/casos';
import type { PaginatedCasos } from '@/types/casos';

export default function CasosIndex({ casos }: { casos: PaginatedCasos }) {
    const { name } = usePage().props;
    const hayCasos = casos.data.length > 0;

    return (
        <>
            <Head title="Casos" />
            <div className="mx-auto flex min-h-screen w-full max-w-5xl flex-col gap-6 p-6 lg:p-8">
                <header className="flex items-center gap-3 border-b pb-4">
                    <div className="flex size-10 items-center justify-center rounded-md bg-primary text-primary-foreground">
                        <Scale className="size-5" />
                    </div>
                    <div>
                        <h1 className="text-lg font-semibold">{name}</h1>
                        <p className="text-sm text-muted-foreground">
                            Listado de casos
                        </p>
                    </div>
                </header>

                {hayCasos ? (
                    <>
                        <div className="overflow-hidden rounded-lg border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Expediente</TableHead>
                                        <TableHead>Cliente</TableHead>
                                        <TableHead>Fecha de inicio</TableHead>
                                        <TableHead>Estado</TableHead>
                                        <TableHead className="text-right">
                                            Acciones
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {casos.data.map((caso) => (
                                        <TableRow key={caso.id}>
                                            <TableCell className="font-medium">
                                                {caso.numero_expediente}
                                            </TableCell>
                                            <TableCell>
                                                {caso.cliente.nombre}
                                            </TableCell>
                                            <TableCell>
                                                {formatFecha(caso.fecha_inicio)}
                                            </TableCell>
                                            <TableCell>
                                                <Badge
                                                    variant={
                                                        ESTADO_VARIANTS[
                                                            caso.estado
                                                        ]
                                                    }
                                                >
                                                    {ESTADO_LABELS[caso.estado]}
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <Button
                                                    asChild
                                                    variant="outline"
                                                    size="sm"
                                                >
                                                    <Link
                                                        href={showCaso(caso.id)}
                                                    >
                                                        Ver detalles
                                                    </Link>
                                                </Button>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </div>

                        <Pagination>
                            <PaginationContent>
                                <PaginationItem>
                                    <PaginationPrevious
                                        href={casos.links.prev ?? '#'}
                                        preserveScroll
                                        tabIndex={
                                            casos.links.prev ? undefined : -1
                                        }
                                        aria-disabled={!casos.links.prev}
                                        className={
                                            !casos.links.prev
                                                ? 'pointer-events-none opacity-50'
                                                : undefined
                                        }
                                    />
                                </PaginationItem>
                                <PaginationItem>
                                    <span className="px-4 text-sm whitespace-nowrap text-muted-foreground">
                                        Página {casos.meta.current_page} de{' '}
                                        {casos.meta.last_page}
                                    </span>
                                </PaginationItem>
                                <PaginationItem>
                                    <PaginationNext
                                        href={casos.links.next ?? '#'}
                                        preserveScroll
                                        tabIndex={
                                            casos.links.next ? undefined : -1
                                        }
                                        aria-disabled={!casos.links.next}
                                        className={
                                            !casos.links.next
                                                ? 'pointer-events-none opacity-50'
                                                : undefined
                                        }
                                    />
                                </PaginationItem>
                            </PaginationContent>
                        </Pagination>
                    </>
                ) : (
                    <div className="rounded-lg border border-dashed py-16 text-center text-muted-foreground">
                        Aún no hay casos registrados.
                    </div>
                )}
            </div>
        </>
    );
}
