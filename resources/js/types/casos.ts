export type EstadoCaso =
    'en_tramite' | 'archivado' | 'suspendido' | 'finalizado';

export type CasoSortBy = 'fecha_inicio' | 'estado';

export type CasoSortDir = 'asc' | 'desc';

export type CasoFilters = {
    search: string | null;
    estado: EstadoCaso | null;
    sort_by: CasoSortBy;
    sort_dir: CasoSortDir;
};

export type CasoListItem = {
    id: number;
    numero_expediente: string;
    fecha_inicio: string;
    estado: EstadoCaso;
    cliente: {
        id: number;
        nombre: string;
    };
    [key: string]: unknown;
};

export type PaginationLinks = {
    first: string | null;
    last: string | null;
    prev: string | null;
    next: string | null;
};

export type PaginationMeta = {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
};

export type PaginatedCasos = {
    data: CasoListItem[];
    links: PaginationLinks;
    meta: PaginationMeta;
};

export type ClienteDetalle = {
    id: number;
    cedula: string;
    nombre: string;
    telefono: string | null;
    email: string | null;
    direccion: string | null;
};

export type AbogadoDetalle = {
    id: number;
    cedula: string;
    nombre: string;
    telefono: string | null;
    email: string | null;
    tarjeta_profesional: string | null;
};

export type CasoDetalle = {
    id: number;
    numero_expediente: string;
    fecha_inicio: string;
    fecha_archivo: string | null;
    estado: EstadoCaso;
    cliente: ClienteDetalle;
    abogados: AbogadoDetalle[];
};
