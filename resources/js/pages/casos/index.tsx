import { Head, Link, router, usePage } from '@inertiajs/react';
import { ArrowDown, ArrowUp, ArrowUpDown, Scale } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
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
import { cn, formatFecha } from '@/lib/utils';
import { home } from '@/routes';
import { show as showCaso } from '@/routes/casos';
import type {
    CasoFilters,
    CasoSortBy,
    EstadoCaso,
    PaginatedCasos,
} from '@/types/casos';

const ESTADOS = Object.keys(ESTADO_LABELS) as EstadoCaso[];

const SEARCH_DEBOUNCE_MS = 350;

function actualizarQuery(
    casos: PaginatedCasos,
    filters: CasoFilters,
    overrides: Record<string, string | number | null>,
) {
    const query: Record<string, string | number> = {
        per_page: casos.meta.per_page,
        sort_by: filters.sort_by,
        sort_dir: filters.sort_dir,
        ...(filters.search ? { search: filters.search } : {}),
        ...(filters.estado ? { estado: filters.estado } : {}),
        ...overrides,
    };

    for (const key of Object.keys(query)) {
        const value = query[key];

        if (value === null || value === '') {
            delete query[key];
        }
    }

    router.get(home.url(), query, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function SortableTableHead({
    label,
    column,
    filters,
    onSort,
    align = 'left',
}: {
    label: string;
    column: CasoSortBy;
    filters: CasoFilters;
    onSort: (column: CasoSortBy) => void;
    align?: 'left' | 'right' | 'center';
}) {
    const isActive = filters.sort_by === column;
    const Icon = !isActive
        ? ArrowUpDown
        : filters.sort_dir === 'asc'
          ? ArrowUp
          : ArrowDown;

    return (
        <TableHead
            className={cn(
                align === 'right' && 'text-right',
                align === 'center' && 'text-center',
            )}
        >
            <button
                type="button"
                onClick={() => onSort(column)}
                className={cn(
                    'inline-flex items-center gap-1 hover:text-foreground',
                    align === 'center' && 'justify-center',
                    isActive ? 'text-foreground' : 'text-muted-foreground',
                )}
            >
                {label}
                <Icon className="size-3.5" />
            </button>
        </TableHead>
    );
}

export default function CasosIndex({
    casos,
    filters,
}: {
    casos: PaginatedCasos;
    filters: CasoFilters;
}) {
    const { name } = usePage().props;
    const hayCasos = casos.data.length > 0;
    const hayFiltrosActivos = Boolean(filters.search || filters.estado);

    const [search, setSearch] = useState(filters.search ?? '');
    const [filtersSearchSincronizado, setFiltersSearchSincronizado] = useState(
        filters.search,
    );
    const esPrimerRender = useRef(true);

    if (filters.search !== filtersSearchSincronizado) {
        setFiltersSearchSincronizado(filters.search);
        setSearch(filters.search ?? '');
    }

    useEffect(() => {
        if (esPrimerRender.current) {
            esPrimerRender.current = false;

            return;
        }

        if (search === (filters.search ?? '')) {
            return;
        }

        const timeout = setTimeout(() => {
            actualizarQuery(casos, filters, {
                search: search || null,
                page: 1,
            });
        }, SEARCH_DEBOUNCE_MS);

        return () => clearTimeout(timeout);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [search]);

    function handleSort(column: CasoSortBy) {
        const isActive = filters.sort_by === column;
        const nextDir = isActive && filters.sort_dir === 'asc' ? 'desc' : 'asc';

        actualizarQuery(casos, filters, {
            sort_by: column,
            sort_dir: nextDir,
            page: 1,
        });
    }

    function handleEstadoFilter(estado: EstadoCaso | null) {
        actualizarQuery(casos, filters, { estado, page: 1 });
    }

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

                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <Input
                        type="search"
                        placeholder="Buscar por expediente o cliente..."
                        value={search}
                        onChange={(event) => setSearch(event.target.value)}
                        className="sm:max-w-xs"
                        aria-label="Buscar por expediente o cliente"
                    />

                    <div className="flex flex-wrap gap-2">
                        <button
                            type="button"
                            onClick={() => handleEstadoFilter(null)}
                            aria-pressed={!filters.estado}
                        >
                            <Badge
                                variant={
                                    !filters.estado ? 'default' : 'outline'
                                }
                                className={cn(
                                    filters.estado && 'text-muted-foreground',
                                )}
                            >
                                Todos
                            </Badge>
                        </button>
                        {ESTADOS.map((estado) => {
                            const isActive = filters.estado === estado;

                            return (
                                <button
                                    key={estado}
                                    type="button"
                                    onClick={() => handleEstadoFilter(estado)}
                                    aria-pressed={isActive}
                                >
                                    <Badge
                                        variant={
                                            isActive
                                                ? ESTADO_VARIANTS[estado]
                                                : 'outline'
                                        }
                                        className={cn(
                                            !isActive &&
                                                'text-muted-foreground',
                                        )}
                                    >
                                        {ESTADO_LABELS[estado]}
                                    </Badge>
                                </button>
                            );
                        })}
                    </div>
                </div>

                {hayCasos ? (
                    <>
                        <div className="overflow-hidden rounded-lg border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Expediente</TableHead>
                                        <TableHead>Cliente</TableHead>
                                        <SortableTableHead
                                            label="Fecha de inicio"
                                            column="fecha_inicio"
                                            filters={filters}
                                            onSort={handleSort}
                                            align="right"
                                        />
                                        <SortableTableHead
                                            label="Estado"
                                            column="estado"
                                            filters={filters}
                                            onSort={handleSort}
                                            align="center"
                                        />
                                        <TableHead className="text-right">
                                            Acciones
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {casos.data.map((caso, index) => (
                                        <TableRow
                                            key={caso.id}
                                            className={cn(
                                                index % 2 === 1 &&
                                                    'bg-muted/50',
                                            )}
                                        >
                                            <TableCell className="py-3 font-medium">
                                                {caso.numero_expediente}
                                            </TableCell>
                                            <TableCell className="py-3">
                                                {caso.cliente.nombre}
                                            </TableCell>
                                            <TableCell className="py-3 text-right">
                                                {formatFecha(caso.fecha_inicio)}
                                            </TableCell>
                                            <TableCell className="py-3 text-center">
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
                                            <TableCell className="py-3 text-right">
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
                        {hayFiltrosActivos
                            ? 'No se encontraron casos con los filtros aplicados.'
                            : 'Aún no hay casos registrados.'}
                    </div>
                )}
            </div>
        </>
    );
}
